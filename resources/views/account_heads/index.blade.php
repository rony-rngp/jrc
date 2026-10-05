@extends('layouts.master')

@section('title', 'JR || Corporation - Account Heads')
@section('page_title', 'Account Heads')

@section('breadcrumb')
<li class="active">Account Heads</li>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header with-border">
                <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> New Account Head</a>
            </div>
            <div class="box-body table-responsive">
                <table id="example1" class="table table-bordered">
                    <thead>
                        <th>ID</th>
                        <th>Head Name</th>
                        <th>Parent Head</th>
                        <th>Head Type</th>
                        <th>Description</th>
                        <th width="150">Actions</th>
                    </thead>
                    <tbody>
                        @foreach($heads as $head)
                            <tr>
                                <td>{{ $head->id }}</td>
                                <td><b>{{ $head->head_name }}</b></td>
                                <td>
                                    @if($head->parent)
                                        <span class="label label-info">{{ $head->parent->head_name }}</span>
                                    @else
                                        <span class="label label-default">Main Head</span>
                                    @endif
                                </td>
                                <td>
                                    @if($head->head_type == 'Payment')
                                        <span class="label label-danger">Payment</span>
                                    @else
                                        <span class="label label-success">Receive</span>
                                    @endif
                                </td>
                                <td>{{ $head->description }}</td>
                                <td>
                                    <a href="#edit_{{ $head->id }}" data-toggle="modal" class='btn btn-success btn-xs btn-flat'><i class='fa fa-edit'></i> Edit</a>
                                    <form action="{{ route('account-heads.destroy', $head->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this head?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class='btn btn-danger btn-xs btn-flat'><i class='fa fa-trash'></i> Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modals (Outside table for proper form grid and spacing) -->
@foreach($heads as $head)
<div class="modal fade" id="edit_{{ $head->id }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <form class="form-horizontal" method="POST" action="{{ route('account-heads.update', $head->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><b>Edit Account Head</b></h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Parent Head</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="parent_id">
                                <option value="0">None (Main Head)</option>
                                @foreach($heads as $h)
                                    @if($h->id != $head->id)
                                        <option value="{{ $h->id }}" {{ $head->parent_id == $h->id ? 'selected' : '' }}>{{ $h->head_name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Head Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="head_name" value="{{ $head->head_name }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Head Type</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="head_type" required>
                                <option value="Payment" {{ $head->head_type == 'Payment' ? 'selected' : '' }}>Payment (Expense)</option>
                                <option value="Receive" {{ $head->head_type == 'Receive' ? 'selected' : '' }}>Receive (Income)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Description</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="description" rows="3">{{ $head->description }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                    <button type="submit" class="btn btn-success btn-flat"><i class="fa fa-check-square-o"></i> Update Head</button>
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
            <form class="form-horizontal" method="POST" action="{{ route('account-heads.store') }}">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><b>Add Account Head</b></h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Parent Head</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="parent_id">
                                <option value="0">None (Main Head)</option>
                                @foreach($heads as $h)
                                    <option value="{{ $h->id }}">{{ $h->head_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Head Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="head_name" placeholder="Enter Head Name" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Head Type</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="head_type" required>
                                <option value="Payment">Payment (Expense)</option>
                                <option value="Receive">Receive (Income)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Description</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="description" rows="3" placeholder="Remarks or Description"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                    <button type="submit" class="btn btn-primary btn-flat"><i class="fa fa-save"></i> Save Head</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
