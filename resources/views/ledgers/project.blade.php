@extends('layouts.master')

@section('title', 'Project Ledger - JRC ERP')
@section('page_title', 'Project Ledger Statement')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <form method="GET" action="{{ route('ledgers.project') }}" class="form-inline">
                    <div class="form-group">
                        <label>Project: &nbsp;</label>
                        <select class="form-control" name="project_name">
                            <option value="">All Projects</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->project_name }}" {{ $projectName == $p->project_name ? 'selected' : '' }}>{{ $p->project_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>&nbsp; From: &nbsp;</label>
                        <input type="date" class="form-control" name="fromDate" value="{{ $fromDate }}">
                    </div>
                    <div class="form-group">
                        <label>&nbsp; To: &nbsp;</label>
                        <input type="date" class="form-control" name="toDate" value="{{ $toDate }}">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Filter</button>
                    <a href="{{ route('ledgers.project') }}" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Reset</a>
                    <button type="button" onclick="window.print()" class="btn btn-success btn-sm pull-right"><i class="fa fa-print"></i> Print</button>
                </form>
            </div>
            <div class="box-body">
                <table class="table table-bordered table-striped table-datatable">
                    <thead>
                        <th>Date</th>
                        <th>Project</th>
                        <th>Client</th>
                        <th>Bill Amount</th>
                        <th>Paid Amount</th>
                        <th>Payment Method</th>
                        <th>Note</th>
                    </thead>
                    <tbody>
                        @foreach($records as $r)
                        <tr>
                            <td>{{ $r->date }}</td>
                            <td><b>{{ $r->project_name }}</b></td>
                            <td>{{ $r->company_name }}</td>
                            <td>৳ {{ number_format((float)$r->bill_amount, 2) }}</td>
                            <td><span class="text-green">৳ {{ number_format((float)$r->pay_amount, 2) }}</span></td>
                            <td>{{ $r->method_name }}</td>
                            <td>{{ $r->note }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
