<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employees';
    public $timestamps = false;
    protected $guarded = [];

    public function getFullNameAttribute()
    {
        return trim($this->firstname . ' ' . $this->lastname);
    }
}
