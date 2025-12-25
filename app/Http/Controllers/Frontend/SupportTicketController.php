<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Tenant;
use Intervention\Image\Facades\Image;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Cache;

class SupportTicketController extends Controller
{
    // Kullanıcı tarafı - destek talepleri listesi
   public function index($tenant_id)
    {
        $user = Auth::user();
        
        // Super Admin değilse tenant kontrolü yap
        if (!$user->isSuperAdmin() && $user->tenant_id != $tenant_id) {
            abort(403, 'Bu tenant\'a erişim yetkiniz yok.');
        }
        
        $query = SupportTicket::where('user_id', $user->user_id);
        
        // Super Admin değilse sadece kendi tenant'ını görsün
        if (!$user->isSuperAdmin()) {
            $query->where('tenant_id', $user->tenant_id);
        }

        // İstatistikler için tüm ticket'ları al (pagination olmadan)
        $allTicketsQuery = clone $query;
        $allTickets = $allTicketsQuery->get();
        
        // İstatistikleri hesapla
        $statusCounts = [
            'acik' => $allTickets->where('status', 'acik')->count(),
            'cevaplandi' => $allTickets->where('status', 'cevaplandi')->count(),
            'kapali' => $allTickets->where('status', 'kapali')->count()
        ];
        $totalTickets = $allTickets->count();

       // Tüm ticket'ları al (sayfalama kaldırıldı)
        $tickets = $query->orderBy('last_reply_at', 'desc')->get();

        return view('frontend.secure.support.index', compact('tickets', 'statusCounts', 'totalTickets'));
    }

    // Kullanıcı tarafı - yeni destek talebi formu
    public function create($tenant_id)
    {
        $user = Auth::user();
        
        // Super Admin değilse tenant kontrolü yap
        if (!$user->isSuperAdmin() && $user->tenant_id != $tenant_id) {
            abort(403, 'Bu tenant\'a erişim yetkiniz yok.');
        }

        $categories = [
            'teknik_sorun' => 'Teknik Sorun',
            'faturalandirma' => 'Faturalandırma',
            'ozellik_talebi' => 'Özellik Talebi',
            'genel_destek' => 'Genel Destek',
            'hesap_sorunu' => 'Hesap Sorunu'
        ];

        return view('frontend.secure.support.create', compact('categories'));
    }

    // Kullanıcı tarafı - destek talebi oluştur
    public function store(Request $request, $tenant_id)
    {
        $request->validate([
            'category' => 'required|string',
            'subject' => 'required|string|max:255',
            'priority' => 'required|in:acil,kritik,yuksek,orta,dusuk',
            'description' => 'required|string',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx',
            'attachments' => 'max:3' // 3 dosya sınırı
        ]);

        $user = Auth::user();
        
        // Super Admin değilse tenant kontrolü yap
        if (!$user->isSuperAdmin() && $user->tenant_id != $tenant_id) {
            abort(403, 'Bu tenant\'a erişim yetkiniz yok.');
        }

        // Tenant bilgisini al
        $tenant = Tenant::where('id', $tenant_id)->first();
        if (!$tenant) {
            return redirect()->back()->with('error', 'Firma bulunamadı.');
        }

        // Storage kontrolü - dosyalar varsa
        if ($request->hasFile('attachments')) {
            $totalUploadSize = 0;
            foreach ($request->file('attachments') as $file) {
                $totalUploadSize += $file->getSize();
            }
            
            // Storage limitini kontrol et
            if (!$tenant->canUploadFile($totalUploadSize)) {
                $storageInfo = $tenant->getStorageInfo();
                return redirect()->back()
                            ->with('error', "Storage limiti doldu! Dosya boyutu: " . $this->formatBytes($totalUploadSize) . 
                                            ", Kalan alan: " . $storageInfo['remaining_formatted'] . 
                                            ". Planınızı yükseltiniz veya eski dosyaları siliniz.")
                            ->withInput();
            }
        }

        // Token kontrolü
        $token = $request->input('form_token');
        // Token boş mu kontrol et
        if (empty($token)) {
            return back()->withInput()->with('error', 'Geçersiz form token! Lütfen sayfayı yenileyin.');
        }
        // Bu token daha önce kullanıldı mı kontrol et
        $cacheKey = 'support_form_token_' . $token;
        if (Cache::has($cacheKey)) {
            return back()->withInput()->with('error', 'Bu destek talebi zaten gönderildi! Lütfen bekleyin veya sayfayı yenileyin.');
        }
        // Token'ı 10 dakika boyunca sakla
        Cache::put($cacheKey, true, now()->addMinutes(10));

        // Ticket numarasını bir kez oluştur
        $ticketNumber = SupportTicket::generateTicketNumber();
        $attachments = [];

        // Dosya yükleme
        if ($request->hasFile('attachments')) {
            // Tenant bilgisini al
            $tenant = Tenant::where('id', $tenant_id)->first();
            
            foreach ($request->file('attachments') as $file) {
                $ext = $file->getClientOriginalExtension();
                $uuid = Str::uuid()->toString() . '.' . $ext;
                
                // Organize path oluştur - aynı ticket numarasını kullan
                $path = "support_attachments/firma_{$tenant->firma_slug}/ticket_{$ticketNumber}/" . now()->toDateString();
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
                    // Diğer dosyalar için normal kaydetme
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


        // Storage warning kontrolü
        $storageWarning = null;
        if ($tenant->getStorageUsagePercentage() >= 80) {
            $storageInfo = $tenant->getStorageInfo();
            $storageWarning = "Destek talebi oluşturuldu ancak storage alanınız %{$storageInfo['usage_percentage']} dolu. Kalan alan: {$storageInfo['remaining_formatted']}. Planınızı yükseltmeyi düşünün.";
        }

        $ticket = SupportTicket::create([
            'ticket_number' => $ticketNumber, // Aynı numarayı kullan
            'tenant_id' => $tenant_id,
            'user_id' => $user->user_id,
            'category' => $request->category,
            'subject' => $request->subject,
            'priority' => $request->priority,
            'description' => $request->description,
            'attachments' => !empty($attachments) ? $attachments : null,
            'status' => 'acik',
            'last_reply_at' => now()
        ]);

        $successMessage = 'Destek talebiniz başarıyla oluşturuldu. Talep numaranız: ' . $ticket->ticket_number;
    
        // Storage warning varsa ek mesaj
        if ($storageWarning) {
            session()->flash('storage_warning', $storageWarning);
        }
          // LOG EKLE
    ActivityLogger::logSupportTicketCreated($ticket->id, $ticket->ticket_number, $ticket->subject);
        return redirect()->route('support.index', $tenant_id)->with('success', $successMessage);
    }


// Kullanıcı tarafı - destek talebi detay
public function show($tenant_id, SupportTicket $ticket)
{
    $user = Auth::user();
    
    // Super Admin tümünü görebilir, diğerleri sadece kendilerininkini
    if (!$user->isSuperAdmin() && $ticket->user_id !== $user->user_id) {
        abort(403, 'Bu destek talebini görüntüleme yetkiniz bulunmamaktadır.');
    }

    // Tenant kontrolü (super admin değilse)
    if (!$user->isSuperAdmin() && $ticket->tenant_id != $tenant_id) {
        abort(403, 'Bu destek talebi size ait değil.');
    }

    $ticket->load('replies.user', 'user');

    // Bu ticket'a özel aktivite loglarını getir
    $ticketActivities = ActivityLog::where('tenant_id', $ticket->tenant_id)
                                  ->where('module', 'support')
                                  ->where(function($query) use ($ticket) {
                                      // reference_id ile eşleşenler VEYA description'da ticket ID'si geçenler
                                      $query->where('reference_id', $ticket->id)
                                            ->orWhere('description', 'like', "%TalepID: {$ticket->id}%");
                                  })
                                  ->orderBy('created_at', 'asc') // Kronolojik sıra ile
                                  ->get();

    return view('frontend.secure.support.show', compact('ticket', 'ticketActivities'));
}

    // Kullanıcı tarafı - yanıt ekle
    public function reply(Request $request, $tenant_id, SupportTicket $ticket)
    {
        $request->validate([
            'message' => 'required|string',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx'
        ]);

        $user = Auth::user();
        
        // Super Admin tümünü yanıtlayabilir, diğerleri sadece kendilerininkini
        if (!$user->isSuperAdmin() && $ticket->user_id !== $user->user_id) {
            abort(403, 'Bu destek talebini yanıtlama yetkiniz bulunmamaktadır.');
        }

        // Tenant kontrolü (super admin değilse)
        if (!$user->isSuperAdmin() && $ticket->tenant_id != $tenant_id) {
            abort(403, 'Bu destek talebi size ait değil.');
        }

        // Kapalı taleplere yanıt verilemez
        if (!$ticket->canBeReplied()) {
            return back()->with('error', 'Kapalı taleplere yanıt verilemez.');
        }

        // Tenant bilgisini al
        $tenant = Tenant::where('id', $tenant_id)->first();
        if (!$tenant) {
            return redirect()->back()->with('error', 'Firma bulunamadı.');
        }

        // Storage kontrolü - dosyalar varsa
        if ($request->hasFile('attachments')) {
            $totalUploadSize = 0;
            foreach ($request->file('attachments') as $file) {
                $totalUploadSize += $file->getSize();
            }
            
            // Storage limitini kontrol et
            if (!$tenant->canUploadFile($totalUploadSize)) {
                $storageInfo = $tenant->getStorageInfo();
                return redirect()->back()
                            ->with('error', "Storage limiti doldu! Dosya boyutu: " . $this->formatBytes($totalUploadSize) . 
                                            ", Kalan alan: " . $storageInfo['remaining_formatted'] . 
                                            ". Planınızı yükseltiniz veya eski dosyaları siliniz.")
                            ->withInput();
            }
        }

        $token = $request->input('form_token');
        // Token boş mu kontrol et
        if (empty($token)) {
            return back()->withInput()->with('error', 'Geçersiz form token! Lütfen sayfayı yenileyin.');
        }
        // Bu token daha önce kullanıldı mı kontrol et
        $cacheKey = 'support_reply_token_' . $token;
        if (Cache::has($cacheKey)) {
            return back()->withInput()->with('error', 'Bu yanıt zaten gönderildi! Lütfen bekleyin veya sayfayı yenileyin.');
        }
        // Token'ı 10 dakika boyunca sakla
        Cache::put($cacheKey, true, now()->addMinutes(10));
        $attachments = [];

    // Dosya yükleme 
    if ($request->hasFile('attachments')) {
        // Tenant bilgisini al
        $tenant = Tenant::where('id', $ticket->tenant_id)->first();
        
        foreach ($request->file('attachments') as $file) {
            $ext = $file->getClientOriginalExtension();
            $uuid = Str::uuid()->toString() . '.' . $ext;
            
            // Reply için organize path
            $path = "support_attachments/firma_{$tenant->firma_slug}/ticket_{$ticket->ticket_number}/replies/" . now()->toDateString();
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
            'user_id' => $user->user_id,
            'message' => $request->message,
            'attachments' => !empty($attachments) ? $attachments : null,
            'is_admin_reply' => $user->isSuperAdmin() // Super Admin ise admin yanıtı olarak işaretle
        ]);

        $oldStatus = $ticket->status;
    
    // Kullanıcı yanıt verince durum güncellemesi
    if (!$user->isSuperAdmin()) {
        // Normal kullanıcı yanıt verdi
        if ($oldStatus === 'cevaplandi') {
            // Admin cevaplamıştı, şimdi kullanıcı tekrar yanıt verdi -> 'acik' yap
            $ticket->update([
                'status' => 'acik',
                'last_reply_at' => now()
            ]);
            
            // Durum değişikliği logunu ekle
            ActivityLogger::logSupportTicketStatusChanged(
                $ticket->id, 
                $ticket->ticket_number, 
                $oldStatus, 
                'acik'
            );
        } else {
            // Durum 'acik' kalsın, sadece son yanıt tarihini güncelle
            $ticket->update(['last_reply_at' => now()]);
        }
        
        // *** ÖNEMLİ: Kullanıcı yanıt logunu ekle ***
        ActivityLogger::logSupportTicketReply($ticket->id, $ticket->ticket_number, false);
        
    } else {
        // Super Admin yanıt verdi - bu kısım zaten AdminSupportController'da var ama yine de
        $ticket->update([
            'status' => 'cevaplandi',
            'last_reply_at' => now()
        ]);
        
        if ($oldStatus !== 'cevaplandi') {
            ActivityLogger::logSupportTicketStatusChanged(
                $ticket->id, 
                $ticket->ticket_number, 
                $oldStatus, 
                'cevaplandi'
            );
        }
        
        ActivityLogger::logSupportTicketReply($ticket->id, $ticket->ticket_number, true);
    }

        return back()->with('success', 'Yanıtınız başarıyla gönderildi.');
    }

    // Dosya indirme
    public function downloadAttachment($tenant_id, $ticketId, $fileName)
{
    $user = Auth::user();
    $ticket = SupportTicket::findOrFail($ticketId);
    
    // Kullanıcı kontrolü - Super Admin veya ticket sahibi
    if (!$user->isSuperAdmin() && $ticket->user_id !== $user->user_id) {
        abort(403);
    }

    // DEĞIŞEN KISIM - Dosyayı farklı yerlerde ara
    $tenant = Tenant::where('id', $ticket->tenant_id)->first();
    
    $possiblePaths = [
        // Yeni organize yapı
        "support_attachments/firma_{$tenant->firma_slug}/ticket_{$ticket->ticket_number}/" . now()->toDateString() . "/{$fileName}",
        "support_attachments/firma_{$tenant->firma_slug}/ticket_{$ticket->ticket_number}/replies/" . now()->toDateString() . "/{$fileName}",
        "support_attachments/firma_{$tenant->firma_slug}/ticket_{$ticket->ticket_number}/admin_replies/" . now()->toDateString() . "/{$fileName}",
        // Eski format için backward compatibility
        "support-attachments/{$fileName}"
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
}