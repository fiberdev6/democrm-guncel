<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrontendSetting extends Model
{
    use HasFactory;
        protected $fillable = [
        'section',
        'key',
        'value',
        'data',
        'order',
        'is_active'
    ];

    protected $casts = [
        'data' => 'array',
        'is_active' => 'boolean'
    ];

    // Helper method
    public static function getSectionData($section)
    {
        return self::where('section', $section)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
    }
}
