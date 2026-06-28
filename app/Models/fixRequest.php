<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class fixRequest extends Model
{
    protected $guarded = [];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }



    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}