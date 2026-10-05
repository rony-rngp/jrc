@extends('layouts.master')

@section('title', 'JR || Corporation - Balance/Fund Transfer')
@section('page_title', 'Balance/Fund Transfer')

@section('breadcrumb')
<li>Accounts</li>
<li class="active">Fund Transfer</li>
@endsection

@push('styles')
<style>
    .transfer-box { background: #fdfdfd; border: 1px solid #e1e4e8; padding: 25px; border-radius: 8px; margin-bottom: 20px; }
    .transfer-icon { font-size: 30px; color: #3c8dbc; margin-top: 35px; text-align: center; }
    .amount-input { color: #00a65a !important; font-weight: bold; font-size: 24px !important; height: 50px !important; text-align: center; }
    .balance-badge { font-size: 13px; font-weight: bold; margin-top: 5px; display: inline-block; }
    .table-header { background-color: #3c8dbc; color: white; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Make a new fund transfer.</h3>
            </div>
            <form method="POST" action="{{ route('balance-transfers.store') }}" id="transferForm">
                @csrf
                <input type="hidden" name="main_head" value="Fund Transfer">
                
                <div class="box-body">
                    <div class="row transfer-box">
                        <div class="col-md-5">
                            <h4 class="text-danger" style="border-bottom: 2px solid #dd4b39; padding-bottom: 5px;"><b><i class="fa fa-upload"></i> From Account</b></h4>
                            <div class="form-group">
                                <label>Account Type <span class="text-danger">*</span></label>
                                <select class="form-control" name="from_category" id="from_category" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Bank">Bank</option>
                                    <option value="MFS">MFS</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Select Account <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="from_method_id" id="from_account" style="width:100%;" required>
                                    <option value="">-- First Select Type --</option>
                                </select>
                                <span id="from_balance_display" class="text-muted balance-badge"></span>
                                
                                <input type="hidden" id="max_transfer_limit" value="0">
                                <input type="hidden" name="from_account_name" id="from_account_name" value="">
                            </div>
                        </div>

                        <div class="col-md-2 transfer-icon">
                            <i class="fa fa-exchange"></i>
                        </div>

                        <div class="col-md-5">
                            <h4 class="text-success" style="border-bottom: 2px solid #00a65a; padding-bottom: 5px;"><b><i class="fa fa-download"></i> To Account</b></h4>
                            <div class="form-group">
                                <label>Account Type <span class="text-danger">*</span></label>
                                <select class="form-control" name="to_category" id="to_category" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Bank">Bank</option>
                                    <option value="MFS">MFS</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Select Account <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="to_method_id" id="to_account" style="width:100%;" required>
                                    <option value="">-- First Select Type --</option>
                                </select>
                                <span id="to_balance_display" class="text-muted balance-badge"></span>
                                
                                <input type="hidden" name="to_account_name" id="to_account_name" value="">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-md-offset-3">
                            <div class="form-group text-center">
                                <label>Transfer Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="amount" id="transfer_amount" class="form-control amount-input" placeholder="0.00" required>
                                <small id="amount_error" class="text-danger" style="display:none; font-weight:bold;">Insufficient Balance!</small>
                            </div>
                        </div>
                    </div>

                    <div class="row" style="margin-top: 15px;">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Voucher No <span class="text-danger">*</span></label>
                                <input type="text" name="voucher" class="form-control" placeholder="Voucher No" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Transfer Date <span class="text-danger">*</span></label>
                                <input type="date" name="t_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>TrxID / Cheque (Optional)</label>
                                <input type="text" name="ref_no" class="form-control" placeholder="TrxID/Cheque No">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Note / Remarks</label>
                                <input type="text" name="note" class="form-control" placeholder="Short description...">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer text-center">
                    <button type="submit" name="transfer_save" id="btn_transfer" class="btn btn-primary btn-flat btn-lg"><i class="fa fa-check-circle"></i> Confirm Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-list"></i> Recent Transfers</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped" id="example1">
                    <thead class="table-header">
                        <tr>
                            <th>Date</th>
                            <th>Voucher No</th>
                            <th>Amount</th>
                            <th>Details (Note)</th>
                            <th width="15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transfers as $t)
                            <tr>
                                <td>{{ date('d-m-Y', strtotime($t->t_date)) }}</td>
                                <td><span class="label label-info" style="font-size:14px;">{{ $t->check_no }}</span></td>
                                <td style="font-weight:bold; color:#00a65a; font-size:16px;">৳ {{ number_format((float)$t->amount, 2) }}</td>
                                <td>{{ $t->note }}</td>
                                <td>
                                    <a href="{{ route('balance-transfers.show', $t->check_no) }}" target="_blank" class="btn btn-primary btn-sm btn-flat" title="Print"><i class="fa fa-print"></i> Print</a>
                                    <form method="POST" action="{{ route('balance-transfers.destroy', $t->id) }}" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this transfer? Balances will be safely reverted.');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="voucher" value="{{ $t->check_no }}">
                                        <button type="submit" name="delete_transfer" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-trash"></i> Delete</button>
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

    $(document).on('wheel', 'input[type=number]', function (e) {
        $(this).blur();
    });

    var ajaxUrl = "{{ route('balance-transfers.index') }}";

    // 1. From Account category change
    $('#from_category').change(function(){
        var cat = $(this).val();
        $('#from_balance_display').text('');
        $('#max_transfer_limit').val(0);
        
        if(!cat) {
            $('#from_account').html('<option value="">-- Select Type First --</option>').trigger('change');
            return;
        }

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: { ajax_request: 'get_accounts', type: cat },
            dataType: 'json',
            success: function(res) {
                var opts = '<option value="">-- Select Account --</option>';
                $.each(res, function(i, item){ 
                    opts += '<option value="'+item.id+'" data-balance="'+item.amount+'">'+item.method_name+'</option>'; 
                });
                $('#from_account').html(opts).trigger('change');
            }
        });
    });

    // 2. From Account select -> updates balance and refreshes To Account
    $('#from_account').change(function(){
        var selectedFromID = $(this).val();
        var bal = $(this).find(':selected').data('balance') || 0;
        var name = $(this).find(':selected').text();
        
        $('#from_account_name').val(name); 
        $('#max_transfer_limit').val(bal);
        
        if(selectedFromID){
            $('#from_balance_display').text('Available Balance: ৳ ' + parseFloat(bal).toLocaleString('en-IN', {minimumFractionDigits: 2}));
            $('#from_balance_display').css('color', '#00a65a');
            
            if($('#to_category').val() != ""){
                $('#to_category').trigger('change');
            }
        }
        checkBalance();
    });

    // 3. To Account category load
    $('#to_category').change(function(){
        var cat = $(this).val();
        var from_acc_id = $('#from_account').val(); 
        $('#to_balance_display').text('');
        
        if(!cat) {
            $('#to_account').html('<option value="">-- Select Type First --</option>').trigger('change');
            return;
        }

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: { ajax_request: 'get_accounts', type: cat },
            dataType: 'json',
            success: function(res) {
                var opts = '<option value="">-- Select Account --</option>';
                $.each(res, function(i, item){ 
                    if(item.id != from_acc_id){
                        opts += '<option value="'+item.id+'" data-balance="'+item.amount+'">'+item.method_name+'</option>'; 
                    }
                });
                $('#to_account').html(opts).trigger('change');
            }
        });
    });

    // 4. To Account select
    $('#to_account').change(function(){
        var selectedToID = $(this).val();
        var name = $(this).find(':selected').text();
        var bal = $(this).find(':selected').data('balance') || 0;
        
        $('#to_account_name').val(name); 
        
        if(selectedToID){
            $('#to_balance_display').text('Current Balance: ৳ ' + parseFloat(bal).toLocaleString('en-IN', {minimumFractionDigits: 2}));
            $('#to_balance_display').css('color', '#3c8dbc');
        } else {
            $('#to_balance_display').text('');
        }
    });

    // 5. Balance check function
    function checkBalance() {
        var limit = parseFloat($('#max_transfer_limit').val()) || 0;
        var amount = parseFloat($('#transfer_amount').val()) || 0;
        
        if(amount > limit && limit > 0) {
            $('#amount_error').slideDown();
            $('#btn_transfer').prop('disabled', true);
            $('#transfer_amount').css('border-color', '#dd4b39');
        } else {
            $('#amount_error').slideUp();
            $('#btn_transfer').prop('disabled', false);
            $('#transfer_amount').css('border-color', '#d2d6de');
        }
    }

    $('#transfer_amount').on('input', function(){ checkBalance(); });

    if($.fn.DataTable && !$.fn.DataTable.isDataTable('#example1')) { 
        $('#example1').DataTable({ "order": [] }); 
    }
});
</script>
@endpush
