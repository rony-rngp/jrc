@extends('layouts.master')

@section('title', 'Edit Client - JRC ERP')
@section('page_title', 'Edit Client Profile')

@section('content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="box box-primary">
            <div class="box-header with-border">
                <a href="{{ route('clients.index') }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back to List</a>
            </div>
            <form class="form-horizontal" method="POST" action="{{ route('clients.update', $client->id) }}">
                @csrf
                @method('PUT')
                <div class="box-body">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Company Name *</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="company_name" value="{{ $client->company_name }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Contact Person</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="persion_name" value="{{ $client->persion_name }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Contact Number</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="contact" value="{{ $client->contact }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Address</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="address">{{ $client->address }}</textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Opening Balance</label>
                        <div class="col-sm-9">
                            <input type="number" step="any" class="form-control" name="opening_balance" value="{{ $client->opening_balance ?? 0 }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Date</label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control" name="date" value="{{ $client->date }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Note</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="note">{{ $client->note }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="box-footer text-right">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Client</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
