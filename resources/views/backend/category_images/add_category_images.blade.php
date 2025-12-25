@extends('backend.admin_master')
@section('admin')


<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">

            <h4 class="card-title">Kategori Resimleri Ekleme</h4><br>
            <form method="post" action="{{ route('store.category.image')}}" enctype="multipart/form-data" class="needs-validation" novalidate>   <!--buradaki enctype veritabanına resim yüklenmesine yarıyor -->
              
              @csrf

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Kategori: </label>
                <div class="col-sm-10">
                  <select name="category" class="form-select" required>
                    <option selected disabled value="">-Seçiniz-</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ $category->id == $kategoriler->id ? 'selected' : ''}}>{{ $category->title }}</option>
                    @endforeach
                  </select>

                </div>
              </div> <!--end row-->
              
              <div class="row mb-3">
                <label for="example-text-input" class="col-sm-2 col-form-label">Resimler:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="image[]" type="file"  id="image" multiple="" required>
                  @if($errors->has('image.*'))
                    <div class="error">{{ $errors->first('image.*') }}</div>
                  @endif
                  <label class=" col-form-label">Not: Maksimum resim boyutu 2MB'tan fazla olmamalıdır.</label>
                </div>
              </div>
              <!-- end row -->

              <div class="row mb-3">
              <label for="example-text-input" class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                <img class="img-thumbnail" id="showImage" alt="200x200" width="200" src="{{ url('upload/no_image.jpg') }}" data-holder-rendered="true">
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