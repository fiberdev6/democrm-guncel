
@extends('frontend.main_master')

@section('title', 'Serbis - Teknik Servis Yönetim Sistemi')

@section('main')

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <!-- Badge -->
                <div class="hero-badge">
                    <i class="fa-solid fa-heart" style="color:red;"></i>
                    <span>{{ $hero['badge'] ??  ''}}</span>
                </div>
                
                <h1 class="hero-title">
                    {{ $hero['title'] ?? 'Teknik Servisi' }} <span class="highlight">{{ $hero['highlight'] ?? 'Yeniden Tanımlayın' }}</span>
                </h1>
                <p class="hero-description-ana">
                    {{ $hero['description'] }}
                </p>
               <div class="hero-buttons">
                    <!-- Giriş Butonu (Değişmedi) -->
                    <a href="{{ url('/kullanici-girisi') }}" class="btn-hero-primary" target="_blank">
                        <i class="{{ $hero['primary_button_icon'] }}"></i> {{ $hero['primary_button_text'] }}
                    </a>
                    
                    <!-- Video Butonu (button olarak değiştirildi) -->
                    <button type="button" class="btn-hero-secondary" data-bs-toggle="modal" data-bs-target="#videoModal">
                        <i class="{{ $hero['secondary_button_icon'] }}"></i> {{ $hero['secondary_button_text'] }}
                    </button>
                </div>
                <div class="hero-features">
                    @foreach($hero['features'] as $feature)
                    <div class="hero-feature-ana">
                        <i class="{{ $feature['icon'] }}"></i>
                        <span>{{ $feature['text'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <div class="col-lg-6 mt-5 mt-lg-0">
                <div class="hero-mockup-container">
                    <!-- Browser Mockup -->
                    <div class="browser-mockup">
                        <div class="browser-header">
                            <div class="browser-dots">
                                <span class="dot"></span>
                                <span class="dot"></span>
                                <span class="dot"></span>
                            </div>
                            <div class="browser-url">serbis.com.tr</div>
                            <div class="browser-actions"></div>
                        </div>
                        <div class="browser-content">
                            <img src="{{ asset($hero['image']) }}" 
                                 alt="Serbis Dashboard" 
                                 onerror="this.src='https://via.placeholder.com/1200x800/f8f9fa/6c757d?text=Dashboard'">
                        </div>
                    </div>
                    
                    <!-- Floating Stat Card -->
                    @php
                        $floatingStat = $hero['floating_stat'] ?? [
                            'icon' => 'fas fa-bolt',
                            'number' => '2,450+',
                            'label' => 'Aktif Servis'
                        ];
                    @endphp
                    <div class="floating-stat-card">
                        <div class="floating-stat-icon">
                            <i class="{{ $floatingStat['icon'] }}"></i>
                        </div>
                        <div class="floating-stat-content">
                            <div class="floating-stat-number">{{ $floatingStat['number'] }}</div>
                            <div class="floating-stat-label">{{ $floatingStat['label'] }}</div>
                        </div>
                    </div>
                    
                    <!-- Mobile Preview -->
                    <div class="mobile-preview">
                        <div class="mobile-device">
                            <img src="{{ asset($hero['mobile_image'] ?? $hero['image']) }}" 
                                 alt="Mobile View"
                                 onerror="this.src='https://via.placeholder.com/400x800/f8f9fa/6c757d?text=Mobile'">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Wave Shape -->
    <div class="hero-wave">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="white"/>
        </svg>
    </div>
</section>



<!-- Sectors Section -->
<section class="sectors-section section">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">{{ $sectionHeaders['sectors']['badge'] ?? 'SEKTÖRLER' }}</span>
            <h2 class="section-title">
                {{ $sectionHeaders['sectors']['title'] ?? 'Hangi' }} 
                <span class="accent">{{ $sectionHeaders['sectors']['highlight'] ?? 'Sektörlere' }}</span> 
                {{ $sectionHeaders['sectors']['title_end'] ?? 'Hizmet Veriyoruz?' }}
            </h2>
            <p class="section-subtitle">
                {{ $sectionHeaders['sectors']['subtitle'] ?? 'Farklı sektörlerdeki teknik servis işletmelerinin ihtiyaçlarına özel çözümler' }}
            </p>
        </div>
        <div class="row g-4">
            @foreach($sectors as $sector)
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('sector.detail', $sector['slug']) }}" class="home-sector-card">
                    <img src="{{ asset($sector['image']) }}" alt="{{ $sector['title'] }}" class="sector-bg-img">
                    
                    <div class="sector-card-content">
                        <h4>{{ $sector['title'] }}</h4>
                        <p class="small-desc">{{ $sector['description'] }}</p>
                        <span class="btn-arrow"><i class="fas fa-arrow-right"></i></span>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        
        <div class="text-center mt-5">
            <a href="{{ route('sectors') }}" class="btn btn-hero-primary" style="background: var(--primary-blue); color: white;">
                Tüm Sektörleri Görüntüle <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Modules Section -->
<section class="modules-section section">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">{{ $sectionHeaders['modules']['badge'] ?? 'Özellikler' }}</span>
            <h2 class="section-title">{{ $sectionHeaders['modules']['title'] ?? 'Güçlü' }} <span class="accent">{{ $sectionHeaders['modules']['highlight'] ?? 'Özellikler' }}</span></h2>
            <p class="section-subtitle">
                {{ $sectionHeaders['modules']['subtitle'] ?? 'İşletmenizi yönetmek için ihtiyacınız olan tüm özellikler tek platformda' }}
            </p>
        </div>
        <div class="row g-4">
            @foreach($modules as $module)
            <div class="col-md-6 col-lg-4">
                <div class="card-item">
                    <div class="card-icon {{ $module['color'] == 'orange' ? 'orange' : '' }}">
                        <i class="{{ $module['icon'] }}"></i>
                    </div>
                    <h3 class="card-title">{{ $module['title'] }}</h3>
                    <p class="card-description">
                        {{ $module['description'] }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-5">
            <a href="{{ url('/ozellikler') }}" class="btn btn-hero-primary" style="background: var(--primary-blue); color: white;">
                Tüm Özellikleri Görüntüle <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section-dashboard">
    <div class="container">
        <div class="row">
            @foreach($stats as $stat)
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

<!-- Integrations Carousel -->
<section class="integrations-section section" style="overflow-x: hidden;"> <!-- Yatay taşmayı engeller -->
    <div class="container">
        <div class="section-header">
            <span class="section-badge">{{ $sectionHeaders['integrations']['badge'] ?? 'ENTEGRASYONLAR' }}</span>
            <h2 class="section-title">{{ $sectionHeaders['integrations']['title'] ?? 'Güçlü' }} <span class="accent">{{ $sectionHeaders['integrations']['highlight'] ?? 'Entegrasyonlar' }}</span></h2>
            <p class="section-subtitle">
                {{ $sectionHeaders['integrations']['subtitle'] ?? 'Kullandığınız tüm araçlarla sorunsuz entegre olun' }}
            </p>
        </div>
        
        <!-- position-relative: Okların bu kutuya göre hizalanmasını sağlar -->
        <div id="integrationsCarousel" class="carousel slide position-relative" data-bs-ride="carousel">
            <div class="carousel-inner">
                @php $chunks = collect($integrations)->chunk(3); @endphp
                @foreach($chunks as $chunkIndex => $chunk)
                <div class="carousel-item {{ $chunkIndex == 0 ? 'active' : '' }}">
                    <!-- Gutter (g-4) boşlukları korundu, padding kaldırıldı -->
                    <div class="row g-4">
                        @foreach($chunk as $integration)
                        <div class="col-md-4">
                            <div class="card-item text-center h-100">
                                <div class="card-icon {{ $integration['color'] == 'orange' ? 'orange' : '' }}" style="margin: 0 auto 1.5rem;">
                                    <i class="{{ $integration['icon'] }}"></i>
                                </div>
                                <h3 class="card-title">{{ $integration['title'] }}</h3>
                                <p class="card-description">{{ $integration['description'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Butonlar (CSS Sınıfları ile Yönetiliyor) -->
            <button class="custom-nav-btn prev-btn" type="button" data-bs-target="#integrationsCarousel" data-bs-slide="prev">
                <span class="custom-nav-icon">
                    <i class="fas fa-chevron-left" style="color: white;"></i>
                </span>
            </button>
            
            <button class="custom-nav-btn next-btn" type="button" data-bs-target="#integrationsCarousel" data-bs-slide="next">
                <span class="custom-nav-icon">
                    <i class="fas fa-chevron-right" style="color: white;"></i>
                </span>
            </button>
            
            {{-- <div class="carousel-indicators" style="position: relative; margin-top: 3rem;">
                @foreach($chunks as $index => $chunk)
                <button type="button" data-bs-target="#integrationsCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index == 0 ? 'active' : '' }}" style="background: {{ $index == 0 ? 'var(--primary-blue)' : 'var(--gray)' }}; width: 12px; height: 12px; border-radius: 50%;"></button>
                @endforeach
            </div> --}}
        </div>
    </div>
</section>

<!-- Testimonials Carousel -->
<section class="sectors-section section">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">{{ $sectionHeaders['testimonials']['badge'] ?? 'MÜŞTERİ YORUMLARI' }}</span>
            <h2 class="section-title">{{ $sectionHeaders['testimonials']['title'] ?? 'Müşterilerimiz' }} <span class="accent">{{ $sectionHeaders['testimonials']['highlight'] ?? 'Ne Diyor?' }}</span></h2>
            <p class="section-subtitle">{{ $sectionHeaders['testimonials']['subtitle'] ?? 'Binlerce mutlu müşterimizden bazı görüşler' }}</p>
        </div>
        
        <!-- Testimonials carousel içeriği aynı kalacak -->
        <div id="testimonialsCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                @foreach($testimonials as $index => $testimonial)
                <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="card-item text-center" style="padding: 3rem;">
                                <div style="margin-bottom: 2rem;">
                                    <i class="fas fa-quote-left" style="font-size: 3rem; color: var(--orange); opacity: 0.3;"></i>
                                </div>
                                <p style="font-size: 1.2rem; font-style: italic; margin-bottom: 2.5rem; color: var(--dark); line-height: 1.8;">
                                    "{{ $testimonial['quote'] }}"
                                </p>
                                <div style="display: flex; align-items: center; gap: 1rem; justify-content: center;">
                                    <div style="width: 60px; height: 60px; background: {{ $testimonial['color'] == 'blue' ? 'var(--primary-blue)' : 'var(--orange)' }}; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.4rem;">
                                        {{ $testimonial['initials'] }}
                                    </div>
                                    <div style="text-align: left;">
                                        <div style="font-weight: 700; color: var(--dark); font-size: 1.1rem;">{{ $testimonial['name'] }}</div>
                                        <div style="font-size: 1rem; color: var(--gray);">{{ $testimonial['position'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <button class="carousel-control-prev" type="button" data-bs-target="#testimonialsCarousel" data-bs-slide="prev" style="width: 50px; left: 0;">
                <span style="width: 50px; height: 50px; background: var(--primary-blue); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-chevron-left" style="color: white;"></i>
                </span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#testimonialsCarousel" data-bs-slide="next" style="width: 50px; right: 0;">
                <span style="width: 50px; height: 50px; background: var(--primary-blue); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-chevron-right" style="color: white;"></i>
                </span>
            </button>
            
            {{-- <div class="carousel-indicators" style="position: relative; margin-top: 2rem;">
                @foreach($testimonials as $index => $testimonial)
                <button type="button" data-bs-target="#testimonialsCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index == 0 ? 'active' : '' }}" style="background: {{ $index == 0 ? 'var(--primary-blue)' : 'var(--gray)' }}; width: 12px; height: 12px; border-radius: 50%;"></button>
                @endforeach
            </div> --}}
        </div>
    </div>
</section>


<!-- FAQ Section -->
<section class="modules-section section">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">{{ $sectionHeaders['faqs']['badge'] ?? 'SIK SORULAN SORULAR' }}</span>
            <h2 class="section-title">{{ $sectionHeaders['faqs']['title'] ?? 'Sıkça Sorulan' }} <span class="accent">{{ $sectionHeaders['faqs']['highlight'] ?? 'Sorular' }}</span></h2>
            <p class="section-subtitle">{{ $sectionHeaders['faqs']['subtitle'] ?? 'Merak ettiğiniz soruların cevaplarını burada bulabilirsiniz' }}</p>
        </div>
        <!-- FAQ içeriği aynı kalacak -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    @foreach($faqs as $index => $faq)
                    <div class="accordion-item" style="border: 1px solid var(--border); border-radius: 12px; margin-bottom: 1rem; overflow: hidden;">
                        <h2 class="accordion-header">
                            <button class="accordion-button {{ $index != 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faq{{ $index }}" style="background: white; color: var(--dark); font-weight: 600; padding: 1.5rem;">
                                {{ $faq['question'] }}
                            </button>
                        </h2>
                        <div id="faq{{ $index }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" data-bs-parent="#faqAccordion">
                            <div class="accordion-body" style="padding: 1.5rem; color: var(--gray);">
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

<!-- Contact Section -->
<section id="iletisim" class="contact-section section">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">{{ $contact['badge'] ?? 'İLETİŞİM' }}</span>
            <h2 class="section-title">{{ $contact['title'] ?? 'Bizimle' }} <span class="accent">{{ $contact['highlight'] ?? 'İletişime Geçin' }}</span></h2>
            <p class="section-subtitle">{{ $contact['subtitle'] ?? 'Sorularınız mı var? Size yardımcı olmaktan memnuniyet duyarız' }}</p>
        </div>
        <div class="row g-4">
            @foreach($contact['items'] ?? [] as $item)
            <div class="col-md-4">
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="{{ $item['icon'] }}"></i>
                    </div>
                    <h3 class="contact-title">{{ $item['title'] }}</h3>
                    <p class="contact-info">{{ $item['info'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <h2 class="cta-title">{{ $cta['title'] ?? 'Hemen Başlamaya Hazır mısınız?' }}</h2>
        <p class="cta-description">
            {{ $cta['description'] ?? '14 gün ücretsiz deneyin. Kredi kartı gerekmez. İstediğiniz zaman iptal edebilirsiniz.' }}
        </p>
        <button class="btn btn-cta" onclick="window.open('{{ url('/kullanici-girisi')}}', '_blank')">
            <i class="{{ $cta['button_icon'] ?? 'fas fa-rocket' }} me-2"></i> {{ $cta['button_text'] ?? 'Ücretsiz Denemeyi Başlat' }}
        </button>
    </div>
</section>

<!-- Video Modal -->
<div class="modal fade" 
     id="videoModal" 
     tabindex="-1" 
     aria-hidden="true"
     data-bs-backdrop="true"
     data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-body">
                <div class="ratio ratio-16x9">
                    <iframe id="videoIframe" 
                            src="" 
                            title="{{ $video['title'] ?? 'Serbis Demo Video' }}" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen" 
                            allowfullscreen
                            playsinline></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

<!-- Mobil Sabit Alt Menü -->
{{-- <div class="mobile-bottom-nav">
    <div class="nav-items">
        <!-- Demo İste -->
        <a href="{{ url('/kullanici-girisi') }}" 
           class="nav-item" 
           target="_blank"
           rel="noopener noreferrer">
            <i class="fas fa-rocket"></i>
            <span>Demo</span>
        </a>
        
        <!-- E-posta (Dinamik) -->
        <a href="mailto:{{ $contact['mobile_menu']['email'] ?? 'info@serbis.com' }}" 
           class="nav-item nav-email"
           data-action="email">
            <i class="fas fa-envelope"></i>
            <span>E-posta</span>
        </a>
        
        <!-- Telefon (Dinamik) -->
        <a href="tel:{{ $contact['mobile_menu']['phone'] ?? '02129092861' }}" 
           class="nav-item nav-phone"
           data-action="phone">
            <i class="fas fa-phone"></i>
            <span>Ara</span>
        </a>
    </div>
</div> --}}

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ==========================================
    // AUTO-PLAY CAROUSELS
    // ==========================================
    var testimonialsCarousel = new bootstrap.Carousel(document.getElementById('testimonialsCarousel'), {
        interval: 5000,
        ride: 'carousel'
    });

    var integrationsCarousel = new bootstrap.Carousel(document.getElementById('integrationsCarousel'), {
        interval: 4000,
        ride: 'carousel'
    });

    // ==========================================
    // VIDEO MODAL - DESKTOP + MOBİL
    // ==========================================
    var videoModalEl = document.getElementById('videoModal');
    var videoIframe = document.getElementById('videoIframe');
    var videoUrl = '{{ $video["video_url"] ?? "https://www.youtube.com/embed/Caa1CJUFFIs" }}';
    var closeBtn = videoModalEl ? videoModalEl.querySelector('.btn-close') : null;
    
    if (videoModalEl && videoIframe) {
        // Modal açılma
        videoModalEl.addEventListener('show.bs.modal', function () {
            videoIframe.src = videoUrl + '?autoplay=1&rel=0&playsinline=1';
        });
        
        // Modal kapanma
        videoModalEl.addEventListener('hide.bs.modal', function () {
            videoIframe.src = '';
        });
        
        // ==========================================
        // X BUTONUNA TIKLAMA - MOBİL FİX
        // ==========================================
        if (closeBtn) {
            // Click event
            closeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var modal = bootstrap.Modal.getInstance(videoModalEl);
                if (modal) {
                    modal.hide();
                }
            });
            
            // Touch event (mobil)
            closeBtn.addEventListener('touchend', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var modal = bootstrap.Modal.getInstance(videoModalEl);
                if (modal) {
                    modal.hide();
                }
            }, { passive: false });
            
            // Touchstart feedback
            closeBtn.addEventListener('touchstart', function(e) {
                this.style.opacity = '0.5';
            }, { passive: true });
            
            closeBtn.addEventListener('touchcancel', function(e) {
                this.style.opacity = '1';
            }, { passive: true });
        }
    }
    
    // ==========================================
    // VİDEO BUTONUNA TIKLAMA - MOBİL FİX
    // ==========================================
    var videoButton = document.querySelector('[data-bs-target="#videoModal"]');
    
    if (videoButton) {
        videoButton.addEventListener('touchstart', function(e) {
            this.style.opacity = '0.8';
        }, { passive: true });
        
        videoButton.addEventListener('touchend', function(e) {
            this.style.opacity = '1';
        }, { passive: true });
    }
// ==========================================
    // MOBİL MENÜ - BROWSER EXTENSİON FİX
    // ==========================================
    const mobileNavLinks = document.querySelectorAll('.mobile-bottom-nav .nav-item');
    
    mobileNavLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            // Event'in yayılmasını durdur (uzantılar müdahale etmesin)
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            const href = this.getAttribute('href');
            
            // Eğer tel: veya mailto: ise direkt aç
            if (href && (href.startsWith('tel:') || href.startsWith('mailto:'))) {
                // Tarayıcının native davranışını kullan
                window.location.href = href;
            }
        }, true); // true = capturing phase
    });
});
</script>