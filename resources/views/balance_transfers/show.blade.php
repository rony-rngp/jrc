<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transfer Voucher - {{ $voucher }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: #e9ecef; }
        .receipt-box { max-width: 750px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-top: 6px solid #1a73e8; }
        
        .receipt-box .logo-container {
            margin: -40px -40px 30px -40px !important;
            border-radius: 12px 12px 0 0 !important;
        }

        .header-title { text-align: center; margin-bottom: 30px; }
        .header-title h2 { margin: 0; color: #1a73e8; font-size: 24px; text-transform: uppercase; font-weight: 800; border-bottom: 2px solid #1a73e8; display: inline-block; padding-bottom: 5px; }
        .info-bar { display: flex; justify-content: space-between; background: #f8f9fa; padding: 12px 20px; border-radius: 8px; margin-bottom: 30px; font-weight: 600; color: #555; }
        .info-bar span { color: #000; }
        .content-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .content-table th { width: 30%; padding: 15px; background: #f1f3f4; border: 1px solid #dadce0; text-align: left; color: #3c4043; }
        .content-table td { padding: 15px; border: 1px solid #dadce0; color: #202124; font-weight: 600; }
        .amount-section { background: #e8f0fe; border: 2px solid #1a73e8; border-radius: 8px; padding: 20px; text-align: center; margin-bottom: 40px; }
        .amount-section h3 { margin: 0; color: #1a73e8; font-size: 26px; }
        .signatures { display: flex; justify-content: space-between; margin-top: 80px; }
        .sign-block { width: 200px; text-align: center; border-top: 1.5px solid #000; padding-top: 8px; font-weight: 700; color: #444; }
        .no-print-area { text-align: center; margin-top: 30px; }
        .btn-print { background: #1a73e8; color: white; border: none; padding: 12px 30px; font-size: 16px; border-radius: 6px; cursor: pointer; transition: 0.2s; text-decoration: none; display: inline-block; }
        .btn-print:hover { background: #1557b0; }
        @media print { .no-print-area { display: none; } .receipt-box { box-shadow: none; margin: 0; width: 100%; max-width: 100%; } }
    </style>
</head>
<body>

<div class="receipt-box">
    
    @include('layouts.voucher_logo')

    <div class="header-title">
        <h2>Fund Transfer Voucher</h2>
    </div>

    <div class="info-bar">
        <div>Voucher No: <span>{{ $voucher }}</span></div>
        <div>Date: <span>{{ $date }}</span></div>
    </div>

    <table class="content-table">
        <tr>
            <th>From Account:</th>
            <td>{{ !empty($from_account) ? $from_account : 'N/A' }}</td>
        </tr>
        <tr>
            <th>To Account:</th>
            <td>{{ !empty($to_account) ? $to_account : 'N/A' }}</td>
        </tr>
        <tr>
            <th>TrxID / Cheque:</th>
            <td>{{ $trx_info }}</td>
        </tr>
        @if(!empty($remarks_info))
        <tr>
            <th>Remarks / Note:</th>
            <td>{{ $remarks_info }}</td>
        </tr>
        @endif
    </table>

    <div class="amount-section">
        <small style="display:block; color:#666; text-transform:uppercase; margin-bottom:5px;">Amount Transferred</small>
        <h3>BDT {{ number_format((float)$amount, 2) }} /=</h3>
    </div>

    <div class="signatures">
        <div class="sign-block">Prepared By</div>
        <div class="sign-block">Authorized By</div>
    </div>
</div>

<div class="no-print-area">
    <a href="{{ route('balance-transfers.index') }}" class="btn-print" style="background:#6c757d; margin-right:10px;">Go Back</a>
    <button class="btn-print" onclick="window.print()">Print This Voucher</button>
</div>

</body>
</html>
