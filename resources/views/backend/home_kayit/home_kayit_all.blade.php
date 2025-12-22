@extends('backend.admin_master')
@section('admin')


<div class="page-content">
  <div class="container-fluid">

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">

            <h4 class="card-title mb-4">Home Kayit Page</h4>
            <form method="post" action="{{ route('update.home.kayit')}}" enctype="multipart/form-data">

              @csrf

                <input type="hidden" name="id" value="{{ $home_kayit->id }}">

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Title:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="title" type="text" value="{{$home_kayit->title}}">
                </div>
              </div>
              <!-- end row -->

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Subitle:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="subtitle" type="text" value="{{ $home_kayit->subtitle}}">
                </div>
              </div>
              <!-- end row -->



              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Image:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="image" type="file"  id="image">
                </div>
              </div>
              <!-- end row -->

              <div class="row mb-3">
              <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                <img class="img-thumbnail" id="showImage" width="200" src="{{(!empty($home_kayit->image))? url($home_kayit->image):  url('upload/no_image.jpg') }}" data-holder-rendered="true">
                </div>
              </div>
              <!-- end row -->

              <input type="submit" class="btn btn-info waves-effect waves-light" value="Send">
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

@endsection
