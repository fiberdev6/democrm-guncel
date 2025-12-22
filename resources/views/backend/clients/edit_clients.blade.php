@extends('backend.admin_master')
@section('admin')

<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <h4 class="mb-sm-0">Yorum Düzenle</h4>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <form method="post" action="{{ route('update.client')}}" enctype="multipart/form-data" class="needs-validation" novalidate>

              @csrf

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Soru:</label>
                <div class="col-sm-10">
                  <input name="name" class="form-control" value="{{ $client_id->name }}" type="text" required>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Sıra:</label>
                <div class="col-sm-10">
                  <input name="job" class="form-control" value="{{ $client_id->job }}" type="text" required>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Müşteri Mesajı:</label>
                <div class="col-sm-10">
                  <textarea id="elm1" name="message" type="text" aria-hidden="true">{{$client_id->message}}</textarea>
                </div>
              </div>
              <!-- end row -->

              <div class="row">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                  <input type="hidden" name="id" value="{{ $client_id->id }}">
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