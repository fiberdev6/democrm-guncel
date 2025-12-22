@extends('frontend.main_master')
@section('title', $sector['title'] . ' - Serbis')
@section('main')

<!-- Hero Section -->
<section class="sector-detail-hero">
    <div class="sector-detail-overlay"></div>
    <img src="{{ asset($sector['hero_image']) }}" 
         alt="{{ $sector['title'] }}" 
         class="sector-detail-bg" 
         onerror="this.src='https://via.placeholder.com/1920x600/49657B/ffffff?text={{ urlencode($sector['title']) }}'">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="sector-detail-hero-content">
                    <div class="sector-hero-icon">
                        <i class="{{ $sector['icon'] }}"></i>
                    </div>
                    <h1 class="sector-detail-title">{{ $sector['title'] }}</h1>
                    <p class="sector-detail-subtitle">{{ $sector['description'] }}</p>
                    <div class="sector-hero-buttons">
                        <a href="{{ url('/kullanici-girisi') }}"  class="btn btn-hero-primary" target="_blank">
                            <i class="fas fa-phone"></i> İletişime Geç
                        </a>
                        <a href="#hizmetler" class="btn btn-hero-secondary">
                            <i class="fas fa-info-circle"></i> Detaylı Bilgi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
@if(isset($sector['stats']) && count($sector['stats']) > 0)
<section class="sector-stats-section">
    <div class="container">
        <div class="row">
            @foreach($sector['stats'] as $stat)
            <div class="col-md-3 col-6 mb-4 mb-md-0">
                <div class="stat-item">
                    <div class="stat-number">{{ $stat['number'] }}</div>
                    <div class="stat-label">{{ $stat['label'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Features Section -->
@if(isset($sector['features']['items']) && count($sector['features']['items']) > 0)
<section class="sector-features-section section">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">{{ $sector['features']['badge'] ?? 'ÖZELLİKLER' }}</span>
            <h2 class="section-title">
                {{ $sector['features']['title'] ?? 'Neden' }} 
                <span class="accent">{{ $sector['features']['title_highlight'] ?? 'Bizi Seçmelisiniz?' }}</span>
            </h2>
        </div>
        <div class="row g-4">
            @foreach($sector['features']['items'] as $feature)
            <div class="col-lg-3 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="{{ $feature['icon'] }}"></i>
                    </div>
                    <h3 class="feature-title">{{ $feature['title'] }}</h3>
                    <p class="feature-description">{{ $feature['description'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Services Section -->
@if((isset($sector['services_section']['services']['items']) && count($sector['services_section']['services']['items']) > 0) || (isset($sector['services_section']['benefits']['items']) && count($sector['services_section']['benefits']['items']) > 0))
<section id="hizmetler" class="sector-services-section section">
    <div class="container">
        <div class="row">
            @if(isset($sector['services_section']['services']['items']) && count($sector['services_section']['services']['items']) > 0)
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="section-header text-start">
                    <span class="section-badge">{{ $sector['services_section']['services']['badge'] ?? 'HİZMETLERİMİZ' }}</span>
                    <h2 class="section-title">
                        {{ $sector['services_section']['services']['title'] ?? 'Sunduğumuz' }} 
                        <span class="accent">{{ $sector['services_section']['services']['title_highlight'] ?? 'Hizmetler' }}</span>
                    </h2>
                    <p class="section-subtitle text-start">
                        {{ $sector['services_section']['services']['subtitle'] ?? $sector['title'] . ' alanında geniş hizmet yelpazesi ile yanınızdayız.' }}
                    </p>
                </div>
                <ul class="services-list">
                    @foreach($sector['services_section']['services']['items'] as $service)
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <span>{{ $service }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            @if(isset($sector['services_section']['benefits']['items']) && count($sector['services_section']['benefits']['items']) > 0)
            <div class="col-lg-6">
                <div class="section-header text-start">
                    <span class="section-badge">{{ $sector['services_section']['benefits']['badge'] ?? 'AVANTAJLAR' }}</span>
                    <h2 class="section-title">
                        {{ $sector['services_section']['benefits']['title'] ?? 'Bizimle Çalışmanın' }} 
                        <span class="accent">{{ $sector['services_section']['benefits']['title_highlight'] ?? 'Avantajları' }}</span>
                    </h2>
                    <p class="section-subtitle text-start">
                        {{ $sector['services_section']['benefits']['subtitle'] ?? 'Müşteri memnuniyeti odaklı hizmet anlayışımızla fark yaratıyoruz.' }}
                    </p>
                </div>
                <ul class="benefits-list">
                    @foreach($sector['services_section']['benefits']['items'] as $benefit)
                    <li>
                        <i class="fas fa-star"></i>
                        <span>{{ $benefit }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
</section>
@endif

<!-- Process Section -->
@if(isset($sector['process']['steps']) && count($sector['process']['steps']) > 0)
<section class="sector-process-section section">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">{{ $sector['process']['badge'] ?? 'İŞ AKIŞI' }}</span>
            <h2 class="section-title">
                {{ $sector['process']['title'] ?? 'Sistem Nasıl' }} 
                <span class="accent">{{ $sector['process']['title_highlight'] ?? 'İşler?' }}</span>
            </h2>
            <p class="section-subtitle">
                {{ $sector['process']['subtitle'] ?? 'Servis operasyonlarınızı dijitalleştirerek adım adım mükemmel yönetim' }}
            </p>
        </div>

        <div class="row g-4">
            @foreach($sector['process']['steps'] as $step)
            <div class="col-lg-3 col-md-6">
                <div class="process-card">
                    <div class="process-number">{{ $step['number'] }}</div>
                    <div class="process-icon">
                        <i class="{{ $step['icon'] }}"></i>
                    </div>
                    <h3 class="process-title">{{ $step['title'] }}</h3>
                    <p class="process-description">{{ $step['description'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Other Sectors - DYNAMIC -->
@php
    // Diğer sektörleri al
    $allSectorsContent = \App\Models\HomepageContent::where('section', 'sectors_content')->first();
    $otherSectors = [];
    
    if($allSectorsContent && isset($allSectorsContent->content['sectors'])) {
        // Mevcut sektörü hariç tut, maksimum 4 sektör göster
        $currentSlug = request()->route('slug');
        $otherSectors = collect($allSectorsContent->content['sectors'])
            ->reject(function($s) use ($currentSlug) {
                return $s['slug'] == $currentSlug;
            })
            ->take(4)
            ->toArray();
    }
@endphp

@if(count($otherSectors) > 0)
<section class="other-sectors-section section">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">{{ $sector['other_sectors']['badge'] ?? 'DİĞER SEKTÖRLER' }}</span>
            <h2 class="section-title">
                {{ $sector['other_sectors']['title'] ?? 'Diğer' }} 
                <span class="accent">{{ $sector['other_sectors']['title_highlight'] ?? 'Hizmetlerimiz' }}</span>
            </h2>
        </div>
        <div class="row g-4">
            @foreach($otherSectors as $otherSector)
            <div class="col-lg-3 col-md-6">
                <a href="{{ route('sector.detail', $otherSector['slug']) }}" class="other-sector-card photo-card">
                    <img src="{{ asset($otherSector['image']) }}" 
                         alt="{{ $otherSector['title'] }}" 
                         class="sector-bg-img"
                         onerror="this.src='https://via.placeholder.com/400x300/49657B/ffffff?text={{ urlencode($otherSector['title']) }}'">
                    <div class="sector-overlay"></div>
                    <div class="sector-card-content">
                        <h4>{{ $otherSector['title'] }}</h4>
                        <span class="btn-arrow"><i class="fas fa-arrow-right"></i></span>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- FAQ Section -->
@if(isset($sector['faqs']['items']) && count($sector['faqs']['items']) > 0)
<section class="faq-section section">
    <div class="container">
        <div class="section-header text-center mb-5">
            <span class="section-badge">{{ $sector['faqs']['badge'] ?? 'MERAK EDİLENLER' }}</span>
            <h2 class="section-title">
                {{ $sector['faqs']['title'] ?? 'Sıkça Sorulan' }} 
                <span class="accent">{{ $sector['faqs']['title_highlight'] ?? 'Sorular' }}</span>
            </h2>
            <p class="section-subtitle mt-3">
                {{ str_replace('{sector_title}', $sector['title'], $sector['faqs']['subtitle'] ?? '{sector_title} hakkında merak ettiğiniz soruların cevapları') }}
            </p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    @foreach($sector['faqs']['items'] as $index => $faq)
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

@endsection