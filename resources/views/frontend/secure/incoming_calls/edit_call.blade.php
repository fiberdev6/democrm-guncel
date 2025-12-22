<meta name="csrf-token" content="{{ csrf_token() }}">
<form method="post" id="editCall" action="{{ route('update.call', $firma->id)}}" enctype="multipart/form-data" class="needs-validation" novalidate>
    @csrf
    <div class="row">
        <label class="col-sm-4">Servis Kaynağı<span style="font-weight: bold; color: red;">*</span></label>
        <div class="col-sm-8">
        <select name="serviceResource" class="form-select" required>
            <option selected disabled value="">-Seçiniz-</option>
            @foreach($service_resources as $resource)
            <option value="{{$resource->id}}" {{$resource->id == $call_id->servisKaynak ? 'selected' : ''}}>{{$resource->kaynak}}</option>
            @endforeach
        </select>
        </div>
    </div> <!--end row-->

    <div class="row">
        <label class="col-sm-4">Marka<span style="font-weight: bold; color: red;">*</span></label>
        <div class="col-sm-8">
        <select name="deviceBrand" class="form-select" required>
            <option selected disabled value="">-Seçiniz-</option>
            @foreach($device_brands as $brand)
            <option value="{{$brand->id}}" {{$brand->id == $call_id->marka ? 'selected' : ''}}>{{$brand->marka}}</option>
            @endforeach
        </select>
        </div>
    </div> <!--end row-->

    <div class="row">
    <label class="col-sm-4">Personel<span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8">
        <select class="form-select" disabled>
            @foreach($operators as $item)
                @if($item->user_id == $call_id->kayitAlan)
                    <option selected>{{$item->name}}</option>
                @else
                
                @endif
            @endforeach
        </select>
        <input type="hidden" name="personel" value="{{ $call_id->kayitAlan }}">
    </div>
</div>

    <div class="row form-group ">
      <div class="col-md-4 rw1"><label style="text-align: left;width: auto;display: inline-block;margin: 0;">Açıklama <span style="font-weight: bold; color: red;">*</span></label></div>
      <div class="col-md-8">
        <input id="arizaSearch" type="text" name="cihazAriza" value="{{$call_id->ariza}}" class="form-control cihazAriza" autocomplete="off" required>
        <ul id="arizaResult" style="margin: 0;padding: 0"></ul>
      </div>
   </div>

    <div class="row">
      <div class="col-sm-12 gonderBtn">
        <input type="hidden" name="id" value="{{ $call_id->id }}">
        <input type="submit" class="btn btn-info btn-sm waves-effect waves-light" value="Kaydet">
      </div>
    </div>
  </form>
  
  <script>
    $(document).ready(function () {
      $('#editCall').submit(function (event) {
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
    $(document).ready(function(){
      $('#editCall').submit(function(e){
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
              alert("Çağrı başarıyla güncellendi");
              var rowToUpdate = $('#incomingCallsTable tbody tr[data-id="' + response.id + '"]');
              rowToUpdate.find('td:eq(1)').html(`<a class="t-link editIncomingCall" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editIncomingCallModal"><div class="mobileTitle">Tarih:</div>${response.formatted_created_at}</a>`);    
              rowToUpdate.find('td:eq(2)').html(`<a class="t-link editIncomingCall" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editIncomingCallModal"><div class="mobileTitle">Telefon:</div>${response.serviskaynak.kaynak}</a>`);
              rowToUpdate.find('td:eq(3)').html(`<a class="t-link editIncomingCall" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editIncomingCallModal"><div class="mobileTitle">Marka:</div>${response.brand.marka}</a>`);
              rowToUpdate.find('td:eq(4)').html(`<a class="t-link editIncomingCall" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editIncomingCallModal"><div class="mobileTitle">Açıklama:</div>${response.ariza}</a>`);
              rowToUpdate.find('td:eq(5)').html(`<a class="t-link editIncomingCall" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editIncomingCallModal"><div class="mobileTitle">Personel:</div>${response.kayit_alan.name}</a>`);
              $('#editIncomingCallModal').modal('hide');
            },
            error: function(xhr, status, error) {
              console.error(xhr.responseText);
            }
          });
        }
      });
    });
</script>