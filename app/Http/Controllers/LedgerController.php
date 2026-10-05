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

        return view('ledgers.supplier', compact('suppliers', 'bills', 'supplierName', 'fromDate', 'toDate'));
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
