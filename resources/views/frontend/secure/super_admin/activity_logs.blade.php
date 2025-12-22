<!-- resources/views/secure/super_admin/activity_logs.blade.php -->

<div class="container-fluid" id="activityLogsPage">
    <div class="card card-log">
        <div class="card-header card-log-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Sistem Log Kayıtları (Son 7 Gün)</h5>
            <small class="text-muted">
                <i class="fas fa-info-circle"></i> 
                Log kayıtları 7 gün sonra otomatik olarak silinir
            </small>
        </div>
        <div class="card-body card-log-body">
            <!-- Filtreler - Tek satırda -->
            <div class="row mb-3 align-items-end">
                <!-- Firma -->
                <div class="col-md-2">
                    <label class="form-label">Firma</label>
                    <select class="form-select" id="tenant_filter">
                        <option value="">Tüm Firmalar</option>
                        @foreach($users->unique('tenant_id') as $user)
                            @if($user->tenant)
                                <option value="{{ $user->tenant->id }}">{{ $user->tenant->firma_adi }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <!-- Kullanıcı -->
                <div class="col-md-2">
                    <label class="form-label">Kullanıcı</label>
                    <select class="form-select" id="user_filter">
                        <option value="all">Tüm Kullanıcılar</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" data-tenant="{{ $user->tenant_id }}">
                                {{ $user->name }} @if($user->tenant)({{ $user->tenant->firma_adi }})@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <!-- İşlem Türü -->
                {{-- <div class="col-md-2">
                    <label class="form-label">İşlem Türü</label>
                    <select class="form-select" id="action_filter">
                        <option value="all">Tüm İşlemler</option>
                        <option value="login">Giriş</option>
                        <option value="logout">Çıkış</option>
                        <option value="login_failed">Başarısız Giriş</option>
                        <option value="service_created">Servis Oluşturma</option>
                        <option value="service_updated">Servis Güncelleme</option>
                        <option value="service_plan_added">Servis Aşama Ekleme</option>
                        <option value="stock_created">Stok Oluşturma</option>
                        <option value="stock_action">Stok Hareketi</option>
                        <option value="consignment_created">Konsinye Oluşturma</option>
                        <option value="cash_transaction">Kasa İşlemi</option>
                        <option value="cash_transaction_updated">Kasa Güncelleme</option>
                        <option value="cash_transaction_deleted">Kasa Silme</option>
                    </select>
                </div> --}}
                
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
                        <input type="date" class="form-control" style="font-size:0.72rem !important" id="start_date" value="{{ date('Y-m-d', strtotime('-7 days')) }}">
                        <span class="input-group-text px-1" style="height:28px !important">-</span>
                        <input type="date" class="form-control" style="font-size:0.72rem !important" id="end_date" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                
                <!-- Arama -->
                <div class="col-md-3">
                    <label class="form-label">Arama</label>
                    <input type="text" class="form-control" id="search_input" placeholder="IP, kullanıcı adı ve servis id ile arama yapın...">
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

            <!-- Kayıt Sayısı -->
            <div class="row mb-3">
                <div class="col-12 text-end">
                    <span class="text-muted" id="log_count">0 kayıt</span>
                </div>
            </div>
            <!-- Log Listesi -->
            <div class="table-responsive">
                <div id="loading" class="text-center py-3" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Yükleniyor...
                </div>
                
                <div id="log_container">
                    <textarea class="form-control" id="log_display" rows="25" readonly 
                              style="font-family: 'Courier New', monospace; font-size: 11px; padding: 15px;"></textarea>
                </div>
            </div>

           <!-- Sayfalama -->
        <div class="row mt-3">
            <div class="col-md-6">
                <nav id="pagination_container"></nav>
            </div>
            <div class="col-md-6 text-end">
                <select class="form-select d-inline-block w-auto" id="per_page" onchange="loadLogs()">
                    <option value="50">50 kayıt</option>
                    <option value="100" selected>100 kayıt</option>
                    <option value="200">200 kayıt</option>
                    <option value="500">500 kayıt</option>
                </select>
            </div>
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

    // Firma filtresine göre kullanıcıları filtrele
    $('#tenant_filter').on('change', function() {
        const tenantId = $(this).val();
        const userFilter = $('#user_filter');
        
        userFilter.find('option').each(function() {
            const option = $(this);
            if (option.val() === 'all') return;
            
            if (!tenantId || option.data('tenant') == tenantId) {
                option.show();
            } else {
                option.hide();
            }
        });
        
        userFilter.val('all');
    });
});

function loadLogs(page = 1) {
    const filters = {
        tenant_id: $('#tenant_filter').val(),
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
        url: `/super-admin/activity-logs/data`,
        type: 'GET',
        data: filters,
        success: function(response) {
            $('#loading').hide();
            
            if (response.success) {
                let logText = '';
                response.data.forEach(function(log) {
                    const firmaBilgisi = log.tenant_name ? ` [${log.tenant_name}] ` : ' ';
                    logText += log.ip_address + ' - ' + (log.user_id || '') + ' - ' + 
                              (log.user_role || '') + ' - ' + log.date + ' -' + firmaBilgisi + 
                              log.description + '\n';
                });
                
                $('#log_display').val(logText);
                $('#log_count').text(`${response.pagination.total} kayıt`);
                
                createPagination(response.pagination);
            } else {
                alert('Loglar yüklenirken hata oluştu: ' + response.message);
            }
        },
        error: function(xhr) {
            $('#loading').hide();
            alert('Loglar yüklenirken hata oluştu: ' + (xhr.responseJSON?.message || 'Bilinmeyen hata'));
        }
    });
}

function createPagination(pagination) {
    let html = '';
    
    if (pagination.last_page > 1) {
        html += '<ul class="pagination pagination-sm">';
        
        if (pagination.current_page > 1) {
            html += `<li class="page-item">
                        <a class="page-link" href="#" onclick="loadLogs(${pagination.current_page - 1})"><i class="fas fa-chevron-left"></i></a>
                    </li>`;
        }
        
        let startPage = Math.max(1, pagination.current_page - 2);
        let endPage = Math.min(pagination.last_page, pagination.current_page + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            html += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="loadLogs(${i})">${i}</a>
                    </li>`;
        }
        
        if (pagination.current_page < pagination.last_page) {
            html += `<li class="page-item">
                        <a class="page-link" href="#" onclick="loadLogs(${pagination.current_page + 1})"><i class="fas fa-chevron-right"></i></a>
                    </li>`;
        }
        
        html += '</ul>';
    }
    
    $('#pagination_container').html(html);
}

function resetFilters() {
    $('#tenant_filter').val('');
    $('#user_filter').val('all');
    $('#start_date').val('{{ date('Y-m-d', strtotime('-7 days')) }}');
    $('#end_date').val('{{ date('Y-m-d') }}');
    $('#action_filter').val('all');
    $('#module_filter').val('all');
    $('#search_input').val('');
    $('#per_page').val('100');
    
    // Kullanıcı filtresini sıfırla
    $('#user_filter option').show();
    
    loadLogs();
}
</script>