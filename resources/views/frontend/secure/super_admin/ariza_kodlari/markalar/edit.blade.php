<form id="markaDuzenle" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="form-group">
        <input type="text" name="marka" class="form-control marka" 
               placeholder="Marka Adı" value="{{ $markaSec->marka }}" required>
    </div>
    <div class="form-group">
        <input type="file" name="resim" class="form-control-file">
        <small class="text-muted">Sadece jpg, png dosya türlerini yükleyebilirsiniz.</small>
        
        @if($markaSec->resimyol)
        <div class="mt-2">
            <img src="{{ asset('upload/ariza_kodlari/'.$markaSec->resimyol) }}" width="100">
        </div>
        @endif
    </div>
    <div class="text-center">
        <button type="submit" class="btn btn-primary btn-sm">Güncelle</button>
    </div>
</form>


<script>
$(document).ready(function() {
    $("#markaDuzenle").on('submit', function(e) {
        e.preventDefault();
        var marka = $.trim($(".marka").val());
        
        if (marka.length === 0) {
            alert("Marka adı boş geçilemez");
            $(".marka").focus();
            return false;
        }
        
        $.ajax({
            url: "{{ route('super.admin.markalar.update', $markaSec->id) }}",
            type: "POST",
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData: false,
            success: function(data) {
                alert(data.message);
                window.location.href = "{{ route('super.admin.markalar.index') }}";
            },
            error: function(xhr) {
                // Hata mesajını daha güvenli almak için opsiyonel kontrol
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Bir hata oluştu";
                alert("Hata: " + msg);
            }
        });
    });
});
</script>