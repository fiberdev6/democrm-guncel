<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalContent extends Model
{
    use HasFactory;
    protected $fillable = ['type', 'content'];
    
    public static function getTerms()
    {
        return self::where('type', 'terms')->first()?->content ?? '';
    }
    
    public static function getPrivacy()
    {
        return self::where('type', 'privacy')->first()?->content ?? '';
    }
}
