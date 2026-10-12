<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentReceivedProject;
use App\Models\PaymentMethod;
use App\Models\AccountHead;
use App\Models\Client;
use App\Models\Project;
use App\Models\SalesGenerate;
use App\Models\GeneralTransaction;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReceiveVoucherController extends Controller
{
    public function index(Request $request)
    {
        // Handle AJAX Requests
        if ($request->has('ajax_request') || $request->ajax()) {
            $ajax_request = $request->input('ajax_request');

            // 1. Get Project List
            if ($ajax_request == 'get_projects') {
                $client = $request->input('client');
                $projects = Project::where('company_name', $client)
                    ->orderBy('project_name', 'asc')
                    ->select('id', 'project_name')
                    ->get();
                return response()->json($projects);
            }

            // 2. Real-time Pending Bills with Advance/Opening Adjustment
            if ($ajax_request == 'get_project_bills') {
                $projectName = $request->input('project_name');
                $project = Project::where('project_name', $projectName)->first();
                $clientName = $project ? $project->company_name : '';

                if (empty($clientName)) {
                    return response()->json([]);
                }

                $client = Client::where('company_name', $clientName)->first();
                $opening = $client ? (float)$client->opening_balance : 0.0;

                $totalPaid = (float)PaymentReceivedProject::where('company_name', $clientName)
                    ->where('val', 0)
                    ->sum('pay_amount');

                $availableFund = $totalPaid - $opening;

                $allBills = SalesGenerate::where('company_name', $clientName)
                    ->orderBy('date', 'asc')
                    ->orderBy('id', 'asc')
                    ->get(['id', 'project_name', 'note', 'bill_amount', 'date']);

                $finalPendingBills = [];

                foreach ($allBills as $bill) {
                    $billAmt = (float)$bill->bill_amount;

                    if ($availableFund >= $billAmt) {
                        $availableFund -= $billAmt;
                        $currentDue = 0;
                    } else {
                        $currentDue = $billAmt - $availableFund;
                        $availableFund = 0;
                    }

                    if ($bill->project_name == $projectName && $currentDue > 0) {
                        $finalPendingBills[] = [
                            'id' => $bill->id,
                            'project_name' => $bill->project_name,
                            'note' => $bill->note,
                            'bill_amount' => $bill->bill_amount,
                            'date' => $bill->date,
                            'due_amount' => number_format($currentDue, 2, '.', ''),
                        ];
                    }
                }

                return response()->json($finalPendingBills);
            }

            // 3. Client Overall Summary
            if ($ajax_request == 'get_client_total_dues') {
                $clientName = $request->input('client_name');
                $client = Client::where('company_name', $clientName)->first();
                $opening = $client ? (float)$client->opening_balance : 0.0;

                $billed = (float)SalesGenerate::where('company_name', $clientName)->sum('bill_amount');
                $received = (float)PaymentReceivedProject::where('company_name', $clientName)->where('val', 0)->sum('pay_amount');

                $net = ($opening + $billed) - $received;
                $status = ($net > 0) ? 'Total Due' : (($net < 0) ? 'Total Advance' : 'Settled');
                $color = ($net > 0) ? 'danger' : (($net < 0) ? 'success' : 'default');

                return response()->json([
                    'amount' => number_format(abs($net), 2),
                    'status' => $status,
                    'color' => $color,
                    'opening' => number_format($opening, 2)
                ]);
            }

            // 4. Get Loan Info (Real-time)
            if ($ajax_request == 'get_loan_info') {
                $headId = (int)$request->input('head_id');
                $totalReceived = (float)GeneralTransaction::where('head_id', $headId)->where('t_type', 'Receive')->sum('amount');
                $totalPaid = (float)GeneralTransaction::where('head_id', $headId)->where('t_type', 'Payment')->sum('amount');

                return response()->json([
                    'total_received' => number_format($totalReceived, 2),
                    'total_paid' => number_format($totalPaid, 2),
                    'due_balance' => number_format($totalReceived - $totalPaid, 2)
                ]);
            }

            // 5. Check Duplicate Voucher
            if ($ajax_request == 'check_voucher') {
                $voucher = $request->input('voucher');
                $q1 = GeneralTransaction::where('check_no', $voucher)->exists();
                $q2 = PaymentReceivedProject::where('check_no', $voucher)->orWhere('ref_no', $voucher)->exists();

                if ($q1 || $q2) {
                    return response()->json(['status' => 'duplicate']);
                }
                return response()->json(['status' => 'unique']);
            }

            // 6. Get Payment Methods
            if ($ajax_request == 'get_payment_methods') {
                $type = $request->input('type');
                $methods = PaymentMethod::where('account_type', $type)->orderBy('method_name', 'asc')->get();
                return response()->json($methods);
            }

            // 7. Get Sub Heads
            if ($ajax_request == 'get_sub_heads') {
                $parent_id = (int)$request->input('parent_id');
                $subHeads = AccountHead::where('parent_id', $parent_id)->where('head_type', 'Receive')->orderBy('head_name', 'asc')->get();
                return response()->json($subHeads);
            }
        }

        // Fetch recent transactions union like legacy
        $generalTx = DB::table('general_transactions as g')
            ->leftJoin('account_heads as ah', 'ah.id', '=', 'g.head_id')
            ->leftJoin('payment_method as pm', 'pm.id', '=', 'g.method_id')
            ->where('g.t_type', 'Receive')
            ->select([
                'g.id',
                'g.t_date as date',
                'g.check_no as voucher',
                DB::raw("CASE WHEN g.trans_type = 'Transfer' THEN 'Fund Transfer' WHEN g.main_head IS NOT NULL AND g.main_head != '' THEN (g.main_head || ' - ' || ah.head_name) ELSE ah.head_name END as source"),
                'pm.method_name as account',
                'g.amount',
                DB::raw("'Others' as type"),
                'g.approval_status'
            ]);

        $projectTx = DB::table('payment_received_project as p')
            ->leftJoin('payment_method as pm', 'pm.id', '=', 'p.method_id')
            ->where('p.val', 0)
            ->where('p.type', 'Receive')
            ->select([
                'p.id',
                'p.date',
                DB::raw("CASE WHEN p.check_no IS NOT NULL AND p.check_no != '' THEN p.check_no ELSE p.name END as voucher"),
                'p.company_name as source',
                'pm.method_name as account',
                'p.pay_amount as amount',
                DB::raw("'Client' as type"),
                'p.approval_status'
            ]);

        $recentTransactions = $generalTx->unionAll($projectTx)
            ->orderBy('date', 'desc')
            ->limit(500)
            ->get();

        $clients = Client::orderBy('company_name', 'asc')->get();
        $loanHeads = AccountHead::where('head_type', 'Receive')->orderBy('head_name', 'asc')->get();

        return view('receive_vouchers.index', compact('recentTransactions', 'clients', 'loanHeads'));
    }

    public function create()
    {
        return redirect()->route('receive-vouchers.index');
    }

    public function store(Request $request)
    {
        if ($request->has('ajax_request') || $request->ajax()) {
            return $this->index($request);
        }

        $user = Auth::user();
        $user_role = $user->role ?? 1;
        $approval_status = ($user_role == 1) ? 'Approved' : 'Pending';

        $voucher = $request->input('voucher') ?? $request->input('voucher_no') ?? $request->input('check_no') ?? ('REC-' . strtoupper(uniqid()));
        $receive_type = $request->input('receive_type', 'Others');
        $t_date = $request->input('t_date') ?? $request->input('date') ?? date('Y-m-d');
        $amount = (float)($request->input('amount') ?? $request->input('pay_amount') ?? 0);
        $method_id = (int)$request->input('method_id');
        $deposit_category = $request->input('deposit_category');
        $note = $request->input('note', '');

        if (!$method_id && $request->filled('method_name')) {
            $pmFound = PaymentMethod::where('method_name', $request->input('method_name'))->first();
            if ($pmFound) {
                $method_id = $pmFound->id;
            }
        }

        $sender_bank = '';
        $trans_type = '';
        $ref_no = '';
        $ref_date = null;

        if ($deposit_category == 'Bank') {
            $sender_bank = $request->input('sender_bank_name', '');
            $trans_type = $request->input('bank_trans_type', '');
            $ref_no = $request->input('bank_ref_no', '');
            $ref_date = $request->input('bank_ref_date');
        } elseif ($deposit_category == 'MFS') {
            $sender_bank = $request->input('mfs_type', '');
            $trans_type = 'Mobile Transfer';
            $ref_no = $request->input('mfs_ref_no', '');
            $ref_date = $request->input('mfs_ref_date');
        } else {
            $trans_type = 'Cash Receive';
        }

        DB::beginTransaction();
        try {
            if ($receive_type == 'Client') {
                $client_name = $request->input('client_name');
                $client_pay_mode = $request->input('client_pay_mode', 'General');
                $project_id = 0;
                $project_name = '';
                $bill_id = (int)$request->input('bill_id', 0);

                if ($client_pay_mode == 'Project' && $request->filled('project_id')) {
                    $project_id = (int)$request->input('project_id');
                    $proj = Project::find($project_id);
                    if ($proj) {
                        $project_name = $proj->project_name;
                    }
                }

                if ($bill_id > 0) {
                    $bill = SalesGenerate::find($bill_id);
                    if ($bill) {
                        $currentDue = (float)$bill->amount;
                        $newDue = $currentDue - $amount;
                        $payStatus = ($newDue <= 0) ? 'Paid' : 'Partial';
                        $bill->update([
                            'amount' => $newDue,
                            'payment_status' => $payStatus,
                        ]);
                    }
                }

                $method = PaymentMethod::find($method_id);
                $method_name = $method ? $method->method_name : '';
                $fund_recev = ($client_pay_mode == 'Project') ? 'Project' : 'General Advance';

                PaymentReceivedProject::create([
                    'company_name' => $client_name,
                    'project_name' => $project_name,
                    'bill_id' => $bill_id,
                    'Source' => 'Received',
                    'fund_recev' => $fund_recev,
                    'name' => 'Voucher: ' . $voucher,
                    'method_name' => $method_name,
                    'bill_amount' => 0,
                    'pay_amount' => $amount,
                    'sender_bank' => $sender_bank,
                    'trans_type' => $trans_type,
                    'ref_no' => $ref_no,
                    'ref_date' => $ref_date,
                    'method_id' => $method_id,
                    'check_no' => $voucher,
                    'check_date' => $t_date,
                    'type' => 'Receive',
                    'date' => $t_date,
                    'note' => $note,
                    'val' => 0,
                    'approval_status' => $approval_status,
                ]);

            } elseif ($receive_type == 'Loan') {
                $head_id = (int)$request->input('loan_head_id');
                $main_head = 'Loan Received';
                $loan_trans_type = 'Loan Received';

                GeneralTransaction::create([
                    't_date' => $t_date,
                    'head_id' => $head_id,
                    'main_head' => $main_head,
                    'method_id' => $method_id,
                    'amount' => $amount,
                    'check_no' => $voucher,
                    'sender_bank' => $sender_bank,
                    'trans_type' => $loan_trans_type,
                    'ref_no' => $ref_no,
                    'ref_date' => $ref_date,
                    'note' => $note,
                    't_type' => 'Receive',
                    'approval_status' => $approval_status,
                ]);
            }

            if ($approval_status == 'Approved') {
                $pm = PaymentMethod::find($method_id);
                if ($pm) {
                    $pm->increment('amount', $amount);
                }
            }

            ActivityLogger::log('Create', "Created receive voucher {$voucher} for amount {$amount}");

            DB::commit();

            $msg = ($approval_status == 'Pending')
                ? "Receive Voucher <b>[$voucher]</b> saved and waiting for Admin Approval!"
                : "Receive Voucher <b>[$voucher]</b> saved and Account Balance updated!";

            return redirect()->route('receive-vouchers.index')->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('receive-vouchers.index')->with('error', 'Transaction Failed! ' . $e->getMessage());
        }
    }

    public function show($id, Request $request)
    {
        $type = $request->input('type', 'Others');

        $voucher_no = '';
        $t_date = '';
        $received_from = '';
        $receive_type_label = '';
        $amount = 0;
        $method_name = '';
        $ref_info = '';
        $note = '';
        $current_balance_text = '';

        if ($type == 'Client') {
            $r = PaymentReceivedProject::with('method')->findOrFail($id);
            $voucher_no = $r->check_no ?: (str_contains($r->name, ': ') ? explode(': ', $r->name)[1] : $r->name);
            $t_date = $r->date;

            $received_from = "<b>Client:</b> " . $r->company_name;
            if (!empty($r->project_name)) {
                $received_from .= " <br><small>(Project: " . $r->project_name . ")</small>";
            }

            if (!empty($r->bill_id)) {
                $received_from .= "<br><span style='display:inline-block; background:#3c8dbc; color:#fff; padding:3px 8px; border-radius:3px; font-size:11px; margin-top:5px; font-weight:normal;'><i class='fa fa-check-square-o'></i> Specific Bill Payment</span>";
            } else {
                $received_from .= "<br><span style='display:inline-block; background:#00a65a; color:#fff; padding:3px 8px; border-radius:3px; font-size:11px; margin-top:5px; font-weight:normal;'><i class='fa fa-th-large'></i> Project Advance / General (Auto-Adjusted)</span>";
            }

            $receive_type_label = 'Client Collection';
            $amount = (float)$r->pay_amount;
            $method_name = $r->method->method_name ?? $r->payment_type;

            $ref_str = [];
            if (!empty($r->sender_bank)) $ref_str[] = "Bank/MFS: " . $r->sender_bank;
            if (!empty($r->ref_no)) $ref_str[] = "TrxID/Ref: " . $r->ref_no;
            if (!empty($r->ref_date) && $r->ref_date != '0000-00-00') $ref_str[] = "Date: " . date('d-M-Y', strtotime($r->ref_date));
            $ref_info = implode(' | ', $ref_str);
            $note = $r->note;

            $client = Client::where('company_name', $r->company_name)->first();
            $opening = $client ? (float)$client->opening_balance : 0;
            $billed = (float)SalesGenerate::where('company_name', $r->company_name)->sum('bill_amount');
            $received = (float)PaymentReceivedProject::where('company_name', $r->company_name)->where('val', 1)->sum('pay_amount');
            $net_bal = ($opening + $billed) - $received;

            if ($net_bal > 0) {
                $current_balance_text = "Total Due: ৳ " . number_format($net_bal, 2);
            } elseif ($net_bal < 0) {
                $current_balance_text = "Total Advance: ৳ " . number_format(abs($net_bal), 2);
            } else {
                $current_balance_text = "Settled (৳ 0.00)";
            }
        } else {
            $r = GeneralTransaction::with(['accountHead', 'paymentMethod'])->findOrFail($id);
            $voucher_no = $r->check_no;
            $t_date = $r->t_date;
            $headName = $r->accountHead->head_name ?? $r->head;
            $received_from = "<b>Source/Head:</b> " . $headName;
            if ($type == 'Loan' || str_contains(strtolower($headName), 'loan')) {
                $receive_type_label = 'Loan Received';
            } else {
                $receive_type_label = 'Other Receive';
            }
            $amount = (float)$r->amount;
            $method_name = $r->paymentMethod->method_name ?? $r->payment_category;

            $ref_str = [];
            if (!empty($r->ref_no)) $ref_str[] = "Ref: " . $r->ref_no;
            $ref_info = implode(' | ', $ref_str);
            $note = $r->note;

            if ($type == 'Loan' || str_contains(strtolower($headName), 'loan')) {
                $headId = $r->head_id;
                $tot_rec = (float)GeneralTransaction::where('head_id', $headId)->where('t_type', 'Receive')->sum('amount');
                $tot_pay = (float)GeneralTransaction::where('head_id', $headId)->where('t_type', 'Payment')->sum('amount');
                $due_bal = $tot_rec - $tot_pay;
                if ($due_bal > 0) {
                    $current_balance_text = "Remaining Loan to Pay: ৳ " . number_format($due_bal, 2);
                } else {
                    $current_balance_text = "Loan Cleared (৳ 0.00)";
                }
            }
        }

        if ($request->has('print') || $request->get('view') == 'print') {
            return view('receive_vouchers.print', compact(
                'voucher_no', 't_date', 'received_from', 'receive_type_label',
                'amount', 'method_name', 'ref_info', 'note', 'current_balance_text', 'type', 'id'
            ));
        }

        return view('receive_vouchers.show', compact(
            'voucher_no', 't_date', 'received_from', 'receive_type_label',
            'amount', 'method_name', 'ref_info', 'note', 'current_balance_text', 'type', 'id'
        ));
    }

    public function edit($id, Request $request)
    {
        $type = $request->input('type', 'Others');

        if ($type == 'Others') {
            $voucher = GeneralTransaction::findOrFail($id);
            $voucherNo = $voucher->check_no;
            $amount = $voucher->amount;
            $date = $voucher->t_date;
            $note = $voucher->note;
            $client_name = '';

            $lineage_ids = [];
            if (!empty($voucher->head_id)) {
                $curr = (int)$voucher->head_id;
                while ($curr > 0) {
                    array_unshift($lineage_ids, $curr);
                    $head = AccountHead::find($curr);
                    $curr = ($head && $head->parent_id) ? (int)$head->parent_id : 0;
                }
            }
        } else {
            $voucher = PaymentReceivedProject::findOrFail($id);
            $voucherNo = $voucher->check_no ?: (str_contains($voucher->name, ': ') ? explode(': ', $voucher->name)[1] : $voucher->name);
            $amount = $voucher->pay_amount;
            $date = $voucher->date;
            $note = $voucher->note;
            $client_name = $voucher->company_name;
            $lineage_ids = [];
        }

        $method = PaymentMethod::find($voucher->method_id);
        $deposit_category = $method ? $method->account_type : 'Cash';

        $paymentMethods = PaymentMethod::where('account_type', $deposit_category)->orderBy('method_name', 'asc')->get();
        $clients = Client::orderBy('company_name', 'asc')->get();

        return view('receive_vouchers.edit', compact(
            'voucher', 'type', 'id', 'voucherNo', 'amount', 'date', 'note',
            'client_name', 'deposit_category', 'lineage_ids', 'paymentMethods', 'clients'
        ));
    }

    public function update(Request $request, $id)
    {
        $type = $request->input('type', 'Others');
        $amount = (float)$request->input('amount', 0);
        $old_amount = (float)$request->input('old_amount', 0);
        $method_id = (int)$request->input('method_id');
        $old_method_id = (int)$request->input('old_method_id');
        $deposit_category = $request->input('deposit_category', 'Cash');
        $t_date = $request->input('t_date', date('Y-m-d'));
        $voucher = $request->input('voucher');
        $base_note = $request->input('note', '');

        $extra_info = "";
        $ref_no = $voucher;

        if ($deposit_category == 'Bank') {
            $sender_bank = $request->input('sender_bank_name', '');
            $trans_type = $request->input('bank_trans_type', '');
            $bank_ref_no = $request->input('bank_ref_no', '');
            if (!empty($bank_ref_no)) $ref_no = $bank_ref_no;
            if (!empty($sender_bank) || !empty($trans_type) || !empty($bank_ref_no)) {
                $extra_info = "[Bank: $sender_bank | Type: $trans_type | Ref: $bank_ref_no]";
            }
        } elseif ($deposit_category == 'MFS') {
            $mfs_type = $request->input('mfs_type', '');
            $mfs_ref_no = $request->input('mfs_ref_no', '');
            if (!empty($mfs_ref_no)) $ref_no = $mfs_ref_no;
            if (!empty($mfs_type) || !empty($mfs_ref_no)) {
                $extra_info = "[MFS: $mfs_type | TrxID: $mfs_ref_no]";
            }
        }

        $final_note = !empty($extra_info) ? ($extra_info . " - " . $base_note) : $base_note;

        DB::beginTransaction();
        try {
            if ($old_method_id) {
                PaymentMethod::where('id', $old_method_id)->decrement('amount', $old_amount);
            }
            if ($method_id) {
                PaymentMethod::where('id', $method_id)->increment('amount', $amount);
            }

            if ($type == 'Others') {
                $head_id = (int)$request->input('head_id');
                $tx = GeneralTransaction::findOrFail($id);
                $tx->update([
                    't_date' => $t_date,
                    'head_id' => $head_id ?: $tx->head_id,
                    'method_id' => $method_id,
                    'amount' => $amount,
                    'check_no' => $ref_no,
                    'note' => $final_note,
                ]);
            } else {
                $client_name = $request->input('client_name');
                $voucher_str = "Voucher: " . $voucher;
                $rec = PaymentReceivedProject::findOrFail($id);
                $rec->update([
                    'date' => $t_date,
                    'company_name' => $client_name ?: $rec->company_name,
                    'name' => $voucher_str,
                    'check_no' => $voucher,
                    'pay_amount' => $amount,
                    'method_id' => $method_id,
                    'note' => $final_note,
                ]);
            }

            ActivityLogger::log('Update', "Updated receive voucher ID {$id} ({$type})");
            DB::commit();

            return redirect()->route('receive-vouchers.index')->with('success', 'Voucher updated and balance adjusted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function destroy($id, Request $request)
    {
        $type = $request->input('type', 'Others');

        DB::beginTransaction();
        try {
            if ($type == 'Client') {
                $rec = PaymentReceivedProject::findOrFail($id);
                if ($rec->approval_status == 'Approved' && $rec->method_id) {
                    $pm = PaymentMethod::find($rec->method_id);
                    if ($pm) {
                        $pm->decrement('amount', $rec->pay_amount);
                    }
                }
                $rec->delete();
            } else {
                $tx = GeneralTransaction::findOrFail($id);
                if ($tx->approval_status == 'Approved' && $tx->method_id) {
                    $pm = PaymentMethod::find($tx->method_id);
                    if ($pm) {
                        $pm->decrement('amount', $tx->amount);
                    }
                }
                $tx->delete();
            }

            ActivityLogger::log('Delete', "Deleted receive voucher ID {$id} ({$type})");
            DB::commit();

            return redirect()->route('receive-vouchers.index')->with('success', 'Voucher deleted and balance adjusted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('receive-vouchers.index')->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }
}
