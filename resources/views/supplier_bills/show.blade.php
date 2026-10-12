@extends('layouts.master')

@section('title', 'Supplier Bill Details - ' . $bill->voucher_no)

@section('content')
<section class="content-header">
    <h1>
        <i class="fa fa-file-text-o text-primary"></i> Supplier Purchase Bill
        <small>Voucher #{{ $bill->voucher_no }}</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{ route('supplier-bills.index') }}">Supplier Bills</a></li>
        <li class="active">{{ $bill->voucher_no }}</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Bill Summary & Actions</h3>
                    <div class="box-tools pull-right">
                        <a href="{{ route('supplier-bills.show', $bill->id) }}?print=1" target="_blank" class="btn btn-primary btn-sm btn-flat">
                            <i class="fa fa-print"></i> Print Invoice
                        </a>
                        <a href="{{ route('supplier-bills.edit', $bill->id) }}" class="btn btn-success btn-sm btn-flat">
                            <i class="fa fa-edit"></i> Edit Bill
                        </a>
                        <a href="{{ route('supplier-bills.index') }}" class="btn btn-default btn-sm btn-flat">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <!-- Top Metric Cards -->
                    <div class="row">
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="info-box bg-aqua">
                                <span class="info-box-icon"><i class="fa fa-file-text-o"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Voucher No</span>
                                    <span class="info-box-number">{{ $bill->voucher_no }}</span>
                                    <span class="progress-description">Date: {{ date('d M, Y', strtotime($bill->bill_date)) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="info-box bg-blue">
                                <span class="info-box-icon"><i class="fa fa-money"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Grand Total</span>
                                    <span class="info-box-number">৳ {{ number_format((float)$bill->grand_total, 2) }}</span>
                                    <span class="progress-description">Type: {{ $bill->bill_type }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="info-box bg-green">
                                <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Paid Amount</span>
                                    <span class="info-box-number">৳ {{ number_format((float)$real_paid, 2) }}</span>
                                    <span class="progress-description">Status: {{ $status }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-12">
                            <div class="info-box {{ $real_due > 0 ? 'bg-red' : 'bg-gray' }}">
                                <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Due Amount</span>
                                    <span class="info-box-number">৳ {{ number_format((float)$real_due, 2) }}</span>
                                    <span class="progress-description">{{ $real_due > 0 ? 'Payment Pending' : 'Fully Settled' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Meta Information -->
                    <div class="row" style="margin-top: 15px;">
                        <div class="col-md-6">
                            <div class="box box-solid box-default">
                                <div class="box-header with-border bg-gray-light">
                                    <h3 class="box-title"><i class="fa fa-truck text-primary"></i> Supplier & Project Info</h3>
                                </div>
                                <div class="box-body no-padding">
                                    <table class="table table-bordered table-striped">
                                        <tr>
                                            <th style="width: 35%;">Supplier Name</th>
                                            <td>
                                                @if($bill->supplier_id == 'Cash Purchase')
                                                    <span class="label label-default" style="font-size:13px;">Cash Purchase</span>
                                                @elseif($supplier)
                                                    <a href="{{ route('suppliers.show', $supplier->id) }}"><b>{{ $supplier->party_name }}</b></a>
                                                    @if($supplier->phone) <br><small class="text-muted"><i class="fa fa-phone"></i> {{ $supplier->phone }}</small> @endif
                                                    @if($supplier->address) <br><small class="text-muted"><i class="fa fa-map-marker"></i> {{ $supplier->address }}</small> @endif
                                                @else
                                                    <b>{{ $bill->supplier_id }}</b>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Project</th>
                                            <td>
                                                @if($project)
                                                    <a href="{{ route('projects.show', $project->id) }}"><b>{{ $project->project_name }}</b></a>
                                                    @if($project->project_location) <br><small class="text-muted"><i class="fa fa-map-marker"></i> {{ $project->project_location }}</small> @endif
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Approval Status</th>
                                            <td>
                                                @if($bill->approval_status == 'Pending')
                                                    <span class="label label-warning"><i class="fa fa-clock-o"></i> Pending</span>
                                                @elseif($bill->approval_status == 'Rejected')
                                                    <span class="label label-danger"><i class="fa fa-times-circle"></i> Rejected</span>
                                                @else
                                                    <span class="label label-success"><i class="fa fa-check-circle"></i> Approved</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Bill Date</th>
                                            <td><b>{{ date('d-m-Y', strtotime($bill->bill_date)) }}</b></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="box box-solid box-default">
                                <div class="box-header with-border bg-gray-light">
                                    <h3 class="box-title"><i class="fa fa-credit-card text-success"></i> Payment & Note Details</h3>
                                </div>
                                <div class="box-body no-padding">
                                    <table class="table table-bordered table-striped">
                                        <tr>
                                            <th style="width: 35%;">Bill Entry Type</th>
                                            <td><span class="label label-info">{{ $bill->bill_type }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Payment Method</th>
                                            <td>
                                                @if($pay_method)
                                                    <b>{{ $pay_method }}</b>
                                                @else
                                                    <span class="text-muted">None / Direct Credit</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Trx / Ref No</th>
                                            <td>{{ $trx_id ?: 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>General Note</th>
                                            <td>{{ $bill->note ?: 'No specific note recorded.' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-solid box-primary">
                                <div class="box-header with-border">
                                    <h3 class="box-title"><i class="fa fa-cubes"></i> Purchased Items & Materials</h3>
                                </div>
                                <div class="box-body table-responsive no-padding">
                                    <table class="table table-bordered table-hover">
                                        <thead class="bg-gray">
                                            <tr>
                                                <th style="width: 50px;" class="text-center">#</th>
                                                <th>Material Category</th>
                                                <th>Particulars / Item Description</th>
                                                <th class="text-center" style="width: 100px;">Unit</th>
                                                <th class="text-right" style="width: 120px;">Quantity</th>
                                                <th class="text-right" style="width: 140px;">Unit Rate (৳)</th>
                                                <th class="text-right" style="width: 160px;">Total (৳)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $calcTotal = 0; @endphp
                                            @forelse($bill->items as $idx => $item)
                                                @php $calcTotal += (float)$item->amount; @endphp
                                                <tr>
                                                    <td class="text-center">{{ $idx + 1 }}</td>
                                                    <td>
                                                        <span class="label label-default">{{ $item->category->cat_name ?? $item->category_id ?? 'General' }}</span>
                                                    </td>
                                                    <td><b>{{ $item->item_name ?: 'N/A' }}</b></td>
                                                    <td class="text-center">{{ $item->unit ?: '-' }}</td>
                                                    <td class="text-right">{{ number_format((float)$item->qty, 2) }}</td>
                                                    <td class="text-right">{{ number_format((float)$item->rate, 2) }}</td>
                                                    <td class="text-right text-bold">৳ {{ number_format((float)$item->amount, 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted">No item details recorded for this bill.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot class="bg-gray-light">
                                            <tr>
                                                <th colspan="6" class="text-right">Grand Total:</th>
                                                <th class="text-right text-bold" style="font-size: 15px;">৳ {{ number_format((float)$bill->grand_total, 2) }}</th>
                                            </tr>
                                            <tr>
                                                <th colspan="6" class="text-right text-green">Total Paid:</th>
                                                <th class="text-right text-bold text-green" style="font-size: 15px;">৳ {{ number_format((float)$real_paid, 2) }}</th>
                                            </tr>
                                            <tr>
                                                <th colspan="6" class="text-right {{ $real_due > 0 ? 'text-red' : 'text-green' }}">Net Due:</th>
                                                <th class="text-right text-bold {{ $real_due > 0 ? 'text-red' : 'text-green' }}" style="font-size: 15px;">৳ {{ number_format((float)$real_due, 2) }}</th>
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
                            <a href="{{ route('supplier-bills.show', $bill->id) }}?print=1" target="_blank" class="btn btn-primary btn-flat">
                                <i class="fa fa-print"></i> Print Invoice
                            </a>
                            <a href="{{ route('supplier-bills.edit', $bill->id) }}" class="btn btn-success btn-flat">
                                <i class="fa fa-edit"></i> Edit Bill
                            </a>
                            <a href="{{ route('supplier-bills.index') }}" class="btn btn-default btn-flat">
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
