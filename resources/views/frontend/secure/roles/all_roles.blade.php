<div  id="roller">
        <table id="datatableRole" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
          <a class="btn btn-success btn-sm addRole mb-1" data-bs-toggle="modal" data-bs-target="#addRoleModal"><i class="fas fa-plus"></i><span>Rol Ekle</span></a>
          <thead class="title">
            <tr>
              <th style="width: 10px">ID</th>
              <th data-priority="2">Rol Adı</th>
              <th data-priority="1" style="width: 96px;">Düzenle</th>
            </tr>
          </thead>
          <tbody>
            @foreach($roles as $item)
              <tr data-id="{{$item->id}}">
                <td class="gizli"><a class="t-link editRole idWrap" href="javascript:void(0);" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editRoleModal">{{$item->id}}</a></td>
                <td><a class="t-link editRole" href="javascript:void(0);" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editRoleModal"><div class="mobileTitle">Rol Adı:</div>{{$item->name}}</a></td>
                <td class="tabloBtn">
                  <a href="javascript:void(0);" class="btn btn-outline-primary btn-sm editRole mobilBtn mbuton1" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editRoleModal" title="Görüntüle"><i class="fas fa-eye"></i> <span> Düzenle</span></a>
                  <a href="javascript:void(0);" class="btn btn-outline-warning btn-sm editRole mobilBtn mbuton1" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editRoleModal" title="Düzenle"><i class="fas fa-edit"></i> <span> Düzenle</span></a>
                  <a href="javascript:void(0);" class="btn btn-outline-danger btn-sm mobilBtn deleteRole" data-bs-id="{{$item->id}}" title="Sil"><i class="fas fa-trash-alt"></i> <span> Sil</span></a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>

        @if($roles->count() > 0)
              <div class="text-muted mt-2" style="font-size: 13px;">
                Toplam: <strong>{{ $roles->count() }}</strong> 
              </div>
          @endif
      </div>

<!-- add modal content -->
<div id="addRoleModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content device_brands">
      <div class="modal-header">
        <h6 class="modal-title" id="myModalLabel">Rol Ekle</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      Yükleniyor...
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<!-- edit modal content -->
<div id="editRoleModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="myModalLabel">Rol Düzenle</h6>
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
  $(".addRole").click(function(){
    var firma_id = {{$firma->id}};
    $.ajax({
      url: "/"+ firma_id +"/rol/ekle/"
    }).done(function(data) {
      if ($.trim(data) === "-1") {
        window.location.reload(true);
      } else {
        $('#addRoleModal').modal('show');
        $('#addRoleModal .modal-body').html(data);
      }
    });
  });
});
</script>

<script type="text/javascript">
  $(document).ready(function(){
    $('#roller').on('click', '.editRole', function(e){
      var id = $(this).attr("data-bs-id");
      var firma_id = {{$firma->id}};
      $.ajax({
        url: "/"+ firma_id +"/rol/duzenle/" + id
      }).done(function(data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#editRoleModal').modal('show');
          $('#editRoleModal .modal-body').html(data);
        }
      });
    });
    $("#editRoleModal").on("hidden.bs.modal", function() {
      $(".modal-body").html("");
    });

    // Rolü silme işlemi
    $('#roller').on('click', '.deleteRole', function(e){
        e.preventDefault();
        var id = $(this).attr("data-bs-id");
        var row = $(this).closest('tr');
        var firma_id = {{$firma->id}};
        if(confirm('Bu rolü silmek istediğinize emin misiniz?')) {
            $.ajax({
                url: "/"+ firma_id + "/rol/sil/" + id,
                type: "DELETE",
                data: {
                    "_token": "{{ csrf_token() }}", // CSRF koruması için
                },
                success: function(response) {
                    if(response.success) {
                        row.remove(); // Satırı tablodan kaldır
                        alert('Rol başarıyla silindi.');
                    } else {
                        alert('Rol silinirken bir hata oluştu.');
                    }
                },
                error: function(xhr) {
                    alert('Rol silinirken bir hata oluştu.');
                }
            });
        }
    });
  });

  $('#datatableRole').DataTable({
    paging: false,
    searching: false,
    info: false,
    lengthChange: false,
    language: {
        emptyTable: "Tabloda herhangi bir veri mevcut değil"
    }
  });
</script>