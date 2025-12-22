{{-- resources/views/frontend/secure/super_admin/support/dashboard.blade.php --}}
@extends('frontend.secure.user_master')
@section('user')
<div class="page-content" id="supportDashboardPage">
    <div class="container-fluid">
        <!-- Başlık -->
        <div class="row custom-header">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">
                        
                        Destek Talepleri
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
        <!-- Hızlı Erişim -->
        <div class="row ">
            <div class="col-12">
                <div class="card quick-action-card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center px-2 py-2 mb-1">
                            <div class="d-flex align-items-center">
                                <div class="avatarsupport-sm me-3 d-none">
                                    <div class="avatarsupport-title bg-primary bg-opacity-10 text-primary rounded">
                                        <i class="fas fa-bolt"></i>
                                    </div>
                                </div>
                                <div>
                                    <h5 class="card-title mb-0">Hızlı Erişim</h5>
                                    <p class="text-muted mb-0 small">Sık kullanılan işlemler</p>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-lg-3 col-md-6">
                                <a href="{{ route('super.admin.destek.index') }}" class="quick-action-btn btn-all btn w-100 d-flex align-items-center">
                                    <div class="quick-action-content">
                                        <div class="quick-action-info d-flex align-items-center">
                                            <i class="fas fa-list me-2"></i>
                                            <div class="text-start">
                                                <div class="fw-semibold">Tüm Talepler</div>
                                                <small>Listeyi görüntüle</small>
                                            </div>
                                        </div>
                                        <div class="quick-action-count">
                                            <h4 class="fs-22 fw-semibold mb-0 text-white">
                                                <span class="counter-value">{{ $stats['total'] ?? 0 }}</span>
                                            </h4>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div  class="col-lg-3 col-md-6 support-admin-card">
                                <a href="{{ route('super.admin.destek.index', ['status' => 'acik']) }}" class="quick-action-btn btn-open btn w-100 d-flex align-items-center">
                                    <div class="quick-action-content">
                                        <div class="quick-action-info d-flex align-items-center">
                                            <i class="fas fa-clock me-2"></i>
                                            <div class="text-start">
                                                <div class="fw-semibold">Açık Talepler</div>
                                                <small>Bekleyen talepler</small>
                                            </div>
                                        </div>
                                        <div class="quick-action-count">
                                            <h4 class="fs-22 fw-semibold mb-0 text-white">
                                                <span class="counter-value">{{ $stats['open'] ?? 0 }}</span>
                                            </h4>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-6 support-admin-card">
                                <a href="{{ route('super.admin.destek.index', ['priority' => 'acil']) }}" class="quick-action-btn btn-urgent btn w-100 d-flex align-items-center">
                                    <div class="quick-action-content">
                                        <div class="quick-action-info d-flex align-items-center">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <div class="text-start">
                                                <div class="fw-semibold">Acil Talepler</div>
                                                <small>Öncelikli çözüm</small>
                                            </div>
                                        </div>
                                        <div class="quick-action-count">
                                            <h4 class="fs-22 fw-semibold mb-0 text-white">
                                                <span class="counter-value">{{ $stats['high_priority'] ?? 0 }}</span>
                                            </h4>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-6 support-admin-card">
                                <a href="{{ route('super.admin.destek.index', ['status' => 'cevaplandi']) }}" class="quick-action-btn btn-answered btn w-100 d-flex align-items-center">
                                    <div class="quick-action-content">
                                        <div class="quick-action-info d-flex align-items-center">
                                            <i class="fas fa-reply me-2"></i>
                                            <div class="text-start">
                                                <div class="fw-semibold">Cevaplanan</div>
                                                <small>Yanıtlanan talepler</small>
                                            </div>
                                        </div>
                                        <div class="quick-action-count">
                                            <h4 class="fs-22 fw-semibold mb-0 text-white">
                                                <span class="counter-value">{{ $stats['answered'] ?? 0 }}</span>
                                            </h4>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Son Talepler -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center px-1">
                            <div class="d-flex align-items-center py-2 px-2">
                                <div>
                                    <h5 class="card-title mb-0">Son Destek Talepleri</h5>
                                    <p class="text-muted mb-0 small">En son oluşturulan destek talepleri</p>
                                </div>
                            </div>
                            <a href="{{ route('super.admin.destek.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-right me-1"></i> 
                                <span class="d-sm-none">Görüntüle</span>
                                <span class="d-none d-sm-inline">Tümünü Görüntüle</span>
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        @if(isset($recentTickets) && $recentTickets->count() > 0)
                            <div class="table-responsive support-dashboard-table-modern">
                                <table id="destek-talepleri-tablosu" class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="border-0 fw-bold">Talep No</th>
                                            <th class="border-0 fw-bold">Firma</th>
                                            <th class="border-0 fw-bold">Kullanıcı</th>
                                            <th class="border-0 fw-bold">Konu</th>
                                            <th class="border-0 fw-bold">Durum</th>
                                            <th class="border-0 fw-bold">Öncelik</th>
                                            <th class="border-0 fw-bold">Tarih</th>
                                            <th class="border-0 fw-bold text-center">İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentTickets as $ticket)
                                            <tr class="align-middle">
                                                <td data-label="Talep No">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatarsupport-xs me-2">
                                                            <div class="avatarsupport-title rounded-circle bg-light text-primary">
                                                                <i class="fas fa-hashtag"></i>
                                                            </div>
                                                        </div>
                                                        <span class="fw-bold">{{ $ticket->ticket_number }}</span>
                                                    </div>
                                                </td>
                                                <td data-label="Firma">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatarsupport-xs me-2">
                                                            <div class="avatarsupport-title rounded-circle bg-soft-primary text-primary">
                                                                <i class="fas fa-building"></i>
                                                            </div>
                                                        </div>
                                                        <span>{{ $ticket->tenant->firma_adi ?? 'N/A' }}</span>
                                                    </div>
                                                </td>
                                                <td data-label="Kullanıcı">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatarsupport-xs me-2">
                                                            <div class="avatarsupport-title rounded-circle bg-soft-primary text-primary">
                                                                <i class="fas fa-user"></i>
                                                            </div>
                                                        </div>
                                                        <span>{{ $ticket->user->name }}</span>
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
                                                <td data-label="Durum">
                                                    @php
                                                       $statusConfig = [
                                                            'acik' => ['color' => 'primary', 'icon' => 'fas fa-spinner', 'text' => 'Açık'],
                                                            'cevaplandi' => ['color' => 'warning', 'icon' => 'fas fa-check-circle', 'text' => 'Cevaplandı'],
                                                            'kapali' => ['color' => 'danger', 'icon' => 'fas fa-times-circle', 'text' => 'Kapatıldı']
                                                        ];
                                                        $currentStatus = $statusConfig[$ticket->status] ?? ['color' => 'secondary', 'icon' => 'fas fa-question', 'text' => $ticket->status_text ?? $ticket->status];
                                                    @endphp
                                                    
                                                    <span class="badge badge-modern bg-{{ $currentStatus['color'] }} bg-opacity-10 text-{{ $currentStatus['color'] }} border border-{{ $currentStatus['color'] }} border-opacity-25">
                                                        <i class="{{ $currentStatus['icon'] }} me-1"></i>{{ $currentStatus['text'] }}
                                                    </span>
                                                </td>
                                                <td data-label="Öncelik">
                                                    @php
                                                        $priorityColors = [
                                                            'acil' => 'danger',
                                                            'kritik' => 'warning',
                                                            'yuksek' => 'primary',
                                                            'orta' => 'info',
                                                            'dusuk' => 'secondary'
                                                        ];
                                                        
                                                        $priorityIcons = [
                                                            'acil' => 'fas fa-exclamation-circle',
                                                            'kritik' => 'fas fa-shield-alt',
                                                            'yuksek' => 'fas fa-exclamation-triangle',
                                                            'orta' => 'fas fa-clock',
                                                            'dusuk' => 'fas fa-chevron-down'
                                                        ];
                                                    @endphp

                                                    <span class="badge badge-modern bg-{{ $priorityColors[$ticket->priority] ?? 'secondary' }} bg-opacity-10 text-{{ $priorityColors[$ticket->priority] ?? 'secondary' }} border border-{{ $priorityColors[$ticket->priority] ?? 'secondary' }} border-opacity-25">
                                                        <i class="{{ $priorityIcons[$ticket->priority] ?? 'fas fa-question' }} me-1"></i>
                                                        {{ $ticket->priority_text ?? ucfirst($ticket->priority) }}
                                                    </span>
                                                </td>
                                                <td data-label="Tarih">
                                                    <div class="text-nowrap">
                                                        <div class="fw-medium">{{ $ticket->created_at->format('d.m.Y') }}</div>
                                                        <small class="text-muted">{{ $ticket->created_at->format('H:i') }}</small>
                                                    </div>
                                                </td>
                                                <td data-label="İşlem" class="text-center">
                                                    <a href="{{ route('super.admin.destek.show', $ticket->id) }}" 
                                                    class="btn btn-sm btn-outline-primary rounded-pill px-3" 
                                                    title="Detay">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

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
                                        <div class="avatarsupport-xl mx-auto">
                                            <div class="avatarsupport-title rounded-circle bg-light">
                                                <i class="fas fa-inbox fa-2x text-muted"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <h5 class="text-muted mb-3">Henüz destek talebi bulunmuyor</h5>
                                    <p class="text-muted mb-4">Kullanıcılar destek talebi oluşturdukça burada görünecektir.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Yardımcı Bilgi Kartı -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 bg-gradient-light">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <div class="d-flex align-items-center pt-3">
                                    <div class="flex-shrink-0">
                                        <div class="avatarsupport-sm">
                                            <div class="avatarsupport-title rounded-circle bg-warning bg-opacity-10 text-warning">
                                                <i class="fas fa-lightbulb"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-1">
                                        <h6 class="mb-1">Super Admin Destek Yönetimi</h6>
                                        <p class="mb-0 text-muted">Sistemdeki tüm destek taleplerini buradan yönetebilir, istatistiklerini takip edebilir ve raporlar oluşturabilirsiniz.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                                <a href="{{ route('super.admin.destek.index') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-list me-1"></i> Tüm Talepler
                                </a>
                                <a href="#" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-chart-bar me-1"></i> Raporlar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


<script>
$(document).ready(function() {
    var table = $('#destek-talepleri-tablosu').DataTable({
        responsive: true,
        language: {
            "sDecimal":        ",",
            "sEmptyTable":     "Tabloda herhangi bir veri mevcut değil",
            "sInfo":           "_TOTAL_ kayıttan _START_ - _END_ arasındaki kayıtlar gösteriliyor",
            "sInfoEmpty":      "Kayıt yok",
            "sInfoFiltered":   "(_MAX_ kayıt içerisinden bulunan)",
            "sInfoPostFix":    "",
            "sInfoThousands":  ".",
            "sLengthMenu":     "Sayfada _MENU_ kayıt göster",
            "sLoadingRecords": "Yükleniyor...",
            "sProcessing":     "İşleniyor...",
            "sSearch":         "Ara:",
            "sZeroRecords":    "Eşleşen kayıt bulunamadı",
            "oPaginate": {
                "sFirst":    "İlk",
                "sLast":     "Son",
                "sNext":     "Sonraki",
                "sPrevious": "Önceki"
            },
            "oAria": {
                "sSortAscending":  ": artan sütun sıralamasını aktifleştir",
                "sSortDescending": ": azalan sütun sıralamasını aktifleştir"
            }
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        
        "drawCallback": function( settings ) {
            addEventListenersToRows();
        }
    });

    function addEventListenersToRows() {
        const tableRows = document.querySelectorAll('#destek-talepleri-tablosu tbody tr');
        
        tableRows.forEach(function(row) {
            row.style.cursor = 'pointer';
            row.style.transition = 'all 0.2s ease-in-out';
            const detailButton = row.querySelector('a[title="Detay"]');
            
            if (detailButton) {
                const detailUrl = detailButton.getAttribute('href');
                const newRow = row.cloneNode(true);
                row.parentNode.replaceChild(newRow, row);


                newRow.addEventListener('click', function(e) {
                    if (e.target.closest('a, button')) {
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
