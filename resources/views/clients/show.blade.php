@extends('layouts.master')

@section('title', 'Client Details - ' . $client->company_name)
@section('page_title', 'Client Profile & Details')

@section('breadcrumb')
  <li><a href="{{ route('clients.index') }}"><i class="fa fa-users"></i> Clients</a></li>
  <li class="active">{{ $client->company_name }}</li>
@endsection

@section('content')
<div class="row">
    <!-- Client Info Card -->
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-body box-profile">
                <div class="text-center" style="margin-bottom:15px;">
                    <div style="width:70px; height:70px; border-radius:50%; background:#3c8dbc; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:32px;">
                        <i class="fa fa-building-o"></i>
                    </div>
                </div>

                <h3 class="profile-username text-center" style="font-weight:bold; color:#2c3e50;">{{ $client->company_name }}</h3>
                <p class="text-muted text-center"><i class="fa fa-user"></i> {{ $client->persion_name ?: 'No Contact Person' }}</p>

                <ul class="list-group list-group-unbordered">
                    <li class="list-group-item">
                        <b><i class="fa fa-phone text-primary"></i> Phone</b> <span class="pull-right">{{ $client->contact ?: 'N/A' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fa fa-map-marker text-danger"></i> Address</b> <span class="pull-right text-right" style="max-width:200px;">{{ $client->address ?: 'N/A' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fa fa-calendar text-muted"></i> Date Created</b> <span class="pull-right">{{ $client->date ?: 'N/A' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fa fa-sticky-note text-warning"></i> Note</b> <span class="pull-right">{{ $client->note ?: '-' }}</span>
                    </li>
                </ul>

                <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-warning btn-block btn-flat"><i class="fa fa-edit"></i> <b>Edit Client Profile</b></a>
                <a href="{{ route('ledgers.client') }}?client_name={{ urlencode($client->company_name) }}&view_ledger=1" class="btn btn-success btn-block btn-flat" style="margin-top:8px;"><i class="fa fa-book"></i> <b>View Statement / Ledger</b></a>
                <a href="{{ route('clients.index') }}" class="btn btn-default btn-block btn-flat" style="margin-top:8px;"><i class="fa fa-arrow-left"></i> Back to Clients List</a>
            </div>
        </div>
    </div>

    <!-- Financial Summary & Related Data -->
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
                <div class="info-box bg-blue">
                    <span class="info-box-icon"><i class="fa fa-file-text-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Billed</span>
                        <span class="info-box-number" style="font-size:15px;">৳ {{ number_format($totalBilled, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 col-xs-6">
                <div class="info-box bg-green">
                    <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Received</span>
                        <span class="info-box-number" style="font-size:15px;">৳ {{ number_format($totalReceived, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 col-xs-6">
                <div class="info-box {{ $netDue > 0 ? 'bg-red' : 'bg-green' }}">
                    <span class="info-box-icon"><i class="fa fa-money"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ $netDue > 0 ? 'Net Due' : 'Advance' }}</span>
                        <span class="info-box-number" style="font-size:15px;">৳ {{ number_format(abs($netDue), 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabbed Navigation for Projects, Bills, Payments -->
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab_projects" data-toggle="tab"><i class="fa fa-cubes"></i> Projects ({{ count($projects) }})</a></li>
                <li><a href="#tab_bills" data-toggle="tab"><i class="fa fa-file-invoice"></i> Bills Generated ({{ count($bills) }})</a></li>
                <li><a href="#tab_payments" data-toggle="tab"><i class="fa fa-money"></i> Collections ({{ count($payments) }})</a></li>
            </ul>
            <div class="tab-content">
                <!-- Projects Tab -->
                <div class="tab-pane active" id="tab_projects">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr class="bg-gray">
                                    <th>Project Name</th>
                                    <th>Estimated Value</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projects as $p)
                                    <tr>
                                        <td><b>{{ $p->project_name }}</b><br><small class="text-muted">{{ $p->address }}</small></td>
                                        <td>৳ {{ number_format((float)$p->estimated_value, 2) }}</td>
                                        <td><span class="label {{ $p->project_status == 'Complete' ? 'label-success' : 'label-primary' }}">{{ $p->project_status }}</span></td>
                                        <td><a href="{{ route('projects.show', $p->id) }}" class="btn btn-info btn-xs btn-flat"><i class="fa fa-eye"></i> View</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">No projects found for this client.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Bills Tab -->
                <div class="tab-pane" id="tab_bills">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr class="bg-gray">
                                    <th>Date</th>
                                    <th>Project</th>
                                    <th>Stage / Note</th>
                                    <th>Bill Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bills as $b)
                                    <tr>
                                        <td>{{ date('d-m-Y', strtotime($b->date)) }}</td>
                                        <td><b>{{ $b->project_name }}</b></td>
                                        <td>{{ $b->note }}</td>
                                        <td class="text-bold">৳ {{ number_format((float)$b->bill_amount, 2) }}</td>
                                        <td><a href="{{ route('client-bills.show', $b->id) }}" target="_blank" class="btn btn-primary btn-xs btn-flat"><i class="fa fa-eye"></i> Invoice</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">No bills generated yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Collections Tab -->
                <div class="tab-pane" id="tab_payments">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr class="bg-gray">
                                    <th>Date</th>
                                    <th>Voucher</th>
                                    <th>Account</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $pm)
                                    <tr>
                                        <td>{{ date('d-m-Y', strtotime($pm->date)) }}</td>
                                        <td><span class="label label-info">{{ $pm->check_no ?: $pm->name }}</span></td>
                                        <td>{{ $pm->method_name }}</td>
                                        <td class="text-bold text-green">৳ {{ number_format((float)$pm->pay_amount, 2) }}</td>
                                        <td><a href="{{ route('receive-vouchers.show', $pm->id) }}?type=Client" target="_blank" class="btn btn-default btn-xs btn-flat"><i class="fa fa-print"></i> Voucher</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">No collections recorded yet.</td></tr>
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
