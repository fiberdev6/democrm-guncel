<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Il;
use App\Models\ServicePhoto;
use App\Models\stock_photos;
use App\Models\Tenant;
use App\Models\TenantPrim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Image;

class GenelAyarlarController extends Controller
{
    public function GeneralSettings($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı',
                'alert-type' => 'danger'
            );
            return redirect()->route('giris')->with($notification);
        }
        return view('frontend.secure.general_settings.settings', compact('firma'));
    }

    public function CompanySettings($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı',
                'alert-type' => 'danger'
            );
            return redirect()->route('giris')->with($notification);
        }
        $countries = Il::orderBy('name', 'ASC')->get();
        return view('frontend.secure.general_settings.company_settings', compact('firma','countries'));
    }

    public function UpdateCompanySet(Request $request, $tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı',
                'alert-type' => 'danger'
            );
            return redirect()->route('giris')->with($notification);
        }
        $validateData = $request->validate([
            'logo'=> 'max:2000',
        ]);
        $company_settings_id = $request->id;

        if($request->file('logo')) {
            $image = $request->file('logo');
            $extension = $request->file('logo')->extension();
            if($extension != "jpg" && $extension != "png" && $extension != "jpeg"){
                $notification = array(
                    'message' => ' Dosya uzantısı sadece jpg,png,jpeg olmalı',
                    'alert-type' => 'warning'
                );
                return redirect()->back()->with($notification);
            }

            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();

            Image::make($image)->save('upload/company_imgs/' . $name_gen);
            $save_url = 'upload/company_imgs/' . $name_gen;
            
            Tenant::findOrFail($company_settings_id)->update([
                'kayitTarihi' => $request->kayitTarihi,
                'firma_adi' => $request->company_name,
                'tel1' => $request->tel1,
                'tel2' => $request->tel2,
                'il' => $request->il,
                'ilce' => $request->ilce,
                'adres' => $request->company_address,
                'eposta' => $request->company_email,
                'webSitesi' => $request->web_sitesi,
                'iban' => $request->iban,
                'vergiNo' => $request->tax_no,
                'vergiDairesi' => $request->tax_office,
                'logo' => $save_url,
            ]);

            $notification = array(
                'message' => 'Firma bilgileri başarıyla güncellendi.',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        } else{
            Tenant::findOrFail($company_settings_id)->update([
                'kayitTarihi' => $request->kayitTarihi,
                'firma_adi' => $request->company_name,
                'tel1' => $request->tel1,
                'tel2' => $request->tel2,
                'il' => $request->il,
                'ilce' => $request->ilce,
                'adres' => $request->company_address,
                'eposta' => $request->company_email,
                'webSitesi' => $request->web_sitesi,
                'iban' => $request->iban,
                'vergiNo' => $request->tax_no,
                'vergiDairesi' => $request->tax_office,
            ]);

            $notification = array(
                'message' => 'Firma bilgileri başarıyla güncellendi.',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }
        
    }

    public function SmsSettings($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı',
                'alert-type' => 'danger'
            );
            return redirect()->route('giris')->with($notification);
        }
        return view('frontend.secure.general_settings.sms_settings', compact('firma'));
    }

    public function UpdateSms(Request $request,$tenant_id) {
        $sms_settings_id = $request->id;
        Tenant::findOrFail($sms_settings_id)->update([
            'smsKullanici' => $request->smsKullanici,
            'smsSifre' => $request->smsSifre,
            'smsGonderici' => $request->smsGonderici,
            'smsKaraliste' => $request->smsKaraliste,
        ]);

        return response()->json(['success', 'Sms entegrasyon bilgileri güncellendi.']);
    }

    public function PrimSettings($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı.',
                'alert-type' => 'danger'
            );
            return redirect()->route('giris')->with($notification);
        }
        $prim = TenantPrim::where('firma_id', $tenant_id)->first();
        return view('frontend.secure.general_settings.prim_settings', compact('firma','prim'));
    }

    public function UpdateFirmPrim(Request $request, $tenant_id) {
        $firm_id = $request->id;
        TenantPrim::findOrFail($firm_id)->update([
            'operatorPrim' => $request->operatorPrim,
            'operatorPrimTutari' => $request->operatorPrimTutari,
            'teknisyenPrim' => $request->teknisyenPrim,
            'teknisyenPrimTutari' => $request->teknisyenPrimTutari,
            'atolyePrim' => $request->atolyePrim,
            'atolyePrimTutari' => $request->atolyePrimTutari,
        ]);
        return response()->json(['success', 'Prim sistemi bilgileri güncellendi.']);
    }

    public function getStorageInfo($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı.',
                'alert-type' => 'danger'
            );
            return redirect()->route('giris')->with($notification);
        }
        $storageInfo = auth()->user()->tenant->getStorageInfo();
        return view('frontend.secure.general_settings.storage_info', compact('firma','storageInfo'));
    }

    public function getStorageDetails($tenant_id)
{
    try {
        $tenant = Tenant::find($tenant_id);
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı.'
            ], 404);
        }

        $storageInfo = $tenant->getStorageInfo();
        
        $details = [
            'service_photos' => $this->getServicePhotosBreakdown($tenant),
            'stock_photos' => $this->getStockPhotosBreakdown($tenant), // Bu metod güncellenecek
            'other_files' => $this->getOtherFilesBreakdown($tenant)
        ];
        
        return response()->json([
            'success' => true,
            'storage_info' => $storageInfo,
            'details' => $details
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Storage details error', [
            'tenant_id' => $tenant_id,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Storage detayları alınırken hata oluştu.'
        ], 500);
    }
}

// JSON için ayrı endpoint
public function getStorageInfoJson($tenant_id) {
    try {
        $tenant = Tenant::find($tenant_id);
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı.'
            ], 404);
        }

        $storageInfo = $tenant->getStorageInfo();
        
        return response()->json([
            'success' => true,
            'storage_info' => $storageInfo
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Storage info error', [
            'tenant_id' => $tenant_id,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Storage bilgileri alınırken hata oluştu.'
        ], 500);
    }
}

/**
 * Servis fotoğrafları breakdown'ı
 */
private function getServicePhotosBreakdown($tenant)
{
    $photos = ServicePhoto::where('firma_id', $tenant->id)
                         ->selectRaw('COUNT(*) as count, SUM(file_size) as total_size, AVG(file_size) as avg_size')
                         ->first();
    
    $recentPhotos = ServicePhoto::where('firma_id', $tenant->id)
                              ->where('created_at', '>=', now()->subDays(30))
                              ->count();
                              
    $photosPerService = ServicePhoto::where('firma_id', $tenant->id)
                                  ->selectRaw('servisid, COUNT(*) as photo_count')
                                  ->groupBy('servisid')
                                  ->orderByDesc('photo_count')
                                  ->take(5)
                                  ->get();

    return [
        'count' => $photos->count ?? 0,
        'total_size' => $photos->total_size ?? 0,
        'total_size_formatted' => $this->formatBytes($photos->total_size ?? 0),
        'average_size' => $photos->avg_size ?? 0,
        'average_size_formatted' => $this->formatBytes($photos->avg_size ?? 0),
        'recent_uploads_30_days' => $recentPhotos,
        'top_services' => $photosPerService->map(function($item) {
            return [
                'service_id' => $item->servisid,
                'photo_count' => $item->photo_count,
                // Servis adını da ekleyebilirsiniz
                // 'service_name' => Service::find($item->servisid)?->name
            ];
        })
    ];
}

/**
 * Stok fotoğrafları breakdown'ı
 */
private function getStockPhotosBreakdown($tenant)
{
    // stock_photos modelinden direkt veri al
    $stockPhotos = stock_photos::where('kid', $tenant->id)
                              ->selectRaw('COUNT(*) as count, COALESCE(SUM(file_size), 0) as total_size, AVG(file_size) as avg_size')
                              ->first();
    
    $recentPhotos = stock_photos::where('kid', $tenant->id)
                               ->where('created_at', '>=', now()->subDays(30))
                               ->count();

    return [
        'count' => $stockPhotos->count ?? 0,
        'total_size' => $stockPhotos->total_size ?? 0,
        'total_size_formatted' => $this->formatBytes($stockPhotos->total_size ?? 0),
        'average_size' => $stockPhotos->avg_size ?? 0,
        'average_size_formatted' => $this->formatBytes($stockPhotos->avg_size ?? 0),
        'recent_uploads_30_days' => $recentPhotos
    ];
}

/**
 * Diğer dosyalar breakdown'ı
 */
private function getOtherFilesBreakdown($tenant)
{
    $breakdown = [
        'support_attachments' => $this->getSupportAttachmentsBreakdown($tenant),
        'dealer_documents' => $this->getDealerDocumentsBreakdown($tenant),
        'invoice_documents' => $this->getInvoiceDocumentsBreakdown($tenant)
    ];
    
    $totalSize = array_sum(array_column($breakdown, 'size'));
    $totalCount = array_sum(array_column($breakdown, 'count'));
    
    return [
        'total_count' => $totalCount,
        'total_size' => $totalSize,
        'total_size_formatted' => $this->formatBytes($totalSize),
        'breakdown' => $breakdown
    ];
}

private function getSupportAttachmentsBreakdown($tenant)
{
    $count = 0;
    $totalSize = 0;

    // Önce tenant'ın user ID'lerini al
    $userIds = DB::table('tb_user')
                 ->where('tenant_id', $tenant->id)
                 ->pluck('user_id');

    $supportReplies = DB::table('support_ticket_replies')
                        ->whereIn('user_id', $userIds)
                        ->whereNotNull('attachments')
                        ->where('attachments', '!=', '')
                        ->get();

    foreach ($supportReplies as $reply) {
        $attachments = json_decode($reply->attachments, true);
        if (is_array($attachments)) {
            foreach ($attachments as $attachment) {
                if (isset($attachment['path'])) {
                    $filePath = storage_path('app/public/' . $attachment['path']);
                    if (file_exists($filePath)) {
                        $size = filesize($filePath);
                        $totalSize += $size;
                        $count++;
                    }
                }
            }
        }
    }

    return [
        'count' => $count,
        'size' => $totalSize,
        'size_formatted' => $this->formatBytes($totalSize)
    ];
}

private function getDealerDocumentsBreakdown($tenant)
{
    $dealerDocsPath = storage_path("app/public/dealers-documents/firma_{$tenant->firma_slug}");
    
    if (!is_dir($dealerDocsPath)) {
        return ['count' => 0, 'size' => 0, 'size_formatted' => '0 B'];
    }
    
    $count = $this->countFilesInDirectory($dealerDocsPath);
    $size = $this->calculateDirectorySize($dealerDocsPath);
    
    return [
        'count' => $count,
        'size' => $size,
        'size_formatted' => $this->formatBytes($size)
    ];
}

private function getInvoiceDocumentsBreakdown($tenant)
{
    $count = 0;
    $totalSize = 0;
    
    $invoices = DB::table('invoices')
                   ->where('firma_id', $tenant->id)
                   ->whereNotNull('faturaPdf')
                   ->where('faturaPdf', '!=', '')
                   ->get();
    
    foreach ($invoices as $invoice) {
        $filePath = public_path($invoice->faturaPdf);
        if (file_exists($filePath)) {
            $size = filesize($filePath);
            $totalSize += $size;
            $count++;
        }
    }
    
    return [
        'count' => $count,
        'size' => $totalSize,
        'size_formatted' => $this->formatBytes($totalSize)
    ];
}

private function formatBytes($bytes, $precision = 2)
{
    if ($bytes === null || $bytes < 0) {
        return '0 B';
    }
    
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}
/**
 * Son yüklenen dosyaları getir
 */
private function getRecentUploads($tenant, $limit = 10)
{
    $recentFiles = [];
    
    // Servis fotoğrafları
    $recentServicePhotos = ServicePhoto::where('firma_id', $tenant->id)
                                     ->orderByDesc('created_at')
                                     ->take($limit)
                                     ->get()
                                     ->map(function($photo) {
                                         return [
                                             'id' => $photo->id,
                                             'type' => 'service_photo',
                                             'name' => $photo->original_name ?? 'Servis Fotoğrafı',
                                             'size' => $photo->file_size,
                                             'size_formatted' => $this->formatBytes($photo->file_size),
                                             'uploaded_at' => $photo->created_at,
                                             'url' => Storage::url($photo->resimyol),
                                             'service_id' => $photo->servisid
                                         ];
                                     });
    
    // Diğer dosya türleri buraya eklenebilir
    // $recentStockPhotos = ...
    
    $recentFiles = $recentServicePhotos;
    
    return $recentFiles->sortByDesc('uploaded_at')->take($limit)->values();
}

/**
 * En büyük dosyaları getir
 */
private function getLargestFiles($tenant, $limit = 5)
{
    $largestFiles = [];
    
    // Servis fotoğrafları
    $largestServicePhotos = ServicePhoto::where('firma_id', $tenant->id)
                                      ->whereNotNull('file_size')
                                      ->orderByDesc('file_size')
                                      ->take($limit)
                                      ->get()
                                      ->map(function($photo) {
                                          return [
                                              'id' => $photo->id,
                                              'type' => 'service_photo',
                                              'name' => $photo->original_name ?? 'Servis Fotoğrafı',
                                              'size' => $photo->file_size,
                                              'size_formatted' => $this->formatBytes($photo->file_size),
                                              'uploaded_at' => $photo->created_at,
                                              'url' => Storage::url($photo->resimyol)
                                          ];
                                      });
    
    return $largestServicePhotos->take($limit)->values();
}

/**
 * Aylık kullanım istatistikleri
 */
private function getMonthlyUsage($tenant)
{
    $monthlyData = [];
    
    // Son 12 ayın verilerini al
    for ($i = 11; $i >= 0; $i--) {
        $date = now()->subMonths($i);
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        
        // O aydaki yüklenen dosya sayısı ve boyutu
        $uploadCount = ServicePhoto::where('firma_id', $tenant->id)
                                 ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                                 ->count();
                                 
        $uploadSize = ServicePhoto::where('firma_id', $tenant->id)
                                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                                ->sum('file_size') ?? 0;
        
        $monthlyData[] = [
            'month' => $date->format('Y-m'),
            'month_name' => $date->format('M Y'),
            'upload_count' => $uploadCount,
            'upload_size' => $uploadSize,
            'upload_size_formatted' => $this->formatBytes($uploadSize)
        ];
    }
    
    return $monthlyData;
}

/**
 * Klasördeki dosya sayısını hesapla
 */
private function countFilesInDirectory($directory)
{
    if (!is_dir($directory)) {
        return 0;
    }
    
    $count = 0;
    
    try {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $count++;
            }
        }
    } catch (\Exception $e) {
        \Log::warning("File count error for directory: {$directory}", ['error' => $e->getMessage()]);
    }
    
    return $count;
}

/**
 * Klasörün boyutunu hesapla (private method zaten var ama yeniden tanımlayalım)
 */
private function calculateDirectorySize($directory)
{
    if (!is_dir($directory)) {
        return 0;
    }
    
    $totalSize = 0;
    
    try {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $totalSize += $file->getSize();
            }
        }
    } catch (\Exception $e) {
        \Log::warning("Directory size calculation error: {$directory}", ['error' => $e->getMessage()]);
    }
    
    return $totalSize;
}

/**
 * File cleanup metodları
 */
public function cleanupStorageFiles($tenant_id, Request $request)
{
    try {
        $tenant = Tenant::findOrFail($tenant_id);
        $cleanupType = $request->get('type', 'orphaned'); // orphaned, old, large
        $daysOld = $request->get('days_old', 30);
        $minSizeKB = $request->get('min_size_kb', 1000); // 1MB
        
        $cleaned = 0;
        $freedSpace = 0;
        
        switch ($cleanupType) {
            case 'orphaned':
                [$cleaned, $freedSpace] = $this->cleanOrphanedFiles($tenant);
                break;
                
            case 'old':
                [$cleaned, $freedSpace] = $this->cleanOldFiles($tenant, $daysOld);
                break;
                
            case 'large':
                [$cleaned, $freedSpace] = $this->cleanLargeFiles($tenant, $minSizeKB * 1024);
                break;
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Temizlik işlemi tamamlandı',
            'cleaned_files' => $cleaned,
            'freed_space' => $this->formatBytes($freedSpace),
            'storage_info' => $tenant->getStorageInfo()
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Storage cleanup error', [
            'tenant_id' => $tenant_id,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Temizlik sırasında hata oluştu'
        ], 500);
    }
}

/**
 * Orphaned dosyaları temizle
 */
private function cleanOrphanedFiles($tenant)
{
    $cleaned = 0;
    $freedSpace = 0;
    
    // Veritabanında kaydı olmayan dosyaları bul
    $servicePhotosPath = storage_path("app/public/service_photos/firma_{$tenant->firma_slug}");
    
    if (is_dir($servicePhotosPath)) {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($servicePhotosPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relativePath = str_replace(storage_path('app/public/'), '', $file->getPathname());
                
                // Bu dosyanın veritabanında kaydı var mı?
                $exists = ServicePhoto::where('firma_id', $tenant->id)
                                    ->where('resimyol', $relativePath)
                                    ->exists();
                
                if (!$exists) {
                    $fileSize = $file->getSize();
                    if (unlink($file->getPathname())) {
                        $cleaned++;
                        $freedSpace += $fileSize;
                    }
                }
            }
        }
    }
    
    return [$cleaned, $freedSpace];
}

/**
 * Eski dosyaları temizle
 */
private function cleanOldFiles($tenant, $daysOld)
{
    $cleaned = 0;
    $freedSpace = 0;
    $cutoffDate = now()->subDays($daysOld);
    
    $oldPhotos = ServicePhoto::where('firma_id', $tenant->id)
                            ->where('created_at', '<', $cutoffDate)
                            ->get();
    
    foreach ($oldPhotos as $photo) {
        $filePath = storage_path('app/public/' . $photo->resimyol);
        
        if (file_exists($filePath)) {
            $fileSize = filesize($filePath);
            if (unlink($filePath)) {
                $freedSpace += $fileSize;
                $cleaned++;
            }
        }
        
        $photo->delete();
    }
    
    return [$cleaned, $freedSpace];
}

/**
 * Büyük dosyaları temizle
 */
private function cleanLargeFiles($tenant, $minSize)
{
    $cleaned = 0;
    $freedSpace = 0;
    
    $largePhotos = ServicePhoto::where('firma_id', $tenant->id)
                              ->where('file_size', '>', $minSize)
                              ->orderByDesc('file_size')
                              ->get();
    
    foreach ($largePhotos as $photo) {
        $filePath = storage_path('app/public/' . $photo->resimyol);
        
        if (file_exists($filePath)) {
            $fileSize = filesize($filePath);
            if (unlink($filePath)) {
                $freedSpace += $fileSize;
                $cleaned++;
            }
        }
        
        $photo->delete();
    }
    
    return [$cleaned, $freedSpace];
}
}
