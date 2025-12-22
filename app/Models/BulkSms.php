<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkSms extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function servis()
    {
        return $this->belongsTo(Service::class, 'servis_id');
    }

    public function musteri()
    {
        return $this->belongsTo(Customer::class, 'musteri_id');
    }
}
