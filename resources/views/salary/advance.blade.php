@extends('layouts.master')

@section('title', 'JR || Corporation - Salary Advance Management')
@section('page_title', 'Salary Advance Management')

@section('breadcrumb')
<li>Employee Desk</li>
<li class="active">Salary Advance</li>
@endsection

@push('styles')
<style>
    .dynamic-info-box { background: #fffaf0; border: 1px solid #f9cd9f; padding: 15px; border-radius: 5px; margin-bottom: 15px; display: none; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05); }
    .label-info-custom { background: #e08e0b !important; color: #fff; padding: 5px 12px; border-radius: 4px; margin-bottom: 15px; display: inline-block; font-weight: bold; }
    .hr-style { border-top: 1px solid #d2d6de; margin: 15px 0; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-5">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Provide New Advance</h3>
            </div>
            <form method="POST" action="{{ route('salary.advance.store') }}" class="form-horizontal">
                @csrf
                <input type="hidden" name="main_head" value="Advance Salary">

                <div class="box-body">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Select Employee <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <select class="form-control select2" name="employee_id" style="width: 100%;" required>
                                <option value="">-- Select Employee --</option>
                                @foreach($employees as $erow)
                                    <option value="{{ $erow->id }}">{{ $erow->firstname }} {{ $erow->lastname }} ({{ $erow->employee_id }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Advance Date <span class="text-danger">*</span></label>
                        <div class="col-sm-8"><input type="date" class="form-control" name="advance_date" value="{{ date('Y-m-d') }}" required></div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Amount (৳) <span class="text-danger">*</span></label>
                        <div class="col-sm-8"><input type="number" step="0.01" class="form-control text-bold text-red" name="amount" placeholder="0.00" required></div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Adjust Month <span class="text-danger">*</span></label>
                        <div class="col-sm-8"><input type="month" class="form-control" name="adjust_month" value="{{ date('Y-m') }}" required></div>
                    </div>
                    
                    <div class="hr-style"></div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">Category <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
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
                            <label class="col-sm-4 control-label" id="method_label">From Account <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <select class="form-control select2" name="method_id" id="method_id" style="width:100%;" required>
                                    <option value="">-- Select Account --</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="bank_details_box" class="dynamic-info-box">
                        <span class="label-info-custom"><i class="fa fa-bank"></i> Bank Details</span>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Transfer Type</label>
                            <div class="col-sm-8">
                                <select class="form-control" name="bank_trans_type" id="bank_trans_type">
                                    <option value="">Select</option>
                                    <option value="Check">Check</option>
                                    <option value="Online Transfer">Online Transfer</option>
                                </select>
                            </div>
                        </div>
                        <div id="bank_input_fields" style="display:none;">
                            <div class="form-group">
                                <label class="col-sm-4 control-label" id="bank_no_label">Check / Ref No</label>
                                <div class="col-sm-8"><input type="text" name="bank_ref_no" class="form-control" placeholder="Check/Ref No"></div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Date</label>
                                <div class="col-sm-8"><input type="date" name="bank_ref_date" class="form-control" value="{{ date('Y-m-d') }}"></div>
                            </div>
                        </div>
                    </div>

                    <div id="mfs_details_box" class="dynamic-info-box">
                        <span class="label-info-custom"><i class="fa fa-mobile"></i> MFS Details</span>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">MFS Type</label>
                            <div class="col-sm-8">
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
                                <label class="col-sm-4 control-label" id="mfs_no_label">Trx ID</label>
                                <div class="col-sm-8"><input type="text" name="mfs_ref_no" class="form-control" placeholder="Trx ID"></div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Date</label>
                                <div class="col-sm-8"><input type="date" name="mfs_ref_date" class="form-control" value="{{ date('Y-m-d') }}"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">Voucher No <span class="text-danger">*</span></label>
                        <div class="col-sm-8"><input type="text" class="form-control" name="voucher" placeholder="Voucher No" required></div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">Note</label>
                        <div class="col-sm-8"><textarea class="form-control" name="note" rows="2" placeholder="Advance reason..."></textarea></div>
                    </div>

                </div>
                <div class="box-footer text-right">
                    <button type="submit" name="add_advance" class="btn btn-primary btn-flat"><i class="fa fa-save"></i> Save Advance</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-7">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-list"></i> Advance History</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped" id="example1">
                    <thead class="bg-success">
                        <tr>
                            <th>Date</th>
                            <th>Voucher</th>
                            <th>Employee</th>
                            <th>Amount</th>
                            <th>Adjust Month</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($advances as $row)
                            <tr>
                                <td>{{ date('d-m-Y', strtotime($row->advance_date)) }}</td>
                                <td><span class="label label-info">{{ $row->voucher_no }}</span></td>
                                <td><b>{{ $row->employee->firstname ?? '' }} {{ $row->employee->lastname ?? '' }}</b></td>
                                <td class="text-bold text-red">৳ {{ number_format((float)$row->amount, 2) }}</td>
                                <td>{{ date('M-Y', strtotime($row->adjust_month . '-01')) }}</td>
                                <td>
                                    @if($row->status == 'Adjusted')
                                        <span class="label label-success"><i class="fa fa-check"></i> Adjusted</span>
                                    @else
                                        <span class="label label-warning"><i class="fa fa-clock-o"></i> Pending</span>
                                    @endif
                                </td>
                                <td>
                                    @if($row->status == 'Pending')
                                        <button class="btn btn-success btn-xs btn-flat adjust_btn" 
                                            data-id="{{ $row->id }}" 
                                            data-voucher="{{ $row->voucher_no }}" 
                                            data-emp="{{ $row->employee->firstname ?? '' }} {{ $row->employee->lastname ?? '' }}" 
                                            data-amount="{{ number_format((float)$row->amount, 2) }}">
                                            <i class="fa fa-check"></i> Adjust
                                        </button>
                                        <button class="btn btn-danger btn-xs btn-flat delete_btn" 
                                            data-id="{{ $row->id }}" 
                                            data-voucher="{{ $row->voucher_no }}">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                    @else
                                        <span class="text-muted"><i class="fa fa-lock"></i> Adjusted</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="adjustModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-green" style="background-color: #00a65a; color: white;">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 0.8;">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b><i class="fa fa-check-circle"></i> Manual Adjust Confirmation</b></h4>
            </div>
            <form action="{{ route('salary.advance.adjust') }}" method="POST">
                @csrf
                <input type="hidden" class="adj_id" name="adjust_id">
                <div class="modal-body text-center">
                    <h4 class="text-bold" style="margin-bottom: 15px; color: #333;">Are you sure you want to mark this Advance as Adjusted?</h4>
                    <div style="background: #f4fdf4; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 15px auto; display: inline-block; text-align: left; min-width: 320px;">
                        <p style="margin-bottom: 5px;"><strong>Advance Voucher:</strong> <span id="adj_voucher" class="text-primary text-bold"></span></p>
                        <p style="margin-bottom: 5px;"><strong>Employee:</strong> <span id="adj_emp" class="text-bold"></span></p>
                        <p style="margin-bottom: 5px;"><strong>Advance Amount:</strong> <span id="adj_amount" class="text-danger text-bold"></span></p>
                        <hr style="margin: 8px 0; border-top: 1px dashed #c3e6cb;">
                        <div id="adj_salary_info">
                            <p style="margin-bottom: 5px; color: #0073b7;"><i class="fa fa-spinner fa-spin"></i> Checking linked salary record...</p>
                        </div>
                    </div>
                    <p class="text-success" style="margin-top: 5px;"><small><i class="fa fa-bolt"></i> <b>Automatic:</b> Clicking confirm will automatically adjust and reduce the Due Amount on the linked salary voucher!</small></p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Cancel</button>
                  <button type="submit" class="btn btn-success btn-flat" name="confirm_adjust"><i class="fa fa-check"></i> Yes, Adjust & Update Salary Due</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b><i class="fa fa-trash text-danger"></i> Delete Confirmation</b></h4>
            </div>
            <div class="modal-body">
                <form action="{{ route('salary.advance.delete') }}" method="POST">
                @csrf
                <input type="hidden" class="del_id" name="delete_id">
                <div class="text-center">
                    <h4>Are you sure you want to delete this Advance Record?</h4>
                    <h3 id="del_voucher" class="text-bold" style="margin: 15px 0;"></h3>
                    <p class="text-danger"><small>This action will refund the amount to the specific payment account and permanently delete the transaction history.</small></p>
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Cancel</button>
              <button type="submit" class="btn btn-danger btn-flat" name="delete_advance"><i class="fa fa-trash"></i> Delete Now</button>
              </form>
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
    if($.fn.DataTable && !$.fn.DataTable.isDataTable('#example1')) { 
        $('#example1').DataTable({"order": [[ 0, "desc" ]]}); 
    }

    var ajaxUrl = "{{ route('salary.advance') }}";

    $('.adjust_btn').click(function(e){
        e.preventDefault();
        var id = $(this).data('id');
        var voucher = $(this).data('voucher');
        var emp = $(this).data('emp');
        var amount = $(this).data('amount');
        
        $('.adj_id').val(id);
        $('#adj_voucher').text(voucher);
        $('#adj_emp').text(emp);
        $('#adj_amount').text('৳ ' + amount);
        $('#adj_salary_info').html('<p style="margin-bottom: 5px; color: #0073b7;"><i class="fa fa-spinner fa-spin"></i> Checking linked salary record...</p>');
        $('#adjustModal').modal('show');

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: { ajax_request: 'get_adjust_preview', id: id },
            success: function(res){
                if(res.has_salary){
                    var html = '<p style="margin-bottom: 5px;"><strong>Target Salary:</strong> <span class="label label-primary">' + res.salary_month + ' (Voucher: ' + res.salary_voucher + ')</span></p>';
                    html += '<p style="margin-bottom: 5px;"><strong>Current Salary Due:</strong> <span style="text-decoration: line-through; color: #888;">৳ ' + res.current_due + '</span></p>';
                    html += '<p style="margin-bottom: 0; color: #00a65a; font-weight: bold;"><strong>New Due after Adjust:</strong> ৳ ' + res.new_due + ' <small class="text-success"><i class="fa fa-arrow-down"></i> Reduced by ৳ ' + res.amount + '</small></p>';
                    $('#adj_salary_info').html(html);
                } else {
                    $('#adj_salary_info').html('<p style="margin-bottom: 0; color: #888;"><i>No processed salary voucher found for this month yet. Status will be marked as Adjusted.</i></p>');
                }
            },
            error: function(){
                $('#adj_salary_info').html('<p style="margin-bottom: 0; color: #888;"><i>Linked salary will be automatically updated on confirmation.</i></p>');
            }
        });
    });

    $('.delete_btn').click(function(e){
        e.preventDefault();
        $('#deleteModal').modal('show');
        var id = $(this).data('id');
        var voucher = $(this).data('voucher');
        $('.del_id').val(id);
        $('#del_voucher').html('Voucher No: ' + voucher);
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
        } else { 
            $('#bank_input_fields').hide(); 
        }
    });

    $('#mfs_type').change(function(){
        var mfs = $(this).val();
        if(mfs){
            $('#mfs_no_label').text(mfs + ' Trans. No/ID');
            $('#mfs_input_fields').slideDown();
        } else { 
            $('#mfs_input_fields').hide(); 
        }
    });
});
</script>
@endpush
