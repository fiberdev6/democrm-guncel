@extends('frontend.secure.user_master')
@section('user')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="{{asset('backend/assets/css/bootstrap.min.css')}}" id="bootstrap-style" rel="stylesheet" type="text/css" />
<!--<script src="{{asset('backend/assets/libs/jquery/jquery.min.js')}}"></script>-->
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />


<div class="page-content" id="allTeklif">
  <div class="container-fluid staff-header-top">
    <div class="row pageDetail">
      <div class="col-12">
        <div class="card card-offer">
          <div class="card-header card-offer-header sayfaBaslik">
            Teklifler
          </div>
          <div class="card-body card-offer-body">
            <table id="datatableOffer" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">

{{-- MASAÜSTÜ GÖRÜNÜMÜ (Orijinal Kodunuz - Hiçbir Değişiklik Yapılmadı) --}}
{{-- Bu bölüm sadece geniş ekranlarda (lg ve üstü) görünecektir. --}}
<div class="offer-buttons-container">
    <a class="btn btn-success btn-sm addOffer" data-bs-toggle="modal" data-bs-target="#addOfferModal">
        <i class="fas fa-plus"></i><span>Teklif Ekle</span>
    </a>
    
    <a href="javascript:void(0);" class="btn btn-warning btn-sm printOffers">
        <i class="fas fa-print"></i><span>Yazdır</span>
    </a>
    <div class="searchWrap float-end">
        <div class="btn-group">
            <button class="btn btn-dark btn-sm dropdown-toggle filtrele" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                Filtrele <i class="mdi mdi-chevron-down"></i>
            </button>
            <div class="dropdown-menu servisDrop">
                <div class="item">
                    <div class="row">
                        <label class="col-sm-4 col-5 custom-p-r-m-k">Durum</label>
                        <div class="col-sm-8 col-7 custom-p-m-k custom-p-r-m-k">
                            <select name="teklifDurumu" id="teklifDurumu" class="form-select">
                                <option value="">Hepsi</option>
                                <option value="0">Beklemede</option>
                                <option value="1">Onaylandı</option>
                                <option value="2">Onaylanmadı</option>
                                <option value="3">Cevap Gelmedi</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="item">
                    <div class="row">
                        <label class="col-sm-4 col-5 custom-p-r-m-k">Tarih Aralığı:</label>
                        <div class="col-sm-8 col-7 custom-p-m-k custom-p-r-m-k">
                            <input id="daterange" class="tarih-araligi">
                            <div class="tarihAraligi mt-2 mb-2">
                              <button id="today" class="btn btn-sm btn-secondary">Bugün</button>
                              <button id="yesterday" class="btn btn-sm btn-secondary">Dün</button>
                              <button id="lastWeek" class="btn btn-sm btn-secondary">Son 7 Gün</button>
                              <button id="lastMonth" class="btn btn-sm btn-secondary">Son 1 Ay</button>
                                <button id="lastYear" class="btn btn-sm btn-secondary">Son 1 Yıl</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /btn-group -->
    </div>
</div>

              <thead class="title">
                <tr>
                  <th style="width: 10px">ID</th>
                  <th data-priority="2">Tarih</th>
                  <th>Müşteri Adı</th>
                  <th>G. Toplam</th>
                  <th>Durum</th>
                  <th data-priority="1" style="width: 96px;">Düzenle</th>
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
        
<!-- add modal content -->
<div id="addOfferModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog servis-modal-custom">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="myModalLabel">Teklif Ekle</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Yükleniyor...
      </div>
    </div>
  </div>
</div>

<!-- edit modal content -->
<div id="editOfferModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog servis-modal-custom">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="myModalLabel">Teklif Düzenle</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Yükleniyor...
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
  var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1),
      sURLVariables = sPageURL.split('&'),
      sParameterName,
      i;
    for (i = 0; i < sURLVariables.length; i++) {
      sParameterName = sURLVariables[i].split('=');
      if (sParameterName[0] === sParam) {
        return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
      }
    }
  };

  var mid = getUrlParameter('did');
  var firma_id = {{$firma->id}};
  if(mid){
    $.ajax({
      url: "/"+ firma_id + "/teklif-duzenle/"+ mid
    }).done(function(data) { 
      if($.trim(data)==="-1"){
        window.location.reload(true);
      }else{
        $('#editOfferModal').modal('show');
        $('#editOfferModal .modal-body').html(data);
      }
    });
  }
  });
</script>

<script type="text/javascript">
$(document).ready(function(){
  $(".addOffer").click(function(){
    var firma_id = {{$firma->id}};
    $.ajax({
      url: "/"+ firma_id + "/teklif-ekle/"
    }).done(function(data) {
      if ($.trim(data) === "-1") {
        window.location.reload(true);
      } else {
        $('#addOfferModal').modal('show');
        $('#addOfferModal .modal-body').html(data);
      }
    });
  });
  
  $("#addOfferModal").on("hidden.bs.modal", function() {
    $('#addOfferModal .modal-body').html("Yükleniyor...");
  });

  // Edit Offer Modal - Buton click event'i (mobil ve masaüstü için gerekli)
  $('#datatableOffer').on('click', '.editOffer', function(e){
    var id = $(this).attr("data-bs-id");
    var firma_id = {{$firma->id}};
    $.ajax({
      url: "/"+ firma_id + "/teklif-duzenle/" + id
    }).done(function(data) {
      if ($.trim(data) === "-1") {
        window.location.reload(true);
      } else {
        $('#editOfferModal').modal('show');
        $('#editOfferModal .modal-body').html(data);
      }
    });
  });

  // Mobilde ve masaüstünde satırın boş alanlarına tıklayınca da açılsın
  $('#datatableOffer tbody').on('click', 'tr', function(e) {
    var $target = $(e.target);
    
    // Düzenle butonuna tıklandıysa, bu tr event'ini çalıştırma (butonun kendi event'i çalışsın)
    if ($target.closest('.editOffer').length > 0 ||
        $target.closest('.btn').length > 0 || 
        $target.closest('td').index() === 5) {
      return;
    }
    
    var id = $(this).find('.editOffer').first().attr('data-bs-id');
    
    if (id) {
      // 1. MODAL'I HEMEN AÇ (AJAX beklemeden)
      $('#editOfferModal').modal('show');
      
      // 2. AYNI ANDA AJAX BAŞLAT
      var firma_id = {{$firma->id}};
      $.ajax({
        url: "/"+ firma_id + "/teklif-duzenle/" + id
      }).done(function(data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#editOfferModal .modal-body').html(data);
        }
      });
    }
  });
  
  $("#editOfferModal").on("hidden.bs.modal", function() {
    $('#editOfferModal .modal-body').html("Yükleniyor...");
  });
});
</script>

<script type="text/javascript">
$(document).ready(function(){
  $('#datatableOffer').on('click', '.editOffer', function(e){
    var id = $(this).attr("data-bs-id");
    var firma_id = {{$firma->id}};
    $.ajax({
      url: "/"+ firma_id + "/teklif-duzenle/" + id
    }).done(function(data) {
      if ($.trim(data) === "-1") {
        window.location.reload(true);
      } else {
        $('#editOfferModal').modal('show');
        $('#editOfferModal .modal-body').html(data);
      }
    });
  });
  
  $("#editOfferModal").on("hidden.bs.modal", function() {
    $('#editOfferModal .modal-body').html("Yükleniyor...");
  });
});
</script>

<script>
$(document).ready(function () {
  // Varsayılan tarih aralığı: Son 3 gün
  var start_date = moment().subtract(2, 'days');
  var end_date = moment(); 

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
  }, function(start_date, end_date){
    $('#daterange').html(start_date.format('DD-MM-YYYY') + ' - ' + end_date.format('DD-MM-YYYY'));
    table.draw();
  });

  var table = $('#datatableOffer').DataTable({
    processing: true,
    serverSide: true,
    order: [[0, 'desc']],
    language: {
      paginate: {
        previous: "<i class='mdi mdi-chevron-left'>",
        next: "<i class='mdi mdi-chevron-right'>"
      }
    },
    ajax: {
      url: "{{ route('offers', $firma->id) }}",
      data: function(data) {
        data.search = $('input[type="search"]').val();
        data.teklifDurumu = $('#teklifDurumu').val();
        
        var daterangepicker = $('#daterange').data('daterangepicker');
        if (daterangepicker && daterangepicker.startDate) {
          data.from_date = daterangepicker.startDate.format('YYYY-MM-DD');
          data.to_date = daterangepicker.endDate.format('YYYY-MM-DD');
        }
      }
    },
    columns: [
      { data: 'id', orderable: true},
      { data: 'created_at', orderable: true},
      { data: 'mid', orderable: false },
      { data: 'genelToplam', orderable: true },
      { data: 'teklifDurumu', orderable: false},
      { data: 'action', orderable: false}           
    ],
    drawCallback: function() {
      $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
    },
    order: [[1, 'desc']],
    columnDefs: [{
      targets: 0,
      className: "gizli"
    }],
    oLanguage: {
      sDecimal: ",",
      sEmptyTable: "Tabloda herhangi bir veri mevcut değil",
      sInfo: "Teklif Sayısı: _TOTAL_",
      sInfoEmpty: "Kayıt yok",
      sInfoFiltered: "",
      sInfoPostFix: "",
      sInfoThousands: ".",
      sLengthMenu: "_MENU_",
      sLoadingRecords: "Yükleniyor...",
      sProcessing: "İşleniyor...",
      sSearch: "",
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
    dom: 'B<"top"f>rt<"bottom"i<"float-end"lp>><"clear">',
    buttons: [{
  extend: 'print',
  text: 'Yazdır',
  autoPrint: true,
  exportOptions: {
    columns: [0, 1, 2, 3, 4],
    format: {
      body: function (data, row, column, node) {
        if (data === null || data === undefined) {
          return '';
        }
        
        if (typeof data === 'object') {
          data = $(data).text();
        }
        
        data = String(data);
        
        // Etiketleri temizle
        data = data.replace(/ID\s*:/gi, '');
        data = data.replace(/Tarih\s*:/gi, '');
        data = data.replace(/Müşteri\s+Adı\s*:/gi, '');
        data = data.replace(/Müşteri\s*:/gi, '');
        data = data.replace(/G\.\s*Toplam\s*:/gi, '');
        data = data.replace(/Durum\s*:/gi, '');
        
        // Satır sonları ve boşlukları temizle
        data = data.replace(/\n/g, ' ');
        data = data.replace(/\s+/g, ' ');
        
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
      'table tbody td * { color: #000 !important; font-weight: normal !important; }' +
      'table tbody td span { color: #000 !important; background-color: transparent !important; font-weight: normal !important; }' +
      'table tbody td { font-weight: normal !important; }' +
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
    
    // Inline style'ları temizle ve font-weight'i normal yap
    $(win.document.body).find('table tbody td').each(function() {
      $(this).find('*').removeAttr('style').css('font-weight', 'normal');
      $(this).css('font-weight', 'normal');
    });
    
    var header = '<div class="print-header">' +
                '  <span>' + printDate + '</span>' +
                '  <span>' + firmaAdi.toUpperCase() + '</span>' +
                '</div>';
    $(win.document.body).prepend(header);
    
    var title = '<div class="print-title">Teklifler</div>';
    $(win.document.body).find('table').before(title);
    
    var footer = '<div class="print-footer">' +
                '  <span>Listelenen Teklif Sayısı: ' + totalRecords + ' - Tarih: ' + moment().format('DD/MM/YYYY') + '</span>' +
                '</div>';
    $(win.document.body).find('table').after(footer);
    
    var pageInfo = '<div class="page-number-bottom">1/1</div>';
    $(win.document.body).append(pageInfo);
  }
}],
    lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "Tümü"]],
    initComplete: function(settings, json) {
      var searchContainer = $('#datatableOffer_filter');
      var searchInput = searchContainer.find('input');
      var filterWrapper = $('.searchWrap');

      // Arama kutusu ve filtre butonu için yeni bir flex container oluştur
      var flexContainer = $('<div class="d-flex justify-content-end w-100"></div>');

      // DataTables'ın varsayılan "Search:" etiketini kaldır
      searchContainer.find('label').contents().filter(function() {
        return this.nodeType == 3;
      }).remove();

      // Arama kutusunu ve kapsayıcısını stillendir
      searchContainer.addClass('flex-grow-1');
      searchInput.addClass('w-100');
      searchInput.attr('placeholder', 'Teklif Ara...');

      // Arama kutusunu ve filtre butonunu flex container'a ekle
      flexContainer.append(searchContainer);
      flexContainer.append(filterWrapper);

      // Yeni flex container'ı tablonun üst kısmına ekle
      $('#datatableOffer_wrapper .top').append(flexContainer);

      // Filtre butonunu görünür yap
      $('.searchWrap').css({ visibility: 'visible', opacity: 1 });
    }
  });

  // Tarih butonları
  $('#today').on('click', function() {
    var today = moment();
    $('#daterange').data('daterangepicker').setStartDate(today);
    $('#daterange').data('daterangepicker').setEndDate(today);
    table.draw();
  });

  $('#yesterday').on('click', function() {
    var yesterday = moment().subtract(1, 'days');
    $('#daterange').data('daterangepicker').setStartDate(yesterday);
    $('#daterange').data('daterangepicker').setEndDate(yesterday);
    table.draw();
  });

  $('#lastWeek').on('click', function() {
    var lastWeek = moment().subtract(7, 'days');
    $('#daterange').data('daterangepicker').setStartDate(lastWeek);
    $('#daterange').data('daterangepicker').setEndDate(moment());
    table.draw();
  });

  $('#lastMonth').on('click', function() {
    var lastMonth = moment().subtract(1, 'month');
    $('#daterange').data('daterangepicker').setStartDate(lastMonth);
    $('#daterange').data('daterangepicker').setEndDate(moment());
    table.draw();
  });

  $('#lastYear').on('click', function() {
    var lastYear = moment().subtract(1, 'year');
    $('#daterange').data('daterangepicker').setStartDate(lastYear);
    $('#daterange').data('daterangepicker').setEndDate(moment());
    table.draw();
  });

  // Durum filtresi değiştiğinde
  $('#teklifDurumu').change(function(){
    table.draw();        
  });

  // Filtre butonu metin değiştirme
  $('.searchWrap').on('show.bs.dropdown', '.btn-group', function () {
    $(this).find('.filtrele').html('Kapat <i class="mdi mdi-chevron-down"></i>');
  });

  $('.searchWrap').on('hide.bs.dropdown', '.btn-group', function () {
    $(this).find('.filtrele').html('Filtrele <i class="mdi mdi-chevron-down"></i>');
  });
  // Yazdır butonu click event'i
    $('.printOffers').on('click', function(e) {
      e.preventDefault();
      table.button('.buttons-print').trigger();
    });
});

</script>

@endsection