<form method="post" id="editWarranty" action="{{ route('update.warranty', $firma->id) }}" enctype="multipart/form-data" class="needs-validation" novalidate>
    @csrf
    <div class="row mb-3">
      <label class="col-sm-3">Garanti Süresi:</label>
      <div class="col-sm-9">
        <input name="garanti" class="form-control" value="{{ $warranty_id->garanti }}" type="text" required>
      </div>
    </div>
    <span style="font-size: 13px;color: red;">Ay hesabına göre ekleyin. Örneğin: 6 Ay için sadece 6 yazın.</span>
  
    <div class="row">
      <div class="col-sm-12 gonderBtn">
        <input type="hidden" name="id" value="{{ $warranty_id->id }}">
        <input type="submit" class="btn btn-info btn-sm waves-effect waves-light" value="Kaydet">
      </div>
    </div>
  </form>
  
  <script>
    $(document).ready(function () {
      $('#editWarranty').submit(function (event) {
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
      $('#editWarranty').submit(function(e){
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
              alert("Garanti süresi başarıyla güncellendi");
              var rowToUpdate = $('#datatableWarranty tbody tr[data-id="' + response.id + '"]');
              rowToUpdate.find('td:eq(0)').html(`<a class="t-link editWarranty" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editWarrantyModal"><div class="mobileTitle">Garanti Süresi:</div>${response.garanti} Ay</a>`);    
              $('#editWarrantyModal').modal('hide');
            },
            error: function(xhr, status, error) {
              console.error(xhr.responseText);
            }
          });
        }
      });
    });
</script>