@extends('frontend.main_master')

@section('title', 'İletişim - Serbis')

@section('main')

<!-- Hero Section -->
<section class="contact-hero">
    <div class="container">
        <div class="contact-hero-content">
            <h1 class="contact-hero-title">{{ $contactContent['page_header']['title'] ?? 'İletişim' }}</h1>
            <p class="contact-hero-subtitle">
                {{ $contactContent['page_header']['subtitle'] ?? 'Sorularınız için bize ulaşın, size yardımcı olmaktan mutluluk duyarız.' }}
            </p>
            <div class="contact-breadcrumb">
                <a href="{{ route('home') }}">{{ $contactContent['page_header']['breadcrumb_home'] ?? 'Ana Sayfa' }}</a>
                <span>/</span>
                <span class="current">{{ $contactContent['page_header']['breadcrumb_current'] ?? 'İletişim' }}</span>
            </div>
        </div>
    </div>
</section>

<!-- Contact Cards -->
@if(isset($contactContent['contact_cards']) && count($contactContent['contact_cards']) > 0)
<section class="contact-cards-section">
    <div class="container">
        <div class="row g-4">
            @foreach($contactContent['contact_cards'] as $card)
            <div class="col-lg-4 col-md-6">
                <div class="contact-card-page">
                    <div class="contact-card-icon">
                        <i class="{{ $card['icon'] }}"></i>
                    </div>
                    <h3 class="contact-card-title">{{ $card['title'] }}</h3>
                    <p class="contact-card-text">
                        @if($card['link'])
                            <a href="{{ $card['link'] }}">{{ $card['text'] }}</a>
                        @else
                            {{ $card['text'] }}
                        @endif
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Main Contact Section -->
<section class="contact-main-section" id="iletisim">
    <div class="container">
        <!-- Success Message -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Unified Contact Wrapper -->
        <div class="contact-split-wrapper">
            <div class="row g-0">
                <!-- Left Side: Dark Info Panel -->
                <div class="col-lg-5">
                    <div class="contact-left-panel">
                        <div class="panel-content">
                            <h2 class="panel-title">
                                {{ $contactContent['left_panel']['title'] ?? 'Serbis CRM ile' }} <br>
                                <span>{{ $contactContent['left_panel']['title_highlight'] ?? 'İşinizi Büyütün' }}</span>
                            </h2>
                            <p class="panel-desc">
                                {{ $contactContent['left_panel']['description'] ?? 'Teknik servis süreçlerinizi dijitalleştirmek için formu doldurun. Uzman ekibimiz size özel çözüm önerileriyle en kısa sürede dönüş yapsın.' }}
                            </p>
                            
                            <!-- Modern Feature Grid -->
                            @if(isset($contactContent['left_panel']['features']) && count($contactContent['left_panel']['features']) > 0)
                            <div class="panel-features">
                                @foreach($contactContent['left_panel']['features'] as $feature)
                                <div class="p-feature-item">
                                    <i class="{{ $feature['icon'] }}"></i>
                                    <span>{{ $feature['text'] }}</span>
                                </div>
                                @endforeach
                            </div>
                            @endif

                            <!-- App Download Minimal -->
                            <div class="panel-apps">
                                <p class="apps-label">{{ $contactContent['left_panel']['apps_label'] ?? 'Mobil Uygulamamızı İndirin:' }}</p>
                                <div class="apps-buttons">
                                    <a href="{{ $contactContent['left_panel']['google_play_link'] ?? '#' }}" class="app-btn-light">
                                        <i class="fa-brands fa-google-play"></i>
                                    </a>
                                    <a href="{{ $contactContent['left_panel']['app_store_link'] ?? '#' }}" class="app-btn-light">
                                        <i class="fa-brands fa-apple"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Background Pattern Decoration -->
                        <div class="panel-bg-pattern"></div>
                    </div>
                </div>
                
                <!-- Right Side: Clean Form -->
                <div class="col-lg-7">
                    <div class="contact-right-panel">
                        <div class="form-header-clean">
                            <h3>{{ $contactContent['form_section']['title'] ?? 'Bize Ulaşın' }}</h3>
                            <p>{{ $contactContent['form_section']['subtitle'] ?? 'Aşağıdaki formu doldurarak bize mesaj gönderin.' }}</p>
                        </div>
                        
                        <form action="{{ route('contact.submit') }}" method="POST" class="modern-form">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="input-group-modern">
                                        <label>{{ $contactContent['form_section']['name_label'] ?? 'Ad-Soyad' }}</label>
                                        <input type="text" name="name" required placeholder="{{ $contactContent['form_section']['name_placeholder'] ?? 'Adınız Soyadınız' }}" value="{{ old('name') }}">
                                        @error('name')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group-modern">
                                        <label>{{ $contactContent['form_section']['email_label'] ?? 'E-posta' }}</label>
                                        <input type="email" name="email" required placeholder="{{ $contactContent['form_section']['email_placeholder'] ?? 'ornek@email.com' }}" value="{{ old('email') }}">
                                        @error('email')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="input-group-modern">
                                        <label>{{ $contactContent['form_section']['phone_label'] ?? 'Telefon' }}</label>
                                        <input type="tel" name="phone" placeholder="{{ $contactContent['form_section']['phone_placeholder'] ?? '0555 555 55 55' }}" value="{{ old('phone') }}">
                                        @error('phone')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="input-group-modern">
                                        <label>{{ $contactContent['form_section']['message_label'] ?? 'Mesajınız' }}</label>
                                        <textarea name="message" required placeholder="{{ $contactContent['form_section']['message_placeholder'] ?? 'Size nasıl yardımcı olabiliriz?' }}">{{ old('message') }}</textarea>
                                        @error('message')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="submit-btn-modern">
                                        <span>{{ $contactContent['form_section']['button_text'] ?? 'Mesajı Gönder' }}</span>
                                        <i class="fas fa-arrow-right"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    $(document).ready(function() {
    // Telefon numarası maskesi
    $('input[name="phone"]').mask('0000 000 00 00', {
        placeholder: "0555 555 55 55"
    });
});
</script>
@endsection