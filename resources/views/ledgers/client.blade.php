@extends('layouts.master')

@section('title', 'Client Ledger - JRC ERP')
@section('page_title', 'Client Ledger Statement')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <form method="GET" action="{{ route('ledgers.client') }}" class="form-inline">
                    <div class="form-group">
                        <label>Client: &nbsp;</label>
                        <select class="form-control" name="company_name">
                            <option value="">All Clients</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->company_name }}" {{ $clientName == $c->company_name ? 'selected' : '' }}>{{ $c->company_name }}</option>
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
                    <a href="{{ route('ledgers.client') }}" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Reset</a>
                    <button type="button" onclick="window.print()" class="btn btn-success btn-sm pull-right"><i class="fa fa-print"></i> Print</button>
                </form>
            </div>
            <div class="box-body">
                <table class="table table-bordered table-striped table-datatable">
                    <thead>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Project</th>
                        <th>Type / Note</th>
                        <th>Bill Amount (Debit)</th>
                        <th>Paid Amount (Credit)</th>
                        <th>Method</th>
                    </thead>
                    <tbody>
                        @php $totalBill = 0; $totalPaid = 0; @endphp
                        @foreach($transactions as $t)
                        @php 
                            $totalBill += (float)$t->bill_amount;
                            $totalPaid += (float)$t->pay_amount;
                        @endphp
                        <tr>
                            <td>{{ $t->date }}</td>
                            <td><b>{{ $t->company_name }}</b></td>
                            <td>{{ $t->project_name ?? '-' }}</td>
                            <td>{{ $t->note ?: $t->type }}</td>
                            <td>৳ {{ number_format((float)$t->bill_amount, 2) }}</td>
                            <td><span class="text-green">৳ {{ number_format((float)$t->pay_amount, 2) }}</span></td>
                            <td>{{ $t->method_name }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray font-weight-bold">
                            <td colspan="4" class="text-right"><b>Total:</b></td>
                            <td><b>৳ {{ number_format($totalBill, 2) }}</b></td>
                            <td><b>৳ {{ number_format($totalPaid, 2) }}</b></td>
                            <td><b>Due: ৳ {{ number_format($totalBill - $totalPaid, 2) }}</b></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
