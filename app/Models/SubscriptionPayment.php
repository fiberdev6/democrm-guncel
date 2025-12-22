<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'subscription_id', 'payment_id', 'amount', 'currency',
        'status', 'payment_method', 'transaction_id', 'gateway',
        'gateway_response', 'paid_at', 'failure_reason'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at' => 'datetime'
    ];

    // İlişkiler
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription()
    {
        return $this->belongsTo(TenantSubscription::class, 'subscription_id');
    }

    public function getFormattedAmount()
    {
        return number_format($this->amount, 2, ',', '.') . ' ' . $this->currency;
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'completed', 'paid' => 'success',
            'pending', 'processing' => 'warning', 
            'failed', 'cancelled' => 'danger',
            'refunded' => 'info',
            default => 'secondary'
        };
    }

    /**
     * Ödemenin durumunu Türkçe label olarak döndürür
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'completed' => 'Tamamlandı',
            'paid' => 'Ödendi',
            'pending' => 'Beklemede',
            'processing' => 'İşleniyor',
            'failed' => 'Başarısız',
            'cancelled' => 'İptal Edildi',
            'refunded' => 'İade Edildi'
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Ödeme yöntemini düzenli hale getirir
     */
    public function getFormattedPaymentMethodAttribute()
    {
        $methods = [
            'kredi_karti' => 'Kredi Kartı',
            'banka_karti' => 'Banka Kartı', 
            'paytr' => 'PayTR',
            'iyzico' => 'Iyzico',
            'bank_transfer' => 'Banka Havalesi',
            'cash' => 'Nakit'
        ];

        return $methods[$this->payment_method] ?? ucfirst(str_replace('_', ' ', $this->payment_method));
    }

    /**
     * Faturanın var olup olmadığını kontrol eder
     */
    public function hasInvoice()
    {
        return !empty($this->invoice_path) && file_exists(storage_path('app/' . $this->invoice_path));
    }

    /**
     * Ödeme açıklamasını oluşturur
     */
    public function getDescriptionAttribute()
    {
        $description = "Abonelik Ödemesi";
        
        if ($this->subscription_id) {
            $description .= " (Abonelik #{$this->subscription_id})";
        }
        
        if ($this->transaction_id) {
            $description .= " - İşlem: {$this->transaction_id}";
        }
        
        return $description;
    }

    /**
     * Scope: Başarılı ödemeler
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', ['completed', 'paid']);
    }

    /**
     * Scope: Belirli tarih aralığı
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ]);
    }

    /**
     * Scope: Belirli tenant
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // Scope'lar
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
