@extends('frontend.secure.user_master')
@section('user')
<div class="page-content usersupport-show-page" id="supportUserPageShow">
    <div class="container-fluid">
        <!-- Başlık -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between card-header-custom2">
                    <span class="mb-sm-0 text-dark">
                        Destek Talebi: {{ $ticket->ticket_number }}
                    </span>
                    <div class="page-title-right" style="font-size: 12px;">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('secure.home', Auth::user()->tenant_id) }}" class="text-decoration-none">Ana Sayfa</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('support.index', Auth::user()->tenant_id) }}" class="text-decoration-none">Destek Taleplerim</a></li>
                            <li class="breadcrumb-item active">{{ $ticket->ticket_number }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
         @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show alert-modern" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
            </div>
        @endif
         @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show alert-modern" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
            </div>
        @endif

        <!-- Talep Bilgileri Header -->
        <div class="row">
            <div class="col-12">
                <div class="ticket-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-3 text-dark">{{ $ticket->subject }}</h3>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge status-badge-large bg-secondary">
                                    <i class="fas fa-circle me-1"></i>{{ $ticket->status_text }}
                                </span>

                                <span class="badge status-badge-large bg-secondary">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    @switch($ticket->priority)
                                        @case('acil') Acil @break
                                        @case('kritik') Kritik @break
                                        @case('yuksek') Yüksek @break
                                        @case('orta') Orta @break
                                        @case('dusuk') Düşük @break
                                        @default {{ ucfirst($ticket->priority) }} @break
                                    @endswitch
                                </span>

                                @switch($ticket->category)
                                    @case('teknik_sorun')
                                        <span class="badge status-badge-large bg-secondary">
                                            <i class="fas fa-cogs me-1"></i>Teknik Sorun
                                        </span>
                                        @break
                                    @case('faturalandirma')
                                        <span class="badge status-badge-large bg-secondary">
                                            <i class="fas fa-file-invoice me-1"></i>Faturalandırma
                                        </span>
                                        @break
                                    @case('ozellik_talebi')
                                        <span class="badge status-badge-large bg-secondary">
                                            <i class="fas fa-lightbulb me-1"></i>Özellik Talebi
                                        </span>
                                        @break
                                    @case('genel_destek')
                                        <span class="badge status-badge-large bg-secondary">
                                            <i class="fas fa-question-circle me-1"></i>Genel Destek
                                        </span>
                                        @break
                                    @case('hesap_sorunu')
                                        <span class="badge status-badge-large bg-secondary">
                                            <i class="fas fa-question-circle me-1"></i>Hesap Sorunu
                                        </span>
                                        @break
                                    @default
                                        <span class="badge status-badge-large bg-secondary">{{ $ticket->category }}</span>
                                @endswitch
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="card support-message border-0 bg-white">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-calendar-plus text-success me-2"></i>
                                        <small class="text-muted">
                                            <strong>Oluşturma:</strong> {{ $ticket->created_at->format('d.m.Y H:i') }}
                                        </small>
                                    </div>
                                    @if($ticket->last_reply_at)
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-clock text-info me-2"></i>
                                            <small class="text-muted">
                                                <strong>Son Yanıt:</strong> {{ $ticket->last_reply_at->format('d.m.Y H:i') }}
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mesajlar -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="message-container">
                            <!-- İlk Mesaj -->
                            <div class="d-flex mb-4">
                                <div class="user-avatar bg-primary me-3">
                                    {{ substr($ticket->user->name, 0, 1) }}
                                </div>
                                <div class="flex-grow-1">
                                    <div class="message-bubble">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-1 text-dark">{{ $ticket->user->name }}</h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ $ticket->created_at->format('d.m.Y H:i') }}
                                                </small>
                                            </div>
                                            <span class="badge bg-primary">İlk Mesaj</span>
                                        </div>
                                        <p class="mb-2 text-dark">{{ $ticket->description }}</p>
                                        
                                        @if($ticket->attachments)
                                            <div class="mt-3">
                                                <h6 class="text-muted mb-2">
                                                    <i class="fas fa-paperclip me-1"></i>Ekli Dosyalar:
                                                </h6>
                                                <div class="row g-2">
                                                    @foreach($ticket->attachments as $attachment)
                                                        <div class="col-md-4">
                                                            <a href="{{ route('support.download', [$ticket->tenant_id, $ticket->id, $attachment['stored_name']]) }}" 
                                                               class="attachment-card d-block text-decoration-none">
                                                                <div class="d-flex align-items-center">
                                                                    <i class="fas fa-file text-primary me-2"></i>
                                                                    <div class="flex-grow-1">
                                                                        <small class="text-dark d-block">{{ $attachment['original_name'] }}</small>
                                                                        <small class="text-muted">
                                                                            <i class="fas fa-download me-1"></i>İndir
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Yanıtlar -->
                            @if($ticket->replies->count() > 0)
                                <div class="mb-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <hr class="flex-grow-1">
                                        <span class="px-3 text-muted">
                                            <i class="fas fa-comments me-1"></i>
                                            {{ $ticket->replies->count() }} Yanıt
                                        </span>
                                        <hr class="flex-grow-1">
                                    </div>
                                </div>
                                
                                @foreach($ticket->replies as $reply)
                                    <div class="d-flex mb-4">
                                        <div class="user-avatar bg-{{ $reply->is_admin_reply ? 'success' : 'primary' }} me-3">
                                            {{ substr($reply->user->name == 'Super Administrator' ? 'SerbisCRM' : $reply->user->name, 0, 1) }}
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="message-bubble {{ $reply->is_admin_reply ? 'admin-bubble' : '' }}">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <h6 class="mb-1 text-dark">
                                                             {{ $reply->user->name == 'Super Administrator' ? 'SerbisCRM' : $reply->user->name }}
                                                            @if($reply->is_admin_reply)
                                                                <span class="badge bg-success ms-2">
                                                                    <i class="fas fa-shield-alt me-1"></i>Destek Ekibi
                                                                </span>
                                                            @endif
                                                        </h6>
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock me-1"></i>
                                                            {{ $reply->created_at->format('d.m.Y H:i') }}
                                                        </small>
                                                    </div>
                                                </div>
                                                <p class="mb-2 text-dark">{{ $reply->message }}</p>
                                                
                                                @if($reply->attachments)
                                                    <div class="mt-3">
                                                        <h6 class="text-muted mb-2">
                                                            <i class="fas fa-paperclip me-1"></i>Ekli Dosyalar:
                                                        </h6>
                                                        <div class="row g-2">
                                                            @foreach($reply->attachments as $attachment)
                                                                <div class="col-md-4">
                                                                    <a href="{{ route('support.download', [$ticket->tenant_id, $ticket->id, $attachment['stored_name']]) }}" 
                                                                       class="attachment-card d-block text-decoration-none">
                                                                        <div class="d-flex align-items-center">
                                                                            <i class="fas fa-file text-primary me-2"></i>
                                                                            <div class="flex-grow-1">
                                                                                <small class="text-dark d-block">{{ $attachment['original_name'] }}</small>
                                                                                <small class="text-muted">
                                                                                    <i class="fas fa-download me-1"></i>İndir
                                                                                </small>
                                                                            </div>
                                                                        </div>
                                                                    </a>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            <!-- Yanıt Formu -->
                            @if($ticket->canBeReplied())
                                <div class="mt-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <hr class="flex-grow-1">
                                        <span class="px-3 text-muted">
                                            <i class="fas fa-reply me-1"></i>
                                            Yanıt Ekle
                                        </span>
                                        <hr class="flex-grow-1">
                                    </div>
                                    
                                    <form  id="replyForm" action="{{ route('support.reply', [$ticket->tenant_id, $ticket->id]) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="form_token" id="formToken" value="">
                                        <div class="mb-3">
                                            <label class="form-label text-dark fw-semibold">
                                                <i class="fas fa-comment me-1"></i>Mesajınız
                                            </label>
                                            <textarea name="message" rows="4" 
                                                      class="form-control @error('message') is-invalid @enderror" 
                                                      placeholder="Yanıtınızı buraya yazın..." required>{{ old('message') }}</textarea>
                                            @error('message')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label text-dark fw-semibold">
                                                <i class="fas fa-paperclip me-1"></i>Dosya Ekle (Maksimum 3 adet)
                                            </label>
                                            <div class="file-upload-area">
                                                <input type="file" name="attachments[]" id="fileInput"
                                                       class="form-control @error('attachments.*') is-invalid @enderror" 
                                                       multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" style="display: none;">
                                                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('fileInput').click();">
                                                    <i class="fas fa-cloud-upload-alt me-2"></i>
                                                    Dosya Seç
                                                </button>
                                                <div class="mt-2 text-muted small">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Maksimum 3 dosya.Desteklenen formatlar: JPG, PNG, PDF, DOC, DOCX
                                                </div>
                                            </div>
                                            
                                            <div id="filePreview" class="mt-3" style="display: none;">
                                                <h6 class="text-muted">Seçilen Dosyalar:</h6>
                                                <div id="fileList"></div>
                                            </div>
                                            
                                            @error('attachments.*')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('support.index', Auth::user()->tenant_id) }}" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-arrow-left me-1"></i> Geri Dön
                                            </a>
                                            <button type="submit" class="btn btn-primary btn-sm px-4">
                                                Yanıt Gönder
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @else
                                <div class="alert alert-info border-0 shadow-sm">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle text-info me-3 fa-2x"></i>
                                        <div>
                                            <h6 class="alert-heading mb-1">Destek Talebi Kapatıldı</h6>
                                            <p class="mb-0">Bu destek talebi kapatılmıştır. Yeni yanıt ekleyemezsiniz.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center">
                                    <a href="{{ route('support.index', Auth::user()->tenant_id) }}" class="btn btn-outline-secondary px-4">
                                        <i class="fas fa-arrow-left me-1"></i> Destek Taleplerime Dön
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('fileInput').addEventListener('change', function() {
    const files = this.files;
    const maxFiles = 3;
    const filePreview = document.getElementById('filePreview');
    const fileList = document.getElementById('fileList');
    
    if (files.length > maxFiles) {
        alert('Maksimum ' + maxFiles + ' dosya seçebilirsiniz.');
        this.value = '';
        filePreview.style.display = 'none';
        return;
    }
    
    if (files.length > 0) {
        filePreview.style.display = 'block';
        fileList.innerHTML = '';
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileDiv = document.createElement('div');
            fileDiv.className = 'file-preview';
            fileDiv.innerHTML = `
                <i class="fas fa-file me-2"></i>
                <span>${file.name}</span>
                <button type="button" class="file-remove" onclick="removeFile(${i})">×</button>
            `;
            fileList.appendChild(fileDiv);
        }
    } else {
        filePreview.style.display = 'none';
    }
});

function removeFile(index) {
    const fileInput = document.getElementById('fileInput');
    const dt = new DataTransfer();
    const files = fileInput.files;
    
    for (let i = 0; i < files.length; i++) {
        if (i !== index) {
            dt.items.add(files[i]);
        }
    }
    
    fileInput.files = dt.files;
    fileInput.dispatchEvent(new Event('change'));
}
$(document).ready(function() {
    let formSubmitting = false;
    
    // Benzersiz token oluştur
    function generateToken() {
        return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    // Sayfa yüklendiğinde ilk token'ı oluştur
    $('#formToken').val(generateToken());
    
    // Form submit
    $('#replyForm').submit(function(event) {
        // Token kontrolü
        if (formSubmitting) {
            event.preventDefault();
            return false;
        }
        
        // Butonu disable et
        formSubmitting = true;
        $(this).find('button[type="submit"]').prop('disabled', true);
        
        // 5 saniye sonra yeniden aktif et
        setTimeout(function() {
            $('#formToken').val(generateToken());
            formSubmitting = false;
            $('#replyForm button[type="submit"]').prop('disabled', false);
        }, 5000);
        
        return true;
    });
});
</script>

@endsection