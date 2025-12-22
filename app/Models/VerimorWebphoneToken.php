<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerimorWebphoneToken extends Model
{
    use HasFactory;
     protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Token geçerli mi?
     */
    public function isValid()
    {
        return $this->expires_at && $this->expires_at->isFuture();
    }

    /**
     * Süresi dolmuş token'ları temizle (cronjob için)
     */
    public static function cleanExpired()
    {
        return self::where('expires_at', '<', now())->delete();
    }
}
