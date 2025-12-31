
        <div id="servisAraclari">
          <table id="datatableCars" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
            <a  class="btn btn-success btn-sm mb-1 addCar" data-bs-toggle="modal" data-bs-target="#addCarModal"><i class="fas fa-plus"></i><span>Servis Aracı Ekle</span></a>
            <thead class="title">
              <tr>
                <th>Araç</th>
                <th data-priority="1" style="width: 96px;">Düzenle</th>
              </tr>
            </thead>
            <tbody>
              @foreach($all_cars as $item)
                <tr data-id="{{$item->id}}">
                  <td><a class="t-link editCar" href="javascript:void(0);" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editCarModal"><div class="mobileTitle">Araç:</div>{{$item->arac}}</a></td>
                  <td class="tabloBtn">
                    <a href="javascript:void(0);" class="btn btn-outline-primary btn-sm editCar mobilBtn mbuton1" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editCarModal" title="Düzenle"><i class="fas fa-eye"></i> <span> Düzenle</span></a>
                    <a href="javascript:void(0);" class="btn btn-outline-warning btn-sm editCar mobilBtn mbuton1" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editCarModal" title="Düzenle"><i class="fas fa-edit"></i> <span> Düzenle</span></a>
                    <a href="javascript:void(0);"  class="btn btn-outline-danger btn-sm mobilBtn deleteCar" data-bs-id="{{$item->id}}" title="Sil"><i class="fas fa-trash-alt"></i> <span> Sil</span></a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>

            @if($all_cars->count() > 0)
              <div class="text-muted mt-2" style="font-size: 13px;">
                Toplam: <strong>{{ $all_cars->count() }}</strong> 
              </div>
            @endif
        </div>
  
  <!-- add modal content -->
  <div id="addCarModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content device_brands">
        <div class="modal-header">
          <h6 class="modal-title" id="myModalLabel">Araç Ekle</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
  
  <!-- edit modal content -->
  <div id="editCarModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content device_brands">
        <div class="modal-header">
          <h6 class="modal-title" id="myModalLabel">Araç Düzenle</h6>
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
    $(".addCar").click(function(){
        var firma_id = {{$firma->id}};
      $.ajax({
        url: "/"+ firma_id + "/arac-ekle"
      }).done(function(data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#addCarModal').modal('show');
          $('#addCarModal .modal-body').html(data);
        }
      });
    });
  });
  </script>
  
  <script type="text/javascript">
  $(document).ready(function(){
    $('#servisAraclari').on('click', '.editCar', function(e){
      var id = $(this).attr("data-bs-id");
      var firma_id = {{$firma->id}};
      $.ajax({
        url: "/"+ firma_id + "/arac-duzenle/" + id
      }).done(function(data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#editCarModal').modal('show');
          $('#editCarModal .modal-body').html(data);
        }
      });
    });
    $("#editCarModal").on("hidden.bs.modal", function() {
      $(".modal-body").html("");
    });
  
    // silme işlemi
    $('#servisAraclari').on('click', '.deleteCar', function(e){
      e.preventDefault();
      var id = $(this).attr("data-bs-id");
      var row = $(this).closest('tr');
      var firma_id = {{$firma->id}};
      if(confirm('Bu servis aracını silmek istediğinize emin misiniz?')) {
        $.ajax({
          url: "/"+ firma_id + "/arac-sil/" + id,
          type: "DELETE",
          data: {
            "_token": "{{ csrf_token() }}", // CSRF koruması için token ekleyin
          },
          success: function(response) {
            if(response.success) {
              row.remove(); // Satırı tablodan kaldır
              alert('Servis aracı başarıyla silindi.');
            } else {
              alert('Servis aracı silinirken bir hata oluştu.');
            }
          },
          error: function(xhr) {
            alert('Servis aracı silinirken bir hata oluştu.');
          }
        });
      }
    });
  });

  $('#datatableCars').DataTable({
    paging: false,
    searching: false,
    info: false,
    lengthChange: false,
    language: {
        emptyTable: "Tabloda herhangi bir veri mevcut değil"
    }
  });
  </script>
  
  