@extends('frontend.main_master')

@section('title', 'Entegrasyonlar - Serbis')

@section('main')

<!-- Hero Section -->
<section class="integrations-hero">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="hero-main-title">{{ $integrationsContent['page_header']['title'] ?? 'Serbis Entegrasyonları ile Tüm Süreçlerinizi Entegre Edin' }}</h1>
                <p class="hero-description">
                    {{ $integrationsContent['page_header']['subtitle'] ?? 'Serbis uygulama mağazasındaki uygulama ve entegrasyonlar ile teknik servis sitenizi çok yönlü hale getirin.' }}
                </p>
                <button class="btn btn-hero-cta" onclick="window.open('{{ url($integrationsContent['page_header']['button_url'] ?? '/kullanici-girisi')}}', '_blank')">
                    {{ $integrationsContent['page_header']['button_text'] ?? 'Deneme Hesabı Oluştur' }}
                </button>
            </div>
        </div>
        
        <!-- Featured Logos Slider -->
        @if(isset($integrationsContent['marquee_logos']) && count($integrationsContent['marquee_logos']) > 0)
        <div class="marquee-wrapper">
            <div class="marquee-content">
                <!-- 1. GRUP LOGOLAR -->
                @foreach($integrationsContent['marquee_logos'] as $logo)
                <div class="logo-item">
                    <img src="{{ asset($logo['logo']) }}" alt="{{ $logo['name'] }}" onerror="this.src='https://via.placeholder.com/100x50/49657B/ffffff?text={{ urlencode($logo['name']) }}'">
                    <span>{{ $logo['name'] }}</span>
                    <small>{{ $logo['category'] }}</small>
                </div>
                @endforeach

                <!-- 2. GRUP LOGOLAR (Sonsuz döngü için tekrar) -->
                @foreach($integrationsContent['marquee_logos'] as $logo)
                <div class="logo-item">
                    <img src="{{ asset($logo['logo']) }}" alt="{{ $logo['name'] }}" onerror="this.src='https://via.placeholder.com/100x50/49657B/ffffff?text={{ urlencode($logo['name']) }}'">
                    <span>{{ $logo['name'] }}</span>
                    <small>{{ $logo['category'] }}</small>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>

<!-- Integration Categories -->
@if(isset($integrationsContent['categories']) && count($integrationsContent['categories']) > 0)
    @foreach($integrationsContent['categories'] as $category)
    <section class="integration-category-section {{ $category['bg_style'] == 'gray' ? 'gray-bg' : '' }}">
        <div class="container">
            <div class="category-header-simple">
                <h2 class="category-title">{{ $category['title'] }}</h2>
                <p class="category-description">{{ $category['subtitle'] }}</p>
            </div>
            
            @if(isset($category['integrations']) && count($category['integrations']) > 0)
            <div class="row g-4 {{ count($category['integrations']) <= 3 ? 'justify-content-center' : '' }}">
                @foreach($category['integrations'] as $integration)
                <div class="col-lg-{{ count($category['integrations']) <= 3 ? '4' : '3' }} col-md-{{ count($category['integrations']) <= 3 ? '6' : '4' }} col-sm-6">
                    <div class="integration-card-soft">
                        <div class="integration-logo-soft">
                            <img src="{{ asset($integration['logo']) }}" 
                                 alt="{{ $integration['name'] }}"
                                 onerror="this.src='https://via.placeholder.com/150x80/49657B/ffffff?text={{ urlencode($integration['name']) }}'">
                        </div>
                        <h3 class="integration-name-soft">{{ $integration['name'] }}</h3>
                        <span class="integration-category-tag">{{ $integration['category_tag'] }}</span>
                        @if(count($category['integrations']) <= 3)
                        <p class="integration-description-soft">{{ $integration['description'] }}</p>
                        @endif
                        
                        <!-- Hover Overlay -->
                        <div class="card-overlay">
                            @if(count($category['integrations']) <= 3)
                            <h4 class="overlay-title">{{ $integration['name'] }}</h4>
                            @endif
                            <p class="overlay-description">{{ $integration['detail'] ?? $integration['description'] }}</p>
                            @if(isset($integration['features']) && count($integration['features']) > 0)
                            <ul class="overlay-features">
                                @foreach($integration['features'] as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            </ul>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </section>
    @endforeach
@endif

<!-- FAQ Section -->
@if(isset($integrationsContent['faqs']) && count($integrationsContent['faqs']) > 0)
<section class="modules-section section" style="padding: 60px 0; background-color: #fff;">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-badge" style="color: var(--primary); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; font-size: 0.85rem;">MERAK EDİLENLER</span>
            <h2 class="section-title mt-2" style="font-size: 2.5rem; font-weight: 700;">Sıkça Sorulan <span class="accent" style="color: var(--primary);">Sorular</span></h2>
            <p class="section-subtitle mt-3" style="color: #6c757d; max-width: 600px; margin-left: auto; margin-right: auto;">
                Entegrasyon süreçleri, kurulum ve teknik detaylar hakkında merak ettiğiniz soruların cevapları.
            </p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    @foreach($integrationsContent['faqs'] as $index => $faq)
                    <div class="accordion-item" style="border: 1px solid #e9ecef; border-radius: 12px; margin-bottom: 1rem; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                        <h2 class="accordion-header">
                            <button class="accordion-button {{ $index != 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faq{{ $index }}" 
                                style="background: white; color: #2d3436; font-weight: 600; padding: 1.5rem; border: none; box-shadow: none;">
                                {{ $faq['question'] }}
                            </button>
                        </h2>
                        <div id="faq{{ $index }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" data-bs-parent="#faqAccordion">
                            <div class="accordion-body" style="padding: 0 1.5rem 1.5rem 1.5rem; color: #636e72; line-height: 1.6;">
                                {{ $faq['answer'] }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endif

@endsection