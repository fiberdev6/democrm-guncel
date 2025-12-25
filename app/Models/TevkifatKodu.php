<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TevkifatKodu extends Model
{
    use HasFactory;
    protected $guarded = [];
    
    protected $table = 'tevkifat_kodlari';
    public $timestamps = false;
}
