<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class Survey extends Model
{
    use HasFactory;
    protected $guarded = [];

    //Anketi ekleyen kullanıcı 
    public function ekleyenUser()
    {
        return $this->belongsTo(User::class, 'ekleyen', 'user_id');
    }
  
    //Anketi yapılan personel
    public function personelUser()
    {
        return $this->belongsTo(User::class, 'personel', 'user_id');
    }
 
    //Anketin bağlı olduğu servis 
    public function servis()
    {
        return $this->belongsTo(Service::class, 'servisid', 'id');
    }
    public function bayiUser() 
    {
        return $this->belongsTo(User::class, 'bayi', 'user_id');
    }

}
