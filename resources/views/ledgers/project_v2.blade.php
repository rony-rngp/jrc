@extends('layouts.master')

@section('title', 'Project Ledger V2 - JRC ERP')
@section('page_title', 'Project Ledger V2')

@push('styles')
<style>
    /* Professional Statement / Excel Sheet Styling matching reference image */
    .ledger-v2-card {
        background: #fff;
        padding: 30px;
        border-radius: 6px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        margin-bottom: 30px;
        border-top: 4px solid #1e282c;
    }

    .v2-sheet-header {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0px;
    }
    .v2-sheet-header td, .v2-sheet-header th {
        border: 2px solid #222;
        padding: 8px 12px;
        font-family: Arial, sans-serif;
    }

    .project-details-box {
        width: 35%;
        background: #fff;
        vertical-align: top;
    }
    .project-details-box .hd-title {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        color: #333;
        border-bottom: 1px solid #ccc;
        padding-bottom: 4px;
        margin-bottom: 6px;
    }
    .project-details-box .client-name {
        font-size: 14px;
        font-weight: 700;
        color: #000;
        margin-bottom: 4px;
    }
    .project-details-box .call-box {
        font-size: 13px;
        font-weight: 700;
        color: #111;
        margin-bottom: 4px;
    }
    .project-details-box .addr-box {
        font-size: 12px;
        color: #333;
        line-height: 1.3;
    }

    .project-title-center {
        text-align: center;
        vertical-align: middle;
        background: #fff;
    }
    .project-title-center h2 {
        margin: 0;
        font-size: 20px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #000;
    }
    .project-title-center h3 {
        margin: 8px 0 0 0;
        font-size: 22px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #000;
    }

    .dates-table {
        width: 100%;
        border-collapse: collapse;
    }
    .dates-table td {
        border: 1px solid #222 !important;
        padding: 6px 10px !important;
        font-size: 13px;
        font-weight: 700;
    }
    .dates-table .date-lbl {
        background: #f7f7f7;
        width: 50%;
        text-align: right;
    }
    .dates-table .date-val {
        text-align: center;
        width: 50%;
        background: #fff;
    }

    /* Statement Main Table */
    .table-v2-statement {
        width: 100%;
        border-collapse: collapse;
        margin-top: -2px; /* Connect seamlessly with header */
        font-family: Arial, sans-serif;
    }
    .table-v2-statement th, .table-v2-statement td {
        border: 1px dotted #555;
        border-left: 1px solid #222;
        border-right: 1px solid #222;
        padding: 8px 10px;
        font-size: 13px;
    }
    .table-v2-statement thead th {
        background: #bcbcbc;
        color: #000;
        font-weight: 800;
        font-size: 14px;
        text-align: center;
        border-top: 2px solid #222;
        border-bottom: 2px solid #222;
        padding: 10px;
    }
    .table-v2-statement tbody tr {
        background: #fff;
    }
    .table-v2-statement tbody tr:nth-child(even) {
        background: #fbfbfb;
    }
    .table-v2-statement tbody tr.highlight-cash {
        background: #fed8b1 !important; /* Soft peach highlight as seen in reference image */
        font-weight: 600;
    }
    .table-v2-statement td.text-sn {
        text-align: center;
        font-weight: 600;
        width: 45px;
    }
    .table-v2-statement td.text-supplier {
        font-weight: 700;
        text-transform: uppercase;
    }
    .table-v2-statement td.text-particular {
        text-transform: uppercase;
        font-size: 12.5px;
    }
    .table-v2-statement td.text-qty {
        text-align: right;
        white-space: nowrap;
        font-weight: 600;
        width: 90px;
    }
    .table-v2-statement td.text-amount {
        text-align: right;
        font-family: 'Courier New', Courier, monospace;
        font-size: 14px;
        font-weight: 700;
        width: 130px;
    }
    .table-v2-statement td.text-tot-amount {
        text-align: right;
        font-family: 'Courier New', Courier, monospace;
        font-size: 14px;
        font-weight: 700;
        width: 140px;
    }
    .table-v2-statement td.text-remarks {
        text-align: center;
        width: 110px;
        font-size: 12px;
    }

    /* Summary Footer Rows */
    .row-total-cost {
        background: #000 !important;
        color: #fff !important;
        font-weight: 900;
        font-size: 15px;
    }
    .row-total-cost td {
        border-top: 2px solid #000 !important;
        border-bottom: 2px solid #000 !important;
        padding: 10px !important;
        color: #fff !important;
    }
    .row-payment-receive {
        background: #fff !important;
        font-weight: 800;
        font-size: 14px;
    }
    .row-payment-receive td {
        padding: 8px 10px !important;
        border-top: 1px dotted #333 !important;
        border-bottom: 1px dotted #333 !important;
    }
    .row-cost-recover {
        background: #fff !important;
        font-weight: 900;
        font-size: 15px;
    }
    .row-cost-recover td {
        padding: 8px 10px !important;
        border-bottom: 2px solid #222 !important;
    }
    .text-recover-label {
        color: #c0392b;
        font-weight: 900;
        text-align: right;
    }
    .text-recover-val {
        color: #c0392b;
        font-family: 'Courier New', Courier, monospace;
        text-align: right;
        font-weight: 900;
        font-size: 16px;
    }

    /* Action bar */
    .actions-bar {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-bottom: 15px;
    }

    /* Print styles */
    @media print {
        body { background: #fff !important; }
        .main-header, .main-sidebar, .content-header, .search-box, .btn, .main-footer, .actions-bar, .no-print {
            display: none !important;
        }
        .content-wrapper {
            margin-left: 0 !important;
            padding: 0 !important;
        }
        .ledger-v2-card {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .v2-sheet-header td, .v2-sheet-header th,
        .table-v2-statement th, .table-v2-statement td {
            border-color: #000 !important;
        }
        .table-v2-statement thead th {
            background: #d8d8d8 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .table-v2-statement tbody tr.highlight-cash {
            background: #fed8b1 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .row-total-cost {
            background: #000 !important;
            color: #fff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .row-total-cost td {
            color: #fff !important;
        }
    }
</style>
@endpush

@section('content')
<div class="box box-primary search-box no-print">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-filter"></i> Select Project & Date Range</h3>
        <div class="box-tools pull-right">
            <span class="label label-primary">Project Ledger V2</span>
        </div>
    </div>
    <div class="box-body" style="padding: 20px;">
        <form method="GET" action="{{ route('ledgers.project_v2') }}">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="text-muted">Choose Project <span class="text-danger">*</span></label>
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
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="text-muted">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $from_date }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="text-muted">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $to_date }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="text-muted">View Mode</label>
                        <select class="form-control" name="view_mode">
                            <option value="detailed" {{ $view_mode == 'detailed' ? 'selected' : '' }}>Detailed (Per Bill/Vch)</option>
                            <option value="summary" {{ $view_mode == 'summary' ? 'selected' : '' }}>Summary (Per Supplier)</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group" style="padding-top: 25px;">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-file-text-o"></i> View Statement
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@if($show_report && $proj_info)
<div class="actions-bar no-print">
    {{-- <a href="{{ route('ledgers.project_v2_print', ['project_id' => $project_id, 'from_date' => $from_date, 'to_date' => $to_date, 'view_mode' => $view_mode]) }}" target="_blank" class="btn btn-default">
        <i class="fa fa-external-link"></i> Open Print Page
    </a> --}}
    <button onclick="window.print()" class="btn btn-success">
        <i class="fa fa-print"></i> Print Ledger V2
    </button>
</div>

<div class="ledger-v2-card">
    <!-- Top Details & Header Table -->
    <table class="v2-sheet-header">
        <tr>
            <!-- Left: Project Client Info -->
            <td class="project-details-box">
                <div class="hd-title">PROJECT DETAILS</div>
                <div class="client-name">{{ $client_person ?: ($company_name ?: $project_name) }}</div>
                <div class="call-box">Call &nbsp; <b>{{ $client_contact ?: 'N/A' }}</b></div>
                <div class="addr-box">{{ $client_address ?: '82, Awlad Hossain Market 4th Floor, Tejkunipara' }}</div>
            </td>

            <!-- Center & Right: Title and Dates -->
            <td style="padding: 0; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td colspan="2" style="border: none; border-bottom: 2px solid #222; text-align: center; padding: 12px 10px;">
                            <h2 style="margin: 0; font-size: 22px; font-weight: 900; text-transform: uppercase; color: #000; letter-spacing: 0.5px;">
                                {{ $project_name }}
                            </h2>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 50%; border: none; border-right: 2px solid #222; text-align: center; vertical-align: middle; padding: 10px;">
                            <h3 style="margin: 0; font-size: 22px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; color: #000;">
                                PROJECT LEDGER
                            </h3>
                        </td>
                        <td style="width: 50%; padding: 0; border: none;">
                            <table class="dates-table">
                                <tr>
                                    <td class="date-lbl">Project Start Date</td>
                                    <td class="date-val">
                                        {{ !empty($project_start_date) && $project_start_date != '0000-00-00' ? date('d.m.Y', strtotime($project_start_date)) : '15.09.2026' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="date-lbl">Project Ending Date</td>
                                    <td class="date-val">
                                        {{ !empty($project_end_date) && $project_end_date != '0000-00-00' ? date('d.m.Y', strtotime($project_end_date)) : '30.09.2026' }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Main Statement Table -->
    <table class="table-v2-statement">
        <thead>
            <tr>
                <th style="width: 45px;">SN</th>
                <th style="width: 250px;">Supplyer Name</th>
                <th>Perticular</th>
                <th style="width: 90px;">QTY</th>
                <th style="width: 130px;">Amount</th>
                <th style="width: 140px;">Total Amount</th>
                <th style="width: 110px;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $idx => $r)
                <tr class="{{ $r['is_cash'] ? 'highlight-cash' : '' }}">
                    <td class="text-sn">{{ $idx + 1 }}</td>
                    <td class="text-supplier">{{ $r['supplier_name'] }}</td>
                    <td class="text-particular">{{ $r['particular'] }}</td>
                    <td class="text-qty">{{ $r['qty'] }}</td>
                    <td class="text-amount">{{ number_format($r['amount'], 2) }}</td>
                    <td class="text-tot-amount">{{ number_format($r['total_amount'], 2) }}</td>
                    <td class="text-remarks">{{ $r['remarks'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding: 25px; color: #777;">
                        <i class="fa fa-info-circle"></i> No supplier bills or expenses found for this project in the selected period.
                    </td>
                </tr>
            @endforelse

            <!-- Summary Totals matching reference format -->
            <tr class="row-total-cost">
                <td colspan="5" style="text-align: right; letter-spacing: 0.5px;">
                    TOATAL PROJECT COST
                </td>
                <td class="text-tot-amount" style="color: #fff; font-size: 16px;">
                    {{ number_format($total_project_cost, 2) }}
                </td>
                <td></td>
            </tr>

            <tr class="row-payment-receive">
                <td colspan="5" style="text-align: right; font-weight: 800; color: #111;">
                    PAYMENT RECEIVE
                </td>
                <td class="text-tot-amount" style="font-size: 15px; color: #000;">
                    {{ number_format($payment_receive, 2) }}
                </td>
                <td></td>
            </tr>

            <tr class="row-cost-recover">
                <td colspan="5" class="text-recover-label">
                    {{ $status_label }}
                </td>
                <td class="text-recover-val">
                    {{ number_format(abs($cost_recover), 2) }}
                </td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>
@endif
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
@endpush
