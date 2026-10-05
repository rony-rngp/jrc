@extends('layouts.master')

@section('title', 'Activity Log - JRC ERP')
@section('page_title', 'System Audit & Activity Log')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-history"></i> User Activity Trail</h3>
            </div>
            <div class="box-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Action Type</th>
                        <th>Description</th>
                        <th>IP Address</th>
                    </thead>
                    <tbody>
                        @foreach($logs as $l)
                        <tr>
                            <td>{{ $l->created_at }}</td>
                            <td><b>{{ $l->username }}</b></td>
                            <td>
                                <span class="label label-{{ $l->action_type == 'Create' ? 'success' : ($l->action_type == 'Delete' ? 'danger' : ($l->action_type == 'Update' ? 'primary' : 'info')) }}">
                                    {{ $l->action_type }}
                                </span>
                            </td>
                            <td>{{ $l->description }}</td>
                            <td><code>{{ $l->ip_address }}</code></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="text-center">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
