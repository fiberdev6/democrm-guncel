@extends('backend.admin_master')
@section('admin')

<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Çözüm Ortağı Logo Ekleme</h4><br>
            <form method="post" action="{{ route('store.references')}}" enctype="multipart/form-data" class="needs-validation dropzone" id="dropzone" >   <!--buradaki enctype veritabanına resim yüklenmesine yarıyor -->
              @csrf
              <div class="fallback">
                <input name="file" type="file" multiple="multiple" id="image-upload">
              </div>
              <div class="dz-message needsclick">
                <div class="mb-3">
                  <i class="display-4 text-muted ri-upload-cloud-2-line"></i>
                </div>                                  
                <h4>Drop files here or click to upload.</h4>
              </div>
              <!-- <input type="submit" class="btn btn-info waves-effect waves-light" value="Gönder"> -->
            </form>
          </div>
        </div>
      </div> <!-- end col -->

      <div class="foto">
        <div class="row">
          @foreach($references as $item)
            <div class="col-xl-2 col-sm-4 mb-3 imgWrap1">
              <div class="card text-white  o-hidden h-100">
                <img src="{{asset($item->logo) }}" >
                <div class="row1 mt-1">
                  <span class="col-sm-6">
                    <a href="{{ route('delete.references', $item->id)}}" class="btn btn-danger btn-sm mb-1" id="delete"><i class="fas fa-trash-alt"></i></a>
                  </span>
                  <span class="col-sm-6">
                    <input type="number" data-id="{{$item->id}}" class="form-control galeriSira" name="sira" value="{{$item->sira}}"></span>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {

      $(".galeriSira").keyup(function(){
        var sira = $(this).val();
       var dataId = $(this).attr('data-id');
       var veriler = 'galeriId=' + dataId + '&sira='+sira;
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
          }
        });
       $.ajax({
        url: "{{ route('store.references.sort') }}",
        type: "POST",
        data: veriler,
        success: function(data) {
         console.log(data);
        }
       });     
      });
    });
</script>

<script type="text/javascript">
  Dropzone.options.dropzone = {
    thumbnailWidth:200,
    maxFileSize:1,
    acceptedFiles:".jpeg, .jpg, .png",
    addRemoveLinks:true,
    timeout:5000,
    init: function () {
      this.on("queuecomplete", function (file) {
        location.reload();
      });
    }
  };
</script>

@endsection