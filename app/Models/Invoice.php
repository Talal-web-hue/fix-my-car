<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $guarded = [];


    public function fixRequest()
    {
        return $this->belongsTo(fixRequest::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function pieces()
    {
        return $this->belongsToMany(Piece::class , 'invoice_pieces');
    } 
}