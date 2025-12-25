@extends('frontend.secure.user_master')
@section('user')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <div class="page-content servis-istatistik" id="personelDepoStats">
        <div class="container-fluid">
            @include('frontend.secure.statistics.statistics_menu', ['tenant_id' => $tenant_id])
            <div class="row pageDetail">
                <div class="col-12">
                    <div class="table-modern ">
                        <div class="card-header">
                            <span style="font-size: 15px;">Personel Depo İstatistikleri</span>
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
                            <table id="datatablePersonelDepoStats" class="table table-hover mb-0">
                                <thead class="title">
                                    <tr>
                                        <th>
                                            <i class="fas fa-user me-2"></i>
                                            <span class="d-sm-none">Personel</span>
                                            <span class="d-none d-sm-inline">Personel</span>
                                        </th>
                                        <th>
                                            <i class="fas fa-warehouse me-2"></i>
                                            <span class="d-sm-none">Toplam</span>
                                            <span class="d-none d-sm-inline">Toplam Stok Adedi</span>
                                        </th>
                                        <th style="width: 130px;">İşlemler</th>
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
        // URL parametresi almak için yardımcı fonksiyon
        function getUrlParameter(name) {
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
            var results = regex.exec(location.search);
            return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
        }

        $(document).ready(function () {
            // Tarih değişkenlerini tanımla
            let today = moment();
            let start_date = moment().subtract(1, 'months');
            let end_date = today;
            let preventDropdownHide = false;
            
            $('.searchWrap .btn-group').on('hide.bs.dropdown', function (e) {
                if (preventDropdownHide) {
                    e.preventDefault();
                }
                preventDropdownHide = false;
            });

            // Daterangepicker'a tıklandığında dropdown'u açık tut
            $(document).on('mousedown', function (e) {
                if ($(e.target).closest('.daterangepicker').length) {
                    preventDropdownHide = true;
                }
            });

            // Tarih input'una tıklandığında
            $('#daterange').on('focus mousedown', function () {
                preventDropdownHide = true;
            });

            // Hızlı tarih butonlarına tıklandığında
            $('.tarihAraligi button').on('mousedown', function () {
                preventDropdownHide = true;
            });

            // Daterangepicker kapatıldığında flag'i sıfırla
            $('#daterange').on('apply.daterangepicker cancel.daterangepicker hide.daterangepicker', function () {
                preventDropdownHide = false;
            });

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

            // DateRangePicker'ı başlat
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

            var table = $('#datatablePersonelDepoStats').DataTable({
                processing: true,
                serverSide: true,
                order: [[1, 'desc']],
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    },
                    sEmptyTable: "Herhangi bir personel depo hareketi bulunamadı.",
                    sInfo: "Personel Sayısı: _TOTAL_",
                    sInfoEmpty: "Kayıt yok",
                    sSearch: "Personel Ara:",
                    sLengthMenu: "_MENU_",
                    sZeroRecords: "Eşleşen kayıt bulunamadı"
                },
                ajax: {
                    url: "{{ route('stock.statistics.data', $tenant_id) }}", 
                    type: "POST",
                    data: function (d) {
                        d._token = "{{ csrf_token() }}";
                        // Tarih parametrelerini ekle
                        d.from_date = $('#daterange').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        d.to_date = $('#daterange').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    }
                },
                columns: [
                    {
                        data: 'personel_name',
                        name: 'name',
                        render: function (data, type, row) {
                            let tempDiv = document.createElement("div");
                            tempDiv.innerHTML = data;
                            let text = tempDiv.textContent || tempDiv.innerText || "";

                            return `
                            <div class="d-flex align-items-center">
                                <div>
                                    <div class="fw-bold">${text}</div>
                                </div>
                            </div>`;
                        }

                    },
                    {
                        data: 'toplam_adet',
                        render: function (data) {
                            return `<div class="badge bg-primary">${data}</div>`;
                        }
                    },
                    {
                        data: null,
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            var url = "{{ url($tenant_id . '/stoklar') }}?personel=" + row.user_id;
                            return '<a href="' + url + '" target="_blank"  class="btn btn-action btn-sm" ><i class="fas d-md-flex d-none fa-eye me-1"></i>Parçaları Göster</a>';
                        }
                    }
                ],
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                },
                dom: 'frt<"bottom"i<"float-end"lp>><"clear">',
                lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "Tümü"]],
                "initComplete": function (settings, json) {
                    var searchContainer = $('#datatablePersonelDepoStats_filter');
                    var searchInput = searchContainer.find('input');
                    var filterWrapper = $('.searchWrap');
                    var flexContainer = $('<div class="d-flex justify-content-end"></div>');

                    searchContainer.find('label').contents().filter(function () {
                        return this.nodeType == 3;
                    }).remove();

                    searchContainer.addClass('flex-grow-1');
                    searchInput.addClass('w-100');
                    searchInput.attr('placeholder', 'Personel Ara...');

                    flexContainer.append(searchContainer);
                    flexContainer.append(filterWrapper);

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