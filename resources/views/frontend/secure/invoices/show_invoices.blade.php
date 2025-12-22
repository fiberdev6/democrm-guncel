<form method="post" id="showInvo" action="{{ route('upload.invoices', $firma->id) }}"  enctype="multipart/form-data">
    @csrf
    <div class=" form-group">
      <div class="col-md-12 rw2">
        @if(empty($invoice_id->faturaPdf))
          <div class="imgLoadWrap">
            <span class="imgLoad" style="font-size: 14px;display: none">Yükleniyor. Lütfen Bekleyin.. <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 63%;height: 15px;border-radius: 10px;display: inline-block;position: relative;top: 2px;"></div></span>
            <input class="form-control" name="pdf" type="file"  required>
            <span style="font-size: 12px;color: red;line-height: initial;display: block;padding-left: 1px;">Sadece jpg, png veya pdf uzantılı dosyalar yükleyebilirsiniz.</span>
          </div>

          <div class="row">
          <label class="col-sm-3 col-form-label"></label>
          <div class="col-sm-9">
          <input type="hidden" name="id" value="{{$invoice_id->id}}">
            <input type="submit" class="btn btn-info waves-effect waves-light" value="Gönder">
          </div>
        </div>
        @endif

        @if(!empty($invoice_id->faturaPdf))
        <div class="row faturalar" style="margin-left: -15px;margin-right: -15px;">
          <div class=" stn">
            <i class="far fa-file-pdf"></i>
            <div class="btnWrap">
              <a href="{{asset($invoice_id->faturaPdf)}}" target="_blank" class="btn btn-warning btn-sm btn-block">Görüntüle</a>
              <a href="" class="btn btn-danger btn-sm btn-block eArsivSil" data-id="{{$invoice_id->id}}">Sil</a>
            </div>
          </div>
        </div>
        @endif
       
      </div>
    </div>
</form>

<script>
  $(document).ready(function () {
    $('#showInvo').submit(function (event) {
      event.preventDefault();
      var formData = new FormData(this);
  
      $.ajax({
        url: $(this).attr("action"),
        type: "POST",
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        success: function (data) {
          if (data === false) {
            
            window.location.reload(true);
          } else {
            alert("Fatura güncellendi");
            
            $('#InvoiceModal').modal('hide');
            $('#datatableInvoice').DataTable().ajax.reload();
            $('#editInvoiceModal').modal('hide');
          }
        },
        error: function (xhr, status, error) {
          alert("Güncelleme başarısız!");
          window.location.reload(true);
        },
      });
    });
  });
</script>

<script>
  $(document).ready(function() {
    $('#showInvo').on('click', '.eArsivSil', function(e) {
      e.preventDefault();
      var confirmDelete = confirm("Bu e-faturayı silmek istediğinizden emin misiniz?");
      if (confirmDelete) {
        var id = $(this).attr('data-id');
        var firma_id = {{$firma->id}};
        $.ajax({
          url: '/' + firma_id + '/eArsiv/sil/' + id,
          type: 'POST',
          data: {
            _method: 'POST', 
            _token: '{{ csrf_token() }}'
          },
          success: function(data) {
            if (data) {
              $('#datatableInvoice').DataTable().ajax.reload();
              $('#InvoiceModal').modal('hide');
              $('#editInvoiceModal').modal('hide');
            } else {
              alert("Silme işlemi başarısız oldu.");
            }
          },
          error: function(xhr, status, error) {
            console.error(xhr.responseText);
          }
        });
      }
    });
  });
</script>