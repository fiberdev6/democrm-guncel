<div class="row mt-3 mb-2">
  <div class="col-12">
    <div class=" d-sm-flex align-items-center justify-content-between">
      <h4 class="mb-sm-0 fw-bold text-gray border-bottom pb-2" style="font-size: 15px;">Yazıcı Fiş Tasarımı</h4>
    </div>
  </div>
</div>
        <form id="yaziciFisTasarimi" method="post" action="{{ route('update.receipt.design',$firma->id)}}" enctype="multipart/form-data" class="needs-validation" novalidate style="width: 50%;">
          @csrf
          <input type="hidden" name="id" value="{{ $receiptDesign->id ?? '' }}">
  
          <div class="row mb-3">
            <div class="col-sm-6">
              <textarea class="form-control  mesaj" name="mesaj" type="text" rows="10" style="resize: none;width: 330px;height: 510px;"  required>{{$receiptDesign->fisTasarimi ?? ''}}</textarea>
            </div>
            <div class="col-sm-6">
              <label style="display: block;margin-bottom: 5px">Kullanmak İstediğiniz Yazıcı</label>
                <select class="form-control yaziciBoyut" name="yaziciBoyut" style="width: 150px;">
                  <option value="58" {{ ($receiptDesign->boyut ?? null) == 58 ? 'selected' : '' }}>58mm Yazıcı</option>
                  <option value="80" {{ ($receiptDesign->boyut ?? null) == 80 ? 'selected' : '' }}>80mm Yazıcı</option>
                </select>
                <br>

                <label style="display: block;margin-bottom: 10px">Fiş Üzerindeki Değişkenler</label>
                <label style="display: block;margin: 0;">[SNO]</label>
                <span style="display: block;margin-bottom: 5px;font-size: 13px">Servis no çıkartır.</span>

                <label style="display: block;margin: 0;">[FIRMAADI]</label>
                <span style="display: block;margin-bottom: 5px;font-size: 13px">Firmanın kayıtlı adını çıkartır.</span>

                <label style="display: block;margin: 0;">[TEL]</label>
                <span style="display: block;margin-bottom: 5px;font-size: 13px">Firmanın kayıtlı telefonunu çıkartır.</span>

                <label style="display: block;margin: 0;">[MUSTERIBILGILERI]</label>
                <span style="display: block;margin-bottom: 5px;font-size: 13px">Müşteri bilgilerini çıkartır.</span>

                <label style="display: block;margin: 0;">[CIHAZBILGILERI]</label>
                <span style="display: block;margin-bottom: 5px;font-size: 13px">Cihaz bilgilerini çıkartır.</span>

                <label style="display: block;margin: 0;">[YAPILANISLEMLER]</label>
                <span style="display: block;margin-bottom: 5px;font-size: 13px">Yapılan son 3 işlemi çıkartır.</span>

                <label style="display: block;margin: 0;">[KASAHAREKETLERI]</label>
                <span style="display: block;margin-bottom: 5px;font-size: 13px">Para hareketlerini çıkartır.</span>

                <label style="display: block;margin: 0;">[TEKNISYENADI]</label>
                <span style="display: block;margin-bottom: 5px;font-size: 13px">Teknisyen adını çıkartır.</span>

                <label style="display: block;margin: 0;">[TARIHSAAT]</label>
                <span style="display: block;margin-bottom: 10px;font-size: 13px">Tarih saati çıkartır.</span>

                <label style="display: block;margin: 0;color: red;cursor: pointer;font-weight: 500;" class="ornekSayfaBtn">- Örnek Sayfa -</label>
                <br>


<textarea class="ornekSayfa58" style="display:none;">
[FIRMAADI]
TEL : [TEL]
ADRES : [ADRES]
--------------------------------
BEYAZ ESYA - KLIMA - KOMBI - TV
================================
- MUSTERI BILGISI - 
--------------------------------
[MUSTERIBILGILERI]
================================
- CIHAZ BILGISI - 
--------------------------------
[CIHAZBILGILERI]
================================
- YAPILAN ISLEMLER - 
--------------------------------
[YAPILANISLEMLER]
================================
- KASA HAREKETLERI - 
--------------------------------
[KASAHAREKETLERI]
================================
- TEKNISYEN ADI VE IMZASI - 
--------------------------------
[TEKNISYENADI]
TARIH : [TARIHSAAT]



================================
- MUSTERI ADI VE IMZASI - 
--------------------------------
[MUSTERIADI]
TARIH : [TARIHSAAT]



================================
</textarea>
<textarea class="ornekSayfa80" style="display:none;">
[FIRMAADI]
TEL : [TEL]
ADRES : [ADRES]
-------------------------------------
BEYAZ ESYA - KLIMA - KOMBI - TV
=====================================
- MUSTERI BILGISI - 
-------------------------------------
[MUSTERIBILGILERI]
=====================================
- CIHAZ BILGISI - 
-------------------------------------
[CIHAZBILGILERI]
=====================================
- YAPILAN ISLEMLER - 
-------------------------------------
[YAPILANISLEMLER]
=====================================
- KASA HAREKETLERI - 
-------------------------------------
[KASAHAREKETLERI]
=====================================
- TEKNISYEN ADI VE IMZASI - 
-------------------------------------
[TEKNISYENADI]
TARIH : [TARIHSAAT]



================================
- MUSTERI ADI VE IMZASI - 
--------------------------------
[MUSTERIADI]
TARIH : [TARIHSAAT]



================================
</textarea>
            </div>
          </div>
          <!-- end row -->

          <div class="row">
            <label class="col-sm-4 col-form-label"></label>
            <div class="col-sm-8">
              <input type="submit" class="btn btn-info waves-effect waves-light" value="Kaydet">
            </div>
          </div>
        </form>
    
<script>
  $(document).ready(function () {
    $('#yaziciFisTasarimi').submit(function (event) {
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
    $('#yaziciFisTasarimi').submit(function(e){
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
            alert("Servis form bilgileri güncellendi");
          },
          error: function(xhr, status, error) {
            console.error(xhr.responseText);
          }
        });
      }
    });
  });
</script>
  
<script type="text/javascript">
  $(document).ready(function(){
    $(".yaziciBoyut").on("change", function () {
      var width = $(this).val();
      if(width=="58"){
        var ornekSayfa58 = $(".ornekSayfa58").val();
        $("#yaziciFisTasarimi textarea.mesaj").val(ornekSayfa58);
        $("#yaziciFisTasarimi textarea.mesaj").css("width", "315px");
      }else if(width=="80"){
        var ornekSayfa80 = $(".ornekSayfa80").val();
        $("#yaziciFisTasarimi textarea.mesaj").val(ornekSayfa80);
        $("#yaziciFisTasarimi textarea.mesaj").css("width", "360px");
      }
    });

    $(".ornekSayfaBtn").click(function(){
      var ornekSayfa58 = $(".ornekSayfa58").val();
      $("#yaziciFisTasarimi textarea.mesaj").val(ornekSayfa58);
      $('#yaziciFisTasarimi .yaziciBoyut').val("58").trigger('change');

    });

  });
</script>
    