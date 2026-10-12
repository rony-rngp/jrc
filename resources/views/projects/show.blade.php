@extends('layouts.master')

@section('title', 'Project Details - ' . $project->project_name)
@section('page_title', 'Project Overview & Statement')

@section('breadcrumb')
  <li><a href="{{ route('projects.index') }}"><i class="fa fa-cubes"></i> Projects</a></li>
  <li class="active">{{ $project->project_name }}</li>
@endsection

@section('content')
<div class="row">
    <!-- Project Info Card -->
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-body box-profile">
                <div class="text-center" style="margin-bottom:15px;">
                    <div style="width:70px; height:70px; border-radius:50%; background:#00c0ef; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:32px;">
                        <i class="fa fa-building"></i>
                    </div>
                </div>

                <h3 class="profile-username text-center" style="font-weight:bold; color:#2c3e50;">{{ $project->project_name }}</h3>
                <p class="text-muted text-center">
                    <i class="fa fa-user"></i> <b>Client:</b> {{ $project->company_name }}
                </p>

                <div class="text-center" style="margin-bottom:15px;">
                    <span class="label {{ $project->project_status == 'Complete' ? 'label-success' : 'label-primary' }}" style="font-size:13px; padding:4px 10px;">
                        Status: {{ $project->project_status }}
                    </span>
                </div>

                <ul class="list-group list-group-unbordered">
                    <li class="list-group-item">
                        <b><i class="fa fa-money text-green"></i> Estimated Budget</b> 
                        <span class="pull-right text-bold text-green">৳ {{ number_format((float)$project->estimated_value, 2) }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fa fa-map-marker text-danger"></i> Site Location</b> 
                        <span class="pull-right text-right" style="max-width:180px;">{{ $project->address ?: 'N/A' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fa fa-calendar text-muted"></i> Start Date</b> 
                        <span class="pull-right">{{ $project->date ?: 'N/A' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fa fa-sticky-note text-warning"></i> Remarks / Note</b> 
                        <span class="pull-right">{{ $project->note ?: '-' }}</span>
                    </li>
                </ul>

                <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-warning btn-block btn-flat"><i class="fa fa-edit"></i> <b>Edit Project</b></a>
                <a href="{{ route('ledgers.project') }}?project_id={{ $project->id }}&view_ledger=1" class="btn btn-success btn-block btn-flat" style="margin-top:8px;"><i class="fa fa-book"></i> <b>View Project Ledger</b></a>
                <a href="{{ route('projects.index') }}" class="btn btn-default btn-block btn-flat" style="margin-top:8px;"><i class="fa fa-arrow-left"></i> Back to Projects List</a>
            </div>
        </div>
    </div>

    <!-- Financial Analytics & Breakdown Tabs -->
    <div class="col-md-8">
        <!-- 4 Metric Cards -->
        <div class="row">
            <div class="col-sm-3 col-xs-6">
                <div class="info-box bg-purple">
                    <span class="info-box-icon"><i class="fa fa-cubes"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Materials</span>
                        <span class="info-box-number" style="font-size:14px;">৳ {{ number_format($totalMaterialCost, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 col-xs-6">
                <div class="info-box bg-orange">
                    <span class="info-box-icon"><i class="fa fa-wrench"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Direct/Gen Exp</span>
                        <span class="info-box-number" style="font-size:14px;">৳ {{ number_format($totalDirectExpense + $totalGeneralExpense, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 col-xs-6">
                <div class="info-box bg-red">
                    <span class="info-box-icon"><i class="fa fa-calculator"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Cost</span>
                        <span class="info-box-number" style="font-size:14px;">৳ {{ number_format($totalCostIncurred, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 col-xs-6">
                <div class="info-box bg-green">
                    <span class="info-box-icon"><i class="fa fa-money"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Client Billed</span>
                        <span class="info-box-number" style="font-size:14px;">৳ {{ number_format($totalClientBilled, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabbed Navigation -->
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab_materials" data-toggle="tab"><i class="fa fa-truck"></i> Material Bills ({{ count($supplierBills) }})</a></li>
                <li><a href="#tab_direct_expenses" data-toggle="tab"><i class="fa fa-wrench"></i> Direct Expenses ({{ count($projectExpenses) }})</a></li>
                <li><a href="#tab_client_bills" data-toggle="tab"><i class="fa fa-file-invoice"></i> Client Bills ({{ count($clientBills) }})</a></li>
                <li><a href="#tab_client_rcv" data-toggle="tab"><i class="fa fa-check-circle"></i> Receipts ({{ count($clientPayments) }})</a></li>
            </ul>
            <div class="tab-content">
                <!-- Material Bills -->
                <div class="tab-pane active" id="tab_materials">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr class="bg-gray">
                                    <th>Date</th>
                                    <th>Voucher</th>
                                    <th>Supplier</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($supplierBills as $sb)
                                    @php
                                        $items = $sb->items->map(function($i){ return ($i->category->cat_name ?? 'Item') . " (x{$i->qty})"; })->implode(', ');
                                    @endphp
                                    <tr>
                                        <td>{{ date('d-m-Y', strtotime($sb->bill_date)) }}</td>
                                        <td><span class="label label-info">{{ $sb->voucher_no }}</span></td>
                                        <td><b>{{ $sb->supplier->party_name ?? $sb->supplier_id }}</b></td>
                                        <td><small>{{ $items ?: 'Materials' }}</small></td>
                                        <td class="text-bold">৳ {{ number_format((float)$sb->grand_total, 2) }}</td>
                                        <td><a href="{{ route('supplier-bills.show', $sb->id) }}" target="_blank" class="btn btn-primary btn-xs btn-flat"><i class="fa fa-eye"></i> View</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">No material bills for this project.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Direct Expenses -->
                <div class="tab-pane" id="tab_direct_expenses">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr class="bg-gray">
                                    <th>Date</th>
                                    <th>Voucher</th>
                                    <th>Expense Head</th>
                                    <th>Amount</th>
                                    <th>Note</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projectExpenses as $pe)
                                    <tr>
                                        <td>{{ date('d-m-Y', strtotime($pe->t_date)) }}</td>
                                        <td><span class="label label-warning">{{ $pe->voucher_no }}</span></td>
                                        <td><b>{{ $pe->expense_head }}</b></td>
                                        <td class="text-bold text-red">৳ {{ number_format((float)$pe->amount, 2) }}</td>
                                        <td>{{ $pe->note ?: '-' }}</td>
                                        <td><a href="{{ route('payment-vouchers.show', $pe->id) }}?type=Project" target="_blank" class="btn btn-default btn-xs btn-flat"><i class="fa fa-print"></i> Voucher</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">No direct project expenses recorded.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Client Bills -->
                <div class="tab-pane" id="tab_client_bills">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr class="bg-gray">
                                    <th>Date</th>
                                    <th>Stage</th>
                                    <th>Bill Amount</th>
                                    <th>Note</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($clientBills as $cb)
                                    <tr>
                                        <td>{{ date('d-m-Y', strtotime($cb->date)) }}</td>
                                        <td><span class="label label-primary">{{ $cb->payment_status ?: 'Bill' }}</span></td>
                                        <td class="text-bold">৳ {{ number_format((float)$cb->bill_amount, 2) }}</td>
                                        <td>{{ $cb->note }}</td>
                                        <td><a href="{{ route('client-bills.show', $cb->id) }}" target="_blank" class="btn btn-primary btn-xs btn-flat"><i class="fa fa-eye"></i> Invoice</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">No client bills generated.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Receipts -->
                <div class="tab-pane" id="tab_client_rcv">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr class="bg-gray">
                                    <th>Date</th>
                                    <th>Voucher</th>
                                    <th>Account</th>
                                    <th>Amount Received</th>
                                    <th>Note</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($clientPayments as $cp)
                                    <tr>
                                        <td>{{ date('d-m-Y', strtotime($cp->date)) }}</td>
                                        <td><span class="label label-info">{{ $cp->check_no ?: $cp->name }}</span></td>
                                        <td>{{ $cp->method_name }}</td>
                                        <td class="text-bold text-green">৳ {{ number_format((float)$cp->pay_amount, 2) }}</td>
                                        <td>{{ $cp->note ?: '-' }}</td>
                                        <td><a href="{{ route('receive-vouchers.show', $cp->id) }}?type=Client" target="_blank" class="btn btn-default btn-xs btn-flat"><i class="fa fa-print"></i> Voucher</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">No receipts recorded specifically for this project.</td></tr>
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
