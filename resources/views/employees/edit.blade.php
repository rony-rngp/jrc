@extends('layouts.master')

@section('title', 'JR || Corporation - Edit Employee')
@section('page_title', 'Edit Employee')

@section('breadcrumb')
<li>Employee Desk</li>
<li><a href="{{ route('employees.index') }}">Employee List</a></li>
<li class="active">Edit</li>
@endsection

@section('content')
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Edit Employee Information & Salary Structure</h3>
    </div>
    
    <form class="form-horizontal" method="POST" action="{{ route('employees.update', $employee->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="box-body">
            <h4 style="border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 20px; color: #3c8dbc;">1. Personal Details</h4>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Employee ID</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="employee_id" value="{{ $employee->employee_id }}" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">First Name <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="firstname" value="{{ $employee->firstname }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Last Name <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="lastname" value="{{ $employee->lastname }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Mobile Number <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="contact_info" value="{{ $employee->contact_info }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">NID Number</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="emp_nid" value="{{ $employee->emp_nid }}">
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Date of Birth</label>
                        <div class="col-sm-8">
                            <input type="date" class="form-control" name="birthdate" value="{{ $employee->birthdate }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Gender</label>
                        <div class="col-sm-8">
                            <select class="form-control" name="gender">
                                <option value="Male" {{ $employee->gender == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ $employee->gender == 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ $employee->gender == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Upload Photo</label>
                        <div class="col-sm-8">
                            <input type="file" class="form-control" name="photo" accept="image/*">
                            @if($employee->photo)
                                <small class="text-muted">Current: {{ $employee->photo }}</small>
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Present Address</label>
                        <div class="col-sm-8">
                            <textarea class="form-control" name="address" rows="2">{{ $employee->address }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <h4 style="border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-top: 30px; margin-bottom: 20px; color: #3c8dbc;">2. Official & Salary Setup</h4>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Designation <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="position" value="{{ $employee->position }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Employee Type</label>
                        <div class="col-sm-8">
                            <select class="form-control" name="empType">
                                <option value="Head Office" {{ $employee->empType == 'Head Office' ? 'selected' : '' }}>Head Office</option>
                                <option value="Project Basis" {{ $employee->empType == 'Project Basis' ? 'selected' : '' }}>Project Basis</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Joining Date <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="date" class="form-control" name="created_on" value="{{ $employee->created_on }}" required>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Basic Salary <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="number" step="0.01" class="form-control cal-salary" name="salary" id="basic_salary" value="{{ (float)$employee->salary }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Mobile Bill </label>
                        <div class="col-sm-8">
                            <input type="number" step="0.01" class="form-control cal-salary" name="mobile_bill" id="mobile_bill" value="{{ (float)$employee->mobile_bill }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Lunch Bill </label>
                        <div class="col-sm-8">
                            <input type="number" step="0.01" class="form-control cal-salary" name="lunch_bill" id="lunch_bill" value="{{ (float)$employee->lunch_bill }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Other Allowance </label>
                        <div class="col-sm-8">
                            <input type="number" step="0.01" class="form-control cal-salary" name="other_allowances" id="other_allowances" value="{{ (float)$employee->other_allowances }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" style="color:#00a65a;">Total Gross Salary</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="total_gross" value="{{ number_format((float)$employee->salary + (float)$employee->mobile_bill + (float)$employee->lunch_bill + (float)$employee->other_allowances, 2, '.', '') }}" readonly style="font-weight:bold; color:#00a65a; background-color:#f4f4f4;">
                        </div>
                    </div>
                </div>
            </div>

            <h4 style="border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-top: 30px; margin-bottom: 20px; color: #e08e0b;"><i class="fa fa-balance-scale"></i> 3. Opening Balance Setup (ঐচ্ছিক / পূর্বের বকেয়া বা অগ্রিম থাকলে)</h4>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-sm-5 control-label">Opening Amount</label>
                        <div class="col-sm-7">
                            <input type="number" step="0.01" class="form-control" name="opening_balance" value="{{ $opening_balance ?? 0 }}" placeholder="0.00">
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Balance Type</label>
                        <div class="col-sm-8">
                            <select class="form-control" name="opening_type">
                                <option value="due" {{ ($opening_type ?? 'due') == 'due' ? 'selected' : '' }}>Salary Due (বকেয়া - কর্মচারী পাবে)</option>
                                <option value="advance" {{ ($opening_type ?? 'due') == 'advance' ? 'selected' : '' }}>Salary Advance (অগ্রিম - কোম্পানি পাবে)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Opening Date</label>
                        <div class="col-sm-8">
                            <input type="date" class="form-control" name="opening_date" value="{{ $opening_date ?? date('Y-m-d') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="box-footer text-right">
            <a href="{{ route('employees.index') }}" class="btn btn-default btn-flat"><i class="fa fa-times"></i> Cancel</a>
            <button type="submit" class="btn btn-warning btn-flat"><i class="fa fa-edit"></i> Update Employee</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function(){
    $('.cal-salary').on('input', function(){
        var basic = parseFloat($('#basic_salary').val()) || 0;
        var mobile = parseFloat($('#mobile_bill').val()) || 0;
        var lunch = parseFloat($('#lunch_bill').val()) || 0;
        var other = parseFloat($('#other_allowances').val()) || 0;
        
        var total = basic + mobile + lunch + other;
        $('#total_gross').val(total.toFixed(2));
    });
});
</script>
@endpush
