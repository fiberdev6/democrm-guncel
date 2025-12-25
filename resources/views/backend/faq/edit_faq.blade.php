@extends('backend.admin_master')
@section('admin')

<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <h4 class="mb-sm-0">Sık Sorulan Soruları Düzenle</h4>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <form method="post" action="{{ route('update.faq')}}" class="needs-validation" novalidate>

              @csrf

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Soru:</label>
                <div class="col-sm-10">
                  <input name="question" class="form-control" value="{{ $faq_id->question }}" type="text" required>
                </div>
              </div>

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Açıklama:</label>
                <div class="col-sm-10">
                  <textarea name="description" class="form-control" rows="5" required>{{ $faq_id->description }}</textarea>
                </div>
              </div>
              <!-- end row -->

              <div class="row">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                  <input type="hidden" name="id" value="{{ $faq_id->id }}">
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