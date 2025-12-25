<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageContent extends Model
{
    use HasFactory;
    protected $table = 'homepage_content';
    
    protected $fillable = [
        'section',
        'content',
        'is_active'
    ];

    protected $casts = [
        'content' => 'array',
        'is_active' => 'boolean'
    ];

    // Helper method
    public static function getSection($section)
    {
        $data = self::where('section', $section)
            ->where('is_active', true)
            ->first();
            
        return $data ? $data->content : null;
    }
}
