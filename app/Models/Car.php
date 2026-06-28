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
        return $this->hasMany(fixRequest::class);
    }


    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }


    public function maintenanceHistories()
    {
        return $this->hasMany(MaintenanceHistory::class);
    }
}