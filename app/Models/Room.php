<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function images()
    {
        return $this->hasMany(RoomImage::class, 'room_id', 'id');
    }

    public function categori()
    {
        return $this->belongsTo(Category::class, 'category', 'id');
    }
}
