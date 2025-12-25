@extends('frontend.secure.user_master')
@section('user')


@php
    $storageInfo = auth()->user()->tenant->getStorageInfo();
    $progressColorClass = '';
    $iconClass = 'fas fa-database';
    $statusText = '';
    
    // Yüzde hesaplama - backend'den gelen değer düşükse manuel hesapla
    $storageUsagePercent = isset($storageInfo['usage_percentage']) ? floatval($storageInfo['usage_percentage']) : 0;
    
    if ($storageUsagePercent <= 0.01) {
        $usageFormatted = $storageInfo['current_usage_formatted'] ?? '0 B';
        $limitFormatted = $storageInfo['limit_formatted'] ?? '1 GB';
        
        $usageBytes = 0;
        $limitBytes = 0;
        
        // Kullanılan alan hesabı - regex ile daha kesin parsing
        if (preg_match('/(\d+(?:\.\d+)?)\s*KB/i', $usageFormatted, $matches)) {
            $usageBytes = floatval($matches[1]) * 1024;
        } elseif (preg_match('/(\d+(?:\.\d+)?)\s*MB/i', $usageFormatted, $matches)) {
            $usageBytes = floatval($matches[1]) * 1024 * 1024;
        } elseif (preg_match('/(\d+(?:\.\d+)?)\s*GB/i', $usageFormatted, $matches)) {
            $usageBytes = floatval($matches[1]) * 1024 * 1024 * 1024;
        } elseif (preg_match('/(\d+(?:\.\d+)?)\s*B/i', $usageFormatted, $matches)) {
            $usageBytes = floatval($matches[1]);
        }
        
        // Limit hesabı - regex ile daha kesin parsing
        if (preg_match('/(\d+(?:\.\d+)?)\s*KB/i', $limitFormatted, $matches)) {
            $limitBytes = floatval($matches[1]) * 1024;
        } elseif (preg_match('/(\d+(?:\.\d+)?)\s*MB/i', $limitFormatted, $matches)) {
            $limitBytes = floatval($matches[1]) * 1024 * 1024;
        } elseif (preg_match('/(\d+(?:\.\d+)?)\s*GB/i', $limitFormatted, $matches)) {
            $limitBytes = floatval($matches[1]) * 1024 * 1024 * 1024;
        } elseif (preg_match('/(\d+(?:\.\d+)?)\s*B/i', $limitFormatted, $matches)) {
            $limitBytes = floatval($matches[1]);
        }
        
        // Yüzde hesapla - minimum 0.01% göster
        if ($limitBytes > 0) {
            $storageUsagePercent = ($usageBytes / $limitBytes) * 100;
            if ($storageUsagePercent > 0 && $storageUsagePercent < 0.01) {
                $storageUsagePercent = 0.01; // En az 0.01% göster
            }
            $storageUsagePercent = round($storageUsagePercent, 3);
        }
        
        // storageInfo'yu güncelle
        $storageInfo['usage_percentage'] = $storageUsagePercent;
    }
    
    if ($storageInfo['danger_threshold']) {
        $progressColorClass = 'bg-gradient-danger';
        $iconClass = 'fas fa-exclamation-triangle';
        $statusText = 'Kritik Seviye';
    } elseif ($storageInfo['warning_threshold']) {
        $progressColorClass = 'bg-gradient-warning';
        $iconClass = 'fas fa-exclamation-circle';
        $statusText = 'Dikkat Gerekli';
    } else {
        $progressColorClass = 'bg-gradient-success';
        $iconClass = 'fas fa-check-circle';
        $statusText = 'Normal';
    }
@endphp

<div class="page-content" id="storageInfoPage">
  <div class="container-fluid">
<div class="storage-widget" id="storageWidget">
    <!-- Widget Header -->
    <div class="widget-header">
        <div class="widget-title">
            <i class="{{ $iconClass }} widget-icon"></i>
            <span>Depolama Alanı</span>
            <span style="margin-right: 10px;" class="status-badge status-{{ $storageInfo['danger_threshold'] ? 'danger' : ($storageInfo['warning_threshold'] ? 'warning' : 'success') }}">
                {{ $statusText }}
            </span>
        </div>
        <div class="widget-actions">
            <button class="btn-refresh" onclick="refreshStorageInfo()" title="Yenile">
                <i class="fas fa-sync-alt"></i>
            </button>
            {{-- <button class="btn-details" onclick="toggleStorageDetails()" title="Detaylar">
                <i class="fas fa-info-circle"></i>
            </button> --}}
        </div>
    </div>

    <!-- Storage Overview -->
    <div class="storage-overview">
        <div class="usage-container">
            <!-- Circular Progress -->
            <div class="circular-progress">
                <svg class="progress-ring" width="120" height="120">
                    <circle class="progress-ring-circle-bg" cx="60" cy="60" r="50"></circle>
                    <circle class="progress-ring-circle progress-{{ $storageInfo['danger_threshold'] ? 'danger' : ($storageInfo['warning_threshold'] ? 'warning' : 'success') }}" 
                            cx="60" cy="60" r="50" 
                            stroke-dasharray="314" 
                            stroke-dashoffset="{{ 314 - (314 * $storageInfo['usage_percentage'] / 100) }}">
                    </circle>
                </svg>
                <div class="progress-text">
                    <span class="percentage">{{ $storageInfo['usage_percentage'] }}%</span>
                    <span class="label">Kullanım</span>
                </div>
            </div>

            <!-- Usage Stats -->
            <div class="usage-stats">
                <div class="stat-item">
                    <div class="stat-value">{{ $storageInfo['current_usage_formatted'] }}</div>
                    <div class="stat-label">Kullanılan</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="stat-value">{{ $storageInfo['remaining_formatted'] }}</div>
                    <div class="stat-label">Kalan</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="stat-value">{{ $storageInfo['limit_formatted'] }}</div>
                    <div class="stat-label">Toplam</div>
                </div>
            </div>
        </div>

        <!-- Linear Progress Bar -->
        <div class="linear-progress-container">
            <div class="progress-info">
                <span class="current-usage">{{ $storageInfo['current_usage_formatted'] }}</span>
                <span class="total-limit">{{ $storageInfo['limit_formatted'] }}</span>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar-bg">
                    <div class="progress-bar-fill {{ $progressColorClass }}" 
                         style="width: {{ $storageInfo['usage_percentage'] }}%"
                         data-percentage="{{ $storageInfo['usage_percentage'] }}">
                        <div class="progress-bar-glow"></div>
                    </div>
                </div>
                <div class="progress-markers">
                    <div class="marker marker-25" style="left: 25%"></div>
                    <div class="marker marker-50" style="left: 50%"></div>
                    <div class="marker marker-75" style="left: 75%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if($storageInfo['danger_threshold'])
        <div class="storage-alert alert-danger" style="color: #db7588;">
            <i class="fas fa-exclamation-triangle"></i>
            <div class="alert-content">
                <strong>Depolama Alanı Doldu!</strong>
                <p>Yeni dosya yükleyemezsiniz. Lütfen eski dosyaları silin veya planınızı yükseltin.</p>
            </div>
        </div>
    @elseif($storageInfo['warning_threshold'])
        <div class="storage-alert alert-warning">
            <i class="fas fa-exclamation-circle"></i>
            <div class="alert-content">
                <strong>Depolama Alanı Azalıyor</strong>
                <p>%{{ $storageInfo['usage_percentage'] }} kullanıldı. Yakında limit dolacak.</p>
            </div>
        </div>
    @endif

    <!-- Action Buttons -->
    <div class="storage-actions">
        @if($storageInfo['warning_threshold'])
            <a href="{{ route('subscription.plans',$firma->id) }}" class="btn btn-upgrade">
                <i class="fas fa-arrow-up"></i>
                <span>Planı Yükselt</span>
            </a>
            <a href="{{ route('storage.packages', $firma->id) }}" class="btn btn-storage" id="ekDepoBtn">
                <i class="fas fa-plus-circle"></i>
                <span>Ek Depolama Al</span>
            </a>
        @endif
        
        {{-- <button class="btn btn-manage" onclick="toggleStorageDetails()">
            <i class="fas fa-folder-open"></i>
            <span>Dosyaları Yönet</span>
        </button> --}}
    </div>

    <!-- Detailed Information (Hidden by default) -->
    <div class="storage-details" id="storageDetails" style="display: block;">
        <div class="details-grid">
            <div class="detail-item">
                <i class="fas fa-images text-primary"></i>
                <div class="detail-info">
                    <span class="detail-label">Servis Fotoğrafları</span>
                    <span class="detail-value" id="servicePhotosCount">-</span>
                </div>
            </div>
            <div class="detail-item">
                <i class="fas fa-boxes text-info"></i>
                <div class="detail-info">
                    <span class="detail-label">Stok Resimleri</span>
                    <span class="detail-value" id="stockPhotosCount">-</span>
                </div>
            </div>
            <div class="detail-item">
                <i class="fas fa-file-alt text-secondary"></i>
                <div class="detail-info">
                    <span class="detail-label">Diğer Dosyalar</span>
                    <span class="detail-value" id="otherFilesCount">-</span>
                </div>
            </div>
        </div>
        
        <div class="plan-info">
            <h6 style="color: #d9d3d3;"><i class="fas fa-crown"></i> Mevcut Paket</h6>
            <div class="plan-details">
                <span class="plan-name">{{ auth()->user()->tenant->plan()?->name ?? 'Temel' }}</span>
                <span class="plan-limit">{{ $storageInfo['limit_formatted'] }} Depolama</span>
            </div>
        </div>
    </div>
</div>
</div>
</div>
{{-- JavaScript Functionality --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    initStorageWidget();
});

function initStorageWidget() {
    // Auto refresh every 60 seconds
    //setInterval(refreshStorageInfo, 60000);
    
    // Load detailed info on page load
    loadStorageDetails();
}

function refreshStorageInfo() {
    const widget = document.getElementById('storageWidget');
    const refreshBtn = widget.querySelector('.btn-refresh');
    
    // Add loading state
    widget.classList.add('loading');
    refreshBtn.querySelector('i').style.animation = 'spin 1s linear infinite';
    
    // YENİ JSON endpoint'i kullan
    const url = '{{ route("depolama.bilgisi.json", ["tenant_id" => auth()->user()->tenant->id]) }}';
    
    fetch(url)
        .then(response => {
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                throw new Error("Sunucudan JSON yerine HTML döndü. Route kontrol edilmeli.");
            }
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                updateStorageDisplay(data.storage_info);
                showToast('Storage bilgileri güncellendi', 'success');
            } else {
                throw new Error(data.message || 'Bilinmeyen hata');
            }
        })
        .catch(error => {
            console.error('Storage info update error:', error);
            showToast('Güncelleme sırasında hata oluştu: ' + error.message, 'error');
        })
        .finally(() => {
            widget.classList.remove('loading');
            refreshBtn.querySelector('i').style.animation = '';
        });
}

function updateStorageDisplay(info) {
    // Yüzdeyi kontrol et ve gerekirse manuel hesapla
    let usagePercentage = parseFloat(info.usage_percentage) || 0;
    
    if (usagePercentage <= 0.01 && info.current_usage_formatted && info.limit_formatted) {
        // Manuel hesaplama
        const usageMatch = info.current_usage_formatted.match(/(\d+(?:\.\d+)?)\s*(KB|MB|GB|B)/i);
        const limitMatch = info.limit_formatted.match(/(\d+(?:\.\d+)?)\s*(KB|MB|GB|B)/i);
        
        if (usageMatch && limitMatch) {
            const usageValue = parseFloat(usageMatch[1]);
            const usageUnit = usageMatch[2].toUpperCase();
            const limitValue = parseFloat(limitMatch[1]);
            const limitUnit = limitMatch[2].toUpperCase();
            
            // Byte'a çevir
            const unitMultipliers = { 'B': 1, 'KB': 1024, 'MB': 1024*1024, 'GB': 1024*1024*1024 };
            const usageBytes = usageValue * (unitMultipliers[usageUnit] || 1);
            const limitBytes = limitValue * (unitMultipliers[limitUnit] || 1);
            
            if (limitBytes > 0) {
                usagePercentage = (usageBytes / limitBytes) * 100;
                if (usagePercentage > 0 && usagePercentage < 0.01) {
                    usagePercentage = 0.01; // En az 0.01% göster
                }
                usagePercentage = Math.round(usagePercentage * 1000) / 1000; // 3 ondalık basamak
            }
        }
    }
    
    // Update circular progress
    const progressCircle = document.querySelector('.progress-ring-circle');
    const circumference = 314;
    const offset = circumference - (circumference * usagePercentage / 100);
    progressCircle.style.strokeDashoffset = offset;
    
    // Update percentage text
    document.querySelector('.percentage').textContent = usagePercentage.toFixed(2) + '%';
    
    // Update stat values
    const statValues = document.querySelectorAll('.stat-value');
    statValues[0].textContent = info.current_usage_formatted;
    statValues[1].textContent = info.remaining_formatted;
    statValues[2].textContent = info.limit_formatted;
    
    // Update linear progress
    const progressFill = document.querySelector('.progress-bar-fill');
    progressFill.style.width = usagePercentage + '%';
    
    // Update info text
    document.querySelector('.current-usage').textContent = info.current_usage_formatted;
    document.querySelector('.total-limit').textContent = info.limit_formatted;
    
    // Update colors based on threshold
    updateProgressColors(info);
}

function updateProgressColors(info) {
    const progressCircle = document.querySelector('.progress-ring-circle');
    const progressFill = document.querySelector('.progress-bar-fill');
    
    // Remove existing classes
    progressCircle.classList.remove('progress-success', 'progress-warning', 'progress-danger');
    progressFill.classList.remove('bg-gradient-success', 'bg-gradient-warning', 'bg-gradient-danger');
    
    // Add appropriate classes
    if (info.danger_threshold) {
        progressCircle.classList.add('progress-danger');
        progressFill.classList.add('bg-gradient-danger');
    } else if (info.warning_threshold) {
        progressCircle.classList.add('progress-warning');
        progressFill.classList.add('bg-gradient-warning');
    } else {
        progressCircle.classList.add('progress-success');
        progressFill.classList.add('bg-gradient-success');
    }
}

// function toggleStorageDetails() {
//     const details = document.getElementById('storageDetails');
//     const isVisible = details.style.display !== 'none';
    
//     if (isVisible) {
//         details.style.display = 'none';
//     } else {
//         details.style.display = 'block';
//         loadStorageDetails();
//     }
// }

function loadStorageDetails() {
    const url = '{{ route("tenant.storage.details", ["tenant_id" => auth()->user()->tenant->id]) }}';
    
    fetch(url)
        .then(response => {
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                throw new Error("Storage details: JSON yerine HTML döndü");
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                updateDetailedInfo(data.details);
            }
        })
        .catch(error => {
            console.error('Storage details error:', error);
        });
}

function updateDetailedInfo(details) {
    // Servis fotoğrafları
    const serviceCount = details.service_photos?.count || 0;
    //const serviceSize = details.service_photos?.total_size_formatted || '0 B';
    //document.getElementById('servicePhotosCount').textContent = `${serviceCount} dosya (${serviceSize})`;
    document.getElementById('servicePhotosCount').textContent = `${serviceCount} dosya`;
    
    // Stok fotoğrafları - sayı ve boyut göster
    const stockCount = details.stock_photos?.count || 0;
    //const stockSize = details.stock_photos?.total_size_formatted || '0 B';
    //document.getElementById('stockPhotosCount').textContent = `${stockCount} dosya (${stockSize})`;
    document.getElementById('stockPhotosCount').textContent = `${stockCount} dosya`;
    
    // Diğer dosyalar
    // Diğer dosyalar - detaylı breakdown
    const otherDetails = details.other_files?.breakdown || {};
    const supportCount = otherDetails.support_attachments?.count || 0;
    const dealerCount = otherDetails.dealer_documents?.count || 0;
    const invoiceCount = otherDetails.invoice_documents?.count || 0;
    const totalOtherCount = supportCount + dealerCount + invoiceCount;
    const totalOtherSize = details.other_files?.total_size_formatted || '0 B';
    
    document.getElementById('otherFilesCount').textContent = 
        `${totalOtherCount} dosya - Destek: ${supportCount}, Bayi: ${dealerCount}, Fatura: ${invoiceCount}`;
}



function showToast(message, type = 'info') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        border-radius: 8px;
        color: white;
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.3s ease;
    `;
    
    if (type === 'success') {
        toast.style.background = 'linear-gradient(135deg, #28a745, #20c997)';
    } else if (type === 'error') {
        toast.style.background = 'linear-gradient(135deg, #dc3545, #e91e63)';
    } else {
        toast.style.background = 'linear-gradient(135deg, #17a2b8, #007bff)';
    }
    
    document.body.appendChild(toast);
    
    setTimeout(() => toast.style.opacity = '1', 100);
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => document.body.removeChild(toast), 300);
    }, 3000);
}

// File upload validation
window.validateFileUpload = function(input) {
    const files = input.files;
    if (!files.length) return true;
    
    let totalSize = 0;
    for (let file of files) {
        totalSize += file.size;
    }
    
    const remainingBytes = {{ $storageInfo['remaining_gb'] }} * 1024 * 1024 * 1024;
    
    if (totalSize > remainingBytes) {
        showToast(`Dosya boyutu storage limitinizi aşıyor. Kalan alan: {{ $storageInfo['remaining_formatted'] }}`, 'error');
        input.value = '';
        return false;
    }
    
    return true;
};
</script>
@endsection