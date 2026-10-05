@extends('layouts.master')

@section('title', 'JR || Corporation - Payment Methods')
@section('page_title', 'Payment Method')

@section('breadcrumb')
<li class="active">Payment Method</li>
@endsection

@section('content')
<div class="row">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-header with-border">
          <a href="{{ route('payment-methods.index') }}" class="btn btn-success"><i class="fa fa-refresh"></i> Refresh </a>
          <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> Add New Payment Method</a>
      </div>
      <div class="box-body table-responsive">
        <table id="example1" class="table table-bordered">
          <thead>
            <th>ID</th>
            <th>Type</th>
            <th>Method Name</th>
            <th>Date</th>
            <th>Live Balance</th>
            <th>Note</th>
            <th width="200">Tools</th>
          </thead>
          <tbody>
            @foreach($methods as $method)
              <tr>				
                <td>{{ $method->id }}</td>
                <td><span class="label label-info">{{ $method->account_type }}</span></td>
                <td><b>{{ $method->method_name }}</b></td>				  
                <td>{{ $method->date }}</td>
                <td style="font-weight: bold; color: {{ $method->amount < 0 ? 'red' : 'green' }};">
                  ৳ {{ number_format($method->amount, 2) }}
                </td>
                <td>{{ $method->note }}</td>
                <td>
                  <a href="#edit_{{ $method->id }}" data-toggle="modal" class="btn btn-success btn-sm btn-flat"><i class="fa fa-edit"></i> Edit</a>
                  <form action="{{ route('payment-methods.destroy', $method->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this payment method?');">
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

<!-- Edit Modals (Outside table for valid DOM structure and full width inputs) -->
@foreach($methods as $method)
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
                    <label class="col-sm-3 control-label">Update Date</label>
                    <div class="col-sm-9">
                      <input type="date" class="form-control" name="date" value="{{ $method->date }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Note</label>
                    <div class="col-sm-9">
                      <textarea class="form-control" name="note" rows="3">{{ $method->note }}</textarea>
                    </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                <button type="submit" class="btn btn-success btn-flat"><i class="fa fa-check-square-o"></i> Update Method</button>
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
                    <textarea class="form-control" name="note" rows="3" placeholder="Account remarks..."></textarea>
                  </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
              <button type="submit" class="btn btn-primary btn-flat"><i class="fa fa-save"></i> Save Method</button>
            </div>
          </form>
        </div>
    </div>
</div>
@endsection
