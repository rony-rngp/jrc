<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierBillItem extends Model
{
    protected $table = 'supplier_bill_items';
    public $timestamps = false;
    protected $guarded = [];

    public function bill()
    {
        return $this->belongsTo(SupplierBill::class, 'bill_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(MaterialCategory::class, 'product_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(MaterialCategory::class, 'product_id', 'id');
    }
}
