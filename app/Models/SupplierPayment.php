<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPayment extends Model
{
    protected $table = 'supplier_payments';
    public $timestamps = false;
    protected $guarded = [];

    public function supplier()
    {
        return $this->belongsTo(Party::class, 'supplier_id');
    }

    public function party()
    {
        return $this->belongsTo(Party::class, 'supplier_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function bill()
    {
        return $this->belongsTo(SupplierBill::class, 'bill_id');
    }

    public function method()
    {
        return $this->belongsTo(PaymentMethod::class, 'method_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'method_id');
    }
}
