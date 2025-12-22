<form method="post" id="addStokShelf" action="{{ route('store.stock.shelf', $firma->id)}}" enctype="multipart/form-data" class="needs-validation" novalidate>
    @csrf
    <input type="hidden" name="form_token" id="formTokenStockShelf" value="">
    <div class="row mb-3">
      <label class="col-sm-3">Raf Adı:<span style="font-weight: bold; color: red;">*</span></label>
      <div class="col-sm-9">
        <input name="raf_adi" class="form-control" type="text" required>
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
      $('#addStokShelf').submit(function (event) {
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
      $('#addStokShelf').submit(function(e){
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
              alert("Stok rafları başarıyla eklendi");
              var newRow = `<tr>
                <td><a class="t-link editStockShelf" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editStockShelfModal"><div class="mobileTitle">Raf Adı:</div>${response.raf_adi} </a></td>
                <td>
                  <a href="javascript:void(0);" data-bs-id="${response.id}" class="btn btn-outline-primary btn-sm editStockShelf mobilBtn mbuton1" data-bs-toggle="modal" data-bs-target="#editStockShelfModal" title="Görüntüle"><i class="fas fa-eye"></i> <span> Düzenle</span></a>
                  <a href="javascript:void(0);" data-bs-id="${response.id}" class="btn btn-outline-warning btn-sm editStockShelf mobilBtn mbuton1" data-bs-toggle="modal" data-bs-target="#editStockShelfModal" title="Düzenle"><i class="fas fa-edit"></i> <span> Düzenle</span></a>
                  <a href="javascript:void(0);"  class="btn btn-outline-danger btn-sm mobilBtn deleteStockShelf" data-bs-id="${response.id}" title="Sil"><i class="fas fa-trash-alt"></i> <span> Sil</span></a>
                </td>
              </tr>`;
              $('#datatableStockShelf tbody').prepend(newRow);
              $('#addStockShelfModal').modal('hide');
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
    $('#formTokenStockShelf').val(generateToken());
    
    // Form submit sonrası token yenileme
    $('#addStokShelf').on('submit', function() {
        // Token kontrolü
        if (formSubmitting) {
            return false;
        }
        
        // Butonu disable et
        formSubmitting = true;
        $(this).find('input[type="submit"]').prop('disabled', true);
        
        // 3 saniye sonra yeniden aktif et
        setTimeout(function() {
            $('#formTokenStockShelf').val(generateToken());
            formSubmitting = false;
            $('#addStokShelf input[type="submit"]').prop('disabled', false);
        }, 3000);
    });
});
</script>