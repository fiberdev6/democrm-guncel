<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\StockShelf;
use App\Models\DeviceBrand;
use App\Models\DeviceType;
use App\Models\StockAction;
use App\Models\stockphotos;
use App\Models\StockCategory;
use App\Models\PersonelStock;


class Stock extends Model
{
    use HasFactory;

            public function raf() {
                return $this->belongsTo(StockShelf::class, 'urunDepo', 'id'); 
            }

            public function marka() {
                return $this->belongsTo(DeviceBrand::class, 'stok_marka', 'id');
            }

            public function cihaz() {
                return $this->belongsTo(DeviceType::class, 'stok_cihaz', 'id');
            }

            public function sonHareket() {
                return $this->hasOne(StockAction::class, 'stokId', 'id')->latest();
            }

            public function hareketler()
            {
                return $this->hasMany(StockAction::class, 'stokId', 'id');
            }
            public function kalanStok()
            {
                $toplamGiris = $this->hareketler()->where('islem', 1)->sum('adet');
                $toplamCikis = $this->hareketler()->whereIn('islem', [2, 3])->sum('adet');
                return $toplamGiris - $toplamCikis;
            }

            public function personel() {
                return $this->belongsTo(User::class, 'pid', 'user_id');
            } 

            public function photos()
            {
                return $this->hasMany(stockphotos::class);
            }
            public function kategori()
            {
                return $this->belongsTo(StockCategory::class, 'urunKategori', 'id');
            }
            public function stockActions()
            {
                return $this->hasMany(StockAction::class, 'stokId', 'id');
            }
                public function personelStoklar()
            {
                return $this->hasMany(PersonelStock::class, 'stokid');
            }





}
