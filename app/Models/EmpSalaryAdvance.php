<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpSalaryAdvance extends Model
{
    protected $table = 'emp_salary_advance';
    public $timestamps = false;
    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'method_id', 'id');
    }
}
