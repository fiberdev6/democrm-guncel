@extends('backend.admin_master')
@section('admin')

<div class="page-content">
  <div class="container-fluid">
    <div class="row">
        <div class="col-12">
          <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Paket Ekle</h4>
          </div>
        </div>
      </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">

            <form method="post" action="{{ route('store.pricing')}}" enctype="multipart/form-data" class="needs-validation" novalidate>

              @csrf

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"> Başlık: </label>
                <div class="col-sm-10">
                  <input name="name" class="form-control" type="text" required>

                </div>
              </div> <!--end row-->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"> Fiyat: </label>
                <div class="col-sm-10">
                  <input name="price" class="form-control" type="text" required>

                </div>
              </div> <!--end row-->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"> Renk: </label>
                <div class="col-sm-10">
                  <input name="color" class="form-control" type="text" required>

                </div>
              </div> <!--end row-->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"> İcon: </label>
                <div class="col-sm-10">
                  <input name="icon" class="form-control" type="text" required>

                </div>
              </div> <!--end row-->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"> Açıklama: </label>
                <div class="col-sm-10">
                <textarea id="elm1" name="description" type="text" aria-hidden="true"></textarea>


                </div>
              </div> <!--end row-->

              

              <input type="submit" class="btn btn-info waves-effect waves-light" value="Gönder">
            </form>

          </div>
        </div>
      </div> <!-- end col -->
    </div>



  </div>
</div>

@endsection
