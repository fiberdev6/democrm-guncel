@extends('frontend.secure.user_master')
@section('user')

<div class="page-content" id="passwords">
    <div class="container-fluid">
        <div class="row pageDetail">
            <div class="col-12">
                <div class="integration-detail">
                    <!-- Breadcrumb -->
                    <div class="breadcrumb-custom">
                        <a href="{{ route('tenant.integrations.marketplace', $tenant->id) }}">
                            <i class="fas fa-arrow-left"></i> Entegrasyonlar
                        </a>
                        <span class="separator">›</span>
                        <span>{{ $integration->name }}</span>
                    </div>

                    <div class="detail-container">
                        <!-- Sol Taraf - Detaylar -->
                        <div class="detail-left">
                            <div class="integration-header">
                                @if($integration->logo)
                                <img src="{{ asset($integration->logo) }}" alt="{{ $integration->name }}" class="integration-logo-large">
                                @else
                                <div class="integration-logo-large d-flex align-items-center justify-content-center">
                                    <i class="fas fa-puzzle-piece fa-3x text-muted"></i>
                                </div>
                                @endif

                                <div class="integration-title-section">
                                    <h1>{{ $integration->name }}</h1>
                                    <p style="color: #757575; font-size: 16px;">{{ $integration->description }}</p>
                                    
                                    <div class="integration-meta">
                                        <div class="meta-item">
                                            <span class="meta-label">Kategori</span>
                                            <span class="meta-value">
                                                @if($integration->category == 'invoice')
                                                    Fatura
                                                @elseif($integration->category == 'sms')
                                                    SMS
                                                @elseif($integration->category == 'accounting')
                                                    Muhasebe
                                                @else
                                                    Diğer
                                                @endif
                                            </span>
                                        </div>
                                        <div class="meta-item">
                                            <span class="meta-label">Son Güncelleme</span>
                                            <span class="meta-value">{{ $integration->updated_at->format('d M Y') }}</span>
                                        </div>
                                    </div>

                                    <div class="integration-tags">
                                        <span class="tag">{{ $integration->category }}</span>
                                        @if($integration->price == 0)
                                        <span class="tag">ücretsiz</span>
                                        @endif
                                        <span class="tag">entegrasyon</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Detaylı Açıklama -->
                            <div class="integration-description">
                                <h2>{{ $integration->name }} Hakkında</h2>
                                {!! $integration->explanation ?: '<p>Bu entegrasyon için detaylı açıklama henüz eklenmemiştir.</p>' !!}
                            </div>
                        </div>

                        <!-- Sağ Taraf - Fiyatlandırma ve Aksiyon -->
                        <div class="detail-right">
                            <div class="pricing-box">
                                <div class="price-display">
                                    @if($integration->price > 0)
                                    <div class="price-amount">₺{{ number_format($integration->price) }}</div>
                                    @else
                                    <div class="price-free">
                                        <i class="fas fa-gift"></i> Ücretsiz
                                    </div>
                                    @endif
                                </div>

                                @if($isPurchased)
                                    @if($isActive)
                                        <div class="alert alert-success" style="padding: 10px; border-radius: 8px;">
                                            <i class="fas fa-check-circle"></i> Bu entegrasyon aktif
                                        </div>
                                    @endif
                                @else
                                    @if($integration->price > 0)
                                        <a href="{{ route('tenant.integrations.purchase', [$tenant->id, $integration->id]) }}" class="action-button-primary">
                                            <i class="fas fa-shopping-cart"></i> Şimdi Satın Al
                                        </a>
                                    @endif
                                @endif
                            </div>

                            <!-- Tab Yapısı -->
                            <div class="info-tabs mt-3">
                                <ul class="nav nav-tabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ ($isPurchased && $isActive) ? '' : 'active' }}" 
                                                id="features-tab" 
                                                data-bs-toggle="tab" 
                                                data-bs-target="#features" 
                                                type="button" 
                                                role="tab">
                                            <i class="fas fa-star"></i> Özellikler
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" 
                                                id="setup-tab" 
                                                data-bs-toggle="tab" 
                                                data-bs-target="#setup" 
                                                type="button" 
                                                role="tab">
                                            <i class="fas fa-cogs"></i> Kurulum
                                        </button>
                                    </li>
                                    
                                    {{-- API Ayarları Tabı - Sadece Aktif Entegrasyonlar İçin --}}
                                    @if($isPurchased && $isActive)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" 
                                                id="api-tab" 
                                                data-bs-toggle="tab" 
                                                data-bs-target="#api" 
                                                type="button" 
                                                role="tab">
                                            <i class="fas fa-key"></i> API
                                        </button>
                                    </li>
                                    @endif
                                    @if($isPurchased && $isActive)

                                    @else
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" 
                                                    id="support-tab" 
                                                    data-bs-toggle="tab" 
                                                    data-bs-target="#support" 
                                                    type="button" 
                                                    role="tab">
                                                <i class="fas fa-life-ring"></i> Destek
                                            </button>
                                        </li>
                                    @endif
                                </ul>
                                <div class="tab-content">
                                    <!-- Özellikler Tab -->
                                    <div class="tab-pane fade {{ ($isPurchased && $isActive) ? '' : 'show active' }}" id="features" role="tabpanel">
                                        <ul class="features-list">
                                            <li>
                                                <i class="fas fa-check-circle"></i>
                                                <span>Kolay Kurulum ve Kullanım</span>
                                            </li>
                                            <li>
                                                <i class="fas fa-check-circle"></i>
                                                <span>Otomatik Veri Senkronizasyonu</span>
                                            </li>
                                            <li>
                                                <i class="fas fa-check-circle"></i>
                                                <span>Gerçek Zamanlı Bildirimler</span>
                                            </li>
                                            <li>
                                                <i class="fas fa-check-circle"></i>
                                                <span>Kapsamlı Raporlama</span>
                                            </li>
                                            <li>
                                                <i class="fas fa-check-circle"></i>
                                                <span>API Desteği</span>
                                            </li>
                                            <li>
                                                <i class="fas fa-check-circle"></i>
                                                <span>Güvenli Veri İletimi</span>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Kurulum Tab -->
                                    <div class="tab-pane fade" id="setup" role="tabpanel">
                                        <div class="setup-step">
                                            <h4>
                                                <span class="setup-step-number">1</span>
                                                Entegrasyonu Aktifleştirin
                                            </h4>
                                            <p>Yukarıdaki "Satın Al" butonuna tıklayarak entegrasyonu aktifleştirin.</p>
                                        </div>

                                        <div class="setup-step">
                                            <h4>
                                                <span class="setup-step-number">2</span>
                                                API Bilgilerinizi Girin
                                            </h4>
                                            <p>Entegrasyon ayarları sayfasından gerekli API anahtarlarını ve kimlik bilgilerini girin.</p>
                                        </div>

                                        <div class="setup-step">
                                            <h4>
                                                <span class="setup-step-number">3</span>
                                                Bağlantıyı Test Edin
                                            </h4>
                                            <p>Ayarlar sayfasındaki "Bağlantıyı Test Et" butonuna tıklayarak kurulumun doğru yapıldığından emin olun.</p>
                                        </div>

                                        <div class="setup-step">
                                            <h4>
                                                <span class="setup-step-number">4</span>
                                                Kullanmaya Başlayın
                                            </h4>
                                            <p>Entegrasyon aktif ve hazır! Artık sisteminizdeki veriler otomatik olarak senkronize edilecek.</p>
                                        </div>
                                    </div>

                                    {{-- API Ayarları Tab --}}
                                    @if($isPurchased && $isActive)
                                    <div class="tab-pane fade show active" id="api" role="tabpanel">
                                        @php
                                            $hasCredentials = $purchase->credentials && (is_array($purchase->credentials) ? count($purchase->credentials) : count(json_decode($purchase->credentials, true) ?? [])) > 0;
                                            
                                            // api_fields'i array'e çevir
                                            $apiFields = [];
                                            if ($integration->api_fields) {
                                                if (is_string($integration->api_fields)) {
                                                    $apiFields = json_decode($integration->api_fields, true) ?? [];
                                                } elseif (is_array($integration->api_fields)) {
                                                    $apiFields = $integration->api_fields;
                                                }
                                            }
                                        @endphp
                                        
                                        <div class="api-status-badge {{ $hasCredentials ? 'configured' : 'not-configured' }}">
                                            <i class="fas fa-{{ $hasCredentials ? 'check-circle' : 'exclamation-circle' }}"></i>
                                            {{ $hasCredentials ? 'API Bilgileri Yapılandırılmış' : 'API Bilgileri Bekleniyor' }}
                                        </div>

                                        <form id="apiSettingsForm" action="{{ route('tenant.integrations.save_settings', [$tenant->id, $integration->id]) }}" method="POST">
                                            @csrf
                                            <div class="api-form">
                                                @if(count($apiFields) > 0)
                                                    {{-- Dinamik API Alanları --}}
                                                    @foreach($apiFields as $field)
                                                        <div class="api-form-group">
                                                            <label class="api-form-label">
                                                                {{ $field['label'] ?? 'Alan' }}
                                                                @if(($field['required'] ?? false))
                                                                    <span class="required">*</span>
                                                                @endif
                                                            </label>
                                                            
                                                            @php
                                                                $fieldType = $field['type'] ?? 'text';
                                                                $fieldName = $field['name'] ?? '';
                                                                $fieldValue = '';
                                                                
                                                                // Hipcall için webhook_url kontrolü
                                                                if ($integration->slug === 'hipcall' && $fieldName === 'api_url' && $purchase->webhook_url) {
                                                                    $fieldValue = $purchase->webhook_url;
                                                                } else {
                                                                    // Normal credential değerlerini al
                                                                    if ($purchase->credentials) {
                                                                        if (is_string($purchase->credentials)) {
                                                                            $credentials = json_decode($purchase->credentials, true) ?? [];
                                                                            $fieldValue = $credentials[$fieldName] ?? '';
                                                                        } elseif (is_array($purchase->credentials)) {
                                                                            $fieldValue = $purchase->credentials[$fieldName] ?? '';
                                                                        }
                                                                    }
                                                                }
                                                                
                                                                // Hipcall api_url için readonly
                                                                $isHipcallWebhook = $integration->slug === 'hipcall' && $fieldName === 'api_url';
                                                            @endphp
                                                            
                                                            @if($fieldType == 'password')
                                                                <div class="password-toggle">
                                                                    <input 
                                                                        type="password" 
                                                                        name="credentials[{{ $fieldName }}]" 
                                                                        class="api-form-input password-input" 
                                                                        value="{{ $fieldValue }}"
                                                                        placeholder="{{ $field['placeholder'] ?? '' }}"
                                                                        {{ ($field['required'] ?? false) ? 'required' : '' }}
                                                                    >
                                                                    <button type="button" class="password-toggle-btn" onclick="togglePassword(this)">
                                                                        <i class="fas fa-eye"></i>
                                                                    </button>
                                                                </div>
                                                            @elseif($fieldType == 'select')
                                                                <select 
                                                                    name="credentials[{{ $fieldName }}]" 
                                                                    class="api-form-input"
                                                                    {{ ($field['required'] ?? false) ? 'required' : '' }}
                                                                >
                                                                    <option value="">Seçiniz</option>
                                                                    @if(isset($field['options']) && is_array($field['options']))
                                                                        @foreach($field['options'] as $optKey => $optValue)
                                                                            <option value="{{ $optKey }}" {{ $fieldValue == $optKey ? 'selected' : '' }}>
                                                                                {{ $optValue }}
                                                                            </option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            @elseif($fieldType == 'textarea')
                                                                <textarea 
                                                                    name="credentials[{{ $fieldName }}]" 
                                                                    class="api-form-input" 
                                                                    rows="4"
                                                                    placeholder="{{ $field['placeholder'] ?? '' }}"
                                                                    {{ ($field['required'] ?? false) ? 'required' : '' }}
                                                                >{{ $fieldValue }}</textarea>
                                                            @else
                                                                <input 
                                                                    type="{{ $fieldType }}" 
                                                                    name="credentials[{{ $fieldName }}]" 
                                                                    id="{{ $isHipcallWebhook ? 'hipcallWebhookUrl' : '' }}"
                                                                    class="api-form-input {{ $isHipcallWebhook ? 'webhook-url-field' : '' }}" 
                                                                    value="{{ $fieldValue }}"
                                                                    placeholder="{{ $field['placeholder'] ?? '' }}"
                                                                    {{ ($field['required'] ?? false) ? 'required' : '' }}
                                                                    {{ $isHipcallWebhook ? 'readonly onclick="this.select()"' : '' }}
                                                                >
                                                            @endif
                                                            
                                                            @if(isset($field['help']))
                                                                <small class="api-form-help">{{ $field['help'] }}</small>
                                                            @endif
                                                            
                                                            {{-- Hipcall Webhook için ekstra butonlar --}}
                                                            @if($isHipcallWebhook && $fieldValue)
                                                                <div class="mt-2">
                                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="copyHipcallWebhookUrl('{{ $fieldValue }}')">
                                                                        <i class="fas fa-copy"></i> Kopyala
                                                                    </button>
                                                                    <a href="https://use.hipcall.com.tr/portal/settings/marketplace/" 
                                                                    target="_blank" 
                                                                    class="btn btn-sm btn-outline-secondary ms-2">
                                                                        <i class="fas fa-external-link-alt"></i> Hipcall Paneli
                                                                    </a>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                @else
                                                    {{-- Varsayılan API Alanları --}}
                                                    @php
                                                        $credentials = [];
                                                        if ($purchase->credentials) {
                                                            if (is_string($purchase->credentials)) {
                                                                $credentials = json_decode($purchase->credentials, true) ?? [];
                                                            } elseif (is_array($purchase->credentials)) {
                                                                $credentials = $purchase->credentials;
                                                            }
                                                        }
                                                    @endphp
                                                    
                                                    <div class="api-form-group">
                                                        <label class="api-form-label">
                                                            API Kullanıcı Adı / ID
                                                            <span class="required">*</span>
                                                        </label>
                                                        <input 
                                                            type="text" 
                                                            name="credentials[username]" 
                                                            class="api-form-input" 
                                                            value="{{ $credentials['username'] ?? '' }}"
                                                            placeholder="API kullanıcı adınız veya ID"
                                                            required
                                                        >
                                                        <small class="api-form-help">Entegrasyon sağlayıcısından aldığınız kullanıcı adı</small>
                                                    </div>

                                                    <div class="api-form-group">
                                                        <label class="api-form-label">
                                                            API Anahtarı / Şifre
                                                            <span class="required">*</span>
                                                        </label>
                                                        <div class="password-toggle">
                                                            <input 
                                                                type="password" 
                                                                name="credentials[api_key]" 
                                                                class="api-form-input password-input" 
                                                                value="{{ $credentials['api_key'] ?? '' }}"
                                                                placeholder="API anahtarınız veya şifreniz"
                                                                required
                                                            >
                                                            <button type="button" class="password-toggle-btn" onclick="togglePassword(this)">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                        <small class="api-form-help">API anahtarınızı güvenli bir şekilde saklayın</small>
                                                    </div>

                                                    <div class="api-form-group">
                                                        <label class="api-form-label">
                                                            API URL (Opsiyonel)
                                                        </label>
                                                        <input 
                                                            type="url" 
                                                            name="credentials[api_url]" 
                                                            class="api-form-input" 
                                                            value="{{ $credentials['api_url'] ?? '' }}"
                                                            placeholder="https://api.example.com"
                                                        >
                                                        <small class="api-form-help">Özel API endpoint kullanıyorsanız girin</small>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="api-form-actions">
                                                <button type="submit" class="btn-save-api btn-sm">
                                                    <i class="fas fa-save"></i>
                                                    Kaydet
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    @endif
                                         

                                    <!-- Destek Tab -->
                                    <div class="tab-pane fade" id="support" role="tabpanel">
                                        <div class="info-item">
                                            <i class="fas fa-clock"></i>
                                            <span class="info-item-text">7/24 Canlı Destek Hizmeti - Her zaman yanınızdayız</span>
                                        </div>
                                        <div class="info-item">
                                            <i class="fas fa-book"></i>
                                            <span class="info-item-text">Detaylı Dokümantasyon - Adım adım kurulum rehberi</span>
                                        </div>
                                        <div class="info-item">
                                            <i class="fas fa-shield-alt"></i>
                                            <span class="info-item-text">Güvenli Entegrasyon - SSL şifrelemeli veri aktarımı</span>
                                        </div>
                                        <div class="info-item">
                                            <i class="fas fa-credit-card"></i>
                                            <span class="info-item-text">Güvenli Ödeme - 3D Secure ile korumalı ödeme</span>
                                        </div>
                                        <div class="info-item">
                                            <i class="fas fa-sync-alt"></i>
                                            <span class="info-item-text">Otomatik Güncellemeler - Her zaman en son sürüm</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Bootstrap Tab Aktivasyonu
document.addEventListener('DOMContentLoaded', function () {
    var triggerTabList = [].slice.call(document.querySelectorAll('.nav-tabs button'));
    triggerTabList.forEach(function (triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl);
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault();
            tabTrigger.show();
        });
    });
});

// Şifre göster/gizle
function togglePassword(button) {
    const input = button.parentElement.querySelector('.password-input');
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Hipcall webhook URL kopyalama
function copyHipcallWebhookUrl(url) {
    // Modern clipboard API
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url)
            .then(function() {
                // Toastr varsa kullan
                if (typeof toastr !== 'undefined') {
                    toastr.success('Webhook URL panoya kopyalandı');
                } else {
                    alert('✓ Webhook URL kopyalandı!');
                }
            })
            .catch(function(err) {
                fallbackCopyWebhook(url);
            });
    } else {
        // Eski tarayıcılar için fallback
        fallbackCopyWebhook(url);
    }
}

// Fallback kopyalama
function fallbackCopyWebhook(url) {
    const input = document.querySelector('.webhook-url-field');
    
    if (input) {
        input.focus();
        input.select();
        input.setSelectionRange(0, 99999); // Mobil için
        
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                if (typeof toastr !== 'undefined') {
                    toastr.success('Webhook URL panoya kopyalandı');
                } else {
                    alert('✓ Webhook URL kopyalandı!');
                }
            } else {
                alert('Kopyalama başarısız. Lütfen manuel olarak seçip kopyalayın (Ctrl+C)');
            }
        } catch (err) {
            alert('Kopyalama başarısız. Lütfen manuel olarak seçip kopyalayın (Ctrl+C)');
        }
    } else {
        alert('Kopyalama başarısız. Lütfen manuel olarak seçip kopyalayın (Ctrl+C)');
    }
}

// Form submit
$('#apiSettingsForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                if (typeof toastr !== 'undefined') {
                    toastr.success('API ayarları başarıyla kaydedildi');
                } else {
                    alert('✓ API ayarları başarıyla kaydedildi');
                }
                setTimeout(function() {
                    location.reload();
                }, 1500);
            }
        },
        error: function(xhr) {
            let errorMessage = 'API ayarları kaydedilemedi.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            if (typeof toastr !== 'undefined') {
                toastr.error(errorMessage);
            } else {
                alert('✗ ' + errorMessage);
            }
        }
    });
});

// Verimor Santral - Bağlantı Testi
$(document).ready(function() {
    $('#testVerimorConnectionBtn').click(function() {
        const btn = $(this);
        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Test ediliyor...');
        
        $.ajax({
            url: '{{ route("tenant.integrations.verimor-santral.test-connection", $tenant->id) }}',  // $tenant->id eklendi
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success('✓ ' + response.message);
                    } else {
                        alert('✓ ' + response.message + (response.token ? '\n\nToken: ' + response.token : ''));
                    }
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('✗ ' + response.message + (response.details ? '\n' + response.details : ''));
                    } else {
                        alert('✗ ' + response.message + (response.details ? '\n\n' + response.details : ''));
                    }
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Bağlantı testi yapılamadı';
                if (typeof toastr !== 'undefined') {
                    toastr.error('✗ ' + errorMsg);
                } else {
                    alert('✗ ' + errorMsg);
                }
            },
            complete: function() {
                btn.prop('disabled', false).html(originalHtml);
            }
        });
    });
});
</script>

@endsection