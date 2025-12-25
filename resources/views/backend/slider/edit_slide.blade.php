@extends('backend.admin_master')
@section('admin')
  <div class="page-content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Slide Düzenle</h4>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-body">
              <form method="post" action="{{ route('update.slide') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
                @csrf
								

                <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Başlık:</label>
                <div class="col-sm-10">
                  <input name="title" class="form-control" type="text" value="{{ $slide_id->title }}" required>
                </div>
              </div>

              <div class="row mb-3">
                  <label class="col-sm-2 col-form-label">Açıklama:</label>
                  <div class="col-sm-10">
                    <input name="sub_title" class="form-control" value="{{ $slide_id->sub_title }}" type="text" required>
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-2 col-form-label">Sıra:</label>
                  <div class="col-sm-10">
                    <input name="slide_no" class="form-control" value="{{ $slide_id->slide_no }}" type="number">
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-2 col-form-label">Slide Image:</label>
                  <div class="col-sm-10">
                    <input class="form-control" name="home_image" type="file" id="image">
                    @if($errors->has('home_image'))
                      <div class="error">{{ $errors->first('home_image') }}</div>
                    @endif
                    <label class=" col-form-label">Not: Maksimum resim boyutu 2MB'tan fazla olmamalıdır.</label>
                  </div>
                </div>

                <div class="row mb-3">
                  <label class="col-sm-2 col-form-label"></label>
                  <div class="col-sm-10">
                    <img class="img-thumbnail" id="showImage" width="200" src="{{ asset($slide_id->home_image) }}" data-holder-rendered="true">
                  </div>
                </div>

                <div class="row">
                  <label class="col-sm-2 col-form-label"></label>
                  <div class="col-sm-10">
                    <input type="hidden" name="id" value="{{ $slide_id->id }}">
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
    $(document).ready(function() {
      $('#image').change(function(e) {
        var reader = new FileReader();
        reader.onload = function(e) {
          $('#showImage').attr('src', e.target.result);
        }
        reader.readAsDataURL(e.target.files['0']);
      });
    });
  </script>
@endsection
