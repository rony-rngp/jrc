<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Print Bill - {{ $bill->project_name }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body { font-family: 'Open Sans', sans-serif; background: #525659; margin: 0; padding: 20px; }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 14px;
            line-height: 24px;
            color: #333;
            background: #fff;
            position: relative;
        }
        
        .bill-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #333;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .bill-header .company-title {
            font-size: 26px;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .bill-header .company-sub {
            font-size: 12px;
            color: #666;
            margin: 0;
        }
        .bill-title { 
            text-align: center; 
            margin: 10px 0 20px 0; 
            padding: 5px 0; 
            font-size: 22px; 
            font-weight: bold; 
            text-transform: uppercase; 
            color: #2c3e50;
            letter-spacing: 1px;
        }
        
        .info-table { width: 100%; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .info-table td { padding: 5px; vertical-align: top; }
        .info-label { font-weight: bold; color: #555; width: 120px; }
        
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .details-table th, .details-table td { padding: 10px; border: 1px solid #ddd; }
        .details-table th { background: #f4f4f4; text-align: left; font-weight: bold; color: #333; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .net-row td { background: #e8f8f5; color: #27ae60; font-size: 16px; font-weight: bold; }
        
        .signature-section { width: 100%; margin-top: 50px; display: flex; justify-content: space-between;}
        .signature-box { width: 30%; text-align: center; border-top: 1px solid #333; padding-top: 5px; font-weight: 600; font-size: 13px;}
        
        .note-box { background: #fcfcfc; border-left: 4px solid #f39c12; padding: 10px; margin-bottom: 20px; font-size: 13px; font-style: italic; }

        .btn-print { display: block; width: 150px; margin: 20px auto; padding: 10px; text-align: center; background: #3498db; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px; border: none; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.2);}

        @media print {
            body { background: #fff; padding: 0; }
            .invoice-box { box-shadow: none; border: none; margin: 0; padding: 30px; width: 100%; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>

<button class="btn-print" onclick="window.print()"><i class="fa fa-print"></i> Print Bill</button>

<div class="invoice-box">
    
    <div class="bill-header">
        <div>
            <h1 class="company-title">JR Corporation</h1>
            <p class="company-sub">Project Construction, Engineering & Materials Supply</p>
        </div>
        <div style="text-align: right; font-size: 12px; color: #555;">
            <b>Head Office:</b> Dhaka, Bangladesh<br>
            <b>Email:</b> info@jrcbd.com | <b>Web:</b> jrcorporationbd.com
        </div>
    </div>

    <div class="bill-title">{{ $stage }}</div>

    <table class="info-table">
        <tr>
            <td class="info-label">Bill No</td>
            <td>: #<b>{{ str_pad($bill->id, 5, "0", STR_PAD_LEFT) }}</b></td>
            <td class="info-label">Bill Date</td>
            <td>: <b>{{ date('d F, Y', strtotime($bill->date)) }}</b></td>
        </tr>
        <tr>
            <td class="info-label">Client Name</td>
            <td>: <b>{{ $bill->company_name }}</b></td>
            <td class="info-label">Project Name</td>
            <td>: <b>{{ $bill->project_name }}</b></td>
        </tr>
        <tr>
            <td class="info-label">Bill Stage</td>
            <td>: <b>{{ $stage }}</b></td>
            <td class="info-label">Currency</td>
            <td>: <b>BDT (৳)</b></td>
        </tr>
        <tr>
            <td class="info-label">Payment Status</td>
            <td>: <b>{{ $bill->payment_status ?? 'Pending' }}</b></td>
            <td></td>
            <td></td>
        </tr>
    </table>

    @if(!empty($clean_note))
    <div class="note-box">
        <b>Remarks:</b> {{ $clean_note }}
    </div>
    @endif

    <h4 style="margin-bottom: 10px; color: #333;">Cost Breakdown Details</h4>
    <table class="details-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">Sl</th>
                <th width="15%">Type</th>
                <th width="60%">Description / Particulars</th>
                <th width="20%" class="text-right">Billed Amount</th> 
            </tr>
        </thead>
        <tbody>
            @php $rowCount = count($breakdown_items); @endphp
            @if($rowCount > 0)
                @foreach($breakdown_items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item['type'] }}</td>
                        <td>{!! $item['desc'] !!}</td>
                        @if($index == 0)
                            <td rowspan="{{ $rowCount }}" class="text-right" style="vertical-align: middle; font-weight: bold; font-size: 18px; color: #2c3e50; background: #fafafa;">৳ {{ number_format($billAmount, 2) }}</td>
                        @endif
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="3" class="text-center">No detailed breakdown found.</td>
                    <td class="text-right" style="font-weight: bold; font-size: 16px;">৳ {{ number_format($billAmount, 2) }}</td>
                </tr>
            @endif
        </tbody>
        
        <tfoot>
            @if($stage == 'Final Bill' || $discount > 0)
            <tr>
                <td colspan="3" class="text-right" style="font-weight:bold; color:#e74c3c;">Discount Applied :</td>
                <td class="text-right" style="color:#e74c3c; font-weight:bold;">- ৳ {{ number_format($discount, 2) }}</td>
            </tr>
            @endif

            <tr class="net-row">
                <td colspan="3" class="text-right">Net Payable Amount :</td>
                <td class="text-right">৳ {{ number_format($netAmount, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    
    <div class="signature-section">
        <div class="signature-box">Prepared By</div>
        <div class="signature-box">Authorized Signature</div>
        <div class="signature-box">Client Signature</div>
    </div>
</div>

<script>
    window.onload = function() {
        // window.print();
    };
</script>
</body>
</html>
