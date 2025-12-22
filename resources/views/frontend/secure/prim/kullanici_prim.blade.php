{{-- Bu dosya, mevcut index.blade.php dosyanızın bir kopyası olup düzenlenmiştir. --}}

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid mt-1" >
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          {{-- <h4 class="card-title">Primlerim</h4> --}}
        </div>
        <div class="card-body">
          <!-- Prim Ayarları Özeti (Mevcut kodunuzdaki gibi kalabilir) -->
          <div class="row mb-1">
            {{-- ... Mevcut prim ayarları özeti bölümü ... --}}
          </div>

          {{-- Personel seçim alanı kaldırıldı --}}
          <form id="primForm" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-4">
              <label for="tarih1prim" class="form-label">Başlangıç Tarihi</label>
              <input type="date" class="form-control" id="tarih1prim" name="tarih1prim" required>
            </div>
                        
            <div class="col-md-4">
              <label for="tarih2prim" class="form-label">Bitiş Tarihi</label>
              <input type="date" class="form-control" id="tarih2prim" name="tarih2prim" required>
            </div>
                        
            <div class="col-md-4">
              <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-sm">
                  <i class="fas fa-calculator"></i> Prim Hesapla
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

   <!-- Sonuçlar Tablosu -->
  <div class="row mt-1" id="sonuclarContainer" style="display: none;">
    <div class="col-12">
      <div class="card" style="margin-bottom: 0!important;">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Prim Hesaplama Sonuçları</h5>
          <div id="sonucBilgi" class="text-muted small"></div>
        </div>
        <div class="card-body">
          <!-- Özet Kartları -->
          <div class="row mb-1" id="ozetKartlari">
            <div class="col-md-3">
              <div class="card bg-primary text-white" style="margin-bottom: 0!important;">
                <div class="card-body">
                  <div class="d-flex justify-content-between">
                    <div>
                      <h4 id="toplamPrimTutar">0 ₺</h4>
                      <small>Toplam Prim</small>
                    </div>
                    <div class="align-self-center">
                      <i class="fas fa-money-bill-wave fa-2x"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-success text-white">
                <div class="card-body">
                  <div class="d-flex justify-content-between">
                    <div>
                      <h4 id="primliGunSayisi">0</h4>
                      <small>Primli Gün Sayısı</small>
                    </div>
                    <div class="align-self-center">
                      <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-info text-white">
                <div class="card-body">
                  <div class="d-flex justify-content-between">
                    <div>
                      <h4 id="ortalamaPrim">0 ₺</h4>
                      <small>Ortalama Günlük Prim</small>
                    </div>
                    <div class="align-self-center">
                      <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-warning text-white">
                <div class="card-body">
                  <div class="d-flex justify-content-between">
                    <div>
                      <h4 id="toplamPerformans">0</h4>
                      <small>Toplam Performans</small>
                    </div>
                    <div class="align-self-center">
                      <i class="fas fa-trophy fa-2x"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-striped table-hover" id="sonuclarTable" style="margin-bottom: 0!important;">
              <thead class="title">
                <tr>
                  <th>#</th>
                  <th>Tarih</th>
                  <th>Performans</th>
                  <th>Sınır</th>
                  <th>Prim Oranı</th>
                  <th>Prim Tutarı</th>
                
                </tr>
              </thead>
              <tbody id="sonuclarTableBody">
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Günlük Detay Modal -->
<div class="modal fade" id="gunlukDetayModal" tabindex="-1"  style="padding-top: 50px;background: rgba(0, 0, 0, 0.50);">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Günlük Prim Detayları</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="gunlukDetayContent">
        <!-- Detay içeriği buraya gelecek -->
      </div>
      <div class="modal-footer" style="margin-bottom: 5px!important;">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Kapat</button>
      </div>
    </div>
  </div>
</div>

<!-- Loading Spinner -->
<div class="text-center" id="loadingSpinner" style="display: none;">
  <div class="spinner-border text-primary" role="status">
    <span class="visually-hidden">Yükleniyor...</span>
  </div>
</div>

<script>
  // Mevcut JavaScript kodunuzu buraya kopyalayın ve AJAX URL'lerini güncelleyin.
  $(document).ready(function() {
    // ... (flatpickr başlatma)

    $('#primForm').on('submit', function(e) {
        e.preventDefault();
        primHesapla();
    });
  });

  function primHesapla() {
    const formData = new FormData($('#primForm')[0]);
    $('#loadingSpinner').show();
    $('#sonuclarContainer').hide();
    
    $.ajax({
      // URL'yi yeni rotanızla değiştirin
      url: '{{ route("prim.kullanici.hesapla", $firma->id) }}',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        $('#loadingSpinner').hide();
        if (response.success) {
          showSonuclar(response.data); // Bu fonksiyon aynı kalabilir
        } else {
          showErrors(response.message); // Bu fonksiyon aynı kalabilir
        }
      },
      error: function(xhr) {
        // ... (hata yönetimi aynı kalabilir)
      }
    });
  }

  // gunlukDetayGoster fonksiyonundaki AJAX URL'ini güncelleyin
  function gunlukDetayGoster(tarih) { // personelId parametresi buradan kaldırıldı
    const modalContent = document.getElementById('gunlukDetayContent');
    modalContent.innerHTML = `<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>`;

    const modal = new bootstrap.Modal(document.getElementById('gunlukDetayModal'));
    modal.show();

    $.ajax({
        url: '{{ route("prim.kullanici.detay", $firma->id) }}', // <--- KONTROL EDİLECEK İKİNCİ ÖNEMLİ YER
        type: 'GET',
        data: { 
            tarih: tarih 
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                renderModernDetay(response.data);
            } else {
                showErrorState('Detaylar alınamadı.');
            }
        },
        error: function(xhr) {
            console.error('Detay yükleme hatası:', xhr);
            showErrorState('Günlük detaylar alınırken hata oluştu.');
        }
    });
  }
  
  
</script>
<script>
  $(document).ready(function() {
    // Tarih seçicileri başlat
    flatpickr("#tarih1prim, #tarih2prim", {
      dateFormat: "Y-m-d",
      locale: "tr",
      allowInput: true,
      defaultDate: "today", //Sayfa ilk açıldığında tarih aralığını bugün yapmakta
    });

    // Form submit
    $('#primForm').on('submit', function(e) {
        e.preventDefault();
        primHesapla();
    });
  });

  

  function showSonuclar(data) {
    const sonuclar = data.sonuclar;
    const grup = data.grup;
    const tarihAraligi = data.tarih_araligi;
    const toplamKayit = data.toplam_kayit;
    const toplamPrim = data.toplam_prim;
    
    // Özet kartlarını güncelle
    updateOzetKartlari(sonuclar, toplamPrim);
    
    // Bilgi alanını güncelle
    $('#sonucBilgi').html(`
      <strong>Tarih:</strong> ${tarihAraligi.baslangic} - ${tarihAraligi.bitis} | 
      <strong>Toplam:</strong> ${toplamKayit} primli gün
    `);
    
    // Tablo içeriğini temizle
    $('#sonuclarTableBody').empty();
    
    if (sonuclar.length === 0) {
      $('#sonuclarTableBody').html(`
        <tr>
          <td colspan="7" class="text-center py-4">
            <i class="fas fa-info-circle text-muted"></i> 
            Belirtilen kriterlere uygun prim bulunamadı.
          </td>
        </tr>
      `);
    } else {
      sonuclar.forEach(function(sonuc, index) {
        let performansText = '';
        let performansValue = 0;
  
        if (sonuc.teklif_toplami) {
          // Teknisyen
          performansText = `${numberFormat(sonuc.teklif_toplami)} TL`;
          performansValue = sonuc.teklif_toplami;
        } else if (sonuc.servis_sayisi) {
          // Operator
          performansText = `${sonuc.servis_sayisi} Servis`;
          performansValue = sonuc.servis_sayisi;
        } else if (sonuc.tamamlanan_sayisi) {
          // Atölye Ustası
          performansText = `${sonuc.tamamlanan_sayisi} Tamamlama`;
          performansValue = sonuc.tamamlanan_sayisi;
        }
            
        const row = `
          <tr>
            <td>${index + 1}</td>
            <td>
              <span class="">${formatTarih(sonuc.tarih)}</span>
            </td>
            <td>
              <span class=" fw-bold">${performansText}</span>
            </td>
            <td>
              <span class="text-muted">${numberFormat(sonuc.gunluk_sinir)}</span>
            </td>
            <td>
              <span class="">${sonuc.prim_orani}%</span>
            </td>
            <td>
              <span class="text-success fw-bold fs-6">${numberFormat(sonuc.prim_tutari)} ₺</span>
            </td>
            
          </tr>
          `;
          $('#sonuclarTableBody').append(row);
        });
      }
    
      $('#sonuclarContainer').show();
      toastr.success('Prim hesaplama işlemi başarıyla tamamlandı.');
  }

  function updateOzetKartlari(sonuclar, toplamPrim) {
    const primliGunSayisi = sonuclar.length;
    const ortalamaPrim = primliGunSayisi > 0 ? toplamPrim / primliGunSayisi : 0;
    
    // Toplam performans (rol göre farklı hesaplama)
    let toplamPerformans = 0;
    sonuclar.forEach(function(sonuc) {
      if (sonuc.teklif_toplami) {
        toplamPerformans += sonuc.teklif_toplami;
      } else if (sonuc.servis_sayisi) {
        toplamPerformans += sonuc.servis_sayisi;
      } else if (sonuc.tamamlanan_sayisi) {
        toplamPerformans += sonuc.tamamlanan_sayisi;
      }
    });
    
    $('#toplamPrimTutar').text(numberFormat(toplamPrim) + ' ₺');
    $('#primliGunSayisi').text(primliGunSayisi);
    $('#ortalamaPrim').text(numberFormat(ortalamaPrim) + ' ₺');
    $('#toplamPerformans').text(numberFormat(toplamPerformans));
  }

  function showErrors(errors) {
    if (typeof errors === 'string') {
      toastr.error(errors);
    } else {
      $.each(errors, function(field, messages) {
        if (Array.isArray(messages)) {
          $.each(messages, function(index, message) {
            toastr.error(message);
          });
        } else {
          toastr.error(messages);
        }
      });
    }
  }


// Toast bildirimi gösterme
function showToast(type, message, icon) {
    const toastTypes = {
        success: 'text-bg-success',
        error: 'text-bg-danger',
        warning: 'text-bg-warning',
        info: 'text-bg-info'
    };
    
    const toastHtml = `
        <div class="toast align-items-center ${toastTypes[type]} border-0" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="${icon} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    const toastContainer = document.createElement('div');
    toastContainer.innerHTML = toastHtml;
    document.body.appendChild(toastContainer);
    
    const toast = new bootstrap.Toast(toastContainer.querySelector('.toast'));
    toast.show();
    
    // Toast kapandıktan sonra container'ı temizle
    setTimeout(() => {
        toastContainer.remove();
    }, 5000);
}

// Yardımcı fonksiyonlar
function getInitials(name) {
    if (!name) return '??';
    return name.split(' ')
        .map(n => n.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2);
}

function getAvatarColor(name) {
    const colors = ['bg-primary', 'bg-success', 'bg-warning', 'bg-info', 'bg-secondary', 'bg-danger'];
    if (!name) return colors[0];
    
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    const index = Math.abs(hash) % colors.length;
    return colors[index];
}

function formatTarih(tarih) {
    if (!tarih) return 'Belirtilmemiş';
    
    const date = new Date(tarih);
    return date.toLocaleDateString('tr-TR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        weekday: 'long'
    });
}

function formatSaat(datetime) {
    if (!datetime) return '--:--';
    
    const date = new Date(datetime);
    return date.toLocaleTimeString('tr-TR', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

function numberFormat(number) {
    if (!number || isNaN(number)) return '0';
    
    return new Intl.NumberFormat('tr-TR', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    }).format(number);
}

// Modal kapanma olayları
document.getElementById('gunlukDetayModal').addEventListener('hidden.bs.modal', function() {
    // Modal kapandığında içeriği temizle
    document.getElementById('gunlukDetayContent').innerHTML = '';
});

// Klavye kısayolları
document.addEventListener('keydown', function(e) {
    // ESC tuşu ile modal kapatma
    if (e.key === 'Escape') {
        const modal = bootstrap.Modal.getInstance(document.getElementById('gunlukDetayModal'));
        if (modal) {
            modal.hide();
        }
    }
});

  // Yardımcı fonksiyonlar
  function numberFormat(number) {
    return new Intl.NumberFormat('tr-TR', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 2
    }).format(number);
  }

  function formatTarih(tarih) {
    return new Date(tarih).toLocaleDateString('tr-TR');
  }

  function formatSaat(datetime) {
    return new Date(datetime).toLocaleTimeString('tr-TR', {
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  // Personel seçimi değiştiğinde
  $('#personel_id').on('change', function() {
    const selectedPersonel = $(this).find('option:selected').text();
    if (selectedPersonel && selectedPersonel !== 'Personel Seçiniz') {
      toastr.info(`${selectedPersonel} personeli seçildi.`);
    }
  });

  // Tarih validasyonu
  $('#tarih2prim').on('change', function() {
    const tarih1 = $('#tarih1prim').val();
    const tarih2 = $('#tarih2prim').val();
    
    if (tarih1 && tarih2) {
      const date1 = new Date(tarih1);
      const date2 = new Date(tarih2);
        
      if (date2 < date1) {
        toastr.warning('Bitiş tarihi başlangıç tarihinden önce olamaz.');
        $('#tarih2prim').val('');
      }
    }
  });
</script>