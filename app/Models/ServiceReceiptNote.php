<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceReceiptNote extends Model
{
    use HasFactory;

    protected $guarded = [];

     public function personel()
    {
        return $this->belongsTo(User::class, 'kid', 'user_id');
    }

     public function servis()
    {
        return $this->belongsTo(Service::class, 'servisid', 'id');
    }
}
