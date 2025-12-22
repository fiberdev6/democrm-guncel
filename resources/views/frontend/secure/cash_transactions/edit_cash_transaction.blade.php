
<form method="post" id="editDom" action="{{ route('update.cash.transaction', $firma->id) }}" enctype="multipart/form-data" class="needs-validation" novalidate>
  @csrf
  @php 
    $sontarih = \Carbon\Carbon::parse($cash_transaction_id->created_at)->format('Y-m-d');
  @endphp
  <div class="row">
    <label class="col-sm-4 custom-p-r">İşlem Tarihi:<span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <input name="islemTarihi" class="form-control datepicker kayitTarihi" value="{{$sontarih}}" type="date"  required>
    </div>
  </div>

  <div class="row">
    <label class="col-sm-4 custom-p-r">Ödeme Yönü:<span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <select name="odeme_yonu" class="form-select" required>
        <option selected value="1" {{$cash_transaction_id->odemeYonu == 1 ? 'selected' : ''}}>Gelen Ödeme</option>
        <option value="2" {{$cash_transaction_id->odemeYonu == 2 ? 'selected' : ''}}>Giden Ödeme</option>
      </select>
    </div>
  </div>

  <div class="row">
    <label class="col-sm-4 custom-p-r">Ödeme Şekli:<span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <select name="odeme_sekli" class="form-select odemeSekli" required> 
        @foreach($payment_methods as $method)
          <option value="{{$method->id}}" {{$method->id == $cash_transaction_id->odemeSekli ? 'selected' : ''}}>{{$method->odemeSekli}}</option>
        @endforeach
      </select>
    </div>
  </div>

  <div class="row">
    <label class="col-sm-4 custom-p-r">Ödeme Türü:<span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <select name="odeme_turu" class="form-select odemeTuru"  disabled>
        <option selected value="">-Seçiniz-</option>
        @foreach($payment_types as $type)
          <option value="{{$type->id}}"  {{$type->id == $cash_transaction_id->odemeTuru ? 'selected' : ''}}>{{$type->odemeTuru}}</option>
        @endforeach
      </select>
      <input type="hidden" name="odeme_turu" value="{{$cash_transaction_id->odemeTuru}}">
    </div>
  </div>

  @if (strpos($cash_payment_id["cevaplar"], '6') !== false)
    <div class="row form-group">
      <label class="col-sm-4 custom-p-r">Cihazlar<span style="font-weight: bold; color: red;">*</span></label>
      <div class="col-md-8 custom-p-l">
        <select class="form-select cihazlar" name="cihazlar" style="font-weight: 500" disabled>
          <option value="">-Seçiniz-</option>
          @foreach ($cihazlar as $item)
            <option value="{{$item->id}}" {{$item->id == $cash_transaction_id->cihaz ? 'selected' : ''}}>{{$item->cihaz}}</option>
          @endforeach
        </select>
        <input type="hidden" name="cihazlar" value="{{$cash_transaction_id->cihaz}}">
      </div>
    </div>
  @endif

  @if (strpos($cash_payment_id["cevaplar"], '5') !== false)
    <div class="row form-group">
      <label class="col-sm-4 custom-p-r">Markalar<span style="font-weight: bold; color: red;">*</span></label>
      <div class="col-md-8 custom-p-l">
        <select class="form-select markalar" name="markalar" style="font-weight: 500" disabled>
          <option value="">-Seçiniz-</option>
          @foreach ($markalar as $item)
            <option value="{{$item->id}}" {{$item->id == $cash_transaction_id->marka ? 'selected' : ''}}>{{$item->marka}}</option>
          @endforeach
        </select>
        <input type="hidden" name="markalar" value="{{$cash_transaction_id->marka}}">
      </div>
    </div>
  @endif

  @if (strpos($cash_payment_id["cevaplar"], '4') !== false)
    <div class="row form-group">
      <label class="col-sm-4 custom-p-r">Tedarikçiler<span style="font-weight: bold; color: red;">*</span></label>
      <div class="col-md-8 custom-p-l">
        <select class="form-select tedarikciler" name="tedarikciler" style="font-weight: 500">
          <option value="">-Seçiniz-</option>
          @foreach ($tedarikciler as $item)
            <option value="{{$item->id}}" {{$item->id == $cash_transaction_id->tedarikci ? 'selected' : ''}}>{{$item->tedarikci}}</option>
          @endforeach
        </select>
      </div>
    </div>
  @endif

  @if (strpos($cash_payment_id["cevaplar"], '3') !== false)
    <div class="row ">
      <div class="col-sm-4 custom-p-r"><label>Servis</label></div>
      <div class="col-sm-8 custom-p-l" style="align-items: center; justify-content: space-between; display: flex;">
    <input type="text" name="servis" class="form-control servis" style="font-weight: 500; width: fit-content; display: inline-block;" data-id="" autocomplete="off" value="{{$cash_transaction_id->servis}}" disabled>
    <div class="text-danger mt-1" id="servisHata"></div>
    <a href="{{ route('all.services', [$firma->id, 'did' => $cash_transaction_id->servis]) }}" target="_blank" style="font-weight: 500; font-size: 12px; color: white; background-color: #f32f53; padding: 4px; border-radius: 5px; text-decoration: none;">Servisi Göster</a>
    <input type="hidden" name="servisler" value="{{$cash_transaction_id->servis}}">
</div>
    </div>
  @endif

  @if(strpos($cash_payment_id["cevaplar"], '2') !== false)
    <div class="row">
      <div class="col-sm-4 custom-p-r"><label>Personeller</label></div>
      <div class="col-sm-8 custom-p-l">
        <select class="form-select personeller" name="personeller" style="font-weight: 500">
          <option value="">-Seçiniz-</option>
          @foreach ($personeller as $personel)
            <option value="{{$personel->user_id}}" {{$personel->user_id == $cash_transaction_id->personel ? 'selected' : ''}}>{{$personel->name}}</option>
          @endforeach
        </select>
      </div>
    </div>
  @endif

  @if (strpos($cash_payment_id["cevaplar"], '1') !== false)
    <div class="row">
      <div class="col-sm-4 custom-p-r"><label>Açıklama</label></div>
      <div class="col-sm-8 custom-p-l">
        <input type="text" name="aciklama" class="form-control aciklama" value="{{$cash_transaction_id->aciklama}}" autocomplete="off" style="font-weight: 500">
      </div>
    </div>
  @endif

  <div class="row">
    <label class="col-sm-4 custom-p-r">Ödeme Durumu:<span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <select name="odeme_durum" class="form-select" required>
        <option value="1" {{$cash_transaction_id->odemeDurumu == "1" ? 'selected': ''}}>Tamamlandı</option>
        <option value="0" {{$cash_transaction_id->odemeDurumu == "0" ? 'selected' : ''}}>Tamamlanmadı</option>
      </select>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-sm-4 custom-p-r"><label>Tutar (₺) <span style="font-weight: bold; color: red;">*</span></label></div>
    <div class="col-sm-8 custom-p-l">
      <input type="number" step="0.01" name="fiyat" class="form-control fiyat" value="{{$cash_transaction_id->fiyat}}" placeholder="0.00" required>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-12 gonderBtn">
      <input type="hidden" name="id" value="{{ $cash_transaction_id->id }}">
      <input type="submit" class="btn btn-sm btn-info waves-effect waves-light" value="Kaydet">
    </div>
  </div>
</form>

<script>
  $('.fiyat').on('input', function () {
  let val = $(this).val();
  val = val.replace(/[^\d.,]/g, ''); // sadece sayı, . ve , kalsın
  $(this).val(val);
});

  $(document).ready(function () {
    $('#editDom').submit(function (event) {
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
<script type="text/javascript">
  $(document).ready(function () {
    $(".phone").mask("9999-999-9999");
    $(".saat").mask("00:00");
  });
  $(document).ready(function(e) {
    $('.datepicker').datepicker({
      language: 'tr',
      autoclose: true,
    });
  });
</script>

<script>
$(document).ready(function (e) {
  $("#editDom").submit(function (event) {
    event.preventDefault();
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
        if (data === false) {
          window.location.reload(true);
        } else {
          alert("Para hareketi güncellendi");
          $('#datatableKasa').DataTable().ajax.reload();
          $('#editCashTransactionsModal').modal('hide');  
        }
      },
      error: function (xhr, status, error) {
        alert("Güncelleme başarısız!");
        window.location.reload(true);
      },
    });
  });
});
</script>