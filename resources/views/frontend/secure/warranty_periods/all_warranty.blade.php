
        <div id="garantiSuresi">
          <table id="datatableWarranty" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
            <a  class="btn btn-success btn-sm mb-1 addWarranty" data-bs-toggle="modal" data-bs-target="#addWarrantyModal"><i class="fas fa-plus"></i><span>Garanti Ekle</span></a>
            <thead class="title">
              <tr>
                <th>Garanti Süresi</th>
                <th data-priority="1" style="width: 96px;">Düzenle</th>
              </tr>
            </thead>
            <tbody>
              @foreach($warranties as $item)
                <tr data-id="{{$item->id}}">
                  <td><a class="t-link editWarranty" href="javascript:void(0);" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editWarrantyModal"><div class="mobileTitle">Garanti Süresi:</div>{{$item->garanti}} Ay</a></td>
                  <td class="tabloBtn">
                    <a href="javascript:void(0);" class="btn btn-outline-primary btn-sm editWarranty mobilBtn mbuton1" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editWarranty" title="Görüntüle"><i class="fas fa-eye"></i> <span> Düzenle</span></a>
                    <a href="javascript:void(0);" class="btn btn-outline-warning btn-sm editWarranty mobilBtn mbuton1" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editWarranty" title="Düzenle"><i class="fas fa-edit"></i> <span> Düzenle</span></a>
                    <a href="javascript:void(0);"  class="btn btn-outline-danger btn-sm mobilBtn deleteWarranty" data-bs-id="{{$item->id}}" title="Sil"><i class="fas fa-trash-alt"></i> <span> Sil</span></a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>

            @if($warranties->count() > 0)
              <div class="text-muted mt-2" style="font-size: 13px;">
                Toplam: <strong>{{ $warranties->count() }}</strong> 
              </div>
            @endif
        </div>
  
  <!-- add modal content -->
  <div id="addWarrantyModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title" id="myModalLabel">Garanti Ekle</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
  
  <!-- edit modal content -->
  <div id="editWarrantyModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title" id="myModalLabel">Garanti Düzenle</h6>
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
    $(".addWarranty").click(function(){
        var firma_id = {{$firma->id}};
      $.ajax({
        url: "/"+ firma_id + "/garanti-ekle"
      }).done(function(data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#addWarrantyModal').modal('show');
          $('#addWarrantyModal .modal-body').html(data);
        }
      });
    });
  });
  </script>
  
  <script type="text/javascript">
  $(document).ready(function(){
    $('#garantiSuresi').on('click', '.editWarranty', function(e){
      var id = $(this).attr("data-bs-id");
      var firma_id = {{$firma->id}};
      $.ajax({
        url: "/"+ firma_id + "/garanti-duzenle/" + id
      }).done(function(data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#editWarrantyModal').modal('show');
          $('#editWarrantyModal .modal-body').html(data);
        }
      });
    });
    $("#editWarrantyModal").on("hidden.bs.modal", function() {
      $(".modal-body").html("");
    });
  
    // silme işlemi
    $('#garantiSuresi').on('click', '.deleteWarranty', function(e){
      e.preventDefault();
      var id = $(this).attr("data-bs-id");
      var row = $(this).closest('tr');
      var firma_id = {{$firma->id}};
      if(confirm('Bu garanti süresini silmek istediğinize emin misiniz?')) {
        $.ajax({
          url: "/"+ firma_id + "/garanti-sil/" + id,
          type: "DELETE",
          data: {
            "_token": "{{ csrf_token() }}", // CSRF koruması için token ekleyin
          },
          success: function(response) {
            if(response.success) {
              row.remove(); // Satırı tablodan kaldır
              alert('Garanti süresi başarıyla silindi.');
            } else {
              alert('Garanti süresi silinirken bir hata oluştu.');
            }
          },
          error: function(xhr) {
            alert('Garanti süresi silinirken bir hata oluştu.');
          }
        });
      }
    });
  });

  $('#datatableWarranty').DataTable({
    paging: false,
    searching: false,
    info: false,
    lengthChange: false,
    language: {
        emptyTable: "Tabloda herhangi bir veri mevcut değil"
    }
  });
  </script>
  
  