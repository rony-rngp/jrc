@php
if (!function_exists('getBdAmountInWords')) {
    function getBdAmountInWords($number) {
        $no = (int)floor($number);
        $point = (int)round(($number - $no) * 100);
        $words = array(0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety');
        $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
        $i = 0; $str = array(); $digits_length = strlen($no);
        while ($i < $digits_length) {
            $divider = ($i == 2) ? 10 : 100; $value = floor($no % $divider); $no = floor($no / $divider); $i += ($divider == 10) ? 1 : 2;
            if ($value) {
                $counter = count($str); $plural = ($counter && $value > 9) ? '' : null; $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                if ($value < 21) $str[] = $words[$value] . ' ' . $digits[$counter] . $plural . $hundred;
                else $str[] = $words[floor($value / 10) * 10] . ' ' . $words[$value % 10] . ' ' . $digits[$counter] . $plural . $hundred;
            } else $str[] = null;
        }
        $result = implode('', array_reverse($str));
        return ($result ? $result : "Zero") . " Taka Only.";
    }
}
$amount_in_words = getBdAmountInWords((float)$amount);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Money Receipt - {{ $voucher_no }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body { font-family: 'Arial', sans-serif; background: #f0f0f0; margin: 0; padding: 20px; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); background: #fff; position: relative; }
        .invoice-box .logo-container { margin: -30px -30px 10px -30px !important; border-radius: 0 !important; }
        .receipt-header { text-align: center; margin-bottom: 20px; }
        .voucher-title { display: inline-block; background: #333; color: #fff; padding: 5px 20px; font-size: 18px; font-weight: bold; border-radius: 3px; margin-top: 5px; text-transform: uppercase; letter-spacing: 1px;}
        .info-table { width: 100%; margin-bottom: 20px; font-size: 14px; border-top: 1px solid #eee; padding-top: 15px; }
        .info-table td { padding: 5px; vertical-align: top; }
        .info-table .right-align { text-align: right; }
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .details-table th, .details-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .details-table th { background: #f9f9f9; font-weight: bold; color: #333; }
        .details-table .amount-col { text-align: right; width: 150px; font-weight: bold; }
        .total-row td { font-weight: bold; font-size: 16px; background: #f1f1f1; }
        .balance-row td { font-weight: bold; font-size: 14px; background: #e8f5e9; color: #2e7d32; }
        .words-box { background: #fdfdfd; border: 1px dashed #ccc; padding: 10px; margin-bottom: 40px; font-style: italic; font-weight: bold; color: #444; }
        .signatures { width: 100%; margin-top: 50px; display: table; }
        .sig-box { display: table-cell; text-align: center; width: 33.33%; padding: 0 15px; }
        .sig-line { border-top: 1px solid #333; margin-top: 40px; padding-top: 5px; font-weight: bold; font-size: 14px; }
        .action-buttons { text-align: center; margin-bottom: 20px; }
        .btn { background: #00a65a; color: white; border: none; padding: 10px 20px; font-size: 16px; cursor: pointer; border-radius: 4px; display: inline-block; text-decoration: none; }

        /* প্রিন্টের জন্য হাফ পেজ সেটআপ */
        @media print {
            body { background: white; padding: 0; margin: 0; }
            .action-buttons { display: none !important; }
            @page { size: A4; margin: 0; }
            .invoice-box { 
                box-shadow: none; border: none; width: 100%; max-width: 100%; 
                padding: 15mm; margin: 0;
                height: 148.5mm; /* A4 এর অর্ধেক */
                box-sizing: border-box;
                border-bottom: 1px dashed #ccc; /* কাটার গাইড */
            }
        }
    </style>
</head>
<body>

    <div class="action-buttons">
        <a href="{{ route('receive-vouchers.index') }}" class="btn" style="background: #6c757d;">Go Back</a>
        <button onclick="window.print();" class="btn">Print Receipt</button>
    </div>

    <div class="invoice-box">
        @include('layouts.voucher_logo')
        <div class="receipt-header"><div class="voucher-title">Money Receipt</div></div>

        <table class="info-table">
            <tr>
                <td><b>Receipt No:</b> {{ $voucher_no }}<br><b>Receive Type:</b> {{ $receive_type_label }}</td>
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
                        <div style="font-size: 16px; margin-bottom: 8px;">{!! $received_from !!}</div>
                        <div style="margin-top: 15px;"><b>Received Via:</b> {{ $method_name }}</div>
                        @if(!empty($ref_info))<div style="margin-top: 5px;"><b>Transaction Info:</b> {{ $ref_info }}</div>@endif
                        @if(!empty($note))<div style="margin-top: 15px; color: #555;"><b>Note:</b> {!! nl2br(e($note)) !!}</div>@endif
                    </td>
                    <td class="amount-col" style="vertical-align: bottom; font-size: 18px;">{{ number_format((float)$amount, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td style="text-align: right;">Total Amount Received:</td>
                    <td class="amount-col"> {{ number_format((float)$amount, 2) }}</td>
                </tr>
                @if(!empty($current_balance_text))
                <tr class="balance-row">
                    <td style="text-align: right;">Status After This Receive:</td>
                    <td class="amount-col">{{ $current_balance_text }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        <div class="words-box"><b>In Words:</b> {{ $amount_in_words }}</div>

        <div class="signatures">
            <div class="sig-box"><div class="sig-line">Payer / Depositor</div></div>
            <div class="sig-box"><div class="sig-line">Prepared By</div></div>
            <div class="sig-box"><div class="sig-line">Authorized Signature</div></div>
        </div>
    </div>
</body>
</html>
