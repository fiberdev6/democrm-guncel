@extends('frontend.main_master')

@section('title', 'Özellikler - Serbis')

@section('main')

<!-- Page Header -->
<section class="features-page-header" style="padding-top: 50px;">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="features-main-title">
                    {{ $featuresContent['page_header']['title'] ?? 'Serbis Özellikleri' }}
                </h1>
                <p class="features-main-subtitle">
                    {{ $featuresContent['page_header']['subtitle'] ?? 'Teknik servis işletmenizi büyütmek için ihtiyacınız olan tüm özellikleri keşfedin' }}
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Features Categories -->
<section class="features-categories-section">
    <div class="container">
        
        @if(isset($featuresContent['categories']))
            @foreach($featuresContent['categories'] as $category)
            <!-- {{ $category['title'] }} -->
            <div class="feature-category-section">
                <div class="text-center mb-5">
                    <h2 class="feature-section-title">{{ $category['title'] }}</h2>
                    <p class="feature-section-subtitle">{{ $category['subtitle'] }}</p>
                </div>
                
                <div class="row g-4">
                    @if(isset($category['items']))
                        @foreach($category['items'] as $item)
                        <div class="col-md-6">
                            <div class="feature-box">
                                <div class="feature-box-icon {{ $item['color'] }}">
                                    <i class="{{ $item['icon'] }}"></i>
                                </div>
                                <div class="feature-box-content">
                                    <h4>{{ $item['title'] }}</h4>
                                    <p>{{ $item['description'] }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
            @endforeach
        @endif

    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <h2 class="cta-title">{{ $featuresContent['cta']['title'] ?? 'Tüm Özellikleri Ücretsiz Deneyin!' }}</h2>
        <p class="cta-description">
            {{ $featuresContent['cta']['description'] ?? '14 gün boyunca kredi kartı gerektirmeden tüm özelliklere erişin' }}
        </p>
        <button class="btn btn-cta" onclick="window.open('{{ url($featuresContent['cta']['button_url'] ?? '/kullanici-girisi') }}', '_blank')">
            {{ $featuresContent['cta']['button_text'] ?? 'Hemen Başlayın' }}
        </button>
    </div>
</section>

@endsection