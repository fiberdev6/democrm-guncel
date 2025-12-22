<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function serviceMoneyActions()
    {
        return $this->hasMany(ServiceMoneyAction::class, 'odemeSekli', 'id');
    }
}
