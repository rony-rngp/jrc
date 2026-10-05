@php
$trx_details = "";
if(!empty($trans_type_val)) {
    $trx_details .= $trans_type_val;
}
if(!empty($ref_info)) {
    $trx_details .= ($trx_details != "" ? " | " : "") . "No: " . $ref_info;
}
if(!empty($ref_date_val) && $ref_date_val != '0000-00-00' && $ref_date_val != '1970-01-01') {
    $trx_details .= ($trx_details != "" ? " | " : "") . "Date: " . date('d-M-Y', strtotime($ref_date_val));
}

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
    <title>Voucher - {{ $voucher_no }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body { font-family: 'Arial', sans-serif; background: #fff; margin: 0; padding: 0; }
        .invoice-box { width: 100%; max-width: 800px; margin: auto; padding: 20px 30px; box-sizing: border-box; }
        .logo-container { margin: -20px -30px 10px -30px !important; border-radius: 0 !important; }
        .receipt-header { text-align: center; margin-bottom: 20px; }
        .voucher-title { display: inline-block; background: #333; color: #fff; padding: 5px 20px; font-size: 18px; font-weight: bold; border-radius: 3px; text-transform: uppercase; }

        .info-table { width: 100%; margin-bottom: 20px; font-size: 14px; border-top: 1px solid #eee; padding-top: 15px; }
        .info-table td { padding: 5px; vertical-align: top; }
        .info-table .right-align { text-align: right; }

        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .details-table th, .details-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .details-table th { background: #f9f9f9; font-weight: bold; color: #333; }
        .details-table .amount-col { text-align: right; width: 150px; font-weight: bold; }
        
        .total-row td { font-weight: bold; font-size: 16px; background: #f1f1f1; }
        .balance-row td { font-weight: bold; font-size: 14px; background: #e8f5e9; color: #2e7d32; }
        .words-box { background: #fdfdfd; border: 1px dashed #ccc; padding: 10px; margin-bottom: 20px; font-style: italic; font-weight: bold; color: #444; font-size: 13px; }

        .signatures { width: 100%; margin-top: 50px; display: table; }
        .sig-box { display: table-cell; text-align: center; width: 33.33%; padding: 0 15px; }
        .sig-line { border-top: 1px solid #333; margin-top: 40px; padding-top: 5px; font-weight: bold; font-size: 13px; }

        .action-buttons { text-align: center; padding: 20px; background: #f0f0f0; }
        .btn { background: #00a65a; color: white; border: none; padding: 10px 20px; font-size: 16px; cursor: pointer; border-radius: 4px; display: inline-block; text-decoration: none; }

        @media print {
            body { background: white; padding: 0; margin: 0; }
            .action-buttons { display: none !important; }
            @page { size: A4; margin: 0; }
            .invoice-box { 
                box-shadow: none; border: none; width: 100%; max-width: 100%; 
                padding: 15mm; height: 148.5mm; overflow: hidden;
                border-bottom: 1px dashed #ccc; 
            }
        }
    </style>
</head>
<body>

    <div class="action-buttons">
        <a href="{{ route('payment-vouchers.index') }}" class="btn" style="background: #6c757d;"><i class="fa fa-arrow-left"></i> Go Back</a>
        <button onclick="window.print();" class="btn"><i class="fa fa-print"></i> Print Voucher</button>
    </div>

    <div class="invoice-box">
        @include('layouts.voucher_logo')

        <div class="receipt-header"><div class="voucher-title">Payment Voucher</div></div>

        <table class="info-table">
            <tr>
                <td><b>Voucher No:</b> {{ $voucher_no }}<br><b>Type:</b> {{ $payment_type_label }}</td>
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
                        <div style="font-size: 15px; margin-bottom: 8px;">{!! $paid_to !!}</div>
                        <div style="margin-top: 10px;"><b>Payment Via:</b> {{ $method_name }} {{ ($deposit_category !== 'N/A') ? "($deposit_category)" : "" }}</div>
                        
                        @if(!empty($trx_details))
                            <div style="margin-top: 5px;"><b>Trx/Check Details:</b> {{ $trx_details }}</div>
                        @endif

                        @if(!empty($note))<div style="margin-top: 10px; color: #555; font-size: 12px;"><b>Note:</b> {!! nl2br(e($note)) !!}</div>@endif
                    </td>
                    <td class="amount-col" style="vertical-align: bottom; font-size: 18px;">{{ number_format((float)$amount, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td style="text-align: right;">Total Amount Paid:</td>
                    <td class="amount-col"> {{ number_format((float)$amount, 2) }}</td>
                </tr>
                @if(!empty($current_balance_text))
                <tr class="balance-row">
                    <td style="text-align: right;">Status After This Payment:</td>
                    <td class="amount-col">{{ $current_balance_text }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        <div class="words-box"><b>In Words:</b> {{ $amount_in_words }}</div>

        <div class="signatures">
            <div class="sig-box"><div class="sig-line">Prepared By</div></div>
            <div class="sig-box"><div class="sig-line">Checked By</div></div>
            <div class="sig-box"><div class="sig-line">Receiver's Signature</div></div>
        </div>
    </div>
</body>
</html>
