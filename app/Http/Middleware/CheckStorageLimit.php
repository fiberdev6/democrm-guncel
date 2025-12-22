<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStorageLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
{
    // Sadece dosya yükleme işlemleri için çalışsın
    if (!$this->hasFileUploads($request)) {
        return $next($request);
    }

    $user = auth()->user();
    
    if (!$user || !$user->tenant) {
        return $this->handleAuthError($request);
    }

    $tenant = $user->tenant;
    
    try {
        // Yüklenen tüm dosyaların toplam boyutunu hesapla
        $totalUploadSize = $this->calculateTotalUploadSize($request);
        
        // Storage kontrolü
        if (!$tenant->canUploadFile($totalUploadSize)) {
            return $this->handleStorageLimitExceeded($tenant, $request);
        }
        
        // Storage kullanımı %80'i geçtiyse uyarı ver
        $this->addStorageWarningIfNeeded($tenant, $request);

        return $next($request);
        
    } catch (\Exception $e) {
        \Log::error('Storage middleware error', [
            'tenant_id' => $tenant->id,
            'error' => $e->getMessage()
        ]);
        
        // Hata durumunda işleme devam et
        return $next($request);
    }
}

private function handleAuthError(Request $request)
{
    if ($request->expectsJson()) {
        return response()->json([
            'success' => false,
            'message' => 'Yetkilendirme hatası'
        ], 403);
    }
    
    return redirect()->route('giris')->with('error', 'Yetkilendirme hatası');
}

private function handleStorageLimitExceeded($tenant, Request $request)
{
    $storageInfo = $tenant->getStorageInfo();
    
    if ($request->expectsJson()) {
        return response()->json([
            'success' => false,
            'message' => 'Storage limiti aşıldı! Dosya yükleyemezsiniz.',
            'error_type' => 'storage_limit_exceeded',
            'storage_info' => $storageInfo
        ], 422);
    }
    
    return redirect()->back()->with([
        'error' => 'Storage limiti aşıldı! Dosya yükleyemezsiniz.',
        'storage_info' => $storageInfo
    ]);
}

private function addStorageWarningIfNeeded($tenant, Request $request)
{
    if ($tenant->getStorageUsagePercentage() >= 80) {
        $request->attributes->set('storage_warning', true);
        $request->attributes->set('storage_info', $tenant->getStorageInfo());
    }
}
    
    /**
     * Request'te dosya yüklemesi var mı kontrol et
     * 
     * @param Request $request
     * @return bool
     */
    private function hasFileUploads(Request $request)
{
    // Tüm dosyaları kontrol et
    foreach ($request->allFiles() as $key => $files) {
        if (!empty($files)) {
            return true;
        }
    }
    
    return false;
}
    
    /**
     * Yüklenen tüm dosyaların toplam boyutunu hesapla
     * 
     * @param Request $request
     * @return int bytes
     */
    private function calculateTotalUploadSize(Request $request)
{
    $totalSize = 0;
    
    foreach ($request->allFiles() as $files) {
        if (!is_array($files)) {
            $files = [$files];
        }
        
        foreach ($files as $file) {
            if ($file && $file->isValid()) {
                $fileSize = $file->getSize();
                
                // Dosya boyutu 0 ise skip et
                if ($fileSize > 0) {
                    $totalSize += $fileSize;
                }
            }
        }
    }
    
    return $totalSize;
}
}
