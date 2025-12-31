<div class="container">
  <div class="row">
    <div class="col-md-12">
      <form method="POST" id="servisPlanGuncelle" action="{{route('update.service.plan', $tenant_id)}}">
        @csrf
        <input type="hidden" name="planid" value="{{ $servisPlan->id }}">
        <input type="hidden" name="tenant_id" value="{{ $tenant_id }}">

        {{-- İşlemi Yapan Personel Seçimi - Sadece Patron Görebilir --}}
        @if(auth()->user()->hasRole('Patron'))
          <div class="row form-group">
            <div class="col-lg-12">
              <label>İşlemi Yapan</label>
            </div>
            <div class="col-lg-12">
              <select name="planIslemiYapan" class="form-control planIslemiYapan">
                @foreach($personellerAll as $personel)
                  <option value="{{ $personel->user_id }}" 
                    {{ $personel->user_id == $servisPlan->pid ? 'selected' : '' }}>
                    {{ $personel->name }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>
        @else
          {{-- Patron değilse mevcut değeri hidden olarak gönder --}}
          <input type="hidden" name="planIslemiYapan" value="{{ $servisPlan->pid }}">
        @endif
                
        {{-- Plan Cevapları --}}
        @foreach($planCevaplar as $plan)
          @php
            $soru = App\Models\StageQuestion::find($plan->soruid);
          @endphp   {{-- Parça--}}
        {{-- Parça - Sadece Görüntüleme --}}
        @if($soru->cevapTuru == "[Parca]")
            <div class="row form-group">
                <div class="col-lg-12">
                    <label>{{ $soru->soru }}</label>
                    @php
                        $kullanilanParcalarArray = [];
                        if ($plan->cevap) {
                            $cevaplarArray = explode(', ', $plan->cevap);
                            foreach ($cevaplarArray as $cevapItem) {
                                list($itemStokId, $itemAdet) = array_pad(explode('---', $cevapItem), 2, 0);
                                $kullanilanStok = App\Models\Stock::find($itemStokId);
                                if ($kullanilanStok) {
                                    $kullanilanParcalarArray[] = $kullanilanStok->urunAdi . ' (Adet: ' . $itemAdet . ')';
                                }
                            }
                        }
                    @endphp

                    @if(!empty($kullanilanParcalarArray))
                        <div class="alert alert-info" style="margin-bottom: 10px; padding: 10px;">
                            <strong>✓ Kullanılan Parçalar:</strong>
                            <ul style="margin-bottom:0; margin-top:5px;">
                                @foreach($kullanilanParcalarArray as $parcaText)
                                    <li>{{ $parcaText }}</li>
                                @endforeach
                            </ul>
                            <small class="text-muted">* Parça seçimi sonradan değiştirilemez</small>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i>Henüz parça seçilmemiş</i>
                        </div>
                    @endif
                    
                    <!-- Hidden input ile mevcut değeri koru -->
                    <input type="hidden" name="soru{{ $plan->id }}" value="Parca"/>
                </div>
            </div>
            {{-- Konsinye Cihaz --}}            
{{-- Konsinye Cihaz - Sadece Görüntüleme --}}
@elseif($soru->cevapTuru == "[Konsinye Cihaz]")
    <div class="row form-group">
        <div class="col-lg-12">
            <label>{{ $soru->soru }}</label>
            @php
                $kullanilanKonsinyeArray = [];
                if ($plan->cevap) {
                    $cevaplarArray = explode(', ', $plan->cevap);
                    foreach ($cevaplarArray as $cevapItem) {
                        list($itemStokId, $itemAdet) = array_pad(explode('---', $cevapItem), 2, 0);
                        $stok = App\Models\Stock::find($itemStokId);
                        if ($stok) {
                            $kullanilanKonsinyeArray[] = $stok->urunAdi . ' (Adet: ' . $itemAdet . ')';
                        }
                    }
                }
            @endphp

            @if(!empty($kullanilanKonsinyeArray))
                <div class="alert alert-info" style="margin-bottom: 10px; padding: 10px;">
                    <strong>✓ Kullanılan Konsinye Cihazlar:</strong>
                    <ul style="margin-bottom:0; margin-top:5px;">
                        @foreach($kullanilanKonsinyeArray as $cihazText)
                            <li>{{ $cihazText }}</li>
                        @endforeach
                    </ul>
                    <small class="text-muted">* Konsinye cihaz seçimi sonradan değiştirilemez</small>
                </div>
            @else
                <div class="alert alert-warning">
                    <i>Henüz konsinye cihaz seçilmemiş</i>
                </div>
            @endif
            
            <!-- Hidden input ile mevcut değeri koru -->
            <input type="hidden" name="soru{{ $plan->id }}" value="Konsinye Cihaz"/>
            @else
            {{-- Diğer Soru Tipleri --}}
            <div class="row form-group">
              <div class="col-lg-4">
                <label>{{ $soru->soru }}</label>
              </div>
              <div class="col-lg-12">
                @if($soru->cevapTuru == "[Aciklama]")
                  <input type="text" name="soru{{ $plan->id }}" class="form-control" value="{{ $plan->cevap }}">
                @elseif(strpos($soru->cevapTuru, 'Grup') !== false)
                {{-- Grup Seçimi --}}
                @if(strpos($soru->cevapTuru, 'Grup-0') !== false)
                    <select class="form-control" name="soru{{ $plan->id }}">
                        @php 
                            $adminPersonel = App\Models\User::where('tenant_id', $tenant_id)
                                ->where('status', '1')
                                ->whereHas('roles', function($query) {
                                    $query->where('name', 'Admin');
                                })
                                ->orderBy('name', 'asc')
                                ->get();
                        @endphp
                        @foreach($adminPersonel as $personel)
                            <option value="{{ $personel->user_id }}" {{ $plan->cevap == $personel->user_id ? 'selected' : '' }}>
                                {{ $personel->name }}
                            </option>
                        @endforeach
                    </select>
                @else
                    {{-- Belirli Grup Personelleri --}}
                    @php
                        // Grupları çöz
                        $roller = [];
                        preg_match_all('/Grup-(\d+)/', $soru->cevapTuru, $matches);
                        $grupKodlari = $matches[1] ?? [];

                        // Rol ataması
                        if (array_intersect([261, 262], $grupKodlari)) {
                            $roller = ['Atölye Ustası'];
                        } elseif (array_intersect([4, 5], $grupKodlari)) {
                            $roller = ['Teknisyen', 'Teknisyen Yardımcısı'];
                        }

                        // Personelleri çek
                        $grupPersoneller = App\Models\User::where('tenant_id', $tenant_id)
                            ->where('status', '1')
                            ->whereHas('roles', function($query) use ($roller) {
                                $query->whereIn('name', $roller);
                            })
                            ->orderBy('name', 'asc')
                            ->get();
                    @endphp

                    <select class="form-control" name="soru{{ $plan->id }}">
                        <option value="">-Seçiniz-</option>
                        @foreach($grupPersoneller as $personel)
                            <option value="{{ $personel->user_id }}" {{ $plan->cevap == $personel->user_id ? 'selected' : '' }}>
                                {{ $personel->name }}
                            </option>
                        @endforeach
                    </select>
                @endif


                  @elseif($soru->cevapTuru == "[Tarih]")
                    <input type="date" name="soru{{ $plan->id }}" class="form-control datepicker" value="{{ $plan->cevap }}" style="background:#fff;">
                  @elseif($soru->cevapTuru == "[Saat]")
                    <select class="form-control" name="soru{{ $plan->id }}">
                      @php
                        $saatler = [
                          '08:00-10:00', '09:00-11:00', '10:00-12:00', '11:00-13:00',
                          '12:00-14:00', '13:00-15:00', '14:00-16:00', '15:00-17:00',
                          '16:00-18:00', '17:00-19:00', '18:00-20:00', '19:00-21:00',
                          '20:00-22:00', '21:00-23:00'
                        ];
                      @endphp
                      @foreach($saatler as $saat)
                        <option value="{{ $saat }}" 
                          {{ $plan->cevap == $saat ? 'selected' : '' }}>
                          {{ $saat }}
                        </option>
                      @endforeach
                    </select>

                  @elseif($soru->cevapTuru == "[Arac]")
                    @php
                      $araclar = App\Models\Car::where('firma_id', $tenant_id)
                            ->orderBy('id', 'ASC')
                            ->get();
                    @endphp
                    <select class="form-control" name="soru{{ $plan->id }}">
                      @foreach($araclar as $arac)
                        <option value="{{ $arac->id }}" {{ $plan->cevap == $arac->id ? 'selected' : '' }}>
                          {{ $arac->arac }}
                        </option>
                      @endforeach
                    </select>

                  @elseif($soru->cevapTuru == "[Fiyat]")
                    <input type="number" name="soru{{ $plan->id }}" class="form-control" value="{{ $plan->cevap }}">
                  @elseif($soru->cevapTuru == "[Teklif]")
                    <input type="number" name="soru{{ $plan->id }}" class="form-control" value="{{ $plan->cevap }}">
                    <span style="font-size: 12px; color: red; font-weight: 500; margin: 0; padding: 0; display: block;">
                      Bu alan sadece teklif vermek için kullanılır.
                    </span>
                  @elseif($soru->cevapTuru == "[Bayi]")
                    @php
                      $bayiler = App\Models\User::where('tenant_id', $tenant_id)
                              ->where('status', '1')
                              ->whereHas('roles', function($query) {
                                  $query->whereIn('name', ['Bayi']);
                              })
                              ->orderBy('name', 'asc')
                              ->get()
                    @endphp
                    <select class="form-control" name="soru{{ $plan->id }}">
                      @foreach($bayiler as $bayi)
                        <option value="{{ $bayi->user_id }}" {{ $plan->cevap == $bayi->user_id ? 'selected' : '' }}>
                          {{ $bayi->name }}
                        </option>
                      @endforeach
                    </select>
                    <input type="hidden" name="eskiBayi" value="{{ $plan->cevap }}">
                  @endif
                </div>
            </div>
          @endif
        @endforeach

        {{-- Form Butonları --}}
        <div class="row">
          <div class="col-lg-12" style="text-align: center; margin-bottom: 0px; margin-top: 5px;">
            <input type="submit" class="btn btn-primary btn-sm" value="Güncelle">
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function(e) {

// Ürün Arama  Filtreleme
$(document).on('keyup', '.urunAraInput', function () {
    const searchText = $(this).val().toLowerCase();

    // En yakın .stock-item öğelerini barındıran konteyneri bul
    let $stockContainer = $(this).siblings('.myParcaList, .myKonsinyeList');

    $stockContainer.find('.stock-item').each(function () {
        const productName = ($(this).data('product-name') || '').toLowerCase();
        const productCode = String($(this).data('product-code') || '').toLowerCase();

        if (productName.includes(searchText) || productCode.includes(searchText)) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
});

    // Personel Stoğu checkbox değiştiğinde adet girişini göster/gizle ve maksimumu ayarla
    $(document).on('change', '.stock-checkbox', function() {
        const quantityInput = $(this).closest('.stock-item').find('.quantity-input');
        const availableQuantity = parseInt($(this).data('available'));

        if ($(this).is(':checked')) {
            quantityInput.attr('max', availableQuantity).val(1).show();
        } else {
            quantityInput.val(1).removeAttr('max').hide();
        }
    });
// Konsinye Cihaz checkbox değiştiğinde adet girişini göster/gizle ve maksimumu ayarla
    $(document).on('change', '.consignment-checkbox', function() {
        const quantityInput = $(this).closest('.stock-item').find('.consignment-quantity-input');
        const availableQuantity = parseInt($(this).data('available'));

        if ($(this).is(':checked')) {
            quantityInput.attr('max', availableQuantity).val(1).show();
        } else {
            quantityInput.val(1).removeAttr('max').hide();
        }
    });

    

    // Adet girişlerine sadece sayı girilmesini sağla ve min/max kontrolü yap
    $(document).on('input', '.quantity-input', function() {
        let value = $(this).val();
        const max = parseInt($(this).attr('max'));
        const min = parseInt($(this).attr('min')) || 1; // Belirtilmemişse varsayılan minimum 1

        // Geçerli bir sayı olduğundan emin ol
        value = parseInt(value) || min; // Sayı değilse minimuma ayarla

        if (value > max) {
            alert('Maksimum mevcut adeti (' + max + ') aşamazsınız.');
            value = max;
        } else if (value < min) {
            value = min;
        }
        $(this).val(value);
    });

    
    // Form Submit
    $("#servisPlanGuncelle").on('submit', function(e) {
        e.preventDefault();
        let formIsValid = true;
        const $form = $(this);

        // Tüm zorunlu alanları (checkbox'lar hariç) kontrol et
        $form.find('[required]').not('.stock-checkbox').each(function() {
            if (!$(this).val()) {
                formIsValid = false;
                $(this).addClass('is-invalid'); // Geçersizse CSS sınıfı ekle
                return false; // Döngüyü kır
            } else {
                $(this).removeClass('is-invalid');
            }
        });

       
        if (!formIsValid) {
            alert('Lütfen tüm zorunlu alanları doldurun.');
            return; // Geçersizse işlemi durdur
        }

        // FormData nesnesini oluştur
        const formData = new FormData(this);


        // Seçili "Personel Stoğu" ürünlerini FormData'ya manuel olarak ekle
        $('.stock-checkbox:checked').each(function() {
        // Checkbox'ın bulunduğu .myParcaList div'inden data-soru-id'yi al
        const parcaDropdown = $(this).closest('.myParcaList');
        const stageId = parcaDropdown.data('soru-id'); // data-soru-id="{{ $plan->id }}" 
        
        if (!stageId) {
            console.error('Stage ID bulunamadı:', parcaDropdown);
            return; // Bu öğeyi atla
        }
        
        const stockId = $(this).val();
        const quantityInput = $(this).closest('.stock-item').find('.quantity-input');
        const quantity = quantityInput.val();

        formData.append(`stokCheck${stockId}`, 'on'); // Checkbox'ın seçili olduğunu belirtir
        formData.append(`stokAdet${stockId}`, quantity); // Adet bilgisini ekler

    });
        // Seçili "Konsinye Cihaz" ürünlerini FormData'ya manuel olarak ekle
       $('.consignment-checkbox:checked').each(function() {
        // Checkbox'ın name'inden ID'yi çıkar: konsinyeCheck123 -> 123
        const checkboxName = $(this).attr('name'); // konsinyeCheck123
        const stockIdMatch = checkboxName ? checkboxName.match(/konsinyeCheck(\d+)/) : null;
        
        if (!stockIdMatch) {
            console.error('Konsinye ID bulunamadı:', checkboxName);
            return;
        }
        
        const stockId = stockIdMatch[1];
        const quantityInput = $(this).closest('.stock-item').find('.consignment-quantity-input');
        const quantity = quantityInput.val();

        formData.append(`konsinyeCheck${stockId}`, stockId);
        formData.append(`konsinyeAdet${stockId}`, quantity);
    });

        // İşaretlenmemiş personel stoğu checkbox'larının verilerini FormData'dan sil
        // Bu, önceden işaretlenip sonra kaldırılan değerlerin gönderilmesini önler.
        $('.stock-checkbox:not(:checked)').each(function() {
            const fullName = $(this).attr('name');
            const quantityInputName = $(this).closest('.stock-item').find('.quantity-input').attr('name');
            formData.delete(fullName);
            if (quantityInputName) {
                formData.delete(quantityInputName);
            }
        });

        // İşaretlenmemiş konsinye checkbox'larının verilerini temizle
      $('.consignment-checkbox:not(:checked)').each(function() {
          const checkboxName = $(this).attr('name'); // konsinyeCheck{id}
          const quantityInputName = $(this).closest('.stock-item').find('.consignment-quantity-input').attr('name');
          formData.delete(checkboxName);
          if (quantityInputName) {
              formData.delete(quantityInputName);
          }
      });
        
        $.ajax({
            url: $(this).attr('action'),
            type: "POST",
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            success: function(data) {
                if(data.error) {
                    alert(data.error);
                } else {
                    alert('Plan başarıyla güncellendi');
                   

                    // Servis geçmişini güncelle
                    if(typeof loadServiceHistory === 'function' && data.servis_id) {
                        loadServiceHistory(data.servis_id);
                    }else {
                        alert(response.error || 'Bir hata oluştu');
                    }

                     $('#editServicePlanModal').modal('hide');
                    $('.nav1').trigger('click');
                }
            },
            error: function(e) {
                alert("Hata: " + e.responseText);
            }
        });
    });

    // Dropdown tıklama engelleme
    $(document).on('click', '.parcalar-dropdown', function(e) {
        e.stopPropagation();
    });
    // --- Başlangıç Ayarları ---
    // Sayfa yüklendiğinde işaretli olmayan stok ürünlerinin adet girişlerini gizle
    $('.stock-checkbox:not(:checked)').closest('.stock-item').find('.quantity-input').hide();
    // Konsinye cihaz adet giriş grubunu gizle
    $('.consignment-quantity-group').hide();
});
</script>