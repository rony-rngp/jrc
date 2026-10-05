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
    <link rel="stylesheet" href="{{ asset('dist/css/skins/_all-skins.min.css') }}">

  	<!-- Google Font -->
  	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

  	<style type="text/css">
  		.mt20{
  			margin-top:20px;
  		}
      .bold{
        font-weight:bold;
      }

     /* chart style*/
      #legend ul {
        list-style: none;
      }

      #legend ul li {
        display: inline;
        padding-left: 30px;
        position: relative;
        margin-bottom: 4px;
        border-radius: 5px;
        padding: 2px 8px 2px 28px;
        font-size: 14px;
        cursor: default;
        -webkit-transition: background-color 200ms ease-in-out;
        -moz-transition: background-color 200ms ease-in-out;
        -o-transition: background-color 200ms ease-in-out;
        transition: background-color 200ms ease-in-out;
      }

      #legend li span {
        display: block;
        position: absolute;
        left: 0;
        top: 0;
        width: 20px;
        height: 100%;
        border-radius: 5px;
      }

      /* =========================================
         Modern Smart Header CSS 
         ========================================= */
      .main-header {
          box-shadow: 0 4px 15px rgba(0,0,0,0.05);
          border: none;
      }
      
      .main-header .logo {
          background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
          color: #ffffff !important;
          font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
          font-weight: 800;
          letter-spacing: 0.5px;
          border-bottom: 1px solid rgba(255,255,255,0.05) !important;
          border-right: 1px solid rgba(255,255,255,0.05) !important;
          transition: all 0.3s ease;
      }
      .main-header .logo:hover {
          background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
      }

      .main-header .navbar {
          background: #ffffff !important;
          border: none;
      }

      .main-header .sidebar-toggle {
          color: #64748b !important;
          font-size: 16px;
          transition: all 0.3s ease;
      }
      .main-header .sidebar-toggle:hover {
          background: #f1f5f9 !important;
          color: #3b82f6 !important;
      }

      .navbar-nav > li > a {
          color: #475569 !important;
          font-weight: 600;
          padding-top: 15px;
          padding-bottom: 15px;
          transition: all 0.3s ease;
      }
      .navbar-nav > li > a:hover {
          background: #f8fafc !important;
          color: #2563eb !important;
      }
      .navbar-nav > .user-menu > a > img {
          border: 2px solid #e2e8f0;
          box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      }

      .navbar-nav > .messages-menu > a .label {
          border-radius: 50%;
          padding: 4px 6px;
          font-size: 10px;
          top: 8px;
          right: 5px;
          box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
      }
      .navbar-nav > .messages-menu > .dropdown-menu {
          border-radius: 16px;
          box-shadow: 0 10px 40px rgba(0,0,0,0.12);
          border: none;
          width: 320px;
          padding: 0;
          margin-top: 10px;
      }
      .navbar-nav > .messages-menu > .dropdown-menu > li.header {
          background: #f8fafc;
          color: #334155;
          font-weight: 700;
          padding: 15px;
          border-bottom: 1px solid #e2e8f0;
          border-radius: 16px 16px 0 0;
          font-size: 14px;
      }
      .navbar-nav > .messages-menu > .dropdown-menu > li .menu {
          max-height: 250px;
          overflow-y: auto;
          margin: 0;
          padding: 0;
          list-style: none;
      }
      .navbar-nav > .messages-menu > .dropdown-menu > li .menu > li {
          border-bottom: 1px solid #f1f5f9;
      }
      .navbar-nav > .messages-menu > .dropdown-menu > li .menu > li > a {
          padding: 12px 15px;
          display: block;
          transition: all 0.2s ease;
          text-decoration: none;
      }
      .navbar-nav > .messages-menu > .dropdown-menu > li .menu > li > a:hover {
          background: #f1f5f9;
      }
      .navbar-nav > .messages-menu > .dropdown-menu > li .menu > li > a h4 {
          margin: 0 0 5px 0;
          font-size: 14px;
          color: #0f172a;
          font-weight: 600;
      }
      .navbar-nav > .messages-menu > .dropdown-menu > li .menu > li > a p {
          margin: 0;
          font-size: 12px;
          color: #64748b;
          white-space: normal;
      }
      .navbar-nav > .messages-menu > .dropdown-menu > li.footer > a {
          background: #ffffff;
          color: #3b82f6;
          font-weight: 600;
          text-align: center;
          padding: 12px;
          border-top: 1px solid #e2e8f0;
          border-radius: 0 0 16px 16px;
          transition: all 0.3s ease;
      }
      .navbar-nav > .messages-menu > .dropdown-menu > li.footer > a:hover {
          background: #eff6ff;
      }

      .navbar-nav > .user-menu > .dropdown-menu {
          border-radius: 16px;
          box-shadow: 0 10px 40px rgba(0,0,0,0.12);
          border: none;
          overflow: hidden;
          width: 280px;
          margin-top: 10px;
      }
      .navbar-nav > .user-menu > .dropdown-menu > .user-header {
          background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
          padding: 25px 10px;
      }
      .navbar-nav > .user-menu > .dropdown-menu > .user-header > img {
          border: 3px solid rgba(255,255,255,0.3);
          box-shadow: 0 4px 15px rgba(0,0,0,0.2);
      }
      .navbar-nav > .user-menu > .dropdown-menu > .user-header > p {
          color: #ffffff;
          font-weight: 700;
          font-size: 16px;
          margin-top: 10px;
      }
      .navbar-nav > .user-menu > .dropdown-menu > .user-header > p > small {
          color: #bfdbfe;
          font-weight: 500;
          font-size: 12px;
          margin-top: 5px;
      }
      .navbar-nav > .user-menu > .dropdown-menu > .user-footer {
          background: #ffffff !important;
          padding: 15px 20px !important;
          display: flex;
          justify-content: space-between;
          align-items: center;
      }
      
      .btn-smart-update {
          background: #f1f5f9;
          color: #334155;
          border: none;
          border-radius: 20px;
          font-weight: 600;
          padding: 8px 20px;
          transition: all 0.3s ease;
          box-shadow: 0 2px 5px rgba(0,0,0,0.05);
      }
      .btn-smart-update:hover {
          background: #e2e8f0;
          color: #0f172a;
          transform: translateY(-2px);
      }

      .btn-smart-logout {
          background: #fee2e2;
          color: #ef4444;
          border: none;
          border-radius: 20px;
          font-weight: 600;
          padding: 8px 20px;
          transition: all 0.3s ease;
          box-shadow: 0 2px 5px rgba(239, 68, 68, 0.1);
      }
      .btn-smart-logout:hover {
          background: #fca5a5;
          color: #b91c1c;
          transform: translateY(-2px);
      }

      /* =========================================
         Modern Smart Sidebar CSS 
         ========================================= */
      .main-sidebar {
          background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%) !important;
          box-shadow: 4px 0 15px rgba(0,0,0,0.1);
          font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      }
      
      .user-panel {
          padding: 20px 15px;
          border-bottom: 1px solid rgba(255, 255, 255, 0.08);
          margin-bottom: 15px;
      }
      .user-panel .info p {
          color: #f8fafc;
          font-weight: 700;
          font-size: 16px;
          margin-bottom: 5px;
          letter-spacing: 0.5px;
      }
      .user-panel .info a {
          color: #94a3b8;
          font-size: 13px;
          font-weight: 500;
      }
      .user-panel .info a i {
          text-shadow: 0 0 8px rgba(34, 197, 94, 0.8);
      }
      .user-panel img {
          border: 2px solid #3b82f6 !important;
          padding: 2px;
          box-shadow: 0 4px 10px rgba(59, 130, 246, 0.4);
          transition: transform 0.3s ease;
      }
      .user-panel img:hover {
          transform: scale(1.08);
      }

      .sidebar-menu {
          padding-top: 10px;
      }
      .sidebar-menu > li {
          margin: 5px 12px;
      }
      .sidebar-menu > li > a {
          border-radius: 8px;
          color: #cbd5e1 !important;
          transition: all 0.3s ease;
          padding: 12px 15px !important;
          font-weight: 600;
          letter-spacing: 0.3px;
      }
      
      .sidebar-menu > li:hover > a, 
      .sidebar-menu > li.active > a {
          background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%) !important;
          color: #ffffff !important;
          box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
          transform: translateX(5px);
      }
      
      .sidebar-menu > li > a i.fa {
          font-size: 16px;
          margin-right: 10px;
          width: 20px;
          text-align: center;
          transition: transform 0.3s ease;
      }
      .sidebar-menu > li:hover > a i.fa {
          transform: scale(1.2);
      }

      .sidebar-menu .treeview-menu {
          background: rgba(0, 0, 0, 0.15) !important;
          border-radius: 8px;
          padding: 8px;
          margin-top: 5px;
      }
      .sidebar-menu .treeview-menu > li {
          margin: 2px 0;
      }
      .sidebar-menu .treeview-menu > li > a {
          color: #94a3b8 !important;
          border-radius: 6px;
          transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
          padding: 8px 10px 8px 15px !important;
          font-size: 14px;
          font-weight: 500;
      }
      .sidebar-menu .treeview-menu > li > a i {
          font-size: 12px;
          transition: all 0.3s ease;
      }
      .sidebar-menu .treeview-menu > li > a:hover {
          color: #ffffff !important;
          background: rgba(255, 255, 255, 0.08) !important;
          padding-left: 20px !important;
      }
      .sidebar-menu .treeview-menu > li > a:hover i {
          color: #3b82f6;
      }

      /* Smart Dashboard Boxes */
      .smart-box { border-radius: 18px; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.08); transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative; color: #fff; margin-bottom: 25px; border: none; }
      .smart-box:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,0.15); }
      .smart-box .inner { padding: 25px 20px; z-index: 2; position: relative; }
      .smart-box h3 { font-size: 28px; font-weight: 800; margin: 0 0 5px 0; white-space: nowrap; padding: 0; text-shadow: 2px 2px 4px rgba(0,0,0,0.2); font-family: 'Segoe UI', sans-serif; }
      .smart-box p { font-size: 14px; margin-bottom: 0; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9; }
      .smart-box .icon { position: absolute; top: -10px; right: 10px; z-index: 1; font-size: 80px; color: rgba(255, 255, 255, 0.15); transition: all 0.4s ease; }
      .smart-box:hover .icon { font-size: 90px; color: rgba(255, 255, 255, 0.25); transform: rotate(-10deg) scale(1.1); }
      .smart-box-footer { background: rgba(0,0,0,0.15); color: #fff; display: block; padding: 8px 0; text-align: center; text-decoration: none; font-size: 13px; font-weight: 600; z-index: 2; position: relative; transition: all 0.3s ease; letter-spacing: 0.5px; }
      .smart-box-footer:hover { background: rgba(0,0,0,0.3); color: #fff; text-decoration: none; }

      .grad-success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
      .grad-danger { background: linear-gradient(135deg, #ED213A 0%, #93291E 100%); }
      .grad-info { background: linear-gradient(135deg, #00B4DB 0%, #0083B0 100%); }
      .grad-warning { background: linear-gradient(135deg, #f2994a 0%, #f2c94c 100%); }
      .grad-primary { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
      .grad-purple { background: linear-gradient(135deg, #8E2DE2 0%, #4A00E0 100%); }
      .grad-dark { background: linear-gradient(135deg, #434343 0%, #000000 100%); }
      .grad-magenta { background: linear-gradient(135deg, #ec008c 0%, #fc6767 100%); }

      .smart-card { border-radius: 18px; border: none; box-shadow: 0 8px 25px rgba(0,0,0,0.06); background: #fff; margin-bottom: 30px; overflow: hidden; }
      .smart-card-header { background: #fff; border-bottom: 1px solid #f0f2f5; padding: 20px 25px; }
      .smart-card-title { font-weight: 800; color: #2c3e50; font-size: 18px; margin: 0; text-transform: uppercase; letter-spacing: 0.5px; }
      .chart-container { padding: 20px; }
  	</style>
    @stack('styles')
</head>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

    <!-- Header Navbar -->
    <header class="main-header">
        <a href="{{ route('dashboard') }}" class="logo">
          <span class="logo-mini"><b>J</b>RC</span>
          <span class="logo-lg"><i class="fa fa-building-o" style="color:#3b82f6; margin-right:5px;"></i> <b>JR</b> Corp</span>
        </a>
        
        <nav class="navbar navbar-static-top">
          <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <i class="fa fa-bars"></i>
            <span class="sr-only">Toggle navigation</span>
          </a>

          <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
              
              @php
                $notif_count = \App\Models\SupplierBill::where('approval_status', 'Pending')->count() + \App\Models\SupplierPayment::where('approval_status', 'Pending')->count();
              @endphp
              <li class="dropdown messages-menu">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-bell-o" style="font-size: 18px;"></i>
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
                          <li><a href='#'><p style='text-align:center; margin-top:10px;'>No new notifications</p></a></li>
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
                      <a href="#profile" data-toggle="modal" class="btn btn-smart-update" id="admin_profile"><i class="fa fa-user-circle"></i> Update</a>
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
