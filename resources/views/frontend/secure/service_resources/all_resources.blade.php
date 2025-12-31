
        <div id="servisKaynak">
          <table id="datatableServiceResource" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
            <a  class="btn btn-success btn-sm mb-1 addServiceResource" data-bs-toggle="modal" data-bs-target="#addServiceResourceModal"><i class="fas fa-plus"></i><span>Servis Kaynağı Ekle</span></a>
            <thead class="title">
              <tr>
                <th>Kaynak</th>
                <th data-priority="1" style="width: 96px;">Düzenle</th>
              </tr>
            </thead>
            <tbody>
              @foreach($resources as $item)
                <tr data-id="{{$item->id}}">
                  <td><a class="t-link editServiceResource" href="javascript:void(0);" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editServiceResourceModal"><div class="mobileTitle">Kaynak:</div>{{$item->kaynak}}</a></td>
                  <td class="tabloBtn">
                    <a href="javascript:void(0);" class="btn btn-outline-primary btn-sm editServiceResource mobilBtn mbuton1" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editServiceResource" title="Görüntüle"><i class="fas fa-eye"></i> <span> Düzenle</span></a>
                    <a href="javascript:void(0);" class="btn btn-outline-warning btn-sm editServiceResource mobilBtn mbuton1" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editServiceResource" title="Düzenle"><i class="fas fa-edit"></i> <span> Düzenle</span></a>
                    <a href="javascript:void(0);"  class="btn btn-outline-danger btn-sm mobilBtn deleteServiceResource" data-bs-id="{{$item->id}}" title="Sil"><i class="fas fa-trash-alt"></i> <span> Sil</span></a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>

            @if($resources->count() > 0)
              <div class="text-muted mt-2" style="font-size: 13px;">
                Toplam: <strong>{{ $resources->count() }}</strong> 
              </div>
            @endif
        </div>
  
  <!-- add modal content -->
  <div id="addServiceResourceModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content device_brands">
        <div class="modal-header">
          <h6 class="modal-title" id="myModalLabel">Kaynak Ekle</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
  
  <!-- edit modal content -->
  <div id="editServiceResourceModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content device_brands">
        <div class="modal-header">
          <h6 class="modal-title" id="myModalLabel">Kaynak Düzenle</h6>
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
    $(".addServiceResource").click(function(){
        var firma_id = {{$firma->id}};
      $.ajax({
        url: "/"+ firma_id + "/servis-kaynak/ekle"
      }).done(function(data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#addServiceResourceModal').modal('show');
          $('#addServiceResourceModal .modal-body').html(data);
        }
      });
    });
  });
</script>
  
<script type="text/javascript">
  $(document).ready(function(){
    $('#servisKaynak').on('click', '.editServiceResource', function(e){
      var id = $(this).attr("data-bs-id");
      var firma_id = {{$firma->id}};
      $.ajax({
        url: "/"+ firma_id + "/servis-kaynak/duzenle/" + id
      }).done(function(data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#editServiceResourceModal').modal('show');
          $('#editServiceResourceModal .modal-body').html(data);
        }
      });
    });
    $("#editServiceResourceModal").on("hidden.bs.modal", function() {
      $(".modal-body").html("");
    });
  
    // silme işlemi
    $('#servisKaynak').on('click', '.deleteServiceResource', function(e){
      e.preventDefault();
      var id = $(this).attr("data-bs-id");
      var row = $(this).closest('tr');
      var firma_id = {{$firma->id}};
      if(confirm('Bu servis kaynağını silmek istediğinize emin misiniz?')) {
        $.ajax({
          url: "/"+ firma_id + "/servis-kaynak/sil/" + id,
          type: "DELETE",
          data: {
            "_token": "{{ csrf_token() }}", // CSRF koruması için token ekleyin
          },
          success: function(response) {
            if(response.success) {
              row.remove(); // Satırı tablodan kaldır
              alert('Servis kaynağı başarıyla silindi.');
            } else {
              alert('Servis kaynağı silinirken bir hata oluştu.');
            }
          },
          error: function(xhr) {
            alert('Servis kaynağı silinirken bir hata oluştu.');
          }
        });
      }
    });
  });

  $('#datatableServiceResource').DataTable({
    paging: false,
    searching: false,
    info: false,
    lengthChange: false,
    language: {
        emptyTable: "Tabloda herhangi bir veri mevcut değil"
    }
  });
</script>
  
  