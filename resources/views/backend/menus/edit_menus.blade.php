@extends('backend.admin_master')
@section('admin')



<div class="page-content">
  <div class="container-fluid">
    <div class="row">
        <div class="col-12">
          <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Menü Düzenle</h4>
          </div>
        </div>
      </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">

            <form method="post" action="{{ route('update.menus', $menus_id->id)}}" class="needs-validation" novalidate>

              @csrf

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Menu Adı: </label>
                <div class="col-sm-10">
                  <input name="name" class="form-control" value="{{$menus_id->name}}" type="text" required>

                </div>
              </div> <!--end row-->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Menu Route: </label>
                <div class="col-sm-10">
                  <select name="link" class="form-select" required>
                    <option selected="">-Seçiniz-</option>
                    <option value="home" {{ $menus_id->link == "home" ? 'selected' : ''}}>Home</option>
                    <option value="about" {{ $menus_id->link == "about" ? 'selected' : ''}}>About</option>
                    <option value="products" {{ $menus_id->link == "products" ? 'selected' : ''}}>Usage Areas</option>
                    <option value="features" {{ $menus_id->link == "features" ? 'selected' : ''}}>Features</option>
                    <option value="contact" {{ $menus_id->link == "contact" ? 'selected' : ''}}>Contact</option>
                    <option value="pricing" {{ $menus_id->link == "pricing" ? 'selected' : ''}}>Pricing</option>
                  </select>
                </div>
              </div> <!--end row-->

              <div class="row mb-3 ">
                <label for="status" class="col-sm-2 col-form-label"> Menu Statü: </label>
                <div class="col-sm-10">
                  <select class="form-select" required name="status" required> 
                    <option value="enable" {{ $menus_id->status == "enable" ? 'selected' : ''}}>
                      Enabled
                    </option>
                    <option value="disable" {{ $menus_id->status == "disable" ? 'selected' : ''}}>
                      Disabled
                    </option>
                  </select>
                </div>
              </div> <!--end row-->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Menu Sıra: </label>
                <div class="col-sm-10">
                  <input name="sira" class="form-control" value="{{$menus_id->sira}}" type="number" required>
                </div>
              </div> <!--end row-->

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


@endsection
