<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomingCall extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function serviskaynak()
    {
        return $this->belongsTo(ServiceResource::class, 'servisKaynak', 'id');
    }

    public function brand()
    {
        return $this->belongsTo(DeviceBrand::class, 'marka', 'id');
    }

    public function kayit_alan()
    {
        return $this->belongsTo(User::class, 'kayitAlan', 'user_id');
    }
}
