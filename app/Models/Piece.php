<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Piece extends Model
{
    protected $guarded = [];

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class , 'invoice_pieces');
    }
}