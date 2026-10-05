@extends('layouts.master')

@section('title', 'JR || Corporation - Account Heads')
@section('page_title', 'Account Heads')

@section('breadcrumb')
<li class="active">Account Heads</li>
@endsection

@push('styles')
<style>
    .level-0 { font-weight: bold; color: #2c3e50; }
    .level-1 { padding-left: 25px; color: #34495e; }
    .level-2 { padding-left: 50px; color: #7f8c8d; }
    .level-3 { padding-left: 75px; color: #95a5a6; }
    .folder-icon { color: #f39c12; margin-right: 5px; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> Add New Head/Sub-Head</a>
            </div>
            <div class="box-body table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                    <thead style="background-color: #2c3e50; color: white;">
                        <th width="5%">SL</th>
                        <th>Account Head Hierarchy</th>
                        <th width="15%">Type</th>
                        <th width="25%">Description</th>
                        <th width="15%">Action</th>
                    </thead>
                    <tbody>
                        @php $sl = 1; @endphp
                        @foreach($heads as $head)
                            <tr>
                                <td>{{ $sl++ }}</td>
                                <td class="level-0"><i class='fa fa-folder-open folder-icon'></i> {{ $head->head_name }}</td>
                                <td class='text-center'>
                                    @if($head->head_type == 'Receive')
                                        <span class="label label-success">Receive</span>
                                    @else
                                        <span class="label label-danger">Payment</span>
                                    @endif
                                </td>
                                <td>{{ $head->description }}</td>
                                <td>
                                    <a href="#edit_{{ $head->id }}" data-toggle="modal" class='btn btn-success btn-xs btn-flat'><i class='fa fa-edit'></i></a>
                                    <form action="{{ route('account-heads.destroy', $head->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this head?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class='btn btn-danger btn-xs btn-flat'><i class='fa fa-trash'></i></button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
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
                                                        <textarea class="form-control" name="description">{{ $head->description }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-success btn-flat">Update Head</button>
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
                            <textarea class="form-control" name="description" placeholder="Remarks or Description"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-flat">Save Head</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
