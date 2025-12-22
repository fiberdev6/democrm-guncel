@extends('backend.admin_master')
@section('admin')
<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <h4 class="mb-sm-0">Rollerin İzinlerini Düzenle</h4>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <form method="post" action="{{ route('update.roles.permission',$role->id)}}" enctype="multipart/form-data" class="needs-validation col-sm-8" novalidate>

              @csrf

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Rol Adı: </label>
                <div class="col-sm-10">
                  <h5>{{$role->name}}</h5>

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

              @php 
                $permissions = App\Models\User::getpermissionByGroupName($group->group_name);
            @endphp
              <div class="row">
                <div class="col-3">
                    <div class="col-sm-10">
                        <input class="form-check-input" type="checkbox" id="checkDefault" {{ App\Models\User::roleHasPermissions($role,$permissions) ? 'checked' : ''}}>
                        <label class="form-check-label" for="checkDefault">{{$group->group_name}}</label>
                    </div>
                </div>
                <div class="col-9">

                

                @foreach($permissions as $permission)
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="permission[]" value="{{$permission->id}}" id="checkDefault{{$permission->id}}" {{$role->hasPermissionTo($permission->name) ? 'checked' : ''}}>
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



  </div>
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

@endsection
