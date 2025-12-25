@extends('backend.admin_master')
@section('admin')


<div class="page-content">
  <div class="container-fluid">
    <div class="row">
        <div class="col-12">
          <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Firma Ayarları</h4>
          </div>
        </div>
      </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">

            <form method="post" action="{{ route('update.company.settings')}}" enctype="multipart/form-data" class="needs-validation" novalidate>

              @csrf

                <input type="hidden" name="id" value="{{ $company_settings->id }}">

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Firma Adı:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="company_name" type="text" value="{{ $company_settings->company_name}}" required>
                </div>
              </div>
              <!-- end row -->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Firma Adresi:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="company_address" type="text" value="{{ $company_settings->company_address}}">
                </div>
              </div>
              <!-- end row -->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">2. Firma Adresi:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="address_second" type="text" value="{{ $company_settings->address_second}}">
                </div>
              </div>
              <!-- end row -->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Firma Email:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="company_email" type="email" value="{{ $company_settings->company_email}}">
                </div>
              </div>
              <!-- end row -->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Firma Telefon:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="company_phone" type="text" value="{{ $company_settings->company_phone}}" required>
                </div>
              </div>
              <!-- end row -->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Whatsapp Numarası:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="company_number" type="text" value="{{ $company_settings->company_number}}">
                </div>
              </div>
              <!-- end row -->

              <div class="row">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                  <input type="submit" class="btn btn-info waves-effect waves-light" value="Gönder">
                </div>
              </div>
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
