@extends('layouts.master')

@section('title', 'JR || Corporation - Receive Voucher')
@section('page_title', 'Receive Voucher')

@section('breadcrumb')
<li>Accounts</li>
<li class="active">Received</li>
@endsection

@push('styles')
<style>
    .dynamic-info-box { background: #f4f7f9; border: 1px solid #cfe0eb; padding: 20px; border-radius: 8px; margin-bottom: 20px; display: none; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05); }
    .label-info-custom { background: #3c8dbc !important; color: #fff; padding: 5px 12px; border-radius: 4px; margin-bottom: 15px; display: inline-block; font-weight: bold; }
    .hr-style { border-top: 1px solid #d2d6de; margin: 20px 0; }
    .bill-table th { background: #e0e7eb; }
    .loan-stats { background: #fff; padding: 15px; border: 1px solid #d2d6de; border-radius: 4px; margin-top: 15px; }
    .loan-stats p { margin: 0 0 8px; font-size: 14px; }
    .loan-stats .val { float: right; font-weight: bold; }
    .loan-stats .due-line { border-top: 2px dashed #00a65a; padding-top: 8px; margin-top: 8px; font-size: 16px; color: #00a65a; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="box box-success">
            <div class="box-header with-border"><h3 class="box-title">Create New Voucher</h3></div>
            <form class="form-horizontal" method="POST" action="{{ route('receive-vouchers.store') }}" id="receiveForm">
                @csrf
                <div class="box-body">
                    
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Receive Type <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <select class="form-control" name="receive_type" id="receive_type" required>
                                <option value="">-- Select Type --</option>
                                <option value="Client">Client Collection</option>
                                <option value="Loan">Loan Received</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="client_section" style="display:none;">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Client Name</label>
                            <div class="col-sm-9">
                                <select class="form-control select2" name="client_name" id="client_name" style="width:100%;">
                                    <option value="">-- Select Client --</option>
                                    @foreach($clients as $r)
                                        <option value="{{ $r->company_name }}">{{ $r->company_name }}</option>
                                    @endforeach
                                </select>
                                <div id="client_total_status" style="margin-top:8px; display:none; font-weight:bold;">
                                    Client Status: <span id="cli_overall_badge" class="label"></span> <span id="cli_overall_amt"></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" id="payment_mode_div" style="display:none;">
                            <label class="col-sm-3 control-label">Payment Mode</label>
                            <div class="col-sm-9">
                                <label class="radio-inline"><input type="radio" name="client_pay_mode" value="Project" checked> <b>Project Specific</b></label>
                                <label class="radio-inline"><input type="radio" name="client_pay_mode" value="General"> <b>General / Advance</b></label>
                            </div>
                        </div>

                        <div class="form-group" id="project_div" style="display:none;">
                            <label class="col-sm-3 control-label">Project Name</label>
                            <div class="col-sm-9">
                                <select class="form-control select2" name="project_id" id="project_name" style="width:100%;"></select>
                            </div>
                        </div>

                        <div id="bill_info_box" class="dynamic-info-box">
                            <span class="label-info-custom"><i class="fa fa-file-invoice-dollar"></i> Pending Bills</span>
                            <div class="table-responsive">
                                <table class="table table-bordered bill-table" id="pending_bills_table">
                                    <thead><tr><th>Date</th><th>Bill Details/Note</th><th>Total Billed</th><th>Due Amount</th><th>Action</th></tr></thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <div class="form-group" style="margin-top: 15px;">
                                <label class="col-sm-3 control-label text-blue">Select Bill to Pay</label>
                                <div class="col-sm-9">
                                    <select class="form-control" name="bill_id" id="bill_id">
                                        <option value="">-- Advance / Partial for whole project --</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="loan_section" style="display:none;" class="dynamic-info-box">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Loan From</label>
                            <div class="col-sm-9">
                                <select class="form-control select2" name="loan_head_id" id="loan_head_id" style="width:100%;">
                                    <option value="">-- Select Person --</option>
                                    @foreach($loanHeads as $r)
                                        <option value="{{ $r->id }}">{{ $r->head_name }}</option>
                                    @endforeach
                                </select>
                                <div id="realtime_loan_info" class="loan-stats" style="display:none;">
                                    <p><i class="fa fa-arrow-circle-down text-success"></i> Total Loan Taken: <span class="val" id="lbl_total_loan">0.00</span></p>
                                    <p><i class="fa fa-arrow-circle-up text-primary"></i> Already Paid Back: <span class="val" id="lbl_loan_paid">0.00</span></p>
                                    <p class="due-line"><b>Current Loan Balance: <span class="val" id="lbl_loan_balance">0.00</span></b></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Amount <span class="text-danger">*</span></label>
                        <div class="col-sm-9"><input type="number" step="any" name="amount" id="pay_amount" class="form-control input-lg" style="color: green; font-weight: bold;" placeholder="0.00" required></div>
                    </div>

                    <div class="hr-style"></div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Deposit Category <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <select class="form-control" name="deposit_category" id="deposit_category" required>
                                <option value="">-- Select Category --</option>
                                <option value="Cash">Cash</option>
                                <option value="Bank">Bank</option>
                                <option value="MFS">Mobile Financial Services (MFS)</option>
                            </select>
                        </div>
                    </div>

                    <div id="method_container" style="display:none;">
                        <div class="form-group">
                            <label class="col-sm-3 control-label" id="method_label">Deposit To Account <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <select class="form-control select2" name="method_id" id="method_id" style="width:100%;"></select>
                            </div>
                        </div>
                    </div>

                    <div id="bank_details_box" class="dynamic-info-box" style="display:none;">
                        <span class="label-info-custom"><i class="fa fa-bank"></i> Sender's Bank & Transaction Info</span>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Sender Bank Name</label>
                            <div class="col-sm-9">
                                <select class="form-control select2" name="sender_bank_name" id="sender_bank_name" style="width:100%;">
                                    <option value="">-- Select / Type Bank Name --</option>
                                    <option value="AB Bank PLC"> AB Bank PLC</option>
                                    <option value="Agrani Bank PLC">Agrani Bank PLC</option>
                                    <option value="Al-Arafah Islami Bank PLC">Al-Arafah Islami Bank PLC</option>
                                    <option value="Bank Asia PLC">Bank Asia PLC</option>
                                    <option value="BRAC Bank PLC">BRAC Bank PLC</option>
                                    <option value="City Bank PLC">City Bank PLC</option>
                                    <option value="Dutch-Bangla Bank PLC">Dutch-Bangla Bank PLC</option>
                                    <option value="Islami Bank Bangladesh PLC">Islami Bank Bangladesh PLC</option>
                                    <option value="Mutual Trust Bank PLC">Mutual Trust Bank PLC</option>
                                    <option value="Other Bank">Other Bank</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Transfer Type</label>
                            <div class="col-sm-9">
                                <select class="form-control" name="bank_trans_type" id="bank_trans_type">
                                    <option value="">-- Select --</option>
                                    <option value="Check">Check</option>
                                    <option value="Online Transfer">Online Transfer</option>
                                </select>
                            </div>
                        </div>

                        <div id="bank_input_fields" style="display:none;">
                            <div class="form-group">
                                <label class="col-sm-3 control-label" id="bank_no_label">Number</label>
                                <div class="col-sm-4"><input type="text" name="bank_ref_no" class="form-control" placeholder="No."></div>
                                <div class="col-sm-5"><input type="date" name="bank_ref_date" class="form-control" value="{{ date('Y-m-d') }}"></div>
                            </div>
                        </div>
                    </div>

                    <div id="mfs_details_box" class="dynamic-info-box" style="display:none;">
                        <span class="label-info-custom"><i class="fa fa-mobile"></i> MFS Transaction Details</span>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">MFS Type</label>
                            <div class="col-sm-9">
                                <select class="form-control" name="mfs_type" id="mfs_type">
                                    <option value="">-- Select --</option>
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
                        <div id="mfs_input_fields" style="display:none;">
                            <div class="form-group">
                                <label class="col-sm-3 control-label" id="mfs_no_label">Transaction ID</label>
                                <div class="col-sm-4"><input type="text" name="mfs_ref_no" class="form-control" placeholder="TrxID/No."></div>
                                <div class="col-sm-5"><input type="date" name="mfs_ref_date" class="form-control" value="{{ date('Y-m-d') }}"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Voucher No <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="text" name="voucher" id="voucher_input" class="form-control" placeholder="Voucher No" required>
                            <small id="voucher_error" class="text-danger" style="display:none; font-weight:bold;"><i class="fa fa-times-circle"></i> This voucher number already exists!</small>
                        </div>
                    </div>

                    <div class="form-group"><label class="col-sm-3 control-label">Voucher Date</label><div class="col-sm-9"><input type="date" name="t_date" class="form-control" value="{{ date('Y-m-d') }}" required></div></div>
                    <div class="form-group"><label class="col-sm-3 control-label">Note</label><div class="col-sm-9"><textarea name="note" class="form-control" rows="2" placeholder="Note..."></textarea></div></div>
                </div>
                <div class="box-footer text-center"><button type="submit" id="btn_save" name="receive_save" class="btn btn-success btn-flat btn-lg">Save Voucher</button></div>
            </form>
        </div>
    </div>
</div>

<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-list"></i> Recent Transactions</h3>
            </div>
            <div class="box-body table-responsive">
                <table id="transactionTable" class="table table-bordered table-striped">
                    <thead style="background-color: #dff0d8;">
                        <tr>
                            <th>Date</th>
                            <th>Voucher No</th>
                            <th>Source / Head</th>
                            <th>Account</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTransactions as $row)
                            @php
                                $amt = (float)($row->amount ?? 0);
                                $source_badge = '';
                                if($row->type == 'Client') {
                                    $source_badge = '<span class="label label-primary"><i class="fa fa-user"></i> '.$row->source.'</span>';
                                } else {
                                    if($row->source == 'Fund Transfer'){
                                        $bg_color = 'bg-purple';
                                        $icon = 'fa-exchange';
                                    } else {
                                        $bg_color = (strpos(strtolower($row->source), 'loan') !== false) ? 'label-warning' : 'label-info';
                                        $icon = 'fa-list-alt';
                                    }
                                    $source_badge = '<span class="label '.$bg_color.'"><i class="fa '.$icon.'"></i> '.$row->source.'</span>';
                                }

                                $approval_status = $row->approval_status ?? 'Approved';
                                if($approval_status == 'Pending'){
                                    $status_badge_html = '<span class="label label-warning"><i class="fa fa-clock-o"></i> Pending</span>';
                                } else if($approval_status == 'Rejected'){
                                    $status_badge_html = '<span class="label label-danger"><i class="fa fa-times"></i> Rejected</span>';
                                } else {
                                    $status_badge_html = '<span class="label label-success"><i class="fa fa-check"></i> Approved</span>';
                                }
                            @endphp
                            <tr>
                                <td>{{ date('d-M-Y', strtotime($row->date)) }}</td>
                                <td><b>{{ $row->voucher }}</b></td>
                                <td>{!! $source_badge !!}</td>
                                <td>{{ $row->account }}</td>
                                <td class='text-bold text-green'>{{ number_format($amt, 2) }}</td>
                                <td>{!! $status_badge_html !!}</td>
                                <td>
                                    <a href="{{ route('receive-vouchers.show', $row->id) }}?type={{ $row->type }}" class="btn btn-info btn-xs btn-flat" title="View Details"><i class="fa fa-eye"></i> View</a>
                                    <a href="{{ route('receive-vouchers.show', $row->id) }}?type={{ $row->type }}&print=1" target="_blank" class="btn btn-primary btn-xs btn-flat" title="Print"><i class="fa fa-print"></i> Print</a>
                                    <a href="{{ route('receive-vouchers.edit', $row->id) }}?type={{ $row->type }}" class="btn btn-warning btn-xs btn-flat" title="Edit"><i class="fa fa-edit"></i> Edit</a>
                                    <form action="{{ route('receive-vouchers.destroy', $row->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to reverse and delete this transaction?');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="type" value="{{ $row->type }}">
                                        <button type="submit" class="btn btn-danger btn-xs btn-flat" title="Delete"><i class="fa fa-trash"></i> Delete</button>
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

    var ajaxUrl = "{{ route('receive-vouchers.index') }}";

    // Voucher Duplicate Check 
    $('#voucher_input').on('keyup blur', function(){
        var voucher = $(this).val();
        if(voucher.length > 0){
            $.post(ajaxUrl, {ajax_request: 'check_voucher', voucher: voucher}, function(res){
                if(res.status === 'duplicate'){ $('#voucher_error').show(); $('#btn_save').prop('disabled', true); }
                else { $('#voucher_error').hide(); $('#btn_save').prop('disabled', false); }
            }, 'json');
        }
    });

    // Receive Type Logic
    $('#receive_type').change(function(){
        var val = $(this).val();
        $('#client_section, #loan_section').hide();
        if(val === 'Client') $('#client_section').slideDown();
        else if(val === 'Loan') $('#loan_section').slideDown();
    });

    // Client Overall Dues
    $('#client_name').change(function(){
        var client = $(this).val();
        $('#project_name').html('<option value="">-- Select Project --</option>');
        $('#payment_mode_div, #project_div, #bill_info_box, #client_total_status').hide();
        if(!client) return;
        
        $.post(ajaxUrl, {ajax_request: 'get_client_total_dues', client_name: client}, function(res){
            $('#cli_overall_badge').text(res.status).removeClass('label-danger label-success label-default').addClass('label-' + res.color);
            
            var infoText = ' Total: ' + res.amount;
            if(parseFloat(res.opening) != 0){
                infoText += ' (Opening: ' + res.opening + ')';
            }
            
            $('#cli_overall_amt').text(infoText);
            $('#client_total_status').slideDown();
        }, 'json');

        $.post(ajaxUrl, {ajax_request: 'get_projects', client: client}, function(res){
            if(res.length > 0){
                $.each(res, function(i, item){ $('#project_name').append('<option value="'+item.id+'">'+item.project_name+'</option>'); });
                $('#payment_mode_div').slideDown();
                if($('input[name="client_pay_mode"]:checked').val() == 'Project') $('#project_div').slideDown();
            }
        }, 'json');
    });

    // Loan Info logic
    $('#loan_head_id').change(function(){
        var head_id = $(this).val();
        if(head_id){
            $.post(ajaxUrl, {ajax_request: 'get_loan_info', head_id: head_id}, function(res){
                $('#lbl_total_loan').text(res.total_received);
                $('#lbl_loan_paid').text(res.total_paid);
                $('#lbl_loan_balance').text(res.due_balance);
                $('#realtime_loan_info').slideDown();
            }, 'json');
        } else { $('#realtime_loan_info').hide(); }
    });

    // Payment Mode logic (Project vs General)
    $('input[name="client_pay_mode"]').change(function(){
        if($(this).val() == 'Project') $('#project_div').slideDown();
        else { $('#project_div, #bill_info_box').hide(); }
    });

    // Project Name -> Fetch Bills
    $('#project_name').change(function(){
        var projName = $("#project_name option:selected").text();
        $('#pending_bills_table tbody').empty();
        $('#bill_id').html('<option value="">-- Advance / Partial for whole project --</option>');
        $('#bill_info_box').hide();

        if(!$(this).val()) return;

        $.post(ajaxUrl, {ajax_request: 'get_project_bills', project_name: projName}, function(res){
            if(res.length > 0){
                var rows = '';
                $.each(res, function(i, item){
                    var amt = parseFloat(item.due_amount).toFixed(2);
                    rows += '<tr>' +
                                '<td>'+item.date+'</td>' +
                                '<td>'+item.note+'</td>' +
                                '<td>৳ '+item.bill_amount+'</td>' +
                                '<td class="text-danger text-bold">৳ '+amt+'</td>' +
                                '<td><button type="button" class="btn btn-xs btn-primary fill-amt-btn" data-id="'+item.id+'" data-amt="'+item.due_amount+'">Pay This</button></td>' +
                            '</tr>';
                    $('#bill_id').append('<option value="'+item.id+'">Bill: '+item.note+' (Due: ৳'+amt+')</option>');
                });
                $('#pending_bills_table tbody').html(rows);
            } 
            else {
                $('#pending_bills_table tbody').html('<tr><td colspan="5" class="text-center text-success" style="padding: 20px;"><b>All bills are settled or advance exists.</b></td></tr>');
            }
            $('#bill_info_box').slideDown();
        }, 'json');
    });

    $(document).on('click', '.fill-amt-btn', function(){
        $('#pay_amount').val($(this).data('amt'));
        $('#bill_id').val($(this).data('id')).trigger('change');
    });

    // Deposit, Bank & MFS Logic
    $('#deposit_category').change(function(){
        var cat = $(this).val();
        $('#method_id').html('<option value="">-- Select Account --</option>');
        $('#method_container, #bank_details_box, #mfs_details_box').hide();
        if(!cat) return;
        
        $.post(ajaxUrl, {ajax_request: 'get_payment_methods', type: cat}, function(res){
            if(res.length > 0){
                $.each(res, function(i, item){
                    $('#method_id').append('<option value="'+item.id+'">'+item.method_name+' (Balance: ৳'+parseFloat(item.amount).toFixed(2)+')</option>');
                });
                $('#method_container').slideDown();
                if(cat === 'Bank') $('#bank_details_box').slideDown();
                if(cat === 'MFS') $('#mfs_details_box').slideDown();
            }
        }, 'json');
    });

    $('#bank_trans_type').change(function(){
        var type = $(this).val();
        if(type){
            $('#bank_no_label').text((type === 'Check') ? 'Check Number' : 'Trans. No/ID');
            $('#bank_input_fields').slideDown();
        } else { $('#bank_input_fields').hide(); }
    });

    $('#mfs_type').change(function(){
        var mfs = $(this).val();
        if(mfs){
            $('#mfs_no_label').text(mfs + ' Trans. No/ID');
            $('#mfs_input_fields').slideDown();
        } else { $('#mfs_input_fields').hide(); }
    });

    if($.fn.DataTable && !$.fn.DataTable.isDataTable('#transactionTable')) {
        $('#transactionTable').DataTable({
            "order": [] 
        });
    }
});
</script>
@endpush
