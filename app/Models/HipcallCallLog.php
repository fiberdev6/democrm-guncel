<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HipcallCallLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'raw_data' => 'array',
        'started_at' => 'datetime',
        'ended_at' => 'datetime'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Arama süresini formatla
    public function getFormattedDurationAttribute()
    {
        if (!$this->call_duration) return '0s';
        
        $minutes = floor($this->call_duration / 60);
        $seconds = $this->call_duration % 60;
        
        if ($minutes > 0) {
            return "{$minutes}dk {$seconds}s";
        }
        return "{$seconds}s";
    }
}
