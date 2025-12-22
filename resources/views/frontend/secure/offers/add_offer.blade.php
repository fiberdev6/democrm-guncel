
<form method="post" id="addOffer" class="servisModal" action="{{ route('store.offer', $firma->id)}}" enctype="multipart/form-data" class="needs-validation" novalidate>
    @csrf
    <input type="hidden" name="form_token" id="offerFormToken" value="">
    <div class="row gx-4" style="margin: 0">
      <div class="card card-offer col-sm-6 card1">
        <div class="card-header card-offer-header" style="color: black;font-weight:bold">Müşteri Bilgileri</div>
        <div class="card-body">
          <div class="row form-group ">
            <label class="col-sm-3 col-form-label rw1">Müşteri<span style="font-weight: bold; color: red;">*</span></label>
            <div class="col-md-9 m-p">
              <input id="search" type="text" name="adSoyad" class="form-control adSoyad rw2" data-id="" autocomplete="off" placeholder="Müşteri Adı" required>
              <input type="hidden" name="musteri" class="mid" />
              <ul id="result" style="margin: 0; padding: 0"></ul>
            </div>
          </div>
  
          <div class="row form-group">
            <div class="col-md-3 rw1 col-form-label"><label>Müşteri Bilgileri</label></div>
            <div class="col-md-9 m-p rw2 col-form-label"><textarea class="form-control musBilgileri" disabled style="height: 77px;resize: none !important"></textarea></div>
          </div>
        </div>
      </div>
  
      <div class="card card-offer col-sm-6 card2">
        <div class="card-header card-offer-header" style="color: black;font-weight:bold">Teklif Bilgileri</div>
        <div class="card-body">  
        <div class="row form-group">
            <div class="col-md-3 rw1 col-form-label"><label><span class="musteriAdiSpan">Tarih</span> <span style="font-weight: bold; color: red;">*</span></label></div>
            <div class="col-md-9 m-p rw2">
              <input name="kayitTarihi" class="form-control datepicker kayitTarihi" type="date" value="{{date('Y-m-d')}}" style="display: inline-block;background:#fff;text-align:left" required>
            </div>
          </div>  
          <div class="row form-group">
            <div class="col-md-3 rw1 col-form-label"><label>Başlık 1</label></div>
            <div class="col-md-9 m-p rw2 col-form-label">
              <input type="text" name="baslik1" class="form-control baslik1" placeholder="Başlık 1" value="Teknik Servis">
            </div>
          </div> 
          <div class="row form-group">
            <div class="col-md-3 rw1 col-form-label"><label>Başlık 2</label></div>
            <div class="col-md-9 m-p rw2 col-form-label">
              <input type="text" name="baslik2" class="form-control baslik2" placeholder="Başlık 2" value="Teklif Formu">
            </div>
          </div>
          
        </div>
      </div>
    </div>
  
    <div class="card card-offer my-2 card3">
      <div class="card-body ">
        <div class="row form-group head">
          <div class="col-5 rw1 col-form-label"><label>Cinsi</label></div>
          <div class="col-2 rw1 col-form-label custom-padding-offer"><label>Miktar</label></div>
          <div class="col-2 rw1 col-form-label custom-padding-offer"><label>Fiyat</label></div>
          <div class="col-3 rw1 col-form-label custom-padding-offer"><label>Tutar</label></div>
        </div>
  
        <div class="satirBody">
          <div class="row form-group">
            <div class="col-5 rw1 col-form-label"><input type="text" name="aciklama[]" class="form-control aciklama aciklama0" placeholder="Ürün" autocomplete="off" required></div>
            <div class="col-2 col-form-label custom-gutter"><input type="text" name="miktar[]" onkeyup="sayiKontrol(this)" class="form-control miktar miktar0" autocomplete="off" required></div>
            <div class="col-2 rw3 col-form-label custom-gutter"><input type="text" name="fiyat[]" onkeyup="sayiKontrol(this)" class="form-control fiyat fiyat0" autocomplete="off" required></div>
            <div class="col-3 rw4 col-form-label custom-gutter"><input type="text" name="tutar[]" onkeyup="sayiKontrol(this)" class="form-control tutar tutar0" autocomplete="off" required></div>
          </div>
        </div>
  
        <div class="row form-group">
          <button type="button" class="col-xs-12 form-control btn btn-primary2 satirEkle" data-id="1" style="color: #fff;display: inline-block;margin-top: 5px">Satır Ekle</button>
        </div>
      </div>
    </div>
  
    <div class="row" style="margin: 0">
      <div class="card card-offer col-sm-6 card4">
        <div class="card-body card-offer-body">
          <div class="row form-group" style="border:0">
            <div class="col-md-4 rw1 col-form-label"><label>Durum</label></div>
            <div class="col-md-8 m-p rw2">
              <select class="form-select durum" name="durum">
                <option value="0">Beklemede</option>
                <option value="1">Onaylandı</option>
                <option value="2">Onaylanmadı</option>
                <option value="3">Cevap Gelmedi</option>
              </select>
            </div>
          </div>
  
          <div class="row form-group" style="border:0">
            <div class="col-md-4 rw1 col-form-label"><label>Toplam Yazıyla</label></div>
            <div class="col-md-8 m-p rw2 col-form-label"><input type="text" name="toplamYazi" autocomplete="off" class="form-control toplamYazi"></div>
          </div>
  
          <div class="row form-group" style="border:0">
            <div class="col-md-4 rw1 col-form-label"><label>Döviz Kuru</label></div>
            <div class="col-md-8 m-p rw2 col-form-label">
              <input type="text" onkeyup="sayiKontrol(this)" name="dovizKuru" autocomplete="off" class="form-control dovizKuru">
            </div>
          </div>
        </div>
      </div>
  
      <div class="card card-offer col-sm-6 card5">
        <div class="card-body card-offer-body">
          <div class="row form-group">
            <div class="col-md-4 rw1 col-form-label"><label>Toplam</label></div>
            <div class="col-md-8 custom-rw2 m-p rw2 col-form-label"><input type="text" onkeyup="sayiKontrol(this)" name="toplam" autocomplete="off" class="form-control toplam"></div>
          </div>
          
          <div class="row form-group">
            <div class="col-md-1 rw1 col-form-label"><label>KDV</label></div>
              <div class="col-md-3 m-p rw2 col-form-label col-6"><input type="text" onkeyup="sayiKontrol(this)" name="kdvTutar" autocomplete="off" class="form-control kdvTutar" value="20"></div>
              <div class="col-md-8 custom-rw2 m-p rw2 col-form-label col-6"><input type="text" onkeyup="sayiKontrol(this)" name="kdv" class="form-control kdv"></div>
            </div>
  
            <div class="row form-group" style="padding-bottom: 0">
              <div class="col-md-4 m-p rw1 col-form-label"><label>Genel Toplam</label></div>
              <div class="col-md-8 rw2 custom-rw2 m-p col-form-label"><input type="text" onkeyup="sayiKontrol(this)" name="genelToplam" autocomplete="off" class="form-control genelToplam"></div>
            </div>
          </div>
        </div>
  
        <textarea name="aciklamalar" class="form-control aciklamalar mt-2" rows="6" placeholder="Açıklamalar"></textarea>
      </div>
  
      <div class="row">
        <div class="col-sm-12 gonderBtn">
          <input type="submit" class="btn btn-sm btn-info waves-effect waves-light" value="Kaydet">
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
  </script>
   
 <script>

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
      // ✅ AJAX ile dinamik müşteri arama
      $('#search').keyup(function () {
        $('#result').html('');
        var searchField = $('#search').val();
        
        if (searchField.length > 2) { // 3 karakterden sonra ara
          $.ajax({
            url: "{{ route('search.customer', $firma->id) }}",
            method: "POST",
            data: {
              musteriGetir: searchField,
              _token: "{{ csrf_token() }}"
            },
            success: function (data) {
              $('#result').html('');
              
              if (data.length === 0) {
                $('#result').append('<li class="list-group-item">Sonuç bulunamadı</li>');
                return;
              }
              
              $.each(data, function (key, value) {
                var tip = value.musteriTipi == "1" ? "Bireysel" : "Kurumsal";
                var ilceAdi = value.state ? value.state.ilceName : '';
                var ilAdi = value.country ? value.country.name : '';
                var adresFormatli = (value.adres && value.adres.trim() !== "") ? value.adres : '';
                
                var adresDisplay = adresFormatli 
                  ? adresFormatli + " - " + ilceAdi + "/" + ilAdi 
                  : ilceAdi + "/" + ilAdi;
                
                $('#result').append(
                  '<li class="list-group-item link-class" ' +
                  'data-id="' + value.id + '" ' +
                  'data-adSoyad="' + value.adSoyad + '" ' +
                  'data-tel="' + value.tel1 + '" ' +
                  'data-adres="' + adresDisplay + '">' +
                  '<span style="font-weight:500;">Ad Soyad: </span>' + value.adSoyad + 
                  ' (' + tip + ')<br>' +
                  '<span style="font-weight:500;">Telefon: </span>' + value.tel1 + '<br>' +
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
        }
      });

      // Müşteri seçme
      $('#result').on('click', 'li.link-class', function () {
        var click_id = $(this).attr('data-id');
        var click_adSoyad = $(this).attr('data-adSoyad');
        var click_tel = $(this).attr('data-tel');
        var click_adres = $(this).attr('data-adres');
        
        $('.mid').val(click_id);
        $('.adSoyad').val(click_adSoyad);
        $('#addOffer .musBilgileri').val(click_adSoyad + "\n" + click_tel + "\n" + click_adres);
        $("#result").html('');
      });

      // Dışarı tıklanınca listeyi kapat
      $(document).click(function (e) {
        if (!$(e.target).closest('.adSoyad, #result').length) {
          $("#result").html('');
        }
      });
    });
</script>
  
<script>
    $(document).ready(function () {
      $('#addOffer').submit(function (event) {
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
    $(document).ready(function () {
      $('#addOffer').submit(function (event) {
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
    $(document).ready(function () {
  function kdvHesapla(toplam) {
    let kdvOran = parseFloat($(".kdvTutar").val()) || 0;
    let kdv = (toplam * kdvOran) / 100;
    let genelToplam = toplam + kdv;

    $(".toplam").val(toplam.toFixed(2));
    $(".kdv").val(kdv.toFixed(2));
    $(".genelToplam").val(genelToplam.toFixed(2));
  }

  function hesaplaTumSatirlar() {
    let toplam = 0;
    $(".satirBody .row.form-group").each(function () {
      let miktar = parseFloat($(this).find(".miktar").val()) || 0;
      let fiyat = parseFloat($(this).find(".fiyat").val()) || 0;
      let tutar = miktar * fiyat;

      $(this).find(".tutar").val(tutar.toFixed(2));
      toplam += tutar;
    });

    kdvHesapla(toplam);
  }

  // Dinamik event delegation
  $(document).on("keyup change", ".miktar, .fiyat", function () {
    hesaplaTumSatirlar();
  });

  $(document).on("keyup change", ".kdvTutar", function () {
    hesaplaTumSatirlar();
  });

  // Satır ekle
  $(".satirEkle").click(function () {
    var dataNum = Number($(this).attr("data-id"));
    var yeniSatir = `
      <div class="row form-group">
        <div class="col-5 rw1">
          <input type="text" name="aciklama[]" class="form-control aciklama" placeholder="Ürün" autocomplete="off">
        </div>
        <div class="col-2  custom-gutter">
          <input type="text" name="miktar[]" class="form-control miktar" autocomplete="off">
        </div>
        <div class="col-2 rw3 custom-gutter">
          <input type="text" name="fiyat[]" class="form-control fiyat" autocomplete="off">
        </div>
        <div class="col-3 rw4 custom-gutter">
          <input type="text" name="tutar[]" class="form-control tutar" autocomplete="off" readonly>
        </div>
      </div>`;
    $(".satirBody").append(yeniSatir);
    $(this).attr("data-id", dataNum + 1);
  });
});
  </script>
  

  
  <script>
    $(document).ready(function(){
      $('#addOffer').submit(function(e){
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
            alert("Teklif başarıyla eklendi");
            $('#datatableOffer').DataTable().ajax.reload();
            $('#addOfferModal').modal('hide');
          },
          error: function(xhr, status, error) {
            console.error(xhr.responseText);
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
    $('#offerFormToken').val(generateToken());
    
    // Form submit
    $('#addOffer').submit(function(event) {
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
            $('#offerFormToken').val(generateToken());
            formSubmitting = false;
            $('#addOffer input[type="submit"]').prop('disabled', false);
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
    $('#addOffer').submit(function() {
        isSubmitting = true;
    });
    
    // Modal kapatılmaya çalışıldığında
    $('#addOfferModal').on('hide.bs.modal', function(e) {
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
    $('#addOfferModal').on('hidden.bs.modal', function() {
        isSubmitting = false;
        if (shouldReload) {
            shouldReload = false;
            location.reload(); // Sayfayı yenile
        }
    });
});
</script>