<div class="card" style="margin-bottom: 3px;">
  <div class="card-header ch1" style="padding: 3px 0;">
    <div class="row" style="margin-left: -10px; margin-right: -10px;">
      <div class="col-5">
        <button type="button"
                class="btn btn-success btn-sm hareketEkleBtn"
                style="margin-left: 10px;"
                data-stokid="{{ $stock->id }}">
          Hareket Ekle
        </button>
      </div>
      <div class="col-7 text-end">
        <label style="width: auto; display: inline-block; margin: 0;">
          <i class="bi bi-filter-circle text-primary"></i> İşlem :
        </label>
        <select class="form-control-select islemSec d-inline-block" name="islemSec" style="width: auto; min-width: 150px;">
          <option value="0">Hepsi</option>
          <option value="1">Alış</option>
          <option value="2">Serviste Kullanım</option>
          <option value="4">Müşteriden Geri Alma</option>
        </select>
      </div>
    </div>
  </div>

  <div class="card-body" style="padding: 0;">
    <div class="table-responsive">
      <table class="table table-bordered table-sm mb-0">
        <thead>
          <tr>
            <th style="display:none;"></th>
            <th style="width: 50px;">Tarih</th>
            <th>İşlem</th>
            <th>Detay</th>
            <th>Adet</th>
            <th>Fiyat</th>
            <th style="width: 55px;">Sil</th>
          </tr>
      <tr class="toplam-header-row" style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
        <td style="display:none;"></td>
        <td colspan="3"></td>
        <td class="toplam-adet-header" style="font-weight: bold; text-align: left; color: #050505;">
          0 Adet
        </td>
        <td class="toplam-fiyat-header" style="font-weight: bold; text-align: left; color: #050505;">
          0 TL
        </td>
        <td></td>
      </tr>
        </thead>
        <tbody>
          @php $toplam = 0; @endphp
          @foreach($stokHareketleri as $stokIslem)
            @php
              $tarihSaat = explode(' ', $stokIslem->created_at);
              $tarih = explode('-', $tarihSaat[0]);

              $filterIslem = 0;
              $islem = '';
              $renk = '';

              if($stokIslem->islem == 1){
                $islem = "Alış";
                $renk = 'background-color: rgb(135, 255, 135);';
                $filterIslem = 1;
                $toplam += $stokIslem->adet;
              }elseif($stokIslem->islem == 4){
                $islem = "Müşteriden Geri Alma";
                $renk = 'background-color: rgb(135, 206, 235);'; 
                $filterIslem = 4;
                $toplam += $stokIslem->adet;
             }elseif($stokIslem->islem == 2){ 
              $islem = "Serviste Kullanım";
              $filterIslem = 2;
              $toplam -= $stokIslem->adet;
              $teknisyenAdi = $stokIslem->performer_name ?? 'Bilinmiyor';
            
          }
            
            @endphp

            <tr style="{{ $renk }}">
              <td class="tdNumber" style="display:none;">0,{{ $stokIslem->islem }}</td>
              <td>{{ $tarih[2] }}/{{ $tarih[1] }}/{{ $tarih[0] }}</td>
              <td>{{ $islem }}</td>
              <td>
                @if($stokIslem->islem == 1)
                    {{ $stokIslem->tedarikci ?? '-' }}
                @elseif($stokIslem->islem == 2)
                <a href="{{ route('all.services', [$firma->id,'did'=>$stokIslem->servisid]) }}" target="_blank" class="link-stock">
                Servis: {{ $stokIslem->servisid }}
                </a> ({{ $teknisyenAdi }})
                @elseif($stokIslem->islem == 4)
                    Müşteri: {{ $stokIslem->servis->musteri->adSoyad ?? '-' }}
                @endif
              </td>
              <td>{{ $stokIslem->adet }}</td>
              <td>
                @if($stokIslem->islem == 1 && $stokIslem->fiyat > 0)
                  {{ number_format($stokIslem->fiyat, 2, '.', '') }} TL
                @else
                  -
                @endif
              </td>
              <td>
                <button type="button"
                    class="btn btn-danger btn-sm stokKonsinyeSilBtn"
                    data-id="{{ $stokIslem->id }}">
                    <i class="fas fa-trash-alt"></i>
                </button>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Hareket Ekle Modal -->
<div class="modal fade" id="hareketEkleModal" tabindex="-1" aria-labelledby="hareketEkleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="hareketEkleForm" method="POST" action="{{ route('store.consignment.stock.action', request()->route('tenant_id')) }}">
      @csrf
      <input type="hidden" name="stok_id" id="modalStokId">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Konsinye Stok Hareketi Ekle</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
        </div>
        <div class="modal-body">

          <div class="mb-3">
            <label for="islem">İşlem</label>
            <select name="islem" class="form-control" id="islemSecModal" required>
              <option value="">Seçiniz</option>
              <option value="1" selected>Alış</option>
            </select>
          </div>

          <div class="mb-2"  id="tedarikciGroup">
            <label>Tedarikçi</label>
            <select name="tedarikci" class="form-control">
              <option value="">Seçiniz</option>
              @foreach(\App\Models\StockSupplier::all() as $tedarikci)
                <option value="{{ $tedarikci->id }}">{{ $tedarikci->tedarikci }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label for="adet">Adet</label>
            <input type="number" name="adet" class="form-control" required min="1">
          </div>

          <div class="mb-3" id="fiyatGroup">
            <label for="fiyat">Fiyat (TL)</label>
            <input type="text" name="fiyat" class="form-control">
          </div>

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
  $(document).ready(function () {

    // Modal açıldığında stok ID'yi yerleştir ve tedarikçi kontrolü yap
    $(document).on('click', '.hareketEkleBtn', function() {
      let stokId = $(this).data('stokid');
      $('#modalStokId').val(stokId);
      $('#hareketEkleModal').modal('show');

      // Modal açıldığında işlem tipine göre tedarikçiyi kontrol et
      setTimeout(kontrolTedarikciGoster, 100); // DOM tam yüklensin
    });

    $(document).ready(function () {
    $('.islemSec').on('change', function () {
      var selected = $(this).val();
      var card = $(this).closest('.card');
      var rows = card.find('table tbody tr');
      var toplamAdet = 0;
      var toplamFiyat = 0;

       rows.each(function () {         
      var tdNumber = $(this).find('.tdNumber').text().trim();

      if (selected == 0) {           
        $(this).show();                      
        // Hepsi filtresi seçildiğinde toplam hesaplanmayacak
      } else {           
        if (tdNumber.endsWith(',' + selected)) {             
          $(this).show();                          
          var adet = parseInt($(this).find('td').eq(4).text()) || 0;
          var fiyat = parseFloat($(this).find('td').eq(5).text().replace(' TL', '').trim()) || 0;
          toplamAdet += adet;             
          toplamFiyat += fiyat;                        
        } else {             
          $(this).hide();           
        }       
      }     
    });
    // Header'daki toplam bilgileri güncelle - sadece filtrelenmiş durumda
    if (selected == 0) {
      // Hepsi filtresi seçildiğinde toplam bilgilerini gizle veya sıfırla
      card.find('.toplam-adet-header').text('');
      card.find('.toplam-fiyat-header').text('');
    } else {
      card.find('.toplam-adet-header').text(toplamAdet);       
      card.find('.toplam-fiyat-header').text(toplamFiyat.toFixed(2)+' TL');
    }
  });  
    // Sayfa yüklendiğinde toplam hesapla
    $('.islemSec').trigger('change');
  });


function kontrolTedarikciGoster() {
  let islem = $('#islemSecModal').val();

  if (islem == '1') { // Alış
    $('#tedarikciGroup').show();
    $('#fiyatGroup').show();
    $('#fiyatInput').prop('required', true);
    $('#musteriGroup').hide();
    $('#musteriGroup select').val('');
  } else if (islem == '4') { // Müşteriden Geri Alma
    $('#tedarikciGroup').hide();
    $('#tedarikciGroup select').val('');
    $('#fiyatGroup').hide();
    $('#fiyatInput').prop('required', false);
    $('#musteriGroup').show();
  } else {
    $('#tedarikciGroup').hide();
    $('#fiyatGroup').show();
    $('#fiyatInput').prop('required', true);
    $('#musteriGroup').hide();
    $('#musteriGroup select').val('');
  }
}
    // Modal içindeki işlem tipi değişince tedarikçi göster/gizle
    $('#islemSecModal').on('change', function () {
      kontrolTedarikciGoster();
    });
    // Sayfa ilk yüklendiğinde tedarikçi alanı gizli olsun
    $('#tedarikciGroup').hide();
    $('#musteriGroup').hide();
  });
</script>
<script>
$(document).on('click', '.stokKonsinyeSilBtn', function () {
    var id = $(this).data('id');
    var tenant_id = "{{ request()->route('tenant_id') }}";

    Swal.fire({
        title: 'Emin misiniz?',
        text: "Stok hareketini silmek istiyor musunuz?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Evet, sil',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                 url: '/'+tenant_id+'/stok-konsinye-hareket-sil/' + id,
                  method: 'POST',
                  data: {
                      _token: '{{ csrf_token() }}',
                      _method: 'DELETE'  // Laravel bunu DELETE olarak işler
                  },
                success: function (res) {
                    if (res.status === 'success') {
                        Swal.fire('Silindi!', res.message, 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        Swal.fire('Uyarı', res.message, 'warning');
                    }
                },
                error: function () {
                    Swal.fire('Hata!', 'Silme işlemi sırasında hata oluştu.', 'error');
                }
            });
        }
    });
});
</script>
