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
        $str = array_reverse($str); $result = implode('', $str); $result = trim(preg_replace('/\s+/', ' ', $result));
        $paisa_str = '';
        if ($point > 0) {
            if ($point < 21) $paisa_str = " and " . $words[$point] . " Paisa"; 
            else $paisa_str = " and " . $words[floor($point / 10) * 10] . " " . $words[$point % 10] . " Paisa";
        }
        return ($result ? $result . " Taka" : "Zero Taka") . $paisa_str . " Only.";
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
        body { font-family: 'Arial', sans-serif; background: #f4f6f9; margin: 0; padding: 20px 0; color: #222; font-size: 13px; }
        .invoice-box { 
            width: 100%; 
            max-width: 800px; 
            margin: auto; 
            padding: 15px 25px 20px 25px; 
            box-sizing: border-box; 
            background: #fff; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.08); 
            border-radius: 6px; 
        }
        .invoice-box .logo-container { 
            margin: -15px -25px 8px -25px !important; 
            border-bottom: 2px solid #eee !important; 
            border-radius: 6px 6px 0 0 !important; 
        }
        .invoice-box .logo-container img {
            width: 100% !important;
            height: auto !important;
            display: block !important;
        }
        .receipt-header { text-align: center; margin: 4px 0 8px 0; }
        .voucher-title { 
            display: inline-block; 
            border: 1.5px solid #333; 
            background: #f4f4f4; 
            color: #111; 
            padding: 2px 18px; 
            font-size: 13.5px; 
            font-weight: 700; 
            border-radius: 3px; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
        }

        .info-table { width: 100%; margin-bottom: 6px; font-size: 12px; border-top: 1px solid #eee; padding-top: 4px; line-height: 1.4; }
        .info-table td { padding: 2px 4px; vertical-align: top; }
        .info-table .right-align { text-align: right; }

        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; font-size: 12px; }
        .details-table th, .details-table td { border: 1px solid #ccc; padding: 5px 8px; text-align: left; }
        .details-table th { background: #f7f7f7; font-weight: bold; color: #222; font-size: 12px; }
        .details-table .amount-col { text-align: right; width: 140px; font-weight: bold; white-space: nowrap; }
        
        .total-row td { font-weight: bold; font-size: 13px; background: #f0f0f0; }
        .balance-row td { font-weight: bold; font-size: 12px; background: #edf7ed; color: #2e7d32; }
        .words-box { background: #fafafa; border: 1px dashed #ccc; padding: 4px 8px; margin-bottom: 6px; font-style: italic; font-weight: 600; color: #333; font-size: 11.5px; line-height: 1.35; }

        .signatures { width: 100%; margin-top: 15px; display: table; }
        .sig-box { display: table-cell; text-align: center; width: 33.33%; padding: 0 10px; vertical-align: bottom; }
        .sig-line { border-top: 1.2px solid #333; margin-top: 25px; padding-top: 4px; font-weight: bold; font-size: 11.5px; color: #222; }

        .action-buttons { text-align: center; padding: 15px; margin-bottom: 15px; background: #e9ecef; border-bottom: 1px solid #ddd; }
        .btn { background: #00a65a; color: white; border: none; padding: 8px 18px; font-size: 14px; cursor: pointer; border-radius: 4px; display: inline-block; text-decoration: none; margin: 0 5px; }

        /* প্রিন্টের জন্য হাফ পেজ সেটআপ */
        @media print {
            body { background: white !important; padding: 0 !important; margin: 0 !important; }
            .action-buttons { display: none !important; }
            @page { 
                size: A4 portrait; 
                margin: 0; 
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .invoice-box { 
                box-shadow: none !important; 
                border: none !important; 
                border-radius: 0 !important; 
                width: 100% !important; 
                max-width: 100% !important; 
                margin: 0 !important; 
                padding: 2mm 8mm 3mm 8mm !important; 
                height: 148.5mm !important; 
                max-height: 148.5mm !important; 
                overflow: hidden !important;
                box-sizing: border-box !important;
                border-bottom: 1px dashed #777 !important; 
                display: flex !important;
                flex-direction: column !important;
                justify-content: flex-start !important;
                position: relative !important;
            }
            .invoice-box .logo-container { 
                margin: 0 0 4px 0 !important; 
                border-radius: 0 !important; 
                border-bottom: 1.5px solid #ccc !important; 
            }
            .invoice-box .logo-container img {
                width: 100% !important;
                height: auto !important;
                display: block !important;
            }
            .invoice-box .signatures {
                margin-top: auto !important;
                padding-bottom: 2mm !important;
            }
        }
    </style>
</head>
<body onload="window.print();">

    <div class="action-buttons">
        <a href="{{ route('receive-vouchers.index') }}" class="btn" style="background: #6c757d;"><i class="fa fa-arrow-left"></i> Go Back</a>
        <button onclick="window.print();" class="btn"><i class="fa fa-print"></i> Print Receipt</button>
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
                    <td style="vertical-align: middle;">
                        <div style="font-size: 13px; font-weight: 600; margin-bottom: 3px;">{!! $received_from !!}</div>
                        <div style="margin-top: 3px; font-size: 12px;"><b>Received Via:</b> {{ $method_name }}</div>
                        @if(!empty($ref_info))<div style="margin-top: 2px; font-size: 11px; color: #444;"><b>Transaction Info:</b> {{ $ref_info }}</div>@endif
                        @if(!empty($note))<div style="margin-top: 3px; color: #555; font-size: 11px;"><b>Note:</b> {!! nl2br(e($note)) !!}</div>@endif
                    </td>
                    <td class="amount-col" style="vertical-align: middle; font-size: 15px;">{{ number_format((float)$amount, 2) }}</td>
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
