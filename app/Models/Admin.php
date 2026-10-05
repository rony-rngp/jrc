<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admin';

    public $timestamps = false;

    protected $fillable = [
        'username',
        'password',
        'firstname',
        'lastname',
        'photo',
        'role',
        'empID',
        'created_on',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getFullNameAttribute()
    {
        return trim($this->firstname . ' ' . $this->lastname);
    }
}
