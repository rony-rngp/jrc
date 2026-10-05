@extends('layouts.master')

@section('title', 'Revenue & Balance Statement - JRC ERP')
@section('page_title', 'Revenue & Balance Statement')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-line-chart"></i> Company Financial Overview</h3>
                <button type="button" onclick="window.print()" class="btn btn-success btn-sm pull-right"><i class="fa fa-print"></i> Print</button>
            </div>
            <div class="box-body">
                <h4><b>Current Account Balances</b></h4>
                <table class="table table-bordered table-striped">
                    <thead>
                        <th>Account / Method</th>
                        <th>Account Number</th>
                        <th>Current Balance</th>
                    </thead>
                    <tbody>
                        @php $tot = 0; @endphp
                        @foreach($paymentMethods as $pm)
                        @php $tot += (float)$pm->amount; @endphp
                        <tr>
                            <td><b>{{ $pm->method_name }}</b></td>
                            <td>{{ $pm->account_number ?? '-' }}</td>
                            <td><span class="label label-success" style="font-size: 14px;">৳ {{ number_format($pm->amount, 2) }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray">
                            <td colspan="2"><b>Total Available Cash/Bank Balance:</b></td>
                            <td><b class="text-green" style="font-size: 16px;">৳ {{ number_format($tot, 2) }}</b></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
