@extends('layouts.master')

@section('title', 'Pending Approvals - JRC ERP')
@section('page_title', 'Pending Voucher Approvals')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-clock-o"></i> Pending Supplier Bills</h3>
            </div>
            <div class="box-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <th>Voucher #</th>
                        <th>Supplier</th>
                        <th>Bill Date</th>
                        <th>Total Amount</th>
                        <th>Actions</th>
                    </thead>
                    <tbody>
                        @forelse($supplierBills as $sb)
                        <tr>
                            <td><b>{{ $sb->voucher_no }}</b></td>
                            <td>{{ $sb->supplier_id }}</td>
                            <td>{{ $sb->bill_date }}</td>
                            <td>৳ {{ number_format($sb->grand_total, 2) }}</td>
                            <td>
                                <form action="{{ route('approvals.approve', ['type' => 'supplier', 'id' => $sb->id]) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-xs"><i class="fa fa-check"></i> Approve</button>
                                </form>
                                <button class="btn btn-danger btn-xs" data-toggle="modal" data-target="#reject_sb_{{ $sb->id }}"><i class="fa fa-times"></i> Reject</button>
                            </td>
                        </tr>

                        <!-- Reject Modal -->
                        <div class="modal fade" id="reject_sb_{{ $sb->id }}">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title">Reject Voucher</h4>
                                    </div>
                                    <form method="POST" action="{{ route('approvals.reject', ['type' => 'supplier', 'id' => $sb->id]) }}">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>Reason for rejection</label>
                                                <textarea class="form-control" name="reject_reason" required></textarea>
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
                        @empty
                        <tr><td colspan="5" class="text-center">No pending supplier bills</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
