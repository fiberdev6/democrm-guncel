{{-- resources/views/frontend/secure/super_admin/support/index.blade.php --}}
@extends('frontend.secure.user_master')
@section('user')

<div class="page-content support-index-page" id="supportIndexPage">
    <div class="container-fluid">
        <!-- Başlık -->
        <div class="row header-t">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">
                        Destek Talepleri Yönetimi
                    </h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('super.admin.dashboard') }}" class="text-decoration-none">Super Admin</a></li>
                            <li class="breadcrumb-item active">Destek Talepleri</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtreler -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card filter-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2 pt-2">
                            <i class="fas fa-filter text-primary me-2 d-none"></i>
                            <h6 class="mb-0 text-dark">Filtreler</h6>
                        </div>
                        <form method="GET" class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label text-muted small">Durum</label>
                                <select name="status" class="form-select">
                                    <option value="">Tüm Durumlar</option>
                                    <option value="acik" {{ request('status') == 'acik' ? 'selected' : '' }}>Açık</option>
                                    <option value="cevaplandi" {{ request('status') == 'cevaplandi' ? 'selected' : '' }}>Cevaplandı</option>
                                    <option value="kapali" {{ request('status') == 'kapali' ? 'selected' : '' }}>Kapalı</option>
                                </select>
                            </div>
                        
                            <div class="col-md-2 all-support">
                                <label class="form-label text-muted small">Öncelik</label>
                                <select name="priority" class="form-select">
                                    <option value="">Tüm Öncelikler</option>
                                    <option value="acil" {{ request('priority') == 'acil' ? 'selected' : '' }}>Acil</option>
                                    <option value="kritik" {{ request('priority') == 'kritik' ? 'selected' : '' }}>Kritik</option>
                                    <option value="yuksek" {{ request('priority') == 'yuksek' ? 'selected' : '' }}>Yüksek</option>
                                    <option value="orta" {{ request('priority') == 'orta' ? 'selected' : '' }}>Orta</option>
                                    <option value="dusuk" {{ request('priority') == 'dusuk' ? 'selected' : '' }}>Düşük</option>
                                </select>
                            </div>
                            <div class="col-md-2 all-support">
                                <label class="form-label text-muted small">Kategori</label>
                                <select name="category" class="form-select">
                                    <option value="">Tüm Kategoriler</option>
                                    @if(isset($categories))
                                        @foreach($categories as $key => $value)
                                            <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-2 all-support">
                                <label class="form-label text-muted small">Firma</label>
                                <select name="tenant_id" class="form-select">
                                    <option value="">Tüm Firmalar</option>
                                    @if(isset($tenants))
                                        @foreach($tenants as $tenant)
                                            <option value="{{ $tenant->id }}" {{ request('tenant_id') == $tenant->id ? 'selected' : '' }}>
                                                {{ $tenant->firma_adi }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-3 all-support">
                                <label class="form-label text-muted small">Arama</label>
                                <input type="text" name="search" class="form-control mb-1" 
                                       placeholder="Talep no, konu veya kullanıcı ara..." 
                                       value="{{ request('search') }}">
                            </div>

                            <div class="col-md-1">
                                <label class="form-label d-block">&nbsp;</label> 
                                <button type="submit" class="btn btn-secondary btn-sm w-100" style="border: 1px solid black">
                                    <i class="fas fa-search me-1"></i>
                                    Filtrele
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bildirimler -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
            </div>
        @endif

        <!-- Ana Kart -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center p-1">
                            <h5 class="card-title mb-0 d-flex align-items-center">
                                Destek Talepleri
                                @if(isset($tickets) && $tickets->count() > 0)
                                    <span class="badge bg-light text-dark ms-2">{{ $tickets->count() }}</span>
                                @endif
                            </h5>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        @if(isset($tickets) && $tickets->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="superAdminSupportTable">
                                    <thead class="">
                                        <tr style="background-color: #efefef !important;">
                                            <th class="border-0 fw-bold">Talep No</th>
                                            <th class="border-0 fw-bold">Firma</th>
                                            <th class="border-0 fw-bold">Kullanıcı</th>
                                            <th class="border-0 fw-bold">Konu</th>
                                            <th class="border-0 fw-bold">Kategori</th>
                                            <th class="border-0 fw-bold">Öncelik</th>
                                            <th class="border-0 fw-bold">Durum</th>
                                            <th class="border-0 fw-bold">Oluşturma</th>
                                            <th class="border-0 fw-bold">Son Yanıt</th>
                                            <th class="border-0 custom-none fw-bold text-center" >İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tickets as $ticket)
                                            <tr class="align-middle">
                                                <td data-label="Talep No">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-xs me-2">
                                                            <div class="avatar-title rounded-circle bg-light text-primary">
                                                                <i class="fas fa-hashtag"></i>
                                                            </div>
                                                        </div>
                                                        <span class="fw-bold">{{ $ticket->ticket_number }}</span>
                                                    </div>
                                                </td>
                                                <td data-label="Firma">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-xs me-2">
                                                            <div class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                                <i class="fas fa-building"></i>
                                                            </div>
                                                        </div>
                                                        <span>{{ $ticket->tenant->firma_adi ?? 'Bilinmiyor' }}</span>
                                                    </div>
                                                </td>
                                                <td data-label="Kullanıcı">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-xs me-2">
                                                            <div class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                                <i class="fas fa-user"></i>
                                                            </div>
                                                        </div>
                                                        <span>{{ $ticket->user->name ?? 'Bilinmiyor' }}</span>
                                                    </div>
                                                </td>
                                                <td data-label="Konu">
                                                    <div style="max-width: 200px;">
                                                        <h6 class="mb-0">{{ Str::limit($ticket->subject, 40) }}</h6>
                                                        <small class="text-muted">
                                                            {{ Str::limit(strip_tags($ticket->description ?? ''), 50) }}
                                                        </small>
                                                    </div>
                                                </td>
                                                <td data-label="Kategori">
                                                    @switch($ticket->category)
                                                        @case('teknik_sorun')
                                                            <span class="badge bg-soft-danger text-danger border border-danger border-opacity-25">
                                                                <i class="fas fa-cogs me-1"></i>Teknik Sorun
                                                            </span>
                                                            @break
                                                        @case('faturalandirma')
                                                            <span class="badge bg-soft-warning text-warning border border-warning border-opacity-25">
                                                                <i class="fas fa-file-invoice me-1"></i>Faturalandırma
                                                            </span>
                                                            @break
                                                        @case('ozellik_talebi')
                                                            <span class="badge bg-soft-info text-info border border-info border-opacity-25">
                                                                <i class="fas fa-lightbulb me-1"></i>Özellik Talebi
                                                            </span>
                                                            @break
                                                        @case('genel_destek')
                                                            <span class="badge bg-soft-primary text-primary border border-primary border-opacity-25">
                                                                <i class="fas fa-question-circle me-1"></i>Genel Destek
                                                            </span>
                                                            @break
                                                        @case('hesap_sorunu')
                                                            <span class="badge bg-soft-secondary text-secondary border border-secondary border-opacity-25">
                                                                <i class="fas fa-user-times me-1"></i>Hesap Sorunu
                                                            </span>
                                                            @break
                                                        @default
                                                            <span class="badge bg-soft-secondary text-secondary">{{ $ticket->category }}</span>
                                                    @endswitch
                                                </td>
                                                <td data-label="Öncelik">
                                                    @switch($ticket->priority)
                                                        @case('acil')
                                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                                                <i class="fas fa-exclamation-circle me-1"></i>Acil
                                                            </span>
                                                            @break
                                                        @case('kritik')
                                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                                                                <i class="fas fa-shield-alt me-1"></i>Kritik
                                                            </span>
                                                            @break
                                                        @case('yuksek')
                                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                                                <i class="fas fa-exclamation-triangle me-1"></i>Yüksek
                                                            </span>
                                                            @break
                                                        @case('orta')
                                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                                                <i class="fas fa-clock me-1"></i>Orta
                                                            </span>
                                                            @break
                                                        @case('dusuk')
                                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                                                <i class="fas fa-chevron-down me-1"></i>Düşük
                                                            </span>
                                                            @break
                                                        @default
                                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                                {{ ucfirst($ticket->priority) }}
                                                            </span>
                                                    @endswitch
                                                </td>
                                                <td data-label="Durum">
                                                    @php
                                                        $statusConfig = [
                                                            'acik' => ['color' => 'primary', 'icon' => 'fas fa-spinner', 'text' => 'Açık'],
                                                            'cevaplandi' => ['color' => 'warning', 'icon' => 'fas fa-check-circle', 'text' => 'Cevaplandı'],
                                                            'kapali' => ['color' => 'danger', 'icon' => 'fas fa-times-circle', 'text' => 'Kapatıldı']
                                                        ];
                                                        $currentStatus = $statusConfig[$ticket->status] ?? ['color' => 'secondary', 'icon' => 'fas fa-question', 'text' => $ticket->status];
                                                    @endphp
                                                    
                                                    <span class="badge bg-{{ $currentStatus['color'] }} bg-opacity-10 text-{{ $currentStatus['color'] }} border border-{{ $currentStatus['color'] }} border-opacity-25">
                                                        <i class="{{ $currentStatus['icon'] }} me-1"></i>{{ $currentStatus['text'] }}
                                                    </span>
                                                </td>
                                                <td data-label="Oluşturma">
                                                    <div class="text-nowrap">
                                                        <div class="fw-medium">{{ $ticket->created_at->format('d.m.Y') }}</div>
                                                        <small class="text-muted">{{ $ticket->created_at->format('H:i') }}</small>
                                                    </div>
                                                </td>
                                                <td data-label="Son Yanıt">
                                                    <div class="text-nowrap">
                                                        @if($ticket->last_reply_at)
                                                            <div class="fw-medium">{{ $ticket->last_reply_at->format('d.m.Y') }}</div>
                                                            <small class="text-muted">{{ $ticket->last_reply_at->format('H:i') }}</small>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td data-label="İşlemler" class="text-center">
                                                    <div class="d-flex gap-1 justify-content-center">
                                                         <a href="{{ route('super.admin.destek.show', $ticket->id) }}" 
                                                            class="btn btn-sm btn-outline-primary rounded-pill px-3" 
                                                            title="Detay">
                                                                <i class="fas fa-eye"></i>
                                                         </a>
                                                        
                                                        @if($ticket->status == 'kapali')
                                                            <form action="{{ route('super.admin.destek.reopen', $ticket->id) }}" 
                                                                  method="POST" class="d-inline">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3" 
                                                                        onclick="return confirm('Bu talebi yeniden açmak istediğinizden emin misiniz?')" title="Aç">
                                                                    <i class="fas fa-undo me-1"></i>
                                                                </button>
                                                            </form>
                                                        @else
                                                            <form action="{{ route('super.admin.destek.close', $ticket->id) }}" 
                                                                  method="POST" class="d-inline">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3" 
                                                                        onclick="return confirm('Bu talebi kapatmak istediğinizden emin misiniz?')"  title="Kapat">
                                                                    <i class="fas fa-times me-1"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5 mx-3">
                                <div class="empty-state">
                                    <div class="empty-state-icon mb-4">
                                        <div class="avatar-xl mx-auto">
                                            <div class="avatar-title rounded-circle bg-light">
                                                <i class="fas fa-ticket-alt fa-2x text-muted"></i>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if(request()->hasAny(['status', 'priority', 'category', 'tenant_id', 'search']))
                                        <h5 class="text-muted mb-3">Filtrelere uygun destek talebi bulunamadı</h5>
                                        <p class="text-muted mb-4">Filtre kriterlerinizi değiştirerek tekrar deneyebilirsiniz.</p>
                                        <div class="mt-3">
                                            <a href="{{ route('super.admin.destek.index') }}" class="btn btn-primary">
                                                <i class="fas fa-refresh me-1"></i> Filtreleri Temizle
                                            </a>
                                        </div>
                                    @else
                                        <h5 class="text-muted mb-3">Henüz sistemde destek talebi bulunmuyor</h5>
                                        <p class="text-muted mb-4">Kullanıcılar destek talebi oluşturdukça burada görünecektir.</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var customSearchInput = $('input[name="search"]');
    var filterCard = $('.filter-card');
    var dataTableSearchBox = $('#superAdminSupportTable_filter');
    dataTableSearchBox.hide();

    var table = $('#superAdminSupportTable').DataTable({
        responsive: true,
        ordering: false, 
        paging: false,   
        info: false,   
        
        language: {
            "sEmptyTable":     "Tabloda herhangi bir veri mevcut değil",
            "sZeroRecords":    "Eşleşen kayıt bulunamadı",
        },

        "search": {
            "search": customSearchInput.val()
        },
        
        "drawCallback": function( settings ) {
            addEventListenersToRows();
        }
    });

    customSearchInput.on('keyup', function () {
        table.search(this.value).draw();
    });

    function addEventListenersToRows() {
        const tableRows = document.querySelectorAll('#superAdminSupportTable tbody tr');
        
        tableRows.forEach(function(row) {
            row.style.cursor = 'pointer';
            
            const detailButton = row.querySelector('a[title="Detay"]');
            
            if (detailButton) {
                const detailUrl = detailButton.getAttribute('href');
                
                const newRow = row.cloneNode(true);
                row.parentNode.replaceChild(newRow, row);

                newRow.addEventListener('click', function(e) {
                    if (e.target.closest('a, button, form')) {
                        return;
                    }
                    window.location.href = detailUrl;
                });
                
                newRow.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = 'rgba(0, 123, 255, 0.05)';
                });
                
                newRow.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                });
            }
        });
    }

    addEventListenersToRows();
});
</script>

@endsection