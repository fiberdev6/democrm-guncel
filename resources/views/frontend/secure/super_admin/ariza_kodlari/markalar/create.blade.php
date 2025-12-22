
<form id="markaEkle" method="POST" enctype="multipart/form-data">
    @csrf
        <input type="hidden" name="form_token" id="markaEkleFormToken" value="">
    <div class="form-group">
        <input type="text" name="marka" class="form-control marka" placeholder="Marka Adı" required>
    </div>
    <div class="form-group">
        <input type="file" name="resim" class="form-control-file">
        <small class="text-muted">Sadece jpg, png dosya türlerini yükleyebilirsiniz.</small>
    </div>
    <div class="text-center">
        <button type="submit" class="btn btn-primary btn-sm">Gönder</button>
    </div>
</form>

