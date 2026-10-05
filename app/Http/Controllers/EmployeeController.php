<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Helpers\ActivityLogger;

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
        return view('employees.edit', compact('employee'));
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
