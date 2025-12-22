@extends('frontend.secure.user_master')
@section('user')
{{-- Daterangepicker için gerekli kütüphaneler --}}
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

@php 
if ($firma->isOnTrial()) {
    $konsinyeLimit = $firma->konsinyeSayisi ?? null;
  } else {
    $konsinyeLimit = $firma->plan()?->limits['konsinye'] ?? null;
  }
$stockAll = App\Models\Stock::where('firma_id', $firma->id)
  ->where('durum','1')
  ->where('urunKategori',  3)
  ->count();
@endphp
<div class="page-content" id="consignmentPage">
  <div class="container-fluid consigment-header-top">
    <div class="row pageDetail">
      <div class="col-12">
        <div class="card card-consigment">
        <div class="card-header card-consigment-header sayfaBaslik">
  Konsinye Cihazlar
</div>
<div class="card-body card-consigment-body">
  @if(!is_null($konsinyeLimit) && $konsinyeLimit != -1 && $stockAll >= $konsinyeLimit)
    <div class="consignment-limit-banner" id="consignmentLimitBanner">
      <div class="consignment-limit-banner-content">
        <div class="consignment-limit-banner-icon">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="consignment-limit-banner-text">
          <div class="consignment-limit-banner-title">Konsinye Limiti Doldu!</div>
          <div class="consignment-limit-banner-subtitle">
            Maksimum konsinye limiti ({{ $konsinyeLimit }}) ulaşıldı. Daha fazla cihaz eklemek için planınızı yükseltin.
          </div>
        </div>
      </div>
      <div class="consignment-limit-banner-actions">
        <a href="{{ route('abonelikler', ['tenant_id' => $firma->id]) }}" class="consignment-limit-banner-link">
          <i class="fas fa-arrow-up me-1"></i>Planı Yükselt
        </a>
        <button type="button" class="consignment-limit-banner-close" onclick="closeConsignmentBanner()">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
  @endif
  
  <div class="consignment-buttons-container">
    @if(is_null($konsinyeLimit) || $konsinyeLimit == -1 || $stockAll < $konsinyeLimit)
      <a data-bs-toggle="modal" data-bs-target="#addConsignmentModal" class="btn btn-success btn-sm addConsignment">
        <i class="fas fa-plus"></i><span>Cihaz Ekle</span>
      </a>
    @else
      <a class="btn btn-success btn-sm addStock" disabled style="pointer-events: none; opacity: .4; cursor: default;">
        <i class="fas fa-plus"></i><span>Cihaz Ekle</span>
      </a>
    @endif
    
    <a href="javascript:void(0);" class="btn btn-warning btn-sm printConsignment">
      <i class="fas fa-print"></i><span>Yazdır</span>
    </a>
  </div>

            <!-- Filtre dropdown butonu -->
            <div class="searchWrap float-end">
              <div class="btn-group">
                <button class="btn btn-dark btn-sm dropdown-toggle filtrele" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Filtrele <i class="mdi mdi-chevron-down"></i>
                </button>
                <div class="dropdown-menu servisDrop" style="min-width: 250px;">
                  
                  <!-- Raf -->
                  <div class="item mb-1">
                    <div class="row align-items-center">
                      <label class="col-sm-5 col-5 custom-p-r-m mb-0 custom-p-m-k">Raf</label>
                      <div class="col-sm-7 col-7 custom-p-m custom-p-r-m-k">
                        <select id="raf" class="form-select form-select-sm">
                          <option value="">Hepsi</option>
                          @foreach($rafListesi as $raf)
                            <option value="{{ $raf->id }}">{{ $raf->raf_adi }}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Marka -->
                  <div class="item mb-1">
                    <div class="row align-items-center">
                      <label class="col-sm-5 col-5 custom-p-r-m mb-0 custom-p-m-k ">Marka</label>
                      <div class="col-sm-7 col-7 custom-p-m custom-p-r-m-k">
                        <select id="marka" class="form-select form-select-sm">
                          <option value="">Hepsi</option>
                          @foreach($markalar as $marka)
                            <option value="{{ $marka->id }}">{{ $marka->marka }}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                  </div>

                  <!-- Cihaz -->
                  <div class="item mb-1">
                    <div class="row align-items-center">
                      <label class="col-sm-5 col-5 custom-p-r-m mb-0 custom-p-m-k">Cihaz</label>
                      <div class="col-sm-7 col-7 custom-p-m custom-p-r-m-k">
                        <select id="cihaz" class="form-select form-select-sm">
                          <option value="">Hepsi</option>
                          @foreach($cihazlar as $cihaz)
                            <option value="{{ $cihaz->id }}">{{ $cihaz->cihaz }}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                  </div>

                  <!-- Personel -->
                  <div class="item mb-2">
                    <div class="row align-items-center">
                      <label class="col-sm-5 col-5 custom-p-r-m mb-0 custom-p-m-k">Personel</label>
                      <div class="col-sm-7 col-7 custom-p-m custom-p-r-m-k">
                        <select id="personel" class="form-select form-select-sm">
                          <option value="">Hepsi</option>
                          @foreach($personeller as $personel)
                            <option value="{{ $personel->id }}">{{ $personel->name }}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                  </div>

                  {{-- TARİH ARALIĞI FİLTRESİ - DROPDOWN İÇİNDE --}}
                  <div class="item">
                    <div class="row ">
                      <label class="col-sm-5 col-5 custom-p-r-m mb-0 custom-p-m-k">Tarih Aralığı:</label>
                      <div class="col-sm-7 col-7 custom-p-m custom-p-r-m-k">
                        <input id="daterangeConsignment" class="form-control form-control-sm mb-2">
                        <div class="tarihAraligi d-flex flex-wrap gap-1">
                          <button id="lastYearConsignment" class="btn btn-sm btn-secondary">Son 1 Yıl</button>
                          <button id="lastMonthConsignment" class="btn btn-sm btn-secondary">Son 1 Ay</button>
                          <button id="lastWeekConsignment" class="btn btn-sm btn-secondary">Son 7 Gün</button>
                          <button id="yesterdayConsignment" class="btn btn-sm btn-secondary">Dün</button>
                          <button id="todayConsignment" class="btn btn-sm btn-secondary">Bugün</button>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
              </div><!-- /btn-group -->
            </div>

            <table id="datatableConsignment" class="table table-bordered dt-responsive nowrap" style="width: 100%;">
              <thead class="title">
                <tr>
                  <th style="width: 10px">ID</th>
                  <th>Tarih</th>
                  <th>Ürün Adı</th>
                  <th>Ürün Kodu</th>
                  <th>Fiyat</th>
                  <th>Adet</th>
                  <th>Raf</th>
                  <th>Marka / Cihaz</th>
                  <th style="width: 96px;">Düzenle</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>

            <!-- Toplam alanı -->
            <div class="tableToplamaAlani" >
              <div class="row r1" style="display: flex; align-items: center; justify-content: center;">
                <div class="sol"><strong>Toplam Bilgiler</strong></div>
                <div class="sag">
                  <div class="tur t1"><span>Toplam Ürün: </span><span id="toplamAdet">0</span></div>
                  <div class="tur t4"><span>Depo Kazanç: </span><span id="toplamFiyat">0 ₺</span></div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modallar -->
<div id="addConsignmentModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addConsignmentModalLabel" aria-hidden="true">
  <div class="modal-dialog custom-modal-width">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="addConsignmentModalLabel">Konsinye Cihaz Ekle</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">Yükleniyor...</div>
    </div>
  </div>
</div>

<div id="editConsignmentModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editConsignmentModalLabel" aria-hidden="true">
  {{-- modal-dialog sınıfı JS tarafından eklenecek --}}
  <div class="modal-dialog"> 
    <div class="modal-content">
      {{-- İçerik AJAX ile buraya gelecek --}}
    </div>
  </div>
</div>

<script>
$(document).ready(function(){
  var firma_id = {{ $firma->id }};

  $(".addConsignment").click(function(){
    $.ajax({
      url: "/"+firma_id+"/konsinye-cihaz-ekle/"
    }).done(function(data){
      if($.trim(data) === "-1"){
        location.reload(true);
      } else {
        $('#addConsignmentModal').modal('show');
        $('#addConsignmentModal .modal-body').html(data);
      }
    });
  });

  // Ana modal temizleme işlemi
  $("#addConsignmentModal").on("hidden.bs.modal", function(e){
      var modal = $(this);
      
      if (e.target === this) {
        setTimeout(function(){
          if (!$('.modal.show').length) {
            console.log("Modal içeriği temizleniyor");
            modal.find(".modal-body").html("");
    
            // Tüm backdrop'ları zorla kaldır
            $('.modal-backdrop').remove();
            
            // Body'den tüm modal sınıflarını kaldır ve style'ları sıfırla
            $('body').removeClass('modal-open').removeAttr('style');
            $('html').removeClass('modal-open').removeAttr('style');
            
          } else {
            console.log("Başka modal açık, temizleme yapılmıyor");
          }
        }, 100);
      }
  });

  // Edit modal için özel temizlik
  $('#editConsignmentModal').on('hidden.bs.modal', function(e) {
      // Sadece bu modal kapatılıyorsa (event target kontrolü)
      if (e.target === this) {
          setTimeout(function() {
              // Hiç modal açık değilse backdrop temizliği yap
              if (!$('.modal.show').length) {
                  $('.modal-backdrop').remove();
                  $('body').removeClass('modal-open').removeAttr('style');
                  $('html').removeClass('modal-open').removeAttr('style');
              }
          }, 100);
      }
  });

  // Tüm alt modal'lar için ortak temizlik
  $('#addBrandModal, #addDeviceTypeModal, #addCategoryModal, #addShelfModal, #addSupplierModal, #hareketEkleModal').on('hidden.bs.modal', function(e) {
      if (e.target === this) {
          setTimeout(function(){
              // Ana modal hala açıksa body'ye modal-open sınıfını geri ekle
              if ($('#addConsignmentModal').hasClass('show') || $('#editConsignmentModal').hasClass('show')) {
                  $('body').addClass('modal-open');
              } else if (!$('.modal.show').length) {
                  // Hiçbir modal açık değilse tam temizlik
                  $('.modal-backdrop').remove();
                  $('body').removeClass('modal-open').removeAttr('style');
                  $('html').removeClass('modal-open').removeAttr('style');
              }
          }, 50);
      }
  });

   // Edit Consignment Modal - Buton click event'i
  $('#datatableConsignment').on('click', '.editConsignment', function(){
    var id = $(this).data('bs-id');
    var modal = $('#editConsignmentModal'); 
   
    modal.find('.modal-dialog').removeClass('modal-xl').addClass('modal-xl'); // Boyutu ayarla (her seferinde)
    modal.find('.modal-content').html('<div class="modal-body text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Yükleniyor...</span></div></div>');
    
    // 2. Modal'ı göster
    modal.modal('show');
    
    // 3. Sunucudan veriyi çek
    $.ajax({
      url: "/" + firma_id + "/konsinye-cihazlar/duzenle/" + id,
      dataType: 'json',
      success: function(data){
        if($.trim(data.html) === "-1"){
          location.reload(true);
        } else {
          // 4. Gelen tüm HTML'i modal-content'in içine bas
          // Bu sayede gelen HTML'in kendi header, body, footer'ı kullanılır.
          modal.find('.modal-content').html(data.html);
        }
      },
      error: function() {
        // Hata durumunda içeriği temizle ve hata mesajı göster
        modal.find('.modal-content').html('<div class="modal-header"><h5 class="modal-title">Hata</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="alert alert-danger">İçerik yüklenirken bir hata oluştu.</div></div>');
      }
    });
  });

  // Mobilde ve masaüstünde satırın boş alanlarına tıklayınca da açılsın
  $('#datatableConsignment tbody').on('click', 'tr', function(e) {
    var $target = $(e.target);
    
    // Düzenle butonuna tıklandıysa, bu tr event'ini çalıştırma (butonun kendi event'i çalışsın)
    if ($target.closest('.editConsignment').length > 0 ||
        $target.closest('.btn').length > 0 || 
        $target.closest('td').index() === 8) {
      return;
    }
    
    var id = $(this).find('.editConsignment').first().data('bs-id');
    
    if (id) {
      var modal = $('#editConsignmentModal');
      
      // 1. Modal boyutunu ayarla ve loading göster
      modal.find('.modal-dialog').removeClass('modal-xl').addClass('modal-xl');
      modal.find('.modal-content').html('<div class="modal-body text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Yükleniyor...</span></div></div>');
      
      // 2. Modal'ı hemen göster
      modal.modal('show');
      
      // 3. Sunucudan veriyi çek
      $.ajax({
        url: "/" + firma_id + "/konsinye-cihazlar/duzenle/" + id,
        dataType: 'json',
        success: function(data){
          if($.trim(data.html) === "-1"){
            location.reload(true);
          } else {
            // 4. Gelen tüm HTML'i modal-content'in içine bas
            modal.find('.modal-content').html(data.html);
          }
        },
        error: function() {
          // Hata durumunda içeriği temizle ve hata mesajı göster
          modal.find('.modal-content').html('<div class="modal-header"><h5 class="modal-title">Hata</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="alert alert-danger">İçerik yüklenirken bir hata oluştu.</div></div>');
        }
      });
    }
  });
});
</script>

<script>
// DataTable
$(document).ready(function () {
  // Dashboard'dan gelen URL parametrelerini oku
  const urlParams = new URLSearchParams(window.location.search);
  const dashboardStartDate = urlParams.get('dashboard_istatistik_tarih1');
  const dashboardEndDate = urlParams.get('dashboard_istatistik_tarih2');

  // Daterangepicker başlatma (varsayılan son 3 gün)
  let initialConsignmentStartDate = dashboardStartDate ? moment(dashboardStartDate) : moment().subtract(2, 'days').startOf('day');
  let initialConsignmentEndDate = dashboardEndDate ? moment(dashboardEndDate) : moment().endOf('day');

  $('#daterangeConsignment').daterangepicker({
    startDate: initialConsignmentStartDate,
    endDate: initialConsignmentEndDate,
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
  },
  function (start_date, end_date) {
    $('#daterangeConsignment').val(start_date.format('DD-MM-YYYY') + ' - ' + end_date.format('DD-MM-YYYY'));
    table.draw();
  });

  // Hızlı tarih filtreleme butonları
  $('#lastYearConsignment').on('click', function () {
    $('#daterangeConsignment').data('daterangepicker').setStartDate(moment().subtract(1, 'year'));
    $('#daterangeConsignment').data('daterangepicker').setEndDate(moment());
    table.draw();
  });

  $('#lastMonthConsignment').on('click', function () {
    $('#daterangeConsignment').data('daterangepicker').setStartDate(moment().subtract(1, 'month'));
    $('#daterangeConsignment').data('daterangepicker').setEndDate(moment());
    table.draw();
  });

  $('#lastWeekConsignment').on('click', function () {
    $('#daterangeConsignment').data('daterangepicker').setStartDate(moment().subtract(7, 'days'));
    $('#daterangeConsignment').data('daterangepicker').setEndDate(moment());
    table.draw();
  });

  $('#yesterdayConsignment').on('click', function () {
    $('#daterangeConsignment').data('daterangepicker').setStartDate(moment().subtract(1, 'days'));
    $('#daterangeConsignment').data('daterangepicker').setEndDate(moment().subtract(1, 'days'));
    table.draw();
  });

  $('#todayConsignment').on('click', function () {
    $('#daterangeConsignment').data('daterangepicker').setStartDate(moment());
    $('#daterangeConsignment').data('daterangepicker').setEndDate(moment());
    table.draw();
  });

  var table = $('#datatableConsignment').DataTable({
      processing:true,
      serverSide: true,
      ajax: {
        url: "{{ route('consignmentdevice.data', $firma->id) }}",
        data: function (d) {
          d.raf = $('#raf').val();
          d.marka = $('#marka').val();
          d.cihaz = $('#cihaz').val();
          d.personel = $('#personel').val();
          
          // Konsinye tarih aralığını ekle
          d.from_date_consignment = $('#daterangeConsignment').data('daterangepicker').startDate.format('YYYY-MM-DD');
          d.to_date_consignment = $('#daterangeConsignment').data('daterangepicker').endDate.format('YYYY-MM-DD');
          
          // Dashboard tarih parametreleri varsa ekle
          if (dashboardStartDate && dashboardEndDate) {
              d.dashboard_istatistik_tarih1 = dashboardStartDate;
              d.dashboard_istatistik_tarih2 = dashboardEndDate;
          }
        }
      },
      columns: [
        { data: 'id', name: 'id', orderable: true },
        { data: 'created_at', name: 'created_at', orderable: true },
        { data: 'urunAdi', name: 'urunAdi', orderable: false },
        { data: 'urunKodu', name: 'urunKodu', orderable: true },
        { data: 'toplamTutar', name: 'toplamTutar', orderable: false },
        { data: 'adet', name: 'adet', orderable: false },
        { data: 'raf_adi', name: 'raf_adi', orderable: false },
        { data: 'marka_cihaz', name: 'marka_cihaz', orderable: false },
        { data: 'action', orderable: false, searchable: false }
      ],
      order: [[0, 'desc']],
      dom: 'B<"top"f>rt<"bottom"i<"float-end"lp>><"clear">',
      buttons: [{
        extend: 'print',
        text: 'Yazdır',
        autoPrint: true,
        exportOptions: {
          columns: [0, 1, 2, 3, 4, 5, 6, 7],
          format: {
            body: function (data, row, column, node) {
              // Önce string'e çevir ve temizle
              if (data === null || data === undefined) {
                return '';
              }
              
              // jQuery objesi veya DOM elementi ise text içeriğini al
              if (typeof data === 'object') {
                data = $(data).text();
              }
              
              // String'e çevir
              data = String(data);
              
              // Etiketleri temizle
              data = data.replace(/ID:/gi, '');
              data = data.replace(/Tarih:/gi, '');
              data = data.replace(/Ürün Adı:/gi, '');
              data = data.replace(/Ürün Kodu:/gi, '');
              data = data.replace(/Satış Fiyatı:/gi, '');
              data = data.replace(/Adet:/gi, '');
              data = data.replace(/Raf:/gi, '');
              data = data.replace(/Marka\/Cihaz:/gi, '');
              
              return data.trim();
            }
          }
        },
        customize: function (win) {
          $(win.document.head).find('style, link').remove();
          
          $(win.document.head).append(
            '<style>' +
            '.print-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; }' +
            '.print-title { text-align: left; font-size: 18px; font-weight: bold; margin-bottom: 13px; }' +
            'table { width: 100%; border-collapse: collapse; }' +
            'table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; color: #000 !important; }' +
            'table thead { display: table-header-group !important; }' +
            'table tbody { display: table-row-group !important; }' +
            'table tbody td * { color: #000 !important; }' +
            'table tbody td span { color: #000 !important; background-color: transparent !important; }' +
            'a, a:link, a:visited, a:hover, a:active { color: #000 !important; text-decoration: none !important; }' +
            '.print-footer { margin-top: 15px; text-align: left; border-top: 1px solid #ddd; padding-top: 10px; }' +
            '.page-number-bottom { text-align: center; margin-top: 30px; font-size: 14px; color: #666; font-weight: bold; }' +
            '@page { margin: 5mm; }' +
            '</style>'
          );
          
          var printDate = moment().format('DD.MM.YYYY HH:mm');
          var totalRecords = table.page.info().recordsDisplay;
          var firmaAdi = '{{ $firma->firma_adi ?? "Firma Adı" }}';
          
          $(win.document.body).find('h1').remove();
          
          // Inline style'ları temizle
          $(win.document.body).find('table tbody td').each(function() {
            $(this).find('*').removeAttr('style');
          });
          
          var header = '<div class="print-header">' +
                      '  <span>' + printDate + '</span>' +
                      '  <span>' + firmaAdi.toUpperCase() + '</span>' +
                      '</div>';
          $(win.document.body).prepend(header);
          
          var title = '<div class="print-title">Konsinye Cihazlar</div>';
          $(win.document.body).find('table').before(title);
          
          var footer = '<div class="print-footer">' +
                      '  <span>Listelenen Cihaz Sayısı: ' + totalRecords + ' - Tarih: ' + moment().format('DD/MM/YYYY') + '</span>' +
                      '</div>';
          $(win.document.body).find('table').after(footer);
          
          var pageInfo = '<div class="page-number-bottom">1/1</div>';
          $(win.document.body).append(pageInfo);
        }
      }],
      language: {
        sDecimal: ",",
        sEmptyTable: "Tabloda herhangi bir veri mevcut değil",
        sInfo: "Listelenen Ürün Sayısı: _TOTAL_ ",
        sInfoEmpty: "Kayıt yok",
        sInfoFiltered: "",
        sInfoPostFix: "",
        sInfoThousands:  ".",
        sLengthMenu: "_MENU_ ",
        sLoadingRecords: "Yükleniyor...",
        sProcessing: "İşleniyor...",
        sSearch: "",
        sZeroRecords: "Eşleşen kayıt bulunamadı",
        oPaginate: {
          sFirst: "İlk",
          sLast: "Son",
          sNext: '<i class="fas fa-angle-right"></i>', 
          sPrevious: '<i class="fas fa-angle-left"></i>'
        },
        oAria: {
          sSortAscending: ": artan sütun sıralamasını aktifleştir",
          sSortDescending: ": azalan sütun sıralamasını aktifleştir"
        },
        select: {
          rows: {
            _: "%d kayıt seçildi",
            0: "",
            1: "1 kayıt seçildi"
          }
        }
      },
      
      drawCallback: function(settings) {
        var api = this.api();
        var json = api.ajax.json();
        
        if (json && json.toplamAdet) {
            $('#toplamAdet').text(json.toplamAdet);
            $('#toplamFiyat').text(json.toplamFiyat);
        }

        $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
      },
      
      lengthMenu: [ [25, 50, 100, -1], [25, 50, 100, "Tümü"] ],
      initComplete: function(settings, json) {
          var searchContainer = $('#datatableConsignment_filter');
          var searchInput = searchContainer.find('input');
          var filterWrapper = $('.searchWrap');
          var flexContainer = $('<div class="d-flex justify-content-end w-100"></div>');

          searchContainer.find('label').contents().filter(function() {
              return this.nodeType == 3;
          }).remove();

          searchContainer.addClass('flex-grow-1 me-2');
          searchInput.addClass('w-100');
          searchInput.attr('placeholder', 'Konsinye Cihaz Ara...');

          flexContainer.append(searchContainer);
          flexContainer.append(filterWrapper);

          $('#datatableConsignment_wrapper .top').append(flexContainer);

          $('.searchWrap').css({ visibility: 'visible', opacity: 1 });
           $('.tableToplamaAlani').insertBefore('#datatableConsignment_wrapper .bottom');
      }
  });

  // Filtreler değiştiğinde tabloyu yeniden çiz
  $('#raf, #marka, #cihaz, #personel').change(function() {
      table.draw();
  });

  // Yazdır butonu click event'i
  $('.printConsignment').on('click', function(e) {
    e.preventDefault();
    table.button('.buttons-print').trigger();
  });
});

// Konsinye Banner Kapatma Fonksiyonu
function closeConsignmentBanner() {
  const banner = document.getElementById('consignmentLimitBanner');
  if (banner) {
    banner.style.display = 'none';
  }
}
</script>

@endsection