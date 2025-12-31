
        <div id="stokRaflari">
          <table id="datatableStockShelf" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
            <a  class="btn btn-success btn-sm mb-1 addStockShelf" data-bs-toggle="modal" data-bs-target="#addStockShelfModal"><i class="fas fa-plus"></i><span>Stok Rafı Ekle</span></a>
            <thead class="title">
              <tr>
                <th>Raf Adı</th>
                <th data-priority="1" style="width: 96px;">Düzenle</th>
              </tr>
            </thead>
            <tbody>
              @foreach($shelves as $item)
                <tr data-id="{{$item->id}}">
                  <td><a class="t-link editStockShelf" href="javascript:void(0);" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editStockShelfModal"><div class="mobileTitle">Raf Adı:</div>{{$item->raf_adi}}</a></td>
                  <td class="tabloBtn">
                    <a href="javascript:void(0);" class="btn btn-outline-primary btn-sm editStockShelf mobilBtn mbuton1" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editStockShelfModal" title="Görüntüle"><i class="fas fa-eye"></i> <span> Düzenle</span></a>
                    <a href="javascript:void(0);" class="btn btn-outline-warning btn-sm editStockShelf mobilBtn mbuton1" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editStockShelfModal" title="Düzenle"><i class="fas fa-edit"></i> <span> Düzenle</span></a>
                    <a href="javascript:void(0);"  class="btn btn-outline-danger btn-sm mobilBtn deleteStockShelf" data-bs-id="{{$item->id}}" title="Sil"><i class="fas fa-trash-alt"></i> <span> Sil</span></a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>

          @if($shelves->count() > 0)
              <div class="text-muted mt-2" style="font-size: 13px;">
                Toplam: <strong>{{ $shelves->count() }}</strong> 
              </div>
          @endif
        </div>
  
  <!-- add modal content -->
  <div id="addStockShelfModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content device_brands">
        <div class="modal-header">
          <h6 class="modal-title" id="myModalLabel">Stok Rafı Ekle</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
  
  <!-- edit modal content -->
  <div id="editStockShelfModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content device_brands">
        <div class="modal-header">
          <h6 class="modal-title" id="myModalLabel">Stok Rafı Düzenle</h6>
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
    $(".addStockShelf").click(function(){
        var firma_id = {{$firma->id}};
      $.ajax({
        url: "/"+ firma_id + "/stok-raf/ekle"
      }).done(function(data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#addStockShelfModal').modal('show');
          $('#addStockShelfModal .modal-body').html(data);
        }
      });
    });
  });
</script>
  
<script type="text/javascript">
  $(document).ready(function(){
    $('#stokRaflari').on('click', '.editStockShelf', function(e){
      var id = $(this).attr("data-bs-id");
      var firma_id = {{$firma->id}};
      $.ajax({
        url: "/"+ firma_id + "/stok-raf/duzenle/" + id
      }).done(function(data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#editStockShelfModal').modal('show');
          $('#editStockShelfModal .modal-body').html(data);
        }
      });
    });
    $("#editStockShelfModal").on("hidden.bs.modal", function() {
      $(".modal-body").html("");
    });
  
    // silme işlemi
    $('#stokRaflari').on('click', '.deleteStockShelf', function(e){
      e.preventDefault();
      var id = $(this).attr("data-bs-id");
      var row = $(this).closest('tr');
      var firma_id = {{$firma->id}};
      if(confirm('Bu stok rafını silmek istediğinize emin misiniz?')) {
        $.ajax({
          url: "/"+ firma_id + "/stok-raf/sil/" + id,
          type: "DELETE",
          data: {
            "_token": "{{ csrf_token() }}", // CSRF koruması için token ekleyin
          },
          success: function(response) {
            if(response.success) {
              row.remove(); // Satırı tablodan kaldır
              alert('Stok rafı başarıyla silindi.');
            } else {
              alert('Stok rafı silinirken bir hata oluştu.');
            }
          },
          error: function(xhr) {
            alert('Stok rafı silinirken bir hata oluştu.');
          }
        });
      }
    });
  });

  $('#datatableStockShelf').DataTable({
    paging: false,
    searching: false,
    info: false,
    lengthChange: false,
    language: {
        emptyTable: "Tabloda herhangi bir veri mevcut değil"
    }
  });
</script>
  
  