<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modell extends Model
{
    use HasFactory;
    protected $guarded = [];

    
    protected $table = 'modeller';
    public $timestamps = false;

     // Marka ilişkisi
    public function marka()
    {
        return $this->belongsTo(Marka::class, 'mid');
    }
    
    // Arıza kodları ilişkisi
    public function arizaKodlari()
    {
        return $this->hasMany(ArizaKodu::class, 'model_id');
    }
}
