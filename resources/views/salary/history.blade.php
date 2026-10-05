@extends('layouts.master')

@section('title', 'Salary History - JRC ERP')
@section('page_title', 'Employee Salary Payment History')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header with-border">
                <a href="{{ route('salary.process') }}" class="btn btn-primary btn-sm"><i class="fa fa-calculator"></i> Process New Salary</a>
            </div>
            <div class="box-body">
                <table class="table table-bordered table-striped table-datatable">
                    <thead>
                        <th>ID</th>
                        <th>Employee</th>
                        <th>Month</th>
                        <th>Basic Salary</th>
                        <th>Gross Salary</th>
                        <th>Paid Amount</th>
                        <th>Due Salary</th>
                        <th>Method</th>
                        <th>Date</th>
                    </thead>
                    <tbody>
                        @foreach($salaries as $s)
                        <tr>
                            <td>{{ $s->id }}</td>
                            <td><b>{{ $s->employee_name }}</b></td>
                            <td><span class="label label-info">{{ $s->pay_month }}</span></td>
                            <td>৳ {{ number_format((float)$s->main_salary, 2) }}</td>
                            <td>৳ {{ number_format((float)$s->gross_salary, 2) }}</td>
                            <td><span class="text-green font-weight-bold">৳ {{ number_format((float)$s->amount, 2) }}</span></td>
                            <td>
                                @if((float)$s->due_amount > 0)
                                    <span class="text-red">৳ {{ number_format((float)$s->due_amount, 2) }}</span>
                                @else
                                    <span class="text-muted">৳ 0.00</span>
                                @endif
                            </td>
                            <td>{{ $s->method_name }}</td>
                            <td>{{ $s->date }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
