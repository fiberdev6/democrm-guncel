<form method="post" id="editPerm" action="{{ route('update.permission', $firma->id) }}" enctype="multipart/form-data" class="needs-validation" novalidate>
  @csrf
  <div class="row">
    <label class="col-sm-3">İzin Adı:</label>
    <div class="col-sm-9">
      <input name="name" class="form-control" value="{{ $permissions->name}}" type="text" required>
    </div>
  </div>

  <div class="row mb-3">
    <label class="col-sm-3">Grup Adı: </label>
    <div class="col-sm-9">
      <select name="group_name" class="form-select" required>
        <option selected disabled value="">-Seçiniz-</option>
        <option value="Ayarlar" {{ $permissions->group_name == "Ayarlar" ? 'selected' : ''}}>Genel Ayarlar</option>
        <option value="Müşteriler" {{ $permissions->group_name == "Müşteriler" ? 'selected' : ''}}>Müşteriler</option>
        <option value="Roller ve İzinler" {{ $permissions->group_name == "Roller ve İzinler" ? 'selected' : ''}}>Roller ve İzinler</option>
        <option value="Personeller" {{ $permissions->group_name == "Personeller" ? 'selected' : ''}}>Personeller</option>
        <option value="Teklifler" {{$permissions->group_name == "Teklifler" ? 'selected' : ''}}>Teklifler</option>
        <option value="Kasa" {{$permissions->group_name == "Kasa" ? 'selected' : ''}}>Kasa</option>
        <option value="Faturalar" {{$permissions->group_name == "Faturalar" ? 'selected' : ''}}>Faturalar</option>
        <option value="Servisler" {{$permissions->group_name == "Servisler" ? 'selected' : ''}}>Servisler</option>
        <option value="Bayiler" {{$permissions->group_name == "Bayiler" ? 'selected' : ''}}>Bayiler</option>
        <option value="Depo" {{$permissions->group_name == "Depo" ? 'selected' : ''}}>Depo</option>
      </select>
    </div>
  </div> <!--end row-->

  <div class="row">
    <div class="col-sm-12 gonderBtn">
      <input type="hidden" name="id" value="{{ $permissions->id }}">
      <input type="submit" class="btn btn-info btn-sm waves-effect waves-light" value="Kaydet">
    </div>
  </div>
</form>

<script>
  $(document).ready(function () {
    $('#editPerm').submit(function (event) {
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
    $('#editPerm').submit(function(e){
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
          alert("İzin detayı güncellendi");
          var rowToUpdate = $('#datatablePermission tbody tr[data-id="' + response.id + '"]');
  
          rowToUpdate.find('td:eq(1)').html(`<a class="t-link editPermission" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editPermissionModal"><div class="mobileTitle">İzin Adı:</div>${response.name}</a>`);
          rowToUpdate.find('td:eq(2)').html(`<a class="t-link editPermission" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editPermissionModal"><div class="mobileTitle">Grup:</div>${response.group_name}</a>`);
          //Modalı kapat
          $('#editPermissionModal').modal('hide');
        },
        error: function(xhr, status, error) {
          console.error(xhr.responseText);
        }
      });
    }
    });
  });
</script>