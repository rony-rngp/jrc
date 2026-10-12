@extends('layouts.master')

@section('title', 'JR || Corporation - Client Bill Management')
@section('page_title', 'Client Bill Management')

@section('breadcrumb')
<li><a href="{{ route('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active">Client Bills</li>
@endsection

@push('styles')
<style>
    .clean-box { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); border: 1px solid #f2f2f2; margin-bottom: 25px; border-top: 4px solid #3498db;}
    .sec-title { margin-top: 0; font-size: 18px; font-weight: 600; color: #2c3e50; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eaeaea; }
    
    .table-modern { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.02); width: 100%;}
    .table-modern thead th { background: #f8fbff; color: #2c3e50; font-weight: 600; font-size: 13px; padding: 12px 15px; border-bottom: 2px solid #e1ebf5;}
    .table-modern tbody td { padding: 12px 15px; font-size: 13px; color: #444; vertical-align: middle; border-bottom: 1px solid #f4f4f4; }
    .table-modern tbody tr:hover { background-color: #fcfdfe; }
    
    .status-running { background: #fff3cd; color: #856404; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
    .status-final { background: #d4edda; color: #155724; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
    
    .amt-cost { color: #e74c3c; font-weight: 600; }
    .amt-bill { color: #3498db; font-weight: bold; font-size: 15px;}
    .amt-net { color: #27ae60; font-weight: bold; font-size: 15px; } 
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="clean-box">
            <h4 class="sec-title" id="form_title"><i class="fa fa-plus-circle"></i> Create New Bill</h4>
            <form method="POST" action="{{ route('client-bills.store') }}" id="billForm">
                @csrf
                <input type="hidden" name="edit_id" id="edit_id" value="">
                <input type="hidden" name="project_name" id="project_name" value="">
                <input type="hidden" name="company_name" id="company_name" value="">
                <input type="hidden" name="actual_cost" id="actual_cost" value="0">
                
                <div class="form-group" id="client_select_group">
                    <label>Select Client <span class="text-danger">*</span></label>
                    <select class="form-control select2" id="client_select" style="width:100%;">
                        <option value="">-- Choose a Client --</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->company_name }}">{{ $c->company_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" id="project_select_group">
                    <label>Select Project <span class="text-danger">*</span></label>
                    <select class="form-control select2" name="project_id" id="project_id" style="width:100%;" disabled>
                        <option value="">-- Select Client First --</option>
                    </select>
                </div>

                <div id="edit_display_group" style="display:none; background:#f9f9f9; padding:12px; border-radius:5px; margin-bottom:15px; border-left:4px solid #f39c12;">
                    <p style="margin:0;"><b>Client:</b> <span id="disp_comp_name" class="text-primary"></span></p>
                    <p style="margin:0;"><b>Project:</b> <span id="disp_proj_name" class="text-success"></span></p>
                </div>

                <div id="breakdown_area"></div>
                <div id="warning_msg" style="display:none; margin-bottom: 15px;"></div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Bill Stage <span class="text-danger">*</span></label>
                            <select class="form-control" name="bill_stage" id="bill_stage" required>
                                <option value="Running Bill">Running Bill</option>
                                <option value="Final Bill">Final Bill</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Bill Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="date" id="date" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Bill Amount (৳) <span class="text-danger">*</span></label>
                    <input type="number" step="any" class="form-control input-lg" style="font-weight:bold; color:#3498db;" name="bill_amount" id="bill_amount" placeholder="0.00" required>
                </div>

                <div class="form-group" id="discount_group" style="display:none;">
                    <label>Discount Amount (৳)</label>
                    <input type="number" step="any" class="form-control" style="font-weight:bold; color:#e67e22;" name="discount" id="discount" value="0">
                </div>

                <div class="form-group" id="net_amount_group" style="display:none; background: #e8f8f5; padding: 10px; border-radius: 5px; border: 1px dashed #2ecc71;">
                    <label style="color:#27ae60; margin:0;">Final Net Bill Amount:</label>
                    <h3 style="margin:5px 0 0 0; color:#27ae60; font-weight:bold;" id="net_amount_display">৳ 0.00</h3>
                </div>

                <div class="form-group">
                    <label>Note / Remarks</label>
                    <textarea class="form-control" name="note" id="note" rows="2" placeholder="e.g. 1st Running Bill / Final Settlement"></textarea>
                </div>

                <div class="text-right" style="margin-top:20px;">
                    <button type="button" class="btn btn-default" onclick="resetForm()"><i class="fa fa-refresh"></i> Reset</button>
                    <button type="submit" name="save_bill" id="btn_save" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Submit Bill</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-list"></i> Generated Client Bills</h3>
            </div>
            <div class="box-body table-responsive">
                @php
                    $finalProjects = $bills->filter(function($item) {
                        return str_contains($item->note, '[Final Bill]');
                    })->pluck('project_name')->toArray();
                @endphp
                <table class="table table-bordered table-striped" id="example1">
                    <thead class="bg-primary">
                        <tr>
                            <th>Bill No.</th>
                            <th>Date</th>
                            <th>Client Name</th>
                            <th>Project Name</th>
                            <th>Stage</th>
                            <th class="text-right">Total Cost</th>
                            <th class="text-right">Bill Amt.</th>
                            <th class="text-right">Discount</th>
                            <th class="text-right">Net Bill</th> 
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bills as $b)
                            @php
                                $stage = 'Bill';
                                $cleanNote = $b->note ?? '';
                                $badgeClass = 'label-default';
                                if (preg_match('/\[(.*?)\]/', $b->note ?? '', $match)) {
                                    $stage = $match[1];
                                    $cleanNote = trim(str_replace('['.$stage.']', '', $b->note));
                                    if ($stage == 'Running Bill') $badgeClass = 'label-warning';
                                    if ($stage == 'Final Bill') $badgeClass = 'label-success';
                                }

                                $isFinal = ($stage == 'Final Bill');
                                $hasFinalBill = in_array($b->project_name, $finalProjects);
                                $discountVal = (float)($b->discount ?? 0);
                                $billVal = (float)$b->bill_amount;
                                $netVal = ((float)($b->net_amount ?? 0) > 0) ? (float)$b->net_amount : ($billVal - $discountVal);
                            @endphp
                            <tr>
                                <td><span class="label label-primary" style="font-size:13px;">#{{ str_pad($b->id, 5, '0', STR_PAD_LEFT) }}</span></td>
                                <td class="text-muted">{{ date('d-M-Y', strtotime($b->date)) }}</td>
                                <td style="color:#555; font-weight:600;">{{ $b->company_name }}</td>
                                <td><b style="color:#333; font-size:13px;">{{ $b->project_name }}</b></td>
                                <td><span class="label {{ $badgeClass }}">{{ $stage }}</span></td>
                                <td class="text-right" style="font-weight:600; color:#c0392b;">৳ {{ number_format((float)$b->amount, 2) }}</td>
                                <td class="text-right" style="font-weight:600; color:#2980b9;">৳ {{ number_format($billVal, 2) }}</td>
                                <td class="text-right text-orange">৳ {{ number_format($discountVal, 2) }}</td>
                                <td class="text-right" style="font-weight:bold; color:#27ae60;">৳ {{ number_format($netVal, 2) }}</td>
                                <td class="text-center">
                                    @if($hasFinalBill && !$isFinal)
                                        <button class="btn btn-xs btn-default" style="margin-bottom:3px;" title="Locked. Final Bill already created." disabled>
                                            <i class="fa fa-lock"></i>
                                        </button>
                                        <a href="{{ route('client-bills.show', $b->id) }}" class="btn btn-xs btn-info" style="margin-bottom:3px;" title="View Details"><i class="fa fa-eye"></i> View</a>
                                        <a href="{{ route('client-bills.show', $b->id) }}?print=1" target="_blank" class="btn btn-xs btn-success" style="margin-bottom:3px;" title="Print Bill"><i class="fa fa-print"></i> Print</a>
                                        <button class="btn btn-xs btn-default" style="margin-bottom:3px;" disabled><i class="fa fa-trash"></i></button>
                                    @else
                                        <button type="button" class="btn btn-xs btn-warning edit-btn" style="margin-bottom:3px;"
                                            data-id="{{ $b->id }}" 
                                            data-proj="{{ $b->project_name }}"
                                            data-comp="{{ $b->company_name }}"
                                            data-date="{{ $b->date }}"
                                            data-cost="{{ $b->amount }}"
                                            data-bill="{{ $billVal }}"
                                            data-stage="{{ $stage }}"
                                            data-note="{{ $cleanNote }}"
                                            data-discount="{{ $discountVal }}"
                                            title="Edit Bill">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <a href="{{ route('client-bills.show', $b->id) }}" class="btn btn-xs btn-info" style="margin-bottom:3px;" title="View Details"><i class="fa fa-eye"></i> View</a>
                                        <a href="{{ route('client-bills.show', $b->id) }}?print=1" target="_blank" class="btn btn-xs btn-success" style="margin-bottom:3px;" title="Print Bill"><i class="fa fa-print"></i> Print</a>
                                        <form action="{{ route('client-bills.destroy', $b->id) }}" method="POST" style="display:inline-block; margin-bottom:3px;" onsubmit="return confirm('Are you sure you want to delete this bill?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger" title="Delete Bill"><i class="fa fa-trash"></i></button>
                                        </form>
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

    var ajaxUrl = "{{ route('client-bills.index') }}";

    function calculateNet(){
        var bill = parseFloat($('#bill_amount').val()) || 0;
        var discount = parseFloat($('#discount').val()) || 0;
        var net = bill - discount;
        if(net < 0) net = 0;
        $('#net_amount_display').text('৳ ' + net.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    }

    $('#bill_amount, #discount').on('input', function(){
        calculateNet();
    });

    $('#bill_stage').on('change', function(){
        if($(this).val() == 'Final Bill'){
            $('#discount_group').slideDown();
            $('#net_amount_group').slideDown();
        } else {
            $('#discount_group').slideUp();
            $('#net_amount_group').slideUp();
            $('#discount').val('0');
        }
        calculateNet();
    });

    $('#client_select').on('change', function(){
        var client_name = $(this).val();
        $('#project_id').html('<option value="">-- Loading Projects... --</option>').prop('disabled', true);
        $('#breakdown_area').html('');
        $('#warning_msg').hide();
        $('#btn_save').prop('disabled', false);
        $('#bill_amount').prop('readonly', false).val('');
        $('#form_title').html('<i class="fa fa-plus-circle"></i> Create New Bill');
        $('#btn_save').html('<i class="fa fa-paper-plane"></i> Submit Bill').removeClass('btn-warning').addClass('btn-primary');

        if(client_name != ''){
            $.ajax({
                type: 'POST',
                url: ajaxUrl,
                data: { action: 'get_projects_by_client', client_name: client_name },
                dataType: 'json',
                success: function(response){
                    if(response.status == 'success'){
                        $('#project_id').html(response.html).prop('disabled', false);
                    }
                }
            });
        }
    });

    $('#project_id').on('change', function(){
        var p_id = $(this).val();
        if(p_id == '' || p_id == null) {
            $('#project_name').val('');
            $('#company_name').val('');
            $('#actual_cost').val('0');
            $('#breakdown_area').html('');
            $('#warning_msg').hide();
            $('#btn_save').prop('disabled', false);
            $('#bill_amount').prop('readonly', false).val('');
            $('#form_title').html('<i class="fa fa-plus-circle"></i> Create New Bill');
            $('#btn_save').html('<i class="fa fa-paper-plane"></i> Submit Bill').removeClass('btn-warning').addClass('btn-primary');
            calculateNet();
            return;
        }

        $('#breakdown_area').html('<div class="text-center text-primary" style="padding:20px; border: 1px dashed #3498db; border-radius:5px; background: #f8fbff;"><i class="fa fa-spinner fa-spin fa-2x"></i><br><b style="margin-top:10px; display:block;">Calculating Data...</b></div>');
        
        $('#btn_save').prop('disabled', false);
        $('#bill_amount').prop('readonly', false);

        $.ajax({
            type: 'POST',
            url: ajaxUrl,
            data: { action: 'get_project_data', project_id: p_id },
            dataType: 'json',
            success: function(response){
                if(response.status == 'success'){
                    $('#project_name').val(response.project_name);
                    $('#company_name').val(response.company_name);
                    $('#actual_cost').val(response.actual_cost);
                    $('#breakdown_area').html(response.breakdown_html).hide().fadeIn('fast');
                    
                    if(response.actual_cost <= 0 && response.final_exists == false){
                        $('#warning_msg').html('<div class="alert alert-danger" style="padding:12px; font-size:13px; margin:0;"><i class="fa fa-ban"></i> <b>Action Required:</b> No expenses have been recorded for this project yet. You must add project costs/expenses before generating a bill.</div>').fadeIn();
                        $('#btn_save').prop('disabled', true); 
                        $('#bill_amount').val('').prop('readonly', true); 
                        $('#form_title').html('<i class="fa fa-lock text-danger"></i> Billing Blocked');
                        calculateNet();
                    } 
                    else if (response.final_exists == true) {
                        $('#warning_msg').html('<div class="alert alert-danger" style="padding:12px; font-size:13px; margin:0;"><i class="fa fa-lock"></i> <b>Final Bill Generated!</b> A Final Bill already exists for this project. You cannot create new bills. Please edit the Final Bill from the table below.</div>').fadeIn();
                        $('#btn_save').prop('disabled', true); 
                        $('#bill_amount').val('').prop('readonly', true); 
                        $('#form_title').html('<i class="fa fa-lock text-danger"></i> Billing Blocked');
                        calculateNet();
                    }
                    else {
                        $('#btn_save').prop('disabled', false);
                        $('#bill_amount').prop('readonly', false);
                        $('#edit_id').val('');
                        
                        if($('#bill_amount').val() == '' || $('#warning_msg').is(':visible')) {
                            $('#bill_amount').val(response.actual_cost);
                        }
                        
                        $('#bill_stage').html('<option value="Running Bill">Running Bill</option><option value="Final Bill">Final Bill</option>');
                        $('#discount_group').hide();
                        $('#net_amount_group').hide();
                        $('#discount').val('0');
                        calculateNet();
                        
                        $('#warning_msg').hide();
                        $('#form_title').html('<i class="fa fa-plus-circle"></i> Create New Bill');
                        $('#btn_save').html('<i class="fa fa-paper-plane"></i> Submit Bill').removeClass('btn-warning').addClass('btn-primary');
                    }

                } else {
                    $('#breakdown_area').html('<div class="text-danger text-center" style="padding:15px; background:#f2dede; border-radius:5px;"><b>Error:</b> Could not fetch data.</div>');
                }
            },
            error: function() {
                $('#breakdown_area').html('<div class="text-danger" style="padding:15px; border:1px solid #ebccd1; background:#f2dede; border-radius:5px;"><b>Data Load Failed!</b></div>');
            }
        });
    });

    $('.edit-btn').on('click', function(){
        var id = $(this).data('id');
        var proj = $(this).data('proj');
        var comp = $(this).data('comp');
        
        $('#btn_save').prop('disabled', false);
        $('#bill_amount').prop('readonly', false);

        $('#edit_id').val(id);
        $('#project_name').val(proj);
        $('#company_name').val(comp);
        $('#date').val($(this).data('date'));
        $('#actual_cost').val($(this).data('cost'));
        $('#bill_amount').val($(this).data('bill'));
        
        var stage = $(this).data('stage');
        var discount_val = $(this).data('discount') || 0;
        
        if(stage == 'Final Bill') {
            $('#bill_stage').html('<option value="Final Bill">Final Bill</option>');
            $('#discount_group').show();
            $('#net_amount_group').show();
            $('#discount').val(discount_val);
        } else {
            $('#bill_stage').html('<option value="Running Bill">Running Bill</option><option value="Final Bill">Final Bill</option>');
            $('#bill_stage').val('Running Bill'); 
            $('#discount_group').hide();
            $('#net_amount_group').hide();
            $('#discount').val('0');
        }
        
        calculateNet();
        $('#note').val($(this).data('note'));

        $('#form_title').html('<i class="fa fa-edit text-warning"></i> Edit Existing Bill');
        $('#btn_save').html('<i class="fa fa-check"></i> Update Bill').removeClass('btn-primary').addClass('btn-warning');
        
        $('#client_select_group').hide();
        $('#project_select_group').hide();
        $('#breakdown_area').html(''); 
        $('#warning_msg').hide();
        
        $('#disp_proj_name').text(proj);
        $('#disp_comp_name').text(comp);
        $('#edit_display_group').fadeIn();
        
        $('html, body').animate({ scrollTop: 0 }, 'fast');
    });

    if($.fn.DataTable && !$.fn.DataTable.isDataTable('#example1')) { 
        $('#example1').DataTable({ "order": [] }); 
    }
});

function resetForm(){
    $('#billForm')[0].reset();
    $('#edit_id').val('');
    $('#project_name').val('');
    $('#company_name').val('');
    $('#actual_cost').val('0');
    
    $('#btn_save').prop('disabled', false);
    $('#bill_amount').prop('readonly', false);
    
    $('#form_title').html('<i class="fa fa-plus-circle"></i> Create New Bill');
    $('#btn_save').html('<i class="fa fa-paper-plane"></i> Submit Bill').removeClass('btn-warning').addClass('btn-primary');
    
    $('#edit_display_group').hide();
    $('#breakdown_area').html('');
    $('#warning_msg').hide();
    
    $('#client_select_group').fadeIn();
    $('#project_select_group').fadeIn();
    
    $('#bill_stage').html('<option value="Running Bill">Running Bill</option><option value="Final Bill">Final Bill</option>');
    $('#discount_group').hide();
    $('#net_amount_group').hide();
    $('#discount').val('0');
    $('#net_amount_display').text('৳ 0.00'); 
    
    $('#client_select').val('').trigger('change.select2');
    $('#project_id').html('<option value="">-- Select Client First --</option>').val('').trigger('change.select2').prop('disabled', true);
}
</script>
@endpush
