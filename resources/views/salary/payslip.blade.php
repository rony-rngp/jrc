<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip - {{ $salary->employee_name }}</title>
    <style>
        body { font-family: Arial, sans-serif; background: #fff; padding: 20px; font-size: 14px; color: #222; }
        .slip-container { max-width: 800px; margin: 0 auto; border: 1px solid #000; padding: 25px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; color: #111; text-transform: uppercase; }
        .header p { margin: 5px 0 0 0; }
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 6px; border-bottom: 1px dotted #ccc; font-size: 13.5px; }
        .calc-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .calc-table th, .calc-table td { border: 1px solid #000; padding: 8px; text-align: left; }
        .calc-table th { background-color: #f2f2f2; text-align: center; font-size: 13.5px; }
        .amount-col { text-align: right !important; font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .total-row { font-weight: bold; background-color: #e8e8e8; }
        
        .payment-summary { float: right; width: 350px; margin-top: 10px; border: 1px solid #ddd; padding: 12px; border-radius: 5px; background: #fdfdfd; }
        .payment-summary table { width: 100%; }
        .payment-summary td { padding: 5px 0; font-size: 15px; }
        .net-pay-text { font-weight: bold; }
        .actual-paid { font-size: 18px; font-weight: bold; color: #0073b7; border-top: 1px solid #ccc; padding-top: 5px; margin-top: 5px; }
        .due-adv-text { font-size: 16px; font-weight: bold; }
        .text-red { color: #dd4b39; }
        .text-green { color: #00a65a; }

        .signatures { width: 100%; margin-top: 100px; clear: both; }
        .signatures td { text-align: center; vertical-align: bottom; width: 33%; }
        .sign-line { border-top: 1px solid #000; display: inline-block; width: 160px; padding-top: 5px; font-size: 12px; font-weight: bold; }
        
        @media print {
            .no-print { display: none; }
            .slip-container { border: none; padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="text-align: right; margin-bottom: 15px; max-width: 800px; margin: 0 auto;">
        <button onclick="window.print()" style="padding: 8px 18px; font-size: 14px; cursor: pointer; background: #00a65a; color: #fff; border: none; border-radius: 4px;">🖨️ Print Slip</button>
        <button onclick="window.close()" style="padding: 8px 18px; font-size: 14px; cursor: pointer; background: #dd4b39; color: #fff; border: none; border-radius: 4px; margin-left: 5px;">Close</button>
    </div>
    
    <div class="slip-container">
        <div class="header">
            <h1>JR Corporation</h1>
            <p>123 New Kakrail Road, Mohubon Super Market 1st Floor, Shantinagar, Dhaka</p>
            <p style="margin-top: 8px; font-size: 15px;">Payslip for the month of <b>{{ $salary->pay_month }}</b></p>
        </div>

        <table class="info-table">
            <tr>
                <td width="20%"><b>Employee Name:</b></td>
                <td width="30%">{{ $salary->employee_name }}</td>
                <td width="20%"><b>Payment Date:</b></td>
                <td width="30%">{{ !empty($salary->date) && $salary->date != '0000-00-00' ? date('d-M-Y', strtotime($salary->date)) : '-' }}</td>
            </tr>
            <tr>
                <td><b>Employee ID:</b></td>
                <td>{{ $salary->employee->employee_id ?? ('EMP-' . str_pad($salary->employee_id, 4, '0', STR_PAD_LEFT)) }}</td>
                <td><b>Voucher No:</b></td>
                <td>{{ !empty($salary->voucher_no) ? $salary->voucher_no : 'N/A' }}</td>
            </tr>
            <tr>
                <td><b>Designation:</b></td>
                <td>{{ $salary->employee->position ?? 'Staff' }}</td>
                <td><b>Pay Method:</b></td>
                <td>{{ !empty($salary->payment_category) ? $salary->payment_category : 'Cash' }} ({{ $salary->method_name }})</td>
            </tr>
        </table>

        <table class="calc-table">
            <tr>
                <th width="50%">Earnings</th>
                <th width="50%">Deductions</th>
            </tr>
            <tr>
                <td style="vertical-align: top;">
                    <table width="100%">
                        <tr><td>Basic Salary</td><td class="amount-col">{{ number_format((float)$salary->main_salary, 2) }}</td></tr>
                        <tr><td>Mobile Bill</td><td class="amount-col">{{ number_format((float)$salary->mobile_bill, 2) }}</td></tr>
                        <tr><td>Lunch Bill</td><td class="amount-col">{{ number_format((float)$salary->lunch_bill, 2) }}</td></tr>
                        <tr><td>Other Allowances</td><td class="amount-col">{{ number_format((float)$salary->other_allowances, 2) }}</td></tr>
                        <tr><td>Bonus</td><td class="amount-col">{{ number_format((float)$salary->bonus, 2) }}</td></tr>
                    </table>
                </td>
                <td style="vertical-align: top;">
                    <table width="100%">
                        <tr><td>Advance Deduction</td><td class="amount-col">{{ number_format((float)$salary->adv_deduct, 2) }}</td></tr>
                        <tr><td>Fine / Absent</td><td class="amount-col">{{ number_format((float)$salary->fine, 2) }}</td></tr>
                        <tr><td>Other Deductions</td><td class="amount-col">{{ number_format((float)$salary->other_deduction, 2) }}</td></tr>
                    </table>
                </td>
            </tr>
            <tr class="total-row">
                <td>
                    <table width="100%"><tr><td><b>Total Gross</b></td><td class="amount-col"><b>{{ number_format((float)$salary->gross_salary, 2) }}</b></td></tr></table>
                </td>
                <td>
                    <table width="100%"><tr><td><b>Total Deductions</b></td><td class="amount-col"><b>{{ number_format((float)$salary->adv_deduct + (float)$salary->fine + (float)$salary->other_deduction, 2) }}</b></td></tr></table>
                </td>
            </tr>
        </table>

        <div style="margin-top: 10px; float: left; width: 380px;">
            <b>Remarks / Note:</b><br>
            {{ !empty($salary->note) ? $salary->note : 'N/A' }}<br><br>
            @if(!empty($salary->trans_ref_no))
                <b>Trx Info:</b> {{ $salary->trans_type }} - {{ $salary->trans_ref_no }}
            @endif
        </div>

        <div class="payment-summary">
            <table>
                <tr class="net-pay-text">
                    <td>Net Payable:</td>
                    <td class="amount-col">৳ {{ number_format((float)$salary->net_payable, 2) }}</td>
                </tr>
                <tr class="actual-paid">
                    <td>Actual Paid:</td>
                    <td class="amount-col">৳ {{ number_format((float)$salary->amount, 2) }}</td>
                </tr>
                @if((float)$salary->due_amount > 0)
                <tr class="due-adv-text text-red">
                    <td>Due (Unpaid):</td>
                    <td class="amount-col">৳ {{ number_format((float)$salary->due_amount, 2) }}</td>
                </tr>
                @elseif((float)$salary->due_amount < 0)
                <tr class="due-adv-text text-green">
                    <td>Extra Advance:</td>
                    <td class="amount-col">৳ {{ number_format(abs((float)$salary->due_amount), 2) }}</td>
                </tr>
                @endif
            </table>
        </div>

        <table class="signatures">
            <tr>
                <td><span class="sign-line">Employee Signature</span></td>
                <td><span class="sign-line">Prepared By</span></td>
                <td><span class="sign-line">Authorized Signatory</span></td>
            </tr>
        </table>
    </div>
</body>
</html>
