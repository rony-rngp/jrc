@extends('layouts.master')

@section('title', 'JR || Corporation - Account Heads')
@section('page_title', 'Account Heads')

@push('styles')
<style>
    /* Sub-head hierarchy indent styles */
    .level-0 { font-weight: 700; color: #1e293b; font-size: 14px; }
    .level-1 { padding-left: 28px !important; color: #334155; font-weight: 600; font-size: 13.5px; }
    .level-2 { padding-left: 54px !important; color: #475569; font-weight: 500; font-size: 13px; }
    .level-3 { padding-left: 80px !important; color: #64748b; font-weight: 400; font-size: 13px; }
    .folder-icon { color: #f59e0b; margin-right: 6px; }
    .fa-rotate-90 {
        -webkit-transform: rotate(90deg);
        -moz-transform: rotate(90deg);
        -ms-transform: rotate(90deg);
        -o-transform: rotate(90deg);
        transform: rotate(90deg);
    }
</style>
@endpush

@section('breadcrumb')
<li class="active"><i class="fa fa-tags"></i> Account Heads</li>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat">
                    <i class="fa fa-plus"></i> Add New Head/Sub-Head
                </a>
                <a href="{{ route('account-heads.index') }}" class="btn btn-success btn-sm btn-flat pull-right">
                    <i class="fa fa-refresh"></i> Refresh
                </a>
            </div>
            <div class="box-body table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                    <thead style="background-color: #2c3e50; color: white;">
                        <th width="5%">SL</th>
                        <th>Account Head Hierarchy</th>
                        <th width="15%" class="text-center">Type</th>
                        <th width="25%">Description</th>
                        <th width="15%" class="text-center">Action</th>
                    </thead>
                    <tbody>
                        @php $sl = 1; @endphp
                        @forelse($heads as $head)
                            <tr>
                                <td>{{ $sl++ }}</td>
                                <td class="level-{{ min($head->level ?? 0, 3) }}">
                                    @if(($head->level ?? 0) > 0)
                                        {!! str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $head->level) !!}
                                        <i class="fa fa-level-up fa-rotate-90 text-muted" style="margin-right: 4px;"></i>
                                    @endif
                                    <i class="fa fa-folder-open folder-icon"></i>
                                    <span>{{ $head->head_name }}</span>
                                </td>
                                <td class="text-center">
                                    @if($head->head_type == 'Receive')
                                        <span class="label label-success">Receive</span>
                                    @else
                                        <span class="label label-danger">Payment</span>
                                    @endif
                                </td>
                                <td>{{ $head->description }}</td>
                                <td class="text-center">
                                    <button class="btn btn-success btn-xs edit btn-flat"
                                        data-id="{{ $head->id }}"
                                        data-name="{{ $head->head_name }}"
                                        data-type="{{ $head->head_type }}"
                                        data-parent="{{ $head->parent_id }}"
                                        data-desc="{{ $head->description }}"
                                        title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-xs delete btn-flat"
                                        data-id="{{ $head->id }}"
                                        data-name="{{ $head->head_name }}"
                                        title="Delete">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No account heads found.</td>
                            </tr>
                        @endforelse
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
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title"><b>Add Account Head</b></h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Parent Head</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="parent_id">
                                <option value="0">None (Main Head)</option>
                                @foreach($allHeads as $h)
                                    <option value="{{ $h->id }}">{{ $h->head_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Head Name *</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="head_name" placeholder="Enter Head Name" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Type *</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="head_type" required>
                                <option value="Receive">Receive (Income)</option>
                                <option value="Payment">Payment (Expense)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Description</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="description" rows="3" placeholder="Head description or remarks..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
                        <i class="fa fa-close"></i> Close
                    </button>
                    <button type="submit" class="btn btn-primary btn-flat" name="add">
                        <i class="fa fa-save"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="edit">
    <div class="modal-dialog">
        <div class="modal-content">
            <form class="form-horizontal" id="editForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title"><b>Update Account Head</b></h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Parent Head</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="parent_id" id="edit_parent_id">
                                <option value="0">None (Main Head)</option>
                                @foreach($allHeads as $h)
                                    <option value="{{ $h->id }}">{{ $h->head_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Head Name *</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="edit_head_name" name="head_name" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Type *</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="edit_head_type" name="head_type" required>
                                <option value="Receive">Receive (Income)</option>
                                <option value="Payment">Payment (Expense)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Description</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
                        <i class="fa fa-close"></i> Close
                    </button>
                    <button type="submit" class="btn btn-success btn-flat" name="edit">
                        <i class="fa fa-check-square-o"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="delete">
    <div class="modal-dialog">
        <div class="modal-content">
            <form class="form-horizontal" id="deleteForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title"><b>Deleting...</b></h4>
                </div>
                <div class="modal-body text-center">
                    <p style="font-size: 15px;">Are you sure you want to delete this Account Head?</p>
                    <h3 id="del_head_name" class="bold text-danger" style="margin: 15px 0;"></h3>
                    <p class="text-warning" style="font-size: 12.5px;">
                        <i class="fa fa-warning"></i> Cannot be deleted if sub-heads or transactions exist.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
                        <i class="fa fa-close"></i> Close
                    </button>
                    <button type="submit" class="btn btn-danger btn-flat" name="delete">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function(){
    var updateUrlTemplate = "{{ route('account-heads.update', ':id') }}";
    var deleteUrlTemplate = "{{ route('account-heads.destroy', ':id') }}";

    $('.edit').click(function(e){
        e.preventDefault();
        var id = $(this).data('id');
        var name = $(this).data('name');
        var type = $(this).data('type');
        var parent = $(this).data('parent');
        var desc = $(this).data('desc');

        $('#edit_head_name').val(name);
        $('#edit_head_type').val(type);
        $('#edit_parent_id').val(parent);
        $('#edit_description').val(desc);

        var actionUrl = updateUrlTemplate.replace(':id', id);
        $('#editForm').attr('action', actionUrl);

        $('#edit').modal('show');
    });

    $('.delete').click(function(e){
        e.preventDefault();
        var id = $(this).data('id');
        var name = $(this).data('name');

        $('#del_head_name').html(name);

        var actionUrl = deleteUrlTemplate.replace(':id', id);
        $('#deleteForm').attr('action', actionUrl);

        $('#delete').modal('show');
    });
});
</script>
@endpush
