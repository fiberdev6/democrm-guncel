@extends('frontend.secure.user_master')
@section('user')
  <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<div class="page-content" id="customerPage">
  <div class="container-fluid customer-header-top">
    <div class="row pageDetail">
      <div class="col-12">
        <div class="card card-customer">
          <div class="card-header card-header-custom sayfaBaslik">
            Müşteriler
          </div>
          <div class="card-body">
            <table id="datatableCustomer" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
<div class="customer-buttons-container d-flex d-sm-block">
    <a data-bs-toggle="modal" data-bs-target="#addCustomerModal" class="btn btn-success btn-sm addCustomer">
        <i class="fas fa-plus"></i>
        <span>Müşteri Ekle</span>
    </a>
    
    <button id="printCustomers" class="btn btn-warning btn-sm printCustomers ">
        <i class="fas fa-print"></i>
        <span>Yazdır</span>
    </button>
</div>
    <!-- Filtreleme ve Arama Alanı (JavaScript ile taşınacak) -->
    <div class="searchWrap float-end">

        <div class="btn-group" id="müsteri_filtre">
            <button class="btn btn-dark btn-sm dropdown-toggle filtrele" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                Filtrele <i class="mdi mdi-chevron-down"></i>
            </button>
            <div class="dropdown-menu">
                <div class="item">
                    <div class="row">
                        <label class="col-sm-5 custom-p col-5 filtre-i-p">Durum</label>
                        <div class="filtre-i-p custom-p custom-p-m col-7 col-sm-7">
                            <select name="musteriTipi" id="musteriTipi" class="form-select">
                                <option value="">Hepsi</option>
                                <option value="1" >Bireysel</option>
                                <option value="2">Kurumsal</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="item">
                    <div class="row">
                        <label class="col-sm-5 custom-p col-5 filtre-i-p">İl</label>
                        <div class="col-sm-7 custom-p custom-p-m col-7 filtre-i-p">
                            <select name="il" id="countrySelect" class="form-control form-select" style="width:100%!important;">
                                <option value="" selected disabled>-Seçiniz-</option>
                                @foreach($countries as $item)
                                    <option value="{{ $item->id }}">{{ $item->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="item">
                    <div class="row">
                        <label class="col-sm-5 custom-p col-5 filtre-i-p">İlçe</label>
                        <div class="col-sm-7 custom-p custom-p-m col-7 filtre-i-p">
                            <select name="ilce" id="citySelect" class="form-control form-select" style="width:100%!important;">
                                <option value="" selected disabled>-Seçiniz-</option>                              
                            </select>
                        </div>
                    </div>
                </div>
                <!-- YENİ TARİH FİLTRESİ BAŞLANGICI -->
                <div class="item">
                    <div class="row">
                        <label class="col-sm-5 col-5 custom-p">Tarih Aralığı</label>
                        <div class="col-sm-7 custom-p col-7 custom-p-m custom-p-r-m-c">
                            <input id="daterangeCustomer" class="tarih-araligi form-control">
                            <div class="tarihAraligi mt-2 mb-2">
                                <button id="lastYearCustomer" class="btn btn-sm btn-secondary">Son 1 Yıl</button>
                                <button id="lastMonthCustomer" class="btn btn-sm btn-secondary">Son 1 Ay</button>
                                <button id="lastWeekCustomer" class="btn btn-sm btn-secondary">Son 7 Gün</button>
                                <button id="yesterdayCustomer" class="btn btn-sm btn-secondary">Dün</button>
                                <button id="todayCustomer" class="btn btn-sm btn-secondary">Bugün</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- YENİ TARİH FİLTRESİ SONU -->
            </div>
        </div><!-- /btn-group -->
    </div>
</div>

              
              <thead class="title">
                <tr>
                  <th style="width: 10px">ID</th>
                  <th data-priority="2">Ad Soyad</th>
                  <th>Telefon</th>
                  <th>Adres</th>
                  <th data-priority="1" style="width: 96px;">Düzenle</th>
                </tr>
              </thead>
              <tbody>
               
              </tbody>
            </table>
          </div>
        </div>
      </div> <!-- end col -->
    </div> <!-- end row -->
  </div>
</div>

<!-- add modal content -->
<div id="addCustomerModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog custom-modal-width">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="myModalLabel">Müşteri Ekle</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      Yükleniyor...
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<!-- edit modal content -->
<div id="editCustomerModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog custom-modal-width-edit">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="myModalLabel">Müşteri Düzenle</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="padding: 5px;">
        Yükleniyor...
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script type="text/javascript">
$(document).ready(function(){
  // Add Customer Modal
  var firma_id = {{$firma->id}};
  $(".addCustomer").click(function(){
    $.ajax({
      url: "/"+ firma_id + "/musteri-ekle/"
    }).done(function(data) {
      if ($.trim(data) === "-1") {
        window.location.reload(true);
      } else {
        $('#addCustomerModal').modal('show');
        $('#addCustomerModal .modal-body').html(data);
      }
    });
  });
  $("#addCustomerModal").on("hidden.bs.modal", function() {
      $('#addCustomerModal .modal-body').html("");
  });

 // Edit Customer Modal - Buton click event'i
  $('#datatableCustomer').on('click', '.editCustomer', function(e){
      var id = $(this).attr("data-bs-id");
      var firma_id = {{$firma->id}};
      $.ajax({
          url: "/"+ firma_id + "/musteri/duzenle/" + id
      }).done(function(data) {
          if ($.trim(data) === "-1") {
              window.location.reload(true);
          } else {
              $('#editCustomerModal').modal('show');
              $('#editCustomerModal .modal-body').html(data);
          }
      });
  });

  // Mobilde ve masaüstünde satırın boş alanlarına tıklayınca da açılsın
  $('#datatableCustomer tbody').on('click', 'tr', function(e) {
      var $target = $(e.target);
      
      // Düzenle butonuna tıklandıysa, bu tr event'ini çalıştırma (butonun kendi event'i çalışsın)
      if ($target.closest('.editCustomer').length > 0 ||
          $target.closest('.btn').length > 0 || 
          $target.closest('td').index() === 4) {
          return;
      }
      
      var id = $(this).find('.editCustomer').first().attr('data-bs-id');
      
      if (id) {
          // 1. MODAL'I HEMEN AÇ (AJAX beklemeden)
          $('#editCustomerModal').modal('show');
          
          // 2. AYNI ANDA AJAX BAŞLAT
          $.ajax({
              url: "/" + firma_id + "/musteri/duzenle/" + id
          }).done(function(data) {
              if ($.trim(data) === "-1") {
                  window.location.reload(true);
              } else {
                  $('#editCustomerModal .modal-body').html(data);
              }
          });
      }
  });

  $("#editCustomerModal").on("hidden.bs.modal", function() {
    $('#editCustomerModal .modal-body').html("");
  });


  // Ülke seçildiğinde şehirleri getir
  $("#countrySelect").change(function() {
    var selectedCountryId = $(this).val();
    if (selectedCountryId) {
      loadCities(selectedCountryId);
    }
  });

  // Şehirleri yüklemek için kullanılan fonksiyon
  function loadCities(countryId) {
    var citySelect = $("#citySelect");
    citySelect.empty(); // Önceki seçenekleri temizle
    citySelect.append(new Option("Yükleniyor...", "")); // Kullanıcıya yükleniyor bilgisi ver

    // AJAX isteğiyle şehirleri al
    $.get("/get-states/" + countryId, function(data) {
      citySelect.empty(); // Yükleniyor mesajını temizle
      citySelect.append(new Option("-Seçiniz-", "")); // İlk boş seçeneği ekle
      $.each(data, function(index, city) {
        citySelect.append(new Option(city.ilceName, city.id));
      });
    }).fail(function() {
      citySelect.empty(); // Hata durumunda temizle
      citySelect.append(new Option("Unable to load cities", ""));
    });
  }

  // Dashboard'dan gelen URL parametrelerini oku
  const urlParams = new URLSearchParams(window.location.search);
  const dashboardStartDate = urlParams.get('dashboard_istatistik_tarih1');
  const dashboardEndDate = urlParams.get('dashboard_istatistik_tarih2');

// Müşteri filtreleme dropdown'unun daterangepicker ile etkileşimde kapanmasını engelle
let preventCustomerDropdownHide = false;

// DOĞRU ID KULLANIMI
$('#müsteri_filtre').on('hide.bs.dropdown', function (e) {
  if (preventCustomerDropdownHide) {
    e.preventDefault();
  }
  preventCustomerDropdownHide = false;
});

// Daterangepicker'a tıklandığında dropdown'u açık tut
$(document).on('mousedown', function (e) {
  if ($(e.target).closest('.daterangepicker').length) {
    preventCustomerDropdownHide = true;
  }
});

// Tarih input'una tıklandığında
$('#daterangeCustomer').on('focus mousedown', function () {
  preventCustomerDropdownHide = true;
});

// Hızlı tarih butonlarına tıklandığında
$('.tarihAraligi button').on('mousedown', function () {
  preventCustomerDropdownHide = true;
});

// Daterangepicker kapatıldığında flag'i sıfırla
$('#daterangeCustomer').on('apply.daterangepicker cancel.daterangepicker hide.daterangepicker', function () {
  preventCustomerDropdownHide = false;
});
  // Müşteriler için daterangepicker başlatma (varsayılan son 3 gün)
  // Dashboard'dan gelen tarihler varsa onları, yoksa son 3 günü kullan
  let initialCustomerStartDate = dashboardStartDate ? moment(dashboardStartDate) : moment().subtract(2, 'days').startOf('day');
  let initialCustomerEndDate = dashboardEndDate ? moment(dashboardEndDate) : moment().endOf('day');

  $('#daterangeCustomer').daterangepicker({
    startDate: initialCustomerStartDate,
    endDate: initialCustomerEndDate,
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
    // daterangepicker değiştiğinde DataTable'ı yeniden çiz
    $('#daterangeCustomer').val(start_date.format('DD-MM-YYYY') + ' - ' + end_date.format('DD-MM-YYYY'));
    table.draw();
  });

  // Hızlı tarih filtreleme butonları
  $('#lastYearCustomer').on('click', function () {
    $('#daterangeCustomer').data('daterangepicker').setStartDate(moment().subtract(1, 'year'));
    $('#daterangeCustomer').data('daterangepicker').setEndDate(moment());
    table.draw();
  });

  $('#lastMonthCustomer').on('click', function () {
    $('#daterangeCustomer').data('daterangepicker').setStartDate(moment().subtract(1, 'month'));
    $('#daterangeCustomer').data('daterangepicker').setEndDate(moment());
    table.draw();
  });

  $('#lastWeekCustomer').on('click', function () {
    $('#daterangeCustomer').data('daterangepicker').setStartDate(moment().subtract(7, 'days'));
    $('#daterangeCustomer').data('daterangepicker').setEndDate(moment());
    table.draw();
  });

  $('#yesterdayCustomer').on('click', function () {
    $('#daterangeCustomer').data('daterangepicker').setStartDate(moment().subtract(1, 'days'));
    $('#daterangeCustomer').data('daterangepicker').setEndDate(moment().subtract(1, 'days'));
    table.draw();
  });

  $('#todayCustomer').on('click', function () {
    $('#daterangeCustomer').data('daterangepicker').setStartDate(moment());
    $('#daterangeCustomer').data('daterangepicker').setEndDate(moment());
    table.draw();
  });

  // Yazdırma için filtre bilgilerini hazırla
function getFilterInfoForPrint() {
  var filters = [];
  
  var musteriTipi = $('#musteriTipi').val();
  if (musteriTipi) {
      var tipText = musteriTipi == '1' ? 'Bireysel' : 'Kurumsal';
      filters.push('Müşteri Tipi: ' + tipText);
  }
  
  var il = $('#countrySelect').val();
  if (il) {
      var ilText = $('#countrySelect option:selected').text();
      filters.push('İl: ' + ilText);
  }
  
  var ilce = $('#citySelect').val();
  if (ilce) {
      var ilceText = $('#citySelect option:selected').text();
      filters.push('İlçe: ' + ilceText);
  }
  
  var dateRange = $('#daterangeCustomer').val();
  if (dateRange) {
      filters.push('Tarih Aralığı: ' + dateRange);
  }
  
  var searchTerm = $('input[type="search"]').val();
  if (searchTerm) {
      filters.push('Arama: ' + searchTerm);
  }
  
  if (filters.length > 0) {
      return filters.join('<br>');
  }
  
  return 'Filtre uygulanmadı (Tüm müşteriler)';
}


  var table = $('#datatableCustomer').DataTable({
      processing: true,
      serverSide: true,
      language: {
        paginate: {
          previous: "<i class='mdi mdi-chevron-left'>",
          next: "<i class='mdi mdi-chevron-right'>"
        }
      },
      ajax: {
        url: "{{ route('customers',$firma->id) }}",
        data: function(data) {
          data.search = $('input[type="search"]').val();
          data.tip = $('#musteriTipi').val();
          data.il = $('#countrySelect').val();
          data.ilce = $('#citySelect').val();

          // Müşteri tarih aralığını ekle
          data.from_date_customer = $('#daterangeCustomer').data('daterangepicker').startDate.format('YYYY-MM-DD');
          data.to_date_customer = $('#daterangeCustomer').data('daterangepicker').endDate.format('YYYY-MM-DD');

          // Eğer URL'den dashboard tarih parametreleri geldiyse, onları da ajax isteğine ekle
          // Ancak müşteri tarih aralığı filtreleri kullanılıyorsa, dashboard tarihleri override edilmeli.
          // Controller tarafında öncelik verilecek.
          if (dashboardStartDate && dashboardEndDate) {
              data.dashboard_istatistik_tarih1 = dashboardStartDate;
              data.dashboard_istatistik_tarih2 = dashboardEndDate;
          }
        }
      },
      'columns': [
        { data: 'id', orderable: true},
        { data: 'name', orderable: false },
        { data: 'tel', orderable: false },
        { data: 'address', orderable: false },
        { data: 'action', orderable: false}           
      ],
      drawCallback: function() {
        $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
      },
        "order": [[0, 'desc']],
        "columnDefs": [{
          "targets": 0,
          "className": "gizli"
        }],
       
        "oLanguage": {
            "sDecimal":        ",",
          "sEmptyTable":     "Tabloda herhangi bir veri mevcut değil",
          "sInfo":           "Müşteri Sayısı: _TOTAL_",
          "sInfoEmpty":      "Kayıt yok",
          "sInfoFiltered":   "",
          "sInfoPostFix":    "",
          "sInfoThousands":  ".",
          "sLengthMenu":     "_MENU_",
          "sLoadingRecords": "Yükleniyor...",
          "sProcessing":     "İşleniyor...",
          "sSearch":         "",
          "sZeroRecords":    "Eşleşen kayıt bulunamadı",
          "oPaginate": {
              "sFirst":    "İlk",
              "sLast":     "Son",
              "sNext":     '<i class="fas fa-angle-double-right"></i>',
              "sPrevious": '<i class="fas fa-angle-double-left"></i>'
          },
          "oAria": {
              "sSortAscending":  ": artan sütun sıralamasını aktifleştir",
              "sSortDescending": ": azalan sütun sıralamasını aktifleştir"
          },
          "select": {
              "rows": {
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
    columns: [0, 1, 2, 3],
    format: {
      body: function (data, row, column, node) {
        // Her hücredeki "Ad Soyad:", "Telefon:", "Adres:" vb. etiketleri temizle
        data = data.replace(/Ad Soyad:/gi, '');
        data = data.replace(/Telefon:/gi, '');
        data = data.replace(/Adres:/gi, '');
        data = data.replace(/ID:/gi, '');
        
        // Gereksiz boşlukları temizle
        return data.trim();
      }
    }
  },
  customize: function (win) {
    // 1. Mevcut tüm stilleri temizle
    $(win.document.head).find('style, link').remove();
    
    // 2. Özel CSS stilleri ekle
    $(win.document.head).append(
      '<style>' +
      '.print-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px;}' +
      '.print-title { text-align: left; font-size: 18px; font-weight: bold; margin-bottom: 13px; }' +
      'table { width: 100%; border-collapse: collapse; }' +
      'table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left;}' +
      'table thead { display: table-header-group !important; }' +
      'table tbody { display: table-row-group !important; }' +
      // Link stillerini düzenle
      'a, a:link, a:visited, a:hover, a:active { color: #000 !important; text-decoration: none !important; }' +
      'table td a, table td a:link, table td a:visited { color: #000 !important; text-decoration: none !important; }' +
      '.print-footer { margin-top: 15px; text-align: left; border-top: 1px padding-top: 10px; }' +
      '.page-number-bottom { text-align: center; margin-top: 30px; font-size: 14px; color: #666;font-weight: bold; page-break-after: always; }' +
      '@page { margin: 5mm; }' +
      '@media print { ' +
      '  thead { display: table-header-group; }' +
      '  tbody { page-break-inside: avoid; }' +
      '  a, a:link, a:visited { color: #000 !important; text-decoration: none !important; }' +
      '}' +
      '</style>'
    );
    
    //Gerekli bilgileri al
    var printDate = moment().format('DD.MM.YYYY HH:mm');
    var totalRecords = table.page.info().recordsDisplay;
    var firmaAdi = '{{ $firma->firma_adi ?? "Firma Adı" }}';
    
    //DataTables'ın otomatik eklediği başlık ve sayfa numaralarını kaldır
    $(win.document.body).find('h1').remove();
    // $(win.document.body).find('.page-number').remove();
    
    //Sayfanın en başına tarih ve firma adını ekle
    var header = '<div class="print-header">' +
                 '  <span>' + printDate + '</span>' +
                 '  <span>' + firmaAdi.toUpperCase() + '</span>' +
                 '</div>';
    $(win.document.body).prepend(header);
    
    //"Tüm Müşteriler" başlığını ekle
    var title = '<div class="print-title">Tüm Müşteriler</div>';
    $(win.document.body).find('table').before(title);
    
    //Alt bilgiyi ekle
    var footer = '<div class="print-footer">' +
                 '  <span>Listelenen Müşteri Sayısı: ' + totalRecords + ' - Tarih: ' + moment().format('DD/MM/YYYY') + '</span>' +
                 '</div>';
    $(win.document.body).find('table').after(footer);

    //SAYFA NUMARASINI EKLE 
    var pageInfo = '<div class="page-number-bottom">1/1</div>';
    $(win.document.body).append(pageInfo);
    
  }
}],
      "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "Tümü"] ],
     "initComplete": function(settings, json) {
          var topContainer = $('#datatableCustomer_wrapper .top');
          var searchContainer = $('#datatableCustomer_filter');
          var searchInput = searchContainer.find('input');
          
          // Filtre butonunu HTML'den al (artık sadece bir tane var)
          var filterWrapper = $('.searchWrap');

          // Arama kutusu ve filtreyi sarmalayacak yeni bir flex container oluştur
          var flexContainer = $('<div class="d-flex justify-content-end w-100"></div>');

          // DataTables'in varsayılan "Search:" etiketini kaldır ve input'u ayarla
          searchContainer.find('label').contents().filter(function() {
              return this.nodeType == 3;
          }).remove();
          
          searchInput.attr('placeholder', 'Müşteri Ara...');
          searchContainer.addClass('flex-grow-1 me-1'); // Arama kutusunun esnemesini ve sağ boşluk bırakmasını sağla
          searchInput.addClass('w-100');

          // Ögeleri yeni flex container'a ekle
          flexContainer.append(searchContainer);
          flexContainer.append(filterWrapper);
          
          // Her şeyi tablonun üstündeki "top" alanına ekle
          topContainer.html(flexContainer);

          // Görünür yap
          $('.searchWrap').css({ visibility: 'visible', opacity: 1 });
      } 
  });
  $('#musteriTipi').change(function(){
    table.draw();        
  });

  $('#countrySelect').change(function(){
    table.draw();        
  });

  $('#citySelect').change(function(){
    table.draw();        
  });
 // Yazdır butonu click event'i - DataTables'ın kendi print fonksiyonunu tetikle
$('#printCustomers').on('click', function() {
  table.button('.buttons-print').trigger();
});



});


</script>

<script>
    $(document).ready(function() {
      // Ülke seçildiğinde şehirleri getir
      $("#countrySelect").change(function() {
        var selectedCountryId = $(this).val();
        if (selectedCountryId) {
          loadCities(selectedCountryId);
        }
      });
    
      // Şehirleri yüklemek için kullanılan fonksiyon
      function loadCities(countryId) {
        var citySelect = $("#citySelect");
        citySelect.empty(); // Önceki seçenekleri temizle
        citySelect.append(new Option("Yükleniyor...", "")); // Kullanıcıya yükleniyor bilgisi ver
    
        // AJAX isteğiyle şehirleri al
        $.get("/get-states/" + countryId, function(data) {
          citySelect.empty(); // Yükleniyor mesajını temizle
          citySelect.append(new Option("-Seçiniz-", "")); // İlk boş seçeneği ekle
          $.each(data, function(index, city) {
            citySelect.append(new Option(city.ilceName, city.id));
          });
        }).fail(function() {
          citySelect.empty(); // Hata durumunda temizle
          citySelect.append(new Option("Unable to load cities", ""));
        });
      }
    });
    </script>

    <script>
    $(document).ready(function () {
      var dropdownContainer = $('#müsteri_filtre');
      var filterButton = dropdownContainer.find('.filtrele');
      dropdownContainer.on('show.bs.dropdown', function () {
        filterButton.html('Kapat <i class="mdi mdi-chevron-down"></i>');
      });
      dropdownContainer.on('hide.bs.dropdown', function () {
        filterButton.html('Filtrele <i class="mdi mdi-chevron-down"></i>');
      });
    });
  </script>
   <script>
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

    $(document).ready(function() {
    var mid = getUrlParameter('did');
    var firma_id = {{$firma->id}};
    if (mid) {
      $.ajax({
        url: "/" + firma_id + "/musteri/duzenle/" + mid
      }).done(function (data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#editCustomerModal .modal-body').html(data);
          $('#editCustomerModal').modal('show');
        }
      });
    }
     });
  </script>

@endsection