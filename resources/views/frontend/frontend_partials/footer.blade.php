<footer class="footer">
    <div class="container">
        <div class="row">
            <!-- Hakkımızda Kolonu -->
            <div class="col-lg-4 mb-4 mb-lg-0">
                <h5 class="footer-title">{{ $footerContent['about']['title'] ?? 'Hakkımızda' }}</h5>
                
                <p class="footer-description" style="font-size: 1rem; line-height: 1.6; margin-bottom: 1.5rem;">
                    {{ $footerContent['about']['description'] ?? 'Teknik servis işletmeleri için yeni nesil, bulut tabanlı yönetim sistemi.' }}
                </p>
                
                <!-- Mobil Uygulama Linkleri -->
                @if(isset($footerContent['mobile_apps']))
                <div class="mb-3">
                    <p class="mb-2" style="font-weight: 600; color: white; font-size: 0.95rem;">
                        {{ $footerContent['mobile_apps']['title'] ?? 'Mobil Uygulamayı İndirin' }}
                    </p>
                    <div class="d-flex gap-2">
                        <a href="{{ $footerContent['mobile_apps']['app_store'] ?? '#' }}" class="app-store-badge">
                            <img src="https://developer.apple.com/assets/elements/badges/download-on-the-app-store.svg" alt="App Store" style="height: 40px;">
                        </a>
                        <a href="{{ $footerContent['mobile_apps']['google_play'] ?? '#' }}" class="google-play-badge">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Google Play" style="height: 40px;">
                        </a>
                    </div>
                </div>
                @endif
                
                <!-- Sosyal Medya -->
                @if(isset($footerContent['social_media']))
                <div class="footer-social">
                    @if(!empty($footerContent['social_media']['facebook']))
                        <a href="{{ $footerContent['social_media']['facebook'] }}" class="social-icon" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    @endif
                    @if(!empty($footerContent['social_media']['twitter']))
                        <a href="{{ $footerContent['social_media']['twitter'] }}" class="social-icon" target="_blank"><i class="fab fa-twitter"></i></a>
                    @endif
                    @if(!empty($footerContent['social_media']['instagram']))
                        <a href="{{ $footerContent['social_media']['instagram'] }}" class="social-icon" target="_blank"><i class="fab fa-instagram"></i></a>
                    @endif
                    @if(!empty($footerContent['social_media']['linkedin']))
                        <a href="{{ $footerContent['social_media']['linkedin'] }}" class="social-icon" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                    @endif
                </div>
                @endif
            </div>
            
            <!-- Ürün Kolonu -->
            <div class="col-lg-2 col-6 mb-4 mb-lg-0">
                <h5 class="footer-title">{{ $footerContent['product_menu']['title'] ?? 'Ürün' }}</h5>
                <ul class="footer-links">
                    @if(isset($footerContent['product_menu']['links']))
                        @foreach($footerContent['product_menu']['links'] as $link)
                        <li>
                            <a href="{{ url($link['url'] ?? '/') }}">
                                {{ $link['title'] }}
                            </a>
                        </li>                        
                        @endforeach
                    @else
                        <li><a href="{{ route('home') }}">Anasayfa</a></li>
                        <li><a href="{{ url('/hakkimizda') }}">Hakkımızda</a></li>
                        <li><a href="{{ route('sectors') }}">Sektörler</a></li>
                        <li><a href="{{ url('/ozellikler') }}">Özellikler</a></li>
                        <li><a href="{{ url('/entegrasyonlar') }}">Entegrasyonlar</a></li>
                        <li><a href="{{ url('/fiyatlar') }}">Fiyatlar</a></li>
                    @endif
                </ul>
            </div>
            
            <!-- Özellikler Kolonu -->
            <div class="col-lg-3 col-6 mb-4 mb-lg-0">
                <h5 class="footer-title">{{ $footerContent['features_menu']['title'] ?? 'Özellikler' }}</h5>
                <ul class="footer-links">
                    @if(isset($footerContent['features_menu']['links']))
                        @foreach($footerContent['features_menu']['links'] as $link)
                            <li><a href="{{ url($link['url'] ?? '/') }}">{{ $link['title'] }}</a></li>
                        @endforeach
                    @else
                        <li><a href="{{ route('feature.detail', 'musteri-yonetimi') }}">Müşteri Yönetimi</a></li>
                        <li><a href="{{ route('feature.detail', 'is-talep-yonetimi') }}">İş Talep Yönetimi</a></li>
                        <li><a href="{{ route('feature.detail', 'mobil-saha-yonetimi') }}">Mobil Saha Yönetimi</a></li>
                        <li><a href="{{ route('feature.detail', 'stok-parca') }}">Stok Yönetimi</a></li>
                        <li><a href="{{ route('feature.detail', 'fatura-yonetimi') }}">Fatura Yönetimi</a></li>
                        <li><a href="{{ route('feature.detail', 'teklif-yonetimi') }}">Teklif Yönetimi</a></li>
                    @endif
                </ul>
            </div>
            
            <!-- İletişim Kolonu -->
            <div class="col-lg-3 col-6">
                <h5 class="footer-title">{{ $footerContent['contact_menu']['title'] ?? 'İletişim' }}</h5>
                <ul class="footer-links">
                    @if(isset($footerContent['contact_menu']['items']))
                        @foreach($footerContent['contact_menu']['items'] as $item)
                            <li>
                                <i class="{{ $item['icon'] ?? 'fas fa-info' }} me-2" style="color: var(--primary-blue);"></i>
                                @if(isset($item['url']) && $item['url'])
                                    <a href="{{ $item['url'] }}">{{ $item['text'] ?? '' }}</a>
                                @else
                                    <span>{{ $item['text'] ?? '' }}</span>
                                @endif
                            </li>
                        @endforeach
                    @else
                        <li>
                            <i class="fas fa-phone me-2" style="color: var(--primary-blue);"></i>
                            <a href="tel:02129092861">0212 909 2861</a>
                        </li>
                        <li>
                            <i class="fas fa-envelope me-2" style="color: var(--primary-blue);"></i>
                            <a href="mailto:info@serbis.com">info@serbis.com</a>
                        </li>
                        <li>
                            <i class="fas fa-map-marker-alt me-2" style="color: var(--primary-blue);"></i>
                            <span>İstanbul, Türkiye</span>
                        </li>
                    @endif
                    
                    @if(isset($footerContent['contact_menu']['contact_form_url']))
                        <li class="mt-3">
                            <a href="{{ $footerContent['contact_menu']['contact_form_url'] }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-paper-plane me-1"></i> İletişim Formu
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
        
    
        <div class="footer-bottom">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                    <p class="mb-0">{{ $footerContent['copyright'] ?? '© ' . date('Y') . ' Serbis. Tüm hakları saklıdır.' }}</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    @if(isset($footerContent['legal_links']))
                        @foreach($footerContent['legal_links'] as $index => $link)
                            <a href="{{ url($link['url'] ?? '/') }}" class="footer-legal-link">{{ $link['title'] ?? '' }}</a>
                            @if($index < count($footerContent['legal_links']) - 1)
                                <span class="mx-2">|</span>
                            @endif
                        @endforeach
                    @else
                        <a href="{{ url('/gizlilik') }}" class="footer-legal-link">Gizlilik Politikası</a>
                        <span class="mx-2">|</span>
                        <a href="{{ url('/kullanim-sartlari') }}" class="footer-legal-link">Kullanım Şartları</a>
                        <span class="mx-2">|</span>
                        <a href="{{ url('/kvkk') }}" class="footer-legal-link">KVKK</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</footer>