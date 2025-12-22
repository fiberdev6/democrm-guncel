@php
    // Verimor Santral aktif mi kontrol et
    $verimorActive = false;
    if (Auth::check() && Auth::user()->tenant_id) {
        $verimorActive = \App\Models\IntegrationPurchase::where('tenant_id', Auth::user()->tenant_id)
            ->whereHas('integration', function($q) {
                $q->where('slug', 'verimor-santral');
            })
            ->where('status', 'completed')
            ->where('is_active', true)
            ->exists();
    }
@endphp

@if($verimorActive)
<!-- Floating Web Phone Widget -->
<div id="floatingWebPhone" class="floating-webphone hidden">
    <!-- Header -->
    <div class="floating-phone-header" id="phoneHeader">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span class="phone-title">
                 Web Telefonu
            </span>
            <div>
                <button class="phone-control-btn" id="minimizeBtn" title="Küçült">
                    <i class="fas fa-minus"></i>
                </button>
                <button class="phone-control-btn" id="closeBtn" title="Kapat">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Body (iframe container) -->
    <div class="floating-phone-body" id="phoneBody">
        <div class="phone-loading" id="phoneLoading">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2">Yükleniyor...</p>
        </div>
        <div id="phoneIframeContainer" style="display: none;"></div>
    </div>
    
    <!-- Minimized (Küçültülmüş) -->
    <div class="floating-phone-minimized" id="phoneMinimized" style="display: none;">
        <button class="phone-restore-btn" id="restoreBtn">
            <i class="fas fa-phone-volume fa-lg"></i>
        </button>
    </div>
</div>

<!-- Açma Butonu (Floating) -->
<button id="openFloatingPhone" class="floating-open-btn" title="Web Telefonunu Aç">
    <i class="fas fa-phone fa-lg"></i>
</button>



<script>
$(document).ready(function() {
    const floatingPhone = $('#floatingWebPhone');
    const openBtn = $('#openFloatingPhone');
    let isLoaded = false;
    
    // Açma butonu
    openBtn.click(function() {
        floatingPhone.removeClass('hidden');
        openBtn.hide();
        
        if (!isLoaded) {
            loadWebPhone();
            isLoaded = true;
        }
    });
    
    // Web telefonunu yükle
    function loadWebPhone() {
        $.ajax({
            url: '{{ route("tenant.integrations.verimor-santral.get-iframe", ["tenant_id" => Auth::user()->tenant_id]) }}',
            type: 'GET',
            data: { width: 300, height: 550 },
            success: function(response) {
                if (response.success) {
                    $('#phoneLoading').hide();
                    $('#phoneIframeContainer').html(response.html).show();
                } else {
                    $('#phoneLoading').html('<div class="text-danger"><i class="fas fa-times"></i><p>' + response.message + '</p></div>');
                }
            },
            error: function() {
                $('#phoneLoading').html('<div class="text-danger"><i class="fas fa-times"></i><p>Yüklenemedi</p></div>');
            }
        });
    }
    
    // Minimize
    $('#minimizeBtn').click(function() {
        floatingPhone.addClass('minimized');
    });
    
    // Restore
    $('#restoreBtn').click(function() {
        floatingPhone.removeClass('minimized');
    });
    
    // Kapat
    $('#closeBtn').click(function() {
        floatingPhone.addClass('hidden');
        openBtn.show();
    });
});
</script>
@endif