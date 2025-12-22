<form method="POST" id="stokFotoEkle" enctype="multipart/form-data">
    @csrf
    <div class="mb-2">
        <input type="file" class="form-control" name="resim" id="customFile" accept="image/jpeg,image/png">
        <input type="hidden" name="stock_id" value="{{ $stock_id }}">
        <small class="text-danger">Dosya boyutu 5mb'dan büyük olamaz. Sadece jpg ve png uzantılı dosyalar yükleyebilirsiniz.</small>
    </div>
    <div class="mb-3" id="resimOnizlemeDiv" style="display: none;">
        <img 
            id="resimGoster" 
            alt="Resim Önizleme"
            style="max-width: 200px; border: 1px solid #ccc; padding: 5px; width: auto; height: auto;"
        >
    </div>

    @if(isset($firstPhoto) && $firstPhoto->resimyol)
    <div class="mb-3">
        <img 
            src="{{ $firstPhoto->resimyol }}" 
            alt="Mevcut Resim"
            style="max-width: 200px; border: 1px solid #ccc; padding: 5px;"
            class="img-fluid"
        >
    </div>
    @endif

    <div class="imgLoad" style="display: none;">
        <div class="progress my-1" style="height: 10px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
        </div>
    </div>
</form>

<div class="row imgBox">
    @foreach($photos as $foto)
        <div class="col-6 col-sm-3 stn mb-2" data-id="{{ $foto->id }}">
            <img src="{{ Storage::url($foto->resimyol) }}" class="img-fluid border" style="width: 100%;">
            <button class="btn btn-danger btn-sm w-100 stokFotoSil mt-1" data-id="{{ $foto->id }}"><i class="fas fa-trash-alt"></i></button>

        </div>
    @endforeach
</div>

<script>
$(document).ready(function () {

    $('#customFile').on("change", function () {
        let file = this.files[0];
        if (!file) {
            // Dosya seçilmezse önizlemeyi gizle
            $('#resimOnizlemeDiv').hide();
            $('#resimGoster').removeAttr('src');
            return;
        }

        if (file.size > 5242880) {
            alert("Dosya 5MB'dan büyük olamaz.");
            $(this).val('');
            $('#resimGoster').hide();
            return;
        }

        if (!["image/jpeg", "image/png", "image/jpg"].includes(file.type)) {
            alert("Sadece JPG ve PNG yüklenebilir.");
            $(this).val('');
            $('#resimGoster').hide();
            return;
        }

        // Önizleme göster
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#resimGoster').attr('src', e.target.result);
            $('#resimOnizlemeDiv').show();
        }
        reader.readAsDataURL(file);

        let formData = new FormData($('#stokFotoEkle')[0]);

        $.ajax({
            url: "/{{ $tenant_id }}/stok-foto-ekle",
            method: "POST",
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            beforeSend: function () {
                $(".imgLoad").show();
            },
            success: function (res) {
                $(".imgLoad").hide();
                $('#customFile').val('');
                // Başarılı yükleme sonrası önizlemeyi gizle
                $('#resimOnizlemeDiv').hide();
                $('#resimGoster').removeAttr('src');

                $('.imgBox').prepend(`
                    <div class="col-6 col-sm-3 stn mb-2" data-id="${res.id}">
                        <img src="${res.resim_yolu}" class="img-fluid border" style="width: 100%;">
                        <button class="btn btn-danger btn-sm w-100 stokFotoSil mt-1" data-id="${res.id}">Sil</button>

                    </div>
                `);
            },
            error: function (xhr) {
                $(".imgLoad").hide();
                let err = "Yükleme başarısız.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    err = xhr.responseJSON.message;
                }
                alert(err);
            }
        });
    });

    $(document).on('click', '.stokFotoSil', function (e) {
        e.preventDefault();

        if (!confirm("Fotoğraf silinsin mi?")) return;

        let id = $(this).data('id');
        let fotoDiv = $('.stn[data-id="' + id + '"]');

        $.ajax({
            url: "/{{ $tenant_id }}/stok-foto-sil",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: id
            },
            success: function (res) {
                alert(res.message);
                fotoDiv.fadeOut(300, function () { $(this).remove(); });
            },
            error: function (xhr) {
                let errMsg = "Silme işlemi başarısız.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                }
                alert(errMsg);
            }
        });
    });

});
</script>