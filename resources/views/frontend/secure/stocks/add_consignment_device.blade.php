<form method="POST" id="addConsignmentDevice" action="{{ route('store.consignment.device', $tenant_id) }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="form_token" id="formToken" value="">
    <div class="row mb-1 align-items-center">
        <label class="col-sm-3 custom-p-r">Markalar<span class="text-danger">*</span></label>
        <div class="col-sm-9 custom-p-l">
            <div class="input-group input-group-sm">
                <select name="marka_id" class="form-select form-select-sm" required>
                    <option value="" selected disabled>- Marka arayın veya seçin -</option>
                    @if(old('marka_id'))
                        @php
                            $oldMarka = \App\Models\Marka::find(old('marka_id'));
                        @endphp
                        @if($oldMarka)
                            <option value="{{ $oldMarka->id }}" selected>{{ $oldMarka->marka }}</option>
                        @endif
                    @endif
                </select>
                <button class="btn btn-success btn-sm" type="button" id="addNewBrandBtn">+</button>
            </div>
        </div>
    </div>

    <div class="row mb-1 align-items-center">
        <label class="col-sm-3 custom-p-r">Cihaz Türleri<span class="text-danger">*</span></label>
        <div class="col-sm-9 custom-p-l">
            <div class="input-group input-group-sm">
                <select name="cihaz_id" class="form-select form-select-sm" required>
                    <option value="" selected disabled>- Cihaz türü arayın veya seçin -</option>
                     @if(old('cihaz_id'))
                        @php
                            $oldCihaz = \App\Models\Cihaz::find(old('cihaz_id'));
                        @endphp
                        @if($oldCihaz)
                            <option value="{{ $oldCihaz->id }}" selected>{{ $oldCihaz->cihaz }}</option>
                        @endif
                    @endif
                </select>
                <button class="btn btn-success btn-sm" type="button" id="addNewDeviceTypeBtn">+</button>
            </div>
        </div>
    </div>

    <div class="row mb-1 align-items-center">
        <label class="col-sm-3 custom-p-r">Raf Seç<span class="text-danger">*</span></label>
        <div class="col-sm-9 custom-p-l">
            <div class="input-group input-group-sm" >
                <select name="raf_id" class="form-select form-select-sm"  required>
                    <option value="" selected disabled>- Raf arayın veya seçin -</option>
                     @if(old('raf_id'))
                        @php
                            $oldRaf = \App\Models\Raf::find(old('raf_id'));
                        @endphp
                        @if($oldRaf)
                            <option value="{{ $oldRaf->id }}" selected>{{ $oldRaf->raf_adi }}</option>
                        @endif
                    @endif
                </select>
                <button class="btn btn-success btn-sm" type="button" id="addNewShelfBtn">+</button>
            </div>
        </div>
    </div>

    <div class="row mb-1 align-items-center">
        <label class="col-sm-3 custom-p-r">Ürün Kodu <span class="text-danger">*</span></label>
        <div class="col-sm-9 custom-p-l">
            <input name="urunKodu" type="text" class="form-control"
                   value="{{ old('urunKodu') }}"
                   placeholder="0000000000000"
                   data-mask="0000000000000" required>
            <small class="text-danger">Ürün kodu 13 haneli olmalıdır.</small>
            @error('urunKodu') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="row mb-1 align-items-center">
        <label class="col-sm-3 custom-p-r">Ürün Adı <span class="text-danger">*</span></label>
        <div class="col-sm-9 custom-p-l">
            <input name="urunAdi" type="text" class="form-control" value="{{ old('urunAdi') }}" required>
            <div id="urunAdiUyari"></div>
        </div>
    </div>

    {{-- <div class="row mb-1 align-items-center">
        <label class="col-sm-3">Adet <span class="text-danger">*</span></label>
        <div class="col-sm-9">
            <input name="adet" type="number" min="1" class="form-control" value="{{ old('adet') ?? 1 }}" required>
        </div>
    </div> --}}

    <div class="row mb-0">
        <label class="col-sm-3 custom-p-r">Satış Fiyatı (₺)<span class="text-danger">*</span></label>
        <div class="col-sm-9 custom-p-l">
            <input name="fiyat" type="number" min="0" step="0.01" class="form-control" required>
        </div>
    </div>

    <div class="row mb-1 align-items-center">
        <label class="col-sm-3 custom-p-r">Açıklama</label>
        <div class="col-sm-9 custom-p-l">
            <textarea name="aciklama" rows="3" class="form-control">{{ old('aciklama') }}</textarea>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-sm btn-info">Kaydet</button>
            {{-- <a href="{{ route('consignmentdevice', $tenant_id) }}" class="btn btn-secondary">Geri</a> --}}
        </div>
    </div>
</form>

<!-- Yeni Marka Ekle Modal -->
<div class="modal fade" id="addBrandModal" tabindex="-1" aria-hidden="true" style="background: rgba(0, 0, 0, 0.7); padding-top:100px;">
    <div class="modal-dialog modal-sm ">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Marka Ekle</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="addBrandForm" action="{{ route('store.brand.ajax', $tenant_id) }}">
                    @csrf
                    <input type="hidden" name="brand_form_token" id="brandFormToken" value="">
                    <div class="row mb-3"><label class="col-sm-4">Marka:<span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-8"><input name="marka" class="form-control" type="text" required>
                    </div>
                  </div>
                    <div class="row">
                      <div class="col-sm-12 text-end">
                      <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                      <input type="submit" class="btn btn-info btn-sm" value="Kaydet">
                    </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Yeni Cihaz Türü Ekle Modal -->
<div class="modal fade" id="addDeviceTypeModal" tabindex="-1" aria-hidden="true" style="background: rgba(0, 0, 0, 0.7); padding-top:100px;">
    <div class="modal-dialog modal-sm ">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Cihaz Türü Ekle</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <form id="addDeviceTypeForm" action="{{ route('store.device.type.ajax', $tenant_id) }}">
             @csrf 
            <input type="hidden" name="device_form_token" id="deviceFormToken" value="">
            <div class="row mb-3"><label class="col-sm-4">Cihaz:<span class="text-danger">*</span></label><div class="col-sm-8">
            <input name="cihaz" class="form-control" type="text" required>
            </div>
        </div>
    <div class="row"><div class="col-sm-12 text-end">
      <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
      <input type="submit" class="btn btn-info btn-sm" value="Kaydet">
    </div>
  </div>
</form>
</div>
</div>
</div>
</div>

<!-- Yeni Raf Ekle Modal -->
<div class="modal fade" id="addShelfModal" tabindex="-1" aria-hidden="true" style="background: rgba(0, 0, 0, 0.7); padding-top:100px;">
    <div class="modal-dialog modal-sm ">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Raf Ekle</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body"><form id="addShelfForm" action="{{ route('store.shelf.ajax', $tenant_id) }}">
           @csrf 
           <input type="hidden" name="shelf_form_token" id="shelfFormToken" value="">
           <div class="row mb-3">
            <label class="col-sm-4">Raf:<span class="text-danger">*</span></label>
            <div class="col-sm-8">
              <input name="raf_adi" class="form-control" type="text" required>
            </div>
          </div>
          <div class="row">
                <div class="col-sm-12 text-end">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                    <input type="submit" class="btn btn-info btn-sm" value="Kaydet">
                </div>
              </div>
        </div>
      </form>
    </div>
  </div>
</div>
</div>

<!-- Ürün Düzenle Modal -->
<div class="modal fade" id="editConsignmentModal" tabindex="-1" aria-labelledby="editConsignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <!-- Düzenleme formu AJAX ile buraya yüklenecek -->
            </div>
        </div>
    </div>
</div>


<script>
$(document).ready(function () {
    var tenantId = "{{ $tenant_id }}";
    var mainFormId = '#addConsignmentDevice';
    
    var mainModalSelector = '#addConsignmentDeviceModal'; 

     
    // ========== TOKEN DEĞİŞKENLERİ ==========
    let formSubmitting = false;
    let brandFormSubmitting = false;
    let deviceFormSubmitting = false;
    let shelfFormSubmitting = false;
    
    // Benzersiz token oluştur
    function generateToken() {
        return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    // Sayfa yüklendiğinde tokenları oluştur
    $('#formToken').val(generateToken());
    $('#brandFormToken').val(generateToken());
    $('#deviceFormToken').val(generateToken());
    $('#shelfFormToken').val(generateToken());
    function initializeSelect2(selector, placeholder, url) {
        var parentModal = $(selector).closest('.modal');
        if (parentModal.length === 0) {
            parentModal = $('.modal:visible').last();
        }
        $(selector).select2({
            theme: "bootstrap-5",
            placeholder: placeholder,
            allowClear: true,
            dropdownParent: parentModal.length ? parentModal : $('body'),
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                data: function (params) { return { q: params.term }; },
                processResults: function (data) { return { results: data }; },
                cache: true
            }
        });
    }

    // Select2'leri sayfa başında başlatıyoruz 
    initializeSelect2(mainFormId + ' select[name="marka_id"]', 'Marka ara...', "/" + tenantId + "/search-brands");
    initializeSelect2(mainFormId + ' select[name="cihaz_id"]', 'Cihaz türü ara...', "/" + tenantId + "/search-devices");
    initializeSelect2(mainFormId + ' select[name="raf_id"]', 'Raf ara...', "/" + tenantId + "/search-shelves");



       // --- MARKA MODALI ---
    $('#addBrandForm').submit(function(e) {
        e.preventDefault();
        
        if (brandFormSubmitting) {
            return false;
        }
        
        brandFormSubmitting = true;
        var submitBtn = $(this).find('input[type="submit"]');
        submitBtn.prop('disabled', true);
        
        var form = $(this);  
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                var newOption = new Option(response.text, response.id, true, true);
                $(mainFormId + ' select[name="marka_id"]').append(newOption).trigger('change');
                $('#addBrandModal').modal('hide');
                form[0].reset();
                alert('Marka başarıyla eklendi.');
                
                $('#brandFormToken').val(generateToken());
                brandFormSubmitting = false;
                submitBtn.prop('disabled', false);
            },
            error: function(xhr) {
                var errorMessage = 'Bir hata oluştu. Lütfen tekrar deneyin.';
                
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    if (xhr.responseJSON.error.includes('token') || xhr.responseJSON.error.includes('gönderildi')) {
                        console.log('Token hatası:', xhr.responseJSON.error);
                    } else {
                        alert(errorMessage);
                    }
                } else {
                    alert(errorMessage);
                }
                
                $('#brandFormToken').val(generateToken());
                brandFormSubmitting = false;
                submitBtn.prop('disabled', false);
            }
        });
    });
    
    $('#addBrandModal').on('hidden.bs.modal', function (e) {
        if ($(mainModalSelector).is(':visible')) {
            $('body').addClass('modal-open');
        }
    });
    
    $(document).on('click', '#addNewBrandBtn', function () {
        var brandModal = new bootstrap.Modal(document.getElementById('addBrandModal'));
        brandModal.show();
    });

    // --- CİHAZ TÜRÜ MODALI ---
    $('#addDeviceTypeForm').submit(function(e) {
        e.preventDefault();
        
        if (deviceFormSubmitting) {
            return false;
        }
        
        deviceFormSubmitting = true;
        var submitBtn = $(this).find('input[type="submit"]');
        submitBtn.prop('disabled', true);
        
        var form = $(this);  
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                var newOption = new Option(response.text, response.id, true, true);
                $(mainFormId + ' select[name="cihaz_id"]').append(newOption).trigger('change');
                $('#addDeviceTypeModal').modal('hide');
                form[0].reset();
                alert('Cihaz Türü başarıyla eklendi.');
                
                $('#deviceFormToken').val(generateToken());
                deviceFormSubmitting = false;
                submitBtn.prop('disabled', false);
            },
            error: function(xhr) {
                var errorMessage = 'Bir hata oluştu. Lütfen tekrar deneyin.';
                
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    if (xhr.responseJSON.error.includes('token') || xhr.responseJSON.error.includes('gönderildi')) {
                        console.log('Token hatası:', xhr.responseJSON.error);
                    } else {
                        alert(errorMessage);
                    }
                } else {
                    alert(errorMessage);
                }
                
                $('#deviceFormToken').val(generateToken());
                deviceFormSubmitting = false;
                submitBtn.prop('disabled', false);
            }
        });
    });
    
    $('#addDeviceTypeModal').on('hidden.bs.modal', function (e) {
        if ($(mainModalSelector).is(':visible')) {
            $('body').addClass('modal-open');
        }
    });
    
    $(document).on('click', '#addNewDeviceTypeBtn', function () {
        var deviceTypeModal = new bootstrap.Modal(document.getElementById('addDeviceTypeModal'));
        deviceTypeModal.show();
    });

    // --- RAF MODALI ---
    $('#addShelfForm').submit(function(e) {
        e.preventDefault();
        
        if (shelfFormSubmitting) {
            return false;
        }
        
        shelfFormSubmitting = true;
        var submitBtn = $(this).find('input[type="submit"]');
        submitBtn.prop('disabled', true);
        
        var form = $(this);  
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                var newOption = new Option(response.text, response.id, true, true);
                $(mainFormId + ' select[name="raf_id"]').append(newOption).trigger('change');
                $('#addShelfModal').modal('hide');
                form[0].reset();
                alert('Raf başarıyla eklendi.');
                
                $('#shelfFormToken').val(generateToken());
                shelfFormSubmitting = false;
                submitBtn.prop('disabled', false);
            },
            error: function(xhr) {
                var errorMessage = 'Bir hata oluştu. Lütfen tekrar deneyin.';
                
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    if (xhr.responseJSON.error.includes('token') || xhr.responseJSON.error.includes('gönderildi')) {
                        console.log('Token hatası:', xhr.responseJSON.error);
                    } else {
                        alert(errorMessage);
                    }
                } else {
                    alert(errorMessage);
                }
                
                $('#shelfFormToken').val(generateToken());
                shelfFormSubmitting = false;
                submitBtn.prop('disabled', false);
            }
        });
    });
    
    $('#addShelfModal').on('hidden.bs.modal', function (e) {
        if ($(mainModalSelector).is(':visible')) {
            $('body').addClass('modal-open');
        }
    });
    
    $(document).on('click', '#addNewShelfBtn', function () {
        var ShelfModal = new bootstrap.Modal(document.getElementById('addShelfModal'));
        ShelfModal.show();
    });

    
    // Ürün kodu maskesi
    $(mainFormId + ' input[name="urunKodu"]').mask('0000000000000', {
        placeholder: '_____________',
        translation: { '0': { pattern: /[0-9]/ } }
    }).on('input', function() {
        let cleanValue = $(this).cleanVal();
        $(this).removeClass('is-invalid is-valid').addClass(cleanValue.length === 13 ? 'is-valid' : 'is-invalid');
    });
    
    // Ürün adı kontrolü
    var checkTimeout;
    $(mainFormId + ' input[name="urunAdi"]').on('input', function () {
        clearTimeout(checkTimeout);
        var urunAdi = $(this).val().trim();
        $('#urunAdiUyari').html('');
        if (urunAdi.length < 3) return;
        checkTimeout = setTimeout(function () {
            $.ajax({
                url: "/" + tenantId + "/stok/urun-adi-kontrol",
                method: "POST",
                data: { urunAdi: urunAdi, _token: "{{ csrf_token() }}" },
                success: function (res) {
                    if (res.exists) {
                        var warningHtml = '<div class="alert alert-warning mt-2">' +
                            'Bu ürün adı zaten mevcut. ' +
                            '<button id="openEditModalBtn" data-url="' + res.edit_url + '" class="btn btn-sm btn-primary ms-2">Ürünü Düzenle</button>' +
                            '</div>';
                        $('#urunAdiUyari').html(warningHtml);
                    }
                }
            });
        }, 600);
    });

    // Form gönderim kontrolü
    $(mainFormId).submit(function(event) {
        var urunKoduInput = $(this).find('input[name="urunKodu"]');
        var urunKodu = urunKoduInput.cleanVal();
        if (urunKodu.length !== 13) {
            event.preventDefault();
            alert('Ürün kodu tam 13 haneli olmalıdır!');
            urunKoduInput.focus();
            return false;
        }
        urunKoduInput.val(urunKodu);

        var isValid = true;
        $(this).find('input[required], select[required]').each(function () {
            if (!$(this).val()) {
                isValid = false; return false;
            }
        });
        if (!isValid) {
            event.preventDefault();
            alert('Lütfen yıldızlı zorunlu alanları doldurun.');
        }
    });

    // Ürün Düzenle Modalını açma
    $(document).on('click', '#openEditModalBtn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var url = $(this).data('url');
        $.ajax({
            url: url,
            type: 'GET',
            success: function (res) {
                if (res.html) {
                    $('#editConsignmentModal .modal-body').html(res.html);
                    $('#editConsignmentModal').modal('show');
                } else {
                    alert('Düzenleme formu yüklenemedi.');
                }
            },
            error: function () { alert('Düzenleme formu yüklenirken hata oluştu.'); }
        });
    });

    // Düzenleme modalı açıldığında ana formu gizle/göster (Ana modal ID'sine göre düzeltildi)
    $('#editConsignmentModal').on('show.bs.modal', function () {
        $(mainModalSelector).css('visibility', 'hidden');
    }).on('hidden.bs.modal', function () {
        $(mainModalSelector).css('visibility', 'visible');
        // Üst üste modal sorununu çözmek için bu da eklenmeli
        if ($(mainModalSelector).is(':visible')) {
            $('body').addClass('modal-open');
        }
    });

});
</script>
<script>
$(document).ready(function () {
    let formSubmitting = false;
    
    // Benzersiz token oluştur
    function generateToken() {
        return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    // Sayfa yüklendiğinde ilk token'ı oluştur
    $('#formToken').val(generateToken());
    
    var tenantId = "{{ $tenant_id }}";
    var mainFormId = '#addConsignmentDevice';
    var mainModalSelector = '#addConsignmentDeviceModal';
    
    // Form gönderim kontrolü - Token kontrolü ile güncellenmiş
    $(mainFormId).submit(function(event) {
        // Token kontrolü
        if (formSubmitting) {
            event.preventDefault();
            return false;
        }
        
        // Ürün kodu kontrolü
        var urunKoduInput = $(this).find('input[name="urunKodu"]');
        var urunKodu = urunKoduInput.cleanVal();
        
        if (urunKodu.length !== 13) {
            event.preventDefault();
            alert('Ürün kodu tam 13 haneli olmalıdır!');
            urunKoduInput.focus();
            return false;
        }
        
        urunKoduInput.val(urunKodu);

        // Required alan kontrolü
        var isValid = true;
        $(this).find('input[required], select[required]').each(function () {
            if (!$(this).val()) {
                isValid = false;
                return false;
            }
        });
        
        if (!isValid) {
            event.preventDefault();
            alert('Lütfen yıldızlı zorunlu alanları doldurun.');
            return false;
        }
        
        // Butonu disable et
        formSubmitting = true;
        $(this).find('button[type="submit"]').prop('disabled', true);
        
        // 3 saniye sonra yeniden aktif et
        setTimeout(function() {
            $('#formToken').val(generateToken());
            formSubmitting = false;
            $('#addConsignmentDevice button[type="submit"]').prop('disabled', false);
        }, 3000);
        
        return true;
    });
});
</script>
<script>
$(document).ready(function() {
    let isSubmitting = false;
    let shouldReload = false;
    let closingSubModal = false; // Alt modal kapatılıyor mu kontrolü
    
    // Form submit edildiğinde flag'i ayarla
    $('#addConsignmentDevice').submit(function() {
        isSubmitting = true;
    });
    
    // Alt modallar kapatılmadan önce flag'i işaretle
    $('#addBrandModal, #addDeviceTypeModal, #addShelfModal').on('hide.bs.modal', function(e) {
        closingSubModal = true;
    });
    
    // Alt modallar kapandıktan sonra flag'i sıfırla
    $('#addBrandModal, #addDeviceTypeModal, #addShelfModal').on('hidden.bs.modal', function(e) {
        closingSubModal = false;
        
        // Ana modal açıksa body'ye modal-open sınıfını geri ekle
        if ($('#addConsignmentModal').hasClass('show')) {
            $('body').addClass('modal-open');
        }
    });
    
    // ANA MODAL kapatılmaya çalışıldığında
    $('#addConsignmentModal').on('hide.bs.modal', function(e) {
        // Eğer alt modal kapatılıyorsa, ana modalı etkileme
        if (closingSubModal) {
            return;
        }
        
        // Form submit edildiyse direkt kapat
        if (isSubmitting) {
            isSubmitting = false;
            shouldReload = true;
            return true;
        }
        
        // Her zaman onay iste
        if (!confirm('Kapatmak istediğinizden emin misiniz?')) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        
        shouldReload = true; // Tamam'a basıldı, yenile
        isSubmitting = false;
    });
    
    // ANA MODAL tamamen kapandığında sayfayı yenile
    $('#addConsignmentModal').on('hidden.bs.modal', function() {
        // Eğer alt modal kapatılıyorsa sayfa yenileme
        if (closingSubModal) {
            return;
        }
        
        isSubmitting = false;
        if (shouldReload) {
            shouldReload = false;
            location.reload(); // Sayfayı yenile
        }
    });
});
</script>