@extends('layouts.master')

@section('title', 'JR || Corporation')
@section('page_title', 'Dashboard Overview')

@section('breadcrumb')
<li class="active">Dashboard</li>
@endsection

@section('content')
<div class="section-title"><i class="fa fa-line-chart"></i> Accounts Snapshot</div>
<div class="row">
    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="smart-box grad-success">
            <div class="inner">
                <h3>{{ number_format($today_rcv, 0) }}</h3>
                <p>Today's RCV</p>
            </div>
            <div class="icon"><i class="fa fa-arrow-circle-down"></i></div>
            <a href="{{ route('ledgers.receipts_payments') }}" class="smart-box-footer">View Details <i class="fa fa-arrow-right"></i></a>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="smart-box grad-danger">
            <div class="inner">
                <h3>{{ number_format($today_pay, 0) }}</h3>
                <p>Today's Pay</p>
            </div>
            <div class="icon"><i class="fa fa-arrow-circle-up"></i></div>
            <a href="{{ route('ledgers.receipts_payments') }}" class="smart-box-footer">View Details <i class="fa fa-arrow-right"></i></a>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="smart-box grad-info">
            <div class="inner">
                <h3>{{ number_format($cash_balance, 0) }}</h3>
                <p>Cash</p>
            </div>
            <div class="icon"><i class="fa fa-money"></i></div>
            <a href="{{ route('payment-methods.index') }}" class="smart-box-footer">Live Balance <i class="fa fa-refresh"></i></a>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="smart-box grad-warning">
            <div class="inner">
                <h3>{{ number_format($bank_balance, 0) }}</h3>
                <p>Bank</p>
            </div>
            <div class="icon"><i class="fa fa-bank"></i></div>
            <a href="{{ route('payment-methods.index') }}" class="smart-box-footer">Live Balance <i class="fa fa-refresh"></i></a>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="smart-box grad-magenta">
            <div class="inner">
                <h3>{{ number_format($mfs_balance, 0) }}</h3>
                <p>MFS</p>
            </div>
            <div class="icon"><i class="fa fa-mobile-phone"></i></div>
            <a href="{{ route('payment-methods.index') }}" class="smart-box-footer">Live Balance <i class="fa fa-refresh"></i></a>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="smart-box grad-dark">
            <div class="inner">
                <h3>{{ number_format($total_net, 0) }}</h3>
                <p>Total Net</p>
            </div>
            <div class="icon"><i class="fa fa-pie-chart"></i></div>
            <a href="{{ route('payment-methods.index') }}" class="smart-box-footer">Total Fund <i class="fa fa-check-circle"></i></a>
        </div>
    </div>
</div>

<br>

<div class="section-title"><i class="fa fa-globe"></i> General Statistics</div>
<div class="row">
    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="smart-box grad-dark">
            <div class="inner">
                <h3>{{ $totalSuppliers }}</h3>
                <p>Suppliers</p>
            </div>
            <div class="icon"><i class="fa fa-truck"></i></div>
            <a href="{{ route('suppliers.index') }}" class="smart-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="smart-box grad-primary">
            <div class="inner">
                <h3>{{ $totalClients }}</h3>
                <p>Clients</p>
            </div>
            <div class="icon"><i class="fa fa-users"></i></div>
            <a href="{{ route('projects.index') }}" class="smart-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="smart-box grad-info">
            <div class="inner">
                <h3>{{ $totalProjects }}</h3>
                <p>Projects</p>
            </div>
            <div class="icon"><i class="fa fa-building-o"></i></div>
            <a href="{{ route('projects.index') }}" class="smart-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>    

    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="smart-box grad-danger">
            <div class="inner">
                <h3>{{ number_format($totalPaid, 0) }}</h3>
                <p>Total Paid</p>
            </div>
            <div class="icon"><i class="fa fa-sign-out"></i></div>
            <a href="{{ route('ledgers.account_statement') }}" class="smart-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="smart-box grad-success">
            <div class="inner">
                <h3>{{ number_format($totalRcv, 0) }}</h3>
                <p>Total RCV</p>
            </div>
            <div class="icon"><i class="fa fa-sign-in"></i></div>
            <a href="{{ route('ledgers.account_statement') }}" class="smart-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="smart-box grad-purple">
            <div class="inner">
                <h3>{{ number_format($officeExpense, 0) }}</h3>
                <p>Office Exp.</p>
            </div>
            <div class="icon"><i class="fa fa-coffee"></i></div>
            <a href="{{ route('ledgers.office_expense') }}" class="smart-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="smart-card">
            <div class="smart-card-header d-flex justify-content-between align-items-center">
                <h3 class="smart-card-title"><i class="fa fa-bar-chart text-primary"></i> Monthly Attendance Report</h3>
                <div class="box-tools pull-right" style="margin-top:-25px;">
                    <form class="form-inline">
                        <div class="form-group">
                            <label style="color:#555; margin-right:10px;">Select Year: </label>
                            <select class="form-control input-sm" id="select_year" style="border-radius:20px; outline:none; box-shadow:none;">
                                @for($i = 2015; $i <= 2065; $i++)
                                    <option value="{{ $i }}" {{ $i == $year ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </form>
                </div>
            </div>
            <div class="chart-container">
                <div id="legend" class="text-center" style="margin-bottom: 15px;"></div>
                <canvas id="barChart" style="height:350px"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function(){
  var barChartCanvas = $('#barChart').get(0).getContext('2d')
  var barChart = new Chart(barChartCanvas)
  var barChartData = {
    labels  : {!! json_encode($months) !!},
    datasets: [
      {
        label               : 'Late',
        fillColor           : 'rgba(237, 33, 58, 0.7)',
        strokeColor         : 'rgba(237, 33, 58, 1)',
        data                : {!! json_encode($late) !!}
      },
      {
        label               : 'Ontime',
        fillColor           : 'rgba(17, 153, 142, 0.8)',
        strokeColor         : 'rgba(17, 153, 142, 1)',
        data                : {!! json_encode($ontime) !!}
      }
    ]
  }
  var barChartOptions = {
    scaleBeginAtZero        : true,
    scaleShowGridLines      : true,
    responsive              : true,
    maintainAspectRatio     : true,
    barBorderRadius         : 5,
    legendTemplate          : '<ul class="<%=name.toLowerCase()%>-legend" style="list-style:none; padding:0; display:flex; justify-content:center; gap:20px;"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].fillColor%>; width:15px; height:15px; display:inline-block; border-radius:3px; margin-right:5px; vertical-align:middle;"></span><span style="font-weight:bold; color:#555;"><%if(datasets[i].label){%><%=datasets[i].label%><%}%></span></li><%}%></ul>'
  }
  barChartOptions.datasetFill = false
  var myChart = barChart.Bar(barChartData, barChartOptions)
  document.getElementById('legend').innerHTML = myChart.generateLegend();

  $('#select_year').change(function(){
    window.location.href = "{{ route('dashboard') }}?year=" + $(this).val();
  });
});
</script>
@endpush
