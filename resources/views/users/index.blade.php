@extends('layouts.master')

@section('title', 'Users & Roles - JRC ERP')
@section('page_title', 'System Users & Roles')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header with-border">
                <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Add New User</a>
            </div>
            <div class="box-body">
                <table class="table table-bordered table-striped table-datatable">
                    <thead>
                        <th>Photo</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Role</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                        <tr>
                            <td><img src="{{ !empty($u->photo) ? asset('images/'.$u->photo) : asset('images/profile.jpg') }}" width="40" height="40" class="img-circle"></td>
                            <td><b>{{ $u->username }}</b></td>
                            <td>{{ $u->firstname }} {{ $u->lastname }}</td>
                            <td>
                                @if($u->role == 1)
                                    <span class="label label-danger">Super Admin</span>
                                @elseif($u->role == 2)
                                    <span class="label label-primary">Manager</span>
                                @else
                                    <span class="label label-info">Accountant / Staff</span>
                                @endif
                            </td>
                            <td>{{ $u->created_on }}</td>
                            <td>
                                <button class="btn btn-success btn-xs" data-toggle="modal" data-target="#edit_{{ $u->id }}"><i class="fa fa-edit"></i> Edit</button>
                                @if($u->id != 1)
                                <form action="{{ route('users.destroy', $u->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i> Delete</button>
                                </form>
                                @endif
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="edit_{{ $u->id }}">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title">Edit User {{ $u->username }}</h4>
                                    </div>
                                    <form method="POST" action="{{ route('users.update', $u->id) }}" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>First Name</label>
                                                <input type="text" class="form-control" name="firstname" value="{{ $u->firstname }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Last Name</label>
                                                <input type="text" class="form-control" name="lastname" value="{{ $u->lastname }}">
                                            </div>
                                            <div class="form-group">
                                                <label>Password (leave blank to keep current)</label>
                                                <input type="password" class="form-control" name="password" placeholder="New password">
                                            </div>
                                            <div class="form-group">
                                                <label>Role</label>
                                                <select class="form-control" name="role">
                                                    <option value="1" {{ $u->role == 1 ? 'selected' : '' }}>Super Admin</option>
                                                    <option value="2" {{ $u->role == 2 ? 'selected' : '' }}>Manager</option>
                                                    <option value="3" {{ $u->role == 3 ? 'selected' : '' }}>Accountant / Staff</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Photo</label>
                                                <input type="file" name="photo">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Add System User</h4>
            </div>
            <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" class="form-control" name="username" required placeholder="Unique username">
                    </div>
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" class="form-control" name="password" required placeholder="Password">
                    </div>
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" class="form-control" name="firstname" required placeholder="First Name">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" class="form-control" name="lastname" placeholder="Last Name">
                    </div>
                    <div class="form-group">
                        <label>Role *</label>
                        <select class="form-control" name="role" required>
                            <option value="3" selected>Accountant / Staff</option>
                            <option value="2">Manager</option>
                            <option value="1">Super Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Photo</label>
                        <input type="file" name="photo">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
