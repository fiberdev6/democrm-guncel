<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    use HasFactory;

     protected $fillable = [
        'ticket_number',
        'tenant_id',
        'user_id',
        'category',
        'subject',
        'priority',
        'description',
        'attachments',
        'status',
        'last_reply_at'
    ];

    protected $casts = [
        'attachments' => 'array',
        'last_reply_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // İlişkiler
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function replies()
    {
        return $this->hasMany(SupportTicketReply::class)->orderBy('created_at', 'asc');
    }

    // Scope'lar
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'acik');
    }

    public function scopeAnswered($query)
    {
        return $query->where('status', 'cevaplandi');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'kapali');
    }

    // Accessor'lar
    public function getPriorityTextAttribute()
    {
        switch ($this->priority) {
            case 'acil':
                return 'Acil';
            case 'kritik':
                return 'Kritik';
            case 'yuksek':
                return 'Yüksek';
            case 'orta':
                return 'Orta';
            case 'dusuk':
                return 'Düşük';
            default:
                return ucfirst($this->priority); // Tanımlanmamışsa ilk harfini büyük yap
        }
    }
    public function getStatusTextAttribute()
    {
        $statuses = [
            'acik' => 'Açık',
            'cevaplandi' => 'Cevaplandı',
            'kapali' => 'Kapalı'
        ];
        
        return $statuses[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'acik' => 'warning',
            'cevaplandi' => 'info',
            'kapali' => 'success'
        ];
        
        return $colors[$this->status] ?? 'secondary';
    }

    // Helper metodlar
    public static function generateTicketNumber()
    {
        do {
            $number = 'TKT-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('ticket_number', $number)->exists());
        
        return $number;
    }

    public function canBeReplied()
    {
        return $this->status !== 'kapali';
    }

    public function markAsAnswered()
    {
        $this->update([
            'status' => 'cevaplandi',
            'last_reply_at' => now()
        ]);
    }

    public function close()
    {
        $this->update([
            'status' => 'kapali'
        ]);
    }
}
