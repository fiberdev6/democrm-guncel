<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArizaKodu extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $table = 'ariza_kodlari';
    public $timestamps = false;

    // Marka ilişkisi
    public function marka()
    {
        return $this->belongsTo(Marka::class, 'marka_id');
    }
    
    // Model ilişkisi
    public function model()
    {
        return $this->belongsTo(Modell::class, 'model_id');
    }
}
