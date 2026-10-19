@extends('layouts.master')

@section('title', 'All Suppliers Summary Ledger - JRC ERP')
@section('page_title', 'All Suppliers Summary Ledger')

@push('styles')
<style>
    .summary-header { background: #fff; padding: 20px; border-top: 4px solid #dd4b39; margin-bottom: 20px; box-shadow: 0 1px 5px rgba(0,0,0,0.1); border-radius: 5px; }
    .table-custom th { background-color: #34495e; color: white; text-align: center; font-size: 13px; padding: 12px !important; vertical-align: middle; border: 1px solid #2c3e50; }
    .table-custom td { vertical-align: middle !important; font-size: 14px; border: 1px solid #eee !important; }
    .supplier-link { color: #3c8dbc; font-weight: bold; text-decoration: none; transition: 0.3s; cursor: pointer; }
    .supplier-link:hover { color: #d9534f; text-decoration: underline; }
    
    .info-card { background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; align-items: center; margin-bottom: 20px; border: 1px solid #eee; }
    .info-card .icon-box { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-right: 15px; }
    .ic-billed { background: #fdedec; color: #e74c3c; }
    .ic-paid { background: #eafaf1; color: #27ae60; }
    .ic-balance { background: #f4f6f7; color: #2c3e50; }
    .info-card .details h4 { margin: 0; font-size: 12px; text-transform: uppercase; color: #7f8c8d; font-weight: bold; }
    .info-card .details h2 { margin: 5px 0 0; font-size: 20px; font-weight: 800; color: #2c3e50; }

    .bal-cr { color: #27ae60; font-weight: bold; font-size: 11px; }
    .bal-dr { color: #e74c3c; font-weight: bold; font-size: 11px; }
    .grand-total { background-color: #f8f9fa !important; font-weight: bold; font-size: 15px; }
    
    @media print {
        .NoPrint, .no-print { display: none !important; }
        .content-wrapper { margin-left: 0 !important; }
        .main-footer { display: none !important; }
        .table-custom th { background-color: #f2f2f2 !important; color: #000 !important; border: 1px solid #000 !important; }
        .table-custom td { border: 1px solid #000 !important; }
        a[href]:after { content: "" !important; }
        abbr[title]:after { content: "" !important; }
        .supplier-link { color: #000 !important; text-decoration: none !important; }
        .dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate { display: none !important; }
    }
</style>
@endpush

@section('content')
<div class="box box-danger NoPrint">
    <div class="box-header with-border text-center">
        <form method="GET" action="{{ route('top-ledgers.supplier-summary') }}" class="form-inline">
            <div class="form-group">
                <label>From: &nbsp;</label>
                <input type="text" name="fromDate" id="from_date" class="form-control" value="{{ $fromDateSearch }}" autocomplete="off" required/>
            </div>
            <div class="form-group" style="margin-left: 10px;">
                <label>To: &nbsp;</label>
                <input type="text" name="toDate" id="to_date" class="form-control" value="{{ $toDateSearch }}" autocomplete="off" required/>
            </div>
            <button type="submit" name="searchDate" class="btn btn-danger" style="margin-left: 10px;"><i class="fa fa-search"></i> Show Summary</button>
            <button type="button" onclick="window.print()" class="btn btn-default" style="margin-left: 5px;"><i class="fa fa-print"></i> Print</button>
        </form>
    </div>
</div>

<div class="box box-solid">
    <div class="box-body">
        <div class="summary-header text-center">
            <h2 style="margin: 0; font-weight: 800; color: #1a2a3a; text-transform: uppercase;">Consolidated Supplier Balances</h2>
            <p style="margin: 5px 0 0; color: #666; font-size: 14px;">Statement Period: <b>{{ date('d M Y', strtotime($sqlFromDate)) }}</b> to <b>{{ date('d M Y', strtotime($sqlToDate)) }}</b></p>
        </div>

        <div class="row NoPrint" style="margin-top: 20px;">
            <div class="col-md-4">
                <div class="info-card">
                    <div class="icon-box ic-billed"><i class="fa fa-shopping-cart"></i></div>
                    <div class="details"><h4>Period Billed</h4><h2>৳ {{ number_format($gt_billed, 2) }}</h2></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-card">
                    <div class="icon-box ic-paid"><i class="fa fa-check-square-o"></i></div>
                    <div class="details"><h4>Period Paid</h4><h2>৳ {{ number_format($gt_paid, 2) }}</h2></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-card">
                    <div class="icon-box ic-balance"><i class="fa fa-balance-scale"></i></div>
                    <div class="details"><h4>Total Outstanding</h4><h2>৳ {{ number_format(abs($gt_closing), 2); }} {{ $gt_closing >= 0 ? '(Dr)' : '(Cr)' }}</h2></div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="example1" class="table table-bordered table-striped table-hover table-custom">
                <thead>
                    <tr>
                        <th width="4%">SL</th>
                        <th width="20%">Supplier Name</th>
                        <th width="18%">Address</th>
                        <th width="12%">Mobile No</th>
                        <th width="12%">Opening (Dr)</th>
                        <th width="11%">Billed (+)</th>
                        <th width="11%">Paid (-)</th>
                        <th width="12%">Closing Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sl = 1; @endphp
                    @foreach($supplier_list as $row)
                    <tr>
                        <td class="text-center">{{ $sl++ }}</td>
                        <td>
                            <a href="{{ route('ledgers.supplier', ['supplier_id' => $row['id'], 'from_date' => $sqlFromDate, 'to_date' => $sqlToDate, 'view_ledger' => 1]) }}" 
                               target="_blank" class="supplier-link">
                                <i class="fa fa-truck NoPrint" style="font-size:10px;"></i> {{ $row['name'] }}
                            </a>
                        </td>
                        <td>{{ $row['address'] }}</td>
                        <td class="text-center">{{ $row['phone'] ?: 'N/A' }}</td>
                        <td class="text-right">{{ number_format($row['opening'], 2) }}</td>
                        <td class="text-right text-danger">{{ $row['billed'] > 0 ? number_format($row['billed'], 2) : '-' }}</td>
                        <td class="text-right text-success" style="font-weight:bold;">{{ $row['paid'] > 0 ? number_format($row['paid'], 2) : '-' }}</td>
                        <td class="text-right" style="font-weight: 800; background: #fcfcfc;">
                            {{ number_format(abs($row['closing']), 2) }}
                            {!! $row['closing'] > 0 ? ' <span class="bal-dr">Dr</span>' : ($row['closing'] < 0 ? ' <span class="bal-cr">Cr</span>' : '') !!}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="grand-total">
                    <tr>
                        <td colspan="5" class="text-right">GRAND TOTAL:</td>
                        <td class="text-right" style="color: #dd4b39;">৳ {{ number_format($gt_billed, 2) }}</td>
                        <td class="text-right" style="color: #00a65a;">৳ {{ number_format($gt_paid, 2) }}</td>
                        <td class="text-right" style="font-size: 16px;">
                            ৳ {{ number_format(abs($gt_closing), 2) }}
                            {{ $gt_closing > 0 ? ' (Dr)' : ($gt_closing < 0 ? ' (Cr)' : '') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <p class="text-muted" style="margin-top: 15px; font-size: 11px;"><i>* Note: Opening balance represents the cumulative position before the selected start date. Click on the supplier name to view the detailed transaction history.</i></p>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function(){
    if ($.fn.DataTable.isDataTable('#example1')) {
        $('#example1').DataTable().destroy();
    }
    $('#example1').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "pageLength": 50,
        "order": [[ 1, "asc" ]]
    });

    window.addEventListener('beforeprint', function() {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#example1')) {
            var dt = $('#example1').DataTable();
            window._dt_orig_len_sup = dt.page.len();
            dt.page.len(-1).draw();
        }
    });
    window.addEventListener('afterprint', function() {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#example1') && window._dt_orig_len_sup !== undefined) {
            var dt = $('#example1').DataTable();
            dt.page.len(window._dt_orig_len_sup).draw();
        }
    });
});
</script>
@endpush
