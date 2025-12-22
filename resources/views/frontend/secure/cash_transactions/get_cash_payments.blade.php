<div class="odemeAciklamalariAlt" id="odemeAciklamalariAlt">
    @if (strpos($cash_payment_id["cevaplar"], '6') !== false)
    <div class="row form-group">
      <label class="col-sm-4 custom-p-r">Cihazlar<span style="font-weight: bold; color: red;">*</span></label>
      <div class="col-md-8 custom-p-l">
        <select class="form-select cihazlar" name="cihazlar" style="font-weight: 500">
          <option value="">-Seçiniz-</option>
          @foreach ($cihazlar as $item)
            <option value="{{$item->id}}">{{$item->cihaz}}</option>
          @endforeach
        </select>
      </div>
    </div>
  @endif
    @if (strpos($cash_payment_id["cevaplar"], '5') !== false)
    <div class="row form-group">
      <label class="col-sm-4 custom-p-r">Markalar<span style="font-weight: bold; color: red;">*</span></label>
      <div class="col-md-8 custom-p-l">
        <select class="form-select markalar" name="markalar" style="font-weight: 500">
          <option value="">-Seçiniz-</option>
          @foreach ($markalar as $item)
            <option value="{{$item->id}}">{{$item->marka}}</option>
          @endforeach
        </select>
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
            <option value="{{$item->id}}">{{$item->tedarikci}}</option>
          @endforeach
        </select>
      </div>
    </div>
  @endif

  @if (strpos($cash_payment_id["cevaplar"], '3') !== false)
    <div class="row ">
      <div class="col-sm-4 custom-p-r"><label>Servis</label></div>
      <div class="col-sm-8 custom-p-l">
        <input type="text" name="servis" class="form-control servis" data-id="" autocomplete="off"  required>
        <div class="text-danger mt-1" id="servisHata"></div>
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
            <option value="{{$personel->user_id}}">{{$personel->name}}</option>
          @endforeach
        </select>
      </div>
    </div>
  @endif

  @if (strpos($cash_payment_id["cevaplar"], '1') !== false)
    <div class="row">
      <div class="col-sm-4 custom-p-r"><label>Açıklama</label></div>
      <div class="col-sm-8 custom-p-l">
        <input type="text" name="aciklama" class="form-control aciklama" autocomplete="off" style="font-weight: 500">
      </div>
    </div>
  @endif
</div>
