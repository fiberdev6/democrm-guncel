<form method="post" id="editPaymentType" action="{{ route('update.payment.type', $firma->id) }}" enctype="multipart/form-data" class="needs-validation" novalidate>
    @csrf
    <div class="row mb-3">
      <label class="col-sm-4">Ödeme Türü:</label>
      <div class="col-sm-8">
        <input name="odemeTuru" class="form-control" value="{{ $type_id->odemeTuru }}" type="text" required>
      </div>
    </div>
    @php
      $cevaplar = explode(',', $type_id->cevaplar); // örneğin: "1, 2, 4"
    @endphp
    <div class="row mb-3">
      <label class="col-sm-4">Sorulacak Sorular :<span style="font-weight: bold; color: red;">*</span></label>
      <div class="col-sm-8">
        <label style="display: block;width: 100%;"><input value="1" type="checkbox" name="cevaplar[]" {{ in_array('1', $cevaplar) ? 'checked' : '' }}> Açıklama Sor</label>
        <label style="display: block;width: 100%;"><input value="2" type="checkbox" name="cevaplar[]" {{ in_array('2', $cevaplar) ? 'checked' : '' }}> Personel Sor</label>
        <label style="display: block;width: 100%;"><input value="3" type="checkbox" name="cevaplar[]" {{ in_array('3', $cevaplar) ? 'checked' : '' }}> Servis No Sor</label>
        <label style="display: block;width: 100%;"><input value="4" type="checkbox" name="cevaplar[]" {{ in_array('4', $cevaplar) ? 'checked' : '' }}> Tedarikçi Sor</label>
        <label style="display: block;width: 100%;"><input value="5" type="checkbox" name="cevaplar[]" {{ in_array('5', $cevaplar) ? 'checked' : '' }}> Cihaz Marka Sor</label>
        <label style="display: block;width: 100%;"><input value="6" type="checkbox" name="cevaplar[]" {{ in_array('6', $cevaplar) ? 'checked' : '' }}> Cihaz Türü Sor</label>
      </div>
    </div>

    <div class="row mb-3">
      <label class="col-sm-4">Diğer İşlemler :<span style="font-weight: bold; color: red;">*</span></label>
      <div class="col-sm-8">
        <label><input value="1" type="checkbox" name="stokSor" {{ $type_id->stok == '1' ? 'checked' : '' }}> Stok işlemlerinde kullanılsın mı?</label>
        <label><input value="1" type="checkbox" name="servisSor" {{ $type_id->servis == '1' ? 'checked' : '' }}> Servis para işlemlerinde kullanılsın mı?</label>
        <label><input value="1" type="checkbox" name="parcaSor" {{ $type_id->parca == '1' ? 'checked' : '' }}> Servis parça işlemlerinde kullanılsın mı?</label>
        <label><input value="1" type="checkbox" name="personelSor" {{ $type_id->personel == '1' ? 'checked' : '' }}> Personel kasalarında kullanılsın mı?</label>
      </div>
    </div>
  
    <div class="row">
      <div class="col-sm-12 gonderBtn">
        <input type="hidden" name="id" value="{{ $type_id->id }}">
        <input type="submit" class="btn btn-info btn-sm waves-effect waves-light" value="Kaydet">
      </div>
    </div>
  </form>
  
  <script>
    $(document).ready(function () {
      $('#editPaymentType').submit(function (event) {
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
      $('#editPaymentType').submit(function(e){
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
              alert("Ödeme türü başarıyla güncellendi");
              var rowToUpdate = $('#datatablePaymentType tbody tr[data-id="' + response.id + '"]');
              rowToUpdate.find('td:eq(0)').html(`<a class="t-link editPaymentType" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editPaymentTypeModal"><div class="mobileTitle">Ö. Türü:</div>${response.odemeTuru}</a>`);    
              $('#editPaymentTypeModal').modal('hide');
            },
            error: function(xhr, status, error) {
              console.error(xhr.responseText);
            }
          });
        }
      });
    });
</script>