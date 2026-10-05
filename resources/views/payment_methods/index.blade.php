@extends('layouts.master')

@section('title', 'JR || Corporation - Payment Method List')
@section('page_title', 'Payment Method List')

@section('breadcrumb')
<li>Payment Method</li>
<li class="active">Payment Method List</li>
@endsection

@section('content')
<div class="row">
  <div class="col-xs-12">
    <div class="box box-primary">
      <div class="box-header with-border">
          <a href="{{ route('payment-methods.index') }}" class="btn btn-success btn-sm btn-flat"><i class="fa fa-refresh"></i> Refresh </a>
          <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> Add Payment Method</a>            
      </div>
      <div class="box-body table-responsive">
        <table id="example1" class="table table-bordered table-striped">
          <thead style="background-color: #3c8dbc; color: white;">
            <th>ID</th>
            <th>Category (Type)</th>
            <th>Account / Method Name</th>
            <th>Balance (৳)</th>
            <th>Date</th>
            <th>Note</th>
            <th width="150">Action</th>
          </thead>
          <tbody>
            @foreach($methods as $method)
              @php
                $type_badge = "";
                if($method->account_type == 'Cash') { $type_badge = "<span class='label label-success'>Cash</span>"; }
                elseif($method->account_type == 'Bank') { $type_badge = "<span class='label label-primary'>Bank</span>"; }
                elseif($method->account_type == 'Online Bank') { $type_badge = "<span class='label label-info'>Online Bank</span>"; }
                elseif($method->account_type == 'MFS') { $type_badge = "<span class='label label-warning'>MFS</span>"; }
                else { $type_badge = "<span class='label label-default'>".$method->account_type."</span>"; }
              @endphp
              <tr>                
                <td>{{ $method->id }}</td>
                <td>{!! $type_badge !!}</td>
                <td><b>{{ $method->method_name }}</b></td>
                <td class="text-bold text-green">৳ {{ number_format((float)$method->amount, 2) }}</td>
                <td>{{ (!empty($method->date) && $method->date != '0000-00-00') ? date('d-M-Y', strtotime($method->date)) : 'N/A' }}</td>  
                <td>{{ $method->note }}</td> 
                <td>
                  <a href="#edit_{{ $method->id }}" data-toggle="modal" class="btn btn-success btn-sm btn-flat"><i class="fa fa-edit"></i> Edit</a>
                  <form action="{{ route('payment-methods.destroy', $method->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this method?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-trash"></i> Delete</button>
                  </form>
                </td>
              </tr>

              <!-- Edit Modal -->
              <div class="modal fade" id="edit_{{ $method->id }}">
                  <div class="modal-dialog">
                      <div class="modal-content">
                          <form class="form-horizontal" method="POST" action="{{ route('payment-methods.update', $method->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal">&times;</button>
                              <h4 class="modal-title"><b>Edit Payment Method</b></h4>
                            </div>
                            <div class="modal-body">
                              <div class="form-group">
                                  <label class="col-sm-3 control-label">Account Type</label>
                                  <div class="col-sm-9">
                                    <select class="form-control" name="account_type" required>
                                      <option value="Cash" {{ $method->account_type == 'Cash' ? 'selected' : '' }}>Cash</option>
                                      <option value="Bank" {{ $method->account_type == 'Bank' ? 'selected' : '' }}>Bank</option>
                                      <option value="Online Bank" {{ $method->account_type == 'Online Bank' ? 'selected' : '' }}>Online Bank</option>
                                      <option value="MFS" {{ $method->account_type == 'MFS' ? 'selected' : '' }}>MFS</option>
                                    </select>
                                  </div>
                              </div>
                              <div class="form-group">
                                  <label class="col-sm-3 control-label">Method / Bank Name</label>
                                  <div class="col-sm-9">
                                    <input type="text" class="form-control" name="method_name" value="{{ $method->method_name }}" required>
                                  </div>
                              </div>
                              <div class="form-group">
                                  <label class="col-sm-3 control-label">Balance</label>
                                  <div class="col-sm-9">
                                    <input type="number" step="any" class="form-control" name="amount" value="{{ $method->amount }}" required>
                                  </div>
                              </div>
                              <div class="form-group">
                                  <label class="col-sm-3 control-label">Note</label>
                                  <div class="col-sm-9">
                                    <textarea class="form-control" name="note">{{ $method->note }}</textarea>
                                  </div>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">Close</button>
                              <button type="submit" class="btn btn-success btn-flat">Update Method</button>
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
          <form class="form-horizontal" method="POST" action="{{ route('payment-methods.store') }}">
            @csrf
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title"><b>Add Payment Method</b></h4>
            </div>
            <div class="modal-body">
              <div class="form-group">
                  <label class="col-sm-3 control-label">Account Type</label>
                  <div class="col-sm-9">
                    <select class="form-control" name="account_type" required>
                      <option value="Cash">Cash</option>
                      <option value="Bank">Bank</option>
                      <option value="Online Bank">Online Bank</option>
                      <option value="MFS">MFS</option>
                    </select>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-3 control-label">Method / Bank Name</label>
                  <div class="col-sm-9">
                    <input type="text" class="form-control" name="method_name" placeholder="e.g. Pubali Bank PLC" required>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-3 control-label">Opening Balance</label>
                  <div class="col-sm-9">
                    <input type="number" step="any" class="form-control" name="amount" placeholder="0.00" value="0" required>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-3 control-label">Date</label>
                  <div class="col-sm-9">
                    <input type="date" class="form-control" name="date" value="{{ date('Y-m-d') }}">
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-3 control-label">Note</label>
                  <div class="col-sm-9">
                    <textarea class="form-control" name="note" placeholder="Account remarks..."></textarea>
                  </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary btn-flat">Save Method</button>
            </div>
          </form>
        </div>
    </div>
</div>
@endsection
