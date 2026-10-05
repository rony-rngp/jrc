<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BalanceTransferHistory extends Model
{
    protected $table = 'balance_transfer_history';
    public $timestamps = false;
    protected $guarded = [];
}
