<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Supplier Statement - {{ $supplier_name }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #eef2f5; margin: 0; padding: 20px; color: #333; }
        
        .print-container { width: 210mm; min-height: 297mm; margin: 0 auto; background: #fff; padding: 15mm 20mm; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .logo-area { text-align: center; margin-bottom: -20px; padding-bottom: 0px; }
        .logo-area .logo-container { margin: -15mm -20mm 20px -20mm; border-radius: 0; }
        
        .header-info { display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 14px; }
        .client-det h2 { margin: 0 0 5px 0; font-size: 22px; color: #2c3e50; text-transform: uppercase; }
        .client-det p { margin: 2px 0; color: #555; }
        
        .stmt-det { text-align: right; }
        .stmt-det h3 { margin: 0 0 5px 0; background: #dd4b39; color: #fff; padding: 5px 15px; display: inline-block; border-radius: 3px; font-size: 16px; text-transform: uppercase; }
        .stmt-det p { margin: 2px 0; font-weight: bold; }

        .table-stmt { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 20px; }
        .table-stmt th { background: #f4f6f7; color: #333; border: 1px solid #ccc; padding: 8px; text-align: center; text-transform: capitalize; }
        .table-stmt td { border: 1px solid #ddd; padding: 8px; vertical-align: middle; }
        
        .row-bf { background: #fffde7; font-weight: bold; }
        .row-footer { background: #ecf0f5; font-weight: bold; }
        
        .amt { text-align: right; font-family: 'Courier New', Courier, monospace; font-weight: bold; font-size: 13px; }
        .text-dr { color: #27ae60; } /* Debit is paid */
        .text-cr { color: #c0392b; } /* Credit is billed */
        
        .no-print-area { text-align: center; padding: 15px; background: #333; position: fixed; top: 0; left: 0; width: 100%; z-index: 1000; }
        .btn { background: #00a65a; color: #fff; padding: 10px 20px; border: none; cursor: pointer; font-size: 15px; font-weight: bold; border-radius: 4px; }
        
        @media print {
            @page { size: A4; margin: 10mm; }
            body { background: #fff; padding: 0; }
            .no-print-area { display: none !important; }
            .print-container { margin: 0; padding: 0; box-shadow: none; border: none; width: 100%; }
            .table-stmt th, .table-stmt td { padding: 5px !important; font-size: 11px !important; border: 1px solid #000 !important; }
            .amt { font-size: 12px !important; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print-area">
        <button onclick="window.print()" class="btn"><i class="fa fa-print"></i> Print Statement</button>
        <button onclick="window.close()" class="btn" style="background:#d9534f; margin-left:10px;">Close Window</button>
    </div>
    
    <div class="print-container" style="margin-top: 50px;">
        <div class="logo-area">
            @include('layouts.voucher_logo')
        </div>

        <div class="header-info">
            <div class="client-det">
                <h2>{{ $supplier_name }}</h2>
                <p><b>Address:</b> {{ !empty($address) ? $address : 'N/A' }}</p>
                <p><b>Phone:</b> {{ !empty($contact) ? $contact : 'N/A' }}</p>
            </div>
            <div class="stmt-det">
                <h3>Supplier Statement Ledger</h3>
                <p>From: {{ date('d M Y', strtotime($from_date)) }}</p>
                <p>To: {{ date('d M Y', strtotime($to_date)) }}</p>
                <p>Date Printed: {{ date('d M Y h:i A') }}</p>
            </div>
        </div>

        <table class="table-stmt">
            <thead>
                <tr>
                    <th width="12%">Date</th>
                    <th width="15%">Voucher No</th>
                    <th width="35%">Particulars / Details</th>
                    <th width="12%">Paid (Dr)</th>
                    <th width="12%">Billed (Cr)</th>
                    <th width="14%">Balance</th>
                </tr>
            </thead>
            <tbody>
                <tr class="row-bf">
                    <td colspan="3" class="text-right">Balance Brought Forward (As of {{ date('d M Y', strtotime($from_date . ' -1 day')) }}):</td>
                    <td></td><td></td>
                    <td class="amt">{!! number_format(abs($brought_forward), 2) . ($brought_forward < 0 ? ' <small class="text-dr">Dr</small>' : ' <small class="text-cr">Cr</small>') !!}</td>
                </tr>
                @php
                    $sum_dr = 0; 
                    $sum_cr = 0;
                    $running_balance = $brought_forward;
                @endphp
                @if(count($ledger_data) > 0)
                    @foreach($ledger_data as $row)
                        @php
                            $sum_dr += $row['debit']; 
                            $sum_cr += $row['credit'];
                            $running_balance = $running_balance + $row['credit'] - $row['debit'];
                            
                            $dr_text = $row['debit'] > 0 ? number_format($row['debit'], 2) : '-';
                            $cr_text = $row['credit'] > 0 ? number_format($row['credit'], 2) : '-';
                            
                            $bal_str = number_format(abs($running_balance), 2);
                            if($running_balance > 0) $bal_str .= " <small class='text-cr'>Cr</small>";
                            elseif($running_balance < 0) $bal_str .= " <small class='text-dr'>Dr</small>";
                            else $bal_str = "0.00";
                        @endphp
                        <tr>
                            <td>{{ date('d M Y', strtotime($row['date'])) }}</td>
                            <td><b>{{ $row['ref'] }}</b><br><small>{{ $row['type'] }}</small></td>
                            <td>{!! $row['details'] !!}</td>
                            <td class="amt text-dr">{{ $dr_text }}</td>
                            <td class="amt text-cr">{{ $cr_text }}</td>
                            <td class="amt">{!! $bal_str !!}</td>
                        </tr>
                    @endforeach
                @else
                    <tr><td colspan="6" style="text-align:center; padding:20px; color:#777;">No approved transactions found for this period.</td></tr>
                @endif
            </tbody>
            <tfoot class="row-footer">
                <tr>
                    <td colspan="3" style="text-align:right;"><b>Total for Period:</b></td>
                    <td class="amt text-dr">{{ number_format($sum_dr, 2) }}</td>
                    <td class="amt text-cr">{{ number_format($sum_cr, 2) }}</td>
                    <td class="amt"><b>Closing:</b> {!! number_format(abs($running_balance), 2) . ($running_balance < 0 ? ' <small class="text-dr">Dr</small>' : ' <small class="text-cr">Cr</small>') !!}</td>
                </tr>
            </tfoot>
        </table>

        <div style="margin-top: 40px; text-align: center; font-size: 11px; color: #777;">
            <p>This is a system-generated document and does not require a physical signature.</p>
            <p>Software By: JR CORPORATION System Management</p>
        </div>
    </div>
</body>
</html>
