<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class stock_photos extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function stock(){
            return $this->belongsTo(Stock::class);
    }

}
