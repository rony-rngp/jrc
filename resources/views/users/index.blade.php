@extends('layouts.master')

@section('title', 'JR || Corporation - User Management')
@section('page_title', 'System Users')

@section('breadcrumb')
<li class="active">Users</li>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header with-border">
                <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> Add New User</a>
            </div>
            <div class="box-body table-responsive">
                <table id="example1" class="table table-bordered">
                    <thead>
                        <th>Photo</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Role</th>
                        <th>Created On</th>
                        <th width="150">Actions</th>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                        <tr>
                            <td>
                                <img src="{{ !empty($u->photo) ? asset('images/' . $u->photo) : asset('images/profile.jpg') }}" width="35" height="35" class="img-circle" alt="User Image">
                            </td>
                            <td><b>{{ $u->username }}</b></td>
                            <td>{{ $u->firstname }} {{ $u->lastname }}</td>
                            <td>
                                @if($u->role == 1)
                                    <span class="label label-danger">Super Admin</span>
                                @elseif($u->role == 2)
                                    <span class="label label-warning">Manager</span>
                                @else
                                    <span class="label label-primary">Staff / Accountant</span>
                                @endif
                            </td>
                            <td>{{ $u->created_on }}</td>
                            <td>
                                <a href="#edit_{{ $u->id }}" data-toggle="modal" class="btn btn-success btn-xs"><i class="fa fa-edit"></i> Edit</a>
                                @if($u->id != Auth::id())
                                <form action="{{ route('users.destroy', $u->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i> Delete</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modals (Outside table for valid form layout) -->
@foreach($users as $u)
<div class="modal fade" id="edit_{{ $u->id }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit User: <b>{{ $u->username }}</b></h4>
            </div>
            <form class="form-horizontal" method="POST" action="{{ route('users.update', $u->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">First Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="firstname" value="{{ $u->firstname }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Last Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="lastname" value="{{ $u->lastname }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Password</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" name="password" placeholder="Leave blank to keep current">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Role</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="role">
                                <option value="1" {{ $u->role == 1 ? 'selected' : '' }}>Super Admin</option>
                                <option value="2" {{ $u->role == 2 ? 'selected' : '' }}>Manager</option>
                                <option value="3" {{ $u->role == 3 ? 'selected' : '' }}>Accountant / Staff</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Photo</label>
                        <div class="col-sm-9">
                            <input type="file" name="photo">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Add Modal -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><b>Add System User</b></h4>
            </div>
            <form class="form-horizontal" method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Username *</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="username" required placeholder="Unique username">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Password *</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" name="password" required placeholder="Password">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">First Name *</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="firstname" required placeholder="First Name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Last Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="lastname" placeholder="Last Name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Role *</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="role" required>
                                <option value="3" selected>Accountant / Staff</option>
                                <option value="2">Manager</option>
                                <option value="1">Super Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Photo</label>
                        <div class="col-sm-9">
                            <input type="file" name="photo">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
