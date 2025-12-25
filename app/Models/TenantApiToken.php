<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TenantApiToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'token',
        'abilities',
        'is_active',
        'last_used_at'
    ];

    protected $casts = [
        'abilities' => 'array',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    protected $hidden = [
        'token',
    ];

    /**
     * Tenant ilişkisi
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * Yeni token oluştur
     */
    public static function generateToken()
    {
        return hash('sha256', Str::random(60));
    }

    /**
     * Token'ı hashle
     */
    public static function hashToken($plainTextToken)
    {
        return hash('sha256', $plainTextToken);
    }

    /**
     * Son kullanım tarihini güncelle
     */
    public function updateLastUsed()
    {
        $this->last_used_at = now();
        $this->save();
    }
}
