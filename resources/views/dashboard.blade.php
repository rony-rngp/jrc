@extends('layouts.master')

@section('title', 'Dashboard | JR Corporation')
@section('page_title', 'Dashboard Overview')

@section('breadcrumb')
<li class="active"><i class="fa fa-dashboard"></i> Dashboard</li>
@endsection

@section('content')
<!-- Financial Accounts Snapshot -->
<div class="section-title">
    <i class="fa fa-line-chart"></i>
    <span>Accounts & Fund Snapshot</span>
</div>

<div class="row">
    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="smart-box grad-success">
            <div class="inner">
                <h3>৳ {{ number_format($today_rcv, 0) }}</h3>
                <p>Today's RCV</p>
            </div>
            <div class="icon"><i class="fa fa-arrow-circle-down"></i></div>
            <a href="{{ route('ledgers.receipts_payments') }}" class="smart-box-footer">View Receipts <i class="fa fa-arrow-right"></i></a>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="smart-box grad-danger">
            <div class="inner">
                <h3>৳ {{ number_format($today_pay, 0) }}</h3>
                <p>Today's Pay</p>
            </div>
            <div class="icon"><i class="fa fa-arrow-circle-up"></i></div>
            <a href="{{ route('ledgers.receipts_payments') }}" class="smart-box-footer">View Payments <i class="fa fa-arrow-right"></i></a>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="smart-box grad-info">
            <div class="inner">
                <h3>৳ {{ number_format($cash_balance, 0) }}</h3>
                <p>Cash Fund</p>
            </div>
            <div class="icon"><i class="fa fa-money"></i></div>
            <a href="{{ route('payment-methods.index') }}" class="smart-box-footer">Live Balance <i class="fa fa-refresh"></i></a>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="smart-box grad-warning">
            <div class="inner">
                <h3>৳ {{ number_format($bank_balance, 0) }}</h3>
                <p>Bank Accounts</p>
            </div>
            <div class="icon"><i class="fa fa-university"></i></div>
            <a href="{{ route('payment-methods.index') }}" class="smart-box-footer">Live Balance <i class="fa fa-refresh"></i></a>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="smart-box grad-magenta">
            <div class="inner">
                <h3>৳ {{ number_format($mfs_balance, 0) }}</h3>
                <p>MFS Wallet</p>
            </div>
            <div class="icon"><i class="fa fa-mobile"></i></div>
            <a href="{{ route('payment-methods.index') }}" class="smart-box-footer">Live Balance <i class="fa fa-refresh"></i></a>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="smart-box grad-dark">
            <div class="inner">
                <h3>৳ {{ number_format($total_net, 0) }}</h3>
                <p>Total Net Fund</p>
            </div>
            <div class="icon"><i class="fa fa-pie-chart"></i></div>
            <a href="{{ route('payment-methods.index') }}" class="smart-box-footer">All Accounts <i class="fa fa-check-circle"></i></a>
        </div>
    </div>
</div>

<div class="section-title" style="margin-top: 15px;">
    <i class="fa fa-cubes"></i>
    <span>Business Operations & Ledgers</span>
</div>

<div class="row">
    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="smart-box grad-dark">
            <div class="inner">
                <h3>{{ $totalSuppliers }}</h3>
                <p>Suppliers</p>
            </div>
            <div class="icon"><i class="fa fa-truck"></i></div>
            <a href="{{ route('suppliers.index') }}" class="smart-box-footer">Supplier Desk <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="smart-box grad-primary">
            <div class="inner">
                <h3>{{ $totalClients }}</h3>
                <p>Clients</p>
            </div>
            <div class="icon"><i class="fa fa-users"></i></div>
            <a href="{{ route('clients.index') }}" class="smart-box-footer">Client Desk <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="smart-box grad-info">
            <div class="inner">
                <h3>{{ $totalProjects }}</h3>
                <p>Projects</p>
            </div>
            <div class="icon"><i class="fa fa-building-o"></i></div>
            <a href="{{ route('projects.index') }}" class="smart-box-footer">Projects List <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>    

    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="smart-box grad-danger">
            <div class="inner">
                <h3>৳ {{ number_format($totalPaid, 0) }}</h3>
                <p>Total Paid</p>
            </div>
            <div class="icon"><i class="fa fa-sign-out"></i></div>
            <a href="{{ route('ledgers.account_statement') }}" class="smart-box-footer">Statement <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="smart-box grad-success">
            <div class="inner">
                <h3>৳ {{ number_format($totalRcv, 0) }}</h3>
                <p>Total RCV</p>
            </div>
            <div class="icon"><i class="fa fa-sign-in"></i></div>
            <a href="{{ route('ledgers.account_statement') }}" class="smart-box-footer">Statement <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="smart-box grad-purple">
            <div class="inner">
                <h3>৳ {{ number_format($officeExpense, 0) }}</h3>
                <p>Office Exp.</p>
            </div>
            <div class="icon"><i class="fa fa-coffee"></i></div>
            <a href="{{ route('ledgers.office_expense') }}" class="smart-box-footer">Expenses <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<!-- Attendance Analytics Card -->
<div class="row" style="margin-top: 10px;">
    <div class="col-xs-12">
        <div class="smart-card">
            <div class="smart-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <h3 class="smart-card-title"><i class="fa fa-bar-chart" style="color: #4f46e5; margin-right: 6px;"></i> Monthly Attendance & Punctuality Overview</h3>
                <div>
                    <form class="form-inline" style="margin: 0;">
                        <div class="form-group" style="margin: 0; display: flex; align-items: center; gap: 8px;">
                            <label style="color: #64748b; font-weight: 600; margin: 0;">Year:</label>
                            <select class="form-control input-sm" id="select_year" style="border-radius: 8px; font-weight: 600; min-width: 100px;">
                                @for($i = 2015; $i <= 2065; $i++)
                                    <option value="{{ $i }}" {{ $i == $year ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </form>
                </div>
            </div>
            <div class="chart-container">
                <div id="legend" class="text-center" style="margin-bottom: 20px;"></div>
                <canvas id="barChart" style="height:350px; width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function(){
  var barChartCanvas = $('#barChart').get(0).getContext('2d');
  var barChart = new Chart(barChartCanvas);
  var barChartData = {
    labels  : {!! json_encode($months) !!},
    datasets: [
      {
        label               : 'Late Attendance',
        fillColor           : 'rgba(239, 68, 68, 0.85)',
        strokeColor         : 'rgba(239, 68, 68, 1)',
        data                : {!! json_encode($late) !!}
      },
      {
        label               : 'On-time Attendance',
        fillColor           : 'rgba(16, 185, 129, 0.85)',
        strokeColor         : 'rgba(16, 185, 129, 1)',
        data                : {!! json_encode($ontime) !!}
      }
    ]
  };
  var barChartOptions = {
    scaleBeginAtZero        : true,
    scaleShowGridLines      : true,
    responsive              : true,
    maintainAspectRatio     : false,
    barBorderRadius         : 6,
    legendTemplate          : '<ul class="<%=name.toLowerCase()%>-legend" style="list-style:none; padding:0; display:flex; justify-content:center; gap:24px;"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].fillColor%>; width:14px; height:14px; display:inline-block; border-radius:4px; margin-right:8px; vertical-align:middle;"></span><span style="font-weight:700; color:#334155; font-size:13px;"><%if(datasets[i].label){%><%=datasets[i].label%><%}%></span></li><%}%></ul>'
  };
  barChartOptions.datasetFill = false;
  var myChart = barChart.Bar(barChartData, barChartOptions);
  document.getElementById('legend').innerHTML = myChart.generateLegend();

  $('#select_year').change(function(){
    window.location.href = "{{ route('dashboard') }}?year=" + $(this).val();
  });
});
</script>
@endpush
