@extends('backend.admin_master')
@section('admin')

<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <h4 class="mb-sm-0">Anasayfa</h4>
        </div>
      </div>
    </div>


    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">

            <form method="post" action="{{ route('update.pages.home')}}" class="needs-validation" novalidate>

              @csrf

              <input type="hidden" name="id" value="{{ $pageshome->id }}">

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"> Sayfa Başlık: </label>
                <div class="col-sm-10">
                  <input name="title" class="form-control" value="{{ $pageshome->title}}" type="text" id="example-text-input" readonly required>

                </div>
              </div> <!--end row-->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"> Meta Tag Description: </label>
                <div class="col-sm-10">
                  <input name="description" class="form-control" value="{{ $pageshome->description}}" type="text" id="example-text-input">

                </div>
              </div> <!--end row-->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"> Meta Tag Keywords: </label>
                <div class="col-sm-10">
                  <input name="keywords" class="form-control" value="{{ $pageshome->keywords}}" type="text" id="example-text-input">

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