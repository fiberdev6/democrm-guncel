<!-- resources/views/secure/super_admin/activity_logs.blade.php -->

<div class="card card-log">
    <div class="card-header card-log-header">
        <h5 class="card-title mb-0">Sistem Log Kayıtları</h5>
    </div>
    <div class="card-body card-log-body">
        <!-- Filtreler - Tek satırda -->
        <div class="row mb-3 align-items-end">
            <!-- Personel -->
            <div class="col-md-2">
                <label class="form-label">Personel</label>
                <select class="form-select" id="user_filter">
                    <option value="all">Tüm Personeller</option>
                    @foreach($users as $user)
                        <option value="{{ $user->user_id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- İşlem Türü -->
            <div class="col-md-2">
                <label class="form-label">İşlem Türü</label>
                <select class="form-select" id="action_filter">
                    <option value="all">Tüm İşlemler</option>
                    <option value="login">Giriş</option>
                    <option value="logout">Çıkış</option>
                    <option value="service_created">Servis Oluşturma</option>
                    <option value="service_updated">Servis Güncelleme</option>
                    <option value="service_plan_added">Servis Aşama Ekleme</option>
                    <option value="stock_created">Stok Oluşturma</option>
                    <option value="stock_action">Stok Hareketi</option>
                    <option value="consignment_created">Konsinye Oluşturma</option>
                    <option value="cash_transaction">Kasa İşlemi</option>
                    <option value="cash_transaction_updated">Kasa Güncelleme</option>
                    <option value="cash_transaction_deleted">Kasa Silme</option>
                    <option value="support_ticket_created">Destek Talebi Oluşturma</option>
                    {{-- <option value="support_ticket_updated">Destek Talebi Güncelleme</option>
                    <option value="support_ticket_closed">Destek Talebi Kapatma</option> --}}
                    {{-- <option value="support_ticket_reopened">Destek Talebi Yeniden Açma</option> --}}
                    <option value="support_ticket_reply">Destek Talebi Cevaplama</option>
                    <option value="support_ticket_status_changed">Destek Talebi Durum Değişimi</option>
                </select>
            </div>
            
            <!-- Modül -->
            <div class="col-md-2">
                <label class="form-label">Modül</label>
                <select class="form-select" id="module_filter">
                    <option value="all">Tüm Modüller</option>
                    <option value="auth">Giriş-Çıkış</option>
                    <option value="service">Servis</option>
                    <option value="customer">Müşteri</option>
                    <option value="staff">Personel</option>
                    <option value="dealer">Bayi</option>
                    <option value="stock">Depo(Stok)</option>
                    <option value="invoice">Fatura</option>
                    <option value="offer">Teklif</option>
                    <option value="cash">Kasa</option>
                    <option value="support">Destek Talebi</option>
                </select>
            </div>
            
            <!-- Tarih Aralığı -->
            <div class="col-md-2">
                <label class="form-label" style="height:20px !important">Tarih Aralığı</label>
                <div class="input-group" style="height: 30px !important">
                    <input type="date" class="form-control" style="font-size:0.72rem !important" id="start_date" value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                    <span class="input-group-text px-1" style="height:28px !important">-</span>
                    <input type="date" class="form-control" style="font-size:0.72rem !important"  id="end_date" value="{{ date('Y-m-d') }}">
                </div>
            </div>
            
            <!-- Arama -->
            <div class="col-md-2">
                <label class="form-label">Arama</label>
                <input type="text" class="form-control" id="search_input" placeholder="IP, kullanıcı adı ve servis id ile arama yapın...">
            </div>
         
            <div style="margin-bottom: 7px;" class="col-md-1 custom-p-r-min custom-p-min">
                <span class="text-muted" id="log_count">0 kayıt</span>
            </div>
           <!-- Butonlar -->
            <div class="col-md-1 d-flex align-items-end">
                <div class="d-flex">
                    <button class="btn btn-primary me-1" onclick="loadLogs()" title="Ara">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="btn btn-secondary" onclick="resetFilters()" title="Temizle">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
        </div>

       

        <!-- Log Listesi -->
        <div class="table-responsive">
            <div id="loading" class="text-center py-3" style="display: none;">
                <i class="fas fa-spinner fa-spin"></i> Yükleniyor...
            </div>
            
            <div id="log_container">
                <textarea class="form-control" id="log_display" rows="20" readonly style="font-family: monospace; font-size: 12px; background-color: #f8f9fa;"></textarea>
            </div>
        </div>

        <!-- Sayfalama -->
        <div class="row mt-3">
            <div class="col-md-6">
                <nav id="pagination_container"></nav>
            </div>
            <div class="col-md-6 text-end" >
                <select class="form-select d-inline-block w-custom" id="per_page" onchange="loadLogs()">
                    <option value="50">50</option>
                    <option value="100" selected>100</option>
                    <option value="200">200</option>
                    <option value="500">500</option>
                </select>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadLogs();
    
    // Enter tuşu ile arama
    $('#search_input').on('keypress', function(e) {
        if (e.which == 13) {
            loadLogs();
        }
    });
});

function loadLogs(page = 1) {
    const filters = {
        user_id: $('#user_filter').val(),
        start_date: $('#start_date').val(),
        end_date: $('#end_date').val(),
        action: $('#action_filter').val(),
        module: $('#module_filter').val(),
        search: $('#search_input').val(),
        per_page: $('#per_page').val(),
        page: page
    };

    $('#loading').show();
    $('#log_display').val('');

    $.ajax({
        url: `/{{$tenant_id}}/activity-logs/data`,
        type: 'GET',
        data: filters,
        success: function(response) {
            $('#loading').hide();
            
            if (response.success) {
                let logText = '';
                response.data.forEach(function(log) {
                    logText += log.formatted_text + '\n';
                });
                
                $('#log_display').val(logText);
                $('#log_count').text(`${response.pagination.total} kayıt`);
                
                // Sayfalama oluştur
                createPagination(response.pagination);
            } else {
                alert('Loglar yüklenirken hata oluştu: ' + response.message);
            }
        },
        error: function(xhr) {
            $('#loading').hide();
            alert('Loglar yüklenirken hata oluştu: ' + xhr.responseJSON?.message || 'Bilinmeyen hata');
        }
    });
}

function createPagination(pagination) {
    let html = '';
    
    if (pagination.last_page > 1) {
        html += '<ul class="pagination pagination-sm">';
        
        // Önceki sayfa
        if (pagination.current_page > 1) {
            html += `<li class="page-item">
                <a class="page-link" href="#" onclick="loadLogs(${pagination.current_page - 1})">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>`;
        }
        
        // Sayfa numaraları (sadece birkaç sayfa göster)
        let startPage = Math.max(1, pagination.current_page - 2);
        let endPage = Math.min(pagination.last_page, pagination.current_page + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            html += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="loadLogs(${i})">${i}</a>
                    </li>`;
        }
        
        // Sonraki sayfa
        if (pagination.current_page < pagination.last_page) {
            html += `<li class="page-item page-item-custom">
                <a class="page-link" href="#" onclick="loadLogs(${pagination.current_page + 1})">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>`;
        }
        
        html += '</ul>';
    }
    
    $('#pagination_container').html(html);
}

function resetFilters() {
    $('#user_filter').val('all');
    $('#start_date').val('{{ date('Y-m-d', strtotime('-30 days')) }}');
    $('#end_date').val('{{ date('Y-m-d') }}');
    $('#action_filter').val('all');
    $('#module_filter').val('all');
    $('#search_input').val('');
    $('#per_page').val('100');
    loadLogs();
}
</script>