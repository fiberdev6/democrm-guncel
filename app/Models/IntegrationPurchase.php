<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'integration_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'transaction_id',
        'gateway',
        'payment_response',
        'invoice_path',
        'paid_at',
        'credentials',
        'settings',
        'is_active',
        'activated_at',
        'tokenPayment',
        'webhook_token',
        'webhook_url',
        'is_default',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_response' => 'array',
        'credentials' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'paid_at' => 'datetime',
        'activated_at' => 'datetime',
    ];

    // İlişkiler
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function integration()
    {
        return $this->belongsTo(Integration::class);
    }

    // Scope'lar
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('status', 'completed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Metodlar
    public function isExpired()
    {
        if (!$this->expires_at) {
            return false;
        }
        return $this->expires_at->isPast();
    }

    public function isActive()
    {
        return $this->is_active && 
               $this->status === 'completed' && 
               !$this->isExpired();
    }

    public function activate()
    {
        $this->update([
            'is_active' => true,
            'activated_at' => now(),
            'status' => 'completed'
        ]);
    }

    public function deactivate()
    {
        $this->update([
            'is_active' => false
        ]);
    }
}
