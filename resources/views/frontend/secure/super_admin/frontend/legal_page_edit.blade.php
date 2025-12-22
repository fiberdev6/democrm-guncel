@extends('frontend.secure.user_master')
@section('user')
<div class="page-content">
    <div class="container-fluid">
        
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">{{ $page ? 'Yasal Sayfa Düzenle' : 'Yasal Sayfa Oluştur' }}</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('super.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('super.admin.frontend.legal-pages') }}">Yasal Sayfalar</a></li>
                            <li class="breadcrumb-item active">{{ $page ? 'Düzenle' : 'Oluştur' }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('super.admin.frontend.legal-pages.store') }}" method="POST">
                            @csrf
                            
                            <div class="mb-3">
                                <label class="form-label">Sayfa Türü</label>
                                <select class="form-control" name="section" {{ $page ? 'disabled' : '' }} required>
                                    <option value="">Seçiniz</option>
                                    <option value="gizlilik" {{ $section == 'gizlilik' ? 'selected' : '' }}>Gizlilik Politikası</option>
                                    <option value="kullanim-kosullari" {{ $section == 'kullanim-kosullari' ? 'selected' : '' }}>Kullanım Şartları</option>
                                    <option value="kvkk" {{ $section == 'kvkk' ? 'selected' : '' }}>KVKK Aydınlatma Metni</option>
                                </select>
                                @if($page)
                                    <input type="hidden" name="section" value="{{ $section }}">
                                @endif
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Sayfa Başlığı</label>
                                <input type="text" class="form-control" name="title" value="{{ $page->content['title'] ?? '' }}" required placeholder="Örn: Gizlilik Politikası">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">İçerik</label>
                                <textarea class="form-control" id="legal_content" name="content">{{ $page->content['content'] ?? '' }}</textarea>
                                <small class="text-muted">HTML desteklidir. Editör ile düzenleyebilirsiniz.</small>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Kaydet
                                </button>
                                <a href="{{ route('super.admin.frontend.legal-pages') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Geri
                                </a>
                                @if($page)
                                    <a href="{{ url('/' . $section) }}" target="_blank" class="btn btn-info">
                                        <i class="fas fa-external-link-alt me-1"></i> Sayfayı Görüntüle
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Summernote CSS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">

<!-- Summernote JS -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<script>
$(document).ready(function() {
    $('#legal_content').summernote({
        height: 500,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['table', ['table']],
            ['insert', ['link', 'picture']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        callbacks: {
            onInit: function() {
                console.log('Summernote initialized');
            }
        }
    });
});
</script>
@endsection