@extends('frontend.secure.user_master')
@section('user')

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="page-title mb-0">Hipcall Çağrıları (Test)</h4>
                    <div>
                        <button type="button" class="btn btn-primary btn-sm" onclick="refreshCalls()">
                            <i class="fas fa-sync-alt"></i> Yenile
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="testApiConnection()">
                            <i class="fas fa-plug"></i> API Test
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        
                        @if(!$success && isset($error))
                        <div class="alert alert-danger">
                            <strong>Hata:</strong> {{ $error }}
                            <hr>
                            <small>
                                <strong>Kontrol listesi:</strong><br>
                                1. API Key doğru girildi mi?<br>
                                2. Hipcall hesabınızda API erişimi aktif mi?<br>
                                3. API Key'in geçerlilik süresi dolmadı mı?
                            </small>
                        </div>
                        @endif

                        <div id="callsContainer">
                            @if($success && count($calls) > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered">
                                        <thead class="table-light">
    <tr>
        <th>Tarih/Saat</th>
        <th>Arayan Numara</th>
        <th>Müşteri</th> <!-- YENİ SÜTUN -->
        <th>Aranan Numara</th>
        <th>Yön</th>
        <th>Süre</th>
        <th>Durum</th>
    </tr>
</thead>
<tbody>
    @foreach($calls as $call)
    <tr>
        <td>
            {{ isset($call['started_at']) ? \Carbon\Carbon::parse($call['started_at'])->format('d.m.Y H:i') : '-' }}
        </td>
        <td>
            <span class="badge bg-info">{{ $call['caller_number'] ?? '-' }}</span>
        </td>
        <td>
            <!-- MÜŞTERĠ BİLGİSİ -->
            @if(isset($call['customer']))
                <a href="{{ route('customer.detail', [$tenant->id, $call['customer']['id']]) }}" 
                   class="text-primary">
                    <i class="fas fa-user"></i>
                    {{ $call['uuid'] }}
                </a>
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>
            <span class="badge bg-secondary">{{ $call['callee_number'] ?? '-' }}</span>
        </td>
        <td>
            @if(isset($call['direction']) && $call['direction'] == 'inbound')
                <span class="badge bg-success">
                    <i class="fas fa-arrow-down"></i> Gelen
                </span>
            @else
                <span class="badge bg-primary">
                    <i class="fas fa-arrow-up"></i> Giden
                </span>
            @endif
        </td>
        <td>
            @if(isset($call['call_duration']))
                {{ gmdate("i:s", $call['call_duration']) }} dk
            @else
                -
            @endif
        </td>
        <td>
            @if(isset($call['status']))
                <span class="badge bg-{{ $call['status'] == 'completed' ? 'success' : 'warning' }}">
                    {{ $call['status'] }}
                </span>
            @else
                -
            @endif
        </td>
    </tr>
    @endforeach
</tbody>
                                    </table>
                                </div>
                                
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle"></i>
                                    Toplam <strong>{{ count($calls) }}</strong> çağrı gösteriliyor (Son 50 kayıt)
                                </div>
                            @elseif($success && count($calls) == 0)
                                <div class="alert alert-warning text-center py-5">
                                    <i class="fas fa-phone-slash fa-3x mb-3"></i>
                                    <h5>Henüz çağrı kaydı yok</h5>
                                    <p class="text-muted">Hipcall'dan ilk çağrınızı yaptığınızda burada görünecektir.</p>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Debug Bilgisi -->
        @if(config('app.debug'))
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Debug - Raw API Response</h5>
                    </div>
                    <div class="card-body">
                        <pre class="bg-light p-3" style="max-height: 300px; overflow-y: auto;">{{ json_encode($calls, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

<script>
// Çağrıları yenile
function refreshCalls() {
    if (typeof toastr !== 'undefined') {
        toastr.info('Çağrılar yenileniyor...');
    }
    
    $.ajax({
        url: '{{ route("tenant.integrations.hipcall.fetch-calls", $tenant->id) }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                // Sayfayı yenile
                location.reload();
            } else {
                if (typeof toastr !== 'undefined') {
                    toastr.error(response.message || 'Çağrılar yüklenemedi');
                } else {
                    alert('✗ ' + (response.message || 'Çağrılar yüklenemedi'));
                }
            }
        },
        error: function(xhr) {
            if (typeof toastr !== 'undefined') {
                toastr.error('Yenileme sırasında hata oluştu');
            } else {
                alert('✗ Yenileme sırasında hata oluştu');
            }
        }
    });
}

</script>

@endsection