<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptDesign extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'firma_id');
    }
}
