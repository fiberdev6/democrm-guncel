@extends('frontend.secure.user_master')
@section('user')
<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
         <div class="card payment_history_superadmin" style="margin-bottom: 0!important;">
          <div class="card-header" style="padding: 5px!important;">
            <h3 class="card-title">
              Tüm Müşterilerin Ödeme Geçmişi
            </h3>
          </div>

          <div class="card-body">
            <!-- Filtreleme Formu -->
            <div class="row">
              <div class="col-12">
                <div class="card shadow-sm" style="margin-bottom: 0!important;">           
                  <div class="card-body">
                    <div class="row align-items-end">
                      <!-- Firma -->
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <div class="form-group">
                                <label for="tenant_id" class="form-label fw-bold" style="font-size: 12px;">Müşteri</label>
                                <select class="form-control form-control-sm select-with-arrow" id="tenant_id" name="tenant_id">
                                    <option value="">Tüm Müşteriler</option>
                                    @foreach($tenants as $tenant)
                                    <option value="{{ $tenant->id }}">{{ $tenant->firma_adi }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                      <!-- Başlangıç -->
                      <div class="col-lg-2 col-md-3 col-sm-6">
                        <div class="form-group ">
                          <label for="date_from" class="form-label fw-bold" style="font-size: 12px;">Başlangıç</label>
                          <input type="date" class="form-control datepicker form-control-sm" id="date_from" name="date_from">
                        </div>
                      </div>

                      <!-- Bitiş -->
                      <div class="col-lg-2 col-md-3 col-sm-6">
                        <div class="form-group">
                          <label for="date_to" class="form-label fw-bold" style="font-size: 12px;">Bitiş</label>
                          <input type="date" class="form-control datepicker form-control-sm" id="date_to" name="date_to">
                        </div>
                      </div>

                      <!-- Tür -->
                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <div class="form-group ">
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
                        <div class="form-group">
                          <label class="form-label fw-bold" style="font-size: 12px;">İşlemler</label>
                          <div class="d-flex gap-1">
                            <button type="button" class="btn btn-outline-secondary btn-sm action-btn" id="clear-filter" title="Temizle">
                              <i class="fas fa-eraser me-1"></i>Temizle
                            </button>
                            <a href="#" class="btn btn-outline-secondary btn-sm action-btn" id="excel-export" title="Excel İndir">
                              <i class="fas fa-file-excel me-1"></i> Excell İndir
                            </a>
                          </div>
                        </div>
                      </div>

                      <!-- Hızlı Tarih Filtreleri -->
                      <div class="col-12 col-lg-auto">
                        <div class="form-group">
                          <label class="form-label fw-bold text-transparent d-none" style="font-size: 12px;">Hızlı</label>
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
                  <th style="width: 150px;">Müşteri</th>
                  <th style="width: 150px;">Tür</th>
                  <th>Açıklama</th>
                  <th style="width: 150px;">Tutar</th>
                  <th style="width: 150px;">Durum</th>
                  <th style="width: 150px;">Tarih</th>
                  <th style="width: 150px;">Fatura</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>

            <!-- Toplam Alanı -->
            <div class="tableToplamaAlani">
              <div class="row r2">
                <div class="sol"><strong>Abonelik Ödemeleri</strong></div>
                <div class="sag">
                  <div class="tur t1 subscription_completed"><span>Tamamlanan:</span></div>
                  <div class="tur t2 subscription_pending"><span>Bekleyen:</span></div>
                  <div class="tur t3 subscription_failed"><span>Başarısız:</span></div>
                  <div class="tur t4 subscription_total"><span>Toplam:</span></div>
                </div>
              </div>

              <div class="row r3">
                <div class="sol"><strong>Depolama Ödemeleri</strong></div>
                <div class="sag">
                  <div class="tur t1 storage_completed"><span>Tamamlanan:</span></div>
                  <div class="tur t2 storage_pending"><span>Bekleyen:</span></div>
                  <div class="tur t3 storage_failed"><span>Başarısız:</span></div>
                  <div class="tur t4 storage_total"><span>Toplam:</span></div>
                </div>
              </div>

              <div class="row r5">
                <div class="sol"><strong>Entegrasyon Ödemeleri</strong></div>
                <div class="sag">
                  <div class="tur t1 integration_completed"><span>Tamamlanan:</span></div>
                  <div class="tur t2 integration_pending"><span>Bekleyen:</span></div>
                  <div class="tur t3 integration_failed"><span>Başarısız:</span></div>
                  <div class="tur t4 integration_total"><span>Toplam:</span></div>
                </div>
              </div>

              <div class="row r4">
                <div class="sol"><strong>Genel Toplam</strong></div>
                <div class="sag">
                  <div class="tur t1 general_completed"><span>Tamamlanan:</span></div>
                  <div class="tur t2 general_pending"><span>Bekleyen:</span></div>
                  <div class="tur t3 general_failed"><span>Başarısız:</span></div>
                  <div class="tur t4 general_total"><span>Toplam:</span></div>
                </div>
              </div>
            </div>
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
    $('#date_from').val(lastMonth.toISOString().split('T')[0]);
    $('#date_to').val(today);
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
        language: {
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>"
            }
        },
        ajax: {
            url: "{{ route('super.admin.payment.history.index') }}",
            data: function(data) {
                data.search = $('input[type="search"]').val();
                data.date_from = $('#date_from').val();
                data.date_to = $('#date_to').val();
                data.tenant_id = $('#tenant_id').val();
                data.type = $('#type').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'tenant_name', name: 'tenant_name' },
            { data: 'type_label', name: 'type_label' },
            { data: 'description', name: 'description' },
            { data: 'amount', name: 'amount' },
            { data: 'status_label', name: 'status_label' },
            { data: 'created_at', name: 'created_at' },
            { data: 'invoice_status', name: 'invoice_status' }
        ],
        drawCallback: function() {
           var headers = [];
    // Not: Tablonuzun ID'si #datatablePayments olmalı. Değilse burayı güncelleyin.
    $('#datatablePayments thead th').each(function() { 
        headers.push($(this).text());
    });

    // 2. Tablonun gövdesindeki her bir satır için dön
    $('#datatablePayments tbody tr').each(function() {
        // 3. O satırdaki her bir hücre (td) için dön
        $(this).find('td').each(function(index) {
            // Sütun gizli değilse data-label ekle
            if (!$(this).hasClass('gizli')) {
                 // 4. Hücreye, başlık dizisindeki karşılığına göre data-label ekle
                $(this).attr('data-label', headers[index]);
            }
        });
    });
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
        },
        order: [[6, 'desc']],
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
         dom: 'rt<"bottom"i<"float-end"lp>><"clear">',
        lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "Tümü"]],
        initComplete: function(settings, json) {
            // Toplamlar bölümünü, DataTables tarafından oluşturulan alt bölümün (sayfalama) önüne taşı.
            $('.tableToplamaAlani').insertBefore('#datatablePayments_wrapper .bottom');
        }
    });

    // Filtre değişikliklerinde tabloyu yenile
    $('#tenant_id, #type, #date_from, #date_to').change(function(){
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
        $('#tenant_id').val('');
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
        var tenantId = $('#tenant_id').val();
        var type = $('#type').val();
        
        var exportUrl = '{{ route("super.admin.payment.history.export") }}' + 
            '?date_from=' + dateFrom + 
            '&date_to=' + dateTo +
            '&tenant_id=' + tenantId +
            '&type=' + type;
        
        window.open(exportUrl, '_blank');
    });

    // Toplam değerleri güncelleme fonksiyonu
    var updateTotals = function() {
        var dateFrom = $('#date_from').val();
        var dateTo = $('#date_to').val();
        var tenantId = $('#tenant_id').val();
        var type = $('#type').val();

        $.ajax({
            url: '{{ route("super.admin.payment.history.totals") }}',
            method: 'GET',
            data: {
                date_from: dateFrom,
                date_to: dateTo,
                tenant_id: tenantId,
                type: type
            },
            success: function(response) {
                $('.subscription_completed').html('<span>Tamamlanan:</span> ' + response.subscription_completed);
                $('.subscription_pending').html('<span>Bekleyen:</span> ' + response.subscription_pending);
                $('.subscription_failed').html('<span>Başarısız:</span> ' + response.subscription_failed);
                $('.subscription_total').html('<span>Toplam:</span> ' + response.subscription_total);
                
                $('.storage_completed').html('<span>Tamamlanan:</span> ' + response.storage_completed);
                $('.storage_pending').html('<span>Bekleyen:</span> ' + response.storage_pending);
                $('.storage_failed').html('<span>Başarısız:</span> ' + response.storage_failed);
                $('.storage_total').html('<span>Toplam:</span> ' + response.storage_total);
                
                // Entegrasyon - YENİ
                $('.integration_completed').html('<span>Tamamlanan:</span> ' + response.integration_completed);
                $('.integration_pending').html('<span>Bekleyen:</span> ' + response.integration_pending);
                $('.integration_failed').html('<span>Başarısız:</span> ' + response.integration_failed);
                $('.integration_total').html('<span>Toplam:</span> ' + response.integration_total);
                
                $('.general_completed').html('<span>Tamamlanan:</span> ' + response.completed);
                $('.general_pending').html('<span>Bekleyen:</span> ' + response.pending);
                $('.general_failed').html('<span>Başarısız:</span> ' + response.failed);
                $('.general_total').html('<span>Toplam:</span> ' + response.total);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    };

    // Sayfa yüklendiğinde ve tablo her çizildiğinde toplamları güncelle
    updateTotals();
    table.on('draw.dt', function () {
        updateTotals();
    });
});
</script>
@endsection