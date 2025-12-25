@extends('backend.admin_master')
@section('admin')

<div class="page-content">
  <div class="container-fluid">
    <div class="row">
        <div class="col-12">
          <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Google Ayarları</h4>
          </div>
        </div>
      </div>


    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">

            <form method="post" action="{{ route('update.google.settings')}}">

              @csrf

                <input type="hidden" name="id" value="{{ $google_settings_all->id }}">

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Google Maps Kodu:</label>
                <div class="col-sm-10">
                  <textarea name="maps_kod" id="basicpill-address-input" class="form-control" rows="6" style="resize: none" >{{ $google_settings_all->maps_kod }}</textarea>
                </div>
              </div>
              <!-- end row -->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Google Tag Manager Kodu (Head):</label>
                <div class="col-sm-10">
                <textarea name="taghead_kod" id="basicpill-address-input" class="form-control" rows="6" style="resize: none">{{ $google_settings_all->taghead_kod }}</textarea>
                </div>
              </div>
              <!-- end row -->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Google Tag Manager Kodu (Body):</label>
                <div class="col-sm-10">
                  <textarea name="tagbody_kod" id="basicpill-address-input" class="form-control" rows="6" style="resize: none">{{ $google_settings_all->tagbody_kod }}</textarea>
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
