@extends('frontend.main_master')

@section('title', $feature['title'] . ' - Serbis')

@section('main')

<!-- Hero Section -->
<section class="feature-detail-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="feature-detail-title">{{ $feature['title'] }}</h1>
                <p class="feature-detail-subtitle">{{ $feature['subtitle'] }}</p>
                <p class="feature-detail-description">{{ $feature['description'] }}</p>
                <div class="feature-hero-buttons">
                    <button class="btn btn-hero-primary" onclick="window.open('{{ url('/kullanici-girisi')}}', '_blank')">
                       Ücretsiz Deneyin
                    </button>
                    <a href="#detaylar" class="btn btn-hero-secondary">
                        Detaylı Bilgi
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="feature-hero-image">
                    <img src="{{ asset($feature['hero_image']) }}" 
                         alt="{{ $feature['title'] }}" 
                         onerror="this.src='https://via.placeholder.com/600x400/49657B/ffffff?text={{ urlencode($feature['title']) }}'">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Benefits Section -->
@if(isset($feature['benefits']) && count($feature['benefits']) > 0)
<section id="detaylar" class="feature-benefits-section section">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">AVANTAJLAR</span>
            <h2 class="section-title">{{ $feature['title'] }} ile <span class="accent">Neler Yapabilirsiniz?</span></h2>
        </div>
        
        @foreach($feature['benefits'] as $index => $benefit)
        <div class="benefit-row {{ $index % 2 == 0 ? '' : 'reverse' }}">
            <div class="row align-items-center">
                <div class="col-lg-6 {{ $index % 2 == 0 ? '' : 'order-lg-2' }}">
                    <div class="benefit-content">
                        <div class="benefit-number">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</div>
                        <h3 class="benefit-title">{{ $benefit['title'] }}</h3>
                        <p class="benefit-description">{{ $benefit['description'] }}</p>
                    </div>
                </div>
                <div class="col-lg-6 {{ $index % 2 == 0 ? '' : 'order-lg-1' }}">
                    <div class="benefit-visual">
                        @if(isset($benefit['mini_features']) && count($benefit['mini_features']) > 0)
                        <div class="benefit-features-mini">
                            @foreach($benefit['mini_features'] as $mini)
                            <div class="mini-feature-card">
                                <i class="{{ $mini['icon'] }}"></i>
                                <span>{{ $mini['label'] }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif

<!-- Features List Section -->
@if((isset($feature['features_list']) && count($feature['features_list']) > 0) || (isset($feature['stats']) && count($feature['stats']) > 0))
<section class="feature-list-section section">
    <div class="container">
        <div class="row">
            @if(isset($feature['features_list']) && count($feature['features_list']) > 0)
            <div class="col-lg-6">
                <div class="section-header text-start">
                    <span class="section-badge">ÖZELLİKLER</span>
                </div>
                <div class="features-checklist">
                    @foreach($feature['features_list'] as $item)
                    <div class="checklist-item">
                        <div class="check-box"></div>
                        <span>{{ $item }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            @if(isset($feature['stats']) && count($feature['stats']) > 0)
            <div class="col-lg-6">
                <div class="features-stats-box">
                    <h3>Kullanım İstatistikleri</h3>
                    <p class="stats-description">{{ $feature['title'] }} modülünün gerçek performans verileri</p>
                    <div class="stats-grid">
                        @foreach($feature['stats'] as $stat)
                        <div class="stat-card">
                            <div class="stat-number">{{ $stat['number'] }}</div>
                            <div class="stat-label">{{ $stat['label'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</section>
@endif

<!-- FAQ Section -->
@if(isset($feature['faqs']) && count($feature['faqs']) > 0)
<section class="faq-section section">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-badge">MERAK EDİLENLER</span>
            <h2 class="section-title">Sıkça Sorulan <span class="accent">Sorular</span></h2>
            <p class="section-subtitle mt-3">
                {{ $feature['title'] }} modülü hakkında merak ettiğiniz soruların cevapları
            </p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    @foreach($feature['faqs'] as $index => $faq)
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button {{ $index != 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faq{{ $index }}">
                                {{ $faq['question'] }}
                            </button>
                        </h2>
                        <div id="faq{{ $index }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" data-bs-parent="#faqAccordion">
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
        <h2 class="cta-title">{{ $feature['cta']['title'] ?? $feature['title'] . ' Modülünü Hemen Deneyin!' }}</h2>
        <p class="cta-description">
            {{ $feature['cta']['description'] ?? '14 gün ücretsiz deneme ile tüm özelliklere anında erişin. Kredi kartı gerektirmez.' }}
        </p>
        <button class="btn btn-cta" onclick="window.open('{{ url($feature['cta']['button_url'] ?? '/kullanici-girisi') }}', '_blank')">
            {{ $feature['cta']['button_text'] ?? 'Hemen Ücretsiz Başlayın' }}
        </button>
    </div>
</section>

@endsection