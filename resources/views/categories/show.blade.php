@extends('layouts.master')

@section('title', 'Product Details - ' . $category->cat_name)
@section('page_title', 'Product / Material Details')

@section('breadcrumb')
  <li><a href="{{ route('categories.index') }}"><i class="fa fa-cubes"></i> Products / Categories</a></li>
  <li class="active">{{ $category->cat_name }}</li>
@endsection

@section('content')
<div class="row">
    <!-- Category Info Card -->
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-body box-profile">
                <div class="text-center" style="margin-bottom:15px;">
                    <div style="width:70px; height:70px; border-radius:50%; background:#00a65a; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:32px;">
                        <i class="fa fa-tag"></i>
                    </div>
                </div>

                <h3 class="profile-username text-center" style="font-weight:bold; color:#2c3e50;">{{ $category->cat_name }}</h3>
                <p class="text-muted text-center"><i class="fa fa-info-circle"></i> Material Category Item</p>

                <ul class="list-group list-group-unbordered">
                    <li class="list-group-item">
                        <b><i class="fa fa-calendar text-muted"></i> Date Added</b> <span class="pull-right">{{ $category->date ?: 'N/A' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fa fa-sticky-note text-warning"></i> Description / Note</b> <span class="pull-right">{{ $category->note ?: '-' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fa fa-cube text-primary"></i> Total Purchased Qty</b> <span class="pull-right text-bold">{{ number_format($totalQty, 2) }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fa fa-money text-green"></i> Total Purchase Spent</b> <span class="pull-right text-bold text-green">৳ {{ number_format($totalAmount, 2) }}</span>
                    </li>
                </ul>

                <a href="{{ route('categories.index') }}" class="btn btn-default btn-block btn-flat"><i class="fa fa-arrow-left"></i> Back to Products List</a>
            </div>
        </div>
    </div>

    <!-- Purchase Usage History -->
    <div class="col-md-8">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-history"></i> Purchase & Usage History</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped" id="example1">
                    <thead>
                        <tr class="bg-gray">
                            <th>Bill Date</th>
                            <th>Voucher</th>
                            <th>Supplier</th>
                            <th>Project</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $it)
                            <tr>
                                <td>{{ !empty($it->bill->bill_date) ? date('d-m-Y', strtotime($it->bill->bill_date)) : '-' }}</td>
                                <td><a href="{{ route('supplier-bills.show', $it->bill_id) }}" target="_blank"><span class="label label-info">{{ $it->bill->voucher_no ?? 'Bill #'.$it->bill_id }}</span></a></td>
                                <td><b>{{ $it->bill->supplier->party_name ?? ($it->bill->supplier_id ?? 'N/A') }}</b></td>
                                <td>{{ $it->bill->project->project_name ?? 'General' }}</td>
                                <td class="text-bold">{{ number_format((float)$it->qty, 2) }}</td>
                                <td>৳ {{ number_format((float)$it->unit_price, 2) }}</td>
                                <td class="text-bold text-green">৳ {{ number_format((float)$it->total_price, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">No purchase records found for this product category.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
