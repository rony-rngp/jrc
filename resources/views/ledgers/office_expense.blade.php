@extends('layouts.master')

@section('title', 'Office Expense Ledger - JRC ERP')
@section('page_title', 'Office & Head Office Expense Ledger')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <form method="GET" action="{{ route('ledgers.office-expense') }}" class="form-inline">
                    <div class="form-group">
                        <label>From: &nbsp;</label>
                        <input type="date" class="form-control" name="fromDate" value="{{ $fromDate }}">
                    </div>
                    <div class="form-group">
                        <label>&nbsp; To: &nbsp;</label>
                        <input type="date" class="form-control" name="toDate" value="{{ $toDate }}">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Filter</button>
                    <a href="{{ route('ledgers.office-expense') }}" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Reset</a>
                    <button type="button" onclick="window.print()" class="btn btn-success btn-sm pull-right"><i class="fa fa-print"></i> Print</button>
                </form>
            </div>
            <div class="box-body">
                <table class="table table-bordered table-striped table-datatable">
                    <thead>
                        <th>Date</th>
                        <th>Expense Head / Name</th>
                        <th>Payment Method</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Note</th>
                    </thead>
                    <tbody>
                        @php $totalExp = 0; @endphp
                        @foreach($expenses as $e)
                        @php $totalExp += (float)$e->pay_amount; @endphp
                        <tr>
                            <td>{{ $e->pay_date }}</td>
                            <td><b>{{ $e->expense_name }}</b></td>
                            <td>{{ $e->method_name }}</td>
                            <td><span class="text-red">৳ {{ number_format((float)$e->pay_amount, 2) }}</span></td>
                            <td><span class="label label-success">{{ $e->pay_status }}</span></td>
                            <td>{{ $e->pay_note }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray">
                            <td colspan="3" class="text-right"><b>Total Expenses:</b></td>
                            <td colspan="3"><b class="text-red">৳ {{ number_format($totalExp, 2) }}</b></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
