<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServisStock extends Model
{
    use HasFactory;

    protected $guarded = [];
    public $timestamps = false;
    public function stok()
    {
        return $this->belongsTo(Stock::class, 'stokid');
    }

    public function personel()
    {
        return $this->belongsTo(User::class, 'pid');
    }
}
