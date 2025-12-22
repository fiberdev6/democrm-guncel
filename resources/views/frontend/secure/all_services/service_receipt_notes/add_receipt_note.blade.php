<form method="post" id="servisFisNotuEkle" action="{{ route('store.receipt.note', $firma->id) }}" class="col-sm-6" style="margin: 0 auto;padding:10px;">
  @csrf
  <input type="hidden" name="form_token" id="formTokenReceiptNote" value="">
  <div class="row form-group ">
    <div class="col-lg-12 rw1"><label><strong style="color: red;">Fiş notu, servis fişinde yapılan işlemler bölümünde çıkmaktadır.</strong></label></div>
  </div>

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
    let receiptNoteFormSubmitting = false;
    
    // Benzersiz token oluştur
    function generateToken() {
        return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    // Sayfa yüklendiğinde ilk token'ı oluştur
    $('#formTokenReceiptNote').val(generateToken());
    
    // Token yenileme fonksiyonu
    function resetReceiptNoteFormToken() {
        $('#formTokenReceiptNote').val(generateToken());
        receiptNoteFormSubmitting = false;
        $('#servisFisNotuEkle input[type="submit"]').prop('disabled', false).val('Gönder');
    }
    
    // Form submit
    $('#servisFisNotuEkle').on('submit', function(event) {
        event.preventDefault();
        
        // Token kontrolü
        if (receiptNoteFormSubmitting) {
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
        receiptNoteFormSubmitting = true;
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
                    alert("Servis fiş notu başarıyla eklendi.");
                    $('.nav7').trigger('click');
                } else {
                    alert("Kayıt yapılamadı.");
                    window.location.reload(true);
                }
                // Token'ı yenile
                setTimeout(resetReceiptNoteFormToken, 3000);
            },
            error: function(xhr, status, error) {
                alert("Güncelleme başarısız!");
                // Token'ı yenile
                resetReceiptNoteFormToken();
            }
        });
    });
});
</script>
