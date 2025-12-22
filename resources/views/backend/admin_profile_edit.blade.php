@extends('backend.admin_master')
@section('admin')


<div class="page-content">
  <div class="container-fluid">

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">

            <h4 class="card-title">Profil Güncelle</h4>
             <form method="post" action="{{ route('store.profile')}}" enctype="multipart/form-data">
              @csrf
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Ad:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="name" type="text" value="{{ $editData->name}}">
                </div>
              </div>
              <!-- end row -->



              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Kullanıcı Adı:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="username" type="text" value="{{ $editData->username}}">
                </div>
              </div>
              <!-- end row -->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Yeni Şifre:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="newpassword" type="password" id="newpassword">
                </div>
              </div>
              <!-- end row -->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Şifre Yeniden:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="confirm_password" type="password" id="confirm_password">
                </div>
              </div>
              <!-- end row -->

              <input type="submit" class="btn btn-info waves-effect waves-light" value="Gönder">
            </form>

          </div>
        </div>
      </div> <!-- end col -->
    </div>



  </div>
</div>

<!-- burada javascript ile seçilen resmi görüntüledik -->
<script type="text/javascript">
  $(document).ready(function(){
    $('#image').change(function(e){
      var reader = new FileReader();
      reader.onload = function(e){
        $('#showImage').attr('src', e.target.result);
      }
      reader.readAsDataURL(e.target.files['0']);
    });
  });
</script>

@endsection
