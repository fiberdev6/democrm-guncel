@extends('frontend.secure.user_master')
@section('user')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<div class="page-content servis-istatistik-genel" id="serviceStatsPage">
    <div class="container-fluid">
        @include('frontend.secure.statistics.statistics_menu', ['tenant_id' => $tenant_id])
           <!-- Modern Header Card -->
            <div class="card card-statics shadow-sm istatistik-card mt-2">
                <div class="card-header card-statics-header sayfaBaslik d-flex justify-content-between align-items-center col-c">
                <span class="custom-header" style="font-weight: 600; color: #2c3e50; font-size: 16px;">Servis İstatistikleri</span>
                <div class="btn-group mb-1" id="servis_s_filtre">
                    <button class="btn btn-dark btn-sm dropdown-toggle filtrele" type="button" data-bs-toggle="dropdown">
                        Filtrele <i class="mdi mdi-chevron-down"></i>
                    </button>
                   <div class="dropdown-menu dropdown-menu-end servisDrop p-3 servisDrop" style="min-width: 320px;">
                    <form id="istatistikAra" action="{{ route('statistics', $tenant_id) }}" method="get">
                        {{-- Personel --}}
                        <div class="row g-2 align-items-center mb-3">
                            <div class="col-4">
                                <label for="personellerSelect" class="form-label mb-0">Personel</label>
                            </div>
                            <div class="col-8">
                                <select name="personeller" id="personellerSelect" class="form-select">
                                    <option value="0">Tüm Personeller</option>
                                    @foreach($personeller as $p)
                                        <option value="{{ $p->user_id }}" {{ request()->personeller == $p->user_id ? 'selected' : '' }}>
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Servis Kaynağı --}}
                        <div class="row g-2 align-items-center mb-3">
                            <div class="col-4">
                                <label for="servisKaynakSelect" class="form-label mb-0">Servis Kaynağı</label>
                            </div>
                            <div class="col-8">
                                <select name="servisKaynak" id="servisKaynakSelect" class="form-select form-select-sm w-100">
                                    <option value="0">Tüm Kaynaklar</option>
                                    @foreach($servisKaynaklari as $kaynak)
                                        <option value="{{ $kaynak->id }}" 
                                            {{ (isset($request) && $request->servisKaynak == $kaynak->id) ? 'selected' : '' }}>
                                            {{ $kaynak->kaynak }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Tarih Aralığı --}}
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <label for="daterange" class="form-label mb-0" style="padding-top: 6px;">Tarih Aralığı</label>
                            </div>
                            <div class="col-8">
                                {{-- Gizli inputlar daterangepicker tarafından doldurulacak --}}
                                <input type="hidden" name="tarih1" id="tarih1" value="{{ \Carbon\Carbon::parse($request->tarih1 ?? now()->subMonth())->format('Y-m-d') }}">
                                <input type="hidden" name="tarih2" id="tarih2" value="{{ \Carbon\Carbon::parse($request->tarih2 ?? now())->format('Y-m-d') }}">
                                
                                {{-- Görünür daterangepicker inputu --}}
                                <input id="daterange" class="form-control form-control-sm tarih-araligi mb-2" />
                                
                                <div class="tarihAraligi">
                                    <button type="button" id="lastMonth" class="btn btn-sm btn-secondary me-1 mb-1">Son 1 Ay</button>
                                    <button type="button" id="last15Days" class="btn btn-sm btn-secondary me-1 mb-1">Son 15 Gün</button>
                                    <button type="button" id="lastWeek" class="btn btn-sm btn-secondary me-1 mb-1">Son 7 Gün</button>
                                    <button type="button" id="yesterday" class="btn btn-sm btn-secondary me-1 mb-1">Dün</button>
                                    <button type="button" id="today" class="btn btn-sm btn-secondary mb-1">Bugün</button>
                                </div>
                            </div>
                        </div>

                        {{-- Ara Butonu --}}
                        <div>
                            <button type="submit" name="servisSayListele" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-search me-1"></i> Ara
                            </button>
                        </div>
                    </form>
                </div>
                </div>
            </div>
        @if(isset($statistics))
            <!-- Filtered Results -->
            <div class="row mb-4 statistics-filter-section">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title mb-0">Arama Sonuçları</h5>
                                <span class="badge bg-secondary fs-6 istatistik-badge">Toplam: {{ $statistics['toplam'] }}</span>
                            </div>
                            <div class="row g-4">
                                <!-- Markalar -->
                                <div class="col-lg-3 col-md-6">
                                    <div class="card h-100 border-0 bg-light">
                                                <div class="card-header bg-secondary text-white text-center">
                                                    <small><i class="fas fa-tags me-1"></i>Markalar</small>
                                                </div>
                                        <div class="card-body p-2">
                                            @forelse($statistics['markalar'] as $marka)
                                                <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                                    <span class="text-truncate">{{ $marka->marka }}</span>
                                                    <span class="badge bg-info">{{ $marka->sayi }}</span>
                                                </div>
                                            @empty
                                                <div class="text-muted text-center">Kayıt Yok</div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                <!-- Türler -->
                                <div class="col-lg-3 col-md-6">
                                    <div class="card h-100 border-0 bg-light">
                                                <div class="card-header bg-secondary text-white text-center">
                                                    <small><i class="fas fa-cube me-1"></i>Türler</small>
                                                </div>
                                        <div class="card-body p-2">
                                            @forelse($statistics['turler'] as $tur)
                                                <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                                    <span class="text-truncate">{{ $tur->cihaz }}</span>
                                                    <span class="badge bg-success">{{ $tur->sayi }}</span>
                                                </div>
                                            @empty
                                                <div class="text-muted text-center">Kayıt Yok</div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                <!-- Kaynaklar -->
                                <div class="col-lg-3 col-md-6">
                                    <div class="card h-100 border-0 bg-light">
                                         <div class="card-header bg-secondary text-white text-center">
                                                    <small><i class="fas fa-compass me-1"></i>Kaynaklar</small>
                                                </div>
                                        <div class="card-body p-2">
                                            @forelse($statistics['kaynaklar'] as $kaynak)
                                                <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                                    <span class="text-truncate">{{ $kaynak->kaynak }}</span>
                                                    <span class="badge bg-warning text-dark">{{ $kaynak->sayi }}</span>
                                                </div>
                                            @empty
                                                <div class="text-muted text-center">Kayıt Yok</div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                <!-- Operatörler -->
                                <div class="col-lg-3 col-md-6">
                                    <div class="card h-100 border-0 bg-light">
                                        <div class="card-header bg-secondary text-white text-center">
                                                    <small><i class="fas fa-users me-1"></i>Personeller</small>
                                                </div>
                                        <div class="card-body p-2">
                                            @forelse($statistics['operatorler'] as $operator)
                                                <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                                    <span class="text-truncate">{{ $operator->name }}</span>
                                                    <span class="badge bg-danger">{{ $operator->sayi }}</span>
                                                </div>
                                            @empty
                                                <div class="text-muted text-center">Kayıt Yok</div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Compact Tab-Accordion Period Statistics -->
            <div class="period-statistics">
                <!-- Compact Tab Headers -->
                <div class="period-tabs-compact d-flex mb-2">
                    @foreach($periodStats as $key => $period)
                        <button class="period-tab-compact flex-fill {{ $key === 'bugun' ? 'active' : '' }}" 
                                data-period="{{ $key }}" 
                                data-target="#period-{{ $key }}"
                                type="button">
                            <i class="fas fa-calendar-day me-1"></i>
                            {{ $period['label'] }}
                            <span class="badge bg-light text-dark ms-1">{{ $period['toplam'] }}</span>
                        </button>
                    @endforeach
                </div>
                <!-- Accordion Content -->
                <div class="period-accordion">
                    @foreach($periodStats as $key => $period)
                        <div class="collapse {{ $key === 'bugun' ? 'show' : '' }}" 
                             id="period-{{ $key }}" 
                             data-bs-parent=".period-accordion">
                            <div class="card shadow-sm mb-3">
                                <div class="card-body p-4">
                                    <div class="row g-4">
                                        <!-- Markalar -->
                                        <div class="col-lg-3 col-md-6">
                                            <div class="card border-secondary card-p-c">
                                                <div class="card-header bg-secondary text-white text-center">
                                                    <small><i class="fas fa-tags me-1"></i>Markalar</small>
                                                </div>
                                                <div class="card-body p-2">
                                                    @forelse($period['markalar'] as $marka)
                                                        <div class="d-flex justify-content-between py-1 border-bottom">
                                                            <small class="text-truncate">{{ $marka->marka }}</small>
                                                            <span class="badge bg-secondary">{{ $marka->sayi }}</span>
                                                        </div>
                                                    @empty
                                                        <small class="text-muted">Kayıt Yok</small>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Türler -->
                                        <div class="col-lg-3 col-md-6">
                                            <div class="card border-secondary card-p-c">
                                                <div class="card-header bg-secondary text-white text-center">
                                                    <small><i class="fas fa-cube me-1"></i>Türler</small>
                                                </div>
                                                <div class="card-body p-2">
                                                    @forelse($period['turler'] as $tur)
                                                        <div class="d-flex justify-content-between py-1 border-bottom">
                                                            <small class="text-truncate">{{ $tur->cihaz }}</small>
                                                            <span class="badge bg-secondary">{{ $tur->sayi }}</span>
                                                        </div>
                                                    @empty
                                                        <small class="text-muted">Kayıt Yok</small>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Kaynaklar -->
                                        <div class="col-lg-3 col-md-6">
                                            <div class="card border-secondary card-p-c">
                                                <div class="card-header bg-secondary text-white text-center">
                                                    <small><i class="fas fa-compass me-1"></i>Kaynaklar</small>
                                                </div>
                                                <div class="card-body p-2">
                                                    @forelse($period['kaynaklar'] as $kaynak)
                                                        <div class="d-flex justify-content-between py-1 border-bottom">
                                                            <small class="text-truncate">{{ $kaynak->kaynak }}</small>
                                                            <span class="badge bg-secondary text-white">{{ $kaynak->sayi }}</span>
                                                        </div>
                                                    @empty
                                                        <small class="text-muted">Kayıt Yok</small>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Operatörler -->
                                        <div class="col-lg-3 col-md-6">
                                            <div class="card border-secondary card-p-c">
                                                <div class="card-header bg-secondary text-white text-center">
                                                    <small><i class="fas fa-users me-1"></i>Personeller</small>
                                                </div>
                                                <div class="card-body p-2">
                                                    @forelse($period['operatorler'] as $operator)
                                                        <div class="d-flex justify-content-between py-1 border-bottom">
                                                            <small class="text-truncate">{{ $operator->name }}</small>
                                                            <span class="badge bg-secondary">{{ $operator->sayi }}</span>
                                                        </div>
                                                    @empty
                                                        <small class="text-muted">Kayıt Yok</small>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        <!-- Grafik Bölümleri -->
        <div class="row grafik-t-c">
            <!-- Servis Sayıları Grafiği -->
            <div class="col-lg-7">
                <div class="card card-statics shadow-sm servisSayilariChart" style="height: 300px;">
                    <div class="card-header card-statics-header  d-flex justify-content-between align-items-center col-c">
                        <div class="d-flex px-1 align-items-center head-padding">
                            <span class="custom-header" style="font-weight: 600; color: #2c3e50; font-size: 16px;">Servis Sayıları</span>
                        </div>
                        <ul class="nav nav-tabs border-0" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active chart-tab" data-bs-toggle="tab" href="#gun7" data-days="7">7 Gün</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link chart-tab" data-bs-toggle="tab" href="#gun15" data-days="15">15 Gün</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link chart-tab" data-bs-toggle="tab" href="#gun30" data-days="30">30 Gün</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body card-statics-body" style="height: calc(100% - 70px);">
                        <div class="tab-content h-100">
                            <div id="gun7" class="tab-pane fade show active h-100">
                                <canvas id="myAreaChart" style="height: 100% !important;"></canvas>
                            </div>
                            <div id="gun15" class="tab-pane fade h-100">
                                <canvas id="myAreaChart2" style="height: 100% !important;"></canvas>
                            </div>
                            <div id="gun30" class="tab-pane fade h-100">
                                <canvas id="myAreaChart3" style="height: 100% !important;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Saatlik Dağılım Grafiği -->
            <div class="col-lg-5">
                <div class="card card-statics shadow-sm servisSaatleriChart" style="height: 300px;">
                    <div class="card-header card-statics-header  d-flex justify-content-between align-items-center col-j">
                        <div class="d-flex align-items-center head-padding px-1">
                            <span class="custom-header" style="font-weight: 600; color: #2c3e50; font-size: 16px;">Saat Araıkları</span>
                        </div>
                        <!-- Tab Navigation -->
                        <ul class="nav nav-tabs border-0 grafik-csutom" role="tablist">
                            <li class="nav-item  calendar">
                            <input type="date" name="saatTarih" class="form-control form-control-sm saatTarih" style="max-width: 100px;" value="{{ date('Y-m-d') }}">
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active hourly-tab" data-bs-toggle="tab" href="#saat7" data-type="7days">7 Gün</a>
                            </li>
                            <li class="nav-item tarih-m-l">
                                <a class="nav-link hourly-tab" data-bs-toggle="tab" href="#saat15" data-type="15days">15 Gün</a>
                            </li>
                            <li class="nav-item tarih-m-l">
                                <a class="nav-link hourly-tab" data-bs-toggle="tab" href="#saat30" data-type="30days">30 Gün</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body card-statics-body" style="height: calc(100% - 70px);">
                        <div class="tab-content h-100">
                            <div id="saat7" class="tab-pane fade show active h-100">
                                <canvas id="saatArea7" style="height: 100% !important;"></canvas>
                            </div>
                            <div id="saat15" class="tab-pane fade h-100">
                                <canvas id="saatArea15" style="height: 100% !important;"></canvas>
                            </div>
                            <div id="saat30" class="tab-pane fade h-100">
                                <canvas id="saatArea30" style="height: 100% !important;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tenantId = {{ $tenant_id }};
    let chartInstances = {}; // Chart instance'larını saklamak için
    let hourlyChartInstances = {}; // Saatlik chart instance'lar

    // Chart.js defaults
    Chart.defaults.font.family = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
    Chart.defaults.color = '#292b2c';

    // Compact Period Tab-Accordion Functionality
    const compactTabs = document.querySelectorAll('.period-tab-compact');
    const collapseElements = document.querySelectorAll('.period-accordion .collapse');
    
    compactTabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const targetId = this.getAttribute('data-target');
            const targetCollapse = document.querySelector(targetId);
            
            if (!targetCollapse) return;
            
            // Tüm tab'lardan active class'ını kaldır
            compactTabs.forEach(t => {
                t.classList.remove('active');
                t.setAttribute('aria-expanded', 'false');
            });
            
            // Tüm collapse'ları gizle
            collapseElements.forEach(collapse => {
                collapse.classList.remove('show');
            });
            
            // Aktif tab'ı işaretle
            this.classList.add('active');
            this.setAttribute('aria-expanded', 'true');
            
            // Hedef collapse'ı göster (animasyon olmadan)
            targetCollapse.classList.add('show');
            
            return false;
        });
    });

    // Bootstrap'ın varsayılan collapse davranışını tamamen devre dışı bırak
    collapseElements.forEach(collapse => {
        // Bootstrap event'lerini engelle
        collapse.addEventListener('show.bs.collapse', function(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });
        
        collapse.addEventListener('hide.bs.collapse', function(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });
        
        collapse.addEventListener('shown.bs.collapse', function(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });
        
        collapse.addEventListener('hidden.bs.collapse', function(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        });
    });

    // Dinamik chart yükleme fonksiyonu (Servis Sayıları)
    function loadChart(days, canvasId, colors) {
        fetch(`/${tenantId}/chart-data?days=${days}`)
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById(canvasId).getContext('2d');
                
                // Eski chart instance'ını yok et
                if (chartInstances[canvasId]) {
                    chartInstances[canvasId].destroy();
                }

                const tarih = data.map(item => {
                    const date = new Date(item.tarih);
                    return String(date.getDate()).padStart(2, '0') + '/' + String(date.getMonth() + 1).padStart(2, '0');
                });

                const counts = data.map(item => item.count);

                // Yeni chart oluştur
                chartInstances[canvasId] = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: tarih,
                        datasets: [{
                            label: "Servis Sayısı",
                            tension: 0.3,
                            backgroundColor: colors.background,
                            borderColor: colors.border,
                            pointRadius: 5,
                            pointBackgroundColor: colors.point,
                            pointBorderColor: "rgba(255,255,255,0.8)",
                            pointHoverRadius: 5,
                            pointHoverBackgroundColor: colors.point,
                            pointHitRadius: 50,
                            pointBorderWidth: 2,
                            data: counts,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: "rgba(0, 0, 0, .125)"
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            })
            .catch(error => {
                console.error('Chart yüklenirken hata:', error);
            });
    }

    // Saatlik chart yükleme fonksiyonu
    function loadHourlyChart(type, canvasId, colors, date = null) {
        let url = `/${tenantId}/hourly-data?type=${type}`;
        if (date) {
            url += `&date=${date}`;
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById(canvasId).getContext('2d');
                
                // Eski chart instance'ını yok et
                if (hourlyChartInstances[canvasId]) {
                    hourlyChartInstances[canvasId].destroy();
                }

                const labels = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24'];

                // Yeni chart oluştur
                hourlyChartInstances[canvasId] = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: "Servis Sayısı",
                            tension: 0.3,
                            backgroundColor: colors.background,
                            borderColor: colors.border,
                            pointRadius: 5,
                            pointBackgroundColor: colors.point,
                            pointBorderColor: "rgba(255,255,255,0.8)",
                            pointHoverRadius: 5,
                            pointHoverBackgroundColor: colors.point,
                            pointHitRadius: 50,
                            pointBorderWidth: 2,
                            data: data,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: "rgba(0, 0, 0, .125)"
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            })
            .catch(error => {
                console.error('Saatlik chart yüklenirken hata:', error);
            });
    }
    //Sayısal Chart renklerini tanımla
    const chartColors = {
        7: {
            background: "rgba(255,165,0,0.2)",
            border: "rgba(255,165,0,0.7)",
            point: "rgba(255,165,0,1)"
        },
        15: {
            background: "rgba(255,0,0,0.2)",
            border: "rgba(255,0,0,0.7)",
            point: "rgba(255,0,0,1)"
        },
        30: {
            background: "rgba(84,177,47,0.2)",
            border: "rgba(84,177,47,0.7)",
            point: "rgba(84,177,47,1)"
        }
    };

    // Saatlik chart renkleri
    const hourlyColors = {
        '7days': {
            background: "rgba(255,0,0,0.2)",
            border: "rgba(255,0,0,0.7)",
            point: "rgba(255,0,0,1)"
        },
        '15days': {
            background: "rgba(2,117,216,0.2)",
            border: "rgba(2,117,216,1)",
            point: "rgba(2,117,216,1)"
        },
        '30days': {
            background: "rgba(255,165,0,0.2)",
            border: "rgba(255,165,0,0.7)",
            point: "rgba(255,165,0,1)"
        }
    };

    // İlk chart'ları yükle
    loadChart(7, 'myAreaChart', chartColors[7]);
    loadHourlyChart('7days', 'saatArea7', hourlyColors['7days']);

    // Tab değiştirildiğinde chart'ları yükle (Servis Sayıları)
    document.querySelectorAll('.chart-tab').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            const days = parseInt(this.dataset.days);
            let canvasId = '';
            
            switch(days) {
                case 7:
                    canvasId = 'myAreaChart';
                    break;
                case 15:
                    canvasId = 'myAreaChart2';
                    break;
                case 30:
                    canvasId = 'myAreaChart3';
                    break;
            }
            
            if (canvasId) {
                loadChart(days, canvasId, chartColors[days]);
            }
        });
    });

    // Saatlik chart tab değişimi
    document.querySelectorAll('.hourly-tab').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            const type = this.dataset.type;
            let canvasId = '';
            let date = null;
            
            switch(type) {
                case 'today':
                    canvasId = 'saatArea';
                    // Eğer tarih seçilmişse onu kullan
                    const selectedDate = document.querySelector('.saatTarih').value;
                    if (selectedDate && selectedDate !== new Date().toISOString().split('T')[0]) {
                        date = new Date(selectedDate).toLocaleDateString('tr-TR');
                    }
                    break;
                case '7days':
                    canvasId = 'saatArea7';
                    break;
                case '15days':
                    canvasId = 'saatArea15';
                    break;
                case '30days':
                    canvasId = 'saatArea30';
                    break;
            }
            
            
            if (canvasId && hourlyColors[type]) {
                loadHourlyChart(type, canvasId, hourlyColors[type], date);
            }
        });
    });

    // Tarih değiştirildiğinde grafik güncelle (aktif tab'a göre)
    document.querySelector('.saatTarih').addEventListener('change', function () {
        const dateValue = this.value;

        if (!dateValue) return;

        // Aktif tab'ı bul
        const activeTab = document.querySelector('.hourly-tab.active');
        if (!activeTab) return;

        const type = activeTab.dataset.type;
        let canvasId = '';

        switch (type) {
            case '7days':
                canvasId = 'saatArea7';
                break;
            case '15days':
                canvasId = 'saatArea15';
                break;
            case '30days':
                canvasId = 'saatArea30';
                break;
        }

        if (canvasId && hourlyColors[type]) {
            loadHourlyChart(type, canvasId, hourlyColors[type], dateValue);
        }
    });
});
</script>

<script>
$(document).ready(function () {
    // Başlangıç ve bitiş tarihlerini gizli inputlardan al
    let start_date = moment($('#tarih1').val());
    let end_date = moment($('#tarih2').val());

    // Daterangepicker'ı başlat
    $('#daterange').daterangepicker({
        startDate: start_date,
        endDate: end_date,
        locale: {
            format: 'DD-MM-YYYY',
            separator: ' - ',
            applyLabel: 'Uygula',
            cancelLabel: 'İptal',
            weekLabel: 'H',
            daysOfWeek: ['Pz', 'Pzt', 'Sal', 'Çrş', 'Prş', 'Cm', 'Cmt'],
            monthNames: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'],
            firstDay: 1
        }
    }, function(start, end) {
        // Tarih seçildiğinde gizli inputları güncelle
        $('#tarih1').val(start.format('YYYY-MM-DD'));
        $('#tarih2').val(end.format('YYYY-MM-DD'));
    });
    
    // Kısayol butonları için fonksiyon
    function updateRange(start, end) {
        $('#daterange').data('daterangepicker').setStartDate(start);
        $('#daterange').data('daterangepicker').setEndDate(end);
        // Gizli inputları da güncelle
        $('#tarih1').val(start.format('YYYY-MM-DD'));
        $('#tarih2').val(end.format('YYYY-MM-DD'));
    }

    // Kısayol buton eventleri
    $('#lastMonth').click(function() { updateRange(moment().subtract(1, 'month'), moment()); });
    $('#last15Days').click(function() { updateRange(moment().subtract(15, 'days'), moment()); });
    $('#lastWeek').click(function() { updateRange(moment().subtract(7, 'days'), moment()); });
    $('#yesterday').click(function() { updateRange(moment().subtract(1, 'days'), moment().subtract(1, 'days')); });
    $('#today').click(function() { updateRange(moment(), moment()); });

    // Dropdown'ın kapanmasını engellemek için
    let isClickFromDaterangepicker = false;
    $('.servisDrop').parent().on('hide.bs.dropdown', function(e) {
        if (isClickFromDaterangepicker) {
            e.preventDefault();
        }
        isClickFromDaterangepicker = false;
    });

    $(document).on('mousedown', function(e) {
        if ($(e.target).closest('.daterangepicker, #daterange, .tarihAraligi button').length) {
            isClickFromDaterangepicker = true;
        }
    });

    $('#daterange').on('apply.daterangepicker cancel.daterangepicker', function() {
        // Daterangepicker'dan bir seçim yapıldığında dropdown'ın kapanmasına izin ver
        // ama bunu bir sonraki tıklamada yapmak için bayrağı sıfırla
        setTimeout(() => isClickFromDaterangepicker = false, 100);
    });

    $('.servisDrop button').on('click', function(e) {
        // Kısayol butonları tıklandığında formun gönderilmesini engelle
        e.stopPropagation();
    });
});
</script>
<script>
    $(document).ready(function () {
      var dropdownContainer = $('#servis_s_filtre');
      var filterButton = dropdownContainer.find('.filtrele');
      dropdownContainer.on('show.bs.dropdown', function () {
        filterButton.html('Kapat <i class="mdi mdi-chevron-down"></i>');
      });
      dropdownContainer.on('hide.bs.dropdown', function () {
        filterButton.html('Filtrele <i class="mdi mdi-chevron-down"></i>');
      });
    });
  </script>
@endsection