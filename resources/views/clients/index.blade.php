@extends('layouts.master')

@section('title', 'JR || Corporation - Client List')
@section('page_title', 'Client List')

@section('breadcrumb')
<li>Project</li>
<li class="active">Client List</li>
@endsection

@section('content')
<div class="row">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-header with-border">
          <a href="{{ route('clients.index') }}" class="btn btn-success"><i class="fa fa-refresh"></i> Refresh </a>
          <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> Add New Client</a>
      </div>
      <div class="box-body table-responsive">
        <table id="example1" class="table table-bordered">
          <thead>
            <th>ID</th>
            <th>Company Name</th>
            <th>Person Name</th>
            <th>Contact</th>
            <th>Address</th>
            <th>Opening Balance</th>
            <th>Date</th>
            <th>Note</th>
            <th width="200">Tools</th>
          </thead>
          <tbody>
            @foreach($clients as $client)
              <tr>                
                <td>{{ $client->id }}</td>
                <td>{{ $client->company_name }}</td>
                <td>{{ $client->persion_name }}</td>
                <td>{{ $client->contact }}</td>
                <td>{{ $client->address }}</td>
                <td>{{ number_format((float)$client->opening_balance, 2) }}</td>
                <td>{{ $client->date }}</td>
                <td>{{ $client->note }}</td>
                <td>
                  <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-success btn-sm btn-flat"><i class="fa fa-edit"></i> Edit</a>
                  <form action="{{ route('clients.destroy', $client->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this client?');">
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
                <h4 class="modal-title"><b><i class="fa fa-user-plus"></i> Add New Client</b></h4>
            </div>
            <form class="form-horizontal" method="POST" action="{{ route('clients.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="company_name" class="col-sm-3 control-label">Company Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="company_name" name="company_name" placeholder="Enter Company Name" required>
                        </div>
                    </div>      
                    
                    <div class="form-group">
                        <label for="persion_name" class="col-sm-3 control-label">Person Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="persion_name" name="persion_name" placeholder="Contact Person Name" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="contact" class="col-sm-3 control-label">Contact Number</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="contact" name="contact" placeholder="Mobile or Phone Number" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="amount" class="col-sm-3 control-label">Opening Balance</label>
                        <div class="col-sm-9">
                            <input type="number" step="any" class="form-control" id="amount" name="amount" placeholder="0.00" value="0">
                            <small class="text-muted">Previous due/advance balance if any.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address" class="col-sm-3 control-label">Address</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="address" name="address" placeholder="Full Address">
                        </div>
                    </div>                      
                    
                    <div class="form-group">
                        <label for="datepicker_add" class="col-sm-3 control-label">Date</label>
                        <div class="col-sm-9"> 
                            <div class="input-group date">
                                <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                <input type="text" class="form-control pull-right" id="datepicker_add" name="date" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>               

                    <div class="form-group">
                        <label for="note" class="col-sm-3 control-label">Note</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="note" id="note" rows="3" placeholder="Additional remarks..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                    <button type="submit" class="btn btn-primary btn-flat" name="add"><i class="fa fa-save"></i> Save Client</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
