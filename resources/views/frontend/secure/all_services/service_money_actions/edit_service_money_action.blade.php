<form method="POST" id="servisOdemeDuzenle" action="{{ route('update.service.money.action', $firma->id) }}" class="col-sm-6" style="margin: 0 auto;padding:10px;">
  @csrf
  @hasanyrole('Patron|Admin')
    <div class="row form-group mb-3">
      <div class="col-lg-5"><label>Tarih <span class="text-danger">*</span></label></div>
      <div class="col-lg-7">
        <input type="date" name="tarih" class="form-control datepicker service-money-date" 
          value="{{ \Carbon\Carbon::parse($servisPara->created_at)->format('Y-m-d') }}" 
          style=" display: inline-block; background:#fff">
      </div>
    </div>
                            
    <div class="row form-group mb-3">
      <div class="col-lg-5"><label>Personel <span class="text-danger">*</span></label></div>
      <div class="col-lg-7">
        <select class="form-control personeller" name="personeller">
          <option value="">-Seçiniz-</option>
          @foreach ($personeller as $personel)
            <option value="{{ $personel->user_id }}" 
              {{ $servisPara->pid == $personel->user_id ? 'selected' : '' }}>
              {{ $personel->name }}
            </option>
          @endforeach
        </select>
      </div>
    </div>
  @endhasanyrole

  <div class="row form-group mb-3">
    <div class="col-lg-5"><label>Ödeme Yönü</label></div>
    <div class="col-lg-7">
      <select class="form-control odemeYonu" name="odemeYonu">
        <option value="">-Seçiniz-</option>
        <option value="1" {{ $servisPara->odemeYonu == "1" ? 'selected' : '' }}>Gelir Ekle</option>
        <option value="2" {{ $servisPara->odemeYonu == "2" ? 'selected' : '' }}>Gider Ekle</option>
      </select>
    </div>
  </div>

  <div class="row form-group mb-3">
    <div class="col-lg-5"><label>Ödeme Şekli <span class="text-danger">*</span></label></div>
    <div class="col-lg-7">
      <select class="form-control odemeSekli" name="odemeSekli" required>
        @foreach ($odemeSekli as $sekli)
          <option value="{{ $sekli->id }}" 
            {{ $servisPara->odemeSekli == $sekli->id ? 'selected' : '' }}>
            {{ $sekli->odemeSekli }}
          </option>
        @endforeach
      </select>
    </div>
  </div>

  <div class="row form-group mb-3">
    <div class="col-lg-5"><label>Ödeme Durumu <span class="text-danger">*</span></label></div>
    <div class="col-lg-7">
      <select class="form-control odemeDurum" name="odemeDurum" required>
        <option value="1" {{ $servisPara->odemeDurum == "1" ? 'selected' : '' }}>Tamamlandı</option>
        <option value="2" {{ $servisPara->odemeDurum == "2" ? 'selected' : '' }}>Beklemede</option>
      </select>
    </div>
  </div>

  <div class="row form-group mb-3">
    <div class="col-lg-5"><label>Fiyat <span class="text-danger">*</span></label></div>
    <div class="col-lg-7">
      <input type="text" name="fiyat" class="form-control fiyat" 
        autocomplete="off" value="{{ $servisPara->fiyat }}" required>
    </div>
  </div>

  <div class="row form-group mb-3">
    <div class="col-lg-5"><label>Açıklama</label></div>
    <div class="col-lg-7">
      <input type="text" name="aciklama" class="form-control aciklama" 
        autocomplete="off" value="{{ $servisPara->aciklama }}">
    </div>
  </div>

  <div class="text-center mt-3">
    <input type="hidden" name="payment_id" value="{{$servisPara->id}}">
    <button type="submit" class="btn btn-primary btn-sm">Güncelle</button>
    <a href="javascript:void(0);" class="btn btn-secondary btn-sm" id="formIptalBtn">İptal</a>
  </div>
</form>

<script>
$(document).ready(function() {
    // Datepicker initialization
    $('.datepicker').datepicker({
        language: 'tr',
        autoclose: true,
        format: 'dd/mm/yyyy'
    });
    
    // Sayı kontrolü
    function sayiKontrol(input) {
        var value = input.value;
        // Sadece rakam, nokta ve virgül kabul et
        var isNum = /^[0-9.,]*$/;
        if (!isNum.test(value)) {
            input.value = value.replace(/[^0-9.,]/g, "");
        }
    }
    
    // Fiyat inputu için sayı kontrolü
    $('.fiyat').on('keyup', function() {
        sayiKontrol(this);
    });
    
    // Ödeme yönü değişikliği
    $('.odemeYonu').on('change', function() {
        var val = $(this).val();
        if (val == "2") {
            $(".odemeDurum").val(1);
        }
    });

    $('.odemeDurum').on('change', function() {
        var val = $(".odemeYonu").val();
        if (val == "2") {
            $(".odemeDurum").val(1);
        }
    });
    
    
});
</script>

<script>
  $(document).ready(function (e) {
    $("#servisOdemeDuzenle").submit(function (event) {
      event.preventDefault();
      if (this.checkValidity() === false) {
        e.stopPropagation();
      } else {
      var formData = new FormData(this);
      $.ajax({
        url: $(this).attr("action"),
        type: "POST",
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        beforeSend: function () {
          $(".btnWrap").html("Yükleniyor. Bekleyin..");
        },
        success: function (data) {
          if (data.success) { 
            alert("Servis geliri başarıyla eklendi.");
            $('#datatableService').DataTable().ajax.reload();
            loadServiceHistory({{ $servisPara->servisid }});
            $('.nav4').trigger('click');   
            
          } else {
              alert("Kayıt yapılamadı.");
              window.location.reload(true);
          }
        },
        error: function (xhr, status, error) {
          alert("Güncelleme başarısız!");
          
        },
      });
    }
    });
  });

  $('#formIptalBtn').on('click', function () {
    $('#servisOdemeDuzenle').remove(); // formu sil
});
</script>

