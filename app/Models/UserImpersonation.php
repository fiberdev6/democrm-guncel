<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserImpersonation extends Model
{
    use HasFactory;
     protected $fillable = [
        'impersonator_id',
        'impersonated_id', 
        'tenant_id',
        'started_at',
        'ended_at',
        'ip_address',
        'reason'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    // İlişkiler
    public function impersonator()
    {
        return $this->belongsTo(User::class, 'impersonator_id', 'user_id');
    }

    public function impersonated()
    {
        return $this->belongsTo(User::class, 'impersonated_id', 'user_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    // Scope'lar
    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }

    public function scopeByTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('started_at', '>=', now()->subDays($days));
    }

    // Helper metodlar
    public function isActive()
    {
        return $this->ended_at === null;
    }

    public function getDuration()
    {
        $endTime = $this->ended_at ?? now();
        return $this->started_at->diffForHumans($endTime);
    }
}
