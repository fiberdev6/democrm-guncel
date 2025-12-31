
        <div id="odemeTurleri">
          <table id="datatablePaymentType" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
            <a  class="btn btn-success btn-sm mb-1 addPaymentType" data-bs-toggle="modal" data-bs-target="#addPaymentTypeModal"><i class="fas fa-plus"></i><span>Ödeme Türü Ekle</span></a>
            <thead class="title">
              <tr>
                <th>Ödeme Türü</th>
                <th data-priority="1" style="width: 96px;">Düzenle</th>
              </tr>
            </thead>
            <tbody>
              @foreach($payment_types as $item)
                <tr data-id="{{$item->id}}">
                  <td><a class="t-link editPaymentType" href="javascript:void(0);" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editPaymentTypeModal"><div class="mobileTitle">Ö. Türü:</div>{{$item->odemeTuru}}</a></td>
                  <td class="tabloBtn paymenttypes-mobil">
                    <a href="javascript:void(0);" class="btn btn-outline-primary btn-sm editPaymentType mobilBtn mbuton1" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editPaymentTypeModal" title="Görüntüle"><i class="fas fa-eye"></i> <span> Düzenle</span></a>
                    <a href="javascript:void(0);" class="btn btn-outline-warning btn-sm editPaymentType mobilBtn mbuton1" data-bs-id="{{$item->id}}" data-bs-toggle="modal" data-bs-target="#editPaymentTypeModal" title="Düzenle"><i class="fas fa-edit"></i> <span> Düzenle</span></a>
                    <a href="javascript:void(0);" class="btn btn-outline-danger btn-sm mobilBtn deletePaymentType" data-bs-id="{{$item->id}}" title="Sil"><i class="fas fa-trash-alt"></i> <span> Sil</span></a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>

          @if($payment_types->count() > 0)
              <div class="text-muted mt-2" style="font-size: 13px;">
                Toplam: <strong>{{ $payment_types->count() }}</strong> 
              </div>
          @endif
        </div>
  
  <!-- add modal content -->
  <div id="addPaymentTypeModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content ">
        <div class="modal-header">
          <h6 class="modal-title" id="myModalLabel">Ödeme Türü Ekle</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
  
  <!-- edit modal content -->
  <div id="editPaymentTypeModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title" id="myModalLabel">Ödeme Türü Düzenle</h6>
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
    $(".addPaymentType").click(function(){
        var firma_id = {{$firma->id}};
      $.ajax({
        url: "/"+ firma_id + "/odeme-turu/ekle"
      }).done(function(data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#addPaymentTypeModal').modal('show');
          $('#addPaymentTypeModal .modal-body').html(data);
        }
      });
    });
  });
</script>
  
<script type="text/javascript">
  $(document).ready(function(){
    $('#odemeTurleri').on('click', '.editPaymentType', function(e){
      var id = $(this).attr("data-bs-id");
      var firma_id = {{$firma->id}};
      $.ajax({
        url: "/"+ firma_id + "/odeme-turu/duzenle/" + id
      }).done(function(data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#editPaymentTypeModal').modal('show');
          $('#editPaymentTypeModal .modal-body').html(data);
        }
      });
    });
    $("#editPaymentTypeModal").on("hidden.bs.modal", function() {
      $(".modal-body").html("");
    });
  
    // silme işlemi
    $('#odemeTurleri').on('click', '.deletePaymentType', function(e){
      e.preventDefault();
      var id = $(this).attr("data-bs-id");
      var row = $(this).closest('tr');
      var firma_id = {{$firma->id}};
      if(confirm('Bu ödeme türü silmek istediğinize emin misiniz?')) {
        $.ajax({
          url: "/"+ firma_id + "/odeme-turu/sil/" + id,
          type: "DELETE",
          data: {
            "_token": "{{ csrf_token() }}", // CSRF koruması için token
          },
          success: function(response) {
            if(response.success) {
              row.remove(); // Satırı tablodan kaldırır
              alert('Ödeme türü başarıyla silindi.');
            } else {
              alert('Ödeme türü silinirken bir hata oluştu.');
            }
          },
          error: function(xhr) {
            alert('Ödeme türü silinirken bir hata oluştu.');
          }
        });
      }
    });
  });

  $('#datatablePaymentType').DataTable({
    paging: false,
    searching: false,
    info: false,
    lengthChange: false,
    language: {
        emptyTable: "Tabloda herhangi bir veri mevcut değil"
    }
  });
</script>
  
  