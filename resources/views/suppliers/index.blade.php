@extends('layouts.master')

@section('title', 'JR || Corporation - Supplier List')
@section('page_title', 'Supplier List')

@section('breadcrumb')
<li>Supplier</li>
<li class="active">Supplier List</li>
@endsection

@section('content')
<div class="row">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-header with-border">
          <a href="{{ route('suppliers.index') }}" class="btn btn-success"><i class="fa fa-refresh"></i> Refresh </a>
          <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> Add Supplier</a>
      </div>
      <div class="box-body table-responsive">
        <table id="example1" class="table table-bordered">
          <thead>
            <th>ID</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Opening Balance</th>
            <th>Address</th>
            <th>Date</th>
            <th>Note</th>
            <th width="150">Action</th>
          </thead>
          <tbody>
            @foreach($suppliers as $supplier)
              <tr>                
                <td>{{ $supplier->id }}</td>
                <td>{{ $supplier->party_name }}</td>
                <td>{{ $supplier->party_phone }}</td>
                <td>{{ number_format((float)$supplier->amount, 2) }}</td> 
                <td>{{ $supplier->address }}</td>
                <td>{{ $supplier->date }}</td>
                <td>{{ $supplier->note }}</td>
                <td>
                  <a href="{{ route('suppliers.show', $supplier->id) }}" class="btn btn-info btn-sm btn-flat"><i class="fa fa-eye"></i> View</a>
                  <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-success btn-sm btn-flat"><i class="fa fa-edit"></i> Edit</a>
                  <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this supplier?');">
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

<!-- Add Modal -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Add Supplier</b></h4>
            </div>
            <form class="form-horizontal" method="POST" action="{{ route('suppliers.store') }}" enctype="multipart/form-data"> 
              @csrf
              <div class="modal-body">
                <div class="form-group">
                    <label for="party_name" class="col-sm-3 control-label">Supplier Name</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="party_name" name="party_name" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="party_phone" class="col-sm-3 control-label">Supplier Phone</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="party_phone" name="party_phone" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="amount" class="col-sm-3 control-label">Opening Balance</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="amount" name="amount" value="0" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="address" class="col-sm-3 control-label">Address</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="address" name="address">
                    </div>
                </div>                
                <div class="form-group">
                    <label for="datepicker_add" class="col-sm-3 control-label">Entry Date</label>
                    <div class="col-sm-9"> 
                      <div class="date">
                        <input type="text" class="form-control" id="datepicker_add" name="date" value="{{ date('Y-m-d') }}" placeholder="YYYY-MM-DD">
                      </div>
                    </div>
                </div>                
                <div class="form-group">
                    <label for="note" class="col-sm-3 control-label">Note</label>
                    <div class="col-sm-9">
                      <textarea class="form-control" name="note" id="note"></textarea>
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
