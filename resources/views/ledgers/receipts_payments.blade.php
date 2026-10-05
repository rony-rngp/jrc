@extends('layouts.master')

@section('title', 'Receipts & Payments Report - JRC ERP')
@section('page_title', 'Receipts & Payments Summary Report')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <form method="GET" action="{{ route('ledgers.receipts-payments') }}" class="form-inline">
                    <div class="form-group">
                        <label>From: &nbsp;</label>
                        <input type="date" class="form-control" name="fromDate" value="{{ $fromDate }}">
                    </div>
                    <div class="form-group">
                        <label>&nbsp; To: &nbsp;</label>
                        <input type="date" class="form-control" name="toDate" value="{{ $toDate }}">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Generate Report</button>
                    <button type="button" onclick="window.print()" class="btn btn-success btn-sm pull-right"><i class="fa fa-print"></i> Print</button>
                </form>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-arrow-circle-down"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Receipts</span>
                                <span class="info-box-number">৳ {{ number_format($totalReceived, 2) }}</span>
                                <div class="progress"><div class="progress-bar" style="width: 100%"></div></div>
                                <span class="progress-description">All incoming funds</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="fa fa-arrow-circle-up"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Payments</span>
                                <span class="info-box-number">৳ {{ number_format($totalExpenses + $totalSupplierPaid + $totalSalaryPaid, 2) }}</span>
                                <div class="progress"><div class="progress-bar" style="width: 100%"></div></div>
                                <span class="progress-description">Expenses + Supplier + Salary</span>
                            </div>
                        </div>
                    </div>
                </div>

                <table class="table table-bordered table-striped" style="margin-top: 20px;">
                    <thead>
                        <tr class="bg-gray">
                            <th>Category</th>
                            <th>Description</th>
                            <th>Amount (BDT)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><b>Receipts</b></td>
                            <td>General & Client Collections</td>
                            <td><b class="text-green">৳ {{ number_format($totalReceived, 2) }}</b></td>
                        </tr>
                        <tr>
                            <td><b>Payments</b></td>
                            <td>Office & General Head Expenses</td>
                            <td><b class="text-red">৳ {{ number_format($totalExpenses, 2) }}</b></td>
                        </tr>
                        <tr>
                            <td><b>Payments</b></td>
                            <td>Supplier Payments & Procurement</td>
                            <td><b class="text-red">৳ {{ number_format($totalSupplierPaid, 2) }}</b></td>
                        </tr>
                        <tr>
                            <td><b>Payments</b></td>
                            <td>Employee Salary Paid</td>
                            <td><b class="text-red">৳ {{ number_format($totalSalaryPaid, 2) }}</b></td>
                        </tr>
                        <tr class="bg-info" style="font-size: 16px;">
                            <td colspan="2"><b>Net Balance for Period:</b></td>
                            <td><b>৳ {{ number_format($totalReceived - ($totalExpenses + $totalSupplierPaid + $totalSalaryPaid), 2) }}</b></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
