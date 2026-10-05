@extends('layouts.master')

@section('title', 'Edit Receive Voucher - JRC ERP')
@section('page_title', 'Edit Receive Voucher')

@section('content')
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-edit"></i> Edit Receive Voucher: <b>{{ $voucherNo }}</b></h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('receive-vouchers.index') }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back to List</a>
                </div>
            </div>
            <form class="form-horizontal" method="POST" action="{{ route('receive-vouchers.update', $id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="type" value="{{ $type }}">

                <div class="box-body">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Voucher No</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" value="{{ $voucherNo }}" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Voucher Date <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="date" name="t_date" class="form-control" value="{{ $voucher->date ?? $voucher->t_date }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Amount (৳) <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="number" step="any" name="amount" class="form-control input-lg text-bold text-green" value="{{ $voucher->pay_amount ?? $voucher->amount }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Deposit Account <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <select class="form-control select2" name="method_id" style="width:100%;" required>
                                @foreach($paymentMethods as $pm)
                                    <option value="{{ $pm->id }}" {{ $voucher->method_id == $pm->id ? 'selected' : '' }}>
                                        {{ $pm->method_name }} (৳ {{ number_format((float)$pm->amount, 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Note / Remarks</label>
                        <div class="col-sm-9">
                            <textarea name="note" class="form-control" rows="3">{{ $voucher->note }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="box-footer text-right">
                    <a href="{{ route('receive-vouchers.index') }}" class="btn btn-default"><i class="fa fa-times"></i> Cancel</a>
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Update Voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
