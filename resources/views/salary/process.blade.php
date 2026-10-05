@extends('layouts.master')

@section('title', 'JR || Corporation - Monthly Salary Processing')
@section('page_title', 'Monthly Salary Processing')

@section('breadcrumb')
<li>Employee Desk</li>
<li class="active">Salary Pay</li>
@endsection

@push('styles')
<style>
    .calc-box { background: #f9fafc; padding: 15px; border-radius: 5px; border: 1px solid #e1e4e8; margin-bottom: 15px; }
    .amount-text { font-weight: bold; text-align: right; }
    .gross-text { color: #00a65a; font-size: 16px; font-weight: bold; text-align: right; }
    .deduct-text { color: #dd4b39; font-size: 16px; font-weight: bold; text-align: right; }
    .net-text { color: #666; font-size: 16px; font-weight: bold; text-align: right; background: #eee; margin-bottom: 10px;}
    .paid-text { color: #0073b7; font-size: 22px; font-weight: bold; text-align: right; background: #eaf3f9; margin-bottom: 10px;}
    .dynamic-info-box { background: #fffaf0; border: 1px solid #f9cd9f; padding: 15px; border-radius: 5px; margin-bottom: 15px; display: none; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05); }
    .label-info-custom { background: #e08e0b !important; color: #fff; padding: 5px 12px; border-radius: 4px; margin-bottom: 15px; display: inline-block; font-weight: bold; font-size: 12px; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Process Monthly Employee Salary</h3>
            </div>
            <form method="POST" action="{{ route('salary.process.store') }}" class="form-horizontal" id="salaryForm">
                @csrf
                <input type="hidden" name="emp_name" id="emp_name" value="">
                <input type="hidden" name="main_head" value="Salary">

                <div class="box-body">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Select Month <span class="text-danger">*</span></label>
                        <div class="col-sm-4">
                            <input type="month" class="form-control" name="pay_month" id="pay_month" value="{{ $previous_month }}" required>
                        </div>
                        <div class="col-sm-5">
                            <p class="text-muted" style="margin-top: 7px;"><i class="fa fa-info-circle"></i> Defaults to previous month</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Select Employee <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <select class="form-control select2" name="employee_id" id="employee_id" style="width: 100%;" required>
                                <option value="">-- Select Employee --</option>
                                @foreach($employees as $erow)
                                    <option value="{{ $erow->id }}">{{ $erow->firstname }} {{ $erow->lastname }} ({{ $erow->employee_id }})</option>
                                @endforeach
                            </select>
                            <span id="paid_warning" class="text-danger font-weight-bold" style="display:none; margin-top:5px;"><i class="fa fa-ban"></i> Salary already paid for this employee in selected month!</span>
                        </div>
                    </div>

                    <div id="salary_calc_area" style="display:none;">
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="calc-box">
                                    <h4 class="text-success"><i class="fa fa-plus-circle"></i> Earnings & Allowances</h4>
                                    <hr style="margin: 10px 0;">
                                    <div class="form-group">
                                        <label class="col-sm-6 control-label">Basic Salary</label>
                                        <div class="col-sm-6"><input type="number" step="0.01" class="form-control amount-text auto-calc" name="main_salary" id="main_salary" value="0" readonly></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-6 control-label">Mobile Bill</label>
                                        <div class="col-sm-6"><input type="number" step="0.01" class="form-control amount-text auto-calc" name="mobile_bill" id="mobile_bill" value="0"></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-6 control-label">Lunch Bill</label>
                                        <div class="col-sm-6"><input type="number" step="0.01" class="form-control amount-text auto-calc" name="lunch_bill" id="lunch_bill" value="0"></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-6 control-label">Other Allowances</label>
                                        <div class="col-sm-6"><input type="number" step="0.01" class="form-control amount-text auto-calc" name="other_allowances" id="other_allowances" value="0"></div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-6 control-label" style="color: #0073b7;">Previous Due</label>
                                        <div class="col-sm-6">
                                            <input type="number" step="0.01" class="form-control amount-text auto-calc" name="previous_due" id="previous_due" value="0" style="color: #0073b7; font-weight: bold;">
                                            <small id="due_notice" class="text-primary" style="display:none;"><i class="fa fa-info-circle"></i> Unpaid salary added</small>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-6 control-label">Special Bonus</label>
                                        <div class="col-sm-6"><input type="number" step="0.01" class="form-control amount-text auto-calc" name="bonus" id="bonus" value="0"></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-6 control-label">Total Gross Salary</label>
                                        <div class="col-sm-6"><input type="number" step="0.01" class="form-control gross-text" name="gross_salary" id="gross_salary" value="0" readonly></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="calc-box">
                                    <h4 class="text-danger"><i class="fa fa-minus-circle"></i> Deductions & Adjustments</h4>
                                    <hr style="margin: 10px 0;">
                                    <div class="form-group">
                                        <label class="col-sm-6 control-label">Advance Deduction</label>
                                        <div class="col-sm-6">
                                            <input type="number" step="0.01" class="form-control amount-text auto-calc" name="adv_deduct" id="adv_deduct" value="0" style="color:red;">
                                            <small id="adv_notice" class="text-danger" style="display:none;"><i class="fa fa-warning"></i> Advance deducted</small>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-6 control-label">Late / Absent Fine</label>
                                        <div class="col-sm-6"><input type="number" step="0.01" class="form-control amount-text auto-calc" name="fine" id="fine" value="0"></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-6 control-label">Other Deduction</label>
                                        <div class="col-sm-6"><input type="number" step="0.01" class="form-control amount-text auto-calc" name="other_deduction" id="other_deduction" value="0"></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-6 control-label">Total Deduction</label>
                                        <div class="col-sm-6"><input type="number" step="0.01" class="form-control deduct-text" id="total_deduction" value="0" readonly></div>
                                    </div>
                                    
                                    <hr style="margin: 15px 0;">
                                    <div class="form-group">
                                        <label class="col-sm-6 control-label" style="font-size: 15px;">Net Salary Payable</label>
                                        <div class="col-sm-6"><input type="number" step="0.01" class="form-control net-text" name="net_payable" id="net_payable" value="0" readonly></div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-6 control-label" style="font-size: 16px; color:#0073b7;">Paid Amount</label>
                                        <div class="col-sm-6"><input type="number" step="0.01" class="form-control paid-text" name="paid_amount" id="paid_amount" value="0" required></div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-6 control-label" id="due_label" style="font-size: 14px;">Due / Advance</label>
                                        <div class="col-sm-6"><input type="text" class="form-control text-right" id="due_display" value="0.00" readonly style="font-weight:bold; font-size:16px;"></div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <hr>
                        <h4 style="margin-bottom: 15px;"><i class="fa fa-money"></i> Payment Details</h4>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Payment Category <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <select class="form-control" name="deposit_category" id="deposit_category" required>
                                    <option value="">-- Select Category --</option>
                                    <option value="Cash">Cash (নগদ)</option>
                                    <option value="Bank">Bank (ব্যাংক একাউন্ট)</option>
                                    <option value="MFS">Mobile Financial Services (MFS)</option>
                                </select>
                            </div>
                        </div>

                        <div id="method_container" style="display:none;">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">From Account <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select class="form-control select2" name="method_id" id="method_id" style="width: 100%;" required>
                                        <option value="">-- Select Account --</option>
                                    </select>
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
                                        <option value="Check">Check</option>
                                        <option value="Online Transfer">Online Transfer</option>
                                    </select>
                                </div>
                            </div>
                            <div id="bank_input_fields" style="display:none;">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label" id="bank_no_label">Number & Date</label>
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
                                    <label class="col-sm-3 control-label" id="mfs_no_label">Trx ID & Date</label>
                                    <div class="col-sm-4"><input type="text" name="mfs_ref_no" class="form-control" placeholder="TrxID"></div>
                                    <div class="col-sm-5"><input type="date" name="mfs_ref_date" class="form-control" value="{{ date('Y-m-d') }}"></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Voucher No <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="voucher" placeholder="Voucher No" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Payment Date <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control" name="pay_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Note / Remarks</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" name="note" rows="2" placeholder="Payment remarks..."></textarea>
                            </div>
                        </div>

                        <div class="box-footer text-center">
                            <button type="submit" name="pay_salary" class="btn btn-success btn-flat btn-lg"><i class="fa fa-money"></i> Confirm & Pay Salary</button>
                        </div>
                    </div>
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

    var ajaxUrl = "{{ route('salary.process') }}";

    $('#employee_id, #pay_month').change(function(){
        var emp_id = $('#employee_id').val();
        var month = $('#pay_month').val();

        if(!emp_id || !month){
            $('#salary_calc_area').slideUp();
            return;
        }

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: { ajax_request: 'get_salary_info', emp_id: emp_id, month: month },
            success: function(res){
                if(res.status === 'paid'){
                    $('#paid_warning').show();
                    $('#salary_calc_area').slideUp();
                    return;
                }
                
                $('#paid_warning').hide();
                $('#emp_name').val(res.emp_name);
                $('#main_salary').val(res.salary);
                $('#mobile_bill').val(res.mobile_bill);
                $('#lunch_bill').val(res.lunch_bill);
                $('#other_allowances').val(res.other_allowances);
                
                $('#previous_due').val(res.previous_due);
                if(res.previous_due > 0){ $('#due_notice').show(); } 
                else { $('#due_notice').hide(); }
                
                $('#adv_deduct').val(res.advance_deduct);
                if(res.advance_deduct > 0){ $('#adv_notice').show(); } 
                else { $('#adv_notice').hide(); }

                $('#salary_calc_area').slideDown();
                calculateSalary();
            }
        });
    });

    function calculateSalary(){
        var basic = parseFloat($('#main_salary').val()) || 0;
        var mobile = parseFloat($('#mobile_bill').val()) || 0;
        var lunch = parseFloat($('#lunch_bill').val()) || 0;
        var other = parseFloat($('#other_allowances').val()) || 0;
        var prev_due = parseFloat($('#previous_due').val()) || 0;
        var bonus = parseFloat($('#bonus').val()) || 0;

        var gross = basic + mobile + lunch + other + prev_due + bonus;
        $('#gross_salary').val(gross.toFixed(2));

        var adv = parseFloat($('#adv_deduct').val()) || 0;
        var fine = parseFloat($('#fine').val()) || 0;
        var other_ded = parseFloat($('#other_deduction').val()) || 0;

        var total_deduct = adv + fine + other_ded;
        $('#total_deduction').val(total_deduct.toFixed(2));

        var net = gross - total_deduct;
        $('#net_payable').val(net.toFixed(2));
        
        $('#paid_amount').val(net.toFixed(2));
        $('#due_label').text("Due / Advance");
        $('#due_display').val("0.00").css({'color':'#999'});
    }

    $('.auto-calc').on('input', function(){ calculateSalary(); });

    $('#paid_amount').on('input', function(){
        var net = parseFloat($('#net_payable').val()) || 0;
        var paid = parseFloat($(this).val()) || 0;
        var diff = net - paid;
        
        if(diff > 0){
            $('#due_label').text("Due (Employee Receivable)");
            $('#due_display').val(diff.toFixed(2) + " ৳").css({'color':'#dd4b39'});
        } else if(diff < 0){
            $('#due_label').text("Extra Advance (Deducts Next Month)");
            $('#due_display').val(Math.abs(diff).toFixed(2) + " ৳").css({'color':'#00a65a'});
        } else {
            $('#due_label').text("Due / Advance");
            $('#due_display').val("0.00").css({'color':'#999'});
        }
    });

    $('#deposit_category').change(function(){
        var cat = $(this).val();
        $('#method_id').html('<option value="">Loading...</option>').trigger('change');
        $('#method_container, #bank_details_box, #mfs_details_box').hide();
        
        if(!cat) {
            $('#method_id').html('<option value="">-- Select Account --</option>').trigger('change');
            return;
        }
        
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: { ajax_request: 'get_payment_methods', type: cat },
            success: function(res) {
                var html = '<option value="">-- Select Account --</option>';
                if(res.length > 0){
                    $.each(res, function(i, item){
                        var bal = parseFloat(item.amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        html += '<option value="'+item.id+'">'+item.method_name+' (Balance: ৳ '+bal+')</option>';
                    });
                    $('#method_id').html(html).trigger('change');
                    $('#method_container').slideDown();
                    
                    if(cat === 'Bank') $('#bank_details_box').slideDown();
                    if(cat === 'MFS') $('#mfs_details_box').slideDown();
                } else {
                    $('#method_id').html('<option value="" disabled>No accounts found!</option>').trigger('change');
                    $('#method_container').slideDown();
                }
            }
        });
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
});
</script>
@endpush
