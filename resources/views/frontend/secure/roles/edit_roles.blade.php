<form method="post" id="editRol" action="{{ route('update.roles', $firma->id) }}" enctype="multipart/form-data" class="needs-validation" novalidate>
  @csrf
  <div class="row mb-3">
    <label class="col-sm-4">Rol Adı:</label>
    <div class="col-sm-8">
      <input name="name" class="form-control" value="{{ $roles->name }}" type="text" required>
    </div>
  </div>
  <hr>

      @foreach($permissions->groupBy('group_name') as $groupName => $groupPermissions)
      <div class="row mb-3">
        <label class="col-sm-4 ">{{ $groupName }} Yetkisi:</label>
        <div class="col-sm-8">
      <div class="permission-group">
          @foreach($groupPermissions as $permission)
            <div class="form-check form-switch mb-3">
              <label class="form-check-label" for="customSwitch{{ $permission->id }}">
                {{ $permission->name }}
              </label>
              <input class="form-check-input" type="checkbox" name="permission[]" value="{{ $permission->id }}" id="customSwitch{{ $permission->id }}" {{ $roles->hasPermissionTo($permission->name) ? 'checked' : '' }}>
            </div>
          @endforeach
        </div>
      </div>
    </div>
      @endforeach
   


  <div class="row">
    <div class="col-sm-12 gonderBtn">
      <input type="hidden" name="id" value="{{ $roles->id }}">
      <input type="submit" class="btn btn-info btn-sm waves-effect waves-light" value="Kaydet">
    </div>
  </div>
</form>

<script>
  $(document).ready(function () {

  $('#editRol').submit(function (event) {
    event.preventDefault(); // Formun varsayılan gönderim işlemini durdur

    if (this.checkValidity() === false) {
      event.stopPropagation();
    } else {
      var formData = $(this).serialize();
      $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        success: function (response) {
          alert("Rol detayı güncellendi");
          var rowToUpdate = $('#datatableRole tbody tr[data-id="' + response.id + '"]');
          rowToUpdate.find('td:eq(1)').html(`
            <a class="t-link editRole" href="javascript:void(0);" data-bs-id="${response.id}" data-bs-toggle="modal" data-bs-target="#editRoleModal">
              <div class="mobileTitle">Rol Adı:</div>${response.name}
            </a>
          `);
          $('#editRoleModal').modal('hide');
        },
        error: function (xhr, status, error) {
          console.error(xhr.responseText);
        }
      });
    }
    this.classList.add('was-validated');
  });
});
</script>
