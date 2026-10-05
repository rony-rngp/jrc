@extends('layouts.master')

@section('title', 'Client Consolidated Ledger Summary - JRC ERP')
@section('page_title', 'Client Consolidated Ledger Summary')

@push('styles')
<style>
    .summary-header { background: #fff; padding: 20px; border-top: 4px solid #3c8dbc; margin-bottom: 20px; box-shadow: 0 1px 5px rgba(0,0,0,0.1); border-radius: 5px; }
    .table-custom th { background-color: #34495e; color: white; text-align: center; font-size: 13px; padding: 12px !important; vertical-align: middle; border: 1px solid #2c3e50; }
    .table-custom td { vertical-align: middle !important; font-size: 14px; border: 1px solid #eee !important; }
    
    .client-link { color: #3c8dbc; font-weight: bold; text-decoration: none; cursor: pointer; transition: 0.3s; }
    .client-link:hover { color: #d9534f; text-decoration: underline; }
    
    .info-card { background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; align-items: center; margin-bottom: 20px; border: 1px solid #eee; }
    .info-card .icon-box { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-right: 15px; }
    .ic-billed { background: #fdf2e9; color: #e67e22; }
    .ic-received { background: #eafaf1; color: #27ae60; }
    .ic-balance { background: #fdedec; color: #e74c3c; }
    .info-card .details h4 { margin: 0; font-size: 12px; text-transform: uppercase; color: #7f8c8d; font-weight: bold; }
    .info-card .details h2 { margin: 5px 0 0; font-size: 20px; font-weight: 800; color: #2c3e50; }

    .bal-cr { color: #27ae60; font-weight: bold; font-size: 11px; }
    .bal-dr { color: #e74c3c; font-weight: bold; font-size: 11px; }
    .grand-total { background-color: #f8f9fa !important; font-weight: bold; font-size: 15px; }
    
    @media print {
        .NoPrint { display: none !important; }
        .content-wrapper { margin-left: 0 !important; }
        .main-footer { display: none !important; }
        .table-custom th { background-color: #f2f2f2 !important; color: #000 !important; border: 1px solid #000 !important; }
        .table-custom td { border: 1px solid #000 !important; }
    }
</style>
@endpush

@section('content')
<div class="box box-primary NoPrint">
    <div class="box-header with-border text-center">
        <form method="GET" action="{{ route('top-ledgers.client-summary') }}" class="form-inline">
            <div class="form-group">
                <label>From: &nbsp;</label>
                <input type="text" name="fromDate" id="from_date" class="form-control" value="{{ $fromDateSearch }}" autocomplete="off" required/>
            </div>
            <div class="form-group" style="margin-left: 10px;">
                <label>To: &nbsp;</label>
                <input type="text" name="toDate" id="to_date" class="form-control" value="{{ $toDateSearch }}" autocomplete="off" required/>
            </div>
            <button type="submit" name="searchDate" class="btn btn-primary" style="margin-left: 10px;"><i class="fa fa-filter"></i> Filter</button>
            <button type="button" onclick="window.print()" class="btn btn-default" style="margin-left: 5px;"><i class="fa fa-print"></i> Print Summary</button>
        </form>
    </div>
</div>

<div class="box box-solid">
    <div class="box-body">
        <div class="summary-header text-center">
            <h2 style="margin: 0; font-weight: 800; color: #2c3e50; text-transform: uppercase;">Outstanding Balances Summary</h2>
            <p style="margin: 5px 0 0; color: #7f8c8d; font-size: 14px;">Report Generated for: <b>{{ date('d M Y', strtotime($sqlFromDate)) }}</b> to <b>{{ date('d M Y', strtotime($sqlToDate)) }}</b></p>
        </div>

        <div class="row NoPrint" style="margin-top: 25px;">
            <div class="col-md-4">
                <div class="info-card">
                    <div class="icon-box ic-billed"><i class="fa fa-file-text"></i></div>
                    <div class="details"><h4>Total Period Billed</h4><h2>৳ {{ number_format($total_billed_all, 2) }}</h2></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-card">
                    <div class="icon-box ic-received"><i class="fa fa-money"></i></div>
                    <div class="details"><h4>Total Period Received</h4><h2>৳ {{ number_format($total_received_all, 2) }}</h2></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-card">
                    <div class="icon-box ic-balance"><i class="fa fa-pie-chart"></i></div>
                    <div class="details"><h4>Net Outstanding</h4><h2>৳ {{ number_format(abs($total_balance_all), 2) }} {{ $total_balance_all >= 0 ? '(Dr)' : '(Cr)' }}</h2></div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="example1" class="table table-bordered table-striped table-hover table-custom">
                <thead>
                    <tr>
                        <th width="4%">SL</th>
                        <th width="25%">Client / Company Name</th>
                        <th width="15%">Mobile / Contact</th>
                        <th width="14%">Opening (Dr)</th>
                        <th width="14%">Billed (Period)</th>
                        <th width="13%">Received (Period)</th>
                        <th width="15%">Closing Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sl = 1; @endphp
                    @foreach($all_clients_summary as $row)
                    <tr>
                        <td class="text-center">{{ $sl++ }}</td>
                        <td>
                            <a href="{{ route('ledgers.client', ['client_name' => $row['company'], 'from_date' => $sqlFromDate, 'to_date' => $sqlToDate, 'view_ledger' => 1]) }}" 
                               target="_blank" class="client-link">
                                <i class="fa fa-building-o NoPrint" style="font-size:10px;"></i> {{ $row['company'] }}
                            </a>
                            <br><small class="text-muted">{{ $row['address'] }}</small>
                        </td>
                        <td class="text-center">{{ $row['contact'] ?: 'N/A' }}</td>
                        <td class="text-right">{{ number_format($row['opening'], 2) }}</td>
                        <td class="text-right text-warning" style="font-weight:bold;">{{ $row['billed'] > 0 ? number_format($row['billed'], 2) : '-' }}</td>
                        <td class="text-right text-success" style="font-weight:bold;">{{ $row['received'] > 0 ? number_format($row['received'], 2) : '-' }}</td>
                        <td class="text-right" style="font-weight: 800; background: #f9f9f9;">
                            {{ number_format(abs($row['closing']), 2) }}
                            {!! $row['closing'] > 0 ? ' <span class="bal-dr">Dr</span>' : ($row['closing'] < 0 ? ' <span class="bal-cr">Cr</span>' : '') !!}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="grand-total">
                    <tr>
                        <td colspan="4" class="text-right">GRAND TOTAL :</td>
                        <td class="text-right">৳ {{ number_format($total_billed_all, 2) }}</td>
                        <td class="text-right text-success">৳ {{ number_format($total_received_all, 2) }}</td>
                        <td class="text-right" style="font-size: 16px; background: #eee;">
                            ৳ {{ number_format(abs($total_balance_all), 2) }}
                            {!! $total_balance_all >= 0 ? ' <small class="text-dr">Dr</small>' : ' <small class="text-cr">Cr</small>' !!}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <p class="text-muted" style="margin-top: 15px; font-size: 11px;"><i>* Note: Opening balance includes client's base opening and all transactions before the selected period. In case of Final Bill, previous Running Bills are superseded.</i></p>
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
});
</script>
@endpush
