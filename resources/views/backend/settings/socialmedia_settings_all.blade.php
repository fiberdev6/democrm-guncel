@extends('backend.admin_master')
@section('admin')


<div class="page-content">
  <div class="container-fluid">
    <div class="row">
        <div class="col-12">
          <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Sosyal Medya Ayarları</h4>
          </div>
        </div>
      </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">

            <form method="post" action="{{ route('update.socialmedia.settings')}}" class="needs-validation" novalidate>

              @csrf

                <input type="hidden" name="id" value="{{ $socialmedia_settings_id->id }}">

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Facebook:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="facebook" type="text" value="{{ $socialmedia_settings_id->facebook}}">
                </div>
              </div>
              <!-- end row -->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Twitter:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="twitter" type="text" value="{{ $socialmedia_settings_id->twitter }}">
                </div>
              </div>
              <!-- end row -->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Instagram:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="instagram" type="text" value="{{ $socialmedia_settings_id->instagram }}">
                </div>
              </div>
              <!-- end row -->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">LinkedIn:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="linkedin" type="text" value="{{ $socialmedia_settings_id->linkedin }}">
                </div>
              </div>
              <!-- end row -->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Youtube:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="youtube" type="text" value="{{ $socialmedia_settings_id->youtube }}">
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


@endsection
