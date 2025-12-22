<form method="post" id="editIntegration" action="{{ route('super.admin.integration.update', $integration->id)}}" enctype="multipart/form-data">
    @csrf   
    
    <div class="row">
        <label class="col-sm-3 custom-p-r">Entegrasyon Adı<span style="font-weight: bold; color: red;">*</span></label>
        <div class="col-sm-9 custom-p-l">
            <input name="name" class="form-control buyukYaz" type="text" placeholder="Entegrasyon adını giriniz" value="{{ $integration->name }}" required>
        </div>
    </div>
    
    <div class="row">
        <label class="col-sm-3 custom-p-r">Kategori<span style="font-weight: bold; color: red;">*</span></label>
        <div class="col-sm-9 custom-p-l">
            <select name="category" class="form-select" required>
                <option value="" disabled>-Seçiniz-</option>
                <option value="invoice" {{ $integration->category == 'invoice' ? 'selected' : '' }}>Fatura</option>
                <option value="sms" {{ $integration->category == 'sms' ? 'selected' : '' }}>SMS</option>
                <option value="accounting" {{ $integration->category == 'accounting' ? 'selected' : '' }}>Muhasebe</option>
                <option value="santral" {{ $integration->category == 'santral' ? 'selected' : '' }}>Santral</option>
                <option value="other" {{ $integration->category == 'other' ? 'selected' : '' }}>Diğer</option>
            </select>
        </div>
    </div>
    
    <div class="row">
        <label class="col-sm-3 custom-p-r">Fiyat (₺)</label>
        <div class="col-sm-9 custom-p-l">
            <input name="price" class="form-control" type="number" step="0.01" min="0" placeholder="0.00" value="{{ $integration->price }}">
            <small class="text-muted">Ücretsiz ise boş bırakabilirsiniz</small>
        </div>
    </div>
    
    <div class="row">
        <label class="col-sm-3 custom-p-r">Logo</label>
        <div class="col-sm-9 custom-p-l">
            <input name="logo" class="form-control" type="file" accept="image/*" id="logoInput">
            @if($integration->logo)
                <div class="mt-3" id="logoPreviewContainer">
                    <p class="text-muted mb-2">Mevcut Logo:</p>
                    <img src="{{ asset($integration->logo) }}" alt="Mevcut Logo" style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; padding: 10px; border-radius: 8px; background: #f8f9fa;" id="logoPreview">
                    <p class="text-muted mt-2"><small>Yeni logo seçerseniz güncellenecektir</small></p>
                </div>
            @else
                <div class="mt-3" id="logoPreviewContainer" style="display: none;">
                    <p class="text-muted mb-2">Önizleme:</p>
                    <img src="" alt="Logo Önizleme" style="max-width: 100px; max-height: 100px; border: 1px solid #ddd; padding: 10px; border-radius: 8px; background: #f8f9fa;" id="logoPreview">
                </div>
            @endif
        </div>
    </div>
    
    <div class="row">
        <label class="col-sm-3 custom-p-r">Kısa Açıklama</label>
        <div class="col-sm-9 custom-p-l">
            <textarea name="description" type="text" class="form-control" rows="3" placeholder="Entegrasyon hakkında kısa açıklama yazınız...">{{ $integration->description }}</textarea>
        </div>
    </div>
    
    <div class="row">
        <label class="col-sm-3 custom-p-r">Detaylı Açıklama</label>
        <div class="col-sm-9 custom-p-l">
            <textarea id="elm1" name="explanation" type="text" class="form-control" aria-hidden="true">{{ $integration->explanation }}</textarea>
        </div>
    </div>
    
    <div class="row">
        <label class="col-sm-3 custom-p-r">API Form Alanları</label>
        <div class="col-sm-9 custom-p-l">
            <textarea name="api_fields" id="api_fields" class="form-control json-editor" rows="12" placeholder='[{"name":"username","label":"Kullanıcı Adı","type":"text","required":true}]'>{!! $integration->api_fields !!}</textarea>
            <small class="text-muted">
                Firmalar için gerekli API alanlarını JSON formatında tanımlayın. 
                <a href="javascript:void(0);" class="examples-link" onclick="openApiFieldsModal()">Örnekleri göster</a>
            </small>
            <div id="jsonValidation" class="mt-2" style="display: none;"></div>
        </div>
    </div>
    
    <div class="row">
        <label class="col-sm-3 custom-p-r">Durum</label>
        <div class="col-sm-9 custom-p-l">
            <div class="form-check form-switch">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ $integration->is_active ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Entegrasyon Aktif</label>
            </div>
        </div>
    </div>
    
    <div class="row">               
        <div class="col-sm-12 gonderBtn">
            <input type="submit" class="btn btn-sm btn-info waves-effect waves-light" value="Güncelle">
            <a href="{{route('super.admin.integrations')}}" class="btn btn-sm btn-light waves-effect">İptal</a>
        </div>
    </div>
</form>

<script>
$('.buyukYaz').keyup(function(){
    this.value = this.value.toUpperCase();
});

// Logo önizleme
$('#logoInput').change(function(){
    var file = this.files[0];
    if (file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#logoPreview').attr('src', e.target.result).show();
            $('#logoPreviewContainer').show();
        }
        reader.readAsDataURL(file);
    }
});

// Modal açma fonksiyonu
function openApiFieldsModal() {
    var myModal = new bootstrap.Modal(document.getElementById('apiFieldsModal'));
    myModal.show();
}

// JSON Validation
$('#api_fields').on('input blur', function() {
    validateJSON(this);
});

function validateJSON(element) {
    const $element = $(element);
    const value = $element.val().trim();
    const $validation = $('#jsonValidation');
    
    if (!value) {
        $element.removeClass('json-valid json-invalid');
        $validation.hide();
        return true;
    }
    
    try {
        JSON.parse(value);
        $element.removeClass('json-invalid').addClass('json-valid');
        $validation.html('<span class="text-success"><i class="fas fa-check-circle"></i> Geçerli JSON formatı</span>').show();
        return true;
    } catch (e) {
        $element.removeClass('json-valid').addClass('json-invalid');
        $validation.html('<span class="text-danger"><i class="fas fa-times-circle"></i> Geçersiz JSON formatı: ' + e.message + '</span>').show();
        return false;
    }
}

function copyExample(codeId) {
    const code = document.getElementById(codeId).textContent;
    $('#api_fields').val(code);
    validateJSON($('#api_fields')[0]);
    
    // Modal'ı kapat
    var myModalEl = document.getElementById('apiFieldsModal');
    var modal = bootstrap.Modal.getInstance(myModalEl);
    modal.hide();
    
    if (typeof toastr !== 'undefined') {
        toastr.success('Örnek JSON kopyalandı!');
    } else {
        alert('Örnek JSON kopyalandı!');
    }
}

$(document).ready(function () {
    // Sayfa yüklendiğinde mevcut JSON'u validate et
    if ($('#api_fields').val().trim()) {
        validateJSON($('#api_fields')[0]);
    }
    
    $('#editIntegration').submit(function (event) {
        if (tinymce.get('elm1')) {
            tinymce.get('elm1').save();
        }
        
        // JSON validation kontrolü
        const apiFieldsValue = $('#api_fields').val().trim();
        if (apiFieldsValue && !validateJSON($('#api_fields')[0])) {
            event.preventDefault();
            alert('API Fields alanında geçersiz JSON formatı var. Lütfen düzeltin.');
            return false;
        }
        
        var formIsValid = true;
        $(this).find('input[required], select[required]').each(function () {
            if (!$(this).val()) {
                formIsValid = false;
                return false;
            }
        });
    
        if (!formIsValid) {
            event.preventDefault();
            alert('Lütfen zorunlu alanları doldurun.');
            return false;
        }
    });
});
</script>