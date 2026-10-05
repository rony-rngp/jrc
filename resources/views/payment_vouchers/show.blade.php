<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Voucher - {{ $voucher_no }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body { font-family: 'Arial', sans-serif; background: #f0f0f0; margin: 0; padding: 20px; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); background: #fff; position: relative; }
        
        .header-top { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 15px; }
        .header-top h1 { margin: 0; font-size: 24px; color: #2c3e50; font-weight: bold; text-transform: uppercase; }
        .header-top p { margin: 0; font-size: 12px; color: #555; }
        
        .receipt-header { text-align: center; margin-bottom: 20px; }
        .voucher-title { display: inline-block; background: #d9534f; color: #fff; padding: 5px 20px; font-size: 18px; font-weight: bold; border-radius: 3px; margin-top: 5px; text-transform: uppercase; letter-spacing: 1px;}
        .info-table { width: 100%; margin-bottom: 20px; font-size: 14px; border-top: 1px solid #eee; padding-top: 15px; }
        .info-table td { padding: 5px; vertical-align: top; }
        .info-table .right-align { text-align: right; }
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .details-table th, .details-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .details-table th { background: #f9f9f9; font-weight: bold; color: #333; }
        .details-table .amount-col { text-align: right; width: 150px; font-weight: bold; }
        .total-row td { font-weight: bold; font-size: 16px; background: #f1f1f1; }
        .balance-row td { font-weight: bold; font-size: 14px; background: #fde8e8; color: #c53030; }
        .words-box { background: #fdfdfd; border: 1px dashed #ccc; padding: 10px; margin-bottom: 40px; font-style: italic; font-weight: bold; color: #444; }
        .signatures { width: 100%; margin-top: 50px; display: table; }
        .sig-box { display: table-cell; text-align: center; width: 33.33%; padding: 0 15px; }
        .sig-line { border-top: 1px solid #333; margin-top: 40px; padding-top: 5px; font-weight: bold; font-size: 14px; }
        .action-buttons { text-align: center; margin-bottom: 20px; }
        .btn { background: #d9534f; color: white; border: none; padding: 10px 20px; font-size: 16px; cursor: pointer; border-radius: 4px; display: inline-block; text-decoration: none; }

        @media print {
            body { background: white; padding: 0; margin: 0; }
            .action-buttons { display: none !important; }
            @page { size: A4; margin: 0; }
            .invoice-box { 
                box-shadow: none; border: none; width: 100%; max-width: 100%; 
                padding: 15mm; margin: 0;
                box-sizing: border-box;
            }
        }
    </style>
</head>
<body>

    <div class="action-buttons">
        <a href="{{ route('payment-vouchers.index') }}" class="btn" style="background: #6c757d;">Go Back</a>
        <button onclick="window.print();" class="btn"><i class="fa fa-print"></i> Print Voucher</button>
    </div>

    <div class="invoice-box">
        <div class="header-top">
            <div>
                <h1>JR Corporation</h1>
                <p>Project Construction & Engineering Solutions</p>
            </div>
            <div style="text-align:right;">
                <p><b>Dhaka, Bangladesh</b></p>
                <p>Accounts & Financial Dept.</p>
            </div>
        </div>

        <div class="receipt-header"><div class="voucher-title">Payment / Debit Voucher</div></div>

        <table class="info-table">
            <tr>
                <td><b>Voucher No:</b> {{ $voucher_no }}<br><b>Payment Type:</b> {{ $payment_type_label }}</td>
                <td class="right-align"><b>Date:</b> {{ date('d M, Y', strtotime($t_date)) }}<br><b>Printed On:</b> {{ date('d M, Y h:i A') }}</td>
            </tr>
        </table>

        <table class="details-table">
            <thead>
                <tr><th>Description / Particulars</th><th class="amount-col">Amount (Tk)</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div style="font-size: 16px; margin-bottom: 8px;">{!! $paid_to !!}</div>
                        <div style="margin-top: 15px;"><b>Paid Via:</b> {{ $method_name }} ({{ $deposit_category }})</div>
                        @if(!empty($ref_info))<div style="margin-top: 5px;"><b>Transaction / Ref:</b> {{ $ref_info }}</div>@endif
                        @if(!empty($note))<div style="margin-top: 15px; color: #555;"><b>Note / Purpose:</b> {!! nl2br(e($note)) !!}</div>@endif
                    </td>
                    <td class="amount-col" style="vertical-align: bottom; font-size: 18px; color:#d9534f;">৳ {{ number_format($amount, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td style="text-align: right;">Total Amount Paid:</td>
                    <td class="amount-col" style="color:#d9534f;">৳ {{ number_format($amount, 2) }}</td>
                </tr>
                @if(!empty($current_balance_text))
                <tr class="balance-row">
                    <td style="text-align: right;">Balance Status:</td>
                    <td class="amount-col">{{ $current_balance_text }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        <div class="words-box"><b>Total Paid Amount:</b> ৳ {{ number_format($amount, 2) }}</div>

        <div class="signatures">
            <div class="sig-box"><div class="sig-line">Receiver / Payee</div></div>
            <div class="sig-box"><div class="sig-line">Prepared By</div></div>
            <div class="sig-box"><div class="sig-line">Authorized Signature</div></div>
        </div>
    </div>
</body>
</html>
