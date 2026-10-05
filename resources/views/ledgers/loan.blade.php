@extends('layouts.master')

@section('title', 'Loan Ledger - JRC ERP')
@section('page_title', 'Loan & Investor Ledger')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <button type="button" onclick="window.print()" class="btn btn-success btn-sm pull-right"><i class="fa fa-print"></i> Print</button>
            </div>
            <div class="box-body">
                <table class="table table-bordered table-striped table-datatable">
                    <thead>
                        <th>ID</th>
                        <th>Investor / Party</th>
                        <th>Source</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Check No</th>
                        <th>Date</th>
                        <th>Note</th>
                    </thead>
                    <tbody>
                        @foreach($records as $r)
                        <tr>
                            <td>{{ $r->id }}</td>
                            <td><b>{{ $r->name }}</b></td>
                            <td>{{ $r->fund_recev }}</td>
                            <td>৳ {{ number_format((float)$r->amount, 2) }}</td>
                            <td>{{ $r->method_name }}</td>
                            <td>{{ $r->check_no }}</td>
                            <td>{{ $r->date }}</td>
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
