@extends('layouts.master')

@section('title', 'Supplier Details - ' . $supplier->party_name)
@section('page_title', 'Supplier Profile & Statement')

@section('breadcrumb')
  <li><a href="{{ route('suppliers.index') }}"><i class="fa fa-truck"></i> Suppliers</a></li>
  <li class="active">{{ $supplier->party_name }}</li>
@endsection

@section('content')
<div class="row">
    <!-- Supplier Profile Info -->
    <div class="col-md-4">
        <div class="box box-danger">
            <div class="box-body box-profile">
                <div class="text-center" style="margin-bottom:15px;">
                    <div style="width:70px; height:70px; border-radius:50%; background:#dd4b39; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:32px;">
                        <i class="fa fa-truck"></i>
                    </div>
                </div>

                <h3 class="profile-username text-center" style="font-weight:bold; color:#2c3e50;">{{ $supplier->party_name }}</h3>
                <p class="text-muted text-center"><i class="fa fa-tag"></i> {{ $supplier->party_type ?: 'General Supplier' }}</p>

                <ul class="list-group list-group-unbordered">
                    <li class="list-group-item">
                        <b><i class="fa fa-phone text-primary"></i> Phone</b> <span class="pull-right">{{ $supplier->party_phone ?: 'N/A' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fa fa-map-marker text-danger"></i> Address</b> <span class="pull-right text-right" style="max-width:200px;">{{ $supplier->address ?: 'N/A' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fa fa-calendar text-muted"></i> Date Created</b> <span class="pull-right">{{ $supplier->date ?: 'N/A' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fa fa-sticky-note text-warning"></i> Note</b> <span class="pull-right">{{ $supplier->note ?: '-' }}</span>
                    </li>
                </ul>

                <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-warning btn-block btn-flat"><i class="fa fa-edit"></i> <b>Edit Supplier Profile</b></a>
                <a href="{{ route('ledgers.supplier') }}?supplier_id={{ $supplier->id }}&view_ledger=1" class="btn btn-success btn-block btn-flat" style="margin-top:8px;"><i class="fa fa-book"></i> <b>View Statement / Ledger</b></a>
                <a href="{{ route('suppliers.index') }}" class="btn btn-default btn-block btn-flat" style="margin-top:8px;"><i class="fa fa-arrow-left"></i> Back to Suppliers List</a>
            </div>
        </div>
    </div>

    <!-- Financial Summary & Related Bills/Payments -->
    <div class="col-md-8">
        <!-- Metric Snapshot Boxes -->
        <div class="row">
            <div class="col-sm-3 col-xs-6">
                <div class="info-box bg-aqua">
                    <span class="info-box-icon"><i class="fa fa-balance-scale"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Opening</span>
                        <span class="info-box-number" style="font-size:15px;">৳ {{ number_format($opening, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 col-xs-6">
                <div class="info-box bg-purple">
                    <span class="info-box-icon"><i class="fa fa-shopping-cart"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Purchases</span>
                        <span class="info-box-number" style="font-size:15px;">৳ {{ number_format($totalBilled, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 col-xs-6">
                <div class="info-box bg-green">
                    <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Paid</span>
                        <span class="info-box-number" style="font-size:15px;">৳ {{ number_format($totalPaid, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 col-xs-6">
                <div class="info-box {{ $netDue > 0 ? 'bg-red' : 'bg-green' }}">
                    <span class="info-box-icon"><i class="fa fa-money"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ $netDue > 0 ? 'Due Payable' : 'Advance Paid' }}</span>
                        <span class="info-box-number" style="font-size:15px;">৳ {{ number_format(abs($netDue), 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabbed Navigation for Purchase Bills and Supplier Payments -->
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab_purchases" data-toggle="tab"><i class="fa fa-shopping-cart"></i> Purchase Bills ({{ count($bills) }})</a></li>
                <li><a href="#tab_supplier_payments" data-toggle="tab"><i class="fa fa-money"></i> Payments Made ({{ count($payments) }})</a></li>
            </ul>
            <div class="tab-content">
                <!-- Purchases Tab -->
                <div class="tab-pane active" id="tab_purchases">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr class="bg-gray">
                                    <th>Date</th>
                                    <th>Voucher</th>
                                    <th>Project</th>
                                    <th>Total</th>
                                    <th>Paid</th>
                                    <th>Due</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bills as $b)
                                    <tr>
                                        <td>{{ date('d-m-Y', strtotime($b->bill_date)) }}</td>
                                        <td><span class="label label-info">{{ $b->voucher_no }}</span></td>
                                        <td><b>{{ $b->project->project_name ?? 'General' }}</b></td>
                                        <td class="text-bold">৳ {{ number_format((float)$b->grand_total, 2) }}</td>
                                        <td class="text-bold text-green">৳ {{ number_format((float)$b->paid_amount, 2) }}</td>
                                        <td class="text-bold text-red">৳ {{ number_format((float)$b->due_amount, 2) }}</td>
                                        <td><a href="{{ route('supplier-bills.show', $b->id) }}" target="_blank" class="btn btn-primary btn-xs btn-flat"><i class="fa fa-eye"></i> View</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted">No purchase bills found for this supplier.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Payments Tab -->
                <div class="tab-pane" id="tab_supplier_payments">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr class="bg-gray">
                                    <th>Date</th>
                                    <th>Voucher</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Note</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $pm)
                                    <tr>
                                        <td>{{ date('d-m-Y', strtotime($pm->t_date)) }}</td>
                                        <td><span class="label label-info">{{ $pm->voucher_no }}</span></td>
                                        <td>{{ $pm->method->method_name ?? 'Cash' }}</td>
                                        <td class="text-bold text-red">৳ {{ number_format((float)$pm->amount, 2) }}</td>
                                        <td>{{ $pm->note ?: '-' }}</td>
                                        <td><a href="{{ route('payment-vouchers.show', $pm->id) }}?type=Supplier" target="_blank" class="btn btn-default btn-xs btn-flat"><i class="fa fa-print"></i> Voucher</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">No payments recorded yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
