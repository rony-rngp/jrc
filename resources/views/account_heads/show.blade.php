@extends('layouts.master')

@section('title', 'Account Head - ' . $head->head_name)
@section('page_title', 'Account Head Details')

@section('breadcrumb')
  <li><a href="{{ route('account-heads.index') }}"><i class="fa fa-sitemap"></i> Account Heads</a></li>
  <li class="active">{{ $head->head_name }}</li>
@endsection

@section('content')
<div class="row">
    <!-- Account Head Details Card -->
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-body box-profile">
                <div class="text-center" style="margin-bottom:15px;">
                    <div style="width:70px; height:70px; border-radius:50%; background:#0073b7; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:32px;">
                        <i class="fa fa-folder-open"></i>
                    </div>
                </div>

                <h3 class="profile-username text-center" style="font-weight:bold; color:#2c3e50;">{{ $head->head_name }}</h3>
                <p class="text-muted text-center">
                    <span class="label {{ $head->head_type == 'Receive' ? 'label-success' : ($head->head_type == 'Expense' ? 'label-danger' : 'label-warning') }}" style="font-size:13px;">
                        Type: {{ $head->head_type }}
                    </span>
                </p>

                <ul class="list-group list-group-unbordered">
                    <li class="list-group-item">
                        <b><i class="fa fa-level-up text-primary"></i> Parent Category</b> 
                        <span class="pull-right">{{ $parent ? $parent->head_name : 'Root (None)' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fa fa-sitemap text-info"></i> Sub-Heads Count</b> 
                        <span class="pull-right badge bg-aqua">{{ count($subHeads) }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fa fa-sticky-note text-warning"></i> Description</b> 
                        <span class="pull-right">{{ $head->description ?: '-' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fa fa-arrow-circle-down text-green"></i> Total Received</b> 
                        <span class="pull-right text-bold text-green">৳ {{ number_format($totalReceive, 2) }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fa fa-arrow-circle-up text-red"></i> Total Paid</b> 
                        <span class="pull-right text-bold text-red">৳ {{ number_format($totalPayment, 2) }}</span>
                    </li>
                </ul>

                @if($head->head_type == 'Receive')
                    <a href="{{ route('ledgers.loan') }}?loan_head_id={{ $head->id }}&view_ledger=1" class="btn btn-success btn-block btn-flat"><i class="fa fa-book"></i> <b>View Loan / Ledger Statement</b></a>
                @else
                    <a href="{{ route('ledgers.office_expense') }}?head_id={{ $head->id }}&view_ledger=1" class="btn btn-success btn-block btn-flat"><i class="fa fa-book"></i> <b>View Expense Ledger</b></a>
                @endif
                <a href="{{ route('account-heads.index') }}" class="btn btn-default btn-block btn-flat" style="margin-top:8px;"><i class="fa fa-arrow-left"></i> Back to Heads List</a>
            </div>
        </div>
    </div>

    <!-- Related Transactions and Sub Heads -->
    <div class="col-md-8">
        @if(count($subHeads) > 0)
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-sitemap"></i> Sub-Heads under {{ $head->head_name }}</h3>
            </div>
            <div class="box-body">
                @foreach($subHeads as $sh)
                    <a href="{{ route('account-heads.show', $sh->id) }}" class="btn btn-default btn-sm btn-flat" style="margin: 4px;">
                        <i class="fa fa-tag text-primary"></i> <b>{{ $sh->head_name }}</b> ({{ $sh->head_type }})
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-list"></i> Transaction History ({{ count($transactions) }})</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped" id="example1">
                    <thead>
                        <tr class="bg-gray">
                            <th>Date</th>
                            <th>Voucher</th>
                            <th>Type</th>
                            <th>Account</th>
                            <th>Amount</th>
                            <th>Note / Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                            <tr>
                                <td>{{ date('d-m-Y', strtotime($tx->t_date)) }}</td>
                                <td><span class="label label-info">{{ $tx->check_no }}</span></td>
                                <td>
                                    <span class="label {{ $tx->t_type == 'Receive' ? 'label-success' : 'label-danger' }}">
                                        {{ $tx->t_type }}
                                    </span>
                                </td>
                                <td>{{ $tx->paymentMethod->method_name ?? 'Cash' }}</td>
                                <td class="text-bold {{ $tx->t_type == 'Receive' ? 'text-green' : 'text-red' }}">
                                    ৳ {{ number_format((float)$tx->amount, 2) }}
                                </td>
                                <td>{{ $tx->note ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No transactions found for this account head.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
