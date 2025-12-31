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
                                <a class="nav-link" data-bs-toggle="tab" href="#meta" role="tab">
                                    <i class="fas fa-tags me-1"></i> Meta Tags (SEO)
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#google_tags" role="tab">
                                    <i class="fab fa-google me-1"></i> Google Tags
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
                            <!-- Meta Tags Tab -->
                            <div class="tab-pane" id="meta" role="tabpanel">
                                <h5 class="mb-3">
                                    <i class="fas fa-tags me-2"></i> Sayfa Meta Tags Yönetimi (SEO)
                                </h5>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Her sayfa için ayrı meta tags tanımlayabilirsiniz. Sosyal medyada paylaşıldığında bu bilgiler görünecektir.
                                </div>
                                
                                <!-- SAYFA SEÇİMİ -->
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-file me-1"></i> Düzenlemek İstediğiniz Sayfayı Seçin:
                                        </label>
                                        <select id="meta_page_select" class="form-select form-select-lg">
                                            <optgroup label="📄 ANA SAYFALAR">
                                                <option value="meta_tags_home">🏠 Ana Sayfa</option>
                                                <option value="meta_tags_about">ℹ️ Hakkımızda</option>
                                                <option value="meta_tags_features">⚡ Özellikler (Genel)</option>
                                                <option value="meta_tags_pricing">💰 Fiyatlandırma</option>
                                                <option value="meta_tags_sectors">🏭 Sektörler (Genel)</option>
                                                <option value="meta_tags_integrations">🔗 Entegrasyonlar</option>
                                                <option value="meta_tags_contact">📞 İletişim</option>
                                            </optgroup>
                                            
                                            @php
                                                // SEKTÖR DETAYLARINI ÇEK (sectors_content hariç)
                                                $sectors = App\Models\HomepageContent::where('section', 'LIKE', 'sector_%')
                                                        ->where('section', '!=', 'sectors_content')
                                                        ->where('is_active', true)
                                                        ->orderBy('created_at')
                                                        ->get();
                                            @endphp
                                            
                                            @if($sectors->count() > 0)
                                            <optgroup label="🏭 SEKTÖR DETAYLARI">
                                                @foreach($sectors as $sector)
                                                    @php
                                                        $content = $sector->content;
                                                        $sectorName = $content['title'] ?? 'İsimsiz Sektör';
                                                        $slug = str_replace('sector_', '', $sector->section);
                                                        $metaSection = 'meta_tags_sector_' . $slug;
                                                    @endphp
                                                    <option value="{{ $metaSection }}">🏭 {{ $sectorName }}</option>
                                                @endforeach
                                            </optgroup>
                                            @endif
                                            
                                            @php
                                                // ÖZELLİK DETAYLARINI ÇEK (features_content hariç)
                                                $features = App\Models\HomepageContent::where('section', 'LIKE', 'feature_%')
                                                            ->where('section', '!=', 'features_content')
                                                            ->where('is_active', true)
                                                            ->orderBy('created_at')
                                                            ->get();
                                            @endphp
                                            
                                            @if($features->count() > 0)
                                            <optgroup label="⚡ ÖZELLİK DETAYLARI">
                                                @foreach($features as $feature)
                                                    @php
                                                        $content = $feature->content;
                                                        $featureName = $content['title'] ?? 'İsimsiz Özellik';
                                                        $slug = str_replace('feature_', '', $feature->section);
                                                        $metaSection = 'meta_tags_feature_' . $slug;
                                                    @endphp
                                                    <option value="{{ $metaSection }}">⚡ {{ $featureName }}</option>
                                                @endforeach
                                            </optgroup>
                                            @endif
                                        </select>
                                        <small class="text-muted mt-2 d-block">
                                            Seçtiğiniz sayfanın meta tags bilgilerini aşağıda düzenleyebilirsiniz.
                                        </small>
                                    </div>
                                </div>
                                
                                <hr class="my-4">
                                
                                <!-- META TAGS FORM -->
                                <form id="metaForm">
                                    <input type="hidden" id="current_section" value="meta_tags_home">
                                    
                                    <h6 class="mb-3">
                                        <i class="fas fa-globe me-2"></i> Temel Meta Bilgileri
                                    </h6>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-heading me-1"></i> Site Başlığı (Title) *
                                        </label>
                                        <input type="text" class="form-control" id="meta_title" 
                                            placeholder="Serbis - Teknik Servis Yönetim Sistemi" required>
                                        <small class="text-muted">
                                            <i class="fas fa-lightbulb me-1"></i> 
                                            Tarayıcı sekmesinde ve Google'da görünür (50-60 karakter önerilir)
                                        </small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-align-left me-1"></i> Meta Description (Açıklama) *
                                        </label>
                                        <textarea class="form-control" id="meta_description" rows="3" 
                                                placeholder="Teknik servis işletmenizi dijitalleştirin..." required></textarea>
                                        <small class="text-muted">
                                            <i class="fas fa-lightbulb me-1"></i>
                                            Google arama sonuçlarında görünür (150-160 karakter önerilir)
                                        </small>
                                        <div class="mt-1">
                                            <span class="badge bg-secondary" id="desc_count">0 / 160 karakter</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-key me-1"></i> Meta Keywords (Anahtar Kelimeler)
                                        </label>
                                        <input type="text" class="form-control" id="meta_keywords" 
                                            placeholder="teknik servis yazılımı, servis yönetimi, crm">
                                        <small class="text-muted">Virgülle ayırarak yazın (10-15 kelime yeterli)</small>
                                    </div>
                                    
                                    <hr class="my-4">
                                    <h6 class="mb-3">
                                        <i class="fab fa-facebook me-2"></i> Open Graph (Facebook, WhatsApp, LinkedIn)
                                    </h6>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">OG:Title</label>
                                        <input type="text" class="form-control" id="og_title" 
                                            placeholder="Boş bırakırsanız Site Başlığı kullanılır">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">OG:Description</label>
                                        <textarea class="form-control" id="og_description" rows="2" 
                                                placeholder="Boş bırakırsanız Meta Description kullanılır"></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-image me-1"></i> OG:Image (Sosyal Medya Görseli)
                                        </label>
                                        <input type="file" class="form-control" id="og_image_file" accept="image/*">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Önerilen boyut: 1200x630px
                                        </small>
                                        
                                        <div id="current_og_image" class="mt-3" style="display: none;">
                                            <p class="mb-2 fw-bold">Mevcut Görsel:</p>
                                            <img id="og_image_preview" src="" style="max-width: 400px; border-radius: 8px; border: 2px solid #dee2e6;">
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    <h6 class="mb-3">
                                        <i class="fab fa-twitter me-2"></i> Twitter Card
                                    </h6>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Twitter:Title</label>
                                        <input type="text" class="form-control" id="twitter_title" 
                                            placeholder="Boş bırakırsanız Site Başlığı kullanılır">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Twitter:Description</label>
                                        <textarea class="form-control" id="twitter_description" rows="2" 
                                                placeholder="Boş bırakırsanız Meta Description kullanılır"></textarea>
                                    </div>
                                    
                                    
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Kaydet
                                    </button>
                                    
                                    <button type="button" class="btn btn-secondary" id="preview_meta">
                                        <i class="fas fa-eye me-1"></i> Önizleme
                                    </button>
                                </form>
                            </div>

                           <!-- Google Tags Tab -->
<div class="tab-pane" id="google_tags" role="tabpanel">
    <h5 class="mb-3">
        <i class="fab fa-google me-2"></i> Google Tags (Analytics & Tag Manager)
    </h5>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        Google Analytics ve Google Tag Manager kodlarınızı buraya ekleyin.
    </div>
    
    <form id="googleTagsForm">
        <!-- Google Analytics -->
        <div class="mb-4">
            <label class="form-label fw-bold">
                <i class="fas fa-chart-line me-1"></i> Google Analytics (gtag.js)
            </label>
            <textarea class="form-control font-monospace" id="google_analytics_code" rows="8" 
                placeholder="<!-- Google tag (gtag.js) -->
<script async src='https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX'></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-XXXXXXXXXX');
</script>">{{ $googleTags->content['analytics_code'] ?? '' }}</textarea>
        </div>
        
        <!-- GTM HEAD -->
        <div class="mb-4">
            <label class="form-label fw-bold">
                <i class="fas fa-tags me-1"></i> Google Tag Manager - HEAD Bölümü
            </label>
            <textarea class="form-control font-monospace" id="google_tag_manager_head" rows="8" 
                placeholder="<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-XXXXXXX');</script>
<!-- End Google Tag Manager -->">{{ $googleTags->content['tag_manager_head'] ?? '' }}</textarea>
            <small class="text-muted">Bu kod &lt;head&gt; bölümüne eklenecek</small>
        </div>
        
        <!-- GTM BODY -->
        <div class="mb-4">
            <label class="form-label fw-bold">
                <i class="fas fa-code me-1"></i> Google Tag Manager - BODY Bölümü
            </label>
            <textarea class="form-control font-monospace" id="google_tag_manager_body" rows="4" 
                placeholder="<!-- Google Tag Manager (noscript) -->
<noscript><iframe src='https://www.googletagmanager.com/ns.html?id=GTM-XXXXXXX'
height='0' width='0' style='display:none;visibility:hidden'></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->">{{ $googleTags->content['tag_manager_body'] ?? '' }}</textarea>
            <small class="text-muted">Bu kod &lt;body&gt; açılışından hemen sonra eklenecek</small>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Kaydet
        </button>
    </form>
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
function showToast(icon, title) {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
        width: '280px',                    // Daha da küçük
        padding: '10px 16px',              // Kompakt padding
        iconColor: icon === 'success' ? '#28a745' : 
                   icon === 'error' ? '#dc3545' : 
                   icon === 'info' ? '#17a2b8' : '#ffc107',
        customClass: {
            popup: 'mini-toast',
        },
        didOpen: (toast) => {
            toast.style.fontSize = '9px';
            toast.style.fontWeight = '300';
        }
    });
    Toast.fire({
        icon: icon,
        title: title
    });
}
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
    // ==========================================
    // META TAGS - SAYFA DEĞİŞTİĞİNDE VERİ YÜKLE
    // ==========================================
    
    // Sayfa seçimi değiştiğinde
    $('#meta_page_select').on('change', function() {
        const section = $(this).val();
        loadMetaData(section);
    });

    // Sayfa yüklendiğinde ilk veriyi getir (Ana Sayfa) - GECİKTİRİLDİ
    setTimeout(function() {
        loadMetaData('meta_tags_home');
    }, 500); // 500ms sonra yükle (Swal yüklendikten sonra)
    // Meta verilerini yükle
    function loadMetaData(section) {
        $('#current_section').val(section);
        
        // Loading göster
        $('#metaForm').css('opacity', '0.5');
        
        $.ajax({
            url: '{{ route("super.admin.frontend.content.get") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                section: section
            },
            success: function(response) {
                if(response.success && response.data) {
                    const content = response.data.content || {};
                    
                    // Form alanlarını doldur
                    $('#meta_title').val(content.title || '');
                    $('#meta_description').val(content.description || '');
                    $('#meta_keywords').val(content.keywords || '');
                    $('#og_title').val(content.og_title || '');
                    $('#og_description').val(content.og_description || '');
                    $('#twitter_title').val(content.twitter_title || '');
                    $('#twitter_description').val(content.twitter_description || '');
                    
                    // Karakter sayacını güncelle
                    updateDescCount();
                    
                    // OG Image varsa göster
                    if(content.og_image) {
                        $('#og_image_preview').attr('src', '{{ url("/") }}/' + content.og_image);
                        $('#current_og_image').show();
                    } else {
                        $('#current_og_image').hide();
                    }
                    
                    // Success toast
                    showToast('success', 'Sayfa verileri yüklendi');
                } else {
                    // Boş form
                    clearMetaForm();
                    showToast('info', 'Bu sayfa için henüz meta tags tanımlanmamış');
                }
                
                $('#metaForm').css('opacity', '1');
            },
            error: function() {
                showToast('error', 'Veriler yüklenirken hata oluştu');
                $('#metaForm').css('opacity', '1');
            }
        });
    }
    
    // Form alanlarını temizle
    function clearMetaForm() {
        $('#meta_title').val('');
        $('#meta_description').val('');
        $('#meta_keywords').val('');
        $('#og_title').val('');
        $('#og_description').val('');
        $('#twitter_title').val('');
        $('#twitter_description').val('');
        $('#og_image_file').val('');
        $('#current_og_image').hide();
        updateDescCount();
    }
    
    // ==========================================
    // META TAGS - FORM SUBMIT (KAYDET)
    // ==========================================
    
    $('#metaForm').on('submit', function(e) {
        e.preventDefault();
        
        const section = $('#current_section').val();
        const formData = new FormData();
        
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('section', section);
        formData.append('title', $('#meta_title').val());
        formData.append('description', $('#meta_description').val());
        formData.append('keywords', $('#meta_keywords').val());
        formData.append('og_title', $('#og_title').val());
        formData.append('og_description', $('#og_description').val());
        formData.append('twitter_title', $('#twitter_title').val());
        formData.append('twitter_description', $('#twitter_description').val());
        
        // OG Image dosyası varsa ekle
        const ogImageFile = $('#og_image_file')[0].files[0];
        if(ogImageFile) {
            formData.append('og_image', ogImageFile);
        }
        
        // Loading
        Swal.fire({
            title: 'Kaydediliyor...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: '{{ route("super.admin.frontend.content.update") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.close();
                
                if(response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Başarılı!',
                        text: response.message || 'Meta tags kaydedildi',
                        confirmButtonText: 'Tamam'
                    });
                    
                    // Yeni görseli göster
                    if(response.og_image) {
                        $('#og_image_preview').attr('src', '{{ url("/") }}/' + response.og_image);
                        $('#current_og_image').show();
                        $('#og_image_file').val('');
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Hata!',
                        text: response.message || 'Kaydetme başarısız',
                        confirmButtonText: 'Tamam'
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                
                let errorMsg = 'Kaydetme sırasında hata oluştu';
                if(xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: errorMsg,
                    confirmButtonText: 'Tamam'
                });
            }
        });
    });
    
    // ==========================================
    // KARAKTER SAYACI
    // ==========================================
    
    $('#meta_description').on('input', function() {
        updateDescCount();
    });
    
    function updateDescCount() {
        const length = $('#meta_description').val().length;
        $('#desc_count').text(length + ' / 160 karakter');
        
        if(length > 160) {
            $('#desc_count').removeClass('bg-secondary').addClass('bg-danger');
        } else if(length > 140) {
            $('#desc_count').removeClass('bg-secondary bg-danger').addClass('bg-warning');
        } else {
            $('#desc_count').removeClass('bg-warning bg-danger').addClass('bg-secondary');
        }
    }
    
    // ==========================================
    // ÖNİZLEME
    // ==========================================
    
    $('#preview_meta').on('click', function() {
        const title = $('#meta_title').val() || 'Başlık girilmedi';
        const description = $('#meta_description').val() || 'Açıklama girilmedi';
        const ogImage = $('#og_image_preview').attr('src') || '{{ asset("frontend/img/anasayfa2.png") }}';
        
        Swal.fire({
            title: 'Meta Tags Önizleme',
            html: `
                <div style="text-align: left; border: 1px solid #ddd; padding: 15px; border-radius: 8px;">
                    <img src="${ogImage}" style="width: 100%; border-radius: 4px; margin-bottom: 10px;">
                    <h6 style="color: #1a73e8; margin-bottom: 5px;">${title}</h6>
                    <p style="color: #5f6368; font-size: 14px; margin: 0;">${description}</p>
                    <p style="color: #5f6368; font-size: 12px; margin-top: 5px;">serbis.com.tr</p>
                </div>
            `,
            width: 600,
            confirmButtonText: 'Kapat'
        });
    });
    
// Google Tags Form Submit
$('#googleTagsForm').on('submit', function(e) {
    e.preventDefault();
    
    const data = {
        section: 'google_tags',
        content: {
            analytics_code: $('#google_analytics_code').val(),
            tag_manager_head: $('#google_tag_manager_head').val(),  // ✅ DOĞRU ALAN ADI
            tag_manager_body: $('#google_tag_manager_body').val()   // ✅ YENİ ALAN
        }
    };
    
    console.log('Gönderilen data:', data); // Debug için
    
    $.ajax({
        url: '{{ route("super.admin.frontend.content.update") }}',
        method: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('Başarılı response:', response); // Debug
            toastr.success('Google Tags başarıyla güncellendi!');
            
            // Sayfayı yenile (kayıt başarılıysa)
            setTimeout(function() {
                location.reload();
            }, 1500);
        },
        error: function(xhr) {
            console.error('Hata:', xhr.responseText); // Debug
            toastr.error('Bir hata oluştu: ' + (xhr.responseJSON?.message || 'Bilinmeyen hata'));
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