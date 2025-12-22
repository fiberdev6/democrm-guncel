@extends('backend.admin_master')
@section('admin')



<div class="page-content">
  <div class="container-fluid">
    <div class="row">
        <div class="col-12">
          <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Menü Ekle</h4>
          </div>
        </div>
      </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">

            <form method="post" action="{{ route('store.menus')}}" class="needs-validation" novalidate>

              @csrf

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Menu Adı: </label>
                <div class="col-sm-10">
                  <input name="name" class="form-control" type="text" required>

                </div>
              </div> <!--end row-->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Menu Route: </label>
                <div class="col-sm-10">
                  <select name="link" class="form-select" required>
                    <option selected=""></option>
                    <option value="home">Home</option>
                    <option value="about">About</option>
                    <option value="products">Usage Areas</option>
                    <option value="features">Features</option>
                    <option value="contact">Contact</option>
                    <option value="pricing">Pricing</option>
                  </select>
                </div>
              </div> <!--end row-->

              <div class="row mb-3 ">
                <label for="status" class="col-sm-2 col-form-label"> Menu Statü: </label>
                <div class="col-sm-10">
                  <select class="form-select"  name="status" required>
                  <option selected=""></option>
                    <option value="enable">
                      Enabled
                    </option>
                    <option value="disable">
                      Disabled
                    </option>
                  </select>
                </div>
              </div> <!--end row-->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Menu Sıra: </label>
                <div class="col-sm-10">
                  <input name="sira" class="form-control" type="number" required>
                </div>
              </div>

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
