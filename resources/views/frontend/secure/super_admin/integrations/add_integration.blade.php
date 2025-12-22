<div id="addIntegrationPage">
<form method="post" id="addIntegration" action="{{ route('super.admin.integration.store')}}" enctype="multipart/form-data">
    @csrf   
    <input type="hidden" name="form_token" id="formTokenIntegration" value="">
    <div class="row">
        <label class="col-sm-3 custom-p-r">Entegrasyon Adı<span style="font-weight: bold; color: red;">*</span></label>
        <div class="col-sm-9 custom-p-l">
            <input name="name" class="form-control buyukYaz" type="text" placeholder="Entegrasyon adını giriniz" required>
        </div>
    </div>
    
    <div class="row">
        <label class="col-sm-3 custom-p-r">Kategori<span style="font-weight: bold; color: red;">*</span></label>
        <div class="col-sm-9 custom-p-l">
            <select name="category" class="form-select" required>
                <option value="" selected disabled>-Seçiniz-</option>
                <option value="invoice">Fatura</option>
                <option value="sms">SMS</option>
                <option value="accounting">Muhasebe</option>
                <option value="santral">Santral</option>
                <option value="other">Diğer</option>
            </select>
        </div>
    </div>
    
    <div class="row">
        <label class="col-sm-3 custom-p-r">Fiyat (₺)</label>
        <div class="col-sm-9 custom-p-l">
            <input name="price" class="form-control" type="number" step="0.01" min="0" placeholder="0.00">
            <small class="text-muted">Ücretsiz ise boş bırakabilirsiniz</small>
        </div>
    </div>
    
    <div class="row">
        <label class="col-sm-3 custom-p-r">Logo</label>
        <div class="col-sm-9 custom-p-l">
            <input name="logo" class="form-control" type="file" accept="image/*">
        </div>
    </div>
    
    <div class="row">
        <label class="col-sm-3 custom-p-r">Kısa Açıklama</label>
        <div class="col-sm-9 custom-p-l">
            <textarea name="description" type="text" class="form-control" rows="3" placeholder="Entegrasyon hakkında kısa açıklama yazınız..."></textarea>
        </div>
    </div>
    
    <div class="row">
        <label class="col-sm-3 custom-p-r">Detaylı Açıklama</label>
        <div class="col-sm-9 custom-p-l">
            <textarea id="elm1" name="explanation" type="text" class="form-control" aria-hidden="true"></textarea>
        </div>
    </div>
    
    <div class="row">
        <label class="col-sm-3 custom-p-r">API Form Alanları</label>
        <div class="col-sm-9 custom-p-l">
            <textarea name="api_fields" id="api_fields" class="form-control json-editor" rows="12" placeholder='[{"name":"username","label":"Kullanıcı Adı","type":"text","required":true}]'></textarea>
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
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                <label class="form-check-label" for="is_active">Entegrasyon Aktif</label>
            </div>
        </div>
    </div>
    
    <div class="row">               
        <div class="col-sm-12 gonderBtn">
            <input type="submit" class="btn btn-sm btn-info waves-effect waves-light" value="Kaydet">
            <a href="{{route('super.admin.integrations')}}" class="btn btn-sm btn-light waves-effect">İptal</a>
        </div>
    </div>
</form>
</div>

<!-- API Fields Örnekleri Modal - FORM DIŞINDA -->

<script>
$('.buyukYaz').keyup(function(){
    this.value = this.value.toUpperCase();
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
    
    // Bildirim
    if (typeof toastr !== 'undefined') {
        toastr.success('Örnek JSON kopyalandı!');
    } else {
        alert('Örnek JSON kopyalandı!');
    }
}

$(document).ready(function() {
    let integrationFormSubmitting = false;
    
    // Benzersiz token oluştur
    function generateToken() {
        return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    // Sayfa yüklendiğinde ilk token'ı oluştur
    $('#formTokenIntegration').val(generateToken());
    
    // Form submit
    $('#addIntegration').on('submit', function(event) {
        // TinyMCE içeriğini kaydet
        if (tinymce.get('elm1')) {
            tinymce.get('elm1').save();
        }
        
        // Token kontrolü
        if (integrationFormSubmitting) {
            event.preventDefault();
            alert('Form gönderiliyor, lütfen bekleyin...');
            return false;
        }

        // JSON validation kontrolü
        const apiFieldsValue = $('#api_fields').val().trim();
        if (apiFieldsValue && !validateJSON($('#api_fields')[0])) {
            event.preventDefault();
            alert('API Fields alanında geçersiz JSON formatı var. Lütfen düzeltin.');
            return false;
        }
        
        // Validasyon kontrolü
        var formIsValid = true;
        $(this).find('input, select').each(function() {
            var isRequired = $(this).prop('required');
            var isEmpty = !$(this).val();
            if (isRequired && isEmpty) {
                formIsValid = false;
                return false;
            }
        });
    
        if (!formIsValid) {
            event.preventDefault();
            alert('Lütfen zorunlu alanları doldurun.');
            return false;
        }
        
        // Token işaretle ve butonu disable et
        integrationFormSubmitting = true;
        $(this).find('input[type="submit"]').prop('disabled', true);
        
        // Form gönderilecek (normal submit devam eder)
        return true;
    });
    });
</script>