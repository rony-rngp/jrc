@extends('layouts.master')

@section('title', 'Rejected Vouchers Archive - JRC ERP')
@section('page_title', 'Rejected Vouchers Archive')

@section('breadcrumb')
<li class="active">Rejected Vouchers Archive</li>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <h3 style="margin-top:0; margin-bottom: 20px; font-weight:700;">
            <i class="fa fa-ban text-red"></i> Rejected Vouchers Archive
            <small style="font-size: 14px; color: #777;">Restore or permanently delete rejected entries</small>
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

<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab_supplier" data-toggle="tab">Supplier Payments</a></li>
        <li><a href="#tab_supplier_bills" data-toggle="tab">Supplier Bills</a></li>
        <li><a href="#tab_project" data-toggle="tab">Project Expenses</a></li>
        <li><a href="#tab_general" data-toggle="tab">General/Loan</a></li>
        <li><a href="#tab_income" data-toggle="tab">Income/Collections</a></li>
    </ul>

    <div class="tab-content">
        <!-- Tab 1: Supplier Payments -->
        <div class="tab-pane active" id="tab_supplier">
            <div class="table-responsive">
                <table class="table table-bordered table-striped custom-datatable">
                    <thead>
                        <tr class="bg-red">
                            <th>Date</th>
                            <th>Voucher</th>
                            <th>Supplier</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rejectedSupplierPayments as $row)
                        <tr>
                            <td>{{ date('d-M-Y', strtotime($row->t_date)) }}</td>
                            <td><b>{{ $row->voucher_no }}</b></td>
                            <td>{{ $row->supplier->party_name ?? ($row->supplier_id == 'Cash Purchase' ? 'Cash Purchase' : $row->supplier_id) }}</td>
                            <td class="text-bold text-red">৳ {{ number_format($row->amount, 2) }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('approvals.action', ['action' => 'restore', 'id' => $row->id, 'type' => 'Supplier']) }}" class="btn btn-info btn-sm" title="Restore to Pending" onclick="return confirm('Restore this to Pending list?')">
                                        <i class="fa fa-refresh"></i> Restore
                                    </a>
                                    <a href="{{ route('approvals.action', ['action' => 'delete', 'id' => $row->id, 'type' => 'Supplier']) }}" class="btn btn-danger btn-sm" title="Delete Permanent" onclick="return confirm('PERMANENTLY DELETE this record?')">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 2: Supplier Bills -->
        <div class="tab-pane" id="tab_supplier_bills">
            <div class="table-responsive">
                <table class="table table-bordered table-striped custom-datatable">
                    <thead>
                        <tr style="background-color: #605ca8; color: white;">
                            <th>Bill Date</th>
                            <th>Voucher</th>
                            <th>Supplier</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rejectedSupplierBills as $row)
                        <tr>
                            <td>{{ date('d-M-Y', strtotime($row->bill_date)) }}</td>
                            <td><b>{{ $row->voucher_no }}</b></td>
                            <td>{{ $row->supplier->party_name ?? ($row->supplier_id == 'Cash Purchase' ? 'Cash Purchase' : $row->supplier_id) }}</td>
                            <td class="text-bold" style="color: #605ca8;">৳ {{ number_format($row->grand_total, 2) }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('approvals.action', ['action' => 'restore', 'id' => $row->id, 'type' => 'SupplierBill']) }}" class="btn btn-info btn-sm" title="Restore to Pending" onclick="return confirm('Restore this to Pending list?')">
                                        <i class="fa fa-refresh"></i> Restore
                                    </a>
                                    <a href="{{ route('approvals.action', ['action' => 'delete', 'id' => $row->id, 'type' => 'SupplierBill']) }}" class="btn btn-danger btn-sm" title="Delete Permanent" onclick="return confirm('PERMANENTLY DELETE this record?')">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 3: Project Expenses -->
        <div class="tab-pane" id="tab_project">
            <div class="table-responsive">
                <table class="table table-bordered table-striped custom-datatable">
                    <thead>
                        <tr class="bg-orange">
                            <th>Date</th>
                            <th>Voucher</th>
                            <th>Project</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rejectedProjectExpenses as $row)
                        <tr>
                            <td>{{ date('d-M-Y', strtotime($row->t_date)) }}</td>
                            <td><b>{{ $row->voucher_no }}</b></td>
                            <td>{{ $row->project->project_name ?? 'N/A' }}</td>
                            <td class="text-bold text-orange">৳ {{ number_format($row->amount, 2) }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('approvals.action', ['action' => 'restore', 'id' => $row->id, 'type' => 'Project']) }}" class="btn btn-info btn-sm" title="Restore to Pending" onclick="return confirm('Restore this to Pending list?')">
                                        <i class="fa fa-refresh"></i> Restore
                                    </a>
                                    <a href="{{ route('approvals.action', ['action' => 'delete', 'id' => $row->id, 'type' => 'Project']) }}" class="btn btn-danger btn-sm" title="Delete Permanent" onclick="return confirm('PERMANENTLY DELETE this record?')">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 4: General/Loan -->
        <div class="tab-pane" id="tab_general">
            <div class="table-responsive">
                <table class="table table-bordered table-striped custom-datatable">
                    <thead>
                        <tr class="bg-blue">
                            <th>Date</th>
                            <th>Voucher</th>
                            <th>Note</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rejectedGeneralPayments as $row)
                        <tr>
                            <td>{{ date('d-M-Y', strtotime($row->t_date)) }}</td>
                            <td><b>{{ $row->check_no }}</b></td>
                            <td>{{ $row->note }}</td>
                            <td class="text-bold text-blue">৳ {{ number_format($row->amount, 2) }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('approvals.action', ['action' => 'restore', 'id' => $row->id, 'type' => 'General']) }}" class="btn btn-info btn-sm" title="Restore to Pending" onclick="return confirm('Restore this to Pending list?')">
                                        <i class="fa fa-refresh"></i> Restore
                                    </a>
                                    <a href="{{ route('approvals.action', ['action' => 'delete', 'id' => $row->id, 'type' => 'General']) }}" class="btn btn-danger btn-sm" title="Delete Permanent" onclick="return confirm('PERMANENTLY DELETE this record?')">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 5: Income/Collections -->
        <div class="tab-pane" id="tab_income">
            <div class="table-responsive">
                <table class="table table-bordered table-striped custom-datatable">
                    <thead>
                        <tr class="bg-green">
                            <th>Date</th>
                            <th>Company</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rejectedClientPayments as $row)
                        <tr>
                            <td>{{ date('d-M-Y', strtotime($row->date)) }}</td>
                            <td>{{ $row->company_name }}</td>
                            <td class="text-bold text-green">৳ {{ number_format($row->pay_amount, 2) }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('approvals.action', ['action' => 'restore', 'id' => $row->id, 'type' => 'Income']) }}" class="btn btn-info btn-sm" title="Restore to Pending" onclick="return confirm('Restore this to Pending list?')">
                                        <i class="fa fa-refresh"></i> Restore
                                    </a>
                                    <a href="{{ route('approvals.action', ['action' => 'delete', 'id' => $row->id, 'type' => 'Income']) }}" class="btn btn-danger btn-sm" title="Delete Permanent" onclick="return confirm('PERMANENTLY DELETE this record?')">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function(){
    if($.fn.DataTable) {
        $('.custom-datatable').DataTable({
            "order": []
        });
    }
});
</script>
@endpush
