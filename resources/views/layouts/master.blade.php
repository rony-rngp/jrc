<!DOCTYPE html>
<html>
<head>
  	<meta charset="utf-8">
  	<meta http-equiv="X-UA-Compatible" content="IE=edge">
  	<title>@yield('title', 'JR || Corporation')</title>
  	<!-- Tell the browser to be responsive to screen width -->
  	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">

  	<!-- Bootstrap 3.3.7 -->
  	<link rel="stylesheet" href="{{ asset('bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
  	<!-- Font Awesome -->
  	<link rel="stylesheet" href="{{ asset('bower_components/font-awesome/css/font-awesome.min.css') }}">
  	<!-- Ionicons -->
  	<link rel="stylesheet" href="{{ asset('bower_components/Ionicons/css/ionicons.min.css') }}">
  	<!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('dist/css/AdminLTE.min.css') }}">
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <!-- daterange picker -->
    <link rel="stylesheet" href="{{ asset('bower_components/bootstrap-daterangepicker/daterangepicker.css') }}">
    <!-- Bootstrap time Picker -->
    <link rel="stylesheet" href="{{ asset('plugins/timepicker/bootstrap-timepicker.min.css') }}">
    <!-- bootstrap datepicker -->
    <link rel="stylesheet" href="{{ asset('bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <!-- AdminLTE Skins. -->
    <!-- AdminLTE Skins. -->
    <link rel="stylesheet" href="{{ asset('dist/css/skins/_all-skins.min.css') }}">

    <!-- Google Font & Modern Admin UI CSS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/modern-admin.css') }}">

    <style type="text/css">
        .mt20 { margin-top: 20px; }
        .bold { font-weight: bold; }
        #legend ul { list-style: none; }
        #legend ul li {
            display: inline-block;
            position: relative;
            margin-bottom: 4px;
            border-radius: 6px;
            padding: 4px 10px 4px 28px;
            font-size: 13px;
            font-weight: 600;
        }
        #legend li span {
            display: block;
            position: absolute;
            left: 6px;
            top: 50%;
            transform: translateY(-50%);
            width: 14px;
            height: 14px;
            border-radius: 4px;
        }
        @media print {
            a[href]:after {
                content: "" !important;
            }
            abbr[title]:after {
                content: "" !important;
            }
        }
    </style>
    @stack('styles')
</head>

<body class="hold-transition skin-blue sidebar-mini fixed">
<div class="wrapper">

    <!-- Header Navbar -->
    <header class="main-header">
        <a href="{{ route('dashboard') }}" class="logo">
          <span class="logo-mini"><b>J</b>RC</span>
          <span class="logo-lg"><i class="fa fa-building-o" style="color:#38bdf8; margin-right:6px;"></i> <b>JR</b> Corp</span>
        </a>
        
        <nav class="navbar navbar-static-top">
          <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button" title="Toggle Sidebar">
            <i class="fa fa-bars"></i>
            <span class="sr-only">Toggle navigation</span>
          </a>

          <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
              
              @php
                $notif_count = \App\Models\SupplierBill::where('approval_status', 'Pending')->count()
                    + \App\Models\SupplierPayment::where('approval_status', 'Pending')->count()
                    + \App\Models\PaymentReceivedProject::where('approval_status', 'Pending')->where('val', 0)->count()
                    + \App\Models\ProjectExpense::where('approval_status', 'Pending')->count()
                    + \App\Models\GeneralTransaction::where('approval_status', 'Pending')->where('t_type', 'Payment')->count();
              @endphp
              <li class="dropdown messages-menu">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="Notifications">
                    <i class="fa fa-bell-o"></i>
                    @if($notif_count > 0)
                      <span class="label label-danger">{{ $notif_count }}</span>
                    @endif
                  </a>
                  <ul class="dropdown-menu">
                    <li class="header">You have {{ $notif_count }} pending approvals</li>
                    <li>
                      <ul class="menu">
                        @if($notif_count > 0)
                          <li>
                            <a href="{{ route('approvals.pending') }}">
                              <h4><i class='fa fa-exclamation-circle text-yellow' style='margin-right: 5px;'></i> Approval Required</h4>
                              <p>{{ $notif_count }} voucher(s) waiting for approval</p>
                            </a>
                          </li>
                        @else
                          <li><a href='#'><p style='text-align:center; margin:10px 0;'>No new notifications</p></a></li>
                        @endif
                      </ul>
                    </li>
                    <li class="footer"><a href="{{ route('approvals.pending') }}">View all approvals</a></li>
                  </ul>
              </li>

              <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <img src="{{ !empty(Auth::user()->photo) ? asset('images/' . Auth::user()->photo) : asset('images/profile.jpg') }}" class="user-image" alt="User Image">
                  <span class="hidden-xs">{{ Auth::user()->firstname ?? 'Admin' }} {{ Auth::user()->lastname ?? '' }}</span>
                </a>
                <ul class="dropdown-menu">
                  <li class="user-header">
                    <img src="{{ !empty(Auth::user()->photo) ? asset('images/' . Auth::user()->photo) : asset('images/profile.jpg') }}" class="img-circle" alt="User Image">

                    <p>
                      {{ Auth::user()->firstname ?? 'Admin' }} {{ Auth::user()->lastname ?? '' }}
                      <small>Member since {{ Auth::user()->created_on ? date('M. Y', strtotime(Auth::user()->created_on)) : date('M. Y') }}</small>
                    </p>
                  </li>
                  <li class="user-footer">
                    <div>
                      <a href="#profile" data-toggle="modal" class="btn btn-smart-update" id="admin_profile"><i class="fa fa-user-circle"></i> Profile</a>
                    </div>
                    <div>
                      <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-smart-logout"><i class="fa fa-sign-out"></i> Sign out</button>
                      </form>
                    </div>
                  </li>
                </ul>
              </li>
              
            </ul>
          </div>
        </nav>
    </header>

    <!-- Sidebar -->
    @include('layouts.sidebar')

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <section class="content-header">
            <h1>@yield('page_title', 'Dashboard')</h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
                @yield('breadcrumb')
            </ol>
        </section>

        <section class="content">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible" style="border-radius:10px; box-shadow:0 4px 6px rgba(0,0,0,0.1);">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-warning"></i> Error!</h4>
                    {!! session('error') !!}
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success alert-dismissible" style="border-radius:10px; box-shadow:0 4px 6px rgba(0,0,0,0.1);">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-check"></i> Success!</h4>
                    {!! session('success') !!}
                </div>
            @endif

            @yield('content')
        </section>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="pull-right hidden-xs">
            <b>Version</b> 2.0 (Laravel)
        </div>
        <strong>Copyright &copy; 2026 <a href="#">JR Corporation</a>.</strong> All rights reserved.
    </footer>

</div>

<!-- Profile Modal -->
<div class="modal fade" id="profile">
    <div class="modal-dialog">
        <div class="modal-content">
          	<div class="modal-header">
            	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
              		<span aria-hidden="true">&times;</span></button>
            	<h4 class="modal-title"><b>Admin Profile</b></h4>
          	</div>
          	<div class="modal-body">
            	<form class="form-horizontal" method="POST" action="{{ route('users.profile.update') }}" enctype="multipart/form-data">
                    @csrf
          		  <div class="form-group">
                   	<label for="username" class="col-sm-3 control-label">Username</label>
                   	<div class="col-sm-9">
                     	<input type="text" class="form-control" id="username" name="username" value="{{ Auth::user()->username ?? '' }}">
                   	</div>
                </div>
                <div class="form-group">
                    <label for="password" class="col-sm-3 control-label">New Password</label>
                    <div class="col-sm-9"> 
                      <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current">
                    </div>
                </div>
                <div class="form-group">
                   	<label for="firstname" class="col-sm-3 control-label">Firstname</label>
                   	<div class="col-sm-9">
                     	<input type="text" class="form-control" id="firstname" name="firstname" value="{{ Auth::user()->firstname ?? '' }}">
                   	</div>
                </div>
                <div class="form-group">
                   	<label for="lastname" class="col-sm-3 control-label">Lastname</label>
                   	<div class="col-sm-9">
                     	<input type="text" class="form-control" id="lastname" name="lastname" value="{{ Auth::user()->lastname ?? '' }}">
                   	</div>
                </div>
                <div class="form-group">
                    <label for="photo" class="col-sm-3 control-label">Photo:</label>
                    <div class="col-sm-9">
                      <input type="file" id="photo" name="photo">
                    </div>
                </div>
          	</div>
          	<div class="modal-footer">
            	<button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
            	<button type="submit" class="btn btn-success btn-flat" name="save"><i class="fa fa-check-square-o"></i> Save</button>
            	</form>
          	</div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="{{ asset('bower_components/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('bower_components/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
<script>
  $.widget.bridge('uibutton', $.ui.button);
</script>
<script src="{{ asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('bower_components/raphael/raphael.min.js') }}"></script>
<script src="{{ asset('bower_components/morris.js/morris.min.js') }}"></script>
<script src="{{ asset('bower_components/chart.js/Chart.js') }}"></script>
<script src="{{ asset('bower_components/jquery-sparkline/dist/jquery.sparkline.min.js') }}"></script>
<script src="{{ asset('bower_components/moment/min/moment.min.js') }}"></script>
<script src="{{ asset('bower_components/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('plugins/timepicker/bootstrap-timepicker.min.js') }}"></script>
<script src="{{ asset('bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
<script src="{{ asset('bower_components/fastclick/lib/fastclick.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="{{ asset('dist/js/adminlte.min.js') }}"></script>

<script>
$(function(){
  /** DataTables standard initialization */
  if($('#example1').length && !$.fn.DataTable.isDataTable('#example1')) {
    $('#example1').DataTable({
      responsive: true,
      pageLength: 25,
      order: []
    });
  }

  //Date picker
  $('#datepicker_add, #datepicker_edit, .datepicker').datepicker({
    autoclose: true,
    format: 'yyyy-mm-dd'
  });

  //Timepicker
  if($('.timepicker').length) {
    $('.timepicker').timepicker({
      showInputs: false
    });
  }
});
</script>

@stack('scripts')

</body>
</html>
