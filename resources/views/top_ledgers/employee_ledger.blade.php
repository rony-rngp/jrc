@extends('layouts.master')

@section('title', 'Employee Top Ledger - JRC ERP')
@section('page_title', 'All Employee Top Ledger Summary')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <button type="button" onclick="window.print()" class="btn btn-success btn-sm pull-right"><i class="fa fa-print"></i> Print</button>
            </div>
            <div class="box-body">
                <table class="table table-bordered table-striped table-datatable">
                    <thead>
                        <th>#</th>
                        <th>Employee Name</th>
                        <th>Designation</th>
                        <th>Basic Salary</th>
                        <th>Total Paid Salary</th>
                        <th>Current Due Salary</th>
                    </thead>
                    <tbody>
                        @php $totP = 0; $totD = 0; @endphp
                        @foreach($summaries as $idx => $s)
                        @php 
                            $totP += $s['total_paid'];
                            $totD += $s['total_due'];
                        @endphp
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td><b>{{ $s['name'] }}</b></td>
                            <td>{{ $s['position'] }}</td>
                            <td>৳ {{ number_format($s['basic'], 2) }}</td>
                            <td><span class="text-green">৳ {{ number_format($s['total_paid'], 2) }}</span></td>
                            <td>
                                @if($s['total_due'] > 0)
                                    <b class="text-red">৳ {{ number_format($s['total_due'], 2) }}</b>
                                @else
                                    <span class="text-muted">৳ 0.00</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray font-weight-bold">
                            <td colspan="4" class="text-right"><b>Grand Total:</b></td>
                            <td><b class="text-green">৳ {{ number_format($totP, 2) }}</b></td>
                            <td><b class="text-red">৳ {{ number_format($totD, 2) }}</b></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
