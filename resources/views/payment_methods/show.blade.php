@extends('layouts.master')

@section('title', 'Account Details - ' . $method->method_name)
@section('page_title', 'Payment Method / Bank Account Statement')

@section('breadcrumb')
  <li><a href="{{ route('payment-methods.index') }}"><i class="fa fa-university"></i> Payment Methods</a></li>
  <li class="active">{{ $method->method_name }}</li>
@endsection

@section('content')
<div class="row">
    <!-- Account Info Card -->
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-body box-profile">
                <div class="text-center" style="margin-bottom:15px;">
                    <div style="width:70px; height:70px; border-radius:50%; background:#00a65a; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:32px;">
                        <i class="fa {{ $method->account_type == 'Cash' ? 'fa-money' : ($method->account_type == 'Bank' ? 'fa-university' : 'fa-mobile') }}"></i>
                    </div>
                </div>

                <h3 class="profile-username text-center" style="font-weight:bold; color:#2c3e50;">{{ $method->method_name }}</h3>
                <p class="text-muted text-center">
                    <span class="label label-primary" style="font-size:13px;">{{ $method->account_type }} Account</span>
                </p>

                <ul class="list-group list-group-unbordered">
                    <li class="list-group-item">
                        <b><i class="fa fa-money text-green"></i> Live Balance</b> 
                        <span class="pull-right text-bold {{ $method->amount < 0 ? 'text-red' : 'text-green' }}" style="font-size:16px;">
                            ৳ {{ number_format((float)$method->amount, 2) }}
                        </span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fa fa-calendar text-muted"></i> Date Created</b> 
                        <span class="pull-right">{{ $method->date ?: 'N/A' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fa fa-arrow-circle-down text-green"></i> Total Recorded Inflow</b> 
                        <span class="pull-right text-bold text-green">৳ {{ number_format($totalInflow, 2) }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fa fa-arrow-circle-up text-red"></i> Total Recorded Outflow</b> 
                        <span class="pull-right text-bold text-red">৳ {{ number_format($totalOutflow, 2) }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fa fa-sticky-note text-warning"></i> Remarks</b> 
                        <span class="pull-right">{{ $method->note ?: '-' }}</span>
                    </li>
                </ul>

                <a href="{{ route('ledgers.account_statement') }}?method_id={{ $method->id }}&view_statement=1" class="btn btn-success btn-block btn-flat"><i class="fa fa-book"></i> <b>View Account Statement</b></a>
                <a href="{{ route('payment-methods.index') }}" class="btn btn-default btn-block btn-flat" style="margin-top:8px;"><i class="fa fa-arrow-left"></i> Back to Accounts List</a>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="col-md-8">
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab_general_tx" data-toggle="tab"><i class="fa fa-exchange"></i> General Transactions ({{ count($generalTx) }})</a></li>
                <li><a href="#tab_client_inflow" data-toggle="tab"><i class="fa fa-arrow-down text-green"></i> Client Receipts ({{ count($clientPayments) }})</a></li>
                <li><a href="#tab_supplier_outflow" data-toggle="tab"><i class="fa fa-arrow-up text-red"></i> Supplier Payments ({{ count($supplierPayments) }})</a></li>
            </ul>
            <div class="tab-content">
                <!-- General Transactions Tab -->
                <div class="tab-pane active" id="tab_general_tx">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr class="bg-gray">
                                    <th>Date</th>
                                    <th>Voucher</th>
                                    <th>Type</th>
                                    <th>Head / Details</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($generalTx as $gt)
                                    <tr>
                                        <td>{{ date('d-m-Y', strtotime($gt->t_date)) }}</td>
                                        <td><span class="label label-info">{{ $gt->check_no }}</span></td>
                                        <td>
                                            <span class="label {{ $gt->t_type == 'Receive' ? 'label-success' : 'label-danger' }}">
                                                {{ $gt->t_type }}
                                            </span>
                                        </td>
                                        <td>{{ $gt->main_head ?: $gt->trans_type }} <br><small class="text-muted">{{ $gt->note }}</small></td>
                                        <td class="text-bold {{ $gt->t_type == 'Receive' ? 'text-green' : 'text-red' }}">
                                            ৳ {{ number_format((float)$gt->amount, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">No general transactions found for this account.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Client Receipts Tab -->
                <div class="tab-pane" id="tab_client_inflow">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr class="bg-gray">
                                    <th>Date</th>
                                    <th>Voucher</th>
                                    <th>Client</th>
                                    <th>Project</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($clientPayments as $cp)
                                    <tr>
                                        <td>{{ date('d-m-Y', strtotime($cp->date)) }}</td>
                                        <td><span class="label label-info">{{ $cp->check_no ?: $cp->name }}</span></td>
                                        <td><b>{{ $cp->company_name }}</b></td>
                                        <td>{{ $cp->project_name ?: 'General' }}</td>
                                        <td class="text-bold text-green">৳ {{ number_format((float)$cp->pay_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">No client receipts found for this account.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Supplier Payments Tab -->
                <div class="tab-pane" id="tab_supplier_outflow">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr class="bg-gray">
                                    <th>Date</th>
                                    <th>Voucher</th>
                                    <th>Supplier</th>
                                    <th>Amount</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($supplierPayments as $sp)
                                    <tr>
                                        <td>{{ date('d-m-Y', strtotime($sp->t_date)) }}</td>
                                        <td><span class="label label-danger">{{ $sp->voucher_no }}</span></td>
                                        <td><b>{{ $sp->supplier->party_name ?? $sp->supplier_id }}</b></td>
                                        <td class="text-bold text-red">৳ {{ number_format((float)$sp->amount, 2) }}</td>
                                        <td>{{ $sp->note ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">No supplier payments found for this account.</td></tr>
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
