

<form method="post" id="addCust" action="{{ route('store.customer', $firma->id)}}" enctype="multipart/form-data" >
  @csrf   
  <input type="hidden" name="form_token" id="formToken" value="">
  @if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong><i class="fas fa-exclamation-triangle"></i> Hata!</strong>
    <ul class="mb-0 mt-2">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
  <div class="row">
    <label class="col-sm-3 custom-p-r">Müşteri Tipi<span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-9 custom-p-l">
      <select name="mTipi" id="musteriTipiSelect" class="form-select musteriTipi" required>
        <option value="1">BİREYSEL</option>
        <option value="2">KURUMSAL</option>
      </select>
    </div>
  </div>
  
  <div class="row">
    <label class="col-sm-3 custom-p-r"><span class="musteriAdiSpan">Müşteri Adı</span><span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-9 custom-p-l">
      <input name="name" class="form-control buyukYaz" type="text" placeholder="" required>
    </div>
  </div>
  
  <div class="row">
    <label class="col-sm-3 custom-p-r">Telefon</label>
    <div class="col-sm-4 col-sm-custom col-6 custom-p-r-m-md custom-p-l">
      <input name="tel1" class="form-control phone" type="text" placeholder="5XX XXX XX XX" required>
    </div>
    <div class="col-sm-4 col-sm-custom col-6 custom-p-m-md custom-p-l">
      <input name="tel2" class="form-control phone" type="text" placeholder="5XX XXX XX XX">
    </div>
  </div>
  
  <div class="row">
    <div class="col-sm-3 custom-p-r"><label>İl/İlçe</label></div>
    <div class="col-sm-4 col-sm-custom col-6 custom-p-r-m-md custom-p-l">
      <select name="il" id="country" class="form-control form-select" style="width:100%!important;">
        <option value="" selected disabled>-Seçiniz-</option>
        @foreach($countries as $item)
          <option value="{{ $item->id }}">{{ $item->name}}</option>
        @endforeach
      </select>
    </div>
    <div class="col-sm-4 col-sm-custom col-6 custom-p-m-md custom-p-l">
      <select name="ilce" id="city" class="form-control form-select" style="width:100%!important;">
        <option value="" selected disabled>-Seçiniz-</option>                              
      </select>
    </div>
  </div>
  
  <div class="row">
    <label class="col-sm-3 custom-p-r">Adres:</label>
    <div class="col-sm-9 custom-p-l">
      <textarea name="address" type="text" class="form-control" rows="2" placeholder=""></textarea>
    </div>
  </div>
  
  <!-- Bireysel - TC No -->
  <div class="row" id="tcNoRow">
    <label class="col-sm-3 custom-p-r">T.C. No <span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-9 custom-p-l">
      <input name="tcno" id="tcKimlik" class="form-control" type="text" placeholder="11 haneli TC Kimlik No">
      @if(isset($hasInvoiceIntegration) && $hasInvoiceIntegration)
        <small class="text-muted" style="display: block; margin-top: 5px;">
          <i class="fas fa-info-circle"></i> Fatura kesilecekse TC No zorunludur
        </small>
      @endif
    </div>
  </div>

  <!-- Kurumsal - Vergi No/Dairesi -->
  <div class="row vergi-box" id="vergiBoxRow">
    <label class="col-sm-3 custom-p-r">V. No/Daire <span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-4 col-sm-custom col-6 custom-p-l">
      <input name="vergiNo" id="vergiNo" class="form-control" type="text" placeholder="Vergi No">
      @if(isset($hasInvoiceIntegration) && $hasInvoiceIntegration)
        <small class="text-danger" style="display: block; margin-top: 5px;">
          <i class="fas fa-exclamation-triangle"></i> Kurumsal müşteriler için zorunlu 
        </small>
      @endif
    </div>
    <div class="col-sm-4 col-sm-custom col-6 custom-p-l">
      <input name="vergiDairesi" id="vergiDairesi" class="form-control" type="text" placeholder="Vergi Dairesi">
    </div>
  </div>
  
  <div class="row">               
    <div class="col-sm-12 gonderBtn">
      <input type="submit" class="btn btn-sm btn-info waves-effect waves-light" value="Kaydet">
    </div>
  </div>
</form>
  
<script>
  $('.buyukYaz').keyup(function(){
    this.value = this.value.toUpperCase();
  });

  $(document).ready(function () {
    $(".phone").mask("999 999 9999");
    $("#tcKimlik").mask("99999999999");
    $("#vergiNo").mask("9999999999");
  });

  // ✅ Müşteri Tipi Değişimi - Alan Göster/Gizle
  $(document).ready(function() {
    // Başlangıçta Bireysel seçili olduğu için TC No göster, Vergi gizle
    $('#vergiBoxRow').hide();
    $('#tcNoRow').show();

    $('#musteriTipiSelect').on('change', function() {
      var musteriTipi = $(this).val();
      
      if (musteriTipi == '1') {
        // Bireysel
        $("#addCust .musteriAdiSpan").text("Müşteri Adı");
        $('#tcNoRow').show();
        $('#vergiBoxRow').hide();
        
        // Vergi No zorunluluğunu kaldır, değerleri temizle
        $('#vergiNo').prop('required', false).val('');
        $('#vergiDairesi').val('');
      } else if (musteriTipi == '2') {
        // Kurumsal
        $("#addCust .musteriAdiSpan").text("Firma Adı");
        $('#tcNoRow').hide();
        $('#vergiBoxRow').show();
        
        // TC No'yu temizle
        $('#tcKimlik').val('');
        
        // Vergi No zorunlu yap
        $('#vergiNo').prop('required', true);
      }
    });
  });
</script>

<!-- ✅ Form Validasyonu - TC No ve Vergi No Kontrolü -->
<script>
  $(document).ready(function () {
    let formSubmitting = false;
    
    // Benzersiz token oluştur
    function generateToken() {
      return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    // Sayfa yüklendiğinde ilk token'ı oluştur
    $('#formToken').val(generateToken());
    
    $('#addCust').submit(function(event) {
      event.preventDefault();
      
      // Token kontrolü
      if (formSubmitting) {
        return false;
      }
      
      var formIsValid = true;
      var errorMessage = '';
      
      // Müşteri tipi al
      var musteriTipi = $('#musteriTipiSelect').val();
      
      // Temel zorunlu alanlar kontrolü
      if (!$('input[name="name"]').val()) {
        errorMessage = 'Lütfen müşteri/firma adını girin.';
        formIsValid = false;
      } else if (!$('input[name="tel1"]').val()) {
        errorMessage = 'Lütfen telefon numarasını girin.';
        formIsValid = false;
      }
      
      // ✅ Müşteri tipine göre özel validasyon
      if (formIsValid) {
        if (musteriTipi == '1') {
          // Bireysel Müşteri - TC No Kontrolü
          var tcNo = $('#tcKimlik').val().replace(/\s/g, '');
          
          if (!tcNo || tcNo.length === 0) {
            // TC No boş - Kullanıcıya sor
            var confirmMessage = 'Fatura keseceğiniz bir müşteriyse TC Kimlik Numarası girmeniz önerilir.\n\n' +
                                'TC No olmadan devam etmek istiyor musunuz?';
            
            if (!confirm(confirmMessage)) {
              formIsValid = false;
              $('#tcKimlik').focus();
              return false;
            }
            // Kullanıcı "Tamam" dedi, devam et
          } else if (tcNo.length !== 11) {
            // TC No girilmiş ama 11 haneli değil
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
      $(this).find('input[type="submit"]').prop('disabled', true).val('Kaydediliyor...');
      
      // Formu submit et
      this.submit();
      
      // 5 saniye sonra yeniden aktif et (eğer hata olursa)
      setTimeout(function() {
        $('#formToken').val(generateToken());
        formSubmitting = false;
        $('#addCust input[type="submit"]').prop('disabled', false).val('Kaydet');
      }, 5000);
    });
  });
</script>

<!-- İl/İlçe Select İşlemleri -->
<script>
  $(document).ready(function() {
    $("#country").change(function() {
      var selectedCountryId = $(this).val();
      if (selectedCountryId) {
        loadCities(selectedCountryId);
      }
    });
    
    function loadCities(countryId) {
      var citySelect = $("#city");
      citySelect.empty();
      citySelect.append(new Option("Yükleniyor...", ""));
  
      $.get("/get-states/" + countryId, function(data) {
        citySelect.empty();
        citySelect.append(new Option("-Seçiniz-", ""));
        $.each(data, function(index, city) {
          citySelect.append(new Option(city.ilceName, city.id));
        });
      }).fail(function() {
        citySelect.empty();
        citySelect.append(new Option("Yüklenemedi", ""));
      });
    }
  });
</script>

<!-- Modal Kapatma Kontrolü -->
<script>
  $(document).ready(function() {
    let isSubmitting = false;
    let shouldReload = false;
    
    $('#addCust').submit(function() {
      isSubmitting = true;
    });
    
    $('#addCustomerModal').on('hide.bs.modal', function(e) {
      if (isSubmitting) {
        isSubmitting = false;
        return true;
      }
      
      if (!confirm('Kapatmak istediğinizden emin misiniz?')) {
        e.preventDefault();
        e.stopPropagation();
        return false;
      }
      
      shouldReload = true;
      isSubmitting = false;
    });
    
    $('#addCustomerModal').on('hidden.bs.modal', function() {
      isSubmitting = false;
      if (shouldReload) {
        shouldReload = false;
        location.reload();
      }
    });
  });
</script>