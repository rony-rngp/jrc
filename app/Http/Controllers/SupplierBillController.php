<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupplierBill;
use App\Models\SupplierBillItem;
use App\Models\Party;
use App\Models\Project;
use App\Models\MaterialCategory;
use App\Models\PaymentMethod;
use App\Models\SupplierPayment;
use App\Models\GeneralTransaction;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SupplierBillController extends Controller
{
    public function index(Request $request)
    {
        // AJAX Request Handlers
        if ($request->has('ajax_request') || $request->ajax()) {
            $ajax_request = $request->input('ajax_request');

            // 1. Get Suppliers
            if ($ajax_request == 'get_suppliers') {
                $suppliers = Party::orderBy('party_name', 'asc')->select('id', 'party_name')->get();
                return response()->json($suppliers);
            }

            // 2. Get Projects
            if ($ajax_request == 'get_projects') {
                $projects = Project::orderBy('project_name', 'asc')->select('id', 'project_name')->get();
                return response()->json($projects);
            }

            // 3. Get Project Budget & Existing Expenses
            if ($ajax_request == 'get_project_budget') {
                $projectId = (int)$request->input('project_id');
                $project = Project::find($projectId);
                $estVal = $project ? (float)$project->estimated_value : 0;

                $totalCost = 0;
                $totalCost += (float)SupplierBill::where('project_id', $projectId)->whereIn('approval_status', ['Approved', 'Pending'])->sum('grand_total');
                $totalCost += (float)DB::table('project_expenses')->where('project_id', $projectId)->whereIn('approval_status', ['Approved', 'Pending'])->sum('amount');
                $totalCost += (float)GeneralTransaction::where('project_id', $projectId)
                    ->where('t_type', 'Payment')
                    ->where('note', 'not like', '%Purchase%')
                    ->where('note', 'not like', '%Cash Purchase%')
                    ->whereIn('approval_status', ['Approved', 'Pending'])
                    ->sum('amount');

                return response()->json([
                    'estimated_value' => $estVal,
                    'total_expense' => $totalCost
                ]);
            }

            // 4. Get Products / Categories
            if ($ajax_request == 'get_products') {
                $products = MaterialCategory::orderBy('cat_name', 'asc')->select('id', 'cat_name as product_name')->get();
                return response()->json($products);
            }

            // 5. Get Payment Methods
            if ($ajax_request == 'get_payment_methods') {
                $type = $request->input('type');
                $methods = PaymentMethod::where('account_type', $type)
                    ->orderBy('method_name', 'asc')
                    ->get()
                    ->map(function ($row) {
                        return [
                            'id' => $row->id,
                            'method_name' => $row->method_name,
                            'amount' => $row->amount,
                            'amount_formatted' => number_format((float)$row->amount, 2),
                        ];
                    });
                return response()->json($methods);
            }

            // 6. Check Voucher
            if ($ajax_request == 'check_voucher') {
                $voucher = $request->input('voucher');
                $q1 = GeneralTransaction::where('check_no', $voucher)->exists();
                $q2 = SupplierBill::where('voucher_no', $voucher)->exists();

                if ($q1 || $q2) {
                    return response()->json(['status' => 'duplicate']);
                }
                return response()->json(['status' => 'unique']);
            }
        }

        $bills = SupplierBill::with(['supplier', 'project', 'items.category'])
            ->orderBy('id', 'desc')
            ->get();

        $suppliers = Party::orderBy('party_name', 'asc')->get();
        $projects = Project::orderBy('project_name', 'asc')->get();
        $categories = MaterialCategory::orderBy('cat_name', 'asc')->get();
        $paymentMethods = PaymentMethod::orderBy('method_name', 'asc')->get();

        return view('supplier_bills.index', compact('bills', 'suppliers', 'projects', 'categories', 'paymentMethods'));
    }

    public function create()
    {
        $suppliers = Party::orderBy('party_name', 'asc')->get();
        $projects = Project::orderBy('project_name', 'asc')->get();
        $categories = MaterialCategory::orderBy('cat_name', 'asc')->get();
        $products = $categories;
        $paymentMethods = PaymentMethod::orderBy('method_name', 'asc')->get();
        $lastBill = SupplierBill::orderBy('id', 'desc')->first();
        $nextVoucher = $lastBill ? ((int)$lastBill->voucher_no + 1) : 1001;
        if (!$nextVoucher || !is_numeric($nextVoucher)) {
            $nextVoucher = 1001;
        }
        return view('supplier_bills.create', compact('suppliers', 'projects', 'categories', 'products', 'paymentMethods', 'nextVoucher'));
    }

    public function store(Request $request)
    {
        if ($request->has('ajax_request') || $request->ajax()) {
            return $this->index($request);
        }

        $user = Auth::user();
        $user_role = $user->role ?? 1;
        $approval_status = ($user_role == 1) ? 'Approved' : 'Pending';

        $bill_type = $request->input('bill_entry_type') ?? $request->input('bill_type') ?? 'Only Bill';
        $supplier_id = $request->input('supplier_id');
        $project_id = (int)$request->input('project_id', 0);
        $voucher_no = $request->input('voucher_no') ?? ('SB-' . strtoupper(uniqid()));
        $bill_date = $request->input('bill_date') ?? $request->input('date') ?? date('Y-m-d');
        $grand_total = (float)$request->input('grand_total', 0);
        $note = $request->input('note', '');

        $input_paid_amt = 0;
        if ($bill_type == 'Bill with Payment' || $request->filled('paid_amount')) {
            $input_paid_amt = (float)$request->input('paid_amount', 0);
        }

        $check_voucher = SupplierBill::where('voucher_no', $voucher_no)->exists() || GeneralTransaction::where('check_no', $voucher_no)->exists();
        if ($check_voucher) {
            return redirect()->route('supplier-bills.index')->with('error', "Critical Error: Voucher No '$voucher_no' is already used!");
        }

        DB::beginTransaction();
        try {
            $bill = SupplierBill::create([
                'bill_type' => $bill_type,
                'supplier_id' => $supplier_id,
                'project_id' => $project_id,
                'voucher_no' => $voucher_no,
                'bill_date' => $bill_date,
                'grand_total' => $grand_total,
                'paid_amount' => $input_paid_amt,
                'due_amount' => max(0, $grand_total - $input_paid_amt),
                'note' => $note,
                'approval_status' => $approval_status,
            ]);

            $calculatedTotal = 0;
            $items = $request->input('items', []);
            if (is_array($items) && count($items) > 0) {
                foreach ($items as $item) {
                    $p_id = (int)($item['product_id'] ?? 0);
                    $q = (float)($item['qty'] ?? 0);
                    $u_p = (float)($item['unit_price'] ?? 0);
                    $t_p = (float)($item['total_price'] ?? ($q * $u_p));

                    if ($p_id > 0 && $q > 0) {
                        $calculatedTotal += $t_p;
                        SupplierBillItem::create([
                            'bill_id' => $bill->id,
                            'product_id' => $p_id,
                            'qty' => $q,
                            'unit_price' => $u_p,
                            'total_price' => $t_p,
                        ]);
                    }
                }
            } else {
                $products = $request->input('product_id', []);
                $qtys = $request->input('qty', []);
                $unit_prices = $request->input('unit_price', []);
                $totals = $request->input('total_price', []);

                for ($i = 0; $i < count($products); $i++) {
                    $p_id = (int)($products[$i] ?? 0);
                    $q = (float)($qtys[$i] ?? 0);
                    $u_p = (float)($unit_prices[$i] ?? 0);
                    $t_p = (float)($totals[$i] ?? ($q * $u_p));

                    if ($p_id > 0 && $q > 0) {
                        $calculatedTotal += $t_p;
                        SupplierBillItem::create([
                            'bill_id' => $bill->id,
                            'product_id' => $p_id,
                            'qty' => $q,
                            'unit_price' => $u_p,
                            'total_price' => $t_p,
                        ]);
                    }
                }
            }

            if ($grand_total == 0 && $calculatedTotal > 0) {
                $grand_total = $calculatedTotal;
                $bill->update([
                    'grand_total' => $grand_total,
                    'due_amount' => max(0, $grand_total - $input_paid_amt),
                ]);
            }

            if (($bill_type == 'Bill with Payment' || $input_paid_amt > 0) && $input_paid_amt > 0) {
                $deposit_category = $request->input('deposit_category', 'Cash');
                $method_id = (int)$request->input('method_id');
                if (!$method_id && $request->filled('method_name')) {
                    $pmFound = PaymentMethod::where('method_name', $request->input('method_name'))->first();
                    if ($pmFound) {
                        $method_id = $pmFound->id;
                    }
                }

                $trans_type = "";
                $trans_ref_no = "";
                $trans_date = null;

                if ($deposit_category == 'Bank') {
                    $trans_type = $request->input('bank_trans_type', '');
                    $trans_ref_no = $request->input('bank_ref_no', '');
                    $trans_date = $request->input('bank_ref_date');
                } elseif ($deposit_category == 'MFS') {
                    $trans_type = $request->input('mfs_type', '');
                    $trans_ref_no = $request->input('mfs_ref_no', '');
                    $trans_date = $request->input('mfs_ref_date');
                }

                $supplierObj = Party::where('party_name', $supplier_id)->first() ?? Party::find($supplier_id);
                $supplier_id_int = $supplierObj ? $supplierObj->id : 0;

                $party_name_text = ($supplier_id == 'Cash Purchase') ? "Cash Purchase" : "Supplier: $supplier_id";
                $trx_note = "Instant Payment for Bill ($voucher_no) | $party_name_text | " . $note;

                SupplierPayment::create([
                    'supplier_id' => $supplier_id_int,
                    'project_id' => $project_id,
                    'bill_id' => $bill->id,
                    'payment_mode' => 'Project',
                    'amount' => $input_paid_amt,
                    'method_id' => $method_id,
                    'deposit_category' => $deposit_category,
                    'bank_trans_type' => $trans_type,
                    'ref_no' => $trans_ref_no,
                    'ref_date' => $trans_date,
                    'voucher_no' => $voucher_no,
                    't_date' => $bill_date,
                    'note' => $trx_note,
                    'approval_status' => $approval_status,
                ]);

                if ($approval_status == 'Approved' && $method_id) {
                    $pm = PaymentMethod::find($method_id);
                    if ($pm) {
                        $pm->decrement('amount', $input_paid_amt);
                    }
                }
            }

            ActivityLogger::log('Create', "Added a new Supplier Bill. Voucher No: {$voucher_no}");
            DB::commit();

            return redirect()->route('supplier-bills.index')->with('success', "Bill (Voucher: <b>$voucher_no</b>) saved successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('supplier-bills.index')->with('error', "Operation Failed: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        $bill = is_numeric($id) ? SupplierBill::with(['items.category'])->find($id) : SupplierBill::with(['items.category'])->where('voucher_no', $id)->first();
        if (!$bill) {
            $bill = SupplierBill::with(['items.category'])->where('voucher_no', $id)->firstOrFail();
        }

        $supplier = Party::where('party_name', $bill->supplier_id)->orWhere('id', $bill->supplier_id)->first();
        $project = Project::find($bill->project_id);

        // Fetch payment details
        $payment = SupplierPayment::with('paymentMethod')->where('bill_id', $bill->id)->first();
        $pay_method = $payment && $payment->paymentMethod ? $payment->paymentMethod->method_name : '';
        $trx_id = $payment ? ($payment->ref_no ?: $payment->voucher_no) : '';

        // Calculate real paid and due
        $ext_pending = (float)SupplierPayment::where('bill_id', $bill->id)->where('approval_status', 'Pending')->sum('amount');
        $real_paid = (float)$bill->paid_amount + $ext_pending;
        $real_due = max(0, (float)$bill->grand_total - $real_paid);
        $status = ($real_due <= 0) ? 'PAID' : 'DUE';

        if (request()->has('print') || request()->get('view') == 'print') {
            return view('supplier_bills.print', compact('bill', 'supplier', 'project', 'pay_method', 'trx_id', 'real_paid', 'real_due', 'status'));
        }

        return view('supplier_bills.show', compact('bill', 'supplier', 'project', 'pay_method', 'trx_id', 'real_paid', 'real_due', 'status', 'payment'));
    }

    public function edit($id)
    {
        $bill = SupplierBill::with(['items.category'])->findOrFail($id);
        $suppliers = Party::orderBy('party_name', 'asc')->get();
        $projects = Project::orderBy('project_name', 'asc')->get();
        $categories = MaterialCategory::orderBy('cat_name', 'asc')->get();
        $paymentMethods = PaymentMethod::orderBy('method_name', 'asc')->get();

        $payment = null;
        if ($bill->bill_type == 'Bill with Payment') {
            $payment = SupplierPayment::where('bill_id', $id)->first();
        }

        return view('supplier_bills.edit', compact('bill', 'suppliers', 'projects', 'categories', 'paymentMethods', 'payment'));
    }

    public function update(Request $request, $id)
    {
        $bill = SupplierBill::findOrFail($id);

        $bill_type = $request->input('bill_entry_type', 'Only Bill');
        $supplier_id = $request->input('supplier_id');
        $project_id = (int)$request->input('project_id', 0);
        $voucher_no = $request->input('voucher_no', $bill->voucher_no);
        $bill_date = $request->input('bill_date', date('Y-m-d'));
        $grand_total = (float)$request->input('grand_total', 0);
        $note = $request->input('note', '');

        $input_paid_amt = 0;
        if ($bill_type == 'Bill with Payment') {
            $input_paid_amt = (float)$request->input('paid_amount', 0);
        }

        // Check duplicate voucher excluding current bill
        $check_voucher = SupplierBill::where('voucher_no', $voucher_no)->where('id', '!=', $id)->exists();
        if ($check_voucher) {
            return redirect()->back()->with('error', "Error: Voucher No '$voucher_no' is already used by another bill!");
        }

        DB::beginTransaction();
        try {
            $old_status = $bill->approval_status;
            $old_voucher_no = $bill->voucher_no;

            // 1. Revert Old Instant Payment if approved
            $oldPay = SupplierPayment::where('bill_id', $id)->where('voucher_no', $old_voucher_no)->first();
            if ($oldPay) {
                if ($oldPay->approval_status == 'Approved' && $oldPay->method_id) {
                    $pm = PaymentMethod::find($oldPay->method_id);
                    if ($pm) {
                        $pm->increment('amount', $oldPay->amount);
                    }
                }
                $oldPay->delete();
            }

            // 2. Delete Old Items
            SupplierBillItem::where('bill_id', $id)->delete();

            // 3. Other approved payments
            $total_other_approved = (float)SupplierPayment::where('bill_id', $id)->where('approval_status', 'Approved')->sum('amount');
            $set_paid = $total_other_approved;
            if ($old_status == 'Approved') {
                $set_paid += $input_paid_amt;
            }
            $set_due = max(0, $grand_total - $set_paid);

            // 4. Update Bill
            $bill->update([
                'bill_type' => $bill_type,
                'supplier_id' => $supplier_id,
                'project_id' => $project_id,
                'voucher_no' => $voucher_no,
                'bill_date' => $bill_date,
                'grand_total' => $grand_total,
                'paid_amount' => $set_paid,
                'due_amount' => $set_due,
                'note' => $note,
            ]);

            // 5. Insert New Items
            $products = $request->input('product_id', []);
            $qtys = $request->input('qty', []);
            $unit_prices = $request->input('unit_price', []);
            $totals = $request->input('total_price', []);

            $calculatedTotal = 0;
            for ($i = 0; $i < count($products); $i++) {
                $p_id = (int)($products[$i] ?? 0);
                $q = (float)($qtys[$i] ?? 0);
                $u_p = (float)($unit_prices[$i] ?? 0);
                $t_p = (float)($totals[$i] ?? ($q * $u_p));

                if ($p_id > 0 && $q > 0) {
                    $calculatedTotal += $t_p;
                    SupplierBillItem::create([
                        'bill_id' => $bill->id,
                        'product_id' => $p_id,
                        'qty' => $q,
                        'unit_price' => $u_p,
                        'total_price' => $t_p,
                    ]);
                }
            }

            if ($grand_total == 0 && $calculatedTotal > 0) {
                $grand_total = $calculatedTotal;
                $bill->update([
                    'grand_total' => $grand_total,
                    'due_amount' => max(0, $grand_total - $set_paid),
                ]);
            }

            // 6. Record New Payment if Bill with Payment
            if ($bill_type == 'Bill with Payment' && $input_paid_amt > 0) {
                $deposit_category = $request->input('deposit_category', 'Cash');
                $method_id = (int)$request->input('method_id');
                if (!$method_id && $request->filled('method_name')) {
                    $pmFound = PaymentMethod::where('method_name', $request->input('method_name'))->first();
                    if ($pmFound) {
                        $method_id = $pmFound->id;
                    }
                }

                $trans_type = "";
                $trans_ref_no = "";
                $trans_date = null;

                if ($deposit_category == 'Bank') {
                    $trans_type = $request->input('bank_trans_type', '');
                    $trans_ref_no = $request->input('bank_ref_no', '');
                    $trans_date = $request->input('bank_ref_date');
                } elseif ($deposit_category == 'MFS') {
                    $trans_type = $request->input('mfs_type', '');
                    $trans_ref_no = $request->input('mfs_ref_no', '');
                    $trans_date = $request->input('mfs_ref_date');
                }

                $supplierObj = Party::where('party_name', $supplier_id)->first() ?? Party::find($supplier_id);
                $supplier_id_int = $supplierObj ? $supplierObj->id : 0;
                $party_name_text = ($supplier_id == 'Cash Purchase') ? "Cash Purchase" : "Supplier: $supplier_id";
                $trx_note = "Instant Payment for Bill ($voucher_no) | $party_name_text | " . $note;

                SupplierPayment::create([
                    'supplier_id' => $supplier_id_int,
                    'project_id' => $project_id,
                    'bill_id' => $bill->id,
                    'payment_mode' => 'Project',
                    'amount' => $input_paid_amt,
                    'method_id' => $method_id,
                    'deposit_category' => $deposit_category,
                    'bank_trans_type' => $trans_type,
                    'ref_no' => $trans_ref_no,
                    'ref_date' => $trans_date,
                    'voucher_no' => $voucher_no,
                    't_date' => $bill_date,
                    'note' => $trx_note,
                    'approval_status' => $old_status,
                ]);

                if ($old_status == 'Approved' && $method_id) {
                    $pm = PaymentMethod::find($method_id);
                    if ($pm) {
                        $pm->decrement('amount', $input_paid_amt);
                    }
                }
            }

            ActivityLogger::log('Update', "Edited Supplier Bill. Voucher No: {$voucher_no}");
            DB::commit();

            return redirect()->route('supplier-bills.index')->with('success', "Bill (Voucher: <b>$voucher_no</b>) updated successfully! Status remains <b>$old_status</b>.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', "Update Failed: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $bill = SupplierBill::findOrFail($id);
            
            // Revert payments from account balances if approved
            $payments = SupplierPayment::where('bill_id', $id)->get();
            foreach ($payments as $p) {
                if ($p->approval_status == 'Approved' && $p->method_id) {
                    $pm = PaymentMethod::find($p->method_id);
                    if ($pm) {
                        $pm->increment('amount', $p->amount);
                    }
                }
                $p->delete();
            }

            SupplierBillItem::where('bill_id', $id)->delete();
            $bill->delete();

            ActivityLogger::log('Delete', "Deleted Supplier Bill ID {$id}");
            DB::commit();

            return redirect()->route('supplier-bills.index')->with('success', 'Supplier bill deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('supplier-bills.index')->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }
}
