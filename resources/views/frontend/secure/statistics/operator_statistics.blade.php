@extends('frontend.secure.user_master')
@section('user')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <div class="page-content servis-istatistik" id="operatorStats">
        <div class="container-fluid">
            @include('frontend.secure.statistics.statistics_menu', ['tenant_id' => $tenant_id])

            <div class="row pageDetail">
                <div class="col-12">
                    <div class="table-modern">
                        <div class="card-body card-statics-op-body">
                            <div class="card-header">
                                  <span style="
                                        font-size: 15px;
                                        font-weight: 900;
                                    ">Operatör İstatistikleri</span>

                            </div>
                            <div class="searchWrap float-end">
                                <div class="btn-group mb-2">
                                    <button class="btn btn-dark btn-sm dropdown-toggle filtrele" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        Filtrele <i class="mdi mdi-chevron-down"></i>
                                    </button>
                                    <div class="dropdown-menu servisDrop">
                                        <div class="item">
                                            <div class="row">
                                                <label class="col-sm-4 col-5 custom-p-m-k custom-p-r-m-k">Tarih Aralığı:</label>
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
                            <table id="datatableOperatorStats" class="table table-hover mb-0">

                                <thead class="title">
                                    <tr>
                                        <th>
                                            <i class="fas fa-user me-2 d-none d-lg-inline"></i>
                                            <span class="d-none d-lg-inline">Operatör Adı</span>
                                            <span class="d-lg-none">Operatör</span>
                                        </th>
                                        <th>
                                            <i class="fas fa-clipboard-list me-2 d-none d-lg-inline"></i>
                                            <span class="d-none d-lg-inline">Toplam Servis Kaydı</span>
                                            <span class="d-lg-none">Toplam</span>
                                        </th>
                                        <th style="width: 130px;">
                                            <span class="d-none d-lg-inline">İşlemler</span>
                                            <span class="d-lg-none">İşlem</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Tarih aralığı seçenekleri
            var lastYear = moment().subtract(1, 'year');
            var lastMonth = moment().subtract(1, 'month');
            var lastWeek = moment().subtract(7, 'days');
            var yesterday = moment().subtract(1, 'days');
            var today = moment();

            // Butonlara tıklanınca tarih aralığını değiştir ve tabloyu yenile
            $('#lastYear').on('click', function () {
                $('#daterange').data('daterangepicker').setStartDate(lastYear);
                $('#daterange').data('daterangepicker').setEndDate(today);
                table.draw();
            });

            $('#lastMonth').on('click', function () {
                $('#daterange').data('daterangepicker').setStartDate(lastMonth);
                $('#daterange').data('daterangepicker').setEndDate(today);
                table.draw();
            });

            $('#lastWeek').on('click', function () {
                $('#daterange').data('daterangepicker').setStartDate(lastWeek);
                $('#daterange').data('daterangepicker').setEndDate(today);
                table.draw();
            });

            $('#yesterday').on('click', function () {
                $('#daterange').data('daterangepicker').setStartDate(yesterday);
                $('#daterange').data('daterangepicker').setEndDate(yesterday);
                table.draw();
            });

            $('#today').on('click', function () {
                $('#daterange').data('daterangepicker').setStartDate(today);
                $('#daterange').data('daterangepicker').setEndDate(today);
                table.draw();
            });

            // Tarih aralığı başlangıç değerleri
            var start_date = moment().subtract(1, 'months').format('DD-MM-YYYY');
            var end_date = moment().format('DD-MM-YYYY');

            // Date Range Picker başlat
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
                $('#daterange').html(start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY'));
                table.draw();
            });


            var table = $('#datatableOperatorStats').DataTable({
                processing: true,
                serverSide: true,
                order: [[1, 'desc']],

                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    },
                    sEmptyTable: "Tabloda herhangi bir veri mevcut değil",
                    sInfo: "Operatör Sayısı: _TOTAL_",
                    sInfoEmpty: "Kayıt yok",
                    sSearch: "",
                    sZeroRecords: "Eşleşen kayıt bulunamadı",
                    sLengthMenu: "_MENU_",
                    oPaginate: {
                        sFirst: "İlk",
                        sLast: "Son",
                        sNext: '<i class="fas fa-angle-double-right"></i>',
                        sPrevious: '<i class="fas fa-angle-double-left"></i>'
                    }
                },
                ajax: {
                    url: "{{ route('operator.statistics', $tenant_id) }}",
                    data: function (data) {
                        // Tarih aralığını ajax'a ekle
                        data.from_date = $('#daterange').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        data.to_date = $('#daterange').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    }
                },
                columns: [
                    {
                        data: 'name',
                        render: function (data, type, row) {
                            return `
                        <div class="d-flex align-items-center">

                            <div>
                                <div class="fw-bold">${data}</div>
                            </div>
                        </div>`;
                        }
                    },
                    {
                        data: 'toplam',
                        render: function (data) {
                            return `<div class="badge bg-primary">${data}</div>`;
                        }
                    },
                    {
                        data: 'id',
                        render: function (data, type, row) {
                            var from_date = $('#daterange').data('daterangepicker').startDate.format('YYYY-MM-DD');
                            var to_date = $('#daterange').data('daterangepicker').endDate.format('YYYY-MM-DD');
                            var url = "{{ url($tenant_id . '/servisler') }}" + "?operator_id=" + data + "&opeator_istatistik_tarih1=" + from_date + "&opeator_istatistik_tarih2=" + to_date;

                            return `<a href="${url}" target="_blank" class="btn btn-action btn-sm">
                                <i class="fas fa-eye me-1"></i>Servisleri Gör
                            </a>`;
                        }
                    }
                ],
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                },
                dom: 'frt<"bottom"i<"float-end"lp>><"clear">', // "f" (filter) etrafındaki <"top"> sarmalayıcısı kaldırıldı.
                lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "Tümü"]],
                "initComplete": function (settings, json) {
                    var searchContainer = $('#datatableOperatorStats_filter');
                    var searchInput = searchContainer.find('input');
                    var filterWrapper = $('.searchWrap');
                    // Genişlik ve alt boşluk sınıfları kaldırıldı, çünkü CSS ile yönetiliyor.
                    var flexContainer = $('<div class="d-flex justify-content-end"></div>'); 

                    searchContainer.find('label').contents().filter(function () {
                        return this.nodeType == 3;
                    }).remove();

                    searchContainer.addClass('flex-grow-1');
                    searchInput.addClass('w-100');
                    searchInput.attr('placeholder', 'Operatör Ara...');

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