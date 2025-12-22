@extends('frontend.secure.user_master')
@section('user')
<div class="page-content">
    <div class="container-fluid">
        
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Özellikler Sayfası Yönetimi</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('super.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Özellikler</li>
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
                                <a class="nav-link" data-bs-toggle="tab" href="#categories" role="tab">
                                    <i class="fas fa-th-large me-1"></i> Özellik Kategorileri
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#cta-section" role="tab">
                                    <i class="fas fa-bullhorn me-1"></i> CTA Bölümü
                                </a>
                            </li>
                        </ul>

                        <form action="{{ route('super.admin.frontend.features-content.update') }}" method="POST">
                            @csrf
                            
                            <div class="tab-content p-3">
                                
                                <!-- PAGE HEADER TAB -->
                                <div class="tab-pane active" id="page-header" role="tabpanel">
                                    <h5 class="mb-3">Sayfa Başlığı</h5>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Ana Başlık</label>
                                        <input type="text" class="form-control" name="header_title" value="{{ $features->content['page_header']['title'] ?? 'Serbis Özellikleri' }}">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Alt Başlık</label>
                                        <textarea class="form-control" name="header_subtitle" rows="2">{{ $features->content['page_header']['subtitle'] ?? 'Teknik servis işletmenizi büyütmek için ihtiyacınız olan tüm özellikleri keşfedin' }}</textarea>
                                    </div>
                                </div>

                                <!-- CATEGORIES TAB -->
                                <div class="tab-pane" id="categories" role="tabpanel">
                                    <h5 class="mb-3">Özellik Kategorileri</h5>
                                    
                                    <div id="categoriesContainer">
                                        @if(isset($features->content['categories']))
                                            @foreach($features->content['categories'] as $catIndex => $category)
                                                <div class="card mb-4 category-item">
                                                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                                        <h6 class="mb-0 text-white">Kategori {{ $catIndex + 1 }}</h6>
                                                        <div>
                                                            <a href="{{ route('super.admin.frontend.feature-detail', $category['slug']) }}" class="btn btn-light btn-sm me-2">
                                                                <i class="fas fa-edit"></i> Detay Düzenle
                                                            </a>
                                                            <button type="button" class="btn btn-danger btn-sm remove-category">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row mb-3">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Kategori Başlığı</label>
                                                                <input type="text" class="form-control category-title" value="{{ $category['title'] }}" placeholder="Müşteri Yönetimi">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">Slug</label>
                                                                <input type="text" class="form-control category-slug" value="{{ $category['slug'] }}" placeholder="musteri-yonetimi">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">Alt Başlık</label>
                                                                <input type="text" class="form-control category-subtitle" value="{{ $category['subtitle'] }}" placeholder="Kısa açıklama">
                                                            </div>
                                                        </div>

                                                        <h6 class="mt-3 mb-2">Özellik Kartları</h6>
                                                        <div class="feature-items-container">
                                                            @if(isset($category['items']))
                                                                @foreach($category['items'] as $itemIndex => $item)
                                                                    <div class="card mb-2 feature-item">
                                                                        <div class="card-body">
                                                                            <div class="row">
                                                                                <div class="col-md-2">
                                                                                    <label class="form-label">İkon</label>
                                                                                    <input type="text" class="form-control feature-icon" value="{{ $item['icon'] }}" placeholder="fas fa-user">
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    <label class="form-label">Renk</label>
                                                                                    <select class="form-control feature-color">
                                                                                        <option value="blue" {{ $item['color'] == 'blue' ? 'selected' : '' }}>Mavi</option>
                                                                                        <option value="green" {{ $item['color'] == 'green' ? 'selected' : '' }}>Yeşil</option>
                                                                                        <option value="orange" {{ $item['color'] == 'orange' ? 'selected' : '' }}>Turuncu</option>
                                                                                        <option value="purple" {{ $item['color'] == 'purple' ? 'selected' : '' }}>Mor</option>
                                                                                        <option value="red" {{ $item['color'] == 'red' ? 'selected' : '' }}>Kırmızı</option>
                                                                                        <option value="teal" {{ $item['color'] == 'teal' ? 'selected' : '' }}>Turkuaz</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="col-md-3">
                                                                                    <label class="form-label">Başlık</label>
                                                                                    <input type="text" class="form-control feature-title" value="{{ $item['title'] }}" placeholder="Özellik Başlığı">
                                                                                </div>
                                                                                <div class="col-md-4">
                                                                                    <label class="form-label">Açıklama</label>
                                                                                    <textarea class="form-control feature-description" rows="1">{{ $item['description'] }}</textarea>
                                                                                </div>
                                                                                <div class="col-md-1">
                                                                                    <label class="form-label">&nbsp;</label>
                                                                                    <button type="button" class="btn btn-danger btn-sm w-100 remove-feature-item">
                                                                                        <i class="fas fa-trash"></i>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                        <button type="button" class="btn btn-secondary btn-sm add-feature-item">
                                                            <i class="fas fa-plus me-1"></i> Özellik Ekle
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>

                                    <button type="button" class="btn btn-primary" id="addCategory">
                                        <i class="fas fa-plus me-1"></i> Yeni Kategori Ekle
                                    </button>
                                </div>

                                <!-- CTA TAB -->
                                <div class="tab-pane" id="cta-section" role="tabpanel">
                                    <h5 class="mb-3">CTA (Call to Action) Bölümü</h5>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Başlık</label>
                                        <input type="text" class="form-control" name="cta_title" value="{{ $features->content['cta']['title'] ?? 'Tüm Özellikleri Ücretsiz Deneyin!' }}">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Açıklama</label>
                                        <textarea class="form-control" name="cta_description" rows="2">{{ $features->content['cta']['description'] ?? '14 gün boyunca kredi kartı gerektirmeden tüm özelliklere erişin' }}</textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Buton Metni</label>
                                            <input type="text" class="form-control" name="cta_button_text" value="{{ $features->content['cta']['button_text'] ?? 'Hemen Başlayın' }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Buton URL</label>
                                            <input type="text" class="form-control" name="cta_button_url" value="{{ $features->content['cta']['button_url'] ?? '/kullanici-girisi' }}">
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Kaydet
                                </button>
                                <a href="{{ url('/ozellikler') }}" target="_blank" class="btn btn-info">
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

// ========== CATEGORY ADD/REMOVE ==========
$('#addCategory').on('click', function() {
    const categoryCount = $('.category-item').length + 1;
    const html = `
        <div class="card mb-4 category-item">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-white">Kategori ${categoryCount}</h6>
                <div>
                    <a href="#" class="btn btn-light btn-sm me-2 feature-detail-btn" style="display:none;">
                        <i class="fas fa-edit"></i> Detay Düzenle
                    </a>
                    <button type="button" class="btn btn-danger btn-sm remove-category">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Kategori Başlığı</label>
                        <input type="text" class="form-control category-title" placeholder="Müşteri Yönetimi">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control category-slug" placeholder="musteri-yonetimi">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Alt Başlık</label>
                        <input type="text" class="form-control category-subtitle" placeholder="Kısa açıklama">
                    </div>
                </div>

                <h6 class="mt-3 mb-2">Özellik Kartları</h6>
                <div class="feature-items-container">
                </div>
                <button type="button" class="btn btn-secondary btn-sm add-feature-item">
                    <i class="fas fa-plus me-1"></i> Özellik Ekle
                </button>
            </div>
        </div>
    `;
    $('#categoriesContainer').append(html);
});

// Slug değişince Detay Düzenle linkini güncelle
$(document).on('input', '.category-slug', function() {
    const slug = $(this).val();
    const detailBtn = $(this).closest('.category-item').find('.feature-detail-btn');
    
    if(slug) {
        const detailUrl = "{{ route('super.admin.frontend.feature-detail', ':slug') }}".replace(':slug', slug);
        detailBtn.attr('href', detailUrl).show();
    } else {
        detailBtn.hide();
    }
});
    $(document).on('click', '.remove-category', function() {
        if (confirm('Bu kategoriyi silmek istediğinize emin misiniz?')) {
            $(this).closest('.category-item').remove();
            // Numaraları yeniden düzenle
            $('.category-item').each(function(index) {
                $(this).find('.card-header h6').text('Kategori ' + (index + 1));
            });
        }
    });

    // ========== FEATURE ITEM ADD/REMOVE ==========
    $(document).on('click', '.add-feature-item', function() {
        const container = $(this).siblings('.feature-items-container');
        const html = `
            <div class="card mb-2 feature-item">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <label class="form-label">İkon</label>
                            <input type="text" class="form-control feature-icon" placeholder="fas fa-user">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Renk</label>
                            <select class="form-control feature-color">
                                <option value="blue">Mavi</option>
                                <option value="green">Yeşil</option>
                                <option value="orange">Turuncu</option>
                                <option value="purple">Mor</option>
                                <option value="red">Kırmızı</option>
                                <option value="teal">Turkuaz</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Başlık</label>
                            <input type="text" class="form-control feature-title" placeholder="Özellik Başlığı">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Açıklama</label>
                            <textarea class="form-control feature-description" rows="1"></textarea>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-sm w-100 remove-feature-item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.append(html);
    });

    $(document).on('click', '.remove-feature-item', function() {
        $(this).closest('.feature-item').remove();
    });

    // ========== FORM SUBMIT ==========
    $('form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Page Header
        const pageHeader = {
            title: $('[name="header_title"]').val(),
            subtitle: $('[name="header_subtitle"]').val()
        };
        
        // Categories Topla
        const categories = [];
        $('.category-item').each(function() {
            const items = [];
            $(this).find('.feature-item').each(function() {
                const item = {
                    icon: $(this).find('.feature-icon').val(),
                    color: $(this).find('.feature-color').val(),
                    title: $(this).find('.feature-title').val(),
                    description: $(this).find('.feature-description').val()
                };
                if(item.title) {
                    items.push(item);
                }
            });
            
            const category = {
                title: $(this).find('.category-title').val(),
                slug: $(this).find('.category-slug').val(),
                subtitle: $(this).find('.category-subtitle').val(),
                items: items
            };
            
            if(category.title && category.slug) {
                categories.push(category);
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
            categories: categories,
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
                toastr.success('Özellikler içeriği güncellendi!');
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