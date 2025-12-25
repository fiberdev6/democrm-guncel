@extends('frontend.secure.user_master')
@section('user')
<div class="page-content">
    <div class="container-fluid">
        
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Sektörler Sayfası Yönetimi</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('super.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Sektörler</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        
                        <!-- Tabs -->
                        <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#page-header" role="tab">
                                    <i class="fas fa-heading me-1"></i> Sayfa Başlığı
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#sectors-list" role="tab">
                                    <i class="fas fa-th-large me-1"></i> Sektörler
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#cta-section" role="tab">
                                    <i class="fas fa-bullhorn me-1"></i> CTA Bölümü
                                </a>
                            </li>
                        </ul>

                        <form action="{{ route('super.admin.frontend.sectors-content.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="tab-content p-3">
                                
                                <!-- PAGE HEADER TAB -->
                                <div class="tab-pane active" id="page-header" role="tabpanel">
                                    <h5 class="mb-3">Sayfa Başlığı</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Başlık (1. Kısım)</label>
                                            <input type="text" class="form-control" name="header_title" value="{{ $sectors->content['page_header']['title'] ?? 'Hizmet Verdiğimiz' }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Başlık Vurgulu (2. Kısım)</label>
                                            <input type="text" class="form-control" name="header_title_highlight" value="{{ $sectors->content['page_header']['title_highlight'] ?? 'Sektörler' }}">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Alt Başlık</label>
                                        <textarea class="form-control" name="header_subtitle" rows="2">{{ $sectors->content['page_header']['subtitle'] ?? 'Farklı sektörlerdeki teknik servis işletmelerinin ihtiyaçlarına özel çözümler sunuyoruz' }}</textarea>
                                    </div>
                                </div>

                                <!-- SECTORS TAB -->
                                <div class="tab-pane" id="sectors-list" role="tabpanel">
                                    <h5 class="mb-3">Sektörler</h5>
                                    
                                    <div id="sectorsContainer">
                                        @if(isset($sectors->content['sectors']))
                                            @foreach($sectors->content['sectors'] as $index => $sector)
                                                <div class="card mb-3 sector-item">
                                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                        <h6 class="mb-0">Sektör {{ $index + 1 }}</h6>
                                                        <div>
                                                            <a href="{{ route('super.admin.frontend.sector-detail', $sector['slug']) }}" class="btn btn-primary btn-sm me-2">
                                                                <i class="fas fa-edit"></i> Detay Düzenle
                                                            </a>
                                                            <button type="button" class="btn btn-danger btn-sm remove-sector">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-3 mb-3">
                                                                <label class="form-label">Slug (URL)</label>
                                                                <input type="text" class="form-control sector-slug" value="{{ $sector['slug'] }}" placeholder="elektrik-elektronik">
                                                            </div>
                                                            <div class="col-md-3 mb-3">
                                                                <label class="form-label">İkon</label>
                                                                <input type="text" class="form-control sector-icon" value="{{ $sector['icon'] }}" placeholder="fas fa-plug">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Başlık</label>
                                                                <input type="text" class="form-control sector-title" value="{{ $sector['title'] }}" placeholder="Elektrik-Elektronik">
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Kısa Açıklama</label>
                                                            <input type="text" class="form-control sector-description" value="{{ $sector['short_description'] }}" placeholder="Elektronik cihaz servis süreçleri yönetimi">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Görsel</label>
                                                            @if(isset($sector['image']))
                                                                <div class="mb-2">
                                                                    <img src="{{ asset($sector['image']) }}" style="height: 80px; border-radius: 5px;">
                                                                </div>
                                                            @endif
                                                            <input type="file" class="form-control sector-image" accept="image/*">
                                                            <input type="hidden" class="sector-image-current" value="{{ $sector['image'] ?? '' }}">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Özellikler (Her satıra bir özellik)</label>
                                                            <textarea class="form-control sector-features" rows="4" placeholder="Cihaz kabul ve barkodlama&#10;Yedek parça stok takibi">{{ isset($sector['features']) ? implode("\n", $sector['features']) : '' }}</textarea>
                                                            <small class="text-muted">Her özelliği yeni satıra yazın</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>

                                    <button type="button" class="btn btn-secondary" id="addSector">
                                        <i class="fas fa-plus me-1"></i> Yeni Sektör Ekle
                                    </button>
                                </div>

                                <!-- CTA TAB -->
                                <div class="tab-pane" id="cta-section" role="tabpanel">
                                    <h5 class="mb-3">CTA (Call to Action) Bölümü</h5>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Başlık</label>
                                        <input type="text" class="form-control" name="cta_title" value="{{ $sectors->content['cta']['title'] ?? '14 Gün Ücretsiz Deneyin!' }}">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Açıklama</label>
                                        <textarea class="form-control" name="cta_description" rows="2">{{ $sectors->content['cta']['description'] ?? 'Sektörünüze özel Serbis takip programını keşfedin.' }}</textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Buton Metni</label>
                                            <input type="text" class="form-control" name="cta_button_text" value="{{ $sectors->content['cta']['button_text'] ?? 'Hemen Ücretsiz Başla' }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Buton URL</label>
                                            <input type="text" class="form-control" name="cta_button_url" value="{{ $sectors->content['cta']['button_url'] ?? '/kullanici-girisi' }}">
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Kaydet
                                </button>
                                <a href="{{ url('/sektorler') }}" target="_blank" class="btn btn-info">
                                    <i class="fas fa-external-link-alt me-1"></i> Sayfayı Görüntüle
                                </a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
$(document).ready(function() {
    // Tab hash kontrolü
    let hash = window.location.hash;
    if (hash) {
        $('.nav-tabs a[href="' + hash + '"]').tab('show');
    }
    
    $('.nav-tabs a').on('shown.bs.tab', function(e) {
        window.location.hash = e.target.hash;
    });

// ========== SECTOR ADD/REMOVE ==========
$('#addSector').on('click', function() {
    const index = $('.sector-item').length;
    const html = `
        <div class="card mb-3 sector-item">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Sektör ${index + 1}</h6>
                <div>
                    <a href="#" class="btn btn-primary btn-sm me-2 sector-detail-btn" style="display:none;">
                        <i class="fas fa-edit"></i> Detay Düzenle
                    </a>
                    <button type="button" class="btn btn-danger btn-sm remove-sector">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Slug (URL)</label>
                        <input type="text" class="form-control sector-slug" placeholder="elektrik-elektronik">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">İkon</label>
                        <input type="text" class="form-control sector-icon" placeholder="fas fa-plug">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Başlık</label>
                        <input type="text" class="form-control sector-title" placeholder="Elektrik-Elektronik">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kısa Açıklama</label>
                    <input type="text" class="form-control sector-description" placeholder="Elektronik cihaz servis süreçleri yönetimi">
                </div>

                <div class="mb-3">
                    <label class="form-label">Görsel</label>
                    <input type="file" class="form-control sector-image" accept="image/*">
                    <input type="hidden" class="sector-image-current" value="">
                </div>

                <div class="mb-3">
                    <label class="form-label">Özellikler (Her satıra bir özellik)</label>
                    <textarea class="form-control sector-features" rows="4" placeholder="Cihaz kabul ve barkodlama&#10;Yedek parça stok takibi"></textarea>
                    <small class="text-muted">Her özelliği yeni satıra yazın</small>
                </div>
            </div>
        </div>
    `;
    $('#sectorsContainer').append(html);
});

// Slug değişince Detay Düzenle linkini güncelle
$(document).on('input', '.sector-slug', function() {
    const slug = $(this).val();
    const detailBtn = $(this).closest('.sector-item').find('.sector-detail-btn');
    
    if(slug) {
        const detailUrl = "{{ route('super.admin.frontend.sector-detail', ':slug') }}".replace(':slug', slug);
        detailBtn.attr('href', detailUrl).show();
    } else {
        detailBtn.hide();
    }
});

    $(document).on('click', '.remove-sector', function() {
        if (confirm('Bu sektörü silmek istediğinize emin misiniz?')) {
            $(this).closest('.sector-item').remove();
            // Numaraları yeniden düzenle
            $('.sector-item').each(function(index) {
                $(this).find('.card-header h6').text('Sektör ' + (index + 1));
            });
        }
    });

    // ========== FORM SUBMIT ==========
    $('form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Page Header
        const pageHeader = {
            title: $('[name="header_title"]').val(),
            title_highlight: $('[name="header_title_highlight"]').val(),
            subtitle: $('[name="header_subtitle"]').val()
        };
        
        // Sectors Topla
        const sectors = [];
        $('.sector-item').each(function(index) {
            const featuresText = $(this).find('.sector-features').val();
            const features = featuresText.split('\n').map(f => f.trim()).filter(f => f.length > 0);
            
            const sector = {
                slug: $(this).find('.sector-slug').val(),
                icon: $(this).find('.sector-icon').val(),
                title: $(this).find('.sector-title').val(),
                short_description: $(this).find('.sector-description').val(),
                image: $(this).find('.sector-image-current').val(),
                features: features
            };
            
            if(sector.slug && sector.title) {
                sectors.push(sector);
                
                // Image file varsa ekle
                const imageFile = $(this).find('.sector-image')[0].files[0];
                if(imageFile) {
                    formData.append('sector_image_' + index, imageFile);
                }
            }
        });
        
        // CTA
        const cta = {
            title: $('[name="cta_title"]').val(),
            description: $('[name="cta_description"]').val(),
            button_text: $('[name="cta_button_text"]').val(),
            button_url: $('[name="cta_button_url"]').val()
        };
        
        // JSON Content Oluştur
        const content = {
            page_header: pageHeader,
            sectors: sectors,
            cta: cta
        };
        
        console.log('Gönderilecek Content:', content);
        
        formData.append('content', JSON.stringify(content));
        
        // AJAX Submit
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                toastr.success('Sektörler içeriği güncellendi!');
                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                toastr.error('Bir hata oluştu!');
                console.error(xhr);
            }
        });
    });
});
</script>
@endsection