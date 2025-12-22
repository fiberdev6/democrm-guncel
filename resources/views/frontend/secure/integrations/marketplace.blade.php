@extends('frontend.secure.user_master')
@section('user')

<div class="page-content" id="passwords">
    <div class="container-fluid">
        <div class="row pageDetail">
            <div class="col-12">
                <div class="integration-marketplace">
                    <!-- Header -->
                    <div class="marketplace-header">
                        <div class="container">
                            <h1 class="marketplace-title">
                                <i class="fas fa-store"></i> Entegrasyon Mağazası
                            </h1>
                            <p class="marketplace-subtitle">{{ $tenant->firma_adi }} için mevcut entegrasyonları keşfedin</p>
                        </div>
                    </div>

                    <!-- Arama ve Filtreler -->
                    <div class="container">
                        <div class="search-filter-container d-flex align-items-center gap-3">
                            <!-- Arama -->
                            <div class="search-wrapper">
                                <form action="{{ route('tenant.integrations.marketplace', $tenant->id) }}" method="GET" id="searchForm">
                                    <input type="hidden" name="category" value="{{ request('category') }}">
                                    <input type="hidden" name="filter" value="{{ request('filter') }}">
                                    <input type="text" 
                                           name="search" 
                                           id="searchInput"
                                           class="search-input" 
                                           placeholder="Entegrasyon ara..." 
                                           value="{{ request('search') }}">
                                    <button type="submit" class="search-button btn-sm" id="searchButton">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <button type="button" class="clear-button btn-sm" id="clearButton">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </div>

                            <!-- Kategori Butonları -->
                            <div class="category-buttons">
                                <a href="{{ route('tenant.integrations.marketplace', ['tenant_id' => $tenant->id, 'filter' => request('filter')]) }}" 
                                   class="category-btn btn-sm {{ !request('category') || request('category') == 'all' ? 'active' : '' }}">
                                    <i class="fas fa-th"></i> Tümü
                                </a>
                                @foreach($categories as $key => $name)
                                    @if($key != 'all')
                                    <a href="{{ route('tenant.integrations.marketplace', ['tenant_id' => $tenant->id, 'category' => $key, 'search' => request('search'), 'filter' => request('filter')]) }}"
                                       class="category-btn btn-sm {{ request('category') == $key ? 'active' : '' }}">
                                        @if($key == 'invoice')
                                            <i class="fas fa-file-invoice"></i>
                                        @elseif($key == 'sms')
                                            <i class="fas fa-sms"></i>
                                        @elseif($key == 'santral')
                                            <i class="fas fa-calculator"></i>
                                        @else
                                            <i class="fas fa-puzzle-piece"></i>
                                        @endif
                                        {{ $name }}
                                    </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <!-- İstatistikler -->
                        <div class="stats-bar">
                            <div class="stats-count">
                                <strong>{{ $integrations->count() }}</strong> Entegrasyon Bulundu
                            </div>
                            <a href="{{ route('tenant.integrations.marketplace', ['tenant_id' => $tenant->id, 'filter' => request('filter') == 'active' ? null : 'active', 'category' => request('category'), 'search' => request('search')]) }}" 
                               class="my-integrations-btn btn-sm {{ request('filter') == 'active' ? 'active' : '' }}">
                                <i class="fas fa-check-circle"></i>
                                Aktif Entegrasyonlarım 
                                <span class="badge" style="color: #000;background:#fff">{{ $activeIntegrationsCount }}</span>
                            </a>
                        </div>

                        <!-- Aktif Filtre Bildirimi -->
                        @if(request('filter') == 'active')
                        <div class="filter-active-banner">
                            <div class="filter-active-banner-content">
                                <div class="filter-active-banner-icon">
                                    <i class="fas fa-filter"></i>
                                </div>
                                <div class="filter-active-banner-text">
                                    <h4 style="color: #fff">Aktif Entegrasyonlar Gösteriliyor</h4>
                                    <p>Sadece satın aldığınız ve aktif olan entegrasyonları görüyorsunuz</p>
                                </div>
                            </div>
                            <a href="{{ route('tenant.integrations.marketplace', ['tenant_id' => $tenant->id, 'category' => request('category'), 'search' => request('search')]) }}" 
                               class="clear-filter-btn btn-sm">
                                <i class="fas fa-times"></i> Filtreyi Kaldır
                            </a>
                        </div>
                        @endif

                        <!-- Entegrasyonlar Grid -->
                        @if($integrations->count() > 0)
                        <div class="integration-grid">
                            @foreach($integrations as $integration)
                            <div class="integration-card {{ in_array($integration->id, $activeIntegrationIds) ? 'active-integration' : '' }}" 
                                 data-url="{{ route('tenant.integrations.show', [$tenant->id, $integration->slug]) }}">
                            
                                @if($integration->logo)
                                <img src="{{ asset($integration->logo) }}" alt="{{ $integration->name }}" class="integration-logo">
                                @else
                                <div class="integration-logo d-flex align-items-center justify-content-center">
                                    <i class="fas fa-puzzle-piece fa-2x text-muted"></i>
                                </div>
                                @endif

                                <h3 class="integration-name">{{ $integration->name }}</h3>

                                <p class="integration-description">
                                    {!! Illuminate\Support\Str::limit(strip_tags($integration->explanation ?? 'Bu entegrasyon için açıklama bulunmuyor.'), 100, '...') !!}
                                </p>

                                <div class="integration-footer">
                                    <span class="integration-category">
                                        {{ $categories[$integration->category] ?? $integration->category }}
                                    </span>

                                   @if(in_array($integration->id, $activeIntegrationIds))
                                        <span class="integration-price free" >
                                            <i class="fas fa-check-circle"></i> Aktif
                                        </span>
                                    @else
                                        @if($integration->price > 0)
                                            <span class="integration-price">{{ number_format($integration->price, 2) }} ₺</span>
                                        @else
                                            <span class="integration-price free">
                                                <i class="fas fa-gift"></i> Ücretsiz
                                            </span>
                                        @endif
                                    @endif
                                </div>

                                <div class="action-buttons">
                                    
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="no-results">
                            <i class="fas fa-search fa-3x"></i>
                            <h4>Sonuç Bulunamadı</h4>
                            @if(request('filter') == 'active')
                                <p>Henüz aktif entegrasyonunuz bulunmuyor.</p>
                                <a href="{{ route('tenant.integrations.marketplace', $tenant->id) }}" class="btn-activate" style="display: inline-block; width: auto; padding: 12px 32px;">
                                    <i class="fas fa-store"></i> Tüm Entegrasyonları Keşfet
                                </a>
                            @else
                                <p>Aradığınız kriterlere uygun entegrasyon bulunamadı.</p>
                                <a href="{{ route('tenant.integrations.marketplace', $tenant->id) }}" class="btn-activate" style="display: inline-block; width: auto; padding: 12px 32px;">
                                    <i class="fas fa-redo"></i> Tüm Entegrasyonları Göster
                                </a>
                            @endif
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
    function toggleSearchButtons() {
        const searchValue = $('#searchInput').val();
        if (searchValue && searchValue.trim() !== '') {
            $('#searchButton').hide();
            $('#clearButton').show();
        } else {
            $('#searchButton').show();
            $('#clearButton').hide();
        }
    }

    toggleSearchButtons();

    $('#searchInput').on('input', function() {
        toggleSearchButtons();
    });

    $('#clearButton').on('click', function() {
        $('#searchInput').val('');
        toggleSearchButtons();
        $('#searchForm').submit();
    });

    $('.integration-card').on('click', function(e) {
        if (!$(e.target).closest('.action-buttons').length) {
            const url = $(this).data('url');
            if (url) {
                window.location.href = url;
            }
        }
    });

    $('.action-buttons a').on('click', function(e) {
        e.stopPropagation();
    });
});
</script>

@endsection