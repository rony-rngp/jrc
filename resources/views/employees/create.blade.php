@extends('layouts.master')

@section('title', 'JR || Corporation - Add New Employee')
@section('page_title', 'Add New Employee')

@section('breadcrumb')
<li>Employee Desk</li>
<li class="active">Employee Add</li>
@endsection

@section('content')
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Employee Information & Salary Structure</h3>
    </div>
    
    <form class="form-horizontal" method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="box-body">
            <h4 style="border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 20px; color: #3c8dbc;">1. Personal Details</h4>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Employee ID</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="employee_id" value="{{ time() . rand(10, 99) }}" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">First Name <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="firstname" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Last Name <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="lastname" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Mobile Number <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="contact_info" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">NID Number</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="emp_nid">
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Date of Birth</label>
                        <div class="col-sm-8">
                            <input type="date" class="form-control" name="birthdate">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Gender</label>
                        <div class="col-sm-8">
                            <select class="form-control" name="gender">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Upload Photo</label>
                        <div class="col-sm-8">
                            <input type="file" class="form-control" name="photo" accept="image/*">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Present Address</label>
                        <div class="col-sm-8">
                            <textarea class="form-control" name="address" rows="2"></textarea>
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
                            <input type="text" class="form-control" name="position" placeholder="e.g. IT Executive" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Employee Type</label>
                        <div class="col-sm-8">
                            <select class="form-control" name="empType">
                                <option value="Head Office">Head Office</option>
                                <option value="Project Basis">Project Basis</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Joining Date <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="date" class="form-control" name="created_on" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Basic Salary <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="number" step="0.01" class="form-control cal-salary" name="salary" id="basic_salary" value="0" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Mobile Bill </label>
                        <div class="col-sm-8">
                            <input type="number" step="0.01" class="form-control cal-salary" name="mobile_bill" id="mobile_bill" value="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Lunch Bill </label>
                        <div class="col-sm-8">
                            <input type="number" step="0.01" class="form-control cal-salary" name="lunch_bill" id="lunch_bill" value="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Other Allowance </label>
                        <div class="col-sm-8">
                            <input type="number" step="0.01" class="form-control cal-salary" name="other_allowances" id="other_allowances" value="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" style="color:#00a65a;">Total Gross Salary</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="total_gross" value="0" readonly style="font-weight:bold; color:#00a65a; background-color:#f4f4f4;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="box-footer text-right">
            <button type="reset" class="btn btn-default btn-flat"><i class="fa fa-times"></i> Reset</button>
            <button type="submit" name="add_employee" class="btn btn-primary btn-flat"><i class="fa fa-save"></i> Save Employee & Salary Setup</button>
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
