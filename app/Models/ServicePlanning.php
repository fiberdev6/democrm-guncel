<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePlanning extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'pid', 'user_id');
    }
    
    public function serviceStage()
    {
        return $this->belongsTo(ServiceStage::class, 'gidenIslem');
    }
    
    public function answers()
    {
        return $this->hasMany(ServiceStageAnswer::class, 'planid');
    }
     public function service()
    {
        return $this->belongsTo(Service::class, 'servisid');
    }
    

}
