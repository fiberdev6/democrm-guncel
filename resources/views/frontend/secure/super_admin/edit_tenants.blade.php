
<!-- Header -->
<div class="card-header text-white d-flex align-items-center justify-content-between p-3" >
  <!-- Add this hidden input in your modal -->
<input type="hidden" id="current-tenant-id" value="{{ $tenant->id ?? '' }}">
  <div class="d-flex align-items-center">
    <div>
      <h4 class="py-2 mb-0" style="padding: 16px;">
      <span style="text-transform: uppercase;">{{ $tenant->firma_adi }} </span>
    </h4>
    </div>
  </div>
  <button type="button" class="btn-close" style="padding-left: 21px" data-bs-dismiss="modal"></button>
</div>

<!-- Body -->
<div class="card-body p-3">
  <!-- Nav Tabs - 3 Tab -->
  <div class="nav nav-pills nav-fill mb-3" role="tablist">
    <!-- İlk Tab: Müşteri ve Abonelik Bilgileri -->
    <button class="nav-link active" id="customer-subscription-tab" data-bs-toggle="pill" data-bs-target="#customer-subscription" type="button">
      <i class="fas fa-building me-1"></i>Müşteri ve Abonelik Bilgileri
    </button>
    
    <!-- İkinci Tab: Ödeme Bilgileri -->
    <button class="nav-link" id="payment-info-tab" data-bs-toggle="pill" data-bs-target="#payment-info" type="button">
      <i class="fas fa-money-bill-wave me-1"></i>Ödeme Bilgileri
    </button>

    <!-- Üçüncü Tab: Servis İstatistikleri -->
    <button class="nav-link" id="service-stats-tab" data-bs-toggle="pill" data-bs-target="#service-stats" type="button">
      <i class="fas fa-chart-bar me-1"></i>Servis İstatistikleri
    </button>
  </div>

  <!-- Tab Content -->
  <div class="tab-content">
    
    <!-- İLK TAB: MÜŞTERİ VE ABONELİK BİLGİLERİ -->
    <div class="tab-pane fade show active" id="customer-subscription">
      <div class="row g-1">
        <!-- Firma Bilgileri -->
        <div class="col-md-6">
          <div class="info-card">
            <h6 class="info-card-title">Firma Bilgileri</h6>
            <div class="info-content">
              <div class="info-item">
                Firma Adı:
                <span class="info-value fw-bold">{{ $tenant->firma_adi ?? 'Bilinmiyor' }}</span>
              </div>
              <div class="info-item">
                Firma Kodu:
                <span class="info-value fw-bold storage-status-badge success" style="font-size: 13px;">{{ $tenant->firma_kodu ?? '' }} </span>
              </div>
              <div class="info-item">
                Adres:
                <span class="info-value">{{ $tenant->adres ?? '-'}}</span>
              </div>
              <div>
                İletişim Kişisi:
                <span class="info-value">{{ $tenant->name ?? '-' }}</span>
              </div>
              <div>
                Vergi No:
                <span class="info-value">{{ $tenant->vergiNo ?? '-' }}</span>
              </div>
              <div>
                Vergi Dairesi:
                <span class="info-value">{{ $tenant->vergiDairesi ?? '-' }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- İletişim Bilgileri -->
        <div class="col-md-6">
          <div class="info-card">
            <h6 class="info-card-title">İletişim Bilgileri</h6>
            <div class="info-content">
              <div class="info-item">
               Telefon:
                <span class="info-value">{{ $tenant->tel1 ?? $tenant->tel2 }}</span>
              </div>
              <div class="info-item">
                E-posta:
                <span class="info-value">{{ $tenant->eposta }}</span>
              </div>
              <div class="info-item">
                Kayıt tarihi:
                <span class="info-value">{{ $tenant->created_at->format('d.m.Y H:i') }}</span>
              </div>
              <div>
                Bitiş tarihi:
                <span class="info-value">{{ \Carbon\Carbon::parse($tenant->bitisTarihi)->format('d.m.Y') }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Abonelik Bilgileri -->

@php
  // Mevcut abonelik kodu (blade php kısmını koruyoruz)
  $currentSubscription = null;
  $isTrialFromTenant = false;
  
  if ($tenant->status == 1) {
      if ($tenant->subscription_status === 'trial' && $tenant->trial_ends_at && $tenant->trial_ends_at->isFuture()) {
          $isTrialFromTenant = true;
          $currentSubscription = $tenant->currentSubscription;
      } else if ($tenant->activeSubscription) {
          $currentSubscription = $tenant->activeSubscription;
      } else {
          $currentSubscription = $tenant->currentSubscription;
      }
  } else {
      $currentSubscription = $tenant->currentSubscription;
  }
  
  // Progress hesaplamaları
  $remainingDays = 0;
  $usedDays = 0;
  $totalDays = 30;
  $timePercentage = 0;
  
  if($isTrialFromTenant && $tenant->trial_ends_at) {
      $remainingDays = $tenant->trial_ends_at->diffInDays(now());
      $totalDays = $tenant->created_at->diffInDays($tenant->trial_ends_at);
      $usedDays = $totalDays - $remainingDays;  // Kullanılan gün = Toplam - Kalan
  } elseif($currentSubscription) {
      $remainingDays = $currentSubscription->getRemainingDays() ?? 0;
      if($currentSubscription->starts_at && $currentSubscription->ends_at) {
          $totalDays = $currentSubscription->starts_at->diffInDays($currentSubscription->ends_at);
          // Kullanılan gün = Başlangıçtan şimdiye kadar geçen gün
          $usedDays = now()->diffInDays($currentSubscription->starts_at);
          // Eğer usedDays negatifse (henüz başlamamışsa) 0 yap
          $usedDays = max(0, $usedDays);
      }
  }
  
   if($totalDays > 0) {
      $timePercentage = ($usedDays / $totalDays) * 100;
      $timePercentage = min(100, $timePercentage); // Maximum 100% olsun
  }
@endphp

@if($isTrialFromTenant || ($currentSubscription && $currentSubscription->plansubs))
<div class="unified-subscription-storage-widget">
    <!-- Widget Header -->
    {{-- <div class="widget-header">
        <div class="widget-title">
            <span>Abonelik ve Depolama Bilgileri</span>
            @if($isTrialFromTenant)
                <span class="status-badge status-trial">Deneme</span>
            @elseif($currentSubscription && $currentSubscription->status === 'active')
                <span class="status-badge status-success">Aktif</span>
            @else
                <span class="status-badge status-inactive">Pasif</span>
            @endif
        </div>
        <div class="widget-actions">
            <button class="btn-refresh" onclick="refreshUnifiedWidget()" title="Yenile">
                <i class="fas fa-sync-alt"></i>
            </button>
           
        </div>
    </div> --}}

    <!-- Main Content Area -->
    <div class="widget-main-content">
        <!-- Left Section: Subscription Info -->
        <div class="subscription-section">
            <div class="section-header">
                <span>Abonelik Paket Detayları</span>
            </div>
            
            {{-- <div class="plan-info">
                <div class="plan-name">
                    @if($isTrialFromTenant)
                        Ücretsiz Deneme
                    @else
                        {{ $currentSubscription->plansubs->name ?? 'Bilinmeyen Plan' }}
                    @endif
                </div>
                <div class="plan-price">
                    @if($isTrialFromTenant)
                        Ücretsiz
                    @else
                        {{ $currentSubscription->plansubs->getFormattedPrice() ?? '₺0,00' }}
                    @endif
                </div>
            </div> --}}

            <!-- Time Progress - Compact -->
            <div class="time-progress-compact">
                <div class="time-stats">
                    <div class="stat">
                      <span class="value">{{ $usedDays }}</span>
                      <span class="label">Kullanılan Gün</span>
                    </div>
                    <div class="stat">
                        <span class="value">{{ $remainingDays }}</span>
                        <span class="label">Kalan Gün</span>
                    </div>
                </div>
                
                <div class="progress-bar-simple">
                    <div class="progress-fill 
                        @if($timePercentage > 75)
                            bg-danger
                        @elseif($timePercentage > 50)
                            bg-warning
                        @else
                            bg-success
                        @endif" style="width: {{ $timePercentage }}%">
                    </div>
                </div>
                
                <div class="date-info">
                    <small>
                        @if($isTrialFromTenant)
                            {{ $tenant->trial_ends_at->format('d.m.Y') }} tarihinde sona erecek
                        @else
                            {{ $currentSubscription->ends_at ? $currentSubscription->ends_at->format('d.m.Y') : 'Belirsiz' }} tarihinde sona erecek
                        @endif
                    </small>
                </div>
            </div>

            <div class="subscription-details-inline">
            <div class="subscription-details">
                <div class="detail-row">
                    <div class="detail-label">Paket:</div>
                    <div class="detail-value">
                        @if($isTrialFromTenant)
                            Ücretsiz Deneme
                        @else
                            {{ $currentSubscription->plansubs->name ?? 'Bilinmeyen Plan' }}
                        @endif
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Durum:</div>
                    <div class="detail-value">
                        @if($isTrialFromTenant)
                            <span class="status-active">Deneme</span>
                        @elseif($currentSubscription && $currentSubscription->status === 'active')
                            <span class="status-active">Aktif</span>
                        @elseif($currentSubscription && $currentSubscription->status === 'trial')
                            <span class="status-trial">Deneme</span>
                        @else
                            <span class="status-inactive">{{ $currentSubscription ? ucfirst($currentSubscription->status) : 'Pasif' }}</span>
                        @endif
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Fiyat:</div>
                    <div class="detail-value price">
                        @if($isTrialFromTenant)
                            Ücretsiz
                        @else
                            {{ $currentSubscription->plansubs->getFormattedPrice() ?? '₺0,00' }}
                        @endif
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Kalan Süre:</div>
                    <div class="detail-value remaining">
                        @if($isTrialFromTenant)
                            {{ $tenant->trial_ends_at->diffInDays(now()) }} gün
                        @else
                            {{ $currentSubscription->getRemainingDays() ?? 0 }} gün
                        @endif
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Bitiş Tarihi:</div>
                    <div class="detail-value">
                        @if($isTrialFromTenant)
                            {{ $tenant->trial_ends_at->format('d.m.Y') }}
                        @else
                            {{ $currentSubscription->ends_at ? $currentSubscription->ends_at->format('d.m.Y') : 'Belirsiz' }}
                        @endif
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Kullanıcı Sayısı:</div>
                    <div class="detail-value">
                        @if($isTrialFromTenant)
                            @if($tenant->personelSayisi == -1)
                                Sınırsız
                            @elseif($tenant->personelSayisi)
                                {{ $tenant->personelSayisi }}
                            @else
                                Belirsiz
                            @endif
                        @elseif(isset($currentSubscription->plansubs->limits['users']))
                            @if($currentSubscription->plansubs->limits['users'] == -1)
                                Sınırsız
                            @elseif($currentSubscription->plansubs->limits['users'] == 0)
                                Yok
                            @else
                                {{ number_format($currentSubscription->plansubs->limits['users']) }}
                            @endif
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Bayi Sayısı:</div>
                    <div class="detail-value">
                        @if($isTrialFromTenant)
                            @if($tenant->bayiSayisi == -1)
                                Sınırsız
                            @elseif($tenant->bayiSayisi)
                                {{ $tenant->bayiSayisi }}
                            @else
                                Belirsiz
                            @endif
                        @elseif(isset($currentSubscription->plansubs->limits['dealers']))
                            @if($currentSubscription->plansubs->limits['dealers'] == -1)
                                Sınırsız
                            @elseif($currentSubscription->plansubs->limits['dealers'] == 0)
                                Yok
                            @else
                                {{ number_format($currentSubscription->plansubs->limits['dealers']) }}
                            @endif
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Stok Ürün Sayısı:</div>
                    <div class="detail-value">
                        @if($isTrialFromTenant)
                            @if($tenant->stokSayisi == -1)
                                Sınırsız
                            @elseif($tenant->stokSayisi)
                                {{ $tenant->stokSayisi }}
                            @else
                                Belirsiz
                            @endif
                        @elseif(isset($currentSubscription->plansubs->limits['stocks']))
                            @if($currentSubscription->plansubs->limits['stocks'] == -1)
                                Sınırsız
                            @elseif($currentSubscription->plansubs->limits['stocks'] == 0)
                                Yok
                            @else
                                {{ number_format($currentSubscription->plansubs->limits['stocks']) }}
                            @endif
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Konsinye Sayısı:</div>
                    <div class="detail-value">
                        @if($isTrialFromTenant)
                            @if($tenant->konsinyeSayisi == -1)
                                Sınırsız
                            @elseif($tenant->konsinyeSayisi)
                                {{ $tenant->konsinyeSayisi }}
                            @else
                                Belirsiz
                            @endif
                        @elseif(isset($currentSubscription->plansubs->limits['konsinye']))
                            @if($currentSubscription->plansubs->limits['konsinye'] == -1)
                                Sınırsız
                            @elseif($currentSubscription->plansubs->limits['konsinye'] == 0)
                                Yok
                            @else
                                {{ number_format($currentSubscription->plansubs->limits['konsinye']) }}
                            @endif
                        @else
                            -
                        @endif
                    </div>
                </div>
                @if(!$isTrialFromTenant && isset($currentSubscription->plansubs->limits['tickets_per_month']))
                <div class="detail-row">
                    <div class="detail-label">Aylık Destek Sayısı:</div>
                    <div class="detail-value">
                        @if($currentSubscription->plansubs->limits['tickets_per_month'] == -1)
                            Sınırsız
                        @elseif($currentSubscription->plansubs->limits['tickets_per_month'] == 0)
                            Yok
                        @else
                            {{ number_format($currentSubscription->plansubs->limits['tickets_per_month']) }}
                        @endif
                    </div>
                </div>
                @endif
   
            </div>
        </div>




        </div>

        <!-- Center Divider -->
        <div class="widget-divider"></div>

        <!-- Right Section: Storage Info -->

<div class="storage-section">
    <div class="section-header">
        <span>Depolama Alanı Detayları</span>
@php
    // Backend'den gelen yüzdeyi al
    $storageUsagePercent = isset($storageInfo['usage_percentage']) ? floatval($storageInfo['usage_percentage']) : 0;
    
    // Backend yüzdesi yanlışsa (0 veya çok düşükse) manuel hesapla
    if ($storageUsagePercent <= 0.001) {  // 0.001'den küçükse manuel hesapla
        $usageFormatted = $storageInfo['current_usage_formatted'] ?? '0 B';
        $limitFormatted = $storageInfo['limit_formatted'] ?? '1 GB';
        
        $usageBytes = 0;
        $limitBytes = 0;
        
        // Kullanılan alan hesabı
        if (strpos($usageFormatted, 'KB') !== false) {
            $usageBytes = floatval(str_replace(['KB', ' '], '', $usageFormatted)) * 1024;
        } elseif (strpos($usageFormatted, 'MB') !== false) {
            $usageBytes = floatval(str_replace(['MB', ' '], '', $usageFormatted)) * 1024 * 1024;
        } elseif (strpos($usageFormatted, 'GB') !== false) {
            $usageBytes = floatval(str_replace(['GB', ' '], '', $usageFormatted)) * 1024 * 1024 * 1024;
        } else {
            $usageBytes = floatval(str_replace([' ', 'B'], '', $usageFormatted));
        }
        
        // Limit hesabı
        if (strpos($limitFormatted, 'KB') !== false) {
            $limitBytes = floatval(str_replace(['KB', ' '], '', $limitFormatted)) * 1024;
        } elseif (strpos($limitFormatted, 'MB') !== false) {
            $limitBytes = floatval(str_replace(['MB', ' '], '', $limitFormatted)) * 1024 * 1024;
        } elseif (strpos($limitFormatted, 'GB') !== false) {
            $limitBytes = floatval(str_replace(['GB', ' '], '', $limitFormatted)) * 1024 * 1024 * 1024;
        } else {
            $limitBytes = floatval(str_replace([' ', 'B'], '', $limitFormatted));
        }
        
        // Yüzde hesapla
        if ($limitBytes > 0) {
            $storageUsagePercent = ($usageBytes / $limitBytes) * 100;
            $storageUsagePercent = round($storageUsagePercent, 3);  // 3 ondalık basamak
        }
    }
    
    $usedFormatted = $storageInfo['current_usage_formatted'] ?? '0 B';
    $limitFormatted = $storageInfo['limit_formatted'] ?? '0 GB';
@endphp
        
        @if($storageUsagePercent >= 90)
            <span class="storage-status-badge danger">Kritik</span>
        @elseif($storageUsagePercent >= 80)
            <span class="storage-status-badge warning">Dikkat</span>
        @else
            <span class="storage-status-badge success">Normal</span>
        @endif
    </div>

    <!-- Storage Progress - Compact -->
    <div class="storage-progress-compact">
        <div class="storage-stats">
            <div class="stat">
                <span class="value">{{ number_format($storageUsagePercent, 3) }}%</span>
                <span class="label">Kullanım</span>
            </div>
            <div class="stat">
                <span class="value">{{ $usedFormatted }}</span>
                <span class="label">Kullanılan</span>
            </div>
        </div>
        
        <div class="progress-bar-simple">
            <div class="progress-fill 
                @if($storageUsagePercent >= 90)
                    bg-danger
                @elseif($storageUsagePercent >= 80)
                    bg-warning
                @else
                    bg-success
                @endif" style="width: {{ $storageUsagePercent }}%">
            </div>
        </div>
        
        <div class="storage-info">
            <small>{{ $usedFormatted }} kullanılan / {{ $limitFormatted }} toplam</small>
        </div>
    </div>
        <div class="storage-details-section">
            <div class="storage-breakdown-container" id="storageBreakdownContainer">
                <div class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-white" role="status">
                        <span class="visually-hidden">Yükleniyor...</span>
                    </div>
                    <p class="mt-2 mb-0 small">Depolama bilgileri yükleniyor...</p>
                </div>
            </div>
        </div>
                {{-- @if(!$isTrialFromTenant && isset($currentSubscription->plansubs->limits['storage_gb']))
                <div class="subscription-storage-detail">
                    <div class="detail-label">Depolama:</div>
                    <div class="detail-value">
                        @if($currentSubscription->plansubs->limits['storage_gb'] == -1)
                            {{ $storageInfo['current_usage_formatted'] ?? '0 B' }} / Sınırsız
                        @elseif($currentSubscription->plansubs->limits['storage_gb'] == 0)
                            Yok
                        @else
                            @php
                                $totalGB = isset($storageInfo['total_limit_gb']) ? $storageInfo['total_limit_gb'] : 0;
                                $extraStorage = isset($storageInfo['extra_storage_gb']) ? $storageInfo['extra_storage_gb'] : 0;
                                $percentage = isset($storageInfo['usage_percentage']) ? $storageInfo['usage_percentage'] : 0;
                            @endphp
                            {{ $storageInfo['current_usage_formatted'] ?? '0 B' }} / {{ number_format($totalGB, 0) }} GB
                            @if($extraStorage > 0)
                                <small class="storage-extra">(+{{ number_format($extraStorage, 0) }} GB ek)</small>
                            @endif
                            @if($percentage > 0)
                                <div class="storage-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: {{ min($percentage, 100) }}%"></div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
                @endif --}}

<!-- Alert Messages -->
@if($storageUsagePercent >= 95)
    <div class="unified-alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        <div class="alert-content">
            <strong>Depolama Alanı Doldu!</strong>
            <span>%{{ $storageUsagePercent }} kullanıldı. Yeni dosya yükleyemezsiniz.</span>
        </div>
    </div>
@elseif($storageUsagePercent >= 80)
    <div class="unified-alert alert-warning">
        <i class="fas fa-exclamation-circle"></i>
        <div class="alert-content">
            <strong>Depolama Alanı Azalıyor</strong>
            <span>%{{ $storageUsagePercent }} kullanıldı. Yakında limit dolacak.</span>
        </div>
    </div>
@endif
        </div>
    </div>
</div>
@else
  <div class="no-subscription">
      <div class="no-subscription-text">
          Bu firmanın aktif bir aboneliği bulunmamaktadır.
      </div>
  </div>
@endif
<!-- subscription-section sonu -->
    </div>
    <!-- customer-subscription tab-pane sonu -->

    <!-- İKİNCİ TAB: ÖDEME BİLGİLERİ -->
    <div class="tab-pane fade" id="payment-info">
      <!-- Loading spinner -->
      <div id="payment-loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Yükleniyor...</span>
        </div>
        <p class="mt-2 text-muted">Ödeme bilgileri yükleniyor...</p>
      </div>
      
      <!-- Ana içerik -->
      <div id="payment-content" style="display: none;">
        
        <!-- Ödeme Özeti Kartları -->
        <div class="row">
          <div class="col-lg-3 col-md-6 col-6">
            <div class="card border-success">
              <div class="card-body text-center">
                <i class="fas fa-check-circle text-success fa-x "></i>
                <h4 class="text-success mb-1" id="summary-completed">₺0</h4>
                <small class="text-muted">Tamamlanan Ödemeler</small>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 col-6">
            <div class="card border-warning">
              <div class="card-body text-center">
                <i class="fas fa-clock text-warning fa-x "></i>
                <h4 class="text-warning mb-1" id="summary-pending">₺0</h4>
                <small class="text-muted">Bekleyen Ödemeler</small>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 mb-3 col-6">
            <div class="card border-danger">
              <div class="card-body text-center">
                <i class="fas fa-times-circle text-danger fa-x "></i>
                <h4 class="text-danger mb-1" id="summary-failed">₺0</h4>
                <small class="text-muted">Başarısız Ödemeler</small>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 mb-3 col-6">
            <div class="card border-info">
              <div class="card-body text-center">
                <i class="fas fa-undo text-info fa-x"></i>
                <h4 class="text-info mb-1" id="summary-refunded">₺0</h4>
                <small class="text-muted">İade Edilen</small>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Filtre ve Arama -->
           <!-- Ödeme Geçmişi -->
          <div class="card card-tenants mb-2">
            <div class="card-header card-tenants-header bg-light" style="background-color: white !important;">
              <div class="row align-items-center">
                <div class="col-md-6">
                  <h6 class="mb-2 mt-1">Ödeme Geçmişi</h6>
                </div>
                <div class="col-md-6 text-end">
                  <div class="d-flex gap-2 justify-content-end">
                    <!-- Durum Filtresi -->
                    <select class="form-select form-select-sm" id="status-filter" style="width: 120px;">
                      <option value="">Tüm Durumlar</option>
                      <option value="completed">Tamamlandı</option>
                      <option value="pending">Bekliyor</option>
                      <option value="failed">Başarısız</option>
                      <option value="refunded">İade Edildi</option>
                      <option value="canceled">İptal Edildi</option>
                    </select>
                    
                    <!-- Tür Filtresi -->
                    <select class="form-select form-select-sm" id="type-filter" style="width: 120px;">
                      <option value="">Tüm Türler</option>
                      <option value="subscription">Abonelik</option>
                      <option value="storage">Depolama</option>
                      <option value="integration">Entegrasyon</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-body card-tenants-body p-0">
              <div class="table-responsive">
                <table class="table  mb-0" id="payments-table">
                  <thead class="table-light">
                    <tr>
                      <th>Tarih</th>
                      <th>Tür</th>
                      <th>Açıklama</th>
                      <th>Tutar</th>
                      <th>Ödeme Yöntemi</th>
                      <th>Durum</th>
                      <th>İşlemler</th>
                    </tr>
                  </thead>
                  <tbody id="payments-table-body">
                    <!-- Dinamik içerik buraya gelecek -->
                  </tbody>
                </table>
              </div>
              
              <!-- Boş durum mesajı -->
              <div id="no-payments-message" class="text-center py-5" style="display: none;">
                <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Henüz ödeme kaydı bulunmamaktadır</h5>
                <p class="text-muted">Bu firmaya ait herhangi bir ödeme işlemi görünmüyor.</p>
              </div>
            </div>
          </div>
          <!-- Ödeme Özeti - Sadece Toplam -->
          <div class="tableToplamaAlani odemeToplamaAlani mt-3">
            <div class="row r1">
              <div class="sol"><strong>Genel Toplam</strong></div>
              <div class="sag">
                <div class="tur t1 completedAmount"><span>Tamamlanan:</span></div>
                <div class="tur t2 pendingAmount"><span>Bekleyen:</span></div>
                <div class="tur t3 failedAmount"><span>Başarısız:</span></div>
                <div class="tur t5 refundedAmount"><span>İade:</span></div>
                <div class="tur t4 totalPaidAmount"><span>Toplam:</span></div>
              </div>
            </div>
          </div>
        
        <!-- Sayfalama -->
        <div class="d-flex justify-content-between align-items-center">
          <nav>
            <ul class="pagination pagination-sm mb-0" id="payments-pagination">
              <!-- Dinamik sayfalama -->
            </ul>
          </nav>
        </div>
      </div>
      
      <!-- Hata durumu -->
      <div id="payment-error" class="text-center py-5" style="display: none;">
        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
        <h5 class="text-warning">Ödeme bilgileri yüklenemedi</h5>
        <p class="text-muted">Bir hata oluştu. Lütfen sayfayı yenileyin veya daha sonra tekrar deneyin.</p>
        <button class="btn btn-outline-primary" onclick="PaymentModule.loadPaymentInfo(PaymentModule.currentTenantId)">
          <i class="fas fa-sync-alt me-1"></i>Tekrar Dene
        </button>
      </div>
    </div>
    <!-- payment-info tab-pane sonu -->

    <!-- ÜÇÜNCÜ TAB: SERVİS İSTATİSTİKLERİ -->
    <div class="tab-pane fade" id="service-stats">
      <!-- Servis İstatistikleri -->
      <div class="border rounded p-2">
        <!-- Period Navigation - Daha dar tasarım -->
        <div class="service-period-nav mb-2" role="tablist">
          @foreach($periodStats as $key => $period)
            <button class="service-period-btn {{ $key === 'bugun' ? 'active' : '' }}" 
                    data-period="{{ $key }}" 
                    data-target="#period-{{ $key }}"
                    type="button">
              <span class="period-label">{{ $period['label'] }}</span>
              <span class="period-count">{{ $period['toplam'] }}</span>
            </button>
          @endforeach
        </div>

        <!-- Tab Content -->
        <div class="tab-content">
          @foreach($periodStats as $key => $period)
            <div class="tab-pane {{ $key === 'bugun' ? 'show active' : '' }}" 
                 id="period-{{ $key }}">
              <div class="row g-2">
                <!-- Markalar -->
                <div class="col-lg-3 col-md-6">
                  <div class="service-stats-card">
                    <div class="service-stats-card-header">
                      <span class="stats-title">Markalar</span>
                    </div>
                    <div class="service-stats-card-body">
                      @forelse($period['markalar'] as $marka)
                        <div class="stats-item">
                          <span class="stats-name">{{ $marka->marka }}</span>
                          <span class="stats-count">{{ $marka->sayi }}</span>
                        </div>
                      @empty
                        <div class="stats-empty">Kayıt yok</div>
                      @endforelse
                    </div>
                  </div>
                </div>

                <!-- Türler -->
                <div class="col-lg-3 col-md-6">
                  <div class="service-stats-card">
                    <div class="service-stats-card-header">
                      <span class="stats-title">Cihaz Türleri</span>
                    </div>
                    <div class="service-stats-card-body">
                      @forelse($period['turler'] as $tur)
                        <div class="stats-item">
                          <span class="stats-name">{{ $tur->cihaz }}</span>
                          <span class="stats-count">{{ $tur->sayi }}</span>
                        </div>
                      @empty
                        <div class="stats-empty">Kayıt yok</div>
                      @endforelse
                    </div>
                  </div>
                </div>

                <!-- Kaynaklar -->
                <div class="col-lg-3 col-md-6">
                  <div class="service-stats-card">
                    <div class="service-stats-card-header">
                      <span class="stats-title">Kaynaklar</span>
                    </div>
                    <div class="service-stats-card-body">
                      @forelse($period['kaynaklar'] as $kaynak)
                        <div class="stats-item">
                          <span class="stats-name">{{ $kaynak->kaynak }}</span>
                          <span class="stats-count">{{ $kaynak->sayi }}</span>
                        </div>
                      @empty
                        <div class="stats-empty">Kayıt yok</div>
                      @endforelse
                    </div>
                  </div>
                </div>

                <!-- Personeller -->
                <div class="col-lg-3 col-md-6">
                  <div class="service-stats-card">
                    <div class="service-stats-card-header">
                      <span class="stats-title">Personeller</span>
                    </div>
                    <div class="service-stats-card-body">
                      @forelse($period['operatorler'] as $operator)
                        <div class="stats-item">
                          <span class="stats-name">{{ $operator->name }}</span>
                          <span class="stats-count">{{ $operator->sayi }}</span>
                        </div>
                      @empty
                        <div class="stats-empty">Kayıt yok</div>
                      @endforelse
                    </div>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
        
        <!-- Toplam Servis Sayısı - Alt kısma taşındı -->
        <div class="service-total-footer">
          <div class="service-total-badge">
            <span class="service-total-number">{{ $topServisSayisi ?? 0 }}</span>
            <small class="service-total-label">Toplam Genel Servis Sayısı</small>
          </div>
        </div>
      </div>
    </div>
    <!-- service-stats tab-pane sonu -->

  </div>
  <!-- tab-content sonu -->
</div>
<!-- card-body sonu -->

<!-- Footer -->
<div class="card-footer bg-light d-flex justify-content-end align-items-center p-2">
  <div>
    <form action="{{ route('super.admin.tenant.toggle.status', [$tenant->id,$tenant->id]) }}" method="POST" style="display: inline;">
      @csrf
      @if($tenant->status == 1)
        <button type="submit" class="btn btn-danger btn-sm">Pasif Yap</button>
      @else
        <button type="submit" class="btn btn-success btn-sm">Aktif Et</button>
      @endif
    </form>
  </div>
</div>



<!-- Payment Detail Modal -->
<div class="modal fade" id="paymentDetailModal" tabindex="-1" aria-labelledby="paymentDetailModalLabel" aria-hidden="true"style="padding-top: 70px; background: rgba(0, 0, 0, 0.50);">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="paymentDetailModalLabel">
           ÖDEME DETAYLARI
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="paymentDetailContent">
        <!-- Loading State -->
        <div id="paymentDetailLoading" class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Yükleniyor...</span>
          </div>
          <p class="mt-2 text-muted">Ödeme detayları yükleniyor...</p>
        </div>
        
        <!-- Content will be loaded here -->
        <div id="paymentDetailData" style="display: none;">
          <!-- Dynamic content -->
        </div>
        
        <!-- Error State -->
        <div id="paymentDetailError" style="display: none;" class="text-center py-4">
          <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
          <h5 class="text-warning">Detaylar yüklenemedi</h5>
          <p class="text-muted">Bir hata oluştu. Lütfen tekrar deneyin.</p>
        </div>
      </div>
      
    </div>
  </div>
</div>


<script>
/// Global depolama fonksiyonu - BASİT VERSİYON
function loadStorageDetails() {
  const tenantId = $('#current-tenant-id').val();
  const container = $('#storage-breakdown-container');
  
  if (!tenantId) {
    container.html('<div class="text-center py-3"><small class="text-muted">Tenant ID bulunamadı</small></div>');
    return;
  }
  
  $.ajax({
    url: `/super-admin/tenant/${tenantId}/storage-details`,
    method: 'GET',
    success: function(response) {
      console.log('Storage Response:', response);
      
      if (response.success) {
        let storageHtml = '<div class="storage-breakdown-list">';
        
        // Servis Fotoğrafları
        storageHtml += `
          <div class="storage-item">
            <div class="storage-type">Servis Fotoğrafları</div>
            <div class="storage-size">${response.details.service_photos.count} adet</div>
          </div>`;
        
        // Stok Fotoğrafları
        storageHtml += `
          <div class="storage-item">
            <div class="storage-type">Stok Fotoğrafları</div>
            <div class="storage-size">${response.details.stock_photos.count} adet</div>
          </div>`;
        
        // Diğer Dosyalar
        storageHtml += `
          <div class="storage-item">
            <div class="storage-type">Diğer Dosyalar</div>
            <div class="storage-size">${response.details.other_files.total_count} adet</div>
          </div>`;
        
        // Toplam
        let totalSize = response.storage_info.current_usage_formatted || '0 B';
        storageHtml += `
          <div class="storage-item total">
            <div>Toplam Kullanım</div>
            <div class="storage-size">${totalSize}</div>
          </div>
        </div>`;
        
        container.html(storageHtml);
        
      } else {
        container.html(`
          <div class="text-center py-3">
            <small class="text-muted text-danger">Depolama bilgileri yüklenemedi</small>
          </div>
        `);
      }
    },
    error: function(xhr, status, error) {
      console.error('Storage AJAX Error:', xhr.responseText);
      container.html(`
        <div class="text-center py-3">
          <small class="text-muted text-danger">Hata oluştu</small>
        </div>
      `);
    }
  });
}

// Modül pattern kullanarak namespace çakışmalarını önle
window.PaymentModule = (function() {
  'use strict';

  // Ödeme detayını göster - Updated implementation
  function showPaymentDetail(type, paymentId) {
    console.log('Ödeme detayı isteniyor:', type, paymentId, 'Tenant ID:', currentTenantId);
    
    if (!currentTenantId || !paymentId) {
      console.error('Geçersiz parametreler');
      return;
    }
    
    // Modal'ı aç
    const modal = new bootstrap.Modal(document.getElementById('paymentDetailModal'));
    modal.show();
    
    // Loading durumunu göster
    $('#paymentDetailLoading').show();
    $('#paymentDetailData').hide();
    $('#paymentDetailError').hide();
    
    // AJAX ile detayları getir
    $.ajax({
      url: `/super-admin/tenant/${currentTenantId}/payment/${type}/${paymentId}`,
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        console.log('Ödeme detayı başarılı:', response);
        if (response.success) {
          displayPaymentDetail(response.payment);
        } else {
          showPaymentDetailError();
        }
      },
      error: function(xhr, status, error) {
        console.error('Ödeme detayı hatası:', {
          status: status,
          error: error,
          response: xhr.responseText
        });
        showPaymentDetailError();
      }
    });
  }
  
  // Ödeme detayını göster
  function displayPaymentDetail(payment) {
    const detailHtml = createPaymentDetailHtml(payment);
    $('#paymentDetailData').html(detailHtml).show();
    $('#paymentDetailLoading').hide();
    $('#paymentDetailError').hide();
  }

  // PaymentModule içine bu fonksiyonu ekleyin (showPaymentDetail fonksiyonundan önce)
function translatePaymentMethod(method) {
  if (!method) return 'Belirtilmemiş';
  
  const translations = {
    'credit_card': 'Kredi Kartı',
    'debit_card': 'Banka Kartı',
    'cash': 'Nakit',
    'bank_transfer': 'Banka Transferi',
    'eft': 'EFT',
    'havale': 'Havale',
    'paypal': 'PayPal',
    'stripe': 'Stripe',
    'iyzico': 'Iyzico'
  };
  
  // Önce küçük harfe çevir ve alt çizgi varsa kontrol et
  const methodLower = method.toLowerCase().trim();
  return translations[methodLower] || method;
}
  
  // Ödeme detay HTML'i oluştur (Depolama bilgileri kaldırıldı)
  function createPaymentDetailHtml(payment) {
    const statusBadge = getStatusBadge(payment.status);
    const typeBadge = getTypeBadge(payment.type, payment.type_label);
    
    return `
      <div class="row g-1">
        <div class="col-md-6">
          <div class="card mb-2">
            <div class="card-header bg-light ms-2">
              <h6 class="mb-0">Genel Bilgiler</h6>
            </div>
            <div class="card-body">
              <div class="row mb-2">
                <div class="col-5"><strong>Ödeme ID:</strong></div>
                <div class="col-7">#${payment.id}</div>
              </div>
              <div class="row mb-2">
                <div class="col-5"><strong>Tür:</strong></div>
                <div class="col-7">${typeBadge}</div>
              </div>
              <div class="row mb-2">
                <div class="col-5"><strong>Durum:</strong></div>
                <div class="col-7">${statusBadge}</div>
              </div>
              <div class="row mb-2">
                <div class="col-5"><strong>Tutar:</strong></div>
                <div class="col-7"><span class="fw-bold">₺${formatMoney(payment.amount || 0)}</span></div>
              </div>
              <div class="row mb-2">
                <div class="col-5"><strong>Açıklama:</strong></div>
                <div class="col-7">${payment.description || '-'}</div>
              </div>
              ${payment.plan_name ? `
              <div class="row mb-2">
                <div class="col-5"><strong>Plan:</strong></div>
                <div class="col-7">${payment.plan_name}</div>
              </div>
              ` : ''}
            </div>
          </div>
        </div>
        
        <div class="col-md-6">
          <div class="card mb-3">
            <div class="card-header bg-light ms-2">
              <h6 class="mb-0">Ödeme Bilgileri</h6>
            </div>
            <div class="card-body">
              <div class="row mb-2">
                <div class="col-5"><strong>Ödeme Yöntemi:</strong></div>
                <div class="col-7">${translatePaymentMethod(payment.payment_method)}</div>
              </div>
              ${payment.gateway ? `
              <div class="row mb-2">
                <div class="col-5"><strong>Gateway:</strong></div>
                <div class="col-7">${payment.gateway}</div>
              </div>
              ` : ''}
              ${payment.transaction_id ? `
              <div class="row mb-2">
                <div class="col-5"><strong>İşlem ID:</strong></div>
                <div class="col-7">${payment.transaction_id}</div>
              </div>
              ` : ''}
              <div class="row mb-2">
                <div class="col-5"><strong>Oluşturulma Tarihi:</strong></div>
                <div class="col-7">${formatDate(payment.created_at)}</div>
              </div>
              ${payment.paid_at ? `
              <div class="row mb-2">
                <div class="col-5"><strong>Ödenme Tarihi:</strong></div>
                <div class="col-7">${formatDate(payment.paid_at)}</div>
              </div>
              ` : ''}
            </div>
          </div>
        </div>
      </div>
    `;
  }
  
  // Ödeme detay hatasını göster
  function showPaymentDetailError() {
    $('#paymentDetailLoading').hide();
    $('#paymentDetailData').hide();
    $('#paymentDetailError').show();
  }
  
  // Private değişkenler
  let currentTenantId = null;
  let allPayments = [];
  let filteredPayments = [];
  let currentPage = 1;
  const itemsPerPage = 10;
  let initialized = false;

  // Ödeme bilgilerini yükle
  function loadPaymentInfo(tenantId) {
    if (!tenantId || tenantId === 'null' || tenantId === null) {
      console.error('Geçersiz tenant ID:', tenantId);
      showPaymentError();
      return;
    }
    
    showPaymentLoading();
    
    $.ajax({
      url: `/super-admin/tenant/${tenantId}/payments`,
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        if (response.success) {
          allPayments = response.payments || [];
          updatePaymentCards(response.summary);  // Üst kartlar
          updatePaymentSummary(response.summary); // Alt toplam alanı
          applyFilters();
          showPaymentContent();
        } else {
          console.error('API yanıt hatası:', response.message);
          showPaymentError();
        }
      },
      error: function(xhr, status, error) {
        console.error('AJAX hatası:', {
          status: status,
          error: error,
          response: xhr.responseText,
          xhr: xhr
        });
        showPaymentError();
      }
    });
  }

  // UI durumları
  function showPaymentLoading() {
    $('#payment-loading').show();
    $('#payment-content').hide();
    $('#payment-error').hide();
  }

  function showPaymentContent() {
    $('#payment-loading').hide();
    $('#payment-content').show();
    $('#payment-error').hide();
  }

  function showPaymentError() {
    $('#payment-loading').hide();
    $('#payment-content').hide();
    $('#payment-error').show();
  }

  // Ödeme özetini güncelle
  // Üst kartlar
  function updatePaymentCards(summary) {
      $('#summary-completed').text('₺' + (summary.completed_formatted || '0,00'));
      $('#summary-pending').text('₺' + (summary.pending_formatted || '0,00'));
      $('#summary-failed').text('₺' + (summary.failed_formatted || '0,00'));
      $('#summary-refunded').text('₺' + (summary.refunded_formatted || '0,00'));
  }
  // Alt toplam alanını güncelle
   function updatePaymentSummary(summary) {
    $('.completedAmount').html('<span>Tamamlanan:</span> ₺' + (summary.completed_formatted || '0,00'));
    $('.pendingAmount').html('<span>Bekleyen:</span> ₺' + (summary.pending_formatted || '0,00'));
    $('.failedAmount').html('<span>Başarısız:</span> ₺' + (summary.failed_formatted || '0,00'));
    $('.refundedAmount').html('<span>İade:</span> ₺' + (summary.refunded_formatted || '0,00'));
    $('.totalPaidAmount').html('<span>Toplam:</span> ₺' + (summary.total_amount_formatted || '0,00'));
  }


  // Filtreleri uygula
  function applyFilters() {
    const statusFilter = $('#status-filter').val();
    const typeFilter = $('#type-filter').val();
    
    filteredPayments = allPayments.filter(payment => {
      const statusMatch = !statusFilter || payment.status === statusFilter;
      const typeMatch = !typeFilter || payment.type === typeFilter;
      return statusMatch && typeMatch;
    });
    
    currentPage = 1;
    updatePaymentsTable();
    updatePagination();
  }

  // Ödeme tablosunu güncelle
  function updatePaymentsTable() {
  const tbody = $('#payments-table-body');
  tbody.empty();
  
  $('#total-payments-count').text(filteredPayments.length);
  
  if (filteredPayments.length === 0) {
    $('#payments-table').hide();
    $('#no-payments-message').show();
    return;
  }
  
  $('#payments-table').show();
  $('#no-payments-message').hide();
  
  // Sayfalama için veriyi böl
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const pagePayments = filteredPayments.slice(startIndex, endIndex);
  
  pagePayments.forEach(payment => {
    const row = createPaymentRow(payment);
    tbody.append(row);
  });
  
  // Satır tıklama olayı ekle
  $('.payment-row-clickable').off('click').on('click', function() {
    const paymentType = $(this).data('payment-type');
    const paymentId = $(this).data('payment-id');
    showPaymentDetail(paymentType, paymentId);
  });
}
  // Ödeme satırı oluştur
function createPaymentRow(payment) {
  const statusBadge = getStatusBadge(payment.status);
  const typeBadge = getTypeBadge(payment.type, payment.type_label);
  const formattedDate = formatDate(payment.created_at);
  const paidDate = payment.paid_at ? formatDate(payment.paid_at) : null;
  
  return `
    <tr class="payment-row-clickable" data-payment-type="${payment.type}" data-payment-id="${payment.id}" style="cursor: pointer; height: 60px;">
      <td class="align-middle">
        <div class="fw-medium">${formattedDate}</div>
        
      </td>
      <td class="align-middle">${typeBadge}</td>
      <td class="align-middle">
        <div class="fw-medium">${payment.description || '-'}</div>
      </td>
      <td class="align-middle">
        <span class="fw-bold">₺${formatMoney(payment.amount || 0)}</span>
        ${payment.currency && payment.currency !== 'TRY' ? `<br><small class="text-muted">${payment.currency}</small>` : ''}
      </td>
      <td class="align-middle">
        <div>${payment.payment_method || 'Belirtilmemiş'}</div>
        
      </td>
      <td class="align-middle">${statusBadge}</td>
      <td class="align-middle">
        <div class="btn-group btn-group-sm">
          ${createActionButtons(payment)}
        </div>
      </td>
    </tr>
  `;
}

  // Durum badge'i oluştur
  function getStatusBadge(status) {
    const badges = {
      'completed': '<span >Tamamlandı</span>',
      'pending': '<span >Bekliyor</span>',
      'failed': '<span >Başarısız</span>',
      'refunded': '<span >İade Edildi</span>',
      'canceled': '<span >İptal Edildi</span>'
    };
    return badges[status] || '<span >Bilinmeyen</span>';
  }

  // Tür badge'i oluştur
  function getTypeBadge(type, label) {
    if (type === 'subscription') {
      return '<span class="badge bg-primary bg-opacity-10 text-primary">Abonelik</span>';
    } else if (type === 'storage') {
      return '<span class="badge bg-success bg-opacity-10 text-success">Depolama</span>';
    }
    return `<span class="badge bg-secondary bg-opacity-10 text-secondary">${label}</span>`;
  }

  // Aksiyon butonları oluştur

  function createActionButtons(payment) {
    let buttons = '';
    if (payment.invoice_path) {
        let invoPath = '/' + payment.invoice_path; // veya asset URL'nizi buraya
        buttons += `<a href="${invoPath}" class="btn btn-outline-primary btn-sm" title="Faturayı Görüntüle" target="_blank">
                      <i class="fas fa-file-pdf"></i>
                    </a>`;
    }
    
    // Detay butonu
    // buttons += `<button class="btn btn-outline-info btn-sm" onclick="PaymentModule.showPaymentDetail('${payment.type}', ${payment.id})" title="Detayları Görüntüle">
    //               <i class="fas fa-info-circle"></i>
    //             </button>`;
    
    return buttons || '<span class="text-muted">-</span>';

}

  // Sayfalama güncelle
  function updatePagination() {
  const totalPages = Math.ceil(filteredPayments.length / itemsPerPage);
  const pagination = $('#payments-pagination');
  pagination.empty(); // Önce temizle
  
  if (totalPages <= 1) return;
  
  let paginationHtml = ''; // String olarak biriktir
  
  // Önceki sayfa
  const prevDisabled = currentPage === 1 ? 'disabled' : '';
  paginationHtml += `
    <li class="page-item ${prevDisabled}">
      <a class="page-link" href="#" data-page="${currentPage - 1}">
        <i class="fas fa-chevron-left"></i>
      </a>
    </li>
  `;
  
  // Sayfa numaraları
  const startPage = Math.max(1, currentPage - 2);
  const endPage = Math.min(totalPages, currentPage + 2);
  
  // İlk sayfa
  if (startPage > 1) {
    paginationHtml += `
      <li class="page-item">
        <a class="page-link" href="#" data-page="1">1</a>
      </li>
    `;
    if (startPage > 2) {
      paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }
  }
  
  // Orta sayfalar
  for (let i = startPage; i <= endPage; i++) {
    const active = i === currentPage ? 'active' : '';
    paginationHtml += `
      <li class="page-item ${active}">
        <a class="page-link" href="#" data-page="${i}">${i}</a>
      </li>
    `;
  }
  
  // Son sayfa
  if (endPage < totalPages) {
    if (endPage < totalPages - 1) {
      paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }
    paginationHtml += `
      <li class="page-item">
        <a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a>
      </li>
    `;
  }
  
  // Sonraki sayfa
  const nextDisabled = currentPage === totalPages ? 'disabled' : '';
  paginationHtml += `
    <li class="page-item ${nextDisabled}">
      <a class="page-link" href="#" data-page="${currentPage + 1}">
        <i class="fas fa-chevron-right"></i>
      </a>
    </li>
  `;
  
  // Tek seferde ekle
  pagination.html(paginationHtml);
  
  // Click event'lerini bağla
  pagination.find('a.page-link').off('click').on('click', function(e) {
    e.preventDefault();
    const page = parseInt($(this).data('page'));
    if (page && !isNaN(page)) {
      changePage(page);
    }
  });
}

  // Sayfa değiştir
  function changePage(page) {
    const totalPages = Math.ceil(filteredPayments.length / itemsPerPage);
    if (page < 1 || page > totalPages) return;
    
    currentPage = page;
    updatePaymentsTable();
    updatePagination();
  }

  // Yardımcı fonksiyonlar
  function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('tr-TR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

function formatMoney(amount) {
    return new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(amount || 0);
}
  // Event handler'ları başlat
  function initEventHandlers() {
    if (initialized) return;

    // Modal açıldığında tenant ID'yi al - birden fazla yöntemle dene
    $(document).off('show.bs.modal', '#editTenantModal').on('show.bs.modal', '#editTenantModal', function(event) {
      const button = $(event.relatedTarget);
      let tenantId = null;
      
      // Farklı yöntemlerle tenant ID'yi almaya çalış
      tenantId = button.data('bs-id') || button.attr('data-bs-id') || button.data('id') || button.attr('data-id');
      
      if (tenantId && tenantId !== 'null' && tenantId !== null) {
        currentTenantId = tenantId;
        $('#current-tenant-id').val(tenantId);
        
        // Depolama detaylarını da yükle
        setTimeout(function() {
          if ($('#storage-breakdown-container').length > 0) {
            loadStorageDetails();
          }
        }, 500);
      }
    });
    // Storage accordion açıldığında veri yükle
    $(document).off('shown.bs.collapse', '#storageCollapse').on('shown.bs.collapse', '#storageCollapse', function() {
      if ($('#storage-breakdown-container').length > 0) {
        loadStorageDetails();
      }
    });

    // Alternatif olarak, editTenant class'ına sahip elementlere click event'i ekle
    $(document).off('click', '.editTenant').on('click', '.editTenant', function() {
      const tenantId = $(this).data('bs-id') || $(this).attr('data-bs-id');
      
      if (tenantId) {
        currentTenantId = tenantId;
        $('#current-tenant-id').val(tenantId);
        
        // Depolama detaylarını da yükle
        setTimeout(function() {
          if ($('#storage-breakdown-container').length > 0) {
            loadStorageDetails();
          }
        }, 500);
      }
    });

    // Payment tab'ına tıklandığında
    $(document).off('shown.bs.tab', '#payment-info-tab').on('shown.bs.tab', '#payment-info-tab', function() {
      // Eğer currentTenantId null ise, modal'dan almaya çalış
      if (!currentTenantId) {
        const modalTenantId = $('#current-tenant-id').val();
        if (modalTenantId) {
          currentTenantId = modalTenantId;
        }
      }
      
      if (currentTenantId && currentTenantId !== 'null' && currentTenantId !== null) {
        loadPaymentInfo(currentTenantId);
      } else {
        console.error('Tenant ID hala geçersiz:', currentTenantId);
        showPaymentError();
      }
    });

    // Customer subscription tab'ına tıklandığında depolama detaylarını yükle
    $(document).off('shown.bs.tab', '#customer-subscription-tab').on('shown.bs.tab', '#customer-subscription-tab', function() {
      setTimeout(function() {
        if ($('#storage-breakdown-container').length > 0) {
          loadStorageDetails();
        }
      }, 500);
    });

    // Filtre değişiklikleri
    $(document).off('change', '#status-filter, #type-filter').on('change', '#status-filter, #type-filter', function() {
      applyFilters();
    });

    // Servis istatistikleri period switching
    $(document).off('click', '.service-period-btn').on('click', '.service-period-btn', function(e) {
      e.preventDefault();
      
      var $this = $(this);
      var targetSelector = $this.data('target');
      
      // Tab navigation
      $('.service-period-btn').removeClass('active');
      $this.addClass('active');
      
      // Tab content
      $('.tab-pane[id^="period-"]').removeClass('show active');
      $(targetSelector).addClass('show active');
    });
    
    initialized = true;
  }

  // Public API
  return {
    init: initEventHandlers,
    loadPaymentInfo: loadPaymentInfo,
    changePage: changePage,
    showPaymentDetail: showPaymentDetail,
    get currentTenantId() { return currentTenantId; }
  };
})();

// Document ready event
$(document).ready(function() {
  PaymentModule.init();
  
  // Kısa gecikme ile depolama bilgilerini yükle
  setTimeout(function() {
    if ($('#storage-breakdown-container').length > 0) {
      loadStorageDetails();
    }
  }, 1000);
});
</script>

<script>
// Global fonksiyonları tanımla
// function refreshUnifiedWidget() {
//     const widget = document.querySelector('.unified-subscription-storage-widget');
//     const refreshBtn = widget.querySelector('.btn-refresh');
//     const icon = refreshBtn.querySelector('i');
    
//     widget.classList.add('loading');
//     icon.style.animation = 'spin 1s linear infinite';
    
//     // Buraya AJAX çağrısı eklenecek
//     setTimeout(() => {
//         widget.classList.remove('loading');
//         icon.style.animation = '';
        
//         // Depolama detaylarını yükle
//         if (typeof loadStorageDetailsForUnified === 'function') {
//             loadStorageDetailsForUnified();
//         }
//     });
// }



function loadStorageDetailsForUnified() {
    const tenantId = $('#current-tenant-id').val();
    const container = $('#storageBreakdownContainer');
    
    if (!tenantId) {
        container.html('<div class="text-center py-3"><small>Tenant ID bulunamadı</small></div>');
        return;
    }
    
    $.ajax({
        url: `/super-admin/tenant/${tenantId}/storage-details`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                let storageHtml = '<div class="storage-breakdown-list">';
                
                // Servis Fotoğrafları
                storageHtml += `
                    <div class="storage-item">
                        <div class="storage-type">Servis Fotoğrafları</div>
                        <div class="storage-size">${response.details.service_photos.count} adet</div>
                    </div>`;
                
                // Stok Fotoğrafları
                storageHtml += `
                    <div class="storage-item">
                        <div class="storage-type">Stok Fotoğrafları</div>
                        <div class="storage-size">${response.details.stock_photos.count} adet</div>
                    </div>`;
                
                // Diğer Dosyalar
                storageHtml += `
                    <div class="storage-item">
                        <div class="storage-type">Diğer Dosyalar</div>
                        <div class="storage-size">${response.details.other_files.total_count} adet</div>
                    </div>`;
                
                // Toplam
                let totalSize = response.storage_info.current_usage_formatted || '0 B';
                storageHtml += `
                    <div class="storage-item total">
                        <div class="storage-type">Toplam Kullanım</div>
                        <div class="storage-size">${totalSize}</div>
                    </div>
                </div>`;
                
                container.html(storageHtml);
            } else {
                container.html(`
                    <div class="text-center py-3">
                        <small class="text-danger">Depolama bilgileri yüklenemedi</small>
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('Storage details error:', error);
            container.html(`
                <div class="text-center py-3">
                    <small class="text-danger">Hata oluştu</small>
                </div>
            `);
        }
    });
}

// Document ready event
$(document).ready(function() {
    // Kısa gecikme ile depolama bilgilerini yükle
    setTimeout(function() {
        if (typeof loadStorageDetailsForUnified === 'function') {
            loadStorageDetailsForUnified();
        }
    }, 10);
});

</script>

