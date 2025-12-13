<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $guarded = [];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    // علاقة: القسم له مدير واحد
public function manager()
{
    return $this->belongsTo(Employee::class, 'manager_id');
}
}