@extends('layouts.master')

@section('title', 'JR || Corporation - Products Name')
@section('page_title', 'Products Name')

@section('breadcrumb')
<li class="active">Products Name</li>
@endsection

@section('content')
<div class="row">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-header with-border">
          <a href="{{ route('categories.index') }}" class="btn btn-success"><i class="fa fa-refresh"></i> Refresh </a>
          <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> Add New Products Name</a>
      </div>
      <div class="box-body table-responsive">
        <table id="example1" class="table table-bordered">
          <thead>
            <th>ID</th>
            <th>Products Name</th>
            <th>Entry Date</th>
            <th>Note</th>
            <th width="200">Tools</th>
          </thead>
          <tbody>
            @foreach($categories as $category)
              <tr>				
                <td>{{ $category->id }}</td>
                <td>{{ $category->cat_name }}</td>				  
                <td>{{ $category->date }}</td>
                <td>{{ $category->note }}</td>
                <td>
                  <a href="#edit_{{ $category->id }}" data-toggle="modal" class="btn btn-success btn-sm btn-flat"><i class="fa fa-edit"></i> Edit</a>
                  <form action="{{ route('categories.destroy', $category->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this category?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-trash"></i> Delete</button>
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

<!-- Edit Modals (Outside table for valid DOM & proper form margins) -->
@foreach($categories as $category)
<div class="modal fade" id="edit_{{ $category->id }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Edit Product Name</b></h4>
            </div>
            <form class="form-horizontal" method="POST" action="{{ route('categories.update', $category->id) }}">
              @csrf
              @method('PUT')
              <div class="modal-body">
                <div class="form-group">
                    <label for="cat_name_{{ $category->id }}" class="col-sm-3 control-label">Category Name</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="cat_name_{{ $category->id }}" name="cat_name" value="{{ $category->cat_name }}" required>
                    </div>
                </div>     
                <div class="form-group">
                    <label for="date_{{ $category->id }}" class="col-sm-3 control-label">Entry Date</label>
                    <div class="col-sm-9">
                      <input type="date" class="form-control" id="date_{{ $category->id }}" name="date" value="{{ $category->date }}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="note_{{ $category->id }}" class="col-sm-3 control-label">Note</label>
                    <div class="col-sm-9">
                      <textarea class="form-control" id="note_{{ $category->id }}" name="note" rows="3">{{ $category->note }}</textarea>
                    </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                <button type="submit" class="btn btn-success btn-flat"><i class="fa fa-check-square-o"></i> Update</button>
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
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Add Products Name</b></h4>
            </div>
            <form class="form-horizontal" method="POST" action="{{ route('categories.store') }}"> 
              @csrf
              <div class="modal-body">
                <div class="form-group">
                    <label for="cat_name" class="col-sm-3 control-label">Category Name</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="cat_name" name="cat_name" placeholder="Enter New Category Name" required>
                    </div>
                </div>     
                <div class="form-group">
                    <label for="note" class="col-sm-3 control-label">Note</label>
                    <div class="col-sm-9">
                      <textarea class="form-control" name="note" id="note" rows="3" placeholder="Category remarks..."></textarea>
                    </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                <button type="submit" class="btn btn-primary btn-flat" name="add"><i class="fa fa-save"></i> Save</button>
              </div>
            </form>
        </div>
    </div>
</div>
@endsection
