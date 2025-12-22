<form id="modelEkle" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="form_token" id="modelEkleFormToken" value="">
    <input type="hidden" name="mid" value="{{ $marka_id }}">
    
    <div class="form-group">
        <input type="text" name="model" class="form-control model" placeholder="Model Adı" required>
    </div>
    <div class="form-group">
        <input type="file" name="resim" class="form-control-file">
        <small class="text-muted">Sadece jpg, png dosya türlerini yükleyebilirsiniz.</small>
    </div>
    <div class="text-center">
        <button type="submit" class="btn btn-primary btn-sm">Gönder</button>
    </div>
</form>

<script>
$(document).ready(function() {
    let modelFormSubmitting = false;
    
    // Benzersiz token oluştur
    function generateModelToken() {
        return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    // Sayfa yüklendiğinde ilk token'ı oluştur
    $('#modelEkleFormToken').val(generateModelToken());
    
    $("#modelEkle").on('submit', function(e) {
        e.preventDefault();
        
        // Token kontrolü
        if (modelFormSubmitting) {
            return false;
        }
        
        var model = $.trim($(".model").val());
        
        if (model.length === 0) {
            alert("Model adı boş geçilemez");
            $(".model").focus();
            return false;
        }
        
        // Butonu disable et
        modelFormSubmitting = true;
        $(this).find('button[type="submit"]').prop('disabled', true);
        
        $.ajax({
            url: "{{ route('super.admin.modeller.store') }}",
            type: "POST",
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData: false,
            success: function(data) {
                alert(data.message);
                window.location.href = "{{ route('super.admin.modeller.index', $marka_id) }}";
            },
            error: function(xhr) {
                alert("Hata: " + xhr.responseJSON.message);
                
                // Yeni token oluştur ve formu yeniden aktif et
                $('#modelEkleFormToken').val(generateModelToken());
                modelFormSubmitting = false;
                $('#modelEkle button[type="submit"]').prop('disabled', false).html('Gönder');
            }
        });
        
        // 3 saniye sonra yeniden aktif et
        setTimeout(function() {
            $('#modelEkleFormToken').val(generateModelToken());
            modelFormSubmitting = false;
            $('#modelEkle button[type="submit"]').prop('disabled', false).html('Gönder');
        }, 3000);
        
        return false;
    });
});
</script>