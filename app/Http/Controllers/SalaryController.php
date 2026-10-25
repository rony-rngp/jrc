<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\EmpSalaryAdvance;
use App\Models\TblEmployeeSalary;
use App\Models\PaymentMethod;
use App\Models\GeneralTransaction;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SalaryController extends Controller
{
    public function process(Request $request)
    {
        // AJAX Request Handler
        if ($request->has('ajax_request') || $request->ajax()) {
            $ajax_request = $request->input('ajax_request');

            if ($ajax_request == 'get_salary_info') {
                $empId = (int)$request->input('emp_id');
                $month = $request->input('month');

                $checkPaid = TblEmployeeSalary::where('employee_id', $empId)->where('pay_month', $month)->exists();
                if ($checkPaid) {
                    return response()->json(['status' => 'paid']);
                }

                $response = ['status' => 'unpaid'];
                $emp = Employee::find($empId);

                if ($emp) {
                    $response['emp_name'] = $emp->firstname . ' ' . $emp->lastname;
                    $response['salary'] = $emp->salary;
                    $response['mobile_bill'] = $emp->mobile_bill;
                    $response['lunch_bill'] = $emp->lunch_bill;
                    $response['other_allowances'] = $emp->other_allowances;
                }

                // Calculate Previous Due from the latest processed salary
                $lastSalary = TblEmployeeSalary::where('employee_id', $empId)
                    ->orderBy('date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                $previous_due = 0;
                $extra_advance_from_salary = 0;

                if ($lastSalary) {
                    $last_due = (float)$lastSalary->due_amount;
                    if ($last_due > 0) {
                        $previous_due = $last_due;
                    } elseif ($last_due < 0) {
                        $extra_advance_from_salary = abs($last_due);
                    }
                }

                // Check pending advances for this employee up to the processing month
                $pending_adv = (float)DB::table('emp_salary_advance')
                    ->where('employee_id', $empId)
                    ->where('status', 'Pending')
                    ->where(function($q) use ($month) {
                        $q->where('adjust_month', '<=', $month)->orWhereNull('adjust_month');
                    })
                    ->sum('amount');

                // Advance deduct = pending advances + any extra advance from last salary
                $total_adv_deduct = $pending_adv + $extra_advance_from_salary;

                $response['previous_due'] = round($previous_due, 2);
                $response['advance_deduct'] = round($total_adv_deduct, 2);

                return response()->json($response);
            }

            if ($ajax_request == 'get_payment_methods') {
                $type = $request->input('type');
                $methods = PaymentMethod::where('account_type', $type)->orderBy('method_name', 'asc')->get();
                return response()->json($methods);
            }
        }

        $employees = Employee::orderBy('firstname', 'asc')->get();
        $previous_month = date('Y-m', strtotime('first day of last month'));

        return view('salary.process', compact('employees', 'previous_month'));
    }

    public function storeProcess(Request $request)
    {
        if ($request->has('ajax_request') || $request->ajax()) {
            return $this->process($request);
        }

        $employee_id = (int)$request->input('employee_id');
        $emp = Employee::find($employee_id);
        $emp_name = $request->input('emp_name', $emp ? ($emp->firstname . ' ' . $emp->lastname) : '');
        $pay_month = $request->input('pay_month', date('F Y'));
        $pay_date = $request->input('pay_date') ?? $request->input('date') ?? date('Y-m-d');
        $voucher = $request->input('voucher') ?? ('SAL-' . strtoupper(uniqid()));
        $main_head = $request->input('main_head', 'Salary');
        $note = $request->input('note', '');

        $method_id = (int)$request->input('method_id');
        if (!$method_id && $request->filled('method_name')) {
            $pmFound = PaymentMethod::where('method_name', $request->input('method_name'))->first();
            if ($pmFound) {
                $method_id = $pmFound->id;
            }
        }

        $check_month = TblEmployeeSalary::where('employee_id', $employee_id)->where('pay_month', $pay_month)->exists();
        if ($check_month) {
            return redirect()->route('salary.process')->with('error', "Warning: Salary for this month is already paid!");
        }

        $check_voucher = GeneralTransaction::where('check_no', $voucher)->exists();
        if ($check_voucher) {
            return redirect()->route('salary.process')->with('error', "Warning: Voucher No '$voucher' is already used!");
        }

        $main_salary = (float)$request->input('main_salary', $emp ? $emp->salary : 0);
        $mobile_bill = (float)$request->input('mobile_bill', 0);
        $lunch_bill = (float)$request->input('lunch_bill', 0);
        $other_allowances = (float)$request->input('other_allowances', 0);
        $bonus = (float)$request->input('bonus', 0);
        $previous_due = (float)$request->input('previous_due', 0);
        $gross_salary = (float)$request->input('gross_salary', $main_salary + $mobile_bill + $lunch_bill + $other_allowances + $bonus + $previous_due);

        $adv_deduct = (float)$request->input('adv_deduct', 0);
        $fine = (float)$request->input('fine', 0);
        $other_deduction = (float)$request->input('other_deduction', 0);

        $paid_amount = (float)($request->input('paid_amount') ?? $request->input('amount') ?? 0);
        $net_payable = (float)$request->input('net_payable', $gross_salary - $adv_deduct - $fine - $other_deduction);
        $due_amount = $net_payable - $paid_amount;
        $current_due_status = ($due_amount > 0) ? 'Pending' : 'Cleared';

        $deposit_category = $request->input('deposit_category');
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

        $pm = PaymentMethod::find($method_id);
        $method_name = $pm ? $pm->method_name : 'Cash';

        DB::beginTransaction();
        try {
            if ($previous_due > 0) {
                TblEmployeeSalary::where('employee_id', $employee_id)
                    ->where('due_status', 'Pending')
                    ->update(['due_status' => 'Adjusted']);
            }

            TblEmployeeSalary::create([
                'employee_id' => $employee_id,
                'employee_name' => $emp_name,
                'method_name' => $method_name,
                'payment_category' => $deposit_category,
                'trans_type' => $trans_type,
                'trans_ref_no' => $trans_ref_no,
                'trans_date' => $trans_date,
                'voucher_no' => $voucher,
                'main_salary' => $main_salary,
                'mobile_bill' => $mobile_bill,
                'lunch_bill' => $lunch_bill,
                'other_allowances' => $other_allowances,
                'bonus' => $bonus,
                'previous_due' => $previous_due,
                'gross_salary' => $gross_salary,
                'adv_deduct' => $adv_deduct,
                'fine' => $fine,
                'other_deduction' => $other_deduction,
                'net_payable' => $net_payable,
                'amount' => $paid_amount,
                'due_amount' => $due_amount,
                'due_status' => $current_due_status,
                'pay_month' => $pay_month,
                'date' => $pay_date,
                'note' => $note,
                'pay_status' => 'Paid',
            ]);

            if ($pm) {
                $pm->decrement('amount', $paid_amount);
            }

            $extra_info = ($deposit_category != 'Cash') ? " | $deposit_category: $trans_ref_no" : "";
            $full_note = "Salary Paid to $emp_name for " . date("F Y", strtotime($pay_month . '-01')) . " | " . $note . $extra_info;

            GeneralTransaction::create([
                't_date' => $pay_date,
                'head_id' => 10,
                'main_head' => $main_head,
                'method_id' => $method_id,
                'amount' => $paid_amount,
                'note' => $full_note,
                't_type' => 'Payment',
                'check_no' => $voucher,
                'approval_status' => 'Approved',
            ]);

            if ($adv_deduct != 0) {
                DB::table('emp_salary_advance')
                    ->where('employee_id', $employee_id)
                    ->where(function($q) use ($pay_month) {
                        $q->where('adjust_month', '<=', $pay_month)
                          ->orWhereNull('adjust_month');
                    })
                    ->where('status', 'Pending')
                    ->update([
                        'status' => 'Adjusted',
                        'note' => DB::raw("CONCAT(IFNULL(note,''), ' | Adjusted on $pay_date via Voucher: $voucher')")
                    ]);
            }

            if ($paid_amount > $net_payable) {
                $extra_advance = $paid_amount - $net_payable;
                $next_month = date('Y-m', strtotime($pay_month . '-01 +1 month'));
                $adv_note = "Auto-generated Advance (Extra payment in $pay_month salary)";

                EmpSalaryAdvance::create([
                    'employee_id' => $employee_id,
                    'advance_date' => $pay_date,
                    'amount' => $extra_advance,
                    'adjust_month' => $next_month,
                    'voucher_no' => $voucher,
                    'method_id' => $method_id,
                    'payment_category' => $deposit_category,
                    'trans_type' => $trans_type,
                    'trans_ref_no' => $trans_ref_no,
                    'trans_date' => $trans_date,
                    'note' => $adv_note,
                    'status' => 'Pending',
                ]);
            }

            ActivityLogger::log('Create', "Processed salary for {$emp_name} month {$pay_month} amount {$paid_amount}");
            DB::commit();

            return redirect()->route('salary.process')->with('success', "Salary for {$emp_name} ({$pay_month}) processed successfully! Voucher: {$voucher}");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('salary.process')->with('error', 'Salary Processing Failed: ' . $e->getMessage());
        }
    }

    public function advance(Request $request)
    {
        // AJAX Request Handler
        if ($request->has('ajax_request') || $request->ajax()) {
            $ajax_type = $request->input('ajax_request');

            if ($ajax_type == 'get_payment_methods') {
                $type = $request->input('type');
                $methods = PaymentMethod::where('account_type', $type)->orderBy('method_name', 'asc')->get();
                return response()->json($methods);
            }

            if ($ajax_type == 'get_adjust_preview') {
                $id = (int)$request->input('id');
                $adv = EmpSalaryAdvance::with('employee')->find($id);
                if (!$adv) {
                    return response()->json(['status' => 'not_found']);
                }

                $advance_amount = (float)$adv->amount;
                $employee_id = (int)$adv->employee_id;
                $adjust_month = $adv->adjust_month;

                // Priority 1: Salary of exact adjust month
                $salary = TblEmployeeSalary::where('employee_id', $employee_id)
                    ->where('pay_month', $adjust_month)
                    ->first();

                // Priority 2: Latest pending salary with due
                if (!$salary && !empty($adjust_month)) {
                    $salary = TblEmployeeSalary::where('employee_id', $employee_id)
                        ->where('due_amount', '>', 0)
                        ->where('due_status', 'Pending')
                        ->where('pay_month', '!=', 'Opening')
                        ->orderBy('date', 'desc')
                        ->first();
                }

                $res = [
                    'status' => 'ok',
                    'voucher' => $adv->voucher_no,
                    'amount' => number_format($advance_amount, 2),
                    'emp_name' => ($adv->employee->firstname ?? '') . ' ' . ($adv->employee->lastname ?? ''),
                    'has_salary' => false
                ];

                if ($salary) {
                    $gross = (float)$salary->gross_salary;
                    $paid = (float)$salary->amount;
                    $fine = (float)$salary->fine;
                    $other_ded = (float)$salary->other_deduction;
                    $new_adv_deduct = (float)$salary->adv_deduct + $advance_amount;
                    $new_net = $gross - ($new_adv_deduct + $fine + $other_ded);
                    $new_due = max(0, $new_net - $paid);

                    $res['has_salary'] = true;
                    $res['salary_month'] = $salary->pay_month;
                    $res['salary_voucher'] = $salary->voucher_no;
                    $res['current_due'] = number_format((float)$salary->due_amount, 2);
                    $res['new_due'] = number_format($new_due, 2);
                }

                return response()->json($res);
            }
        }

        $employees = Employee::orderBy('firstname', 'asc')->get();
        $advances = EmpSalaryAdvance::with(['employee', 'paymentMethod'])->orderBy('id', 'desc')->get();

        return view('salary.advance', compact('employees', 'advances'));
    }

    public function storeAdvance(Request $request)
    {
        if ($request->has('ajax_request') || $request->ajax()) {
            return $this->advance($request);
        }

        $employee_id = (int)$request->input('employee_id');
        $advance_date = $request->input('advance_date', date('Y-m-d'));
        $amount = (float)$request->input('amount');
        $adjust_month = $request->input('adjust_month');
        $method_id = (int)$request->input('method_id');
        $note = $request->input('note', '');
        $voucher = $request->input('voucher');
        $main_head = $request->input('main_head', 'Advance Salary');

        $check_voucher = GeneralTransaction::where('check_no', $voucher)->exists();
        if ($check_voucher) {
            return redirect()->route('salary.advance')->with('error', "Warning: Voucher No '$voucher' is already used! Please use a unique voucher.");
        }

        $deposit_category = $request->input('deposit_category');
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

        $final_note = "Advance for " . date("M-Y", strtotime($adjust_month . '-01')) . " | " . $note;

        DB::beginTransaction();
        try {
            EmpSalaryAdvance::create([
                'employee_id' => $employee_id,
                'advance_date' => $advance_date,
                'amount' => $amount,
                'adjust_month' => $adjust_month,
                'voucher_no' => $voucher,
                'method_id' => $method_id,
                'payment_category' => $deposit_category,
                'trans_type' => $trans_type,
                'trans_ref_no' => $trans_ref_no,
                'trans_date' => $trans_date,
                'note' => $note,
                'status' => 'Pending',
            ]);

            GeneralTransaction::create([
                't_date' => $advance_date,
                'head_id' => 10,
                'main_head' => $main_head,
                'method_id' => $method_id,
                'amount' => $amount,
                'note' => $final_note,
                't_type' => 'Payment',
                'check_no' => $voucher,
                'approval_status' => 'Approved',
            ]);

            $pm = PaymentMethod::find($method_id);
            if ($pm) {
                $pm->decrement('amount', $amount);
            }

            ActivityLogger::log('Create', "Provided advance of {$amount} (Voucher: {$voucher})");
            DB::commit();

            return redirect()->route('salary.advance')->with('success', 'Salary advance added successfully with Voucher: ' . $voucher);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('salary.advance')->with('error', 'Transaction Failed: ' . $e->getMessage());
        }
    }

    public function destroyAdvance(Request $request)
    {
        $del_id = (int)$request->input('delete_id');

        DB::beginTransaction();
        try {
            $advance = EmpSalaryAdvance::findOrFail($del_id);
            if ($advance->status == 'Adjusted') {
                return redirect()->route('salary.advance')->with('error', "Error: Adjusted advance voucher ({$advance->voucher_no}) cannot be deleted!");
            }

            $del_amount = (float)$advance->amount;
            $del_method_id = (int)$advance->method_id;
            $del_voucher = $advance->voucher_no;

            $pm = PaymentMethod::find($del_method_id);
            if ($pm) {
                $pm->increment('amount', $del_amount);
            }

            GeneralTransaction::where('check_no', $del_voucher)->where('main_head', 'Advance Salary')->delete();
            $advance->delete();

            ActivityLogger::log('Delete', "Deleted advance voucher {$del_voucher} and restored {$del_amount}");
            DB::commit();

            return redirect()->route('salary.advance')->with('success', "Voucher ({$del_voucher}) deleted and amount restored to account successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('salary.advance')->with('error', 'Deletion Failed: ' . $e->getMessage());
        }
    }

    public function manualAdjustAdvance(Request $request)
    {
        $adjust_id = (int)$request->input('adjust_id');

        DB::beginTransaction();
        try {
            $advance = EmpSalaryAdvance::findOrFail($adjust_id);

            if ($advance->status == 'Adjusted') {
                return redirect()->route('salary.advance')->with('info', "Notice: Voucher ({$advance->voucher_no}) is already marked as Adjusted.");
            }

            $advance_amount = (float)$advance->amount;
            $employee_id = (int)$advance->employee_id;
            $adjust_month = $advance->adjust_month;

            // Find matching salary record to automatically adjust due:
            // 1. Try exact month match
            $salary = TblEmployeeSalary::where('employee_id', $employee_id)
                ->where('pay_month', $adjust_month)
                ->first();

            // 2. If no exact month match, try latest pending salary with due
            if (!$salary && !empty($adjust_month)) {
                $salary = TblEmployeeSalary::where('employee_id', $employee_id)
                    ->where('due_amount', '>', 0)
                    ->where('due_status', 'Pending')
                    ->where('pay_month', '!=', 'Opening')
                    ->orderBy('date', 'desc')
                    ->first();
            }

            $adjusted_salary_info = null;
            $old_due = 0;
            $new_due = 0;

            if ($salary) {
                $old_due = (float)$salary->due_amount;
                $gross = (float)$salary->gross_salary;
                $paid = (float)$salary->amount;
                $fine = (float)$salary->fine;
                $other_ded = (float)$salary->other_deduction;

                // Add to adv_deduct and recalculate
                $current_adv_deduct = (float)$salary->adv_deduct;
                $new_adv_deduct = $current_adv_deduct + $advance_amount;

                $new_net = $gross - ($new_adv_deduct + $fine + $other_ded);
                $new_due = max(0, $new_net - $paid);
                $new_status = ($new_due <= 0.001) ? 'Cleared' : 'Pending';

                $salary_note = $salary->note ? $salary->note : '';
                $add_note = " | Adjusted Adv Voucher: {$advance->voucher_no} (৳ " . number_format($advance_amount, 2) . ")";

                $salary->update([
                    'adv_deduct' => $new_adv_deduct,
                    'net_payable' => $new_net,
                    'due_amount' => $new_due,
                    'due_status' => $new_status,
                    'note' => $salary_note . $add_note
                ]);

                $adjusted_salary_info = "Salary Voucher: {$salary->voucher_no} ({$salary->pay_month})";
            }

            // Update Advance record
            $current_note = $advance->note ? $advance->note : '';
            $adjust_note = ' | Manually Adjusted on ' . date('d-m-Y');
            if ($adjusted_salary_info) {
                $adjust_note .= " against {$adjusted_salary_info}";
            }

            $advance->update([
                'status' => 'Adjusted',
                'note' => $current_note . $adjust_note
            ]);

            ActivityLogger::log('Update', "Manually adjusted advance voucher {$advance->voucher_no} for employee ID {$advance->employee_id}" . ($adjusted_salary_info ? " and updated {$adjusted_salary_info} Due from {$old_due} to {$new_due}" : ""));
            DB::commit();

            $msg = "Advance Voucher ({$advance->voucher_no}) adjusted successfully!";
            if ($adjusted_salary_info) {
                $msg .= " Automatically updated {$adjusted_salary_info}: Due reduced from ৳ " . number_format($old_due, 2) . " to ৳ " . number_format($new_due, 2) . ".";
            }

            return redirect()->route('salary.advance')->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('salary.advance')->with('error', 'Adjustment Failed: ' . $e->getMessage());
        }
    }

    public function history(Request $request)
    {
        $employee_id = $request->input('employee_id', '');
        $from_date = $request->input('from_date', '');
        $to_date = $request->input('to_date', '');
        $pay_month = $request->input('pay_month', '');

        $query = TblEmployeeSalary::with(['employee']);

        if (!empty($employee_id)) {
            $query->where('employee_id', $employee_id);
        }
        if (!empty($from_date) && !empty($to_date)) {
            $query->whereBetween('date', [$from_date, $to_date]);
        } elseif (!empty($from_date)) {
            $query->where('date', '>=', $from_date);
        } elseif (!empty($to_date)) {
            $query->where('date', '<=', $to_date);
        }
        if (!empty($pay_month)) {
            $query->where('pay_month', $pay_month);
        }

        $salaries = $query->orderBy('id', 'desc')->get();
        $employees = Employee::orderBy('firstname', 'asc')->get();

        return view('salary.history', compact('salaries', 'employees', 'employee_id', 'from_date', 'to_date', 'pay_month'));
    }

    public function printSheet(Request $request)
    {
        $employee_id = $request->input('employee_id', '');
        $from_date = $request->input('from_date', '');
        $to_date = $request->input('to_date', '');
        $pay_month = $request->input('pay_month', '');

        $query = TblEmployeeSalary::with(['employee']);

        $employee = null;
        if (!empty($employee_id)) {
            $query->where('employee_id', $employee_id);
            $employee = Employee::find($employee_id);
        }
        if (!empty($from_date) && !empty($to_date)) {
            $query->whereBetween('date', [$from_date, $to_date]);
        } elseif (!empty($from_date)) {
            $query->where('date', '>=', $from_date);
        } elseif (!empty($to_date)) {
            $query->where('date', '<=', $to_date);
        }
        if (!empty($pay_month)) {
            $query->where('pay_month', $pay_month);
        }

        $salaries = $query->orderBy('date', 'asc')->orderBy('id', 'asc')->get();

        return view('salary.sheet_print', compact('salaries', 'employee', 'employee_id', 'from_date', 'to_date', 'pay_month'));
    }

    public function printSlip($id)
    {
        $salary = TblEmployeeSalary::with(['employee'])->findOrFail($id);
        return view('salary.payslip', compact('salary'));
    }
}
