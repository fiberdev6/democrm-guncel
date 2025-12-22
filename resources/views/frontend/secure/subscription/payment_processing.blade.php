@extends('frontend.secure.user_master')

@section('user')
<div class="page-content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Yükleniyor...</span>
                            </div>
                        </div>
                        
                        <h4 class="mb-3">Ödemeniz İşleniyor</h4>
                        <p class="text-muted mb-4">
                            Ödeme durumunuz kontrol ediliyor. Lütfen sayfayı kapatmayınız.
                        </p>
                        
                        <div id="payment-status" class="mb-4">
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" style="width: 0%"></div>
                            </div>
                            <small class="text-muted mt-2 d-block">Kontrol ediliyor...</small>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Bu işlem birkaç saniye sürebilir. Sayfayı yenilemeyiniz.
                        </div>
                    </div>
                </div>
                
                <!-- Yedek Butonlar -->
                <div class="text-center mt-3">
                    <a href="{{ route('subscription.payment', [$tenant_id, $planid]) }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>Ödeme Sayfasına Dön
                    </a>
                    <button type="button" id="manualCheck" class="btn btn-outline-primary">
                        <i class="fas fa-sync me-2"></i>Durumu Kontrol Et
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let checkCount = 0;
    const maxChecks = 24; // 2 dakika (5 saniye * 24)
    const progressBar = document.querySelector('.progress-bar');
    const statusText = document.querySelector('#payment-status small');
    
    function checkPaymentStatus() {
        checkCount++;
        const progress = (checkCount / maxChecks) * 100;
        progressBar.style.width = progress + '%';
        
        fetch('/subscription/payment/check-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ 
                payment_id: {{ session('subscription.payment_id') ?? 'null' }} 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'completed') {
                statusText.textContent = 'Ödeme başarılı! Yönlendiriliyor...';
                progressBar.classList.remove('progress-bar-striped', 'progress-bar-animated');
                progressBar.classList.add('bg-success');
                progressBar.style.width = '100%';
                
                setTimeout(() => {
                    window.location.href = "{{ route('secure.home', $tenant_id) }}";
                }, 2000);
                return;
            }
            
            if (data.status === 'failed') {
                statusText.textContent = 'Ödeme başarısız!';
                progressBar.classList.remove('progress-bar-striped', 'progress-bar-animated');
                progressBar.classList.add('bg-danger');
                
                setTimeout(() => {
                    window.location.href = "{{ route('subscription.payment.fail', [$tenant_id, $planid]) }}";
                }, 2000);
                return;
            }
            
            // Hala pending ise devam et
            if (checkCount < maxChecks) {
                statusText.textContent = `Kontrol ediliyor... (${checkCount}/${maxChecks})`;
                setTimeout(checkPaymentStatus, 5000);
            } else {
                // Maksimum deneme sayısına ulaşıldı
                statusText.textContent = 'Zaman aşımı! Manuel kontrol gerekli.';
                progressBar.classList.remove('progress-bar-striped', 'progress-bar-animated');
                progressBar.classList.add('bg-warning');
                
                document.getElementById('manualCheck').style.display = 'inline-block';
            }
        })
        .catch(error => {
            console.error('Status check error:', error);
            statusText.textContent = 'Bağlantı hatası!';
            
            if (checkCount < maxChecks) {
                setTimeout(checkPaymentStatus, 10000); // Hata durumunda 10 saniye bekle
            }
        });
    }
    
    // Manual check button
    document.getElementById('manualCheck').addEventListener('click', function() {
        checkCount = 0; // Sayacı sıfırla
        progressBar.style.width = '0%';
        progressBar.classList.add('progress-bar-striped', 'progress-bar-animated');
        progressBar.classList.remove('bg-warning', 'bg-danger', 'bg-success');
        statusText.textContent = 'Yeniden kontrol ediliyor...';
        this.style.display = 'none';
        
        checkPaymentStatus();
    });
    
    // İlk kontrolü başlat
    setTimeout(checkPaymentStatus, 2000); // 2 saniye bekleyip başlat
});
</script>

@endsection