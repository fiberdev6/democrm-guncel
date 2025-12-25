<form id="kodDuzenle" method="POST">
    @csrf
    <div class="form-group">
        <label for="duzenle_kod">Hata Kodu <span class="text-danger">*</span></label>
        <input type="text" 
               name="kod" 
               id="duzenle_kod"
               class="form-control kod" 
               placeholder="Hata Kodu" 
               value="{{ $kodSec->kodu }}" 
               required>
    </div>
    <div class="form-group">
        <label for="duzenle_baslik">Başlık</label>
        <input type="text" 
               name="baslik" 
               id="duzenle_baslik"
               class="form-control baslik" 
               placeholder="Başlık" 
               value="{{ $kodSec->baslik }}">
    </div>
    <div class="form-group">
        <label for="duzenle_aciklama">Açıklama <span class="text-danger">*</span></label>
        <textarea name="aciklama" 
                  id="duzenle_aciklama"
                  class="form-control aciklama" 
                  rows="5" 
                  placeholder="Açıklama" 
                  required>{{ $kodSec->aciklama }}</textarea>
    </div>
    <div class="justify-content-end d-flex">
        <button type="submit" class="btn btn-primary btn-sm">
           Güncelle
        </button>
    </div>
</form>

<script>
$(document).ready(function() {
    $("#kodDuzenle").on('submit', function(e) {
        e.preventDefault();
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
        
        $.ajax({
            url: "{{ route('super.admin.kodlar.update', $kodSec->id) }}",
            type: "POST",
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData: false,
            success: function(data) {
                alert(data.message);
                window.location.href = "{{ route('super.admin.kodlar.index', ['marka_id' => $kodSec->marka_id, 'model_id' => $kodSec->model_id]) }}";
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Bir hata oluştu";
                alert("Hata: " + msg);
            }
        });
    });
});
</script>