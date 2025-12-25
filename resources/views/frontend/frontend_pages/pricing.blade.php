@extends('frontend.main_master')

@section('title', 'Fiyatlar - Serbis')

@section('main')

<!-- Hero Section -->
<section class="pricing-hero">
    <div class="container">
        <div class="pricing-hero-content">
            <div class="pricing-badge">
                <i class="{{ $pricingContent['page_header']['badge_icon'] ?? 'fas fa-tag' }}"></i>
                {{ $pricingContent['page_header']['badge_text'] ?? '14 Gün Ücretsiz Deneme' }}
            </div>
            <h1 class="pricing-hero-title">
                {{ $pricingContent['page_header']['title'] ?? 'Size Uygun' }} 
                <span>{{ $pricingContent['page_header']['title_highlight'] ?? 'Planı' }}</span> 
                {{ $pricingContent['page_header']['title_suffix'] ?? 'Seçin' }}
            </h1>
            <p class="pricing-hero-description">
                {{ $pricingContent['page_header']['subtitle'] ?? 'Her ölçekteki teknik servis için uygun fiyatlı çözümler. Kredi kartı gerektirmeden hemen başlayın, işinizi büyütün.' }}
            </p>
            @if(isset($pricingContent['page_header']['hero_features']) && count($pricingContent['page_header']['hero_features']) > 0)
            <div class="pricing-hero-features">
                @foreach($pricingContent['page_header']['hero_features'] as $feature)
                <div class="hero-feature">
                    <i class="{{ $feature['icon'] }}"></i>
                    <span>{{ $feature['text'] }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section class="pricing-section">
    <div class="container">
        @if(isset($pricingContent['pricing_plans']) && count($pricingContent['pricing_plans']) > 0)
        <div class="row g-4 justify-content-center">
            
            @foreach($pricingContent['pricing_plans'] as $index => $plan)
            <div class="col-lg-4 col-md-6">
                <div class="pricing-card">
                    @if($plan['is_popular'] ?? false)
                    <span class="popular-badge">Önerilen</span>
                    @endif
                    
                    <!-- Plan Icon -->
                    <div class="plan-icon">
                        <i class="{{ $plan['icon'] }}"></i>
                    </div>
                    
                    <!-- Plan Name -->
                    <h3 class="plan-name">{{ $plan['name'] }}</h3>
                    
                    <!-- Short Description -->
                    <p class="plan-short-desc">{{ $plan['short_description'] }}</p>
                    
                    <!-- Price -->
                    <div class="plan-price">
                        <span class="currency">₺</span>
                        <span class="amount">{{ number_format($plan['price'], 0, ',', '.') }}</span>
                        <span class="period">/yıllık</span>
                        @if(isset($plan['discount_badge']) && $plan['discount_badge'])
                        <span class="discount-badge">{{ $plan['discount_badge'] }}</span>
                        @endif
                    </div>
                    
                    <!-- Device & User Meta -->
                    <div class="plan-meta">
                        <div class="plan-devices">
                            <!-- Mobile -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <!-- Tablet -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 1v22m-4-18h8a2 2 0 012 2v14a2 2 0 01-2 2h-8a2 2 0 01-2-2V5a2 2 0 012-2z" />
                            </svg>
                            <!-- Desktop -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="plan-users">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>{{ $plan['users'] }} Kullanıcı</span>
                        </div>
                    </div>
                    
                    <!-- Buy Button - GÜNCELLENDİ -->
                    <a href="{{ route('select.plan', $index) }}" class="btn-buy">Satın Al</a>
                    
                    <!-- Divider -->
                    <hr class="features-divider">
                    
                    <!-- Features Header -->
                    <p class="features-header-text">{{ $plan['description'] }}</p>
                    
                    <!-- Features List -->
                    @if(isset($plan['features']) && count($plan['features']) > 0)
                    <ul class="features-list">
                        @foreach($plan['features'] as $feature)
                        <li>
                            <span class="check-icon"></span>
                            <span>{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
            @endforeach
            
        </div>
        @endif
    </div>
</section>
<!-- FAQ Section -->
@if(isset($pricingContent['faqs']) && count($pricingContent['faqs']) > 0)
<section class="pricing-faq">
    <div class="container">
        <div class="text-center mb-5">
            <span class="pricing-badge mb-3">
                <i class="fas fa-question-circle"></i>
                MERAK EDİLENLER
            </span>
            <h2 class="faq-title">Sıkça Sorulan <span style="color: var(--primary-blue);">Sorular</span></h2>
            <p class="faq-subtitle">Merak ettiğiniz soruların cevaplarını burada bulabilirsiniz</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="pricingFaqAccordion">
                    
                    @foreach($pricingContent['faqs'] as $index => $faq)
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button {{ $index != 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#pricingFaq{{ $index }}">
                                {{ $faq['question'] }}
                            </button>
                        </h2>
                        <div id="pricingFaq{{ $index }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" data-bs-parent="#pricingFaqAccordion">
                            <div class="accordion-body">
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

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <h2 class="cta-title">{{ $pricingContent['cta']['title'] ?? '14 Gün Ücretsiz Deneyin!' }}</h2>
        <p class="cta-description">
            {{ $pricingContent['cta']['description'] ?? 'Kredi kartı gerektirmez. Anında başlayın, tüm özellikleri keşfedin.' }}
        </p>
        <button class="btn btn-cta" onclick="window.open('{{ url($pricingContent['cta']['button_url'] ?? '/kullanici-girisi')}}', '_blank')">
            {{ $pricingContent['cta']['button_text'] ?? 'Hemen Ücretsiz Başla' }}
        </button>
    </div>
</section>

@endsection