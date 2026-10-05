@extends('layouts.master')

@section('title', 'Loan Master Ledger - JRC ERP')
@section('page_title', 'Loan Master Ledger')

@push('styles')
<style>
    /* Professional Accounting UI CSS */
    .ledger-container { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 25px; border-top: 5px solid #8e44ad; }
    
    .client-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ecf0f5; padding-bottom: 20px; margin-bottom: 20px; }
    .client-title h2 { margin: 0 0 8px 0; font-weight: 700; color: #2c3e50; font-size: 28px; text-transform: uppercase; letter-spacing: 1px; }
    .client-title p { margin: 0; color: #555; font-size: 15px; }
    
    .status-box-main { padding: 20px 30px; border-radius: 8px; text-align: right; min-width: 250px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .status-due { background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; } /* Liability Red */
    .status-advance { background: linear-gradient(135deg, #2ecc71, #27ae60); color: white; } /* Overpaid Green */
    .status-settled { background: linear-gradient(135deg, #8e44ad, #732d91); color: white; } /* Settled Purple */
    
    .status-box-main h4 { margin: 0 0 5px 0; font-size: 15px; font-weight: 700; letter-spacing: 1.5px; opacity: 0.9; }
    .status-box-main h1 { margin: 0; font-size: 36px; font-weight: 800; color: #fff; }
    
    /* Summary Cards */
    .summary-row { display: flex; gap: 20px; margin-bottom: 25px; flex-wrap: wrap; }
    .summary-card { flex: 1; min-width: 220px; background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0; box-shadow: 0 2px 5px rgba(0,0,0,0.02); display: flex; align-items: center; }
    .summary-card .icon { font-size: 40px; margin-right: 15px; opacity: 0.8; }
    .summary-card .details p { margin: 0; font-size: 13px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; }
    .summary-card .details h3 { margin: 5px 0 0 0; font-size: 22px; font-weight: 800; }
    
    .card-open { border-left: 5px solid #8e44ad; } .card-open .icon { color: #8e44ad; } .card-open h3 { color: #732d91; }
    .card-prin { border-left: 5px solid #2ecc71; } .card-prin .icon { color: #2ecc71; } .card-prin h3 { color: #27ae60; }
    .card-int { border-left: 5px solid #f39c12; } .card-int .icon { color: #f39c12; } .card-int h3 { color: #d35400; }

    /* Ledger Table */
    .table-ledger { border: 1px solid #ddd; width: 100%; margin-bottom: 0; }
    .table-ledger thead th { background: #34495e; color: #fff; font-weight: 600; text-transform: uppercase; font-size: 13px; padding: 12px; border: 1px solid #2c3e50; text-align: center; }
    .table-ledger tbody td { padding: 12px; font-size: 14px; border: 1px solid #eee; vertical-align: middle; }
    .table-ledger tbody tr:hover { background: #f9f9f9; }
    
    .row-bf { background-color: #f4ecf7 !important; font-weight: bold; color: #512e5f !important; }
    .row-footer { background-color: #ecf0f5 !important; font-weight: bold; border-top: 2px solid #333; }
    
    .amt { text-align: right; font-family: 'Courier New', Courier, monospace; font-weight: 700; font-size: 15px; }
    .text-dr { color: #27ae60; } /* Repayment is Good (Green) */
    .text-cr { color: #c0392b; } /* Loan Received is Liability (Red) */
    .text-int { color: #d35400; } /* Interest Expense (Orange) */
    
    .badge-type { padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
    .badge-rec { background: #fadbd8; color: #922b21; }
    .badge-prin { background: #d5f5e3; color: #1e8449; }
    .badge-int { background: #fdebd0; color: #d35400; }

    @media print {
        body { background: #fff !important; }
        .main-header, .main-sidebar, .content-header, .search-box, .btn, .main-footer { display: none !important; }
        .content-wrapper { margin-left: 0 !important; }
        .ledger-container { border: none; box-shadow: none; padding: 0; }
        .summary-card { border: 1px solid #ddd; }
        .table-ledger thead th { background: #eee !important; color: #000 !important; border: 1px solid #000 !important; }
        .table-ledger tbody td, .table-ledger tfoot th { border: 1px solid #000 !important; }
    }
</style>
@endpush

@section('content')
<div class="box search-box" style="border-top: 3px solid #8e44ad;">
    <div class="box-body" style="padding: 20px;">
        <form method="GET" action="{{ route('ledgers.loan') }}">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="text-muted">Select Loan Account</label>
                        <select class="form-control select2" name="loan_head_id" style="width:100%;" required>
                            <option value="">-- Choose Loan Person/Bank --</option>
                            @foreach($loanAccounts as $h)
                                <option value="{{ $h->id }}" {{ $loan_head_id == $h->id ? 'selected' : '' }}>
                                    {{ $h->head_name }}
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
                        <button type="submit" name="view_ledger" value="1" class="btn btn-block" style="background:#8e44ad; color:#fff;">
                            <i class="fa fa-filter"></i> Generate
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@if($show_report)
<div class="ledger-container">
    <div class="client-header">
        <div class="client-title">
            <h2>{{ $loan_name }}</h2>
            <p><i class="fa fa-info-circle"></i> Account Type: <b>Loan & Borrowings (Liability)</b></p>
            <p style="margin-top: 5px;"><i class="fa fa-calendar-check-o"></i> Statement Period: <b>{{ date('d M Y', strtotime($from_date)) }}</b> to <b>{{ date('d M Y', strtotime($to_date)) }}</b></p>
        </div>
        <div class="status-box-main {{ $status_color }}">
            <h4><i class="fa {{ $status_icon }}"></i> {{ $status_text }}</h4>
            <h1>{{ number_format(abs($current_balance), 2) }} <small style="color:rgba(255,255,255,0.8); font-size:18px;">{{ $status_label }}</small></h1>
        </div>
    </div>

    <div class="summary-row">
        <div class="summary-card card-open">
            <div class="icon"><i class="fa fa-arrow-circle-down"></i></div>
            <div class="details">
                <p>Total Loan Received (Cr)</p>
                <h3 class="text-cr">{{ number_format($tot_received, 2) }}</h3>
            </div>
        </div>
        <div class="summary-card card-prin">
            <div class="icon"><i class="fa fa-arrow-circle-up"></i></div>
            <div class="details">
                <p>Total Principal Repaid (Dr)</p>
                <h3 class="text-dr">{{ number_format($tot_principal, 2) }}</h3>
            </div>
        </div>
        <div class="summary-card card-int">
            <div class="icon"><i class="fa fa-pie-chart"></i></div>
            <div class="details">
                <p>Total Interest Paid (Exp)</p>
                <h3 class="text-int">{{ number_format($tot_interest, 2) }}</h3>
            </div>
        </div>
    </div>

    <div style="text-align: right; margin-bottom: 15px;">
        <button onclick="window.print()" class="btn btn-default btn-lg"><i class="fa fa-print"></i> Print Ledger</button>
    </div>

    <div class="table-responsive">
        <table class="table table-ledger">
            <thead>
                <tr>
                    <th width="10%">Date</th>
                    <th width="15%" class="text-left">Ref / Category</th>
                    <th width="29%" class="text-left">Details</th>
                    <th width="12%" class="text-right">Interest Paid<br><small>(Does not reduce Principal)</small></th>
                    <th width="12%" class="text-right">Principal Repaid<br>(Dr)</th>
                    <th width="10%" class="text-right">Loan Received<br>(Cr)</th>
                    <th width="12%" class="text-right">Principal Balance</th>
                </tr>
            </thead>
            <tbody>
                <tr class="row-bf">
                    <td colspan="3" class="text-right"><i class="fa fa-share"></i> Brought Forward (Principal Due till {{ date('d-M-Y', strtotime($from_date . ' -1 day')) }}):</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="amt">{!! number_format(abs($brought_forward), 2) . ($brought_forward > 0 ? ' <small class="text-cr">Cr</small>' : ' <small class="text-dr">Dr</small>') !!}</td>
                </tr>

                @php
                    $sum_prin_dr = 0; $sum_int_dr = 0; $sum_cr = 0;
                    $r_balance = $brought_forward;
                @endphp

                @if(count($ledger_data) > 0)
                    @foreach($ledger_data as $row)
                        @php
                            $sum_prin_dr += $row['principal_dr'];
                            $sum_int_dr  += $row['interest_dr'];
                            $sum_cr      += $row['credit'];
                            
                            $r_balance = $r_balance + $row['credit'] - $row['principal_dr'];
                            
                            $cr_text   = $row['credit'] > 0 ? number_format($row['credit'], 2) : '-';
                            $prin_text = $row['principal_dr'] > 0 ? number_format($row['principal_dr'], 2) : '-';
                            $int_text  = $row['interest_dr'] > 0 ? number_format($row['interest_dr'], 2) : '-';
                            
                            $badge = 'badge-prin'; 
                            if($row['type'] == 'Loan Received') $badge = 'badge-rec';
                            if($row['type'] == 'Interest Paid') $badge = 'badge-int';
                            
                            $bal_str = number_format(abs($r_balance), 2);
                            if($r_balance > 0) $bal_str .= " <small class='text-cr'>Cr</small>"; 
                            elseif($r_balance < 0) $bal_str .= " <small class='text-dr'>Dr</small>"; 
                            else $bal_str = "0.00";
                        @endphp
                        <tr>
                            <td>{{ date('d-M-Y', strtotime($row['date'])) }}</td>
                            <td>
                                <b>{{ $row['ref'] }}</b><br>
                                <span class="badge-type {{ $badge }}">{{ $row['type'] }}</span>
                            </td>
                            <td>{!! $row['details'] !!}</td>
                            <td class="amt text-int">{{ $int_text }}</td>
                            <td class="amt text-dr">{{ $prin_text }}</td>
                            <td class="amt text-cr">{{ $cr_text }}</td>
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
                    <td colspan="3" class="text-right" style="padding:15px; font-size:15px;"><b>Total for Period ({{ date('d-M-y', strtotime($from_date)) }} to {{ date('d-M-y', strtotime($to_date)) }}) :</b></td>
                    <td class="amt text-int" style="padding:15px;">{{ number_format($sum_int_dr, 2) }}</td>
                    <td class="amt text-dr" style="padding:15px;">{{ number_format($sum_prin_dr, 2) }}</td>
                    <td class="amt text-cr" style="padding:15px;">{{ number_format($sum_cr, 2) }}</td>
                    <td class="amt" style="padding:15px; background:#fff;">
                        Closing: {!! number_format(abs($r_balance), 2) . ($r_balance > 0 ? ' <small class="text-cr">Cr</small>' : ' <small class="text-dr">Dr</small>') !!}
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
