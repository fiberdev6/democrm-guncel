@extends('frontend.secure.user_master')
@section('user')
<div class="page-content">
    <div class="container-fluid">
        
        <!-- Breadcrumb -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Ana Sayfa Yönetimi</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('super.admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Ana Sayfa</li>
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
                                <a class="nav-link active" data-bs-toggle="tab" href="#stats" role="tab">
                                    <i class="fas fa-chart-line me-1"></i> İstatistikler
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#modules" role="tab">
                                    <i class="fas fa-cube me-1"></i> Modüller
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#sectors" role="tab">
                                    <i class="fas fa-industry me-1"></i> Sektörler
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#integrations" role="tab">
                                    <i class="fas fa-plug me-1"></i> Entegrasyonlar
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#testimonials" role="tab">
                                    <i class="fas fa-quote-left me-1"></i> Yorumlar
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#faqs" role="tab">
                                    <i class="fas fa-question-circle me-1"></i> SSS
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content p-3">
                            <!-- İstatistikler Tab -->
                            <div class="tab-pane active" id="stats" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5>İstatistikler</h5>
                                    <button class="btn btn-info btn-sm" onclick="addStat()">
                                        <i class="fas fa-plus me-1"></i> Yeni Ekle
                                    </button>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-middle"> 
                                        <thead class="table-dark">
                                            <tr>
                                                <th width="50" class="text-center">Sıra</th>
                                                <th>Sayı</th>
                                                <th>Etiket</th>
                                                <th width="120" class="text-center">Durum</th>
                                                <th width="120" class="text-center">İşlemler</th>
                                            </tr>
                                        </thead>
                                        <tbody id="statsTable">
                                            @foreach($stats as $stat)
                                            <tr>
                                                <td class="text-center fw-bold">{{ $stat->order }}</td>
                                                <td>{{ $stat->data['number'] ?? '' }}</td>
                                                <td>{{ $stat->data['label'] ?? '' }}</td>
                                        
                                                <td class="text-center">
                                                    <span class="badge rounded-pill bg-{{ $stat->is_active ? 'success' : 'danger' }} font-size-12" style="min-width: 60px;">
                                                        {{ $stat->is_active ? 'Aktif' : 'Pasif' }}
                                                    </span>
                                                </td>
                                                
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-3"> 
                                                        <button type="button" 
                                                                class="btn btn-link p-0" 
                                                                onclick="editStat({{ $stat->id }})" 
                                                                data-bs-toggle="tooltip" 
                                                                title="Düzenle">
                                                            <i class="fas fa-edit text-warning" ></i>
                                                        </button>
                                                        
                                                        <button type="button" 
                                                                class="btn btn-link p-0" 
                                                                onclick="deleteStat({{ $stat->id }})" 
                                                                data-bs-toggle="tooltip" 
                                                                title="Sil">
                                                            <i class="fas fa-trash-alt text-danger"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Modüller Tab -->
                                <div class="tab-pane" id="modules" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5>Modüller / Özellikler</h5>
                                        <button class="btn btn-info btn-sm" onclick="addModule()">
                                            <i class="fas fa-plus me-1"></i> Yeni Ekle
                                        </button>
                                    </div>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover align-middle">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th width="50" class="text-center">Sıra</th>
                                                    <th width="80" class="text-center">İkon</th>
                                                    <th>Başlık</th>
                                                    <th>Açıklama</th>
                                                    <th width="100" class="text-center">Renk</th>
                                                    <th width="120" class="text-center">Durum</th>
                                                    <th width="120" class="text-center">İşlemler</th>
                                                </tr>
                                            </thead>
                                            <tbody id="modulesTable">
                                                @foreach($modules as $module)
                                                <tr>
                                                    <td class="text-center fw-bold">{{ $module->order }}</td>
                                                    <td class="text-center">
                                                        <i class="{{ $module->data['icon'] ?? '' }}" style="color: {{ $module->data['color'] == 'orange' ? '#f37021' : '#49657B' }};"></i>
                                                    </td>
                                                    <td>{{ $module->data['title'] ?? '' }}</td>
                                                    <td>{{ Str::limit($module->data['description'] ?? '', 60) }}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-{{ $module->data['color'] == 'orange' ? 'warning' : 'primary' }}">
                                                            {{ $module->data['color'] == 'orange' ? 'Turuncu' : 'Mavi' }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge rounded-pill bg-{{ $module->is_active ? 'success' : 'danger' }} font-size-12" style="min-width: 60px;">
                                                            {{ $module->is_active ? 'Aktif' : 'Pasif' }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center gap-3">
                                                            <button type="button" 
                                                                    class="btn btn-link p-0" 
                                                                    onclick="editModule({{ $module->id }})" 
                                                                    data-bs-toggle="tooltip" 
                                                                    title="Düzenle">
                                                                <i class="fas fa-edit text-warning"></i>
                                                            </button>
                                                            
                                                            <button type="button" 
                                                                    class="btn btn-link p-0" 
                                                                    onclick="deleteModule({{ $module->id }})" 
                                                                    data-bs-toggle="tooltip" 
                                                                    title="Sil">
                                                                <i class="fas fa-trash-alt text-danger"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- Sektörler Tab -->
                                    <div class="tab-pane" id="sectors" role="tabpanel">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5>Sektörler</h5>
                                            <button class="btn btn-info btn-sm" onclick="addSector()">
                                                <i class="fas fa-plus me-1"></i> Yeni Ekle
                                            </button>
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover align-middle">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th width="50" class="text-center">Sıra</th>
                                                        <th width="100" class="text-center">Resim</th>
                                                        <th width="150">Slug</th>
                                                        <th>Başlık</th>
                                                        <th>Açıklama</th>
                                                        <th width="120" class="text-center">Durum</th>
                                                        <th width="120" class="text-center">İşlemler</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="sectorsTable">
                                                    @foreach($sectors as $sector)
                                                    <tr>
                                                        <td class="text-center fw-bold">{{ $sector->order }}</td>
                                                        <td class="text-center">
                                                            @if(isset($sector->data['image']) && file_exists(public_path($sector->data['image'])))
                                                                <img src="{{ asset($sector->data['image']) }}" alt="{{ $sector->data['title'] ?? '' }}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                                            @else
                                                                <div style="width: 60px; height: 60px; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                                    <i class="fas fa-image text-muted"></i>
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td><code>{{ $sector->data['slug'] ?? '' }}</code></td>
                                                        <td>{{ $sector->data['title'] ?? '' }}</td>
                                                        <td>{{ Str::limit($sector->data['description'] ?? '', 50) }}</td>
                                                        <td class="text-center">
                                                            <span class="badge rounded-pill bg-{{ $sector->is_active ? 'success' : 'danger' }} font-size-12" style="min-width: 60px;">
                                                                {{ $sector->is_active ? 'Aktif' : 'Pasif' }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="d-flex justify-content-center gap-3">
                                                                <button type="button" 
                                                                        class="btn btn-link p-0" 
                                                                        onclick="editSector({{ $sector->id }})" 
                                                                        data-bs-toggle="tooltip" 
                                                                        title="Düzenle">
                                                                    <i class="fas fa-edit text-warning"></i>
                                                                </button>
                                                                
                                                                <button type="button" 
                                                                        class="btn btn-link p-0" 
                                                                        onclick="deleteSector({{ $sector->id }})" 
                                                                        data-bs-toggle="tooltip" 
                                                                        title="Sil">
                                                                    <i class="fas fa-trash-alt text-danger"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <!-- Entegrasyonlar Tab -->
                                        <div class="tab-pane" id="integrations" role="tabpanel">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5>Entegrasyonlar</h5>
                                                <button class="btn btn-info btn-sm" onclick="addIntegration()">
                                                    <i class="fas fa-plus me-1"></i> Yeni Ekle
                                                </button>
                                            </div>
                                            
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-hover align-middle">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th width="50" class="text-center">Sıra</th>
                                                            <th width="80" class="text-center">İkon</th>
                                                            <th>Başlık</th>
                                                            <th>Açıklama</th>
                                                            <th width="100" class="text-center">Renk</th>
                                                            <th width="120" class="text-center">Durum</th>
                                                            <th width="120" class="text-center">İşlemler</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="integrationsTable">
                                                        @foreach($integrations as $integration)
                                                        <tr>
                                                            <td class="text-center fw-bold">{{ $integration->order }}</td>
                                                            <td class="text-center">
                                                                <i class="{{ $integration->data['icon'] ?? '' }}" style="color: {{ $integration->data['color'] == 'orange' ? '#f37021' : '#49657B' }};"></i>
                                                            </td>
                                                            <td>{{ $integration->data['title'] ?? '' }}</td>
                                                            <td>{{ Str::limit($integration->data['description'] ?? '', 60) }}</td>
                                                            <td class="text-center">
                                                                <span class="badge bg-{{ $integration->data['color'] == 'orange' ? 'warning' : 'primary' }}">
                                                                    {{ $integration->data['color'] == 'orange' ? 'Turuncu' : 'Mavi' }}
                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="badge rounded-pill bg-{{ $integration->is_active ? 'success' : 'danger' }} font-size-12" style="min-width: 60px;">
                                                                    {{ $integration->is_active ? 'Aktif' : 'Pasif' }}
                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                <div class="d-flex justify-content-center gap-3">
                                                                    <button type="button" 
                                                                            class="btn btn-link p-0" 
                                                                            onclick="editIntegration({{ $integration->id }})" 
                                                                            data-bs-toggle="tooltip" 
                                                                            title="Düzenle">
                                                                        <i class="fas fa-edit text-warning"></i>
                                                                    </button>
                                                                    
                                                                    <button type="button" 
                                                                            class="btn btn-link p-0" 
                                                                            onclick="deleteIntegration({{ $integration->id }})" 
                                                                            data-bs-toggle="tooltip" 
                                                                            title="Sil">
                                                                        <i class="fas fa-trash-alt text-danger"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                      <!-- Yorumlar Tab -->
                                    <div class="tab-pane" id="testimonials" role="tabpanel">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5>Müşteri Yorumları</h5>
                                            <button class="btn btn-info btn-sm" onclick="addTestimonial()">
                                                <i class="fas fa-plus me-1"></i> Yeni Ekle
                                            </button>
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover align-middle">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th width="50" class="text-center">Sıra</th>
                                                        <th width="80" class="text-center">İnisiyel</th>
                                                        <th>İsim</th>
                                                        <th>Pozisyon</th>
                                                        <th>Yorum</th>
                                                        <th width="100" class="text-center">Renk</th>
                                                        <th width="120" class="text-center">Durum</th>
                                                        <th width="120" class="text-center">İşlemler</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="testimonialsTable">
                                                    @foreach($testimonials as $testimonial)
                                                    <tr>
                                                        <td class="text-center fw-bold">{{ $testimonial->order }}</td>
                                                        <td class="text-center">
                                                            <div style="width: 28px; height: 28px; background: {{ $testimonial->data['color'] == 'blue' ? '#49657B' : '#f37021' }}; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; margin: 0 auto;">
                                                                {{ $testimonial->data['initials'] ?? '' }}
                                                            </div>
                                                        </td>
                                                        <td>{{ $testimonial->data['name'] ?? '' }}</td>
                                                        <td>{{ $testimonial->data['position'] ?? '' }}</td>
                                                        <td>{{ Str::limit($testimonial->data['quote'] ?? '', 80) }}</td>
                                                        <td class="text-center">
                                                            <span class="badge bg-{{ $testimonial->data['color'] == 'orange' ? 'warning' : 'primary' }}">
                                                                {{ $testimonial->data['color'] == 'orange' ? 'Turuncu' : 'Mavi' }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge rounded-pill bg-{{ $testimonial->is_active ? 'success' : 'danger' }} font-size-12" style="min-width: 60px;">
                                                                {{ $testimonial->is_active ? 'Aktif' : 'Pasif' }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="d-flex justify-content-center gap-3">
                                                                <button type="button" 
                                                                        class="btn btn-link p-0" 
                                                                        onclick="editTestimonial({{ $testimonial->id }})" 
                                                                        data-bs-toggle="tooltip" 
                                                                        title="Düzenle">
                                                                    <i class="fas fa-edit text-warning"></i>
                                                                </button>
                                                                
                                                                <button type="button" 
                                                                        class="btn btn-link p-0" 
                                                                        onclick="deleteTestimonial({{ $testimonial->id }})" 
                                                                        data-bs-toggle="tooltip" 
                                                                        title="Sil">
                                                                    <i class="fas fa-trash-alt text-danger"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>  
                                    <!-- SSS Tab -->
<div class="tab-pane" id="faqs" role="tabpanel">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>Sıkça Sorulan Sorular (SSS)</h5>
        <button class="btn btn-info btn-sm" onclick="addFaq()">
            <i class="fas fa-plus me-1"></i> Yeni Ekle
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th width="50" class="text-center">Sıra</th>
                    <th width="300">Soru</th>
                    <th>Cevap</th>
                    <th width="120" class="text-center">Durum</th>
                    <th width="120" class="text-center">İşlemler</th>
                </tr>
            </thead>
            <tbody id="faqsTable">
                @foreach($faqs as $faq)
                <tr>
                    <td class="text-center fw-bold">{{ $faq->order }}</td>
                    <td>{{ Str::limit($faq->data['question'] ?? '', 80) }}</td>
                    <td>{{ Str::limit($faq->data['answer'] ?? '', 100) }}</td>
                    <td class="text-center">
                        <span class="badge rounded-pill bg-{{ $faq->is_active ? 'success' : 'danger' }} font-size-12" style="min-width: 60px;">
                            {{ $faq->is_active ? 'Aktif' : 'Pasif' }}
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-3">
                            <button type="button" 
                                    class="btn btn-link p-0" 
                                    onclick="editFaq({{ $faq->id }})" 
                                    data-bs-toggle="tooltip" 
                                    title="Düzenle">
                                <i class="fas fa-edit text-warning"></i>
                            </button>
                            
                            <button type="button" 
                                    class="btn btn-link p-0" 
                                    onclick="deleteFaq({{ $faq->id }})" 
                                    data-bs-toggle="tooltip" 
                                    title="Sil">
                                <i class="fas fa-trash-alt text-danger"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

                            <!-- Diğer tablar için benzer yapı -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<!-- İstatistik Modal -->
<div class="modal fade" id="statModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statModalLabel">İstatistik Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statForm">
                <div class="modal-body">
                    <input type="hidden" id="stat_id" name="id">
                    
                    <div class="mb-3">
                        <label class="form-label">Sıra</label>
                        <input type="number" class="form-control" id="stat_order" name="order" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Sayı</label>
                        <input type="text" class="form-control" id="stat_number" name="number" placeholder="500+" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Etiket</label>
                        <input type="text" class="form-control" id="stat_label" name="label" placeholder="Aktif Firma" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select class="form-control" id="stat_status" name="is_active">
                            <option value="1">Aktif</option>
                            <option value="0">Pasif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modül Modal -->
<div class="modal fade" id="moduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="moduleModalLabel">Modül Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="moduleForm">
                <div class="modal-body">
                    <input type="hidden" id="module_id" name="id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sıra</label>
                            <input type="number" class="form-control" id="module_order" name="order" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Renk</label>
                            <select class="form-control" id="module_color" name="color" required>
                                <option value="blue">Mavi</option>
                                <option value="orange">Turuncu</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">İkon (FontAwesome)</label>
                        <input type="text" class="form-control" id="module_icon" name="icon" placeholder="fas fa-users" required>
                        <small class="text-muted">Örnek: fas fa-users, fas fa-boxes, fas fa-chart-line</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Başlık</label>
                        <input type="text" class="form-control" id="module_title" name="title" placeholder="Müşteri Yönetimi" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Açıklama</label>
                        <textarea class="form-control" id="module_description" name="description" rows="3" placeholder="Modül açıklaması..." required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select class="form-control" id="module_status" name="is_active">
                            <option value="1">Aktif</option>
                            <option value="0">Pasif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Sektör Modal -->
<div class="modal fade" id="sectorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sectorModalLabel">Sektör Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="sectorForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="sector_id" name="id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sıra</label>
                            <input type="number" class="form-control" id="sector_order" name="order" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" class="form-control" id="sector_slug" name="slug" placeholder="beyaz-esya" required>
                            <small class="text-muted">URL için kullanılır. Örnek: beyaz-esya, klima-sogutma</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Başlık</label>
                        <input type="text" class="form-control" id="sector_title" name="title" placeholder="Beyaz Eşya" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Açıklama</label>
                        <textarea class="form-control" id="sector_description" name="description" rows="2" placeholder="Kısa açıklama..." required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Resim</label>
                        <input type="file" class="form-control" id="sector_image" name="image" accept="image/*">
                        <small class="text-muted">Önerilen boyut: 800x600px</small>
                        <div id="current_image_preview" class="mt-2"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select class="form-control" id="sector_status" name="is_active">
                            <option value="1">Aktif</option>
                            <option value="0">Pasif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Entegrasyon Modal -->
<div class="modal fade" id="integrationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="integrationModalLabel">Entegrasyon Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="integrationForm">
                <div class="modal-body">
                    <input type="hidden" id="integration_id" name="id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sıra</label>
                            <input type="number" class="form-control" id="integration_order" name="order" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Renk</label>
                            <select class="form-control" id="integration_color" name="color" required>
                                <option value="blue">Mavi</option>
                                <option value="orange">Turuncu</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">İkon (FontAwesome)</label>
                        <input type="text" class="form-control" id="integration_icon" name="icon" placeholder="fas fa-file-invoice" required>
                        <small class="text-muted">Örnek: fas fa-plug, fas fa-sms, fas fa-envelope</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Başlık</label>
                        <input type="text" class="form-control" id="integration_title" name="title" placeholder="Paraşüt" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Açıklama</label>
                        <textarea class="form-control" id="integration_description" name="description" rows="3" placeholder="Entegrasyon açıklaması..." required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select class="form-control" id="integration_status" name="is_active">
                            <option value="1">Aktif</option>
                            <option value="0">Pasif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Yorum Modal -->
<div class="modal fade" id="testimonialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testimonialModalLabel">Müşteri Yorumu Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="testimonialForm">
                <div class="modal-body">
                    <input type="hidden" id="testimonial_id" name="id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sıra</label>
                            <input type="number" class="form-control" id="testimonial_order" name="order" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Renk</label>
                            <select class="form-control" id="testimonial_color" name="color" required>
                                <option value="blue">Mavi</option>
                                <option value="orange">Turuncu</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">İsim</label>
                            <input type="text" class="form-control" id="testimonial_name" name="name" placeholder="Ahmet Yılmaz" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">İnisiyel (Baş Harfler)</label>
                            <input type="text" class="form-control" id="testimonial_initials" name="initials" placeholder="AY" maxlength="3" required>
                            <small class="text-muted">Maksimum 3 karakter</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Pozisyon / Firma</label>
                        <input type="text" class="form-control" id="testimonial_position" name="position" placeholder="Beyaz Eşya Servisi" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Yorum</label>
                        <textarea class="form-control" id="testimonial_quote" name="quote" rows="4" placeholder="Müşteri yorumu..." required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select class="form-control" id="testimonial_status" name="is_active">
                            <option value="1">Aktif</option>
                            <option value="0">Pasif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- SSS Modal -->
<div class="modal fade" id="faqModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="faqModalLabel">SSS Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="faqForm">
                <div class="modal-body">
                    <input type="hidden" id="faq_id" name="id">
                    
                    <div class="mb-3">
                        <label class="form-label">Sıra</label>
                        <input type="number" class="form-control" id="faq_order" name="order" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Soru</label>
                        <input type="text" class="form-control" id="faq_question" name="question" placeholder="Serbis'i kullanmak için teknik bilgiye ihtiyacım var mı?" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Cevap</label>
                        <textarea class="form-control" id="faq_answer" name="answer" rows="5" placeholder="Cevap metni..." required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select class="form-control" id="faq_status" name="is_active">
                            <option value="1">Aktif</option>
                            <option value="0">Pasif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
// Sayfa yüklendiğinde aktif tab'ı kontrol et
$(document).ready(function() {
    // URL'den hash'i al
    let hash = window.location.hash;
    if (hash) {
        // Hash varsa o tab'ı aktif yap
        $('.nav-tabs a[href="' + hash + '"]').tab('show');
    }
    
    // Tab değiştiğinde URL hash'ini güncelle
    $('.nav-tabs a').on('shown.bs.tab', function(e) {
        window.location.hash = e.target.hash;
    });
});

// İstatistik Ekleme
function addStat() {
    $('#statModalLabel').text('İstatistik Ekle');
    $('#statForm')[0].reset();
    $('#stat_id').val('');
    $('#statModal').modal('show');
}

// İstatistik Düzenleme
function editStat(id) {
    $.ajax({
        url: `/super-admin/frontend/home/stat/${id}`,
        method: 'GET',
        success: function(response) {
            $('#statModalLabel').text('İstatistik Düzenle');
            $('#stat_id').val(response.id);
            $('#stat_order').val(response.order);
            $('#stat_number').val(response.data.number);
            $('#stat_label').val(response.data.label);
            $('#stat_status').val(response.is_active ? 1 : 0);
            $('#statModal').modal('show');
        }
    });
}

// İstatistik Kaydetme
$('#statForm').on('submit', function(e) {
    e.preventDefault();
    
    const id = $('#stat_id').val();
    const url = id ? `/super-admin/frontend/home/stat/${id}` : '/super-admin/frontend/home/stat';
    const method = id ? 'PUT' : 'POST';
    
    const data = {
        section: 'home_stats',
        order: $('#stat_order').val(),
        is_active: $('#stat_status').val(),
        data: {
            number: $('#stat_number').val(),
            label: $('#stat_label').val()
        }
    };
    
    $.ajax({
        url: url,
        method: method,
        data: JSON.stringify(data),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#statModal').modal('hide');
            toastr.success('İstatistik başarıyla kaydedildi');
            window.location.hash = '#stats';
            location.reload();
        },
        error: function(xhr) {
            toastr.error('Bir hata oluştu');
        }
    });
});

// İstatistik Silme
function deleteStat(id) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: "Bu işlem geri alınamaz!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/super-admin/frontend/home/stat/${id}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    toastr.success('İstatistik silindi');
                    window.location.hash = '#stats';
                    location.reload();
                }
            });
        }
    });
}
// ============ MODÜLLER ============

// Modül Ekleme
function addModule() {
    $('#moduleModalLabel').text('Modül Ekle');
    $('#moduleForm')[0].reset();
    $('#module_id').val('');
    $('#moduleModal').modal('show');
}

// Modül Düzenleme
function editModule(id) {
    $.ajax({
        url: `/super-admin/frontend/home/module/${id}`,
        method: 'GET',
        success: function(response) {
            $('#moduleModalLabel').text('Modül Düzenle');
            $('#module_id').val(response.id);
            $('#module_order').val(response.order);
            $('#module_icon').val(response.data.icon);
            $('#module_title').val(response.data.title);
            $('#module_description').val(response.data.description);
            $('#module_color').val(response.data.color);
            $('#module_status').val(response.is_active ? 1 : 0);
            $('#moduleModal').modal('show');
        },
        error: function(xhr) {
            toastr.error('Modül bilgileri alınamadı');
        }
    });
}

// Modül Kaydetme
$('#moduleForm').on('submit', function(e) {
    e.preventDefault();
    
    const id = $('#module_id').val();
    const url = id ? `/super-admin/frontend/home/module/${id}` : '/super-admin/frontend/home/module';
    const method = id ? 'PUT' : 'POST';
    
    const data = {
        section: 'home_modules',
        order: $('#module_order').val(),
        is_active: $('#module_status').val(),
        data: {
            icon: $('#module_icon').val(),
            title: $('#module_title').val(),
            description: $('#module_description').val(),
            color: $('#module_color').val()
        }
    };
    
    $.ajax({
        url: url,
        method: method,
        data: JSON.stringify(data),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#moduleModal').modal('hide');
            toastr.success('Modül başarıyla kaydedildi');
            window.location.hash = '#modules';
            location.reload();
        },
        error: function(xhr) {
            toastr.error('Bir hata oluştu');
        }
    });
});

// Modül Silme
function deleteModule(id) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: "Bu işlem geri alınamaz!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/super-admin/frontend/home/module/${id}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    toastr.success('Modül silindi');
                    window.location.hash = '#modules';
                    location.reload();
                },
                error: function(xhr) {
                    toastr.error('Silme işlemi başarısız');
                }
            });
        }
    });
}
// ============ SEKTÖRLER ============

// Sektör Ekleme
function addSector() {
    $('#sectorModalLabel').text('Sektör Ekle');
    $('#sectorForm')[0].reset();
    $('#sector_id').val('');
    $('#current_image_preview').html('');
    $('#sectorModal').modal('show');
}

// Sektör Düzenleme
function editSector(id) {
    $.ajax({
        url: `/super-admin/frontend/home/sector/${id}`,
        method: 'GET',
        success: function(response) {
            $('#sectorModalLabel').text('Sektör Düzenle');
            $('#sector_id').val(response.id);
            $('#sector_order').val(response.order);
            $('#sector_slug').val(response.data.slug);
            $('#sector_title').val(response.data.title);
            $('#sector_description').val(response.data.description);
            $('#sector_status').val(response.is_active ? 1 : 0);
            
            // Mevcut resmi göster
            if(response.data.image) {
                $('#current_image_preview').html(`
                    <div class="mt-2">
                        <p class="mb-1">Mevcut Resim:</p>
                        <img src="/${response.data.image}" style="max-width: 200px; border-radius: 8px;">
                    </div>
                `);
            }
            
            $('#sectorModal').modal('show');
        },
        error: function(xhr) {
            toastr.error('Sektör bilgileri alınamadı');
        }
    });
}

// Sektör Kaydetme
$('#sectorForm').on('submit', function(e) {
    e.preventDefault();
    
    const id = $('#sector_id').val();
    const url = id ? `/super-admin/frontend/home/sector/${id}` : '/super-admin/frontend/home/sector';
    
    // FormData kullanarak dosya yükleme
    const formData = new FormData();
    formData.append('section', 'home_sectors');
    formData.append('order', $('#sector_order').val());
    formData.append('is_active', $('#sector_status').val());
    formData.append('slug', $('#sector_slug').val());
    formData.append('title', $('#sector_title').val());
    formData.append('description', $('#sector_description').val());
    
    // Eğer resim seçildiyse ekle
    const imageFile = $('#sector_image')[0].files[0];
    if(imageFile) {
        formData.append('image', imageFile);
    }
    
    // if(id) {
    //     formData.append('_method', 'PUT');
    // }
    
    $.ajax({
        url: url,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#sectorModal').modal('hide');
            toastr.success('Sektör başarıyla kaydedildi');
            window.location.hash = '#sectors';
            location.reload();
        },
        error: function(xhr) {
            toastr.error('Bir hata oluştu');
        }
    });
});

// Sektör Silme
function deleteSector(id) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: "Bu işlem geri alınamaz!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/super-admin/frontend/home/sector/${id}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    toastr.success('Sektör silindi');
                    window.location.hash = '#sectors';
                    location.reload();
                },
                error: function(xhr) {
                    toastr.error('Silme işlemi başarısız');
                }
            });
        }
    });
}

// ============ ENTEGRASYONLAR ============

// Entegrasyon Ekleme
function addIntegration() {
    $('#integrationModalLabel').text('Entegrasyon Ekle');
    $('#integrationForm')[0].reset();
    $('#integration_id').val('');
    $('#integrationModal').modal('show');
}

// Entegrasyon Düzenleme
function editIntegration(id) {
    $.ajax({
        url: `/super-admin/frontend/home/integration/${id}`,
        method: 'GET',
        success: function(response) {
            $('#integrationModalLabel').text('Entegrasyon Düzenle');
            $('#integration_id').val(response.id);
            $('#integration_order').val(response.order);
            $('#integration_icon').val(response.data.icon);
            $('#integration_title').val(response.data.title);
            $('#integration_description').val(response.data.description);
            $('#integration_color').val(response.data.color);
            $('#integration_status').val(response.is_active ? 1 : 0);
            $('#integrationModal').modal('show');
        },
        error: function(xhr) {
            toastr.error('Entegrasyon bilgileri alınamadı');
        }
    });
}

// Entegrasyon Kaydetme
$('#integrationForm').on('submit', function(e) {
    e.preventDefault();
    
    const id = $('#integration_id').val();
    const url = id ? `/super-admin/frontend/home/integration/${id}` : '/super-admin/frontend/home/integration';
    const method = id ? 'PUT' : 'POST';
    
    const data = {
        section: 'home_integrations',
        order: $('#integration_order').val(),
        is_active: $('#integration_status').val(),
        data: {
            icon: $('#integration_icon').val(),
            title: $('#integration_title').val(),
            description: $('#integration_description').val(),
            color: $('#integration_color').val()
        }
    };
    
    $.ajax({
        url: url,
        method: method,
        data: JSON.stringify(data),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#integrationModal').modal('hide');
            toastr.success('Entegrasyon başarıyla kaydedildi');
            window.location.hash = '#integrations';
            location.reload();
        },
        error: function(xhr) {
            toastr.error('Bir hata oluştu');
        }
    });
});

// Entegrasyon Silme
function deleteIntegration(id) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: "Bu işlem geri alınamaz!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/super-admin/frontend/home/integration/${id}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    toastr.success('Entegrasyon silindi');
                    window.location.hash = '#integrations';
                    location.reload();
                },
                error: function(xhr) {
                    toastr.error('Silme işlemi başarısız');
                }
            });
        }
    });
}
// ============ YORUMLAR ============

// Yorum Ekleme
function addTestimonial() {
    $('#testimonialModalLabel').text('Müşteri Yorumu Ekle');
    $('#testimonialForm')[0].reset();
    $('#testimonial_id').val('');
    $('#testimonialModal').modal('show');
}

// Yorum Düzenleme
function editTestimonial(id) {
    $.ajax({
        url: `/super-admin/frontend/home/testimonial/${id}`,
        method: 'GET',
        success: function(response) {
            $('#testimonialModalLabel').text('Müşteri Yorumu Düzenle');
            $('#testimonial_id').val(response.id);
            $('#testimonial_order').val(response.order);
            $('#testimonial_name').val(response.data.name);
            $('#testimonial_initials').val(response.data.initials);
            $('#testimonial_position').val(response.data.position);
            $('#testimonial_quote').val(response.data.quote);
            $('#testimonial_color').val(response.data.color);
            $('#testimonial_status').val(response.is_active ? 1 : 0);
            $('#testimonialModal').modal('show');
        },
        error: function(xhr) {
            toastr.error('Yorum bilgileri alınamadı');
        }
    });
}

// Yorum Kaydetme
$('#testimonialForm').on('submit', function(e) {
    e.preventDefault();
    
    const id = $('#testimonial_id').val();
    const url = id ? `/super-admin/frontend/home/testimonial/${id}` : '/super-admin/frontend/home/testimonial';
    const method = id ? 'PUT' : 'POST';
    
    const data = {
        section: 'home_testimonials',
        order: $('#testimonial_order').val(),
        is_active: $('#testimonial_status').val(),
        data: {
            name: $('#testimonial_name').val(),
            initials: $('#testimonial_initials').val().toUpperCase(),
            position: $('#testimonial_position').val(),
            quote: $('#testimonial_quote').val(),
            color: $('#testimonial_color').val()
        }
    };
    
    $.ajax({
        url: url,
        method: method,
        data: JSON.stringify(data),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#testimonialModal').modal('hide');
            toastr.success('Yorum başarıyla kaydedildi');
            window.location.hash = '#testimonials';
            location.reload();
        },
        error: function(xhr) {
            toastr.error('Bir hata oluştu');
        }
    });
});

// Yorum Silme
function deleteTestimonial(id) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: "Bu işlem geri alınamaz!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/super-admin/frontend/home/testimonial/${id}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    toastr.success('Yorum silindi');
                    window.location.hash = '#testimonials';
                    location.reload();
                },
                error: function(xhr) {
                    toastr.error('Silme işlemi başarısız');
                }
            });
        }
    });
}
// ============ SSS ============

// SSS Ekleme
function addFaq() {
    $('#faqModalLabel').text('SSS Ekle');
    $('#faqForm')[0].reset();
    $('#faq_id').val('');
    $('#faqModal').modal('show');
}

// SSS Düzenleme
function editFaq(id) {
    $.ajax({
        url: `/super-admin/frontend/home/faq/${id}`,
        method: 'GET',
        success: function(response) {
            $('#faqModalLabel').text('SSS Düzenle');
            $('#faq_id').val(response.id);
            $('#faq_order').val(response.order);
            $('#faq_question').val(response.data.question);
            $('#faq_answer').val(response.data.answer);
            $('#faq_status').val(response.is_active ? 1 : 0);
            $('#faqModal').modal('show');
        },
        error: function(xhr) {
            toastr.error('SSS bilgileri alınamadı');
        }
    });
}

// SSS Kaydetme
$('#faqForm').on('submit', function(e) {
    e.preventDefault();
    
    const id = $('#faq_id').val();
    const url = id ? `/super-admin/frontend/home/faq/${id}` : '/super-admin/frontend/home/faq';
    const method = id ? 'PUT' : 'POST';
    
    const data = {
        section: 'home_faqs',
        order: $('#faq_order').val(),
        is_active: $('#faq_status').val(),
        data: {
            question: $('#faq_question').val(),
            answer: $('#faq_answer').val()
        }
    };
    
    $.ajax({
        url: url,
        method: method,
        data: JSON.stringify(data),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#faqModal').modal('hide');
            toastr.success('SSS başarıyla kaydedildi');
            window.location.hash = '#faqs';
            location.reload();
        },
        error: function(xhr) {
            toastr.error('Bir hata oluştu');
        }
    });
});

// SSS Silme
function deleteFaq(id) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: "Bu işlem geri alınamaz!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/super-admin/frontend/home/faq/${id}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    toastr.success('SSS silindi');
                    window.location.hash = '#faqs';
                    location.reload();
                },
                error: function(xhr) {
                    toastr.error('Silme işlemi başarısız');
                }
            });
        }
    });
}
</script>
@endsection
