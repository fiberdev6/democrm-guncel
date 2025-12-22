<div class="modal-header">
    <h5 class="modal-title" id="editConsignmentModalTitle">Konsinye Cihaz Detayları [{{ $stock->urunAdi }}]</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
</div>

<div class="modal-body">
    <div class="row">
        {{-- SOL SÜTUN: ÜRÜN BİLGİLERİ VE FOTOĞRAFLAR --}}
        <div class="col-lg-5">
            <!-- 1. KART: ÜRÜN BİLGİLERİ -->
            <div class="card card-stock mb-3">
                <div class="card-header card-stock-header py-2">
                    <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Ürün Bilgileri</h5>
                </div>
                <div class="card-body card-stock-body p-1">
                    <div id="updateConsignmentFormMessages" class="px-2"></div>
                    <form method="POST" id="editConsignmentUnifiedForm"
                        action="{{ route('update.consignment.device', [$firma->id, $stock->id]) }}">
                        @csrf
                        {{-- Marka --}}
                        <div class="row g-1 align-items-center">
                            <label class="col-sm-4 col-form-label col-form-label-sm">Markalar<span
                                    style="color:red;">*</span></label>
                            <div class="col-sm-8">
                                <select name="marka_id" class="form-select form-select-sm" required>
                                    <option value="" selected disabled>Seçiniz</option>
                                    @foreach($markalar as $marka)
                                        <option value="{{ $marka->id }}" {{ $stock->stok_marka == $marka->id ? 'selected' : '' }}>
                                            {{ $marka->marka }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        {{-- Cihaz Türü --}}
                        <div class="row g-1  align-items-center">
                            <label class="col-sm-4 col-form-label col-form-label-sm">Cihaz Türü<span
                                    style="color:red;">*</span></label>
                            <div class="col-sm-8">
                                <select name="cihaz_id" class="form-select form-select-sm" required>
                                    <option value="" selected disabled>Seçiniz</option>
                                    @foreach($cihazlar as $cihaz)
                                        <option value="{{ $cihaz->id }}" {{ $stock->stok_cihaz == $cihaz->id ? 'selected' : '' }}>
                                            {{ $cihaz->cihaz }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        {{-- Raf --}}
                        <div class="row g-1  align-items-center">
                            <label class="col-sm-4 col-form-label col-form-label-sm">Raf<span
                                    style="color:red;">*</span></label>
                            <div class="col-sm-8">
                                <select name="raf_id" class="form-select form-select-sm" required>
                                    <option value="" selected disabled>Seçiniz</option>
                                    @foreach($rafListesi as $raf)
                                        <option value="{{ $raf->id }}" {{ $stock->urunDepo == $raf->id ? 'selected' : '' }}>
                                            {{ $raf->raf_adi }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Ürün Adı --}}
                        <div class="row g-1 align-items-center">
                            <label class="col-sm-4 col-form-label col-form-label-sm">Ürün Adı<span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" name="urunAdi" class="form-control form-control-sm"
                                    value="{{ $stock->urunAdi }}" required>
                            </div>
                        </div>

                        {{-- Ürün Kodu --}}
                        <div class="row g-1  align-items-center">
                            <label class="col-sm-4">Ürün Kodu <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <div class="d-flex align-items-center">
                                    <input type="text" name="urunKodu"
                                        class="form-control @error('urunKodu') is-invalid @enderror me-2"
                                        value="{{ old('urunKodu', $stock->urunKodu) }}" required>

                                    <a href="{{ route('consignment.device.barcode.pdf', [$firma->id, $stock->id]) }}"
                                        target="_blank" class="btn btn-warning btn-sm text-nowrap px-1 d-flex">
                                        Barkodu Yazdır
                                    </a>
                                </div>
                                @error('urunKodu')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        {{-- Satış Fiyatı --}}
                        <div class="row g-1 align-items-center">
                            <label class="col-sm-4 col-form-label col-form-label-sm">Satış Fiyatı (₺)<span
                                    style="color:red;">*</span></label>
                            <div class="col-sm-8">
                                <input name="fiyat" type="number" min="0" step="0.01"
                                    class="form-control form-control-sm" value="{{ $stock->fiyat }}" required>
                            </div>
                        </div>

                        {{-- Açıklama --}}
                        <div class="row g-1 align-items-center">
                            <label class="col-sm-4 col-form-label col-form-label-sm">Açıklama</label>
                            <div class="col-sm-8">
                                <textarea name="aciklama" class="form-control form-control-sm"
                                    rows="2">{{ $stock->aciklama }}</textarea>
                            </div>
                        </div>

                        {{-- Stok Durumu (Sadece Depo Stoku) --}}
                        @php
                            $toplamGiris = \App\Models\StockAction::where('stokId', $stock->id)->whereIn('islem', [1, 4])->sum('adet');
                            $toplamCikis = \App\Models\StockAction::where('stokId', $stock->id)->where('islem', 2)->sum('adet');
                            $kalanStok = $toplamGiris - $toplamCikis;
                        @endphp
                        <div class="row g-1 align-items-center">
                            <label class="col-sm-4 col-form-label col-form-label-sm">Stok Durumu</label>
                            <div class="col-sm-8">
                                <div class="alert alert-secondary py-1 px-2 mb-0">
                                    <small>{{ $kalanStok }} Adet</small>
                                </div>
                            </div>
                        </div>
                        <hr style="margin:5px;">
                        <div class="row g-1">
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-sm btn-info" id="saveConsignmentInfoBtn" form="editConsignmentUnifiedForm">Ürün
                                Bilgilerini Kaydet</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 2. KART: FOTOĞRAFLAR -->
            <div class="card card-stock" style="margin-bottom: 5px;">
                <div class="card-header card-stock-header py-2">
                    <h5 class="mb-0"><i class="bi bi-images"></i> Fotoğraflar</h5>
                </div>
                <div class="card-body card-stock-body p-2">
                    <div id="uploadFormContainer" style="display: {{ $photos->isEmpty() ? 'block' : 'none' }};">
                        <form method="POST" id="consignmentFotoEkle" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-2">
                                <input type="file" class="form-control form-control-sm" name="resim" id="customFile"
                                    accept="image/jpeg,image/png" data-tenant-id="{{ $firma->id }}">
                                <input type="hidden" name="stock_id" value="{{ $stock->id }}">
                            </div>
                            <div class="imgLoad" style="display: none;">
                                <div class="progress my-1" style="height: 5px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                                        style="width: 100%"></div>
                                </div>
                            </div>
                        </form>
                        <hr class="my-2">
                    </div>
                    <div id="photoLimitWarning" class="alert alert-warning  p-2" role="alert"
                        style="display: {{ !$photos->isEmpty() ? 'block' : 'none' }};padding:5px;">
                        <small><i class="fas fa-exclamation-triangle"></i> Yalnızca 1 fotoğraf ekleyebilirsiniz.
                            Değiştirmek için mevcut fotoğrafı silin.</small>
                    </div>

                    <div class="row imgBox">
                        @foreach($photos as $foto)
                            <div class="col-4 col-md-3 stn mb-2" data-id="{{ $foto->id }}">
                                <img src="{{ Storage::url($foto->resimyol) }}" class="img-fluid border rounded">
                                <button class="btn btn-outline-danger btn-sm w-100 stokFotoSil mt-1 py-0"
                                    data-id="{{ $foto->id }}" data-tenant-id="{{ $firma->id }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <div id="noPhotos" class="text-center text-muted"
                        style="display: {{ $photos->isEmpty() ? 'block' : 'none' }};">
                        <i class="fas fa-images" style="font-size: 2em;"></i>
                        <p class="mb-0 mt-1">Henüz fotoğraf yüklenmemiş</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- SAĞ SÜTUN: STOK HAREKETLERİ --}}
        <div class="col-lg-7">
            <div class="card card-stock">
                <div class="card-header card-stock-header ch-1">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h5 class="mb-0"><i class="bi bi-arrows-move"></i> Stok Hareketleri</h5>
                        <div>
                            <button type="button" class="btn btn-success btn-sm hareketEkleBtn"
                                data-stokid="{{ $stock->id }}" data-tenant-id="{{ $firma->id }}">
                                 Stok Hareketi Ekle
                            </button>
                            <select class="form-control-select islemSec d-inline-block" name="islemSec"
                                style="width: auto;">
                                <option value="0">Hepsi</option>
                                <option value="1">Alış</option>
                                <option value="4">Müşteriden Geri Alma</option>
                                <option value="2">Serviste Kullanım</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body card-body scrollable-card-body p-0">
                    <div class="table-responsive scrol-table">
                        <table class="table table-bordered table-sm mb-0">
                            <thead>
                                <tr>
                                    <th style="display:none;"></th>
                                    <th>Tarih</th>
                                    <th>İşlem</th>
                                    <th>Detay</th>
                                    <th>Adet</th>
                                    <th>Fiyat</th>
                                    <th style="width: 50px;">Sil</th>
                                </tr>
                                <tr class="toplam-header-row"
                                    style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; display: none;">
                                    <td style="display:none;"></td>
                                    <td colspan="3"></td>
                                    <td class="toplam-adet-header fw-bold"></td>
                                    <td class="toplam-fiyat-header fw-bold"></td>
                                    <td></td>
                                </tr>
                            </thead>
                            <tbody style="font-size:.800rem;">
                                @if($stokHareketleri->isEmpty())
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-3">
                                            <i class="fas fa-inbox" style="font-size: 2em;"></i>
                                            <p class="mb-0 mt-2">Henüz stok hareketi bulunmuyor</p>
                                            <small>İlk stok hareketini eklemek için "Hareket Ekle" butonunu kullanın</small>
                                        </td>
                                    </tr>
                                @else
                                    @foreach($stokHareketleri as $stokIslem)
                                        @php
                                            $tarih = \Carbon\Carbon::parse($stokIslem->created_at)->format('d/m/Y');
                                            $islem = '';
                                            $renk = '';

                                            if ($stokIslem->islem == 1) {
                                                $islem = "Alış";
                                                $renk = 'background-color: #d4edda;';
                                            } elseif ($stokIslem->islem == 4) {
                                                $islem = "Müşteriden Geri Alma";
                                                $renk = 'background-color: #cce5ff;';
                                            } elseif ($stokIslem->islem == 2) {
                                                $islem = "Serviste Kullanım";
                                                $teknisyenAdi = $stokIslem->performer_name ?? 'Bilinmiyor';
                                            }
                                        @endphp
                                        <tr style="{{ $renk }}">
                                            <td class="tdNumber" style="display:none;">0,{{ $stokIslem->islem }}</td>
                                            <td>{{ $tarih }}</td>
                                            <td>{{ $islem }}</td>
                                            <td>
                                                @if($stokIslem->islem == 1) {{ $stokIslem->tedarikci_adi ?? '-' }}
                                                @elseif($stokIslem->islem == 2)
                                                    <a href="{{ route('all.services', [$firma->id, 'did' => $stokIslem->servisid]) }}"
                                                        target="_blank" style="color:red!important;" class="link-stock">Servis:
                                                        {{ $stokIslem->servisid }}</a> ({{ $teknisyenAdi }})
                                                @elseif($stokIslem->islem == 4)
                                                    Müşteri: {{ $stokIslem->servis->musteri->adSoyad ?? '-' }}
                                                @endif
                                            </td>
                                            <td>{{ $stokIslem->adet }}</td>
                                            <td>{{ $stokIslem->islem == 1 && $stokIslem->fiyat > 0 ? number_format($stokIslem->fiyat, 2, '.', '') . ' TL' : '-' }}
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-outline-danger btn-sm stokSilBtn"
                                                    data-id="{{ $stokIslem->id }}" data-tenant-id="{{ $firma->id }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Kapat</button>
    
</div>

<!-- Hareket Ekle Modal -->
<div class="modal fade" id="hareketEkleModal" tabindex="-1" aria-hidden="true"
    style="padding-top: 70px; background: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-konsinye">
        <form id="hareketEkleForm" method="POST" action="{{ route('store.consignment.stock.action', $firma->id) }}">
            @csrf
            <input type="hidden" name="stok_id" id="modalStokId">
            <input type="hidden" name="form_token" id="hareketFormToken" value="">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konsinye Stok Hareketi Ekle</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Başlık ve input yan yana (col-sm-4 ve col-sm-8 ile) -->
                    <div class="row mb-3">
                        <label class="col-sm-4 col-4 col-form-label">İşlem</label>
                        <div class="col-sm-8 col-8">
                            <input type="text" class="form-control" value="Alış" readonly>
                            <input type="hidden" name="islem" value="1">
                        </div>
                    </div>

                    <!-- Başlık ve input yan yana (col-sm-4 ve col-sm-8 ile) -->
                    <div id="tedarikciSelectDiv" class="row mb-3">
                        <label class="col-sm-4 col-4 col-form-label">Tedarikçi</label>
                        <div class="col-sm-8 col-8">
                            <div class="input-group input-group-sm">
                                <!-- Select2 için güncellenmiş select -->
                                <select name="tedarikci" id="tedarikciSelect" class="form-control select2-ajax"
                                    data-url="/{{ $firma->id }}/search-suppliers">
                                    <option value="">Seçiniz</option>
                                    @foreach($sonTedarikciler as $tedarikci)
                                        <option value="{{ $tedarikci->id }}">{{ $tedarikci->tedarikci }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-success btn-sm d-flex align-items-center justify-content-center"
                                    type="button" id="addNewSupplierBtn" >
                                    +
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Adet ve Fiyat (Alt Alta) -->
                    <div class="row mb-3">
                        <label for="adet" class="col-sm-4 col-4 col-form-label">Adet</label>
                        <div class="col-sm-8 col-8">
                            <input type="number" id="adet" name="adet" class="form-control" required min="1">
                        </div>
                    </div>

                    <!-- Alış Fiyatı (Başlık ve input yan yana) -->
                    <div id="fiyatInputDiv" class="row mb-3">
                        <label for="fiyat" class="col-sm-4 col-4 col-form-label">Alış Fiyatı(₺)</label>
                        <div class="col-sm-8 col-8">
                            <input type="text" id="fiyat" name="fiyat" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tedarikçi Ekle Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" style="padding-top: 100px; background: rgba(0,0,0,0.7);">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yeni Tedarikçi Ekle</h5><button type="button" class="btn-close"
                    data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post" id="addStokSupplier" action="{{ route('store.stock.supplier', $firma->id) }}">
                    @csrf
                    <input type="hidden" name="form_token" id="supplierFormToken" value="">
                    <div class="mb-3"><label>Tedarikçi Adı :<span class="text-danger">*</span></label><input
                            name="tedarikci" class="form-control" type="text" required></div>
                    <div class="text-end"><input type="submit" class="btn btn-info btn-sm" value="Kaydet"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Token sistemi
        let hareketFormSubmitting = false;
        let supplierFormSubmitting = false;
        // Benzersiz token oluştur
        function generateHareketToken() {
            return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }
        // Sayfa yüklendiğinde tokenları oluştur
        $('#hareketFormToken').val(generateHareketToken());
        $('#supplierFormToken').val(generateHareketToken());

        // Select2'leri AJAX ile başlat
        $('.select2-ajax').each(function () {
            $(this).select2({
                theme: "bootstrap-5",
                dropdownParent: $(this).closest('.modal-body'),
                placeholder: 'Arama yapın...',
                ajax: {
                    url: $(this).data('url'),
                    dataType: 'json',
                    delay: 250,
                    processResults: function (data) { return { results: data }; },
                    cache: true
                }
            });
        });

        // Ürün kodu maskesi
        $('input[name="urunKodu"]').mask('0000000000000', { placeholder: '_____________' });

        // Ürün Bilgilerini Kaydetme Formu
        $(document).on('submit', '#editConsignmentUnifiedForm', function (e) {
            e.preventDefault();
            var $form = $(this),
                $submitButton = $('#saveConsignmentInfoBtn'),
                $messageDiv = $('#updateConsignmentFormMessages');

            // Mesaj alanlarını temizle
            $messageDiv.html('');
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('.invalid-feedback').text('');

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                beforeSend: function () {
                    $submitButton.prop('disabled', true).text('Kaydediliyor...');
                },
                success: function (response) {
                    $messageDiv.html('<div class="alert alert-success py-1 px-2">' + response.message + '</div>');

                    // Modal başlığını güncelle
                    var newUrunAdi = $form.find('input[name="urunAdi"]').val();
                    $('#editConsignmentModal .modal-title').text('Konsinye Cihaz Detayları [' + newUrunAdi + ']');

                    setTimeout(function () {
                        $messageDiv.fadeOut('slow', function () { $(this).html('').show(); });
                    }, 3000);
                },
                error: function (jqXHR) {
                    var response = jqXHR.responseJSON;

                    if (jqXHR.status === 422 && response.errors) {
                        $messageDiv.html('<div class="alert alert-danger py-1 px-2">' + (response.message || 'Lütfen formdaki hataları düzeltin.') + '</div>');

                        // Alan hatalarını göster
                        $.each(response.errors, function (field, messages) {
                            var $input = $form.find('[name="' + field + '"]');
                            $input.addClass('is-invalid');
                            $input.next('.invalid-feedback').text(messages[0]);
                        });
                    } else {
                        var errorMessage = response.message || 'Bilinmeyen bir sunucu hatası oluştu.';
                        $messageDiv.html('<div class="alert alert-danger py-1 px-2">' + errorMessage + '</div>');
                    }
                },
                complete: function () {
                    $submitButton.prop('disabled', false).text('Ürün Bilgilerini Kaydet');
                }
            });
        });

        // Modal içeriğini yenileme fonksiyonu
        function refreshConsignmentDetails(stockId, tenantId) {
            var modalContent = $('#editConsignmentModal .modal-content');

            $.ajax({
                url: '/' + tenantId + '/konsinye-cihazlar/duzenle/' + stockId,
                type: 'GET',
                success: function (response) {
                    if (response.html) {
                        modalContent.html(response.html);
                        // Yeni HTML yüklendiği için maskelemeyi yeniden başlat
                        $('input[name="urunKodu"]').mask('0000000000000', { placeholder: '_____________' });
                    }
                },
                error: function () {
                    alert('Konsinye detayları yenilenirken bir hata oluştu. Lütfen sayfayı yenileyin.');
                },
                complete: function () {
                    modalContent.css('opacity', 1);
                }
            });
        }

          // Hareket Ekleme Formu
    $(document).off('submit', '#hareketEkleForm').on('submit', '#hareketEkleForm', function(e) {
        e.preventDefault();
        
        // Token kontrolü
        if (hareketFormSubmitting) {
            return false;
        }
        
        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        
        // Form submit durumunu işaretle
        hareketFormSubmitting = true;
        
        $.ajax({
            type: form.attr('method'),
            url: form.attr('action'),
            data: form.serialize(),
            beforeSend: function() {
                submitButton.prop('disabled', true);
            },
            success: function(response) {
                $('#hareketEkleModal').modal('hide');
                form[0].reset();

                var stockId = $('#modalStokId').val();
                var tenantId = "{{ $firma->id }}";

                refreshConsignmentDetails(stockId, tenantId);
                
                setTimeout(function() {
                    var successMsg = '<div class="alert alert-success alert-dismissible fade show mt-2" role="alert">' + 
                                    response.message + 
                                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                    
                    $('#editConsignmentModal .modal-body').prepend(successMsg);
                    
                    setTimeout(function() {
                        $('#editConsignmentModal .alert-success').fadeOut(300, function() {
                            $(this).remove();
                        });
                    }, 4000);
                }, 500);
            },
            error: function(xhr) {
                var errorMessage = 'Bir hata oluştu.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors)[0][0];
                }
                
                // Tek bir alert göster
                if (!form.hasClass('error-shown')) {
                    form.addClass('error-shown');
                    alert(errorMessage);
                    setTimeout(function() {
                        form.removeClass('error-shown');
                    }, 1000);
                }
            },
            complete: function() {
                // 3 saniye sonra yeniden aktif et
                setTimeout(function() {
                    $('#hareketFormToken').val(generateHareketToken());
                    hareketFormSubmitting = false;
                    submitButton.prop('disabled', false).text('Kaydet');
                }, 3000);
            }
        });
    });

 // Hareket Ekle modalını aç
    $(document).on('click', '.hareketEkleBtn', function () {
        let stokId = $(this).data('stokid');
        let tenantId = $(this).data('tenant-id') || "{{ $firma->id ?? '' }}";
        $('#modalStokId').val(stokId);
        
        // Yeni token oluştur
        $('#hareketFormToken').val(generateHareketToken());
        hareketFormSubmitting = false;

        // Select2'yi AJAX ile başlatma
        $('#tedarikciSelect').select2({
            theme: "bootstrap-5",
            dropdownParent: $('#hareketEkleModal'),
            placeholder: 'Tedarikçi ara...',
            ajax: {
                url: '/' + tenantId + '/search-suppliers',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return { results: data };
                },
                cache: true
            }
        });

        $('#hareketEkleModal').modal('show');
    });

        // Modal kapandığında Select2'yi imha et
        $('#hareketEkleModal').on('hidden.bs.modal', function () {
            $('#tedarikciSelect').select2('destroy');

            // Arkadaki modal için scroll düzeltmesi
            if ($('#editConsignmentModal').is(':visible')) {
                $('body').addClass('modal-open');
            }
        });

        // Modal açıldığında - Hareket türü değiştiğinde çalışacak fonksiyon
        $('#hareketEkleModal').on('shown.bs.modal', function () {
            // İlk açılışta trigger et
            $('select[name="islem"]').trigger('change');
        });

        // Hareket türü seçimi - Tek bir event listener
        $(document).on('change', 'select[name="islem"]', function () {
            // Sadece modal içindeki select için çalış
            if (!$(this).closest('#hareketEkleModal').length) return;

            var val = $(this).val();
            var tedarikciBlok = $('#tedarikciSelectDiv');

            if (val == '1') { // Alış
                $('#fiyatInputDiv').show();
                $('#fiyatInputDiv input').prop('required', true);
                tedarikciBlok.show();
            }
        });

        // Hareketleri filtrele
        $(document).on('change', 'select[name="islemSec"]', function () {
            var selected = $(this).val();
            var card = $(this).closest('.card');
            var rows = card.find('table tbody tr');
            var summaryRow = card.find('.toplam-header-row'); // Toplam satırını seçelim

            // Eğer stok hareketi yoksa (yani "henüz hareket bulunmuyor" mesajı varsa) hiçbir şey yapma
            if (rows.find('td[colspan="7"]').length > 0) {
                summaryRow.hide();
                return;
            }

            var toplamAdet = 0;
            var toplamFiyat = 0;

            rows.each(function () {
                var tdNumber = $(this).find('.tdNumber').text().trim();

                // "Hepsi" seçiliyse tüm satırları göster
                if (selected == 0) {
                    $(this).show();
                } else {
                    // Değilse, seçilen işleme göre filtrele
                    if (tdNumber.endsWith(',' + selected)) {
                        $(this).show();
                        var adet = parseInt($(this).find('td').eq(4).text()) || 0;
                        var fiyat = parseFloat($(this).find('td').eq(5).text().replace(' TL', '').trim()) || 0;
                        toplamAdet += adet;
                        toplamFiyat += fiyat * adet;
                    } else {
                        $(this).hide();
                    }
                }
            });

            // "Hepsi" seçiliyse toplam satırını GİZLE
            if (selected == 0) {
                summaryRow.hide();
            } else {
                // Bir filtre seçiliyse toplamları hesapla ve satırı GÖSTER
                card.find('.toplam-adet-header').text(toplamAdet + ' Adet');
                card.find('.toplam-fiyat-header').text(toplamFiyat.toFixed(2) + ' TL');
                summaryRow.show();
            }
        });

        // Stok hareketini sil - Benzersiz class ile işaretleme
        $(document).on('click', '.stokSilBtn', function (e) {
            e.preventDefault();

            // Eğer bu buton zaten işlem görüyorsa dur
            if ($(this).hasClass('processing')) {
                return false;
            }

            var id = $(this).data('id');
            var tenant_id = $(this).data('tenant-id') || "{{ $firma->id ?? '' }}";
            var $button = $(this);

            // Silme butonuna en yakın tablo satırını (tr) bulalım
            var $tableRow = $button.closest('tr');

            if (confirm("Bu hareketi silmek istediğinize emin misiniz?")) {
                // Butonu işlem durumuna al
                $button.addClass('processing').prop('disabled', true);

                $.ajax({
                    url: '/' + tenant_id + '/stok-konsinye-hareket-sil/' + id,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                    success: function (res) {
                        alert(res.message);
                        if (res.status === 'success') {
                            $tableRow.fadeOut(400, function () {
                                // Satırı silmeden önce ana <tbody> elementini bulalım
                                var $tbody = $(this).closest('tbody');

                                // Şimdi satırı DOM'dan tamamen kaldıralım
                                $(this).remove();

                                // Kontrol: <tbody> içinde başka <tr> kaldı mı?
                                if ($tbody.find('tr').length === 0) {
                                    // Eğer hiç satır kalmadıysa, "hareket yok" mesajını oluştur
                                    var noMovementHtml = `
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">
                                        <i class="fas fa-inbox" style="font-size: 2em;"></i>
                                        <p class="mb-0 mt-2">Henüz stok hareketi bulunmuyor</p>
                                        <small>İlk stok hareketini eklemek için "Hareket Ekle" butonunu kullanın</small>
                                    </td>
                                </tr>
                            `;
                                    // Ve bu mesajı <tbody> içine ekle
                                    $tbody.append(noMovementHtml);
                                }
                            });

                        } else {
                            $button.removeClass('processing').prop('disabled', false);
                        }
                    },
                    error: function () {
                        alert('Silme işlemi sırasında hata oluştu.');
                        $button.removeClass('processing').prop('disabled', false);
                    }
                });
            }
            return false;
        });

        // Fotoğraf seçildiğinde otomatik yükle
        $(document).off('change', '#customFile').on('change', '#customFile', function () {
            let file = this.files[0];
            if (!file) return;

            if (file.size > 5242880) { alert("Dosya 5MB'dan büyük olamaz."); $(this).val(''); return; }
            if (!["image/jpeg", "image/png"].includes(file.type)) { alert("Sadece JPG ve PNG yüklenebilir."); $(this).val(''); return; }

            let tenantId = $(this).data('tenant-id') || "{{ $firma->id ?? '' }}";
            let formData = new FormData($('#consignmentFotoEkle')[0]);

            $.ajax({
                url: "/" + tenantId + "/stok-konsinye-foto-ekle",
                method: "POST",
                data: formData,
                contentType: false, processData: false,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                beforeSend: function () { $(".imgLoad").show(); },
                success: function (res) {
                    $(".imgLoad").hide();
                    $('#customFile').val('');
                    $('.imgBox').prepend(`<div class="col-4 col-md-3 stn mb-2" data-id="${res.id}"><img src="${res.resim_yolu}" class="img-fluid border rounded"><button class="btn btn-outline-danger btn-sm w-100 stokFotoSil mt-1 py-0" data-id="${res.id}"><i class="fas fa-trash-alt"></i></button></div>`);

                    $('#noPhotos').hide();
                    $('#uploadFormContainer').hide();
                    $('#photoLimitWarning').show();
                },
                error: function (xhr) {
                    $(".imgLoad").hide();
                    alert(xhr.responseJSON ? xhr.responseJSON.message : "Yükleme başarısız.");
                }
            });
        });

        // Fotoğrafı sil - Benzersiz class ile işaretleme
        $(document).off('click', '.stokFotoSil').on('click', '.stokFotoSil', function (e) {
            e.preventDefault();

            if ($(this).hasClass('processing')) {
                return false;
            }

            if (!confirm("Fotoğraf silinsin mi?")) {
                return false;
            }

            var $button = $(this);
            var id = $button.data('id');
            var tenantId = $button.data('tenant-id') || "{{ $firma->id ?? '' }}";

            $button.addClass('processing').prop('disabled', true);

            let fotoDiv = $('.stn[data-id="' + id + '"]');

            $.ajax({
                url: "/" + tenantId + "/stok-konsinye-foto-sil",
                method: "POST",
                data: { _token: "{{ csrf_token() }}", id: id },
                success: function (res) {
                    alert(res.message);
                    fotoDiv.fadeOut(300, function () {
                        $(this).remove();

                        if ($('.imgBox .stn').length === 0) {
                            $('#noPhotos').show();
                            $('#uploadFormContainer').show();
                            $('#photoLimitWarning').hide();
                        }
                    });
                },
                error: function (xhr) {
                    alert(xhr.responseJSON ? xhr.responseJSON.message : "Silme işlemi başarısız.");
                    $button.removeClass('processing').prop('disabled', false);
                }
            });

            return false;
        });

        
        // Tedarikçi ekleme formu - TOKEN KONTROLÜ İLE GÜNCELLENDİ
        $(document).on('submit', '#addStokSupplier', function(e){
            e.preventDefault();
            
            // Token kontrolü - YENİ
            if (supplierFormSubmitting) {
                return false;
            }
            
            var form = $(this);
            var submitButton = form.find('input[type="submit"]');
            
            if (this.checkValidity() === false) {
                e.stopPropagation();
                return false;
            }
            
            // Form submit durumunu işaretle
            supplierFormSubmitting = true; 
            submitButton.prop('disabled', true);
            
            var formData = form.serialize();
            
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                success: function(response) {
                    alert("Tedarikçi başarıyla eklendi.");
                    
                    var newOption = new Option(response.tedarikci, response.id, true, true);
                    $('#hareketEkleModal select[name="tedarikci"]').append(newOption);
                    $('#tedarikciSelect').val(response.id).trigger('change');
                    
                    form[0].reset();
                    $('#addSupplierModal').modal('hide');
                    
                    // Token'ı yenile - YENİ
                    $('#supplierFormToken').val(generateHareketToken());
                    supplierFormSubmitting = false;
                    submitButton.prop('disabled', false).val('Kaydet');
                },
                error: function(xhr, status, error) {
                    var errorMessage = 'Bir hata oluştu. Lütfen tekrar deneyin.';
                    
                    // Token hatası kontrolü - YENİ
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        if (xhr.responseJSON.error.includes('token') || xhr.responseJSON.error.includes('gönderildi')) {
                            console.log('Token hatası:', xhr.responseJSON.error);
                        } else {
                            alert(errorMessage + ': ' + (xhr.responseJSON.message || error));
                        }
                    } else {
                        alert(errorMessage);
                    }
                    
                    console.log('Hata detayları:', xhr.responseText);
                    
                    // Token'ı yenile - YENİ
                    $('#supplierFormToken').val(generateHareketToken());
                    supplierFormSubmitting = false;
                    submitButton.prop('disabled', false).val('Kaydet');
                }
            });
            
            return false;
        });
        // Modal tamamen kapandığında temizlik yap
        $('#addSupplierModal').on('hidden.bs.modal', function () {
            // Form durumunu sıfırla (eğer hala submitting durumundaysa)
            $('#addStokSupplier').removeClass('submitting');
            $('#addStokSupplier input[type="submit"]').prop('disabled', false).val('Kaydet');

            // Ana hareket modal'ı açıksa body scroll problemini düzelt
            if ($('#hareketEkleModal').hasClass('show')) {
                $('body').addClass('modal-open');
            }
        });
        // Tedarikçi Ekle (+) butonuna tıklandığında modalı aç
        $(document).on('click', '#addNewSupplierBtn', function () {
            $('#supplierFormToken').val(generateHareketToken());
            supplierFormSubmitting = false;
            var supplierModal = new bootstrap.Modal(document.getElementById('addSupplierModal'));
            supplierModal.show();
        });

        //Tedarikçi eklemede +  butonuna basınca karartıyordu datatble ı onu önlemek için eklendi
        $('#editConsignmentModal').on('hidden.bs.modal', function (e) {
            $('body').removeClass('modal-open');
        });


    });

</script>