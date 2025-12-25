<nav class="navbar navbar-expand-lg">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand" href="{{ route('home') }}">
            @if(isset($navbarContent['logo']))
                <img src="{{ asset($navbarContent['logo']) }}" alt="Serbis Logo" >
            @else
                <img src="{{ asset('frontend/img/logo_turkce.png') }}" alt="Serbis Logo" >
            @endif
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
<ul class="navbar-nav ms-auto align-items-center">
    @if(isset($navbarContent['menu_items']))
        @foreach($navbarContent['menu_items'] as $item)
            @if($item['type'] == 'link')
                <li class="nav-item">
                    <a class="nav-link" href="{{ $item['url'] }}">{{ $item['title'] }}</a>
                </li>
            @elseif($item['type'] == 'dropdown')
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="dropdown{{ $loop->index }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ $item['title'] }}
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="dropdown{{ $loop->index }}">
                        @foreach($item['items'] as $subItem)
                            @if(isset($subItem['divider']) && $subItem['divider'])
                                <li><hr class="dropdown-divider"></li>
                            @else
                                <li>
                                    <a class="dropdown-item" href="{{ $subItem['url'] }}">
                                        @if(isset($subItem['bold']) && $subItem['bold'])
                                            <strong>{{ $subItem['title'] }}</strong>
                                        @else
                                            {{ $subItem['title'] }}
                                        @endif
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </li>
            @endif
        @endforeach
                @else
                    <!-- Default Menu Items -->
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">Anasayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/hakkimizda') }}">Hakkımızda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('sectors') }}">Sektörler</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="featuresDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Özellikler
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="featuresDropdown">
                            {{-- <li><a class="dropdown-item" href="{{ route('feature.detail', 'musteri-yonetimi') }}">Müşteri Yönetimi</a></li>
                            <li><a class="dropdown-item" href="{{ route('feature.detail', 'is-talep-yonetimi') }}">İş Talep Yönetimi</a></li>
                            <li><a class="dropdown-item" href="{{ route('feature.detail', 'mobil-saha-yonetimi') }}">Mobil Saha Yönetimi</a></li>
                            <li><a class="dropdown-item" href="{{ route('feature.detail', 'stok-parca') }}">Stok Yönetimi</a></li>
                            <li><a class="dropdown-item" href="{{ route('feature.detail', 'fatura-yonetimi') }}">Fatura Yönetimi</a></li>
                            <li><a class="dropdown-item" href="{{ route('feature.detail', 'destek-yardim') }}">Destek ve Yardım</a></li>
                            <li><a class="dropdown-item" href="{{ route('feature.detail', 'teklif-yonetimi') }}">Teklif Yönetimi</a></li>
                            <li><a class="dropdown-item" href="{{ route('feature.detail', 'entegrasyonlar') }}">Entegrasyonlar</a></li> --}}
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ url('/ozellikler') }}">
                                <strong>Tüm Özellikleri Görüntüle →</strong>
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/entegrasyonlar') }}">Entegrasyonlar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/fiyatlar') }}">Fiyatlar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('contact_frontend') }}">İletişim</a>
                    </li>
                @endif

                <!-- Login Button -->
                <li class="nav-item ms-2">
                    @if(isset($navbarContent['login_button']))
                        <a href="{{ $navbarContent['login_button']['url'] }}" 
                           class="btn-login" 
                           target="{{ $navbarContent['login_button']['target'] ?? '_self' }}">
                            <i class="{{ $navbarContent['login_button']['icon'] ?? 'fas fa-sign-in-alt' }}"></i> 
                            {{ $navbarContent['login_button']['text'] }}
                        </a>
                    @else
                        <a href="{{ url('/kullanici-girisi') }}" class="btn-login" target="_blank">
                            <i class="fas fa-sign-in-alt"></i> Giriş Yap
                        </a>
                    @endif
                </li>
                
                <!-- CTA Button -->
                <li class="nav-item ms-2">
                    @if(isset($navbarContent['cta_button']))
                        <button class="btn btn-primary-custom" 
                                onclick="window.open('{{ $navbarContent['cta_button']['url'] }}', '{{ $navbarContent['cta_button']['target'] ?? '_blank' }}')">
                            {{ $navbarContent['cta_button']['text'] }}
                        </button>
                    @else
                        <button class="btn btn-primary-custom" onclick="window.open('{{ url('/kullanici-girisi')}}', '_blank')">
                            Ücretsiz Dene
                        </button>
                    @endif
                </li>
            </ul>
        </div>
    </div>
</nav>