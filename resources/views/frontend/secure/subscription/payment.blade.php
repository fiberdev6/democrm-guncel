@extends('frontend.secure.user_master')
@section('user')
<div class="page-content">

  <div class="container-fluid py-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Ödeme</h2>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Sol taraf: Sipariş ve Ödeme Bilgileri -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Sipariş Özeti -->
        <div class="bg-white rounded-2xl shadow-lg p-6">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Sipariş Özeti</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-gray-600">
            <div>
              <h4 class="font-medium mb-2">Paket Bilgileri</h4>
              <p><strong>{{ $planData['name'] }}</strong></p>
              <p>{{ $planData['price'] }} TL / {{ $planData['billing_cycle'] == 'monthly' ? 'Aylık' : 'Yıllık' }}</p>

            </div>
            <div>
              <h4 class="font-medium mb-2">Fatura Bilgileri</h4>
              <p><strong>{{ $billingData['first_name'] }}</strong></p>
              <p>{{ $billingData['email'] }}</p>
              <p>{{ $billingData['phone'] }}</p>
              @if(isset($billingData['address']))
                <p>{{ $billingData['address'] }}</p>
              @endif
              @if($billingData['billing_type'] == 'bireysel')
                <p>Bireysel Fatura</p>
                @if(isset($billingData['identity_number']))
                  <p>TC: {{ $billingData['identity_number'] }}</p>
                @endif
              @else
                <p>Kurumsal Fatura</p>
                @if(isset($billingData['tax_office']))
                  <p>Vergi Dairesi: {{ $billingData['tax_office'] }}</p>
                @endif
                @if(isset($billingData['tax_number']))
                  <p>Vergi No: {{ $billingData['tax_number'] }}</p>
                @endif
              @endif
            </div>
          </div>
        </div>

        <!-- Ödeme Bilgileri -->
        <div class="bg-white rounded-2xl shadow-lg p-6">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Ödeme Bilgileri</h3>

          <!-- Güvenli Ödeme Uyarısı -->
          <div class="p-4 mb-4 rounded-lg bg-blue-50 border border-blue-200 text-blue-700">
            <i class="fas fa-credit-card mr-2"></i>
            <strong>Güvenli Ödeme:</strong> Ödemeniz Paytr ile 256-bit SSL şifrelemesi altında işlenir.
          </div>

          <!-- Kart Logoları -->
          <div class="flex items-center space-x-4 mb-6">
            <img src="{{asset('frontend/img/visa.jpg')}}" alt="Visa" class="h-8">
            <img src="{{asset('frontend/img/masterpass.jpg')}}" alt="Mastercard" class="h-10">
            <img src="{{asset('frontend/img/troy.jpg')}}" alt="Troy" class="h-8">
          </div>

          <!-- Sözleşme Onayı -->
          <div class="flex items-start space-x-2 mb-6">
            <input type="checkbox" id="terms" class="mt-1">
           <label for="terms" class="text-sm text-gray-600">
              <a href="#" data-legal-type="terms"class="text-orange-600 hover:underline">Kullanım Koşulları</a>
              <a href="#" data-legal-type="privacy" class="text-orange-600 hover:underline">Gizlilik Politikası</a>'nı okudum, kabul ediyorum.
          </label>
          </div>

          <!-- Butonlar -->
          <div class="flex justify-between">
            <a href="{{ route('subscription.subscribe', [$tenant_id, $planid]) }}" 
               class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700">
              <i class="fas fa-arrow-left mr-2"></i>Geri
            </a>
            <button type="button" id="payButton" 
              class="px-6 py-2 rounded-lg bg-green-500 text-white font-semibold hover:opacity-90">
              <i class="fas fa-lock mr-2"></i>Ödeme Yap
            </button>
          </div>
        </div>
      </div>

      <!-- Sağ taraf: Fiyat Özeti -->
      <div>
        <div class="bg-white rounded-2xl shadow-lg p-6">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Ödeme Özeti</h3>

          <div class="flex justify-between mb-2 text-gray-600">
            <span>Paket Ücreti:</span>
            <span>{{ number_format($planData['price'], 2) }} TL</span>
          </div>
          <div class="flex justify-between mb-2 text-gray-600">
            <span>KDV (%20):</span>
            <span>{{ number_format($planData['price'] * 0.20, 2) }} TL</span>
          </div>
          <hr class="my-3">
          <div class="flex justify-between text-lg font-semibold text-gray-800">
            <span>Toplam:</span>
            <span>{{ number_format($planData['price'] * 1.20, 2) }} TL</span>
          </div>

          <div class="mt-4 p-3 bg-gray-50 rounded text-sm text-gray-500">
            <i class="fas fa-shield-alt mr-1"></i>
            Ödemeniz 256-bit SSL ile güvence altındadır.
          </div>

          <div class="mt-3 p-3 rounded  bg-gradient-to-r from-orange-500 to-[#f9b233] text-white text-center text-sm">
            <i class="fas fa-check-circle mr-1"></i>
            Paytr Güvenli Ödeme Sistemi
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Kullanım Koşulları Modal -->
<div id="termsModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kullanım Koşulları</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="termsContent" style="max-height: 500px; overflow-y: auto;">
                <div class="text-center"><i class="fas fa-spinner fa-spin"></i> Yükleniyor...</div>
            </div>
        </div>
    </div>
</div>

<!-- Gizlilik Politikası Modal -->
<div id="privacyModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gizlilik Politikası</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="privacyContent" style="max-height: 500px; overflow-y: auto;">
                <div class="text-center"><i class="fas fa-spinner fa-spin"></i> Yükleniyor...</div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.tailwindcss.com"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const payButton = document.getElementById('payButton');
  const termsCheckbox = document.getElementById('terms');

  payButton.addEventListener('click', function(e) {
    e.preventDefault();

    if (!termsCheckbox.checked) {
      alert('Lütfen kullanım koşullarını ve gizlilik politikasını kabul ediniz.');
      return;
    }

    const originalText = payButton.innerHTML;
    payButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Yönlendiriliyor...';
    payButton.disabled = true;

    const paymentUrl = '{{ route("subscription.payment.initiate", [$tenant_id, $planid]) }}';

    setTimeout(() => window.location.href = paymentUrl, 1000);

    setTimeout(() => {
      payButton.innerHTML = originalText;
      payButton.disabled = false;
    }, 10000);
  });
});
</script>
<script>
// Link tıklamalarını dinle
$(document).on('click', 'a[href="#"][data-legal-type]', function(e) {
    e.preventDefault();
    const type = $(this).data('legal-type');
    
    if (type === 'terms') {
        $('#termsModal').modal('show');
        loadLegalContent('terms');
    } else if (type === 'privacy') {
        $('#privacyModal').modal('show');
        loadLegalContent('privacy');
    }
});

function loadLegalContent(type) {
    const url = type === 'terms' 
        ? '{{ route("api.terms", $tenant_id) }}' 
        : '{{ route("api.privacy", $tenant_id) }}';
    
    const contentId = type === 'terms' ? '#termsContent' : '#privacyContent';
    
    $.ajax({
        url: url,
        method: 'GET',
        success: function(response) {
            $(contentId).html(response.content || '<p class="text-muted">İçerik henüz eklenmemiş.</p>');
        },
        error: function() {
            $(contentId).html('<p class="text-danger">İçerik yüklenirken hata oluştu.</p>');
        }
    });
}
</script>
@endsection
