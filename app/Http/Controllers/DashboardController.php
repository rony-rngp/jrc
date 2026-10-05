<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Client;
use App\Models\Party;
use App\Models\Project;
use App\Models\Employee;
use App\Models\PaymentMethod;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = date('Y-m-d');
        $year = $request->input('year', date('Y'));

        // 1. Accounts Snapshot
        $today_rcv = DB::table('payment_received_project')->where('date', $today)->sum('pay_amount')
                   + DB::table('general_transactions')->where('t_type', 'Receive')->where('t_date', $today)->sum('amount');

        $today_pay = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('supplier_bill_pay_status')) {
            $today_pay += DB::table('supplier_bill_pay_status')->where('bill_date', $today)->sum('pay_amount');
        }
        $today_pay += DB::table('tbl_employee_salary')->where('date', $today)->sum('amount');
        $today_pay += DB::table('general_transactions')->where('t_type', 'Payment')->where('t_date', $today)->sum('amount');

        $cash_balance = (float) PaymentMethod::where('account_type', 'Cash')->sum('amount');
        $bank_balance = (float) PaymentMethod::where('account_type', 'Bank')->sum('amount');
        $mfs_balance = (float) PaymentMethod::where('account_type', 'MFS')->sum('amount');
        $total_net = $cash_balance + $bank_balance + $mfs_balance;

        // 2. General Statistics
        $totalSuppliers = Party::count();
        $totalClients = Client::distinct('company_name')->count('company_name');
        $totalProjects = Project::count();
        $totalPaid = DB::table('payment_received_info')->where('type', 'like', '%Payment%')->sum('amount');
        $totalRcv = DB::table('payment_received_info')->where('type', 'like', '%Received%')->sum('amount');
        
        $officeExpense = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('head_office_expense_list')) {
            $officeExpense = DB::table('head_office_expense_list')->sum('total_amount');
        }

        // 3. Attendance Chart
        $months = [];
        $ontime = [];
        $late = [];

        for ($m = 1; $m <= 12; $m++) {
            $ontimeCount = 0;
            $lateCount = 0;
            if (\Illuminate\Support\Facades\Schema::hasTable('attendance')) {
                $ontimeCount = DB::table('attendance')
                    ->whereRaw('MONTH(date) = ?', [$m])
                    ->whereRaw('YEAR(date) = ?', [$year])
                    ->where('status', 1)
                    ->count();

                $lateCount = DB::table('attendance')
                    ->whereRaw('MONTH(date) = ?', [$m])
                    ->whereRaw('YEAR(date) = ?', [$year])
                    ->where('status', 0)
                    ->count();
            }
            $ontime[] = $ontimeCount;
            $late[] = $lateCount;
            $months[] = date('M', mktime(0, 0, 0, $m, 1));
        }

        return view('dashboard', compact(
            'today_rcv',
            'today_pay',
            'cash_balance',
            'bank_balance',
            'mfs_balance',
            'total_net',
            'totalSuppliers',
            'totalClients',
            'totalProjects',
            'totalPaid',
            'totalRcv',
            'officeExpense',
            'months',
            'ontime',
            'late',
            'year'
        ));
    }
}
