<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{

    protected $guarded =[];  // مشان يسمحلي بإدخال البيانات إالى جميع الحقول
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fixRequests()
    {
        return $this->hasMnay(fixRequest::class);
    }
}