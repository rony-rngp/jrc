<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountHead extends Model
{
    protected $table = 'account_heads';
    public $timestamps = false;
    protected $guarded = [];

    public function parent()
    {
        return $this->belongsTo(AccountHead::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(AccountHead::class, 'parent_id')->orderBy('head_name', 'asc');
    }
}
