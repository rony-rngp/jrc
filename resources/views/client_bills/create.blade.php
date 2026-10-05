@extends('layouts.master')

@section('title', 'Create Client Bill - JRC ERP')
@section('page_title', 'Create Client Bill / Project Sale')

@section('content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="box box-primary">
            <div class="box-header with-border">
                <a href="{{ route('client-bills.index') }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back to List</a>
            </div>
            <form method="POST" action="{{ route('client-bills.store') }}">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label>Client / Company Name *</label>
                        <select class="form-control" name="company_name" required>
                            <option value="">- Select Client -</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->company_name }}">{{ $c->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Project Name</label>
                        <select class="form-control" name="project_name">
                            <option value="">None / General</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->project_name }}">{{ $p->project_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Bill / Sale Amount *</label>
                                <input type="number" step="any" class="form-control" name="bill_amount" value="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Received / Paid Amount</label>
                                <input type="number" step="any" class="form-control" name="pay_amount" value="0.00">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Payment Method</label>
                                <select class="form-control" name="method_name">
                                    @foreach($paymentMethods as $pm)
                                        <option value="{{ $pm->method_name }}">{{ $pm->method_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Billing Date *</label>
                                <input type="date" class="form-control" name="date" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Reference / Check No</label>
                        <input type="text" class="form-control" name="ref_no" placeholder="Optional ref/voucher no">
                    </div>
                    <div class="form-group">
                        <label>Note / Remarks</label>
                        <textarea class="form-control" name="note" placeholder="Note"></textarea>
                    </div>
                </div>
                <div class="box-footer text-right">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Client Bill</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
