<div class="container">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @if($service_resources->isEmpty())
    <!-- Servis Kaynağı Yoksa Uyarı -->
    <div class="alert alert-warning d-flex align-items-center justify-content-between" role="alert" style="margin-bottom: 15px; padding: 10px 15px;">
      <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-triangle" style="font-size: 20px; margin-right: 10px;"></i>
        <span style="margin: 0; font-size: 14px;">
          Servis eklemek için önce <strong>Servis Kaynağı</strong> eklemeniz gerekiyor.
        
          Örneğin; <strong>444 0 000, Google Formlar, Sabit Telefon veya Çağrı Merkezi Hattı</strong> vb.
        </span>
      </div>
      <button class="btn btn-success btn-sm" type="button" id="addNewServiceResourceBtn" style="white-space: nowrap; margin-left: 15px;">
        <i class="fas fa-plus"></i> Kaynak Ekle
      </button>
    </div>
  @endif
  <form method="post" id="servisEkle" class="servisModal" action="{{ route('store.service', $firma->id) }}">
    @csrf
    <input type="hidden" name="form_token" id="formToken" value="">
    <div class="card" style="margin-bottom: 10px;">
      <div class="card-header ch1" >
        <div class="row">
          <div class="col-sm-6 c1">
            <label style="text-align: left;width: auto;display: inline-block;margin: 0;">Kayıt Tarihi: </label>
            <input type="text" class="form-control" disabled value="{{ date('d/m/Y H:i') }}" style="width: 120px;display: inline-block;">
            <label class="servisAcilLabel servisAcilBtn" style="user-select: none;-ms-user-select: none;-moz-user-select: none;-webkit-user-select: none;-webkit-touch-callout: none;position: relative;margin: 0; color: #fff; background: #343a40; border: 1px solid #212529;padding: 0 5px;border-radius: 3px;height: 24px;top: 1px;line-height: 24px;">
              <span>Acil</span>
              <input type="checkbox" style="display: none;">
              <div class="checkmark"><i class="fas fa-check"></i></div>
            </label>
            <input type="hidden" name="acil" class="acil" value="0"/>
          </div>

         <!-- Servis Kaynağı - Card Header'da -->
        <div class="col-sm-6 c2" style="text-align: right;">
          <label style="text-align: left; width: auto; display: inline-block; margin: 0; margin-right: 5px;">
            Servis Kaynağı:
          </label>
          
          @if(!$service_resources->isEmpty() && auth()->user()->hasRole('Patron'))

            <button class="btn btn-success btn-sm" type="button" id="addNewServiceResourceBtn" 
                    title="Yeni Kaynak Ekle" style="margin-right: 5px;">
              <i class="fas fa-plus"></i>
            </button>
          @endif
          
          <select class="form-select servisReso" name="servisReso" 
                  style="width: 152px; display: inline-block;" required>
            <option value="">SEÇİNİZ</option>
            @foreach($service_resources as $item)
              <option value="{{ $item->id }}">{{ $item->kaynak }}</option>
            @endforeach
          </select>
        </div>
        </div>
      </div>
    </div>     
        
    <div class="row row1">
      <div class="col-sm-6 c1">
        <div class="card">
          <div class="card-header" style="font-size:13px;">MÜŞTERİ BİLGİSİ
            <span class="musteriCikart"><i class="mid"></i> <i class="fas fa-times-circle"></i></span>
            <input type="hidden" name="eskiMusteriId" class="eskiMusteriId">
            <div class="clearfix"></div>
          </div>
          <div class="card-body custom-padding-bottom">
            <div class="row form-group ">
              <div class="col-md-4 rw1"><label>Müşteri Tipi <span style="font-weight: bold; color: red;">*</span></label></div>
                <div class="col-md-8 rw2">
                  <select class="form-control form-select musteriTipi" id="musteriTipiSelect" name="musteriTipi" required>
                    <option value="1">BİREYSEL</option>
                    <option value="2">KURUMSAL</option>
                  </select>
                </div>
              </div>
              <div class="row form-group">
                <div class="col-md-4 rw1"><label><span class="musteriAdiSpan">Müşteri Adı</span> <span style="font-weight: bold; color: red;">*</span></label></div>
                  <div class="col-md-8 rw2">
                    <input id="search" type="text" name="adSoyad" class="form-control buyukYaz adSoyad" style="text-transform: uppercase;" data-id="" autocomplete="off" placeholder="Müşteri Adı" required>
                    <input type="hidden" name="alici" id="alici" />
                    <ul id="result" style="margin: 0;padding: 0"></ul>
                  </div>
                </div>
                <div class="row form-group">
                  <div class="col-md-4 rw1"><label>Telefon <span style="font-weight: bold; color: red;">*</span></label></div>
                  <div class="col-md-4 col-6 custom-p-r-m-md rw2"><input type="text" name="tel1" class="form-control phone tel1" autocomplete="off" placeholder="TELEFON 1" required></div>
                  <div class="col-md-4 col-6 custom-p-m-md rw2"><input type="text" name="tel2" class="form-control phone tel2" autocomplete="off" placeholder="TELEFON 2"></div>
                </div>
                <div class="row form-group">
                  <div class="col-md-4 rw1"><label>İl/İlçe <span style="font-weight: bold; color: red;">*</span></label></div>
                  <div class="col-md-4 col-6 custom-p-r-m-md rw2">
                    <select class="form-control form-select il" name="il" id="countrySelect" required>
                      <option value="">-SEÇİNİZ-</option>
                      @foreach($iller as $il)
                        <option value="{{ $il->id }}" {{ $il->id == 34 ? 'selected' : '' }}>{{ $il->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-4 col-6 custom-p-m-md rw2">
                    <select class="form-control form-select ilce" name="ilce" id="citySelect" required>
                      <option value="">-İLÇE SEÇİNİZ-</option>
                    </select>
                  </div>
                </div>
                <div class="row form-group">
                  <div class="col-md-4 rw1"><label>Adres <span style="font-weight: bold; color: red;">*</span></label></div>
                  <div class="col-md-8 rw2"><textarea name="adres" class="form-control buyukYaz adres" placeholder="Adres" rows="4" style="resize: none !important;text-transform:uppercase;" required></textarea></div>
                </div>
                <!-- TC No - Bireysel için -->
                <div class="row form-group" id="tcNoRow" style="border: 0;margin-bottom: 0">
                  <div class="col-md-4 rw1"><label>T.C. No</label></div>
                  <div class="col-md-8 rw2">
                    <input type="text" name="tcNo" id="tcKimlik" class="form-control tcNo" autocomplete="off" placeholder="TC">
                    
                  </div>
                </div>
                
                <!-- Vergi No - Kurumsal için -->
                <div class="row form-group" id="vergiBoxRow" style="border: 0;margin-bottom: 0; display: none;">
                  <div class="col-md-4 rw1"><label>Vergi No/Dairesi <span style="font-weight: bold; color: red;">*</span></label></div>
                  <div class="col-md-4 col-6 rw2">
                    <input type="text" name="vergiNo" id="vergiNo" class="form-control vergiNo" placeholder="Vergi No" autocomplete="off">
                    @if(isset($hasInvoiceIntegration) && $hasInvoiceIntegration)
                      <small class="text-danger" style="display: block; margin-top: 5px;">
                        <i class="fas fa-exclamation-triangle"></i> Kurumsal müşteriler için zorunlu
                      </small>
                    @endif
                  </div>
                  <div class="col-md-4 col-6 rw2">
                    <input type="text" name="vergiDairesi" id="vergiDairesi" class="form-control buyukYaz vergiDairesi" style="text-transform: uppercase;" placeholder="Vergi Dairesi" autocomplete="off">
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 c2">
            <div class="card" style="margin-bottom: 5px;">
              <div class="card-header" style="font-size:13px;">CİHAZ BİLGİSİ</div>
              <div class="card-body custom-padding-bottom">
                <!-- Cihaz Markası -->
                <div class="row form-group">
                  <div class="col-md-4 rw1">
                    <label>Cihaz Markası <span style="font-weight: bold; color: red;">*</span></label>
                  </div>
                  <div class="col-md-8 rw2">
                    <div class="d-flex align-items-center gap-2">
                      @if(auth()->user()->hasRole('Patron'))
                        <button class="btn btn-success flex-shrink-0" type="button" 
                                id="addNewDeviceBrandBtn" title="Yeni Marka Ekle">
                          <i class="fas fa-plus"></i>
                        </button>
                      @endif
                      <select class="form-select cihazMarka flex-grow-1" name="cihazMarka" required>
                        <option value="">-SEÇİNİZ-</option>
                        @foreach($device_brands as $marka)
                          <option value="{{ $marka->id }}">{{ $marka->marka }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                </div>

                <!-- Cihaz Türü -->
                <div class="row form-group">
                  <div class="col-md-4 rw1">
                    <label>Cihaz Türü <span style="font-weight: bold; color: red;">*</span></label>
                  </div>
                  <div class="col-md-8 rw2">
                    <div class="d-flex align-items-center gap-2">
                      @if(auth()->user()->hasRole('Patron'))
                        <button class="btn btn-success flex-shrink-0" type="button" 
                                id="addNewDeviceTypeServiceBtn" title="Yeni Cihaz Türü Ekle">
                          <i class="fas fa-plus"></i>
                        </button>
                      @endif
                      <select class="form-select cihazTur flex-grow-1" name="cihazTur" required>
                        <option value="">-SEÇİNİZ-</option>
                        @foreach($device_types as $cihaz)
                          <option value="{{ $cihaz->id }}">{{ $cihaz->cihaz }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                </div>
                <div class="row form-group">
                  <div class="col-md-4 rw1"><label>Cihaz Modeli</label></div>
                  <div class="col-md-8"><input type="text" name="cihazModel" class="form-control" autocomplete="off"></div>
                </div>
                <div class="row form-group">
                  <div class="col-md-4 rw1"><label>Cihaz Arızası <span style="font-weight: bold; color: red;">*</span></label></div>
                  <div class="col-md-8">
                    <input id="arizaSearch" type="text" name="cihazAriza" class="form-control buyukYaz cihazAriza" style="text-transform: uppercase;" autocomplete="off" required>
                    <ul id="arizaResult" style="margin: 0;padding: 0"></ul>
                  </div>
                </div>
                <div class="row form-group">
                  <div class="col-md-4 rw1"><label>Operatör Notu</label></div>
                  <div class="col-md-8"><input type="text" name="opNot" class="form-control opNot" autocomplete="off"></div>
                </div>
                <div class="row form-group" style="margin-bottom: 0; border: 0;">
                  <div class="col-md-4 rw1"><label>Garanti Süresi</label></div>
                  <div class="col-md-8">
                    <select class="form-control form-select" name="cihazGaranti">
                      <option value="">-SEÇİNİZ-</option>
                      @foreach($warranty_periods as $index => $garanti)
                        <option value="{{ $garanti->id }}" {{ $index == 0 ? 'selected' : '' }}>
                          {{ $garanti->garanti }} Ay
                        </option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="card b1" style="margin-top: 5px;">
              <div class="card-body custom-padding-time">
                <div class="row form-group" style="border: 0;padding-bottom: 0;margin-bottom: 0">
                  <div class="col-md-4 rw1"><label>Müsait Zamanı</label></div>
                  <div class="col-md-8 " style="display: flex; gap: 5px; align-items: center;">
                    @php
                      $gun = date('l');
                      if($gun == "Saturday"){
                        $musaitTarih = date('Y-m-d', strtotime(date('Y-m-d'). ' + 2 days'));
                      } else {
                        $musaitTarih = date('Y-m-d', strtotime(date('Y-m-d'). ' + 1 days'));
                      }
                      $musaitTarihArray = explode("-", $musaitTarih);
                      $formattedDate = $musaitTarihArray[2]."/".$musaitTarihArray[1]."/".$musaitTarihArray[0];
                    @endphp
                    <input name="kayitTarihi" class="form-control datepicker kayitTarihi" type="date" value="{{$musaitTarih}}" style="width: 100px;display: inline-block;background:#fff;text-align:center;" required>
                    <select name="musaitSaat1" class="form-control form-select musaitSaat1" style="width: 106px;display: inline-block;background-position: right 0.15rem center;">
                      <option value="08:00">08:00</option>
                      <option value="08:30">08:30</option>
                      <option value="09:00" selected>09:00</option>
                      <option value="09:30">09:30</option>
                      <option value="10:00">10:00</option>
                      <option value="10:30">10:30</option>
                      <option value="11:00">11:00</option>
                      <option value="11:30">11:30</option>
                      <option value="12:00">12:00</option>
                      <option value="12:30">12:30</option>
                      <option value="13:00">13:00</option>
                      <option value="13:30">13:30</option>
                      <option value="14:00">14:00</option>
                      <option value="14:30">14:30</option>
                      <option value="15:00">15:00</option>
                      <option value="15:30">15:30</option>
                      <option value="16:00">16:00</option>
                      <option value="16:30">16:30</option>
                      <option value="17:00">17:00</option>
                      <option value="17:30">17:30</option>
                      <option value="18:00">18:00</option>
                      <option value="18:30">18:30</option>
                      <option value="19:00">19:00</option>
                      <option value="19:30">19:30</option>
                      <option value="20:00">20:00</option>
                      <option value="20:30">20:30</option>
                      <option value="21:00">21:00</option>
                    </select>
                    <select name="musaitSaat2" class="form-control form-select musaitSaat2" style="width: 106px;display: inline-block;background-position: right 0.15rem center;">
                      <option value="08:00">08:00</option>
                      <option value="08:30">08:30</option>
                      <option value="09:00">09:00</option>
                      <option value="09:30">09:30</option>
                      <option value="10:00">10:00</option>
                      <option value="10:30">10:30</option>
                      <option value="11:00">11:00</option>
                      <option value="11:30">11:30</option>
                      <option value="12:00">12:00</option>
                      <option value="12:30">12:30</option>
                      <option value="13:00">13:00</option>
                      <option value="13:30">13:30</option>
                      <option value="14:00">14:00</option>
                      <option value="14:30">14:30</option>
                      <option value="15:00">15:00</option>
                      <option value="15:30">15:30</option>
                      <option value="16:00">16:00</option>
                      <option value="16:30">16:30</option>
                      <option value="17:00">17:00</option>
                      <option value="17:30">17:30</option>
                      <option value="18:00">18:00</option>
                      <option value="18:30">18:30</option>
                      <option value="19:00">19:00</option>
                      <option value="19:30">19:30</option>
                      <option value="20:00" selected>20:00</option>
                      <option value="20:30">20:30</option>
                      <option value="21:00">21:00</option>
                      <option value="21:30">21:30</option>
                      <option value="22:00">22:00</option>
                      <option value="22:30">22:30</option>
                      <option value="23:00">23:00</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
           </div>
          </div>
        
          <div class="gonderBtnWrap" style="text-align: center;margin-top: 5px;">
            <input type="hidden" name="servisEkle" value="Ekle"/>
            <input style="background-color: #343a40;border-color: #343a40;" type="submit" class="btn btn-info btn-sm" value="Servis Kaydet"/>
          </div>
{{-- 
          <div class="yukleniyor" style="text-align: center;margin-top: 5px;display: none;">
            Yükleniyor. Lütfen bekleyin..
          </div> --}}
    </form>
</div>



<script type="text/javascript">
  $(document).ready(function(){
    $('.phone').mask('000 000 0000');
  });

    $(document).ready(function(){
    $("#vergiNo").mask("9999999999");
  });

  $(document).ready(function(){
    $(".tcNo").mask("99999999999");
  });
</script>

<script>
    $(document).ready(function () {
      $('#servisEkle').submit(function (event) {
        var formIsValid = true;
        $(this).find('input, select').each(function () {
          var isRequired = $(this).prop('required');
          var isEmpty = !$(this).val();
          if (isRequired && isEmpty) {
            formIsValid = false;
            return false;
          }
        });
        if (!formIsValid) {
          event.preventDefault();
          alert('Lütfen zorunlu alanları doldurun.');
          return false;
        }
      });
    });
</script>

<script>
$(document).ready(function() {
  var defaultIlId = $("#countrySelect").val();
  if (defaultIlId) {
    loadCities(defaultIlId);
  }
  
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
    citySelect.append(new Option("-Seçiniz-", "")); // Kullanıcıya yükleniyor bilgisi ver

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
<script type="text/javascript">
  $(document).ready(function (e) {
    // Başlangıçta Bireysel seçili, TC No göster
    $('#vergiBoxRow').hide();
    $('#tcNoRow').show();

    $('#musteriTipiSelect').on('change', function () {
      var val = $(this).val();
      
      if (val == 2) {
        // Kurumsal
        $(".musteriAdiSpan").text("Firma Adı");
        $('#vergiBoxRow').show();
        $('#tcNoRow').hide();
        
        // Vergi No zorunlu yap
        $('#vergiNo').prop('required', true);
        
        // TC No temizle
        $('#tcKimlik').val('');
      } else {
        // Bireysel
        $(".musteriAdiSpan").text("Müşteri Adı");
        $('#vergiBoxRow').hide();
        $('#tcNoRow').show();
        
        // Vergi No zorunluluğunu kaldır ve temizle
        $('#vergiNo').prop('required', false).val('');
        $('#vergiDairesi').val('');
      }
    });
  });
</script>

<script>
$(document).ready(function () {
    $(".musteriCikart").hide();
    let searchTimeout;

    // Arama fonksiyonu - debounce ile optimize edilmiş
    $('#search').on('input', function () {
        clearTimeout(searchTimeout);
        const searchField = $(this).val().trim();
        
        // Arama alanı temizlenirse sonuçları da temizle
        if (searchField.length === 0) {
            $('#result').html('');
            return;
        }
        
        // Minimum 2 karakter bekle
        if (searchField.length < 2) {
            $('#result').html('');
            return;
        }
        
        // 300ms bekle, kullanıcı yazmayı bitirince ara
        searchTimeout = setTimeout(function() {
            searchCustomers(searchField);
        }, 300);
    });

    // Müşteri arama AJAX fonksiyonu
    function searchCustomers(searchField) {
        // Loading göster
        $('#result').html('<li class="list-group-item">Aranıyor...</li>');
        
        $.ajax({
            url: "{{ route('customer.search', $firma->id) }}", // Route kullanımı
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                musteriGetir: searchField
            },
            success: function (response) {
                displaySearchResults(response);
            },
            error: function(xhr, status, error) {
                console.error('Search error:', error);
                $('#result').html('<li class="list-group-item text-danger">Arama sırasında hata oluştu</li>');
            }
        });
    }

    // Arama sonuçlarını gösterme
    function displaySearchResults(customers) {
        $('#result').html('');
        
        if (!customers || customers.length === 0) {

            // $('#result').html('<li class="list-group-item text-muted">Müşteri bulunamadı</li>');

            return;
        }
        
        $.each(customers, function (key, customer) {
            const customerType = customer.musteriTipi == "1" ? "Bireysel" : "Kurumsal";
            const customerHtml = `
                <li class="list-group-item link-class customer-item" 
                    data-id="${customer.id}"
                    data-adsoyad="${customer.adSoyad}"
                    data-tel="${customer.tel1}"
                    data-tel2="${customer.tel2 || ''}"
                    data-adres="${customer.adres}"
                    data-il="${customer.il}"
                    data-ilce="${customer.ilce}"
                    data-tip="${customer.musteriTipi}"
                    data-tc="${customer.tcNo || ''}"
                    data-vno="${customer.vergiNo || ''}"
                    data-vdairesi="${customer.vergiDairesi || ''}"
                    style="cursor: pointer; border-left: 4px solid #007bff; margin-bottom: 2px;">
                    <div>
                        <strong style="color:#fff;">${customer.adSoyad}</strong> 
                        <span class="badge badge-secondary" style="font-size: 0.75em;">${customerType}</span>
                        <br>
                        <small class="text-muted">
                          📲 ${customer.tel1}
                            <br>
                            📍 ${customer.adres} - ${customer.ilce}/${customer.il}
                        </small>
                    </div>
                </li>
            `;
            $('#result').append(customerHtml);
        });
    }

    // Müşteri seçimi
    $('#result').on('click', 'li.customer-item', function () {
        selectCustomer($(this));
    });

    // Müşteri seçme fonksiyonu
    function selectCustomer($element) {
        const customerData = {
            id: $element.data('id'),
            adSoyad: $element.data('adsoyad'),
            tel1: $element.data('tel'),
            tel2: $element.data('tel2'),
            adres: $element.data('adres'),
            il: $element.data('il'),
            ilce: $element.data('ilce'),
            tip: $element.data('tip'),
            tc: $element.data('tc'),
            vno: $element.data('vno'),
            vdairesi: $element.data('vdairesi')
        };

        // Form alanlarını doldur
        fillCustomerForm(customerData);
        
        // Sonuçları temizle
        $('#result').html('');
        
        // Müşteri çıkart butonunu göster
        $(".musteriCikart").show();
        $('#servisEkle .mid').html("M.No: " + customerData.id);
    }

    // Form alanlarını doldurma fonksiyonu
    function fillCustomerForm(customer) {
        $('#servisEkle .eskiMusteriId').val(customer.id);
        $('#servisEkle #alici').val(customer.id);
        $('#servisEkle .adSoyad').val(customer.adSoyad);
        $('#servisEkle .tel1').val(customer.tel1);
        $('#servisEkle .tel2').val(customer.tel2);
        $('#servisEkle .adres').val(customer.adres);
        $('#servisEkle .tcNo').val(customer.tc);
        $('#servisEkle .vergiNo').val(customer.vno);
        $('#servisEkle .vergiDairesi').val(customer.vdairesi);
        
        // Müşteri tipini ayarla
        $('#servisEkle .musteriTipi').val(customer.tip).trigger('change');
        
        $('#servisEkle #countrySelect option').each(function() {
          if (this.text.includes(customer.il) || this.value == customer.il) {
              $(this).prop('selected', true);
              $('#servisEkle #countrySelect').trigger('change');
              return false;
              
          }
      });
      
      // İlçe için 2 saniye bekle
      setTimeout(function() {
          $('#servisEkle #citySelect option').each(function() {
              if (this.text.includes(customer.ilce) || this.value == customer.ilce) {
                  $(this).prop('selected', true);
                  return false;
                  loadCities(costumer.il);
              }
          });
      }, 1000);
    }

    // Müşteri temizleme
    $(".musteriCikart .fas").click(function(){
        clearCustomerForm();
    });

    // Form temizleme fonksiyonu
    function clearCustomerForm() {
        $(".musteriCikart").hide();
        $('#servisEkle .eskiMusteriId').val("");
        $('#servisEkle #alici').val("");
        $('#servisEkle .adSoyad').val("");
        $('#servisEkle .tel1').val("");
        $('#servisEkle .tel2').val("");
        $('#servisEkle .adres').val("");
        $('#servisEkle .tcNo').val("");
        $('#servisEkle .vergiNo').val("");
        $('#servisEkle .vergiDairesi').val("");
        $('#servisEkle .musteriTipi').val("1").trigger('change');
        $('#servisEkle .il').val("34").trigger('change'); // İstanbul default
        $("#result").html('');
    }

    // Dış tıklama ile sonuçları kapat
    $(document).click(function(e) {
        if (!$(e.target).closest('#search, #result').length) {
            $('#result').html('');
        }
    });
});
</script>
<script>
  $(document).ready(function () {
    let formSubmitting = false;
    
    function generateToken() {
      return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    $('#formToken').val(generateToken());
    
    $('#servisEkle').submit(function(event) {
      event.preventDefault();
      
      if (formSubmitting) {
        return false;
      }
      
      var formIsValid = true;
      var errorMessage = '';
      
      // Temel zorunlu alanlar kontrolü
      var emptyRequired = false;
      $(this).find('input[required], select[required], textarea[required]').each(function() {
        if (!$(this).val()) {
          emptyRequired = true;
          return false;
        }
      });
      
      if (emptyRequired) {
        alert('Lütfen zorunlu alanları doldurun.');
        return false;
      }
      
      // ✅ Müşteri tipine göre özel validasyon
      var musteriTipi = $('#musteriTipiSelect').val();
      var eskiMusteriId = $('.eskiMusteriId').val();
      
      // Sadece yeni müşteri ekleniyorsa kontrol yap (mevcut müşteri seçilmediyse)
      if (!eskiMusteriId || eskiMusteriId === '') {
        if (musteriTipi == '1') {
          // Bireysel Müşteri - TC No Kontrolü
          var tcNo = $('#tcKimlik').val().replace(/\s/g, '');
          
          if (tcNo && tcNo.length > 0 && tcNo.length !== 11) {
            errorMessage = 'TC Kimlik Numarası 11 haneli olmalıdır.';
            formIsValid = false;
            $('#tcKimlik').focus();
          }
        } 
        else if (musteriTipi == '2') {
          // Kurumsal Müşteri - Vergi No ZORUNLU
          var vergiNo = $('#vergiNo').val().replace(/\s/g, '');
          
          if (!vergiNo || vergiNo.length === 0) {
            errorMessage = 'Kurumsal müşteriler için Vergi Numarası zorunludur!\n\n' +
                          'Fatura kesilebilmesi için mutlaka Vergi No girilmelidir.';
            formIsValid = false;
            $('#vergiNo').focus();
          } else if (vergiNo.length !== 10) {
            errorMessage = 'Vergi Numarası 10 haneli olmalıdır.';
            formIsValid = false;
            $('#vergiNo').focus();
          }
        }
      }
      
      if (!formIsValid) {
        if (errorMessage) {
          alert(errorMessage);
        }
        return false;
      }
      
      // Form geçerliyse submit et
      formSubmitting = true;
      $(this).find('input[type="submit"]').prop('disabled', true);
      
      // Formu submit et
      this.submit();
      
      // 5 saniye sonra yeniden aktif et
      setTimeout(function() {
        $('#formToken').val(generateToken());
        formSubmitting = false;
        $('#servisEkle input[type="submit"]').prop('disabled', false);
      }, 5000);
    });
  });
</script>
<script>
$(document).ready(function() {
    let isSubmitting = false;
    let shouldReload = false;
    
    // Form submit edildiğinde flag'i ayarla
    $('#servisEkle').submit(function() {
        isSubmitting = true;
    });
    
    // Modal kapatılmaya çalışıldığında
    $('#addServiceModal').on('hide.bs.modal', function(e) {
        if (isSubmitting) {
            isSubmitting = false;
            return true;
        }
        
        // Her zaman onay iste
        if (!confirm('Kapatmak istediğinizden emin misiniz?')) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        
        shouldReload = true; // Tamam'a basıldı, yenile
        isSubmitting = false;
    });
    
    // Modal tamamen kapandığında sayfayı yenile
    $('#addServiceModal').on('hidden.bs.modal', function() {
        isSubmitting = false;
        if (shouldReload) {
            shouldReload = false;
            location.reload(); // Sayfayı yenile
        }
    });
});
</script>
<script>
$(document).ready(function() {
    var tenantId = "{{ $firma->id }}";
    var mainFormId = '#servisEkle';
    var hasServiceResources = {{ $service_resources->isEmpty() ? 'false' : 'true' }};
    
    // Token oluşturma fonksiyonu
    function generateToken() {
        return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    // Sayfa yüklendiğinde tokenları oluştur
    $('#deviceBrandFormToken').val(generateToken());
    $('#deviceTypeServiceFormToken').val(generateToken());
    $('#serviceResourceFormToken').val(generateToken());
    
    // Submit flag'leri
    let deviceBrandFormSubmitting = false;
    let deviceTypeServiceFormSubmitting = false;
    let serviceResourceFormSubmitting = false;
    
    // ========== CİHAZ MARKASI EKLEME ==========
    $('#addNewDeviceBrandBtn').click(function() {
        var subModal = new bootstrap.Modal(document.getElementById('addDeviceBrandModal'));
        subModal.show();
    });
    
    $('#addDeviceBrandModal').on('hidden.bs.modal', function() {
        if ($('#addServiceModal').is(':visible')) {
            $('body').addClass('modal-open');
        }
    });
    
    $('#addDeviceBrandForm').submit(function(e) {
        e.preventDefault();
        
        if (deviceBrandFormSubmitting) {
            return false;
        }
        
        deviceBrandFormSubmitting = true;
        var submitBtn = $(this).find('input[type="submit"]');
        submitBtn.prop('disabled', true);
        
        var form = $(this);
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                var newOption = new Option(response.text, response.id, true, true);
                $(mainFormId + ' select[name="cihazMarka"]').append(newOption).trigger('change');
                $('#addDeviceBrandModal').modal('hide');
                form[0].reset();
                alert('Cihaz markası başarıyla eklendi.');
                
                $('#deviceBrandFormToken').val(generateToken());
                deviceBrandFormSubmitting = false;
                submitBtn.prop('disabled', false);
            },
            error: function(xhr) {
                var errorMessage = 'Bir hata oluştu. Lütfen tekrar deneyin.';
                
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    if (xhr.responseJSON.error.includes('token') || xhr.responseJSON.error.includes('gönderildi')) {
                        console.log('Token hatası:', xhr.responseJSON.error);
                    } else {
                        alert(errorMessage);
                    }
                } else {
                    alert(errorMessage);
                }
                
                $('#deviceBrandFormToken').val(generateToken());
                deviceBrandFormSubmitting = false;
                submitBtn.prop('disabled', false);
            }
        });
    });
    
    // ========== CİHAZ TÜRÜ EKLEME ==========
    $('#addNewDeviceTypeServiceBtn').click(function() {
        var subModal = new bootstrap.Modal(document.getElementById('addDeviceTypeServiceModal'));
        subModal.show();
    });
    
    $('#addDeviceTypeServiceModal').on('hidden.bs.modal', function() {
        if ($('#addServiceModal').is(':visible')) {
            $('body').addClass('modal-open');
        }
    });
    
    $('#addDeviceTypeServiceForm').submit(function(e) {
        e.preventDefault();
        
        if (deviceTypeServiceFormSubmitting) {
            return false;
        }
        
        deviceTypeServiceFormSubmitting = true;
        var submitBtn = $(this).find('input[type="submit"]');
        submitBtn.prop('disabled', true);
        
        var form = $(this);
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                var newOption = new Option(response.text, response.id, true, true);
                $(mainFormId + ' select[name="cihazTur"]').append(newOption).trigger('change');
                $('#addDeviceTypeServiceModal').modal('hide');
                form[0].reset();
                alert('Cihaz türü başarıyla eklendi.');
                
                $('#deviceTypeServiceFormToken').val(generateToken());
                deviceTypeServiceFormSubmitting = false;
                submitBtn.prop('disabled', false);
            },
            error: function(xhr) {
                var errorMessage = 'Bir hata oluştu. Lütfen tekrar deneyin.';
                
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    if (xhr.responseJSON.error.includes('token') || xhr.responseJSON.error.includes('gönderildi')) {
                        console.log('Token hatası:', xhr.responseJSON.error);
                    } else {
                        alert(errorMessage);
                    }
                } else {
                    alert(errorMessage);
                }
                
                $('#deviceTypeServiceFormToken').val(generateToken());
                deviceTypeServiceFormSubmitting = false;
                submitBtn.prop('disabled', false);
            }
        });
    });
    
    // ========== SERVİS KAYNAĞI EKLEME ==========
    $('#addNewServiceResourceBtn').click(function() {
        var subModal = new bootstrap.Modal(document.getElementById('addServiceResourceModal'));
        subModal.show();
    });
    
    $('#addServiceResourceModal').on('hidden.bs.modal', function() {
        if ($('#addServiceModal').is(':visible')) {
            $('body').addClass('modal-open');
        }
    });
    
    $('#addServiceResourceForm').submit(function(e) {
        e.preventDefault();
        
        if (serviceResourceFormSubmitting) {
            return false;
        }
        
        serviceResourceFormSubmitting = true;
        var submitBtn = $(this).find('input[type="submit"]');
        submitBtn.prop('disabled', true);
        
        var form = $(this);
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                var newOption = new Option(response.text, response.id, true, true);
                $(mainFormId + ' select[name="servisReso"]').append(newOption).trigger('change');
                $('#addServiceResourceModal').modal('hide');
                form[0].reset();
                alert('Servis kaynağı başarıyla eklendi.');
                
                // Eğer ilk kaynak eklendiyse sayfayı yenile
                if (!hasServiceResources) {
                    location.reload();
                }
                
                $('#serviceResourceFormToken').val(generateToken());
                serviceResourceFormSubmitting = false;
                submitBtn.prop('disabled', false);
            },
            error: function(xhr) {
                var errorMessage = 'Bir hata oluştu. Lütfen tekrar deneyin.';
                
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    if (xhr.responseJSON.error.includes('token') || xhr.responseJSON.error.includes('gönderildi')) {
                        console.log('Token hatası:', xhr.responseJSON.error);
                    } else {
                        alert(errorMessage);
                    }
                } else {
                    alert(errorMessage);
                }
                
                $('#serviceResourceFormToken').val(generateToken());
                serviceResourceFormSubmitting = false;
                submitBtn.prop('disabled', false);
            }
        });
    });
});
</script>