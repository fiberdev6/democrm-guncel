@extends('frontend.main_master')

@section('title', 'Hakkımızda - Serbis')

@section('main')

<!-- Hero Section -->
<section class="about-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="about-hero-content">
                    <div class="about-badge">
                        <i class="fas fa-heart"></i>
                        {{ $aboutContent['hero']['badge'] ?? 'Türkiye\'nin Teknik Servis Yazılımı' }}
                    </div>
                    <h1 class="about-hero-title">
                        {{ $aboutContent['hero']['title'] ?? 'Teknik Servislerin' }} 
                        <span>{{ $aboutContent['hero']['title_highlight'] ?? 'Dijital Dönüşüm' }}</span> 
                        {{ $aboutContent['hero']['title_suffix'] ?? 'Ortağı' }}
                    </h1>
                    <p class="about-hero-description">
                        {{ $aboutContent['hero']['description'] ?? 'Serbis olarak, teknik servis sektörünün dijitalleşme ihtiyacını yakından tanıyoruz.' }}
                    </p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about-hero-image">
                    <img src="{{ asset($aboutContent['hero']['image'] ?? 'frontend/img/about/hakkimizda1.jpeg') }}" 
                         alt="Serbis Ekibi" 
                         onerror="this.src='https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=600&h=400&fit=crop'">
                    
                    @if(isset($aboutContent['hero']['stats']))
                        @foreach($aboutContent['hero']['stats'] as $index => $stat)
                            <div class="floating-stat stat-{{ $index + 1 }}">
                                <div class="stat-icon {{ $stat['color'] ?? '' }}">
                                    <i class="{{ $stat['icon'] }}"></i>
                                </div>
                                <div>
                                    <div class="stat-number">{{ $stat['number'] }}</div>
                                    <div class="stat-label">{{ $stat['label'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision -->
<section class="mission-vision-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="mv-card">
                    <div class="mv-icon">
                        <i class="{{ $aboutContent['mission']['icon'] ?? 'fas fa-bullseye' }}"></i>
                    </div>
                    <h3 class="mv-title">{{ $aboutContent['mission']['title'] ?? 'Misyonumuz' }}</h3>
                    <p class="mv-text">
                        {{ $aboutContent['mission']['text'] ?? 'Teknik servis firmalarının iş süreçlerini dijitalleştirerek...' }}
                    </p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="mv-card">
                    <div class="mv-icon orange">
                        <i class="{{ $aboutContent['vision']['icon'] ?? 'fas fa-eye' }}"></i>
                    </div>
                    <h3 class="mv-title">{{ $aboutContent['vision']['title'] ?? 'Vizyonumuz' }}</h3>
                    <p class="mv-text">
                        {{ $aboutContent['vision']['text'] ?? 'Türkiye\'nin lider teknik servis yönetim platformu olmak...' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Story Section -->
<section class="story-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 order-lg-2">
                <div class="story-image-container">
                    <img src="{{ asset($aboutContent['story']['image'] ?? 'frontend/img/about/hakkimizda2.jpeg') }}" 
                         alt="Hikayemiz" 
                         onerror="this.src='https://images.unsplash.com/photo-1553877522-43269d4ea984?w=600&h=450&fit=crop'">
                    <div class="story-highlight-box">
                        <div class="highlight-icon">
                            <i class="{{ $aboutContent['story']['highlight_icon'] ?? 'fas fa-lightbulb' }}"></i>
                        </div>
                        <div class="highlight-text">
                            {{ $aboutContent['story']['highlight_text'] ?? 'Sektörün ihtiyaçlarını bilen bir ekip tarafından geliştirildi' }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 order-lg-1">
                <div class="section-header text-start">
                    <h2 class="section-title">{{ $aboutContent['story']['title'] ?? 'Hikayemiz' }}</h2>
                </div>
                <div class="story-timeline">
                    @if(isset($aboutContent['story']['timeline']))
                        @foreach($aboutContent['story']['timeline'] as $item)
                            <div class="story-item">
                                <div class="story-year">{{ $item['year'] }}</div>
                                <h4 class="story-title">{{ $item['title'] }}</h4>
                                <p class="story-text">{{ $item['text'] }}</p>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="values-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">{{ $aboutContent['values']['title'] ?? 'Değerlerimiz' }}</h2>
            <p class="section-description">
                {{ $aboutContent['values']['description'] ?? 'Her kararımızda bizi yönlendiren temel ilkeler' }}
            </p>
        </div>
        
        <div class="row g-4">
            @if(isset($aboutContent['values']['items']))
                @foreach($aboutContent['values']['items'] as $value)
                    <div class="col-lg-3 col-md-6">
                        <div class="value-card">
                            <div class="value-icon {{ $value['color'] ?? '' }}">
                                <i class="{{ $value['icon'] }}"></i>
                            </div>
                            <h4 class="value-title">{{ $value['title'] }}</h4>
                            <p class="value-text">{{ $value['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="row align-items-center justify-content-center">
            @if(isset($aboutContent['stats']))
                @foreach($aboutContent['stats'] as $index => $stat)
                    <div class="col-6 col-md-3">
                        <div class="stat-box {{ !$loop->last ? 'with-divider' : '' }}">
                            <div class="stat-number">{{ $stat['number'] }}</div>
                            <div class="stat-label">{{ $stat['label'] }}</div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="team-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">{{ $aboutContent['team']['title'] ?? 'Ekibimiz' }}</h2>
            <p class="section-description">
                {{ $aboutContent['team']['description'] ?? 'Serbis\'in arkasındaki tutkulu ekip' }}
            </p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="team-intro">
                    <div class="team-intro-icon">
                        <i class="{{ $aboutContent['team']['intro_icon'] ?? 'fas fa-users-cog' }}"></i>
                    </div>
                    <h3>{{ $aboutContent['team']['intro_title'] ?? 'Deneyimli & Tutkulu Bir Ekip' }}</h3>
                    <p>
                        {{ $aboutContent['team']['intro_text'] ?? 'Serbis ekibi, teknik servis sektörünü yakından tanıyan yazılım geliştiricileri...' }}
                    </p>
                    <div class="team-tags">
                        @if(isset($aboutContent['team']['tags']))
                            @foreach($aboutContent['team']['tags'] as $tag)
                                <span class="team-tag">
                                    <i class="{{ $tag['icon'] }}"></i> {{ $tag['text'] }}
                                </span>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection