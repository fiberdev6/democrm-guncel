<form method="post" id="addPerm" action="{{ route('store.permission', $firma->id)}}" enctype="multipart/form-data" class="needs-validation" novalidate>
  @csrf
  <div class="row">
    <label class="col-sm-3">İzin Adı:</label>
    <div class="col-sm-9">
      <input name="name" class="form-control" type="text" required>
    </div>
  </div>

  <div class="row mb-3">
    <label class="col-sm-3">Grup Adı: </label>
    <div class="col-sm-9">
      <select name="group_name" class="form-select" required>
        <option selected disabled value="">-Seçiniz-</option>
        <option value="Ayarlar">Genel Ayarlar</option>
        <option value="Müşteriler">Müşteriler</option>
        <option value="Roller ve İzinler">Roller ve İzinler</option>
        <option value="Personeller">Personeller</option>
        <option value="Teklifler">Teklifler</option>
        <option value="Kasa">Kasa</option>
        <option value="Faturalar">Faturalar</option>
        <option value="Servisler">Servisler</option>
        <option value="Bayiler">Bayiler</option>
        <option value="Depo">Depo</option>
        
      </select>
    </div>
  </div> <!--end row-->

  <div class="row">
    <div class="col-sm-12 gonderBtn">
      <input type="submit" class="btn btn-info btn-sm waves-effect waves-light" value="Kaydet">
    </div>
  </div>
</form>

<script>
  $(document).ready(function () {
    $('#addPerm').submit(function (event) {
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
    $('#addPerm').submit(function(e){
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
          alert("İzin başarıyla eklendi");
  
          var newRow = `<tr>
            <td class="gizli"><a class="t-link editPermission idWrap" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editPermissionModal">${response.id}</a></td>
            <td><a class="t-link editPermission" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editPermissionModal"><div class="mobileTitle">İzin Adı:</div>${response.name}</a></td>
            <td><a class="t-link editPermission" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editPermissionModal"><div class="mobileTitle">Grup:</div>${response.group_name}</a></td>
            <td>
              <a href="javascript:void(0);" data-bs-id="${response.id}" class="btn btn-outline-primary btn-sm editPermission mobilBtn mbuton1" data-bs-toggle="modal" data-bs-target="#editPermissionModal" title="Görüntüle"><i class="fas fa-eye"></i> <span> Düzenle</span></a>
              <a href="javascript:void(0);" data-bs-id="${response.id}" class="btn btn-outline-warning btn-sm editPermission mobilBtn mbuton1" data-bs-toggle="modal" data-bs-target="#editPermissionModal" title="Düzenle"><i class="fas fa-edit"></i> <span> Düzenle</span></a>
              <a href="javascript:void(0);" data-bs-id="${response.id}" class="btn btn-outline-danger  btn-sm mobilBtn deletePermission" title="Sil"><i class="fas fa-trash-alt"></i> <span> Sil</span></a>
            </td>
          </tr>`;
  
          $('#datatablePermission tbody').prepend(newRow);
          //Modalı kapat
          $('#addPermissionModal').modal('hide');
        },
        error: function(xhr, status, error) {
          console.error(xhr.responseText);
        }
      });
    }
    });
  });
</script>