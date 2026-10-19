@extends('layouts.master')

@section('title', 'All Employees Summary Ledger - JRC ERP')
@section('page_title', 'All Employees Summary Ledger')

@push('styles')
<style>
    .summary-header { background: #fff; padding: 20px; border-top: 4px solid #00a65a; margin-bottom: 20px; box-shadow: 0 1px 5px rgba(0,0,0,0.1); border-radius: 5px; }
    .table-custom th { background-color: #34495e; color: white; text-align: center; font-size: 13px; padding: 12px !important; vertical-align: middle; border: 1px solid #2c3e50; }
    .table-custom td { vertical-align: middle !important; font-size: 14px; border: 1px solid #eee !important; }
    
    .emp-link { color: #3c8dbc; font-weight: bold; text-decoration: none; cursor: pointer; transition: 0.3s; }
    .emp-link:hover { color: #d9534f; text-decoration: underline; }
    
    .info-card { background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; align-items: center; margin-bottom: 20px; border: 1px solid #eee; }
    .info-card .icon-box { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-right: 15px; }
    .ic-earned { background: #eafaf1; color: #27ae60; }
    .ic-paid { background: #fdedec; color: #e74c3c; }
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
        .emp-link { color: #000 !important; text-decoration: none !important; }
        .dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate { display: none !important; }
    }
</style>
@endpush

@section('content')
<div class="box box-success NoPrint">
    <div class="box-header with-border text-center">
        <form method="GET" action="{{ route('top-ledgers.employee-ledger') }}" class="form-inline">
            <div class="form-group">
                <label>From: &nbsp;</label>
                <input type="text" name="fromDate" id="from_date" class="form-control" value="{{ $fromDateSearch }}" autocomplete="off" required/>
            </div>
            <div class="form-group" style="margin-left: 10px;">
                <label>To: &nbsp;</label>
                <input type="text" name="toDate" id="to_date" class="form-control" value="{{ $toDateSearch }}" autocomplete="off" required/>
            </div>
            <button type="submit" name="searchDate" class="btn btn-success" style="margin-left: 10px;"><i class="fa fa-filter"></i> Filter</button>
            <button type="button" onclick="window.print()" class="btn btn-default" style="margin-left: 5px;"><i class="fa fa-print"></i> Print Summary</button>
        </form>
    </div>
</div>

<div class="box box-solid">
    <div class="box-body">
        <div class="summary-header text-center">
            <h2 style="margin: 0; font-weight: 800; color: #2c3e50; text-transform: uppercase;">Employee Balances Summary</h2>
            <p style="margin: 5px 0 0; color: #7f8c8d; font-size: 14px;">Statement Period: <b>{{ date('d M Y', strtotime($sqlFromDate)) }}</b> to <b>{{ date('d M Y', strtotime($sqlToDate)) }}</b></p>
        </div>

        <div class="row NoPrint" style="margin-top: 25px;">
            <div class="col-md-4">
                <div class="info-card">
                    <div class="icon-box ic-earned"><i class="fa fa-briefcase"></i></div>
                    <div class="details"><h4>Period Earned (Payable)</h4><h2>৳ {{ number_format($total_earned_all, 2) }}</h2></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-card">
                    <div class="icon-box ic-paid"><i class="fa fa-credit-card"></i></div>
                    <div class="details"><h4>Period Paid / Advance</h4><h2>৳ {{ number_format($total_paid_all, 2) }}</h2></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-card">
                    <div class="icon-box ic-balance"><i class="fa fa-balance-scale"></i></div>
                    <div class="details"><h4>Net Payable Balance</h4><h2>৳ {{ number_format(abs($total_balance_all), 2) }} {{ $total_balance_all >= 0 ? '(Cr)' : '(Dr)' }}</h2></div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="example1" class="table table-bordered table-striped table-hover table-custom">
                <thead>
                    <tr>
                        <th width="4%">SL</th>
                        <th width="25%">Employee Name & Designation</th>
                        <th width="15%">Mobile No</th>
                        <th width="14%">Opening Balance</th>
                        <th width="14%">Earned (+)</th>
                        <th width="13%">Paid (-)</th>
                        <th width="15%">Closing Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sl = 1; @endphp
                    @foreach($all_emps_summary as $row)
                    <tr>
                        <td class="text-center">{{ $sl++ }}</td>
                        <td>
                            <a href="{{ route('employees.show', $row['id']) }}" class="emp-link">
                                <i class="fa fa-user NoPrint" style="font-size:10px;"></i> {{ $row['name'] }}
                            </a>
                            <br><small class="text-muted">{{ $row['position'] }}</small>
                        </td>
                        <td class="text-center">{{ $row['contact'] ?: 'N/A' }}</td>
                        <td class="text-right">
                            {{ number_format(abs($row['opening']), 2) }}
                            {!! $row['opening'] > 0 ? ' <small class="text-success">Cr</small>' : ($row['opening'] < 0 ? ' <small class="text-danger">Dr</small>' : '') !!}
                        </td>
                        <td class="text-right text-success" style="font-weight:bold;">{{ $row['earned'] > 0 ? number_format($row['earned'], 2) : '-' }}</td>
                        <td class="text-right text-danger" style="font-weight:bold;">{{ $row['paid'] > 0 ? number_format($row['paid'], 2) : '-' }}</td>
                        <td class="text-right" style="font-weight: 800; background: #f9f9f9;">
                            {{ number_format(abs($row['closing']), 2) }}
                            {!! $row['closing'] > 0 ? ' <span class="bal-cr">Cr</span>' : ($row['closing'] < 0 ? ' <span class="bal-dr">Dr</span>' : '') !!}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="grand-total">
                    <tr>
                        <td colspan="4" class="text-right">GRAND TOTAL :</td>
                        <td class="text-right text-success">৳ {{ number_format($total_earned_all, 2) }}</td>
                        <td class="text-right text-danger">৳ {{ number_format($total_paid_all, 2) }}</td>
                        <td class="text-right" style="font-size: 16px; background: #eee;">
                            ৳ {{ number_format(abs($total_balance_all), 2) }}
                            {!! $total_balance_all >= 0 ? ' <small class="text-success">Cr</small>' : ' <small class="text-danger">Dr</small>' !!}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <p class="text-muted" style="margin-top: 15px; font-size: 11px;"><i>* Note: Opening balance represents the cumulative position (Salary earned vs Paid) before the selected period. <b>(Cr)</b> means Company Owes (Payable), <b>(Dr)</b> means Advance given.</i></p>
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
            window._dt_orig_len_emp = dt.page.len();
            dt.page.len(-1).draw();
        }
    });
    window.addEventListener('afterprint', function() {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#example1') && window._dt_orig_len_emp !== undefined) {
            var dt = $('#example1').DataTable();
            dt.page.len(window._dt_orig_len_emp).draw();
        }
    });
});
</script>
@endpush
