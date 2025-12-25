@extends('frontend.secure.user_master')
@section('user')
  {{-- <link href="{{ asset('frontend/css/cash_transactions/all_cash_transaction.css') }}" rel="stylesheet" type="text/css" /> --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<div class="page-content" id="cash_transactions">
  <div class="container-fluid">
    <div class="row pageDetail">
      <div class="col-12">
        <div class="card">
          <div class="card-header sayfaBaslik">Kasa Hareketleri</div>
          <div class="card-body">
            <table id="datatableKasa" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
              <!-- Masaüstü -->
              <div class="d-none d-md-block">
                  <div class="cash-buttons-container">
                      @if(Auth::user()->can('Kasa Hareketi Ekleyebilir'))
                          <a class="btn btn-success btn-sm addCashTransactions" data-bs-toggle="modal" data-bs-target="#addCashTransactionsModal">
                              <i class="fas fa-plus"></i><span>Kasa Hareketi Ekle</span>
                          </a>
                      @endif
                      
                      <a href="javascript:void(0);" class="btn btn-warning btn-sm printCash">
                          <i class="fas fa-print"></i><span>Yazdır</span>
                      </a>
                  </div>
                  
                  <button type="button" class="btn btn-sm btn-dark searchBtn kasaArama float-end">
                      <i class="fas fa-search"></i>
                  </button>
              </div>

             <!-- Mobil -->
              <div class="d-md-none">
                  <div class="cash-buttons-container mb-2">
                      @if(Auth::user()->can('Kasa Hareketi Ekleyebilir'))
                          <a class="btn btn-success btn-sm addCashTransactions" data-bs-toggle="modal" data-bs-target="#addCashTransactionsModal">
                              <i class="fas fa-plus"></i><span>Kasa Hareketi Ekle</span>
                          </a>
                      @endif
                      
                      <a href="javascript:void(0);" class="btn btn-warning btn-sm printCash">
                          <i class="fas fa-print"></i><span>Yazdır</span>
                      </a>
                      
                      <button type="button" class="btn btn-sm btn-dark searchBtn kasaArama">
                          <i class="fas fa-search"></i><span></span>
                      </button>
                  </div>
              </div>
              <div class="searchWrap float-end">
                <div class="btn-group" id="kasaFilterDropdownContainer">
                  <button class="btn btn-dark btn-sm dropdown-toggle filtrele" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Filtrele <i class="mdi mdi-chevron-down"></i>
                  </button>
                  <div class="dropdown-menu">
                    <div class="item">
                      <div class="row">
                        <label class="col-4 col-sm-4">Ödeme Yönü:</label>
                        <div class="col-8 col-sm-8">
                          <select name="odeme_yonu" id="odemeYonu" class="form-select">
                            <option value="">Hepsi</option>
                            <option value="1">Gelen Ödeme(Borç)</option>
                            <option value="2">Giden Ödeme(Alacak)</option>
                          </select>

                        </div>
                      </div>
                      <div class="item">
                        <div class="row">
                          <label class="col-4 col-sm-4">Ödeme Türü:</label>
                          <div class="col-8 col-sm-8">
                            <select name="odeme_turu" id="odemeTuru" class="form-select">
                              <option value="">Hepsi</option>
                              @foreach($payment_types as $type)
                                <option value="{{$type->id}}">{{$type->odemeTuru}}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                      </div>
                      <div class="item hide-on-mobile">
                        <div class="row">
                          <label class="col-4 col-sm-4">Ödeme Şekli:</label>
                          <div class="col-8 col-sm-8">
                            <select name="odeme_sekil" id="odemeSekil" class="form-select">
                              <option value="">Hepsi</option>
                              @foreach($payment_methods as $method)
                                <option value="{{$method->id}}">{{$method->odemeSekli}}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                      </div>
                      <div class="item">
                        <div class="row">
                          <label class="col-4 col-sm-4">Durumu:</label>
                          <div class="col-8 col-sm-8">
                            <select name="odeme_durumu" id="odemeDurumu" class="form-select">
                              <option value="0">Hepsi</option>
                              <option value="2">Tamamlanmadı</option>
                              <option value="1">Tamamlandı</option>
                            </select>
                          </div>
                        </div>
                      </div>
                      <div class="item">
                        <div class="row">
                          <label class="col-4 col-sm-4">Personel:</label>
                          <div class="col-8 col-sm-8">
                            <select name="staff" id="staff" class="form-select">
                              <option value="">Hepsi</option>
                              @foreach($personel as $person)
                                <option value="{{$person->user_id}}">{{$person->name}}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="item hide-on-mobile">
                        <div class="row">
                          <label class="col-4 col-sm-4">Bayiler:</label>
                          <div class="col-8 col-sm-8">
                            <select name="bayi" id="bayi" class="form-select">
                              <option value="">Hepsi</option>
                              @foreach($bayiler as $bayi)
                                <option value="{{$bayi->user_id}}">{{$bayi->name}}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="item">
                        <div class="row">
                          <label class="col-4 col-sm-4">Tedarikçiler:</label>
                          <div class="col-8 col-sm-8">
                            <select name="tedarikci" id="tedarikci" class="form-select">
                              <option value="">Hepsi</option>
                              @foreach($tedarikciler as $item)
                                <option value="{{$item->id}}">{{$item->tedarikci}}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="item">
                        <div class="row">
                          <label class="col-4 col-sm-4">Marka:</label>
                          <div class="col-8 col-sm-8">
                            <select name="marka" id="marka" class="form-select">
                              <option value="">Hepsi</option>
                              @foreach($markalar as $item)
                                <option value="{{$item->id}}">{{$item->marka}}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="item">
                        <div class="row">
                          <label class="col-4 col-sm-4">Cihaz:</label>
                          <div class="col-8 col-sm-8">
                            <select name="cihaz" id="cihaz" class="form-select">
                              <option value="">Hepsi</option>
                              @foreach($cihazlar as $item)
                                <option value="{{$item->id}}">{{$item->cihaz}}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="item">
                        <div class="row">
                          <label class="col-4 col-sm-4">Tarih Aralığı:</label>
                          <div class="col-8 col-sm-8">
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
                    <th data-priority="2" style="width: 10px">Tarih</th>
                    <th data-priority="3" style="width: 20px">Personel</th>
                    <th style="width: 10px;">Türü</th>
                    <th>Açıklama</th>
                    <th style="width: 10px;">Şekli</th>
                    <th style="width: 90px;">Borç(Gelen)</th>
                    <th style="width: 90px;">Alacak(Giden)</th>
                    <th style="width: 90px;">Bakiye(Toplam)</th>
                    <th data-priority="1" style="width: 96px;">Düzenle</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>

              <div class="tableToplamaAlani kasaToplamaAlani">
                <div class="row r1">
                  <div class="sol"><strong>Borç</strong></div>
                  <div class="sag">
                    <div class="tur t1 gelenNakitTL"><span>Nakit: </span></div>
                    <div class="tur t2 gelenHavaleTL"><span>EFT/Havale: </span></div>
                    <div class="tur t3 gelenKartTL"><span>Kredi Kartı: </span></div>
                    <div class="tur t4 gelenToplamTL"><span>Toplam: </span></div>
                  </div>
                </div>
                <div class="row r2">
                  <div class="sol"><strong>Alacak</strong></div>
                  <div class="sag">
                    <div class="tur t1 gidenNakitTL"><span>Nakit: </span></div>
                    <div class="tur t2 gidenHavaleTL"><span>EFT/Havale: </span></div>
                    <div class="tur t3 gidenKartTL"><span>Kredi Kartı: </span></div>
                    <div class="tur t4 gidenToplamTL"><span>Toplam: </span></div>
                  </div>
                </div>
                <div class="row r4">
                  <div class="sol"><strong>Bakiye</strong></div>
                  <div class="sag">
                    <div class="tur t1 genelToplamTL"><span>Toplam: </span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div> <!-- end col -->
      </div> <!-- end row -->
    </div>
  </div>

  <!-- add modal content -->
  <div id="addCashTransactionsModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog custom-modal-width">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title" id="myModalLabel">Kasa Hareketi Ekle</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->

  <!-- add modal content -->
  <div id="cashTransactionStatisticsModal" class="modal fade bs-example-modal-xl" tabindex="-1" role="dialog"
    aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title" id="myModalLabel">Kasa İstatistikleri</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="padding: .5rem">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->

  <!-- edit modal content -->
  <div id="editCashTransactionsModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog custom-modal-width">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title" id="myModalLabel">Kasa Hareketi Düzenle</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->

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

  var mid = getUrlParameter('did');
  if(mid){
    $.ajax({
      url: "/kasa-hareketi/duzenle/"+ mid
    }).done(function(data) { 
      if($.trim(data)==="-1"){
        window.location.reload(true);
      }else{
        $('#editCashTransctionsModal').modal('show');
        $('#editCashTransactionsModal .modal-body').html(data);
      }
    });
  }
</script>

<script type="text/javascript">
$(document).ready(function(){
  $(".addCashTransactions").click(function(){
    var firma_id = {{$firma->id}};
    $.ajax({
      url: "/" + firma_id + "/kasa-hareketi/ekle/"
    }).done(function(data) {
      if ($.trim(data) === "-1") {
        window.location.reload(true);
      } else {
        $('#addCashTransactionsModal').modal('show');
        $('#addCashTransactionsModal .modal-body').html(data);
      }
    });
  });
  $("#addCashTransactionsModal").on("hidden.bs.modal", function() {
    $('#addCashTransactionsModal .modal-body').html("");
  });
});
</script>

<script type="text/javascript">
  $(document).ready(function(){
    // Edit Cash Transactions Modal - Buton click event'i (mobil ve masaüstü için gerekli)
    $('#datatableKasa').on('click', '.editCashTransactions', function(e){
      var id = $(this).attr("data-bs-id");
      var firma_id = {{$firma->id}};
      $.ajax({
        url: "/" + firma_id + "/kasa-hareketi/duzenle/" + id
      }).done(function(data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#editCashTransactionsModal').modal('show');
          $('#editCashTransactionsModal .modal-body').html(data);
        }
      });
    });

    // Mobilde ve masaüstünde satırın boş alanlarına tıklayınca da açılsın
    $('#datatableKasa tbody').on('click', 'tr', function(e) {
      var $target = $(e.target);
      
      // Düzenle butonuna tıklandıysa, bu tr event'ini çalıştırma (butonun kendi event'i çalışsın)
      if ($target.closest('.editCashTransactions').length > 0 ||
          $target.closest('.btn').length > 0 || 
          $target.closest('td').index() === 9) {
        return;
      }
      
      var id = $(this).find('.editCashTransactions').first().attr('data-bs-id');
      
      if (id) {
        // 1. MODAL'I HEMEN AÇ (AJAX beklemeden)
        $('#editCashTransactionsModal').modal('show');
        
        // 2. AYNI ANDA AJAX BAŞLAT
        var firma_id = {{$firma->id}};
        $.ajax({
          url: "/" + firma_id + "/kasa-hareketi/duzenle/" + id
        }).done(function(data) {
          if ($.trim(data) === "-1") {
            window.location.reload(true);
          } else {
            $('#editCashTransactionsModal .modal-body').html(data);
          }
        });
      }
    });

    $("#editCashTransactionsModal").on("hidden.bs.modal", function() {
      $('#editCashTransactionsModal .modal-body').html("");
    });
  });
</script>

<script type="text/javascript">
  $(document).ready(function(){
    $(".statistics").click(function(){
      $.ajax({
        url: "/kasa-istatistik/"
      }).done(function(data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#cashTransactionStatisticsModal').modal('show');
          $('#cashTransactionStatisticsModal .modal-body').html(data);
        }
      });
    });
  });
</script>

<script>
  $(document).ready(function () {
    var aramaZamanlayici; // Debounce için

    // ✅ AJAX ile dinamik müşteri arama
    $('#search').keyup(function () {
      var searchField = $(this).val();
      
      // Önceki zamanlayıcıyı iptal et
      clearTimeout(aramaZamanlayici);
      
      // Liste temizle
      $('#result').html('');
      
      if (searchField.length > 2) { // 3 karakterden sonra ara
        // 300ms bekle, ardından ara
        aramaZamanlayici = setTimeout(function() {
          $.ajax({
            url: "{{ route('search.customer.kasa', $firma->id) }}",
            method: "POST",
            data: {
              musteriGetir: searchField,
              _token: "{{ csrf_token() }}"
            },
            beforeSend: function() {
              $('#result').html('<li class="list-group-item text-muted">Aranıyor...</li>');
            },
            success: function (data) {
              $('#result').html('');
              
              if (data.length === 0) {
                $('#result').append('<li class="list-group-item text-muted">Sonuç bulunamadı</li>');
                return;
              }
              
              $.each(data, function (key, value) {
                var tip = value.musteriTipi == "1" ? "Bireysel" : "Kurumsal";
                var ilceAdi = value.state ? value.state.ilceName : '';
                var ilAdi = value.country ? value.country.name : '';
                
                // Adres formatla
                var adresDisplay = value.adres || '';
                if (ilceAdi || ilAdi) {
                  adresDisplay += (adresDisplay ? ' - ' : '') + ilceAdi + '/' + ilAdi;
                }
                
                $('#result').append(
                  '<li class="list-group-item link-class" ' +
                  'data-id="' + value.id + '" ' +
                  'data-adSoyad="' + (value.adSoyad || value.m_adi) + '" ' +
                  'data-firmaAdi="' + (value.firma_adi || '') + '" ' +
                  'data-tel="' + (value.tel1 || value.telefon) + '" ' +
                  'data-adres="' + adresDisplay + '">' +
                  '<span style="font-weight:500;">Ad Soyad: </span>' + (value.adSoyad || value.m_adi) + 
                  ' (' + (value.firma_adi || '') + ') <span style="color: #666;">(' + tip + ')</span><br>' +
                  '<span style="font-weight:500;">Telefon: </span>' + (value.tel1 || value.telefon) + '<br>' +
                  '<span style="font-weight:500;">Adres: </span>' + adresDisplay + 
                  '</li>'
                );
              });
            },
            error: function(xhr, status, error) {
              console.error('Arama hatası:', error);
              $('#result').html('<li class="list-group-item text-danger">Bir hata oluştu</li>');
            }
          });
        }, 300); // 300ms gecikme
        
      } else if (searchField.length === 0) {
        // Arama kutusu boşaltılırsa temizle
        $('#result').html('');
      }
    });

    // Müşteri seçme
    $('#result').on('click', 'li.link-class', function () {
      var click_id = $(this).attr('data-id');
      var click_adSoyad = $(this).attr('data-adSoyad');
      
      $('#alici').val(click_id);
      $('.mid').val(click_id);
      $('.musteriAdSoyad').val(click_adSoyad);
      $("#result").html('');
      
      // Tabloyu güncelle
      $('#datatableKasa').DataTable().draw();
    });

    // Dışarı tıklanınca kapat
    $(document).click(function (e) {
      if (!$(e.target).closest('#search, #result').length) {
        $("#result").html('');
      }
    });
  });
</script>
<script>
  $(document).ready(function () {
    // Bu bayrak, daterangepicker veya kısa yol butonlarına tıklandığında dropdown'ın kapanmasını engellemek için kullanılır.
    let preventDropdownHide = false;
    // Dropdown'ın kapanma olayını dinliyoruz
    $('#kasaFilterDropdownContainer').on('hide.bs.dropdown', function(e) {
        // Eğer bayrak 'true' ise, yani tıklama daterangepicker'dan geldiyse...
        if (preventDropdownHide) {
            e.preventDefault(); // Bootstrap'ın dropdown'ı kapatmasını engelle.
        }
        // Olay kontrol edildikten sonra bayrağı her zaman sıfırla ki bir sonraki normal tıklamada dropdown kapanabilsin.
        preventDropdownHide = false;
    });
    // daterangepicker'ın takvim arayüzü içindeki herhangi bir tıklamayı yakala
    $(document).on('mousedown', function(e) {
        // Eğer tıklama .daterangepicker sınıfına sahip bir elementin içindeyse...
        if ($(e.target).closest('.daterangepicker').length) {
            preventDropdownHide = true; // Bayrağı ayarla.
        }
    });
    // Dropdown içindeki daterangepicker input alanına tıklandığında bayrağı ayarla
    $('#kasaFilterDropdownContainer').find('#daterange').on('focus mousedown', function() {
        preventDropdownHide = true;
    });
    // Dropdown içindeki tarih kısayol butonlarına tıklandığında bayrağı ayarla
    $('#kasaFilterDropdownContainer').find('.tarihAraligi button').on('mousedown', function() {
        preventDropdownHide = true;
    });
    // daterangepicker "Uygula", "İptal" butonlarına basıldığında veya kapandığında bayrağı sıfırla.
    // Bu, daterangepicker ile işimiz bittikten sonra dropdown'ın normal şekilde kapanabilmesini sağlar.
    $('#daterange').on('apply.daterangepicker cancel.daterangepicker hide.daterangepicker', function() {
        preventDropdownHide = false;
    });


    // Tarih aralığı seçenekleri
    var lastYear = moment().subtract(1, 'year');
    var lastMonth = moment().subtract(1, 'month');
    var lastWeek = moment().subtract(7, 'days');
    var yesterday = moment().subtract(1, 'days');
    var today = moment();
    var baslangicYil = '01-01-2025';

    // Butonları oluştur ve tarih aralığını güncelle
    $('#lastYear').on('click', function() {
      $('#daterange').data('daterangepicker').setStartDate(lastYear);
      $('#daterange').data('daterangepicker').setEndDate(today);
      filterData();
    });

    $('#lastMonth').on('click', function() {
      $('#daterange').data('daterangepicker').setStartDate(lastMonth);
      $('#daterange').data('daterangepicker').setEndDate(today);
      filterData();
    });

    $('#lastWeek').on('click', function() {
      $('#daterange').data('daterangepicker').setStartDate(lastWeek);
      $('#daterange').data('daterangepicker').setEndDate(today);
      filterData();
    });

    $('#yesterday').on('click', function() {
      $('#daterange').data('daterangepicker').setStartDate(yesterday);
      $('#daterange').data('daterangepicker').setEndDate(yesterday);
      filterData();
    });

    $('#today').on('click', function() {
      $('#daterange').data('daterangepicker').setStartDate(today);
      $('#daterange').data('daterangepicker').setEndDate(today);
      filterData();
    });

    $('.kasaArama').on('click', function() {
      var baslangicYil = '01-01-2025';
      var today = moment();
      $('#daterange').data('daterangepicker').setStartDate(baslangicYil);
      $('#daterange').data('daterangepicker').setEndDate(today);
      filterData();
    });
        
    // Filtreleme fonksiyonu
    function filterData() {
      $('#datatableKasa').DataTable().draw();
    }
  });
</script>

<script>
  $(document).ready(function () {
    $('#kasaArama').click(function() {
      $('#baslangicYil').trigger('click');
    });
  });

  function setDateRangeToFull() {
    var baslangicYil = moment('01-01-2025', 'DD-MM-YYYY');
    var today = moment();
    $('#daterange').data('daterangepicker').setStartDate(baslangicYil);
    $('#daterange').data('daterangepicker').setEndDate(today);
  }

  function setDateRangeToToday() {
        var today = moment();
        $('#daterange').data('daterangepicker').setStartDate(today);
        $('#daterange').data('daterangepicker').setEndDate(today);
    }
</script>

<script>
  $(document).ready(function () {
    var start_date = moment();
    var end_date = moment();
    $('#daterange').daterangepicker({
      startDate : start_date,
      endDate : end_date,
      locale: {
        format: 'DD-MM-YYYY',
        separator: ' - ',
        applyLabel: 'Uygula',
        cancelLabel: 'İptal',
        weekLabel: 'H',
        daysOfWeek: ['Pz', 'Pzt', 'Sal', 'Çrş', 'Prş', 'Cm', 'Cmt'],
        monthNames: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'],
        firstDay: 1,
      }
    },
    function(start_date, end_date){
      $('#daterange').html(start_date.format('DD-MM-YYYY') + '-' + end_date.format('DD-MM-YYYY'));
      table.draw();
      updateValues();
    });

    // Dashboard istatistiklerinden gelen filtreyi kontrol et
    var dashboard_filter = getUrlParameter('dashboard_filter');
    var dashboard_istatistik_tarih1 = getUrlParameter('dashboard_istatistik_tarih1');
    var dashboard_istatistik_tarih2 = getUrlParameter('dashboard_istatistik_tarih2');

    if (dashboard_filter && dashboard_istatistik_tarih1 && dashboard_istatistik_tarih2) {
        $('#daterange').data('daterangepicker').setStartDate(moment(dashboard_istatistik_tarih1));
        $('#daterange').data('daterangepicker').setEndDate(moment(dashboard_istatistik_tarih2));
    }


    var table = $('#datatableKasa').DataTable({
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
        url: "{{ route('kasa.filter',$firma->id) }}",
        data: function(data) {
          data.search = $('input[type="search"]').val();
          data.odemeTuru = $('#odemeTuru').val();
          data.from_date = $('#daterange').data('daterangepicker').startDate.format('YYYY-MM-DD');
          data.to_date = $('#daterange').data('daterangepicker').endDate.format('YYYY-MM-DD');
          data.customer = $('#customer').val();
          data.odemeSekil = $('#odemeSekil').val();
          data.staff = $('#staff').val();
          data.tedarikci = $('#tedarikci').val();
          data.marka = $('#marka').val();
          data.cihaz = $('#cihaz').val();
          data.odemeYonu = $('#odemeYonu').val();
          data.musteri = $('.mid').val();
          data.odemeDurum = $('#odemeDurumu').val();
          data.bayi = $('#bayi').val();

          //Dashboard kasa filtreleri
          data.dashboard_filter = getUrlParameter('dashboard_filter');
          data.dashboard_istatistik_tarih1 = getUrlParameter('dashboard_istatistik_tarih1');
          data.dashboard_istatistik_tarih2 = getUrlParameter('dashboard_istatistik_tarih2');
        }
      },
      'columns': [
        { data: 'id' },
        { data: 'created_at' },
        { data: 'pid' },
        { data: 'odemeTuru', orderable: false },
        { data: 'aciklama' },
        { data: 'odemeSekli' },
        { data: 'odemeYonuBorc', orderable:false },
        { data: 'odemeYonuAlacak', orderable:false},
        { data: 'fiyat' },
        { data: 'action', orderable: false}           
      ],
      drawCallback: function(settings) {
        $(".dataTables_paginate > .pagination").addClass("pagination-rounded");    
      },
      order: [[1, 'desc']],
      "columnDefs": [{
        "targets": 0,
        "className": "gizli"
      }],
      "oLanguage": {
        "sDecimal":        ",",
        "sEmptyTable":     "Tabloda herhangi bir veri mevcut değil",
        "sInfo":           "Kasa Hareketi Sayısı: _TOTAL_",
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
    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
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
        data = data.replace(/Personel\s*:/gi, '');
        data = data.replace(/Türü\s*:/gi, '');
        data = data.replace(/Açıklama\s*:/gi, '');
        data = data.replace(/Şekli\s*:/gi, '');
        data = data.replace(/Ö. /gi, '');
        data = data.replace(/Borç\s*\(Gelen\)\s*:/gi, '');
        data = data.replace(/Alacak\s*\(Giden\)\s*:/gi, '');
        data = data.replace(/Bakiye\s*\(Toplam\)\s*:/gi, '');
        
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
      '.print-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px;}' +
      '.print-title { text-align: left; font-size: 18px; font-weight: bold; margin-bottom: 13px; }' +
      'table { width: 100%; border-collapse: collapse; }' +
      'table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; color: #000 !important; font-size: 11px; }' +
      'table thead { display: table-header-group !important; }' +
      'table tbody { display: table-row-group !important; }' +
      'table tbody td * { color: #000 !important; font-weight: normal !important; }' +
      'table tbody td span { color: #000 !important; background-color: transparent !important; font-weight: normal !important; }' +
      'table tbody td { font-weight: normal !important; }' +
      'a, a:link, a:visited, a:hover, a:active { color: #000 !important; text-decoration: none !important; }' +
      '.print-footer { margin-top: 15px; text-align: left; border-top: 1px solid #ddd; padding-top: 10px; }' +
      '.page-number-bottom { text-align: center; margin-top: 30px; font-size: 14px; color: #666; font-weight: bold; }' +
      '@page { margin: 5mm; size: A4 landscape; }' + // LANDSCAPE (Yatay) - Çok kolonlu tablolar için
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
    
    var title = '<div class="print-title">Kasa Hareketleri</div>';
    $(win.document.body).find('table').before(title);
    
    var footer = '<div class="print-footer">' +
                '  <span>Listelenen Hareket Sayısı: ' + totalRecords + ' - Tarih: ' + moment().format('DD/MM/YYYY') + '</span>' +
                '</div>';
    $(win.document.body).find('table').after(footer);
    
    var pageInfo = '<div class="page-number-bottom">1/1</div>';
    $(win.document.body).append(pageInfo);
  }
}],
      "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "Tümü"] ],
      "initComplete": function(settings, json) {

    var searchContainer = $('#datatableKasa_filter');
    var searchInput = searchContainer.find('input');
    var filterWrapper = $('.searchWrap');
    var flexContainer = $('<div class="d-flex justify-content-end w-100"></div>');


    // --- DEĞİŞTİRİLEN BÖLÜM BAŞLANGICI ---

    // 1. Sadece masaüstü görünümündeki arama butonunu bul.
    var kasaAramaButton = $('.d-none.d-md-block .kasaArama');

    // 2. Filtrele ve arama butonunu yan yana getirmek için ana sarmalayıcıya flex özellikleri ekle.
    filterWrapper.addClass('d-flex align-items-center');

    // 3. Bulunan arama butonunu, "Filtrele" butonunu içeren sarmalayıcının içine taşı.
    // .append() ile sona eklenir, yani "Filtrele" butonunun sağına gelir.
    filterWrapper.append(kasaAramaButton);

    // --- DEĞİŞTİRİLEN BÖLÜM SONU ---

    // Varsayılan "Search:" etiketini kaldır
    searchContainer.find('label').contents().filter(function() {
        return this.nodeType == 3;
    }).remove();

    // Arama kutusunu ve filtreyi sarmalamak için
    searchContainer.addClass('flex-grow-1 ');
    searchInput.addClass('w-100');
    searchInput.attr('placeholder', 'Kasa Hareketi Ara...');

    // Ögeleri flex container'a ekle
    flexContainer.append(searchContainer);
    flexContainer.append(filterWrapper); // filterWrapper artık hem "Filtrele" hem de arama butonunu içeriyor.

    // Flex container'ı tablonun üstüne ekle
    $('#datatableKasa_wrapper .top').append(flexContainer);

    // Hazır olduğunda görünür yap
    $('.searchWrap').css({ visibility: 'visible', opacity: 1 });
     $('.tableToplamaAlani').insertBefore('#datatableKasa_wrapper .bottom');
}
    });

    // Kullanıcının filtreleme yaptığını takip etmek için flag
    var userHasFiltered = false;

    // Filtre değişikliklerini dinle
    const filtreElementleri = ['#odemeTuru', '#customer', '#odemeSekil', '#staff', '#odemeYonu', '#tedarikci','#marka','#cihaz','#odemeDurumu','#bayi'];

    filtreElementleri.forEach(function(id) {
        $(id).change(function() {
            var selectedValue = $(this).val();
            
            // Kullanıcı bir filtre seçti (boş değil)
            if (selectedValue && selectedValue !== '' && selectedValue !== '0') {
                userHasFiltered = true;
                // Filtre seçildiğinde tarih aralığını genişlet
                setDateRangeToFull();
            } 
            // Kullanıcı "Hepsi" seçti
            else {
                // Eğer daha önce filtreleme yapılmışsa, tarih aralığını geniş tut
                if (userHasFiltered) {
                    setDateRangeToFull();
                }
                // Eğer hiç filtreleme yapılmamışsa, bugünkü tarihe dön
                else {
                    setDateRangeToToday();
                }
            }
            
            table.draw();
        });
    });

    $('#result').on('click', 'li', function () {
      var selectedCustomerId = $(this).attr('data-id');
      table.column('musteri:name').search(selectedCustomerId).draw();
    });

    table.on('draw.dt', function () {
      updateValues();
    });

    // Yazdır butonu click event'i
    $('.printCash').on('click', function(e) {
      e.preventDefault();
      table.button('.buttons-print').trigger();
    });

    var updateValues = function() {
      var startDate = $('#daterange').data('daterangepicker').startDate.format('YYYY-MM-DD');
      var endDate = $('#daterange').data('daterangepicker').endDate.format('YYYY-MM-DD');
      var odemeTuru = $('#odemeTuru').val();
      var odemeYonu = $('#odemeYonu').val();
      var customer = $('#customer').val();
      var odemeSekil = $('#odemeSekil').val();
      var staff = $('#staff').val();
      var tedarikci = $('#tedarikci').val();
      var marka = $('#marka').val();
      var cihaz = $('#cihaz').val();
      var musteri = $('.mid').val();
      var odemeDurum = $('#odemeDurumu').val();
      var bayi = $('#bayi').val();
      $.ajax({
        url: '/{{$firma->id}}/kasa-toplam',
        method: 'GET',
        data: {
          from_date: startDate,
          to_date: endDate,
          odemeTuru:odemeTuru,
          odemeYonu:odemeYonu,
          customer:customer,
          odemeSekil:odemeSekil,
          staff:staff,
          tedarikci:tedarikci,
          marka:marka,
          cihaz:cihaz,
          musteri:musteri,
          odemeDurum:odemeDurum,
          bayi:bayi,
        },
        success: function(response) {
          $('.gelenNakitTL').html('<span>Nakit:</span> ' + response.gelenNakitTL);
          $('.gelenHavaleTL').html('<span>EFT/Havale:</span> ' + response.gelenHavaleTL);
          $('.gelenKartTL').html('<span>Kredi Kartı:</span> ' + response.gelenKartTL);
          $('.gelenToplamTL').html('<span>Toplam:</span> ' + response.gelenToplamTL);
          $('.gidenNakitTL').html('<span>Nakit:</span> ' + response.gidenNakitTL);
          $('.gidenHavaleTL').html('<span>EFT/Havale:</span> ' + response.gidenHavaleTL);
          $('.gidenKartTL').html('<span>Kredi Kartı:</span> ' + response.gidenKartTL);
          $('.gidenToplamTL').html('<span>Toplam:</span> ' + response.gidenToplamTL);
          $('.genelToplamTL').html('<span>Toplam:</span> ' + response.genelToplamTL);
        },
        error: function(xhr, status, error) {
          console.error(error);
        }
      });
    }
  });
</script>

@endsection