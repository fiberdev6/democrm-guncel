@extends('frontend.secure.user_master')
@section('user')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<div class="page-content servis-istatistik" id="technicianStatsPage">
    <div class="container-fluid">
        @include('frontend.secure.statistics.statistics_menu', ['tenant_id' => $tenant_id])
        <div class="card card-statics-t">
            <div class="techinican-p card-header card-statics-t-header sayfaBaslik d-flex justify-content-between align-items-center">
                <span class="custom-header">Teknisyen İstatistikleri</span>
                <!-- Filtre Dropdown -->
                <div class="dropdown teknisyen-dropdown" id="teknisyenFilterDropdownContainer">
                <button class="btn btn-dark btn-sm dropdown-toggle filtrele" type="button" id="filtreDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    Filtrele <i class="mdi mdi-chevron-down"></i>
                </button>
                <div class="dropdown-menu servisDrop teknisyen-dropdown-menu p-3 dropdown-menu-end" style="min-width: 300px;">
                        <form id="filtreForm">
                            <div class="row mb-3">
                                <label class="col-5 col-form-label">Cihaz Türü</label>
                                <div class="col-7">
                                    <select class="form-select" name="cihazTur" id="cihazTur">
                                        <option value="">Hepsi</option>
                                        @foreach($cihazTurleri as $cihaz)
                                            <option value="{{ $cihaz->id }}">{{ $cihaz->cihaz }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                           <div class="row mb-3">
                                <label class="col-5 col-form-label">Tarih Aralığı</label>
                                <div class="col-7">
                                     <input type="text" id="tarihAraligiTeknisyen" class="form-control" style="background:#fff;">
                                </div>
                            </div>
                              <div class="tarih-butonlari d-flex flex-wrap gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-secondary tarih-btn" data-days="30">Son 1 Ay</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary tarih-btn" data-days="15">Son 15 Gün</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary tarih-btn" data-days="7">Son 7 Gün</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary tarih-btn" data-days="1" data-yesterday="true">Dün</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary tarih-btn" data-days="0">Bugün</button>
                                </div>
                            {{-- <button type="button" class="btn btn-primary btn-sm w-100" id="araBtn">ARA</button> --}}
                        </form>
                    </div>
                </div>
            </div>

            <div class="card-body card-statics-t-body">
                <div id="loadingDiv" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Yükleniyor...</span>
                    </div>
                </div>

                <div id="istatistikTable" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="teknisyenTable">
                            <thead class="title">
                                <tr>
                                    <th>Teknisyen</th>
                                    <th class="text-center" style="width: 90px;">Atanan<br><small>Servis</small></th>
                                    <th class="text-center" style="width: 90px;">Tamamlanan<br><small>Servis</small></th>
                                    <th class="text-center" style="width: 90px;">Şikayetçi<br><small>Servis</small></th>
                                    <th class="text-center" style="width: 90px;">İptal<br><small>Servis</small></th>
                                    <th class="text-center" style="width: 90px;">Haber<br><small>Verecek</small></th>
                                    <th class="text-center" style="width: 90px;">Fiyatta<br><small>Anlaşılamadı</small></th>
                                    <th class="text-center" style="width: 90px;">Alınan<br><small>Ücret</small></th>
                                    <th class="text-center" style="width: 90px;">Verilen<br><small>Teklif</small></th>
                                </tr>
                            </thead>
                            <tbody id="teknisyenTableBody">
                                <!-- AJAX ile doldurulacak -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    let preventDropdownHide = false;
    $('#teknisyenFilterDropdownContainer').on('hide.bs.dropdown', function(e) {
        if (preventDropdownHide) {
            e.preventDefault();
        }
        preventDropdownHide = false;
    });
    $(document).on('mousedown', function(e) {
        if ($(e.target).closest('.daterangepicker').length) {
            preventDropdownHide = true;
        }
    });
    $('#teknisyenFilterDropdownContainer').find('#tarihAraligiTeknisyen').on('focus mousedown', function() {
        preventDropdownHide = true;
    });
    $('#teknisyenFilterDropdownContainer').find('.tarih-btn').on('mousedown', function() {
        preventDropdownHide = true;
    });
    $('#tarihAraligiTeknisyen').on('apply.daterangepicker cancel.daterangepicker hide.daterangepicker', function() {
        preventDropdownHide = false;
    });

     let dataTable = null;

    // Daterangepicker'ı başlat
    $('#tarihAraligiTeknisyen').daterangepicker({
        startDate: moment().subtract(29, 'days'), // Varsayılan Son 1 Ay
        endDate: moment(),
        locale: {
            format: 'DD/MM/YYYY',
            separator: ' - ',
            applyLabel: 'Uygula',
            cancelLabel: 'İptal',
            weekLabel: 'H',
            daysOfWeek: ['Pz', 'Pzt', 'Sal', 'Çrş', 'Prş', 'Cm', 'Cmt'],
            monthNames: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'],
            firstDay: 1
        }
    }, function(start, end) {
        // "Uygula" butonuna basıldığında verileri yükle
        loadTechnicianStatistics();
    });

    // Tarih kısayol butonları
    $('.tarih-btn').click(function() {
        $('.tarih-btn').removeClass('active btn-primary').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('active btn-primary');

        const days = $(this).data('days');
        const isYesterday = $(this).data('yesterday');
        const picker = $('#tarihAraligiTeknisyen').data('daterangepicker');
        
        let startDate, endDate;
        
        if (isYesterday) {
            startDate = moment().subtract(1, 'days');
            endDate = moment().subtract(1, 'days');
        } else if (days === 0) {
            startDate = moment();
            endDate = moment();
        } else {
            startDate = moment().subtract(days - 1, 'days');
            endDate = moment();
        }
        
        picker.setStartDate(startDate);
        picker.setEndDate(endDate);

        loadTechnicianStatistics();
    });

    // Varsayılan olarak "Son 1 Ay" butonunu aktif yap
    $('.tarih-btn[data-days="30"]').addClass('active btn-primary').removeClass('btn-outline-secondary');

    // Cihaz türü değiştiğinde verileri yükle
    $('#cihazTur').on('change', function() {
        loadTechnicianStatistics();
    });

    // Sayfa yüklendiğinde varsayılan verileri getir
    loadTechnicianStatistics();

    // Teknisyen istatistiklerini yükle
    function loadTechnicianStatistics() {
        $('#loadingDiv').show();
        $('#istatistikTable').hide();

        const picker = $('#tarihAraligiTeknisyen').data('daterangepicker');
        const tarih1 = picker.startDate.format('DD/MM/YYYY');
        const tarih2 = picker.endDate.format('DD/MM/YYYY');
        const cihazTur = $('#cihazTur').val();

        $.ajax({
            url: '/{{ $tenant_id }}/teknisyen-istatistikleri/data',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                tarihAraligi: tarih1 + '---' + tarih2,
                cihazTur: cihazTur
            },
            success: function(response) {
                if (response.success) {
                    buildStatisticsTable(response.data);
                } else {
                    showError(response.message || 'Bilinmeyen bir hata oluştu');
                }
            },
            error: function(xhr, status, error) {
                showError('Bir hata oluştu: ' + error);
            },
            complete: function() {
                $('#loadingDiv').hide();
                $('#istatistikTable').show();
            }
        });
    }
    // Tablo oluştur
    function buildStatisticsTable(data) {
    console.log('buildStatisticsTable çağrıldı, data:', data);

    // Önce varsa DataTable'ı tamamen yok et
    if (dataTable && $.fn.DataTable.isDataTable('#teknisyenTable')) {
        console.log('DataTable destroy ediliyor...');
        dataTable.destroy();
        dataTable = null;
    }

    // Detay satırlarını temizle
    $('.detay-satir').remove();
    $('.teknisyen-detay-btn').removeClass('clicked');

    let html = '';
    
    if (!data || data.length === 0) {
        html = '<tr><td colspan="9" class="text-center">Veri bulunamadı.</td></tr>';
        console.log('Veri bulunamadı');
    } else {
        console.log('Tablo oluşturuluyor, veri sayısı:', data.length);
        data.forEach(function(teknisyen, index) {
            console.log(`Teknisyen ${index + 1}:`, teknisyen);
            
            //İkon eklendi
            html += `
                <tr class="teknisyen-detay-btn" data-teknisyen-id="${teknisyen.id || 'unknown'}">
                    <td>
                        <strong>${teknisyen.name || 'Bilinmeyen'}</strong>
                        <i class="fas fa-chevron-right detay-icon"></i>
                    </td>
                    <td class="text-center"><strong>${teknisyen.atanan_servis || 0}</strong></td>
                    <td class="text-center"><strong>${teknisyen.tamamlanan_servis || 0}</strong></td>
                    <td class="text-center"><strong>${teknisyen.sikayetci_servis || 0}</strong></td>
                    <td class="text-center"><strong>${teknisyen.iptal_servis || 0}</strong></td>
                    <td class="text-center"><strong>${teknisyen.haber_verecek || 0}</strong></td>
                    <td class="text-center"><strong>${teknisyen.fiyat_anlasma || 0}</strong></td>
                    <td class="text-center"><strong>${formatCurrency(teknisyen.alinan_ucret || 0)}</strong></td>
                    <td class="text-center"><strong>${formatCurrency(teknisyen.verilen_teklif || 0)}</strong></td>
                </tr>
            `;
        });
    }

    console.log('HTML oluşturuldu, tbody güncelleniyor...');
    
    //Table container class'ı eklendi
    $('#teknisyenTableBody').closest('.table-responsive').addClass('table-container');
    $('#teknisyenTableBody').html(html);
    
    // HTML güncellemesinden sonra kısa bir gecikme ekle
    setTimeout(function() {
        console.log('DataTable yeniden initialize ediliyor...');
        
        // DataTable'ı yeniden initialize et
        try {
            dataTable = $('#teknisyenTable').DataTable({
                "paging": false,
                "info": false,
                "searching": false,
                "ordering": true,
                "order": [[7, 'desc']], // Alınan ücrete göre sırala
                "destroy": true,
                "language": {
                    "sEmptyTable": "Herhangi bir teknisyen verisi bulunamadı.",
                    "sSearch": "Teknisyen Ara:",
                    "sZeroRecords": "Eşleşen kayıt bulunamadı",
                    "oPaginate": {
                        "sNext": "Sonraki",
                        "sPrevious": "Önceki"
                    }
                },
                "columnDefs": [
                    { "orderable": false, "targets": 0 }
                ]
            });
            console.log('DataTable başarıyla oluşturuldu');
        } catch (error) {
            console.error('DataTable oluşturma hatası:', error);
        }
    }, 100);
}
// Teknisyen detayına tıklama
$(document).on('click', '.teknisyen-detay-btn', function() {
    const teknisyenId = $(this).data('teknisyen-id');
    const $this = $(this);

    console.log('Teknisyen detayına tıklandı, ID:', teknisyenId);
    
    // Önceki detay satırlarını kapat
    $('.detay-satir').remove();
    $('.teknisyen-detay-btn').removeClass('clicked');

    if ($this.hasClass('clicked')) {
        $this.removeClass('clicked');
    } else {
        $this.addClass('clicked');
        
        // DEĞİŞİKLİK: Daha güzel loading detay satırı
        const detayHtml = `
            <tr class="detay-satir">
                <td colspan="9">
                    <div class="p-4" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                        <div class="text-center mb-3">
                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                <span class="visually-hidden">Detay yükleniyor...</span>
                            </div>
                            <span class="text-muted">
                                <i class="fas fa-chart-line me-1"></i>
                                Teknisyen detay bilgileri yükleniyor...
                            </span>
                        </div>
                    </div>
                </td>
            </tr>
        `;
        
        $this.after(detayHtml);
        
        // Detay verilerini yükle
        loadTechnicianDetail(teknisyenId);
    }
});
// Teknisyen detay verilerini yükle
function loadTechnicianDetail(teknisyenId) {
    const picker = $('#tarihAraligiTeknisyen').data('daterangepicker');
    const tarih1 = picker.startDate.format('DD/MM/YYYY');
    const tarih2 = picker.endDate.format('DD/MM/YYYY');
    const cihazTur = $('#cihazTur').val();

    $.ajax({
        url: '/{{ $tenant_id }}/teknisyen-detay/data',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            teknisyen_id: teknisyenId,
            tarihAraligi: tarih1 + '---' + tarih2,
            cihazTur: cihazTur
        },
        success: function(response) {
            if (response.success) {
                renderTechnicianDetail(response.data);
            } else {
                showDetailError(response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Detay yükleme hatası:', error);
            showDetailError('Detay bilgileri yüklenirken hata oluştu: ' + error);
        }
    });
}

// Teknisyen detayını render et
function renderTechnicianDetail(data) {
const detayHtml = `
    <td colspan="9">
        <div class="detay-loading" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
            <!-- ... diğer içerik ... -->

            <!-- Grafikler - Düzeltilmiş boyutlar -->
           <div class="row mb-lg-4">
                <div class="col-md-4">
                    <div class="card shadow-sm" style="min-height: 250px;">
                        <div class="card-body">
                            <h6 class="card-title text-success">Tamamlanan Servisler</h6>
                            <div style="position: relative; height: 180px; width: 100%;">
                                <canvas 
                                    id="tamamlananChart_${data.id}" 
                                    style="width: 100% !important; height: 100% !important;"
                                ></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm" style="min-height: 250px;">
                        <div class="card-body">
                            <h6 class="card-title text-danger">İptal Servisler</h6>
                            <div style="position: relative; height: 180px; width: 100%;">
                                <canvas 
                                    id="iptalChart_${data.id}" 
                                    style="width: 100% !important; height: 100% !important;"
                                ></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm" style="min-height: 250px;">
                        <div class="card-body">
                            <h6 class="card-title text-info">Alınan Ücretler</h6>
                            <div style="position: relative; height: 180px; width: 100%;">
                                <canvas 
                                    id="gelirChart_${data.id}" 
                                    style="width: 100% !important; height: 100% !important;"
                                ></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detaylı Aşama Sayıları -->
            <div class="row">
                <div class="col-12">
                    <h6 class="text-dark mb-3">
                        Servis Aşamaları Detay
                    </h6>
                </div>
            </div>

            <div class="row g-3">
                ${generateStageCards(data.detay_sayilari)}
            </div>
        </div>
    </td>
`;

$('.detay-satir').html(detayHtml);

    
    // Grafikleri DOM'a eklendikten sonra çiz
    setTimeout(() => {
        drawDetailCharts(data);
    }, 100); //bekleme süresi

}

// Aşama kartlarını oluştur
function generateStageCards(detaySayilari) {
    const stages = [
        { key: 'atanan_servis', label: 'Atanan Servisler', color: 'primary' },
        { key: 'tamamlanan_servis', label: 'Tamamlanan Servisler', color: 'primary' },
        { key: 'sikayetci_servis', label: 'Şikayetçi Servisler', color: 'primary' },
        { key: 'iptal_servis', label: 'İptal Servisler', color: 'primary' },
        { key: 'haber_verecek', label: 'Haber Verecek', color: 'primary' },
        { key: 'atolyede_tamir', label: 'Atölyede Tamir Ediliyor', color: 'primary' },
        { key: 'atolyeye_aldir', label: 'Atölyeye Aldır(Nakliye  Gönder)', color: 'primary' },
        { key: 'cihaz_atolyede', label: 'Cihaz Atölyeye Alındı', color: 'primary' },
        { key: 'tamir_edilemiyor', label: 'Cihaz Tamir Edilemiyor', color: 'primary' },
        { key: 'cihaz_teslim', label: 'Cihaz Teslim Edildi', color: 'primary' },
        { key: 'cihaz_teslim_parca', label: 'Cihaz Teslim Edildi(Parça Takıldı)', color: 'primary' },
        { key: 'fiyat_anlasilamadi', label: 'Fiyatta Anlaşılamadı', color: 'primary' },
        { key: 'musteri_atolyeye_getirdi', label: 'Müşteri Cihazı Atölyeye Getirdi', color: 'primary' },
        { key: 'musteriye_ulasilamadi', label: 'Müşteriye Ulaşılamadı', color: 'primary' },
        { key: 'nakliye_gonder', label: 'Nakliye Gönder', color: 'primary' },
        { key: 'nakliye_teslim', label: 'Nakliyede (Teslim Edilecek)', color: 'primary' },
        { key: 'parca_hazir', label: 'Parça Hazır', color: 'primary' },
        { key: 'parca_sipariste', label: 'Parça Siparişte', color: 'primary' },
        { key: 'parca_tek_yon', label: 'Parça Teknisyen Yönlendir', color: 'primary' },
        { key: 'parca_atolyeye_alindi', label: 'Parçası Atölyeye Alındı', color: 'primary' },
        { key: 'tahsilata_gonder', label: 'Tahsilata Gönder', color: 'primary' },
        { key: 'teslimata_hazir', label: 'Teslimata Hazır(Tamamlandı)', color: 'primary' },
        { key: 'garantili_cikti', label: 'Ürün Garantili Çıktı', color: 'primary' },
        { key: 'yeniden_tek_yon', label: 'Yeniden Teknisyen Yönlendir', color: 'primary' },
        { key: 'yerinde_bakim', label: 'Yerinde Bakım Yapıldı', color: 'primary' },
        { key: 'cihaz_satisi_yapildi', label: 'Cihaz Satışı Yapıldı', color: 'primary' },
        { key: 'bayiye_gonder', label: 'Bayiye Gönder', color: 'primary' },
        { key: 'musteri_para_iade_edilecek', label: 'Müşteri Para İade Edilecek', color: 'primary' },
        { key: 'musteri_para_iade_edildi', label: 'Müşteri Para İade Edildi', color: 'primary' },
        { key: 'fiyat_yukseltildi', label: 'Fiyat Yükseltildi', color: 'primary' },
        { key: 'konsinye_cihaz_ata', label: 'Konsinye Cihaz Ata', color: 'primary' },
        { key: 'konsinye_cihaz_geri_alindi', label: 'Konsinye Cihaz Geri Alındı', color: 'primary' },
    ];

    let html = '';
    stages.forEach(stage => {
        const value = detaySayilari[stage.key] || 0;
        const badgeClass = value > 0 ? `bg-${stage.color}` : 'bg-light text-dark';
        
        html += `
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body technician_statistics_card text-center p-3">
                        <div class="mb-2">
                            <span class="badge ${badgeClass} fs-6 px-3 py-2">${value}</span>
                        </div>
                        <p class="card-text small mb-0" style="font-size: 0.85rem;">
                            ${stage.label}
                        </p>
                    </div>
                </div>
            </div>
        `;
    });

    return html;
}

// Detay grafiklerini çiz
function drawDetailCharts(data) {
    console.log('drawDetailCharts çağrıldı, data:', data);
    
    if (typeof Chart === 'undefined') {
        console.error('Chart.js kütüphanesi yüklenmemiş!');
        return;
    }

    if (!data.grafik_data || !data.grafik_data.labels) {
        console.error('Grafik verileri eksik:', data.grafik_data);
        return;
    }

    // Chart.js versiyon kontrolü ve uygun destroy metodu
    function destroyExistingChart(canvasId) {
        try {
            // Chart.js v3+ için
            const existingChart = Chart.getChart(canvasId);
            if (existingChart) {
                existingChart.destroy();
                console.log(`${canvasId} grafiği temizlendi`);
            }
        } catch (error) {
            console.warn('Grafik temizleme uyarısı:', error);
        }
    }

    // Daha güvenli canvas seçimi
    function safeGetCanvas(canvasId) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.error(`Canvas bulunamadı: ${canvasId}`);
            return null;
        }
        
        // Canvas boyutlarını kontrol et ve ayarla
        if (canvas.offsetWidth === 0 || canvas.offsetHeight === 0) {
            console.warn(`Canvas boyutları sıfır: ${canvasId}`);
            canvas.style.width = '100%';
            canvas.style.height = '200px';
        }
        
        return canvas;
    }

    const chartOptions = {
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
                ticks: { 
                    stepSize: 1,
                    precision: 0,
                    callback: function(value) {
                        return Number.isInteger(value) ? value : '';
                    }
                }
            },
            x: {
                ticks: {
                    maxRotation: 45,
                    minRotation: 0
                }
            }
        },
        elements: {
            point: {
                radius: 4,
                hoverRadius: 6
            },
            line: {
                tension: 0.3
            }
        },
        // Grafik render edildiğinde callback
        onResize: function(chart, size) {
            console.log('Grafik yeniden boyutlandırıldı:', size);
        }
    };

    // Veri kontrolü ve temizleme
    function prepareChartData(rawData, defaultValue = 0) {
        if (!Array.isArray(rawData)) {
            console.warn('Grafik verisi dizi değil:', rawData);
            return new Array(data.grafik_data.labels.length).fill(defaultValue);
        }
        
        // Null/undefined değerleri temizle
        return rawData.map(value => {
            const numValue = parseFloat(value);
            return isNaN(numValue) ? defaultValue : numValue;
        });
    }

    // Tamamlanan Servisler Grafiği
    setTimeout(() => {
        const tamamlananCanvas = safeGetCanvas(`tamamlananChart_${data.id}`);
        if (tamamlananCanvas) {
            try {
                destroyExistingChart(`tamamlananChart_${data.id}`);
                
                const tamamlananData = prepareChartData(data.grafik_data.tamamlanan);
                console.log('Tamamlanan data:', tamamlananData);
                
                new Chart(tamamlananCanvas, {
                    type: 'line',
                    data: {
                        labels: data.grafik_data.labels,
                        datasets: [{
                            label: 'Tamamlanan',
                            data: tamamlananData,
                            borderColor: 'rgba(40, 167, 69, 1)',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: 'rgba(40, 167, 69, 1)',
                            pointBorderColor: 'rgba(40, 167, 69, 1)'
                        }]
                    },
                    options: chartOptions
                });
                console.log('Tamamlanan grafiği başarıyla oluşturuldu');
            } catch (error) {
                console.error('Tamamlanan grafiği hatası:', error);
            }
        }
    }, 100);

    // İptal Servisler Grafiği
    setTimeout(() => {
        const iptalCanvas = safeGetCanvas(`iptalChart_${data.id}`);
        if (iptalCanvas) {
            try {
                destroyExistingChart(`iptalChart_${data.id}`);
                
                const iptalData = prepareChartData(data.grafik_data.iptal);
                console.log('İptal data:', iptalData);
                
                new Chart(iptalCanvas, {
                    type: 'line',
                    data: {
                        labels: data.grafik_data.labels,
                        datasets: [{
                            label: 'İptal',
                            data: iptalData,
                            borderColor: 'rgba(220, 53, 69, 1)',
                            backgroundColor: 'rgba(220, 53, 69, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: 'rgba(220, 53, 69, 1)',
                            pointBorderColor: 'rgba(220, 53, 69, 1)'
                        }]
                    },
                    options: chartOptions
                });
                console.log('İptal grafiği başarıyla oluşturuldu');
            } catch (error) {
                console.error('İptal grafiği hatası:', error);
            }
        }
    }, 150);

    // Gelir Grafiği
    setTimeout(() => {
        const gelirCanvas = safeGetCanvas(`gelirChart_${data.id}`);
        if (gelirCanvas) {
            try {
                destroyExistingChart(`gelirChart_${data.id}`);
                
                const gelirData = prepareChartData(data.grafik_data.gelir);
                console.log('Gelir data:', gelirData);
                
                new Chart(gelirCanvas, {
                    type: 'line',
                    data: {
                        labels: data.grafik_data.labels,
                        datasets: [{
                            label: 'Gelir (TL)',
                            data: gelirData,
                            borderColor: 'rgba(23, 162, 184, 1)',
                            backgroundColor: 'rgba(23, 162, 184, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: 'rgba(23, 162, 184, 1)',
                            pointBorderColor: 'rgba(23, 162, 184, 1)'
                        }]
                    },
                    options: {
                        ...chartOptions,
                        scales: {
                            ...chartOptions.scales,
                            y: {
                                ...chartOptions.scales.y,
                                ticks: {
                                    ...chartOptions.scales.y.ticks,
                                    callback: function(value) {
                                        return value.toLocaleString('tr-TR') + ' TL';
                                    }
                                }
                            }
                        }
                    }
                });
                console.log('Gelir grafiği başarıyla oluşturuldu');
            } catch (error) {
                console.error('Gelir grafiği hatası:', error);
            }
        }
    }, 200);
}
// Detay hata göster
function showDetailError(message) {
    $('.detay-satir').html(`
        <td colspan="9">
            <div class="p-4 text-center">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${message}
                </div>
            </div>
        </td>
    `);
}
    // Yardımcı fonksiyonlar
    function formatCurrency(amount) {
        const num = parseFloat(amount) || 0;
        return num.toLocaleString('tr-TR') + ' TL';
    }

    function showError(message) {
        console.error('Hata:', message);
        // toastr varsa kullan, yoksa alert kullan
        if (typeof toastr !== 'undefined') {
            toastr.error(message, 'Hata');
        } else {
            alert('Hata: ' + message);
        }
    }
});
// Moment.js için Türkçe lokalizasyon
if (typeof moment !== 'undefined') {
    moment.locale('tr');
}
</script>

<script>
  $(document).ready(function () {
    var dropdownContainer = $('#teknisyenFilterDropdownContainer');
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

