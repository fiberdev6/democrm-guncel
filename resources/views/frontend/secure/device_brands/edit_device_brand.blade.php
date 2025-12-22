<form method="post" id="editCat" action="{{ route('update.device', $firma->id) }}" enctype="multipart/form-data" class="needs-validation" novalidate>
    @csrf
    <div class="row mb-3">
      <label class="col-sm-4">Marka:</label>
      <div class="col-sm-8">
        <input name="marka" class="form-control" value="{{ $brand_id->marka }}" type="text" required>
      </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-4">Yetkili Servis Tel:</label>
        <div class="col-sm-8">
          <input name="aciklama" class="form-control" value="{{ $brand_id->aciklama }}" type="text" >
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-4">Servis Ücreti:</label>
        <div class="col-sm-8">
          <input name="servisUcreti" class="form-control" value="{{ $brand_id->servisUcreti }}" type="text">
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-4">Operatör Prim:</label>
        <div class="col-sm-8">
          <input name="operatorPrim" class="form-control" value="{{ $brand_id->operatorPrim }}" type="text" required>
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-4">Atolye Prim:</label>
        <div class="col-sm-8">
          <input name="atolyePrim" class="form-control" value="{{ $brand_id->atolyePrim }}" type="text" required>
        </div>
      </div>
  
    <div class="row">
      <div class="col-sm-12 gonderBtn">
        <input type="hidden" name="id" value="{{ $brand_id->id }}">
        <input type="submit" class="btn btn-info btn-sm waves-effect waves-light" value="Kaydet">
      </div>
    </div>
  </form>
  
  <script>
    $(document).ready(function () {
      $('#editCat').submit(function (event) {
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
      $('#editCat').submit(function(e){
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
              alert("Cihaz markası başarıyla güncellendi");
              var rowToUpdate = $('#datatableDeviceBrand tbody tr[data-id="' + response.id + '"]');
              rowToUpdate.find('td:eq(1)').html(`<a class="t-link editDevice" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editDeviceModal"><div class="mobileTitle">Marka:</div>${response.marka}</a>`);    
              rowToUpdate.find('td:eq(2)').html(`<a class="t-link editDevice" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editDeviceModal"><div class="mobileTitle">Telefon:</div>${response.aciklama}</a>`);
              rowToUpdate.find('td:eq(3)').html(`<a class="t-link editDevice" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editDeviceModal"><div class="mobileTitle">S. Ücreti:</div>${response.servisUcreti}</a>`);
              rowToUpdate.find('td:eq(4)').html(`<a class="t-link editDevice" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editDeviceModal"><div class="mobileTitle">Opr Prim:</div>${response.operatorPrim}</a>`);
              rowToUpdate.find('td:eq(5)').html(`<a class="t-link editDevice" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editDeviceModal"><div class="mobileTitle">Atolye Prim:</div>${response.atolyePrim}</a>`);
              $('#editDeviceModal').modal('hide');
            },
            error: function(xhr, status, error) {
              console.error(xhr.responseText);
            }
          });
        }
      });
    });
</script>