<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuperAdminInvoiceProduct extends Model
{
     use HasFactory;

    protected $table = 'super_admin_invoice_products';
    protected $guarded = [];

    // Ana faturaya ait olduğunu belirt
    public function invoice()
    {
        return $this->belongsTo(SuperAdminInvoice::class, 'faturaid');
    }
}
