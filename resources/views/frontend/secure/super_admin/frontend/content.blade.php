@extends('frontend.secure.user_master')
@section('user')
<div class="page-content">
    <div class="container-fluid">
        
        <!-- Breadcrumb -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">İçerik Yönetimi</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('super.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">İçerik Yönetimi</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#hero" role="tab">
                                    <i class="fas fa-star me-1"></i> Hero Bölümü
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#headers" role="tab">
                                    <i class="fas fa-heading me-1"></i> Bölüm Başlıkları
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#contact" role="tab">
                                    <i class="fas fa-address-card me-1"></i> İletişim
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#cta" role="tab">
                                    <i class="fas fa-bullhorn me-1"></i> CTA
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#video" role="tab">
                                    <i class="fas fa-video me-1"></i> Video
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content p-3">
                            <!-- Hero Tab -->
                            <div class="tab-pane active" id="hero" role="tabpanel">
                                <h5 class="mb-3">Hero Bölümü</h5>
                                <form id="heroForm">
                                    <!-- Badge -->
                                    <div class="mb-3">
                                        <label class="form-label">Badge Metni (Üst küçük etiket)</label>
                                        <input type="text" class="form-control" id="hero_badge" value="{{ $hero->content['badge'] ?? '' }}" placeholder="Yeni: AI destekli servis yönetimi">
                                        <small class="text-muted">Örnek: Yeni: AI destekli servis yönetimi</small>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Başlık (İlk Kısım)</label>
                                            <input type="text" class="form-control" id="hero_title" value="{{ $hero->content['title'] ?? '' }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Vurgulanan Kelime</label>
                                            <input type="text" class="form-control" id="hero_highlight" value="{{ $hero->content['highlight'] ?? '' }}">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Açıklama</label>
                                        <textarea class="form-control" id="hero_description" rows="3">{{ $hero->content['description'] ?? '' }}</textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Birincil Buton Metni</label>
                                            <input type="text" class="form-control" id="hero_primary_btn" value="{{ $hero->content['primary_button_text'] ?? '' }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Birincil Buton İkon</label>
                                            <input type="text" class="form-control" id="hero_primary_icon" value="{{ $hero->content['primary_button_icon'] ?? '' }}">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">İkincil Buton Metni</label>
                                            <input type="text" class="form-control" id="hero_secondary_btn" value="{{ $hero->content['secondary_button_text'] ?? '' }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">İkincil Buton İkon</label>
                                            <input type="text" class="form-control" id="hero_secondary_icon" value="{{ $hero->content['secondary_button_icon'] ?? '' }}">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Özellikler (Her satıra bir özellik, formát: icon|text)</label>
                                        <textarea class="form-control" id="hero_features" rows="3" placeholder="fas fa-check-circle|14 gün ücretsiz">{{ isset($hero->content['features']) ? collect($hero->content['features'])->map(fn($f) => $f['icon'] . '|' . $f['text'])->implode("\n") : '' }}</textarea>
                                        <small class="text-muted">Örnek: fas fa-check-circle|14 gün ücretsiz</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Hero Resmi (Ana Dashboard)</label>
                                        <input type="file" class="form-control" id="hero_image_file" accept="image/*">
                                        <small class="text-muted">Önerilen boyut: 1200x800px (Boş bırakırsanız mevcut resim korunur)</small>
                                        
                                        @if(isset($hero->content['image']) && $hero->content['image'])
                                        <div class="mt-2">
                                            <p class="mb-1">Mevcut Resim:</p>
                                            <img src="{{ asset($hero->content['image']) }}" style="max-width: 200px; border-radius: 8px;">
                                        </div>
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Mobil Önizleme Resmi (Opsiyonel)</label>
                                        <input type="file" class="form-control" id="hero_mobile_image_file" accept="image/*">
                                        <small class="text-muted">Sağ altta gösterilecek mobil görünüm. Boş bırakırsanız ana resim kullanılır.</small>
                                        
                                        @if(isset($hero->content['mobile_image']) && $hero->content['mobile_image'])
                                        <div class="mt-2">
                                            <p class="mb-1">Mevcut Mobil Resim:</p>
                                            <img src="{{ asset($hero->content['mobile_image']) }}" style="max-width: 100px; border-radius: 8px;">
                                        </div>
                                        @endif
                                    </div>

                                    <hr class="my-4">
                                    
                                    <h6 class="mb-3">Floating Stat Card (Soldaki Yüzen Kart)</h6>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">İkon</label>
                                            <input type="text" class="form-control" id="floating_stat_icon" value="{{ $hero->content['floating_stat']['icon'] ?? 'fas fa-bolt' }}" placeholder="fas fa-bolt">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Sayı</label>
                                            <input type="text" class="form-control" id="floating_stat_number" value="{{ $hero->content['floating_stat']['number'] ?? '2,450+' }}" placeholder="2,450+">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Etiket</label>
                                            <input type="text" class="form-control" id="floating_stat_label" value="{{ $hero->content['floating_stat']['label'] ?? 'Aktif Servis' }}" placeholder="Aktif Servis">
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Kaydet
                                    </button>
                                </form>
                            </div>

                            <!-- Section Headers Tab -->
                            <div class="tab-pane" id="headers" role="tabpanel">
                                <h5 class="mb-4">Bölüm Başlıkları</h5>

                                <!-- Modules Header -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Özellikler Bölümü</h6>
                                    </div>
                                    <div class="card-body">
                                        <form class="header-form" data-section="modules">
                                            <div class="row">
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Badge</label>
                                                    <input type="text" class="form-control" name="badge" value="{{ $sectionHeaders->content['modules']['badge'] ?? '' }}">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Başlık</label>
                                                    <input type="text" class="form-control" name="title" value="{{ $sectionHeaders->content['modules']['title'] ?? '' }}">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Vurgulanan Kelime</label>
                                                    <input type="text" class="form-control" name="highlight" value="{{ $sectionHeaders->content['modules']['highlight'] ?? '' }}">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Alt Başlık</label>
                                                    <input type="text" class="form-control" name="subtitle" value="{{ $sectionHeaders->content['modules']['subtitle'] ?? '' }}">
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary">Kaydet</button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Sectors Header -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Sektörler Bölümü</h6>
                                    </div>
                                    <div class="card-body">
                                        <form class="header-form" data-section="sectors">
                                            <div class="row">
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Badge</label>
                                                    <input type="text" class="form-control" name="badge" value="{{ $sectionHeaders->content['sectors']['badge'] ?? '' }}">
                                                </div>
                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label">Başlık (İlk)</label>
                                                    <input type="text" class="form-control" name="title" value="{{ $sectionHeaders->content['sectors']['title'] ?? '' }}">
                                                </div>
                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label">Vurgulu</label>
                                                    <input type="text" class="form-control" name="highlight" value="{{ $sectionHeaders->content['sectors']['highlight'] ?? '' }}">
                                                </div>
                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label">Başlık (Son)</label>
                                                    <input type="text" class="form-control" name="title_end" value="{{ $sectionHeaders->content['sectors']['title_end'] ?? '' }}">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Alt Başlık</label>
                                                    <input type="text" class="form-control" name="subtitle" value="{{ $sectionHeaders->content['sectors']['subtitle'] ?? '' }}">
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary">Kaydet</button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Integrations Header -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Entegrasyonlar Bölümü</h6>
                                    </div>
                                    <div class="card-body">
                                        <form class="header-form" data-section="integrations">
                                            <div class="row">
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Badge</label>
                                                    <input type="text" class="form-control" name="badge" value="{{ $sectionHeaders->content['integrations']['badge'] ?? '' }}">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Başlık</label>
                                                    <input type="text" class="form-control" name="title" value="{{ $sectionHeaders->content['integrations']['title'] ?? '' }}">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Vurgulanan Kelime</label>
                                                    <input type="text" class="form-control" name="highlight" value="{{ $sectionHeaders->content['integrations']['highlight'] ?? '' }}">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Alt Başlık</label>
                                                    <input type="text" class="form-control" name="subtitle" value="{{ $sectionHeaders->content['integrations']['subtitle'] ?? '' }}">
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary">Kaydet</button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Testimonials Header -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Yorumlar Bölümü</h6>
                                    </div>
                                    <div class="card-body">
                                        <form class="header-form" data-section="testimonials">
                                            <div class="row">
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Badge</label>
                                                    <input type="text" class="form-control" name="badge" value="{{ $sectionHeaders->content['testimonials']['badge'] ?? '' }}">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Başlık</label>
                                                    <input type="text" class="form-control" name="title" value="{{ $sectionHeaders->content['testimonials']['title'] ?? '' }}">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Vurgulanan Kelime</label>
                                                    <input type="text" class="form-control" name="highlight" value="{{ $sectionHeaders->content['testimonials']['highlight'] ?? '' }}">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Alt Başlık</label>
                                                    <input type="text" class="form-control" name="subtitle" value="{{ $sectionHeaders->content['testimonials']['subtitle'] ?? '' }}">
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary">Kaydet</button>
                                        </form>
                                    </div>
                                </div>

                                <!-- FAQs Header -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">SSS Bölümü</h6>
                                    </div>
                                    <div class="card-body">
                                        <form class="header-form" data-section="faqs">
                                            <div class="row">
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Badge</label>
                                                    <input type="text" class="form-control" name="badge" value="{{ $sectionHeaders->content['faqs']['badge'] ?? '' }}">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Başlık</label>
                                                    <input type="text" class="form-control" name="title" value="{{ $sectionHeaders->content['faqs']['title'] ?? '' }}">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Vurgulanan Kelime</label>
                                                    <input type="text" class="form-control" name="highlight" value="{{ $sectionHeaders->content['faqs']['highlight'] ?? '' }}">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Alt Başlık</label>
                                                    <input type="text" class="form-control" name="subtitle" value="{{ $sectionHeaders->content['faqs']['subtitle'] ?? '' }}">
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary">Kaydet</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Tab -->
                            <div class="tab-pane" id="contact" role="tabpanel">
                                <h5 class="mb-3">İletişim Bölümü</h5>
                                <form id="contactForm">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Badge</label>
                                            <input type="text" class="form-control" id="contact_badge" value="{{ $contact->content['badge'] ?? '' }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Başlık</label>
                                            <input type="text" class="form-control" id="contact_title" value="{{ $contact->content['title'] ?? '' }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Vurgulanan Kelime</label>
                                            <input type="text" class="form-control" id="contact_highlight" value="{{ $contact->content['highlight'] ?? '' }}">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Alt Başlık</label>
                                        <input type="text" class="form-control" id="contact_subtitle" value="{{ $contact->content['subtitle'] ?? '' }}">
                                    </div>
                                    
                                    <hr class="my-4">
                                    <h6>İletişim Bilgileri</h6>
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Telefon İkon</label>
                                            <input type="text" class="form-control" id="phone_icon" value="{{ $contact->content['items'][0]['icon'] ?? '' }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Telefon Başlık</label>
                                            <input type="text" class="form-control" id="phone_title" value="{{ $contact->content['items'][0]['title'] ?? '' }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Telefon</label>
                                            <input type="text" class="form-control" id="phone_info" value="{{ $contact->content['items'][0]['info'] ?? '' }}">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Email İkon</label>
                                            <input type="text" class="form-control" id="email_icon" value="{{ $contact->content['items'][1]['icon'] ?? '' }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Email Başlık</label>
                                            <input type="text" class="form-control" id="email_title" value="{{ $contact->content['items'][1]['title'] ?? '' }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="text" class="form-control" id="email_info" value="{{ $contact->content['items'][1]['info'] ?? '' }}">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Adres İkon</label>
                                            <input type="text" class="form-control" id="address_icon" value="{{ $contact->content['items'][2]['icon'] ?? '' }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Adres Başlık</label>
                                            <input type="text" class="form-control" id="address_title" value="{{ $contact->content['items'][2]['title'] ?? '' }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Adres</label>
                                            <input type="text" class="form-control" id="address_info" value="{{ $contact->content['items'][2]['info'] ?? '' }}">
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">Kaydet</button>
                                </form>
                            </div>

                            <!-- CTA Tab -->
                            <div class="tab-pane" id="cta" role="tabpanel">
                                <h5 class="mb-3">Call to Action (CTA) Bölümü</h5>
                                <form id="ctaForm">
                                    <div class="mb-3">
                                        <label class="form-label">Başlık</label>
                                        <input type="text" class="form-control" id="cta_title" value="{{ $cta->content['title'] ?? '' }}">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Açıklama</label>
                                        <textarea class="form-control" id="cta_description" rows="2">{{ $cta->content['description'] ?? '' }}</textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Buton Metni</label>
                                            <input type="text" class="form-control" id="cta_button_text" value="{{ $cta->content['button_text'] ?? '' }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Buton İkon</label>
                                            <input type="text" class="form-control" id="cta_button_icon" value="{{ $cta->content['button_icon'] ?? '' }}">
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">Kaydet</button>
                                </form>
                            </div>

                            <!-- Video Tab -->
                            <div class="tab-pane" id="video" role="tabpanel">
                                <h5 class="mb-3">Video Modal</h5>
                                <form id="videoForm">
                                    <div class="mb-3">
                                        <label class="form-label">Video URL (YouTube Embed)</label>
                                        <input type="text" class="form-control" id="video_url" value="{{ $video->content['video_url'] ?? '' }}" placeholder="https://www.youtube.com/embed/VIDEO_ID">
                                        <small class="text-muted">YouTube embed URL'sini girin</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Video Başlığı</label>
                                        <input type="text" class="form-control" id="video_title" value="{{ $video->content['title'] ?? '' }}">
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">Kaydet</button>
                                </form>
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
    // Tab hash kontrolü
    let hash = window.location.hash;
    if (hash) {
        $('.nav-tabs a[href="' + hash + '"]').tab('show');
    }
    
    $('.nav-tabs a').on('shown.bs.tab', function(e) {
        window.location.hash = e.target.hash;
    });
});

// Hero Form Submit
$('#heroForm').on('submit', function(e) {
    e.preventDefault();
    
    // Features'ı parse et
    const featuresText = $('#hero_features').val();
    const features = featuresText.split('\n').filter(line => line.trim()).map(line => {
        const [icon, text] = line.split('|');
        return { icon: icon.trim(), text: text.trim() };
    });
    
    // FormData kullan (resim için)
    const formData = new FormData();
    formData.append('section', 'hero');
    formData.append('badge', $('#hero_badge').val());
    formData.append('title', $('#hero_title').val());
    formData.append('highlight', $('#hero_highlight').val());
    formData.append('description', $('#hero_description').val());
    formData.append('primary_button_text', $('#hero_primary_btn').val());
    formData.append('primary_button_icon', $('#hero_primary_icon').val());
    formData.append('secondary_button_text', $('#hero_secondary_btn').val());
    formData.append('secondary_button_icon', $('#hero_secondary_icon').val());
    formData.append('features', JSON.stringify(features));
    
    // Floating Stat
    const floatingStat = {
        icon: $('#floating_stat_icon').val(),
        number: $('#floating_stat_number').val(),
        label: $('#floating_stat_label').val()
    };
    formData.append('floating_stat', JSON.stringify(floatingStat));
    
    // Ana resim varsa ekle
    const imageFile = $('#hero_image_file')[0].files[0];
    if(imageFile) {
        formData.append('image', imageFile);
    }
    
    // Mobil resim varsa ekle
    const mobileImageFile = $('#hero_mobile_image_file')[0].files[0];
    if(mobileImageFile) {
        formData.append('mobile_image', mobileImageFile);
    }
    
    $.ajax({
        url: '{{ route("super.admin.frontend.content.update") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            toastr.success('Hero bölümü güncellendi');
            setTimeout(function() {
                location.reload();
            }, 1000);
        },
        error: function(xhr) {
            toastr.error('Bir hata oluştu');
        }
    });
});

// Section Headers Forms
$('.header-form').on('submit', function(e) {
    e.preventDefault();
    
    const form = $(this);
    const section = form.data('section');
    const formData = {};
    
    form.find('input').each(function() {
        const name = $(this).attr('name');
        const value = $(this).val();
        if(name) {
            formData[name] = value;
        }
    });
    
    // Mevcut section_headers'ı al
    let currentData = {};
    
    @if($sectionHeaders && $sectionHeaders->content)
        currentData = @json($sectionHeaders->content);
    @endif
    
    if(!currentData || typeof currentData !== 'object' || Array.isArray(currentData)) {
        currentData = {
            stats: {},
            modules: {},
            sectors: {},
            integrations: {},
            testimonials: {},
            faqs: {}
        };
    }
    
    currentData[section] = formData;
    
    const data = {
        section: 'section_headers',
        content: currentData
    };
    
    $.ajax({
        url: '{{ route("super.admin.frontend.content.update") }}',
        method: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            toastr.success(section.charAt(0).toUpperCase() + section.slice(1) + ' bölüm başlığı güncellendi');
            setTimeout(function() {
                location.reload();
            }, 1000);
        },
        error: function(xhr) {
            console.error('Hata:', xhr.responseText);
            toastr.error('Bir hata oluştu');
        }
    });
});

// Contact Form Submit
$('#contactForm').on('submit', function(e) {
    e.preventDefault();
    
    const data = {
        section: 'contact',
        content: {
            badge: $('#contact_badge').val(),
            title: $('#contact_title').val(),
            highlight: $('#contact_highlight').val(),
            subtitle: $('#contact_subtitle').val(),
            items: [
                {
                    icon: $('#phone_icon').val(),
                    title: $('#phone_title').val(),
                    info: $('#phone_info').val()
                },
                {
                    icon: $('#email_icon').val(),
                    title: $('#email_title').val(),
                    info: $('#email_info').val()
                },
                {
                    icon: $('#address_icon').val(),
                    title: $('#address_title').val(),
                    info: $('#address_info').val()
                }
            ]
        }
    };
    
    $.ajax({
        url: '{{ route("super.admin.frontend.content.update") }}',
        method: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            toastr.success('İletişim bölümü güncellendi');
        },
        error: function(xhr) {
            toastr.error('Bir hata oluştu');
        }
    });
});

// CTA Form Submit
$('#ctaForm').on('submit', function(e) {
    e.preventDefault();
    
    const data = {
        section: 'cta',
        content: {
            title: $('#cta_title').val(),
            description: $('#cta_description').val(),
            button_text: $('#cta_button_text').val(),
            button_icon: $('#cta_button_icon').val()
        }
    };
    
    $.ajax({
        url: '{{ route("super.admin.frontend.content.update") }}',
        method: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            toastr.success('CTA bölümü güncellendi');
        },
        error: function(xhr) {
            toastr.error('Bir hata oluştu');
        }
    });
});

// Video Form Submit
$('#videoForm').on('submit', function(e) {
    e.preventDefault();
    
    const data = {
        section: 'video',
        content: {
            video_url: $('#video_url').val(),
            title: $('#video_title').val()
        }
    };
    
    $.ajax({
        url: '{{ route("super.admin.frontend.content.update") }}',
        method: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            toastr.success('Video bölümü güncellendi');
        },
        error: function(xhr) {
            toastr.error('Bir hata oluştu');
        }
    });
});
</script>
@endsection