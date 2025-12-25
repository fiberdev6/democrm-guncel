<form method="post" id="addCat" action="{{ route('store.device', $firma->id)}}" enctype="multipart/form-data" class="needs-validation" novalidate>
    @csrf
    <input type="hidden" name="form_token" id="formToken" value="">
    <div class="row mb-3">
      <label class="col-sm-4">Marka:<span style="font-weight: bold; color: red;">*</span></label>
      <div class="col-sm-8">
        <input name="marka" class="form-control" type="text" required>
      </div>
    </div>
    <div class="row mb-3">
        <label class="col-sm-4">Yetkili Servis Tel:</label>
        <div class="col-sm-8">
          <input name="aciklama" class="form-control" type="text" >
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-sm-4">Servis Ücreti:</label>
        <div class="col-sm-8">
          <input name="servisUcreti" class="form-control" type="text" value="S.Ü.: 500 TL">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-sm-4">Operatör Prim:</label>
        <div class="col-sm-8">
          <input name="operatorPrim" class="form-control" type="text" placeholder="0.00" value="0.00">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-sm-4">Atolye Prim:</label>
        <div class="col-sm-8">
          <input name="atolyePrim" class="form-control" type="text" placeholder="0.00" value="0.00">
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
      $('#addCat').submit(function (event) {
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
      $('#addCat').submit(function(e){
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
              alert("Cihaz markası başarıyla eklendi");
              var newRow = `<tr>
                <td class="gizli"><a class="t-link editDevice idWrap" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editDeviceModal">${response.id}</a></td>
                <td><a class="t-link editDevice" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editDeviceModal"><div class="mobileTitle">Marka:</div>${response.marka}</a></td>
                <td><a class="t-link editDevice" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editDeviceModal"><div class="mobileTitle">Telefon:</div>${response.aciklama}</a></td>
                <td><a class="t-link editDevice" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editDeviceModal"><div class="mobileTitle">S.Ü.:</div>${response.servisUcreti}</a></td>
                <td><a class="t-link editDevice" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editDeviceModal"><div class="mobileTitle">Opt Prim:</div>${response.operatorPrim}</a></td>
                <td><a class="t-link editDevice" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editDeviceModal"><div class="mobileTitle">Atolye Prim:</div>${response.atolyePrim}</a></td>
                <td>
                  <a href="javascript:void(0);" data-bs-id="${response.id}" class="btn btn-outline-primary btn-sm editDevice mobilBtn mbuton1" data-bs-toggle="modal" data-bs-target="#editDeviceModal" title="Göster"><i class="fas fa-eye"></i> <span> Düzenle</span></a>
                  <a href="javascript:void(0);" data-bs-id="${response.id}" class="btn btn-outline-warning btn-sm editDevice mobilBtn mbuton1" data-bs-toggle="modal" data-bs-target="#editDeviceModal" title="Düzenle"><i class="fas fa-edit"></i> <span> Düzenle</span></a>
                  <a href="javascript:void(0);"  class="btn btn-outline-danger btn-sm mobilBtn deleteDevice" data-bs-id="${response.id}" title="Sil"><i class="fas fa-trash-alt"></i> <span> Sil</span></a>
                </td>
              </tr>`;
              $('#datatableDeviceBrand tbody').prepend(newRow);
              $('#addDeviceModal').modal('hide');
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
    $('#formToken').val(generateToken());
    
    // Form submit sonrası token yenileme
    $('#addCat').on('submit', function() {
        // Token kontrolü
        if (formSubmitting) {
            return false;
        }
        
        // Butonu disable et
        formSubmitting = true;
        $(this).find('input[type="submit"]').prop('disabled', true);
        
        // 3 saniye sonra yeniden aktif et
        setTimeout(function() {
            $('#formToken').val(generateToken());
            formSubmitting = false;
            $('#addCat input[type="submit"]').prop('disabled', false);
        }, 3000);
    });
});
</script>