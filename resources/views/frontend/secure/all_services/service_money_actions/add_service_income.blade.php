<form method="post" id="servisOdemeEkle" action="{{ route('store.service.income', $firma->id) }}" class="col-sm-6" style="margin: 0 auto;padding:10px;">
  @csrf
  <input type="hidden" name="form_token" id="formTokenIncome" value="">
  <div class="row form-group ">
    <div class="col-lg-12 rw1"><label><strong>GELİR EKLE (Servis için alınan ödemeleri kayıt edin. Örn: Müşteriden alınan ödeme.)</strong></label></div>
  </div>

  @hasanyrole('Patron|Admin')
    <div class="row form-group">
      <div class="col-lg-5 rw1"><label>Tarih <span style="font-weight: bold; color: red;">*</span></label></div>
      <div class="col-lg-7 rw2">
        <input name="tarih" class="form-control datepicker kayitTarihi" type="date" value="{{date('Y-m-d')}}" style="display: inline-block;background:#fff" required>
      </div>
    </div>
    <div class="row form-group ">
      <div class="col-lg-5 rw1"><label>Personel <span style="font-weight: bold; color: red;">*</span></label></div>
      <div class="col-lg-7 rw2">
        <select class="form-control personeller" name="personeller" required>
          <option value="">-Seçiniz-</option>
          @foreach ($personeller as $item)
              <option value="{{$item->user_id}}">{{$item->name}}</option>
          @endforeach
        </select>
      </div>
    </div>
  @endhasanyrole
  <div class="row form-group ">
    <div class="col-lg-5 rw1"><label>Ödeme Şekli <span style="font-weight: bold; color: red;">*</span></label></div>
    <div class="col-lg-7 rw2">
      <select class="form-control odemeSekli" name="odemeSekli" required>
         <option value="">-Seçiniz-</option>
          @foreach ($odemeSekilleri as $item)
              <option value="{{$item->id}}">{{$item->odemeSekli}}</option>
          @endforeach
      </select>
    </div>
  </div>

  <div class="row form-group">
    <div class="col-lg-5 rw1"><label>Ödeme Durumu <span style="font-weight: bold; color: red;">*</span></label></div>
    <div class="col-lg-7 rw2">
      <select class="form-control odemeDurum" name="odemeDurum" required>
        <option value="1">Tamamlandı</option>
        <option value="2">Beklemede</option>
      </select>
    </div>
  </div>

  <div class="row form-group">
    <div class="col-lg-5 rw1"><label>Fiyat <span style="font-weight: bold; color: red;">*</span></label></div>
    <div class="col-lg-7 rw2">
      <input type="text" name="fiyat" onkeyup="sayiKontrol(this)" class="form-control fiyat" autocomplete="off" placeholder="0.00" required>
    </div>
  </div>

  <div class="row form-group">
    <div class="col-lg-5 rw1"><label>Açıklama </label></div>
    <div class="col-lg-7 rw2">
      <input type="text" name="aciklama" class="form-control aciklama" autocomplete="off">
    </div>
  </div>

  <div style="text-align: center;margin-top: 5px;">
    <input type="hidden" name="cihazid" class="cihazid" value="{{$servis->cihazTur}}"/>
    <input type="hidden" name="markaid" class="markaid" value="{{$servis->cihazMarka}}"/>
    <input type="hidden" name="servisid" class="servisid" value="{{$servis->id}}"/>
    <input type="submit" class="btn btn-primary btn-sm" value="Gönder"/>
  </div>
    
</form>

<script>
$(document).ready(function() {
    let incomeFormSubmitting = false;
    
    // Benzersiz token oluştur
    function generateToken() {
        return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    // Sayfa yüklendiğinde ilk token'ı oluştur
    $('#formTokenIncome').val(generateToken());
    
    // Token yenileme fonksiyonu
    function resetIncomeFormToken() {
        $('#formTokenIncome').val(generateToken());
        incomeFormSubmitting = false;
        $('#servisOdemeEkle input[type="submit"]').prop('disabled', false).val('Gönder');
    }
    
    // Form submit
    $('#servisOdemeEkle').on('submit', function(event) {
        event.preventDefault();
        
        // Token kontrolü
        if (incomeFormSubmitting) {
            alert('Form gönderiliyor, lütfen bekleyin...');
            return false;
        }
        
        // Validasyon kontrolü
        var formIsValid = true;
        $(this).find('input, select').each(function() {
            var isRequired = $(this).prop('required');
            var isEmpty = !$(this).val();
            if (isRequired && isEmpty) {
                formIsValid = false;
                return false;
            }
        });
        
        if (!formIsValid) {
            alert('Lütfen zorunlu alanları doldurun.');
            return false;
        }
        
        if (this.checkValidity() === false) {
            return false;
        }
        
        // Token işaretle ve butonu disable et
        incomeFormSubmitting = true;
        $(this).find('input[type="submit"]').prop('disabled', true);
        
        var formData = new FormData(this);
        
        $.ajax({
            url: $(this).attr("action"),
            type: "POST",
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
                $(".btnWrap").html("Yükleniyor. Bekleyin..");
            },
            success: function(data) {
                if (data.success) { 
                    alert("Servis geliri başarıyla eklendi.");
                    $('#datatableService').DataTable().ajax.reload();
                    if (typeof loadServiceHistory === 'function') {
                        loadServiceHistory({{ $servis->id }});
                    }
                    $('.nav4').trigger('click');
                } else {
                    alert("Kayıt yapılamadı.");
                    window.location.reload(true);
                }
                // Token'ı yenile
                setTimeout(resetIncomeFormToken, 3000);
            },
            error: function(xhr, status, error) {
                alert("Güncelleme başarısız!");
                // Token'ı yenile
                resetIncomeFormToken();
            }
        });
    });
});
</script>