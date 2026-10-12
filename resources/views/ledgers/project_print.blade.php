<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Project Statement - {{ $project_name }}</title>
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
        .stmt-det h3 { margin: 0 0 5px 0; background: #00a65a; color: #fff; padding: 5px 15px; display: inline-block; border-radius: 3px; font-size: 16px; text-transform: uppercase; }
        .stmt-det p { margin: 2px 0; font-weight: bold; }

        .summary-boxes { display: flex; justify-content: space-between; gap: 10px; margin-bottom: 20px; }
        .summary-box { flex: 1; border: 1px solid #ddd; padding: 10px; text-align: center; border-radius: 4px; background: #fafafa; }
        .summary-box .lbl { font-size: 11px; text-transform: uppercase; color: #777; font-weight: bold; }
        .summary-box .val { font-size: 16px; font-weight: bold; margin-top: 4px; font-family: 'Courier New', Courier, monospace; }

        .table-stmt { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 20px; }
        .table-stmt th { background: #f4f6f7; color: #333; border: 1px solid #ccc; padding: 8px; text-align: center; text-transform: uppercase; }
        .table-stmt td { border: 1px solid #ddd; padding: 8px; vertical-align: middle; }
        
        .row-bf { background: #fffde7; font-weight: bold; }
        .row-footer { background: #ecf0f5; font-weight: bold; }
        
        .amt { text-align: right; font-family: 'Courier New', Courier, monospace; font-weight: bold; font-size: 13px; }
        .text-dr { color: #c0392b; } /* Cost/Debit */
        .text-cr { color: #27ae60; } /* Received/Credit */
        .text-muted { color: #888; }
        
        .badge-type { padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: bold; text-transform: uppercase; display: inline-block; }
        .badge-rev { background: #d5f5e3; color: #1e8449; }
        .badge-exp { background: #fdebd0; color: #d35400; }
        .badge-sup { background: #f2d7d5; color: #922b21; }
        .badge-info { background: #d9edf7; color: #31708f; } 
        .badge-settled { background: #f4f4f4; color: #999; }

        .no-print-area { text-align: center; padding: 15px; background: #333; position: fixed; top: 0; left: 0; width: 100%; z-index: 1000; }
        .btn { background: #00a65a; color: #fff; padding: 10px 20px; border: none; cursor: pointer; font-size: 15px; font-weight: bold; border-radius: 4px; }
        
        @media print {
            @page { size: A4; margin: 10mm; }
            body { background: #fff; padding: 0; }
            .no-print-area { display: none !important; }
            .print-container { margin: 0; padding: 0; box-shadow: none; border: none; width: 100%; }
            .table-stmt th, .table-stmt td { padding: 6px !important; font-size: 11px !important; border: 1px solid #000 !important; }
            .summary-box { border: 1px solid #000 !important; }
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
                <h2>{{ $project_name }}</h2>
                <p><b>Client / Company:</b> {{ !empty($company_name) ? $company_name : 'N/A' }}</p>
                @if(!empty($address))
                    <p><b>Address:</b> {{ $address }}</p>
                @endif
                <p style="margin-top: 5px;">
                    <b>Cash In:</b> <span style="color:#27ae60;">৳ {{ number_format($cash_in, 2) }}</span> &nbsp;|&nbsp;
                    <b>Cash Out:</b> <span style="color:#c0392b;">৳ {{ number_format($cash_out, 2) }}</span>
                </p>
            </div>
            <div class="stmt-det">
                <h3>Project Master Ledger</h3>
                <p>Period: {{ date('d M Y', strtotime($from_date)) }} - {{ date('d M Y', strtotime($to_date)) }}</p>
                <p>Printed: {{ date('d M Y h:i A') }}</p>
                <p style="color: {{ $current_balance >= 0 ? '#27ae60' : '#c0392b' }}; font-size: 15px; margin-top: 5px;">
                    <b>{{ $status_text }}: ৳ {{ number_format(abs($current_balance), 2) }} {{ $status_label }}</b>
                </p>
            </div>
        </div>

        <div class="summary-boxes">
            <div class="summary-box">
                <div class="lbl">Total Revenue (Billed)</div>
                <div class="val" style="color: #2980b9;">{{ number_format($tot_revenue, 2) }}</div>
            </div>
            <div class="summary-box">
                <div class="lbl">Total Received (Cash In)</div>
                <div class="val" style="color: #27ae60;">{{ number_format($cash_in, 2) }}</div>
            </div>
            <div class="summary-box">
                <div class="lbl">Total Cost/Exp (Dr)</div>
                <div class="val" style="color: #c0392b;">{{ number_format($tot_expense, 2) }}</div>
            </div>
            <div class="summary-box">
                <div class="lbl">Client Due (Bill - Recv)</div>
                <div class="val" style="color: #d68910;">{{ number_format($client_due, 2) }}</div>
            </div>
        </div>

        <table class="table-stmt">
            <thead>
                <tr>
                    <th width="11%">Date</th>
                    <th width="15%">Ref / Type</th>
                    <th width="32%">Particulars / Details</th>
                    <th width="11%">Cost/Exp (Dr)</th>
                    <th width="10%">Bill (Not added)</th>
                    <th width="11%">Received (Cash)</th>
                    <th width="10%">Balance</th>
                </tr>
            </thead>
            <tbody>
                <tr class="row-bf">
                    <td colspan="3" style="text-align: right;">Balance Brought Forward (till {{ date('d M Y', strtotime($from_date . ' -1 day')) }}):</td>
                    <td></td><td></td><td></td>
                    <td class="amt">{!! number_format(abs($brought_forward), 2) . ($brought_forward < 0 ? ' <small class="text-dr">Dr</small>' : ' <small class="text-cr">Cr</small>') !!}</td>
                </tr>
                @php
                    $sum_dr = 0; 
                    $sum_cr = 0; 
                    $sum_recv = 0;
                    $r_balance = $brought_forward;
                @endphp
                @if(count($ledger_data) > 0)
                    @foreach($ledger_data as $row)
                        @php
                            $sum_dr += $row['debit']; 
                            $sum_cr += $row['credit'];
                            $sum_recv += $row['received'];
                            $r_balance = $r_balance + $row['received'] - $row['debit'];
                            
                            $dr_text = $row['debit'] > 0 ? number_format($row['debit'], 2) : '-';
                            $cr_text = $row['credit'] > 0 ? number_format($row['credit'], 2) : '-';
                            $recv_text = $row['received'] > 0 ? number_format($row['received'], 2) : '-';
                            
                            $badge = 'badge-exp'; 
                            if($row['type'] == 'Project Revenue') $badge = 'badge-rev';
                            if($row['type'] == 'Running Bill') $badge = 'badge-settled';
                            if($row['type'] == 'Material Cost') $badge = 'badge-sup';
                            if($row['type'] == 'Fund Received') $badge = 'badge-info';
                            
                            $bal_str = number_format(abs($r_balance), 2);
                            if($r_balance > 0) $bal_str .= " <small class='text-cr'>Cr</small>";
                            elseif($r_balance < 0) $bal_str .= " <small class='text-dr'>Dr</small>";
                            else $bal_str = "0.00";

                            $cr_td_style = ($row['credit'] > 0) ? "color:#95a5a6; background:#fbfcfc;" : "";
                        @endphp
                        <tr>
                            <td>{{ date('d M Y', strtotime($row['date'])) }}</td>
                            <td>
                                <b>{{ $row['ref'] }}</b><br>
                                <span class="badge-type {{ $badge }}">{{ $row['type'] }}</span>
                            </td>
                            <td>{!! $row['details'] !!}</td>
                            <td class="amt text-dr">{{ $dr_text }}</td>
                            <td class="amt text-muted" style="{{ $cr_td_style }}">{{ $cr_text }}</td>
                            <td class="amt text-cr" style="font-weight:900;">{{ $recv_text }}</td>
                            <td class="amt">{!! $bal_str !!}</td>
                        </tr>
                    @endforeach
                @else
                    <tr><td colspan="7" style="text-align:center; padding:20px; color:#777;">No transactions found for this period.</td></tr>
                @endif
            </tbody>
            <tfoot class="row-footer">
                <tr>
                    <td colspan="3" style="text-align: right;"><b>Total for Period:</b></td>
                    <td class="amt text-dr">{{ number_format($sum_dr, 2) }}</td>
                    <td class="amt text-muted">{{ number_format($sum_cr, 2) }}</td>
                    <td class="amt text-cr">{{ number_format($sum_recv, 2) }}</td>
                    <td class="amt"><b>Closing:</b> {!! number_format(abs($r_balance), 2) . ($r_balance < 0 ? ' <small class="text-dr">Dr</small>' : ' <small class="text-cr">Cr</small>') !!}</td>
                </tr>
            </tfoot>
        </table>

        <div style="margin-top: 30px; text-align: center; font-size: 11px; color: #777;">
            <p>This is a system-generated document and does not require a physical signature.</p>
            <p>Software By: JR CORPORATION System Management</p>
        </div>
    </div>
</body>
</html>
