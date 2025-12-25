<meta name="csrf-token" content="{{ csrf_token() }}">
<form method="post" id="addCall" action="{{ route('store.call', $firma->id)}}" enctype="multipart/form-data" class="needs-validation" novalidate>
    @csrf
    <input type="hidden" name="form_token" id="formTokenCall" value="">
    <div class="row">
        <label class="col-sm-4 custom-p-r">Servis Kaynağı<span style="font-weight: bold; color: red;">*</span></label>
        <div class="col-sm-8 custom-p-l">
        <select name="serviceResource" class="form-select" required>
            <option selected disabled value="">-Seçiniz-</option>
            @foreach($service_resources as $resource)
            <option value="{{$resource->id}}">{{$resource->kaynak}}</option>
            @endforeach
        </select>
        </div>
    </div> <!--end row-->

    <div class="row">
        <label class="col-sm-4 custom-p-r">Marka<span style="font-weight: bold; color: red;">*</span></label>
        <div class="col-sm-8 custom-p-l">
        <select name="deviceBrand" class="form-select" required>
            <option selected disabled value="">-Seçiniz-</option>
            @foreach($device_brands as $brand)
            <option value="{{$brand->id}}">{{$brand->marka}}</option>
            @endforeach
        </select>
        </div>
    </div> <!--end row-->

    <div class="row">
      <div class="col-md-4 custom-p-r rw1"><label style="text-align: left;width: auto;display: inline-block;margin: 0;">Yetkisi Servis Tel </label></div>
      <div class="col-md-8 custom-p-l">
        <input type="text" class="form-control markaTelefon" disabled>
      </div>
    </div>

    <div class="row form-group ">
      <div class="col-md-4 custom-p-r rw1"><label style="text-align: left;width: auto;display: inline-block;margin: 0;">Açıklama <span style="font-weight: bold; color: red;">*</span></label></div>
      <div class="col-md-8 custom-p-l">
        <input id="arizaSearch" type="text" name="cihazAriza"  class="form-control cihazAriza" autocomplete="off" required>
        <ul id="arizaResult" style="margin: 0;padding: 0"></ul>
      </div>
   </div>

    <div class="row">
      <div class="col-sm-12 gonderBtn">
        <input type="submit" class="btn btn-info btn-sm waves-effect waves-light" value="Kaydet">
      </div>
    </div>
  </form>
  
  <script>
    $(document).ready(function () {
      $('#addCall').submit(function (event) {
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
  // 4.1 CSRF token’u global ajax header’ına ekle
  $.ajaxSetup({
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  // 4.2 basit debounce helper’ı
  function debounce(func, wait){
      let timer; 
      return function(){
          clearTimeout(timer);
          timer = setTimeout(() => func.apply(this, arguments), wait);
      };
  }

  // 4.3 arıza arama
  $('#arizaSearch').on('keyup', debounce(function () {
      const term = $(this).val().trim();
      $('#arizaResult').empty();

      if (term.length < 2) return;          // 2 harften önce arama yapma

      $.get('{{ route("ariza.search", $firma->id) }}', { q: term }, function (list) {
          if (!list.length) return;

          list.forEach(item => {
              $('#arizaResult').append(
                  `<li class="list-group-item link-class" 
                        data-id="${item.id}" 
                        data-ariza="${item.ariza.replace(/"/g,'&quot;')}">
                       <span class="fw-semibold">${item.ariza}</span>
                   </li>`
              );
          });
      });
  }, 350));   // 350 ms gecikme

  // 4.4 liste öğesine tıklandığında input’u doldur
  $('#arizaResult').on('click', 'li', function () {
      $('#arizaSearch').val($(this).data('ariza'));
      $('#arizaResult').empty();
  });

  // 4.5 modal kapatılınca listeyi temizle
  $('#gelenCagriModal').on('hide.bs.modal', function () {
      $('#arizaResult').empty();
  });
</script>

  <script>
  // Tüm AJAX isteklerinde CSRF token otomatik gönderilsin
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  // Marka değiştiğinde telefon getir
  $(document).on('change', 'select[name="deviceBrand"]', function () {
    var brandId = $(this).val();
    if (!brandId) {
      $('.markaTelefon').val('');
      return;
    }

    $.ajax({
      url: '{{ route("get.brand.phone", $firma->id) }}',
      type: 'POST',
      data: { brand_id: brandId },
      success: function (res) {
        $('.markaTelefon').val(res.phone ?? '');
      },
      error: function (xhr) {
        console.error("Telefon getirilemedi:", xhr.responseText);
        $('.markaTelefon').val('');
      }
    });
  });
</script>
  
  <script>
$(document).ready(function() {
    let callFormSubmitting = false;
    
    // Benzersiz token oluştur
    function generateToken() {
        return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    // Sayfa yüklendiğinde ilk token'ı oluştur
    $('#formTokenCall').val(generateToken());
    
    // Token yenileme fonksiyonu
    function resetCallFormToken() {
        $('#formTokenCall').val(generateToken());
        callFormSubmitting = false;
        $('#addCall input[type="submit"]').prop('disabled', false).val('Kaydet');
    }
    
    // Form submit
    $('#addCall').on('submit', function(e) {
        e.preventDefault();
        
        // Token kontrolü
        if (callFormSubmitting) {
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
            e.stopPropagation();
            return false;
        }
        
        // Token işaretle ve butonu disable et
        callFormSubmitting = true;
        $(this).find('input[type="submit"]').prop('disabled', true);
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            success: function(response) {
                alert("Yeni çağrı başarıyla eklendi");
                $('#gelenCagriModal').modal('hide');
                window.location.reload();
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                alert("Çağrı eklenirken bir hata oluştu!");
                // Token'ı yenile
                resetCallFormToken();
            }
        });
    });
});
</script>