<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::orderBy('id', 'desc')->get();
        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'firstname' => 'required',
            'contact_info' => 'required',
        ]);

        $photoName = '';
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $photoName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images'), $photoName);
        }

        $emp = Employee::create([
            'employee_id' => $request->input('employee_id', 'EMP-' . rand(1000, 9999)),
            'firstname' => $request->input('firstname'),
            'lastname' => $request->input('lastname', ''),
            'address' => $request->input('address', ''),
            'birthdate' => $request->input('birthdate', '2000-01-01'),
            'emp_nid' => $request->input('emp_nid', ''),
            'contact_info' => $request->input('contact_info'),
            'gender' => $request->input('gender', 'Male'),
            'position' => $request->input('position', 'Staff'),
            'schedule_id' => 1,
            'photo' => $photoName ?: 'profile.jpg',
            'empType' => $request->input('empType', 'Permanent'),
            'created_on' => date('Y-m-d'),
            'salary' => (float)$request->input('salary', 0),
            'mobile_bill' => (float)$request->input('mobile_bill', 0),
            'lunch_bill' => (float)$request->input('lunch_bill', 0),
            'other_allowances' => (float)$request->input('other_allowances', 0),
            'salary_advance' => 0,
            'pay_status' => 'Pending',
            'pay_month' => date('F Y'),
        ]);

        $opening_balance = (float)$request->input('opening_balance', 0);
        $opening_type = $request->input('opening_type', 'due');
        $opening_date = $request->input('opening_date', date('Y-m-d'));

        if ($opening_balance > 0) {
            if ($opening_type == 'due') {
                DB::table('tbl_employee_salary')->insert([
                    'employee_id' => $emp->id,
                    'employee_name' => $emp->firstname . ' ' . $emp->lastname,
                    'method_name' => 'Cash',
                    'main_salary' => 0,
                    'mobile_bill' => 0,
                    'lunch_bill' => 0,
                    'other_allowances' => 0,
                    'gross_salary' => 0,
                    'amount' => 0,
                    'due_amount' => $opening_balance,
                    'due_status' => 'Pending',
                    'pay_month' => 'Opening',
                    'date' => $opening_date,
                    'note' => 'Opening Balance (Salary Due / Will Get)',
                    'bonus' => 0,
                    'previous_due' => 0,
                    'fine' => 0,
                    'adv_deduct' => 0,
                    'other_deduction' => 0,
                    'net_payable' => 0,
                    'voucher_no' => 'OB-EMP-' . $emp->id
                ]);
            } else {
                DB::table('emp_salary_advance')->insert([
                    'employee_id' => $emp->id,
                    'advance_date' => $opening_date,
                    'amount' => $opening_balance,
                    'adjust_month' => date('Y-m', strtotime($opening_date)),
                    'voucher_no' => 'OB-EMP-' . $emp->id,
                    'method_id' => 1,
                    'payment_category' => 'Cash',
                    'note' => 'Opening Balance (Advance Payment)',
                    'status' => 'Pending'
                ]);
            }
        }

        ActivityLogger::log('Create', "Added employee: {$emp->firstname} {$emp->lastname}");

        return redirect()->route('employees.index')->with('success', 'Employee added successfully.');
    }

    public function show($id)
    {
        $employee = Employee::findOrFail($id);
        return view('employees.show', compact('employee'));
    }

    public function edit($id)
    {
        $employee = Employee::findOrFail($id);

        $opening_sal = DB::table('tbl_employee_salary')
            ->where('employee_id', $id)
            ->where('pay_month', 'Opening')
            ->first();

        $opening_adv = DB::table('emp_salary_advance')
            ->where('employee_id', $id)
            ->where(function($q) use ($id) {
                $q->where('voucher_no', 'OB-EMP-' . $id)
                  ->orWhere('note', 'like', '%Opening Balance%');
            })
            ->first();

        $opening_balance = 0;
        $opening_type = 'due';
        $opening_date = date('Y-m-d');

        if ($opening_sal) {
            $opening_balance = (float)$opening_sal->due_amount;
            $opening_type = 'due';
            $opening_date = $opening_sal->date ?? date('Y-m-d');
        } elseif ($opening_adv) {
            $opening_balance = (float)$opening_adv->amount;
            $opening_type = 'advance';
            $opening_date = $opening_adv->advance_date ?? date('Y-m-d');
        }

        return view('employees.edit', compact('employee', 'opening_balance', 'opening_type', 'opening_date'));
    }

    public function update(Request $request, $id)
    {
        $emp = Employee::findOrFail($id);

        $photoName = $emp->photo;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $photoName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images'), $photoName);
        }

        $emp->update([
            'firstname' => $request->input('firstname'),
            'lastname' => $request->input('lastname'),
            'address' => $request->input('address'),
            'contact_info' => $request->input('contact_info'),
            'position' => $request->input('position'),
            'salary' => (float)$request->input('salary'),
            'mobile_bill' => (float)$request->input('mobile_bill', 0),
            'lunch_bill' => (float)$request->input('lunch_bill', 0),
            'other_allowances' => (float)$request->input('other_allowances', 0),
            'photo' => $photoName,
        ]);

        $opening_balance = (float)$request->input('opening_balance', 0);
        $opening_type = $request->input('opening_type', 'due');
        $opening_date = $request->input('opening_date', date('Y-m-d'));

        // Reset old opening balance records
        DB::table('tbl_employee_salary')
            ->where('employee_id', $id)
            ->where('pay_month', 'Opening')
            ->delete();

        DB::table('emp_salary_advance')
            ->where('employee_id', $id)
            ->where(function($q) use ($id) {
                $q->where('voucher_no', 'OB-EMP-' . $id)
                  ->orWhere('note', 'like', '%Opening Balance%');
            })
            ->delete();

        if ($opening_balance > 0) {
            if ($opening_type == 'due') {
                DB::table('tbl_employee_salary')->insert([
                    'employee_id' => $id,
                    'employee_name' => $emp->firstname . ' ' . $emp->lastname,
                    'method_name' => 'Cash',
                    'main_salary' => 0,
                    'mobile_bill' => 0,
                    'lunch_bill' => 0,
                    'other_allowances' => 0,
                    'gross_salary' => 0,
                    'amount' => 0,
                    'due_amount' => $opening_balance,
                    'due_status' => 'Pending',
                    'pay_month' => 'Opening',
                    'date' => $opening_date,
                    'note' => 'Opening Balance (Salary Due / Will Get)',
                    'bonus' => 0,
                    'previous_due' => 0,
                    'fine' => 0,
                    'adv_deduct' => 0,
                    'other_deduction' => 0,
                    'net_payable' => 0,
                    'voucher_no' => 'OB-EMP-' . $id
                ]);
            } else {
                DB::table('emp_salary_advance')->insert([
                    'employee_id' => $id,
                    'advance_date' => $opening_date,
                    'amount' => $opening_balance,
                    'adjust_month' => date('Y-m', strtotime($opening_date)),
                    'voucher_no' => 'OB-EMP-' . $id,
                    'method_id' => 1,
                    'payment_category' => 'Cash',
                    'note' => 'Opening Balance (Advance Payment)',
                    'status' => 'Pending'
                ]);
            }
        }

        ActivityLogger::log('Update', "Updated employee: {$emp->firstname} {$emp->lastname}");

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy($id)
    {
        $emp = Employee::findOrFail($id);
        $name = $emp->firstname . ' ' . $emp->lastname;
        $emp->delete();

        ActivityLogger::log('Delete', "Deleted employee: {$name}");

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }
}
