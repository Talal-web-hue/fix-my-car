<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }



    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // علاقة: الموظف (إذا كان مديراً) يُدير قسماً واحداً
public function managedDepartment()
{
    return $this->hasOne(Department::class, 'manager_id');
}
}