<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marka extends Model
{
    use HasFactory;
    protected $guarded = [];
    
    protected $table = 'markalar';
    public $timestamps = false;

     // Modeller ilişkisi
    public function modeller()
    {
        return $this->hasMany(Modell::class, 'mid');
    }
    
    // Arıza kodları ilişkisi
    public function arizaKodlari()
    {
        return $this->hasMany(ArizaKodu::class, 'marka_id');
    }
}
