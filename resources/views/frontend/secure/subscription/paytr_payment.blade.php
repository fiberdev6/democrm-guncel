@extends('frontend.secure.user_master')

@section('user')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="padding: 7px !important;">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-credit-card me-2"></i>Güvenli Ödeme
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <!-- Ödeme Özeti (Üst Kısım) -->
                        <div class="p-3 bg-light border-bottom">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Ödeme Bilgileri</h6>
                                    <p class="mb-1"><strong>Paket:</strong> {{ $planData['name'] }}</p>
                                    <p class="mb-1"><strong>Müşteri:</strong> {{ $billingData['first_name'] }}</p>
                                    <p class="mb-0"><strong>E-posta:</strong> {{ $billingData['email'] }}</p>
                                </div>
                                <div class="col-md-6 text-end">
                                    <h4 class="text-success mb-0">{{ number_format($totalAmount, 2) }} TL</h4>
                                    <small class="text-muted">KDV Dahil</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Paytr iframe -->
                        <div class="paytr-iframe-container" style="min-height: 600px;">
                            <script src="https://www.paytr.com/js/iframeResizer.min.js"></script>
                            <iframe 
                                src="{{ $iframe_url }}" 
                                id="paytriframe" 
                                frameborder="0" 
                                scrolling="yes" 
                                style="width: 100%; height: 600px;">
                            </iframe>
                        </div>
                    </div>
                </div>
                
                <!-- Güvenlik Bilgileri -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <i class="fas fa-shield-alt text-success fa-2x mb-2"></i>
                                <h6>256-bit SSL Güvenlik</h6>
                                <small class="text-muted">Verileriniz şifrelenerek korunur</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-credit-card text-primary fa-2x mb-2"></i>
                                <h6>3D Secure Onaylı</h6>
                                <small class="text-muted">Bankanız tarafından onaylanır</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // iframe yüklendiğinde boyutunu ayarla
    const iframe = document.getElementById('paytriframe');
    
    iframe.addEventListener('load', function() {
        console.log('Paytr iframe yüklendi');
        
        // iframe içeriğine göre boyut ayarla (opsiyonel)
        try {
            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
            const height = iframeDoc.body.scrollHeight;
            if (height > 400) {
                iframe.style.height = height + 'px';
            }
        } catch(e) {
            // Cross-origin kısıtlaması nedeniyle hata olabilir, normal
            console.log('iframe boyutu ayarlanamadı (cross-origin)');
        }
    });
    
    // Ödeme durumunu kontrol etmek için periyodik kontrol (opsiyonel)
    const paymentId = {{ session('subscription.payment_id') ?? 'null' }};
    
    if (paymentId) {
        const checkInterval = setInterval(function() {
            fetch('/subscription/payment/check-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ payment_id: paymentId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'completed') {
                    clearInterval(checkInterval);
                    // Başarı sayfasına yönlendir
                    window.location.href = "{{ route('subscription.payment.success', [$tenant_id ?? 0, $planid ?? 0]) }}";
                }
            })
            .catch(error => {
                console.log('Payment status check error:', error);
            });
        }, 5000); // 5 saniyede bir kontrol et
        
        // 10 dakika sonra kontrolleri durdur
        setTimeout(function() {
            clearInterval(checkInterval);
        }, 600000);
    }
});
</script>

@endsection