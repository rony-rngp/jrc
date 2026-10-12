<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Bill - {{ $bill->voucher_no }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');
        
        body { 
            font-family: 'Roboto', Arial, sans-serif; 
            margin: 0; 
            padding: 30px; 
            background: #e9ecef; 
            color: #333;
        }
        .invoice-container { 
            max-width: 850px; 
            margin: 0 auto; 
            background: #fff; 
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1); 
            overflow: hidden;
            position: relative;
        }

        .invoice-container .logo-container {
            margin: 0 !important;
            border-radius: 0 !important;
        }
        
        .main-title {
            text-align: center;
            color: #2c3e50;
            text-transform: uppercase;
            margin: 20px 0 10px 0;
            font-size: 24px;
            letter-spacing: 1px;
            font-weight: 700;
        }
        
        .invoice-meta {
            display: flex;
            justify-content: space-between;
            padding: 20px 30px;
            background: #f8f9fa;
            border-bottom: 2px solid #ecf0f5;
            border-top: 1px solid #eee;
        }
        .meta-box { width: 48%; }
        .meta-box h4 {
            margin: 0 0 10px 0;
            color: #7f8c8d;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .meta-table { width: 100%; border-collapse: collapse; }
        .meta-table td { padding: 4px 0; font-size: 15px; }
        .meta-table td strong { color: #2c3e50; display: inline-block; width: 110px; }

        .invoice-body { padding: 30px; }
        .item-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
        }
        .item-table th, .item-table td { 
            padding: 12px 15px; 
            border-bottom: 1px solid #eee; 
            text-align: left; 
        }
        .item-table th { 
            background: #34495e; 
            color: #fff; 
            font-weight: 500;
            text-transform: uppercase;
            font-size: 13px;
        }
        .item-table tbody tr:nth-child(even) { background-color: #fcfcfc; }
        
        .invoice-footer {
            display: flex;
            justify-content: space-between;
            padding: 0 30px 30px 30px;
        }
        .notes-box {
            width: 50%;
            background: #fdfbf3;
            border-left: 4px solid #f1c40f;
            padding: 15px;
            border-radius: 4px;
            font-size: 14px;
        }
        .summary-box { 
            width: 40%; 
        }
        .summary-table { width: 100%; border-collapse: collapse; }
        .summary-table th, .summary-table td { 
            padding: 10px; 
            font-size: 16px; 
            border-bottom: 1px solid #eee;
        }
        .summary-table th { text-align: left; color: #555; }
        .summary-table td { text-align: right; font-weight: bold; }
        
        .row-total th, .row-total td { font-size: 18px; color: #2c3e50; border-top: 2px solid #2c3e50; }
        .text-paid { color: #27ae60 !important; }
        .text-due { color: #e74c3c !important; }

        .stamp {
            position: absolute;
            top: 170px;
            right: 50px;
            font-size: 40px;
            font-weight: bold;
            text-transform: uppercase;
            border: 4px solid;
            padding: 5px 20px;
            border-radius: 10px;
            transform: rotate(-15deg);
            opacity: 0.15;
            z-index: 0;
            pointer-events: none;
        }
        .stamp.paid { color: #27ae60; border-color: #27ae60; }
        .stamp.due { color: #e74c3c; border-color: #e74c3c; }

        .signatures { 
            display: flex; 
            justify-content: space-between; 
            padding: 40px 30px;
            margin-top: 20px;
        }
        .sign-line { 
            border-top: 1px solid #7f8c8d; 
            width: 220px; 
            text-align: center; 
            padding-top: 8px; 
            font-size: 14px;
            color: #2c3e50;
            font-weight: 500;
        }

        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        
        .action-buttons { text-align: center; margin-top: 30px; }
        .btn-print {
            padding: 12px 30px;
            font-size: 16px;
            cursor: pointer;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: 0.3s;
            font-weight: 500;
        }
        .btn-print:hover { background: #219150; }

        @media print {
            body { background: #fff; padding: 0; -webkit-print-color-adjust: exact; }
            .invoice-container { box-shadow: none; border: none; max-width: 100%; border-radius: 0; }
            .action-buttons { display: none; }
            .item-table th { background: #34495e !important; color: #fff !important; }
            .notes-box { background: #fdfbf3 !important; border-left: 4px solid #f1c40f !important; }
            .stamp { top: 170px; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="invoice-container">
    
    <div class="stamp {{ strtolower($status) }}">{{ $status }}</div>

    @include('layouts.voucher_logo')

    <h2 class="main-title">Purchase Bill</h2>

    <div class="invoice-meta">
        <div class="meta-box">
            <h4>Billed To (Project Info)</h4>
            <table class="meta-table">
                <tr>
                    <td><strong>Bill No:</strong></td>
                    <td>{{ str_pad($bill->id, 5, '0', STR_PAD_LEFT) }}</td>
                </tr>
                <tr>
                    <td><strong>Voucher:</strong></td>
                    <td>{{ $bill->voucher_no }}</td>
                </tr>
                
                <tr>
                    <td><strong>Bill Type:</strong></td>
                    <td>{{ $bill->bill_type }}</td>
                </tr>
                <tr>
                    <td><strong>Date:</strong></td>
                    <td>{{ date('d F, Y', strtotime($bill->bill_date)) }}</td>
                </tr>
            </table>
        </div>
        <div class="meta-box">
            <h4>Supplier / Party Info</h4>
            <table class="meta-table">

                <tr>
                    <td><strong>Project Name:</strong></td>
                    <td>{{ $project->project_name ?? 'N/A' }}</td>
                </tr>
                
                <tr>
                    <td><strong>Supplier Name:</strong></td>
                    <td style="font-size: 16px; font-weight: bold; color: #2c3e50;">
                        {{ $bill->supplier_id == 'Cash Purchase' ? 'Cash Purchase (নগদ ক্রয়)' : ($supplier->party_name ?? $bill->supplier_id) }}
                    </td>
                </tr>

                @if(!empty($supplier->party_phone))
                <tr>
                    <td><strong>Mobile No:</strong></td>
                    <td>{{ $supplier->party_phone }}</td>
                </tr>
                @endif

                <tr>
                    <td><strong>Status:</strong></td>
                    <td style="font-weight:bold; font-size:16px;" class="{{ $status == 'PAID' ? 'text-paid' : 'text-due' }}">
                        {{ $status }}
                    </td>
                </tr>
                
                @if(!empty($pay_method))
                <tr>
                    <td><strong>Method:</strong></td>
                    <td>{{ $pay_method }}</td>
                </tr>
                @endif

                @if(!empty($trx_id))
                <tr>
                    <td><strong>Trx/Check No:</strong></td>
                    <td>{{ $trx_id }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    <div class="invoice-body">
        <table class="item-table">
            <thead>
                <tr>
                    <th width="8%" class="text-center">SL</th>
                    <th width="47%">Material / Product Details</th>
                    <th width="15%" class="text-center">Qty</th>
                    <th width="15%" class="text-right">Unit Price</th>
                    <th width="15%" class="text-right">Total (৳)</th>
                </tr>
            </thead>
            <tbody>
                @php $sl = 1; @endphp
                @forelse($bill->items as $item)
                    <tr>
                        <td class="text-center">{{ $sl++ }}</td>
                        <td><strong>{{ $item->category->cat_name ?? 'Item' }}</strong></td>
                        <td class="text-center">{{ $item->qty }}</td>
                        <td class="text-right">{{ number_format((float)$item->unit_price, 2) }}</td>
                        <td class="text-right">{{ number_format((float)$item->total_price, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">No items found in this bill.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="invoice-footer">
        <div class="notes-box">
            <strong style="color: #d35400;">Remarks / Note:</strong><br>
            <span style="color: #555; line-height: 1.5;">
                {!! !empty($bill->note) ? nl2br(e($bill->note)) : 'No remarks available for this bill.' !!}
            </span>
        </div>
        
        <div class="summary-box">
            <table class="summary-table">
                <tr class="row-total">
                    <th>Grand Total:</th>
                    <td>৳ {{ number_format((float)$bill->grand_total, 2) }}</td>
                </tr>
                <tr>
                    <th>Paid Amount:</th>
                    <td class="text-paid">৳ {{ number_format((float)$real_paid, 2) }}</td>
                </tr>
                <tr>
                    <th>Total Due:</th>
                    <td class="text-due">৳ {{ number_format((float)$real_due, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="signatures">
        <div class="sign-line">Prepared By</div>
        <div class="sign-line">Checked By</div>
        <div class="sign-line">Authorized Signature</div>
    </div>

</div>

<div class="action-buttons">
    <button class="btn-print" onclick="window.print()">
        <svg style="width:20px; height:20px; vertical-align:middle; margin-right:5px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
        Print Invoice
    </button>
</div>

</body>
</html>
