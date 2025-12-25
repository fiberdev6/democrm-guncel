<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceMoneyAction extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Personel ilişkisi
    public function personel()
    {
        return $this->belongsTo(User::class, 'pid', 'user_id');
    }

    // Ödeme şekli ilişkisi
    public function odemeSekliRelation()
    {
        return $this->belongsTo(PaymentMethod::class, 'odemeSekli', 'id');
    }

    // Servis ilişkisi
    public function servis()
    {
        return $this->belongsTo(Service::class, 'servisid', 'id');
    }

    // Firma ilişkisi
    public function firma()
    {
        return $this->belongsTo(Tenant::class, 'firma_id', 'id');
    }
}
