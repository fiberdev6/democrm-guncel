<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KdvKodu extends Model
{
    use HasFactory;
    protected $guarded = [];
    
    protected $table = 'kdv_kodlari';
    public $timestamps = false;
}
