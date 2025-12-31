
        <div id="odemeSekilleri">
          <table id="datatablePaymentMethod" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
            <a  class="btn btn-success btn-sm mb-1 addPaymentMethod" data-bs-toggle="modal" data-bs-target="#addPaymentMethodModal"><i class="fas fa-plus"></i><span>Ödeme Şekli Ekle</span></a>
            <thead class="title">
              <tr>
                <th>Ödeme Şekli</th>
                <th data-priority="1" style="width: 96px;">Düzenle</th>
              </tr>
            </thead>
            <tbody>
              @foreach($payment_methods as $item)
                <tr data-id="{{$item->id}}">
                  <td><a class="t-link editPaymentMethod" href="javascript:void(0);" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editPaymentMethodModal"><div class="mobileTitle">Ö. Şekli:</div>{{$item->odemeSekli}}</a></td>
                  <td class="tabloBtn">
                    <a href="javascript:void(0);" class="btn btn-outline-primary btn-sm editPaymentMethod mobilBtn mbuton1" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editPaymentMethodModal" title="Görüntüle"><i class="fas fa-eye"></i> <span> Düzenle</span></a>
                    <a href="javascript:void(0);" class="btn btn-outline-warning btn-sm editPaymentMethod mobilBtn mbuton1" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editPaymentMethodModal" title="Düzenle"><i class="fas fa-edit"></i> <span> Düzenle</span></a>
                    <a href="javascript:void(0);"  class="btn btn-outline-danger btn-sm mobilBtn deletePaymentMethod" data-bs-id="{{$item->id}}" title="Sil"><i class="fas fa-trash-alt"></i> <span> Sil</span></a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>

          @if($payment_methods->count() > 0)
              <div class="text-muted mt-2" style="font-size: 13px;">
                Toplam: <strong>{{ $payment_methods->count() }}</strong> 
              </div>
          @endif
        </div>
  
  <!-- add modal content -->
  <div id="addPaymentMethodModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content device_brands">
        <div class="modal-header">
          <h6 class="modal-title" id="myModalLabel">Ödeme Şekli Ekle</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
  
  <!-- edit modal content -->
  <div id="editPaymentMethodModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content device_brands">
        <div class="modal-header">
          <h6 class="modal-title" id="myModalLabel">Ödeme Şekli Düzenle</h6>
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
    $(".addPaymentMethod").click(function(){
        var firma_id = {{$firma->id}};
      $.ajax({
        url: "/"+ firma_id + "/odeme-sekli/ekle"
      }).done(function(data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#addPaymentMethodModal').modal('show');
          $('#addPaymentMethodModal .modal-body').html(data);
        }
      });
    });
  });
</script>
  
<script type="text/javascript">
  $(document).ready(function(){
    $('#odemeSekilleri').on('click', '.editPaymentMethod', function(e){
      var id = $(this).attr("data-bs-id");
      var firma_id = {{$firma->id}};
      $.ajax({
        url: "/"+ firma_id + "/odeme-sekli/duzenle/" + id
      }).done(function(data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#editPaymentMethodModal').modal('show');
          $('#editPaymentMethodModal .modal-body').html(data);
        }
      });
    });
    $("#editPaymentMethodModal").on("hidden.bs.modal", function() {
      $(".modal-body").html("");
    });
  
    // silme işlemi
    $('#odemeSekilleri').on('click', '.deletePaymentMethod', function(e){
      e.preventDefault();
      var id = $(this).attr("data-bs-id");
      var row = $(this).closest('tr');
      var firma_id = {{$firma->id}};
      if(confirm('Bu ödeme şeklini silmek istediğinize emin misiniz?')) {
        $.ajax({
          url: "/"+ firma_id + "/odeme-sekli/sil/" + id,
          type: "DELETE",
          data: {
            "_token": "{{ csrf_token() }}", // CSRF koruması için token
          },
          success: function(response) {
            if(response.success) {
              row.remove(); // Satırı tablodan kaldırır
              alert('Ödeme şekli başarıyla silindi.');
            } else {
              alert('Ödeme şekli silinirken bir hata oluştu.');
            }
          },
          error: function(xhr) {
            alert('Ödeme şekli silinirken bir hata oluştu.');
          }
        });
      }
    });
  });

  $('#datatablePaymentMethod').DataTable({
    paging: false,
    searching: false,
    info: false,
    lengthChange: false,
    language: {
        emptyTable: "Tabloda herhangi bir veri mevcut değil"
    }
  });
</script>
  
  