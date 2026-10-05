@extends('layouts.master')

@section('title', 'Account Statement - JRC ERP')
@section('page_title', 'Account Statement & Fund Flow')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <form method="GET" action="{{ route('ledgers.account-statement') }}" class="form-inline">
                    <div class="form-group">
                        <label>Account: &nbsp;</label>
                        <select class="form-control" name="method_name">
                            <option value="">All Accounts</option>
                            @foreach($methods as $m)
                                <option value="{{ $m->method_name }}" {{ $method == $m->method_name ? 'selected' : '' }}>{{ $m->method_name }}</option>
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
                    <a href="{{ route('ledgers.account-statement') }}" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Reset</a>
                    <button type="button" onclick="window.print()" class="btn btn-success btn-sm pull-right"><i class="fa fa-print"></i> Print</button>
                </form>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4><b><i class="fa fa-arrow-down text-green"></i> Total Received Funds (Inflow)</b></h4>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <th>Date</th>
                                <th>Account Head</th>
                                <th>Method</th>
                                <th>Amount</th>
                            </thead>
                            <tbody>
                                @php $totIn = 0; @endphp
                                @foreach($allReceived as $in)
                                @php $totIn += (float)$in->amount; @endphp
                                <tr>
                                    <td>{{ $in->date }}</td>
                                    <td>{{ $in->name }}</td>
                                    <td>{{ $in->method_name }}</td>
                                    <td><b class="text-green">৳ {{ number_format((float)$in->amount, 2) }}</b></td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray">
                                    <td colspan="3"><b>Total Inflow:</b></td>
                                    <td><b class="text-green">৳ {{ number_format($totIn, 2) }}</b></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h4><b><i class="fa fa-arrow-up text-red"></i> Total Expenses & Payments (Outflow)</b></h4>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <th>Date</th>
                                <th>Expense Name</th>
                                <th>Method</th>
                                <th>Amount</th>
                            </thead>
                            <tbody>
                                @php $totOut = 0; @endphp
                                @foreach($allExpenses as $out)
                                @php $totOut += (float)$out->pay_amount; @endphp
                                <tr>
                                    <td>{{ $out->pay_date }}</td>
                                    <td>{{ $out->expense_name }}</td>
                                    <td>{{ $out->method_name }}</td>
                                    <td><b class="text-red">৳ {{ number_format((float)$out->pay_amount, 2) }}</b></td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray">
                                    <td colspan="3"><b>Total Outflow:</b></td>
                                    <td><b class="text-red">৳ {{ number_format($totOut, 2) }}</b></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
