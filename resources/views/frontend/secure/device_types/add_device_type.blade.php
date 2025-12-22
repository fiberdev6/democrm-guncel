<form method="post" id="addDev" action="{{ route('store.device.type', $firma->id)}}" enctype="multipart/form-data" class="needs-validation" novalidate>
    @csrf
    <input type="hidden" name="form_token" id="formTokenDev" value="">
    <div class="row mb-3">
      <label class="col-sm-4">Cihaz:<span style="font-weight: bold; color: red;">*</span></label>
      <div class="col-sm-8">
        <input name="cihaz" class="form-control" type="text" required>
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
      $('#addDev').submit(function (event) {
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
      $('#addDev').submit(function(e){
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
              alert("Cihaz türü başarıyla eklendi");
              var newRow = `<tr>
                <td class="gizli"><a class="t-link editDeviceType idWrap" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editDeviceTypeModal">${response.id}</a></td>
                <td><a class="t-link editDeviceType" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editDeviceTypeModal"><div class="mobileTitle">Cihaz:</div>${response.cihaz}</a></td>
                <td><a class="t-link editDeviceType" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editDeviceTypeModal"><div class="mobileTitle">Opt Prim:</div>${response.operatorPrim}</a></td>
                <td><a class="t-link editDeviceType" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editDeviceTypeModal"><div class="mobileTitle">Atolye Prim:</div>${response.atolyePrim}</a></td>
                <td> 
                  <a href="javascript:void(0);" data-bs-id="${response.id}" class="btn btn-outline-primary btn-sm editDeviceType mobilBtn mbuton1" data-bs-toggle="modal" data-bs-target="#editDeviceTypeModal" title="Göster"><i class="fas fa-eye"></i> <span> Düzenle</span></a>
                  <a href="javascript:void(0);" data-bs-id="${response.id}" class="btn btn-outline-warning btn-sm editDeviceType mobilBtn mbuton1" data-bs-toggle="modal" data-bs-target="#editDeviceTypeModal" title="Düzenle"><i class="fas fa-edit"></i> <span> Düzenle</span></a>
                  <a href="javascript:void(0);"  class="btn btn-outline-danger btn-sm mobilBtn deleteDeviceType" data-bs-id="${response.id}" title="Sil"><i class="fas fa-trash-alt"></i> <span> Sil</span></a>
                </td>
              </tr>`;
              $('#datatableDeviceTypes tbody').prepend(newRow);
              $('#addDeviceTypeModal').modal('hide');
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
      $('#formTokenDev').val(generateToken());
      
      // Form submit sonrası token yenileme
      $('#addDev').on('submit', function() {
          // Token kontrolü
          if (formSubmitting) {
              return false;
          }
          
          // Butonu disable et
          formSubmitting = true;
          $(this).find('input[type="submit"]').prop('disabled', true);
          
          // 3 saniye sonra yeniden aktif et
          setTimeout(function() {
              $('#formTokenDev').val(generateToken());
              formSubmitting = false;
              $('#addDev input[type="submit"]').prop('disabled', false);
          }, 3000);
      });
  });
  </script>