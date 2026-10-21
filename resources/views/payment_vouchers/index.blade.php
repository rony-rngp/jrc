@extends('layouts.master')

@section('title', 'JR || Corporation - Payment Voucher')
@section('page_title', 'Payment Voucher')

@section('breadcrumb')
<li>Accounts</li>
<li class="active">Payment</li>
@endsection

@push('styles')
<style>
    .dynamic-info-box { background: #fffaf0; border: 1px solid #f9cd9f; padding: 20px; border-radius: 8px; margin-bottom: 20px; display: none; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05); }
    .loan-info-box { background: #f4f8fa; border: 1px solid #bce8f1; padding: 20px; border-radius: 8px; margin-bottom: 20px; display: none; }
    .project-info-box { background: #eaf3f9; border: 1px solid #3c8dbc; padding: 20px; border-radius: 8px; margin-bottom: 20px; display: none; }
    .supplier-info-box { background: #fdf2f5; border: 1px solid #f5c6cb; padding: 20px; border-radius: 8px; margin-bottom: 20px; display: none; }
    
    .label-info-custom { background: #e08e0b !important; color: #fff; padding: 5px 12px; border-radius: 4px; margin-bottom: 15px; display: inline-block; font-weight: bold; }
    .label-loan-custom { background: #3c8dbc !important; color: #fff; padding: 5px 12px; border-radius: 4px; margin-bottom: 15px; display: inline-block; font-weight: bold; }
    .label-project-custom { background: #00a65a !important; color: #fff; padding: 5px 12px; border-radius: 4px; margin-bottom: 15px; display: inline-block; font-weight: bold; }
    .label-supplier-custom { background: #dd4b39 !important; color: #fff; padding: 5px 12px; border-radius: 4px; margin-bottom: 15px; display: inline-block; font-weight: bold; }
    
    .hr-style { border-top: 1px solid #d2d6de; margin: 20px 0; }
    .amount-input { color: #d9534f !important; font-weight: bold; }
    .bill-table th { background: #f1d7da; }
    
    .loan-stats { background: #fff; padding: 15px; border: 1px solid #d2d6de; border-radius: 4px; margin-top: 15px; }
    .loan-stats p { margin: 0 0 8px; font-size: 14px; }
    .loan-stats .val { float: right; font-weight: bold; }
    .loan-stats .due-line { border-top: 2px dashed #d9534f; padding-top: 8px; margin-top: 8px; font-size: 16px; color: #d9534f; }

    .proj-summary-panel { background: #e8f5e9; padding: 15px; border: 1px solid #c8e6c9; border-radius: 5px; margin-bottom: 15px; }
    .proj-summary-panel h4 { margin-top: 0; font-size: 14px; color: #2e7d32; font-weight: bold; border-bottom: 1px solid #c8e6c9; padding-bottom: 8px; margin-bottom: 12px; }
    .proj-val-box { text-align: center; border-right: 1px solid #c8e6c9; }
    .proj-val-box:last-child { border-right: none; }
    .proj-val-title { font-size: 12px; color: #555; text-transform: uppercase; font-weight: bold; }
    .proj-val-amount { font-size: 18px; font-weight: bold; margin-top: 5px; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="box box-danger">
            <div class="box-header with-border"><h3 class="box-title">Enter a new expense voucher.</h3></div>
            <form id="payment_form" class="form-horizontal" method="POST" action="{{ route('payment-vouchers.store') }}">
                @csrf
                <div class="box-body">
                    
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Payment Type <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <select class="form-control" name="payment_type" id="payment_type" required>
                                <option value="">-- Select Type --</option>
                                <option value="Supplier">Supplier Payment</option>
                                <option value="Project">Project Expense</option>
                                <option value="Loan">Loan Payment</option>
                                @foreach($mainPaymentHeads as $r_h)
                                    <option value="Others_{{ $r_h->id }}">{{ $r_h->head_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="supplier_section" class="supplier-info-box">
                        <span class="label-supplier-custom"><i class="fa fa-truck"></i> Supplier Payment & Adjustment</span>
                        
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Supplier Name <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <select class="form-control select2" name="supplier_id" id="supplier_id" style="width:100%;">
                                    <option value="">-- Select Supplier --</option>
                                    @foreach($suppliers as $r)
                                        <option value="{{ $r->id }}">{{ $r->party_name }}</option>
                                    @endforeach
                                </select>
                                <div id="supplier_total_status" style="margin-top:8px; display:none; font-weight:bold;">
                                    Overall Status: <span id="sup_overall_badge" class="label"></span> <span id="sup_overall_amt"></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" id="supplier_pay_mode_div" style="display:none;">
                            <label class="col-sm-3 control-label">Payment Mode</label>
                            <div class="col-sm-9">
                                <label class="radio-inline"><input type="radio" name="supplier_pay_mode" value="Project" checked> <b>Project Specific / Bill</b></label>
                            </div>
                        </div>

                        <div class="form-group" id="supplier_project_div" style="display:none;">
                            <label class="col-sm-3 control-label">Project Name</label>
                            <div class="col-sm-9">
                                <select class="form-control select2" name="supplier_project_id" id="supplier_project_id" style="width:100%;">
                                    <option value="">-- Select Project --</option>
                                </select>
                                
                                <div id="supplier_project_warning" style="display:none; margin-top:8px; padding:8px 12px; border-radius:4px; font-size:13px; transition: 0.3s;">
                                    <span class="warning-icon"></span> <span class="warning-text"></span>
                                </div>
                            </div>
                        </div>

                        <div id="supplier_bill_info_box" class="dynamic-info-box" style="display:none; background: #fff;">
                            <div class="proj-summary-panel" id="proj_summary_div" style="display:none;">
                                <h4><i class="fa fa-calculator"></i> Project Payment Status</h4>
                                <div class="row">
                                    <div class="col-sm-4 proj-val-box">
                                        <div class="proj-val-title">Total Billed</div>
                                        <div class="proj-val-amount text-primary"> <span id="ps_billed">0.00</span></div>
                                    </div>
                                    <div class="col-sm-4 proj-val-box">
                                        <div class="proj-val-title">Total Paid / Adv</div>
                                        <div class="proj-val-amount text-info"> <span id="ps_paid">0.00</span></div>
                                    </div>
                                    <div class="col-sm-4 proj-val-box">
                                        <div class="proj-val-title"><span id="ps_status_text">Net Balance</span></div>
                                        <div class="proj-val-amount" id="ps_net_amt" style="color:#d9534f"> <span>0.00</span></div>
                                    </div>
                                </div>
                            </div>
                            <span class="label-info-custom" style="background:#d9534f !important;"><i class="fa fa-file-invoice-dollar"></i> Individual Pending Bills</span>
                            <div class="table-responsive">
                                <table class="table table-bordered bill-table" id="supplier_pending_bills_table">
                                    <thead><tr><th>Bill Date</th><th>Voucher</th><th>Total Billed</th><th>Due Amount</th><th>Action</th></tr></thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <div class="form-group" style="margin-top: 15px;">
                                <label class="col-sm-3 control-label text-red">Select Bill to Pay</label>
                                <div class="col-sm-9">
                                    <select class="form-control" name="supplier_bill_id" id="supplier_bill_id">
                                        <option value="">-- Advance / Partial for whole project --</option>
                                    </select>
                                    <small class="text-muted"><i class="fa fa-info-circle"></i> If paying in part, leave it as <b>"-- Advance / Partial for whole project --"</b> and enter amount below. The system will auto-adjust older bills first.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="project_section" class="project-info-box">
                        <span class="label-project-custom"><i class="fa fa-building"></i> Project Direct Expense</span>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Select Project <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <select class="form-control select2" name="expense_project_id" id="expense_project_id" style="width:100%;">
                                    <option value="">-- Select Project --</option>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}">{{ $p->project_name }}</option>
                                    @endforeach
                                </select>
                                
                                <div id="expense_project_warning" style="display:none; margin-top:8px; padding:8px 12px; border-radius:4px; font-size:13px; transition: 0.3s;">
                                    <span class="warning-icon"></span> <span class="warning-text"></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Expense Head <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="project_expense_reason" id="project_expense_reason" class="form-control" list="expense_reasons_list" placeholder="e.g. TA/DA, Materials" autocomplete="off">
                                <datalist id="expense_reasons_list"></datalist>
                            </div>
                        </div>
                    </div>

                    <div id="loan_section" class="loan-info-box">
                        <span class="label-loan-custom"><i class="fa fa-handshake-o"></i> Loan Repayment & Adjustment</span>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Payment Action</label>
                            <div class="col-sm-9">
                                <select class="form-control" name="loan_action" id="loan_action">
                                    <option value="">-- Select Action --</option>
                                    <option value="Adjustment">Principal Repayment</option>
                                    <option value="Interest">Pay Interest</option>
                                </select>
                                <div id="interest_warning" style="display:none; margin-top:8px; padding:8px; background:#fdebd0; border-left:3px solid #d35400; color:#d35400; font-size:12px; font-weight:bold; border-radius:3px;">
                                    <i class="fa fa-info-circle"></i> Accounting note: Interest payment does not reduce principal, it is treated directly as an Expense.
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Loan From</label>
                            <div class="col-sm-9">
                                <select class="form-control select2" name="loan_head_id" id="loan_head_id" style="width:100%;">
                                    <option value="">-- Select Loan Person --</option>
                                    @foreach($loanHeads as $r)
                                        <option value="{{ $r->id }}">{{ $r->head_name }}</option>
                                    @endforeach
                                </select>
                                
                                <div id="realtime_loan_info" class="loan-stats" style="display:none;">
                                    <p><i class="fa fa-arrow-circle-down text-success"></i> Total Loan Received: <span class="val" id="lbl_total_loan">0.00</span></p>
                                    <p><i class="fa fa-arrow-circle-up text-primary"></i> Principal Repaid: <span class="val" id="lbl_principal_paid">0.00</span></p>
                                    <p><i class="fa fa-arrow-circle-up text-warning"></i> Interest Paid: <span class="val" id="lbl_interest_paid">0.00</span></p>
                                    <p class="due-line"><b>Remaining Principal to Pay: <span class="val" id="lbl_due_principal">0.00</span></b></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="heads_section" style="display:none;">
                        <input type="hidden" name="head_id" id="final_head_id" value="">
                        <div id="dynamic_heads_container"></div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Amount <span class="text-danger">*</span></label>
                        <div class="col-sm-9"><input type="number" step="any" name="amount" id="pay_amount" class="form-control input-lg amount-input" placeholder="0.00" required></div>
                    </div>

                    <div class="hr-style"></div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Payment Category <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <select class="form-control" name="deposit_category" id="deposit_category" required>
                                <option value="">-- Select Category --</option>
                                <option value="Cash">Cash</option>
                                <option value="Bank">Bank </option>
                                <option value="MFS">Mobile Financial Services (MFS)</option>
                            </select>
                        </div>
                    </div>

                    <div id="method_container" style="display:none;">
                        <div class="form-group">
                            <label class="col-sm-3 control-label" id="method_label">Paid From Account <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <select class="form-control select2" name="method_id" id="method_id" style="width:100%;"></select>
                            </div>
                        </div>
                    </div>

                    <div id="bank_details_box" class="dynamic-info-box">
                        <span class="label-info-custom"><i class="fa fa-bank"></i> Bank Transaction Info</span>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Transfer Type</label>
                            <div class="col-sm-9">
                                <select class="form-control" name="bank_trans_type" id="bank_trans_type">
                                    <option value="">Select</option>
                                    <option value="Check">Check </option>
                                    <option value="Online Transfer">Online Transfer </option>
                                </select>
                            </div>
                        </div>
                        <div id="bank_input_fields" style="display:none;">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Number & Date</label>
                                <div class="col-sm-4"><input type="text" name="bank_ref_no" class="form-control" placeholder="Check/Ref No."></div>
                                <div class="col-sm-5"><input type="date" name="bank_ref_date" class="form-control" value="{{ date('Y-m-d') }}"></div>
                            </div>
                        </div>
                    </div>

                    <div id="mfs_details_box" class="dynamic-info-box">
                        <span class="label-info-custom"><i class="fa fa-mobile"></i> MFS Transaction Details</span>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">MFS Type</label>
                            <div class="col-sm-9">
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
                        <div id="mfs_input_fields" style="display:none;">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Trx ID & Date</label>
                                <div class="col-sm-4"><input type="text" name="mfs_ref_no" class="form-control" placeholder="TrxID"></div>
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
                <div class="box-footer text-center"><button type="submit" id="btn_save" class="btn btn-danger btn-flat btn-lg">Save Voucher</button></div>
            </form>
        </div>
    </div>
</div>

<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-list"></i> Recent Payment Transactions</h3>
            </div>
            <div class="box-body table-responsive">
                <table id="transactionTable" class="table table-bordered table-striped">
                    <thead style="background-color: #f2dede;">
                        <tr>
                            <th>Date</th>
                            <th>Voucher No</th>
                            <th>Head / Purpose</th>
                            <th>Account</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTransactions as $row)
                            @php
                                $type_badge = '';
                                if($row->trans_type == 'Supplier') {
                                    $type_badge = '<span class="label label-danger"><i class="fa fa-truck"></i> ' . e($row->head_title) . '</span>';
                                } else if($row->trans_type == 'Project') {
                                    $type_badge = '<span class="label label-success"><i class="fa fa-building"></i> ' . e($row->head_title) . '</span>';
                                } else {
                                    $bg_color = (str_contains(strtolower($row->head_title ?? ''), 'loan')) ? 'label-primary' : 'label-warning';
                                    $type_badge = '<span class="label ' . $bg_color . '"><i class="fa fa-list-alt"></i> ' . e($row->head_title) . '</span>';
                                }

                                $approval_status = $row->approval_status ?? 'Approved';
                                if($approval_status == 'Pending'){
                                    $status_badge_html = '<span class="label label-warning"><i class="fa fa-clock-o"></i> Pending</span>';
                                } else if($approval_status == 'Rejected'){
                                    $status_badge_html = '<span class="label label-danger"><i class="fa fa-times-circle"></i> Rejected</span>';
                                } else {
                                    $status_badge_html = '<span class="label label-success"><i class="fa fa-check"></i> Approved</span>';
                                }
                            @endphp
                            <tr>
                                <td>{{ date('d-M-Y', strtotime($row->t_date)) }}</td>
                                <td><b>{{ $row->voucher_no }}</b></td>
                                <td>{!! $type_badge !!}</td>
                                <td>{{ $row->method_name ?? 'Cash' }}</td>
                                <td class="text-bold text-red">৳ {{ number_format((float)$row->amount, 2) }}</td>
                                <td>{!! $status_badge_html !!}</td>
                                <td>
                                    <a href="{{ route('payment-vouchers.show', $row->id) }}?type={{ $row->trans_type }}" class="btn btn-info btn-xs btn-flat" title="View Details"><i class="fa fa-eye"></i> View</a>
                                    <a href="{{ route('payment-vouchers.show', $row->id) }}?type={{ $row->trans_type }}&print=1" target="_blank" class="btn btn-primary btn-xs btn-flat" title="Print"><i class="fa fa-print"></i> Print</a>
                                    <a href="{{ route('payment-vouchers.edit', $row->id) }}?type={{ $row->trans_type }}" class="btn btn-warning btn-xs btn-flat" title="Edit"><i class="fa fa-edit"></i> Edit</a>
                                    <form action="{{ route('payment-vouchers.destroy', $row->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to reverse and delete this transaction?');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="trans_type" value="{{ $row->trans_type }}">
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

    var ajaxUrl = "{{ route('payment-vouchers.index') }}";

    // Voucher Duplicate Check
    $('#voucher_input').on('keyup blur', function(){
        var voucher = $(this).val();
        if(voucher.length > 0){
            $.post(ajaxUrl, {ajax_request: 'check_voucher', voucher: voucher}, function(res){
                if(res.status === 'duplicate'){ 
                    $('#voucher_error').show(); 
                    $('#btn_save').prop('disabled', true); 
                } else { 
                    $('#voucher_error').hide(); 
                    $('#btn_save').prop('disabled', false); 
                }
            }, 'json');
        } else {
            $('#voucher_error').hide();
            $('#btn_save').prop('disabled', false);
        }
    });

    // Loan Info
    $('#loan_head_id').change(function(){
        var head_id = $(this).val();
        if(head_id){
            $.post(ajaxUrl, {ajax_request: 'get_loan_info', head_id: head_id}, function(res){
                $('#lbl_total_loan').text(res.total_received + ' Tk');
                $('#lbl_principal_paid').text(res.principal_paid + ' Tk');
                $('#lbl_interest_paid').text(res.interest_paid + ' Tk');
                $('#lbl_due_principal').text(res.due_principal + ' Tk');
                $('#realtime_loan_info').slideDown();
            }, 'json');
        } else {
            $('#realtime_loan_info').slideUp();
        }
    });

    // Interest Warning Logic
    $('#loan_action').change(function(){
        if($(this).val() === 'Interest'){
            $('#interest_warning').slideDown();
        } else {
            $('#interest_warning').slideUp();
        }
    });

    // Payment Type & Account Heads Logic
    $('#payment_type').change(function(){
        var type = $(this).val();
        $('#supplier_section, #project_section, #loan_section, #heads_section').hide();
        $('#supplier_id, #expense_project_id, #project_expense_reason').prop('required', false);
        $('#loan_action').prop('required', false);
        
        $('#dynamic_heads_container').empty();
        $('#final_head_id').val('');

        if(type === 'Supplier') { 
            $('#supplier_section').slideDown(); 
            $('#supplier_id').prop('required', true); 
        }
        else if(type === 'Project') { 
            $('#project_section').slideDown(); 
            $('#expense_project_id').prop('required', true); 
            $('#project_expense_reason').prop('required', true); 
            $.post(ajaxUrl, {ajax_request: 'get_project_expense_reasons'}, function(res){
                var list = '';
                $.each(res, function(i, item){ list += '<option value="'+item+'">'; });
                $('#expense_reasons_list').html(list);
            }, 'json');
        }
        else if(type === 'Loan') { 
            $('#loan_section').slideDown(); 
            $('#loan_action').prop('required', true); 
        }
        else if(type && type.startsWith('Others_')) { 
            var head_id = type.split('_')[1];
            $('#final_head_id').val(head_id);
            fetchSubHeads(head_id, 1);
        }
    });

    function fetchSubHeads(parent_id, currentLevel) {
        $('.sub-head-group').each(function() {
            if (parseInt($(this).data('level')) > currentLevel) { $(this).remove(); }
        });

        if (parent_id) {
            $.post(ajaxUrl, {ajax_request: 'get_sub_heads', parent_id: parent_id}, function(res) {
                if (res && res.length > 0) {
                    $('#heads_section').slideDown();
                    var nextLevel = currentLevel + 1;
                    var html = '<div class="form-group sub-head-group" data-level="'+nextLevel+'">';
                    html += '<label class="col-sm-3 control-label">Sub Head</label>';
                    html += '<div class="col-sm-9">';
                    html += '<select class="form-control select2 head-select" data-level="'+nextLevel+'" style="width:100%;">';
                    html += '<option value="">-- Select Sub Head --</option>';
                    $.each(res, function(i, item){ html += '<option value="'+item.id+'">'+item.head_name+'</option>'; });
                    html += '</select></div></div>';
                    
                    $('#dynamic_heads_container').append(html);
                    if($.fn.select2) { $('.head-select[data-level="'+nextLevel+'"]').select2(); }
                }
            }, 'json');
        }
    }

    $(document).on('change', '.head-select', function() {
        var currentLevel = parseInt($(this).data('level'));
        var parent_id = $(this).val();
        
        if (parent_id) {
            $('#final_head_id').val(parent_id);
            fetchSubHeads(parent_id, currentLevel);
        } else {
            if (currentLevel > 2) {
                var prev_id = $('.head-select[data-level="' + (currentLevel - 1) + '"]').val();
                $('#final_head_id').val(prev_id);
            } else {
                var type_val = $('#payment_type').val();
                $('#final_head_id').val(type_val.split('_')[1]);
            }
            $('.sub-head-group').each(function() {
                if (parseInt($(this).data('level')) > currentLevel) { $(this).remove(); }
            });
        }
    });

    // Budget Warning Logic
    var current_proj_cost = 0;
    var current_proj_limit = 0;
    var current_proj_has_limit = false;

    function formatAmt(amount) {
        return amount.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function checkBudgetWarning(proj_id, container_id) {
        $(container_id).hide();
        current_proj_has_limit = false;
        var pay_amount = parseFloat($('#pay_amount').val()) || 0;
        
        if(proj_id){
            $.post(ajaxUrl, {ajax_request: 'get_project_budget_status', project_id: proj_id}, function(res){
                if(res.has_limit){
                    current_proj_cost = res.total_cost_raw;
                    current_proj_limit = res.limit_amount_raw;
                    current_proj_has_limit = true;
                    updateBudgetWarningUI(container_id, pay_amount);
                    if (current_proj_cost >= current_proj_limit) {
                        alert("⚠️ Warning Message ⚠️\n\nCurrent cost of this project (" + formatAmt(current_proj_cost) + ") Already Estimated Value (" + formatAmt(current_proj_limit) + ") has crossed!");
                    }
                }
            }, 'json');
        }
    }

    function updateBudgetWarningUI(container_id, input_val) {
        if(!current_proj_has_limit) return;
        var total = current_proj_cost + parseFloat(input_val || 0);
        if(total > current_proj_limit){
            $(container_id).css({'background-color': '#f2dede', 'color': '#a94442', 'border': '1px solid #ebccd1'});
            $(container_id).find('.warning-icon').html('<i class="fa fa-exclamation-triangle"></i>');
            $(container_id).find('.warning-text').html("<b>WARNING:</b> Total cost (" + formatAmt(total) + ") will exceed limit (" + formatAmt(current_proj_limit) + ")!");
        } else {
            $(container_id).css({'background-color': '#d9edf7', 'color': '#31708f', 'border': '1px solid #bce8f1'});
            $(container_id).find('.warning-icon').html('<i class="fa fa-info-circle"></i>');
            $(container_id).find('.warning-text').html("<b>Budget Info:</b> Cost: " + formatAmt(current_proj_cost) + " / Limit: " + formatAmt(current_proj_limit));
        }
        $(container_id).slideDown();
    }

    $('#pay_amount').on('keyup change', function(){
        var val = $(this).val();
        if($('#payment_type').val() === 'Project') updateBudgetWarningUI('#expense_project_warning', val);
        else if($('#payment_type').val() === 'Supplier' && $('input[name="supplier_pay_mode"]:checked').val() === 'Project') updateBudgetWarningUI('#supplier_project_warning', val);
    });

    $('#expense_project_id').change(function(){ checkBudgetWarning($(this).val(), '#expense_project_warning'); });

    // Supplier Logic
    $('#supplier_id').change(function(){
        var supplier_id = $(this).val();
        $('#supplier_project_id').html('<option value="">-- Select Project --</option>');
        $('#supplier_pay_mode_div, #supplier_project_div, #supplier_bill_info_box, #supplier_total_status').hide();
        if(!supplier_id) return;
        
        $.post(ajaxUrl, {ajax_request: 'get_supplier_total_dues', supplier_id: supplier_id}, function(res){
            $('#sup_overall_badge').text(res.status).removeClass('label-danger label-success label-warning label-default').addClass('label-' + res.color);
            $('#sup_overall_amt').text(' ' + res.amount);
            $('#supplier_total_status').slideDown();
        }, 'json');

        $.post(ajaxUrl, {ajax_request: 'get_supplier_projects', supplier_id: supplier_id}, function(res){
            if(res.length > 0){
                $.each(res, function(i, item){ $('#supplier_project_id').append('<option value="'+item.id+'">'+item.project_name+'</option>'); });
                $('#supplier_pay_mode_div, #supplier_project_div').slideDown();
            } else {
                $.post(ajaxUrl, {ajax_request: 'get_all_projects'}, function(allP){
                    $.each(allP, function(i, p){ $('#supplier_project_id').append('<option value="'+p.id+'">'+p.name+'</option>'); });
                    $('#supplier_pay_mode_div, #supplier_project_div').slideDown();
                }, 'json');
            }
        }, 'json');
    });

    $('#supplier_project_id').change(function(){
        var proj_id = $(this).val(); 
        var sup_id = $('#supplier_id').val();
        if(!proj_id) return;
        
        $.post(ajaxUrl, {ajax_request: 'get_supplier_project_summary', supplier_id: sup_id, project_id: proj_id}, function(res){
            $('#ps_billed').text(res.total_billed); 
            $('#ps_paid').text(res.total_paid);
            $('#ps_net_amt span').text(res.net_balance).css('color', res.color);
            $('#ps_status_text').text(res.status); 
            $('#proj_summary_div').slideDown();
        }, 'json');

        checkBudgetWarning(proj_id, '#supplier_project_warning');

        $.post(ajaxUrl, {ajax_request: 'get_supplier_pending_bills', supplier_id: sup_id, project_id: proj_id}, function(res){
            var rows = '';
            $('#supplier_bill_id').html('<option value="">-- Advance / Partial for whole project --</option>');
            if(res.length > 0){
                $.each(res, function(i, item){
                    rows += '<tr><td>'+item.bill_date+'</td><td>'+item.voucher_no+'</td><td> '+item.grand_total+'</td><td class="text-danger text-bold"> '+item.due_amount+'</td><td><button type="button" class="btn btn-xs btn-danger fill-amt-btn" data-id="'+item.id+'" data-amt="'+item.due_amount+'">Pay This</button></td></tr>';
                    $('#supplier_bill_id').append('<option value="'+item.id+'">Bill: '+item.voucher_no+'</option>');
                });
            } else { 
                rows = '<tr><td colspan="5" class="text-center">No pending bills.</td></tr>'; 
            }
            $('#supplier_pending_bills_table tbody').html(rows); 
            $('#supplier_bill_info_box').slideDown();
        }, 'json');
    });

    $(document).on('click', '.fill-amt-btn', function(){ 
        $('#pay_amount').val($(this).data('amt')).trigger('change'); 
    });

    // Payment Category
    $('#deposit_category').change(function(){
        var cat = $(this).val();
        $('#method_container, #bank_details_box, #mfs_details_box').hide();
        if(!cat) return;
        
        $.post(ajaxUrl, {ajax_request: 'get_payment_methods', type: cat}, function(res) {
            var html = '<option value="">-- Select Account --</option>';
            $.each(res, function(i, item){ 
                html += '<option value="'+item.id+'">'+item.method_name+' (Balance: '+item.amount_formatted+')</option>'; 
            });
            $('#method_id').html(html).trigger('change'); 
            $('#method_container').slideDown();
            if(cat === 'Bank') $('#bank_details_box').slideDown();
            if(cat === 'MFS') $('#mfs_details_box').slideDown();
        }, 'json');
    });

    $('#bank_trans_type').change(function(){
        if($(this).val()) { $('#bank_input_fields').slideDown(); } 
        else { $('#bank_input_fields').slideUp(); }
    });

    $('#mfs_type').change(function(){
        if($(this).val()) { $('#mfs_input_fields').slideDown(); } 
        else { $('#mfs_input_fields').slideUp(); }
    });

    if($.fn.DataTable && !$.fn.DataTable.isDataTable('#transactionTable')) { 
        $('#transactionTable').DataTable({ "order": [] }); 
    }

    $('#payment_form').on('submit', function(e) {
        var pType = $('#payment_type').val();
        if(pType && pType.startsWith('Others_')) {
            $('<input>').attr({type: 'hidden', name: 'payment_type', value: 'Others'}).appendTo(this);
        }

        if (current_proj_has_limit) {
            var val = parseFloat($('#pay_amount').val()) || 0;
            var total = current_proj_cost + val;
            if (total > current_proj_limit) {
                if(!confirm("⚠️ Warning: Estimated Value crossed!\nAre you sure you want to save this?")) e.preventDefault();
            }
        }
    });
});
</script>
@endpush
