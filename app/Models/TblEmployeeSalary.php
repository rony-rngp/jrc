<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TblEmployeeSalary extends Model
{
    protected $table = 'tbl_employee_salary';
    public $timestamps = false;
    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
