
<form id="modelDuzenle" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="form-group">
        <input type="text" name="model" class="form-control model" 
               placeholder="Model Adı" value="{{ $modelSec->model }}" required>
    </div>
    <div class="form-group">
        <input type="file" name="resim" class="form-control-file">
        <small class="text-muted">Sadece jpg, png dosya türlerini yükleyebilirsiniz.</small>
        @if($modelSec->resimyol)
        <div class="mt-2">
            <img src="{{ asset('upload/'.$modelSec->resimyol) }}" width="100">
        </div>
        @endif
    </div>
    <div class="text-center">
        <button type="submit" class="btn btn-primary btn-sm">Güncelle</button>
    </div>
</form>

<script>
$(document).ready(function() {
    $("#modelDuzenle").on('submit', function(e) {
        e.preventDefault();
        var model = $.trim($(".model").val());
        
        if (model.length === 0) {
            alert("Model adı boş geçilemez");
            $(".model").focus();
            return false;
        }
        
        $.ajax({
            url: "{{ route('super.admin.modeller.update', $modelSec->id) }}",
            type: "POST",
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData: false,
            success: function(data) {
                alert(data.message);
                window.location.href = "{{ route('super.admin.modeller.index', $modelSec->mid) }}";
            },
            error: function(xhr) {
                alert("Hata: " + xhr.responseJSON.message);
            }
        });
    });
});
</script>