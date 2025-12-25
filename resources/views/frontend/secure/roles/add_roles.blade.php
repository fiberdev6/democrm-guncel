<form method="post" id="addRol" action="{{ route('store.roles', $firma->id)}}" enctype="multipart/form-data" class="needs-validation" novalidate>
  @csrf
  <div class="row">
    <label class="col-sm-3">Rol Adı:</label>
    <div class="col-sm-9">
      <input name="name" class="form-control" type="text" required>
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
    $('#addRol').submit(function (event) {
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
    $('#addRol').submit(function(e){
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
          alert("Rol başarıyla eklendi");
  
          var newRow = `<tr>
            <td class="gizli"><a class="t-link editRole idWrap" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editRoleModal">${response.id}</a></td>
            <td><a class="t-link editRole" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editRoleModal"><div class="mobileTitle">Rol Adı:</div>${response.name}</a></td>
            <td>
              <a href="javascript:void(0);" data-bs-id="${response.id}" class="btn btn-outline-primary btn-sm editPermission mobilBtn mbuton1" data-bs-toggle="modal" data-bs-target="#editRoleModal" title="Görüntüle"><i class="fas fa-eye"></i> <span> Düzenle</span></a>
              <a href="javascript:void(0);" data-bs-id="${response.id}" class="btn btn-outline-warning btn-sm editPermission mobilBtn mbuton1" data-bs-toggle="modal" data-bs-target="#editRoleModal" title="Düzenle"><i class="fas fa-edit"></i> <span> Düzenle</span></a>
              <a href="javascript:void(0);" data-bs-id="${response.id}" class="btn btn-outline-danger btn-sm mobilBtn deleteRole" title="Sil"><i class="fas fa-trash-alt"></i> <span> Sil</span></a>
            </td>
          </tr>`;
  
          $('#datatableRole tbody').prepend(newRow);
          //Modalı kapat
          $('#addRoleModal').modal('hide');
        },
        error: function(xhr, status, error) {
          console.error(xhr.responseText);
        }
      });
    }
    });
  });
</script>
