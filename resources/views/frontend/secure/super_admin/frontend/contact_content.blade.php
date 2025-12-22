@extends('frontend.secure.user_master')
@section('user')
<div class="page-content">
    <div class="container-fluid">
        
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">İletişim Sayfası Yönetimi</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('super.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">İletişim</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        
                        <!-- Tabs -->
                        <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#page-header" role="tab">
                                    <i class="fas fa-heading me-1"></i> Sayfa Başlığı
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#contact-cards" role="tab">
                                    <i class="fas fa-id-card me-1"></i> İletişim Kartları
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#left-panel" role="tab">
                                    <i class="fas fa-info-circle me-1"></i> Sol Panel
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#form-section" role="tab">
                                    <i class="fas fa-envelope me-1"></i> Form Bölümü
                                </a>
                            </li>
                        </ul>

                        <form action="{{ route('super.admin.frontend.contact-content.update') }}" method="POST">
                            @csrf
                            
                            <div class="tab-content p-3">
                                
                                <!-- PAGE HEADER TAB -->
                                <div class="tab-pane active" id="page-header" role="tabpanel">
                                    <h5 class="mb-3">Hero Bölümü</h5>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Ana Başlık</label>
                                        <input type="text" class="form-control" name="header_title" 
                                               value="{{ $contact->content['page_header']['title'] ?? 'İletişim' }}">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Alt Başlık</label>
                                        <textarea class="form-control" name="header_subtitle" rows="2">{{ $contact->content['page_header']['subtitle'] ?? 'Sorularınız için bize ulaşın, size yardımcı olmaktan mutluluk duyarız.' }}</textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Breadcrumb - Ana Sayfa</label>
                                            <input type="text" class="form-control" name="header_breadcrumb_home" 
                                                   value="{{ $contact->content['page_header']['breadcrumb_home'] ?? 'Ana Sayfa' }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Breadcrumb - Aktif Sayfa</label>
                                            <input type="text" class="form-control" name="header_breadcrumb_current" 
                                                   value="{{ $contact->content['page_header']['breadcrumb_current'] ?? 'İletişim' }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- CONTACT CARDS TAB -->
                                <div class="tab-pane" id="contact-cards" role="tabpanel">
                                    <h5 class="mb-3">İletişim Kartları</h5>
                                    
                                    <div id="contactCardsContainer">
                                        @if(isset($contact->content['contact_cards']))
                                            @foreach($contact->content['contact_cards'] as $index => $card)
                                                <div class="card mb-3 contact-card-item">
                                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                        <span>{{ $card['title'] }}</span>
                                                        <button type="button" class="btn btn-danger btn-sm remove-contact-card">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-3">
                                                                <label class="form-label">İkon</label>
                                                                <input type="text" class="form-control card-icon" value="{{ $card['icon'] }}" placeholder="fas fa-envelope">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label">Başlık</label>
                                                                <input type="text" class="form-control card-title" value="{{ $card['title'] }}" placeholder="E-posta">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label">Metin</label>
                                                                <input type="text" class="form-control card-text" value="{{ $card['text'] }}" placeholder="info@example.com">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label">Link (opsiyonel)</label>
                                                                <input type="text" class="form-control card-link" value="{{ $card['link'] }}" placeholder="mailto:info@example.com">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-primary" id="addContactCard">
                                        <i class="fas fa-plus me-1"></i> Kart Ekle
                                    </button>
                                </div>

                                <!-- LEFT PANEL TAB -->
                                <div class="tab-pane" id="left-panel" role="tabpanel">
                                    <h5 class="mb-3">Sol Panel (Koyu Bölüm)</h5>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Başlık (Normal)</label>
                                            <input type="text" class="form-control" name="left_panel_title" 
                                                   value="{{ $contact->content['left_panel']['title'] ?? 'Serbis CRM ile' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Başlık (Vurgulu)</label>
                                            <input type="text" class="form-control" name="left_panel_title_highlight" 
                                                   value="{{ $contact->content['left_panel']['title_highlight'] ?? 'İşinizi Büyütün' }}">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Açıklama</label>
                                        <textarea class="form-control" name="left_panel_description" rows="3">{{ $contact->content['left_panel']['description'] ?? '' }}</textarea>
                                    </div>

                                    <h6 class="mt-4 mb-3">Panel Özellikleri</h6>
                                    <div id="panelFeaturesContainer">
                                        @if(isset($contact->content['left_panel']['features']))
                                            @foreach($contact->content['left_panel']['features'] as $index => $feature)
                                                <div class="row mb-2 panel-feature-item">
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control panel-feature-icon" value="{{ $feature['icon'] }}" placeholder="fas fa-check-circle">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control panel-feature-text" value="{{ $feature['text'] }}" placeholder="14 Gün Ücretsiz Deneme">
                                                    </div>
                                                    <div class="col-md-1">
                                                        <button type="button" class="btn btn-danger btn-sm w-100 remove-panel-feature">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-secondary btn-sm mb-4" id="addPanelFeature">
                                        <i class="fas fa-plus me-1"></i> Özellik Ekle
                                    </button>

                                    <h6 class="mt-4 mb-3">Mobil Uygulama Linkleri</h6>
                                    <div class="mb-3">
                                        <label class="form-label">Uygulama Label</label>
                                        <input type="text" class="form-control" name="left_panel_apps_label" 
                                               value="{{ $contact->content['left_panel']['apps_label'] ?? 'Mobil Uygulamamızı İndirin:' }}">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Google Play Link</label>
                                            <input type="text" class="form-control" name="left_panel_google_play_link" 
                                                   value="{{ $contact->content['left_panel']['google_play_link'] ?? '#' }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">App Store Link</label>
                                            <input type="text" class="form-control" name="left_panel_app_store_link" 
                                                   value="{{ $contact->content['left_panel']['app_store_link'] ?? '#' }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- FORM SECTION TAB -->
                                <div class="tab-pane" id="form-section" role="tabpanel">
                                    <h5 class="mb-3">Form Bölümü</h5>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Form Başlığı</label>
                                            <input type="text" class="form-control" name="form_title" 
                                                   value="{{ $contact->content['form_section']['title'] ?? 'Bize Ulaşın' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Form Alt Başlığı</label>
                                            <input type="text" class="form-control" name="form_subtitle" 
                                                   value="{{ $contact->content['form_section']['subtitle'] ?? 'Aşağıdaki formu doldurarak bize mesaj gönderin.' }}">
                                        </div>
                                    </div>

                                    <h6 class="mt-4 mb-3">Form Alanları</h6>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Ad-Soyad - Label</label>
                                            <input type="text" class="form-control" name="form_name_label" 
                                                   value="{{ $contact->content['form_section']['name_label'] ?? 'Ad-Soyad' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Ad-Soyad - Placeholder</label>
                                            <input type="text" class="form-control" name="form_name_placeholder" 
                                                   value="{{ $contact->content['form_section']['name_placeholder'] ?? 'Adınız Soyadınız' }}">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">E-posta - Label</label>
                                            <input type="text" class="form-control" name="form_email_label" 
                                                   value="{{ $contact->content['form_section']['email_label'] ?? 'E-posta' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">E-posta - Placeholder</label>
                                            <input type="text" class="form-control" name="form_email_placeholder" 
                                                   value="{{ $contact->content['form_section']['email_placeholder'] ?? 'ornek@email.com' }}">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Telefon - Label</label>
                                            <input type="text" class="form-control" name="form_phone_label" 
                                                   value="{{ $contact->content['form_section']['phone_label'] ?? 'Telefon' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Telefon - Placeholder</label>
                                            <input type="text" class="form-control" name="form_phone_placeholder" 
                                                   value="{{ $contact->content['form_section']['phone_placeholder'] ?? '0555 555 55 55' }}">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Mesaj - Label</label>
                                            <input type="text" class="form-control" name="form_message_label" 
                                                   value="{{ $contact->content['form_section']['message_label'] ?? 'Mesajınız' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Mesaj - Placeholder</label>
                                            <input type="text" class="form-control" name="form_message_placeholder" 
                                                   value="{{ $contact->content['form_section']['message_placeholder'] ?? 'Size nasıl yardımcı olabiliriz?' }}">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Buton Metni</label>
                                        <input type="text" class="form-control" name="form_button_text" 
                                               value="{{ $contact->content['form_section']['button_text'] ?? 'Mesajı Gönder' }}">
                                    </div>
                                </div>

                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Kaydet
                                </button>
                                <a href="{{ url('/iletisim') }}" target="_blank" class="btn btn-info">
                                    <i class="fas fa-external-link-alt me-1"></i> Sayfayı Görüntüle
                                </a>
                            </div>
                        </form>

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

    // ========== CONTACT CARDS ==========
    $('#addContactCard').on('click', function() {
        const html = `
            <div class="card mb-3 contact-card-item">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <span>Yeni Kart</span>
                    <button type="button" class="btn btn-danger btn-sm remove-contact-card">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">İkon</label>
                            <input type="text" class="form-control card-icon" placeholder="fas fa-envelope">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Başlık</label>
                            <input type="text" class="form-control card-title" placeholder="E-posta">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Metin</label>
                            <input type="text" class="form-control card-text" placeholder="info@example.com">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Link (opsiyonel)</label>
                            <input type="text" class="form-control card-link" placeholder="mailto:info@example.com">
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#contactCardsContainer').append(html);
    });

    $(document).on('click', '.remove-contact-card', function() {
        $(this).closest('.contact-card-item').remove();
    });

    // Kart başlığı değişince header'ı güncelle
    $(document).on('input', '.card-title', function() {
        const title = $(this).val() || 'Yeni Kart';
        $(this).closest('.contact-card-item').find('.card-header span').text(title);
    });

    // ========== PANEL FEATURES ==========
    $('#addPanelFeature').on('click', function() {
        const html = `
            <div class="row mb-2 panel-feature-item">
                <div class="col-md-5">
                    <input type="text" class="form-control panel-feature-icon" placeholder="fas fa-check-circle">
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control panel-feature-text" placeholder="14 Gün Ücretsiz Deneme">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm w-100 remove-panel-feature">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        $('#panelFeaturesContainer').append(html);
    });

    $(document).on('click', '.remove-panel-feature', function() {
        $(this).closest('.panel-feature-item').remove();
    });

    // ========== FORM SUBMIT ==========
    $('form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Page Header
        const pageHeader = {
            title: $('[name="header_title"]').val(),
            subtitle: $('[name="header_subtitle"]').val(),
            breadcrumb_home: $('[name="header_breadcrumb_home"]').val(),
            breadcrumb_current: $('[name="header_breadcrumb_current"]').val()
        };
        
        // Contact Cards Topla
        const contactCards = [];
        $('.contact-card-item').each(function() {
            const card = {
                icon: $(this).find('.card-icon').val(),
                title: $(this).find('.card-title').val(),
                text: $(this).find('.card-text').val(),
                link: $(this).find('.card-link').val()
            };
            
            if(card.title && card.text) {
                contactCards.push(card);
            }
        });
        
        // Panel Features Topla
        const panelFeatures = [];
        $('.panel-feature-item').each(function() {
            const feature = {
                icon: $(this).find('.panel-feature-icon').val(),
                text: $(this).find('.panel-feature-text').val()
            };
            
            if(feature.icon && feature.text) {
                panelFeatures.push(feature);
            }
        });
        
        // Left Panel
        const leftPanel = {
            title: $('[name="left_panel_title"]').val(),
            title_highlight: $('[name="left_panel_title_highlight"]').val(),
            description: $('[name="left_panel_description"]').val(),
            features: panelFeatures,
            apps_label: $('[name="left_panel_apps_label"]').val(),
            google_play_link: $('[name="left_panel_google_play_link"]').val(),
            app_store_link: $('[name="left_panel_app_store_link"]').val()
        };
        
        // Form Section
        const formSection = {
            title: $('[name="form_title"]').val(),
            subtitle: $('[name="form_subtitle"]').val(),
            name_label: $('[name="form_name_label"]').val(),
            name_placeholder: $('[name="form_name_placeholder"]').val(),
            email_label: $('[name="form_email_label"]').val(),
            email_placeholder: $('[name="form_email_placeholder"]').val(),
            phone_label: $('[name="form_phone_label"]').val(),
            phone_placeholder: $('[name="form_phone_placeholder"]').val(),
            message_label: $('[name="form_message_label"]').val(),
            message_placeholder: $('[name="form_message_placeholder"]').val(),
            button_text: $('[name="form_button_text"]').val()
        };
        
        // JSON Content Oluştur
        const content = {
            page_header: pageHeader,
            contact_cards: contactCards,
            left_panel: leftPanel,
            form_section: formSection
        };
        
        console.log('Gönderilecek Content:', content);
        
        formData.append('content', JSON.stringify(content));
        
        // AJAX Submit
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                toastr.success('İletişim sayfası içeriği güncellendi!');
                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                toastr.error('Bir hata oluştu!');
                console.error(xhr);
            }
        });
    });
});
</script>

@endsection