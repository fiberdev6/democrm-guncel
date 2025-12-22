
<script type="text/javascript">
    function sayiKontrol(v) {
      var isNum = /^[0-9-'.']*$/;
      if (!isNum.test(v.value)) { 
        v.value = v.value.replace(/[^0-9-',']/g, "");
      }                   
    }
  </script>
  
  <form method="post" id="editOffer" class="servisModal" action="{{ route('update.offer', $firma->id)}}" enctype="multipart/form-data" class="needs-validation" novalidate>
    @csrf
    <div class="row" style="margin: 0">
      <div class="card card-offer col-sm-6 card1">
        <div class="card-header card-offer-header" style="color: black;font-weight:bold">Müşteri Bilgileri</div>
        <div class="card-body card-offer-body">
          <div class="row form-group ">
            <label class="col-sm-4 rw1 col-form-label">Müşteri<span style="font-weight: bold; color: red;">*</span></label>
            <div class="col-md-8 rw2">
              <input id="search" type="text" name="adSoyad" class="form-control adSoyad" data-id="" autocomplete="off" placeholder="Müşteri Adı" value="{{$musteri->adSoyad}}" required>
              <input type="hidden" name="musteri" class="mid" value="{{$musteri->id}}"/>
              <ul id="result" style="margin: 0; padding: 0"></ul>
            </div>
          </div>
  
          <div class="row form-group">
            <div class="col-md-4 rw1 col-form-label"><label>Müşteri Bilgileri</label></div>
            <div class="col-md-8 rw2 col-form-label"><textarea class="form-control musBilgileri" disabled style="height: 77px;resize: none !important">{{$musteri->adSoyad}}
            0{{$musteri->tel1}} 
            {{$musteri->adres}} - {{$musteri->state->ilceName}}/{{$musteri->country->name}}</textarea>
            </div>
          </div>
        </div>
      </div>
  
      <div class="card card-offer col-sm-6 card2">
        <div class="card-header card-offer-header" style="color: black;font-weight:bold">Fatura Bilgileri</div>
        <div class="card-body card-offer-body">
          @php 
            $sontarih = \Carbon\Carbon::parse($offer_id->created_at)->format('Y-m-d');
          @endphp
          <div class="row form-group">
            <div class="col-md-3 rw1 col-form-label"><label><span class="musteriAdiSpan">Tarih</span> <span style="font-weight: bold; color: red;">*</span></label></div>
            <div class="col-md-9 rw2 d-flex gap-2">
              <input name="kayitTarihi" class="form-control datepicker"  type="date" value="{{$sontarih}}" style="width: 110px!important;display: inline-block;background:#fff;text-align:center" required>
              <input type="text" class="form-control bg-danger" readonly="" value="{{$personel->name}}" style="width: 120px;display: inline-block;color: #fff;text-align: center;">
            </div>
          </div>
        
          <div class="row form-group">
            <div class="col-md-3 rw1 col-form-label"><label>Başlık 1</label></div>
            <div class="col-md-9 rw2 col-form-label">
              <input type="text" name="baslik1" class="form-control baslik2" placeholder="Başlık 1" value="Teknik Servis">
            </div>
          </div>
          <div class="row form-group">
            <div class="col-md-3 rw1 col-form-label"><label>Başlık 2</label></div>
            <div class="col-md-9 rw2 col-form-label">
              <input type="text" name="baslik2" class="form-control baslik2" placeholder="Başlık 2" value="Teklif Formu">
            </div>
          </div>
        </div>
      </div>
    </div>
  
    <div class="card card-offer card3 my-2">
      <div class="card-body card-offer-body">
        <div class="row form-group head">
          <div class="col-5 rw1 col-form-label"><label>Cinsi</label></div>
          <div class="col-2 rw2 col-form-label "><label>Miktar</label></div>
          <div class="col-2 rw3 col-form-label"><label>Fiyat</label></div>
          <div class="col-3 rw4 col-form-label"><label>Tutar</label></div>
        </div>
  
        <div class="satirBody">
          @php
            $i = -1;
          @endphp
          @foreach($offer_products as $product)
            @php
                $i++;
            @endphp
            <div class="row form-group">
              <div class="col-5 rw1 col-form-label"><input type="text" name="aciklama[]" class="form-control buyukYaz aciklama aciklama{{$i}}" value="{{$product->urun}}" placeholder="{{$i}}.Ürün" autocomplete="off"></div>
              <div class="col-2 rw2 col-form-label custom-gutter"><input type="text" name="miktar[]" onkeyup="sayiKontrol(this)" class="form-control buyukYaz miktar miktar{{$i}}" value="{{$product->miktar}}" autocomplete="off"></div>
              <div class="col-2 rw3 col-form-label custom-gutter"><input type="text" name="fiyat[]" onkeyup="sayiKontrol(this)" class="form-control buyukYaz fiyat fiyat{{$i}}" value="{{$product->fiyat}}" autocomplete="off"></div>
              <div class="col-3 rw4 col-form-label custom-gutter"><input type="text" name="tutar[]" onkeyup="sayiKontrol(this)" class="form-control buyukYaz tutar tutar{{$i}}" value="{{$product->tutar}}" autocomplete="off"></div>       
            </div>
          @endforeach
        </div>
  
        <div class="row form-group">
          <button type="button" class="col-xs-12 form-control btn btn-primary2 satirEkle" data-id="{{$i+1}}" style="color: #fff;display: inline-block;margin-top: 5px">Satır Ekle</button>
        </div>
  
      </div>
    </div>
  
    <div class="row" style="margin: 0">
      <div class="card card-offer col-sm-6 card4">
        <div class="card-body card-offer-body">
          <div class="row form-group" style="border:0">
            <div class="col-md-4 rw1 col-form-label"><label>Durum</label></div>
            <div class="col-md-8 rw2">
              <select class="form-select durum" name="durum">
                <option value="0" {{$offer_id->durum == "0" ? 'selected' : ''}}>Beklemede</option>
                <option value="1" {{$offer_id->durum == "1" ? 'selected' : ''}}>Onaylandı</option>
                <option value="2" {{$offer_id->durum == "2" ? 'selected' : ''}}>Onaylanmadı</option>
                <option value="3" {{$offer_id->durum == "3" ? 'selected' : ''}}>Cevap Gelmedi</option>
              </select>
            </div>
          </div>
  
          <div class="row form-group" style="border:0">
            <div class="col-md-4 rw1 col-form-label"><label>Toplam Yazıyla</label></div>
            <div class="col-md-8 rw2 col-form-label"><input type="text" name="toplamYazi" autocomplete="off" class="form-control toplamYazi" value="{{$offer_id->toplamYazi}}"></div>
          </div>
  
          <div class="row form-group" style="border:0">
            <div class="col-md-4 rw1 col-form-label"><label>Döviz Kuru</label></div>
            <div class="col-md-8 rw2 col-form-label">
              <input type="text" onkeyup="sayiKontrol(this)" name="dovizKuru" value="{{$offer_id->dovizKuru}}" autocomplete="off" class="form-control dovizKuru">
            </div>
          </div>
        </div>
      </div>
  
      <div class="card card-offer col-sm-6 card5">
        <div class="card-body card-offer-body">
          <div class="row form-group">
            <div class="col-md-4 rw1 col-form-label"><label>Toplam</label></div>
            <div class="col-md-8 rw2 custom-rw2 col-form-label"><input type="text" onkeyup="sayiKontrol(this)" value="{{$offer_id->toplam}}" name="toplam" autocomplete="off" class="form-control toplam"></div>
          </div>
          
          <div class="row form-group">
            <div class="col-md-1 rw1 col-form-label"><label>KDV</label></div>
            <div class="col-md-3 rw2 col-form-label col-6"><input type="text" onkeyup="sayiKontrol(this)" name="kdvTutar" autocomplete="off" class="form-control kdvTutar" value="{{$offer_id->kdv}}"></div>
            <div class="col-md-8 rw2 custom-rw2 col-form-label col-6"><input type="text" onkeyup="sayiKontrol(this)" name="kdv" class="form-control kdv" value="{{$offer_id->kdvTutar}}"></div>
          </div>
  
          <div class="row form-group" style="padding-bottom: 0">
            <div class="col-md-4 rw1 col-form-label"><label>Genel Toplam</label></div>
            <div class="col-md-8 rw2 custom-rw2 col-form-label"><input type="text" onkeyup="sayiKontrol(this)" name="genelToplam" value="{{$offer_id->genelToplam}}" autocomplete="off" class="form-control genelToplam"></div>
          </div>
        </div>
      </div>
  
      <textarea name="aciklamalar" class="form-control aciklamalar mt-2" rows="6" placeholder="Açıklamalar">{!! $offer_id->aciklamalar !!}</textarea>
    </div>
  
    <div class="row">
      
      <div class="col-sm-12 gonderBtn">
          <input type="hidden" name="id" value="{{$offer_id->id}}">
  
          <a href="{{route('offer.pdf',[$firma->id,$offer_id->id])}}" class="btn btn-sm btn-warning me-1" target="_blank">Yazdır</a>
  
        <input type="submit" class="btn btn-sm btn-info waves-effect waves-light" value="Kaydet">
        
      </div>
    </div>
  
  </form>
  
  <script>
    var musteriListesi = @json($musteriler);
  
    function turkceKucukHarfeDonustur(text) {
      if (!text) return '';
  
      return text.replace(/Ğ/g, 'ğ')
                 .replace(/Ü/g, 'ü')
                 .replace(/Ş/g, 'ş')
                 .replace(/İ/g, 'i')
                 .replace(/Ö/g, 'ö')
                 .replace(/Ç/g, 'ç')
                 .toLowerCase();
    }
    
    $(document).ready(function () {
      $('#search').keyup(function () {
        $('#result').html('');
        var searchField = turkceKucukHarfeDonustur($('#search').val());
          var veriler = 'musteriGetir=' + searchField;
          if (searchField.length > 2) {
            var filteredMusteriler = musteriListesi.filter(function (musteri) {
              var adiKucukHarf = turkceKucukHarfeDonustur(musteri.adSoyad);
              return adiKucukHarf.includes(searchField);
            });
            $.each(filteredMusteriler, function (key, value) {
              var tip = value.musteriTipi == "1" ? "Bireysel" : "Kurumsal";
              var ilceAdi = value.ilce ? value.state.ilceName : '';
              var ilAdi = value.il ? value.country.name : '';
              var adresFormatli = (value.adres && value.adres.trim() !== "") ? value.adres : ''; // null veya boşsa boş bırak

              // Adresin formatını oluşturma
              var adresDisplay = adresFormatli ? adresFormatli + " - " + ilceAdi + "/" + ilAdi : ilceAdi + "/" + ilAdi;
              $('#result').append('<li class="list-group-item link-class" data-id="' + value.id + '" data-adSoyad="' + value.adSoyad + '" data-tel="' + value.tel1 + '" data-adres="' + adresDisplay + '" ><span style="font-weight:500;">Ad Soyad: </span>' + value.adSoyad  +' <br><span style="font-weight:500;">Telefon: </span>' + value.tel1 + '<br><span style="font-weight:500;">Adres: </span>' + adresDisplay + '</li>');
            });
          }
        });
        $('#result').on('click', 'li', function () {
          var click_id = $(this).attr('data-id');
          var click_adSoyad = $(this).attr('data-adSoyad');
          var click_firmaAdi = $(this).attr('data-firmaAdi');
          var click_tel = $(this).attr('data-tel');
          var click_adres = $(this).attr('data-adres');
          $('.mid').attr('value', click_id);
          $('.adSoyad').val(click_adSoyad);
          $('#addOffer .musBilgileri').val(click_adSoyad+"\n"+click_tel+"\n"+click_adres);
          $("#result").html('');
        });
  
        $(document).click(function (e) {
          if (!$(e.target).closest('.adSoyad').length) {
            $("#result").html('');
          }
        });
      });
  </script>
  
  <script>
    $(document).ready(function () {
      $('#editOffer').submit(function (event) {
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
  function formatToTwoDecimalPlaces(value) {
      return Number(value).toFixed(2);
    }
    
    $(document).ready(function (e) {
      var sonucToplam = 0;
      var sonuc = 0;
      setTimeout(function (){
        $('.miktar').each(function(index, data) {
          var fiyat = Number($(".fiyat"+index).val());
          var miktar = Number($(this).val());
          sonuc = fiyat*miktar;
          sonucToplam = sonucToplam + sonuc;
          $(".tutar"+index).val(formatToTwoDecimalPlaces(sonuc));
        });     
      }, 500); 
  
      $('.satirBody').keyup(function() {
        sonucToplam = 0;
        $('.miktar').each(function(index, data) {
          var fiyat = Number($(".fiyat"+index).val());
          var miktar = Number($(this).val());
          sonuc = fiyat*miktar;
          sonucToplam = sonucToplam + sonuc;
          $(".tutar"+index).val(formatToTwoDecimalPlaces(sonuc))
          kdvHesapla(sonucToplam)
        });
      });
  
      function kdvHesapla(toplam){
        var kdvTutar = Number($(".kdvTutar").val());
        var kdv = (((toplam)*kdvTutar)/100);
        var araToplam = (toplam);
        var genelToplam = ((toplam) + kdv);
  
        $(".toplam").val(formatToTwoDecimalPlaces(toplam));
        $(".genelToplam").val(formatToTwoDecimalPlaces(genelToplam));
        $(".kdv").val(formatToTwoDecimalPlaces(kdv));
      }
  
      $('.kdvTutar').on('keyup', function() {
        var kdvTutar = Number($(".kdvTutar").val());
        var kdv = (((sonucToplam)*kdvTutar)/100);
        var genelToplam = ((sonucToplam) + kdv);
  
        $(".genelToplam").val(formatToTwoDecimalPlaces(genelToplam));
        $(".kdv").val(formatToTwoDecimalPlaces(kdv));
      });
  
      $('.indirim').on('keyup', function() {
        var kdvTutar = Number($(".kdvTutar").val());
        var kdv = (((sonucToplam)*kdvTutar)/100);
        var genelToplam = ((sonucToplam) + kdv);
  
        $(".genelToplam").val(formatToTwoDecimalPlaces(genelToplam));
        $(".kdv").val(formatToTwoDecimalPlaces(kdv));
      });
  
      $(".satirEkle").click(function(){
        var dataNum = Number($(this).attr("data-id"));
        var satirClone = '<div class="row form-group"><div class="col-5 rw1"><input type="text" name="aciklama[]" class="form-control aciklama" placeholder="Ürün" autocomplete="off"></div><div class="col-2 rw2  custom-gutter"><input type="text" name="miktar[]" onkeyup="sayiKontrol(this)" class="form-control miktar miktar'+dataNum+'" autocomplete="off"></div><div class="col-2 rw3 custom-gutter"><input type="text" name="fiyat[]" onkeyup="sayiKontrol(this)" class="form-control fiyat fiyat'+dataNum+'" autocomplete="off"></div><div class="col-3 rw4 custom-gutter"><input type="text" name="tutar[]" onkeyup="sayiKontrol(this)" class="form-control tutar tutar'+dataNum+'" autocomplete="off"></div></div>';
        $(".satirBody").append(satirClone);
        dataNum = dataNum+1;
        $(this).attr("data-id",dataNum);
      });
  
      //Virgülleri nokta yapıyor
      $("input:text").keyup(function() {
        $(this).val($(this).val().replace(/[,]/g, "."));
      });
    });
  </script>
  
  <script type="text/javascript">
    function sayiKontrol(v) {
      var isNum = /^[0-9-'.']*$/;
      if (!isNum.test(v.value)) { 
        v.value = v.value.replace(/[^0-9-',']/g, "");
      }                   
    }
  </script>
  
  <script>
    $(document).ready(function (e) {
    $("#editOffer").submit(function (event) {
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
            alert("Teklif bilgileri güncellendi");
            $('#datatableOffer').DataTable().ajax.reload();
            $('#editOfferModal').modal('hide');
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