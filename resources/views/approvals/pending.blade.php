@extends('layouts.master')

@section('title', 'JR || Corporation - Pending Approvals')
@section('page_title', 'Pending Approvals')

@section('breadcrumb')
<li class="active">Approvals</li>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-clock-o text-yellow"></i> Supplier Bills Pending Approval</h3>
            </div>
            <div class="box-body table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <th>Bill Date</th>
                        <th>Voucher No</th>
                        <th>Supplier</th>
                        <th>Total Amount</th>
                        <th>Actions</th>
                    </thead>
                    <tbody>
                        @forelse($supplierBills as $sb)
                        <tr>
                            <td>{{ $sb->bill_date }}</td>
                            <td><span class="label label-warning">{{ $sb->voucher_no }}</span></td>
                            <td><b>{{ $sb->supplier->supp_name ?? 'N/A' }}</b></td>
                            <td>৳ {{ number_format($sb->grand_total, 2) }}</td>
                            <td>
                                <form action="{{ route('approvals.approve', ['type' => 'supplier', 'id' => $sb->id]) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-xs"><i class="fa fa-check"></i> Approve</button>
                                </form>
                                <button class="btn btn-danger btn-xs" data-toggle="modal" data-target="#reject_sb_{{ $sb->id }}"><i class="fa fa-times"></i> Reject</button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center">No pending supplier bills</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modals (Outside table for valid markup) -->
@foreach($supplierBills as $sb)
<div class="modal fade" id="reject_sb_{{ $sb->id }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Reject Voucher: <b>{{ $sb->voucher_no }}</b></h4>
            </div>
            <form method="POST" action="{{ route('approvals.reject', ['type' => 'supplier', 'id' => $sb->id]) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Reason for rejection</label>
                        <textarea class="form-control" name="reject_reason" rows="3" placeholder="State reason for rejecting this bill..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Confirm Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
