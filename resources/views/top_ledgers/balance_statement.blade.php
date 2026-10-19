@extends('layouts.master')

@section('title', 'Balance Statement (উদ্বৃত্তপত্র) - JRC ERP')
@section('page_title', 'Balance Statement (উদ্বৃত্তপত্র)')

@push('styles')
<style>
    @media print {
        @page {
            size: auto;
            margin: 10mm 15mm;
        }
        html, body {
            background-color: #fff !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .NoPrint, .no-print, .main-header, .main-sidebar, .content-header, .main-footer, .search-box, .btn {
            display: none !important;
        }
        .content-wrapper {
            margin: 0 !important;
            margin-top: 0 !important;
            margin-left: 0 !important;
            padding: 0 !important;
            background-color: #fff !important;
            min-height: auto !important;
            animation: none !important;
        }
        .content {
            padding: 0 !important;
            margin: 0 !important;
            animation: none !important;
        }
        .box {
            border: none !important;
            box-shadow: none !important;
            margin: 0 !important;
            padding: 0 !important;
            background: transparent !important;
        }
        .box-body {
            padding: 0 !important;
            margin: 0 !important;
        }
        .report-header {
            margin-top: 0 !important;
            margin-bottom: 25px !important;
        }
        .report-header h2 {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }
        .table-striped>tbody>tr:nth-of-type(odd) {
            background-color: transparent !important;
        }
        .table-hover>tbody>tr:hover {
            background-color: transparent !important;
        }
        .alert {
            display: none !important;
        }
        .print-footer {
            display: block !important;
        }
        a[href]:after {
            content: "" !important;
        }
        abbr[title]:after {
            content: "" !important;
        }
    }
</style>
@endpush

@section('content')
<!-- Date Filter Search Box -->
<div class="box box-primary NoPrint">
    <div class="box-body" style="padding: 20px;">
        <form method="GET" action="{{ route('top-ledgers.balance-statement') }}">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="text-muted">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $from_date }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="text-muted">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $to_date }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group" style="padding-top: 25px;">
                        <button type="submit" name="filter" class="btn btn-success btn-lg"><i class="fa fa-filter"></i> Generate Statement</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="box box-primary">
    <div class="box-header with-border NoPrint text-center">
        <button type="button" onclick="window.print()" class="btn btn-primary btn-lg"><i class="fa fa-print"></i> Print Balance Sheet</button>
    </div>

    <div class="box-body" style="padding: 30px;">
        <div class="report-header text-center" style="margin-bottom: 40px;">
            <h2 style="margin:0; font-weight:bold; color:#2c3e50; text-transform: uppercase;">JR Corporation</h2>
            <h3 style="margin:5px 0; color:#34495e;">Statement of Financial Position (Balance Sheet)</h3>
            <p style="font-size: 16px;">Period: <b>{{ date('d F, Y', strtotime($from_date)) }}</b> to <b>{{ date('d F, Y', strtotime($to_date)) }}</b></p>
        </div>

        <div class="row">
            <div class="col-md-6" style="border-right: 2px solid #ccc;">
                <h4 class="text-center" style="background:#00a65a; color:white; padding:12px; font-weight:bold; margin-top:0; border-radius: 4px;">
                    <i class="fa fa-money"></i> ASSETS (সম্পদ)
                </h4>
                <table class="table table-hover table-striped" style="font-size: 15px; margin-top:15px;">
                    <tbody>
                        <tr>
                            <td>Cash & Bank Balance</td>
                            <td class="text-right font-weight-bold">৳ {{ number_format($cash_bank_balance, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Accounts Receivable (Clients)</td>
                            <td class="text-right">৳ {{ number_format($total_client_receivable, 2) }}</td>
                        </tr>
                        @if($total_supplier_advance > 0)
                        <tr>
                            <td>Advances Paid to Suppliers</td>
                            <td class="text-right">৳ {{ number_format($total_supplier_advance, 2) }}</td>
                        </tr>
                        @endif
                        @if($total_loan_given > 0)
                        <tr>
                            <td>Loans Given (Others)</td>
                            <td class="text-right">৳ {{ number_format($total_loan_given, 2) }}</td>
                        </tr>
                        @endif
                        @if($total_emp_advance > 0)
                        <tr>
                            <td>Employee Salary Advances</td>
                            <td class="text-right">৳ {{ number_format($total_emp_advance, 2) }}</td>
                        </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr style="background:#ecf0f5; font-weight:bold; font-size:18px;">
                            <td style="padding:15px;">TOTAL ASSETS</td>
                            <td class="text-right text-success" style="padding:15px; border-top: 2px solid #00a65a; border-bottom: 4px double #00a65a;">
                                ৳ {{ number_format($total_assets, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="col-md-6">
                <h4 class="text-center" style="background:#dd4b39; color:white; padding:12px; font-weight:bold; margin-top:0; border-radius: 4px;">
                    <i class="fa fa-bank"></i> LIABILITIES & EQUITY (দায় ও স্বত্ব)
                </h4>
                <table class="table table-hover table-striped" style="font-size: 15px; margin-top:15px;">
                    <tbody>
                        <tr class="active"><td colspan="2"><b style="color:#dd4b39;">LIABILITIES (দায়):</b></td></tr>
                        <tr>
                            <td>Accounts Payable (Suppliers)</td>
                            <td class="text-right">৳ {{ number_format($total_supplier_payable, 2) }}</td>
                        </tr>
                        @if($total_client_advance > 0)
                        <tr>
                            <td>Advances Received from Clients</td>
                            <td class="text-right">৳ {{ number_format($total_client_advance, 2) }}</td>
                        </tr>
                        @endif
                        @if($total_loan_taken > 0)
                        <tr>
                            <td>Loans Received</td>
                            <td class="text-right">৳ {{ number_format($total_loan_taken, 2) }}</td>
                        </tr>
                        @endif
                        
                        <tr class="active"><td colspan="2" style="border-top: 2px solid #ddd;"><b style="color:#3c8dbc;">EQUITY (মালিকানা স্বত্ব):</b></td></tr>
                        <tr>
                            <td>Business Equity (Net Worth & Retained Earnings)</td>
                            <td class="text-right text-primary" style="font-weight:bold;">৳ {{ number_format($equity, 2) }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="background:#ecf0f5; font-weight:bold; font-size:18px;">
                            <td style="padding:15px;">TOTAL LIAB. & EQUITY</td>
                            <td class="text-right text-danger" style="padding:15px; border-top: 2px solid #dd4b39; border-bottom: 4px double #dd4b39;">
                                ৳ {{ number_format($liab_and_equity, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="row NoPrint" style="margin-top: 50px;">
            <div class="col-md-8 col-md-offset-2 text-center">
                @if(round($total_assets, 2) === round($liab_and_equity, 2))
                    <div class="alert alert-success" style="font-size: 18px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                        <i class="fa fa-check-circle fa-2x" style="vertical-align: middle;"></i> 
                        <span style="vertical-align: middle; margin-left:10px;"><b>Balanced!</b> আপনার অ্যাকাউন্টিং রিপোর্ট ১০০% সঠিক আছে। (Assets = Liabilities + Equity)</span>
                    </div>
                @else
                    <div class="alert alert-danger" style="font-size: 18px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                        <i class="fa fa-exclamation-triangle fa-2x" style="vertical-align: middle;"></i> 
                        <span style="vertical-align: middle; margin-left:10px;"><b>Error:</b> ব্যালেন্স মিলেনি! ডাটাবেস এন্ট্রিতে কোনো গড়মিল থাকতে পারে।</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="print-footer text-center" style="margin-top: 60px; display: none;">
            <div style="display: flex; justify-content: space-between; padding: 0 40px;">
                <div style="text-align: center; border-top: 1px solid #333; padding-top: 5px; width: 200px; font-weight: bold;">Prepared By</div>
                <div style="text-align: center; border-top: 1px solid #333; padding-top: 5px; width: 200px; font-weight: bold;">Authorized Signature</div>
            </div>
        </div>

    </div>
</div>
@endsection
