<form id="apiTokenForm">
    @csrf
    <div class="mb-3">
        <label class="form-label">Token Adı</label>
        <input type="text" name="name" class="form-control" 
               value="{{ $apiToken ? $apiToken->name : '' }}" 
               placeholder="Örn: Web Sitesi API" required>
        <small class="text-muted">Token'ınıza açıklayıcı bir isim verin</small>
    </div>

    @if($apiToken)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> 
            <strong>Dikkat!</strong> Güncelleme yaparsanız eski token geçersiz olacaktır. Tüm entegrasyonlarınızı yeni token ile güncellemeniz gerekecektir.
        </div>
    @endif

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
        <button type="submit" class="btn btn-primary btn-sm">
             {{ $apiToken ? 'Güncelle' : 'Oluştur' }}
        </button>
    </div>
</form>

<script type="text/javascript">
$(document).ready(function(){
    var firma_id = {{ $firma_id }};

    $('#apiTokenForm').on('submit', function(e){
        e.preventDefault();
        
        $.ajax({
            url: "/" + firma_id + "/api-token/kaydet", 
            type: "POST",
            data: $(this).serialize(),
            success: function(response) {
                if(response.success) {
                    $('#apiTokenModal').modal('hide');
                    
                    // Token'ı göster
                    alert(response.message + '\n\nYeni Token:\n' + response.token + '\n\nBu token\'ı güvenli bir yerde saklayın!');
                    
                    location.reload();
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON.errors;
                var errorMsg = 'Hata:\n';
                $.each(errors, function(key, value) {
                    errorMsg += value[0] + '\n';
                });
                alert(errorMsg);
            }
        });
    });
});
</script>