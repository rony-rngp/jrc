@extends('layouts.master')

@section('title', 'JR || Corporation - Edit & Approve Transaction')
@section('page_title', 'Edit & Approve Transaction')

@section('breadcrumb')
<li><a href="{{ route('payment-vouchers.index') }}"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active">Edit Transaction</li>
@endsection

@push('styles')
<style>
    .dynamic-info-box { background: #fffaf0; border: 1px solid #f9cd9f; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05); }
    .loan-info-box { background: #f4f8fa; border: 1px solid #bce8f1; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
    .project-info-box { background: #eaf3f9; border: 1px solid #3c8dbc; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
    .supplier-info-box { background: #fdf2f5; border: 1px solid #f5c6cb; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
    
    .label-info-custom { background: #e08e0b !important; color: #fff; padding: 5px 12px; border-radius: 4px; margin-bottom: 15px; display: inline-block; font-weight: bold; }
    .label-loan-custom { background: #3c8dbc !important; color: #fff; padding: 5px 12px; border-radius: 4px; margin-bottom: 15px; display: inline-block; font-weight: bold; }
    .label-project-custom { background: #00a65a !important; color: #fff; padding: 5px 12px; border-radius: 4px; margin-bottom: 15px; display: inline-block; font-weight: bold; }
    .label-supplier-custom { background: #dd4b39 !important; color: #fff; padding: 5px 12px; border-radius: 4px; margin-bottom: 15px; display: inline-block; font-weight: bold; }
    
    .hr-style { border-top: 1px solid #d2d6de; margin: 20px 0; }
    .readonly-field { background-color: #f9f9f9 !important; border: 1px dashed #d2d6de !important; cursor: not-allowed; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Modifying Transaction Details</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('payment-vouchers.index') }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back to List</a>
                </div>
            </div>
            
            <form class="form-horizontal" method="POST" action="{{ route('payment-vouchers.update', $edit_id) }}" id="editForm">
                @csrf
                @method('PUT')
                <div class="box-body">
                    
                    @if(($data->approval_status ?? '') == 'Pending')
                    <div class="callout callout-danger" style="margin-bottom: 20px;">
                        <p><i class="fa fa-exclamation-triangle"></i> <b>Notice:</b> This transaction is currently <b>Pending</b>. Saving this edit will automatically <b>Approve</b> it and adjust the live account balances.</p>
                    </div>
                    @else
                    <div class="callout callout-success" style="margin-bottom: 20px;">
                        <p><i class="fa fa-info-circle"></i> <b>System Note:</b> This transaction is already Approved. You can update the Amount, Date, Voucher No, and Note. The system will auto-adjust the accounting balances.</p>
                    </div>
                    @endif

                    <input type="hidden" name="edit_id" value="{{ $edit_id }}">
                    <input type="hidden" name="edit_type" value="{{ $edit_type }}">
                    <input type="hidden" name="old_amount" value="{{ $data->amount ?? 0 }}">
                    <input type="hidden" name="old_voucher" id="old_voucher_input" value="{{ $data->voucher_no ?? ($data->check_no ?? '') }}">

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Payment Type</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control readonly-field" value="{{ $edit_type }} Payment" readonly>
                        </div>
                    </div>

                    @if($edit_type == 'Supplier')
                    <div class="supplier-info-box">
                        <span class="label-supplier-custom"><i class="fa fa-truck"></i> Supplier Details</span>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Supplier Name</label>
                            <div class="col-sm-9"><input type="text" class="form-control readonly-field" value="{{ $data->party_name ?? '' }}" readonly></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Project</label>
                            <div class="col-sm-9"><input type="text" class="form-control readonly-field" value="{{ !empty($data->project_name) ? $data->project_name : 'General / Advance' }}" readonly></div>
                        </div>
                        @if(!empty($data->bill_voucher))
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Specific Bill</label>
                            <div class="col-sm-9"><input type="text" class="form-control readonly-field text-danger" value="{{ $data->bill_voucher }}" readonly></div>
                        </div>
                        @endif
                    </div>

                    @elseif($edit_type == 'Project')
                    <div class="project-info-box">
                        <span class="label-project-custom"><i class="fa fa-building"></i> Project Expense Details</span>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Project Name</label>
                            <div class="col-sm-9"><input type="text" class="form-control readonly-field" value="{{ $data->project_name ?? '' }}" readonly></div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Expense Head</label>
                            <div class="col-sm-9"><input type="text" class="form-control readonly-field" value="{{ $data->expense_head ?? '' }}" readonly></div>
                        </div>
                    </div>

                    @elseif($edit_type == 'Loan' || $edit_type == 'General')
                    <div class="{{ $edit_type == 'Loan' ? 'loan-info-box' : 'dynamic-info-box' }}">
                        <span class="{{ $edit_type == 'Loan' ? 'label-loan-custom' : 'label-info-custom' }}">
                            <i class="fa {{ $edit_type == 'Loan' ? 'fa-handshake-o' : 'fa-list-alt' }}"></i> 
                            {{ $edit_type == 'Loan' ? 'Loan Details' : 'Account Details' }}
                        </span>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Account Head</label>
                            <div class="col-sm-9"><input type="text" class="form-control readonly-field" value="{{ $data->head_name ?? '' }}" readonly></div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Paid From Method</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control readonly-field" value="{{ ($data->method_name ?? '') . ' (' . ($data->account_type ?? '') . ')' }}" readonly>
                        </div>
                    </div>
                    
                    <div class="hr-style"></div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Voucher Date <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="date" name="new_date" class="form-control input-lg" value="{{ date('Y-m-d', strtotime($data->t_date ?? date('Y-m-d'))) }}" required style="font-weight: bold; color: #3c8dbc; border-color: #3c8dbc;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Voucher No <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control input-lg" name="new_voucher" id="voucher_input" value="{{ $data->voucher_no ?? ($data->check_no ?? '') }}" required style="font-weight: bold; color: #f39c12; border-color: #f39c12;">
                            
                            <small id="voucher_error" class="text-danger" style="display:none; font-weight:bold; margin-top:5px;"><i class="fa fa-times-circle"></i> This voucher number already exists!</small>
                            <small id="voucher_success" class="text-success" style="display:none; font-weight:bold; margin-top:5px;"><i class="fa fa-check-circle"></i> Voucher number is available.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Amount <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="number" step="any" class="form-control input-lg" name="new_amount" value="{{ $data->amount ?? 0 }}" required style="font-weight: bold; color: #00a65a; border-color: #00a65a;">
                            <small class="text-muted">Previous Amount was: ৳ {{ number_format((float)($data->amount ?? 0), 2) }}</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Note / Particulars</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="new_note" rows="3" style="border-color: #00a65a;">{{ $data->note ?? '' }}</textarea>
                        </div>
                    </div>

                </div>
                
                <div class="box-footer text-center">
                    <a href="{{ route('payment-vouchers.index') }}" class="btn btn-default btn-lg pull-left"><i class="fa fa-arrow-left"></i> Cancel</a>
                    <button type="submit" id="btn_update" name="update_transaction" class="btn btn-success btn-lg"><i class="fa fa-check-circle"></i> Save & Approve Transaction</button>
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

    var ajaxUrl = "{{ route('payment-vouchers.index') }}";
    var old_voucher = $('#old_voucher_input').val(); 

    $('#voucher_input').on('keyup blur', function(){
        var new_voucher = $(this).val().trim();

        if(new_voucher === old_voucher) {
            $('#voucher_error').hide(); 
            $('#voucher_success').show().html('<i class="fa fa-check-circle"></i> Retaining current voucher number.');
            $('#btn_update').prop('disabled', false);
        } 
        else if(new_voucher.length > 0) {
            $.post(ajaxUrl, {ajax_request: 'check_voucher', voucher: new_voucher}, function(res){
                if(res.status === 'duplicate'){
                    $('#voucher_error').show(); 
                    $('#voucher_success').hide();
                    $('#btn_update').prop('disabled', true); 
                } else {
                    $('#voucher_error').hide(); 
                    $('#voucher_success').show().html('<i class="fa fa-check-circle"></i> Voucher number is available.');
                    $('#btn_update').prop('disabled', false);
                }
            }, 'json');
        } 
        else {
            $('#voucher_error, #voucher_success').hide();
            $('#btn_update').prop('disabled', false);
        }
    });
});
</script>
@endpush
