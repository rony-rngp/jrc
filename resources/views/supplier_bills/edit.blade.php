@extends('layouts.master')

@section('title', 'Edit Supplier Bill - JR Corporation')
@section('page_title', 'Edit Supplier Bill & Purchase')

@section('breadcrumb')
<li><a href="{{ route('supplier-bills.index') }}"><i class="fa fa-arrow-left"></i> Back to List</a></li>
@endsection

@push('styles')
<style>
    .bill-header-box { background: #fdfdfd; border: 1px solid #e1e4e8; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-top: 3px solid #f39c12; }
    .table-custom { background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
    .table-custom thead { background-color: #f39c12; color: #fff; }
    .table-custom th { text-align: center; font-weight: bold; border: none !important; padding: 12px !important;}
    .table-custom td { vertical-align: middle !important; border-top: 1px solid #f4f4f4 !important; }
    .grand-total-box { background: #eaf3f9; border: 2px dashed #f39c12; padding: 15px; border-radius: 8px; text-align: right; }
    .grand-total-text { font-size: 28px; font-weight: bold; color: #d9534f; margin: 0; }
    .payment-section { background: #fffaf0; border: 1px solid #f9cd9f; padding: 20px; border-radius: 8px; margin-top: 20px; display: none; }
    .dynamic-info-box { background: #fff; border: 1px dashed #ccc; padding: 15px; border-radius: 5px; margin-top: 10px; display: none; }
    .btn-add-more { border-radius: 20px; padding: 6px 20px; font-weight: bold; }
    .row-total-input { background: #f9f9f9 !important; font-weight: bold; color: #00a65a; text-align: right; }
    .due-text { color: #dd4b39; font-weight: bold; font-size: 18px; }
    .label-info-custom { background: #e08e0b !important; color: #fff; padding: 3px 10px; border-radius: 4px; font-size:12px; font-weight: bold; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-xs-12">
        <form method="POST" action="{{ route('supplier-bills.update', $bill->id) }}" id="billForm">
            @csrf
            @method('PUT')
            <div class="box box-warning">
                <div class="box-body bill-header-box">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Bill Entry Type <span class="text-danger">*</span></label>
                                <select class="form-control" name="bill_entry_type" id="bill_entry_type" required>
                                    <option value="Only Bill" {{ $bill->bill_type == 'Only Bill' ? 'selected' : '' }}>Only Bill & Dues</option>
                                    <option value="Bill with Payment" {{ $bill->bill_type == 'Bill with Payment' ? 'selected' : '' }}>Bill + Payment</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Supplier / Party <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="supplier_id" id="supplier_id" style="width:100%;" required>
                                    <option value="Cash Purchase" {{ $bill->supplier_id == 'Cash Purchase' ? 'selected' : '' }}>-- Cash Purchase (নগদ ক্রয়) --</option>
                                    @foreach($suppliers as $sup)
                                        <option value="{{ $sup->id }}" {{ ($bill->supplier_id == $sup->id || $bill->supplier_id == $sup->party_name) ? 'selected' : '' }}>{{ $sup->party_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Project Name <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="project_id" id="project_id" style="width:100%;" required>
                                    <option value="0">General / Head Office</option>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}" {{ $bill->project_id == $p->id ? 'selected' : '' }}>{{ $p->project_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Voucher / Invoice No <span class="text-danger">*</span></label>
                                <input type="text" name="voucher_no" id="voucher_no" class="form-control" value="{{ $bill->voucher_no }}" required>
                                <small id="voucher_error" class="text-danger" style="display:none; font-weight:bold;">This Voucher is already used!</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Bill Date <span class="text-danger">*</span></label>
                                <input type="date" name="bill_date" class="form-control" value="{{ $bill->bill_date }}" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-cubes"></i> Material / Item Details</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-primary btn-sm btn-add-more" id="add_row_btn"><i class="fa fa-plus"></i> Add More Item</button>
                    </div>
                </div>
                <div class="box-body">
                    <table class="table table-bordered table-custom" id="item_table">
                        <thead>
                            <tr>
                                <th width="35%">Material Name <span class="text-danger">*</span></th>
                                <th width="15%">Qty <span class="text-danger">*</span></th>
                                <th width="20%">Unit Price <span class="text-danger">*</span></th>
                                <th width="20%">Total Price</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody id="item_table_body">
                            @if(count($bill->items) > 0)
                                @foreach($bill->items as $idx => $it)
                                <tr>
                                    <td>
                                        <select class="form-control select2 product_select" name="product_id[]" style="width:100%;" required>
                                            <option value="">-- Select Material --</option>
                                            @foreach($categories as $c)
                                                <option value="{{ $c->id }}" {{ $it->product_id == $c->id ? 'selected' : '' }}>{{ $c->cat_name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" step="any" name="qty[]" class="form-control text-center qty auto-calc" value="{{ $it->qty }}" required></td>
                                    <td><input type="number" step="any" name="unit_price[]" class="form-control text-right price auto-calc" value="{{ $it->unit_price }}" required></td>
                                    <td><input type="text" name="total_price[]" class="form-control row-total-input" value="{{ number_format((float)$it->total_price, 2, '.', '') }}" readonly></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger remove-btn {{ count($bill->items) == 1 ? 'disabled' : '' }}" {{ count($bill->items) == 1 ? 'disabled' : '' }}><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td>
                                        <select class="form-control select2 product_select" name="product_id[]" style="width:100%;" required>
                                            <option value="">-- Select Material --</option>
                                            @foreach($categories as $c)
                                                <option value="{{ $c->id }}">{{ $c->cat_name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" step="any" name="qty[]" class="form-control text-center qty auto-calc" placeholder="0" required></td>
                                    <td><input type="number" step="any" name="unit_price[]" class="form-control text-right price auto-calc" placeholder="0.00" required></td>
                                    <td><input type="text" name="total_price[]" class="form-control row-total-input" value="0.00" readonly></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger remove-btn disabled" disabled><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                    <div class="row" style="margin-top: 20px;">
                        <div class="col-md-7">
                            <div class="payment-section" id="payment_div" style="{{ $bill->bill_type == 'Bill with Payment' ? 'display:block;' : 'display:none;' }}">
                                <h4 style="margin-top:0; border-bottom:1px solid #ddd; padding-bottom:5px; color:#e08e0b;"><b><i class="fa fa-money"></i> Instant Payment Details</b></h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Paid From Category</label>
                                            <select class="form-control" name="deposit_category" id="deposit_category">
                                                <option value="">-- Select Category --</option>
                                                <option value="Cash" {{ ($payment->deposit_category ?? '') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                                <option value="Bank" {{ ($payment->deposit_category ?? '') == 'Bank' ? 'selected' : '' }}>Bank</option>
                                                <option value="MFS" {{ ($payment->deposit_category ?? '') == 'MFS' ? 'selected' : '' }}>MFS</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Select Account</label>
                                            <select class="form-control select2" name="method_id" id="method_id" style="width:100%;">
                                                <option value="">-- First Select Category --</option>
                                                @foreach($paymentMethods as $pm)
                                                    <option value="{{ $pm->id }}" {{ ($payment->method_id ?? 0) == $pm->id ? 'selected' : '' }}>{{ $pm->method_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="bank_details_box" class="dynamic-info-box" style="{{ ($payment->deposit_category ?? '') == 'Bank' ? 'display:block;' : 'display:none;' }}">
                                    <span class="label-info-custom"><i class="fa fa-bank"></i> Bank Info</span>
                                    <div class="row" style="margin-top: 10px;">
                                        <div class="col-md-4">
                                            <label style="font-size:12px;">Transfer Type</label>
                                            <select class="form-control input-sm" name="bank_trans_type" id="bank_trans_type">
                                                <option value="">-- Select --</option>
                                                <option value="Check" {{ ($payment->bank_trans_type ?? '') == 'Check' ? 'selected' : '' }}>Check</option>
                                                <option value="Online Transfer" {{ ($payment->bank_trans_type ?? '') == 'Online Transfer' ? 'selected' : '' }}>Online</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 bank_input_fields" style="{{ !empty($payment->ref_no) ? 'display:block;' : 'display:none;' }}">
                                            <label style="font-size:12px;" id="bank_no_label">Number</label>
                                            <input type="text" name="bank_ref_no" class="form-control input-sm" value="{{ $payment->ref_no ?? '' }}" placeholder="No.">
                                        </div>
                                        <div class="col-md-4 bank_input_fields" style="{{ !empty($payment->ref_date) ? 'display:block;' : 'display:none;' }}">
                                            <label style="font-size:12px;">Date</label>
                                            <input type="date" name="bank_ref_date" class="form-control input-sm" value="{{ $payment->ref_date ?? date('Y-m-d') }}">
                                        </div>
                                    </div>
                                </div>

                                <div id="mfs_details_box" class="dynamic-info-box" style="{{ ($payment->deposit_category ?? '') == 'MFS' ? 'display:block;' : 'display:none;' }}">
                                    <span class="label-info-custom"><i class="fa fa-mobile"></i> MFS Info</span>
                                    <div class="row" style="margin-top: 10px;">
                                        <div class="col-md-4">
                                            <label style="font-size:12px;">MFS Type</label>
                                            <select class="form-control input-sm" name="mfs_type" id="mfs_type">
                                                <option value="">-- Select --</option>
                                                @foreach(['bKash', 'Nagad', 'Rocket', 'Tap', 'mCash', 'Upay', 'OK Wallet'] as $mfs)
                                                    <option value="{{ $mfs }}" {{ ($payment->bank_trans_type ?? '') == $mfs ? 'selected' : '' }}>{{ $mfs }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 mfs_input_fields" style="{{ !empty($payment->ref_no) ? 'display:block;' : 'display:none;' }}">
                                            <label style="font-size:12px;" id="mfs_no_label">Trans ID</label>
                                            <input type="text" name="mfs_ref_no" class="form-control input-sm" value="{{ $payment->ref_no ?? '' }}" placeholder="TrxID">
                                        </div>
                                        <div class="col-md-4 mfs_input_fields" style="{{ !empty($payment->ref_date) ? 'display:block;' : 'display:none;' }}">
                                            <label style="font-size:12px;">Date</label>
                                            <input type="date" name="mfs_ref_date" class="form-control input-sm" value="{{ $payment->ref_date ?? date('Y-m-d') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row" style="margin-top: 15px;">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Paid Amount <span class="text-danger">*</span></label>
                                            <input type="number" step="any" name="paid_amount" id="paid_amount" class="form-control" value="{{ $bill->paid_amount }}" style="font-size:20px; font-weight:bold; color:#00a65a;" placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group" style="padding-top:25px;">
                                            <span class="due-text">Due: ৳ <span id="due_display">{{ number_format($bill->due_amount, 2) }}</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group" style="margin-top:15px;">
                                <label>Remarks / Note</label>
                                <textarea name="note" class="form-control" rows="2" placeholder="Write any specific note...">{{ $bill->note }}</textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-5">
                            <div class="grand-total-box">
                                <p style="margin:0; font-size:16px; color:#666;">Grand Total Amount</p>
                                <p class="grand-total-text">৳ <span id="grand_total_display">{{ number_format($bill->grand_total, 2) }}</span></p>
                                <input type="hidden" name="grand_total" id="grand_total_input" value="{{ $bill->grand_total }}">
                            </div>

                            <button type="submit" name="update_supplier_bill" id="btn_submit" class="btn btn-warning btn-block btn-lg" style="margin-top: 15px; font-weight:bold;"><i class="fa fa-save"></i> Update Supplier Bill</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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

    var ajaxUrl = "{{ route('supplier-bills.index') }}";
    var productOptionsHTML = `@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->cat_name }}</option>@endforeach`;

    $('#bill_entry_type').change(function(){
        if($(this).val() == 'Bill with Payment'){
            $('#payment_div').slideDown();
            $('#paid_amount').attr('required', true);
        } else {
            $('#payment_div').slideUp();
            $('#paid_amount').removeAttr('required').val('');
            calculateDue();
        }
    });

    $('#add_row_btn').click(function(){
        var newRow = `<tr>
            <td>
                <select class="form-control select2 product_select" name="product_id[]" style="width:100%;" required>
                    <option value="">-- Select Material --</option>
                    ${productOptionsHTML}
                </select>
            </td>
            <td><input type="number" step="any" name="qty[]" class="form-control text-center qty auto-calc" placeholder="0" required></td>
            <td><input type="number" step="any" name="unit_price[]" class="form-control text-right price auto-calc" placeholder="0.00" required></td>
            <td><input type="text" name="total_price[]" class="form-control row-total-input" value="0.00" readonly></td>
            <td class="text-center">
                <button type="button" class="btn btn-danger remove-btn"><i class="fa fa-trash"></i></button>
            </td>
        </tr>`;
        $('#item_table_body').append(newRow);
        $('.select2').select2();
        updateRemoveButtons();
        updateProductOptions();
    });

    $(document).on('click', '.remove-btn', function(){
        if($('#item_table_body tr').length > 1){
            $(this).closest('tr').remove();
            calculateGrandTotal();
            updateRemoveButtons();
            updateProductOptions();
        }
    });

    function updateRemoveButtons(){
        if($('#item_table_body tr').length <= 1){
            $('#item_table_body .remove-btn').addClass('disabled').attr('disabled', true);
        } else {
            $('#item_table_body .remove-btn').removeClass('disabled').removeAttr('disabled');
        }
    }

    $(document).on('input', '.auto-calc', function(){
        var row = $(this).closest('tr');
        var qty = parseFloat(row.find('.qty').val()) || 0;
        var price = parseFloat(row.find('.price').val()) || 0;
        var total = (qty * price).toFixed(2);
        row.find('.row-total-input').val(total);
        calculateGrandTotal();
    });

    function calculateGrandTotal(){
        var grandTotal = 0;
        $('.row-total-input').each(function(){
            grandTotal += parseFloat($(this).val()) || 0;
        });
        $('#grand_total_display').text(grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#grand_total_input').val(grandTotal.toFixed(2));
        calculateDue();
    }

    $('#paid_amount').on('input', function(){
        calculateDue();
    });

    function calculateDue(){
        var total = parseFloat($('#grand_total_input').val()) || 0;
        var paid = parseFloat($('#paid_amount').val()) || 0;
        var due = total - paid;
        if(due < 0) due = 0;
        $('#due_display').text(due.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    }

    $('#deposit_category').change(function(){
        var cat = $(this).val();
        $('#method_id').html('<option value="">Loading accounts...</option>');
        $('#bank_details_box, #mfs_details_box').hide();
        
        if(cat){
            $.post(ajaxUrl, { ajax_request: 'get_accounts_by_category', category: cat }, function(data){
                var opts = '<option value="">-- Choose Account --</option>';
                $.each(data, function(i, item){
                    opts += `<option value="${item.id}">${item.method_name} (Bal: ${parseFloat(item.amount).toFixed(2)})</option>`;
                });
                $('#method_id').html(opts);
            });
            if(cat == 'Bank') { $('#bank_details_box').slideDown(); }
            if(cat == 'MFS') { $('#mfs_details_box').slideDown(); }
        } else {
            $('#method_id').html('<option value="">-- First Select Category --</option>');
        }
    });

    $('#bank_trans_type').change(function(){
        var val = $(this).val();
        if(val){
            $('.bank_input_fields').show();
            $('#bank_no_label').text(val == 'Check' ? 'Check No' : 'Trx Ref No');
        } else {
            $('.bank_input_fields').hide();
        }
    });

    $('#mfs_type').change(function(){
        var val = $(this).val();
        if(val){
            $('.mfs_input_fields').show();
            $('#mfs_no_label').text(val + ' Trx ID');
        } else {
            $('.mfs_input_fields').hide();
        }
    });

    function updateProductOptions() {
        var selectedProducts = [];
        $('.product_select').each(function() {
            var val = $(this).val();
            if (val) { selectedProducts.push(val); }
        });
        $('.product_select').each(function() {
            var currentVal = $(this).val();
            $(this).find('option').each(function() {
                var optVal = $(this).val();
                if (optVal && optVal !== currentVal && selectedProducts.includes(optVal)) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            });
        });
    }

    $(document).on('change', '.product_select', function() {
        updateProductOptions();
    });
});
</script>
@endpush
