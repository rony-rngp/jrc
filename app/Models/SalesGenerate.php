<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesGenerate extends Model
{
    use HasFactory;

    protected $table = 'tbl_sales_generate';
    public $timestamps = false;

    protected $fillable = [
        'company_name',
        'project_name',
        'amount',
        'bill_amount',
        'discount',
        'net_amount',
        'date',
        'note',
        'payment_status',
    ];
}
