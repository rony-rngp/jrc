@extends('layouts.master')

@section('title', 'Client Master Ledger - JRC ERP')
@section('page_title', 'Client Master Ledger')

@push('styles')
<style>
    /* Professional Accounting UI CSS */
    .ledger-container { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 25px; border-top: 5px solid #3c8dbc; }
    
    .client-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ecf0f5; padding-bottom: 20px; margin-bottom: 20px; }
    .client-title h2 { margin: 0 0 8px 0; font-weight: 700; color: #2c3e50; font-size: 28px; text-transform: uppercase; letter-spacing: 1px; }
    .client-title p { margin: 0; color: #555; font-size: 15px; }
    
    .status-box-main { padding: 20px 30px; border-radius: 8px; text-align: right; min-width: 250px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .status-due { background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; } 
    .status-advance { background: linear-gradient(135deg, #2ecc71, #27ae60); color: white; } 
    .status-settled { background: linear-gradient(135deg, #95a5a6, #7f8c8d); color: white; }
    
    .status-box-main h4 { margin: 0 0 5px 0; font-size: 15px; font-weight: 700; letter-spacing: 1.5px; opacity: 0.9; }
    .status-box-main h1 { margin: 0; font-size: 36px; font-weight: 800; color: #fff; }
    
    /* Summary Cards */
    .summary-row { display: flex; gap: 20px; margin-bottom: 25px; flex-wrap: wrap; }
    .summary-card { flex: 1; min-width: 220px; background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0; box-shadow: 0 2px 5px rgba(0,0,0,0.02); display: flex; align-items: center; }
    .summary-card .icon { font-size: 40px; margin-right: 15px; opacity: 0.8; }
    .summary-card .details p { margin: 0; font-size: 13px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; }
    .summary-card .details h3 { margin: 5px 0 0 0; font-size: 24px; font-weight: 800; }
    
    .card-open { border-left: 5px solid #f39c12; } .card-open .icon { color: #f39c12; } .card-open h3 { color: #d35400; }
    .card-bill { border-left: 5px solid #e74c3c; } .card-bill .icon { color: #e74c3c; } .card-bill h3 { color: #c0392b; }
    .card-recv { border-left: 5px solid #3498db; } .card-recv .icon { color: #3498db; } .card-recv h3 { color: #2980b9; }

    /* Ledger Table */
    .table-ledger { border: 1px solid #ddd; width: 100%; margin-bottom: 0; }
    .table-ledger thead th { background: #34495e; color: #fff; font-weight: 600; text-transform: uppercase; font-size: 13px; padding: 12px; border: 1px solid #2c3e50; text-align: center; }
    .table-ledger tbody td { padding: 12px; font-size: 14px; border: 1px solid #eee; vertical-align: middle; }
    .table-ledger tbody tr:hover { background: #f9f9f9; }
    
    .row-bf { background-color: #fffde7 !important; font-weight: bold; color: #5d4037 !important; }
    .row-footer { background-color: #ecf0f5 !important; font-weight: bold; border-top: 2px solid #333; }
    
    .amt { text-align: right; font-family: 'Courier New', Courier, monospace; font-weight: 700; font-size: 15px; }
    .text-dr { color: #c0392b; }
    .text-cr { color: #27ae60; }
    
    .badge-type { padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
    .badge-bill { background: #fdebd0; color: #d35400; }
    .badge-pay { background: #d5f5e3; color: #1e8449; }
    .badge-settled { background: #f4f4f4; color: #999; }
</style>
@endpush

@section('content')
<div class="box box-primary search-box">
    <div class="box-body" style="padding: 20px;">
        <form method="GET" action="{{ route('ledgers.client') }}">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="text-muted">Select Client</label>
                        <select class="form-control select2" name="client_name" style="width:100%;" required>
                            <option value="">-- Choose a Client --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->company_name }}" {{ $client_name == $c->company_name ? 'selected' : '' }}>
                                    {{ $c->company_name }}
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
                        <button type="submit" name="view_ledger" value="1" class="btn btn-primary btn-block">
                            <i class="fa fa-filter"></i> Generate
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@if($show_report && $client_info)
<div class="ledger-container">

    <div class="client-header">
        <div class="client-title">
            <h2>{{ $client_name }}</h2>
            <p><i class="fa fa-user-circle"></i> Contact: <b>{{ $persion_name ?: 'N/A' }}</b> | <i class="fa fa-phone"></i> {{ $contact ?: 'N/A' }}</p>
            <p style="margin-top: 5px;"><i class="fa fa-calendar-check-o"></i> Statement Period: <b>{{ date('d M Y', strtotime($from_date)) }}</b> to <b>{{ date('d M Y', strtotime($to_date)) }}</b></p>
        </div>
        <div class="status-box-main {{ $status_color }}">
            <h4><i class="fa {{ $status_icon }}"></i> {{ $status_text }}</h4>
            <h1>{{ number_format(abs($current_balance), 2) }} <small style="color:rgba(255,255,255,0.8); font-size:18px;">{{ $status_label }}</small></h1>
        </div>
    </div>

    <div class="summary-row">
        <div class="summary-card card-open">
            <div class="icon"><i class="fa fa-folder-open"></i></div>
            <div class="details">
                <p>Total Opening Balance</p>
                <h3>{{ number_format(abs($opening_balance), 2) }} <span style="font-size:14px;">{{ $opening_balance > 0 ? '(Dr)' : ($opening_balance < 0 ? '(Cr)' : '') }}</span></h3>
            </div>
        </div>
        <div class="summary-card card-bill">
            <div class="icon"><i class="fa fa-file-text"></i></div>
            <div class="details">
                <p>Total Billed (Lifetime Dr)</p>
                <h3>{{ number_format($tot_billed, 2) }}</h3>
            </div>
        </div>
        <div class="summary-card card-recv">
            <div class="icon"><i class="fa fa-money"></i></div>
            <div class="details">
                <p>Total Received (Lifetime Cr)</p>
                <h3>{{ number_format($tot_received, 2) }}</h3>
            </div>
        </div>
    </div>

    <div style="text-align: right; margin-bottom: 15px;">
        <a href="{{ route('ledgers.client-print', ['client' => $client_name, 'from' => $from_date, 'to' => $to_date]) }}" 
           target="_blank" class="btn btn-primary btn-lg" style="box-shadow: 0 3px 6px rgba(0,0,0,0.1);">
           <i class="fa fa-external-link"></i> Open Print View
        </a>
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
                    <td colspan="3" class="text-right"><i class="fa fa-share"></i> Brought Forward (Balance till {{ date('d-M-Y', strtotime($from_date . ' -1 day')) }}):</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="amt">{!! number_format(abs($brought_forward), 2) . ($brought_forward < 0 ? ' <small class="text-cr">Cr</small>' : ' <small class="text-dr">Dr</small>') !!}</td>
                </tr>

                @php
                    $sum_dr = 0; $sum_not_added = 0; $sum_recv = 0;
                    $r_balance = $brought_forward;
                @endphp

                @if(count($ledger_data) > 0)
                    @foreach($ledger_data as $row)
                        @php
                            $sum_dr += $row['debit'];
                            $sum_not_added += $row['not_added'];
                            $sum_recv += $row['received'];
                            
                            $r_balance = $r_balance + $row['debit'] - $row['received'];
                            
                            $dr_text = $row['debit'] > 0 ? number_format($row['debit'], 2) : '-';
                            $na_text = $row['not_added'] > 0 ? number_format($row['not_added'], 2) : '-';
                            $recv_text = $row['received'] > 0 ? number_format($row['received'], 2) : '-';
                            
                            $badge = 'badge-bill';
                            if($row['type'] == 'Payment') $badge = 'badge-pay';
                            if(str_contains($row['type'], 'Running Bill') && $row['not_added'] > 0) $badge = 'badge-settled';
                            
                            $bal_str = number_format(abs($r_balance), 2);
                            if($r_balance > 0) $bal_str .= " <small class='text-dr'>Dr</small>";
                            elseif($r_balance < 0) $bal_str .= " <small class='text-cr'>Cr</small>";
                            else $bal_str = "0.00";

                            $na_td_style = ($row['not_added'] > 0) ? "color:#95a5a6; background:#fbfcfc;" : "";
                        @endphp
                        <tr>
                            <td>{{ date('d-M-Y', strtotime($row['date'])) }}</td>
                            <td>
                                <b>{{ $row['ref'] }}</b><br>
                                <span class="badge-type {{ $badge }}">{{ $row['type'] }}</span>
                            </td>
                            <td>{!! $row['details'] !!}</td>
                            <td class="amt text-dr">{{ $dr_text }}</td>
                            <td class="amt" style="{{ $na_td_style }}">{{ $na_text }}</td>
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
                    <td class="amt" style="padding:15px; color:#7f8c8d;">{{ number_format($sum_not_added, 2) }}</td>
                    <td class="amt text-cr" style="padding:15px; color:#27ae60;">{{ number_format($sum_recv, 2) }}</td>
                    <td class="amt" style="padding:15px; background:#fff;">
                        Closing: {!! number_format(abs($r_balance), 2) . ($r_balance < 0 ? ' <small class="text-cr">Cr</small>' : ' <small class="text-dr">Dr</small>') !!}
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
