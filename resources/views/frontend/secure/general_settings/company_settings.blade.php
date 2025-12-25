<div class="row mt-3 mb-2">
  <div class="col-12">
    <div class=" d-sm-flex align-items-center justify-content-center">
      <h4 class="mb-sm-0 fw-bold text-gray " style="font-size: 19px;">Firma Ayarları</h4>
    </div>
  </div>
</div>
  
<div class="d-flex justify-content-center align-items-center w-100">
        <form id="companySettings" method="post" action="{{ route('update.firma',$firma->id)}}" enctype="multipart/form-data" class="needs-validation" novalidate style="width: 50%;">
          @csrf
          <input type="hidden" name="id" value="{{ $firma->id }}">
          
          <div class="row align-items-center border-bottom custom-border-bottom mb-1">
            <label class="col-sm-4 col-form-label">Kayıt Tarihi<span style="font-weight: bold; color: red;">*</span></label>
            <div class="col-sm-8">
              <input name="kayitTarihi" class="form-control datepicker kayitTarihi" type="date" value="{{\Carbon\Carbon::parse($firma->kayitTarihi)->format('Y-m-d')}}" style="border: 1px solid #ced4da;" required>
            </div>
          </div>

          <div class="row align-items-center border-bottom custom-border-bottom mb-1">
            <label class="col-sm-4 col-form-label">Firma Adı:</label>
            <div class="col-sm-8">
              <input class="form-control" name="company_name" type="text" value="{{ $firma->firma_adi}}" required>
            </div>
          </div>
          <!-- end row -->

          <div class="row align-items-center border-bottom custom-border-bottom mb-1">
            <label class="col-sm-4 col-form-label">Telefon</label>
            <div class="col-sm-4 col-6">
              <input name="tel1" class="form-control phone" type="text" placeholder="Telefon 1" value="{{$firma->tel1}}" required>
            </div>
            <div class="col-sm-4 col-6">
              <input name="tel2" class="form-control phone" type="text" placeholder="Telefon 2" value="{{$firma->tel2}}">
            </div>
          </div>

          <div class="row align-items-center  border-bottom custom-border-bottom mb-1">
            <div class="col-sm-4 col-form-label"><label>İl/İlçe</label></div>
            <div class="col-sm-4 col-6">
              <select name="il" id="sehirSelect" class="form-control form-select" style="width:100%!important;">
                <option value="" selected disabled>-Seçiniz-</option>
                @foreach($countries as $item)
                  <option value="{{ $item->id }}" {{$firma->il == $item->id ? 'selected' : ''}}>{{ $item->name}}</option>
                @endforeach
              </select>
            </div>
            <div class="col-sm-4 col-6 border-bottom custom-border-bottom mb-1">
              <select name="ilce" id="ilceSelect" class="form-control form-select" style="width:100%!important;">
                <option value="" selected disabled>-Seçiniz-</option>                              
              </select>
            </div>
          </div> 
  
          <div class="row align-items-center border-bottom custom-border-bottom mb-1">
            <label class="col-sm-4 col-form-label">Firma Adresi:</label>
            <div class="col-sm-8">
              <input class="form-control" name="company_address" type="text" value="{{ $firma->adres}}">
            </div>
          </div>
          <!-- end row -->

          <div class="row align-items-center border-bottom custom-border-bottom mb-1">
            <label class="col-sm-4 col-form-label">Email:</label>
            <div class="col-sm-8">
              <input class="form-control" name="company_email" type="email" value="{{ $firma->eposta}}">
            </div>
          </div>
          <!-- end row -->
  
          <div class="row  align-items-center border-bottom custom-border-bottom mb-1">
            <label class="col-sm-4 col-form-label">Web Sitesi:</label>
            <div class="col-sm-8">
              <input class="form-control" name="web_sitesi" type="text" value="{{ $firma->webSitesi}}">
            </div>
          </div>
          <!-- end row -->
  
          <div class="row align-items-center border-bottom custom-border-bottom mb-1">
            <label class="col-sm-4 col-form-label">İban:</label>
            <div class="col-sm-8">
              <input class="form-control" name="iban" type="text" value="{{ $firma->iban}}">
            </div>
          </div>
          <!-- end row -->
  
          <div class="row align-items-center border-bottom custom-border-bottom mb-1">
            <label class="col-sm-4 col-form-label">Vergi No/Dairesi:</label>
            <div class="col-sm-4 col-6">
              <input class="form-control" name="tax_no" type="text" value="{{ $firma->vergiNo}}">
            </div>
            <div class="col-sm-4 col-6">
              <input class="form-control" name="tax_office" type="text" value="{{ $firma->vergiDairesi}}">
            </div>
          </div>
          <!-- end row -->

          <div class="row align-items-center border-bottom custom-border-bottom mb-1">
            <label class="col-sm-4 col-form-label">Logo:</label>
            <div class="col-sm-8">
              <input class="form-control" name="logo" type="file" id="logo">
              @if($errors->has('image'))
                <div class="error">{{ $errors->first('image') }}</div>
              @endif
              <label class=" col-form-label">Not: Maksimum resim boyutu 2MB'tan fazla olmamalıdır.</label>
            </div>
          </div>
          <!-- end row -->

          <div class="row align-items-center border-bottom custom-border-bottom mb-1">
            <label class="col-sm-4 col-form-label"></label>
            <div class="col-sm-8">
              <img class="img-thumbnail" id="showImage" width="180" src="{{ asset($firma->logo) }}" data-holder-rendered="true">
            </div>
          </div>
          <!-- end row -->
  
          <div class="row align-items-center mt-2">
            <label class="col-sm-4 col-form-label"></label>
            <div class="col-sm-8">
              <input type="submit" class="btn btn-info waves-effect waves-light" value="Kaydet">
            </div>
          </div>
        </form>
  </div>

<script>
  $(document).ready(function() {
    var selectedCountryId ={{ $firma->il == '' ? '0' : $firma->il}} ;
    if(selectedCountryId){
      $.get("/get-states/" + selectedCountryId, function(data) {
        $.each(data, function(index, city) {
          ilceSelect.append(new Option(city.ilceName, city.id));
          if(city.id == {{ $firma->ilce == '' ? '0' : $firma->ilce}}){
            $("#ilceSelect").val(city.id).change();
          } 
        });
      });
    }
    // Ülke seçildiğinde
    $("#sehirSelect").change(function() {
      var selectedCountryId = $(this).val();
      // Şehirleri getir ve ikinci select'i güncelle
      $.get("/get-states/" + selectedCountryId, function(data) {
        var citySelect = $("#ilceSelect");
        citySelect.empty(); // Önceki seçenekleri temizle
        $.each(data, function(index, city) {         // 'each()' fonksiyonuyla dolaşılıp ikinci selecte eklenir.
          citySelect.append(new Option(city.ilceName, city.id));
        });
      });
    });
  });
</script>

<!-- burada javascript ile seçilen resmi görüntüledik -->
<script type="text/javascript">
  $(document).ready(function() {
    $('#logo').change(function(e) {
      var reader = new FileReader();
      reader.onload = function(e) {
        $('#showImage').attr('src', e.target.result);
      }
      reader.readAsDataURL(e.target.files['0']);
    });
  });
  </script>
  
<script>
  $(document).ready(function () {
    $('#companySettings').submit(function (event) {
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
  
{{-- <script>
  $(document).ready(function(){
    $('#companySettings').submit(function(e){
      e.preventDefault();

      // Form doğrulaması
      if (this.checkValidity() === false) {
        e.stopPropagation();
        alert("Lütfen formu doğru şekilde doldurun.");
        return;
      }

      // FormData nesnesi oluştur
      var formData = new FormData(this);

      // AJAX çağrısı
      $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        processData: false, // FormData nesnesini işlemesini kapat
        contentType: false, // İçerik türünü otomatik ayarla
        success: function(response) {
          alert("Firma bilgileri başarıyla güncellendi!");
        },
        error: function(xhr, status, error) {
          console.error(xhr.responseText);
          alert("Bir hata oluştu: " + error);
        }
      });
    });
  });
</script> --}}

  
  