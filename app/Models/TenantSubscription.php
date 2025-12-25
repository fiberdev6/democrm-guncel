<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'plan_id', 'status', 'starts_at', 'ends_at',
        'trial_ends_at', 'canceled_at', 'payment_method', 
        'external_subscription_id', 'subscription_data'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'canceled_at' => 'datetime',
        'subscription_data' => 'array'
    ];

    // İlişkiler
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plansubs()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function payments()
    {
        return $this->hasMany(SubscriptionPayment::class, 'subscription_id');
    }

    // Durum Kontrolleri
    public function isActive()
    {
        return in_array($this->status, ['trial', 'active']) && 
               $this->ends_at->isFuture();
    }

    public function isOnTrial()
    {
        return $this->status === 'trial' && 
               $this->trial_ends_at && 
               $this->trial_ends_at->isFuture();
    }

    public function isExpired()
    {
        return $this->status === 'expired' || $this->ends_at->isPast();
    }

    public function isCanceled()
    {
        return $this->status === 'canceled';
    }

    public function getRemainingDays()
    {
        if ($this->isOnTrial()) {
            return $this->trial_ends_at->diffInDays(now());
        }
        
        return $this->ends_at->diffInDays(now());
    }

    // Scope'lar
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['trial', 'active'])
                    ->where('ends_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('ends_at', '<', now())
                    ->orWhere('status', 'expired');
    }
    public function getStatusBadge()
    {
        switch ($this->status) {
            case 'active':
                return '<span class="badge bg-success"><i class="mdi mdi-check-circle"></i> Aktif</span>';
            case 'trial':
                return '<span class="badge bg-info"><i class="mdi mdi-clock"></i> Deneme</span>';
        }
    }
}
