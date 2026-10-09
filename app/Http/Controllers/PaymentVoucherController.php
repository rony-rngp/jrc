<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\AccountHead;
use App\Models\Party;
use App\Models\Project;
use App\Models\SupplierBill;
use App\Models\SupplierPayment;
use App\Models\GeneralTransaction;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaymentVoucherController extends Controller
{
    public function index(Request $request)
    {
        // AJAX Request Handlers
        if ($request->has('ajax_request') || $request->ajax()) {
            $ajax_request = $request->input('ajax_request');

            // 1. Get Payment Methods
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

            // 2. Get Sub Heads
            if ($ajax_request == 'get_sub_heads') {
                $parentId = (int)$request->input('parent_id');
                $subHeads = AccountHead::where('parent_id', $parentId)
                    ->where('head_type', 'Payment')
                    ->orderBy('head_name', 'asc')
                    ->select('id', 'head_name')
                    ->get();
                return response()->json($subHeads);
            }

            // 3. Get All Projects
            if ($ajax_request == 'get_all_projects') {
                $projects = Project::orderBy('project_name', 'asc')->get()->map(function($p){
                    return ['id' => $p->id, 'name' => $p->project_name];
                });
                return response()->json($projects);
            }

            // 4. Get Project Expense Reasons
            if ($ajax_request == 'get_project_expense_reasons') {
                $heads = DB::table('project_expense_heads')->orderBy('head_name', 'asc')->pluck('head_name');
                return response()->json($heads);
            }

            // 5. Get Supplier Projects
            if ($ajax_request == 'get_supplier_projects') {
                $supplierId = $request->input('supplier_id');
                $projects = DB::table('supplier_bills as sb')
                    ->join('project as p', 'p.id', '=', 'sb.project_id')
                    ->where('sb.supplier_id', $supplierId)
                    ->select('p.id', 'p.project_name')
                    ->distinct()
                    ->orderBy('p.project_name', 'asc')
                    ->get();
                return response()->json($projects);
            }

            // 6. Get Supplier Pending Bills
            if ($ajax_request == 'get_supplier_pending_bills') {
                $supplierId = $request->input('supplier_id');
                $projectId = (int)$request->input('project_id');

                $bills = SupplierBill::where('supplier_id', $supplierId)
                    ->where('project_id', $projectId)
                    ->where('due_amount', '>', 0)
                    ->orderBy('bill_date', 'asc')
                    ->get(['id', 'voucher_no', 'bill_date', 'note', 'grand_total', 'due_amount']);

                return response()->json($bills);
            }

            // 7. Get Supplier Overall Due / Advance
            if ($ajax_request == 'get_supplier_total_dues') {
                $supplierId = $request->input('supplier_id');
                $billed = (float)SupplierBill::where('supplier_id', $supplierId)->sum('grand_total');
                $paid = (float)SupplierPayment::where('supplier_id', $supplierId)->sum('amount');

                $net = $billed - $paid;
                $status = ($net > 0) ? 'Total Due' : (($net < 0) ? 'Total Advance' : 'Settled');

                return response()->json([
                    'amount' => number_format(abs($net), 2),
                    'status' => $status,
                    'color' => ($net > 0) ? 'danger' : 'success'
                ]);
            }

            // 8. Get Specific Project Summary
            if ($ajax_request == 'get_supplier_project_summary') {
                $supplierId = $request->input('supplier_id');
                $projectId = (int)$request->input('project_id');

                $billed = (float)SupplierBill::where('supplier_id', $supplierId)->where('project_id', $projectId)->sum('grand_total');
                $paid = (float)SupplierPayment::where('supplier_id', $supplierId)->where('project_id', $projectId)->sum('amount');

                $net = $billed - $paid;
                $status = ($net > 0) ? 'Due Balance' : (($net < 0) ? 'Advance Balance' : 'Fully Cleared');
                $color = ($net > 0) ? '#d9534f' : (($net < 0) ? '#00a65a' : '#333');

                return response()->json([
                    'total_billed' => number_format($billed, 2),
                    'total_paid' => number_format($paid, 2),
                    'net_balance' => number_format(abs($net), 2),
                    'status' => $status,
                    'color' => $color
                ]);
            }

            // 9. Check Duplicate Voucher
            if ($ajax_request == 'check_voucher') {
                $voucher = $request->input('voucher');
                $q1 = SupplierPayment::where('voucher_no', $voucher)->exists();
                $q2 = DB::table('project_expenses')->where('voucher_no', $voucher)->exists();
                $q3 = GeneralTransaction::where('check_no', $voucher)->exists();
                $q4 = DB::table('payment_received_project')->where('check_no', $voucher)->exists();

                if ($q1 || $q2 || $q3 || $q4) {
                    return response()->json(['status' => 'duplicate']);
                }
                return response()->json(['status' => 'unique']);
            }

            // 10. Get Loan Info
            if ($ajax_request == 'get_loan_info') {
                $headId = (int)$request->input('head_id');
                $totalReceived = (float)GeneralTransaction::where('head_id', $headId)->where('t_type', 'Receive')->sum('amount');
                $principalPaid = (float)GeneralTransaction::where('head_id', $headId)->where('t_type', 'Payment')
                    ->where(function($q) {
                        $q->where('note', 'like', '%Principal%')->orWhere('note', 'like', '%Adjustment%');
                    })->sum('amount');
                $interestPaid = (float)GeneralTransaction::where('head_id', $headId)->where('t_type', 'Payment')
                    ->where('note', 'like', '%Interest%')->sum('amount');

                return response()->json([
                    'total_received' => number_format($totalReceived, 2),
                    'principal_paid' => number_format($principalPaid, 2),
                    'interest_paid' => number_format($interestPaid, 2),
                    'due_principal' => number_format($totalReceived - $principalPaid, 2)
                ]);
            }

            // 11. Get Project Budget/Estimated Value Status
            if ($ajax_request == 'get_project_budget_status') {
                $projectId = (int)$request->input('project_id');
                $project = Project::find($projectId);

                $estVal = $project ? (float)$project->estimated_value : 0;
                $estCost = $project ? (float)$project->estimated_cost : 0;

                $supCost = (float)SupplierBill::where('project_id', $projectId)->sum('grand_total');
                $dirCostNew = (float)DB::table('project_expenses')->where('project_id', $projectId)->sum('amount');
                $dirCostOld = (float)GeneralTransaction::where('project_id', $projectId)
                    ->where('t_type', 'Payment')
                    ->where('note', 'not like', '%Purchase%')
                    ->where('note', 'not like', '%Cash Purchase%')
                    ->sum('amount');

                $totalCost = $supCost + $dirCostNew + $dirCostOld;
                $budgetLimit = ($estCost > 0) ? $estCost : (($estVal > 0) ? $estVal : 0);
                $isExceeded = ($budgetLimit > 0 && $totalCost >= $budgetLimit);

                return response()->json([
                    'limit_amount' => number_format($budgetLimit, 2),
                    'total_cost' => number_format($totalCost, 2),
                    'limit_amount_raw' => (float)$budgetLimit,
                    'total_cost_raw' => (float)$totalCost,
                    'is_exceeded' => $isExceeded,
                    'has_limit' => ($budgetLimit > 0)
                ]);
            }
        }

        // Load data for view
        $mainPaymentHeads = AccountHead::where('parent_id', 0)->where('head_type', 'Payment')->orderBy('head_name', 'asc')->get();
        $suppliers = Party::orderBy('party_name', 'asc')->get();
        $loanHeads = AccountHead::where('head_type', 'Receive')->orderBy('head_name', 'asc')->get();
        $projects = Project::orderBy('project_name', 'asc')->get();

        // Recent Transactions query (UNION across Supplier, Project, and General)
        $recentTransactions = DB::select("
            SELECT 'Supplier' as trans_type, sp.id, sp.t_date, sp.voucher_no, sp.amount, pm.method_name, sp.note, 
                   CONCAT('Supplier: ', IF(p.party_name IS NOT NULL, p.party_name, IFNULL(sb.supplier_id, 'Cash Purchase'))) as head_title, sp.approval_status 
            FROM supplier_payments sp 
            LEFT JOIN payment_method pm ON sp.method_id = pm.id
            LEFT JOIN party p ON sp.supplier_id = p.id
            LEFT JOIN supplier_bills sb ON sp.bill_id = sb.id
            
            UNION ALL
            
            SELECT 'Project' as trans_type, pe.id, pe.t_date, pe.voucher_no, pe.amount, pm.method_name, pe.note, 
                   CONCAT('Project: ', IFNULL(pr.project_name, 'Unknown')) as head_title, pe.approval_status 
            FROM project_expenses pe 
            LEFT JOIN payment_method pm ON pe.method_id = pm.id
            LEFT JOIN project pr ON pe.project_id = pr.id
            
            UNION ALL
            
            SELECT 'General' as trans_type, gt.id, gt.t_date, gt.check_no as voucher_no, gt.amount, pm.method_name, gt.note, 
                   IFNULL((SELECT head_name FROM account_heads WHERE id = gt.head_id), 'General') as head_title, gt.approval_status 
            FROM general_transactions gt 
            LEFT JOIN payment_method pm ON gt.method_id = pm.id 
            WHERE gt.t_type='Payment' 
            AND gt.note NOT LIKE '%Purchase%' 
            AND gt.note NOT LIKE '%Cash Purchase%'
            AND gt.note NOT LIKE '%Transfer%'
            AND IFNULL((SELECT head_name FROM account_heads WHERE id = gt.head_id), '') NOT LIKE '%Transfer%'
            
            ORDER BY t_date DESC, CAST(voucher_no AS UNSIGNED) DESC LIMIT 5000
        ");

        return view('payment_vouchers.index', compact('mainPaymentHeads', 'suppliers', 'loanHeads', 'projects', 'recentTransactions'));
    }

    public function create()
    {
        return redirect()->route('payment-vouchers.index');
    }

    public function store(Request $request)
    {
        if ($request->has('ajax_request') || $request->ajax()) {
            return $this->index($request);
        }

        $user = Auth::user();
        $user_role = $user->role ?? 1;
        $approval_status = ($user_role == 1) ? 'Approved' : 'Pending';

        $payment_type = $request->input('payment_type', 'Others');
        $amount = (float)($request->input('amount') ?? $request->input('pay_amount') ?? 0);
        $deposit_category = $request->input('deposit_category');
        $method_id = (int)$request->input('method_id');
        $voucher = $request->input('voucher') ?? $request->input('voucher_no') ?? $request->input('check_no') ?? ('PAY-' . strtoupper(uniqid()));
        $t_date = $request->input('t_date') ?? $request->input('pay_date') ?? date('Y-m-d');
        $note = $request->input('note') ?? $request->input('pay_note') ?? '';

        if (!$method_id && $request->filled('method_name')) {
            $pmFound = PaymentMethod::where('method_name', $request->input('method_name'))->first();
            if ($pmFound) {
                $method_id = $pmFound->id;
            }
        }

        $bank_trans_type = '';
        $ref_no = '';
        $ref_date = null;

        if ($deposit_category == 'Bank') {
            $bank_trans_type = $request->input('bank_trans_type', '');
            $ref_no = $request->input('bank_ref_no', '');
            $ref_date = $request->input('bank_ref_date');
        } elseif ($deposit_category == 'MFS') {
            $bank_trans_type = $request->input('mfs_type', '');
            $ref_no = $request->input('mfs_ref_no', '');
            $ref_date = $request->input('mfs_ref_date');
        }

        DB::beginTransaction();
        try {
            if ($payment_type == 'Supplier') {
                $supplier_id = $request->input('supplier_id') ?? $request->input('supplier_name');
                if (!is_numeric($supplier_id) && !empty($supplier_id)) {
                    $foundSupplier = \App\Models\Party::where('party_name', $supplier_id)->first();
                    if ($foundSupplier) {
                        $supplier_id = $foundSupplier->id;
                    }
                }
                $pay_mode = $request->input('supplier_pay_mode', 'Project');
                $project_id = ($pay_mode == 'Project' && $request->filled('supplier_project_id')) ? (int)$request->input('supplier_project_id') : 0;
                $bill_id = (int)$request->input('supplier_bill_id', 0);

                SupplierPayment::create([
                    'supplier_id' => $supplier_id,
                    'project_id' => $project_id,
                    'bill_id' => $bill_id,
                    'payment_mode' => $pay_mode,
                    'amount' => $amount,
                    'method_id' => $method_id,
                    'deposit_category' => $deposit_category,
                    'bank_trans_type' => $bank_trans_type,
                    'ref_no' => $ref_no,
                    'ref_date' => $ref_date,
                    'voucher_no' => $voucher,
                    't_date' => $t_date,
                    'note' => $note,
                    'approval_status' => $approval_status,
                ]);

            } elseif ($payment_type == 'Project') {
                $project_id = (int)$request->input('expense_project_id');
                $expense_head = $request->input('project_expense_reason');

                DB::table('project_expense_heads')->insertOrIgnore(['head_name' => $expense_head]);

                DB::table('project_expenses')->insert([
                    'project_id' => $project_id,
                    'expense_head' => $expense_head,
                    'amount' => $amount,
                    'method_id' => $method_id,
                    'voucher_no' => $voucher,
                    't_date' => $t_date,
                    'note' => $note,
                    'approval_status' => $approval_status,
                ]);

            } elseif ($payment_type == 'Loan' || str_starts_with($payment_type, 'Others')) {
                $head_id = ($payment_type == 'Loan') ? (int)$request->input('loan_head_id') : (int)$request->input('head_id');
                $loan_action = $request->input('loan_action', '');
                $full_note = ($payment_type == 'Loan') ? "[Loan " . $loan_action . "] " . $note : $note;

                GeneralTransaction::create([
                    't_date' => $t_date,
                    'head_id' => $head_id,
                    'method_id' => $method_id,
                    'trans_type' => $bank_trans_type,
                    'ref_no' => $ref_no,
                    'ref_date' => $ref_date,
                    'amount' => $amount,
                    'payment_category' => $deposit_category,
                    'check_no' => $voucher,
                    'note' => $full_note,
                    't_type' => 'Payment',
                    'approval_status' => $approval_status,
                ]);
            }

            if ($approval_status == 'Approved') {
                $pm = PaymentMethod::find($method_id);
                if ($pm) {
                    $pm->decrement('amount', $amount);
                }
            }

            ActivityLogger::log('Create', "Created payment voucher {$voucher} for amount {$amount}");

            DB::commit();

            $msg = ($approval_status == 'Pending')
                ? "Payment Voucher <b>[$voucher]</b> saved as Pending. Waiting for Admin Approval!"
                : "Payment Voucher <b>[$voucher]</b> saved successfully and Account Balance updated!";

            return redirect()->route('payment-vouchers.index')->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('payment-vouchers.index')->with('error', 'Transaction Failed! ' . $e->getMessage());
        }
    }

    public function show($id, Request $request)
    {
        $type = $request->input('type', 'General');

        $voucher_no = '';
        $t_date = '';
        $paid_to = '';
        $payment_type_label = '';
        $amount = 0;
        $method_name = '';
        $deposit_category = '';
        $ref_info = '';
        $note = '';
        $current_balance_text = '';
        $trans_type_val = '';
        $ref_date_val = '';

        if ($type == 'Supplier') {
            $r = SupplierPayment::with(['party', 'project', 'method'])->findOrFail($id);
            $voucher_no = $r->voucher_no;
            $t_date = $r->t_date;
            $paid_to = "<b>Supplier:</b> " . ($r->party->party_name ?? 'N/A');
            if (!empty($r->project->project_name)) {
                $paid_to .= " <br><small>(Project: " . $r->project->project_name . ")</small>";
            }

            $payment_type_label = 'Supplier Payment';
            $amount = (float)$r->amount;
            $method_name = $r->method->method_name ?? '';
            $deposit_category = $r->deposit_category;
            $ref_info = $r->ref_no;
            $note = $r->note;
            $trans_type_val = $r->bank_trans_type ?? '';
            $ref_date_val = $r->ref_date ?? '';

            $supplierId = $r->supplier_id;
            $tot_bill = (float)SupplierBill::where('supplier_id', $supplierId)->sum('grand_total');
            $tot_paid = (float)SupplierPayment::where('supplier_id', $supplierId)->sum('amount');
            $net_bal = $tot_bill - $tot_paid;

            if ($net_bal > 0) $current_balance_text = "Total Due: ৳ " . number_format($net_bal, 2);
            elseif ($net_bal < 0) $current_balance_text = "Total Advance: ৳ " . number_format(abs($net_bal), 2);
            else $current_balance_text = "Settled (৳ 0.00)";

        } elseif ($type == 'Project') {
            $r = DB::table('project_expenses as pe')
                ->leftJoin('project as pr', 'pe.project_id', '=', 'pr.id')
                ->leftJoin('payment_method as m', 'pe.method_id', '=', 'm.id')
                ->where('pe.id', $id)
                ->select('pe.*', 'pr.project_name', 'm.method_name')
                ->first();

            if ($r) {
                $voucher_no = $r->voucher_no;
                $t_date = $r->t_date;
                $paid_to = "<b>Project:</b> " . ($r->project_name ?? 'N/A') . "<br><small>(Head: " . $r->expense_head . ")</small>";
                $payment_type_label = $r->expense_head;
                $amount = (float)$r->amount;
                $method_name = $r->method_name ?? '';
                $deposit_category = 'N/A';
                $note = $r->note ?? '';
            }

        } else {
            $r = GeneralTransaction::with(['accountHead', 'paymentMethod'])->findOrFail($id);
            $voucher_no = $r->check_no;
            $t_date = $r->t_date;
            $headName = $r->accountHead->head_name ?? $r->head;
            $paid_to = "<b>Account Head:</b> " . $headName;
            $payment_type_label = $headName;
            $amount = (float)$r->amount;
            $method_name = $r->paymentMethod->method_name ?? $r->payment_category;
            $deposit_category = $r->payment_category;
            $ref_info = $r->ref_no;
            $note = $r->note;
        }

        return view('payment_vouchers.show', compact(
            'voucher_no', 't_date', 'paid_to', 'payment_type_label', 'amount',
            'method_name', 'deposit_category', 'ref_info', 'note', 'current_balance_text',
            'trans_type_val', 'ref_date_val', 'type', 'id'
        ));
    }

    public function edit($id, Request $request)
    {
        $edit_type = $request->input('type', 'General');
        $edit_id = (int)$id;
        $data = null;

        if ($edit_type == 'Supplier') {
            $data = DB::table('supplier_payments as sp')
                ->leftJoin('party as p', 'sp.supplier_id', '=', 'p.id')
                ->leftJoin('project as pr', 'sp.project_id', '=', 'pr.id')
                ->leftJoin('supplier_bills as b', 'sp.bill_id', '=', 'b.id')
                ->leftJoin('payment_method as m', 'sp.method_id', '=', 'm.id')
                ->where('sp.id', $edit_id)
                ->select('sp.*', 'p.party_name', 'pr.project_name', 'b.voucher_no as bill_voucher', 'm.method_name', 'm.account_type')
                ->first();
        } elseif ($edit_type == 'Project') {
            $data = DB::table('project_expenses as pe')
                ->leftJoin('project as pr', 'pe.project_id', '=', 'pr.id')
                ->leftJoin('payment_method as m', 'pe.method_id', '=', 'm.id')
                ->where('pe.id', $edit_id)
                ->select('pe.*', 'pr.project_name', 'm.method_name', 'm.account_type')
                ->first();
        } else {
            $data = DB::table('general_transactions as gt')
                ->leftJoin('account_heads as h', 'gt.head_id', '=', 'h.id')
                ->leftJoin('payment_method as m', 'gt.method_id', '=', 'm.id')
                ->where('gt.id', $edit_id)
                ->select('gt.*', 'gt.check_no as voucher_no', 'h.head_name', 'm.method_name', 'm.account_type')
                ->first();
            if ($data && str_contains($data->note ?? '', '[Loan')) {
                $edit_type = 'Loan';
            }
        }

        if (!$data) {
            return redirect()->route('payment-vouchers.index')->with('error', 'Data not found!');
        }

        return view('payment_vouchers.edit', compact('data', 'edit_type', 'edit_id'));
    }

    public function update(Request $request, $id)
    {
        $edit_id = (int)$id;
        $type = $request->input('edit_type', 'General');
        $old_amount = (float)$request->input('old_amount', 0);
        $new_amount = (float)$request->input('new_amount', $request->input('amount', 0));
        $old_voucher = $request->input('old_voucher', '');
        $new_voucher = $request->input('new_voucher', $request->input('voucher_no', $old_voucher));
        $new_date = $request->input('new_date', $request->input('t_date', date('Y-m-d')));
        $new_note = $request->input('new_note', $request->input('note', ''));

        // Duplicate voucher check
        if ($old_voucher !== $new_voucher && !empty($new_voucher)) {
            $q1 = DB::table('supplier_payments')->where('voucher_no', $new_voucher)->where('id', '!=', $edit_id)->exists();
            $q2 = DB::table('project_expenses')->where('voucher_no', $new_voucher)->where('id', '!=', $edit_id)->exists();
            $q3 = DB::table('general_transactions')->where('check_no', $new_voucher)->where('id', '!=', $edit_id)->exists();
            if ($q1 || $q2 || $q3) {
                return redirect()->back()->with('error', "Update Failed! Voucher No [<b>{$new_voucher}</b>] already exists in another transaction.");
            }
        }

        DB::beginTransaction();
        try {
            if ($type == 'Supplier') {
                $sp = SupplierPayment::findOrFail($edit_id);
                if ($sp->approval_status == 'Approved') {
                    $diff = $new_amount - $old_amount;
                    if ($sp->method_id) {
                        PaymentMethod::where('id', $sp->method_id)->decrement('amount', $diff);
                    }
                    if ($sp->bill_id > 0) {
                        SupplierBill::where('id', $sp->bill_id)->increment('paid_amount', $diff);
                        SupplierBill::where('id', $sp->bill_id)->decrement('due_amount', $diff);
                    }
                } else {
                    if ($sp->method_id) {
                        PaymentMethod::where('id', $sp->method_id)->decrement('amount', $new_amount);
                    }
                    if ($sp->bill_id > 0) {
                        SupplierBill::where('id', $sp->bill_id)->increment('paid_amount', $new_amount);
                        SupplierBill::where('id', $sp->bill_id)->decrement('due_amount', $new_amount);
                    }
                }

                $sp->update([
                    'amount' => $new_amount,
                    'note' => $new_note,
                    'voucher_no' => $new_voucher,
                    't_date' => $new_date,
                    'approval_status' => 'Approved',
                ]);
            } elseif ($type == 'Project') {
                $pe = DB::table('project_expenses')->where('id', $edit_id)->first();
                if ($pe) {
                    if ($pe->approval_status == 'Approved') {
                        $diff = $new_amount - $old_amount;
                        if ($pe->method_id) {
                            PaymentMethod::where('id', $pe->method_id)->decrement('amount', $diff);
                        }
                    } else {
                        if ($pe->method_id) {
                            PaymentMethod::where('id', $pe->method_id)->decrement('amount', $new_amount);
                        }
                    }

                    DB::table('project_expenses')->where('id', $edit_id)->update([
                        'amount' => $new_amount,
                        'note' => $new_note,
                        'voucher_no' => $new_voucher,
                        't_date' => $new_date,
                        'approval_status' => 'Approved',
                    ]);
                }
            } else {
                $gt = GeneralTransaction::findOrFail($edit_id);
                if ($gt->approval_status == 'Approved') {
                    $diff = $new_amount - $old_amount;
                    if ($gt->method_id) {
                        PaymentMethod::where('id', $gt->method_id)->decrement('amount', $diff);
                    }
                } else {
                    if ($gt->method_id) {
                        PaymentMethod::where('id', $gt->method_id)->decrement('amount', $new_amount);
                    }
                }

                $gt->update([
                    'amount' => $new_amount,
                    'note' => $new_note,
                    'check_no' => $new_voucher,
                    't_date' => $new_date,
                    'approval_status' => 'Approved',
                ]);
            }

            ActivityLogger::log('Update', "Updated and approved payment voucher {$new_voucher} ({$type})");
            DB::commit();

            return redirect()->route('payment-vouchers.index')->with('success', 'Transaction updated and <b>Approved</b> successfully! Balances adjusted automatically.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update transaction balance: ' . $e->getMessage());
        }
    }

    public function destroy($id, Request $request)
    {
        $type = $request->input('trans_type') ?? $request->input('type') ?? 'General';

        DB::beginTransaction();
        try {
            if ($type == 'Supplier') {
                $sp = SupplierPayment::findOrFail($id);
                if ($sp->approval_status == 'Approved' && $sp->method_id) {
                    $pm = PaymentMethod::find($sp->method_id);
                    if ($pm) $pm->increment('amount', $sp->amount);
                }
                $sp->delete();
            } elseif ($type == 'Project') {
                $pe = DB::table('project_expenses')->where('id', $id)->first();
                if ($pe && $pe->approval_status == 'Approved' && $pe->method_id) {
                    $pm = PaymentMethod::find($pe->method_id);
                    if ($pm) $pm->increment('amount', $pe->amount);
                }
                DB::table('project_expenses')->where('id', $id)->delete();
            } else {
                $tx = GeneralTransaction::findOrFail($id);
                if ($tx->approval_status == 'Approved' && $tx->method_id) {
                    $pm = PaymentMethod::find($tx->method_id);
                    if ($pm) {
                        $pm->increment('amount', $tx->amount);
                    }
                }
                $tx->delete();
            }

            ActivityLogger::log('Delete', "Deleted payment voucher ID {$id} ({$type})");
            DB::commit();

            return redirect()->route('payment-vouchers.index')->with('success', 'Payment voucher deleted and balance adjusted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('payment-vouchers.index')->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }
}
