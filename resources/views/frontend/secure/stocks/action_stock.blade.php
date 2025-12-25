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
    <option value="3">Personel'e Gönder</option>
    <option value="2">Serviste Kullanım</option>
  </select>
  <div class="mt-2 toplam-bilgi" style="display: none;">
    <small class="text-muted">
      <span class="badge bg-info">
        <i class="bi bi-calculator"></i> 
        Toplam: <span class="toplam-adet">0</span> Adet - <span class="toplam-tutar">0.00</span> TL
      </span>
    </small>
  </div>
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

               if ($stokIslem->islem == 1) {
                  $islem = "Alış";
                  $renk = 'background-color: rgb(135, 255, 135);';
                  $filterIslem = 1;
                  $toplam += $stokIslem->adet;
                }elseif ($stokIslem->islem == 2) {
                $islem = "Serviste Kullanım";
                $filterIslem = 2;
                $teknisyenAdi = $stokIslem->performer_name ?? 'Bilinmiyor'; 
            } elseif ($stokIslem->islem == 3) {
                  $islem = "Personel Depo";
                  $renk = 'background-color: rgb(255, 119, 119);';
                  $filterIslem = 3;
                
                  $perKasa = \App\Models\PersonelStock::find($stokIslem->perStokId);
                  $perSec = $perKasa ? \App\Models\User::find($perKasa->pid) : \App\Models\User::find($stokIslem->personel);
                  $toplam -= $stokIslem->adet; 
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
                  @elseif($stokIslem->islem == 3)
                    {{ $perSec->name ?? '' }}
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
                    class="btn btn-outline-danger btn-sm stokSilBtn"
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
<div class="modal fade " id="hareketEkleModal" tabindex="-1" aria-labelledby="hareketEkleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="hareketEkleForm" method="POST" action="{{ route('store.stock.action', request()->route('tenant_id')) }}">
      @csrf
      <input type="hidden" name="stok_id" id="modalStokId">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Stok Hareketi Ekle</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="islem">İşlem</label>
            <select name="islem" class="form-control" required>
              <option value="">Seçiniz</option>
              <option value="1" selected>Alış</option>
              <option value="3">Personel'e Gönder</option>
            </select>
          </div>
          <div class="mb-2">
            <label>Tedarikçi</label>
            <select name="tedarikci" class="form-control">
              <option value="">Seçiniz</option>
              @foreach(\App\Models\StockSupplier::all() as $tedarikci)
                <option value="{{ $tedarikci->id }}">{{ $tedarikci->tedarikci }}</option>
              @endforeach
            </select>
          </div>

        <div class="mb-2 d-none" id="personelSelectDiv">
          <label>Personel</label>
          <select name="personel" class="form-control">
            <option value="">Seçiniz</option>
            @foreach(\App\Models\User::where('tenant_id', request()->route('tenant_id'))->get() as $personel)
              <option value="{{ $personel->user_id }}">{{ $personel->name }}</option>
            @endforeach
          </select>
        </div>

          <div class="mb-3">
            <label for="adet">Adet</label>
            <input type="number" name="adet" class="form-control" required min="1">
          </div>

          <div class="mb-3" id="fiyatInputDiv">
            <label for="fiyat">Fiyat (TL)</label>
            <input type="text" name="fiyat" class="form-control" required>
          </div>

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Kaydet</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        </div>
      </div>
      
    </form>
  </div>
</div>

<script>      
$(document).on('click', '.hareketEkleBtn', function() {     
  let stokId = $(this).data('stokid');     
  $('#modalStokId').val(stokId);     
  $('#hareketEkleModal').modal('show');   
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
      card.find('.toplam-adet-header').text(toplamAdet + ' Adet');       
      card.find('.toplam-fiyat-header').text(toplamFiyat.toFixed(2)+' TL');
    }
  });           
  
  // Sayfa yüklendiğinde toplam hesapla     
  $('.islemSec').trigger('change');   
});  
</script>  

<script>   
$(document).ready(function() {     
  $('select[name="islem"]').on('change', function() {       
    var val = $(this).val();             
    
    if (val == '1') {         
      // Alış         
      $('#fiyatInputDiv').show();         
      $('#fiyatInputDiv input').prop('required', true);          
      $('#personelSelectDiv').addClass('d-none');         
      $('#personelSelectDiv select').prop('required', false);          
      $('select[name="tedarikci"]').closest('.mb-2').show();       
    } else if (val == '3') {         
      // Personel'e Gönder         
      $('#fiyatInputDiv').hide();         
      $('#fiyatInputDiv input').prop('required', false).val('');          
      $('#personelSelectDiv').removeClass('d-none');         
      $('#personelSelectDiv select').prop('required', true);          
      $('select[name="tedarikci"]').closest('.mb-2').hide();         
      $('select[name="tedarikci"]').val('');       
    } else {         
      // Varsayılan durum         
      $('#fiyatInputDiv').show();         
      $('#fiyatInputDiv input').prop('required', true);         
      $('#personelSelectDiv').addClass('d-none');         
      $('select[name="tedarikci"]').closest('.mb-2').show();       
    }     
  });   
}); 
</script>



<script>
$(document).on('click', '.stokSilBtn', function () {
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
                 url: '/'+tenant_id+'/stok-haraket-sil/' + id,
                  method: 'POST',
                  data: {
                      _token: '{{ csrf_token() }}',
                      _method: 'DELETE'  
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
