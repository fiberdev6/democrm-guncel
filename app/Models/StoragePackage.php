<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoragePackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'storage_gb', 'price', 
        'currency', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'storage_gb' => 'decimal:2',
        'price' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function purchases()
    {
        return $this->hasMany(StoragePurchase::class);
    }

    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2) . ' ' . $this->currency;
    }
}
