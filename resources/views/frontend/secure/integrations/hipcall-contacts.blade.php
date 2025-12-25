@extends('frontend.secure.user_master')
@section('user')

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="page-title mb-0">
                        <i class="fas fa-address-book"></i> Hipcall Rehberi
                    </h4>
                    <div>
                        <button type="button" class="btn btn-info btn-sm" onclick="refreshContacts()">
                            <i class="fas fa-sync-alt"></i> Yenile
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="importSelected()">
                            <i class="fas fa-download"></i> Seçilenleri Aktar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        
                        @if(!$success && isset($error))
                        <div class="alert alert-danger">
                            <strong>Hata:</strong> {{ $error }}
                            <hr>
                            <small>
                                <strong>Kontrol listesi:</strong><br>
                                1. API Key doğru girildi mi?<br>
                                2. Hipcall'da rehber erişimi var mı?<br>
                                3. Base URL doğru mu? ({{ config('hipcall.base_url', 'Ayarlanmamış') }})
                            </small>
                        </div>
                        @endif

                        @if($success)
                        <div class="alert alert-info d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-info-circle"></i>
                                Hipcall rehberinde <strong>{{ $total }}</strong> kişi bulundu
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                                    <i class="fas fa-check-square"></i> Tümünü Seç
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">
                                    <i class="fas fa-square"></i> Seçimi Kaldır
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="selectNewOnly()">
                                    <i class="fas fa-filter"></i> Sadece Yenileri Seç
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <span class="text-muted">
                                <span id="selectedCount">0</span> kişi seçili
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="selectAllCheckbox" onclick="toggleAll(this)">
                                        </th>
                                        <th>Ad Soyad</th>
                                        <th>Telefon</th>
                                        <th>E-posta</th>
                                        <th>Firma</th>
                                        <th>Durum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($contacts as $index => $contact)
                                    <tr class="{{ $contact['exists_in_serbis'] ? 'table-light' : '' }}">
                                        <td>
                                            <input type="checkbox" 
                                                   class="contact-checkbox {{ $contact['exists_in_serbis'] ? 'existing-contact' : 'new-contact' }}" 
                                                   value="{{ $index }}"
                                                   data-contact="{{ json_encode($contact) }}"
                                                   onchange="updateSelectedCount()"
                                                   {{ $contact['exists_in_serbis'] ? 'disabled' : '' }}>
                                        </td>
                                        <td>
                                            <strong>{{ $contact['first_name'] ?? '' }} {{ $contact['last_name'] ?? '' }}</strong>
                                        </td>
                                        <td>
    @if(isset($contact['phone']) && !empty($contact['phone']))
        <span class="badge bg-info">
            {{ $contact['phone'] }}
        </span>
    @else
        <span class="badge bg-secondary">-</span>
    @endif
</td>
<td>{{ $contact['email'] ?? '-' }}</td>
<td>{{ $contact['company_name'] ?? $contact['company']['name'] ?? '-' }}</td>
                                        <td>
                                            @if($contact['exists_in_serbis'])
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check"></i> Mevcut
                                                </span>
                                                @if($contact['customer_id'])
                                                <a href="" 
                                                   class="btn btn-sm btn-outline-primary ms-1"
                                                   target="_blank">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                                @endif
                                            @else
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-plus"></i> Yeni
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            Hipcall rehberinde kişi bulunamadı
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>

        <!-- Sonuç Modal -->
        <div class="modal fade" id="resultsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Aktarım Sonucu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="resultsContent"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                        <button type="button" class="btn btn-primary" onclick="location.reload()">Sayfayı Yenile</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
// Seçim işlemleri
function toggleAll(checkbox) {
    document.querySelectorAll('.contact-checkbox:not([disabled])').forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateSelectedCount();
}

function selectAll() {
    document.querySelectorAll('.contact-checkbox:not([disabled])').forEach(cb => {
        cb.checked = true;
    });
    document.getElementById('selectAllCheckbox').checked = true;
    updateSelectedCount();
}

function deselectAll() {
    document.querySelectorAll('.contact-checkbox').forEach(cb => {
        cb.checked = false;
    });
    document.getElementById('selectAllCheckbox').checked = false;
    updateSelectedCount();
}

function selectNewOnly() {
    document.querySelectorAll('.contact-checkbox').forEach(cb => {
        cb.checked = false;
    });
    document.querySelectorAll('.new-contact').forEach(cb => {
        cb.checked = true;
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    const count = document.querySelectorAll('.contact-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = count;
}

// Seçili kişileri aktar
function importSelected() {
    const selectedCheckboxes = document.querySelectorAll('.contact-checkbox:checked');
    
    if (selectedCheckboxes.length === 0) {
        alert('Lütfen en az bir kişi seçin');
        return;
    }
    
    const selectedContacts = Array.from(selectedCheckboxes).map(cb => {
        return JSON.parse(cb.getAttribute('data-contact'));
    });
    
    if (!confirm(`${selectedContacts.length} kişiyi SerbisERP'ye aktarmak istediğinizden emin misiniz?`)) {
        return;
    }
    
    if (typeof toastr !== 'undefined') {
        toastr.info('Kişiler aktarılıyor...');
    }
    
    $.ajax({
        url: '{{ route("tenant.integrations.hipcall.import-contacts", $tenant->id) }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            contacts: JSON.stringify(selectedContacts)
        },
        success: function(response) {
            if (response.success) {
                showResults(response.results);
                
                if (typeof toastr !== 'undefined') {
                    toastr.success(response.message);
                } else {
                    alert('✓ ' + response.message);
                }
            } else {
                if (typeof toastr !== 'undefined') {
                    toastr.error(response.message);
                } else {
                    alert('✗ ' + response.message);
                }
            }
        },
        error: function() {
            if (typeof toastr !== 'undefined') {
                toastr.error('Aktarım sırasında hata oluştu');
            } else {
                alert('✗ Aktarım sırasında hata oluştu');
            }
        }
    });
}

// Rehberi yenile
function refreshContacts() {
    if (typeof toastr !== 'undefined') {
        toastr.info('Hipcall rehberi yenileniyor...');
    }
    
    location.reload();
}

// Sonuçları göster
function showResults(results) {
    let html = `
        <div class="alert alert-info">
            <h5>Özet</h5>
            <ul class="mb-0">
                <li>Başarılı: <strong class="text-success">${results.success}</strong></li>
                <li>Zaten Var (Atlandı): <strong class="text-warning">${results.skipped}</strong></li>
                <li>Başarısız: <strong class="text-danger">${results.failed}</strong></li>
            </ul>
        </div>
    `;
    
    if (results.errors && results.errors.length > 0) {
        html += `
            <div class="alert alert-warning">
                <h6>Hatalar:</h6>
                <ul>
        `;
        results.errors.forEach(error => {
            html += `<li>${error.contact}: ${error.error}</li>`;
        });
        html += `</ul></div>`;
    }
    
    document.getElementById('resultsContent').innerHTML = html;
    new bootstrap.Modal(document.getElementById('resultsModal')).show();
}
</script>

@endsection