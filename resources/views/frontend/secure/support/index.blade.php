
@extends('frontend.secure.user_master')
@section('user')
<div class="page-content  usersupport-index-page" id="supportUserPageIndex">
    <div class="container-fluid">
        <!-- Başlık -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">
                        <i class="fas fa-headset text-primary me-2"></i>
                        Destek Taleplerim
                    </h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('secure.home', Auth::user()->tenant_id) }}">Ana Sayfa</a></li>
                            <li class="breadcrumb-item active">Destek Taleplerim</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

       <!-- Özet Kartları -->
        @if($totalTickets > 0)
            <div class="row summary-ticket-cards">
                <div class="col-xl-4 col-4 col-md-6 first-c">
                    <div class="card bg-ticket-total destek-page text-white border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between px-2 py-1 align-items-start h-100">
                                <div class="align-self-end">
                                    <h5 class="ticket-number mb-0 fw-bold">{{ $totalTickets }}</h5>
                                    <p class="ticket-text mb-0 small">Toplam Talep</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-4 col-4 col-md-6">
                    <div class="card bg-ticket-active destek-page text-white border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between px-2 py-1 align-items-start h-100">
                                <div class="align-self-end">
                                    <h5 class="ticket-number mb-0 fw-bold">{{ $statusCounts['acik'] + $statusCounts['cevaplandi'] }}</h5>
                                    <p class="ticket-text mb-0 small">Aktif Talep</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-4 col-4 col-md-6 last-c">
                    <div class="card bg-ticket-solved destek-page text-white border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between px-2 py-1 align-items-start h-100">
                                <div class="align-self-end">
                                    <h5 class="ticket-number mb-0 fw-bold">{{ $statusCounts['kapali'] }}</h5>
                                    <p class="ticket-text mb-0 small">Çözülen Talep</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        <!-- Ana Kart -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 pt-2 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mobil-h5 mb-0 d-flex align-items-center">
                                Destek Taleplerim
                                @if($tickets->count() > 0)
                                    <span class="badge bg-light text-dark ms-2">{{ $tickets->count() }}</span>
                                @endif
                            </h5>
                            <a href="{{ route('support.create', Auth::user()->tenant_id) }}" class="btn destek-link btn-secondary btn-sm shadow-sm">
                                <i class="fas fa-plus me-1"></i> Yeni Destek Talebi
                            </a>
                        </div>
                    </div>

                  <div class="card-body mt-3 p-0">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show m-3 border-0 shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
        </div>
    @endif

    @if($tickets->count() > 0)
        {{-- MASAÜSTÜ GÖRÜNÜM (TABLE) --}}
        <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover mb-0" id="supportTicketsTable">
                <thead class="table-light">
                    <tr>
                        <th class="border-0 fw-bold">Talep No</th>
                        <th class="border-0 fw-bold">Konu</th>
                        <th class="border-0 fw-bold">Kategori</th>
                        <th class="border-0 fw-bold">Öncelik</th>
                        <th class="border-0 fw-bold">Durum</th>
                        <th class="border-0 fw-bold">Oluşturma</th>
                        <th class="border-0 fw-bold">Son Yanıt</th>
                        <th class="border-0 fw-bold text-center">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                        <tr class="align-middle">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs me-2">
                                        <div class="avatar-title rounded-circle bg-light text-primary">
                                            <i class="fas fa-hashtag"></i>
                                        </div>
                                    </div>
                                    <span class="fw-bold">{{ $ticket->ticket_number }}</span>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <h6 class="mb-0">{{ Str::limit($ticket->subject, 40) }}</h6>
                                    <small class="text-muted">
                                        {{ Str::limit(strip_tags($ticket->description), 50) }}
                                    </small>
                                </div>
                            </td>
                            <td>
                                @switch($ticket->category)
                                    @case('teknik_sorun')
                                        <span class="badge bg-soft-danger text-danger border border-danger border-opacity-25">
                                            <i class="fas fa-cog me-1"></i>Teknik Sorun
                                        </span>
                                        @break
                                    @case('faturalandirma')
                                        <span class="badge bg-soft-warning text-warning border border-warning border-opacity-25">
                                            <i class="fas fa-credit-card me-1"></i>Faturalandırma
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
                                            <i class="fas fa-user-cog me-1"></i>Hesap Sorunu
                                        </span>
                                        @break
                                    @default
                                        <span class="badge bg-soft-secondary text-secondary">
                                            {{ $ticket->category }}
                                        </span>
                                @endswitch
                            </td>
                            <td>
                                @switch($ticket->priority)
                                    @case('acil')
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                            <i class="fas fa-exclamation-circle me-1"></i>Acil
                                        </span>
                                        @break
                                    @case('kritik')
                                        <span class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25">
                                            <i class="fas fa-shield-alt me-1"></i>Kritik
                                        </span>
                                        @break
                                    @case('yuksek')
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Yüksek
                                        </span>
                                        @break
                                    @case('orta')
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                            <i class="fas fa-clock me-1"></i>Orta
                                        </span>
                                        @break
                                    @case('dusuk')
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                            <i class="fas fa-chevron-down me-1"></i>Düşük
                                        </span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                            {{ ucfirst($ticket->priority) }}
                                        </span>
                                @endswitch
                            </td>

                            <td>
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
                            <td>
                                <div class="text-nowrap">
                                    <div class="fw-medium">{{ $ticket->created_at->format('d.m.Y') }}</div>
                                    <small class="text-muted">{{ $ticket->created_at->format('H:i') }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="text-nowrap">
                                    @if($ticket->last_reply_at)
                                        <div class="fw-medium">{{ $ticket->last_reply_at->format('d.m.Y') }}</div>
                                        <small class="text-muted">{{ $ticket->last_reply_at->format('H:i') }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('support.show', [$ticket->tenant_id, $ticket->id]) }}" 
                                       class="btn btn-sm btn-outline-primary rounded-pill px-3"  title="Detay">
                                        <i class="fas fa-eye me-1"></i>
                                    </a>
                                    
                                    @if(in_array($ticket->status, ['waiting_customer', 'open']))
                                        <a href="{{ route('support.show', [$ticket->tenant_id, $ticket->id]) }}#reply" 
                                           class="btn btn-sm btn-outline-success rounded-pill px-3">
                                            <i class="fas fa-reply me-1"></i> Yanıtla
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- MOBİL GÖRÜNÜM (LİSTE/KART) --}}
        <div class="d-lg-none">
            @foreach($tickets as $ticket)
                <div class="card m-2 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">
                             <div class="mb-2">
                                <span class="fw-bold">Konu:</span>
                                <h6 class="mb-0 destek-t-b d-inline">{{ Str::limit($ticket->subject, 25) }}</h6>
                             </div>
                             <span class="badge bg-light text-dark ms-2">#{{$ticket->ticket_number}}</span>
                        </div>

                        <div class="d-flex flex-column">
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Kategori:</span>
                                @switch($ticket->category)
                                    @case('teknik_sorun') <span class="fw-bold text-danger">Teknik Sorun</span> @break
                                    @case('faturalandirma') <span class="fw-bold text-warning">Faturalandırma</span> @break
                                    @case('ozellik_talebi') <span class="fw-bold text-info">Özellik Talebi</span> @break
                                    @case('genel_destek') <span class="fw-bold text-primary">Genel Destek</span> @break
                                    @case('hesap_sorunu') <span class="fw-bold text-secondary">Hesap Sorunu</span> @break
                                    @default <span>{{ $ticket->category }}</span>
                                @endswitch
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Öncelik:</span>
                                @switch($ticket->priority)
                                    @case('acil') <span class="fw-bold text-danger">Acil</span> @break
                                    @case('kritik') <span class="fw-bold text-dark">Kritik</span> @break
                                    @case('yuksek') <span class="fw-bold text-warning">Yüksek</span> @break
                                    @case('orta') <span class="fw-bold text-info">Orta</span> @break
                                    @case('dusuk') <span class="fw-bold text-secondary">Düşük</span> @break
                                    @default <span>{{ ucfirst($ticket->priority) }}</span>
                                @endswitch
                            </div>
                             <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Durum:</span>
                                 @php
                                     $statusConfig = [
                                         'acik' => ['color' => 'primary', 'text' => 'Açık'],
                                         'cevaplandi' => ['color' => 'warning', 'text' => 'Cevaplandı'],
                                         'kapali' => ['color' => 'danger', 'text' => 'Kapatıldı']
                                     ];
                                     $currentStatus = $statusConfig[$ticket->status] ?? ['color' => 'secondary', 'text' => $ticket->status];
                                 @endphp
                                <span class="fw-bold text-{{ $currentStatus['color'] }}">{{ $currentStatus['text'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between pt-2">
                                <span class="text-muted">Tarih:</span>
                                <span class="fw-bold">{{ $ticket->created_at->format('d.m.Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-end gap-2 p-2">
                         <a href="{{ route('support.show', [$ticket->tenant_id, $ticket->id]) }}" 
                           class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="fas fa-eye me-1"></i> Detay
                        </a>
                        @if(in_array($ticket->status, ['waiting_customer', 'open']))
                            <a href="{{ route('support.show', [$ticket->tenant_id, $ticket->id]) }}#reply" 
                               class="btn btn-sm btn-success rounded-pill px-3">
                                <i class="fas fa-reply me-1"></i> Yanıtla
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
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
                <h5 class="text-muted mb-3">Henüz bir destek talebiniz bulunmuyor</h5>
                <p class="text-muted mb-4">İlk destek talebinizi oluşturmak için yukarıdaki butona tıklayın.</p>
            </div>
        </div>
    @endif
</div>
                </div>
            </div>
        </div>

        <!-- Yardım Kartı -->
        <div class="row ">
            <div class="col-12">
                <div class="card py-3 border-0 bg-gradient-light">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-sm">
                                            <div class="avatar-title rounded-circle bg-primary bg-opacity-10 text-primary">
                                                <i class="fas fa-info-circle"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">Hızlı yardıma mı ihtiyacınız var?</h6>
                                        <p class="mb-0 text-muted">Sık sorulan sorularımızı inceleyerek hızlı çözüm bulabilirsiniz.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                                 <a href="{{ url('/#faq-section') }}" 
                                    class="btn btn-outline-primary" 
                                    target="_blank">
                                    <i class="fas fa-question-circle me-1"></i> SSS Sayfası
                                </a>
                                <a href="#" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-book me-1"></i> Kılavuz
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    var table = $('#supportTicketsTable').DataTable({
        processing: false,
        serverSide: false,
        ordering: false, // Tüm sıralamaları devre dışı bırak
        //order: [[0, 'desc']],
        
        language: {
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>"
            },
            sEmptyTable: "Henüz bir destek talebiniz bulunmuyor",
            sInfo: "Talep Sayısı: _TOTAL_",
            sInfoEmpty: "Kayıt yok",
            sSearch: "Talep Ara:",
            sZeroRecords: "Eşleşen kayıt bulunamadı",
            sLengthMenu: "_MENU_",
            oPaginate: {
                sFirst: "İlk",
                sLast: "Son",
                sNext: '<i class="fas fa-angle-right"></i>',
                sPrevious: '<i class="fas fa-angle-left"></i>'
            }
        },
        
        drawCallback: function() {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
        },
        
        dom: '<"top">rt<"bottom"i<"float-end"lp>><"clear">',
        lengthMenu: [ [25, 50, 100], [25, 50, 100] ]
    });
});
</script>
@endsection