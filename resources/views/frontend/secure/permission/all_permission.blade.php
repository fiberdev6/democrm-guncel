
      <div id="izinler">
        <a  class="btn btn-success btn-sm addPermission mb-1" data-bs-toggle="modal" data-bs-target="#addPermissionModal"><i class="fas fa-plus"></i><span>İzin Ekle</span></a>
        <table id="datatablePermission" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
          <thead class="title">
            <tr>
              <th style="width: 10px">ID</th>
              <th data-priority="2">İzin Adı</th>
              <th>Grup Adı</th>
              <th data-priority="1" style="width: 96px;">Düzenle</th>
            </tr>
          </thead>
          <tbody>
            @foreach($permissions as $item)
              <tr data-id="{{$item->id}}">
                <td class="gizli"><a class="t-link editPermission idWrap" href="javascript:void(0);" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editPermissionModal">{{$item->id}}</a></td>
                <td><a class="t-link editPermission" href="javascript:void(0);" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editPermissionModal"><div class="mobileTitle">İzin Adı:</div>{{$item->name}}</a></td>
                <td><a class="t-link editPermission" href="javascript:void(0);" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editPermissionModal"><div class="mobileTitle">Grup:</div>{{$item->group_name}}</a></td>
                <td>
                  <a href="javascript:void(0);" class="btn btn-outline-primary btn-sm editPermission mobilBtn mbuton1" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editPermissionModal" title="Görüntüle"><i class="fas fa-eye"></i> <span> Düzenle</span></a>
                  <a href="javascript:void(0);" class="btn btn-outline-warning btn-sm editPermission mobilBtn mbuton1" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editPermissionModal" title="Düzenle"><i class="fas fa-edit"></i> <span> Düzenle</span></a>
                  <a href="javascript:void(0);" class="btn btn-outline-danger btn-sm mobilBtn deletePermission" data-bs-id="{{$item->id}}" title="Sil"><i class="fas fa-trash-alt"></i> <span> Sil</span></a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>

        @if($permissions->count() > 0)
              <div class="text-muted mt-2" style="font-size: 13px;">
                Toplam: <strong>{{ $permissions->count() }}</strong> 
              </div>
          @endif
      </div>

<!-- add modal content -->
<div id="addPermissionModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content device_brands">
      <div class="modal-header">
        <h6 class="modal-title" id="myModalLabel">İzin Ekle</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      Yükleniyor...
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<!-- edit modal content -->
<div id="editPermissionModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content device_brands">
      <div class="modal-header">
        <h6 class="modal-title" id="myModalLabel">İzin Düzenle</h6>
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
  $(".addPermission").click(function(){
    var firma_id = {{$firma->id}};
    $.ajax({
      url: "/"+ firma_id + "/izin/ekle/"
    }).done(function(data) {
      if ($.trim(data) === "-1") {
        window.location.reload(true);
      } else {
        $('#addPermissionModal').modal('show');
        $('#addPermissionModal .modal-body').html(data);
      }
    });
  });
});
</script>

<script type="text/javascript">
  $(document).ready(function(){
    $('#izinler').on('click', '.editPermission', function(e){
      var id = $(this).attr("data-bs-id");
      var firma_id = {{$firma->id}};
      $.ajax({
        url: "/"+ firma_id + "/izin/duzenle/" + id
      }).done(function(data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#editPermissionModal').modal('show');
          $('#editPermissionModal .modal-body').html(data);
        }
      });
    });
    $("#editPermissionModal").on("hidden.bs.modal", function() {
      $(".modal-body").html("");
    });

    // İzin silme işlemi
    $('#izinler').on('click', '.deletePermission', function(e){
        e.preventDefault();
        var id = $(this).attr("data-bs-id");
        var row = $(this).closest('tr');
        var firma_id = {{$firma->id}};
        if(confirm('Bu izni silmek istediğinize emin misiniz?')) {
            $.ajax({
                url: "/"+ firma_id + "/izin/sil/" + id,
                type: "DELETE",
                data: {
                    "_token": "{{ csrf_token() }}", // CSRF koruması için
                },
                success: function(response) {
                    if(response.success) {
                        row.remove(); // Satırı tablodan kaldır
                        alert('İzin başarıyla silindi.');
                    } else {
                        alert('İzin silinirken bir hata oluştu.');
                    }
                },
                error: function(xhr) {
                    alert('İzin silinirken bir hata oluştu.');
                }
            });
        }
    });
  });

  $('#datatablePermission').DataTable({
    paging: false,
    searching: false,
    info: false,
    lengthChange: false,
    language: {
        emptyTable: "Tabloda herhangi bir veri mevcut değil"
    }
  });
</script>