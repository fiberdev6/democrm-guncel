

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">API Token Yönetimi</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        
                        @if($apiToken)
                            <!-- Token Var -->
                            <div class="alert alert-info">
                                <h5 class="alert-heading"><i class="fas fa-info-circle me-2"></i>API Token Bilgileri</h5>
                                <hr>
                                <p><strong>Token Adı:</strong> {{ $apiToken->name }}</p>
                                <p><strong>Durum:</strong> 
                                    @if($apiToken->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Pasif</span>
                                    @endif
                                </p>
                                <p><strong>Son Kullanım:</strong> 
                                    @if($apiToken->last_used_at)
                                        {{ $apiToken->last_used_at->diffForHumans() }}
                                    @else
                                        <span class="text-muted">Hiç kullanılmadı</span>
                                    @endif
                                </p>
                                <p><strong>Oluşturulma:</strong> {{ $apiToken->created_at->format('d.m.Y H:i') }}</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><strong>Token (Kopyalayın):</strong></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="apiTokenInput" value="{{ $apiToken->token }}" readonly>
                                    <button class="btn btn-primary btn-sm" onclick="copyToken()">
                                        <i class="mdi mdi-content-copy"></i> Kopyala
                                    </button>
                                </div>
                                <small class="text-muted">Bu token'ı API isteklerinizde kullanın</small>
                            </div>

                            <div class="d-flex gap-2">
                                <button class="btn btn-warning btn-sm updateApiToken" data-bs-toggle="modal" data-bs-target="#apiTokenModal">
                                    Token'ı Yenile
                                </button>
                                
                                <button class="btn btn-{{ $apiToken->is_active ? 'secondary' : 'success' }} btn-sm toggleApiToken">
                                     {{ $apiToken->is_active ? 'Pasif Yap' : 'Aktif Yap' }}
                                </button>

                                <button class="btn btn-danger btn-sm deleteApiToken">
                                     Sil
                                </button>
                            </div>

                        @else
                            <!-- Token Yok -->
                            <div class="alert alert-warning">
                                <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>API Token Bulunamadı</h5>
                                <p>Henüz bir API token oluşturmadınız. API kullanabilmek için bir token oluşturmalısınız.</p>
                                <hr>
                                <button class="btn btn-success btn-sm createApiToken" data-bs-toggle="modal" data-bs-target="#apiTokenModal">
                                    <i class="fas fa-plus"></i> API Token Oluştur
                                </button>
                            </div>
                        @endif

                        

                    </div>
                </div>
            </div>
        </div>


<!-- Modal -->
<div id="apiTokenModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">API Token Oluştur/Güncelle</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Yükleniyor...
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function(){
    var firma_id = {{ $firma->id }};

    // Token oluştur/güncelle modalını aç
    $('.createApiToken, .updateApiToken').click(function(){
        $.ajax({
            url: "/" + firma_id + "/api-token/olustur"
        }).done(function(data) {
            $('#apiTokenModal .modal-body').html(data);
        });
    });

    // Modal kapanınca içeriği temizle
    $("#apiTokenModal").on("hidden.bs.modal", function() {
        $(".modal-body").html("Yükleniyor...");
    });

    // Token aktif/pasif yap
    $('.toggleApiToken').click(function(){
        if(confirm('Token durumunu değiştirmek istediğinize emin misiniz?')) {
            $.ajax({
                url: "/" + firma_id + "/api-token/aktif-pasif",
                type: "POST",
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(response) {
                    if(response.success) {
                        alert(response.message);
                        location.reload();
                    }
                },
                error: function() {
                    alert('Bir hata oluştu');
                }
            });
        }
    });

    // Token sil
    $('.deleteApiToken').click(function(){
        if(confirm('API Token\'ı silmek istediğinize emin misiniz? Bu işlem geri alınamaz!')) {
            $.ajax({
                url: "/" + firma_id + "/api-token/sil",
                type: "DELETE",
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(response) {
                    if(response.success) {
                        alert(response.message);
                        location.reload();
                    }
                },
                error: function() {
                    alert('Bir hata oluştu');
                }
            });
        }
    });
});

// Token kopyala
function copyToken() {
    var tokenInput = document.getElementById('apiTokenInput');
    tokenInput.select();
    document.execCommand('copy');
    
    alert('Token kopyalandı!');
}
</script>
