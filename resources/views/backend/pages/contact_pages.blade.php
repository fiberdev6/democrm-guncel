@extends('backend.admin_master')
@section('admin')


<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
          <h4 class="mb-sm-0">İletişim Sayfası</h4>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <form method="post" action="{{ route('update.pages.contact')}}" enctype="multipart/form-data" class="needs-validation" novalidate>

              @csrf

              <input type="hidden" name="id" value="{{ $pagescontact->id }}">

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"> Sayfa Başlık: </label>
                <div class="col-sm-10">
                  <input name="title" class="form-control" value="{{ $pagescontact->title}}" type="text" id="example-text-input" readonly required>

                </div>
              </div> <!--end row-->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"> Meta Tag Description: </label>
                <div class="col-sm-10">
                  <input name="description" class="form-control" value="{{ $pagescontact->description}}" type="text" id="example-text-input">

                </div>
              </div> <!--end row-->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label"> Meta Tag Keywords: </label>
                <div class="col-sm-10">
                  <input name="keywords" class="form-control" value="{{$pagescontact->keywords}}" type="text" id="example-text-input">

                </div>
              </div> <!--end row-->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Page Banner:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="page_banner" type="file"  id="image">
                  @if($errors->has('page_banner'))
                                            <div class="error">{{ $errors->first('page_banner') }}</div>
                                        @endif
                                        <label class=" col-form-label">Not: Maksimum resim boyutu 2MB'tan fazla olmamalıdır.</label>
                </div>
              </div>
              <!-- end row -->

              <div class="row mb-3">
              <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                <img class="img-thumbnail" id="showImage" alt="200x200" width="200" src="{{(!empty($pagescontact->page_banner))? url($pagescontact->page_banner):  url('upload/no_image.jpg') }}" data-holder-rendered="true">
                </div>
              </div>
              <!-- end row -->

              <!-- <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Slide:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="slide" type="file"  id="image1">
                </div>
              </div>


              <div class="row mb-3">
              <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                <img class="img-thumbnail" id="showImage1" alt="200x200" width="200" src="{{(!empty($pagescontact->slide))? url($pagescontact->slide):  url('upload/no_image.jpg') }}" data-holder-rendered="true">
                </div>
              </div> -->
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
<!-- burada javascript ile seçilen resmi görüntüledik -->
<script type="text/javascript">
  $(document).ready(function(){
    $('#image').change(function(e){
      var reader = new FileReader();
      reader.onload = function(e){
        $('#showImage').attr('src', e.target.result);
      }
      reader.readAsDataURL(e.target.files['0']);
    });
  });
</script>

<!-- <script type="text/javascript">
  $(document).ready(function(){
    $('#image1').change(function(e){
      var reader = new FileReader();
      reader.onload = function(e){
        $('#showImage1').attr('src', e.target.result);
      }
      reader.readAsDataURL(e.target.files['0']);
    });
  });
</script> -->

@endsection
