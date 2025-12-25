@extends('backend.admin_master')
@section('admin')

<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Katalog Ekleme</h4><br>
            <form method="post" action="{{ route('update.documents')}}" enctype="multipart/form-data" class="needs-validation " >   <!--buradaki enctype veritabanına resim yüklenmesine yarıyor -->
              @csrf
              @if (count($errors) > 0)
                @foreach ($errors->all() as $error)
                {{ $error }}
                @endforeach
              @endif @if ($message = Session::get('success')) × {{ $message }} @endif
              
              <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Katalog Adı:</label>
                <div class="col-sm-10">                 
                  <input class="form-control" name="title" type="text" value="" required>
                </div>
              </div>
              
              
              <div class="row mb-3">
                <label for="example-text-input" class="col-sm-2 col-form-label">Katalog:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="file" type="file"  id="image" required>
                 
                  <label class=" col-form-label">Not: Maksimum dosya boyutu 1.24MB'tan fazla olmamalıdır.</label>
                </div>
              </div>

              <!-- <div class="row mb-3">
                <label for="example-text-input" class="col-sm-2 col-form-label">Belge resmi:</label>
                <div class="col-sm-10">
                  <input class="form-control" name="image" type="file"  id="image" required>   
                  <label class=" col-form-label">Not: Maksimum dosya boyutu 2MB'tan fazla olmamalıdır.</label>
                </div>
              </div> -->

              <input type="submit" class="btn btn-info waves-effect waves-light" value="Gönder">

            </form>
          </div>
        </div>
      </div> <!-- end col -->


      <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
              <thead>
                <tr>
                  <th style="width: 10px">ID</th>
                  <th data-priority="2">Katalog Adı</th>
                  <th >Katalog</th>
                  <th data-priority="1" style="min-width: 50px;width: 50px;max-width: 50px;"></th>
                </tr>
              </thead>

              <tbody>
                @foreach($documents as $item)
                <tr>
                  <td>{{$item->id}}</td>
                  <td>{{$item->title}}</td>
                  <td><a href="{{asset($item->files)}}" target="_blank" class="btn btn-warning"><i class="fa fa-eye me-1"></i> Önizle</a></td>
                  <td>
                    <a href="{{ route('delete.documents', $item->id)}}" class="btn btn-danger btn-sm" id="delete" title="Sil"><i class="fas fa-trash-alt"></i></a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div> <!-- end col -->
    </div> <!-- end row -->

      
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

<!-- <script type="text/javascript">
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
</script> -->

@endsection