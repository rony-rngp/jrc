@extends('layouts.master')

@section('title', 'Edit Supplier - JRC ERP')
@section('page_title', 'Edit Supplier Profile')

@section('content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="box box-primary">
            <div class="box-header with-border">
                <a href="{{ route('suppliers.index') }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back to List</a>
            </div>
            <form class="form-horizontal" method="POST" action="{{ route('suppliers.update', $supplier->id) }}">
                @csrf
                @method('PUT')
                <div class="box-body">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Party Name *</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="party_name" value="{{ $supplier->party_name }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Phone</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="party_phone" value="{{ $supplier->party_phone }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Category / Type</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="party_type" value="{{ $supplier->party_type }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Address</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="address">{{ $supplier->address }}</textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Date</label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control" name="date" value="{{ $supplier->date }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Note</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="note">{{ $supplier->note }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="box-footer text-right">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
