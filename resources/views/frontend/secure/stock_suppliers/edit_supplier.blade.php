<form method="post" id="editStokTedarik" action="{{ route('update.stock.supplier', $firma->id) }}" enctype="multipart/form-data" class="needs-validation" novalidate>
    @csrf
    <div class="row mb-3">
      <label class="col-sm-3">Tedarikçi:</label>
      <div class="col-sm-9">
        <input name="tedarikci" class="form-control" value="{{ $supplier_id->tedarikci }}" type="text" required>
      </div>
    </div>
  
    <div class="row">
      <div class="col-sm-12 gonderBtn">
        <input type="hidden" name="id" value="{{ $supplier_id->id }}">
        <input type="submit" class="btn btn-info btn-sm waves-effect waves-light" value="Kaydet">
      </div>
    </div>
  </form>
  
  <script>
    $(document).ready(function () {
      $('#editStokTedarik').submit(function (event) {
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
      $('#editStokTedarik').submit(function(e){
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
              alert("Stok tedarikçisi başarıyla güncellendi");
              var rowToUpdate = $('#datatableStockSupplier tbody tr[data-id="' + response.id + '"]');
              rowToUpdate.find('td:eq(0)').html(`<a class="t-link editStockSupplier" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editStockSupplierModal"><div class="mobileTitle">Tedarikçi:</div>${response.tedarikci}</a>`);    
              $('#editStockSupplierModal').modal('hide');
            },
            error: function(xhr, status, error) {
              console.error(xhr.responseText);
            }
          });
        }
      });
    });
</script>