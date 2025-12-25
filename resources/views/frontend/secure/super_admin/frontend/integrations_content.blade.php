@extends('frontend.secure.user_master')
@section('user')
<div class="page-content">
    <div class="container-fluid">
        
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Entegrasyonlar Sayfası Yönetimi</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('super.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Entegrasyonlar</li>
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
                                <a class="nav-link" data-bs-toggle="tab" href="#marquee-logos" role="tab">
                                    <i class="fas fa-images me-1"></i> Marquee Logoları
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#categories" role="tab">
                                    <i class="fas fa-th-large me-1"></i> Entegrasyon Kategorileri
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#faqs" role="tab">
                                    <i class="fas fa-question-circle me-1"></i> SSS
                                </a>
                            </li>
                        </ul>

                        <form action="{{ route('super.admin.frontend.integrations-content.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="tab-content p-3">
                                
                                <!-- PAGE HEADER TAB -->
                                <div class="tab-pane active" id="page-header" role="tabpanel">
                                    <h5 class="mb-3">Hero Bölümü</h5>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Ana Başlık</label>
                                        <input type="text" class="form-control" name="header_title" 
                                               value="{{ $integrations->content['page_header']['title'] ?? 'Serbis Entegrasyonları ile Tüm Süreçlerinizi Entegre Edin' }}">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Alt Başlık</label>
                                        <textarea class="form-control" name="header_subtitle" rows="2">{{ $integrations->content['page_header']['subtitle'] ?? 'Serbis uygulama mağazasındaki uygulama ve entegrasyonlar ile teknik servis sitenizi çok yönlü hale getirin.' }}</textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Buton Metni</label>
                                            <input type="text" class="form-control" name="header_button_text" 
                                                   value="{{ $integrations->content['page_header']['button_text'] ?? 'Deneme Hesabı Oluştur' }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Buton URL</label>
                                            <input type="text" class="form-control" name="header_button_url" 
                                                   value="{{ $integrations->content['page_header']['button_url'] ?? '/kullanici-girisi' }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- MARQUEE LOGOS TAB -->
                                <div class="tab-pane" id="marquee-logos" role="tabpanel">
                                    <h5 class="mb-3">Marquee (Kayan) Logoları</h5>
                                    <p class="text-muted">Hero bölümünde kayan logo bandı için entegrasyon logoları</p>
                                    
                                    <div id="marqueeLogosContainer">
                                        @if(isset($integrations->content['marquee_logos']))
                                            @foreach($integrations->content['marquee_logos'] as $index => $logo)
                                                <div class="card mb-2 marquee-logo-item">
                                                    <div class="card-body">
                                                        <div class="row align-items-center">
                                                            <div class="col-md-2">
                                                                @if(isset($logo['logo']))
                                                                    <img src="{{ asset($logo['logo']) }}" style="height: 50px; width: auto;" class="mb-2">
                                                                @endif
                                                                <input type="file" class="form-control form-control-sm marquee-logo-file" accept="image/*">
                                                                <input type="hidden" class="marquee-logo-current" value="{{ $logo['logo'] ?? '' }}">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">İsim</label>
                                                                <input type="text" class="form-control marquee-logo-name" value="{{ $logo['name'] }}" placeholder="NetGSM">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">Kategori</label>
                                                                <input type="text" class="form-control marquee-logo-category" value="{{ $logo['category'] }}" placeholder="SMS">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <label class="form-label">&nbsp;</label>
                                                                <button type="button" class="btn btn-danger btn-sm w-100 remove-marquee-logo">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-primary" id="addMarqueeLogo">
                                        <i class="fas fa-plus me-1"></i> Logo Ekle
                                    </button>
                                </div>

                                <!-- CATEGORIES TAB -->
                                <div class="tab-pane" id="categories" role="tabpanel">
                                    <h5 class="mb-3">Entegrasyon Kategorileri</h5>
                                    
                                    <div id="categoriesContainer">
                                        @if(isset($integrations->content['categories']))
                                            @foreach($integrations->content['categories'] as $catIndex => $category)
                                                <div class="card mb-4 category-item">
                                                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                                        <h6 class="mb-0 text-white">Kategori {{ $catIndex + 1 }}</h6>
                                                        <button type="button" class="btn btn-danger btn-sm remove-category">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row mb-3">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Kategori Başlığı</label>
                                                                <input type="text" class="form-control category-title" value="{{ $category['title'] }}" placeholder="SMS Entegrasyonları">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">Alt Başlık</label>
                                                                <input type="text" class="form-control category-subtitle" value="{{ $category['subtitle'] }}" placeholder="Kısa açıklama">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">Arka Plan Stili</label>
                                                                <select class="form-control category-bg-style">
                                                                    <option value="white" {{ ($category['bg_style'] ?? 'white') == 'white' ? 'selected' : '' }}>Beyaz</option>
                                                                    <option value="gray" {{ ($category['bg_style'] ?? 'white') == 'gray' ? 'selected' : '' }}>Gri</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <h6 class="mt-3 mb-2">Entegrasyonlar</h6>
                                                        <div class="integrations-container">
                                                            @if(isset($category['integrations']))
                                                                @foreach($category['integrations'] as $intIndex => $integration)
                                                                    <div class="card mb-3 integration-item">
                                                                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                                            <span>{{ $integration['name'] }}</span>
                                                                            <button type="button" class="btn btn-danger btn-sm remove-integration">
                                                                                <i class="fas fa-trash"></i>
                                                                            </button>
                                                                        </div>
                                                                        <div class="card-body">
                                                                            <div class="row mb-3">
                                                                                <div class="col-md-3">
                                                                                    @if(isset($integration['logo']))
                                                                                        <img src="{{ asset($integration['logo']) }}" style="height: 60px; width: auto;" class="mb-2 d-block">
                                                                                    @endif
                                                                                    <label class="form-label">Logo</label>
                                                                                    <input type="file" class="form-control form-control-sm integration-logo-file" accept="image/*">
                                                                                    <input type="hidden" class="integration-logo-current" value="{{ $integration['logo'] ?? '' }}">
                                                                                </div>
                                                                                <div class="col-md-3">
                                                                                    <label class="form-label">İsim</label>
                                                                                    <input type="text" class="form-control integration-name" value="{{ $integration['name'] }}" placeholder="NETGSM">
                                                                                </div>
                                                                                <div class="col-md-3">
                                                                                    <label class="form-label">Kategori Etiketi</label>
                                                                                    <input type="text" class="form-control integration-category-tag" value="{{ $integration['category_tag'] }}" placeholder="SMS">
                                                                                </div>
                                                                                <div class="col-md-3">
                                                                                    <label class="form-label">Kısa Açıklama</label>
                                                                                    <input type="text" class="form-control integration-description" value="{{ $integration['description'] }}" placeholder="Kısa açıklama">
                                                                                </div>
                                                                            </div>

                                                                            <div class="mb-3">
                                                                                <label class="form-label">Detay Açıklama (Hover)</label>
                                                                                <textarea class="form-control integration-detail" rows="2">{{ $integration['detail'] }}</textarea>
                                                                            </div>

                                                                            <div>
                                                                                <label class="form-label">Özellikler (Her satıra bir özellik)</label>
                                                                                <textarea class="form-control integration-features" rows="3" placeholder="Toplu SMS gönderimi&#10;SMS şablonları">{{ isset($integration['features']) ? implode("\n", $integration['features']) : '' }}</textarea>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                        <button type="button" class="btn btn-secondary btn-sm add-integration">
                                                            <i class="fas fa-plus me-1"></i> Entegrasyon Ekle
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

                                <!-- FAQS TAB -->
                                <div class="tab-pane" id="faqs" role="tabpanel">
                                    <h5 class="mb-3">Sıkça Sorulan Sorular</h5>
                                    
                                    <div id="faqsContainer">
                                        @if(isset($integrations->content['faqs']))
                                            @foreach($integrations->content['faqs'] as $index => $faq)
                                                <div class="card mb-2 faq-item">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-11">
                                                                <div class="mb-2">
                                                                    <label class="form-label">Soru</label>
                                                                    <input type="text" class="form-control faq-question" value="{{ $faq['question'] }}" placeholder="Soru buraya">
                                                                </div>
                                                                <div>
                                                                    <label class="form-label">Cevap</label>
                                                                    <textarea class="form-control faq-answer" rows="2">{{ $faq['answer'] }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-1">
                                                                <label class="form-label">&nbsp;</label>
                                                                <button type="button" class="btn btn-danger btn-sm w-100 remove-faq">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-primary" id="addFaq">
                                        <i class="fas fa-plus me-1"></i> Soru Ekle
                                    </button>
                                </div>

                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Kaydet
                                </button>
                                <a href="{{ url('/entegrasyonlar') }}" target="_blank" class="btn btn-info">
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

    // ========== MARQUEE LOGOS ==========
    $('#addMarqueeLogo').on('click', function() {
        const html = `
            <div class="card mb-2 marquee-logo-item">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <input type="file" class="form-control form-control-sm marquee-logo-file" accept="image/*">
                            <input type="hidden" class="marquee-logo-current" value="">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">İsim</label>
                            <input type="text" class="form-control marquee-logo-name" placeholder="NetGSM">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kategori</label>
                            <input type="text" class="form-control marquee-logo-category" placeholder="SMS">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-sm w-100 remove-marquee-logo">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#marqueeLogosContainer').append(html);
    });

    $(document).on('click', '.remove-marquee-logo', function() {
        $(this).closest('.marquee-logo-item').remove();
    });

    // ========== CATEGORIES ==========
    $('#addCategory').on('click', function() {
        const categoryCount = $('.category-item').length + 1;
        const html = `
            <div class="card mb-4 category-item">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-white">Kategori ${categoryCount}</h6>
                    <button type="button" class="btn btn-danger btn-sm remove-category">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Kategori Başlığı</label>
                            <input type="text" class="form-control category-title" placeholder="SMS Entegrasyonları">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Alt Başlık</label>
                            <input type="text" class="form-control category-subtitle" placeholder="Kısa açıklama">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Arka Plan Stili</label>
                            <select class="form-control category-bg-style">
                                <option value="white">Beyaz</option>
                                <option value="gray">Gri</option>
                            </select>
                        </div>
                    </div>

                    <h6 class="mt-3 mb-2">Entegrasyonlar</h6>
                    <div class="integrations-container">
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm add-integration">
                        <i class="fas fa-plus me-1"></i> Entegrasyon Ekle
                    </button>
                </div>
            </div>
        `;
        $('#categoriesContainer').append(html);
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

    // ========== INTEGRATIONS ==========
    $(document).on('click', '.add-integration', function() {
        const container = $(this).siblings('.integrations-container');
        const html = `
            <div class="card mb-3 integration-item">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <span>Yeni Entegrasyon</span>
                    <button type="button" class="btn btn-danger btn-sm remove-integration">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Logo</label>
                            <input type="file" class="form-control form-control-sm integration-logo-file" accept="image/*">
                            <input type="hidden" class="integration-logo-current" value="">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">İsim</label>
                            <input type="text" class="form-control integration-name" placeholder="NETGSM">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Kategori Etiketi</label>
                            <input type="text" class="form-control integration-category-tag" placeholder="SMS">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Kısa Açıklama</label>
                            <input type="text" class="form-control integration-description" placeholder="Kısa açıklama">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Detay Açıklama (Hover)</label>
                        <textarea class="form-control integration-detail" rows="2"></textarea>
                    </div>

                    <div>
                        <label class="form-label">Özellikler (Her satıra bir özellik)</label>
                        <textarea class="form-control integration-features" rows="3" placeholder="Toplu SMS gönderimi&#10;SMS şablonları"></textarea>
                    </div>
                </div>
            </div>
        `;
        container.append(html);
    });

    $(document).on('click', '.remove-integration', function() {
        $(this).closest('.integration-item').remove();
    });

    // Entegrasyon adı değişince header'ı güncelle
    $(document).on('input', '.integration-name', function() {
        const name = $(this).val() || 'Yeni Entegrasyon';
        $(this).closest('.integration-item').find('.card-header span').text(name);
    });

    // ========== FAQS ==========
    $('#addFaq').on('click', function() {
        const html = `
            <div class="card mb-2 faq-item">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-11">
                            <div class="mb-2">
                                <label class="form-label">Soru</label>
                                <input type="text" class="form-control faq-question" placeholder="Soru buraya">
                            </div>
                            <div>
                                <label class="form-label">Cevap</label>
                                <textarea class="form-control faq-answer" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-sm w-100 remove-faq">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#faqsContainer').append(html);
    });

    $(document).on('click', '.remove-faq', function() {
        $(this).closest('.faq-item').remove();
    });

    // ========== FORM SUBMIT ==========
    $('form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Page Header
        const pageHeader = {
            title: $('[name="header_title"]').val(),
            subtitle: $('[name="header_subtitle"]').val(),
            button_text: $('[name="header_button_text"]').val(),
            button_url: $('[name="header_button_url"]').val()
        };
        
        // Marquee Logos Topla
        const marqueeLogos = [];
        $('.marquee-logo-item').each(function(index) {
            const logo = {
                name: $(this).find('.marquee-logo-name').val(),
                category: $(this).find('.marquee-logo-category').val(),
                logo: $(this).find('.marquee-logo-current').val()
            };
            
            if(logo.name) {
                marqueeLogos.push(logo);
                
                // Logo dosyası varsa FormData'ya ekle
                const file = $(this).find('.marquee-logo-file')[0].files[0];
                if(file) {
                    formData.append('marquee_logo_files[' + index + ']', file);
                }
            }
        });
        
        // Categories Topla
        const categories = [];
        $('.category-item').each(function(catIndex) {
            const integrations = [];
            
            $(this).find('.integration-item').each(function(intIndex) {
                const featuresText = $(this).find('.integration-features').val();
                const features = featuresText.split('\n').map(f => f.trim()).filter(f => f.length > 0);
                
                const integration = {
                    name: $(this).find('.integration-name').val(),
                    logo: $(this).find('.integration-logo-current').val(),
                    category_tag: $(this).find('.integration-category-tag').val(),
                    description: $(this).find('.integration-description').val(),
                    detail: $(this).find('.integration-detail').val(),
                    features: features
                };
                
                if(integration.name) {
                    integrations.push(integration);
                    
                    // Logo dosyası varsa FormData'ya ekle
                    const file = $(this).find('.integration-logo-file')[0].files[0];
                    if(file) {
                        formData.append('integration_logo_files[' + catIndex + '][' + intIndex + ']', file);
                    }
                }
            });
            
            const category = {
                title: $(this).find('.category-title').val(),
                subtitle: $(this).find('.category-subtitle').val(),
                bg_style: $(this).find('.category-bg-style').val(),
                integrations: integrations
            };
            
            if(category.title) {
                categories.push(category);
            }
        });
        
        // FAQs Topla
        const faqs = [];
        $('.faq-item').each(function() {
            const faq = {
                question: $(this).find('.faq-question').val(),
                answer: $(this).find('.faq-answer').val()
            };
            if(faq.question && faq.answer) {
                faqs.push(faq);
            }
        });
        
        // JSON Content Oluştur
        const content = {
            page_header: pageHeader,
            marquee_logos: marqueeLogos,
            categories: categories,
            faqs: faqs
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
                toastr.success('Entegrasyonlar içeriği güncellendi!');
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