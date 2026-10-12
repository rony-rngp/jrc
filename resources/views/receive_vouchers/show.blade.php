@extends('layouts.master')

@section('title', 'Receive Voucher Details - ' . $voucher_no)

@section('content')
<section class="content-header">
    <h1>
        <i class="fa fa-arrow-circle-o-down text-green"></i> Receive Voucher Details
        <small>{{ $voucher_no }}</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{ route('receive-vouchers.index') }}">Receive Vouchers</a></li>
        <li class="active">{{ $voucher_no }}</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Voucher Information & Actions</h3>
                    <div class="box-tools pull-right">
                        <a href="{{ route('receive-vouchers.show', $id) }}?type={{ $type }}&print=1" target="_blank" class="btn btn-primary btn-sm btn-flat">
                            <i class="fa fa-print"></i> Print Money Receipt
                        </a>
                        <a href="{{ route('receive-vouchers.edit', $id) }}?type={{ $type }}" class="btn btn-success btn-sm btn-flat">
                            <i class="fa fa-edit"></i> Edit Voucher
                        </a>
                        <a href="{{ route('receive-vouchers.index') }}" class="btn btn-default btn-sm btn-flat">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <!-- Top Metric Cards -->
                    <div class="row">
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="info-box bg-aqua">
                                <span class="info-box-icon"><i class="fa fa-barcode"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Voucher No</span>
                                    <span class="info-box-number">{{ $voucher_no }}</span>
                                    <span class="progress-description">Date: {{ date('d M, Y', strtotime($t_date)) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="info-box bg-green">
                                <span class="info-box-icon"><i class="fa fa-money"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Received Amount</span>
                                    <span class="info-box-number">৳ {{ number_format((float)$amount, 2) }}</span>
                                    <span class="progress-description">Type: {{ $receive_type_label }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="info-box bg-blue">
                                <span class="info-box-icon"><i class="fa fa-credit-card"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Deposit Account</span>
                                    <span class="info-box-number" style="font-size:16px;">{{ $method_name ?: 'Cash' }}</span>
                                    <span class="progress-description">Ref: {{ $ref_info ?: 'Direct' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="info-box bg-yellow">
                                <span class="info-box-icon"><i class="fa fa-balance-scale"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Balance Status</span>
                                    <span class="info-box-number" style="font-size:14px;">{{ $current_balance_text ?: 'Updated' }}</span>
                                    <span class="progress-description">Ledger Position</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Details Box -->
                    <div class="row" style="margin-top: 15px;">
                        <div class="col-md-6">
                            <div class="box box-solid box-default">
                                <div class="box-header with-border bg-gray-light">
                                    <h3 class="box-title"><i class="fa fa-user text-green"></i> Payer / Source Information</h3>
                                </div>
                                <div class="box-body no-padding">
                                    <table class="table table-bordered table-striped">
                                        <tr>
                                            <th style="width: 35%;">Category / Type</th>
                                            <td><span class="label label-success" style="font-size: 13px;">{{ $receive_type_label }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Received From</th>
                                            <td>{!! $received_from !!}</td>
                                        </tr>
                                        <tr>
                                            <th>Voucher Date</th>
                                            <td><b>{{ date('d F, Y', strtotime($t_date)) }}</b></td>
                                        </tr>
                                        <tr>
                                            <th>Voucher No</th>
                                            <td><span class="label label-primary" style="font-size:13px;">{{ $voucher_no }}</span></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="box box-solid box-default">
                                <div class="box-header with-border bg-gray-light">
                                    <h3 class="box-title"><i class="fa fa-bank text-primary"></i> Payment & Reference Info</h3>
                                </div>
                                <div class="box-body no-padding">
                                    <table class="table table-bordered table-striped">
                                        <tr>
                                            <th style="width: 35%;">Deposit Method</th>
                                            <td><b>{{ $method_name ?: 'Cash Account' }}</b></td>
                                        </tr>
                                        <tr>
                                            <th>Reference Details</th>
                                            <td>{{ $ref_info ?: 'No external reference / Cash' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Amount Received</th>
                                            <td class="text-green text-bold" style="font-size:16px;">৳ {{ number_format((float)$amount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Remarks / Note</th>
                                            <td>{{ $note ?: 'None' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="row">
                        <div class="col-md-12 text-right">
                            <a href="{{ route('receive-vouchers.show', $id) }}?type={{ $type }}&print=1" target="_blank" class="btn btn-primary btn-flat">
                                <i class="fa fa-print"></i> Print Money Receipt
                            </a>
                            <a href="{{ route('receive-vouchers.edit', $id) }}?type={{ $type }}" class="btn btn-success btn-flat">
                                <i class="fa fa-edit"></i> Edit Voucher
                            </a>
                            <a href="{{ route('receive-vouchers.index') }}" class="btn btn-default btn-flat">
                                <i class="fa fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection
