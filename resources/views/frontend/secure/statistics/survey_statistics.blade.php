@extends('frontend.secure.user_master')
@section('user')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script> <!-- Font Awesome Icons -->
    <div class="page-content servis-istatistik" id="surveyStats">
        <div class="container-fluid">
            @include('frontend.secure.statistics.statistics_menu', ['tenant_id' => $tenant_id])
            <!-- Anket Sonuçları Kartı -->
            <div class="row pageDetail mb-4">
                <div class="col-12">
                    <div class="table-modern">
                        <div style="font-size: 15px;" class="card-header">
                            Anket Sonuçları
                            <div class="searchWrap float-end">
                                <div class="btn-group pt-1" id="resultsFilterDropdownContainer">
                                    <button class="btn btn-dark btn-sm dropdown-toggle filtrele" type="button"
                                        data-bs-toggle="dropdown">
                                        Filtrele <i class="mdi mdi-chevron-down"></i>
                                    </button>
                                    <div class="dropdown-menu servisDrop p-3" style="min-width: 200px;">
                                        <div class="item">
                                            <div class="row mb-3">
                                                <label class="col-sm-4 custom-p-m-k custom-p-r-m-k col-5">Bayi:</label>
                                                <div class="col-sm-8 col-7 custom-p-m-k custom-p-r-m-k">
                                                    <select id="bayiSelect" class="form-select form-select-sm">
                                                        <option value="">Hepsi</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-4 custom-p-m-k custom-p-r-m-k col-5">Cihaz Türü:</label>
                                                <div class="col-sm-8 col-7 custom-p-m-k custom-p-r-m-k">
                                                    <select id="deviceTypeResults" class="form-select form-select-sm">
                                                        <option value="">Hepsi</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-4 custom-p-m-k custom-p-r-m-k col-5">Tarih Aralığı:</label>
                                                <div class="col-sm-8 col-7 custom-p-m-k custom-p-r-m-k">
                                                    <input id="daterangeResults"
                                                        class="form-control form-control-sm tarih-araligi" />
                                                    <div class="tarihAraligi mt-2 mb-2">
                                                        <button id="lastMonthResults"
                                                            class="btn btn-sm btn-secondary me-1">Son 1 Ay</button>
                                                        <button id="last15DaysResults"
                                                            class="btn btn-sm btn-secondary me-1">Son 15 Gün</button>
                                                        <button id="lastWeekResults"
                                                            class="btn btn-sm btn-secondary me-1">Son 7 Gün</button>
                                                        <button id="yesterdayResults"
                                                            class="btn btn-sm btn-secondary me-1">Dün</button>
                                                        <button id="todayResults"
                                                            class="btn btn-sm btn-secondary">Bugün</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Özet Bilgiler -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h4 id="totalSurveysCount" class="text-white">0</h4>
                                            <p class="mb-0">Toplam Anket</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h4 id="positiveRate" class="text-white">0%</h4>
                                            <p class="mb-0">Ortalama Memnuniyet</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body text-center">
                                            <h4 id="neutralRate" class="text-white">0%</h4>
                                            <p class="mb-0">Kararsız Cevaplar</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-danger text-white">
                                        <div class="card-body text-center">
                                            <h4 id="negativeRate" class="text-white">0%</h4>
                                            <p class="mb-0">Olumsuz Cevaplar</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Grafik ve Tablo -->
                            <div class="row d-flex">
                                <div class="col-lg-6 d-flex">
                                    <div class="card flex-fill">
                                        <div class="card-header">
                                            <h5 class="mb-0 custom-h5"><i class="fas fa-chart-bar me-2"></i>Soru Bazında Sonuçlar</h5>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="surveyChart" width="400" height="300"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 d-flex">
                                    <div class="card flex-fill">
                                        <div class="card-header">
                                            <h5 class="mb-0 custom-h5"><i class="fas fa-table me-2"></i>Detaylı Sonuçlar</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0">
                                                    <thead class="title">
                                                        <tr>
                                                            <th style="width: 40%">Sorular</th>
                                                            <th class="text-center">Evet</th>
                                                            <th class="text-center">Hayır</th>
                                                            <th class="text-center">Belli Değil</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="surveyResultsTable">
                                                        <tr>
                                                            <td><strong>Teknisyen dediği saatte geldi mi?</strong></td>
                                                            <td class="text-center"><span class="badge bg-success"
                                                                    id="soru1-evet">0</span></td>
                                                            <td class="text-center"><span class="badge bg-danger"
                                                                    id="soru1-hayir">0</span></td>
                                                            <td class="text-center"><span class="badge bg-warning"
                                                                    id="soru1-bd">0</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Teknisyen davranışlarından memnun musunuz?</strong>
                                                            </td>
                                                            <td class="text-center"><span class="badge bg-success"
                                                                    id="soru2-evet">0</span></td>
                                                            <td class="text-center"><span class="badge bg-danger"
                                                                    id="soru2-hayir">0</span></td>
                                                            <td class="text-center"><span class="badge bg-warning"
                                                                    id="soru2-bd">0</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Teknisyen cihazınızla yeterince ilgilendi
                                                                    mi?</strong></td>
                                                            <td class="text-center"><span class="badge bg-success"
                                                                    id="soru3-evet">0</span></td>
                                                            <td class="text-center"><span class="badge bg-danger"
                                                                    id="soru3-hayir">0</span></td>
                                                            <td class="text-center"><span class="badge bg-warning"
                                                                    id="soru3-bd">0</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Genel servis hizmetinden memnun musunuz?</strong>
                                                            </td>
                                                            <td class="text-center"><span class="badge bg-success"
                                                                    id="soru5-evet">0</span></td>
                                                            <td class="text-center"><span class="badge bg-danger"
                                                                    id="soru5-hayir">0</span></td>
                                                            <td class="text-center"><span class="badge bg-warning"
                                                                    id="soru5-bd">0</span></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!--Personel Bazında Anket İstatistikleri -->
            <div class="row pageDetail">
                <div class="col-12">
                    <div class="table-modern">
                        <div style="font-size: 15px;" class="card-header">
 Personel Bazında Anket İstatistikleri
                            <div class="searchWrap float-end" id="personnelFilterDropdownContainer">
                                <div class="btn-group pt-1">
                                    <button class="btn btn-dark btn-sm dropdown-toggle filtrele" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        Filtrele <i class="mdi mdi-chevron-down"></i>
                                    </button>
                                    <div class="dropdown-menu servisDrop dropdown-menu-end p-3" style="min-width: 300px;">
                                        <div class="item">
                                            <div class="row mb-3">
                                                <label class="col-sm-4 custom-p-m-k custom-p-r-m-k col-5">Cihaz Türü:</label>
                                                <div class="col-sm-8 col-7 custom-p-m-k custom-p-r-m-k">
                                                    <select id="deviceType" class="form-select form-select-sm">
                                                        <option value="">Hepsi</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-4 custom-p-m-k custom-p-r-m-k col-5">Tarih Aralığı:</label>
                                                <div class="col-sm-8 col-7 custom-p-m-k custom-p-r-m-k">
                                                    <input id="daterange"
                                                        class="form-control form-control-sm tarih-araligi" />
                                                    <div class="tarihAraligi mt-2 mb-2">
                                                        <!-- Butonlarda id yerine class -->
                                                        <button class="btn btn-sm btn-secondary me-1 btnLastMonth">Son 1
                                                            Ay</button>
                                                        <button class="btn btn-sm btn-secondary me-1 btnLast15Days">Son 15
                                                            Gün</button>
                                                        <button class="btn btn-sm btn-secondary me-1 btnLastWeek">Son 7
                                                            Gün</button>
                                                        <button
                                                            class="btn btn-sm btn-secondary me-1 btnYesterday">Dün</button>
                                                        <button class="btn btn-sm btn-secondary btnToday">Bugün</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="datatableSurveyStats" class="table table-hover mb-0">
                                    <thead class="title">
                                        <tr>
                                            <th>
                                                <i class="fas fa-user me-2"></i>
                                                <span class="d-sm-none">Personel</span>
                                                <span class="d-none d-sm-inline">Personel</span>
                                            </th>
                                            <th style="width: 170px">
                                                <i class="fas fa-check-circle me-2"></i>
                                                <span class="d-sm-none">Sonlanan</span>
                                                <span class="d-none d-sm-inline">Sonlanan Servisler</span>
                                            </th>
                                            <th style="width: 170px">
                                                <i class="fas fa-poll me-2"></i>
                                                <span class="d-sm-none">Anketler</span>
                                                <span class="d-none d-sm-inline">Yapılan Anketler</span>
                                            </th>
                                            <th style="width: 170px">İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody id="surveyStatsTableBody">
                                        <!-- Data will be loaded here -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4" class="text-start">
                                                <div><strong>Toplam Tamamlanan Servis:</strong> <span
                                                        id="totalCompletedServices">0</span></div>
                                                <div><strong>Toplam Yapılan Anket:</strong> <span
                                                        id="totalSurveyedServices">0</span></div>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // TENANT_ID'yi Blade'den JavaScript'e aktarıyoruz
        const TENANT_ID = "{{ $tenant_id }}";
        let surveyChart;

        $(document).ready(function () {
            let today = moment();
            let start_date = moment().subtract(1, 'month');
            let end_date = moment();

            // Anket sonuçları için tarih seçici
            $('#daterangeResults').daterangepicker({
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
            }, function (start, end) {
                loadSurveyResults();
            });

            // Personel istatistikleri için tarih seçici
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
            }, function (start, end) {
                loadSurveyStatistics();
            });

            let isClickFromDaterangepicker = false;
            function preventDropdownClose(dropdownContainerId) {
                const $dropdownContainer = $(`#${dropdownContainerId}`);
                $dropdownContainer.on('hide.bs.dropdown', function (e) {
                    // Eğer click daterangepicker'dan veya kısayol butonlarından geliyorsa kapatma
                    if (isClickFromDaterangepicker) {
                        e.preventDefault(); // Bootstrap'ın kapanma olayını engelle
                    }
                    isClickFromDaterangepicker = false; // Bayrağı sıfırla
                });
                // Datepicker input alanına odaklanıldığında veya tıklandığında
                $dropdownContainer.find('.tarih-araligi').on('focus mousedown', function (e) {
                    isClickFromDaterangepicker = true; // Bayrağı ayarla
                });
                // Tarih aralığı kısayol butonlarına tıklandığında
                $dropdownContainer.find('.tarihAraligi button').on('click', function (e) {
                    e.stopPropagation(); // Buton tıklamasının dışarıya yayılmasını engelle
                    isClickFromDaterangepicker = true; // Bayrağı ayarla
                });
                // daterangepicker açıldığında, içindeki herhangi bir elemente tıklanırsa bu bayrağı ayarla
                $(document).on('mousedown', function (e) {
                    if ($(e.target).closest('.daterangepicker').length) {
                        isClickFromDaterangepicker = true;
                    } else {
                    }
                });
                // daterangepicker kapatıldığında (apply/cancel veya dışarı tıklama), bayrağı sıfırla
                // Bu, daterangepicker kapandıktan sonra normal dropdown kapanma davranışına izin verir.
                $dropdownContainer.find('.tarih-araligi').on('apply.daterangepicker cancel.daterangepicker hide.daterangepicker', function () {
                    isClickFromDaterangepicker = false;
                });
            }
            // Her iki dropdown için de fonksiyonu çağır
            preventDropdownClose('resultsFilterDropdownContainer');
            preventDropdownClose('personnelFilterDropdownContainer');

            // Anket sonuçları kısayol butonları
            $('#lastMonthResults').click(function () {
                updateResultsRange(moment().subtract(1, 'month'), today);
            });
            $('#last15DaysResults').click(function () {
                updateResultsRange(moment().subtract(15, 'days'), today);
            });
            $('#lastWeekResults').click(function () {
                updateResultsRange(moment().subtract(7, 'days'), today);
            });
            $('#yesterdayResults').click(function () {
                updateResultsRange(moment().subtract(1, 'days'), moment().subtract(1, 'days'));
            });
            $('#todayResults').click(function () {
                updateResultsRange(today, today);
            });

            // Personel istatistikleri kısayol butonları
            $('#personnelFilterDropdownContainer .btnLastMonth').click(function () { updateRange(moment().subtract(1, 'month'), today); });
            $('#personnelFilterDropdownContainer .btnLast15Days').click(function () { updateRange(moment().subtract(15, 'days'), today); });
            $('#personnelFilterDropdownContainer .btnLastWeek').click(function () { updateRange(moment().subtract(7, 'days'), today); });
            $('#personnelFilterDropdownContainer .btnYesterday').click(function () { updateRange(moment().subtract(1, 'days'), moment().subtract(1, 'days')); });
            $('#personnelFilterDropdownContainer .btnToday').click(function () { updateRange(today, today); });

            function updateResultsRange(start, end) {
                $('#daterangeResults').data('daterangepicker').setStartDate(start);
                $('#daterangeResults').data('daterangepicker').setEndDate(end);
                loadSurveyResults();
            }

            function updateRange(start, end) {
                $('#daterange').data('daterangepicker').setStartDate(start);
                $('#daterange').data('daterangepicker').setEndDate(end);
                loadSurveyStatistics();
            }

            // Filtre değişiklikleri
            $('#bayiSelect, #deviceTypeResults').change(function () {
                loadSurveyResults();
            });

            $('#deviceType').change(function () {
                loadSurveyStatistics();
            });

            // Anket sonuçlarını yükle
            function loadSurveyResults() {
                var fromDate = $('#daterangeResults').data('daterangepicker').startDate.format('YYYY-MM-DD');
                var toDate = $('#daterangeResults').data('daterangepicker').endDate.format('YYYY-MM-DD');
                var deviceTypeId = $('#deviceTypeResults').val();
                var bayiId = $('#bayiSelect').val();

                $.ajax({
                    url: "{{ route('survey.results.data', $tenant_id) }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        from_date: fromDate,
                        to_date: toDate,
                        device_type_id: deviceTypeId,
                        bayi_id: bayiId
                    },
                    success: function (response) {
                        // Bayiler dropdown'ını doldur
                        if ($('#bayiSelect option').length <= 1) {
                            response.bayiler.forEach(function (bayi) {
                                $('#bayiSelect').append('<option value="' + bayi.user_id + '">' + bayi.name + '</option>');
                            });
                        }

                        // Cihaz türleri dropdown'ını doldur
                        if ($('#deviceTypeResults option').length <= 1) {
                            response.deviceTypes.forEach(function (deviceType) {
                                $('#deviceTypeResults').append('<option value="' + deviceType.id + '">' + deviceType.cihaz + '</option>');
                            });
                        }

                        // Özet bilgileri güncelle
                        updateSummaryCards(response);

                        // Tabloyu güncelle
                        updateResultsTable(response.questionStats);

                        // Grafiği güncelle
                        updateChart(response.questionStats, response.questionPercentages);
                    },
                    error: function () {
                        console.error('Anket sonuçları yüklenirken hata oluştu.');
                    }
                });
            }

            // Özet kartları güncelle
            function updateSummaryCards(response) {
                $('#totalSurveysCount').text(response.totalSurveys);

                // Ortalama memnuniyet hesapla (tüm soruların evet cevapları)
                var totalAnswers = 0;
                var totalPositive = 0;
                var totalNeutral = 0;
                var totalNegative = 0;

                Object.values(response.questionStats).forEach(function (stat) {
                    totalAnswers += stat.evet + stat.hayir + stat.belli_degil;
                    totalPositive += stat.evet;
                    totalNeutral += stat.belli_degil;
                    totalNegative += stat.hayir;
                });

                var positiveRate = totalAnswers > 0 ? Math.round((totalPositive / totalAnswers) * 100) : 0;
                var neutralRate = totalAnswers > 0 ? Math.round((totalNeutral / totalAnswers) * 100) : 0;
                var negativeRate = totalAnswers > 0 ? Math.round((totalNegative / totalAnswers) * 100) : 0;

                $('#positiveRate').text(positiveRate + '%');
                $('#neutralRate').text(neutralRate + '%');
                $('#negativeRate').text(negativeRate + '%');
            }

            // Sonuçlar tablosunu güncelle
            function updateResultsTable(questionStats) {
                $('#soru1-evet').text(questionStats.soru1.evet);
                $('#soru1-hayir').text(questionStats.soru1.hayir);
                $('#soru1-bd').text(questionStats.soru1.belli_degil);

                $('#soru2-evet').text(questionStats.soru2.evet);
                $('#soru2-hayir').text(questionStats.soru2.hayir);
                $('#soru2-bd').text(questionStats.soru2.belli_degil);

                $('#soru3-evet').text(questionStats.soru3.evet);
                $('#soru3-hayir').text(questionStats.soru3.hayir);
                $('#soru3-bd').text(questionStats.soru3.belli_degil);

                $('#soru5-evet').text(questionStats.soru5.evet);
                $('#soru5-hayir').text(questionStats.soru5.hayir);
                $('#soru5-bd').text(questionStats.soru5.belli_degil);
            }

            // Grafiği güncelle
            function updateChart(questionStats, questionPercentages) {
                const ctx = document.getElementById('surveyChart').getContext('2d');

                if (surveyChart) {
                    surveyChart.destroy();
                }

                const questions = ['Zamanında Geldi', 'Davranış Memnuniyeti', 'Cihaz İlgisi', 'Genel Memnuniyet'];
                const evetData = [questionStats.soru1.evet, questionStats.soru2.evet, questionStats.soru3.evet, questionStats.soru5.evet];
                const hayirData = [questionStats.soru1.hayir, questionStats.soru2.hayir, questionStats.soru3.hayir, questionStats.soru5.hayir];
                const belliDegilData = [questionStats.soru1.belli_degil, questionStats.soru2.belli_degil, questionStats.soru3.belli_degil, questionStats.soru5.belli_degil];

                surveyChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: questions,
                        datasets: [{
                            label: 'Evet',
                            data: evetData,
                            backgroundColor: '#667eea',
                            borderColor: '#667eea',
                            borderWidth: 1
                        }, {
                            label: 'Hayır',
                            data: hayirData,
                            backgroundColor: '#764ba2',
                            borderColor: '#764ba2',
                            borderWidth: 1
                        }, {
                            label: 'Belli Değil',
                            data: belliDegilData,
                            backgroundColor: '#6c757d',
                            borderColor: '#6c757d',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                beginAtZero: true
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function (value) {
                                        if (Number.isInteger(value)) {
                                            return value;
                                        }
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += context.raw;

                                        // Yüzdeyi de tooltipe ekle
                                        const dataIndex = context.dataIndex;
                                        const datasetIndex = context.datasetIndex;
                                        const questionKeyMap = {
                                            0: 'soru1',
                                            1: 'soru2',
                                            2: 'soru3',
                                            3: 'soru5'
                                        };
                                        const questionKey = questionKeyMap[dataIndex];

                                        if (questionKey && questionPercentages[questionKey]) {
                                            let percentage;
                                            if (datasetIndex === 0) { // Evet
                                                percentage = questionPercentages[questionKey].evet_percentage;
                                            } else if (datasetIndex === 1) { // Hayır
                                                percentage = questionPercentages[questionKey].hayir_percentage;
                                            } else { // Belli Değil
                                                percentage = questionPercentages[questionKey].belli_degil_percentage;
                                            }
                                            label += ` (%${percentage})`;
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    } // options nesnesinin kapanışı
                }); // new Chart() çağrısının kapanışı
            } // updateChart fonksiyonunun kapanışı

            // Personel bazında anket istatistiklerini yükle
            function loadSurveyStatistics() {
                var fromDate = $('#daterange').data('daterangepicker').startDate.format('YYYY-MM-DD');
                var toDate = $('#daterange').data('daterangepicker').endDate.format('YYYY-MM-DD');
                var deviceTypeId = $('#deviceType').val();

                $.ajax({
                    url: "{{ route('survey.statistics.data', $tenant_id) }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        from_date: fromDate,
                        to_date: toDate,
                        device_type_id: deviceTypeId
                    },
                    success: function (response) {
                        // Cihaz türleri dropdown'ını doldur
                        if ($('#deviceType option').length <= 1) {
                            response.deviceTypes.forEach(function (deviceType) {
                                $('#deviceType').append('<option value="' + deviceType.id + '">' + deviceType.cihaz + '</option>');
                            });
                        }
                        updatePersonnelTable(response.personnelStats);
                        $('#totalCompletedServices').text(response.totalCompletedServices);
                        $('#totalSurveyedServices').text(response.totalSurveyedServices);
                    },
                    error: function () {
                        console.error('Personel istatistikleri yüklenirken hata oluştu.');
                    }
                });
            }

            function updatePersonnelTable(personnelStats) {
                let tableBody = $('#surveyStatsTableBody');
                tableBody.empty();

                // Obje içeriğini diziye çeviriyoruz
                const statsArray = Object.values(personnelStats);

                if (statsArray.length === 0) {
                    tableBody.append('<tr><td colspan="4" class="text-center">Gösterilecek veri bulunamadı.</td></tr>');
                    return;
                }

                statsArray.forEach(function (personnel) {
                    // Detay butonu için parametreleri hazırla
                    var from_date = $('#daterange').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var to_date = $('#daterange').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    let deviceType = document.getElementById("deviceType").value;

                    let detailUrl = "{{ url($tenant_id . '/servisler') }}" +
                        "?personel_id=" + personnel.personel_id +
                        "&deviceType=" + deviceType +
                        "&anket_yapilan=1" +
                        "&personel_istatistik_tarih1=" + from_date +
                        "&personel_istatistik_tarih2=" + to_date;


                    let row = `<tr>
                    <td>${personnel.adsoyad}</td>
                    <td>${personnel.tamamlanan_servis_sayisi}</td>
                    <td>${personnel.anket_yapilan_servis_sayisi}</td>
                    <td>
                        <a href="${detailUrl}" target="_blank" class="btn btn-action btn-sm">
                            <i class="fas fa-eye me-1 d-md-flex d-none"></i>Servisleri Gör
                        </a>
                    </td>
                </tr>`;
                    tableBody.append(row);
                });
            }
            // Sayfa yüklendiğinde hem anket sonuçlarını hem de personel istatistiklerini yükle
            loadSurveyResults();
            loadSurveyStatistics();
        });
</script>
<script>
    $(document).ready(function () {
        // --- 1. Anket Sonuçları Filtre Butonu ---
        var resultsDropdownContainer = $('#resultsFilterDropdownContainer');
        var resultsFilterButton = resultsDropdownContainer.find('.filtrele');

        // Menü açılırken
        resultsDropdownContainer.on('show.bs.dropdown', function () {
            resultsFilterButton.html('Kapat <i class="mdi mdi-chevron-down"></i>');
        });

        // Menü kapanırken
        resultsDropdownContainer.on('hide.bs.dropdown', function () {
            resultsFilterButton.html('Filtrele <i class="mdi mdi-chevron-down"></i>');
        });


        // --- 2. Personel Bazında İstatistikler Filtre Butonu ---
        var personnelDropdownContainer = $('#personnelFilterDropdownContainer');
        var personnelFilterButton = personnelDropdownContainer.find('.filtrele');

        // Menü açılırken
        personnelDropdownContainer.on('show.bs.dropdown', function () {
            personnelFilterButton.html('Kapat <i class="mdi mdi-chevron-down"></i>');
        });

        // Menü kapanırken
        personnelDropdownContainer.on('hide.bs.dropdown', function () {
            personnelFilterButton.html('Filtrele <i class="mdi mdi-chevron-down"></i>');
        });
    });
</script>

@endsection