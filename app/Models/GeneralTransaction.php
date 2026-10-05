<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralTransaction extends Model
{
    protected $table = 'general_transactions';
    public $timestamps = false;
    protected $guarded = [];

    public function accountHead()
    {
        return $this->belongsTo(AccountHead::class, 'head_id', 'id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'method_id', 'id');
    }
}
