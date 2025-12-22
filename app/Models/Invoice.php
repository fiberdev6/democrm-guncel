<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'parasutPaymentIds' => 'array',
        'has_payment' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'musteriid', 'id');
    }

    public function invoice_products()
    {
        return $this->hasMany(InvoiceProduct::class, 'faturaid');
    }
}
