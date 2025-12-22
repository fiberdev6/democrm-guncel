
<form method="post" id="addCashTransaction" action="{{ route('store.cash.transaction', $firma->id)}}" enctype="multipart/form-data" class="needs-validation" novalidate>
  @csrf
  <input type="hidden" name="form_token" id="cashFormToken" value="">
  <div class="row ">
    <label class="col-sm-4 custom-p-r col-form-label">İşlem Tarihi:<span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <input name="islemTarihi" class="form-control datepicker kayitTarihi" type="date" value="{{date('Y-m-d')}}" required>
    </div>
  </div>

  <div class="row">
    <label class="col-sm-4 custom-p-r">Ödeme Yönü:<span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <select name="odeme_yonu" class="form-select" required>
        <option selected value="1">Gelen Ödeme(Borç)</option>
        <option value="2">Giden Ödeme(Alacak)</option>
      </select>
    </div>
  </div>

  <div class="row">
    <label class="col-sm-4 custom-p-r">Ödeme Şekli:<span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <select name="odeme_sekli" class="form-select odemeSekli" required>
        @foreach($payment_methods as $method)
          <option value="{{$method->id}}">{{$method->odemeSekli}}</option>
        @endforeach
      </select>
    </div>
  </div>


  <div class="row">
    <label class="col-sm-4 custom-p-r">Ödeme Türü:<span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <select name="odeme_turu" class="form-select odemeTuru" required>
        <option selected value="">-Seçiniz-</option>
        @foreach($payment_types as $type)
          <option value="{{$type->id}}">{{$type->odemeTuru}}</option>
        @endforeach
      </select>
    </div>
  </div>

  <div class="odemeAciklamalari"></div>

  <div class="row">
    <label class="col-sm-4 custom-p-r">Ödeme Durumu:<span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <select name="odeme_durum" class="form-select" required>
        <option selected value="1">Tamamlandı</option>
        <option value="2">Tamamlanmadı</option>
      </select>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-sm-4 custom-p-r"><label>Tutar (₺) <span style="font-weight: bold; color: red;">*</span></label></div>
    <div class="col-sm-8 custom-p-l">
      <input type="number" step="0.01" name="fiyat" class="form-control fiyat" placeholder="0.00" required>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-12 gonderBtn">
      <input type="submit" class="btn btn-sm btn-info waves-effect waves-light" value="Kaydet">
    </div>
  </div>
</form>

<script>
  $('.odemeSekli').on('change', function() {
    var val = $(this).val();
    if(val=="7"){
      $(".odenenBankaWrap").hide();
    }else{
      $(".odenenBankaWrap").show();
    }
  });

  $('.odemeTuru').on('change', function() {
    var id = $(this).val();
    var firma_id = {{$firma->id}};
    $.ajax({
      url: "/" + firma_id + "/kasa-odeme/getir/" + id
    }).done(function(data) { 
      if($.trim(data)==="-1"){
        window.location.reload(true);
      }else{
        $('.odemeAciklamalari').html(data);
      }
    });
  });
</script>
<script>
  $(document).ready(function () {
    $('#addCashTransaction').submit(function (event) {
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
  $(document).ready(function(){
    $('#addCashTransaction').submit(function(e){
      e.preventDefault();
      if (this.checkValidity() === false) {
        e.stopPropagation();
      } else {
        var formData = $(this).serialize();
        $.ajax({
          url: $(this).attr('action'),
          type: 'POST',
          data: formData,
          success: function(response) {
            alert("Kasa hareketi eklendi");
            $('#datatableKasa').DataTable().ajax.reload();
            $('#addCashTransactionsModal').modal('hide');
          },
          error: function(xhr, status, error) {
            if (xhr.status === 422) {
                        var response = JSON.parse(xhr.responseText);
                        if (response.error) {
                            $('#servisHata').text(response.error); // ❗Hata yaz
                        }
                    } else {
                        console.error(xhr.responseText);
                    }
          }
        });
      }
    });
  });
</script>

<script>
$(document).ready(function() {
    let formSubmitting = false;
    
    // Benzersiz token oluştur
    function generateToken() {
        return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    // Sayfa yüklendiğinde ilk token'ı oluştur
    $('#cashFormToken').val(generateToken());
    
    // Form submit
    $('#addCashTransaction').submit(function(event) {
        // Token kontrolü
        if (formSubmitting) {
            event.preventDefault();
            return false;
        }
        
        // Mevcut validasyon
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
        
        // Butonu disable et
        formSubmitting = true;
        $(this).find('input[type="submit"]').prop('disabled', true);
        
        // 3 saniye sonra yeniden aktif et
        setTimeout(function() {
            $('#cashFormToken').val(generateToken());
            formSubmitting = false;
            $('#addCashTransaction input[type="submit"]').prop('disabled', false);
        }, 3000);
        
        return true;
    });
});
</script>
<script>
$(document).ready(function() {
    let isSubmitting = false;
    let shouldReload = false;
    
    // Form submit edildiğinde flag'i ayarla
    $('#addCashTransaction').submit(function() {
        isSubmitting = true;
    });
    
    // Modal kapatılmaya çalışıldığında
    $('#addCashTransactionsModal').on('hide.bs.modal', function(e) {
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
    $('#addCashTransactionsModal').on('hidden.bs.modal', function() {
        isSubmitting = false;
        if (shouldReload) {
            shouldReload = false;
            location.reload(); // Sayfayı yenile
        }
    });
});
</script>