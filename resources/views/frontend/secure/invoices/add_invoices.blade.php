@if(App\Services\InvoiceIntegrationFactory::hasIntegration($firma->id))
    <div class="alert alert-info mb-3">
        <i class="fas fa-sync-alt"></i> 
        <strong>Paraşüt Entegrasyonu Aktif:</strong> 
        Faturanız otomatik olarak Paraşüt'e gönderilecek ve e-Arşiv PDF'i oluşturulacaktır.
    </div>
@else
    <div class="alert alert-secondary mb-3">
        <i class="fas fa-info-circle"></i> 
        Fatura entegrasyonu aktif değil. Manuel olarak e-Arşiv PDF yüklemeniz gerekir.
    </div>
@endif

<form method="post" id="addInvo" action="{{ route('store.invoices', $firma->id)}}" enctype="multipart/form-data" class="needs-validation" novalidate>
  @csrf
  <input type="hidden" name="form_token" id="invoiceFormToken" value="">
  <div class="card card-invoices d-md-none d-flex  f5">
    <div class="ch1" style="padding: 3px 10px;">
      <div class="tarihWrap ">
        <label style="text-align: left;width: auto;display: inline-block;margin: 0; margin-right: 2px;">Tarih<span style="font-weight: bold; color: red;">*</span></label>
        <input type="date" name="faturaTarihi" class="form-control datepicker kayitTarihi"  value="{{date('Y-m-d')}}" style="width: 150px!important;display: inline-block;background:#fff" required>
      </div>

      <div class="clearfix"></div>
    </div>
  </div> 

  <div class="card card-invoices f2">
     <div class="card-header card-invoices-header d-flex flex-column flex-md-row justify-content-md-between align-items-md-center">
    <span>MÜŞTERİ BİLGİSİ</span>
    <div class="tarihWrap d-md-flex d-none mt-2 mt-md-0">
        <label style="text-align: left;width: auto;display: inline-block;margin: 0; margin-right: 2px;">Tarih<span style="font-weight: bold; color: red;">*</span></label>
        <input type="date" name="faturaTarihi" class="form-control datepicker kayitTarihi" value="{{date('Y-m-d')}}" style="width: 150px!important;display: inline-block;background:#fff" required>
    </div>
</div>
     <div class="card-body card-invoices-body">
        <div class="row">
           <div class="col-sm-6 s1">
              <div class="row form-group ">
                <div class="col-md-4 rw1"><label>Servis Ara</label></div>
                <div class="col-md-8 rw2 d-flex flex-wrap align-items-center gap-3">
                    <input id="search" type="text" name="servisid" class="form-control servisid" data-bs-id="" autocomplete="off" placeholder="Servis id / Müşteri Ara" style="flex: 1 1 auto; max-width: 160px;">

                    <a href="#" target="_blank" class="servisiAc btn btn-outline-danger px-2 py-1"style="font-size: 13px; line-height: 1.3;">Servisi Aç</a>
                </div>
              
                  <ul id="servisResult" class="list-group" style="display: none;"></ul>
           
              </div>

              <div class="row form-group">
                 <div class="col-md-4 rw1"><label>Müşteri Tipi <span style="font-weight: bold; color: red;">*</span></label></div>
                 <div class="col-md-8 rw2">
                   <select class="form-select musteriTipi" name="musteriTipi" required>
                     <option value="2">KURUMSAL</option>
                     <option value="1">BİREYSEL</option>
                   </select>
                 </div>
              </div>

              <div class="row form-group">
                 <div class="col-md-4 rw1"><label><span class="musteriAdiSpan">Müşteri Adı</span> <span style="font-weight: bold; color: red;">*</span></label></div>
                 <div class="col-md-8 rw2">
                   <input type="text" name="adSoyad" class="form-control buyukYaz adSoyad" data-id="" autocomplete="off" placeholder="Müşteri Adı">
                    
                </div>
              </div>
              <input type="hidden" name="mid" class="eskiMusteriId" value="">

              <div class="row form-group" id="tcNo" style="display: none;">
                 <div class="col-md-4 rw1"><label>T.C. No</label></div>
                 <div class="col-md-8 rw2">
                   <input type="number" name="tcNo" class="form-control tcNo" autocomplete="off" placeholder="Kimlik No">
                 </div>
              </div>

              <div class="row form-group" id="vergiBox">
                 <div class="col-md-4 rw1"><label>Vergi No/Dairesi</label></div>
                 <div class="col-md-4 col-6 rw2">
                    <input type="number" name="vergiNo" class="form-control vergiNo" placeholder="Vergi No" autocomplete="off">
                 </div>
                 <div class="col-md-4 col-6 rw2">
                    <input type="text" name="vergiDairesi" class="form-control buyukYaz vergiDairesi" placeholder="Vergi Dairesi" autocomplete="off">
                 </div>
              </div>
           </div>
           <div class="col-sm-6 s2">
              <div class="row form-group">
                 <div class="col-sm-2"><label>İl/İlçe</label></div>
                <div class="col-sm-5 col-6">
                <select name="il" id="country" class="form-control form-select il" style="width:100%!important;">
                    <option value="" selected disabled>-Seçiniz-</option>
                    @foreach($countries as $item)
                    <option value="{{ $item->id }}">{{ $item->name}}</option>
                    @endforeach
                </select>
                <input type="hidden" id="eskiİl" name="eskiİl" value="" />
                </div>
                <div class="col-sm-5 col-6">
                <select name="ilce" id="city" class="form-control form-select ilce" style="width:100%!important;">
                    <option value="" selected disabled>-Seçiniz-</option>                              
                </select>
                <input type="hidden" id="eskiIlce" name="eskiIlce" value="" />
                </div>
              </div>

              <div class="row form-group">
                 <div class="col-md-2 rw1"><label>Adres <span style="font-weight: bold; color: red;font-size:12px;">*</span></label></div>
                 <div class="col-md-10 rw2"><textarea name="adres" class="form-control buyukYaz adres" placeholder="Adres" rows="3" style="resize: none !important"></textarea></div>
              </div>
           </div>
        </div>
     </div>
  </div>

  <div class="card card-invoices f2">
    <div class="card-body card-invoices-body">
      <div class="row form-group head">
        <div class="col-3 rw1 "><label>Cinsi</label></div>
        <div class="col-3 rw2 "><label>Miktar</label></div>
        <div class="col-3 rw3 "><label>Fiyat</label></div>
        <div class="col-3 rw4 "><label>Tutar</label></div>
      </div>

      <div class="satirBody mb-1">
        <div class="row form-group fatura-mobil-add">
          <div class="col-3 rw1 "><input type="text" name="aciklama[]" class="form-control aciklama aciklama0 buyukYaz" placeholder="Ürün" autocomplete="off"></div>
          <div class="col-3 rw2 "><input type="text" name="miktar[]" onkeyup="sayiKontrol(this)" class="form-control miktar miktar0" autocomplete="off"></div>
          <div class="col-3 rw3 "><input type="text" name="fiyat[]" onkeyup="sayiKontrol(this)" class="form-control fiyat fiyat0" autocomplete="off"></div>
          <div class="col-3 rw4 "><input type="text" name="tutar[]" onkeyup="sayiKontrol(this)" class="form-control tutar tutar0" autocomplete="off"></div>
        </div>
      </div>

      <div class="row form-group" style="margin: 0;border: 0;">
        <button type="button" class="col-xs-12 form-control btn btn-primary2 satirEkle" data-id="1" style="color: #fff;display: inline-block;">Satır Ekle</button>
      </div>
    </div>
  </div>
       
<div class="row cardRow1 mb-1 fatura-mobil-add">

    <div class="col-lg-6 mb-3 mb-lg-0 custom-p-m">
      <div class="card card-invoices f3 h-100"> 
        <div class="card-body card-invoices-body">

          {{-- <div class="row form-group" style="border:0">
            <div class="col-md-6 rw1"><input type="text" autocomplete="off" class="form-control kdvliFiyat" placeholder="KDV'li Fiyat"></div>
            <div class="col-md-6 rw2"><input type="text" class="form-control kdvsizFiyat" placeholder="KDV'siz Fiyat" disabled></div>
          </div> --}}

          <div class="row form-group" style="border:0">
            <div class="col-md-4 rw1"><label>Fatura No</label></div>
            <div class="col-md-8 rw2">
              @if(App\Services\InvoiceIntegrationFactory::hasIntegration($firma->id))
                <input type="text" name="faturaNumarasi" class="form-control buyukYaz faturaNumarasi" value="" disabled placeholder="Paraşüt tarafından atanacak">
              @else
                <input type="text" name="faturaNumarasi" class="form-control buyukYaz faturaNumarasi" value="">
              @endif
            </div>
          </div>

          <div class="row form-group" style="border:0">
    <div class="col-md-4 rw1"><label>Tevkifat Kodu</label></div>
    <div class="col-md-8 rw2">
        <select class="form-select tevkifatKodu" name="tevkifatKodu">
            <option value="" data-oran="0">Tevkifat Yok</option>
            @foreach($tevkifatKodlari as $kod)
                @if($kod->durum == 1)
                    <option value="{{ $kod->kodu }}" data-oran="{{ $kod->orani }}">
                        {{ $kod->kodu }} - {{ $kod->adi }} ({{ $kod->orani }}/10)
                    </option>
                @endif
            @endforeach
        </select>
    </div>
</div>

          <div class="row form-group" style="border:0">
    <div class="col-md-4 rw1"><label>KDV İstisna Kodu</label></div>
    <div class="col-md-8 rw2">
        <select class="form-select kdvKodu" name="kdvKodu">
            <option value="" data-kdv-oran="">KDV İstisnası Yok</option>
            @foreach($kdvKodlari as $kod)
                @if($kod->durum == 1)
                    <option value="{{ $kod->kodu }}" data-kdv-oran="{{ $kod->orani }}">
                        {{ $kod->kodu }} - {{ $kod->adi }} (%{{ $kod->orani }} KDV)
                    </option>
                @endif
            @endforeach
        </select>
    </div>
</div>

          <div class="row form-group" style="border:0">
            <div class="col-md-4 rw1"><label>KDV Açıklaması</label></div>
            <div class="col-md-8 rw2">
              <input type="text" name="kdvAciklama" class="form-control kdvAciklama">
            </div>
          </div>

          <div class="row form-group" style="border:0">
            <div class="col-md-4 rw1"><label>Fatura Açıklaması</label></div>
            <div class="col-md-8 rw2">
              <input type="text" name="faturaAciklama" class="form-control faturaAciklama">
            </div>
          </div>

          @if(!App\Services\InvoiceIntegrationFactory::hasIntegration($firma->id))
            <div class="row form-group" style="border:0">
              <div class="col-md-4 rw1"><label>Ödeme Şekli<span style="font-weight: bold; color: red;">*</span></label></div>
              <div class="col-md-8 rw2">
                <select class="form-select odemeSekilleri" name="odemeSekli" required>
                  <option value="">Seçiniz</option>
                  @foreach($payment_methods as $method)
                    <option value="{{$method->id}}">{{$method->odemeSekli}}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="row form-group" style="border:0">
              <div class="col-md-4 rw1"><label>Toplam Yazıyla</label></div>
              <div class="col-md-8 rw2"><input type="text" name="toplamYazi" autocomplete="off" class="form-control buyukYaz toplamYazi" required></div>
            </div>

            <div class="row form-group" style="border:0">
              <div class="col-md-4 rw1"><label>E-Arşiv<span style="font-weight: bold; color: red;">*</span></label></div>
              <div class="col-md-8 rw2">
                <input type="file" class="form-control" name="document" id="customFile" >
              </div>
            </div>
            @endif
        </div>
      </div>
    </div>

    <div class="col-lg-6 custom-p-r-m">
  <div class="card card-invoices f4 h-100">
    <div class="card-body card-invoices-body" style="padding:17px 5px">
      <div class="row form-group">
        <div class="col-md-4 rw1"><label>Toplam<span style="font-weight: bold; color: red;">*</span></label></div>
        <div class="col-md-8 rw2 custom-rw2"><input type="text" onkeyup="sayiKontrol(this)" name="toplam" autocomplete="off" class="form-control toplam" required></div>
      </div>

      @if(!App\Services\InvoiceIntegrationFactory::hasIntegration($firma->id))
      <div class="row form-group">
        <div class="col-md-4 rw1"><label>İndirim</label></div>
        <div class="col-md-8 rw2 custom-rw2"><input type="text" onkeyup="sayiKontrol(this)" name="indirim" autocomplete="off" class="form-control indirim" value="0.00"></div>
      </div>
      <div class="row form-group">
        <div class="col-md-4 rw1"><label>Ara Toplam</label></div>
        <div class="col-md-8 rw2 custom-rw2"><input type="text" onkeyup="sayiKontrol(this)" name="araToplam" autocomplete="off" class="form-control araToplam"></div>
      </div>
      @else
      {{-- Paraşüt aktifken hidden olarak gönder --}}
      <input type="hidden" name="indirim" class="indirim" value="0.00">
      <input type="hidden" name="araToplam" class="araToplam" value="0.00">
      @endif

      <div class="row form-group">
        <div class="col-md-2 rw1"><label>KDV %</label></div>
        <div class="col-md-2 col-6 rw2"><input type="text" onkeyup="sayiKontrol(this)" name="kdvTutar" autocomplete="off" class="form-control kdvTutar" value="20" ></div>
        <div class="col-md-8 custom-rw2 col-6 rw2"><input type="text" onkeyup="sayiKontrol(this)" name="kdv" class="form-control kdv" value="0"></div>
      </div>

          <div class="row form-group">
            <div class="col-md-6 rw1"><label>Tevkifat Oranı</label></div>
            <div class="col-md-2 col-6 rw2">
              <select class="form-select tevkifatOrani" name="tevkifatOrani">
                <option value="0">0</option>
                <option value="2">2/10</option>
                <option value="3">3/10</option>
                <option value="4">4/10</option>
                <option value="5">5/10</option>
                <option value="7">7/10</option>
                <option value="9">9/10</option>
                <option value="10">10/10</option>
              </select>
            </div>
            <div class="col-md-4 custom-rw2 col-6 rw2">
              <input type="text" class="form-control tevkifatTutari" disabled>
              <input type="hidden" name="tevkifatTutari" class="tevkifatTutari">
            </div>
          </div>

          <div class="row form-group" style="padding-bottom: 0">
            <div class="col-md-4 rw1"><label>Genel Toplam<span style="font-weight: bold; color: red;">*</span></label></div>
            <div class="col-md-8 rw2 custom-rw2"><input type="text" onkeyup="sayiKontrol(this)" name="genelToplam" autocomplete="off" class="form-control genelToplam" required></div>
          </div>
               
        </div>
      </div>
    </div>
</div>
    <div class="row">
      <div class="col-sm-12 gonderBtn">
        <input type="submit" class="btn btn-sm btn-info waves-effect waves-light" value="Kaydet">
      </div>
    </div>
  </div>
</form>

<script type="text/javascript">
  function sayiKontrol(v) {
    var isNum = /^[0-9-'.']*$/;
    if (!isNum.test(v.value)) { 
      v.value = v.value.replace(/[^0-9-',']/g, "");
    }                   
  }

  $('.buyukYaz').keyup(function(){
    this.value = this.value.toUpperCase();
  });

  $('.satirBody').on('keyup', '.buyukYaz', function () {
    this.value = this.value.toUpperCase();
  });
</script>

<script>
 $(document).ready(function (e) {
    var sonucToplam = 0;
    var sonuc = 0;
    var $form = $('#addInvo');

    $('.satirBody').keyup(function() {
      sonucToplam = 0;
      $('.miktar').each(function(index, data) {
        var fiyat = Number($(".fiyat"+index).val());
        var miktar = Number($(this).val());
        sonuc = fiyat*miktar;
        sonucToplam = sonucToplam + sonuc;
        $(".tutar"+index).val(sonuc)
        kdvHesapla(sonucToplam)
      });
    });

    function kdvHesapla(toplam){
      // ✅ Form içinden al
      var indirim = Number($form.find(".indirim").val()) || 0;
      var kdvTutar = Number($form.find(".kdvTutar").val()) || 20;
      var tevkifatOrani = Number($form.find(".tevkifatOrani").val()) || 0;
      var araToplam = toplam - indirim;
      var kdv = ((araToplam * kdvTutar) / 100);
      
      var tevkifatHesapla = 0;
      var genelToplam = 0;

      if (tevkifatOrani > 0) {
        tevkifatHesapla = (kdv * tevkifatOrani) / 10;
        genelToplam = araToplam + (kdv - tevkifatHesapla);
      } else {
        genelToplam = araToplam + kdv;
      }

      kdv = parseFloat(kdv.toFixed(2));
      tevkifatHesapla = parseFloat(tevkifatHesapla.toFixed(2));
      genelToplam = parseFloat(genelToplam.toFixed(2));

      // ✅ Form içine yaz
      $form.find(".toplam").val(toplam.toFixed(2));
      $form.find(".araToplam").val(araToplam.toFixed(2));
      $form.find(".kdv").val(kdv);
      $form.find(".tevkifatTutari").val(tevkifatHesapla);
      $form.find(".genelToplam").val(genelToplam);
    }

    // ✅ Event handler'ları da form-spesifik yap
    $form.find('.kdvTutar').on('keyup', function() {
      if (sonucToplam === 0) {
        return;
      }

      var indirim = Number($form.find(".indirim").val()) || 0;
      var kdvTutar = Number($form.find(".kdvTutar").val()) || 0;
      var tevkifatOrani = Number($form.find(".tevkifatOrani").val()) || 0;
      var araToplam = sonucToplam - indirim;
      var kdv = ((araToplam * kdvTutar) / 100);

      var tevkifatHesapla = 0;
      var genelToplam = 0;

      if (tevkifatOrani > 0) {
        tevkifatHesapla = (kdv * tevkifatOrani) / 10;
        genelToplam = araToplam + (kdv - tevkifatHesapla);
      } else {
        genelToplam = araToplam + kdv;
      }

      kdv = parseFloat(kdv.toFixed(2));
      tevkifatHesapla = parseFloat(tevkifatHesapla.toFixed(2));
      genelToplam = parseFloat(genelToplam.toFixed(2));

      $form.find(".araToplam").val(araToplam.toFixed(2));
      $form.find(".kdv").val(kdv);
      $form.find(".tevkifatTutari").val(tevkifatHesapla);
      $form.find(".genelToplam").val(genelToplam);
    });

    $form.find('.indirim').on('keyup', function() {
      if (sonucToplam === 0) {
        return;
      }

      var indirim = Number($form.find(".indirim").val()) || 0;
      var kdvTutar = Number($form.find(".kdvTutar").val()) || 20;
      var tevkifatOrani = Number($form.find(".tevkifatOrani").val()) || 0;
      var araToplam = sonucToplam - indirim;
      var kdv = ((araToplam * kdvTutar) / 100);

      var tevkifatHesapla = 0;
      var genelToplam = 0;

      if (tevkifatOrani > 0) {
        tevkifatHesapla = (kdv * tevkifatOrani) / 10;
        genelToplam = araToplam + (kdv - tevkifatHesapla);
      } else {
        genelToplam = araToplam + kdv;
      }

      kdv = parseFloat(kdv.toFixed(2));
      tevkifatHesapla = parseFloat(tevkifatHesapla.toFixed(2));
      genelToplam = parseFloat(genelToplam.toFixed(2));

      $form.find(".araToplam").val(araToplam.toFixed(2));
      $form.find(".kdv").val(kdv);
      $form.find(".tevkifatTutari").val(tevkifatHesapla);
      $form.find(".genelToplam").val(genelToplam);
    });

    // ✅ Tevkifat oranı değişimi
    $form.find('.tevkifatOrani').on('change', function() {
      if (sonucToplam === 0) {
        $form.find(".tevkifatTutari").val(0);
        return;
      }

      var indirim = Number($form.find(".indirim").val()) || 0;
      var kdvTutar = Number($form.find(".kdvTutar").val()) || 20;
      var tevkifatOrani = Number($(this).val()) || 0;
      var araToplam = sonucToplam - indirim;
      var kdv = ((araToplam * kdvTutar) / 100);

      var tevkifatHesapla = 0;
      var genelToplam = 0;

      if (tevkifatOrani > 0) {
        tevkifatHesapla = (kdv * tevkifatOrani) / 10;
        genelToplam = araToplam + (kdv - tevkifatHesapla);
      } else {
        genelToplam = araToplam + kdv;
      }

      kdv = parseFloat(kdv.toFixed(2));
      tevkifatHesapla = parseFloat(tevkifatHesapla.toFixed(2));
      genelToplam = parseFloat(genelToplam.toFixed(2));

      $form.find(".kdv").val(kdv);
      $form.find(".tevkifatTutari").val(tevkifatHesapla);
      $form.find(".genelToplam").val(genelToplam);
    });
  
    $(".satirEkle").click(function () {
      var dataNum = Number($(this).attr("data-id")); 
      var satirClone = `
        <div class="row form-group align-items-center satir fatura-mobil-add">
          <div class="col-3 rw1">
            <input type="text" name="aciklama[]" class="form-control aciklama aciklama${dataNum} buyukYaz" placeholder="Ürün" autocomplete="off">
          </div>
          <div class="col-3 rw2">
            <input type="text" name="miktar[]" onkeyup="sayiKontrol(this)" class="form-control miktar miktar${dataNum}" autocomplete="off">
          </div>
          <div class="col-3 rw3">
            <input type="text" name="fiyat[]" onkeyup="sayiKontrol(this)" class="form-control fiyat fiyat${dataNum}" autocomplete="off">
          </div>
          <div class="col-3 rw4">
            <input type="text" name="tutar[]" onkeyup="sayiKontrol(this)" class="form-control tutar tutar${dataNum}" autocomplete="off">
          </div>
        </div>
      `;  
      $(".satirBody").append(satirClone);
      $(this).attr("data-id", dataNum + 1);
    });
    
    $(document).on('click', '.satirSil', function () {
      $(this).closest('.satir').remove();
    });
});
</script>

<script>
  $(document).ready(function () {
    // YENİ EKLENEN: Müşteri Tipi değişimi
    $('#tcNo').hide();
    
    $('.musteriTipi').on('change', function() {
      var val = $(this).val();
      if (val == 2) {
        $('#vergiBox').show();
        $('#tcNo').hide();
      } else {
        $('#vergiBox').hide();
        $('#tcNo').show();
      }
    });

    // YENİ EKLENEN: KDV'li fiyat hesaplama
    $(".kdvliFiyat").on("input", function() {
      var kdvTutari = parseFloat($(this).val());
      if (!isNaN(kdvTutari)) {
        var kdvOrani = 0.20;
        var kdvsizFiyat = kdvTutari / (1 + kdvOrani);
        $(".kdvsizFiyat").val(kdvsizFiyat.toFixed(2));
      } else {
        $(".kdvsizFiyat").val("");
      }
    });

    

    // İl seçilince ilçeleri getir
    $('#country').change(function () {
      let selectedId = $(this).val();
      if (!selectedId) return;
      
      let citySelect = $('#city');
      citySelect.empty().append(new Option("Yükleniyor...", ""));
      
      $.get(`/get-states/${selectedId}`, function (data) {
        citySelect.empty().append(new Option("-Seçiniz-", ""));
        $.each(data, function (i, city) {
          citySelect.append(new Option(city.ilceName, city.id));
        });
        
        // Eğer eskiIlce varsa seç
        var eskiIlce = $('#eskiIlce').val();
        if (eskiIlce) {
          citySelect.val(eskiIlce);
        }
      }).fail(function () {
        citySelect.empty().append(new Option("Yüklenemedi", ""));
      });
    });

  });
</script>
<script>
$(document).ready(function () {
    let aramaZamanAsimi;

    // Servis Ara - Hem ID hem de Müşteri Adına göre arama
    $('.servisid').on("input", function () {
        var aramaMetni = $(this).val().trim();
        
        // Servisi Aç linkini güncelle
        $(".servisiAc").attr("href", "/{{$firma->id}}/servisler?did=" + aramaMetni);
        
        // Önceki aramayı iptal et
        clearTimeout(aramaZamanAsimi);
        
        // Servis listesini temizle
        $('#servisResult').hide().html('');
        
        if (aramaMetni.length < 2) {
            temizleFormAlanlari();
            return;
        }

        // 500ms bekle, sonra ara
        aramaZamanAsimi = setTimeout(function() {
            // Sayısal mı kontrol et
            var isSayisal = /^\d+$/.test(aramaMetni);
            
            $.ajax({
                url: "{{ route('fatura.musteri.getir', $firma->id) }}",
                type: "POST",
                data: {
                    servisId: isSayisal ? aramaMetni : null,
                    musteriAra: !isSayisal ? aramaMetni : null,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success && response.data) {
                        
                        // Birden fazla sonuç varsa (müşteri adıyla arama)
                        if (response.type === 'multiple') {
                            $('#servisResult').html('').show();
                            
                            $.each(response.data, function(index, servis) {
                                var kayitTarihi = new Date(servis.kayitTarihi).toLocaleDateString('tr-TR');
                                var listItem = `
                                    <li class="list-group-item servis-list-item" style="cursor: pointer; border-left: 3px solid #007bff;"
                                        data-servis-id="${servis.servis_id}"
                                        data-musteri-id="${servis.musteri_id}"
                                        data-musteri-tipi="${servis.musteriTipi}"
                                        data-ad-soyad="${servis.adSoyad}"
                                        data-tel1="${servis.tel1}"
                                        data-adres="${servis.adres}"
                                        data-il="${servis.il}"
                                        data-ilce="${servis.ilce}"
                                        data-vergi-no="${servis.vergiNo || ''}"
                                        data-vergi-dairesi="${servis.vergiDairesi || ''}"
                                        data-tc-no="${servis.tcNo || ''}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong style="color: #dc3545;">Servis #${servis.servis_id}</strong> - 
                                                <span style="font-weight: 500;">${servis.adSoyad}</span>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-phone"></i> ${servis.tel1 || 'Tel yok'} | 
                                                    <i class="fas fa-wrench"></i> ${servis.marka || ''} ${servis.cihaz || ''} |
                                                    <i class="fas fa-calendar"></i> ${kayitTarihi}
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-tools"></i> Arıza: ${servis.cihazAriza ? (servis.cihazAriza.length > 50 ? servis.cihazAriza.substring(0, 50) + '...' : servis.cihazAriza) : 'Belirtilmemiş'}
                                                </small>
                                            </div>
                                        </div>
                                    </li>
                                `;
                                $('#servisResult').append(listItem);
                            });
                            
                            // Uyarı mesajını kaldır
                            $('.servis-uyari').remove();
                            
                        } 
                        // Tek sonuç varsa (Servis ID ile arama)
                        else if (response.type === 'single') {
                            var musteri = response.data;
                            doldurMusteriBilgileri(musteri, aramaMetni);
                        }
                    } else {
                        temizleFormAlanlari();
                        $('.servis-uyari').remove();
                        $('.servisid').after(`<div class="servis-uyari" style="color:red;margin-top:5px;">Bu arama için sonuç bulunamadı.</div>`);
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Hata:', error);
                    temizleFormAlanlari();
                }
            });
        }, 500);
    });

    // Servis listesinden seçim yapıldığında
    $(document).on('click', '.servis-list-item', function() {
        var servisId = $(this).data('servis-id');
        var musteriData = {
            musteri_id: $(this).data('musteri-id'),
            musteriTipi: $(this).data('musteri-tipi'),
            adSoyad: $(this).data('ad-soyad'),
            tel1: $(this).data('tel1'),
            adres: $(this).data('adres'),
            il: $(this).data('il'),
            ilce: $(this).data('ilce'),
            vergiNo: $(this).data('vergi-no'),
            vergiDairesi: $(this).data('vergi-dairesi'),
            tcNo: $(this).data('tc-no')
        };
        
        // Servis ID'yi input'a yaz
        $('.servisid').val(servisId);
        
        // Müşteri bilgilerini doldur
        doldurMusteriBilgileri(musteriData, servisId);
        
        // Listeyi gizle
        $('#servisResult').hide().html('');
        $('.servis-uyari').remove();
    });

    // Müşteri bilgilerini form alanlarına doldurma fonksiyonu
    function doldurMusteriBilgileri(musteri, servisId) {
        $('.adSoyad').val(musteri.adSoyad);
        $('.adres').val(musteri.adres);
        $('.vergiNo').val(musteri.vergiNo || '');
        $('.vergiDairesi').val(musteri.vergiDairesi || '');
        $('.tcNo').val(musteri.tcNo || '');
        
        // Müşteri tipine göre alanları göster/gizle
        if (musteri.musteriTipi == 1) {
            $('.musteriTipi').val('1').trigger('change');
            $('#vergiBox').hide();
            $('#tcNo').show();
        } else {
            $('.musteriTipi').val('2').trigger('change');
            $('#vergiBox').show();
            $('#tcNo').hide();
        }
        
        // İl ve ilçe seçimi
        if (musteri.il) {
            $('#country').val(musteri.il).trigger('change');
            $('#eskiIlce').val(musteri.ilce);
            
            setTimeout(function() {
                if (musteri.ilce) {
                    $('#city').val(musteri.ilce);
                }
            }, 1000);
        }

        // ID'yi sakla
        $('.eskiMusteriId').val(musteri.musteri_id);
        
        // Servisi Aç linkini güncelle
        $(".servisiAc").attr("href", "/{{$firma->id}}/servisler?did=" + servisId);
        
        // Uyarı varsa kaldır
        $('.servis-uyari').remove();
    }

    // Form alanlarını temizleme fonksiyonu
    function temizleFormAlanlari() {
        $('.adSoyad').val('');
        $('.adres').val('');
        $('.vergiNo').val('');
        $('.vergiDairesi').val('');
        $('.tcNo').val('');
        $('#country').val('').trigger('change');
        $('#city').val('');
        $('.eskiMusteriId').val('');
        $('#servisResult').hide().html('');
        $('.servis-uyari').remove();
    }

    // Input dışına tıklandığında listeyi kapat
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.servisid, #servisResult').length) {
            $('#servisResult').hide();
        }
    });

    // Müşteri Tipi değişimi
    $('#tcNo').hide();
    
    $('.musteriTipi').on('change', function() {
        var val = $(this).val();
        if (val == 2) {
            $('#vergiBox').show();
            $('#tcNo').hide();
        } else {
            $('#vergiBox').hide();
            $('#tcNo').show();
        }
    });

    // KDV'li fiyat hesaplama
    $(".kdvliFiyat").on("input", function() {
        var kdvTutari = parseFloat($(this).val());
        if (!isNaN(kdvTutari)) {
            var kdvOrani = 0.20;
            var kdvsizFiyat = kdvTutari / (1 + kdvOrani);
            $(".kdvsizFiyat").val(kdvsizFiyat.toFixed(2));
        } else {
            $(".kdvsizFiyat").val("");
        }
    });

    // İl seçilince ilçeleri getir
    $('#country').change(function () {
        let selectedId = $(this).val();
        if (!selectedId) return;
        
        let citySelect = $('#city');
        citySelect.empty().append(new Option("Yükleniyor...", ""));
        
        $.get(`/get-states/${selectedId}`, function (data) {
            citySelect.empty().append(new Option("-Seçiniz-", ""));
            $.each(data, function (i, city) {
                citySelect.append(new Option(city.ilceName, city.id));
            });
            
            var eskiIlce = $('#eskiIlce').val();
            if (eskiIlce) {
                citySelect.val(eskiIlce);
            }
        }).fail(function () {
            citySelect.empty().append(new Option("Yüklenemedi", ""));
        });
    });

});
</script>
<script>
//E-Arşiv dosya türü ve boyut kontrolü
$(document).ready(function() {
    const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
    const allowedMimeTypes = [
        'application/pdf',
        'image/jpeg',
        'image/jpg', 
        'image/png'
    ];

    $('#customFile').on('change', function() {
        const file = this.files[0];
        
        if (!file) return;

        const fileName = file.name.toLowerCase();
        const fileExtension = fileName.split('.').pop();
        const fileMimeType = file.type;

        // Uzantı kontrolü
        if (!allowedExtensions.includes(fileExtension)) {
            alert('Hatalı dosya türü!\n' + 
                  'Dosya: ' + file.name + 
                  '\n Sadece PDF, JPG, JPEG ve PNG dosyaları yükleyebilirsiniz.');
            this.value = '';
            return false;
        }
        
        // MIME type kontrolü
        if (!allowedMimeTypes.includes(fileMimeType)) {
            alert('Hatalı dosya türü!\n' + 
                  'Dosya: ' + file.name + 
                  '\n Sadece PDF, JPG, JPEG ve PNG dosyaları yükleyebilirsiniz.');
            this.value = '';
            return false;
        }

        // Dosya boyutu kontrolü (5MB)
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('Dosya boyutu çok büyük!\n' + 
                  'Dosya: ' + file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)' +
                  '\n Maksimum dosya boyutu 5MB olmalıdır.');
            this.value = '';
            return false;
        }
    });
});
</script>

<script>
$(document).ready(function() {
    $('#addInvo').submit(function(e) {
        e.preventDefault();
        
        // Form validasyonu
        let formIsValid = true;
        $(this).find('input, select, textarea').each(function() {
            if ($(this).prop('required') && !$(this).val()) {
                formIsValid = false;
                $(this).css('border-color', 'red');
            } else {
                $(this).css('border-color', '');
            }
        });

        if (!formIsValid) {
            alert('Lütfen zorunlu alanları doldurun.');
            return;
        }

        var formData = new FormData(this);
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    
                    if (response.storage_warning) {
                        alert(response.storage_warning);
                    }
                    
                    window.location.reload();
                } else {
                    alert(response.message || 'Bir hata oluştu');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errorResponse = xhr.responseJSON;
                    if (errorResponse && errorResponse.error_type === 'storage_limit_exceeded') {
                        alert(errorResponse.message);
                    } else if (errorResponse && errorResponse.message) {
                        alert(errorResponse.message);
                    } else {
                        alert('Form doğrulama hatası');
                    }
                } else {
                    alert('Sunucu hatası oluştu');
                }
            }
        });
    });
});
</script>

<script>
$(document).ready(function() {
    let formSubmitting = false;
    
    function generateToken() {
        return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    $('#invoiceFormToken').val(generateToken());
    
    $('#addInvo').submit(function(event) {
        if (formSubmitting) {
            event.preventDefault();
            return false;
        }
        
        var formIsValid = true;
        
        $(this).find('input, select, textarea').each(function () {
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
        
        formSubmitting = true;
        $(this).find('input[type="submit"]').prop('disabled', true);
        
        setTimeout(function() {
            $('#invoiceFormToken').val(generateToken());
            formSubmitting = false;
            $('#addInvo input[type="submit"]').prop('disabled', false);
        }, 5000);
        
        return true;
    });
});
</script>
<script>
$(document).ready(function() {
    let isSubmitting = false;
    let shouldReload = false;
    
    // Form submit edildiğinde flag'i ayarla
    $('#addInvo').submit(function() {
        isSubmitting = true;
    });
    
    // Modal kapatılmaya çalışıldığında
    $('#addInvoiceModal').on('hide.bs.modal', function(e) {
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
    $('#addInvoiceModal').on('hidden.bs.modal', function() {
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
    var $form = $('#addInvo');
    var sonucToplam = 0;
    
    // Sayfa yüklendiğinde mevcut toplamı hesapla
    setTimeout(function() {
        sonucToplam = 0;
        $form.find('.miktar').each(function(index) {
            var fiyat = parseFloat($form.find(".fiyat").eq(index).val()) || 0;
            var miktar = parseFloat($(this).val()) || 0;
            sonucToplam += fiyat * miktar;
        });
    }, 500);
    
    // ⭐ Genel hesaplama fonksiyonu
    function hesaplaGenelToplam() {
    // Önce satır toplamını hesapla
    sonucToplam = 0;
    
    // ✅ Düzeltilmiş: index bazlı class kullanımı (fiyat0, fiyat1, miktar0, miktar1 vb.)
    var satirSayisi = $form.find('.satirBody .row.form-group').length;
    
    for (var i = 0; i < satirSayisi; i++) {
        var fiyat = parseFloat($form.find(".fiyat" + i).val()) || 0;
        var miktar = parseFloat($form.find(".miktar" + i).val()) || 0;
        var tutar = fiyat * miktar;
        sonucToplam += tutar;
        $form.find(".tutar" + i).val(tutar.toFixed(2));
    }
        var toplam = sonucToplam;
        var indirim = parseFloat($form.find(".indirim").val()) || 0;
        var kdvOraniVal = $form.find(".kdvTutar").val();
        var kdvOrani = (kdvOraniVal !== '' && kdvOraniVal !== null) ? parseFloat(kdvOraniVal) : 20;
        var tevkifatOrani = parseInt($form.find(".tevkifatOrani").val()) || 0;
        
        var araToplam = toplam - indirim;
        var kdvTutari = (araToplam * kdvOrani) / 100;
        
        var tevkifatTutari = 0;
        var genelToplam = 0;
        
        if (tevkifatOrani > 0) {
            tevkifatTutari = (kdvTutari * tevkifatOrani) / 10;
            genelToplam = araToplam + (kdvTutari - tevkifatTutari);
        } else {
            genelToplam = araToplam + kdvTutari;
        }
        
        // Değerleri yuvarla
        kdvTutari = parseFloat(kdvTutari.toFixed(2));
        tevkifatTutari = parseFloat(tevkifatTutari.toFixed(2));
        genelToplam = parseFloat(genelToplam.toFixed(2));
        
        // ✅ Tüm alanları güncelle
        $form.find(".toplam").val(toplam.toFixed(2));
        $form.find(".araToplam").val(araToplam.toFixed(2));
        $form.find(".kdv").val(kdvTutari.toFixed(2));                    // KDV tutarı
        $form.find(".tevkifatTutari").val(tevkifatTutari.toFixed(2));    // Hidden input
        $form.find(".tevkifatTutariGoster").val(tevkifatTutari.toFixed(2)); // Görünen input
        $form.find(".genelToplam").val(genelToplam.toFixed(2));
        
        console.log('Hesaplama yapıldı:', {
            toplam: toplam,
            indirim: indirim,
            araToplam: araToplam,
            kdvOrani: kdvOrani,
            kdvTutari: kdvTutari,
            tevkifatOrani: tevkifatOrani,
            tevkifatTutari: tevkifatTutari,
            genelToplam: genelToplam
        });
    }
    
    // ⭐ KDV Kodu (İstisna) değiştiğinde
    $form.find('.kdvKodu').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const kdvOrani = selectedOption.data('kdv-oran');
        
        console.log('KDV Kodu değişti:', {
            kod: $(this).val(),
            oran: kdvOrani
        });
        
        if (kdvOrani !== undefined && kdvOrani !== '' && kdvOrani !== null) {
            const yeniOran = parseInt(kdvOrani);
            const $kdvTutarInput = $form.find('.kdvTutar');
            const currentKdv = parseInt($kdvTutarInput.val()) || 20;
            
            if (yeniOran !== currentKdv) {
                Swal.fire({
                    title: 'KDV Oranı Güncellendi',
                    html: `Seçilen KDV istisna kodu için KDV oranı <strong>%${yeniOran}</strong> olarak ayarlandı.`,
                    icon: 'info',
                    confirmButtonText: 'Tamam'
                });
                
                // ✅ KDV oranını güncelle
                $kdvTutarInput.val(yeniOran);
            }
            
            // ✅ Her durumda hesaplamayı yenile
            hesaplaGenelToplam();
        } else {
            // KDV istisnası kaldırıldı, varsayılan %20'ye dön
            $form.find('.kdvTutar').val(20);
            hesaplaGenelToplam();
        }
    });
    
    // ⭐ KDV Oranı manuel değiştiğinde
    $form.find('.kdvTutar').on('change keyup', function() {
        hesaplaGenelToplam();
    });
    
    // ⭐ Tevkifat Kodu değiştiğinde
    $form.find('.tevkifatKodu').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const tevkifatOrani = parseInt(selectedOption.data('oran')) || 0;
        const $tevkifatOraniSelect = $form.find('.tevkifatOrani');
        const currentOran = parseInt($tevkifatOraniSelect.val()) || 0;
        
        console.log('Tevkifat Kodu değişti:', {
            kod: $(this).val(),
            oran: tevkifatOrani
        });
        
        if (tevkifatOrani > 0) {
            if (currentOran !== tevkifatOrani) {
                Swal.fire({
                    title: 'Tevkifat Oranı Güncellendi',
                    html: `Seçilen tevkifat kodu için oran <strong>${tevkifatOrani}/10</strong> olarak ayarlandı.`,
                    icon: 'info',
                    confirmButtonText: 'Tamam'
                });
                
                $tevkifatOraniSelect.val(tevkifatOrani);
            }
        } else {
            $tevkifatOraniSelect.val(0);
        }
        
        // ✅ Hesaplamayı yenile
        hesaplaGenelToplam();
    });
    
    // ⭐ Tevkifat Oranı manuel değiştirildiğinde
    $form.find('.tevkifatOrani').on('change', function() {
        const selectedOran = parseInt($(this).val()) || 0;
        const $tevkifatKoduSelect = $form.find('.tevkifatKodu');
        const selectedKodu = $tevkifatKoduSelect.val();
        
        if (selectedKodu) {
            const koduOrani = parseInt($tevkifatKoduSelect.find('option:selected').data('oran')) || 0;
            
            if (selectedOran !== koduOrani && selectedOran > 0) {
                Swal.fire({
                    title: 'Uyarı!',
                    html: `Seçilen tevkifat kodu için doğru oran <strong>${koduOrani}/10</strong> olmalıdır.<br><br>Mevcut seçiminiz: <strong>${selectedOran}/10</strong>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Doğru Oranı Kullan',
                    cancelButtonText: 'Bu Şekilde Kalsın',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(this).val(koduOrani);
                        hesaplaGenelToplam();
                    }
                });
            }
        }
        
        // ✅ Hesaplamayı yenile
        hesaplaGenelToplam();
    });
    
    // ⭐ İndirim değiştiğinde
    $form.find('.indirim').on('keyup change', function() {
        hesaplaGenelToplam();
    });
    
    // ⭐ Satır değerleri değiştiğinde
    $form.find('.satirBody').on('keyup change', '.miktar, .fiyat', function() {
        hesaplaGenelToplam();
    });
    
    // ⭐ Form submit öncesi kontrol
    $form.on('submit', function(e) {
        const tevkifatKodu = $form.find('.tevkifatKodu').val();
        const tevkifatOrani = parseInt($form.find('.tevkifatOrani').val()) || 0;
        
        if (tevkifatKodu) {
            const koduOrani = parseInt($form.find('.tevkifatKodu option:selected').data('oran')) || 0;
            
            if (tevkifatOrani !== koduOrani) {
                e.preventDefault();
                
                Swal.fire({
                    title: 'Hata!',
                    html: `Tevkifat kodu (${tevkifatKodu}) için oran <strong>${koduOrani}/10</strong> olmalıdır.<br><br>Lütfen oranı düzeltin veya tevkifat kodunu kaldırın.`,
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                });
                
                return false;
            }
        }
        
        if (tevkifatOrani > 0 && !tevkifatKodu) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Uyarı!',
                text: 'Tevkifat oranı seçtiniz ancak tevkifat kodu seçmediniz. Lütfen ilgili tevkifat kodunu seçin.',
                icon: 'warning',
                confirmButtonText: 'Tamam'
            });
            
            return false;
        }
    });
});
</script>
