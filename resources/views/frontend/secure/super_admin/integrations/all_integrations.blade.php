@extends('frontend.secure.user_master')
@section('user')
  <!--<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>-->
  <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

  <div class="page-content" id="allIntegrationsPage">
    <div class="container-fluid">
      <div class="row pageDetail">
        <div class="col-12">
          <div class="card card-invocies">
            <div class="card-header card-invocies-header sayfaBaslik">
              Entegrasyonlar
            </div>
            <div class="card-body card-invocies-body">
              <table id="datatableIntegration" class="table table-bordered dt-responsive nowrap"
                style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                <a class="btn btn-success btn-sm addIntegration" data-bs-toggle="modal" data-bs-target="#addIntegrationModal"><i
                    class="fas fa-plus"></i><span>Entegrasyon Ekle</span></a>
                <div class="searchWrap float-end">
                  <div class="btn-group">
                    <button class="btn btn-dark btn-sm dropdown-toggle filtrele" type="button" data-bs-toggle="dropdown"
                      aria-expanded="false">
                      Filtrele <i class="mdi mdi-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu">
                      <div class="item">
                        <div class="row form-group">
                          <label class="col-sm-4 col-4 custom-p-m-m">Kategoriler<span
                              style="font-weight: bold; color: red;">*</span></label>
                          <div class="col-md-8 col-8 custom-p-m-m">
                            <select name="kategori" id="kategori" class="form-select" required>
                              <option value="" selected >-Seçiniz-</option>
                              <option value="invoice">Fatura</option>
                              <option value="sms">SMS</option>
                              <option value="santral">Santral</option>
                              <option value="accounting">Muhasebe</option>
                              <option value="other">Diğer</option>
                            </select>
                          </div>
                        </div>
                      </div>
                      <div class="item">
                        <div class="row form-group">
                          <div class="col-sm-4 col-4 custom-p-m-m"><label>Durum</label></div>
                          <div class="col-md-8 col-8 custom-p-m-m">
                            <select class="form-select" name="entegreDurumu" id="entegreDurumu">
                              <option value="">Hepsi</option>
                              <option value="1">Aktif</option>
                              <option value="0">Pasif</option>
                            </select>
                          </div>
                        </div>
                      </div>
                      <div class="item">
                        <div class="row">
                          <label class="col-sm-4 col-4 custom-p-m-m">Tarih Aralığı:</label>
                          <div class="col-sm-8 col-8 custom-p-m-m">
                            <input id="daterange" class="tarih-araligi">
                            <div class="tarihAraligi mt-2 mb-2">
                              <button id="lastYear" class="btn btn-sm btn-secondary">Son 1 Yıl</button>
                              <button id="lastMonth" class="btn btn-sm btn-secondary">Son 1 Ay</button>
                              <button id="lastWeek" class="btn btn-sm btn-secondary">Son 7 Gün</button>
                              <button id="yesterday" class="btn btn-sm btn-secondary">Dün</button>
                              <button id="today" class="btn btn-sm btn-secondary">Bugün</button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div><!-- /btn-group -->
                </div>
                <thead class="title">
                  <tr>
                    <th style="width: 10px">ID</th>
                    <th style="width: 10px">Tarih</th>
                    <th>Entegrasyon Adı</th>
                    <th style="width: 250px">Kategori</th>
                    <th>Fiyat</th>
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
  <div id="addIntegrationModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-width: 830px;">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title" id="myModalLabel">Entegrasyon Ekle</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->

  <!-- edit modal content -->
  <div id="editIntegrationModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-width: 830px;">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title" id="myModalLabel">Entegrasyon Düzenle</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->

<div class="modal fade" id="apiFieldsModal" tabindex="-1" aria-labelledby="apiFieldsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="apiFieldsModalLabel">API Form Alanları Örnekleri</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="sms-tab-btn" data-bs-toggle="tab" data-bs-target="#sms-tab" type="button" role="tab">SMS (NETGSM)</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="accounting-tab-btn" data-bs-toggle="tab" data-bs-target="#accounting-tab" type="button" role="tab">Muhasebe (Paraşüt)</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="invoice-tab-btn" data-bs-toggle="tab" data-bs-target="#invoice-tab" type="button" role="tab">e-Fatura</button>
                    </li>
                </ul>
                
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="sms-tab" role="tabpanel">
<pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto;"><code id="sms-code">[
    {
        "name": "username",
        "label": "API Kullanıcı Adı",
        "type": "text",
        "placeholder": "850xxxxxxx",
        "required": true,
        "help": "NETGSM panelinden aldığınız kullanıcı adı"
    },
    {
        "name": "password",
        "label": "API Şifresi",
        "type": "password",
        "placeholder": "••••••••",
        "required": true,
        "help": "NETGSM panel şifreniz"
    },
    {
        "name": "sender_name",
        "label": "Gönderici Başlığı",
        "type": "text",
        "placeholder": "FIRMADI",
        "required": false,
        "help": "SMS gönderici adı (max 11 karakter)"
    }
]</code></pre>
                        <button class="btn btn-sm btn-primary" onclick="copyExample('sms-code')">
                            <i class="fas fa-copy"></i> Kopyala ve Kullan
                        </button>
                    </div>
                    
                    <div class="tab-pane fade" id="accounting-tab" role="tabpanel">
<pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto;"><code id="accounting-code">[
    {
        "name": "company_id",
        "label": "Şirket ID",
        "type": "text",
        "placeholder": "123456",
        "required": true,
        "help": "Paraşüt hesabınızdaki şirket ID"
    },
    {
        "name": "client_id",
        "label": "Client ID",
        "type": "text",
        "required": true
    },
    {
        "name": "client_secret",
        "label": "Client Secret",
        "type": "password",
        "required": true
    }
]</code></pre>
                        <button class="btn btn-sm btn-primary" onclick="copyExample('accounting-code')">
                            <i class="fas fa-copy"></i> Kopyala ve Kullan
                        </button>
                    </div>
                    
                    <div class="tab-pane fade" id="invoice-tab" role="tabpanel">
<pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto;"><code id="invoice-code">[
    {
        "name": "username",
        "label": "Kullanıcı Adı",
        "type": "text",
        "required": true
    },
    {
        "name": "password",
        "label": "Şifre",
        "type": "password",
        "required": true
    },
    {
        "name": "vkn_tckn",
        "label": "VKN / TCKN",
        "type": "text",
        "placeholder": "10 veya 11 haneli",
        "required": true,
        "help": "Vergi Kimlik No veya TC Kimlik No"
    },
    {
        "name": "integration_type",
        "label": "Entegrasyon Tipi",
        "type": "select",
        "options": {
            "test": "Test Ortamı",
            "prod": "Canlı Ortam"
        },
        "required": true
    }
]</code></pre>
                        <button class="btn btn-sm btn-primary" onclick="copyExample('invoice-code')">
                            <i class="fas fa-copy"></i> Kopyala ve Kullan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <script type="text/javascript">
      $(document).ready(function () {
        $(".addIntegration").click(function () {
          $.ajax({
            url: "{{ route('super.admin.integration.add') }}"
          }).done(function (data) {
            if ($.trim(data) === "-1") {
              window.location.reload(true);
            } else {
              $('#addIntegrationModal').modal('show');
              $('#addIntegrationModal .modal-body').html(data);

              setTimeout(function() {
                initTinyMCE('#addIntegrationModal #elm1');
              }, 100);
            }
          });
        });
        $("#addIntegrationModal").on("hidden.bs.modal", function () {
          if (tinymce.get('elm1')) {
            tinymce.get('elm1').remove();
          }
          $('#addIntegrationModal .modal-body').html("");
        });
      });
    </script>

    <script type="text/javascript">
      $(document).ready(function () {
        $('#datatableIntegration').on('click', '.editIntegration', function (e) {
          var id = $(this).attr("data-bs-id");
          $.ajax({
            url: "{{ route('super.admin.integration.edit', '') }}/" + id
          }).done(function (data) {
            if ($.trim(data) === "-1") {
              window.location.reload(true);
            } else {
              $('#editIntegrationModal').modal('show');
              $('#editIntegrationModal .modal-body').html(data);

              setTimeout(function() {
                initTinyMCE('#editIntegrationModal #elm1');
              }, 100);
            }
          });
        });
        $("#editIntegrationModal").on("hidden.bs.modal", function () {
          if (tinymce.get('elm1')) {
            tinymce.get('elm1').remove();
          }
          $('#editIntegrationModal .modal-body').html("");
        });
      });
    </script>
    <script>
  // TinyMCE başlatma fonksiyonu
  function initTinyMCE(selector) {
    // Eğer TinyMCE zaten varsa kaldır
    if (tinymce.get('elm1')) {
      tinymce.get('elm1').remove();
    }
    
    tinymce.init({
      selector: selector,
      height: 300,
      language: 'tr',
      plugins: [
        'advlist', 'autolink', 'link', 'image', 'lists', 'charmap', 'preview', 'anchor', 'pagebreak',
        'searchreplace', 'wordcount', 'visualblocks', 'code', 'fullscreen', 'insertdatetime', 'media', 
        'table', 'emoticons', 'help'
      ],
      toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright alignjustify | ' +
        'bullist numlist outdent indent | link image | print preview media fullscreen | ' +
        'forecolor backcolor emoticons | help',
      menu: {
        favs: {title: 'Favoriler', items: 'code visualaid | searchreplace | emoticons'}
      },
      menubar: 'favs file edit view insert format tools table help',
      content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }',
      setup: function(editor) {
        editor.on('init', function() {
          console.log('TinyMCE başlatıldı');
        });
      }
    });
  }
</script>
    <script>
      $(document).ready(function () {
        // Tarih aralığı seçenekleri
        var lastYear = moment().subtract(1, 'year');
        var lastMonth = moment().subtract(1, 'month');
        var lastWeek = moment().subtract(7, 'days');
        var yesterday = moment().subtract(1, 'days');
        var today = moment();

        // Butonları oluştur ve tarih aralığını güncelle
        $('#lastYear').on('click', function () {
          $('#daterange').data('daterangepicker').setStartDate(lastYear);
          $('#daterange').data('daterangepicker').setEndDate(today);
          filterData();
        });

        $('#lastMonth').on('click', function () {
          $('#daterange').data('daterangepicker').setStartDate(lastMonth);
          $('#daterange').data('daterangepicker').setEndDate(today);
          filterData();
        });

        $('#lastWeek').on('click', function () {
          $('#daterange').data('daterangepicker').setStartDate(lastWeek);
          $('#daterange').data('daterangepicker').setEndDate(today);
          filterData();
        });

        $('#yesterday').on('click', function () {
          $('#daterange').data('daterangepicker').setStartDate(yesterday);
          $('#daterange').data('daterangepicker').setEndDate(yesterday);
          filterData();
        });

        $('#today').on('click', function () {
          $('#daterange').data('daterangepicker').setStartDate(today);
          $('#daterange').data('daterangepicker').setEndDate(today);
          filterData();
        });

        // Filtreleme fonksiyonu
        function filterData() {
          $('#datatableIntegration').DataTable().draw();
        }
      });
    </script>

    <script>
      $(document).ready(function () {

        var start_date = '01-01-2025';
        var end_date = moment().add(1, 'day');

        $('#daterange').daterangepicker({
          startDate: start_date,
          endDate: end_date,
          opens: 'right',
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
            $('#daterange').html(start_date.format('DD-MM-YYYY') + '-' + end_date.format('DD-MM-YYYY'));
            table.draw();
          });

        var table = $('#datatableIntegration').DataTable({
          processing: true,
          serverSide: true,
          language: {
            paginate: {
              previous: "<i class='mdi mdi-chevron-left'>",
              next: "<i class='mdi mdi-chevron-right'>"
            }
          },
          ajax: {
            url: "{{ route('super.admin.integrations') }}",
            data: function (data) {
              data.search = $('input[type="search"]').val();
              data.from_date = $('#daterange').data('daterangepicker').startDate.format('YYYY-MM-DD');
              data.to_date = $('#daterange').data('daterangepicker').endDate.format('YYYY-MM-DD');
              data.kategori = $('#kategori').val();
              data.entegreDurumu = $('#entegreDurumu').val();
            }
          },
          'columns': [
            { data: 'id' },
            { data: 'created_at' },
            { data: 'name' },
            { data: 'category' },
            { data: 'price' },
            { data: 'actions' }
          ],

          drawCallback: function () {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
          },
          "order": [[1, 'desc']],
          "columnDefs": [
            {
              "targets": 0,
              "className": "gizli"
            }
          ],
          "oLanguage": {
            "sDecimal": ",",
            "sEmptyTable": "Tabloda herhangi bir veri mevcut değil",
            "sInfo": "Entegrasyon Sayısı: _TOTAL_",
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
          dom: '<"top"f>rt<"bottom"i<"float-end invoices-filtre"lp>><"clear">',
          "lengthMenu": [[25, 50, 100, -1], [25, 50, 100, "Tümü"]],
          "initComplete": function (settings, json) {
            var searchContainer = $('#datatableIntegration_filter');
            var searchInput = searchContainer.find('input');
            var filterWrapper = $('.searchWrap');
            var flexContainer = $('<div class="d-flex justify-content-end w-100"></div>');
            searchContainer.find('label').contents().filter(function () {
              return this.nodeType == 3;
            }).remove();
            searchContainer.addClass('flex-grow-1 me-2');
            searchInput.addClass('w-100');
            searchInput.attr('placeholder', 'Entegrasyon Ara...');
            flexContainer.append(searchContainer);
            flexContainer.append(filterWrapper);
            $('#datatableIntegration_wrapper .top').append(flexContainer);
            $('.searchWrap').css({ visibility: 'visible', opacity: 1 });

          }


        });

        $('#kategori').change(function () {
          table.draw();
        });

        $('#entegreDurumu').change(function () {
          table.draw();
        });

        
      });
    </script>
@endsection