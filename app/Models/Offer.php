<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function musteri()
    {
        return $this->belongsTo(Customer::class, 'musteri_id', 'id');
    }
}
