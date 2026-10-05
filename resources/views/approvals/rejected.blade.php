@extends('layouts.master')

@section('title', 'Rejected Vouchers - JRC ERP')
@section('page_title', 'Rejected Vouchers History')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-ban"></i> Rejected Supplier Bills</h3>
            </div>
            <div class="box-body">
                <table class="table table-bordered table-striped table-datatable">
                    <thead>
                        <th>Voucher #</th>
                        <th>Supplier</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Rejection Reason</th>
                    </thead>
                    <tbody>
                        @forelse($rejectedSupplierBills as $sb)
                        <tr>
                            <td><b>{{ $sb->voucher_no }}</b></td>
                            <td>{{ $sb->supplier_id }}</td>
                            <td>{{ $sb->bill_date }}</td>
                            <td>৳ {{ number_format($sb->grand_total, 2) }}</td>
                            <td><span class="text-red">{{ $sb->reject_reason }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center">No rejected bills</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
