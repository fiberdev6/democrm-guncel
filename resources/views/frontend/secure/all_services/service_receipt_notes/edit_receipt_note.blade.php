<form method="post" id="servisFisNotuDuzenle" action="{{ route('update.service.receipt.note', $firma->id) }}" class="col-sm-6" style="margin: 0 auto;padding:10px;">
  @csrf

  <div class="row form-group">
    <div class="col-lg-12 rw2">
      <textarea type="text" name="aciklama" class="form-control aciklama" placeholder="Buraya yazın.." rows="3" style="resize: none;" autocomplete="off" required>{{$note_id->aciklama}}</textarea>
    </div>
  </div>

  <div style="text-align: center;margin-top: 5px;">
    <input type="hidden" name="note_id" value="{{$note_id->id}}">
    <input type="hidden" name="servisid" class="servisid" value="{{$note_id->servisid}}"/>
    <input type="submit" class="btn btn-primary btn-sm" value="Gönder"/>
  </div>
    
</form>

<script>
  $(document).ready(function () {
    $('#servisFisNotuDuzenle').submit(function (event) {
      var formIsValid = true;
      $(this).find('input, select').each(function () {
        var isRequired = $(this).prop('required');
        var isEmpty = !$(this).val();
        if (isRequired && isEmpty) {
          formIsValid = false;
          return false;
        }
      });
      if (!formIsValid) {
        event.preventDefault();
        alert('Lütfen zorunlu alanları doldurun.');
        return false;
      }
    });
  });
</script>

<script>
  $(document).ready(function (e) {
    $("#servisFisNotuDuzenle").submit(function (event) {
      event.preventDefault();
      if (this.checkValidity() === false) {
        e.stopPropagation();
      } else {
      var formData = new FormData(this);
      $.ajax({
        url: $(this).attr("action"),
        type: "POST",
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        beforeSend: function () {
          $(".btnWrap").html("Yükleniyor. Bekleyin..");
        },
        success: function (data) {
          if (data.success) { 
            alert("Servis fiş notu başarıyla güncellendi.");
            $('#datatableService').DataTable().ajax.reload();
            $('.nav7').trigger('click');   
            
          } else {
              alert("Kayıt yapılamadı.");
              window.location.reload(true);
          }
        },
        error: function (xhr, status, error) {
          alert("Güncelleme başarısız!");
          
        },
      });
    }
    });
  });
</script>