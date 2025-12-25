@extends('backend.admin_master')
@section('admin')

<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <h4 class="mb-sm-0">Anasayfa İletişim</h4>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <form method="post" action="{{ route('update.misyon') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
              @csrf
              <input type="hidden" name="id" value="{{$all_misyon->id }}">

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Başlık:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="title" type="text" value="{{ $all_misyon->title }}" required>
                </div>
              </div>
              
                <!-- end row -->
                <div class="row mb-3">
                <label for="example-text-input" class="col-sm-2 col-form-label">Description:</label>
                <div class="col-sm-10">
                <textarea id="elm1" name="description" type="text" aria-hidden="true" style="display:;">{{$all_misyon->description}}</textarea>
                </div>
              </div>
              <!-- end row -->

              <!-- <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Resim:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="image" type="file" id="image">
                  @if($errors->has('image'))
                    <div class="error">{{ $errors->first('image') }}</div>
                  @endif
                  <label class=" col-form-label">Not: Maksimum resim boyutu 2MB'tan fazla olmamalıdır.</label>
                </div>
              </div> -->
              <!-- end row -->
              <!-- <div class="row mb-3">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                  <img class="img-thumbnail" id="showImage" width="200" src="{{ !empty($all_misyon->image) ? url($all_misyon->image) : url('upload/no_image.jpg') }}" data-holder-rendered="true">
                </div>
              </div> -->
          
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
