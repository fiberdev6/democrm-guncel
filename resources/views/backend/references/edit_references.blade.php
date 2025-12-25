@extends('backend.admin_master')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>

<div class="page-content">
  <div class="container-fluid">
  
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">

            <h4 class="card-title">Referans Düzenleme</h4><br>
            <form method="post" action="{{ route('update.references') }}" enctype="multipart/form-data">   <!--buradaki enctype veritabanına resim yüklenmesine yarıyor -->
              
              @csrf

              <input type="hidden" name="id" value="{{ $reference_id->id }}" > 
              
              <div class="row mb-3">
                <label for="example-text-input" class="col-sm-2 col-form-label">Logo:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="logo" type="file"  id="image">
                </div>
              </div>
              <!-- end row -->

              <div class="row mb-3">
              <label for="example-text-input" class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                <img class="img-thumbnail" id="showImage" alt="200x200" width="200" src="{{ asset($reference_id->logo)}}" data-holder-rendered="true">
                </div>
              </div>
              <!-- end row -->

              <input type="submit" class="btn btn-info waves-effect waves-light" value="Gönder">
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