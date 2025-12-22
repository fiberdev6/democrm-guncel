@extends('frontend.secure.user_master')
@section('user')

  @php 
    if ($firma->isOnTrial()) {
      // Deneme süresinde => firmalar tablosundaki personelSayisi alanı kullanılacak
      $staffLimit = $firma->personelSayisi ?? null;
    } else {
      // Normal abonelik planındaki limit
      $staffLimit = $firma->plan()?->limits['users'] ?? null;
    }
    $staffAll = App\Models\User::where('tenant_id', $firma->id)
      ->where('status', '1')
      ->whereHas('roles', function ($query) {
        $query->where('name', '!=', 'Bayi');
      })->count();
  @endphp

  <div class="page-content" id="staffPage">
    <div class="container-fluid staff-header-top">
      <div class="row pageDetail">
        <div class="col-12">
          <div class="card card-staff">
          <div class="card-header card-staff-header sayfaBaslik">
            Personeller
          </div>
            <div class="card-body card-staff-body">
              @if(!is_null($staffLimit) && $staffLimit != -1 && $staffAll >= $staffLimit)
                <div class="staff-limit-banner" id="staffLimitBanner">
                  <div class="staff-limit-banner-content">
                    <div class="staff-limit-banner-icon">
                      <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="staff-limit-banner-text">
                      <div class="staff-limit-banner-title">Personel Limiti Doldu!</div>
                      <div class="staff-limit-banner-subtitle">
                        Maksimum personel limiti ({{ $staffLimit }}) ulaşıldı. Daha fazla personel eklemek için planınızı yükseltin.
                      </div>
                    </div>
                  </div>
                  <div class="staff-limit-banner-actions">
                    <a href="{{ route('abonelikler', ['tenant_id' => $firma->id]) }}" class="staff-limit-banner-link">
                      <i class="fas fa-arrow-up me-1"></i>Planı Yükselt
                    </a>
                    <button type="button" class="staff-limit-banner-close" onclick="closeStaffBanner()">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>
              @endif
              
              <table id="datatablePersonel" class="table table-bordered dt-responsive nowrap"
                style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                <div class="staff-buttons-info-wrapper">
                  <div class="staff-buttons-container d-flex d-sm-block">
                    @if(is_null($staffLimit) || $staffLimit == -1 || $staffAll < $staffLimit)
                      <a data-bs-toggle="modal" data-bs-target="#addPersonelModal" class="btn btn-success btn-sm addPersonel">
                        <i class="fas fa-plus"></i><span>Personel Ekle</span>
                      </a>
                    @else
                      <a class="btn btn-success btn-sm addPersonel" disabled
                        style="pointer-events: none; opacity: .4; cursor: default;">
                        <i class="fas fa-plus"></i><span>Personel Ekle</span>
                      </a>
                    @endif
                    
                    <button id="printStaffs" class="btn btn-warning btn-sm printStaffs">
                      <i class="fas fa-print"></i>
                      <span>Yazdır</span>
                    </button>
                  </div>
                </div>
                <div class="searchWrap float-end">
                  <div class="btn-group " id="personelfiltre">
                    <button class="btn btn-dark btn-sm dropdown-toggle filtrele" type="button" data-bs-toggle="dropdown"
                      aria-expanded="false">
                      Filtrele <i class="mdi mdi-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu servisDrop">
                      <div class="item">
                        <div class="row">
                          <label class="col-sm-5 custom-p col-5 ">Durum</label>
                          <div class="col-sm-7 custom-p custom-p-m col-7">
                            <select name="durum" id="durum" class="form-select">
                              <option value="2">Hepsi</option>
                              <option value="1" selected>Çalışıyor</option>
                              <option value="0">Ayrıldı</option>
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="item">
                        <div class="row">
                          <label class="col-sm-5 custom-p col-5">Personel Grubu</label>
                          <div class="col-sm-7 custom-p custom-p-m col-7">
                            <select name="rolePers" id="rolePers" class="form-select">
                              <option value="">Hepsi</option>
                              @foreach($roles as $role)
                                <option value="{{$role->id}}">{{$role->name}}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div><!-- /btn-group -->
                </div>

                <thead class="title">
                  <tr>
                    <th style="width: 10px">ID</th>
                    <th data-priority="2">Personel Adı</th>
                    <th>Personel Grubu</th>
                    <th>Telefon</th>
                    <th>Adres</th>
                    <th>Durum</th>
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
  <div id="addPersonelModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog custom-modal-width">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title" id="myModalLabel">Personel Ekle</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->


  <!-- edit modal content -->
  <div id="editPersonelModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog custom-modal-width">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title" id="myModalLabel">Personel Düzenle</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->

  <script type="text/javascript">
    $(document).ready(function () {
      var firma_id = {{$firma->id}};
      $(".addPersonel").click(function () {

        $.ajax({
          url: "/" + firma_id + "/personel-ekle/"
        }).done(function (data) {
          if ($.trim(data) === "-1") {
            window.location.reload(true);
          } else {
            $('#addPersonelModal').modal('show');
            $('#addPersonelModal .modal-body').html(data);
          }
        });
      });
      $("#addPersonelModal").on("hidden.bs.modal", function () {
        $('#addPersonelModal .modal-body').html("");
      });
    });
  </script>


<script type="text/javascript">
$(document).ready(function(){
    // Edit Personel Modal - Buton click event'i 
    $('#datatablePersonel').on('click', '.editPersonel', function(e){

        var id = $(this).attr("data-bs-id");
        var firma_id = {{$firma->id}};
        $.ajax({
          url: "/" + firma_id + "/personel/duzenle/" + id
        }).done(function (data) {
          if ($.trim(data) === "-1") {
            window.location.reload(true);
          } else {
            $('#editPersonelModal').modal('show');
            $('#editPersonelModal .modal-body').html(data);
          }
        });
      });
      $("#editPersonalModal").on("hidden.bs.modal", function () {
        $('#editPersonelModal .modal-body').html("");
      });
    });


    // Mobilde ve masaüstünde satırın boş alanlarına tıklayınca da açılsın
    $('#datatablePersonel tbody').on('click', 'tr', function(e) {
        var $target = $(e.target);
        
        // Düzenle butonuna tıklandıysa, bu tr event'ini çalıştırma (butonun kendi event'i çalışsın)
        if ($target.closest('.editPersonel').length > 0 ||
            $target.closest('.btn').length > 0 || 
            $target.closest('td').index() === 6) {  // Düzenle kolonu 6. index
            return;
        }
        
        var id = $(this).find('.editPersonel').first().attr('data-bs-id');
        
        if (id) {
            // 1. MODAL'I HEMEN AÇ (AJAX beklemeden)
            $('#editPersonelModal').modal('show');
            
            // 2. AYNI ANDA AJAX BAŞLAT
            var firma_id = {{$firma->id}};
            $.ajax({
                url: "/" + firma_id + "/personel/duzenle/" + id
            }).done(function(data) {
                if ($.trim(data) === "-1") {
                    window.location.reload(true);
                } else {
                    $('#editPersonelModal .modal-body').html(data);
                }
            });
        }
    });

    $("#editPersonelModal").on("hidden.bs.modal", function() {
        $('#editPersonelModal .modal-body').html("");
    });

</script>

<script>
$(document).ready(function () {
  var table = $('#datatablePersonel').DataTable({
      processing: true,
      serverSide: true,
      language: {
        paginate: {
          previous: "<i class='mdi mdi-chevron-left'>",
          next: "<i class='mdi mdi-chevron-right'>"
        }
      },
      ajax: {
        url: "{{ route('staffs',$firma->id) }}",
        data: function(data) {
          data.search = $('input[type="search"]').val();
          data.durum = $('#durum').val();
          data.grup = $('#rolePers').val();
        }
      },
      'columns': [
        { data: 'user_id', orderable: true},
        { data: 'name', orderable: false },
        { data: 'grup', orderable: false },
        { data: 'tel', orderable: false },
        { data: 'address', orderable: false },
        { data: 'status', orderable: false },
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
          "sDecimal": ",",
          "sEmptyTable": "Tabloda herhangi bir veri mevcut değil",
          "sInfo": "Personel Sayısı: _TOTAL_",
          "sInfoEmpty": "Kayıt yok",
          "sInfoFiltered": "",
          "sInfoPostFix": "",
          "sInfoThousands": ".",
          "sLengthMenu": "_MENU_",
          "sLoadingRecords": "Yükleniyor...",
          "sProcessing": "İşleniyor...",
          "sSearch": "",
          "sZeroRecords": "Eşleşen kayıt bulunamadı",
          "oPaginate": {
            "sFirst": "İlk",
            "sLast": "Son",
            "sNext": '<i class="fas fa-angle-double-right"></i>',
            "sPrevious": '<i class="fas fa-angle-double-left"></i>'
          },
          "oAria": {
            "sSortAscending": ": artan sütun sıralamasını aktifleştir",
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
    columns: [0, 1, 2, 3, 4, 5],
    format: {
      body: function (data, row, column, node) {
        // Etiketleri temizle
        data = data.replace(/ID:/gi, '');
        data = data.replace(/Personel Adı:/gi, '');
        data = data.replace(/Personel Grubu:/gi, '');
        data = data.replace(/P. Grubu:/gi, '');
        data = data.replace(/Telefon:/gi, '');
        data = data.replace(/Adres:/gi, '');
        data = data.replace(/Durum:/gi, '');
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
               '  <span style="font-weight: bold;">' + firmaAdi.toUpperCase() + '</span>' +
               '</div>';
  $(win.document.body).prepend(header);
  
  var title = '<div class="print-title">Tüm Personeller</div>';
  $(win.document.body).find('table').before(title);
  
  var footer = '<div class="print-footer">' +
               '  <span>Listelenen Personel Sayısı: ' + totalRecords + ' - Tarih: ' + moment().format('DD/MM/YYYY') + '</span>' +
               '</div>';
  $(win.document.body).find('table').after(footer);
  
  var pageInfo = '<div class="page-number-bottom">1/1</div>';
  $(win.document.body).append(pageInfo);
}
}],
        "lengthMenu": [[25, 50, 100, -1], [25, 50, 100, "Tümü"]],
        "initComplete": function (settings, json) {
          // --- DEĞİŞTİRİLEN BÖLÜM BURASI ---
          var topContainer = $('#datatablePersonel_wrapper .top'); // .top div'ini seçiyoruz
          var searchContainer = $('#datatablePersonel_filter');
          var searchInput = searchContainer.find('input');
          var filterWrapper = $('.searchWrap');
          var flexContainer = $('<div class="d-flex justify-content-end w-100"></div>');

          searchContainer.find('label').contents().filter(function () {
            return this.nodeType == 3;
          }).remove();

          searchContainer.addClass('flex-grow-1');
          searchInput.addClass('w-100');
          searchInput.attr('placeholder', 'Personel Ara...');

          flexContainer.append(searchContainer);
          flexContainer.append(filterWrapper);

          // .append() yerine .html() kullanarak mevcut içeriği değiştiriyoruz
          topContainer.html(flexContainer);

          $('.searchWrap').css({ visibility: 'visible', opacity: 1 });
          // --- DEĞİŞTİRİLEN BÖLÜM SONU ---
        }
      });

      // Yazdır butonu click event'i
      $('#printStaffs').on('click', function() {
        table.button('.buttons-print').trigger();
      });
      $('#rolePers').change(function () {
        table.draw();
      });

      $('#durum').change(function () {
        table.draw();
      });

    });
  </script>

  <script>
    $(document).ready(function () {
      var dropdownContainer = $('#personelfiltre');
      var filterButton = dropdownContainer.find('.filtrele');
      dropdownContainer.on('show.bs.dropdown', function () {
        filterButton.html('Kapat <i class="mdi mdi-chevron-down"></i>');
      });
      dropdownContainer.on('hide.bs.dropdown', function () {
        filterButton.html('Filtrele <i class="mdi mdi-chevron-down"></i>');
      });
    });

    // Personel Banner Kapatma Fonksiyonu
    function closeStaffBanner() {
      const banner = document.getElementById('staffLimitBanner');
      if (banner) {
        banner.style.display = 'none';
      }
    }
  </script>

@endsection