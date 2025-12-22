@if($kategoriler->isEmpty() || $rafListesi->isEmpty())
<!-- Ürün Grubu veya Raf Yoksa Uyarı -->
<div class="alert alert-warning d-flex align-items-center justify-content-between flex-wrap" role="alert" style="margin-bottom: 15px; padding: 10px 15px; gap: 10px;">
  <div class="d-flex align-items-center" style="flex: 1; min-width: 200px;">
    <i class="fas fa-exclamation-triangle" style="font-size: 20px; margin-right: 10px;"></i>
    <span style="margin: 0; font-size: 14px;">
      @if($kategoriler->isEmpty() && $rafListesi->isEmpty())
        Stok eklemek için önce <strong>Ürün Grubu</strong> ve <strong>Raf</strong> eklemeniz gerekiyor.
      @elseif($kategoriler->isEmpty())
        Stok eklemek için önce <strong>Ürün Grubu</strong> eklemeniz gerekiyor.
      @else
        Stok eklemek için önce <strong>Raf</strong> eklemeniz gerekiyor.
      @endif
    </span>
  </div>
  <div class="d-flex gap-2">
    @if($kategoriler->isEmpty())
      <button class="btn btn-success btn-sm" type="button" id="addNewCategoryBtn" style="white-space: nowrap;">
        <i class="fas fa-plus"></i> Ürün Grubu Ekle
      </button>
    @endif
    @if($rafListesi->isEmpty())
      <button class="btn btn-success btn-sm" type="button" id="addNewShelfBtn" style="white-space: nowrap;">
        <i class="fas fa-plus"></i> Raf Ekle
      </button>
    @endif
  </div>
</div>
@endif
<form method="post" id="addStock" action="{{ route('store.stock', $firma->id) }}" enctype="multipart/form-data">
  @csrf
  <input type="hidden" name="form_token" id="formToken" value="">
  <div class="row mb-1 align-items-center">
    <label class="col-sm-4 custom-p-r">Markalar<span style="color:red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <div class="input-group">
        <select name="marka_id" class="form-select" required>
          <option value="" selected disabled>- Seçiniz -</option>
          @foreach($markalar as $marka)
            <option value="{{ $marka->id }}">{{ $marka->marka }}</option>
          @endforeach
        </select>
        <button class="btn btn-success" type="button"  id="addNewBrandBtn">+</button>
      </div>
    </div>
  </div>

  <div class="row mb-1">
    <label class="col-sm-4 custom-p-r">Cihaz Türü<span style="color:red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <div class="input-group">
        <select name="cihaz_id" class="form-select" required>
          <option value="" selected disabled>- Seçiniz -</option>
          @foreach($cihazlar as $cihaz)
            <option value="{{ $cihaz->id }}">{{ $cihaz->cihaz }}</option>
          @endforeach
        </select>
        <button class="btn btn-success" type="button"  id="addNewDeviceTypeBtn">+</button>
      </div>
    </div>
  </div>

  <div class="row mb-1">
    <label class="col-sm-4 custom-p-r">Ürün Grubu<span style="color:red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <div class="input-group">
        <select name="urunKategori" class="form-select" required>
          <option value="" selected disabled>- Seçiniz -</option>
           @foreach($kategoriler as $kategori)
             <option value="{{ $kategori->id }}">{{ $kategori->kategori }}</option>
           @endforeach
        </select>
        <button class="btn btn-success" type="button"  id="addNewCategoryBtn">+</button>
      </div>
    </div>
  </div>
  
<div class="row mb-1">
    <label class="col-sm-4 custom-p-r">Raf Seç<span style="color:red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <div class="input-group">
        <select name="raf_id" class="form-select" required>
          <option value="" selected disabled>- Seçiniz -</option>
           @foreach($rafListesi as $raf)
            <option value="{{ $raf->id }}">{{ $raf->raf_adi }}</option>
           @endforeach
        </select>
        <button class="btn btn-success" type="button"  id="addNewShelfBtn">+</button>
      </div>
    </div>
</div>


<div class="row mb-0">
    <label class="col-sm-4 custom-p-r">Ürün Kodu<span class="text-danger">*</span></label>
    <div class="col-sm-8 custom-p-l">
        <input name="urunKodu" type="text" class="form-control" 
               value="{{ old('urunKodu') }}" 
               placeholder="0000000000000" 
               data-mask="000-0000000000" required>
        <small class="text-danger">Ürün kodu 13 haneli olmalıdır.</small>
        @error('urunKodu') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row mb-0">
    <label class="col-sm-4 custom-p-r">Ürün Adı<span class="text-danger">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <input name="urunAdi" type="text" class="form-control" value="{{ old('urunAdi') }}" required>
      <div id="urunAdiUyari"></div>
    </div>
</div>

<div class="row mb-0">
    <label class="col-sm-4 custom-p-r">Satış Fiyatı (₺)<span class="text-danger">*</span></label>
    <div class="col-sm-8 custom-p-l">
        <input name="fiyat" type="number" min="0" step="0.01" class="form-control" required>
    </div>
</div>

<div class="row mb-0">
    <label class="col-sm-4 custom-p-r">Açıklama</label>
    <div class="col-sm-8 custom-p-l">
      <textarea name="aciklama" rows="3" class="form-control"></textarea>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-12 d-flex justify-content-end">
      <button type="submit" class="btn btn-sm btn-info">Kaydet</button>
    </div>
  </div>
</form>

<!-- Yeni Marka Ekle Modal -->
<div class="modal fade" id="addBrandModal" tabindex="-1" aria-hidden="true" style="background: rgba(0, 0, 0, 0.7); padding-top:100px;">
    <div class="modal-dialog modal-sm ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Marka Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addBrandForm" action="{{ route('store.brand.ajax', $firma->id) }}">
                    @csrf
                    <input type="hidden" name="brand_form_token" id="brandFormToken" value="">
                    <div class="row mb-3"><label class="col-sm-4">Marka:<span class="text-danger">*</span></label>
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
            <div class="modal-header">
                <h5 class="modal-title">Cihaz Türü Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addDeviceTypeForm" action="{{ route('store.device.type.ajax', $firma->id) }}">
                    @csrf
                    <input type="hidden" name="device_form_token" id="deviceFormToken" value="">
                    <div class="row mb-3"><label class="col-sm-4">Cihaz:<span class="text-danger">*</span></label><div class="col-sm-8"><input name="cihaz" class="form-control" type="text" required></div></div>
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

<!-- Yeni Kategori Ekle Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true" style="background: rgba(0, 0, 0, 0.7); padding-top:100px;">
    <div class="modal-dialog modal-sm ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ürün Grubu Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addCategoryForm" action="{{ route('store.category.ajax', $firma->id) }}">
                    @csrf
                    <input type="hidden" name="category_form_token" id="categoryFormToken" value="">
                    <div class="row mb-3"><label class="col-sm-4">Ürün Grubu:<span class="text-danger">*</span></label><div class="col-sm-8"><input name="kategori" class="form-control" type="text" required></div></div>
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

<!-- Yeni Raf Ekle Modal -->
<div class="modal fade" id="addShelfModal" tabindex="-1" aria-hidden="true" style="background: rgba(0, 0, 0, 0.7); padding-top:100px;">
    <div class="modal-dialog modal-sm ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Raf Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addShelfForm" action="{{ route('store.shelf.ajax', $firma->id) }}">
                    @csrf
                    <input type="hidden" name="shelf_form_token" id="shelfFormToken" value="">
                    <div class="row mb-3"><label class="col-sm-4">Raf:<span class="text-danger">*</span></label><div class="col-sm-8"><input name="raf_adi" class="form-control" type="text" required></div></div>
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

<!-- Ürün Düzenle Modal -->
<div class="modal fade" id="editStockModal" tabindex="-1" aria-labelledby="editStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
            </div>
        </div>
    </div>
</div>


<script>
$(document).ready(function () {
    var tenantId = "{{ $firma->id }}";
    var mainFormId = '#addStock'; // Ana formun ID'si
    var mainModalSelector = ''; // Ana form bir modal içinde değil, doğrudan sayfada
    var hasCategories = {{ $kategoriler->isEmpty() ? 'false' : 'true' }};
    var hasShelves = {{ $rafListesi->isEmpty() ? 'false' : 'true' }};
    // Select2'yi ortak bir fonksiyonla başlatmak için bir yardımcı fonksiyon
    function initializeSelect2(selector, placeholder, url) {
        var parentModal = $(selector).closest('.modal');
        if (parentModal.length === 0) {
            parentModal = $('.modal:visible').last();
        }
        $(selector).select2({
            theme: "bootstrap-5",
            placeholder: placeholder,
            allowClear: true,
            // Eğer selector bir modal içindeyse o modalı, değilse body'yi hedefle.
            dropdownParent: parentModal.length ? parentModal : $('body'),
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });
    }

    // Select2 başlatmaları
    initializeSelect2(mainFormId + ' select[name="marka_id"]', 'Marka ara...', '/' + tenantId + '/search-brands');
    initializeSelect2(mainFormId + ' select[name="cihaz_id"]', 'Cihaz türü ara...', '/' + tenantId + '/search-devices');
    initializeSelect2(mainFormId + ' select[name="urunKategori"]', 'Ürün grubu ara...', '/' + tenantId + '/search-categories');
    initializeSelect2(mainFormId + ' select[name="raf_id"]', 'Raf ara...', '/' + tenantId + '/search-shelves');

    // Otomatik hesaplama fonksiyonu
    function hesaplaFiyat() {
        var adet = parseFloat($('input[name="adet"]').val()) || 0;
        var fiyat = parseFloat($('input[name="fiyat"]').val()) || 0;

        if (adet > 0 && fiyat > 0) {
            $('input[name="fiyatBirim"]').val((fiyat / adet).toFixed(2));
        } else {
            $('input[name="fiyatBirim"]').val('');
        }
    }
    $('input[name="adet"], input[name="fiyat"], input[name="fiyatBirim"]').on('input', hesaplaFiyat);

    // Ürün kodu mask
    $(mainFormId + ' input[name="urunKodu"]').mask('0000000000000', {
        placeholder: '_____________',
        translation: {
            '0': {pattern: /[0-9]/}
        }
    }).on('input', function() {
        let cleanValue = $(this).cleanVal();
        
        // Visual feedback
        if (cleanValue.length === 13) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });
    
    $(mainFormId).submit(function(event) {
        // cleanVal() kullanarak sadece rakamları al
        var urunKoduInput = $(this).find('input[name="urunKodu"]');
        var urunKodu = urunKoduInput.cleanVal();

        if (urunKodu.length !== 13) {
            event.preventDefault();
            alert('Ürün kodu tam 13 haneli olmalıdır!');
            urunKoduInput.focus();
            return false;
        }

        // Form gönderilmeden önce temiz değeri input'a ata
        urunKoduInput.val(urunKodu);
        var isValid = true;
        $(this).find('input[required], select[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                return false;
            }
        });

        if (!isValid) {
            event.preventDefault();
            alert('Lütfen zorunlu alanları doldurun.');
        }
    });

    var checkTimeout;

    $(mainFormId + ' input[name="urunAdi"]').on('input', function () {
        clearTimeout(checkTimeout);
        var urunAdi = $(this).val().trim();
        $('#urunAdiUyari').html(''); // Uyarıyı temizle

        if (urunAdi.length < 3) return; // 3 karakterden kısa ise kontrol yapma

        checkTimeout = setTimeout(function () {
            $.ajax({
                url: "/" + tenantId + "/stok/urun-adi-kontrol",
                method: "POST",
                data: {
                    urunAdi: urunAdi,
                    _token: "{{ csrf_token() }}"
                },
                success: function (res) {
                    if (res.exists) {
                        var warningHtml = '<div class="alert alert-warning mt-2">' +
                        'Bu ürün adı zaten mevcut. ' +
                        '<button type="button" id="openEditModalBtn" data-url="' + res.edit_url + '" class="btn btn-sm btn-primary ms-2">Ürünü Düzenle</button>' +
                        '</div>';
                        $('#urunAdiUyari').html(warningHtml);
                    } else {
                        $('#urunAdiUyari').html('');
                    }
                }
            });
        }, 600);
    });

    // Ortak Modal Açma/Kapama İşlevselliği
    function setupSubModal(buttonSelector, modalId, formId, selectName, successMessage) {
        $(document).on('click', buttonSelector, function () {
            var subModal = new bootstrap.Modal(document.getElementById(modalId.substring(1))); // # işaretini kaldır
            subModal.show();
        });

        $(modalId).on('hidden.bs.modal', function (e) {
            // Eğer ana modal açıksa, body'ye modal-open sınıfını geri ekle
            if ($(mainModalSelector).is(':visible')) {
                $('body').addClass('modal-open');
            }
        });

        $(formId).submit(function(e) {
            e.preventDefault();
            var form = $(this);
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    var newOption = new Option(response.text, response.id, true, true);
                    $(mainFormId + ' select[name="' + selectName + '"]').append(newOption).trigger('change');
                    $(modalId).modal('hide');
                    form[0].reset();
                    alert(successMessage);
                },
                error: function(xhr) {
                    alert('Bir hata oluştu. Lütfen tekrar deneyin.');
                    console.log(xhr.responseText);
                }
            });
        });
    }

    // Sub modalları kurulum
    setupSubModal('#addNewBrandBtn', '#addBrandModal', '#addBrandForm', 'marka_id', 'Marka başarıyla eklendi.');
    setupSubModal('#addNewDeviceTypeBtn', '#addDeviceTypeModal', '#addDeviceTypeForm', 'cihaz_id', 'Cihaz Türü başarıyla eklendi.');
    setupSubModal('#addNewCategoryBtn', '#addCategoryModal', '#addCategoryForm', 'urunKategori', 'Cihaz Kategori başarıyla eklendi.');
    setupSubModal('#addNewShelfBtn', '#addShelfModal', '#addShelfForm', 'raf_id', 'Raf başarıyla eklendi.');

    // Ürün Düzenle Modalını açma
    $(document).on('click', '#openEditModalBtn', function() {
        var url = $(this).data('url');

        $.ajax({
            url: url,
            type: 'GET',
            success: function(res) {
                if(res.html) {
                    $('#editStockModal .modal-body').html(res.html);
                    $('#editStockModal').modal('show');
                } else {
                    alert('Düzenleme formu yüklenemedi.');
                }
            },
            error: function() {
                alert('Düzenleme formu yüklenirken hata oluştu.');
            }
        });
    });

    // Düzenleme modalı açıldığında ana formu gizle/göster
    $('#editStockModal').on('show.bs.modal', function () {
        // Ana form modal içinde değil, bu kısmı kaldırıyoruz
    }).on('hidden.bs.modal', function () {
        // Ana form modal içinde değil, bu kısmı kaldırıyoruz
        // Select2'leri yeniden başlat
        setTimeout(function() {
            initializeSelect2(mainFormId + ' select[name="marka_id"]', 'Marka ara...', '/' + tenantId + '/search-brands');
            initializeSelect2(mainFormId + ' select[name="cihaz_id"]', 'Cihaz türü ara...', '/' + tenantId + '/search-devices');
            initializeSelect2(mainFormId + ' select[name="urunKategori"]', 'Kategori ara...', '/' + tenantId + '/search-categories');
            initializeSelect2(mainFormId + ' select[name="raf_id"]', 'Raf ara...', '/' + tenantId + '/search-shelves');
        }, 100);
    });

 // ========== TOKEN VE ÇİFT SUBMİT KORUMASI ==========
    let formSubmitting = false;
    let brandFormSubmitting = false;
    let deviceFormSubmitting = false;
    let categoryFormSubmitting = false;
    let shelfFormSubmitting = false;
    
    // Benzersiz token oluştur
    function generateToken() {
        return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    // Sayfa yüklendiğinde tokenları oluştur
    $('#formToken').val(generateToken());
    $('#brandFormToken').val(generateToken());
    $('#deviceFormToken').val(generateToken());
    $('#categoryFormToken').val(generateToken());
    $('#shelfFormToken').val(generateToken());
    
    // Ana form submit koruması
    $('#addStock').submit(function(event) {
        if (formSubmitting) {
            event.preventDefault();
            return false;
        }
        
        // cleanVal() kullanarak sadece rakamları al
        var urunKoduInput = $(this).find('input[name="urunKodu"]');
        var urunKodu = urunKoduInput.cleanVal();

        if (urunKodu.length !== 13) {
            event.preventDefault();
            alert('Ürün kodu tam 13 haneli olmalıdır!');
            urunKoduInput.focus();
            return false;
        }

        // Form gönderilmeden önce temiz değeri input'a ata
        urunKoduInput.val(urunKodu);
        
        var isValid = true;
        $(this).find('input[required], select[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                return false;
            }
        });

        if (!isValid) {
            event.preventDefault();
            alert('Lütfen zorunlu alanları doldurun.');
            return false;
        }
        
        // Butonu disable et
        formSubmitting = true;
        $(this).find('button[type="submit"]').prop('disabled', true);
        
        // 5 saniye sonra yeniden aktif et
        setTimeout(function() {
            $('#formToken').val(generateToken());
            formSubmitting = false;
            $('#addStock button[type="submit"]').prop('disabled', false);
        }, 5000);
        
        return true;
    });
    
    // Marka ekleme form koruması
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
                
                // Token'ı yenile
                $('#brandFormToken').val(generateToken());
                brandFormSubmitting = false;
                submitBtn.prop('disabled', false);
            },
            error: function(xhr) {
                var errorMessage = 'Bir hata oluştu. Lütfen tekrar deneyin.';
                
                // Token hatası ise sessizce geç
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    if (xhr.responseJSON.error.includes('token') || xhr.responseJSON.error.includes('gönderildi')) {
                        // Token hatası - kullanıcıya gösterme
                        console.log('Token hatası:', xhr.responseJSON.error);
                    } else {
                        // Diğer hatalar için alert göster
                        alert(errorMessage);
                    }
                } else {
                    alert(errorMessage);
                }
                
                console.log(xhr.responseText);
                
                $('#brandFormToken').val(generateToken());
                brandFormSubmitting = false;
                submitBtn.prop('disabled', false);
            }
        });
    });
    
    // Cihaz türü ekleme form koruması
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
                
                console.log(xhr.responseText);
                
                $('#deviceFormToken').val(generateToken());
                deviceFormSubmitting = false;
                submitBtn.prop('disabled', false);
            }
        });
    });
    
    // Kategori ekleme form koruması
    $('#addCategoryForm').submit(function(e) {
        e.preventDefault();
        
        if (categoryFormSubmitting) {
            return false;
        }
        
        categoryFormSubmitting = true;
        var submitBtn = $(this).find('input[type="submit"]');
        submitBtn.prop('disabled', true);
        
        var form = $(this);
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                $('#addCategoryModal').modal('hide');
                form[0].reset();
                alert('Ürün Grubu başarıyla eklendi.');
                
                // Eğer ilk kategori eklendiyse sayfayı yenile
                if (!hasCategories) {
                    location.reload();
                } else {
                    var newOption = new Option(response.text, response.id, true, true);
                    $(mainFormId + ' select[name="urunKategori"]').append(newOption).trigger('change');
                }
                
                $('#categoryFormToken').val(generateToken());
                categoryFormSubmitting = false;
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
                
                console.log(xhr.responseText);
                
                $('#categoryFormToken').val(generateToken());
                categoryFormSubmitting = false;
                submitBtn.prop('disabled', false);
            }
        });
    });
    
    // Raf ekleme form koruması
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
                $('#addShelfModal').modal('hide');
                form[0].reset();
                alert('Raf başarıyla eklendi.');
                
                // Eğer ilk raf eklendiyse sayfayı yenile
                if (!hasShelves) {
                    location.reload();
                } else {
                    var newOption = new Option(response.text, response.id, true, true);
                    $(mainFormId + ' select[name="raf_id"]').append(newOption).trigger('change');
                }
                
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
                
                console.log(xhr.responseText);
                
                $('#shelfFormToken').val(generateToken());
                shelfFormSubmitting = false;
                submitBtn.prop('disabled', false);
            }
        });
    });
}); // $(document).ready sonu
</script>

<script>
$(document).ready(function() {
    let isSubmitting = false;
    let shouldReload = false;
    
    // Form submit edildiğinde flag'i ayarla
    $('#addStock').submit(function() {
        isSubmitting = true;
    });
    
    // Modal kapatılmaya çalışıldığında
    $('#addStockModal').on('hide.bs.modal', function(e) {
        if (isSubmitting) {
            isSubmitting = false;
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
    
    // Modal tamamen kapandığında sayfayı yenile
    $('#addStockModal').on('hidden.bs.modal', function() {
        isSubmitting = false;
        if (shouldReload) {
            shouldReload = false;
            location.reload(); // Sayfayı yenile
        }
    });
});
</script>








