<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Client;
use App\Models\Party;
use App\Models\Project;
use App\Models\Employee;
use App\Models\PaymentMethod;

class LedgerController extends Controller
{
    // ==========================================
    // 1. CLIENT MASTER LEDGER
    // ==========================================
    public function clientLedger(Request $request)
    {
        $clients = Client::orderBy('company_name', 'asc')->get();
        $client_name = $request->input('client_name', $request->input('company_name', ''));
        $from_date = $request->input('from_date', $request->input('fromDate', date('Y-m-d', strtotime('-1 year'))));
        $to_date = $request->input('to_date', $request->input('toDate', date('Y-m-d')));
        $show_report = false;

        $client_info = null;
        $opening_balance = 0;
        $persion_name = '';
        $contact = '';
        $tot_billed = 0;
        $tot_received = 0;
        $current_balance = 0;
        $status_text = "Settled (No Due)";
        $status_color = "status-settled";
        $status_icon = "fa-check-circle";
        $status_label = "";
        $brought_forward = 0;
        $ledger_data = [];

        if (!empty($client_name)) {
            $show_report = true;
            $client_info = Client::where('company_name', $client_name)->first();

            if ($client_info) {
                $opening_balance = (float)$client_info->opening_balance;
                $persion_name = $client_info->persion_name;
                $contact = $client_info->contact;

                // Identify projects of this client that have a Final Bill
                $final_billed_projects = DB::table('tbl_sales_generate')
                    ->where('company_name', $client_name)
                    ->where('note', 'like', '%[Final Bill]%')
                    ->distinct()
                    ->pluck('project_name')
                    ->toArray();

                // 2. Lifetime Snapshot
                $qb1 = DB::table('tbl_sales_generate')
                    ->where('company_name', $client_name)
                    ->get();
                foreach ($qb1 as $rb1) {
                    $val = ((float)($rb1->net_amount ?? 0) > 0) ? (float)$rb1->net_amount : (float)$rb1->bill_amount;
                    $is_running = str_contains($rb1->note ?? '', '[Running Bill]');
                    if (in_array($rb1->project_name, $final_billed_projects) && $is_running) continue;
                    $tot_billed += $val;
                }

                $tot_received = (float)DB::table('payment_received_project')
                    ->where('company_name', $client_name)
                    ->where('approval_status', 'Approved')
                    ->sum('pay_amount');

                $current_balance = $opening_balance + $tot_billed - $tot_received;

                if ($current_balance > 0) {
                    $status_text = "DUE (ASSET)";
                    $status_color = "status-due";
                    $status_icon = "fa-exclamation-triangle";
                    $status_label = "Dr";
                } elseif ($current_balance < 0) {
                    $status_text = "ADVANCE (LIABILITY)";
                    $status_color = "status-advance";
                    $status_icon = "fa-shield";
                    $status_label = "Cr";
                }

                // 3. Brought Forward (Before from_date)
                $prev_billed = 0;
                $qb2 = DB::table('tbl_sales_generate')
                    ->where('company_name', $client_name)
                    ->where('date', '<', $from_date)
                    ->get();
                foreach ($qb2 as $rb2) {
                    $val = ((float)($rb2->net_amount ?? 0) > 0) ? (float)$rb2->net_amount : (float)$rb2->bill_amount;
                    $is_running = str_contains($rb2->note ?? '', '[Running Bill]');
                    if (in_array($rb2->project_name, $final_billed_projects) && $is_running) continue;
                    $prev_billed += $val;
                }

                $prev_received = (float)DB::table('payment_received_project')
                    ->where('company_name', $client_name)
                    ->where('date', '<', $from_date)
                    ->where('approval_status', 'Approved')
                    ->sum('pay_amount');

                $brought_forward = $opening_balance + $prev_billed - $prev_received;

                // 4. Period Ledger Data
                $q_bills = DB::table('tbl_sales_generate')
                    ->where('company_name', $client_name)
                    ->whereBetween('date', [$from_date, $to_date])
                    ->get();
                foreach ($q_bills as $r) {
                    $val = ((float)($r->net_amount ?? 0) > 0) ? (float)$r->net_amount : (float)$r->bill_amount;
                    if ($val > 0) {
                        $is_running = str_contains($r->note ?? '', '[Running Bill]');
                        $proj = $r->project_name;
                        if (in_array($proj, $final_billed_projects) && $is_running) {
                            $ledger_data[] = [
                                'id' => 'B-' . $r->id, 'date' => $r->date, 'type' => 'Running Bill', 'ref' => 'INV-' . $r->id,
                                'details' => '<div style="color:#999;"><del>Project: ' . $proj . '. ' . $r->note . '</del><br><small class="text-danger"><i class="fa fa-info-circle"></i> Superseded by Final Bill.</small></div>',
                                'debit' => 0, 'not_added' => $val, 'received' => 0
                            ];
                        } else {
                            $bill_type = $is_running ? 'Running Bill' : 'Valid Bill';
                            $ledger_data[] = [
                                'id' => 'B-' . $r->id, 'date' => $r->date, 'type' => $bill_type, 'ref' => 'INV-' . $r->id,
                                'details' => 'Project: ' . $proj . '. ' . $r->note,
                                'debit' => $val, 'not_added' => 0, 'received' => 0
                            ];
                        }
                    }
                }

                $q_pays = DB::table('payment_received_project')
                    ->leftJoin('payment_method', 'payment_received_project.method_id', '=', 'payment_method.id')
                    ->where('payment_received_project.company_name', $client_name)
                    ->whereBetween('payment_received_project.date', [$from_date, $to_date])
                    ->where('payment_received_project.approval_status', 'Approved')
                    ->select('payment_received_project.*', 'payment_method.method_name')
                    ->get();
                foreach ($q_pays as $r) {
                    if ((float)$r->pay_amount > 0) {
                        $ref = !empty($r->check_no) ? $r->check_no : 'RCT-' . $r->id;
                        $method = !empty($r->method_name) ? $r->method_name : ($r->payment_type ?? 'Cash');
                        $ledger_data[] = [
                            'id' => 'P-' . $r->id, 'date' => $r->date, 'type' => 'Payment', 'ref' => $ref,
                            'details' => 'Received via ' . $method . '. ' . $r->note,
                            'debit' => 0, 'not_added' => 0, 'received' => (float)$r->pay_amount
                        ];
                    }
                }

                usort($ledger_data, function ($a, $b) {
                    $timeA = strtotime($a['date']);
                    $timeB = strtotime($b['date']);
                    if ($timeA == $timeB) return strcmp($a['id'], $b['id']);
                    return $timeA - $timeB;
                });
            }
        }

        return view('ledgers.client', compact(
            'clients', 'client_name', 'from_date', 'to_date', 'show_report',
            'client_info', 'opening_balance', 'persion_name', 'contact',
            'tot_billed', 'tot_received', 'current_balance',
            'status_text', 'status_color', 'status_icon', 'status_label',
            'brought_forward', 'ledger_data'
        ));
    }

    public function clientLedgerPrint(Request $request)
    {
        $client_name = $request->input('client', $request->input('client_name', ''));
        $from_date = $request->input('from', $request->input('from_date', date('Y-m-d', strtotime('-1 year'))));
        $to_date = $request->input('to', $request->input('to_date', date('Y-m-d')));

        $client_info = Client::where('company_name', $client_name)->first();
        if (!$client_info) {
            return "<h2 style='text-align:center; color:red; margin-top:50px;'>Client not found!</h2>";
        }

        $opening_balance = (float)$client_info->opening_balance;
        $persion_name = $client_info->persion_name;
        $contact = $client_info->contact;

        $final_billed_projects = DB::table('tbl_sales_generate')
            ->where('company_name', $client_name)
            ->where('note', 'like', '%[Final Bill]%')
            ->distinct()
            ->pluck('project_name')
            ->toArray();

        $prev_billed = 0;
        $qb2 = DB::table('tbl_sales_generate')
            ->where('company_name', $client_name)
            ->where('date', '<', $from_date)
            ->get();
        foreach ($qb2 as $rb2) {
            $val = ((float)($rb2->net_amount ?? 0) > 0) ? (float)$rb2->net_amount : (float)$rb2->bill_amount;
            $is_running = str_contains($rb2->note ?? '', '[Running Bill]');
            if (in_array($rb2->project_name, $final_billed_projects) && $is_running) continue;
            $prev_billed += $val;
        }

        $prev_received = (float)DB::table('payment_received_project')
            ->where('company_name', $client_name)
            ->where('date', '<', $from_date)
            ->where('approval_status', 'Approved')
            ->sum('pay_amount');

        $brought_forward = $opening_balance + $prev_billed - $prev_received;

        $ledger_data = [];
        $q_bills = DB::table('tbl_sales_generate')
            ->where('company_name', $client_name)
            ->whereBetween('date', [$from_date, $to_date])
            ->get();
        foreach ($q_bills as $r) {
            $val = ((float)($r->net_amount ?? 0) > 0) ? (float)$r->net_amount : (float)$r->bill_amount;
            if ($val > 0) {
                $is_running = str_contains($r->note ?? '', '[Running Bill]');
                $proj = $r->project_name;
                if (in_array($proj, $final_billed_projects) && $is_running) {
                    $ledger_data[] = ['id' => 'B-' . $r->id, 'date' => $r->date, 'type' => 'Running Bill', 'ref' => 'INV-' . $r->id, 'details' => '<del>Project: ' . $proj . '. ' . $r->note . '</del> (Superseded)', 'debit' => 0, 'not_added' => $val, 'received' => 0];
                } else {
                    $bill_type = $is_running ? 'Running Bill' : 'Valid Bill';
                    $ledger_data[] = ['id' => 'B-' . $r->id, 'date' => $r->date, 'type' => $bill_type, 'ref' => 'INV-' . $r->id, 'details' => 'Project: ' . $proj . '. ' . $r->note, 'debit' => $val, 'not_added' => 0, 'received' => 0];
                }
            }
        }

        $q_pays = DB::table('payment_received_project')
            ->leftJoin('payment_method', 'payment_received_project.method_id', '=', 'payment_method.id')
            ->where('payment_received_project.company_name', $client_name)
            ->whereBetween('payment_received_project.date', [$from_date, $to_date])
            ->where('payment_received_project.approval_status', 'Approved')
            ->select('payment_received_project.*', 'payment_method.method_name')
            ->get();
        foreach ($q_pays as $r) {
            if ((float)$r->pay_amount > 0) {
                $ref = !empty($r->check_no) ? $r->check_no : 'RCT-' . $r->id;
                $ledger_data[] = ['id' => 'P-' . $r->id, 'date' => $r->date, 'type' => 'Payment', 'ref' => $ref, 'details' => 'Received via ' . ($r->method_name ?? $r->payment_type ?? 'Cash') . '. ' . $r->note, 'debit' => 0, 'not_added' => 0, 'received' => (float)$r->pay_amount];
            }
        }

        usort($ledger_data, function ($a, $b) {
            $timeA = strtotime($a['date']);
            $timeB = strtotime($b['date']);
            if ($timeA == $timeB) return strcmp($a['id'], $b['id']);
            return $timeA - $timeB;
        });

        return view('ledgers.client_print', compact('client_name', 'from_date', 'to_date', 'client_info', 'persion_name', 'contact', 'brought_forward', 'ledger_data'));
    }

    // ==========================================
    // 2. SUPPLIER MASTER LEDGER
    // ==========================================
    public function supplierLedger(Request $request)
    {
        $suppliers = Party::orderBy('party_name', 'asc')->get();
        $supplier_id = $request->input('supplier_id', $request->input('party_name', ''));
        $from_date = $request->input('from_date', $request->input('fromDate', date('Y-m-d', strtotime('-1 year'))));
        $to_date = $request->input('to_date', $request->input('toDate', date('Y-m-d')));
        $show_report = false;

        $sup_info = null;
        $supplier_name = '';
        $opening_balance = 0;
        $contact = '';
        $address = '';
        $tot_billed = 0;
        $tot_paid = 0;
        $current_balance = 0;
        $status_text = "Settled (No Due)";
        $status_color = "status-settled";
        $status_icon = "fa-check-circle";
        $status_label = "";
        $brought_forward = 0;
        $ledger_data = [];

        if (!empty($supplier_id)) {
            $show_report = true;
            $sup_info = Party::where('id', $supplier_id)->orWhere('party_name', $supplier_id)->first();

            if ($sup_info) {
                $supplier_id = $sup_info->id;
                $supplier_name = $sup_info->party_name;
                $opening_balance = (float)$sup_info->amount;
                $contact = $sup_info->party_phone;
                $address = $sup_info->address;

                // 2. Calculate Lifetime Snapshot
                $tot_billed = (float)DB::table('supplier_bills')
                    ->where('supplier_id', $supplier_id)
                    ->where('approval_status', 'Approved')
                    ->sum('grand_total');

                $tot_paid = (float)DB::table('supplier_payments')
                    ->where('supplier_id', $supplier_id)
                    ->where('approval_status', 'Approved')
                    ->sum('amount');

                $current_balance = $opening_balance + $tot_billed - $tot_paid;

                if ($current_balance > 0) {
                    $status_text = "DUE (LIABILITY)";
                    $status_color = "status-due";
                    $status_icon = "fa-exclamation-triangle";
                    $status_label = "Cr";
                } elseif ($current_balance < 0) {
                    $status_text = "ADVANCE (ASSET)";
                    $status_color = "status-advance";
                    $status_icon = "fa-shield";
                    $status_label = "Dr";
                }

                // 3. Brought Forward (Before from_date)
                $prev_billed = (float)DB::table('supplier_bills')
                    ->where('supplier_id', $supplier_id)
                    ->where('bill_date', '<', $from_date)
                    ->where('approval_status', 'Approved')
                    ->sum('grand_total');

                $prev_paid = (float)DB::table('supplier_payments')
                    ->where('supplier_id', $supplier_id)
                    ->where('t_date', '<', $from_date)
                    ->where('approval_status', 'Approved')
                    ->sum('amount');

                $brought_forward = $opening_balance + $prev_billed - $prev_paid;

                // 4. Period Ledger Data
                $q_bills = DB::table('supplier_bills as b')
                    ->leftJoin('project as p', 'b.project_id', '=', 'p.id')
                    ->where('b.supplier_id', $supplier_id)
                    ->whereBetween('b.bill_date', [$from_date, $to_date])
                    ->where('b.approval_status', 'Approved')
                    ->select('b.*', 'p.project_name')
                    ->get();
                foreach ($q_bills as $r) {
                    if ((float)$r->grand_total > 0) {
                        $project_info = !empty($r->project_name) ? "<b>Project:</b> {$r->project_name}<br>" : "";
                        $details_html = $project_info . $r->note;
                        $ledger_data[] = [
                            'id' => 'B-' . $r->id, 'date' => $r->bill_date, 'type' => 'Voucher', 'ref' => $r->voucher_no,
                            'details' => $details_html, 'debit' => 0, 'credit' => (float)$r->grand_total
                        ];
                    }
                }

                $q_pays = DB::table('supplier_payments as sp')
                    ->leftJoin('project as p', 'sp.project_id', '=', 'p.id')
                    ->where('sp.supplier_id', $supplier_id)
                    ->whereBetween('sp.t_date', [$from_date, $to_date])
                    ->where('sp.approval_status', 'Approved')
                    ->select('sp.*', 'p.project_name')
                    ->get();
                foreach ($q_pays as $r) {
                    if ((float)$r->amount > 0) {
                        $mode_detail = ($r->payment_mode == 'Project') ? 'Project Specific' : 'General Advance';
                        $project_info = !empty($r->project_name) ? "<b>Project:</b> {$r->project_name}<br>" : "";
                        $details_html = $project_info . 'Paid via ' . $r->deposit_category . ' (' . $mode_detail . '). ' . $r->note;
                        $ledger_data[] = [
                            'id' => 'P-' . $r->id, 'date' => $r->t_date, 'type' => 'Payment', 'ref' => $r->voucher_no,
                            'details' => $details_html, 'debit' => (float)$r->amount, 'credit' => 0
                        ];
                    }
                }

                usort($ledger_data, function ($a, $b) {
                    $timeA = strtotime($a['date']);
                    $timeB = strtotime($b['date']);
                    if ($timeA == $timeB) return strcmp($a['id'], $b['id']);
                    return $timeA - $timeB;
                });
            }
        }

        return view('ledgers.supplier', compact(
            'suppliers', 'supplier_id', 'from_date', 'to_date', 'show_report',
            'sup_info', 'supplier_name', 'opening_balance', 'contact', 'address',
            'tot_billed', 'tot_paid', 'current_balance',
            'status_text', 'status_color', 'status_icon', 'status_label',
            'brought_forward', 'ledger_data'
        ));
    }

    public function supplierLedgerPrint(Request $request)
    {
        $supplier_id = (int)$request->input('supplier', $request->input('supplier_id', 0));
        $from_date = $request->input('from', $request->input('from_date', date('Y-m-d', strtotime('-1 year'))));
        $to_date = $request->input('to', $request->input('to_date', date('Y-m-d')));

        $sup_info = Party::find($supplier_id);
        if (!$sup_info) {
            return "<h2 style='text-align:center; color:red; margin-top:50px;'>Supplier not found!</h2>";
        }

        $supplier_name = $sup_info->party_name;
        $opening_balance = (float)$sup_info->amount;
        $contact = $sup_info->party_phone;
        $address = $sup_info->address;

        $prev_billed = (float)DB::table('supplier_bills')
            ->where('supplier_id', $supplier_id)
            ->where('bill_date', '<', $from_date)
            ->where('approval_status', 'Approved')
            ->sum('grand_total');

        $prev_paid = (float)DB::table('supplier_payments')
            ->where('supplier_id', $supplier_id)
            ->where('t_date', '<', $from_date)
            ->where('approval_status', 'Approved')
            ->sum('amount');

        $brought_forward = $opening_balance + $prev_billed - $prev_paid;

        $ledger_data = [];
        $q_bills = DB::table('supplier_bills as b')
            ->leftJoin('project as p', 'b.project_id', '=', 'p.id')
            ->where('b.supplier_id', $supplier_id)
            ->whereBetween('b.bill_date', [$from_date, $to_date])
            ->where('b.approval_status', 'Approved')
            ->select('b.*', 'p.project_name')
            ->get();
        foreach ($q_bills as $r) {
            if ((float)$r->grand_total > 0) {
                $project_info = !empty($r->project_name) ? "<b>Project:</b> {$r->project_name}<br>" : "";
                $details_html = $project_info . ' ' . $r->note;
                $ledger_data[] = ['id' => 'B-' . $r->id, 'date' => $r->bill_date, 'type' => 'Voucher', 'ref' => $r->voucher_no, 'details' => $details_html, 'debit' => 0, 'credit' => (float)$r->grand_total];
            }
        }

        $q_pays = DB::table('supplier_payments as sp')
            ->leftJoin('project as p', 'sp.project_id', '=', 'p.id')
            ->where('sp.supplier_id', $supplier_id)
            ->whereBetween('sp.t_date', [$from_date, $to_date])
            ->where('sp.approval_status', 'Approved')
            ->select('sp.*', 'p.project_name')
            ->get();
        foreach ($q_pays as $r) {
            if ((float)$r->amount > 0) {
                $mode_detail = ($r->payment_mode == 'Project') ? 'Project Specific' : 'General Advance';
                $project_info = !empty($r->project_name) ? "<b>Project:</b> {$r->project_name}<br>" : "";
                $details_html = $project_info . 'Paid via ' . $r->deposit_category . ' (' . $mode_detail . '). ' . $r->note;
                $ledger_data[] = ['id' => 'P-' . $r->id, 'date' => $r->t_date, 'type' => 'Payment', 'ref' => $r->voucher_no, 'details' => $details_html, 'debit' => (float)$r->amount, 'credit' => 0];
            }
        }

        usort($ledger_data, function ($a, $b) {
            $timeA = strtotime($a['date']);
            $timeB = strtotime($b['date']);
            if ($timeA == $timeB) return strcmp($a['id'], $b['id']);
            return $timeA - $timeB;
        });

        return view('ledgers.supplier_print', compact('supplier_id', 'from_date', 'to_date', 'supplier_name', 'opening_balance', 'contact', 'address', 'brought_forward', 'ledger_data'));
    }

    // ==========================================
    // 3. PROJECT MASTER LEDGER
    // ==========================================
    public function projectLedger(Request $request)
    {
        $projects = Project::orderBy('project_name', 'asc')->get();
        $project_id = $request->input('project_id', $request->input('project_name', ''));
        $from_date = $request->input('from_date', $request->input('fromDate', date('Y-m-01')));
        $to_date = $request->input('to_date', $request->input('toDate', date('Y-m-d')));
        $show_report = false;

        $proj_info = null;
        $project_name = '';
        $company_name = '';
        $address = '';
        $est_value = 0;
        $tot_revenue = 0;
        $tot_expense = 0;
        $cash_in = 0;
        $cash_out = 0;
        $client_due = 0;
        $current_balance = 0;
        $status_text = "BREAK EVEN (0.00)";
        $status_color = "status-settled";
        $status_icon = "fa-balance-scale";
        $status_label = "";
        $brought_forward = 0;
        $ledger_data = [];

        if (!empty($project_id)) {
            $show_report = true;
            $proj_info = Project::where('id', $project_id)->orWhere('project_name', $project_id)->first();

            if ($proj_info) {
                $project_id = $proj_info->id;
                $project_name = $proj_info->project_name;
                $company_name = $proj_info->company_name;
                $address = $proj_info->address;
                $est_value = (float)$proj_info->estimated_value;

                $final_bill_exists = DB::table('tbl_sales_generate')
                    ->where('project_name', $project_name)
                    ->where('note', 'like', '%[Final Bill]%')
                    ->exists();

                // 2. Lifetime Snapshot
                $q_rev = DB::table('tbl_sales_generate')->where('project_name', $project_name);
                if ($final_bill_exists) {
                    $q_rev->where('note', 'like', '%[Final Bill]%');
                }
                foreach ($q_rev->get() as $r_rev) {
                    $val = ((float)($r_rev->net_amount ?? 0) > 0) ? (float)$r_rev->net_amount : (float)$r_rev->bill_amount;
                    $tot_revenue += $val;
                }

                $cash_in = (float)DB::table('payment_received_project')
                    ->where('project_name', $project_name)
                    ->sum('pay_amount');

                $sup_cost = (float)DB::table('supplier_bills')
                    ->where('project_id', $project_id)
                    ->sum('grand_total');

                $dir_cost_new = (float)DB::table('project_expenses')
                    ->where('project_id', $project_id)
                    ->sum('amount');

                $dir_cost_old = (float)DB::table('general_transactions')
                    ->where('project_id', $project_id)
                    ->where('t_type', 'Payment')
                    ->where('note', 'not like', '%Purchase%')
                    ->where('note', 'not like', '%Cash Purchase%')
                    ->sum('amount');

                $dir_cost = $dir_cost_new + $dir_cost_old;
                $tot_expense = $sup_cost + $dir_cost;
                $current_balance = $cash_in - $tot_expense;

                if ($current_balance > 0) {
                    $status_text = "SURPLUS / PROFIT";
                    $status_color = "status-profit";
                    $status_icon = "fa-line-chart";
                    $status_label = "Cr";
                } elseif ($current_balance < 0) {
                    $status_text = "DEFICIT / LOSS";
                    $status_color = "status-loss";
                    $status_icon = "fa-level-down";
                    $status_label = "Dr";
                }

                $cash_out_sup = (float)DB::table('supplier_payments')->where('project_id', $project_id)->sum('amount');
                $cash_out = $cash_out_sup + $dir_cost;
                $client_due = $tot_revenue - $cash_in;

                // 3. Brought Forward (Before from_date)
                $prev_recv = (float)DB::table('payment_received_project')
                    ->where('project_name', $project_name)
                    ->where('date', '<', $from_date)
                    ->sum('pay_amount');

                $prev_sup = (float)DB::table('supplier_bills')
                    ->where('project_id', $project_id)
                    ->where('bill_date', '<', $from_date)
                    ->sum('grand_total');

                $prev_exp = (float)DB::table('project_expenses')
                    ->where('project_id', $project_id)
                    ->where('t_date', '<', $from_date)
                    ->sum('amount');

                $prev_exp += (float)DB::table('general_transactions')
                    ->where('project_id', $project_id)
                    ->where('t_type', 'Payment')
                    ->where('t_date', '<', $from_date)
                    ->where('note', 'not like', '%Purchase%')
                    ->where('note', 'not like', '%Cash Purchase%')
                    ->sum('amount');

                $brought_forward = $prev_recv - ($prev_sup + $prev_exp);

                // 4. Period Ledger Data
                // A. Revenue Bills
                $q_bills = DB::table('tbl_sales_generate')
                    ->where('project_name', $project_name)
                    ->whereBetween('date', [$from_date, $to_date])
                    ->get();
                foreach ($q_bills as $r) {
                    $final_val = ((float)($r->net_amount ?? 0) > 0) ? (float)$r->net_amount : (float)$r->bill_amount;
                    if ($final_val > 0) {
                        $is_running_bill_row = str_contains($r->note ?? '', '[Running Bill]');
                        if ($final_bill_exists && $is_running_bill_row) {
                            $ledger_data[] = [
                                'id' => 'REV-' . $r->id, 'date' => $r->date, 'type' => 'Running Bill', 'ref' => 'INV-' . $r->id,
                                'details' => '<div style="color:#999;"><del>Billed to Client. ' . $r->note . '</del><br><small class="text-danger"><i class="fa fa-info-circle"></i> Superseded by Final Bill. (Orig Amt: ৳ ' . number_format($final_val, 2) . ')</small></div>',
                                'debit' => 0, 'credit' => 0, 'received' => 0
                            ];
                        } else {
                            $ledger_data[] = [
                                'id' => 'REV-' . $r->id, 'date' => $r->date, 'type' => 'Project Revenue', 'ref' => 'INV-' . $r->id,
                                'details' => 'Billed to Client. ' . $r->note,
                                'debit' => 0, 'credit' => $final_val, 'received' => 0
                            ];
                        }
                    }
                }

                // B. Supplier Bills
                $q_sup_bills = DB::table('supplier_bills as s')
                    ->leftJoin('party as p', 's.supplier_id', '=', 'p.id')
                    ->where('s.project_id', $project_id)
                    ->whereBetween('s.bill_date', [$from_date, $to_date])
                    ->select('s.*', 'p.party_name')
                    ->get();
                foreach ($q_sup_bills as $r) {
                    if ((float)$r->grand_total > 0) {
                        $sup_name = "Cash Purchase / Other";
                        if ($r->supplier_id != 'Cash Purchase' && !empty($r->party_name)) {
                            $sup_name = htmlspecialchars($r->party_name);
                        } elseif ($r->supplier_id == 'Cash Purchase') {
                            $sup_name = "Cash Purchase";
                        }
                        $details_text = "Supplier Bill (<b>" . $sup_name . "</b>). " . $r->note;
                        $ledger_data[] = [
                            'id' => 'SUP-' . $r->id, 'date' => $r->bill_date, 'type' => 'Material Cost', 'ref' => $r->voucher_no,
                            'details' => $details_text, 'debit' => (float)$r->grand_total, 'credit' => 0, 'received' => 0
                        ];
                    }
                }

                // C. Direct Expenses New
                $q_dir_exp = DB::table('project_expenses')
                    ->where('project_id', $project_id)
                    ->whereBetween('t_date', [$from_date, $to_date])
                    ->get();
                foreach ($q_dir_exp as $r) {
                    if ((float)$r->amount > 0) {
                        $ledger_data[] = [
                            'id' => 'EXP-N-' . $r->id, 'date' => $r->t_date, 'type' => 'Direct Expense', 'ref' => $r->voucher_no,
                            'details' => 'Head: ' . $r->expense_head . '. ' . $r->note,
                            'debit' => (float)$r->amount, 'credit' => 0, 'received' => 0
                        ];
                    }
                }

                // D. Direct Expenses Old
                $q_dir_old = DB::table('general_transactions')
                    ->where('project_id', $project_id)
                    ->where('t_type', 'Payment')
                    ->whereBetween('t_date', [$from_date, $to_date])
                    ->where('note', 'not like', '%Purchase%')
                    ->where('note', 'not like', '%Cash Purchase%')
                    ->get();
                foreach ($q_dir_old as $r) {
                    if ((float)$r->amount > 0) {
                        $ledger_data[] = [
                            'id' => 'EXP-O-' . $r->id, 'date' => $r->t_date, 'type' => 'Direct Expense', 'ref' => !empty($r->check_no) ? $r->check_no : 'VCH-' . $r->id,
                            'details' => $r->note, 'debit' => (float)$r->amount, 'credit' => 0, 'received' => 0
                        ];
                    }
                }

                // E. Payment Received
                $q_receipts = DB::table('payment_received_project')
                    ->where('project_name', $project_name)
                    ->whereBetween('date', [$from_date, $to_date])
                    ->get();
                foreach ($q_receipts as $r) {
                    if ((float)$r->pay_amount > 0) {
                        $b_id = (int)$r->bill_id;
                        $p_name = htmlspecialchars($r->project_name);
                        $method_name = $r->method_name ?? 'Cash';
                        if ($b_id > 0) {
                            $recv_desc = "Payment Received via <b>" . $method_name . "</b>.<br><span class='text-primary'><i class='fa fa-check-circle'></i> Payment against <b>Bill #" . str_pad($b_id, 5, '0', STR_PAD_LEFT) . "</b> (Project: <b>" . $p_name . "</b>)</span>";
                        } else {
                            $recv_desc = "Payment Received via <b>" . $method_name . "</b>.<br><span class='text-warning'><i class='fa fa-star'></i> <b>Advance / Partial payment</b> for whole project: <b>" . $p_name . "</b></span>";
                        }
                        if (!empty($r->note)) {
                            $recv_desc .= "<br><small class='text-muted'>Note: " . $r->note . "</small>";
                        }
                        $ledger_data[] = [
                            'id' => 'REC-' . $r->id, 'date' => $r->date, 'type' => 'Fund Received', 'ref' => !empty($r->check_no) ? $r->check_no : 'REC-' . $r->id,
                            'details' => $recv_desc, 'debit' => 0, 'credit' => 0, 'received' => (float)$r->pay_amount
                        ];
                    }
                }

                usort($ledger_data, function ($a, $b) {
                    $timeA = strtotime($a['date']);
                    $timeB = strtotime($b['date']);
                    if ($timeA == $timeB) return strcmp($a['id'], $b['id']);
                    return $timeA - $timeB;
                });
            }
        }

        return view('ledgers.project', compact(
            'projects', 'project_id', 'from_date', 'to_date', 'show_report',
            'proj_info', 'project_name', 'company_name', 'address', 'est_value',
            'tot_revenue', 'tot_expense', 'cash_in', 'cash_out', 'client_due', 'current_balance',
            'status_text', 'status_color', 'status_icon', 'status_label',
            'brought_forward', 'ledger_data'
        ));
    }

    // ==========================================
    // 4. LOAN MASTER LEDGER
    // ==========================================
    public function loanLedger(Request $request)
    {
        $loanAccounts = DB::table('account_heads')
            ->where('head_type', 'Receive')
            ->orderBy('head_name', 'asc')
            ->get();

        $loan_head_id = $request->input('loan_head_id', '');
        $from_date = $request->input('from_date', $request->input('fromDate', date('Y-m-01')));
        $to_date = $request->input('to_date', $request->input('toDate', date('Y-m-d')));
        $show_report = false;

        $loan_name = '';
        $tot_received = 0;
        $tot_principal = 0;
        $tot_interest = 0;
        $current_balance = 0;
        $status_text = "SETTLED (NO DUE)";
        $status_color = "status-settled";
        $status_icon = "fa-check-circle";
        $status_label = "";
        $brought_forward = 0;
        $ledger_data = [];

        if (!empty($loan_head_id)) {
            $show_report = true;
            $head_info = DB::table('account_heads')->where('id', $loan_head_id)->first();
            $loan_name = $head_info ? $head_info->head_name : 'Loan Account';

            // 2. Lifetime Snapshot
            $tot_received = (float)DB::table('general_transactions')
                ->where('head_id', $loan_head_id)
                ->where('t_type', 'Receive')
                ->sum('amount');

            $tot_principal = (float)DB::table('general_transactions')
                ->where('head_id', $loan_head_id)
                ->where('t_type', 'Payment')
                ->where(function ($q) {
                    $q->where('note', 'like', '%Principal%')->orWhere('note', 'like', '%Adjustment%');
                })
                ->sum('amount');

            $tot_interest = (float)DB::table('general_transactions')
                ->where('head_id', $loan_head_id)
                ->where('t_type', 'Payment')
                ->where('note', 'like', '%Interest%')
                ->sum('amount');

            $current_balance = $tot_received - $tot_principal;

            if ($current_balance > 0) {
                $status_text = "UNPAID LOAN (LIABILITY)";
                $status_color = "status-due";
                $status_icon = "fa-exclamation-triangle";
                $status_label = "Cr";
            } elseif ($current_balance < 0) {
                $status_text = "LOAN GIVEN (ASSET)";
                $status_color = "status-advance";
                $status_icon = "fa-shield";
                $status_label = "Dr";
            }

            // 3. Brought Forward (Before from_date)
            $prev_rec = (float)DB::table('general_transactions')
                ->where('head_id', $loan_head_id)
                ->where('t_type', 'Receive')
                ->where('t_date', '<', $from_date)
                ->sum('amount');

            $prev_prin = (float)DB::table('general_transactions')
                ->where('head_id', $loan_head_id)
                ->where('t_type', 'Payment')
                ->where(function ($q) {
                    $q->where('note', 'like', '%Principal%')->orWhere('note', 'like', '%Adjustment%');
                })
                ->where('t_date', '<', $from_date)
                ->sum('amount');

            $brought_forward = $prev_rec - $prev_prin;

            // 4. Period Ledger Data
            $q_trans = DB::table('general_transactions as gt')
                ->leftJoin('payment_method as pm', 'gt.method_id', '=', 'pm.id')
                ->where('gt.head_id', $loan_head_id)
                ->whereBetween('gt.t_date', [$from_date, $to_date])
                ->select('gt.*', 'pm.method_name')
                ->orderBy('gt.t_date', 'asc')
                ->orderBy('gt.id', 'asc')
                ->get();

            foreach ($q_trans as $r) {
                if ((float)$r->amount > 0) {
                    $ref = !empty($r->check_no) ? $r->check_no : 'VCH-' . $r->id;
                    $method_parts = [];
                    if (!empty($r->payment_category)) $method_parts[] = "<b>" . $r->payment_category . "</b>";
                    if (!empty($r->method_name)) $method_parts[] = $r->method_name;
                    if (!empty($r->trans_type)) $method_parts[] = $r->trans_type;
                    $method_info_str = !empty($method_parts) ? implode(" - ", $method_parts) : "N/A";

                    $trx_info = "";
                    if (!empty($r->ref_no)) {
                        $trx_info = " | <b>Trx/Check:</b> " . $r->ref_no;
                        if (!empty($r->ref_date) && $r->ref_date != '0000-00-00' && $r->ref_date != '1970-01-01') {
                            $trx_info .= " (Dt: " . date('d-M-Y', strtotime($r->ref_date)) . ")";
                        }
                    }

                    $voucher_info = " | <b>Voucher:</b> " . $ref . " (Dt: " . date('d-M-Y', strtotime($r->t_date)) . ")";
                    $note_html = !empty($r->note) ? "<br><span style='color:#555;'><i class='fa fa-info-circle'></i> Note: " . $r->note . "</span>" : "";
                    $full_details = $method_info_str . $trx_info . $voucher_info . $note_html;

                    if ($r->t_type == 'Receive') {
                        $ledger_data[] = [
                            'id' => $r->id, 'date' => $r->t_date, 'type' => 'Loan Received', 'ref' => $ref,
                            'details' => 'Received via ' . $full_details,
                            'principal_dr' => 0, 'interest_dr' => 0, 'credit' => (float)$r->amount
                        ];
                    } else {
                        $is_interest = (stripos($r->note ?? '', 'Interest') !== false);
                        if ($is_interest) {
                            $ledger_data[] = [
                                'id' => $r->id, 'date' => $r->t_date, 'type' => 'Interest Paid', 'ref' => $ref,
                                'details' => 'Paid via ' . $full_details,
                                'principal_dr' => 0, 'interest_dr' => (float)$r->amount, 'credit' => 0
                            ];
                        } else {
                            $ledger_data[] = [
                                'id' => $r->id, 'date' => $r->t_date, 'type' => 'Principal Repaid', 'ref' => $ref,
                                'details' => 'Paid via ' . $full_details,
                                'principal_dr' => (float)$r->amount, 'interest_dr' => 0, 'credit' => 0
                            ];
                        }
                    }
                }
            }
        }

        return view('ledgers.loan', compact(
            'loanAccounts', 'loan_head_id', 'from_date', 'to_date', 'show_report',
            'loan_name', 'tot_received', 'tot_principal', 'tot_interest', 'current_balance',
            'status_text', 'status_color', 'status_icon', 'status_label',
            'brought_forward', 'ledger_data'
        ));
    }

    // ==========================================
    // 5. OFFICE EXPENSE LEDGER
    // ==========================================
    public function officeExpenseLedger(Request $request)
    {
        $main_head = DB::table('account_heads')->where('head_name', 'Office Expense')->first();
        $main_head_id = $main_head ? $main_head->id : 38;

        $expenseHeads = DB::table('account_heads')
            ->where('parent_id', $main_head_id)
            ->orWhere('id', $main_head_id)
            ->orderBy('head_name', 'asc')
            ->get();

        $selected_head_id = $request->input('head_id', 'all');
        $from_date = $request->input('from_date', $request->input('fromDate', date('Y-m-01')));
        $to_date = $request->input('to_date', $request->input('toDate', date('Y-m-d')));
        $show_report = false;

        $report_title = "Office Expenses (Consolidated)";
        $ledger_data = [];
        $total_expense = 0;

        if ($request->has('view_ledger') || $request->has('from_date') || $request->has('head_id')) {
            $show_report = true;

            $query = DB::table('general_transactions as gt')
                ->leftJoin('account_heads as h', 'gt.head_id', '=', 'h.id')
                ->leftJoin('payment_method as m', 'gt.method_id', '=', 'm.id')
                ->where('gt.t_type', 'Payment')
                ->whereBetween('gt.t_date', [$from_date, $to_date]);

            if ($selected_head_id == 'all' || $selected_head_id == $main_head_id) {
                $report_title = "Office Expenses (Consolidated)";
                $subHeadIds = DB::table('account_heads')
                    ->where('parent_id', $main_head_id)
                    ->orWhere('id', $main_head_id)
                    ->pluck('id')
                    ->toArray();
                $query->whereIn('gt.head_id', $subHeadIds);
            } else {
                $q_name = DB::table('account_heads')->where('id', $selected_head_id)->first();
                $report_title = ($q_name ? $q_name->head_name : 'Expense') . " (Expense Statement)";
                $query->where('gt.head_id', $selected_head_id);
            }

            $q_trans = $query->select('gt.*', 'h.head_name', 'm.method_name')
                ->orderBy('gt.t_date', 'asc')
                ->orderBy('gt.id', 'asc')
                ->get();

            foreach ($q_trans as $r) {
                $total_expense += (float)$r->amount;
                $method_parts = [];
                if (!empty($r->payment_category)) $method_parts[] = "<b>" . $r->payment_category . "</b>";
                if (!empty($r->method_name)) $method_parts[] = $r->method_name;
                if (!empty($r->trans_type)) $method_parts[] = $r->trans_type;
                $method_info = !empty($method_parts) ? implode(" - ", $method_parts) : "Cash";

                if (!empty($r->ref_no)) {
                    $method_info .= " <br><small>Trx/Check: " . $r->ref_no . "</small>";
                }

                $ledger_data[] = [
                    'date' => $r->t_date,
                    'voucher' => !empty($r->check_no) ? $r->check_no : 'VCH-' . $r->id,
                    'head_name' => $r->head_name ?? 'General Expense',
                    'method_info' => $method_info,
                    'note' => $r->note,
                    'amount' => (float)$r->amount
                ];
            }
        }

        return view('ledgers.office_expense', compact(
            'expenseHeads', 'selected_head_id', 'main_head_id', 'from_date', 'to_date',
            'show_report', 'report_title', 'ledger_data', 'total_expense'
        ));
    }

    // ==========================================
    // 6. ACCOUNT STATEMENT
    // ==========================================
    public function accountStatement(Request $request)
    {
        $methods = PaymentMethod::orderBy('account_type', 'asc')->orderBy('method_name', 'asc')->get();
        $method_id = $request->input('method_id', '');
        $from_date = $request->input('from_date', $request->input('fromDate', date('Y-m-01')));
        $to_date = $request->input('to_date', $request->input('toDate', date('Y-m-d')));
        $show_report = false;

        $method_info = null;
        $method_name = '';
        $account_type = '';
        $brought_forward = 0;
        $ledger_data = [];

        if (!empty($method_id)) {
            $show_report = true;
            $method_info = PaymentMethod::find($method_id);
            $method_name = $method_info ? $method_info->method_name : '';
            $account_type = $method_info ? $method_info->account_type : '';

            $sql_union = "
                SELECT gt.t_date as date, 
                       IF(gt.trans_type = 'Transfer' OR gt.note LIKE '%Balance Transfer%', 'Transfer In', 'Loan Recv') as category, 
                       CONCAT(
                           '<b>', 
                           IF(gt.trans_type = 'Transfer', 
                              CONCAT('Fund Transfer <small class=\"text-success\">(From: ', IFNULL(pm2.method_name, 'Other Account'), ')</small>'), 
                              IFNULL(h.head_name, IF(IFNULL(gt.main_head, '') != '', gt.main_head, 'General Receive'))
                           ), 
                           '</b>',
                           IF(IFNULL(gt.check_no, '') != '', CONCAT('<br><small class=\"text-muted\">Voucher: <b>', gt.check_no, '</b></small>'), ''),
                           IF(IFNULL(gt.ref_no, '') != '', CONCAT('<small class=\"text-muted\"> | Ref: ', gt.ref_no, '</small>'), ''),
                           IF(IFNULL(gt.note, '') != '', CONCAT('<br><small class=\"text-muted\">Note: ', gt.note, '</small>'), '')
                       ) as note, 
                       gt.amount as amt_in, 0 as amt_out, gt.id 
                FROM general_transactions gt 
                LEFT JOIN account_heads h ON gt.head_id = h.id 
                LEFT JOIN general_transactions gt2 ON gt.check_no = gt2.check_no AND gt.t_date = gt2.t_date AND gt.amount = gt2.amount AND gt.t_type != gt2.t_type AND gt.trans_type = 'Transfer'
                LEFT JOIN payment_method pm2 ON gt2.method_id = pm2.id
                WHERE gt.method_id = '$method_id' AND gt.t_type = 'Receive' AND (gt.approval_status = 'Approved' OR gt.approval_status IS NULL)
                
                UNION ALL
                
                SELECT gt.t_date as date, 
                       IF(gt.trans_type = 'Transfer' OR gt.note LIKE '%Balance Transfer%', 'Transfer Out', 
                          IF(gt.note LIKE '%[Loan%' OR h.head_name LIKE '%Loan%', 'Loan Pay', 'Others Out')
                       ) as category, 
                       CONCAT(
                           '<b>', 
                           IF(gt.trans_type = 'Transfer', 
                              CONCAT('Fund Transfer <small class=\"text-danger\">(To: ', IFNULL(pm2.method_name, 'Other Account'), ')</small>'), 
                              IFNULL(h.head_name, IF(IFNULL(gt.main_head, '') != '', gt.main_head, 'General Expense'))
                           ), 
                           '</b>',
                           IF(IFNULL(gt.check_no, '') != '', CONCAT('<br><small class=\"text-muted\">Voucher: <b>', gt.check_no, '</b></small>'), ''),
                           IF(IFNULL(gt.ref_no, '') != '', CONCAT('<small class=\"text-muted\"> | Ref: ', gt.ref_no, '</small>'), ''),
                           IF(IFNULL(gt.note, '') != '', CONCAT('<br><small class=\"text-muted\">Note: ', gt.note, '</small>'), '')
                       ) as note, 
                       0 as amt_in, gt.amount as amt_out, gt.id 
                FROM general_transactions gt 
                LEFT JOIN account_heads h ON gt.head_id = h.id 
                LEFT JOIN general_transactions gt2 ON gt.check_no = gt2.check_no AND gt.t_date = gt2.t_date AND gt.amount = gt2.amount AND gt.t_type != gt2.t_type AND gt.trans_type = 'Transfer'
                LEFT JOIN payment_method pm2 ON gt2.method_id = pm2.id
                WHERE gt.method_id = '$method_id' AND gt.t_type = 'Payment' AND (gt.approval_status = 'Approved' OR gt.approval_status IS NULL)
                
                UNION ALL
                
                SELECT date as date, 
                       'Client' as category, 
                       CONCAT(
                           '<b>From: ', IFNULL(company_name, 'Unknown'), '</b>',
                           IF(IFNULL(project_name, '') != '', CONCAT(' <small>(Project: ', project_name, ')</small>'), ''),
                           IF(IFNULL(check_no, '') != '', CONCAT('<br><small class=\"text-muted\">Voucher: <b>', check_no, '</b></small>'), ''),
                           IF(IFNULL(ref_no, '') != '', CONCAT('<small class=\"text-muted\"> | Ref: ', ref_no, '</small>'), ''),
                           IF(IFNULL(note, '') != '', CONCAT('<br><small class=\"text-muted\">Note: ', note, '</small>'), '')
                       ) as note, 
                       pay_amount as amt_in, 0 as amt_out, id 
                FROM payment_received_project 
                WHERE method_id = '$method_id' AND CAST(pay_amount AS DECIMAL(15,2)) > 0 AND (approval_status = 'Approved' OR approval_status IS NULL)
                
                UNION ALL
                
                SELECT sp.t_date as date, 
                       'Supplier' as category, 
                       CONCAT(
                           IF(p.party_name IS NOT NULL, CONCAT('<b>To: ', p.party_name, '</b>'), CONCAT('<b>', IFNULL(sb.supplier_id, 'Cash Purchase'), '</b>')),
                           IF(IFNULL(sp.voucher_no, '') != '', CONCAT('<br><small class=\"text-muted\">Voucher: <b>', sp.voucher_no, '</b></small>'), ''),
                           IF(IFNULL(sp.ref_no, '') != '', CONCAT('<small class=\"text-muted\"> | Ref: ', sp.ref_no, '</small>'), ''),
                           IF(IFNULL(sp.note, '') != '', CONCAT('<br><small class=\"text-muted\">Note: ', sp.note, '</small>'), '')
                       ) as note, 
                       0 as amt_in, sp.amount as amt_out, sp.id 
                FROM supplier_payments sp 
                LEFT JOIN party p ON sp.supplier_id = p.id 
                LEFT JOIN supplier_bills sb ON sp.bill_id = sb.id
                WHERE sp.method_id = '$method_id' AND (sp.approval_status = 'Approved' OR sp.approval_status IS NULL)
                
                UNION ALL
                
                SELECT pe.t_date as date, 
                       'Project Exp' as category, 
                       CONCAT(
                           '<b>Project: ', IFNULL(pr.project_name, 'Unknown'), '</b> <small>(Head: ', IFNULL(pe.expense_head, ''), ')</small>',
                           IF(IFNULL(pe.voucher_no, '') != '', CONCAT('<br><small class=\"text-muted\">Voucher: <b>', pe.voucher_no, '</b></small>'), ''),
                           IF(IFNULL(pe.note, '') != '', CONCAT('<br><small class=\"text-muted\">Note: ', pe.note, '</small>'), '')
                       ) as note, 
                       0 as amt_in, pe.amount as amt_out, pe.id 
                FROM project_expenses pe 
                LEFT JOIN project pr ON pe.project_id = pr.id 
                WHERE pe.method_id = '$method_id' AND (pe.approval_status = 'Approved' OR pe.approval_status IS NULL)
            ";

            // 3. Brought Forward (Opening) Calculation
            if ($method_id == 1) {
                // Special forward rule for Cash on Hand (ID: 1)
                $base_opening = 39415.00; // Base opening balance from 2026-06-01
                $r_opening = DB::selectOne("SELECT SUM(amt_in) as total_in, SUM(amt_out) as total_out FROM ($sql_union) as combined WHERE date >= '2026-06-01' AND date < ?", [$from_date]);
                $brought_forward = $base_opening + (float)($r_opening->total_in ?? 0) - (float)($r_opening->total_out ?? 0);
            } else {
                $r_opening = DB::selectOne("SELECT SUM(amt_in) as total_in, SUM(amt_out) as total_out FROM ($sql_union) as combined WHERE date < ?", [$from_date]);
                $brought_forward = (float)($r_opening->total_in ?? 0) - (float)($r_opening->total_out ?? 0);
            }

            // 4. Statement Period Ledger Data
            $ledger_data = DB::select("SELECT * FROM ($sql_union) as combined WHERE date BETWEEN ? AND ? ORDER BY date ASC, id ASC", [$from_date, $to_date]);
        }

        return view('ledgers.account_statement', compact(
            'methods', 'method_id', 'from_date', 'to_date', 'show_report',
            'method_info', 'method_name', 'account_type', 'brought_forward', 'ledger_data'
        ));
    }

    // ==========================================
    // 7. RECEIPTS & PAYMENTS REPORT
    // ==========================================
    public function receiptsPaymentsReport(Request $request)
    {
        $from_date = $request->input('from_date', $request->input('fromDate', date('Y-m-01')));
        $to_date = $request->input('to_date', $request->input('toDate', date('Y-m-d')));
        $show_report = false;

        $cash_in_hand = 0;
        $cash_at_bank = 0;
        $cash_at_mfs = 0;
        $balance_details = ['Cash' => [], 'Bank' => [], 'MFS' => []];
        $total_current_balance = 0;
        $receipts = [];
        $total_receipts = 0;
        $payments = [];
        $total_payments = 0;
        $net_cash_flow = 0;

        if ($request->has('view_report') || $request->has('from_date') || $request->has('fromDate')) {
            $show_report = true;

            // 1. Current Available Balances (Liquidity)
            $bal_methods = DB::table('payment_method')->where('amount', '>', 0)->orderBy('account_type')->orderBy('method_name')->get();
            foreach ($bal_methods as $bal_r) {
                $amt = (float)$bal_r->amount;
                $type = $bal_r->account_type;
                if ($type == 'Cash') {
                    $cash_in_hand += $amt;
                    $balance_details['Cash'][] = $bal_r;
                } elseif ($type == 'Bank') {
                    $cash_at_bank += $amt;
                    $balance_details['Bank'][] = $bal_r;
                } elseif ($type == 'MFS') {
                    $cash_at_mfs += $amt;
                    $balance_details['MFS'][] = $bal_r;
                }
            }
            $total_current_balance = $cash_in_hand + $cash_at_bank + $cash_at_mfs;

            // 2. Fetch Receipts (Cash Inflows)
            $client_tot = (float)DB::table('payment_received_project')
                ->whereBetween('date', [$from_date, $to_date])
                ->sum('pay_amount');
            if ($client_tot > 0) {
                $receipts['Client Collections (Revenue)'] = $client_tot;
                $total_receipts += $client_tot;
            }

            $q_gen_rec = DB::table('general_transactions as gt')
                ->leftJoin('account_heads as ah', 'gt.head_id', '=', 'ah.id')
                ->where('gt.t_type', 'Receive')
                ->whereBetween('gt.t_date', [$from_date, $to_date])
                ->groupBy('gt.head_id', 'gt.trans_type', 'ah.head_name')
                ->selectRaw("gt.head_id, gt.trans_type, ah.head_name, SUM(CAST(gt.amount AS DECIMAL(15,2))) as tot")
                ->get();

            foreach ($q_gen_rec as $r_gen_rec) {
                $amt = (float)$r_gen_rec->tot;
                if ($amt > 0) {
                    if (($r_gen_rec->head_id == 0 || empty($r_gen_rec->head_id)) && $r_gen_rec->trans_type == 'Transfer') {
                        $head = "Internal Balance Transfer (Contra In)";
                    } elseif (!empty($r_gen_rec->head_name)) {
                        $head = "Loan/Other: " . $r_gen_rec->head_name;
                    } else {
                        $head = "Uncategorized Receipts";
                    }
                    $receipts[$head] = ($receipts[$head] ?? 0) + $amt;
                    $total_receipts += $amt;
                }
            }

            // 3. Fetch Payments (Cash Outflows)
            $sup_tot = (float)DB::table('supplier_payments')
                ->whereBetween('t_date', [$from_date, $to_date])
                ->sum('amount');
            if ($sup_tot > 0) {
                $payments['Supplier Payments (Materials/Purchase)'] = $sup_tot;
                $total_payments += $sup_tot;
            }

            $proj_exp_total = (float)DB::table('project_expenses')
                ->whereBetween('t_date', [$from_date, $to_date])
                ->sum('amount');
            $proj_exp_total += (float)DB::table('general_transactions')
                ->where('project_id', '>', 0)
                ->where('t_type', 'Payment')
                ->whereBetween('t_date', [$from_date, $to_date])
                ->sum('amount');

            if ($proj_exp_total > 0) {
                $payments['Project Direct Expenses (Labor, Transport etc.)'] = $proj_exp_total;
                $total_payments += $proj_exp_total;
            }

            $q_gen_pay = DB::table('general_transactions as gt')
                ->leftJoin('account_heads as ah', 'gt.head_id', '=', 'ah.id')
                ->where('gt.t_type', 'Payment')
                ->where(function ($q) {
                    $q->where('gt.project_id', 0)->orWhereNull('gt.project_id');
                })
                ->whereBetween('gt.t_date', [$from_date, $to_date])
                ->groupBy('gt.head_id', 'gt.trans_type', 'ah.head_name')
                ->selectRaw("gt.head_id, gt.trans_type, ah.head_name, SUM(CAST(gt.amount AS DECIMAL(15,2))) as tot")
                ->get();

            foreach ($q_gen_pay as $r_gen_pay) {
                $amt = (float)$r_gen_pay->tot;
                if ($amt > 0) {
                    if (($r_gen_pay->head_id == 0 || empty($r_gen_pay->head_id)) && $r_gen_pay->trans_type == 'Transfer') {
                        $head = "Internal Balance Transfer (Contra Out)";
                    } elseif (!empty($r_gen_pay->head_name)) {
                        $head = $r_gen_pay->head_name;
                    } else {
                        $head = "General / Uncategorized Expense";
                    }
                    $payments[$head] = ($payments[$head] ?? 0) + $amt;
                    $total_payments += $amt;
                }
            }

            $net_cash_flow = $total_receipts - $total_payments;
        }

        return view('ledgers.receipts_payments', compact(
            'from_date', 'to_date', 'show_report',
            'cash_in_hand', 'cash_at_bank', 'cash_at_mfs', 'balance_details', 'total_current_balance',
            'receipts', 'total_receipts', 'payments', 'total_payments', 'net_cash_flow'
        ));
    }

    // ==========================================
    // 8. TOP LEDGER - BALANCE STATEMENT
    // ==========================================
    public function balanceStatement(Request $request)
    {
        $from_date = $request->input('from_date', $request->input('fromDate', '2026-05-01'));
        $to_date = $request->input('to_date', $request->input('toDate', date('Y-m-d')));

        // 1. Cash & Bank Balance
        $cash_bank_balance = (float)DB::table('payment_method')->sum('amount');

        // 2. Client Receivables & Advances
        $total_client_receivable = 0;
        $total_client_advance = 0;
        $clients = Client::all();
        foreach ($clients as $c) {
            $comp = $c->company_name;
            $bal = (float)$c->opening_balance;
            $q_bill = (float)DB::table('tbl_sales_generate')
                ->where('company_name', $comp)
                ->whereBetween('date', [$from_date, $to_date])
                ->sum('bill_amount');
            $bal += $q_bill;

            $q_pay = (float)DB::table('payment_received_project')
                ->where('company_name', $comp)
                ->where('val', 0)
                ->whereBetween('date', [$from_date, $to_date])
                ->sum('pay_amount');
            $bal -= $q_pay;

            if ($bal > 0) $total_client_receivable += $bal;
            elseif ($bal < 0) $total_client_advance += abs($bal);
        }

        // 3. Supplier Payables & Advances
        $total_supplier_payable = 0;
        $total_supplier_advance = 0;
        $parties = Party::all();
        foreach ($parties as $s) {
            $sid = $s->id;
            $bal = (float)$s->amount;
            $q_bill = (float)DB::table('supplier_bills')
                ->where('supplier_id', $sid)
                ->whereBetween('bill_date', [$from_date, $to_date])
                ->sum('grand_total');
            $bal += $q_bill;

            $q_pay = (float)DB::table('supplier_payments')
                ->where('supplier_id', $sid)
                ->whereBetween('t_date', [$from_date, $to_date])
                ->sum('amount');
            $bal -= $q_pay;

            if ($bal > 0) $total_supplier_payable += $bal;
            elseif ($bal < 0) $total_supplier_advance += abs($bal);
        }

        // 4. Loans Taken & Given
        $total_loan_taken = 0;
        $total_loan_given = 0;
        $q_loan = DB::table('account_heads as h')
            ->join('general_transactions as gt', 'h.id', '=', 'gt.head_id')
            ->where('h.head_type', 'Receive')
            ->whereBetween('gt.t_date', [$from_date, $to_date])
            ->groupBy('h.id')
            ->selectRaw("h.id, 
                         SUM(CASE WHEN gt.t_type = 'Receive' THEN gt.amount ELSE 0 END) as recv,
                         SUM(CASE WHEN gt.t_type = 'Payment' AND gt.note NOT LIKE '%Interest%' THEN gt.amount ELSE 0 END) as paid")
            ->get();

        foreach ($q_loan as $l) {
            $net_loan = (float)$l->recv - (float)$l->paid;
            if ($net_loan > 0) $total_loan_taken += $net_loan;
            elseif ($net_loan < 0) $total_loan_given += abs($net_loan);
        }

        // 5. Employee Salary Advance
        $total_emp_advance = (float)DB::table('emp_salary_advance')
            ->where('status', 'Pending')
            ->whereBetween('advance_date', [$from_date, $to_date])
            ->sum('amount');

        // 6. Final Equation
        $total_assets = $cash_bank_balance + $total_client_receivable + $total_supplier_advance + $total_loan_given + $total_emp_advance;
        $total_liabilities = $total_supplier_payable + $total_client_advance + $total_loan_taken;
        $equity = $total_assets - $total_liabilities;
        $liab_and_equity = $total_liabilities + $equity;

        return view('top_ledgers.balance_statement', compact(
            'from_date', 'to_date',
            'cash_bank_balance', 'total_client_receivable', 'total_supplier_advance', 'total_loan_given', 'total_emp_advance', 'total_assets',
            'total_supplier_payable', 'total_client_advance', 'total_loan_taken', 'total_liabilities',
            'equity', 'liab_and_equity'
        ));
    }

    // ==========================================
    // 9. TOP LEDGER - CLIENT SUMMARY
    // ==========================================
    public function clientSummary(Request $request)
    {
        $min_date = DB::table('tbl_sales_generate')->min('date');
        $fromDateSearch = $request->input('fromDate', $request->input('from_date', !empty($min_date) ? date('d-m-Y', strtotime($min_date)) : '01-01-2015'));
        $toDateSearch = $request->input('toDate', $request->input('to_date', date('d-m-Y')));

        $sqlFromDate = date('Y-m-d', strtotime($fromDateSearch));
        $sqlToDate = date('Y-m-d', strtotime($toDateSearch));

        $all_clients_summary = [];
        $total_billed_all = 0;
        $total_received_all = 0;
        $total_balance_all = 0;

        $clients = Client::orderBy('company_name', 'asc')->get();
        foreach ($clients as $client) {
            $c_name = $client->company_name;
            $base_op = (float)$client->opening_balance;

            $final_billed_projs = DB::table('tbl_sales_generate')
                ->where('company_name', $c_name)
                ->where('note', 'like', '%[Final Bill]%')
                ->distinct()
                ->pluck('project_name')
                ->toArray();

            // Previous (Opening) before sqlFromDate
            $prev_billed = 0;
            $qb_prev = DB::table('tbl_sales_generate')
                ->where('company_name', $c_name)
                ->where('date', '<', $sqlFromDate)
                ->get();
            foreach ($qb_prev as $rbp) {
                $val = ((float)($rbp->net_amount ?? 0) > 0) ? (float)$rbp->net_amount : (float)$rbp->bill_amount;
                if (in_array($rbp->project_name, $final_billed_projs) && str_contains($rbp->note ?? '', '[Running Bill]')) continue;
                $prev_billed += $val;
            }

            $prev_received = (float)DB::table('payment_received_project')
                ->where('company_name', $c_name)
                ->where('date', '<', $sqlFromDate)
                ->sum('pay_amount');

            $opening_balance = $base_op + $prev_billed - $prev_received;

            // Period (Billed & Received)
            $period_billed = 0;
            $qb_period = DB::table('tbl_sales_generate')
                ->where('company_name', $c_name)
                ->whereBetween('date', [$sqlFromDate, $sqlToDate])
                ->get();
            foreach ($qb_period as $rbp) {
                $val = ((float)($rbp->net_amount ?? 0) > 0) ? (float)$rbp->net_amount : (float)$rbp->bill_amount;
                if (in_array($rbp->project_name, $final_billed_projs) && str_contains($rbp->note ?? '', '[Running Bill]')) continue;
                $period_billed += $val;
            }

            $period_received = (float)DB::table('payment_received_project')
                ->where('company_name', $c_name)
                ->whereBetween('date', [$sqlFromDate, $sqlToDate])
                ->sum('pay_amount');

            $closing_balance = $opening_balance + $period_billed - $period_received;

            $all_clients_summary[] = [
                'id' => $client->id,
                'company' => $c_name,
                'address' => $client->address,
                'contact' => $client->contact,
                'opening' => $opening_balance,
                'billed' => $period_billed,
                'received' => $period_received,
                'closing' => $closing_balance
            ];

            $total_billed_all += $period_billed;
            $total_received_all += $period_received;
            $total_balance_all += $closing_balance;
        }

        return view('top_ledgers.client_summary', compact(
            'fromDateSearch', 'toDateSearch', 'sqlFromDate', 'sqlToDate',
            'all_clients_summary', 'total_billed_all', 'total_received_all', 'total_balance_all'
        ));
    }

    // ==========================================
    // 10. TOP LEDGER - SUPPLIER SUMMARY
    // ==========================================
    public function supplierSummary(Request $request)
    {
        $min_date = DB::table('supplier_bills')->min('bill_date');
        $fromDateSearch = $request->input('fromDate', $request->input('from_date', !empty($min_date) ? date('d-m-Y', strtotime($min_date)) : '01-06-2026'));
        $toDateSearch = $request->input('toDate', $request->input('to_date', date('d-m-Y')));

        $sqlFromDate = date('Y-m-d', strtotime($fromDateSearch));
        $sqlToDate = date('Y-m-d', strtotime($toDateSearch));

        $supplier_list = [];
        $gt_billed = 0;
        $gt_paid = 0;
        $gt_closing = 0;

        $parties = Party::orderBy('party_name', 'asc')->get();
        foreach ($parties as $party) {
            $p_name = $party->party_name;
            $sid = $party->id;
            $base_op = (float)$party->amount;

            $past_bill = (float)DB::table('supplier_bills')
                ->where('supplier_id', $sid)
                ->where('bill_date', '<', $sqlFromDate)
                ->sum('grand_total');

            $past_pay = (float)DB::table('supplier_payments')
                ->where('supplier_id', $sid)
                ->where('t_date', '<', $sqlFromDate)
                ->sum('amount');

            $opening_balance = $base_op + $past_bill - $past_pay;

            $period_billed = (float)DB::table('supplier_bills')
                ->where('supplier_id', $sid)
                ->whereBetween('bill_date', [$sqlFromDate, $sqlToDate])
                ->sum('grand_total');

            $period_paid = (float)DB::table('supplier_payments')
                ->where('supplier_id', $sid)
                ->whereBetween('t_date', [$sqlFromDate, $sqlToDate])
                ->sum('amount');

            $closing_balance = $opening_balance + $period_billed - $period_paid;

            if ($opening_balance != 0 || $period_billed != 0 || $period_paid != 0 || $closing_balance != 0) {
                $supplier_list[] = [
                    'id' => $sid,
                    'name' => $p_name,
                    'address' => $party->address,
                    'phone' => $party->party_phone,
                    'opening' => $opening_balance,
                    'billed' => $period_billed,
                    'paid' => $period_paid,
                    'closing' => $closing_balance
                ];

                $gt_billed += $period_billed;
                $gt_paid += $period_paid;
                $gt_closing += $closing_balance;
            }
        }

        return view('top_ledgers.supplier_summary', compact(
            'fromDateSearch', 'toDateSearch', 'sqlFromDate', 'sqlToDate',
            'supplier_list', 'gt_billed', 'gt_paid', 'gt_closing'
        ));
    }

    // ==========================================
    // 11. TOP LEDGER - EMPLOYEE SUMMARY LEDGER
    // ==========================================
    public function employeeLedger(Request $request)
    {
        $min_sal_date = DB::table('tbl_employee_salary')->min('date');
        $min_adv_date = DB::table('emp_salary_advance')->min('advance_date');
        $min_date = min(array_filter([$min_sal_date, $min_adv_date])) ?? null;

        $fromDateSearch = $request->input('fromDate', $request->input('from_date', !empty($min_date) ? date('d-m-Y', strtotime($min_date)) : '01-01-2015'));
        $toDateSearch = $request->input('toDate', $request->input('to_date', date('d-m-Y')));

        $sqlFromDate = date('Y-m-d', strtotime($fromDateSearch));
        $sqlToDate = date('Y-m-d', strtotime($toDateSearch));

        $all_emps_summary = [];
        $total_earned_all = 0;
        $total_paid_all = 0;
        $total_balance_all = 0;

        $employees = Employee::orderBy('firstname', 'asc')->get();
        foreach ($employees as $emp) {
            $emp_id = $emp->id;
            $emp_name = $emp->firstname . ' ' . $emp->lastname;

            $past_earned = 0;
            $past_paid = 0;

            // 1. Past Salaries
            $q_past_sal = DB::table('tbl_employee_salary')
                ->where('employee_id', $emp_id)
                ->where('date', '<', $sqlFromDate)
                ->selectRaw("SUM(IF(pay_month = 'Opening', CAST(due_amount AS DECIMAL(15,2)), CAST(gross_salary AS DECIMAL(15,2)) - CAST(previous_due AS DECIMAL(15,2)) - CAST(fine AS DECIMAL(15,2)) - CAST(other_deduction AS DECIMAL(15,2)))) as p_earned, SUM(CAST(amount AS DECIMAL(15,2))) as p_paid")
                ->first();

            if ($q_past_sal) {
                $past_earned += (float)($q_past_sal->p_earned ?? 0);
                $past_paid += (float)($q_past_sal->p_paid ?? 0);
            }

            // Past non-auto advance
            $q_past_adv = (float)DB::table('emp_salary_advance')
                ->where('employee_id', $emp_id)
                ->where('advance_date', '<', $sqlFromDate)
                ->where('amount', '>', 0)
                ->where(function ($q) {
                    $q->where('note', 'not like', '%Auto-generated Advance%')->orWhereNull('note');
                })
                ->sum('amount');
            $past_paid += $q_past_adv;

            $opening_balance = $past_earned - $past_paid;

            // 2. Period Salaries
            $period_earned = 0;
            $period_paid = 0;

            $q_period_sal = DB::table('tbl_employee_salary')
                ->where('employee_id', $emp_id)
                ->whereBetween('date', [$sqlFromDate, $sqlToDate])
                ->selectRaw("SUM(IF(pay_month = 'Opening', CAST(due_amount AS DECIMAL(15,2)), CAST(gross_salary AS DECIMAL(15,2)) - CAST(previous_due AS DECIMAL(15,2)) - CAST(fine AS DECIMAL(15,2)) - CAST(other_deduction AS DECIMAL(15,2)))) as p_earned, SUM(CAST(amount AS DECIMAL(15,2))) as p_paid")
                ->first();

            if ($q_period_sal) {
                $period_earned += (float)($q_period_sal->p_earned ?? 0);
                $period_paid += (float)($q_period_sal->p_paid ?? 0);
            }

            $q_period_adv = (float)DB::table('emp_salary_advance')
                ->where('employee_id', $emp_id)
                ->whereBetween('advance_date', [$sqlFromDate, $sqlToDate])
                ->where('amount', '>', 0)
                ->where(function ($q) {
                    $q->where('note', 'not like', '%Auto-generated Advance%')->orWhereNull('note');
                })
                ->sum('amount');
            $period_paid += $q_period_adv;

            $closing_balance = $opening_balance + $period_earned - $period_paid;

            $all_emps_summary[] = [
                'id' => $emp_id,
                'name' => $emp_name,
                'position' => $emp->position,
                'contact' => $emp->contact_info,
                'opening' => $opening_balance,
                'earned' => $period_earned,
                'paid' => $period_paid,
                'closing' => $closing_balance
            ];

            $total_earned_all += $period_earned;
            $total_paid_all += $period_paid;
            $total_balance_all += $closing_balance;
        }

        return view('top_ledgers.employee_ledger', compact(
            'fromDateSearch', 'toDateSearch', 'sqlFromDate', 'sqlToDate',
            'all_emps_summary', 'total_earned_all', 'total_paid_all', 'total_balance_all'
        ));
    }
}
