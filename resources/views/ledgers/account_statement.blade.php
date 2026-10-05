@extends('layouts.master')

@section('title', 'Account Statement - JRC ERP')
@section('page_title', 'Account Statement')

@push('styles')
<style>
    /* Screen Styles */
    .print-wrapper { background: #fff; padding: 40px; box-shadow: 0 0 15px rgba(0,0,0,0.1); border-top: 4px solid #3c8dbc; margin-top: 20px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    
    .company-header { text-align: center; border-bottom: 2px solid #222; padding-bottom: 15px; margin-bottom: 25px; }
    .company-header h2 { margin: 0; font-size: 26px; font-weight: bold; color: #222; text-transform: uppercase; letter-spacing: 1px; }
    .company-header p { margin: 4px 0; font-size: 14px; color: #555; }
    .statement-title { display: inline-block; background: #222; color: #fff; padding: 6px 20px; font-size: 16px; font-weight: bold; text-transform: uppercase; margin-top: 10px; border-radius: 3px; }

    .account-info-box { display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 14px; background: #f9f9f9; padding: 15px; border: 1px solid #eee; border-radius: 5px; }
    .info-left p, .info-right p { margin: 4px 0; }
    .info-label { font-weight: bold; color: #555; display: inline-block; width: 110px; }
    .info-val { font-weight: 600; color: #222; }

    .bank-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .bank-table th, .bank-table td { border: 1px solid #ddd; padding: 10px 12px; vertical-align: middle; }
    .bank-table th { background: #f4f4f4; color: #333; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; text-align: center; }
    
    .col-sl { width: 40px; text-align: center; }
    .col-date { width: 90px; text-align: center; }
    .col-particulars { text-align: left; line-height: 1.5; }
    .col-amount { width: 120px; text-align: right; font-family: 'Courier New', Courier, monospace; font-size: 14px; font-weight: bold; }
    
    .dr-amt { color: #d9534f; } /* Withdrawal / Debit */
    .cr-amt { color: #00a65a; } /* Deposit / Credit */
    .bal-amt { color: #333; }

    .opening-row td { font-weight: bold; background: #fdfdfd; font-size: 14px; }
    .footer-row th { background: #f4f4f4; text-align: right; font-size: 14px; color: #222; }
    
    .type-badge { font-size: 10px; background: #eee; padding: 2px 6px; border-radius: 3px; color: #555; font-weight: bold; text-transform: uppercase; margin-right: 5px; border: 1px solid #ccc; display: inline-block; margin-bottom: 4px; }
    .text-muted { color: #777; }
    .text-success { color: #00a65a; }
    .text-danger { color: #d9534f; }

    .print-btn-container { margin-top: 20px; text-align: right; }
    .btn-print { background: #333; color: #fff; border: none; padding: 10px 25px; font-size: 16px; border-radius: 4px; cursor: pointer; transition: 0.3s; }
    .btn-print:hover { background: #555; }

    @media print {
        body, html { background: #fff; margin: 0; padding: 0; font-size: 10pt; }
        .wrapper, .content-wrapper { background: #fff !important; margin: 0 !important; padding: 0 !important; }
        .main-header, .main-sidebar, .box-primary, .print-btn-container, .main-footer, .search-box { display: none !important; }
        .print-wrapper { border: none; box-shadow: none; padding: 0; margin: 0; }
        @page { size: A4 portrait; margin: 15mm; }
        .bank-table th { background-color: #eee !important; -webkit-print-color-adjust: exact; color: #000 !important; }
        .bank-table td, .bank-table th { border: 1px solid #000 !important; }
        .account-info-box { border: 1px solid #000 !important; background: transparent !important; }
        .bank-table tr { page-break-inside: avoid; }
        .dr-amt { color: #d9534f !important; -webkit-print-color-adjust: exact; }
        .cr-amt { color: #00a65a !important; -webkit-print-color-adjust: exact; }
        .statement-title { background: #222 !important; color: #fff !important; -webkit-print-color-adjust: exact; }
        .type-badge { background: #eee !important; border: 1px solid #999 !important; -webkit-print-color-adjust: exact; }
    }
</style>
@endpush

@section('content')
<div class="box box-primary search-box no-print">
    <div class="box-body" style="padding: 20px;">
        <form method="GET" action="{{ route('ledgers.account_statement') }}">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Select Account</label>
                        <select name="method_id" class="form-control select2" style="width:100%;" required>
                            <option value="">-- Choose Cash/Bank/MFS --</option>
                            @foreach($methods as $m)
                                <option value="{{ $m->id }}" {{ $method_id == $m->id ? 'selected' : '' }}>
                                    [{{ $m->account_type }}] {{ $m->method_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $from_date }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $to_date }}" required>
                    </div>
                </div>
                <div class="col-md-2" style="margin-top:25px;">
                    <button type="submit" name="view_statement" value="1" class="btn btn-primary btn-block">
                        <i class="fa fa-filter"></i> Generate
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@if($show_report && $method_info)
<div class="print-wrapper">
    <div class="company-header">
        <h2>JR Corporation</h2>
        <p>123 Business Avenue, Block-C, Dhaka, Bangladesh</p>
        <p>Phone: +880 1234 567890 | Email: info@jrcorp.com</p>
        <div class="statement-title">Account Statement</div>
    </div>

    <div class="account-info-box">
        <div class="info-left">
            <p><span class="info-label">Account Name:</span> <span class="info-val">{{ strtoupper($method_name) }}</span></p>
            <p><span class="info-label">Account Type:</span> <span class="info-val">{{ $account_type }}</span></p>
        </div>
        <div class="info-right text-right">
            <p><span class="info-label">Period From:</span> <span class="info-val">{{ date('d M Y', strtotime($from_date)) }}</span></p>
            <p><span class="info-label">Period To:</span> <span class="info-val">{{ date('d M Y', strtotime($to_date)) }}</span></p>
            <p><span class="info-label">Printed On:</span> <span class="info-val">{{ date('d M Y, h:i A') }}</span></p>
        </div>
    </div>

    <table class="bank-table">
        <thead>
            <tr>
                <th class="col-sl">SL</th>
                <th class="col-date">Date</th>
                <th class="col-particulars">Particulars / Description</th>
                <th class="col-amount">Withdrawal (Dr.)</th>
                <th class="col-amount">Deposit (Cr.)</th>
                <th class="col-amount">Balance</th>
            </tr>
        </thead>
        <tbody>
            <tr class="opening-row">
                <td class="text-center">-</td>
                <td class="col-date text-center">{{ date('d-m-Y', strtotime($from_date)) }}</td>
                <td>Brought Forward (Opening Balance)</td>
                <td class="col-amount"></td>
                <td class="col-amount"></td>
                <td class="col-amount bal-amt">{{ number_format($brought_forward, 2) }}</td>
            </tr>
            @php
                $total_in = 0; $total_out = 0;
                $sl = 1; 
                $r_balance = $brought_forward;
            @endphp
            @foreach($ledger_data as $row)
                @php
                    $r_balance += ((float)$row->amt_in - (float)$row->amt_out);
                    $total_in += (float)$row->amt_in;
                    $total_out += (float)$row->amt_out;
                @endphp
                <tr>
                    <td class="text-center">{{ $sl++ }}</td>
                    <td class="col-date">{{ date('d-m-Y', strtotime($row->date)) }}</td>
                    <td class="col-particulars">
                        <span class="type-badge">{{ $row->category }}</span><br>
                        {!! $row->note !!}
                    </td>
                    <td class="col-amount dr-amt">
                        {{ $row->amt_out > 0 ? number_format((float)$row->amt_out, 2) : '' }}
                    </td>
                    <td class="col-amount cr-amt">
                        {{ $row->amt_in > 0 ? number_format((float)$row->amt_in, 2) : '' }}
                    </td>
                    <td class="col-amount bal-amt">
                        {{ number_format($r_balance, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="footer-row">
            <tr>
                <th colspan="3">Total for this Period:</th>
                <th class="col-amount dr-amt">{{ number_format($total_out, 2) }}</th>
                <th class="col-amount cr-amt">{{ number_format($total_in, 2) }}</th>
                <th class="col-amount bal-amt">Closing: {{ number_format($r_balance, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="print-btn-container no-print">
        <button onclick="window.print()" class="btn-print"><i class="fa fa-print"></i> Print A4 Statement</button>
    </div>
    
    <div style="margin-top: 60px; display: flex; justify-content: space-between; padding: 0 20px;">
        <div style="text-align: center; border-top: 1px solid #333; padding-top: 5px; width: 200px; font-weight: bold; font-size: 13px;">Prepared By</div>
        <div style="text-align: center; border-top: 1px solid #333; padding-top: 5px; width: 200px; font-weight: bold; font-size: 13px;">Authorized Signature</div>
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
