@extends('frontend.secure.user_master')
@section('user')
{{-- CSRF Token Meta Tag --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
<link href="{{ asset('frontend/css/index.css') }}" rel="stylesheet" type="text/css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<div class="page-title" style="height:30px;"></div>
<div class="dashboard-container">
    <!-- Hata mesajı -->
    <div id="errorMessage" class="error-message"></div>
    
    <!-- Üst İstatistik Kartları -->
    <div class="row main-page-custom">
        <div class="col-lg-3 col-md-6">
            <a href="#" class="stat-card blue" id="totalServicesCard">
                <i class="fas fa-tools stat-icon"></i>
                <div class="stat-value" id="totalServices">-</div>
                <div class="stat-label">Aylık Servis Sayısı</div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6">
            <a href="#" class="stat-card green" id="totalCustomersCard">
                <i class="fas fa-users stat-icon"></i>
                <div class="stat-value" id="totalCustomers">-</div>
                <div class="stat-label">Aylık Müşteri Sayısı</div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6">
            <a href="{{ route('staffs', ['tenant_id' => request()->route('tenant_id')]) }}" class="stat-card red" id="totalPersonnelCard">
                <i class="fas fa-user-tie stat-icon"></i>
                <div class="stat-value" id="totalPersonnel">-</div>
                <div class="stat-label">Aktif Personel Sayısı</div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6">
            <a href="#" onclick="window.location.href = buildCashUrl('total')" class="stat-card teal" id="totalCashCard">
                <i class="fas fa-lira-sign stat-icon"></i>
                <div class="stat-value" id="totalCash">0,00 TL</div>
                <div class="stat-label">Aylık Kasa</div>
            </a>
        </div>
    </div>
     
    <!-- Servis Özeti -->
    <div class="service-summary">
        <h5><i class="fas fa-chart-bar"></i> Servis Sayıları</h5>
        <div class="row custom-padding">
            <div class="col-md-4 main-card-padding-l">
                <a href="#" class="service-item today index-color" id="todayServicesCard">
                    <div class="service-count" id="todayServices">-</div>
                    <div>BUGÜN Alınan Servis Sayısı</div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="#" class="service-item yesterday index-color" id="todayCancelledCard">
                    <div class="service-count" id="todayCancelledServices">-</div>
                    <div>BUGÜN İptal Edilen Servisler</div>
                </a>
            </div>
            <div class="col-md-4 main-card-padding-r">
                <a href="#" class="service-item in-process index-color" id="todayInProcessCard">
                    <div class="service-count" id="todayInProcessServices">-</div>
                    <div>BUGÜN İşlemde Olan Servisler</div>
                </a>
            </div>
        </div>
    </div>
   
    <!-- Grafikler -->
    <div class="row">
        <div class="col-lg-6">
            <div class="chart-container">
                <div class="chart-header">
                    <h5><i class="fas fa-chart-line"></i> Günlük Servis Trendi</h5>
                    <div class="time-filter">
                        <button class="filter-btn active" data-period="7" data-chart="daily">7 Gün</button>
                        <button class="filter-btn" data-period="15" data-chart="daily">15 Gün</button>
                        <button class="filter-btn" data-period="30" data-chart="daily">30 Gün</button>
                    </div>
                </div>
                <div class="loading"><i class="fas fa-spinner fa-spin"></i> Yükleniyor...</div>
                <div class="chart-canvas">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="chart-container">
                <div class="chart-header">
                    <h5><i class="fas fa-clock"></i> Saatlik Servis Dağılımı</h5>
                    <div class="time-filter">
                        <button class="filter-btn active" data-period="7" data-chart="hourly">7 Gün</button>
                        <button class="filter-btn" data-period="15" data-chart="hourly">15 Gün</button>
                        <button class="filter-btn" data-period="30" data-chart="hourly">30 Gün</button>
                    </div>
                </div>
                <div class="loading"><i class="fas fa-spinner fa-spin"></i> Yükleniyor...</div>
                <div class="chart-canvas">
                    <canvas id="hourlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Alt Kartlar - Operatör için düzenlenmiş -->
<div class="row">
    <!-- Son Servis Talepleri -->
    <div class="@unlessrole('Operatör') col-lg-6 @else col-lg-12 @endunlessrole">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h5><i class="fas fa-clipboard-list"></i> Son Servis Talepleri</h5>
                <a href="{{ route('all.services', ['tenant_id' => request()->route('tenant_id')]) }}" class="view-all-btn">
                    Tümünü Gör <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="dashboard-card-body">
                @forelse ($last_services as $service)
                <div class="service-request-item">
                    <div class="service-customer-info">
                        <h6>{{ $service->customer_name }} <small class="text-muted">#{{ $service->service_id }}</small></h6>
                        <p>{{ $service->service_description }}</p>
                        <p style="font-size: 0.7rem; color: #8892b0;">
                            <i class="fas fa-user-cog"></i> {{ $service->technician_name ?? 'Atanmadı' }} | 
                            <i class="fas fa-calendar-alt"></i> {{ $service->estimated_date ? \Carbon\Carbon::parse($service->estimated_date)->format('d.m.Y') : 'Belirsiz' }}
                        </p>
                    </div>
                    <span class="service-status-badge {{ $service->status_info['class'] }}">{{ $service->status_info['name'] }}</span>
                </div>
                @empty
                <p class="text-center text-muted">Gösterilecek servis talebi bulunamadı.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Kritik Stok Uyarıları - Sadece Operatör olmayan kullanıcılar görebilir -->
    @unlessrole('Operatör')
    <div class="col-lg-6">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h5><i class="fas fa-exclamation-triangle"></i> Kritik Stok Uyarıları</h5>
                <a href="{{ route('stocks', ['tenant_id' => request()->route('tenant_id')]) }}" class="view-all-btn">
                    Tümünü Gör <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="dashboard-card-body">
                @php $criticalCount = count($stock_alerts['critical']); @endphp
                
                {{-- Kritik Seviyedeki Ürünler --}}
                @foreach ($stock_alerts['critical'] as $item)
                <div class="stock-item">
                    <div class="stock-icon-wrapper critical"><i class="fas fa-box-open"></i></div>
                    <div class="stock-details">
                        <h6>{{ $item->urunAdi }}</h6>
                        <p>Kategori ID: {{ $item->urunKategori }}</p>
                    </div>
                    <div class="stock-level critical">
                        <div class="level-text">{{ $item->current_stock }} / {{ $item->threshold }}</div>
                        <div class="level-label">Kritik Seviye</div>
                    </div>
                </div>
                @endforeach

                {{-- Düşük Stoktaki Ürünler --}}
                @foreach ($stock_alerts['low'] as $item)
                <div class="stock-item">
                    <div class="stock-icon-wrapper low"><i class="fas fa-box-open"></i></div>
                    <div class="stock-details">
                        <h6>{{ $item->urunAdi }}</h6>
                        <p>Kategori ID: {{ $item->urunKategori }}</p>
                    </div>
                    <div class="stock-level low">
                        <div class="level-text">{{ $item->current_stock }} / {{ $item->threshold }}</div>
                        <div class="level-label">Düşük Stok</div>
                    </div>
                </div>
                @endforeach
                
                {{-- Eğer kritik ürün varsa alt uyarı mesajı --}}
                @if ($criticalCount > 0)
                <div class="stock-alert-footer">
                    <i class="fas fa-info-circle"></i> {{ $criticalCount }} ürün kritik stok seviyesinde! Acilen tedarik yapılması gerekiyor.
                </div>
                @endif

                {{-- Eğer hiç uyarı yoksa --}}
                @if(empty($stock_alerts['critical']) && empty($stock_alerts['low']))
                <p class="text-center text-muted">Kritik seviyede ürün bulunmamaktadır.</p>
                @endif
            </div>
        </div>
    </div>
    @endunlessrole
</div>

<script>
    // Global değişkenler
    let dailyChart, hourlyChart;
    let currentDailyPeriod = 7;
    let currentHourlyPeriod = 7;

    // CSRF token al
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Hata gösterme fonksiyonu
    function showError(message) {
        const errorDiv = document.getElementById('errorMessage');
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        setTimeout(() => {
            errorDiv.style.display = 'none';
        }, 5000);
    }

    // Tarih formatı fonksiyonu
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // URL oluşturma fonksiyonu
    function buildServiceUrl(type) {
        const tenant_id = {{ $user->tenant->id }};
        const baseUrl = `/${tenant_id}/servisler`;
        const today = new Date();
        let startDate, endDate;

        let extraParams = '';

        switch(type) {
            case 'today':
                startDate = formatDate(today);
                endDate = formatDate(today);
                break;
            case 'today-cancelled':
                startDate = formatDate(today);
                endDate = formatDate(today);
                extraParams = '&status_group=cancelled';
                break;
            case 'today-in-process':
                startDate = formatDate(today);
                endDate = formatDate(today);
                extraParams = '&status_group=in_process';
                break;
            case 'total':
                const lastMonth = new Date(today);
                lastMonth.setMonth(today.getMonth() - 1);
                startDate = formatDate(lastMonth);
                endDate = formatDate(today);
                break;
            default:
                startDate = formatDate(today);
                endDate = formatDate(today);
        }

        return `${baseUrl}?dashboard_filter=1&dashboard_istatistik_tarih1=${startDate}&dashboard_istatistik_tarih2=${endDate}${extraParams}`;
    }

    function buildCashUrl(type) {
        const tenant_id = {{ $user->tenant->id }};
        const baseUrl = `/${tenant_id}/kasa-filtrele`;
        const today = new Date();
        let startDate, endDate;

        switch(type) {
            case 'today':
                startDate = formatDate(today);
                endDate = formatDate(today);
                break;
            case 'yesterday':
                const yesterday = new Date(today);
                yesterday.setDate(today.getDate() - 1);
                startDate = formatDate(yesterday);
                endDate = formatDate(yesterday);
                break;
            case 'previous':
                const previousDay = new Date(today);
                previousDay.setDate(today.getDate() - 2);
                startDate = formatDate(previousDay);
                endDate = formatDate(previousDay);
                break;
            case 'total':
                const lastMonth = new Date(today);
                lastMonth.setMonth(today.getMonth() - 1);
                startDate = formatDate(lastMonth);
                endDate = formatDate(today);
                break;
            default:
                startDate = formatDate(today);
                endDate = formatDate(today);
        }

        return `${baseUrl}?dashboard_filter=1&dashboard_istatistik_tarih1=${startDate}&dashboard_istatistik_tarih2=${endDate}`;
    }
    function buildCustomerUrl() {
        const tenant_id = {{ $user->tenant->id }};
        const baseUrl = `/${tenant_id}/musteriler`; // Müşteri listesi route'u
        const today = new Date();
        
        // Son 1 ay için tarihleri hesapla
        const lastMonth = new Date(today);
        lastMonth.setMonth(today.getMonth() - 1);
        const startDate = formatDate(lastMonth);
        const endDate = formatDate(today);

        // Filtre parametreleriyle tam URL'yi oluştur
        return `${baseUrl}?dashboard_filter=1&dashboard_istatistik_tarih1=${startDate}&dashboard_istatistik_tarih2=${endDate}`;
    }


    // İstatistikleri yükle
    async function loadStats() {
        try {
            const tenant_id = {{ $user->tenant->id }};
            const url = `/${tenant_id}/dashboard/stats`;
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            const result = await response.json();
            if (result.success) {
                const data = result.data;
                updateCounter('totalServices', data.total_services);
                updateCounter('totalCustomers', data.total_customers);
                updateCounter('totalPersonnel', data.total_personnel);
                updateCounter('todayServices', data.today_services);
                updateCounter('todayCancelledServices', data.today_cancelled_services);
                updateCounter('todayInProcessServices', data.today_in_process_services);
                document.getElementById('totalCash').textContent = 
                    new Intl.NumberFormat('tr-TR', {
                        style: 'currency',
                        currency: 'TRY',
                        minimumFractionDigits: 2
                    }).format(data.monthly_cash.net);
            } else {
                showError('İstatistikler yüklenirken hata oluştu: ' + result.message);
            }
        } catch (error) {
            console.error('AJAX Stats hatası:', error);
            showError('Sunucuya bağlanırken hata oluştu. Lütfen daha sonra tekrar deneyin.');
        }
    }

    // Grafik verilerini yükle 
    async function loadChartData(period, chartType) {
        try {
            const tenant_id = {{ $user->tenant->id }};
            const url = `/${tenant_id}/dashboard/chart-data?period=${period}&type=${chartType}`;
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            const result = await response.json();
            if (result.success) {
                return result.data;
            } else {
                showError('Grafik verisi yüklenirken hata oluştu: ' + result.message);
                return null;
            }
        } catch (error) {
            console.error('AJAX Chart hatası:', error);
            showError('Grafik verisi yüklenirken hata oluştu.');
            return null;
        }
    }

    // Grafik başlatma
    async function initCharts() {
        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
        const dailyData = await loadChartData(7, 'daily');
        const hourlyData = await loadChartData(7, 'hourly');

        // Daily Chart
        dailyChart = new Chart(dailyCtx, {
            type: 'bar',
            data: {
                labels: dailyData?.labels || [],
                datasets: [{
                    label: 'Günlük Servis',
                    data: dailyData?.data || [],
                    backgroundColor: 'rgba(79, 172, 254, 0.8)',
                    borderColor: '#4facfe',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f1f1' }, ticks: { color: '#6c757d' } },
                    x: { grid: { display: false }, ticks: { color: '#6c757d' }, categoryPercentage: 0.8, barPercentage: 0.6 }
                }
            }
        });

        // Hourly Chart
        hourlyChart = new Chart(hourlyCtx, {
            type: 'line',
            data: {
                labels: hourlyData?.labels || [],
                datasets: [{
                    label: 'Saatlik Servis',
                    data: hourlyData?.data || [],
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255,193,7,0.2)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#ffc107',
                    pointBorderColor: '#ffc107',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f1f1' }, ticks: { color: '#6c757d' } },
                    x: { grid: { display: false }, ticks: { color: '#6c757d' } }
                }
            }
        });
    }

    // Grafik güncelleme fonksiyonu
    async function updateChart(chartType, period) {
        const loadingEl = document.querySelector(`.chart-container:has(#${chartType}Chart) .loading`);
        loadingEl.style.display = 'block';
        try {
            const data = await loadChartData(period, chartType);
            if (data) {
                const chart = (chartType === 'daily') ? dailyChart : hourlyChart;
                chart.data.labels = data.labels;
                chart.data.datasets[0].data = data.data;
                chart.update('active');
                if (chartType === 'daily') currentDailyPeriod = period;
                else currentHourlyPeriod = period;
            }
        } catch (error) {
            showError('Grafik güncellenirken hata oluştu.');
        } finally {
            loadingEl.style.display = 'none';
        }
    }

    function updateCounter(elementId, targetValue) {
        const element = document.getElementById(elementId);
        if (element) element.textContent = targetValue;
    }

    document.addEventListener('DOMContentLoaded', function() {
        loadStats();
        initCharts();
        
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const period = parseInt(this.dataset.period);
                const chartType = this.dataset.chart;
                this.parentElement.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                updateChart(chartType, period);
            });
        });

        document.getElementById('totalServicesCard').addEventListener('click', (e) => { e.preventDefault(); window.location.href = buildServiceUrl('total'); });
        document.getElementById('todayServicesCard').addEventListener('click', (e) => { e.preventDefault(); window.location.href = buildServiceUrl('today'); });
        document.getElementById('todayCancelledCard').addEventListener('click', (e) => { e.preventDefault(); window.location.href = buildServiceUrl('today-cancelled'); });
        document.getElementById('todayInProcessCard').addEventListener('click', (e) => { e.preventDefault(); window.location.href = buildServiceUrl('today-in-process'); });
        document.getElementById('totalCustomersCard').addEventListener('click', (e) => { e.preventDefault(); window.location.href = buildCustomerUrl(); });
    });

    setInterval(loadStats, 5 * 60 * 1000); // 5 minutes
</script>
@endsection