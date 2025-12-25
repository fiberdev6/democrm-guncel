@extends('frontend.secure.user_master')
@section('user')
<div class="page-content">
    <div class="container-fluid">
        
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Hakkımızda İçerik Yönetimi</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('super.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Hakkımızda</li>
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
                                <a class="nav-link active" data-bs-toggle="tab" href="#hero" role="tab">
                                    <i class="fas fa-star me-1"></i> Hero Bölümü
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#mission-vision" role="tab">
                                    <i class="fas fa-bullseye me-1"></i> Misyon & Vizyon
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#story" role="tab">
                                    <i class="fas fa-book me-1"></i> Hikayemiz
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#values" role="tab">
                                    <i class="fas fa-gem me-1"></i> Değerlerimiz
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#stats" role="tab">
                                    <i class="fas fa-chart-bar me-1"></i> İstatistikler
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#team" role="tab">
                                    <i class="fas fa-users me-1"></i> Ekibimiz
                                </a>
                            </li>
                        </ul>

                        <form action="{{ route('super.admin.frontend.about-content.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="tab-content p-3">
                                
                                <!-- HERO TAB -->
                                <div class="tab-pane active" id="hero" role="tabpanel">
                                    <h5 class="mb-3">Hero Bölümü</h5>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Badge Metni</label>
                                        <input type="text" class="form-control" name="hero_badge" value="{{ $about->content['hero']['badge'] ?? 'Türkiye\'nin Teknik Servis Yazılımı' }}">
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Başlık (1. Kısım)</label>
                                            <input type="text" class="form-control" name="hero_title" value="{{ $about->content['hero']['title'] ?? 'Teknik Servislerin' }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Başlık Vurgulu (2. Kısım)</label>
                                            <input type="text" class="form-control" name="hero_title_highlight" value="{{ $about->content['hero']['title_highlight'] ?? 'Dijital Dönüşüm' }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Başlık (3. Kısım)</label>
                                            <input type="text" class="form-control" name="hero_title_suffix" value="{{ $about->content['hero']['title_suffix'] ?? 'Ortağı' }}">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Açıklama</label>
                                        <textarea class="form-control" name="hero_description" rows="3">{{ $about->content['hero']['description'] ?? '' }}</textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Hero Görseli</label>
                                        @if(isset($about->content['hero']['image']))
                                            <div class="mb-2">
                                                <img src="{{ asset($about->content['hero']['image']) }}" style="height: 100px; border-radius: 5px;">
                                            </div>
                                        @endif
                                        <input type="file" class="form-control" name="hero_image" accept="image/*">
                                        <input type="hidden" name="hero_image_current" value="{{ $about->content['hero']['image'] ?? '' }}">
                                    </div>

                                    <h6 class="mt-4">İstatistik Kartları</h6>
                                    <div id="heroStats">
                                        @if(isset($about->content['hero']['stats']))
                                            @foreach($about->content['hero']['stats'] as $index => $stat)
                                                <div class="card mb-2 hero-stat-item">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-3">
                                                                <label class="form-label">İkon</label>
                                                                <input type="text" class="form-control hero-stat-icon" value="{{ $stat['icon'] }}">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label">Sayı</label>
                                                                <input type="text" class="form-control hero-stat-number" value="{{ $stat['number'] }}">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label">Etiket</label>
                                                                <input type="text" class="form-control hero-stat-label" value="{{ $stat['label'] }}">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <label class="form-label">Renk</label>
                                                                <select class="form-control hero-stat-color">
                                                                    <option value="blue" {{ ($stat['color'] ?? 'blue') == 'blue' ? 'selected' : '' }}>Mavi</option>
                                                                    <option value="orange" {{ ($stat['color'] ?? '') == 'orange' ? 'selected' : '' }}>Turuncu</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-1">
                                                                <label class="form-label">&nbsp;</label>
                                                                <button type="button" class="btn btn-danger btn-sm w-100 remove-hero-stat">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-secondary btn-sm" id="addHeroStat">
                                        <i class="fas fa-plus me-1"></i> İstatistik Ekle
                                    </button>
                                </div>

                                <!-- MISSION & VISION TAB -->
                                <div class="tab-pane" id="mission-vision" role="tabpanel">
                                    <h5 class="mb-3">Misyon & Vizyon</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Misyon</h6>
                                            <div class="mb-3">
                                                <label class="form-label">İkon</label>
                                                <input type="text" class="form-control" name="mission_icon" value="{{ $about->content['mission']['icon'] ?? 'fas fa-bullseye' }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Başlık</label>
                                                <input type="text" class="form-control" name="mission_title" value="{{ $about->content['mission']['title'] ?? 'Misyonumuz' }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Açıklama</label>
                                                <textarea class="form-control" name="mission_text" rows="5">{{ $about->content['mission']['text'] ?? '' }}</textarea>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <h6>Vizyon</h6>
                                            <div class="mb-3">
                                                <label class="form-label">İkon</label>
                                                <input type="text" class="form-control" name="vision_icon" value="{{ $about->content['vision']['icon'] ?? 'fas fa-eye' }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Başlık</label>
                                                <input type="text" class="form-control" name="vision_title" value="{{ $about->content['vision']['title'] ?? 'Vizyonumuz' }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Açıklama</label>
                                                <textarea class="form-control" name="vision_text" rows="5">{{ $about->content['vision']['text'] ?? '' }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- STORY TAB -->
                                <div class="tab-pane" id="story" role="tabpanel">
                                    <h5 class="mb-3">Hikayemiz</h5>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Hikaye Görseli</label>
                                        @if(isset($about->content['story']['image']))
                                            <div class="mb-2">
                                                <img src="{{ asset($about->content['story']['image']) }}" style="height: 100px; border-radius: 5px;">
                                            </div>
                                        @endif
                                        <input type="file" class="form-control" name="story_image" accept="image/*">
                                        <input type="hidden" name="story_image_current" value="{{ $about->content['story']['image'] ?? '' }}">
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Vurgu İkon</label>
                                            <input type="text" class="form-control" name="story_highlight_icon" value="{{ $about->content['story']['highlight_icon'] ?? 'fas fa-lightbulb' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Vurgu Metni</label>
                                            <input type="text" class="form-control" name="story_highlight_text" value="{{ $about->content['story']['highlight_text'] ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Bölüm Başlığı</label>
                                        <input type="text" class="form-control" name="story_title" value="{{ $about->content['story']['title'] ?? 'Hikayemiz' }}">
                                    </div>

                                    <h6 class="mt-4">Zaman Çizelgesi</h6>
                                    <div id="storyTimeline">
                                        @if(isset($about->content['story']['timeline']))
                                            @foreach($about->content['story']['timeline'] as $index => $item)
                                                <div class="card mb-2 story-timeline-item">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-2">
                                                                <label class="form-label">Yıl/Dönem</label>
                                                                <input type="text" class="form-control story-year" value="{{ $item['year'] }}">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label">Başlık</label>
                                                                <input type="text" class="form-control story-item-title" value="{{ $item['title'] }}">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Açıklama</label>
                                                                <textarea class="form-control story-text" rows="2">{{ $item['text'] }}</textarea>
                                                            </div>
                                                            <div class="col-md-1">
                                                                <label class="form-label">&nbsp;</label>
                                                                <button type="button" class="btn btn-danger btn-sm w-100 remove-story-item">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-secondary btn-sm" id="addStoryItem">
                                        <i class="fas fa-plus me-1"></i> Zaman Öğesi Ekle
                                    </button>
                                </div>

                                <!-- VALUES TAB -->
                                <div class="tab-pane" id="values" role="tabpanel">
                                    <h5 class="mb-3">Değerlerimiz</h5>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Bölüm Başlığı</label>
                                            <input type="text" class="form-control" name="values_title" value="{{ $about->content['values']['title'] ?? 'Değerlerimiz' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Açıklama</label>
                                            <input type="text" class="form-control" name="values_description" value="{{ $about->content['values']['description'] ?? '' }}">
                                        </div>
                                    </div>

                                    <h6>Değer Kartları</h6>
                                    <div id="valuesItems">
                                        @if(isset($about->content['values']['items']))
                                            @foreach($about->content['values']['items'] as $index => $item)
                                                <div class="card mb-2 value-item">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-2">
                                                                <label class="form-label">İkon</label>
                                                                <input type="text" class="form-control value-icon" value="{{ $item['icon'] }}">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label">Başlık</label>
                                                                <input type="text" class="form-control value-title" value="{{ $item['title'] }}">
                                                            </div>
                                                            <div class="col-md-5">
                                                                <label class="form-label">Açıklama</label>
                                                                <textarea class="form-control value-text" rows="2">{{ $item['text'] }}</textarea>
                                                            </div>
                                                            <div class="col-md-1">
                                                                <label class="form-label">Renk</label>
                                                                <select class="form-control value-color">
                                                                    <option value="blue" {{ $item['color'] == 'blue' ? 'selected' : '' }}>Mavi</option>
                                                                    <option value="orange" {{ $item['color'] == 'orange' ? 'selected' : '' }}>Turuncu</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-1">
                                                                <label class="form-label">&nbsp;</label>
                                                                <button type="button" class="btn btn-danger btn-sm w-100 remove-value-item">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-secondary btn-sm" id="addValueItem">
                                        <i class="fas fa-plus me-1"></i> Değer Ekle
                                    </button>
                                </div>

                                <!-- STATS TAB -->
                                <div class="tab-pane" id="stats" role="tabpanel">
                                    <h5 class="mb-3">İstatistikler</h5>
                                    
                                    <div id="statsItems">
                                        @if(isset($about->content['stats']))
                                            @foreach($about->content['stats'] as $index => $stat)
                                                <div class="card mb-2 stat-item">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-5">
                                                                <label class="form-label">Sayı</label>
                                                                <input type="text" class="form-control stat-number" value="{{ $stat['number'] }}">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Etiket</label>
                                                                <input type="text" class="form-control stat-label" value="{{ $stat['label'] }}">
                                                            </div>
                                                            <div class="col-md-1">
                                                                <label class="form-label">&nbsp;</label>
                                                                <button type="button" class="btn btn-danger btn-sm w-100 remove-stat-item">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-secondary btn-sm" id="addStatItem">
                                        <i class="fas fa-plus me-1"></i> İstatistik Ekle
                                    </button>
                                </div>

                                <!-- TEAM TAB -->
                                <div class="tab-pane" id="team" role="tabpanel">
                                    <h5 class="mb-3">Ekibimiz</h5>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Bölüm Başlığı</label>
                                            <input type="text" class="form-control" name="team_title" value="{{ $about->content['team']['title'] ?? 'Ekibimiz' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Açıklama</label>
                                            <input type="text" class="form-control" name="team_description" value="{{ $about->content['team']['description'] ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">İntro İkon</label>
                                            <input type="text" class="form-control" name="team_intro_icon" value="{{ $about->content['team']['intro_icon'] ?? 'fas fa-users-cog' }}">
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label">İntro Başlık</label>
                                            <input type="text" class="form-control" name="team_intro_title" value="{{ $about->content['team']['intro_title'] ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">İntro Metni</label>
                                        <textarea class="form-control" name="team_intro_text" rows="3">{{ $about->content['team']['intro_text'] ?? '' }}</textarea>
                                    </div>

                                    <h6 class="mt-4">Ekip Etiketleri</h6>
                                    <div id="teamTags">
                                        @if(isset($about->content['team']['tags']))
                                            @foreach($about->content['team']['tags'] as $index => $tag)
                                                <div class="card mb-2 team-tag-item">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-5">
                                                                <label class="form-label">İkon</label>
                                                                <input type="text" class="form-control team-tag-icon" value="{{ $tag['icon'] }}">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Metin</label>
                                                                <input type="text" class="form-control team-tag-text" value="{{ $tag['text'] }}">
                                                            </div>
                                                            <div class="col-md-1">
                                                                <label class="form-label">&nbsp;</label>
                                                                <button type="button" class="btn btn-danger btn-sm w-100 remove-team-tag">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-secondary btn-sm" id="addTeamTag">
                                        <i class="fas fa-plus me-1"></i> Etiket Ekle
                                    </button>
                                </div>

                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Kaydet 
                                </button>
                                <a href="{{ url('/hakkimizda') }}" target="_blank" class="btn btn-info">
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

    // ========== HERO STATS ==========
    $('#addHeroStat').on('click', function() {
        const html = `
            <div class="card mb-2 hero-stat-item">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">İkon</label>
                            <input type="text" class="form-control hero-stat-icon" placeholder="fas fa-users">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sayı</label>
                            <input type="text" class="form-control hero-stat-number" placeholder="500+">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Etiket</label>
                            <input type="text" class="form-control hero-stat-label" placeholder="Aktif Firma">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Renk</label>
                            <select class="form-control hero-stat-color">
                                <option value="blue">Mavi</option>
                                <option value="orange">Turuncu</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-sm w-100 remove-hero-stat">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#heroStats').append(html);
    });

    $(document).on('click', '.remove-hero-stat', function() {
        $(this).closest('.hero-stat-item').remove();
    });

    // ========== STORY TIMELINE ==========
    $('#addStoryItem').on('click', function() {
        const html = `
            <div class="card mb-2 story-timeline-item">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <label class="form-label">Yıl/Dönem</label>
                            <input type="text" class="form-control story-year" placeholder="2020">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Başlık</label>
                            <input type="text" class="form-control story-item-title" placeholder="Başlangıç">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Açıklama</label>
                            <textarea class="form-control story-text" rows="2"></textarea>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-sm w-100 remove-story-item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#storyTimeline').append(html);
    });

    $(document).on('click', '.remove-story-item', function() {
        $(this).closest('.story-timeline-item').remove();
    });

    // ========== VALUES ==========
    $('#addValueItem').on('click', function() {
        const html = `
            <div class="card mb-2 value-item">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <label class="form-label">İkon</label>
                            <input type="text" class="form-control value-icon" placeholder="fas fa-handshake">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Başlık</label>
                            <input type="text" class="form-control value-title" placeholder="Güvenilirlik">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Açıklama</label>
                            <textarea class="form-control value-text" rows="2"></textarea>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Renk</label>
                            <select class="form-control value-color">
                                <option value="blue">Mavi</option>
                                <option value="orange">Turuncu</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-sm w-100 remove-value-item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#valuesItems').append(html);
    });

    $(document).on('click', '.remove-value-item', function() {
        $(this).closest('.value-item').remove();
    });

    // ========== STATS ==========
    $('#addStatItem').on('click', function() {
        const html = `
            <div class="card mb-2 stat-item">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5">
                            <label class="form-label">Sayı</label>
                            <input type="text" class="form-control stat-number" placeholder="100+">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Etiket</label>
                            <input type="text" class="form-control stat-label" placeholder="Müşteri">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-sm w-100 remove-stat-item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#statsItems').append(html);
    });

    $(document).on('click', '.remove-stat-item', function() {
        $(this).closest('.stat-item').remove();
    });

    // ========== TEAM TAGS ==========
    $('#addTeamTag').on('click', function() {
        const html = `
            <div class="card mb-2 team-tag-item">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5">
                            <label class="form-label">İkon</label>
                            <input type="text" class="form-control team-tag-icon" placeholder="fas fa-code">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Metin</label>
                            <input type="text" class="form-control team-tag-text" placeholder="Yazılım Geliştirme">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-sm w-100 remove-team-tag">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#teamTags').append(html);
    });

    $(document).on('click', '.remove-team-tag', function() {
        $(this).closest('.team-tag-item').remove();
    });

    // ========== FORM SUBMIT ==========
    $('form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Hero Stats Topla
        const heroStats = [];
        $('.hero-stat-item').each(function() {
            const stat = {
                icon: $(this).find('.hero-stat-icon').val(),
                number: $(this).find('.hero-stat-number').val(),
                label: $(this).find('.hero-stat-label').val(),
                color: $(this).find('.hero-stat-color').val()
            };
            if(stat.icon && stat.number && stat.label) {
                heroStats.push(stat);
            }
        });
        
        // Story Timeline Topla
        const storyTimeline = [];
        $('.story-timeline-item').each(function() {
            const item = {
                year: $(this).find('.story-year').val(),
                title: $(this).find('.story-item-title').val(),
                text: $(this).find('.story-text').val()
            };
            if(item.year && item.title && item.text) {
                storyTimeline.push(item);
            }
        });
        
        // Values Items Topla
        const valuesItems = [];
        $('.value-item').each(function() {
            const item = {
                icon: $(this).find('.value-icon').val(),
                title: $(this).find('.value-title').val(),
                text: $(this).find('.value-text').val(),
                color: $(this).find('.value-color').val()
            };
            if(item.icon && item.title && item.text) {
                valuesItems.push(item);
            }
        });
        
        // Stats Items Topla
        const statsItems = [];
        $('.stat-item').each(function() {
            const stat = {
                number: $(this).find('.stat-number').val(),
                label: $(this).find('.stat-label').val()
            };
            if(stat.number && stat.label) {
                statsItems.push(stat);
            }
        });
        
        // Team Tags Topla
        const teamTags = [];
        $('.team-tag-item').each(function() {
            const tag = {
                icon: $(this).find('.team-tag-icon').val(),
                text: $(this).find('.team-tag-text').val()
            };
            if(tag.icon && tag.text) {
                teamTags.push(tag);
            }
        });
        
        // JSON Content Oluştur
        const content = {
            hero: {
                badge: $('[name="hero_badge"]').val(),
                title: $('[name="hero_title"]').val(),
                title_highlight: $('[name="hero_title_highlight"]').val(),
                title_suffix: $('[name="hero_title_suffix"]').val(),
                description: $('[name="hero_description"]').val(),
                image: $('[name="hero_image_current"]').val(),
                stats: heroStats
            },
            mission: {
                icon: $('[name="mission_icon"]').val(),
                title: $('[name="mission_title"]').val(),
                text: $('[name="mission_text"]').val()
            },
            vision: {
                icon: $('[name="vision_icon"]').val(),
                title: $('[name="vision_title"]').val(),
                text: $('[name="vision_text"]').val()
            },
            story: {
                image: $('[name="story_image_current"]').val(),
                highlight_icon: $('[name="story_highlight_icon"]').val(),
                highlight_text: $('[name="story_highlight_text"]').val(),
                title: $('[name="story_title"]').val(),
                timeline: storyTimeline
            },
            values: {
                title: $('[name="values_title"]').val(),
                description: $('[name="values_description"]').val(),
                items: valuesItems
            },
            stats: statsItems,
            team: {
                title: $('[name="team_title"]').val(),
                description: $('[name="team_description"]').val(),
                intro_icon: $('[name="team_intro_icon"]').val(),
                intro_title: $('[name="team_intro_title"]').val(),
                intro_text: $('[name="team_intro_text"]').val(),
                tags: teamTags
            }
        };
        
        formData.append('content', JSON.stringify(content));
        
        // AJAX Submit
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                toastr.success('Hakkımızda içeriği güncellendi!');
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