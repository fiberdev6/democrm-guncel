<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Tenant extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function ils() 
    {
        return $this->belongsTo(Il::class, 'il', 'id');
    }

    public function ilces()
    {
        return $this->belongsTo(Ilce::class, 'ilce','id');
    }

    // Firmanın aşamaları
    public function serviceStages()
    {
        return $this->hasMany(ServiceStage::class, 'firma_id', 'id');
    }

    // Varsayılan aşamaları getir
    public static function defaultStages()
    {
        return ServiceStage::whereNull('firma_id')->get();
    }

    protected $casts = [
        'trial_starts_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'trial_used' => 'boolean'
    ];

    // İlişkiler
    public function users()
    {
        return $this->hasMany(User::class);
    }

   public function getUsernamesAttribute()
    {
        return $this->users->pluck('username');
    }

    public function subscriptions()
    {
        return $this->hasMany(TenantSubscription::class);
    }

    public function currentSubscription()
    {
        return $this->hasOne(TenantSubscription::class)->latest();
    }

    public function payments()
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(TenantSubscription::class)
                    ->where('status', 'active')
                    ->latestOfMany();
    }

    public function plan()
    {
        return $this->activeSubscription?->plansubs;
    }

    public function hasFeature($key)
    {
        return !empty($this->plan()?->features[$key]) && $this->plan()->features[$key] === true;
    }

    // Abonelik Durumu Kontrolleri
    public function isOnTrial()
    {
        return $this->subscription_status === 'trial' && 
               $this->trial_ends_at && 
               $this->trial_ends_at->isFuture();
    }

    public function hasActiveSubscription()
    {
        return in_array($this->subscription_status, ['trial', 'active']) && 
               $this->subscription_ends_at &&
               $this->subscription_ends_at->isFuture();
    }

    public function isExpired()
    {
        return $this->subscription_status === 'expired' || 
               ($this->subscription_ends_at && $this->subscription_ends_at->isPast());
    }

    public function canAccessFeature($feature)
    {
        if (!$this->hasActiveSubscription()) {
            return false;
        }

        $subscription = $this->currentSubscription;
        if (!$subscription || !$subscription->plan) {
            return false;
        }

        return $subscription->plan->hasFeature($feature);
    }

    public function getFeatureLimit($feature)
    {
        $subscription = $this->currentSubscription;
        if (!$subscription || !$subscription->plan) {
            return 0;
        }

        return $subscription->plan->getFeatureLimit($feature);
    }

    public function getRemainingTrialDays()
    {
        if (!$this->isOnTrial()) {
            return 0;
        }

        return $this->trial_ends_at->diffInDays(now());
    }

    public function startTrial()
    {
        $this->update([
            'trial_starts_at' => now(),
            'trial_ends_at' => now()->addDays(14),
            'subscription_status' => 'trial',
            'subscription_ends_at' => now()->addDays(14),
            'trial_used' => true
        ]);

        // Varsayılan trial planı ile subscription oluştur
        $trialPlan = SubscriptionPlan::where('slug', 'trial')->first();
        if ($trialPlan) {
            $this->subscriptions()->create([
                'plan_id' => $trialPlan->id,
                'status' => 'trial',
                'starts_at' => now(),
                'ends_at' => now()->addDays(14),
                'trial_ends_at' => now()->addDays(14)
            ]);
        }
    }

    public function canAccessDealersModule()
    {
        // Eğer deneme sürecindeyse
        if ($this->isOnTrial()) {
            // Deneme sürecinde bayiSayisi kontrol edilir
            return $this->bayiSayisi > 0;
        }

        // Deneme süreci bittiyse aktif abonelik kontrol edilir
        if ($this->hasActiveSubscription()) {
            $subscription = $this->activeSubscription;
            
            if (!$subscription || !$subscription->plansubs) {
                return false;
            }

            // Abonelik planındaki limits kontrolü
            $limits = $subscription->plansubs->limits;
            
            if (is_string($limits)) {
                $limits = json_decode($limits, true);
            }

            // dealers limiti 0'dan büyükse modül görünür
            return isset($limits['dealers']) && $limits['dealers'] > 0;
        }

        // Hiç aktif abonelik yoksa modül görünmez
        return false;
    }

    /**
     * Bayiler modülü için mevcut limit değerini döndürür
     * 
     * @return int
     */
    public function getDealersLimit()
    {
        // Eğer deneme sürecindeyse bayiSayisi döndür
        if ($this->isOnTrial()) {
            return $this->bayiSayisi ?? 0;
        }

        // Aktif abonelik varsa plan limitini döndür
        if ($this->hasActiveSubscription()) {
            $subscription = $this->activeSubscription;
            
            if (!$subscription || !$subscription->plansubs) {
                return 0;
            }

            $limits = $subscription->plansubs->limits;
            
            if (is_string($limits)) {
                $limits = json_decode($limits, true);
            }

            return $limits['dealers'] ?? 0;
        }

        return 0;
    }

    public function getStorageLimit()
{
    // Deneme sürecindeyse
    if ($this->isOnTrial()) {
        $subscription = $this->subscriptions()->where('status', 'trial')->first();
        if ($subscription && $subscription->plansubs) {
            $limits = $subscription->plansubs->limits;
            if (is_string($limits)) {
                $limits = json_decode($limits, true);
            }
            return $limits['storage_gb'] ?? 0.5; // Trial için default 0.5 GB
        }
        return 0.5; // Trial default
    }

    // Aktif abonelik varsa
    if ($this->hasActiveSubscription()) {
        $subscription = $this->activeSubscription;
        
        if ($subscription && $subscription->plansubs) {
            $limits = $subscription->plansubs->limits;
            
            if (is_string($limits)) {
                $limits = json_decode($limits, true);
            }

            return $limits['storage_gb'] ?? 1; // Default 1GB
        }
    }

    return 0.1; // Aboneliği olmayan firmalar için minimal limit (100MB)
}

/**
 * Firmanın mevcut storage kullanımını hesaplar (GB cinsinden)
 * 
 * @return float
 */
public function getCurrentStorageUsage()
{
    $totalSizeBytes = 0;
    
    // Servis fotoğrafları
    $totalSizeBytes += $this->getServicePhotosSize();
    
    // Stok resimleri (varsa)
    $totalSizeBytes += $this->getStockPhotosSize();
    
    // Diğer dosyalar (belgeler, raporlar vs.)
    $totalSizeBytes += $this->getOtherFilesSize();
    
    // Bytes'ı GB'ye çevir
    return round($totalSizeBytes / (1024 * 1024 * 1024), 4);
}

/**
 * Servis fotoğraflarının toplam boyutunu hesaplar
 * 
 * @return int bytes cinsinden
 */
private function getServicePhotosSize()
{
    $totalSize = 0;
    
    // ServicePhoto modelinden dosya yollarını al
    $servicePhotos = ServicePhoto::where('firma_id', $this->id)->get();
    
    foreach ($servicePhotos as $photo) {
        $filePath = storage_path('app/public/' . $photo->resimyol);
        if (file_exists($filePath)) {
            $totalSize += filesize($filePath);
        }
    }
    
    return $totalSize;
}

/**
 * Stok resimlerinin toplam boyutunu hesaplar
 * 
 * @return int bytes cinsinden
 */
private function getStockPhotosSize()
{
    $totalSize = 0;
   
    $stockPhotos = stock_photos::where('kid', $this->id)->get();
    foreach ($stockPhotos as $photo) {
        $filePath = storage_path('app/public/' . $photo->resimyol);
        if (file_exists($filePath)) {
            $totalSize += filesize($filePath);
        }
    }
    
    
    // Şimdilik klasör bazlı hesaplama
    $stockPath = storage_path("app/public/stock/firma_{$this->firma_slug}");
    if (is_dir($stockPath)) {
        $totalSize += $this->calculateDirectorySize($stockPath);
    }
    
    return $totalSize;
}

/**
 * Diğer dosyaların toplam boyutunu hesaplar
 * 
 * @return int bytes cinsinden
 */
private function getOtherFilesSize()
{
    $totalSize = 0;
    
    // Support ticket attachments
    $totalSize += $this->getSupportAttachmentsSize();
    
    // Bayi belgeleri (dealers-documents klasöründen)
    $totalSize += $this->getDealerDocumentsSize();
    
    // Fatura belgeleri (upload klasöründen)
    $totalSize += $this->getInvoiceDocumentsSize();
    
    return $totalSize;
}

private function getSupportAttachmentsSize()
{
    $totalSize = 0;

    // Önce tenant'ın user ID'lerini al
    $userIds = DB::table('tb_user')
                 ->where('tenant_id', $this->id)
                 ->pluck('user_id');

    // support_ticket_replies tablosundan attachments alanını al
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
                        $totalSize += filesize($filePath);
                    }
                }
            }
        }
    }

    return $totalSize;
}

private function getDealerDocumentsSize()
{
    $totalSize = 0;
    
    // Fatura belgelerini faturas tablosundan al
    $dealer_Photos = DB::table('tb_user')
                   ->where('tenant_id', $this->id)
                   ->whereNotNull('belgePdf')
                   ->where('belgePdf', '!=', '')
                   ->get();
    
    foreach ($dealer_Photos as $invoice) {
        // upload klasöründeki dosyalar
        $filePath = public_path($invoice->belgePdf);
        if (file_exists($filePath)) {
            $totalSize += filesize($filePath);
        }
    }
    
    return $totalSize;
}

private function getInvoiceDocumentsSize()
{
    $totalSize = 0;
    
    // Fatura belgelerini faturas tablosundan al
    $invoices = DB::table('invoices')
                   ->where('firma_id', $this->id)
                   ->whereNotNull('faturaPdf')
                   ->where('faturaPdf', '!=', '')
                   ->get();
    
    foreach ($invoices as $invoice) {
        // upload klasöründeki dosyalar
        $filePath = public_path($invoice->faturaPdf);
        if (file_exists($filePath)) {
            $totalSize += filesize($filePath);
        }
    }
    
    return $totalSize;
}

/**
 * Klasörün toplam boyutunu hesaplar
 * 
 * @param string $directory
 * @return int bytes
 */
private function calculateDirectorySize($directory)
{
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
        \Illuminate\Support\Facades\Log::warning("Storage calculation error for directory: {$directory}", ['error' => $e->getMessage()]);
    }
    
    return $totalSize;
}

/**
 * Kalan storage alanını döndürür (GB)
 * 
 * @return float
 */
public function getRemainingStorage()
{
    $limit = $this->getStorageLimit();
    $used = $this->getCurrentStorageUsage();
    
    return max(0, round($limit - $used, 4));
}

/**
 * Storage limitine ulaşılıp ulaşılmadığını kontrol eder
 * 
 * @return bool
 */
public function hasReachedStorageLimit()
{
    return $this->getCurrentStorageUsage() >= $this->getStorageLimit();
}

/**
 * Belirli boyutta dosya yüklenebilir mi kontrolü
 * 
 * @param int $fileSizeInBytes
 * @return bool
 */
public function canUploadFile($fileSizeInBytes)
{
    $fileSizeInGB = $fileSizeInBytes / (1024 * 1024 * 1024);
    $currentUsage = $this->getCurrentStorageUsage();
    $totalLimit = $this->getTotalStorageLimit(); // Artık toplam limiti kullan
    
    return ($currentUsage + $fileSizeInGB) <= $totalLimit;
}

/**
 * Storage kullanım yüzdesini döndürür
 * 
 * @return float
 */
public function getStorageUsagePercentage()
{
    $limit = $this->getTotalStorageLimit();
    if ($limit == 0) return 100;
    
    $used = $this->getCurrentStorageUsage();
    return round(($used / $limit) * 100, 1);
}
/**
 * Formatlanmış kullanım değerinden yüzde hesapla
 * 
 * @param string $currentUsageFormatted - Örnek: "1.5 GB", "250 MB", "< 10 MB"
 * @param float $totalLimitGB - Toplam limit GB cinsinden
 * @return float - Yüzde değeri (0-100 arası)
 */
public function calculatePercentageFromFormatted($currentUsageFormatted, $totalLimitGB)
{
    if ($totalLimitGB <= 0) {
        return 100; // Limit yoksa %100 kabul et
    }
    
    // Özel durumları kontrol et
    if ($currentUsageFormatted === '0 B' || empty($currentUsageFormatted)) {
        return 0;
    }
    
    if ($currentUsageFormatted === '< 10 MB') {
        // 10 MB'nin altındaki değerler için ortalama 5 MB kabul edelim
        $usageGB = 5 / 1024; // 5 MB'yi GB'ye çevir
        return round(($usageGB / $totalLimitGB) * 100, 1);
    }
    
    // Formatlanmış değeri parse et
    $usageGB = $this->parseFormattedSize($currentUsageFormatted);
    
    if ($usageGB === 0) {
        return 0;
    }
    
    // Yüzdeyi hesapla
    $percentage = ($usageGB / $totalLimitGB) * 100;
    
    // 100'ü aşmaması için kontrol
    return round(min(100, max(0, $percentage)), 1);
}

/**
 * Formatlanmış boyut değerini GB cinsine çevirir
 * 
 * @param string $formattedSize - Örnek: "1.5 GB", "250 MB", "512 KB"
 * @return float - GB cinsinden değer
 */
private function parseFormattedSize($formattedSize)
{
    // String'i temizle ve parçalarına ayır
    $formattedSize = trim($formattedSize);
    preg_match('/^([0-9.,]+)\s*([A-Za-z]+)$/', $formattedSize, $matches);
    
    if (count($matches) !== 3) {
        return 0;
    }
    
    $value = (float) str_replace(',', '.', $matches[1]);
    $unit = strtoupper(trim($matches[2]));
    
    // Birime göre GB'ye çevir
    switch ($unit) {
        case 'B':
        case 'BYTE':
        case 'BYTES':
            return $value / (1024 * 1024 * 1024);
            
        case 'KB':
        case 'KILOBYTE':
            return $value / (1024 * 1024);
            
        case 'MB':
        case 'MEGABYTE':
            return $value / 1024;
            
        case 'GB':
        case 'GIGABYTE':
            return $value;
            
        case 'TB':
        case 'TERABYTE':
            return $value * 1024;
            
        default:
            return 0;
    }
}
/**
 * Storage bilgilerini detaylı olarak döndürür
 * 
 * @return array
 */
public function getStorageInfo()
{
    $currentUsage = $this->getCurrentStorageUsage();
    $baseLimit = $this->getStorageLimit();
    $extraStorage = $this->getExtraStorageSize();
    $totalLimit = $this->getTotalStorageLimit();
    $remaining = max(0, round($totalLimit - $currentUsage, 4));
    
    // current_usage_formatted'ı hesapla
    $currentUsageBytes = $currentUsage * 1024 * 1024 * 1024;
    $formattedUsage = $this->formatBytes($currentUsageBytes, 2, true);
    
    // Yüzde hesaplaması - formatlanmış değerden
    $percentage = $this->calculatePercentageFromFormatted($formattedUsage, $totalLimit);
    
    return [
        'used_bytes' => $currentUsageBytes,
        'current_usage_gb' => $currentUsage,
        'current_usage_formatted' => $formattedUsage,
        'base_limit_gb' => $baseLimit,
        'base_limit_formatted' => number_format($baseLimit, 0) . ' GB',
        'extra_storage_gb' => $extraStorage,
        'extra_storage_formatted' => $extraStorage > 0 ? number_format($extraStorage, 0) . ' GB' : '0 GB',
        'total_limit_gb' => $totalLimit,
        'limit_formatted' => number_format($totalLimit, 0) . ' GB',
        'remaining_gb' => $remaining,
        'remaining_formatted' => $this->formatBytes($remaining * 1024 * 1024 * 1024, 2, true),
        'usage_percentage' => $percentage,
        'is_limit_reached' => $percentage >= 100,
        'warning_threshold' => $percentage >= 80,
        'danger_threshold' => $percentage >= 95,
        'has_extra_storage' => $extraStorage > 0
    ];
}

/**
 * Byte'ları okunabilir formata çevirir
 * 
 * @param int $bytes
 * @param int $precision
 * @return string
 */
private function formatBytes($bytes, $precision = 2, $forDisplay = false) 
{
    if ($bytes === null || $bytes < 0) {
        return '0 B';
    }
    
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    // Kullanıcı dostu gösterim için
    if ($forDisplay) {
        if ($i >= 3) { // GB ve üzeri
            if ($bytes < 0.01) {
                return '< 10 MB';
            }
            return number_format($bytes, $bytes < 1 ? 1 : 0) . ' ' . $units[$i];
        } else {
            return number_format($bytes, $precision) . ' ' . $units[$i];
        }
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

//EK STORAGE PAKET SİSTEMİ İÇİN FONKSİYONLAR
public function storagePurchases()
{
    return $this->hasMany(StoragePurchase::class);
}
public function getExtraStorageSize()
{
    return $this->storagePurchases()
                ->where('status', 'completed')
                ->where(function($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })
                ->sum('storage_gb');
}
public function getTotalStorageLimit()
{
    $baseLimit = $this->getStorageLimit(); // Mevcut plan limiti
    $extraStorage = $this->getExtraStorageSize(); // Satın alınan ek storage
    
    return $baseLimit + $extraStorage;
}
     public function subscriptionPayments()
    {
        return $this->hasMany(\App\Models\SubscriptionPayment::class, 'tenant_id');
    }
    public function getAllPayments()
    {
        $subscriptionPayments = $this->subscriptionPayments()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'type' => 'subscription',
                    'type_label' => 'Abonelik',
                    'description' => "Abonelik Ödemesi (ID: {$payment->subscription_id})",
                    'amount' => $payment->amount ?? 0,
                    'payment_method' => $payment->payment_method,
                    'status' => $payment->status,
                    'invoice_path' => $payment->invoice_path,
                    'created_at' => $payment->created_at,
                    'paid_at' => $payment->paid_at,
                    'transaction_id' => $payment->transaction_id,
                    'gateway' => $payment->gateway,
                    'currency' => $payment->currency,
                ];
            });

        $storagePurchases = $this->storagePurchases()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($purchase) {
                return [
                    'id' => $purchase->id,
                    'type' => 'storage',
                    'type_label' => 'Depolama',
                    'description' => "Ek Depolama Alanı - {$purchase->storage_gb} GB",
                    'amount' => $purchase->amount,
                    'payment_method' => $this->extractPaymentMethod($purchase->payment_response),
                    'status' => $purchase->status,
                    'invoice_path' => $purchase->invoice_path,
                    'created_at' => $purchase->created_at,
                    'storage_gb' => $purchase->storage_gb,
                    'expires_at' => $purchase->expires_at,
                ];
            });

        // Entegrasyon satın alımları ekle
        $integrationPurchases = $this->integrationPurchases()
            ->with('integration')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($purchase) {
                return [
                    'id' => $purchase->id,
                    'type' => 'integration',
                    'type_label' => 'Entegrasyon',
                    'description' => "Entegrasyon - {$purchase->integration->name}",
                    'amount' => $purchase->amount,
                    'payment_method' => $this->extractPaymentMethod($purchase->payment_response),
                    'status' => $purchase->status,
                    'invoice_path' => $purchase->invoice_path,
                    'created_at' => $purchase->created_at,
                    'integration_name' => $purchase->integration->name,
                    'expires_at' => $purchase->expires_at,
                ];
            });

        return $subscriptionPayments
            ->concat($storagePurchases)
            ->concat($integrationPurchases)
            ->sortByDesc('created_at')
            ->values();
    }

    private function extractPaymentMethod($paymentResponse)
    {
        if (is_string($paymentResponse)) {
            $paymentResponse = json_decode($paymentResponse, true);
        }

        return $paymentResponse['payment_method'] ?? 'Belirtilmemiş';
    }

    // Scope'lar
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeOnTrial($query)
    {
        return $query->where('subscription_status', 'trial')
                    ->where('trial_ends_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('subscription_status', 'expired')
                    ->orWhere('subscription_ends_at', '<', now());
    }

    /**
     * Süper admin faturalar ilişkisi
    */
    public function superAdminInvoices()
    {
        return $this->hasMany(SuperAdminInvoice::class, 'firma_id');
    }

    /**
     * Aktif süper admin faturalar
     */
    public function activeSuperAdminInvoices()
    {
        return $this->hasMany(SuperAdminInvoice::class, 'firma_id')->where('durum', '1');
    }


    // ENTEGRASYON SİSTEMİ İÇİN FONKSİYONLAR
    public function integrationPurchases()
    {
        return $this->hasMany(IntegrationPurchase::class);
    }

    public function activeIntegrations()
    {
        return $this->integrationPurchases()
                    ->with('integration')
                    ->where('is_active', true)
                    ->where('status', 'completed');
    }

    public function hasIntegration($integrationId)
    {
        return $this->integrationPurchases()
                    ->where('integration_id', $integrationId)
                    ->where('is_active', true)
                    ->where('status', 'completed')
                    ->exists();
    }

    public function getIntegrationPurchase($integrationId)
    {
        return $this->integrationPurchases()
                    ->where('integration_id', $integrationId)
                    ->where('is_active', true)
                    ->where('status', 'completed')
                    ->first();
    }

   
}
