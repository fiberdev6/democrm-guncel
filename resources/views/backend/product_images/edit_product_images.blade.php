@extends('backend.admin_master')
@section('admin')

<div class="page-content">
  <div class="container-fluid">
  
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">

            <h4 class="card-title">Ürün Resmini Düzenleme</h4><br>
            <form method="post" action="{{ route('update.product.image') }}" enctype="multipart/form-data">   <!--buradaki enctype veritabanına resim yüklenmesine yarıyor -->
              
              @csrf

              <input type="hidden" name="id" value="{{ $product_image_id->id }}" > 

              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Ürün: </label>
                <div class="col-sm-10">
                  <select name="product" class="form-select" required>
                    <option selected="">-Seçiniz-</option>
                    @foreach($products as $product)
                    <option value="{{ $product->id }}" {{ $product->id == $product_image_id->product_id ? 'selected' : ''}}>{{ $product->title }}</option>
                    @endforeach
                  </select>
                </div>
              </div> <!--end row-->
              
              <div class="row mb-3">
                <label for="example-text-input" class="col-sm-2 col-form-label">Resimler:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="product_images" type="file"  id="image">
                  @if($errors->has('product_images'))
                    <div class="error">{{ $errors->first('product_images') }}</div>
                  @endif
                  <label class=" col-form-label">Not: Maksimum resim boyutu 2MB'tan fazla olmamalıdır.</label>
                </div>
              </div>
              <!-- end row -->

              <div class="row mb-3">
              <label for="example-text-input" class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                <img class="img-thumbnail" id="showImage" alt="200x200" width="200" src="{{ asset($product_image_id->product_images)}}" data-holder-rendered="true">
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