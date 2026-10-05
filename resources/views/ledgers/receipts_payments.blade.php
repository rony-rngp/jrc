@extends('layouts.master')

@section('title', 'Receipts & Payments Report - JRC ERP')
@section('page_title', 'Receipts & Payments Report')

@push('styles')
<style>
    /* Professional Accounting UI CSS */
    .report-container { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 25px; border-top: 5px solid #34495e; }
    
    .report-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ecf0f5; padding-bottom: 20px; margin-bottom: 20px; }
    .report-title h2 { margin: 0 0 8px 0; font-weight: 700; color: #2c3e50; font-size: 26px; text-transform: uppercase; letter-spacing: 1px; }
    .report-title p { margin: 0; color: #555; font-size: 15px; }
    
    /* Summary Cards */
    .summary-row { display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap; }
    .summary-card { flex: 1; min-width: 200px; background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0; box-shadow: 0 2px 5px rgba(0,0,0,0.02); display: flex; align-items: center; }
    .summary-card .icon { font-size: 35px; margin-right: 15px; opacity: 0.8; }
    .summary-card .details p { margin: 0; font-size: 12px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; }
    .summary-card .details h3 { margin: 5px 0 0 0; font-size: 22px; font-weight: 800; }
    
    .card-in { border-left: 5px solid #00a65a; } .card-in .icon { color: #00a65a; } .card-in h3 { color: #008d4c; }
    .card-out { border-left: 5px solid #dd4b39; } .card-out .icon { color: #dd4b39; } .card-out h3 { color: #c0392b; }
    .card-flow { border-left: 5px solid #f39c12; } .card-flow .icon { color: #f39c12; } .card-flow h3 { color: #d35400; }
    .card-bal { border-left: 5px solid #00c0ef; background: #f4fcfd; } .card-bal .icon { color: #00c0ef; } .card-bal h3 { color: #0097bc; }

    /* Split Columns for Receipts & Payments */
    .rp-wrapper { display: flex; gap: 20px; flex-wrap: wrap; }
    .rp-box { flex: 1; min-width: 300px; border-radius: 6px; overflow: hidden; border: 1px solid #ddd; }
    
    .rp-header-in { background: #00a65a; color: white; padding: 12px 15px; font-size: 16px; font-weight: bold; text-transform: uppercase; text-align: center; }
    .rp-header-out { background: #dd4b39; color: white; padding: 12px 15px; font-size: 16px; font-weight: bold; text-transform: uppercase; text-align: center; }
    
    .table-rp { width: 100%; margin: 0; }
    .table-rp tbody td { padding: 12px 15px; font-size: 14px; border-bottom: 1px solid #f4f4f4; color: #444; }
    .table-rp tbody tr:hover { background: #f9f9f9; }
    .table-rp tfoot th { padding: 15px; font-size: 16px; background: #f4f7f9; color: #333; }
    
    .amt-in { color: #00a65a; font-weight: bold; text-align: right; font-family: monospace; font-size: 15px;}
    .amt-out { color: #dd4b39; font-weight: bold; text-align: right; font-family: monospace; font-size: 15px;}

    /* Current Balances Box */
    .balance-box { margin-top: 30px; background: #fdfdfd; border: 1px solid #d2d6de; border-radius: 8px; padding: 20px; }
    .balance-box h3 { margin-top: 0; color: #34495e; border-bottom: 2px solid #3c8dbc; padding-bottom: 10px; font-size: 18px; font-weight: bold; text-transform: uppercase; }
    .bal-item { border-bottom: 1px dashed #ccc; padding: 8px 0; display: flex; justify-content: space-between; font-size: 14px; }
    .bal-item:last-child { border-bottom: none; }
    .bal-cat-title { font-weight: bold; color: #333; margin-top: 15px; font-size: 15px; text-transform: uppercase; }

    @media print {
        body { background: #fff !important; }
        .main-header, .main-sidebar, .content-header, .search-box, .btn, .main-footer { display: none !important; }
        .content-wrapper { margin-left: 0 !important; }
        .report-container { border: none; box-shadow: none; padding: 0; }
        .summary-card { border: 1px solid #000; }
        .rp-box { border: 1px solid #000; }
        .rp-header-in, .rp-header-out { background: #eee !important; color: #000 !important; border-bottom: 1px solid #000; }
        .balance-box { border: 1px solid #000; }
    }
</style>
@endpush

@section('content')
<div class="box box-primary search-box">
    <div class="box-body" style="padding: 20px;">
        <form method="GET" action="{{ route('ledgers.receipts_payments') }}">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="text-muted">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $from_date }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="text-muted">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $to_date }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group" style="padding-top: 25px;">
                        <button type="submit" name="view_report" value="1" class="btn btn-primary btn-block btn-lg">
                            <i class="fa fa-bar-chart"></i> Generate Report
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@if($show_report)
<div class="report-container">
    <div class="report-header">
        <div class="report-title">
            <h2>Receipts and Payments Account</h2>
            <p><i class="fa fa-calendar-check-o"></i> Statement Period: <b>{{ date('d F Y', strtotime($from_date)) }}</b> to <b>{{ date('d F Y', strtotime($to_date)) }}</b></p>
        </div>
        <div style="text-align: right;">
            <button onclick="window.print()" class="btn btn-default btn-lg"><i class="fa fa-print"></i> Print Report</button>
        </div>
    </div>

    <div class="summary-row">
        <div class="summary-card card-in">
            <div class="icon"><i class="fa fa-arrow-circle-down"></i></div>
            <div class="details">
                <p>Total Receipts (Inflow)</p>
                <h3>{{ number_format($total_receipts, 2) }}</h3>
            </div>
        </div>
        <div class="summary-card card-out">
            <div class="icon"><i class="fa fa-arrow-circle-up"></i></div>
            <div class="details">
                <p>Total Payments (Outflow)</p>
                <h3>{{ number_format($total_payments, 2) }}</h3>
            </div>
        </div>
        <div class="summary-card card-flow">
            <div class="icon"><i class="fa fa-exchange"></i></div>
            <div class="details">
                <p>Net Cash Flow (Period)</p>
                <h3>{{ number_format($net_cash_flow, 2) }}</h3>
            </div>
        </div>
        <div class="summary-card card-bal">
            <div class="icon"><i class="fa fa-money"></i></div>
            <div class="details">
                <p>Total Current Liquidity</p>
                <h3>{{ number_format($total_current_balance, 2) }}</h3>
            </div>
        </div>
    </div>

    <div class="rp-wrapper">
        <div class="rp-box">
            <div class="rp-header-in"><i class="fa fa-download"></i> Receipts (Cash In)</div>
            <table class="table table-rp">
                <tbody>
                    @if(count($receipts) > 0)
                        @foreach($receipts as $head => $amount)
                            <tr>
                                <td><b>{{ $head }}</b></td>
                                <td class="amt-in">{{ number_format($amount, 2) }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="2" class="text-center text-muted" style="padding:20px;">No receipts found for this period.</td>
                        </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-right">Total Receipts:</th>
                        <th class="amt-in" style="font-size: 18px;">৳ {{ number_format($total_receipts, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="rp-box">
            <div class="rp-header-out"><i class="fa fa-upload"></i> Payments (Cash Out)</div>
            <table class="table table-rp">
                <tbody>
                    @if(count($payments) > 0)
                        @foreach($payments as $head => $amount)
                            <tr>
                                <td><b>{{ $head }}</b></td>
                                <td class="amt-out">{{ number_format($amount, 2) }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="2" class="text-center text-muted" style="padding:20px;">No payments found for this period.</td>
                        </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-right">Total Payments:</th>
                        <th class="amt-out" style="font-size: 18px;">৳ {{ number_format($total_payments, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="balance-box">
        <h3><i class="fa fa-bank text-primary"></i> Closing Liquid Balances (As of Today)</h3>
        <div class="row">
            <div class="col-md-4">
                <div class="bal-cat-title text-success"><i class="fa fa-money"></i> Cash in Hand</div>
                @if(count($balance_details['Cash']) > 0)
                    @foreach($balance_details['Cash'] as $bal)
                        <div class="bal-item"><span>{{ $bal->method_name }}</span> <b class="text-success">৳ {{ number_format((float)$bal->amount, 2) }}</b></div>
                    @endforeach
                @else
                    <div class="bal-item text-muted">No Cash Balance</div>
                @endif
                <div class="bal-item" style="background:#f4fcfd; padding:5px; margin-top:5px;"><b>Total Cash:</b> <b class="text-success">৳ {{ number_format($cash_in_hand, 2) }}</b></div>
            </div>

            <div class="col-md-4">
                <div class="bal-cat-title text-primary"><i class="fa fa-institution"></i> Cash at Bank</div>
                @if(count($balance_details['Bank']) > 0)
                    @foreach($balance_details['Bank'] as $bal)
                        <div class="bal-item"><span>{{ $bal->method_name }}</span> <b class="text-primary">৳ {{ number_format((float)$bal->amount, 2) }}</b></div>
                    @endforeach
                @else
                    <div class="bal-item text-muted">No Bank Balance</div>
                @endif
                <div class="bal-item" style="background:#f4fcfd; padding:5px; margin-top:5px;"><b>Total Bank:</b> <b class="text-primary">৳ {{ number_format($cash_at_bank, 2) }}</b></div>
            </div>

            <div class="col-md-4">
                <div class="bal-cat-title text-warning"><i class="fa fa-mobile-phone" style="font-size:18px;"></i> MFS (bKash/Nagad)</div>
                @if(count($balance_details['MFS']) > 0)
                    @foreach($balance_details['MFS'] as $bal)
                        <div class="bal-item"><span>{{ $bal->method_name }}</span> <b class="text-warning">৳ {{ number_format((float)$bal->amount, 2) }}</b></div>
                    @endforeach
                @else
                    <div class="bal-item text-muted">No MFS Balance</div>
                @endif
                <div class="bal-item" style="background:#f4fcfd; padding:5px; margin-top:5px;"><b>Total MFS:</b> <b class="text-warning">৳ {{ number_format($cash_at_mfs, 2) }}</b></div>
            </div>
        </div>
    </div>

</div>
@endif
@endsection
