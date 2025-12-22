<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Cache;

class AdminSupportController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->isSuperAdmin()) {
                abort(403, 'Bu sayfaya erişim yetkiniz bulunmamaktadır.');
            }
            return $next($request);
        });
    }

    // Super admin - tüm destek talepleri
    public function index(Request $request)
    {
       
        logger('AdminSupportController index() çalışıyor', [
            'user' => Auth::user()->name,
            'is_super_admin' => Auth::user()->isSuperAdmin()
        ]);

        $query = SupportTicket::with(['user', 'tenant']);

        // Filtreler
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->orderBy('last_reply_at', 'desc')->get();
        $tenants = Tenant::select('id', 'firma_adi')->orderBy('firma_adi')->get();
        
        $categories = [
            'teknik_sorun' => 'Teknik Sorun',
            'faturalandirma' => 'Faturalandırma',
            'ozellik_talebi' => 'Özellik Talebi',
            'genel_destek' => 'Genel Destek',
            'hesap_sorunu' => 'Hesap Sorunu'
        ];
         $priorities = [
        'acil' => 'Acil',
        'kritik' => 'Kritik',
        'yuksek' => 'Yüksek',
        'orta' => 'Orta',
        'dusuk' => 'Düşük'
    ];


        return view('frontend.secure.super_admin.support.index', compact('tickets', 'tenants', 'categories','priorities'));
    }

    // Super admin - destek talebi detay
    public function show(SupportTicket $ticket)
    {
        $ticket->load('replies.user', 'user', 'tenant');
        return view('frontend.secure.super_admin.support.show', compact('ticket'));
    }

    // Super admin - yanıt ver
    public function reply(Request $request, SupportTicket $ticket)
    {    
        $token = $request->input('form_token');
        if (empty($token)) {
            return back()->with('error', 'Geçersiz form token! Lütfen sayfayı yenileyin.');
        }
        $cacheKey = 'support_reply_form_token_' . $token;
        if (Cache::has($cacheKey)) {
            return back()->with('error', 'Bu form zaten gönderildi! Lütfen bekleyin.');
        }
        Cache::put($cacheKey, true, now()->addMinutes(10));
        $request->validate([
            'message' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx'
        ]);

        // Kapalı taleplere yanıt verilemez
        if (!$ticket->canBeReplied()) {
            return back()->with('error', 'Kapalı taleplere yanıt verilemez.');
        }

        $attachments = [];

       // Dosya yükleme - DEĞIŞEN KISIM
    if ($request->hasFile('attachments')) {
        // Tenant bilgisini al
        $tenant = Tenant::where('id', $ticket->tenant_id)->first();
        
        foreach ($request->file('attachments') as $file) {
            $ext = $file->getClientOriginalExtension();
            $uuid = Str::uuid()->toString() . '.' . $ext;
            
            // Admin reply için organize path
            $path = "support_attachments/firma_{$tenant->firma_slug}/ticket_{$ticket->ticket_number}/admin_replies/" . now()->toDateString();
            $fullPath = storage_path('app/public/' . $path);
            
            // Klasörü oluştur
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0775, true);
            }
            
            $storedPath = $path . '/' . $uuid;
            
            // Eğer resim dosyasıysa boyutlandır
            if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])) {
                $image = Image::make($file)->resize(1024, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $image->save(storage_path('app/public/' . $storedPath), 75);
            } else {
                $file->storeAs($path, $uuid, 'public');
            }
            
            $attachments[] = [
                'original_name' => $file->getClientOriginalName(),
                'stored_name' => $uuid,
                'path' => $storedPath,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ];
        }
    }

        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::user()->user_id,
            'message' => $request->message,
            'attachments' => !empty($attachments) ? $attachments : null,
            'is_admin_reply' => true
        ]);
         $oldStatus = $ticket->status;
    $ticket->update([
        'status' => 'cevaplandi',
        'last_reply_at' => now()
    ]);
    
    // Durum değişikliği logunu ekle
    if ($oldStatus !== 'cevaplandi') {
        ActivityLogger::logSupportTicketStatusChanged(
            $ticket->id, 
            $ticket->ticket_number, 
            $oldStatus, 
            'cevaplandi'
        );
    }
    
    // Yanıt logunu ekle
    ActivityLogger::logSupportTicketReply($ticket->id, $ticket->ticket_number, true);

        return back()->with('success', 'Yanıtınız başarıyla gönderildi.');
    }

    // Super admin - talebi kapat
    public function close(SupportTicket $ticket)
    {
        // LOG EKLE - Kapatmadan önce
        ActivityLogger::logSupportTicketClosed($ticket->id, $ticket->ticket_number, $ticket->subject);
        $ticket->close();
        return back()->with('success', 'Destek talebi kapatıldı.');
    }

    // Super admin - talebi yeniden aç
    public function reopen(SupportTicket $ticket)
    {
         // LOG EKLE - Açmadan önce eski durumu kaydet
    ActivityLogger::logSupportTicketStatusChanged($ticket->id, $ticket->ticket_number, $ticket->status, 'acik');
        $ticket->update(['status' => 'acik']);
            // LOG EKLE - Yeniden açma
    ActivityLogger::logSupportTicketReopened($ticket->id, $ticket->ticket_number, $ticket->subject);
        return back()->with('success', 'Destek talebi yeniden açıldı.');
    }

    // Dosya indirme
   public function downloadAttachment($ticketId, $fileName)
{
    $ticket = SupportTicket::findOrFail($ticketId);
    
    // DEĞIŞEN KISIM
    $tenant = Tenant::where('id', $ticket->tenant_id)->first();
    
    $possiblePaths = [
        "support_attachments/firma_{$tenant->firma_slug}/ticket_{$ticket->ticket_number}/" . now()->toDateString() . "/{$fileName}",
        "support_attachments/firma_{$tenant->firma_slug}/ticket_{$ticket->ticket_number}/replies/" . now()->toDateString() . "/{$fileName}",
        "support_attachments/firma_{$tenant->firma_slug}/ticket_{$ticket->ticket_number}/admin_replies/" . now()->toDateString() . "/{$fileName}",
        "support-attachments/{$fileName}" // Backward compatibility
    ];
    
    $filePath = null;
    foreach ($possiblePaths as $path) {
        if (Storage::disk('public')->exists($path)) {
            $filePath = $path;
            break;
        }
    }
    
    if (!$filePath) {
        abort(404, 'Dosya bulunamadı.');
    }

    return Storage::disk('public')->download($filePath);
}

    // Dashboard istatistikleri
    public function dashboard()
    {
        $stats = [
            'total' => SupportTicket::count(),
            'open' => SupportTicket::where('status', 'acik')->count(),
            'answered' => SupportTicket::where('status', 'cevaplandi')->count(),
            'closed' => SupportTicket::where('status', 'kapali')->count(),
            'high_priority' => SupportTicket::where('priority', 'acil')
            ->where('status', '!=', 'kapali')
            ->count()

        ];

        $recentTickets = SupportTicket::with(['user', 'tenant'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('frontend.secure.super_admin.support.dashboard', compact('stats', 'recentTickets'));
    }
}