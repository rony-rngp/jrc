<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentReceivedProject extends Model
{
    protected $table = 'payment_received_project';
    public $timestamps = false;
    protected $guarded = [];

    public function method()
    {
        return $this->belongsTo(PaymentMethod::class, 'method_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'method_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'company_name', 'company_name');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_name', 'project_name');
    }
}
