<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function servisler()
    {
        return $this->belongsTo(Service::class, 'servis', 'id');
    }

    public function perso_nel()
    {
        return $this->belongsTo(User::class, 'personel', 'user_id');
    }

    public function islem_yapan()
    {
        return $this->belongsTo(User::class, 'pid', 'user_id');
    }

    public function odemeturu()
    {
        return $this->belongsTo(PaymentType::class, 'odemeTuru', 'id');
    }

    public function payment_method()
    {
        return $this->belongsTo(PaymentMethod::class, 'odemeSekli', 'id');
    }

    public function stok_hareket() 
    {
        return $this->belongsTo(StockAction::class, 'stokIslem', 'id');
    }

}
