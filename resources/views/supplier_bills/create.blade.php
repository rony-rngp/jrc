@extends('layouts.master')

@section('title', 'New Supplier Bill - JRC ERP')
@section('page_title', 'Create Purchase / Supplier Bill')

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <a href="{{ route('supplier-bills.index') }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back to Bills</a>
            </div>
            <form method="POST" action="{{ route('supplier-bills.store') }}" id="billForm">
                @csrf
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Voucher No</label>
                                <input type="text" class="form-control" name="voucher_no" value="{{ $nextVoucher }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Supplier / Party *</label>
                                <select class="form-control" name="supplier_id" required>
                                    <option value="">- Select Supplier -</option>
                                    @foreach($suppliers as $s)
                                        <option value="{{ $s->party_name }}">{{ $s->party_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Project</label>
                                <select class="form-control" name="project_id">
                                    <option value="0">None / General</option>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}">{{ $p->project_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Bill Date *</label>
                                <input type="date" class="form-control" name="bill_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h4><b>Items / Materials</b></h4>
                    <table class="table table-bordered" id="itemsTable">
                        <thead>
                            <tr class="bg-gray">
                                <th width="40%">Product / Material Category</th>
                                <th width="20%">Quantity</th>
                                <th width="20%">Unit Price</th>
                                <th width="15%">Total</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select class="form-control product_select" name="items[0][product_id]" required>
                                        <option value="">- Select Item -</option>
                                        @foreach($products as $prod)
                                            <option value="{{ $prod->id }}">{{ $prod->cat_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" step="any" class="form-control qty" name="items[0][qty]" value="1" required></td>
                                <td><input type="number" step="any" class="form-control unit_price" name="items[0][unit_price]" value="0" required></td>
                                <td><input type="text" class="form-control total_price" value="0.00" readonly></td>
                                <td><button type="button" class="btn btn-danger btn-sm removeRow"><i class="fa fa-trash"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success btn-sm" id="addRow"><i class="fa fa-plus"></i> Add Another Row</button>

                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Notes / Remarks</label>
                                <textarea class="form-control" name="note" rows="4" placeholder="Additional notes..."></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Grand Total</label>
                                <input type="text" class="form-control input-lg" id="grand_total" value="0.00" readonly style="font-weight:bold; color: #2563eb;">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Paid Amount</label>
                                        <input type="number" step="any" class="form-control" name="paid_amount" id="paid_amount" value="0.00">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Payment Method</label>
                                        <select class="form-control" name="method_name">
                                            @foreach($paymentMethods as $pm)
                                                <option value="{{ $pm->method_name }}">{{ $pm->method_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer text-right">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fa fa-save"></i> Save & Generate Bill</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let rowIndex = 1;

function calculateTotals() {
    let grand = 0;
    $('#itemsTable tbody tr').each(function() {
        let qty = parseFloat($(this).find('.qty').val()) || 0;
        let rate = parseFloat($(this).find('.unit_price').val()) || 0;
        let lineTotal = qty * rate;
        $(this).find('.total_price').val(lineTotal.toFixed(2));
        grand += lineTotal;
    });
    $('#grand_total').val(grand.toFixed(2));
}

$('#itemsTable').on('input', '.qty, .unit_price', function() {
    calculateTotals();
});

$('#addRow').click(function() {
    let rowHtml = `<tr>
        <td>
            <select class="form-control product_select" name="items[${rowIndex}][product_id]" required>
                <option value="">- Select Item -</option>
                @foreach($products as $prod)
                    <option value="{{ $prod->id }}">{{ $prod->cat_name }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" step="any" class="form-control qty" name="items[${rowIndex}][qty]" value="1" required></td>
        <td><input type="number" step="any" class="form-control unit_price" name="items[${rowIndex}][unit_price]" value="0" required></td>
        <td><input type="text" class="form-control total_price" value="0.00" readonly></td>
        <td><button type="button" class="btn btn-danger btn-sm removeRow"><i class="fa fa-trash"></i></button></td>
    </tr>`;
    $('#itemsTable tbody').append(rowHtml);
    rowIndex++;
});

$('#itemsTable').on('click', '.removeRow', function() {
    if($('#itemsTable tbody tr').length > 1) {
        $(this).closest('tr').remove();
        calculateTotals();
    }
});
</script>
@endpush
