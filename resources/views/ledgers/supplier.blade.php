@extends('layouts.master')

@section('title', 'Supplier Ledger - JRC ERP')
@section('page_title', 'Supplier Ledger Statement')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <form method="GET" action="{{ route('ledgers.supplier') }}" class="form-inline">
                    <div class="form-group">
                        <label>Supplier: &nbsp;</label>
                        <select class="form-control" name="party_name">
                            <option value="">All Suppliers</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->party_name }}" {{ $supplierName == $s->party_name ? 'selected' : '' }}>{{ $s->party_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>&nbsp; From: &nbsp;</label>
                        <input type="date" class="form-control" name="fromDate" value="{{ $fromDate }}">
                    </div>
                    <div class="form-group">
                        <label>&nbsp; To: &nbsp;</label>
                        <input type="date" class="form-control" name="toDate" value="{{ $toDate }}">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Filter</button>
                    <a href="{{ route('ledgers.supplier') }}" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Reset</a>
                    <button type="button" onclick="window.print()" class="btn btn-success btn-sm pull-right"><i class="fa fa-print"></i> Print</button>
                    @if(!empty($selectedSupplierId))
                        <a href="{{ route('ledgers.supplier-print', ['supplier' => $selectedSupplierId, 'from' => $fromDate ?: date('Y-m-d', strtotime('-1 year')), 'to' => $toDate ?: date('Y-m-d')]) }}" target="_blank" class="btn btn-info btn-sm pull-right" style="margin-right: 5px;"><i class="fa fa-file-text-o"></i> Print Statement</a>
                    @endif
                </form>
            </div>
            <div class="box-body">
                <table class="table table-bordered table-striped table-datatable">
                    <thead>
                        <th>Date</th>
                        <th>Voucher No</th>
                        <th>Supplier</th>
                        <th>Bill Amount (Debit)</th>
                        <th>Paid Amount (Credit)</th>
                        <th>Due Amount</th>
                        <th>Status</th>
                    </thead>
                    <tbody>
                        @php $totBill = 0; $totPaid = 0; $totDue = 0; @endphp
                        @foreach($bills as $b)
                        @php 
                            $totBill += (float)$b->grand_total;
                            $totPaid += (float)$b->paid_amount;
                            $totDue += (float)$b->due_amount;
                        @endphp
                        <tr>
                            <td>{{ $b->bill_date }}</td>
                            <td><b>{{ $b->voucher_no }}</b></td>
                            <td>{{ $b->supplier_id }}</td>
                            <td>৳ {{ number_format((float)$b->grand_total, 2) }}</td>
                            <td><span class="text-green">৳ {{ number_format((float)$b->paid_amount, 2) }}</span></td>
                            <td><span class="text-red">৳ {{ number_format((float)$b->due_amount, 2) }}</span></td>
                            <td><span class="label label-success">{{ $b->approval_status }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray font-weight-bold">
                            <td colspan="3" class="text-right"><b>Total:</b></td>
                            <td><b>৳ {{ number_format($totBill, 2) }}</b></td>
                            <td><b>৳ {{ number_format($totPaid, 2) }}</b></td>
                            <td colspan="2"><b>৳ {{ number_format($totDue, 2) }}</b></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
