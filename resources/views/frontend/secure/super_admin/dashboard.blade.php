@extends('frontend.secure.user_master')
@section('user')
<div class="page-content gradient-bg" id="superAdminDashboardPage">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row main-top">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-sm-0">
                            SerbisCRM Yönetim Paneli
                        </h4>

                    </div>
                </div>
            </div>
        </div>
        <!-- İstatistik Kartları -->
        <div class="row">
            <div class="col-xl-3 col-6 col-md-6 pr custom-p-r-m">
                <div class="card superadmin-dashboard-card superadmin-statistic primary dashboard-p">
                    <div class=" card-h-s">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="sistatistic-label">Toplam Müşteri</p>
                                <h4 class="sistatistic-value animate-count" data-target="{{ $stats['total_tenants'] }}">{{ $stats['total_tenants'] }}</h4>
                            </div>
                            <div class="sistatistic-icon">
                                <i class="fas fa-building"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-6 col-md-6 pl custom-p-r-min">
                <div class="card superadmin-dashboard-card superadmin-statistic success dashboard-p">
                    <div class=" card-h-s">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="sistatistic-label">Aktif Müşteri</p>
                                <h4 class="sistatistic-value animate-count" data-target="{{ $stats['active_tenants'] }}">{{ $stats['active_tenants'] }}</h4>
                            </div>
                            <div class="sistatistic-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-6 col-md-6 pr custom-p-r-m">
                <div class="card superadmin-dashboard-card superadmin-statistic info dashboard-p">
                    <div class=" card-h-s">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="sistatistic-label">Toplam Kullanıcı</p>
                                <h4 class="sistatistic-value animate-count" data-target="{{ $stats['total_users'] }}">{{ $stats['total_users'] }}</h4>
                            </div>
                            <div class="sistatistic-icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-6 col-md-6 pl">
                <div class="card superadmin-dashboard-card superadmin-statistic warning dashboard-p">
                    <div class=" card-h-s">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="sistatistic-label">Aktif Kullanıcı</p>
                                <h4 class="sistatistic-value animate-count" data-target="{{ $stats['active_users'] }}">{{ $stats['active_users'] }}</h4>
                            </div>
                            <div class="sistatistic-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Grafik ve Sistem Durumu -->
    <div class="row ">
    <div class="col-lg-8 custom-p-r-min">
        <div class="card superadmin-dashboard-card equal-height-container">
            <div class="card-header bg-transparent border-bottom">
                <h5 class="card-title px-1 py-2 mb-0">
                    <i class="fas fa-chart-line text-primary me-2"></i>Son 7 Günlük Sistem Aktivitesi
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card superadmin-dashboard-card equal-height-container card-m-h" style="height: 349px">
            <div class="card-header bg-transparent border-bottom">
                <h5 class="card-title px-1 py-2 mb-0">
                    <i class="fas fa-chart-pie text-success me-2"></i>Sistem Durumu
                </h5>
            </div>
            <div class="card-body">
                <div class="status-container">
                    <div class="row">
                        <div class="col-12 custom-b-p">
                            <div class="status-item">
                                <div class="status-percentage {{ $stats['active_tenant_percentage'] >= 80 ? 'high' : ($stats['active_tenant_percentage'] >= 60 ? 'medium' : 'low') }}">
                                    {{ $stats['active_tenant_percentage'] }}%
                                </div>
                                <div class="status-label">Aktif Müşteri Oranı</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="status-item">
                                <div class="status-percentage {{ $stats['active_user_percentage'] >= 80 ? 'high' : ($stats['active_user_percentage'] >= 60 ? 'medium' : 'low') }}">
                                    {{ $stats['active_user_percentage'] }}%
                                </div>
                                <div class="status-label">Aktif Kullanıcı Oranı</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
        <!-- Hızlı Erişim Kartları -->
        <div class="row">
            <!-- Müşteri Yönetimi -->
            <div class="col-lg-4 custom-p-r-min">
                <div class="card superadmin-dashboard-card">
                    <div class="card-header bg-c-color border-bottom">
                        <h5 class="card-title px-3 py-2 mb-0">
                           Müşteri Yönetimi
                        </h5>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('super.admin.tenants') }}" class="action-item d-flex align-items-center">
                            <div class="action-icon" style="background: linear-gradient(135deg, #3498db, #5dade2);">
                                <i class="fas fa-list"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Tüm Müşterileri Listele</h6>
                                <small class="text-muted">{{ $stats['total_tenants'] }} müşteri</small>
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </a>
                        
                        
                        <a href="{{ route('super.admin.tenants') }}" class="action-item d-flex align-items-center">
                            <div class="action-icon" style="background: linear-gradient(135deg, #27ae60, #58d68d);">
                                <i class="fas fa-user-secret"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Kimliğe Bürünme</h6>
                                <small class="text-muted">Herhangi bir kullanıcı olarak giriş</small>
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                             </a>
                        
                            <a href="{{ route('super.admin.tenants') }}" class="action-item d-flex align-items-center">
                            <div class="action-icon" style="background: linear-gradient(135deg, #8e44ad, #af7ac5);">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Yeni Müşteri Ekle</h6>
                                <small class="text-muted">Sisteme yeni müşteri kaydı</small>
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                            </a>
            
                         
                    </div>
                </div>
            </div>

            <!-- Destek Talepleri -->
            <div class="col-lg-4 custom-p-r-min">
                <div class="card superadmin-dashboard-card">
                    <div class="card-header bg-c-color border-bottom">
                        <h5 class="card-title mb-0 px-3 py-2">
                            Destek Talepleri
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(isset($supportStats))
                        <a href="{{ route('super.admin.destek.index', ['priority' => 'acil']) }}" class="action-item d-flex align-items-center">
                            <div class="action-icon" style="background: linear-gradient(135deg, #e74c3c, #ec7063);">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Acil Talepler</h6>
                                <small class="text-muted">Hemen ilgilenilmesi gereken</small>
                            </div>
                            <span class="badge badge-danger">{{ $supportStats['urgent_tickets'] }}</span>
                        </a>

                        <a href="{{ route('super.admin.destek.index', ['status' => 'acik']) }}" class="action-item d-flex align-items-center">
                            <div class="action-icon" style="background: linear-gradient(135deg, #f39c12, #f8c471);">
                                <i class="fas fa-inbox"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Yeni Talepler</h6>
                                <small class="text-muted">Henüz yanıtlanmamış</small>
                            </div>
                            <span class="badge badge-warning">{{ $supportStats['new_tickets'] }}</span>
                        </a>

                        <a href="{{ route('super.admin.destek.index') }}" class="action-item d-flex align-items-center">
                            <div class="action-icon" style="background: linear-gradient(135deg, #3498db, #5dade2);">
                                <i class="fas fa-list-alt"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Tüm Talepler</h6>
                                <small class="text-muted">Geçmiş ve mevcut</small>
                            </div>
                            <span class="badge badge-success">{{ $supportStats['total_tickets'] }}</span>
                        </a>
                        @else
                        <div class="text-center py-4">
                            <i class="fas fa-info-circle text-muted fa-2x mb-2"></i>
                            <p class="text-muted">Destek talebi verileri yükleniyor...</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Raporlar ve Analiz -->
            <div class="col-lg-4 ">
                <div class="card superadmin-dashboard-card">
                    <div class="card-header bg-c-color border-bottom">
                        <h5 class="card-title mb-0 px-3 py-2">
                            Raporlar & Analiz
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="action-item d-flex align-items-center">
                            <div class="action-icon" style="background: linear-gradient(135deg, #27ae60, #58d68d);">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Müşteri Performansı</h6>
                                <small class="text-muted">Detaylı performans raporları</small>
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </div>

                        <div class="action-item d-flex align-items-center">
                            <div class="action-icon" style="background: linear-gradient(135deg, #3498db, #5dade2);">
                                <i class="fas fa-users-cog"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Kullanıcı Aktivitesi</h6>
                                <small class="text-muted">Sistem kullanım istatistikleri</small>
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </div>

                        <div class="action-item d-flex align-items-center">
                            <div class="action-icon" style="background: linear-gradient(135deg, #8e44ad, #af7ac5);">
                                <i class="fas fa-download"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Sistem Raporları</h6>
                                <small class="text-muted">Excel/PDF formatında</small>
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Türkçe gün isimleri için mapping
    const turkishDays = {
        'Monday': 'Pazartesi',
        'Tuesday': 'Salı', 
        'Wednesday': 'Çarşamba',
        'Thursday': 'Perşembe',
        'Friday': 'Cuma',
        'Saturday': 'Cumartesi',
        'Sunday': 'Pazar'
    };
    
    // Kısaltılmış Türkçe gün isimleri
    const turkishDaysShort = {
        'Mon': 'Pzt',
        'Tue': 'Sal',
        'Wed': 'Çar', 
        'Thu': 'Per',
        'Fri': 'Cum',
        'Sat': 'Cmt',
        'Sun': 'Paz'
    };
    
    // Chart.js için locale ayarı
    const originalLabels = {!! json_encode($chartData['labels']) !!};
    const turkishLabels = originalLabels.map(label => {
        // Eğer label İngilizce gün ismi içeriyorsa Türkçe'ye çevir
        for (let [eng, tr] of Object.entries(turkishDaysShort)) {
            if (label.includes(eng)) {
                return label.replace(eng, tr);
            }
        }
        for (let [eng, tr] of Object.entries(turkishDays)) {
            if (label.includes(eng)) {
                return label.replace(eng, tr);
            }
        }
        return label;
    });

    // Aktivite Grafiği
    const ctx = document.getElementById('activityChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: turkishLabels, // Türkçe labellar kullan
                datasets: [{
                    label: 'Yeni Kayıtlar',
                    data: {!! json_encode($chartData['new_registrations']) !!},
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3498db',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }, {
                    label: 'Aktif Kullanıcılar',
                    data: {!! json_encode($chartData['active_users']) !!},
                    borderColor: '#27ae60',
                    backgroundColor: 'rgba(39, 174, 96, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#27ae60',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#dddddd',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#666666'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.1)',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#666666'
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                elements: {
                    point: {
                        hoverRadius: 8
                    }
                }
            }
        });
    }
});
</script>

@endsection