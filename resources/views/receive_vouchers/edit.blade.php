@extends('layouts.master')

@section('title', 'JR || Corporation - Edit Receive Voucher')
@section('page_title', 'Edit Receive Voucher')

@section('breadcrumb')
<li>Accounts</li>
<li><a href="{{ route('receive-vouchers.index') }}">Receive Voucher</a></li>
<li class="active">Edit Voucher</li>
@endsection

@push('styles')
<style>
    .dynamic-info-box { background: #f4f7f9; border: 1px solid #cfe0eb; padding: 20px; border-radius: 8px; margin-bottom: 20px; display: none; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05); }
    .label-info-custom { background: #3c8dbc !important; color: #fff; padding: 5px 12px; border-radius: 4px; margin-bottom: 15px; display: inline-block; font-weight: bold; }
    .hr-style { border-top: 1px solid #d2d6de; margin: 20px 0; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">ভাউচার সংশোধন করুন</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('receive-vouchers.index') }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back to List</a>
                </div>
            </div>
            <form class="form-horizontal" method="POST" action="{{ route('receive-vouchers.update', $id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" value="{{ $id }}">
                
                <div class="box-body">
                    
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Receive Type <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <select class="form-control" disabled>
                                <option value="Client" {{ $type == 'Client' ? 'selected' : '' }}>Client (ক্লাইন্ট থেকে জমা)</option>
                                <option value="Others" {{ $type == 'Others' ? 'selected' : '' }}>Others (অন্যান্য খাত)</option>
                            </select>
                            <input type="hidden" name="type" value="{{ $type }}">
                            <small class="text-danger"><i>* ডাটাবেস নিরাপত্তার জন্য এডিট পেজে Receive Type পরিবর্তন করা যায় না।</i></small>
                        </div>
                    </div>

                    @if($type == 'Client')
                        <div id="client_section">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Client Name</label>
                                <div class="col-sm-9">
                                    <select class="form-control select2" name="client_name" style="width:100%;">
                                        @foreach($clients as $c)
                                            <option value="{{ $c->company_name }}" {{ $c->company_name == $client_name ? 'selected' : '' }}>{{ $c->company_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($type == 'Others')
                        <div id="heads_section">
                            <input type="hidden" name="head_id" id="final_head_id" value="{{ $voucher->head_id ?? '' }}">
                            <div id="dynamic_heads_container">
                                @php
                                    $parentId = 0;
                                    $level = 1;
                                @endphp
                                @if(!empty($lineage_ids))
                                    @foreach($lineage_ids as $selected_id)
                                        @php
                                            $heads = \App\Models\AccountHead::where('parent_id', $parentId)->where('head_type', 'Receive')->orderBy('head_name', 'asc')->get();
                                        @endphp
                                        <div class="form-group head-group" data-level="{{ $level }}">
                                            <label class="col-sm-3 control-label">{{ $level == 1 ? 'Account Head' : 'Sub Head' }}</label>
                                            <div class="col-sm-9">
                                                <select class="form-control select2 head-select" data-level="{{ $level }}" style="width:100%;">
                                                    <option value="">-- Select Head --</option>
                                                    @foreach($heads as $h)
                                                        <option value="{{ $h->id }}" {{ $h->id == $selected_id ? 'selected' : '' }}>{{ $h->head_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        @php
                                            $parentId = $selected_id;
                                            $level++;
                                        @endphp
                                    @endforeach
                                @else
                                    @php
                                        $heads = \App\Models\AccountHead::where(function($q){ $q->whereNull('parent_id')->orWhere('parent_id', 0); })->where('head_type', 'Receive')->orderBy('head_name', 'asc')->get();
                                    @endphp
                                    <div class="form-group head-group" data-level="1">
                                        <label class="col-sm-3 control-label">Account Head</label>
                                        <div class="col-sm-9">
                                            <select class="form-control select2 head-select" data-level="1" style="width:100%;">
                                                <option value="">-- Select Head --</option>
                                                @foreach($heads as $h)
                                                    <option value="{{ $h->id }}" {{ ($voucher->head_id ?? 0) == $h->id ? 'selected' : '' }}>{{ $h->head_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Amount (টাকা) <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="number" step="any" name="amount" class="form-control input-lg" value="{{ $amount }}" required>
                            <input type="hidden" name="old_amount" value="{{ $amount }}">
                        </div>
                    </div>

                    <div class="hr-style"></div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Deposit Category <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <select class="form-control" name="deposit_category" id="deposit_category" required>
                                <option value="Cash" {{ $deposit_category == 'Cash' ? 'selected' : '' }}>Cash (নগদ)</option>
                                <option value="Bank" {{ $deposit_category == 'Bank' ? 'selected' : '' }}>Bank (ব্যাংক একাউন্ট)</option>
                                <option value="MFS" {{ $deposit_category == 'MFS' ? 'selected' : '' }}>Mobile Financial Services (MFS)</option>
                            </select>
                        </div>
                    </div>

                    <div id="method_container">
                        <div class="form-group">
                            <label class="col-sm-3 control-label" id="method_label">Deposit To Account <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <select class="form-control select2" name="method_id" id="method_id" style="width:100%;" required>
                                    @foreach($paymentMethods as $pm)
                                        <option value="{{ $pm->id }}" {{ $voucher->method_id == $pm->id ? 'selected' : '' }}>
                                            {{ $pm->method_name }} (Bal: ৳{{ number_format((float)$pm->amount, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="old_method_id" value="{{ $voucher->method_id }}">
                            </div>
                        </div>
                    </div>

                    <div id="bank_details_box" class="dynamic-info-box" style="{{ $deposit_category == 'Bank' ? 'display:block;' : 'display:none;' }}">
                        <span class="label-info-custom"><i class="fa fa-bank"></i> Bank Transaction Info</span>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Check / Ref No</label>
                            <div class="col-sm-9"><input type="text" name="bank_ref_no" class="form-control" value="{{ $voucherNo }}"></div>
                        </div>
                    </div>

                    <div id="mfs_details_box" class="dynamic-info-box" style="{{ $deposit_category == 'MFS' ? 'display:block;' : 'display:none;' }}">
                        <span class="label-info-custom"><i class="fa fa-mobile"></i> MFS Transaction Details</span>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Transaction ID</label>
                            <div class="col-sm-9"><input type="text" name="mfs_ref_no" class="form-control" value="{{ $voucherNo }}"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Voucher No <span class="text-danger">*</span></label>
                        <div class="col-sm-9"><input type="text" name="voucher" class="form-control" value="{{ $voucherNo }}" required></div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Voucher Date</label>
                        <div class="col-sm-9"><input type="date" name="t_date" class="form-control" value="{{ $date }}" required></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Note</label>
                        <div class="col-sm-9"><textarea name="note" class="form-control" rows="2">{{ $note }}</textarea></div>
                    </div>
                </div>
                
                <div class="box-footer text-center">
                    <a href="{{ route('receive-vouchers.index') }}" class="btn btn-default btn-flat btn-lg">Cancel</a>
                    <button type="submit" class="btn btn-warning btn-flat btn-lg"><i class="fa fa-save"></i> Update Voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    if($.fn.select2) { $('.select2').select2(); }

    var ajaxUrl = "{{ route('receive-vouchers.index') }}";

    // ক্যাটাগরি চেঞ্জ (Bank, MFS, Cash)
    $('#deposit_category').change(function(){
        var cat = $(this).val();
        $('#method_id').html('<option value="">-- Select Account --</option>');
        $('#bank_details_box, #mfs_details_box').hide();
        if(!cat) return;
        $.post(ajaxUrl, {ajax_request: 'get_payment_methods', type: cat}, function(res){
            if(res.length > 0){
                $.each(res, function(i, item){
                    $('#method_id').append('<option value="'+item.id+'">'+item.method_name+' (Balance: ৳'+parseFloat(item.amount).toFixed(2)+')</option>');
                });
                if(cat === 'Bank') $('#bank_details_box').slideDown();
                if(cat === 'MFS') $('#mfs_details_box').slideDown();
            }
        }, 'json');
    });

    // ডাইনামিক অ্যাকাউন্ট হেড (Sub-head) লোডিং লজিক
    $(document).on('change', '.head-select', function(){
        var level = parseInt($(this).data('level'));
        
        // নিচের লেভেলের ড্রপডাউন মুছে ফেলা
        $('.head-group').filter(function(){ return $(this).data('level') > level; }).remove();
        
        // ফাইনাল আইডিতে বর্তমান ভ্যালু সেট করা
        $('#final_head_id').val($(this).val());
        
        if(!$(this).val()) return;
        
        // AJAX এর মাধ্যমে সাব-হেড কল করা
        $.post(ajaxUrl, {ajax_request: 'get_sub_heads', parent_id: $(this).val()}, function(res){
            if(res.length > 0){
                var next = level + 1;
                var html = '<div class="form-group head-group" data-level="'+next+'"><label class="col-sm-3 control-label">Sub Head</label><div class="col-sm-9"><select class="form-control select2 head-select" data-level="'+next+'" style="width:100%;"><option value="">-- Select Sub Head --</option>';
                $.each(res, function(i, item){ html += '<option value="'+item.id+'">'+item.head_name+'</option>'; });
                html += '</select></div></div>';
                
                $('#dynamic_heads_container').append(html);
                $('.head-select[data-level="'+next+'"]').select2();
            }
        }, 'json');
    });
});
</script>
@endpush
