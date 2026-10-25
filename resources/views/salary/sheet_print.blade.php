<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Employee Salary Sheet {{ $employee ? '- ' . $employee->firstname . ' ' . $employee->lastname : '' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background: #fff; 
            margin: 0; 
            padding: 15px; 
            color: #222; 
        }
        
        .print-container { 
            width: 100%; 
            max-width: 1050px; 
            margin: 0 auto; 
            background: #fff; 
        }

        .no-print { 
            text-align: right; 
            margin-bottom: 20px; 
            padding: 10px;
            background: #f7f7f7;
            border-bottom: 1px solid #ddd;
        }
        .btn { 
            background: #00a65a; 
            color: #fff; 
            padding: 8px 18px; 
            border: none; 
            cursor: pointer; 
            font-size: 14px; 
            font-weight: bold; 
            border-radius: 4px; 
        }
        .btn-close { 
            background: #dd4b39; 
            margin-left: 8px; 
        }

        .header-section {
            border-bottom: 2px solid #222;
            padding-bottom: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .company-info h2 {
            margin: 0 0 5px 0;
            font-size: 24px;
            font-weight: 800;
            text-transform: uppercase;
            color: #111;
        }
        .company-info p {
            margin: 2px 0;
            font-size: 13px;
            color: #555;
        }
        .sheet-title {
            text-align: right;
        }
        .sheet-title h3 {
            margin: 0 0 5px 0;
            background: #222;
            color: #fff;
            padding: 6px 16px;
            display: inline-block;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .sheet-title p {
            margin: 2px 0;
            font-size: 12px;
            color: #444;
        }

        .emp-detail-card {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 12px 18px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            font-size: 13px;
        }
        .emp-detail-card div {
            line-height: 1.6;
        }

        .table-sheet {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 25px;
        }
        .table-sheet th {
            background: #e6e6e6;
            color: #000;
            border: 1px solid #333;
            padding: 8px 6px;
            text-align: center;
            font-size: 11.5px;
            font-weight: bold;
        }
        .table-sheet td {
            border: 1px solid #555;
            padding: 6px;
            vertical-align: middle;
        }
        .table-sheet tr:nth-child(even) {
            background: #fafafa;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .amt {
            text-align: right;
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
            font-size: 12px;
        }
        .text-paid { color: #008d4c; font-weight: bold; }
        .text-due { color: #d33724; font-weight: bold; }

        .row-total {
            background: #eee !important;
            font-weight: bold;
            border-top: 2px solid #000 !important;
        }
        .row-total td {
            padding: 8px 6px;
            font-size: 12.5px;
        }

        .signatures {
            width: 100%;
            margin-top: 60px;
            border-collapse: collapse;
        }
        .signatures td {
            text-align: center;
            vertical-align: bottom;
            width: 33.33%;
            border: none;
            padding: 0 20px;
        }
        .sign-line {
            border-top: 1px solid #000;
            display: block;
            padding-top: 6px;
            font-size: 12px;
            font-weight: bold;
        }

        @media print {
            @page {
                size: landscape;
                margin: 10mm;
            }
            body { 
                margin: 0; 
                padding: 0; 
            }
            .no-print { 
                display: none !important; 
            }
            .table-sheet th {
                background: #e6e6e6 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .table-sheet td, .table-sheet th {
                border: 1px solid #000 !important;
            }
            .row-total {
                background: #eee !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print">
        <button onclick="window.print()" class="btn"><i class="fa fa-print"></i> Print Salary Sheet</button>
        <button onclick="window.close()" class="btn btn-close"><i class="fa fa-times"></i> Close Window</button>
    </div>

    <div class="print-container">
        <div class="header-section">
            <div class="company-info">
                <h2>JR Corporation</h2>
                <p>123 New Kakrail Road, Mohubon Super Market 1st Floor, Shantinagar, Dhaka</p>
                <p>Phone: 01791347550 &nbsp;|&nbsp; Accounts & Payroll Department</p>
            </div>
            <div class="sheet-title">
                <h3>Salary Statement Sheet</h3>
                <p><b>Period:</b> 
                    @if(!empty($from_date) || !empty($to_date))
                        {{ !empty($from_date) ? date('d-M-Y', strtotime($from_date)) : 'Start' }} to {{ !empty($to_date) ? date('d-M-Y', strtotime($to_date)) : 'Today' }}
                    @elseif(!empty($pay_month))
                        Month: {{ $pay_month }}
                    @else
                        All Time Records
                    @endif
                </p>
                <p>Date Printed: {{ date('d M Y, h:i A') }}</p>
            </div>
        </div>

        @if($employee)
        <div class="emp-detail-card">
            <div>
                <b>Employee Name:</b> {{ $employee->firstname }} {{ $employee->lastname }}<br>
                <b>Employee ID / Code:</b> {{ $employee->employee_id ?: 'EMP-' . str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}<br>
                <b>Designation:</b> {{ $employee->position ?: 'N/A' }}
            </div>
            <div>
                <b>Contact:</b> {{ $employee->contact_info ?: 'N/A' }}<br>
                <b>Base Salary:</b> ৳ {{ number_format((float)$employee->salary, 2) }}<br>
                <b>Total Slips:</b> {{ count($salaries) }} Record(s)
            </div>
        </div>
        @endif

        <table class="table-sheet">
            <thead>
                <tr>
                    <th width="4%">SL</th>
                    <th width="9%">Date</th>
                    @if(!$employee)
                        <th width="15%">Employee Name</th>
                    @endif
                    <th width="10%">Pay Month</th>
                    <th width="9%">Basic</th>
                    <th width="8%">Allowances</th>
                    <th width="9%">Gross</th>
                    <th width="8%">Deductions</th>
                    <th width="10%">Net Payable</th>
                    <th width="10%">Paid Amount</th>
                    <th width="9%">Due / Adv</th>
                    <th width="9%">Voucher</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $t_basic = 0;
                    $t_allow = 0;
                    $t_gross = 0;
                    $t_deduct = 0;
                    $t_net = 0;
                    $t_paid = 0;
                    $t_due = 0;
                @endphp
                @forelse($salaries as $idx => $s)
                    @php
                        $allowances = (float)$s->mobile_bill + (float)$s->lunch_bill + (float)$s->other_allowances + (float)$s->bonus;
                        $deductions = (float)$s->adv_deduct + (float)$s->fine + (float)$s->other_deduction;
                        $net = (float)$s->net_payable;
                        
                        $t_basic += (float)$s->main_salary;
                        $t_allow += $allowances;
                        $t_gross += (float)$s->gross_salary;
                        $t_deduct += $deductions;
                        $t_net += $net;
                        $t_paid += (float)$s->amount;
                        $t_due += (float)$s->due_amount;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="text-center">{{ !empty($s->date) && $s->date != '0000-00-00' ? date('d-M-Y', strtotime($s->date)) : '-' }}</td>
                        @if(!$employee)
                            <td><b>{{ $s->employee_name }}</b></td>
                        @endif
                        <td class="text-center"><b>{{ $s->pay_month }}</b></td>
                        <td class="amt">{{ number_format((float)$s->main_salary, 2) }}</td>
                        <td class="amt">{{ number_format($allowances, 2) }}</td>
                        <td class="amt">{{ number_format((float)$s->gross_salary, 2) }}</td>
                        <td class="amt">{{ number_format($deductions, 2) }}</td>
                        <td class="amt">{{ number_format($net, 2) }}</td>
                        <td class="amt text-paid">{{ number_format((float)$s->amount, 2) }}</td>
                        <td class="amt {{ (float)$s->due_amount > 0 ? 'text-due' : '' }}">
                            {{ number_format((float)$s->due_amount, 2) }}
                        </td>
                        <td class="text-center"><small>{{ $s->voucher_no ?: 'N/A' }}</small></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $employee ? 11 : 12 }}" class="text-center" style="padding: 25px; color: #777;">
                            No salary records found for this selection.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($employee)
            <tfoot>
                <tr class="row-total">
                    <td colspan="3" class="text-right">Total:</td>
                    <td class="amt">{{ number_format($t_basic, 2) }}</td>
                    <td class="amt">{{ number_format($t_allow, 2) }}</td>
                    <td class="amt">{{ number_format($t_basic + $t_allow, 2) }}</td>
                    <td class="amt">{{ number_format($t_deduct, 2) }}</td>
                    <td class="amt">{{ number_format(($t_basic + $t_allow) - $t_deduct, 2) }}</td>
                    <td class="amt text-paid">{{ number_format($t_paid, 2) }}</td>
                    <td class="amt {{ (count($salaries) > 0 ? (float)$salaries->last()->due_amount : 0) > 0 ? 'text-due' : '' }}">
                        {{ count($salaries) > 0 ? number_format((float)$salaries->last()->due_amount, 2) : '0.00' }}
                    </td>
                    <td class="text-center">
                        @if(count($salaries) > 0)
                            <small class="text-muted"><i class="fa fa-info-circle"></i> Closing Due</small>
                        @endif
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>

        <table class="signatures">
            <tr>
                <td><span class="sign-line">Prepared By</span></td>
                <td><span class="sign-line">Checked By</span></td>
                <td><span class="sign-line">Managing Director / Authority</span></td>
            </tr>
        </table>
    </div>
</body>
</html>
