@extends('frontend.main_master')

@section('title', 'Sektörler - Serbis')

@section('main')

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="page-title">
                    {{ $sectorsContent['page_header']['title'] ?? 'Hizmet Verdiğimiz' }} 
                    <span class="accent">{{ $sectorsContent['page_header']['title_highlight'] ?? 'Sektörler' }}</span>
                </h1>
                <p class="page-subtitle">
                    {{ $sectorsContent['page_header']['subtitle'] ?? 'Farklı sektörlerdeki teknik servis işletmelerinin ihtiyaçlarına özel çözümler sunuyoruz' }}
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Sectors Grid -->
<section class="sectors-grid-section section">
    <div class="container">
        <div class="row g-4">
            @if(isset($sectorsContent['sectors']))
                @foreach($sectorsContent['sectors'] as $sector)
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="{{ route('sector.detail', $sector['slug']) }}" style="text-decoration: none;">
                        <div class="sector-card-detailed">
                            <div class="sector-image">
                                <img src="{{ asset($sector['image']) }}" 
                                     alt="{{ $sector['title'] }}" 
                                     onerror="this.src='https://via.placeholder.com/400x300/49657B/ffffff?text={{ urlencode($sector['title']) }}'">
                                <div class="sector-overlay">
                                    <i class="{{ $sector['icon'] }}"></i>
                                </div>
                            </div>
                            <div class="sector-content">
                                <h3 class="sector-card-title">{{ $sector['title'] }}</h3>
                                <p class="sector-card-description">{{ $sector['short_description'] }}</p>
                                <ul class="sector-features">
                                    @foreach($sector['features'] as $feature)
                                    <li><i class="fas fa-check"></i> {{ $feature }}</li>
                                    @endforeach
                                </ul>
                                <span class="btn-sector-detail">Detaylı Bilgi <i class="fas fa-arrow-right"></i></span>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            @endif
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <h2 class="cta-title">{{ $sectorsContent['cta']['title'] ?? '14 Gün Ücretsiz Deneyin!' }}</h2>
        <p class="cta-description">
            {{ $sectorsContent['cta']['description'] ?? 'Sektörünüze özel Serbis takip programını keşfedin.' }}
        </p>
        <button class="btn btn-cta" onclick="window.open('{{ url($sectorsContent['cta']['button_url'] ?? '/kullanici-girisi') }}', '_blank')">
            {{ $sectorsContent['cta']['button_text'] ?? 'Hemen Ücretsiz Başla' }}
        </button>
    </div>
</section>

@endsection