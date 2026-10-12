@extends('layouts.master')

@section('title', 'Client Bill Details - #' . str_pad($bill->id, 5, '0', STR_PAD_LEFT))

@section('content')
<section class="content-header">
    <h1>
        <i class="fa fa-file-text-o text-primary"></i> Client Bill Details
        <small>Bill #{{ str_pad($bill->id, 5, '0', STR_PAD_LEFT) }}</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{ route('client-bills.index') }}">Client Bills</a></li>
        <li class="active">Bill #{{ str_pad($bill->id, 5, '0', STR_PAD_LEFT) }}</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Bill Summary & Actions</h3>
                    <div class="box-tools pull-right">
                        <a href="{{ route('client-bills.show', $bill->id) }}?print=1" target="_blank" class="btn btn-primary btn-sm btn-flat">
                            <i class="fa fa-print"></i> Print Bill
                        </a>
                        <a href="{{ route('client-bills.index') }}" class="btn btn-default btn-sm btn-flat">
                            <i class="fa fa-arrow-left"></i> Back to Bills List
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <!-- Top Metric Cards -->
                    <div class="row">
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="info-box bg-aqua">
                                <span class="info-box-icon"><i class="fa fa-hashtag"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Bill Number</span>
                                    <span class="info-box-number">#{{ str_pad($bill->id, 5, '0', STR_PAD_LEFT) }}</span>
                                    <span class="progress-description">Date: {{ date('d M, Y', strtotime($bill->date)) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="info-box bg-blue">
                                <span class="info-box-icon"><i class="fa fa-money"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Gross Billed</span>
                                    <span class="info-box-number">৳ {{ number_format($billAmount, 2) }}</span>
                                    <span class="progress-description">Stage: {{ $stage }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="info-box {{ $discount > 0 ? 'bg-yellow' : 'bg-gray' }}">
                                <span class="info-box-icon"><i class="fa fa-tags"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Discount</span>
                                    <span class="info-box-number">৳ {{ number_format($discount, 2) }}</span>
                                    <span class="progress-description">{{ $discount > 0 ? 'Deducted from Bill' : 'No Discount' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="info-box bg-green">
                                <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Net Payable</span>
                                    <span class="info-box-number">৳ {{ number_format($netAmount, 2) }}</span>
                                    <span class="progress-description">Status: {{ $bill->payment_status ?? 'Pending' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Meta Information -->
                    <div class="row" style="margin-top: 15px;">
                        <div class="col-md-6">
                            <div class="box box-solid box-default">
                                <div class="box-header with-border bg-gray-light">
                                    <h3 class="box-title"><i class="fa fa-user text-primary"></i> Client & Project Information</h3>
                                </div>
                                <div class="box-body no-padding">
                                    <table class="table table-bordered table-striped">
                                        <tr>
                                            <th style="width: 35%;">Client / Company</th>
                                            <td><b>{{ $bill->company_name ?: 'N/A' }}</b></td>
                                        </tr>
                                        <tr>
                                            <th>Project Name</th>
                                            <td><b>{{ $bill->project_name ?: 'N/A' }}</b></td>
                                        </tr>
                                        <tr>
                                            <th>Bill Date</th>
                                            <td><b>{{ date('d F, Y', strtotime($bill->date)) }}</b></td>
                                        </tr>
                                        <tr>
                                            <th>Bill Stage</th>
                                            <td><span class="label label-primary" style="font-size:13px;">{{ $stage }}</span></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="box box-solid box-default">
                                <div class="box-header with-border bg-gray-light">
                                    <h3 class="box-title"><i class="fa fa-file-text-o text-success"></i> Payment Status & Remarks</h3>
                                </div>
                                <div class="box-body no-padding">
                                    <table class="table table-bordered table-striped">
                                        <tr>
                                            <th style="width: 35%;">Payment Status</th>
                                            <td>
                                                <span class="label {{ ($bill->payment_status == 'Paid') ? 'label-success' : 'label-warning' }}">
                                                    {{ $bill->payment_status ?? 'Pending' }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Currency</th>
                                            <td><b>BDT (৳)</b></td>
                                        </tr>
                                        <tr>
                                            <th>Remarks / Note</th>
                                            <td>{{ $clean_note ?: 'No remarks provided.' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Record Created</th>
                                            <td>{{ $bill->created_at ? $bill->created_at->format('d-m-Y h:i A') : 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Breakdown Table -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-solid box-primary">
                                <div class="box-header with-border">
                                    <h3 class="box-title"><i class="fa fa-list-alt"></i> Cost Breakdown Details</h3>
                                </div>
                                <div class="box-body table-responsive no-padding">
                                    <table class="table table-bordered table-hover">
                                        <thead class="bg-gray">
                                            <tr>
                                                <th style="width: 50px;" class="text-center">#</th>
                                                <th style="width: 150px;">Cost Type</th>
                                                <th>Description / Particulars</th>
                                                <th class="text-right" style="width: 200px;">Billed Amount (৳)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $rowCount = count($breakdown_items); @endphp
                                            @if($rowCount > 0)
                                                @foreach($breakdown_items as $index => $item)
                                                    <tr>
                                                        <td class="text-center">{{ $index + 1 }}</td>
                                                        <td><span class="label label-default">{{ $item['type'] }}</span></td>
                                                        <td>{!! $item['desc'] !!}</td>
                                                        @if($index == 0)
                                                            <td rowspan="{{ $rowCount }}" class="text-right" style="vertical-align: middle; font-weight: bold; font-size: 18px; color: #2c3e50; background: #fafafa;">
                                                                ৳ {{ number_format($billAmount, 2) }}
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">No detailed breakdown items recorded.</td>
                                                    <td class="text-right text-bold">৳ {{ number_format($billAmount, 2) }}</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                        <tfoot class="bg-gray-light">
                                            <tr>
                                                <th colspan="3" class="text-right">Gross Total:</th>
                                                <th class="text-right text-bold" style="font-size: 15px;">৳ {{ number_format($billAmount, 2) }}</th>
                                            </tr>
                                            @if($stage == 'Final Bill' || $discount > 0)
                                            <tr>
                                                <th colspan="3" class="text-right text-red">Discount Applied:</th>
                                                <th class="text-right text-bold text-red" style="font-size: 15px;">- ৳ {{ number_format($discount, 2) }}</th>
                                            </tr>
                                            @endif
                                            <tr>
                                                <th colspan="3" class="text-right text-green">Net Payable Amount:</th>
                                                <th class="text-right text-bold text-green" style="font-size: 16px;">৳ {{ number_format($netAmount, 2) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="row">
                        <div class="col-md-12 text-right">
                            <a href="{{ route('client-bills.show', $bill->id) }}?print=1" target="_blank" class="btn btn-primary btn-flat">
                                <i class="fa fa-print"></i> Print Invoice
                            </a>
                            <a href="{{ route('client-bills.index') }}" class="btn btn-default btn-flat">
                                <i class="fa fa-arrow-left"></i> Back to Bills List
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection
