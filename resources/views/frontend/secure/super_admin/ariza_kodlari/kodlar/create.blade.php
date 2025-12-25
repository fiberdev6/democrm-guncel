<form id="kodEkle" method="POST">
    @csrf
    <input type="hidden" name="form_token" id="kodEkleFormToken" value="">
    <input type="hidden" name="marka_id" value="{{ $marka_id }}">
    <input type="hidden" name="model_id" value="{{ $model_id ?? 0 }}">
    
    <div class="form-group">
        <label for="kod">Hata Kodu <span class="text-danger">*</span></label>
        <input type="text" 
               name="kod" 
               id="kod"
               class="form-control kod" 
               placeholder="Örn: E01, F12, C4..." 
               required>
    </div>
    <div class="form-group">
        <label for="baslik">Başlık</label>
        <input type="text" 
               name="baslik" 
               id="baslik"
               class="form-control baslik" 
               placeholder="Kısa açıklama başlığı">
    </div>
    <div class="form-group">
        <label for="aciklama">Açıklama <span class="text-danger">*</span></label>
        <textarea name="aciklama" 
                  id="aciklama"
                  class="form-control aciklama" 
                  rows="5"
                  placeholder="Detaylı arıza açıklaması ve çözüm önerileri..."
                  required></textarea>
    </div>
    <div class="justify-content-end d-flex">
        <button type="submit" class="btn btn-info btn-sm">
             Kaydet
        </button>
    </div>
</form>

<script>
$(document).ready(function() {
    let kodFormSubmitting = false;
    
    // Benzersiz token oluştur
    function generateKodToken() {
        return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    // Sayfa yüklendiğinde ilk token'ı oluştur
    $('#kodEkleFormToken').val(generateKodToken());
    
    $("#kodEkle").on('submit', function(e) {
        e.preventDefault();
        
        // Token kontrolü
        if (kodFormSubmitting) {
            return false;
        }
        
        var kod = $.trim($(".kod").val());
        var aciklama = $.trim($(".aciklama").val());
        
        if (kod.length === 0) {
            alert("Hata kodu boş geçilemez");
            $(".kod").focus();
            return false;
        }
        
        if (aciklama.length === 0) {
            alert("Açıklama boş geçilemez");
            $(".aciklama").focus();
            return false;
        }
        
        // Butonu disable et
        kodFormSubmitting = true;
        $(this).find('button[type="submit"]').prop('disabled', true);
        
        $.ajax({
            url: "{{ route('super.admin.kodlar.store') }}",
            type: "POST",
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData: false,
            success: function(data) {
                $('#kodEkleModal').modal('hide');
                alert(data.message);
                location.reload();
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Bir hata oluştu";
                alert("Hata: " + msg);
                
                // Yeni token oluştur ve formu yeniden aktif et
                $('#kodEkleFormToken').val(generateKodToken());
                kodFormSubmitting = false;
                $('#kodEkle button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save"></i> Kaydet');
            }
        });
        
        // 3 saniye sonra yeniden aktif et
        setTimeout(function() {
            $('#kodEkleFormToken').val(generateKodToken());
            kodFormSubmitting = false;
            $('#kodEkle button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save"></i> Kaydet');
        }, 3000);
        
        return false;
    });
});
</script>