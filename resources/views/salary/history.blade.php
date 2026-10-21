@extends('layouts.master')

@section('title', 'Salary History - JRC ERP')
@section('page_title', 'Employee Salary Payment History')

@section('content')
<!-- Filter Box for Employee wise and Date wise Search & Print -->
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-filter"></i> Filter & Print Salary Sheet</h3>
        <div class="box-tools pull-right">
            <a href="{{ route('salary.process') }}" class="btn btn-primary btn-sm"><i class="fa fa-calculator"></i> Process New Salary</a>
        </div>
    </div>
    <div class="box-body" style="padding: 15px;">
        <form method="GET" action="{{ route('salary.history') }}" id="filterForm">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="text-muted">Select Employee</label>
                        <select name="employee_id" id="employee_id" class="form-control select2" style="width:100%;">
                            <option value="">-- All Employees --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ $employee_id == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->firstname }} {{ $emp->lastname }} ({{ $emp->employee_id ?: 'ID: ' . $emp->id }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="text-muted">From Date</label>
                        <input type="date" name="from_date" id="from_date" class="form-control" value="{{ $from_date }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="text-muted">To Date</label>
                        <input type="date" name="to_date" id="to_date" class="form-control" value="{{ $to_date }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="text-muted">Pay Month</label>
                        <input type="text" name="pay_month" id="pay_month" class="form-control" placeholder="e.g. 2026-08" value="{{ $pay_month }}">
                    </div>
                </div>
                <div class="col-md-3" style="padding-top: 25px;">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-search"></i> Filter
                    </button>
                    <a href="{{ route('salary.history') }}" class="btn btn-default" title="Reset Filters">
                        <i class="fa fa-refresh"></i>
                    </a>
                    <button type="button" id="btnPrintSheet" class="btn btn-info pull-right">
                        <i class="fa fa-print"></i> Print Sheet
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- History Table Box -->
<div class="row">
    <div class="col-xs-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-list"></i> Salary Payment Records ({{ count($salaries) }})
                </h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped table-datatable" id="salaryHistoryTable">
                    <thead>
                        <tr>
                            <th width="4%">ID</th>
                            <th>Employee</th>
                            <th>Month</th>
                            <th class="text-right">Basic</th>
                            <th class="text-right">Gross</th>
                            <th class="text-right">Adv Deduct</th>
                            <th class="text-right">Net Payable</th>
                            <th class="text-right">Paid Amount</th>
                            <th class="text-right">Due / Adv</th>
                            <th>Payment Method & Vch</th>
                            <th>Date</th>
                            <th width="8%" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salaries as $s)
                        <tr>
                            <td>{{ $s->id }}</td>
                            <td>
                                <b>{{ $s->employee_name }}</b>
                                @if($s->employee && !empty($s->employee->position))
                                    <br><small class="text-muted">{{ $s->employee->position }}</small>
                                @endif
                            </td>
                            <td><span class="label label-info">{{ $s->pay_month }}</span></td>
                            <td class="text-right font-mono">৳ {{ number_format((float)$s->main_salary, 2) }}</td>
                            <td class="text-right font-mono">৳ {{ number_format((float)$s->gross_salary, 2) }}</td>
                            <td class="text-right font-mono text-danger">
                                @if((float)$s->adv_deduct > 0)
                                    -৳ {{ number_format((float)$s->adv_deduct, 2) }}
                                @else
                                    ৳ 0.00
                                @endif
                            </td>
                            <td class="text-right font-mono font-weight-bold">
                                ৳ {{ number_format((float)$s->net_payable, 2) }}
                            </td>
                            <td class="text-right font-mono">
                                <span class="text-green font-weight-bold">৳ {{ number_format((float)$s->amount, 2) }}</span>
                            </td>
                            <td class="text-right font-mono">
                                @if((float)$s->due_amount > 0)
                                    <span class="label label-danger">Due: ৳ {{ number_format((float)$s->due_amount, 2) }}</span>
                                @elseif((float)$s->due_amount < 0)
                                    <span class="label label-warning">Adv: ৳ {{ number_format(abs((float)$s->due_amount), 2) }}</span>
                                @else
                                    <span class="label label-success">Cleared</span>
                                @endif
                            </td>
                            <td>
                                <b>{{ $s->method_name }}</b>
                                @if(!empty($s->voucher_no))
                                    <br><small class="text-muted"><i class="fa fa-ticket"></i> {{ $s->voucher_no }}</small>
                                @endif
                            </td>
                            <td>{{ !empty($s->date) && $s->date != '0000-00-00' ? date('d-M-Y', strtotime($s->date)) : '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('salary.payslip', $s->id) }}" target="_blank" class="btn btn-default btn-xs" title="Print Individual Payslip">
                                    <i class="fa fa-file-text-o"></i> Slip
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function(){
    if ($.fn.select2) {
        $('.select2').select2();
    }

    // Print Salary Sheet Employee Wise button
    $('#btnPrintSheet').click(function(e){
        e.preventDefault();
        var empId = $('#employee_id').val();
        var fromDate = $('#from_date').val();
        var toDate = $('#to_date').val();
        var month = $('#pay_month').val();

        var url = "{{ route('salary.sheet.print') }}" + 
                  "?employee_id=" + encodeURIComponent(empId) + 
                  "&from_date=" + encodeURIComponent(fromDate) + 
                  "&to_date=" + encodeURIComponent(toDate) + 
                  "&pay_month=" + encodeURIComponent(month);

        window.open(url, '_blank', 'width=1100,height=750');
    });
});
</script>
@endpush
