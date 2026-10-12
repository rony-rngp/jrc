@extends('layouts.master')

@section('title', 'Project Master Ledger - JRC ERP')
@section('page_title', 'Project Master Ledger')

@push('styles')
<style>
    /* Professional Accounting UI CSS */
    .ledger-container { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 25px; border-top: 5px solid #00a65a; }
    
    .client-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ecf0f5; padding-bottom: 20px; margin-bottom: 20px; }
    .client-title h2 { margin: 0 0 8px 0; font-weight: 700; color: #2c3e50; font-size: 28px; text-transform: uppercase; letter-spacing: 1px; }
    .client-title p { margin: 0; color: #555; font-size: 15px; }
    
    .status-box-main { padding: 20px 30px; border-radius: 8px; text-align: right; min-width: 250px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .status-loss { background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; } 
    .status-profit { background: linear-gradient(135deg, #00a65a, #008d4c); color: white; } 
    .status-settled { background: linear-gradient(135deg, #95a5a6, #7f8c8d); color: white; }
    
    .status-box-main h4 { margin: 0 0 5px 0; font-size: 15px; font-weight: 700; letter-spacing: 1.5px; opacity: 0.9; }
    .status-box-main h1 { margin: 0; font-size: 36px; font-weight: 800; color: #fff; }
    
    /* Summary Cards */
    .summary-row { display: flex; gap: 20px; margin-bottom: 25px; flex-wrap: wrap; }
    .summary-card { flex: 1; min-width: 200px; background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0; box-shadow: 0 2px 5px rgba(0,0,0,0.02); display: flex; align-items: center; }
    .summary-card .icon { font-size: 40px; margin-right: 15px; opacity: 0.8; }
    .summary-card .details p { margin: 0; font-size: 13px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; }
    .summary-card .details h3 { margin: 5px 0 0 0; font-size: 24px; font-weight: 800; }
    
    .card-open { border-left: 5px solid #3c8dbc; } .card-open .icon { color: #3c8dbc; } .card-open h3 { color: #2980b9; }
    .card-recv { border-left: 5px solid #00a65a; } .card-recv .icon { color: #00a65a; } .card-recv h3 { color: #008d4c; }
    .card-bill { border-left: 5px solid #e74c3c; } .card-bill .icon { color: #e74c3c; } .card-bill h3 { color: #c0392b; }
    .card-due { border-left: 5px solid #f39c12; } .card-due .icon { color: #f39c12; } .card-due h3 { color: #d68910; }

    /* Ledger Table */
    .table-ledger { border: 1px solid #ddd; width: 100%; margin-bottom: 0; }
    .table-ledger thead th { background: #34495e; color: #fff; font-weight: 600; text-transform: uppercase; font-size: 13px; padding: 12px; border: 1px solid #2c3e50; text-align: center; }
    .table-ledger tbody td { padding: 12px; font-size: 14px; border: 1px solid #eee; vertical-align: middle; }
    .table-ledger tbody tr:hover { background: #f9f9f9; }
    
    .row-bf { background-color: #fffde7 !important; font-weight: bold; color: #5d4037 !important; }
    .row-footer { background-color: #ecf0f5 !important; font-weight: bold; border-top: 2px solid #333; }
    
    .amt { text-align: right; font-family: 'Courier New', Courier, monospace; font-weight: 700; font-size: 15px; }
    .text-dr { color: #c0392b; } 
    .text-cr { color: #00a65a; } 
    .text-info { color: #2980b9; }
    
    .badge-type { padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
    .badge-rev { background: #d5f5e3; color: #1e8449; }
    .badge-exp { background: #fdebd0; color: #d35400; }
    .badge-sup { background: #f2d7d5; color: #922b21; }
    .badge-info { background: #d9edf7; color: #31708f; } 
    .badge-settled { background: #f4f4f4; color: #999; } 

    .cashflow-info { background: #f4f7f9; padding: 10px 15px; border-radius: 5px; display: inline-block; font-size: 13px; font-weight: bold; color: #555; margin-top: 10px; border: 1px solid #ddd; }

    @media print {
        @page {
            size: A4;
            margin: 8mm;
        }
        body, html { 
            background: #fff !important; 
            color: #000 !important;
            font-size: 12px !important;
        }
        .main-header, .main-sidebar, .content-header, .search-box, .btn, .main-footer, .no-print { 
            display: none !important; 
        }
        .content-wrapper, .wrapper, .content { 
            margin: 0 !important; 
            padding: 0 !important; 
            background: #fff !important;
            border: none !important;
        }
        .ledger-container { 
            border: none !important; 
            box-shadow: none !important; 
            padding: 0 !important; 
            margin: 0 !important;
        }
        .client-header {
            display: flex !important;
            justify-content: space-between !important;
            border-bottom: 2px solid #333 !important;
            padding-bottom: 12px !important;
            margin-bottom: 15px !important;
        }
        .client-title h2 {
            font-size: 22px !important;
            color: #000 !important;
            margin-bottom: 5px !important;
        }
        .client-title p {
            color: #333 !important;
            font-size: 13px !important;
        }
        .status-box-main {
            border: 2px solid #333 !important;
            box-shadow: none !important;
            padding: 10px 15px !important;
            min-width: 200px !important;
            background: #f4f6f7 !important;
            color: #000 !important;
        }
        .status-box-main h4 {
            color: #333 !important;
            font-size: 13px !important;
        }
        .status-box-main h1 {
            color: #000 !important;
            font-size: 24px !important;
        }
        .status-box-main small {
            color: #555 !important;
        }
        .summary-row {
            display: flex !important;
            gap: 10px !important;
            margin-bottom: 15px !important;
        }
        .summary-card {
            flex: 1 !important;
            border: 1px solid #999 !important;
            padding: 8px 12px !important;
            box-shadow: none !important;
            background: #fff !important;
        }
        .summary-card .icon {
            display: none !important;
        }
        .summary-card .details p {
            font-size: 10px !important;
            color: #555 !important;
        }
        .summary-card .details h3 {
            font-size: 16px !important;
            margin-top: 2px !important;
            color: #000 !important;
        }
        .table-responsive {
            overflow: visible !important;
        }
        .table-ledger {
            width: 100% !important;
            border-collapse: collapse !important;
            border: 1px solid #000 !important;
            font-size: 11px !important;
        }
        .table-ledger thead th { 
            background: #e6e6e6 !important; 
            color: #000 !important; 
            border: 1px solid #000 !important; 
            padding: 6px 8px !important;
            font-size: 11px !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .table-ledger tbody td, .table-ledger tfoot td { 
            border: 1px solid #666 !important; 
            padding: 5px 8px !important;
            font-size: 11px !important;
        }
        .table-ledger tfoot tr {
            background: #eee !important;
            font-weight: bold !important;
            border-top: 2px solid #000 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .row-bf {
            background: #fff9db !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .amt {
            font-size: 11px !important;
        }
        .badge-type {
            border: 1px solid #999 !important;
            padding: 1px 4px !important;
            font-size: 9px !important;
        }
    }
</style>
@endpush

@section('content')
<div class="box box-success search-box">
    <div class="box-body" style="padding: 20px;">
        <form method="GET" action="{{ route('ledgers.project') }}">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="text-muted">Select Project</label>
                        <select class="form-control select2" name="project_id" style="width:100%;" required>
                            <option value="">-- Choose a Project --</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ $project_id == $p->id ? 'selected' : '' }}>
                                    {{ $p->project_name }} - ({{ $p->company_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="text-muted">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $from_date }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="text-muted">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $to_date }}" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group" style="padding-top: 25px;">
                        <button type="submit" name="view_ledger" value="1" class="btn btn-success btn-block">
                            <i class="fa fa-filter"></i> Generate
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@if($show_report && $proj_info)
<div class="ledger-container">
    <div class="client-header">
        <div class="client-title">
            <h2>{{ $project_name }}</h2>
            <p><i class="fa fa-briefcase"></i> Client/Company: <b>{{ $company_name }}</b></p>
            <p style="margin-top: 5px;"><i class="fa fa-calendar-check-o"></i> Statement Period: <b>{{ date('d M Y', strtotime($from_date)) }}</b> to <b>{{ date('d M Y', strtotime($to_date)) }}</b></p>
            
            <div class="cashflow-info">
                <i class="fa fa-info-circle text-blue"></i> Actual Cash In: <span class="text-success"> ৳ {{ number_format($cash_in, 2) }}</span> &nbsp;|&nbsp; Actual Cash Out: <span class="text-danger">৳ {{ number_format($cash_out, 2) }}</span>
            </div>
        </div>
        <div class="status-box-main {{ $status_color }}">
            <h4><i class="fa {{ $status_icon }}"></i> {{ $status_text }}</h4>
            <h1>{{ number_format(abs($current_balance), 2) }} <small style="color:rgba(255,255,255,0.8); font-size:18px;">{{ $status_label }}</small></h1>
        </div>
    </div>

    <div class="summary-row">
        <div class="summary-card card-open">
            <div class="icon"><i class="fa fa-file-text-o"></i></div>
            <div class="details">
                <p>Total Revenue (Billed)</p>
                <h3 class="text-info">{{ number_format($tot_revenue, 2) }}</h3>
            </div>
        </div>
        <div class="summary-card card-recv">
            <div class="icon"><i class="fa fa-money"></i></div>
            <div class="details">
                <p>Total Received (Collection)</p>
                <h3 class="text-cr">{{ number_format($cash_in, 2) }}</h3>
            </div>
        </div>
        <div class="summary-card card-bill">
            <div class="icon"><i class="fa fa-pie-chart"></i></div>
            <div class="details">
                <p>Total Cost/Exp (Lifetime Dr)</p>
                <h3 class="text-dr">{{ number_format($tot_expense, 2) }}</h3>
            </div>
        </div>
        <div class="summary-card card-due">
            <div class="icon"><i class="fa fa-exclamation-circle"></i></div>
            <div class="details">
                <p>Client Due (Bill - Recv)</p>
                <h3 class="text-warning">{{ number_format($client_due, 2) }}</h3>
            </div>
        </div>
    </div>

    <div class="no-print" style="text-align: right; margin-bottom: 15px;">
        <button onclick="window.print()" class="btn btn-default btn-lg"><i class="fa fa-print"></i> Print Ledger</button>
    </div>

    <div class="table-responsive">
        <table class="table table-ledger">
            <thead>
                <tr>
                    <th width="10%">Date</th>
                    <th width="15%">Ref / Category</th>
                    <th width="30%">Details</th>
                    <th width="11%" class="text-right">Cost/Exp (Dr)</th>
                    <th width="11%" class="text-right" style="background:#f4f6f7; color:#7f8c8d;">Bill (Not added)</th>
                    <th width="12%" class="text-right" style="background:#27ae60; color:#fff;">Received (Cash)</th>
                    <th width="11%" class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                <tr class="row-bf">
                    <td colspan="3" class="text-right"><i class="fa fa-share"></i> Brought Forward (Bal till {{ date('d-M-Y', strtotime($from_date . ' -1 day')) }}):</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="amt">{!! number_format(abs($brought_forward), 2) . ($brought_forward < 0 ? ' <small class="text-dr">Dr</small>' : ' <small class="text-cr">Cr</small>') !!}</td>
                </tr>

                @php
                    $sum_dr = 0; $sum_cr = 0; $sum_recv = 0;
                    $r_balance = $brought_forward;
                @endphp

                @if(count($ledger_data) > 0)
                    @foreach($ledger_data as $row)
                        @php
                            $sum_dr += $row['debit'];
                            $sum_cr += $row['credit']; 
                            $sum_recv += $row['received'];
                            
                            $r_balance = $r_balance + $row['received'] - $row['debit'];
                            
                            $dr_text = $row['debit'] > 0 ? number_format($row['debit'], 2) : '-';
                            $cr_text = $row['credit'] > 0 ? number_format($row['credit'], 2) : '-';
                            $recv_text = $row['received'] > 0 ? number_format($row['received'], 2) : '-';
                            
                            $badge = 'badge-exp'; 
                            if($row['type'] == 'Project Revenue') $badge = 'badge-rev';
                            if($row['type'] == 'Running Bill') $badge = 'badge-settled';
                            if($row['type'] == 'Material Cost') $badge = 'badge-sup';
                            if($row['type'] == 'Fund Received') $badge = 'badge-info';
                            
                            $bal_str = number_format(abs($r_balance), 2);
                            if($r_balance > 0) $bal_str .= " <small class='text-cr'>Cr</small>"; 
                            elseif($r_balance < 0) $bal_str .= " <small class='text-dr'>Dr</small>"; 
                            else $bal_str = "0.00";

                            $cr_td_style = ($row['credit'] > 0) ? "color:#95a5a6; background:#fbfcfc;" : "";
                        @endphp
                        <tr>
                            <td>{{ date('d-M-Y', strtotime($row['date'])) }}</td>
                            <td>
                                <b>{{ $row['ref'] }}</b><br>
                                <span class="badge-type {{ $badge }}">{{ $row['type'] }}</span>
                            </td>
                            <td>{!! $row['details'] !!}</td>
                            <td class="amt text-dr">{{ $dr_text }}</td>
                            <td class="amt" style="{{ $cr_td_style }}">{{ $cr_text }}</td>
                            <td class="amt" style="color:#27ae60; font-weight:900;">{{ $recv_text }}</td>
                            <td class="amt">{!! $bal_str !!}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7" class="text-center text-muted" style="padding:30px;">
                            <i class="fa fa-info-circle fa-2x"></i><br>No transactions found for this period.
                        </td>
                    </tr>
                @endif
            </tbody>
            <tfoot class="row-footer">
                <tr>
                    <td colspan="3" class="text-right" style="padding:15px; font-size:15px;"><b>Total for Period :</b></td>
                    <td class="amt text-dr" style="padding:15px;">{{ number_format($sum_dr, 2) }}</td>
                    <td class="amt" style="padding:15px; color:#7f8c8d;">{{ number_format($sum_cr, 2) }}</td>
                    <td class="amt" style="padding:15px; color:#27ae60;">{{ number_format($sum_recv, 2) }}</td>
                    <td class="amt" style="padding:15px; background:#fff;">
                        Closing: {!! number_format(abs($r_balance), 2) . ($r_balance < 0 ? ' <small class="text-dr">Dr</small>' : ' <small class="text-cr">Cr</small>') !!}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
$(document).ready(function(){ 
    if($.fn.select2) { $('.select2').select2(); } 
});
</script>
@endpush
