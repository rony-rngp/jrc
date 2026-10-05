<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierBill extends Model
{
    protected $table = 'supplier_bills';
    public $timestamps = false;
    protected $guarded = [];

    public function supplier()
    {
        return $this->belongsTo(Party::class, 'supplier_id', 'id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(SupplierBillItem::class, 'bill_id', 'id');
    }

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class, 'bill_id', 'id');
    }
}
