
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <form method="post" id="addRolePermission" action="{{ route('store.roles.permission', $firma->id)}}" enctype="multipart/form-data" class="needs-validation col-sm-8" novalidate>
          @csrf
          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Rol Adı: </label>
            <div class="col-sm-10">
              <select name="role_id" class="form-select" required>
                <option selected disabled value="">-Seçiniz-</option>
                @foreach($roles as $role)
                  <option value="{{$role->id}}">{{$role->name}}</option>
                @endforeach                
              </select>
            </div>
          </div> <!--end row-->

          <div class="row mb-3">             
            <div class="col-sm-12">
              <input class="form-check-input" type="checkbox" id="checkDefaultmain">
              <label class="form-check-label" for="checkDefaultmain">Tüm İzinler</label>
            </div>
          </div> <!--end row-->
          <hr>
            
          @foreach($permission_groups as $group)
            <div class="row">
              <div class="col-5">
                <div class="col-sm-10">
                  <input class="form-check-input" type="checkbox" id="checkDefault">
                  <label class="form-check-label" for="checkDefault">{{$group->group_name}}</label>
                </div>
              </div>
              <div class="col-7">
                @php 
                  $permissions = App\Models\User::getpermissionByGroupName($group->group_name);
                @endphp
                @foreach($permissions as $permission)
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="permission[]" value="{{$permission->id}}" id="checkDefault{{$permission->id}}">
                    <label class="form-check-label" for="checkDefault{{$permission->id}}">{{$permission->name}}</label>
                  </div>
                @endforeach
                <br>
              </div>
            </div>
          @endforeach

          <div class="row">
            <label class="col-sm-2 col-form-label"></label>
            <div class="col-sm-10">
              <input type="submit" class="btn btn-info waves-effect waves-light" value="Kaydet">
            </div>
          </div>
        </form>
      </div>
    </div>
  </div> <!-- end col -->
</div>

<script type="text/javascript">
  $('#checkDefaultmain').click(function(){
    if($(this).is(':checked')){
      $('input[type= checkbox]').prop('checked',true);
    }else{
      $('input[type= checkbox]').prop('checked',false);
    }
  });
</script>

<script>
  $(document).ready(function(){
    $('#addRolePermission').submit(function(e){
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
          alert("Role izin başarıyla eklendi");
          $('#addRolePermission')[0].reset();
          // Checkboxları temizle
          $('input[type=checkbox]').prop('checked', false);
        },
        error: function(xhr, status, error) {
          console.error(xhr.responseText);
        }
      });
    }
    });
  });
</script>