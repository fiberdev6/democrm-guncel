@extends('frontend.secure.user_master')
@section('user')
<div class="page-content" id="paymentUser">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card" style="margin-bottom: 0!important;">
          <div class="card-header card-header-custom2 sayfaBaslik">Ödeme Geçmişi</div>

          <div class="card-body">
            <!-- Filtreleme Formu -->
            <div class="row mb-3">
              <div class="col-12">
                <div class="card shadow-sm" style="margin-bottom: 0!important;">           
                  <div class="card-body">
                    <div class="row align-items-end">
                      <!-- Başlangıç -->
                      <div class="col-lg-2 col-md-3 col-sm-6">
                        <div class="form-group">
                          <label for="date_from" class="form-label fw-bold" style="font-size: 12px;">Başlangıç</label>
                          <input type="date" class="form-control datepicker form-control-sm" id="date_from" name="date_from" value="{{ $dateFrom }}">
                        </div>
                      </div>

                      <!-- Bitiş -->
                      <div class="col-lg-2 col-md-3 col-sm-6">
                        <div class="form-group ">
                          <label for="date_to" class="form-label fw-bold" style="font-size: 12px;">Bitiş</label>
                          <input type="date" class="form-control datepicker form-control-sm" id="date_to" name="date_to" value="{{ $dateTo }}">
                        </div>
                      </div>

                      <!-- Tür -->
                      <div class="col-lg-2 col-md-3 col-sm-6">
                        <div class="form-group mb-1">
                          <label for="type" class="form-label fw-bold" style="font-size: 12px;">Tür</label>
                          <select class="form-control form-control-sm select-with-arrow" id="type" name="type">
                            <option value="all">Tümü</option>
                            <option value="subscription">Abonelik</option>
                            <option value="storage">Depolama</option>
                            <option value="integration">Entegrasyon</option>
                          </select>
                        </div>
                      </div>

                      <!-- İşlem Butonları -->
                      <div class="col-auto">
                        <div class="form-group mb-1">
                          <label class="form-label fw-bold text-transparent" style="font-size: 12px;">İşlemler</label>
                          <div class="d-flex gap-1">
                            <button type="button" class="btn btn-outline-secondary btn-sm action-btn" id="clear-filter" title="Temizle">
                              <i class="fas fa-eraser"></i>
                            </button>
                            <a href="#" class="btn btn-outline-secondary btn-sm action-btn" id="excel-export" title="Excel İndir">
                              <i class="fas fa-file-excel"></i>
                            </a>
                          </div>
                        </div>
                      </div>

                      <!-- Hızlı Tarih Filtreleri -->
                      <div class="col-12 col-lg-auto">
                        <div class="form-group mb-1">
                          <label class="form-label fw-bold text-transparent" style="font-size: 12px;">Hızlı</label>
                          <div class="btn-group btn-group-sm d-flex flex-nowrap" role="group" style="overflow-x: auto; flex-wrap: nowrap;">
                            <button type="button" class="btn btn-outline-light text-dark quick-filter" data-days="7">7 Gün</button>
                            <button type="button" class="btn btn-outline-light text-dark quick-filter" data-days="30">30 Gün</button>
                            <button type="button" class="btn btn-outline-light text-dark quick-filter" id="this-month">Bu Ay</button>
                            <button type="button" class="btn btn-outline-light text-dark quick-filter" data-days="90">3 Ay</button>
                            <button type="button" class="btn btn-outline-light text-dark quick-filter" data-days="365">1 Yıl</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Ödeme Tablosu -->
            <table id="datatablePayments" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
              <thead class="title">
                <tr>
                  <th style="width: 50px;">ID</th>
                  <th style="width: 100px;">Tür</th>
                  <th>Açıklama</th>
                  <th style="width: 120px;">Tutar</th>
                  <th style="width: 120px;">Durum</th>
                  <th style="width: 120px;">Tarih</th>
                  <th style="width: 120px;">Fatura</th>
                  <th style="width: 80px;">İşlem</th>
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
$(document).ready(function() {
    const today = new Date().toISOString().split('T')[0];
    const lastMonth = new Date();
    lastMonth.setMonth(lastMonth.getMonth() - 1);
    
    // Varsayılan tarihler
    $('#date_from').val('{{ $dateFrom }}');
    $('#date_to').val('{{ $dateTo }}');
    $('#date_from, #date_to').attr('max', today);
    
    $('#date_from').on('change', function() {
        $('#date_to').attr('min', $(this).val());
    });
    
    $('#date_to').on('change', function() {
        $('#date_from').attr('max', $(this).val());
    });

    // DataTable
    var table = $('#datatablePayments').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        language: {
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>"
            }
        },
        ajax: {
            url: "{{ route('payment-history.index', $tenant->id) }}",
            data: function(data) {
                data.search = $('input[type="search"]').val();
                data.date_from = $('#date_from').val();
                data.date_to = $('#date_to').val();
                data.type = $('#type').val();
            }
        },
        columns: [
    { data: 'id', name: 'id', title: 'ID' },
    { data: 'type_label', name: 'type_label', title: 'Tür' },
    { data: 'description', name: 'description', title: 'Açıklama' },
    { data: 'amount', name: 'amount', title: 'Tutar' },
    { data: 'status_label', name: 'status_label', title: 'Durum' },
    { data: 'created_at', name: 'created_at', title: 'Tarih' },
    { data: 'invoice_status', name: 'invoice_status', title: 'Fatura' },
    { data: 'action', name: 'action', orderable: false, searchable: false, title: 'İşlem' }
],
        drawCallback: function() {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
            var api = this.api();
    api.rows({ page: 'current' }).nodes().each(function(row) {
        $(row).find('td').each(function(i) {
            var title = api.column(i).header().textContent;
            $(this).attr('data-label', title);
        });
    });
        },
        order: [[5, 'desc']],
        "columnDefs": [{
          "targets": 0,
          "className": "gizli"
        }],
        oLanguage: {
            sDecimal: ",",
            sEmptyTable: "Tabloda herhangi bir veri mevcut değil",
            sInfo: "Ödeme Sayısı: _TOTAL_",
            sInfoEmpty: "Kayıt yok",
            sInfoFiltered: "",
            sInfoPostFix: "",
            sInfoThousands: ".",
            sLengthMenu: "_MENU_",
            sLoadingRecords: "Yükleniyor...",
            sProcessing: "İşleniyor...",
            sSearch: "Ara:",
            sZeroRecords: "Eşleşen kayıt bulunamadı",
            oPaginate: {
                sFirst: "İlk",
                sLast: "Son",
                sNext: '<i class="fas fa-angle-double-right"></i>',
                sPrevious: '<i class="fas fa-angle-double-left"></i>'
            },
            oAria: {
                sSortAscending: ": artan sütun sıralamasını aktifleştir",
                sSortDescending: ": azalan sütun sıralamasını aktifleştir"
            },
            select: {
                rows: {
                    "_": "%d kayıt seçildi",
                    "0": "",
                    "1": "1 kayıt seçildi"
                }
            }
        },
        dom: 'rt<"d-flex justify-content-between align-items-center flex-wrap mt-2"i<"d-flex align-items-center"lp>><"clear">',
        lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "Tümü"]]
    });

    // Filtre değişikliklerinde tabloyu yenile
    $('#type, #date_from, #date_to').change(function(){
        table.draw();
    });

    // Hızlı tarih filtreleri
    $('.quick-filter').on('click', function(e) {
        e.preventDefault();
        const days = parseInt($(this).data('days'));
        if (days) {
            const today = new Date();
            const startDate = new Date();
            startDate.setDate(today.getDate() - days);
            
            $('#date_from').val(startDate.toISOString().split('T')[0]);
            $('#date_to').val(today.toISOString().split('T')[0]);
        }
        table.draw();
    });

    $('#this-month').on('click', function(e) {
        e.preventDefault();
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        
        $('#date_from').val(firstDay.toISOString().split('T')[0]);
        $('#date_to').val(today.toISOString().split('T')[0]);
        
        table.draw();
    });

    $('#clear-filter').on('click', function(e) {
        e.preventDefault();
        $('#type').val('all');
        const today = new Date().toISOString().split('T')[0];
        const lastMonth = new Date();
        lastMonth.setMonth(lastMonth.getMonth() - 1);
        $('#date_from').val(lastMonth.toISOString().split('T')[0]);
        $('#date_to').val(today);
        table.draw();
    });

    $('#excel-export').on('click', function(e) {
        e.preventDefault();
        var dateFrom = $('#date_from').val();
        var dateTo = $('#date_to').val();
        var type = $('#type').val();
        
        var exportUrl = '{{ route("payment-history.export", $tenant->id) }}' + 
            '?date_from=' + dateFrom + 
            '&date_to=' + dateTo +
            '&type=' + type;
        
        window.open(exportUrl, '_blank');
    });
});
</script>
@endsection