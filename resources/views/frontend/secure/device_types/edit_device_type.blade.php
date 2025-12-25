<form method="post" id="editDev" action="{{ route('update.device.type', $firma->id) }}" enctype="multipart/form-data" class="needs-validation" novalidate>
    @csrf
    <div class="row mb-3">
      <label class="col-sm-4">Cihaz:</label>
      <div class="col-sm-8">
        <input name="cihaz" class="form-control" value="{{ $device_id->cihaz }}" type="text" required>
      </div>
    </div>

      <div class="row mb-3">
        <label class="col-sm-4">Operatör Prim:</label>
        <div class="col-sm-8">
          <input name="operatorPrim" class="form-control" value="{{ $device_id->operatorPrim }}" type="text" required>
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-4">Atolye Prim:</label>
        <div class="col-sm-8">
          <input name="atolyePrim" class="form-control" value="{{ $device_id->atolyePrim }}" type="text" required>
        </div>
      </div>
  
    <div class="row">
      <div class="col-sm-12 gonderBtn">
        <input type="hidden" name="id" value="{{ $device_id->id }}">
        <input type="submit" class="btn btn-info btn-sm waves-effect waves-light" value="Kaydet">
      </div>
    </div>
  </form>
  
  <script>
    $(document).ready(function () {
      $('#editDev').submit(function (event) {
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
      $('#editDev').submit(function(e){
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
              alert("Cihaz türü başarıyla güncellendi");
              var rowToUpdate = $('#datatableDeviceTypes tbody tr[data-id="' + response.id + '"]');
              rowToUpdate.find('td:eq(1)').html(`<a class="t-link editDeviceType" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editDeviceTypeModal"><div class="mobileTitle">Cihaz:</div>${response.cihaz}</a>`);    
              rowToUpdate.find('td:eq(2)').html(`<a class="t-link editDeviceType" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editDeviceTypeModal"><div class="mobileTitle">Opr Prim:</div>${response.operatorPrim}</a>`);
              rowToUpdate.find('td:eq(3)').html(`<a class="t-link editDeviceType" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editDeviceTypeModal"><div class="mobileTitle">Atolye Prim:</div>${response.atolyePrim}</a>`);
              $('#editDeviceTypeModal').modal('hide');
            },
            error: function(xhr, status, error) {
              console.error(xhr.responseText);
            }
          });
        }
      });
    });
</script>