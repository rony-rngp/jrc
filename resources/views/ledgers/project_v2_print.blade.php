<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Ledger V2 - {{ $project_name }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #fff;
            color: #000;
            margin: 20px;
            padding: 0;
        }

        .no-print {
            margin-bottom: 20px;
        }

        .btn-print {
            background: #337ab7;
            color: #fff;
            border: none;
            padding: 8px 18px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 4px;
        }

        .v2-sheet-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0px;
        }
        .v2-sheet-header td, .v2-sheet-header th {
            border: 2px solid #222;
            padding: 8px 12px;
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

        .table-v2-statement {
            width: 100%;
            border-collapse: collapse;
            margin-top: -2px;
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
        .table-v2-statement tbody tr.highlight-cash {
            background: #fed8b1 !important;
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

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
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
</head>
<body>
    <div class="no-print" style="text-align: right;">
        <button onclick="window.print()" class="btn-print">Print / Save as PDF</button>
    </div>

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
                        No supplier bills or expenses found for this project in the selected period.
                    </td>
                </tr>
            @endforelse

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
</body>
</html>
