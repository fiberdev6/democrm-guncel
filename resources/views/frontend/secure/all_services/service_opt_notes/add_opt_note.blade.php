<form method="post" id="servisOptNotuEkle" action="{{ route('store.service.opt.note', $firma->id) }}" class="col-sm-6" style="margin: 0 auto;padding:10px;">
  @csrf
  <input type="hidden" name="form_token" id="formTokenOptNote" value="">
  <div class="row form-group">
    <div class="col-lg-12 rw2">
      <textarea type="text" name="aciklama" class="form-control aciklama" placeholder="Buraya yazın.." rows="3" style="resize: none;" autocomplete="off" required></textarea>
    </div>
  </div>

  <div style="text-align: center;margin-top: 5px;">
    <input type="hidden" name="servisid" class="servisid" value="{{$servis->id}}"/>
    <input type="submit" class="btn btn-primary btn-sm" value="Gönder"/>
  </div>
    
</form>

<script>
$(document).ready(function() {
    let optNoteFormSubmitting = false;
    
    // Benzersiz token oluştur
    function generateToken() {
        return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    // Sayfa yüklendiğinde ilk token'ı oluştur
    $('#formTokenOptNote').val(generateToken());
    
    // Token yenileme fonksiyonu
    function resetOptNoteFormToken() {
        $('#formTokenOptNote').val(generateToken());
        optNoteFormSubmitting = false;
        $('#servisOptNotuEkle input[type="submit"]').prop('disabled', false).val('Gönder');
    }
    
    // Form submit
    $('#servisOptNotuEkle').on('submit', function(event) {
        event.preventDefault();
        
        // Token kontrolü
        if (optNoteFormSubmitting) {
            alert('Form gönderiliyor, lütfen bekleyin...');
            return false;
        }
        
        // Validasyon kontrolü
        var formIsValid = true;
        $(this).find('input, select, textarea').each(function() {
            var isRequired = $(this).prop('required');
            var isEmpty = !$(this).val();
            if (isRequired && isEmpty) {
                formIsValid = false;
                return false;
            }
        });
        
        if (!formIsValid) {
            alert('Lütfen zorunlu alanları doldurun.');
            return false;
        }
        
        if (this.checkValidity() === false) {
            return false;
        }
        
        // Token işaretle ve butonu disable et
        optNoteFormSubmitting = true;
        $(this).find('input[type="submit"]').prop('disabled', true);
        
        var formData = new FormData(this);
        
        $.ajax({
            url: $(this).attr("action"),
            type: "POST",
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
                $(".btnWrap").html("Yükleniyor. Bekleyin..");
            },
            success: function(data) {
                if (data.success) { 
                    alert("Servis operatör notu başarıyla eklendi.");
                    $('#datatableService').DataTable().ajax.reload();
                    if (typeof loadServiceHistory === 'function') {
                        loadServiceHistory({{ $servis->id }});
                    }
                    $('.nav8').trigger('click');
                } else {
                    alert("Kayıt yapılamadı.");
                    window.location.reload(true);
                }
                // Token'ı yenile
                setTimeout(resetOptNoteFormToken, 3000);
            },
            error: function(xhr, status, error) {
                alert("Güncelleme başarısız!");
                // Token'ı yenile
                resetOptNoteFormToken();
            }
        });
    });
});
</script>