@extends('layouts.master')

@section('title', 'Fund Transfer Details - ' . $voucher)

@section('content')
<section class="content-header">
    <h1>
        <i class="fa fa-exchange text-purple"></i> Fund Transfer Details
        <small>Voucher #{{ $voucher }}</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{ route('balance-transfers.index') }}">Balance Transfers</a></li>
        <li class="active">{{ $voucher }}</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-purple" style="border-top-color: #605ca8;">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Transfer Summary & Actions</h3>
                    <div class="box-tools pull-right">
                        <a href="{{ route('balance-transfers.show', $voucher) }}?print=1" target="_blank" class="btn btn-primary btn-sm btn-flat">
                            <i class="fa fa-print"></i> Print Voucher
                        </a>
                        <a href="{{ route('balance-transfers.index') }}" class="btn btn-default btn-sm btn-flat">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <!-- Top Metric Cards -->
                    <div class="row">
                        <div class="col-md-4 col-sm-6 col-xs-12">
                            <div class="info-box bg-aqua">
                                <span class="info-box-icon"><i class="fa fa-barcode"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Voucher No</span>
                                    <span class="info-box-number">{{ $voucher }}</span>
                                    <span class="progress-description">Date: {{ $date }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6 col-xs-12">
                            <div class="info-box bg-purple">
                                <span class="info-box-icon"><i class="fa fa-money"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Transferred Amount</span>
                                    <span class="info-box-number">৳ {{ number_format((float)$amount, 2) }}</span>
                                    <span class="progress-description">Fund Internal Transfer</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6 col-xs-12">
                            <div class="info-box bg-green">
                                <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Transfer Status</span>
                                    <span class="info-box-number">Completed</span>
                                    <span class="progress-description">Both Accounts Synchronized</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Flow Card -->
                    <div class="row" style="margin-top: 15px;">
                        <div class="col-md-12">
                            <div class="callout callout-info" style="background-color: #f3e5f5 !important; border-color: #ba68c8 !important; color: #4a148c !important;">
                                <div class="row text-center" style="font-size: 16px;">
                                    <div class="col-md-5">
                                        <div style="background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #e1bee7;">
                                            <small class="text-muted" style="display:block; text-transform:uppercase; font-weight:bold;">Source Account (Deducted)</small>
                                            <h4 style="color: #c0392b; margin: 5px 0 0 0; font-weight:bold;"><i class="fa fa-minus-circle"></i> {{ $from_account ?: 'N/A' }}</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-2" style="padding-top: 20px;">
                                        <i class="fa fa-arrow-right fa-2x text-purple hidden-xs"></i>
                                        <i class="fa fa-arrow-down fa-2x text-purple visible-xs"></i>
                                        <div style="font-weight:bold; color: #605ca8; margin-top:5px;">৳ {{ number_format((float)$amount, 2) }}</div>
                                    </div>
                                    <div class="col-md-5">
                                        <div style="background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #e1bee7;">
                                            <small class="text-muted" style="display:block; text-transform:uppercase; font-weight:bold;">Destination Account (Credited)</small>
                                            <h4 style="color: #27ae60; margin: 5px 0 0 0; font-weight:bold;"><i class="fa fa-plus-circle"></i> {{ $to_account ?: 'N/A' }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Details Box -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-solid box-default">
                                <div class="box-header with-border bg-gray-light">
                                    <h3 class="box-title"><i class="fa fa-list text-purple"></i> Transaction Specifics</h3>
                                </div>
                                <div class="box-body no-padding">
                                    <table class="table table-bordered table-striped">
                                        <tr>
                                            <th style="width: 25%;">Voucher Number</th>
                                            <td><span class="label label-primary" style="font-size:13px;">{{ $voucher }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Transfer Date</th>
                                            <td><b>{{ $date }}</b></td>
                                        </tr>
                                        <tr>
                                            <th>From Account</th>
                                            <td class="text-bold text-red">{{ $from_account ?: 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>To Account</th>
                                            <td class="text-bold text-green">{{ $to_account ?: 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>TrxID / Cheque Details</th>
                                            <td>{{ $trx_info ?: 'None' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Remarks / Purpose</th>
                                            <td>{{ $remarks_info ?: 'Internal Fund Transfer' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="row">
                        <div class="col-md-12 text-right">
                            <a href="{{ route('balance-transfers.show', $voucher) }}?print=1" target="_blank" class="btn btn-primary btn-flat">
                                <i class="fa fa-print"></i> Print Voucher
                            </a>
                            <a href="{{ route('balance-transfers.index') }}" class="btn btn-default btn-flat">
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
