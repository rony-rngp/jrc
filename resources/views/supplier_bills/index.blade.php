@extends('layouts.master')

@section('title', 'JR || Corporation - Supplier Bill Entry & Purchase')
@section('page_title', 'Supplier Bill Entry & Purchase')

@section('breadcrumb')
<li>Purchase</li>
<li class="active">Supplier Bill</li>
@endsection

@push('styles')
<style>
    .bill-header-box { background: #fdfdfd; border: 1px solid #e1e4e8; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-top: 3px solid #3c8dbc; }
    .table-custom { background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
    .table-custom thead { background-color: #3c8dbc; color: #fff; }
    .table-custom th { text-align: center; font-weight: bold; border: none !important; padding: 12px !important;}
    .table-custom td { vertical-align: middle !important; border-top: 1px solid #f4f4f4 !important; }
    .grand-total-box { background: #eaf3f9; border: 2px dashed #3c8dbc; padding: 15px; border-radius: 8px; text-align: right; }
    .grand-total-text { font-size: 28px; font-weight: bold; color: #d9534f; margin: 0; }
    .payment-section { background: #fffaf0; border: 1px solid #f9cd9f; padding: 20px; border-radius: 8px; margin-top: 20px; display: none; }
    .dynamic-info-box { background: #fff; border: 1px dashed #ccc; padding: 15px; border-radius: 5px; margin-top: 10px; display: none; }
    .btn-add-more { border-radius: 20px; padding: 6px 20px; font-weight: bold; }
    .row-total-input { background: #f9f9f9 !important; font-weight: bold; color: #00a65a; text-align: right; }
    .due-text { color: #dd4b39; font-weight: bold; font-size: 18px; }
    .label-info-custom { background: #e08e0b !important; color: #fff; padding: 3px 10px; border-radius: 4px; font-size:12px; font-weight: bold; }
    .budget-warning-box { background: #f9dede; border: 1px solid #ebccd1; color: #a94442; padding: 12px; border-radius: 5px; margin-top: 15px; display: none; font-size: 13px; font-weight: bold; }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('supplier-bills.store') }}" id="billForm">
    @csrf
    <div class="box box-primary">
        <div class="box-body bill-header-box">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Bill Entry Type <span class="text-danger">*</span></label>
                        <select class="form-control" name="bill_entry_type" id="bill_entry_type" required>
                            <option value="Only Bill">Only Bill & Dues</option>
                            <option value="Bill with Payment">Bill + Payment</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Supplier / Party <span class="text-danger">*</span></label>
                        <select class="form-control select2" name="supplier_id" id="supplier_id" style="width:100%;" required>
                            <option value="">-- Select Supplier --</option>
                            <option value="Cash Purchase">Cash Purchase</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->party_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Project Name <span class="text-danger">*</span></label>
                        <select class="form-control select2" name="project_id" id="project_id" style="width:100%;" required>
                            <option value="">-- Select Project --</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->project_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Voucher / Invoice No <span class="text-danger">*</span></label>
                        <input type="text" name="voucher_no" id="voucher_no" class="form-control" placeholder=" Voucher No" required>
                        <small id="voucher_error" class="text-danger" style="display:none; font-weight:bold;">This Voucher is already used!</small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Bill Date <span class="text-danger">*</span></label>
                        <input type="date" name="bill_date" class="form-control" value="{{ date('Y-m-d') }}" required>
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
                </tbody>
            </table>

            <div class="row" style="margin-top: 20px;">
                <div class="col-md-7">
                    <div class="payment-section" id="payment_div">
                        <h4 style="margin-top:0; border-bottom:1px solid #ddd; padding-bottom:5px; color:#e08e0b;"><b><i class="fa fa-money"></i> Instant Payment Details</b></h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Paid From Category</label>
                                    <select class="form-control" name="deposit_category" id="deposit_category">
                                        <option value="">-- Select Category --</option>
                                        <option value="Cash">Cash</option>
                                        <option value="Bank">Bank</option>
                                        <option value="MFS">MFS</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Select Account</label>
                                    <select class="form-control select2" name="method_id" id="method_id" style="width:100%;">
                                        <option value="">-- First Select Category --</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div id="bank_details_box" class="dynamic-info-box">
                            <span class="label-info-custom"><i class="fa fa-bank"></i> Bank Info</span>
                            <div class="row" style="margin-top: 10px;">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Transfer Type</label>
                                        <select class="form-control" name="bank_trans_type" id="bank_trans_type">
                                            <option value="">Select</option>
                                            <option value="Check">Check</option>
                                            <option value="Online Transfer">Online Transfer</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-8 bank_input_fields" style="display:none;">
                                    <div class="form-group">
                                        <label id="bank_no_label">Check / Ref No</label>
                                        <div class="row">
                                            <div class="col-md-6"><input type="text" name="bank_ref_no" class="form-control" placeholder="No."></div>
                                            <div class="col-md-6"><input type="date" name="bank_ref_date" class="form-control" value="{{ date('Y-m-d') }}"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="mfs_details_box" class="dynamic-info-box">
                            <span class="label-info-custom"><i class="fa fa-mobile"></i> MFS Info</span>
                            <div class="row" style="margin-top: 10px;">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>MFS Type</label>
                                        <select class="form-control" name="mfs_type" id="mfs_type">
                                            <option value="">Select</option>
                                            <option value="bKash">bKash</option>
                                            <option value="Nagad">Nagad</option>
                                            <option value="Rocket">Rocket</option>
                                            <option value="Tap">Tap</option>
                                            <option value="mCash">mCash</option>
                                            <option value="Upay">Upay</option>
                                            <option value="OK Wallet">OK Wallet</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-8 mfs_input_fields" style="display:none;">
                                    <div class="form-group">
                                        <label id="mfs_no_label">Trx ID & Date</label>
                                        <div class="row">
                                            <div class="col-md-6"><input type="text" name="mfs_ref_no" class="form-control" placeholder="Trx ID"></div>
                                            <div class="col-md-6"><input type="date" name="mfs_ref_date" class="form-control" value="{{ date('Y-m-d') }}"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top:10px;">
                            <label>Amount Paid Now (৳)</label>
                            <input type="number" step="any" name="paid_amount" id="paid_amount" class="form-control input-lg text-bold text-green" placeholder="0.00" value="0.00">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 15px;">
                        <label>Note / Remarks</label>
                        <textarea class="form-control" name="note" rows="2" placeholder="Enter remarks..."></textarea>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="grand-total-box">
                        <p style="margin: 0; font-size: 14px; text-transform: uppercase; color: #555;">Grand Total</p>
                        <h2 class="grand-total-text">৳ <span id="grand_total_display">0.00</span></h2>
                        <input type="hidden" name="grand_total" id="grand_total_input" value="0">
                        
                        <div id="due_box" style="margin-top: 15px; border-top: 1px solid #cce; padding-top: 10px; display:none;">
                            <span class="text-muted">Calculated Due Amount:</span>
                            <div class="due-text">৳ <span id="due_display">0.00</span></div>
                        </div>

                        <div id="budget_warning" class="budget-warning-box">
                            <i class="fa fa-exclamation-triangle"></i> <span id="budget_warning_text"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-footer text-right">
            <button type="reset" class="btn btn-default btn-flat"><i class="fa fa-refresh"></i> Reset</button>
            <button type="submit" name="save_supplier_bill" id="btn_submit" class="btn btn-success btn-flat btn-lg"><i class="fa fa-save"></i> Save Supplier Bill</button>
        </div>
    </div>
</form>

<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-list"></i> Supplier Bills List</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped" id="example1">
                    <thead class="bg-primary">
                        <tr>
                            <th>Bill No</th>
                            <th>Date</th>
                            <th>Voucher</th>
                            <th>Supplier</th>
                            <th>Project</th>
                            <th>Bill Type</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bills as $b)
                            <tr>
                                <td><span class="label label-primary" style="font-size:13px;">#{{ str_pad($b->id, 5, '0', STR_PAD_LEFT) }}</span></td>
                                <td>{{ date('d-m-Y', strtotime($b->bill_date)) }}</td>
                                <td><span class="label label-info" style="font-size:14px;">{{ $b->voucher_no }}</span></td>
                                <td>
                                    @if($b->supplier_id == 'Cash Purchase')
                                        <span class="text-primary"><b>Cash Purchase</b></span>
                                    @else
                                        {{ $b->supplier->party_name ?? $b->supplier_id }}
                                    @endif
                                </td>
                                <td><b>{{ $b->project->project_name ?? 'N/A' }}</b></td>
                                <td>
                                    @if($b->bill_type == 'Bill with Payment')
                                        <span class="label label-success">{{ $b->bill_type }}</span>
                                    @else
                                        <span class="label label-warning">{{ $b->bill_type }}</span>
                                    @endif
                                </td>
                                <td class="text-bold">৳ {{ number_format((float)$b->grand_total, 2) }}</td>
                                <td class="text-bold" style="color:#00a65a;">৳ {{ number_format((float)$b->paid_amount, 2) }}</td>
                                <td class="text-bold" style="{{ (float)$b->due_amount > 0 ? 'color:#dd4b39;' : 'color:#00a65a;' }}">৳ {{ number_format((float)$b->due_amount, 2) }}</td>
                                <td>
                                    @if($b->approval_status == 'Pending')
                                        <span class="label label-warning"><i class="fa fa-clock-o"></i> Pending</span>
                                    @elseif($b->approval_status == 'Rejected')
                                        <span class="label label-danger"><i class="fa fa-times-circle"></i> Rejected</span>
                                    @else
                                        <span class="label label-success"><i class="fa fa-check-circle"></i> Approved</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('supplier-bills.show', $b->id) }}" target="_blank" class="btn btn-primary btn-xs btn-flat" title="Print"><i class="fa fa-print"></i></a>
                                    <a href="{{ route('supplier-bills.edit', $b->id) }}" class="btn btn-success btn-xs btn-flat" title="Edit" style="margin-left:3px;"><i class="fa fa-edit"></i></a>
                                    <form action="{{ route('supplier-bills.destroy', $b->id) }}" method="POST" style="display:inline-block; margin-left:3px;" onsubmit="return confirm('Are you sure you want to delete this Bill? If any payment was made, the account balance will be safely reverted.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs btn-flat" title="Delete"><i class="fa fa-trash"></i></button>
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

    var currentProjectBudget = 0;
    var currentProjectExpense = 0;

    $('#project_id').change(function(){
        var proj_id = $(this).val();
        currentProjectBudget = 0;
        currentProjectExpense = 0;
        $('#budget_warning').hide();
        
        if(proj_id) {
            $.post(ajaxUrl, { ajax_request: 'get_project_budget', project_id: proj_id }, function(res) {
                currentProjectBudget = parseFloat(res.estimated_value) || 0;
                currentProjectExpense = parseFloat(res.total_expense) || 0;
                calculateGrandTotal();
            }, 'json');
        }
    });

    $('#bill_entry_type').change(function(){
        if($(this).val() === 'Bill with Payment') {
            $('#payment_div').slideDown();
            $('#due_box').slideDown();
            $('#paid_amount').prop('required', true);
        } else {
            $('#payment_div').slideUp();
            $('#due_box').slideUp();
            $('#paid_amount').prop('required', false).val('0.00');
        }
        calculateDue();
    });

    function updateProductOptions() {
        var selectedProducts = [];
        $('.product_select').each(function() {
            var val = $(this).val();
            if(val) selectedProducts.push(val);
        });

        $('.product_select').each(function() {
            var currentSelect = $(this);
            var currentVal = currentSelect.val();

            currentSelect.find('option').each(function() {
                var optVal = $(this).val();
                if(optVal && optVal !== currentVal && selectedProducts.includes(optVal)) {
                    $(this).prop('disabled', true);
                } else {
                    $(this).prop('disabled', false);
                }
            });
            if($.fn.select2) { currentSelect.select2(); }
        });
    }

    $(document).on('change', '.product_select', function() { updateProductOptions(); });

    $('#add_row_btn').click(function(){
        var newRow = `
        <tr>
            <td>
                <select class="form-control product_select dynamic-select2" name="product_id[]" style="width:100%;" required>
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
        
        if($.fn.select2) { $('.dynamic-select2').select2(); }
        $('.dynamic-select2').removeClass('dynamic-select2');
        updateProductOptions(); 
    });

    $(document).on('click', '.remove-btn', function(){
        if(!$(this).hasClass('disabled')){
            $(this).closest('tr').remove();
            calculateGrandTotal();
            updateProductOptions(); 
        }
    });

    $(document).on('input', '.auto-calc', function(){
        var row = $(this).closest('tr');
        var qty = parseFloat(row.find('.qty').val()) || 0;
        var price = parseFloat(row.find('.price').val()) || 0;
        var total = qty * price;
        row.find('.row-total-input').val(total.toFixed(2));
        calculateGrandTotal();
    });

    function calculateGrandTotal() {
        var grandTotal = 0;
        $('.row-total-input').each(function(){ grandTotal += parseFloat($(this).val()) || 0; });
        $('#grand_total_display').text(grandTotal.toLocaleString('en-IN', {minimumFractionDigits: 2}));
        $('#grand_total_input').val(grandTotal.toFixed(2));
        
        if(currentProjectBudget > 0) {
            var totalWithThisBill = currentProjectExpense + grandTotal;
            if(totalWithThisBill > currentProjectBudget) {
                var exceedAmt = totalWithThisBill - currentProjectBudget;
                var msg = 'Warning: This purchase exceeds the Project Estimated Value (৳'+currentProjectBudget.toLocaleString('en-IN')+') by <b>৳'+exceedAmt.toLocaleString('en-IN', {minimumFractionDigits: 2})+'</b>!';
                $('#budget_warning_text').html(msg);
                $('#budget_warning').slideDown();
            } else {
                $('#budget_warning').slideUp();
            }
        } else {
            $('#budget_warning').slideUp();
        }
        calculateDue();
    }

    $('#deposit_category').change(function(){
        var cat = $(this).val();
        $('#method_id').html('<option value="">Loading...</option>');
        $('#bank_details_box, #mfs_details_box').hide();
        
        if(!cat) return;
        
        $.post(ajaxUrl, { ajax_request: 'get_payment_methods', type: cat }, function(res) {
            var opts = '<option value="">-- Select Account --</option>';
            $.each(res, function(i, item){ opts += '<option value="'+item.id+'">'+item.method_name+' ('+item.amount_formatted+')</option>'; });
            $('#method_id').html(opts).trigger('change');
            
            if(cat === 'Bank') $('#bank_details_box').slideDown();
            if(cat === 'MFS') $('#mfs_details_box').slideDown();
        }, 'json');
    });

    $('#bank_trans_type').change(function(){
        var val = $(this).val();
        if(val) {
            $('#bank_no_label').text((val === 'Check') ? 'Check Number' : 'Trans. No/ID');
            $('.bank_input_fields').slideDown();
        } else { $('.bank_input_fields').hide(); }
    });

    $('#mfs_type').change(function(){
        var val = $(this).val();
        if(val) {
            $('#mfs_no_label').text(val + ' Trans ID');
            $('.mfs_input_fields').slideDown();
        } else { $('.mfs_input_fields').hide(); }
    });

    $('#paid_amount').on('input', function(){ calculateDue(); });

    function calculateDue() {
        var gTotal = parseFloat($('#grand_total_input').val()) || 0;
        var paid = parseFloat($('#paid_amount').val()) || 0;
        var due = gTotal - paid;
        if(due < 0) due = 0;
        $('#due_display').text(due.toLocaleString('en-IN', {minimumFractionDigits: 2}));
    }

    var typingTimer;                
    $('#voucher_no').on('keyup', function () {
        clearTimeout(typingTimer);
        var voucher = $(this).val();
        $('#voucher_error').hide();
        $('#btn_submit').prop('disabled', false);

        if (voucher) {
            typingTimer = setTimeout(function(){ 
                $.post(ajaxUrl, { ajax_request: 'check_voucher', voucher: voucher }, function(res) {
                    if(res.status === 'duplicate') {
                        $('#voucher_error').slideDown();
                        $('#btn_submit').prop('disabled', true);
                    }
                }, 'json');
            }, 500);
        }
    });

    $('#billForm').submit(function(e){
        var totalAmount = parseFloat($('#grand_total_input').val()) || 0;
        if(totalAmount <= 0) {
            e.preventDefault();
            alert("Error: Grand Total is Zero. Please add product quantities and prices.");
        }
    });

    if($.fn.DataTable && !$.fn.DataTable.isDataTable('#example1')) { 
        $('#example1').DataTable({ "order": [] }); 
    }
});
</script>
@endpush
