<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuperAdminInvoice extends Model
{
    
    use HasFactory;

    protected $table = 'super_admin_invoices';
    protected $guarded = [];

    // Firma bilgilerini getir
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'firma_id', 'id');
    }

    // Fatura ürünlerini getir
    public function invoice_products()
    {
        return $this->hasMany(SuperAdminInvoiceProduct::class, 'faturaid');
    }

    // Kaydı oluşturan admin kullanıcısı
    public function admin()
    {
        return $this->belongsTo(User::class, 'kayitAlan', 'id');
    }

    public function payment()
    {
        if ($this->payment_type === 'subscription') {
            return $this->belongsTo(SubscriptionPayment::class, 'payment_id');
        } elseif ($this->payment_type === 'storage') {
            return $this->belongsTo(StoragePurchase::class, 'payment_id');
        }
        return null;
    }

    // Scope'lar
    public function scopeActive($query)
    {
        return $query->where('durum', 1);
    }

    public function scopeByTenant($query, $tenantId)
    {
        return $query->where('firma_id', $tenantId);
    }

    public function scopeByPaymentType($query, $paymentType)
    {
        return $query->where('payment_type', $paymentType);
    }
}
