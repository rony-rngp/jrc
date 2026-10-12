@extends('layouts.master')

@section('title', 'User Details - ' . $user->username)
@section('page_title', 'User Account & Activity Profile')

@section('breadcrumb')
  <li><a href="{{ route('users.index') }}"><i class="fa fa-users"></i> Users</a></li>
  <li class="active">{{ $user->username }}</li>
@endsection

@section('content')
<div class="row">
    <!-- User Info Card -->
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-body box-profile">
                <div class="text-center" style="margin-bottom:15px;">
                    <img class="profile-user-img img-responsive img-circle" 
                         src="{{ !empty($user->photo) ? asset('images/' . $user->photo) : asset('images/profile.jpg') }}" 
                         alt="User profile picture" 
                         style="width:90px; height:90px; object-fit:cover;">
                </div>

                <h3 class="profile-username text-center" style="font-weight:bold; color:#2c3e50;">{{ $user->firstname }} {{ $user->lastname }}</h3>
                <p class="text-muted text-center"><i class="fa fa-user-circle"></i> {{ $user->username }}</p>

                <div class="text-center" style="margin-bottom:15px;">
                    @if($user->role == 1)
                        <span class="label label-danger" style="font-size:13px; padding:4px 10px;">Super Admin</span>
                    @elseif($user->role == 2)
                        <span class="label label-warning" style="font-size:13px; padding:4px 10px;">Manager</span>
                    @else
                        <span class="label label-primary" style="font-size:13px; padding:4px 10px;">Staff / Accountant</span>
                    @endif
                </div>

                <ul class="list-group list-group-unbordered">
                    <li class="list-group-item">
                        <b><i class="fa fa-id-badge text-primary"></i> User ID</b> <span class="pull-right">#{{ $user->id }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fa fa-calendar text-muted"></i> Member Since</b> <span class="pull-right">{{ $user->created_on ?: 'N/A' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fa fa-history text-info"></i> Recorded Actions</b> <span class="pull-right badge bg-blue">{{ count($logs) }}</span>
                    </li>
                </ul>

                <a href="{{ route('users.index') }}" class="btn btn-default btn-block btn-flat"><i class="fa fa-arrow-left"></i> Back to Users List</a>
            </div>
        </div>
    </div>

    <!-- Recent Actions by this User -->
    <div class="col-md-8">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-history"></i> Recent Activity Logs (Last {{ count($logs) }})</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped" id="example1">
                    <thead>
                        <tr class="bg-gray">
                            <th>Date & Time</th>
                            <th>Action Type</th>
                            <th>Description</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ date('d-m-Y H:i:s', strtotime($log->created_at)) }}</td>
                                <td>
                                    @if($log->action == 'Create')
                                        <span class="label label-success">Create</span>
                                    @elseif($log->action == 'Update')
                                        <span class="label label-warning">Update</span>
                                    @elseif($log->action == 'Delete')
                                        <span class="label label-danger">Delete</span>
                                    @else
                                        <span class="label label-primary">{{ $log->action }}</span>
                                    @endif
                                </td>
                                <td>{{ $log->description }}</td>
                                <td><code>{{ $log->ip_address ?: '-' }}</code></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No recent activity logs for this user.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
