<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentReceivedInfo extends Model
{
    protected $table = 'payment_received_info';
    public $timestamps = false;
    protected $guarded = [];
}
