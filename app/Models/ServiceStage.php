<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceStage extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Belirli bir aşamanın alt aşamalarını getir
    public function nextStages()
    {
        $ids = explode(',', $this->altAsamalar);
        return self::whereIn('id', $ids)->get();
    }

    // Firma ilişkilendirme
    public function company()
    {
        return $this->belongsTo(Tenant::class, 'firma_id', 'id');
    }
}
