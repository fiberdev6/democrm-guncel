
        <div id="sifreKate">

    <div class="d-none d-lg-block">
        <table id="datatableDeviceBrand" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
            <a  class="btn btn-success btn-sm mb-1 addDevice" data-bs-toggle="modal" data-bs-target="#addDeviceModal"><i class="fas fa-plus"></i><span>Marka Ekle</span></a>
            <thead class="title">
              <tr>
                <th style="width: 10px">ID</th>
                <th data-priority="2">Marka</th>
                <th>Telefon</th>
                <th>S. Ücreti</th>
                <th>Operatör Prim</th>
                <th>Atolye Prim</th>
                <th data-priority="1" style="width: 96px;">Düzenle</th>
              </tr>
            </thead>
            <tbody>
              @foreach($device_brands as $item)
                <tr data-id="{{$item->id}}">
                  <td class="gizli"><a class="t-link editDevice idWrap" href="javascript:void(0);" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editDeviceModal">{{$item->id}}</a></td>
                  <td><a class="t-link editDevice" href="javascript:void(0);" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editDeviceModal"><div class="mobileTitle">Marka:</div>{{$item->marka}}</a></td>
                  <td><a class="t-link editDevice" href="javascript:void(0);" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editDeviceModal"><div class="mobileTitle">Telefon:</div>{{$item->aciklama}}</a></td>
                  <td><a class="t-link editDevice" href="javascript:void(0);" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editDeviceModal"><div class="mobileTitle">S.Ü.:</div>{{$item->servisUcreti}}</a></td>
                  <td><a class="t-link editDevice" href="javascript:void(0);" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editDeviceModal"><div class="mobileTitle">Opt Prim:</div>{{$item->operatorPrim}}</a></td>
                  <td><a class="t-link editDevice" href="javascript:void(0);" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editDeviceModal"><div class="mobileTitle">Atolye Prim:</div>{{$item->atolyePrim}}</a></td>
                  <td class="tabloBtn">
                    <a href="javascript:void(0);" class="btn btn-outline-primary btn-sm editDevice mobilBtn mbuton1" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editDeviceModal" title="Göster"><i class="fas fa-eye"></i> <span> Düzenle</span></a>
                    <a href="javascript:void(0);" class="btn btn-outline-warning btn-sm editDevice mobilBtn mbuton1" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editDeviceModal" title="Düzenle"><i class="fas fa-edit"></i> <span> Düzenle</span></a>
                    <a href="javascript:void(0);"  class="btn btn-outline-danger btn-sm mobilBtn deleteDevice" data-bs-id="{{$item->id}}" title="Sil"><i class="fas fa-trash-alt"></i> <span> Sil</span></a>
                  </td>
                </tr>
              @endforeach
            </tbody>
        </table>

        @if($device_brands->count() > 0)
        <div class="text-muted mt-2" style="font-size: 13px;">
             Toplam: <strong>{{ $device_brands->count() }}</strong> 
        </div>
        @endif
    </div>

    <div class="d-lg-none">
        <a class="btn btn-success btn-sm mb-3 addDevice" data-bs-toggle="modal" data-bs-target="#addDeviceModal"><i class="fas fa-plus"></i><span> Marka Ekle</span></a>

        @foreach($device_brands as $item)
            <div class="card shadow-sm mb-3" data-id="{{$item->id}}">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-muted">Marka</span>
                            <a href="javascript:void(0);" class="t-link editDevice" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editDeviceModal">
                                <h6 class="mb-0 fw-bold d-inline">{{ $item->marka }}</h6>
                            </a>
                        </div>
                        <span class="badge bg-light text-primary border">ID: {{ $item->id }}</span>
                    </div>

                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Telefon:</span>
                            <span class="fw-bold">{{ $item->aciklama }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">S. Ücreti:</span>
                            <span class="fw-bold">{{ $item->servisUcreti }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Operatör Prim:</span>
                            <span class="fw-bold">{{ $item->operatorPrim }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Atolye Prim:</span>
                            <span class="fw-bold">{{ $item->atolyePrim }}</span>
                        </li>
                    </ul>
                </div>
                
                <div class="card-footer bg-white d-flex justify-content-end gap-2 p-2">
                    <a href="javascript:void(0);" class="btn btn-outline-primary btn-sm editDevice mobilBtn mbuton1" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editDeviceModal" title="Göster"><i class="fas fa-eye"></i> <span> Düzenle</span></a>
                    <a href="javascript:void(0);" class="btn btn-outline-warning btn-sm editDevice mobilBtn mbuton1" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editDeviceModal" title="Düzenle"><i class="fas fa-edit"></i> <span> Düzenle</span></a>
                    <a href="javascript:void(0);"  class="btn btn-outline-danger btn-sm mobilBtn deleteDevice" data-bs-id="{{$item->id}}" title="Sil"><i class="fas fa-trash-alt"></i> <span> Sil</span></a>
                </div>
            </div>
        @endforeach
    </div>
</div>

  
  <!-- add modal content -->
  <div id="addDeviceModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content device_brands">
        <div class="modal-header">
          <h6 class="modal-title" id="myModalLabel">Marka Ekle</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
  
  <!-- edit modal content -->
  <div id="editDeviceModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog ">
      <div class="modal-content device_brands">
        <div class="modal-header">
          <h6 class="modal-title" id="myModalLabel">Marka Düzenle</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
  
  
  <script type="text/javascript">
  $(document).ready(function(){
    $(".addDevice").click(function(){
        var firma_id = {{$firma->id}};
      $.ajax({
        url: "/"+ firma_id + "/cihaz-ekle"
      }).done(function(data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#addDeviceModal').modal('show');
          $('#addDeviceModal .modal-body').html(data);
        }
      });
    });
  });
  </script>
  
  <script type="text/javascript">
  $(document).ready(function(){
    $('#sifreKate').on('click', '.editDevice', function(e){
      var id = $(this).attr("data-bs-id");
      var firma_id = {{$firma->id}};
      $.ajax({
        url: "/"+ firma_id + "/cihaz-duzenle/" + id
      }).done(function(data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#editDeviceModal').modal('show');
          $('#editDeviceModal .modal-body').html(data);
        }
      });
    });
    $("#editDeviceModal").on("hidden.bs.modal", function() {
      $(".modal-body").html("");
    });
  
    // Kategori silme işlemi
    $('#sifreKate').on('click', '.deleteDevice', function(e){
      e.preventDefault();
      var id = $(this).attr("data-bs-id");
      var row = $(this).closest('tr');
      var firma_id = {{$firma->id}};
      if(confirm('Bu markayı silmek istediğinize emin misiniz?')) {
        $.ajax({
          url: "/"+ firma_id + "/cihaz-sil/" + id,
          type: "DELETE",
          data: {
            "_token": "{{ csrf_token() }}", // CSRF koruması için token ekleyin
          },
          success: function(response) {
            if(response.success) {
              row.remove(); // Satırı tablodan kaldır
              alert('Cihaz markası başarıyla silindi.');
            } else {
              alert('Marka silinirken bir hata oluştu.');
            }
          },
          error: function(xhr) {
            alert('Marka silinirken bir hata oluştu.');
          }
        });
      }
    });
  });

  $('#datatableDeviceBrand').DataTable({
      paging: false,       // Sayfalama kapalı
      searching: false,    // Arama kutusu kapalı
      info: false,         // "Kayıt yok" bilgisi kapalı
      lengthChange: false, // "10 kayıt göster" kapalı
      ordering: true,      // Sıralama açık kalabilir (istersen false yap)
      language: {
          emptyTable: "Tabloda herhangi bir veri mevcut değil"
      }
  });
  </script>
  
  