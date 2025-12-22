<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAction extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function musteri()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'musteri_id');
    }

    // StockAction tablosundaki 'kid' sütunu, User tablosundaki 'user_id' sütununa bağlanır.
    public function actionPerformer()
    {
        return $this->belongsTo(\App\Models\User::class, 'pid', 'user_id');
    }
    public function aliciPersonel()
    {
        return $this->belongsTo(\App\Models\User::class, 'pid', 'user_id');
    }
    public function servis()
    {
        return $this->belongsTo(\App\Models\Service::class, 'servisid');
    }
    
    public function stok()
    {
        return $this->belongsTo(Stock::class, 'stokId', 'id');
    }


}
