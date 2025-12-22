@extends('frontend.secure.user_master')
@section('user')
<div class="page-content">
    <div class="container-fluid">
        
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Fiyatlandırma Sayfası Yönetimi</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('super.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Fiyatlandırma</li>
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
                                <a class="nav-link" data-bs-toggle="tab" href="#pricing-plans" role="tab">
                                    <i class="fas fa-tags me-1"></i> Fiyatlandırma Planları
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#faqs" role="tab">
                                    <i class="fas fa-question-circle me-1"></i> SSS
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#cta" role="tab">
                                    <i class="fas fa-bullhorn me-1"></i> CTA
                                </a>
                            </li>
                        </ul>

                        <form action="{{ route('super.admin.frontend.pricing-content.update') }}" method="POST">
                            @csrf
                            
                            <div class="tab-content p-3">
                                
                                <!-- PAGE HEADER TAB -->
                                <div class="tab-pane active" id="page-header" role="tabpanel">
                                    <h5 class="mb-3">Hero Bölümü</h5>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Badge İkonu</label>
                                            <input type="text" class="form-control" name="header_badge_icon" 
                                                   value="{{ $pricing->content['page_header']['badge_icon'] ?? 'fas fa-tag' }}" placeholder="fas fa-tag">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Badge Metni</label>
                                            <input type="text" class="form-control" name="header_badge_text" 
                                                   value="{{ $pricing->content['page_header']['badge_text'] ?? '14 Gün Ücretsiz Deneme' }}">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Başlık (Normal)</label>
                                            <input type="text" class="form-control" name="header_title" 
                                                   value="{{ $pricing->content['page_header']['title'] ?? 'Size Uygun' }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Başlık (Vurgulu)</label>
                                            <input type="text" class="form-control" name="header_title_highlight" 
                                                   value="{{ $pricing->content['page_header']['title_highlight'] ?? 'Planı' }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Başlık (Son Kelime)</label>
                                            <input type="text" class="form-control" name="header_title_suffix" 
                                                   value="{{ $pricing->content['page_header']['title_suffix'] ?? 'Seçin' }}">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Alt Başlık</label>
                                        <textarea class="form-control" name="header_subtitle" rows="2">{{ $pricing->content['page_header']['subtitle'] ?? 'Her ölçekteki teknik servis için uygun fiyatlı çözümler. Kredi kartı gerektirmeden hemen başlayın, işinizi büyütün.' }}</textarea>
                                    </div>

                                    <h6 class="mt-4 mb-3">Hero Özellikleri</h6>
                                    <div id="heroFeaturesContainer">
                                        @if(isset($pricing->content['page_header']['hero_features']))
                                            @foreach($pricing->content['page_header']['hero_features'] as $index => $feature)
                                                <div class="row mb-2 hero-feature-item">
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control hero-feature-icon" value="{{ $feature['icon'] }}" placeholder="fas fa-check-circle">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control hero-feature-text" value="{{ $feature['text'] }}" placeholder="Kurulum ücretsiz">
                                                    </div>
                                                    <div class="col-md-1">
                                                        <button type="button" class="btn btn-danger btn-sm w-100 remove-hero-feature">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-secondary btn-sm" id="addHeroFeature">
                                        <i class="fas fa-plus me-1"></i> Özellik Ekle
                                    </button>
                                </div>

                                <!-- PRICING PLANS TAB -->
                                <div class="tab-pane" id="pricing-plans" role="tabpanel">
                                    <h5 class="mb-3">Fiyatlandırma Planları</h5>
                                    
                                    <div id="plansContainer">
                                        @if(isset($pricing->content['pricing_plans']))
                                            @foreach($pricing->content['pricing_plans'] as $planIndex => $plan)
                                                <div class="card mb-4 plan-item">
                                                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                                        <h6 class="mb-0 text-white">Plan {{ $planIndex + 1 }}: {{ $plan['name'] }}</h6>
                                                        <button type="button" class="btn btn-danger btn-sm remove-plan">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row mb-3">
                                                            <div class="col-md-3">
                                                                <label class="form-label">Plan Adı</label>
                                                                <input type="text" class="form-control plan-name" value="{{ $plan['name'] }}" placeholder="Başlangıç">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label">İkon</label>
                                                                <input type="text" class="form-control plan-icon" value="{{ $plan['icon'] }}" placeholder="fas fa-mobile-alt">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label">Fiyat (₺/Yıllık)</label>
                                                                <input type="number" class="form-control plan-price" value="{{ $plan['price'] }}" placeholder="8400">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label">Kullanıcı Sayısı</label>
                                                                <input type="number" class="form-control plan-users" value="{{ $plan['users'] }}" placeholder="3">
                                                            </div>
                                                        </div>

                                                        <div class="row mb-3">
                                                            <div class="col-md-3">
                                                                <label class="form-label">Depolama</label>
                                                                <input type="text" class="form-control plan-storage" value="{{ $plan['storage'] }}" placeholder="2 GB">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label">İndirim Badge</label>
                                                                <input type="text" class="form-control plan-discount-badge" value="{{ $plan['discount_badge'] }}" placeholder="%30 Kazanın">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label">Önerilen Plan mı?</label>
                                                                <select class="form-control plan-is-popular">
                                                                    <option value="0" {{ ($plan['is_popular'] ?? false) ? '' : 'selected' }}>Hayır</option>
                                                                    <option value="1" {{ ($plan['is_popular'] ?? false) ? 'selected' : '' }}>Evet</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Kısa Açıklama</label>
                                                            <textarea class="form-control plan-short-description" rows="2">{{ $plan['short_description'] }}</textarea>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Açıklama</label>
                                                            <input type="text" class="form-control plan-description" value="{{ $plan['description'] }}" placeholder="Küçük işletmeler için temel özellikler">
                                                        </div>

                                                        <div>
                                                            <label class="form-label">Özellikler (Her satıra bir özellik)</label>
                                                            <textarea class="form-control plan-features" rows="8" placeholder="Max. 3 Kullanıcı&#10;2 GB Depolama Alanı">{{ isset($plan['features']) ? implode("\n", $plan['features']) : '' }}</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>

                                    <button type="button" class="btn btn-primary" id="addPlan">
                                        <i class="fas fa-plus me-1"></i> Yeni Plan Ekle
                                    </button>
                                </div>

                                <!-- FAQS TAB -->
                                <div class="tab-pane" id="faqs" role="tabpanel">
                                    <h5 class="mb-3">Sıkça Sorulan Sorular</h5>
                                    
                                    <div id="faqsContainer">
                                        @if(isset($pricing->content['faqs']))
                                            @foreach($pricing->content['faqs'] as $index => $faq)
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

                                <!-- CTA TAB -->
                                <div class="tab-pane" id="cta" role="tabpanel">
                                    <h5 class="mb-3">CTA (Call to Action) Bölümü</h5>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Başlık</label>
                                        <input type="text" class="form-control" name="cta_title" value="{{ $pricing->content['cta']['title'] ?? '14 Gün Ücretsiz Deneyin!' }}">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Açıklama</label>
                                        <textarea class="form-control" name="cta_description" rows="2">{{ $pricing->content['cta']['description'] ?? 'Kredi kartı gerektirmez. Anında başlayın, tüm özellikleri keşfedin.' }}</textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Buton Metni</label>
                                            <input type="text" class="form-control" name="cta_button_text" value="{{ $pricing->content['cta']['button_text'] ?? 'Hemen Ücretsiz Başla' }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Buton URL</label>
                                            <input type="text" class="form-control" name="cta_button_url" value="{{ $pricing->content['cta']['button_url'] ?? '/kullanici-girisi' }}">
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Kaydet 
                                </button>
                                <a href="{{ url('/fiyatlar') }}" target="_blank" class="btn btn-info">
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

    // ========== HERO FEATURES ==========
    $('#addHeroFeature').on('click', function() {
        const html = `
            <div class="row mb-2 hero-feature-item">
                <div class="col-md-5">
                    <input type="text" class="form-control hero-feature-icon" placeholder="fas fa-check-circle">
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control hero-feature-text" placeholder="Kurulum ücretsiz">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm w-100 remove-hero-feature">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        $('#heroFeaturesContainer').append(html);
    });

    $(document).on('click', '.remove-hero-feature', function() {
        $(this).closest('.hero-feature-item').remove();
    });

    // ========== PRICING PLANS ==========
    $('#addPlan').on('click', function() {
        const planCount = $('.plan-item').length + 1;
        const html = `
            <div class="card mb-4 plan-item">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-white">Plan ${planCount}: Yeni Plan</h6>
                    <button type="button" class="btn btn-danger btn-sm remove-plan">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Plan Adı</label>
                            <input type="text" class="form-control plan-name" placeholder="Başlangıç">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">İkon</label>
                            <input type="text" class="form-control plan-icon" placeholder="fas fa-mobile-alt">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fiyat (₺/Yıllık)</label>
                            <input type="number" class="form-control plan-price" placeholder="8400">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Kullanıcı Sayısı</label>
                            <input type="number" class="form-control plan-users" placeholder="3">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Depolama</label>
                            <input type="text" class="form-control plan-storage" placeholder="2 GB">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">İndirim Badge</label>
                            <input type="text" class="form-control plan-discount-badge" placeholder="%30 Kazanın">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Önerilen Plan mı?</label>
                            <select class="form-control plan-is-popular">
                                <option value="0" selected>Hayır</option>
                                <option value="1">Evet</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kısa Açıklama</label>
                        <textarea class="form-control plan-short-description" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Açıklama</label>
                        <input type="text" class="form-control plan-description" placeholder="Küçük işletmeler için temel özellikler">
                    </div>

                    <div>
                        <label class="form-label">Özellikler (Her satıra bir özellik)</label>
                        <textarea class="form-control plan-features" rows="8" placeholder="Max. 3 Kullanıcı&#10;2 GB Depolama Alanı"></textarea>
                    </div>
                </div>
            </div>
        `;
        $('#plansContainer').append(html);
    });

    $(document).on('click', '.remove-plan', function() {
        if (confirm('Bu planı silmek istediğinize emin misiniz?')) {
            $(this).closest('.plan-item').remove();
            // Numaraları yeniden düzenle
            $('.plan-item').each(function(index) {
                const planName = $(this).find('.plan-name').val() || 'Yeni Plan';
                $(this).find('.card-header h6').text('Plan ' + (index + 1) + ': ' + planName);
            });
        }
    });

    // Plan adı değişince header'ı güncelle
    $(document).on('input', '.plan-name', function() {
        const planName = $(this).val() || 'Yeni Plan';
        const planItem = $(this).closest('.plan-item');
        const planIndex = $('.plan-item').index(planItem) + 1;
        planItem.find('.card-header h6').text('Plan ' + planIndex + ': ' + planName);
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
        
        // Page Header - Hero Features
        const heroFeatures = [];
        $('.hero-feature-item').each(function() {
            const feature = {
                icon: $(this).find('.hero-feature-icon').val(),
                text: $(this).find('.hero-feature-text').val()
            };
            if(feature.icon && feature.text) {
                heroFeatures.push(feature);
            }
        });
        
        const pageHeader = {
            badge_icon: $('[name="header_badge_icon"]').val(),
            badge_text: $('[name="header_badge_text"]').val(),
            title: $('[name="header_title"]').val(),
            title_highlight: $('[name="header_title_highlight"]').val(),
            title_suffix: $('[name="header_title_suffix"]').val(),
            subtitle: $('[name="header_subtitle"]').val(),
            hero_features: heroFeatures
        };
        
        // Pricing Plans Topla
        const pricingPlans = [];
        $('.plan-item').each(function() {
            const featuresText = $(this).find('.plan-features').val();
            const features = featuresText.split('\n').map(f => f.trim()).filter(f => f.length > 0);
            
            const plan = {
                name: $(this).find('.plan-name').val(),
                icon: $(this).find('.plan-icon').val(),
                price: parseInt($(this).find('.plan-price').val()) || 0,
                users: parseInt($(this).find('.plan-users').val()) || 1,
                storage: $(this).find('.plan-storage').val(),
                short_description: $(this).find('.plan-short-description').val(),
                description: $(this).find('.plan-description').val(),
                discount_badge: $(this).find('.plan-discount-badge').val(),
                is_popular: $(this).find('.plan-is-popular').val() === '1',
                features: features
            };
            
            if(plan.name) {
                pricingPlans.push(plan);
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
            pricing_plans: pricingPlans,
            faqs: faqs,
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
                toastr.success('Fiyatlandırma içeriği güncellendi!');
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