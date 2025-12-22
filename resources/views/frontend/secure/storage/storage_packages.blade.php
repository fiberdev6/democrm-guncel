{{-- resources/views/frontend/secure/storage/packages.blade.php --}}
@extends('frontend.secure.user_master')
@section('user')
<div class="page-content" id="storagePackagesPage">
  <div class="container-fluid">
    <!-- Başlık -->
    <div class="row ">
      <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between" style="padding-bottom: 5px!important;">
          <h4 class="mb-sm-0">
            Ek Storage Paketleri
          </h4>
        </div>
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="fas fa-check-circle me-2"></i>
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Mevcut Storage Durumu -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div>
                <h6 class="mb-1">
                  <i class="fas fa-info-circle text-primary me-2"></i>
                  Mevcut Storage Durumunuz
                </h6>
                <small>Kullanım: {{ $storageInfo['current_usage_formatted'] }} / {{ $storageInfo['limit_formatted'] }}</small>
              </div>
              <div class="text-end">
                <div class="text-muted">Kalan Alan</div>
                <h4 class="mb-0 text-{{ $storageInfo['danger_threshold'] ? 'danger' : 'primary' }}">
                  {{ $storageInfo['remaining_formatted'] }}
                </h4>
              </div>
            </div>
            <!-- UZUN PROGRESS BAR -->
            <div class="progress" style="height: 22px; border-radius: 10px; overflow: hidden;">
              <div 
                class="progress-bar bg-{{ $storageInfo['danger_threshold'] ? 'danger' : ($storageInfo['warning_threshold'] ? 'warning' : 'success') }}" 
                role="progressbar"
                style="width: {{ $storageInfo['usage_percentage'] }}%; transition: width 0.4s;"
                aria-valuenow="{{ $storageInfo['usage_percentage'] }}" 
                aria-valuemin="0" 
                aria-valuemax="100">
              </div>
            </div>
            @if($storageInfo['has_extra_storage'])
              <small class="text-success mt-2 d-block">
                <i class="fas fa-plus-circle me-1"></i>
                Ek Storage: {{ $storageInfo['extra_storage_gb'] }} GB aktif
              </small>
            @endif
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <section class="" >
          <div class="">
            {{-- DEĞİŞİKLİK: Tailwind grid yapısı Bootstrap 'row' ve 'col' ile değiştirildi --}}
            <div class="row justify-content-center">
              @foreach($packages as $i => $package)
                @php
                  $isPopular = ($i === 1);
                @endphp
                {{-- Her kart mobil için tam genişlik (col-12), masaüstü için 1/3 genişlik (col-md-4) kaplar --}}
                <div class="col-12 col-md-6 col-lg-4 mb-4">
                  {{-- DEĞİŞİKLİK: Tailwind sınıfları Bootstrap ve özel CSS sınıfları ile değiştirildi --}}
                  <div class="card pricing-card shadow-lg position-relative text-center p-3 p-md-4">
                    <div class="card-body d-flex flex-column">                     
                      @if($isPopular)
                        {{-- DEĞİŞİKLİK: "Önerilen" etiketi Bootstrap sınıfları ile yeniden yapıldı --}}
                        <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-custom-gradient text-white shadow-sm py-2 px-3" style="font-size: 0.75rem;">
                          Önerilen
                        </span>
                      @endif

                      <div class="d-flex justify-content-center mb-4 mt-3">
                        <i class="fas fa-database" style="font-size: 3rem; color: #f9b233;"></i>
                      </div>
                      
                      <h3 class="h5 fw-bold text-dark mb-2">{{ $package->name }}</h3>
                      <p class="text-muted small mb-2">{{ $package->description }}</p>

                      <div class="h1 fw-bolder text-dark my-3">
                        ₺{{ number_format($package->price, 2) }}
                        <span class="h6 text-muted fw-normal">/ tek seferlik</span>
                      </div>

                      <div class="d-flex justify-content-between align-items-center my-4 small text-muted">
                        <div class="d-flex align-items-center">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M2.5.5A.5.5 0 0 1 3 .5V1h10V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                          </svg>
                          <span class="ms-2">Kalıcı</span>
                        </div>
                        
                        <div class="d-flex align-items-center fw-medium text-dark">
                          <svg xmlns="http://www.w3.org/2000/svg" class="me-1" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" stroke="currentColor" stroke-width="0.1">
                              <path d="M8 1.5c4.418 0 8 2.015 8 4.5S12.418 10.5 8 10.5 0 8.485 0 6s3.582-4.5 8-4.5zM8 12c4.418 0 8 2.015 8 4.5s-3.582 4.5-8 4.5S0 18.985 0 16.5 3.582 12 8 12zM0 6a.5.5 0 0 1 .5-.5h15a.5.5 0 0 1 0 1H.5A.5.5 0 0 1 0 6z"/>
                          </svg>
                          <span>+{{ $package->storage_gb }} GB</span>
                        </div>
                      </div>

                      <form action="{{ route('storage.purchase', $firma->id) }}" method="POST" class="mb-4 mt-auto">
                        @csrf
                        <input type="hidden" name="package_id" value="{{ $package->id }}">
                        <button type="submit" class="btn btn-gradient rounded-pill w-100 py-2 fw-semibold">
                          Satın Al
                        </button>
                      </form>

                      <hr class="mt-4">

                      <div class="price-description text-muted mb-2 mt-4 text-center">
                        <h6 class="text-dark fw-semibold mb-3">Bu Pakette</h6>
                        <ul>
                          <li>+{{ $package->storage_gb }} GB kalıcı depolama alanı</li>
                          <li>Tüm dosya türleri desteklenir</li>
                          <li>Anında aktifleşir</li>
                          <li>Süre sınırı yoktur</li>
                          <li>Mevcut limitinize eklenir</li>
                          <li>7/24 teknik destek</li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </section>
      </div>
    </div>

    <!-- Alt Bilgilendirme -->
    <div class="row mt-4">
      <div class="col-12">
        <div style="margin-bottom: 0px;padding:5px" class="card">
          <div class="card-body text-center">
            <h5 class="mb-3"><i class="fas fa-question-circle text-primary me-2"></i>Sıkça Sorulan Sorular</h5>
            <div class="row">
              <div class="col-md-4">
                <div class="mb-3">
                  <h6 class="text-primary">Ek storage kalıcı mı?</h6>
                  <p class="small text-muted mb-0">Evet, satın aldığınız ek storage kalıcıdır ve süre sınırı yoktur.</p>
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <h6 class="text-primary">Ne zaman aktif olur?</h6>
                  <p class="small text-muted mb-0">Ödeme onaylandıktan hemen sonra hesabınıza otomatik olarak eklenir.</p>
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <h6 class="text-primary">Güvenli mi?</h6>
                  <p class="small text-muted mb-0">PayTR güvenli ödeme sistemi kullanılır. Kredi kartı bilgileriniz saklanmaz.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
@if(request('payment_check'))
  $(document).ready(function() {
    checkStoragePaymentStatus();
  });

  function checkStoragePaymentStatus() {
    let attempts = 0;
    const maxAttempts = 12;
    
    const checkInterval = setInterval(function() {
      $.get('/{{ $firma->id }}/storage-odeme-durum')
        .done(function(response) {
          if (response.payment_completed) {
            clearInterval(checkInterval);
            alert('Ödeme başarılı! Ek storage alanınız hesabınıza eklendi.');
            location.reload();
          } else if (response.payment_failed) {
            clearInterval(checkInterval);
            alert('Ödeme işlemi başarısız.');
          }
          
          attempts++;
          if (attempts >= maxAttempts) {
            clearInterval(checkInterval);
            alert('Ödeme durumu kontrol edilemiyor. Sayfa yenileniyor...');
            location.reload();
          }
        });
    }, 5000);
  }
@endif
</script>

@endsection