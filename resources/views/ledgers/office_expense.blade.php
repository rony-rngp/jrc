@extends('layouts.master')

@section('title', 'Office Expense Ledger - JRC ERP')
@section('page_title', 'Office Expense Ledger')

@push('styles')
<style>
    /* Professional UI CSS */
    .ledger-container { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 25px; border-top: 5px solid #e74c3c; }
    
    .report-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ecf0f5; padding-bottom: 20px; margin-bottom: 20px; }
    .report-title h2 { margin: 0 0 8px 0; font-weight: 700; color: #2c3e50; font-size: 24px; text-transform: uppercase; letter-spacing: 1px; }
    .report-title p { margin: 0; color: #555; font-size: 15px; }
    
    .status-box-main { padding: 20px 30px; border-radius: 8px; text-align: right; min-width: 250px; background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .status-box-main h4 { margin: 0 0 5px 0; font-size: 14px; font-weight: 700; letter-spacing: 1px; opacity: 0.9; text-transform: uppercase;}
    .status-box-main h1 { margin: 0; font-size: 36px; font-weight: 800; color: #fff; }

    .table-ledger { border: 1px solid #ddd; width: 100%; margin-bottom: 0; }
    .table-ledger thead th { background: #34495e; color: #fff; font-weight: 600; text-transform: uppercase; font-size: 13px; padding: 12px; border: 1px solid #2c3e50; text-align: left; }
    .table-ledger tbody td { padding: 12px; font-size: 14px; border: 1px solid #eee; vertical-align: middle; }
    
    .row-footer { background-color: #fdf2f5 !important; font-weight: bold; border-top: 2px solid #e74c3c; }
    .amt { text-align: right; font-family: 'Courier New', Courier, monospace; font-weight: 700; font-size: 16px; color: #c0392b; }

    /* Print Specific Header */
    .print-header { display: none; }
    
    @media print {
        body { background: #fff !important; }
        .main-header, .main-sidebar, .content-header, .search-box, .btn, .NoPrint, .main-footer { display: none !important; }
        .content-wrapper { margin-left: 0 !important; }
        .ledger-container { border: none; box-shadow: none; padding: 0; }
        .report-header { display: none !important; }
        
        .print-header { display: block !important; margin-bottom: 20px; text-align: center; }
        .print-title { text-align: center; border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 10px; }
        
        .table-ledger thead th { background: #eee !important; color: #000 !important; border: 1px solid #000 !important; }
        .table-ledger tbody td, .table-ledger tfoot th, .table-ledger tfoot td { border: 1px solid #000 !important; }
    }
</style>
@endpush

@section('content')
<div class="box search-box NoPrint" style="border-top: 3px solid #e74c3c;">
    <div class="box-body" style="padding: 20px;">
        <form method="GET" action="{{ route('ledgers.office_expense') }}">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="text-muted">Select Expense Head</label>
                        <select class="form-control select2" name="head_id" style="width:100%;" required>
                            <option value="all" {{ $selected_head_id == 'all' ? 'selected' : '' }}>-- All Office Expenses --</option>
                            @foreach($expenseHeads as $h)
                                <option value="{{ $h->id }}" {{ $selected_head_id == $h->id ? 'selected' : '' }}>
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
                        <button type="submit" name="view_ledger" value="1" class="btn btn-block" style="background:#e74c3c; color:#fff;">
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
    <div class="print-header">
        <div class="print-title">
            <h2 style="margin:0; font-weight:bold; color:#2c3e50; text-transform: uppercase;">JR Corporation</h2>
            <h3 style="margin: 10px 0; font-weight: bold; text-transform: uppercase;">{{ $report_title }}</h3>
            <p>Period: {{ date('d M, Y', strtotime($from_date)) }} to {{ date('d M, Y', strtotime($to_date)) }}</p>
        </div>
    </div>

    <div class="report-header NoPrint">
        <div class="report-title">
            <h2>{{ $report_title }}</h2>
            <p><i class="fa fa-folder-open-o"></i> Category: <b>Operational Expenses</b></p>
            <p style="margin-top: 5px;"><i class="fa fa-calendar-check-o"></i> Period: <b>{{ date('d M, Y', strtotime($from_date)) }}</b> to <b>{{ date('d M, Y', strtotime($to_date)) }}</b></p>
        </div>
        <div class="status-box-main">
            <h4>Total Expense Amount</h4>
            <h1>{{ number_format($total_expense, 2) }} <small style="color:rgba(255,255,255,0.8); font-size:18px;">Tk</small></h1>
        </div>
    </div>

    <div style="text-align: right; margin-bottom: 15px;" class="NoPrint">
        <button onclick="window.print()" class="btn btn-default"><i class="fa fa-print"></i> Print Statement</button>
    </div>

    <div class="table-responsive">
        <table class="table table-ledger">
            <thead>
                <tr>
                    <th width="10%">Date</th>
                    <th width="12%">Voucher No</th>
                    <th width="18%">Expense Head</th>
                    <th width="20%">Payment Method</th>
                    <th width="25%">Note</th>
                    <th width="15%" style="text-align:right;">Amount (Tk)</th>
                </tr>
            </thead>
            <tbody>
                @if(count($ledger_data) > 0)
                    @foreach($ledger_data as $row)
                        <tr>
                            <td>{{ date('d-M-Y', strtotime($row['date'])) }}</td>
                            <td>{{ $row['voucher'] }}</td>
                            <td><b>{{ $row['head_name'] }}</b></td>
                            <td>{!! $row['method_info'] !!}</td>
                            <td>{!! !empty($row['note']) ? htmlspecialchars($row['note']) : '<i>No remarks</i>' !!}</td>
                            <td class="amt">{{ number_format($row['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" class="text-center text-muted" style="padding:30px;">
                            No expenses found for the selected period.
                        </td>
                    </tr>
                @endif
            </tbody>
            @if(count($ledger_data) > 0)
            <tfoot class="row-footer">
                <tr>
                    <td colspan="5" style="text-align:right; padding:10px; font-size:16px;"><b>Total Office Expense for this Period:</b></td>
                    <td class="amt" style="padding:10px; font-size:18px;">{{ number_format($total_expense, 2) }}</td>
                </tr>
            </tfoot>
            @endif
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
