@extends('frontend.secure.user_master')
@section('user')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <div class="page-content servis-istatistik" id="stateStats">
        <div class="container-fluid">
            @include('frontend.secure.statistics.statistics_menu', ['tenant_id' => $tenant_id])
            <div class="row pageDetail">
                <div class="col-12">
                    <div class="table-modern">
                        <div class="card-header">
                             <span style="font-size:15px">Servis Durum İstatistikleri</span>

                        </div>
                        <div class="card-body">
                            <div class="searchWrap float-end">
                                <div class="btn-group">
                                    <button class="btn btn-dark btn-sm dropdown-toggle filtrele" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        Filtrele <i class="mdi mdi-chevron-down"></i>
                                    </button>
                                    <div class="dropdown-menu servisDrop">
                                        <div class="item">
                                            <div class="row">
                                                <label class="col-sm-4 custom-p-m-k custom-p-r-m-k col-5">Tarih Aralığı:</label>
                                                <div class="col-sm-8 col-7 custom-p-m-k">
                                                    <input id="daterange" class="tarih-araligi" />
                                                    <div class="tarihAraligi mt-2 mb-2">
                                                        <button id="lastYear" class="btn btn-sm btn-secondary">Son 1
                                                            Yıl</button>
                                                        <button id="lastMonth" class="btn btn-sm btn-secondary">Son 1
                                                            Ay</button>
                                                        <button id="lastWeek" class="btn btn-sm btn-secondary">Son 7
                                                            Gün</button>
                                                        <button id="yesterday" class="btn btn-sm btn-secondary">Dün</button>
                                                        <button id="today" class="btn btn-sm btn-secondary">Bugün</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <table id="datatableStateStats" class="table table-hover mb-0">
                                <thead class="title">
                                    <tr>
                                        <th>
                                            <i class="fas fa-flag me-2"></i>
                                            <span class="d-sm-none">Durum</span>
                                            <span class="d-none d-sm-inline">Durum</span>
                                        </th>
                                        <th>
                                            <i class="fas fa-list-ol me-2"></i>
                                            <span class="d-sm-none">Toplam</span>
                                            <span class="d-none d-sm-inline">Toplam Servis Sayısı</span>
                                        </th>
                                        <th>İşlem</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            let today = moment();
            let start_date = moment().subtract(1, 'months');
            let end_date = today;
            // Kısayol butonları
            $('#lastYear').click(function () {
                updateRange(moment().subtract(1, 'year'), today);
            });
            $('#lastMonth').click(function () {
                updateRange(moment().subtract(1, 'month'), today);
            });
            $('#lastWeek').click(function () {
                updateRange(moment().subtract(7, 'days'), today);
            });
            $('#yesterday').click(function () {
                updateRange(moment().subtract(1, 'days'), moment().subtract(1, 'days'));
            });
            $('#today').click(function () {
                updateRange(today, today);
            });

            function updateRange(start, end) {
                $('#daterange').data('daterangepicker').setStartDate(start);
                $('#daterange').data('daterangepicker').setEndDate(end);
                table.draw();
            }

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
                table.draw();
            });

            var table = $('#datatableStateStats').DataTable({
                processing: true,
                serverSide: true,
                order: [[1, 'desc']],
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    },
                    sLengthMenu: "_MENU_",
                    sEmptyTable: "Veri yok",
                    sInfo: "Durum Sayısı: _TOTAL_",
                    sInfoEmpty: "Kayıt yok",
                    sSearch: "",
                    sZeroRecords: "Eşleşen kayıt bulunamadı"
                },
                ajax: {
                    url: "{{ route('state.statistics', $tenant_id) }}",
                    data: function (data) {
                        data.from_date = $('#daterange').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        data.to_date = $('#daterange').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    }
                },
                columns: [
                    {
                        data: 'durum',
                        name: 'durum',
                        render: function (data, type, row) {
                            let firstChar = data.charAt(0).toUpperCase();

                            // İstersen avatar içeriği ikon olabilir, şimdilik harf kullandım
                            return `<div style="display:flex; align-items:center;">
                                    <span>${data}</span>
                                </div>`;
                        }
                    },
                    {
                        data: 'toplam',
                        name: 'toplam',
                        render: function (data) {
                            return `<span class="badge bg-primary">${data}</span>`;
                        }
                    },
                    {
                        data: 'durum_id',
                        orderable: false,
                        render: function (data, type, row) {
                            var from_date = $('#daterange').data('daterangepicker').startDate.format('YYYY-MM-DD');
                            var to_date = $('#daterange').data('daterangepicker').endDate.format('YYYY-MM-DD');
                            var url = "{{ url($tenant_id . '/servisler') }}" + "?state_id=" + data + "&state_istatistik_tarih1=" + from_date + "&state_istatistik_tarih2=" + to_date;
                            return `<a href="${url}" target="_blank" class="btn btn-action btn-sm"><i class="fas d-md-flex d-none fa-eye me-1"></i>Servisleri Gör</a>`;
                        }
                    }
                ],
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                },
                dom: 'frt<"bottom"i<"float-end"lp>><"clear">', // "f" (filter) etrafındaki <"top"> sarmalayıcısı kaldırıldı.
                lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "Tümü"]],
                "initComplete": function (settings, json) {
                    var searchContainer = $('#datatableStateStats_filter');
                    var searchInput = searchContainer.find('input');
                    var filterWrapper = $('.searchWrap');
                    // Genişlik ve alt boşluk sınıfları kaldırıldı, çünkü CSS ile yönetiliyor.
                    var flexContainer = $('<div class="d-flex justify-content-end"></div>');

                    searchContainer.find('label').contents().filter(function () {
                        return this.nodeType == 3;
                    }).remove();

                    searchContainer.addClass('flex-grow-1');
                    searchInput.addClass('w-100');
                    searchInput.attr('placeholder', 'Durum Ara...');

                    flexContainer.append(searchContainer);
                    flexContainer.append(filterWrapper);

                    // Konteyner artık doğrudan başlığın (.card-header) içine ekleniyor.
                    $('.card-header').append(flexContainer);

                    $('.searchWrap').css({ visibility: 'visible', opacity: 1 });
                }
                            });
                        });
    </script>

    <script>
    $(document).ready(function () {
        var dropdownContainer = $('.searchWrap .btn-group');
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