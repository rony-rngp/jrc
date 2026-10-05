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
    public function clientLedger(Request $request)
    {
        $clients = Client::orderBy('company_name', 'asc')->get();
        $clientName = $request->input('company_name');
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');

        $query = DB::table('payment_received_project');
        if ($clientName) {
            $query->where('company_name', $clientName);
        }
        if ($fromDate && $toDate) {
            $query->whereBetween('date', [$fromDate, $toDate]);
        }
        $transactions = $query->orderBy('date', 'asc')->get();

        return view('ledgers.client', compact('clients', 'transactions', 'clientName', 'fromDate', 'toDate'));
    }

    public function clientLedgerPrint(Request $request)
    {
        $client_name = $request->input('client');
        $from_date = $request->input('from');
        $to_date = $request->input('to');

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
        $prev_received = 0;

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
                    $ledger_data[] = ['id' => 'B-'.$r->id, 'date' => $r->date, 'type' => 'Running Bill', 'ref' => 'INV-'.$r->id, 'details' => '<del>Project: '.$proj.'. '.$r->note.'</del> (Superseded)', 'debit' => 0, 'not_added' => $val, 'received' => 0];
                } else {
                    $bill_type = $is_running ? 'Running Bill' : 'Valid Bill';
                    $ledger_data[] = ['id' => 'B-'.$r->id, 'date' => $r->date, 'type' => $bill_type, 'ref' => 'INV-'.$r->id, 'details' => 'Project: '.$proj.'. '.$r->note, 'debit' => $val, 'not_added' => 0, 'received' => 0];
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
                $ref = !empty($r->check_no) ? $r->check_no : 'RCT-'.$r->id;
                $ledger_data[] = ['id' => 'P-'.$r->id, 'date' => $r->date, 'type' => 'Payment', 'ref' => $ref, 'details' => 'Received via ' . ($r->method_name ?? $r->payment_type) . '. ' . $r->note, 'debit' => 0, 'not_added' => 0, 'received' => (float)$r->pay_amount];
            }
        }

        usort($ledger_data, function ($a, $b) {
            $timeA = strtotime($a['date']); $timeB = strtotime($b['date']);
            if ($timeA == $timeB) return strcmp($a['id'], $b['id']);
            return $timeA - $timeB;
        });

        return view('ledgers.client_print', compact('client_name', 'from_date', 'to_date', 'client_info', 'persion_name', 'contact', 'brought_forward', 'ledger_data'));
    }

    public function supplierLedgerPrint(Request $request)
    {
        $supplier_id = (int)$request->input('supplier');
        $from_date = $request->input('from');
        $to_date = $request->input('to');

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
                $ledger_data[] = ['id' => 'B-'.$r->id, 'date' => $r->bill_date, 'type' => 'Voucher', 'ref' => $r->voucher_no, 'details' => $details_html, 'debit' => 0, 'credit' => (float)$r->grand_total];
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
                $details_html = $project_info . 'Paid via ' . $r->deposit_category . ' ('.$mode_detail.'). ' . $r->note;
                $ledger_data[] = ['id' => 'P-'.$r->id, 'date' => $r->t_date, 'type' => 'Payment', 'ref' => $r->voucher_no, 'details' => $details_html, 'debit' => (float)$r->amount, 'credit' => 0];
            }
        }

        usort($ledger_data, function ($a, $b) {
            $timeA = strtotime($a['date']); $timeB = strtotime($b['date']);
            if ($timeA == $timeB) return strcmp($a['id'], $b['id']);
            return $timeA - $timeB;
        });

        return view('ledgers.supplier_print', compact('supplier_id', 'from_date', 'to_date', 'supplier_name', 'opening_balance', 'contact', 'address', 'brought_forward', 'ledger_data'));
    }

    public function supplierLedger(Request $request)
    {
        $suppliers = Party::orderBy('party_name', 'asc')->get();
        $supplierName = $request->input('party_name');
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');

        $query = DB::table('supplier_bills');
        if ($supplierName) {
            $query->where('supplier_id', $supplierName);
        }
        if ($fromDate && $toDate) {
            $query->whereBetween('bill_date', [$fromDate, $toDate]);
        }
        $bills = $query->orderBy('bill_date', 'asc')->get();

        $selectedSupplier = null;
        if ($supplierName) {
            $selectedSupplier = Party::where('party_name', $supplierName)->orWhere('id', $supplierName)->first();
        }
        $selectedSupplierId = $selectedSupplier ? $selectedSupplier->id : null;

        return view('ledgers.supplier', compact('suppliers', 'bills', 'supplierName', 'selectedSupplierId', 'fromDate', 'toDate'));
    }

    public function projectLedger(Request $request)
    {
        $projects = Project::orderBy('project_name', 'asc')->get();
        $projectName = $request->input('project_name');
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');

        $query = DB::table('payment_received_project');
        if ($projectName) {
            $query->where('project_name', $projectName);
        }
        if ($fromDate && $toDate) {
            $query->whereBetween('date', [$fromDate, $toDate]);
        }
        $records = $query->orderBy('date', 'asc')->get();

        return view('ledgers.project', compact('projects', 'records', 'projectName', 'fromDate', 'toDate'));
    }

    public function loanLedger(Request $request)
    {
        $records = DB::table('investor_loan_info')->orderBy('date', 'desc')->get();
        return view('ledgers.loan', compact('records'));
    }

    public function officeExpenseLedger(Request $request)
    {
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');

        $query = DB::table('ho_payment_status');
        if ($fromDate && $toDate) {
            $query->whereBetween('pay_date', [$fromDate, $toDate]);
        }
        $expenses = $query->orderBy('pay_date', 'desc')->get();

        return view('ledgers.office_expense', compact('expenses', 'fromDate', 'toDate'));
    }

    public function accountStatement(Request $request)
    {
        $methods = PaymentMethod::orderBy('method_name', 'asc')->get();
        $method = $request->input('method_name');
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');

        $received = DB::table('payment_received_info');
        $expenses = DB::table('ho_payment_status');

        if ($method) {
            $received->where('method_name', $method);
            $expenses->where('method_name', $method);
        }
        if ($fromDate && $toDate) {
            $received->whereBetween('date', [$fromDate, $toDate]);
            $expenses->whereBetween('pay_date', [$fromDate, $toDate]);
        }

        $allReceived = $received->get();
        $allExpenses = $expenses->get();

        return view('ledgers.account_statement', compact('methods', 'allReceived', 'allExpenses', 'method', 'fromDate', 'toDate'));
    }

    public function receiptsPaymentsReport(Request $request)
    {
        $fromDate = $request->input('fromDate', date('Y-m-01'));
        $toDate = $request->input('toDate', date('Y-m-d'));

        $totalReceived = DB::table('payment_received_info')->whereBetween('date', [$fromDate, $toDate])->sum('amount');
        $totalExpenses = DB::table('ho_payment_status')->whereBetween('pay_date', [$fromDate, $toDate])->sum('pay_amount');
        $totalSupplierPaid = DB::table('supplier_payments')->whereBetween('t_date', [$fromDate, $toDate])->sum('amount');
        $totalSalaryPaid = DB::table('tbl_employee_salary')->whereBetween('date', [$fromDate, $toDate])->sum('amount');

        return view('ledgers.receipts_payments', compact('fromDate', 'toDate', 'totalReceived', 'totalExpenses', 'totalSupplierPaid', 'totalSalaryPaid'));
    }

    // Top Ledgers
    public function balanceStatement()
    {
        $paymentMethods = PaymentMethod::all();
        $totalReceived = DB::table('payment_received_info')->sum('amount');
        $totalExpenses = DB::table('ho_payment_status')->sum('pay_amount');

        return view('top_ledgers.balance_statement', compact('paymentMethods', 'totalReceived', 'totalExpenses'));
    }

    public function clientSummary()
    {
        $clients = Client::all();
        $summaries = [];
        foreach ($clients as $c) {
            $totalBill = DB::table('payment_received_project')->where('company_name', $c->company_name)->sum('bill_amount');
            $totalPaid = DB::table('payment_received_project')->where('company_name', $c->company_name)->sum('pay_amount');
            $summaries[] = [
                'name' => $c->company_name,
                'phone' => $c->contact,
                'total_bill' => (float)$totalBill,
                'total_paid' => (float)$totalPaid,
                'due' => (float)$totalBill - (float)$totalPaid,
            ];
        }
        return view('top_ledgers.client_summary', compact('summaries'));
    }

    public function supplierSummary()
    {
        $suppliers = Party::all();
        $summaries = [];
        foreach ($suppliers as $s) {
            $totalBill = DB::table('supplier_bills')->where('supplier_id', $s->party_name)->sum('grand_total');
            $totalPaid = DB::table('supplier_payments')->where('supplier_id', $s->id)->orWhere('supplier_id', $s->party_name)->sum('amount');
            $summaries[] = [
                'name' => $s->party_name,
                'phone' => $s->party_phone,
                'total_bill' => (float)$totalBill,
                'total_paid' => (float)$totalPaid,
                'due' => (float)$totalBill - (float)$totalPaid,
            ];
        }
        return view('top_ledgers.supplier_summary', compact('summaries'));
    }

    public function employeeLedger()
    {
        $employees = Employee::all();
        $summaries = [];
        foreach ($employees as $e) {
            $totalPaid = DB::table('tbl_employee_salary')->where('employee_id', $e->id)->sum('amount');
            $totalDue = DB::table('tbl_employee_salary')->where('employee_id', $e->id)->sum('due_amount');
            $summaries[] = [
                'name' => $e->firstname . ' ' . $e->lastname,
                'position' => $e->position,
                'basic' => (float)$e->salary,
                'total_paid' => (float)$totalPaid,
                'total_due' => (float)$totalDue,
            ];
        }
        return view('top_ledgers.employee_ledger', compact('summaries'));
    }
}
