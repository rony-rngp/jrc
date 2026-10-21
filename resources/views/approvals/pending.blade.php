@extends('layouts.master')

@section('title', 'Central Approval Queue - JRC ERP')
@section('page_title', 'Central Approval Queue')

@section('breadcrumb')
<li class="active">Central Approval Queue</li>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <h3 style="margin-top:0; margin-bottom: 20px; font-weight:700;">
            <i class="fa fa-check-square-o text-green"></i> Central Approval Queue
            <small style="font-size: 14px; color: #777;">Approve, Reject or Delete Pending Transactions</small>
        </h3>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {{ session('error') }}
    </div>
@endif
@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {{ session('success') }}
    </div>
@endif

<!-- 1. Client Project Payments (Incomes) -->
<div class="box box-success">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-arrow-circle-down text-green"></i> Client Project Payments (Incomes)</h3>
    </div>
    <div class="box-body table-responsive">
        <table class="table table-bordered table-striped custom-datatable">
            <thead class="bg-green">
                <tr>
                    <th>Date</th>
                    <th>Voucher/Ref</th>
                    <th>Client Name</th>
                    <th>Amount</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($clientPayments as $row)
                @php
                    $voucher = str_replace('Voucher: ', '', $row->name);
                @endphp
                <tr>
                    <td>{{ date('d-M-Y', strtotime($row->date)) }}</td>
                    <td><b>{{ $voucher }}</b></td>
                    <td>{{ $row->company_name }}</td>
                    <td class="text-bold text-green">৳ {{ number_format($row->pay_amount, 2) }}</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('approvals.action', ['action' => 'approve', 'id' => $row->id, 'type' => 'Income']) }}" class="btn btn-success btn-sm" title="Approve" onclick="return confirm('Approve this transaction and update balance?')">
                                <i class="fa fa-check"></i> Approve
                            </a>
                            <a href="{{ route('approvals.action', ['action' => 'reject', 'id' => $row->id, 'type' => 'Income']) }}" class="btn btn-warning btn-sm" title="Reject (Soft Delete)" onclick="return confirm('Reject this transaction? (It will be moved to Rejected List)')">
                                <i class="fa fa-ban"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- 2. Supplier Bills (Only Bills & Dues) -->
<div class="box box-purple" style="border-top-color: #605ca8;">
    <div class="box-header with-border">
        <h3 class="box-title" style="color: #605ca8;"><i class="fa fa-file-text-o"></i> Supplier Bills (Only Bills & Dues)</h3>
    </div>
    <div class="box-body table-responsive">
        <table class="table table-bordered table-striped custom-datatable">
            <thead style="background-color: #605ca8; color: white;">
                <tr>
                    <th>Bill Date</th>
                    <th>Voucher No</th>
                    <th>Supplier & Project</th>
                    <th>Bill Amount</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($supplierBills as $row)
                <tr>
                    <td>{{ date('d-M-Y', strtotime($row->bill_date)) }}</td>
                    <td><b>{{ $row->voucher_no }}</b></td>
                    <td>
                        <b>{{ $row->supplier->party_name ?? ($row->supplier_id == 'Cash Purchase' ? 'Cash Purchase' : $row->supplier_id) }}</b>
                        <br><small>{{ $row->project->project_name ?? 'N/A' }}</small>
                    </td>
                    <td class="text-bold" style="color:#605ca8;">৳ {{ number_format($row->grand_total, 2) }}</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('approvals.action', ['action' => 'approve', 'id' => $row->id, 'type' => 'SupplierBill']) }}" class="btn btn-success btn-sm" title="Approve" onclick="return confirm('Approve this transaction and update balance?')">
                                <i class="fa fa-check"></i> Approve
                            </a>
                            <a href="{{ route('approvals.action', ['action' => 'reject', 'id' => $row->id, 'type' => 'SupplierBill']) }}" class="btn btn-warning btn-sm" title="Reject (Soft Delete)" onclick="return confirm('Reject this transaction? (It will be moved to Rejected List)')">
                                <i class="fa fa-ban"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- 3. Supplier Payments (Expenses) -->
<div class="box box-danger">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-truck text-red"></i> Supplier Payments (Expenses)</h3>
    </div>
    <div class="box-body table-responsive">
        <table class="table table-bordered table-striped custom-datatable">
            <thead class="bg-red">
                <tr>
                    <th>Date</th>
                    <th>Voucher No</th>
                    <th>Supplier & Project</th>
                    <th>Amount Paid</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($supplierPayments as $row)
                <tr>
                    <td>{{ date('d-M-Y', strtotime($row->t_date)) }}</td>
                    <td><b>{{ $row->voucher_no }}</b></td>
                    <td>
                        <b>{{ $row->supplier->party_name ?? ($row->supplier_id == 'Cash Purchase' ? 'Cash Purchase' : $row->supplier_id) }}</b>
                        <br><small>{{ $row->project->project_name ?? 'N/A' }}</small>
                    </td>
                    <td class="text-bold text-red">৳ {{ number_format($row->amount, 2) }}</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('approvals.action', ['action' => 'approve', 'id' => $row->id, 'type' => 'Supplier']) }}" class="btn btn-success btn-sm" title="Approve" onclick="return confirm('Approve this transaction and update balance?')">
                                <i class="fa fa-check"></i> Approve
                            </a>
                            <a href="{{ route('approvals.action', ['action' => 'reject', 'id' => $row->id, 'type' => 'Supplier']) }}" class="btn btn-warning btn-sm" title="Reject (Soft Delete)" onclick="return confirm('Reject this transaction? (It will be moved to Rejected List)')">
                                <i class="fa fa-ban"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- 4. Project Direct Expenses -->
<div class="box box-warning">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-building text-orange"></i> Project Direct Expenses</h3>
    </div>
    <div class="box-body table-responsive">
        <table class="table table-bordered table-striped custom-datatable">
            <thead class="bg-orange">
                <tr>
                    <th>Date</th>
                    <th>Voucher</th>
                    <th>Project & Reason</th>
                    <th>Amount</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($projectExpenses as $row)
                <tr>
                    <td>{{ date('d-M-Y', strtotime($row->t_date)) }}</td>
                    <td><b>{{ $row->voucher_no }}</b></td>
                    <td>
                        <b>{{ $row->project->project_name ?? 'N/A' }}</b>
                        <br><small>{{ $row->expense_head }}</small>
                    </td>
                    <td class="text-bold text-orange">৳ {{ number_format($row->amount, 2) }}</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('approvals.action', ['action' => 'approve', 'id' => $row->id, 'type' => 'Project']) }}" class="btn btn-success btn-sm" title="Approve" onclick="return confirm('Approve this transaction and update balance?')">
                                <i class="fa fa-check"></i> Approve
                            </a>
                            <a href="{{ route('approvals.action', ['action' => 'reject', 'id' => $row->id, 'type' => 'Project']) }}" class="btn btn-warning btn-sm" title="Reject (Soft Delete)" onclick="return confirm('Reject this transaction? (It will be moved to Rejected List)')">
                                <i class="fa fa-ban"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- 5. General / Loan Payments -->
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-list-alt text-blue"></i> General / Loan Payments</h3>
    </div>
    <div class="box-body table-responsive">
        <table class="table table-bordered table-striped custom-datatable">
            <thead class="bg-blue">
                <tr>
                    <th>Date</th>
                    <th>Voucher</th>
                    <th>Account Head / Note</th>
                    <th>Amount</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($generalPayments as $row)
                <tr>
                    <td>{{ date('d-M-Y', strtotime($row->t_date)) }}</td>
                    <td><b>{{ $row->check_no }}</b></td>
                    <td>
                        <b>{{ $row->head->head_name ?? $row->accountHead->head_name ?? 'N/A' }}</b>
                        <br><small>{{ $row->note }}</small>
                    </td>
                    <td class="text-bold text-blue">৳ {{ number_format($row->amount, 2) }}</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('approvals.action', ['action' => 'approve', 'id' => $row->id, 'type' => 'General']) }}" class="btn btn-success btn-sm" title="Approve" onclick="return confirm('Approve this transaction and update balance?')">
                                <i class="fa fa-check"></i> Approve
                            </a>
                            <a href="{{ route('approvals.action', ['action' => 'reject', 'id' => $row->id, 'type' => 'General']) }}" class="btn btn-warning btn-sm" title="Reject (Soft Delete)" onclick="return confirm('Reject this transaction? (It will be moved to Rejected List)')">
                                <i class="fa fa-ban"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function(){
    if($.fn.DataTable) {
        $('.custom-datatable').DataTable({
            "order": [],
            "pageLength": 10
        });
    }
});
</script>
@endpush
