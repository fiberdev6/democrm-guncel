<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoragePurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'storage_package_id', 'payment_token',
        'amount', 'storage_gb', 'status', 'payment_response',
        'purchased_at', 'expires_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'storage_gb' => 'decimal:2',
        'payment_response' => 'array',
        'purchased_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function package()
    {
        return $this->belongsTo(StoragePackage::class, 'storage_package_id');
    }
}
