<div id="serviceInformationForm">
<meta name="csrf-token" content="{{ csrf_token() }}">
<form method="POST" id="servisDuzenle">
  @csrf
<div class="card card1" style="margin-bottom: 5px">
  <div class="card-header ch1" style="padding: 3px 10px!important;">
    <div class="row">
      <div class="col-12 col-md-4 left">
        <label>Tarih: </label>
        <input type="text" name="tarih" class="form-control tarih" value="{{ Carbon\Carbon::parse($service_id->created_at)->format('d/m/Y H:i:s')}}" disabled="" style="width: 120px;display: inline-block;background: #fff;padding: 3px 5px;font-size:12px;">
      </div>
      <div class="col-12 col-md-8 text-align-right" style="text-align: right;"> 
        <label>Müşteri Kaynağı: </label>
        <select class="form-control form-select kaynak" name="kaynak" style="width: 151px; display: inline-block;padding: 3px 5px;font-size:12px;">
          <option value="">-Seçiniz-</option>
          @foreach($service_resources as $resource)
            <option value="{{$resource->id}}" {{ $service_id->servisKaynak == $resource->id ? 'selected' : ''}}>{{$resource->kaynak}}</option>
          @endforeach
        </select>

        <label class="mt-1">Operatör: <span class="kayitAlan">{{$service_id->users->name}}</span> </label>

      </div>
    </div>
  </div>      
</div>

<div class="row cardWrap2">
  <div class="col-sm-6 custom-p-r-min">
    <div class="card card2">
      <div class="card-header" style="padding: 7px 10px!important;">MÜŞTERİ BİLGİSİ
        <span><a href="#" data-id="{{$service_id->musteri->id}}" class="servisMusteriDuzenleBtn"><i class="fas fa-edit" style="font-size: 15px;color: red;text-shadow: none;"></i></a></span>
      </div>
      <div class="card-body" id="card2">
        @if(!empty($service_id->musteri->adSoyad))<span class="musBilCek" id="musBilCek"><strong>{{$service_id->musteri->adSoyad}}       
        @if($service_id->musteri->musteriTipi == '1')
          (BİREYSEL)
        @elseif($service_id->musteri->musteriTipi == '2')
          (KURUMSAL)
        @endif
        </strong></span>@endif
        @if(!empty($service_id->musteri->tel1))<span id="tele"><a href="tel:{{$service_id->musteri->tel1}}" style="color:red">{{$service_id->musteri->tel1}}</a> - <a href="tel:{{$service_id->musteri->tel2}}" style="color:red">{{$service_id->musteri->tel2}}</a></span>@endif  
        @if(!empty($service_id->musteri->adres))<span id="maps">{{$service_id->musteri->adres}}</span>@endif
        @if(!empty($service_id->musteri->vergiNo))<span id="vergi">{{$service_id->musteri->vergiNo}} / {{$service_id->musteri->vergiDairesi}}</span>@endif
      </div>
    </div>
    <div class="card b1">
      <div class="card-body edit-padding" style="padding-top: 6px;">
        <div class="row form-group" style="border: 0;margin-bottom:0;">
          <div class="col-md-4 rw1"><label>Müsait Olma Zamanı</label></div>
            <div class="col-md-8 rw2 d-flex gap-2">
              <input name="musaitTarih" type="date" class="form-control datepicker kayitTarihi" value="{{$service_id->musaitTarih}}" style="background:#fff;display: inline-block;" data-has-listeners="true">
              <select name="musaitSaat1" class="form-control form-select musaitSaat1" style="display: inline-block;">
                @php
                  $saatler = [
                    "08:00","08:30","09:00","09:30","10:00","10:30","11:00","11:30",
                    "12:00","12:30","13:00","13:30","14:00","14:30","15:00","15:30",
                    "16:00","16:30","17:00","17:30","18:00","18:30","19:00","19:30",
                    "20:00","20:30","21:00","21:30","22:00","22:30","23:00"
                  ];
                @endphp
                @foreach ($saatler as $saat)
                  <option value="{{ $saat }}" {{ $service_id->musaitSaat1 == $saat ? 'selected' : '' }}>
                    {{ $saat }}
                  </option>
                @endforeach
              </select>

              <select name="musaitSaat2" class="form-control form-select musaitSaat2" style="display: inline-block;">
                @foreach ($saatler as $saat)
                  <option value="{{ $saat }}" {{ $service_id->musaitSaat2 == $saat ? 'selected' : '' }}>
                    {{ $saat }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>
        <!--Konsinye Cihaz-->
        <div class="row form-group" style="border: 0;margin-bottom:0;">
          <div class="col-md-4 rw1"><label>Konsinye Cihaz</label></div>
          <div class="col-md-8 rw2">
            <div class="konsinye-cihaz-container">
              @if(count($seciliKonsinyeCihazlar) > 0)
              @foreach($seciliKonsinyeCihazlar as $konsinyeId => $adet)
                  @php
                      $urun = $konsinyeCihazlar->firstWhere('id', $konsinyeId);
                  @endphp
                  @if($urun)
                      <div>
                          <strong style="color:red;">{{ $urun->urunAdi }}</strong> 
                      </div>
                  @endif
              @endforeach
              @else
                  <span>Konsinye cihaz atanmadı.</span>
              @endif
            </div>
          </div>
        </div>

        <div class="row form-group" style="border: 0;margin-bottom:0;">
          <div class="col-md-4 rw1"><label>Fatura Numarası</label></div>
          <div class="col-md-8 rw2">
            <input type="text" name="faturaNumarasi" class="form-control buyukYaz" autocomplete="off" value="{{$service_id->faturaNumarasi}}"></div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-sm-6">
      <div class="card card5">
        <div class="card-header" style="padding:7px 10px!important;">CİHAZ BİLGİSİ</div>
        <div class="card-body">
                <div class="row form-group ">
                  <div class="col-md-4 rw1 custom-p-r-min"><label>Cihaz Markası <span style="font-weight: bold; color: red;">*</span></label></div>
                  <div class="col-md-8 custom-p-min">
                    <select class="form-control form-select cihazMarka" name="cihazMarka" required>
                      <option value="">-Seçiniz-</option>
                      @foreach($device_brands as $marka)
                        <option value="{{ $marka->id }}" {{$service_id->cihazMarka == $marka->id ? 'selected' : ''}}>{{ $marka->marka }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="row form-group ">
                  <div class="col-md-4 rw1 custom-p-r-min"><label>Cihaz Türü <span style="font-weight: bold; color: red;">*</span></label></div>
                  <div class="col-md-8 custom-p-min">
                    <select class="form-control form-select cihazTur" name="cihazTur" required>
                      <option value="">-Seçiniz-</option>
                      @foreach($device_types as $cihaz)
                        <option value="{{ $cihaz->id }}" {{$service_id->cihazTur == $cihaz->id ? 'selected' : ''}}>{{ $cihaz->cihaz }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="row form-group">
                  <div class="col-md-4 rw1 custom-p-r-min"><label>Cihaz Modeli</label></div>
                  <div class="col-md-8 custom-p-min"><input type="text" name="cihazModel" class="form-control" autocomplete="off" value="{{$service_id->cihazModel}}"></div>
                </div>
                <div class="row form-group">
                  <div class="col-md-4 rw1 custom-p-r-min"><label>Cihaz Arızası <span style="font-weight: bold; color: red;">*</span></label></div>
                  <div class="col-md-8 custom-p-min">
                    <input id="arizaSearch" type="text" name="cihazAriza" class="form-control buyukYaz cihazAriza" autocomplete="off" value="{{$service_id->cihazAriza}}" required>
                    <ul id="arizaResult" style="margin: 0;padding: 0"></ul>
                  </div>
                </div>
                <div class="row form-group">
                  <div class="col-md-4 rw1 custom-p-r-min"><label>Operatör Notu</label></div>
                  <div class="col-md-8 custom-p-min"><input type="text" name="opNot" class="form-control opNot" autocomplete="off" value="{{$service_id->operatorNotu}}"></div>
                </div>
                <div class="row form-group" style="margin-bottom: 0; border: 0;">
                  <div class="col-md-4 rw1 custom-p-r-min"><label>Garanti Süresi</label></div>
                  <div class="col-md-8 custom-p-min">
                    <select class="form-control form-select" name="cihazGaranti" style="display: inline-block;width: 26%; width: intrinsic; margin-right: 5px;">
                      <option value="">-Seçiniz-</option>
                      @foreach($warranty_periods as $index => $garanti)
                        <option value="{{ $garanti->id }}" {{ $service_id->garantiSuresi == $garanti->id ? 'selected' : ''}}>
                          {{ $garanti->garanti }} Ay
                        </option>
                      @endforeach
                    </select>
                    {{-- Gün gösterimi --}}
                    @php 
                      use Carbon\Carbon;
                      // kayıt tarihi (örnek: "2024-05-01")
                      $kayitTarihi = Carbon::parse($service_id->kayitTarihi);

                      // Seçili garanti süresi
                      $garantiSuresi = $service_id->warranty->garanti; // örneğin 12 (ay)

                      // Garanti bitiş tarihi
                      $garantiBitis = $kayitTarihi->copy()->addMonths($garantiSuresi);

                      // Bugün ile karşılaştır
                      $kalanGun = Carbon::now()->diffInDays($garantiBitis, false); // negatifse süre bitmiştir
                    @endphp
                    @if($kalanGun !== null)
                      <span style="display:inline-block; margin-left: 10px;">
                        @if($kalanGun >= 0)
                          {{ $garantiBitis->format('d/m/Y') }} ({{ $kalanGun }} gün)
                        @else
                          Garanti süresi {{ abs($kalanGun) }} gün önce doldu ({{ $garantiBitis->format('d/m/Y') }})
                        @endif
                      </span>
                    @endif
                  </div>
                </div>
              </div>             
    </div>
  </div>
</div>
<input type="hidden" name="servisid" class="servisid" value="{{$service_id->id}}"/>
<input type="submit" class="btn btn-primary btn-sm btn-sm-custom" style="display: none;">
</form>

<div class="servisAsamalari">
  <div class="card card3">
    <div class="card-header" style="padding: 3px 7px!important;">
      <div class="row">
        <div class="col-12 col-sm-6 left custom-left">
          <label class="kayitAlan">  
            <span>{{$service_id->asamalar["asama"]}}</span>                  
          </label>     

         <label class="servisAcilLabel servisAcilBtn" style="user-select: none;-ms-user-select: none;-moz-user-select: none;-webkit-user-select: none;-webkit-touch-callout: none;position: relative;margin: 0; color: #fff; background: #343a40; border: 1px solid #212529;padding: 0 5px;border-radius: 3px;top: -2px;cursor: pointer;">
    <span>Acil</span>
    <input type="checkbox" class="acilCheckbox" style="display: none;" {{$service_id->acil == 1 ? 'checked' : ''}}>
    <div class="checkmark" style="display: inline-block; ">
        <i class="fas fa-check" style="position: absolute; top: -1px; left: 1px; font-size: 10px; {{$service_id->acil == 1 ? 'display: block;' : 'display: none;'}}"></i>
    </div>
</label>
<input type="hidden" name="acil" id="acilHiddenInput" class="acil" value="{{$service_id->acil == 1 ? '1' : '0'}}"/>

<!-- Form içinde servis ID'yi ekleyin -->
<input type="hidden" name="servisid" value="{{$service_id->id}}"/>   

        </div>
        
        <div class="col-12 col-sm-6 right">
          <label>Yapılacak işlem: </label>
          <select class="form-control altAsamalar" name="altAsamalar" style="padding:3px 5px;">
            <option value="">-Seçiniz-</option> 
            @php 
              $user = Auth::user();
            @endphp       
            @foreach ($altAsamalar as $item)
                @if($item->id != 264 || $user->tenant->canAccessDealersModule())
                    <option value="{{ $item->id }}">{{ $item->asama }}</option> 
                @endif
            @endforeach
          </select>
        </div>
      </div>
    </div>
    <div class="card-body altSecenekler" style="padding:0!important"></div>   
  </div>
</div>

<div class="card card4">
  <div class="card-body" style="padding: 0!important;">
    <div id="no-more-tables">
      <div class="table-responsive" style="margin: 0">
        <table class="table table-hover table-striped servisAsamaTable" id="servisAsamaTable" width="100%" cellspacing="0" style="margin: 0">
          <thead class="title">
            <tr>
              <th style="padding: 5px 10px;font-size: 12px;">Tarih</th>
              <th style="padding: 5px 10px;font-size: 12px;">İşlemi Yapan</th>
              <th style="padding: 5px 10px;font-size: 12px;">İşlem Adı</th>
              <th style="padding: 5px 10px;font-size: 12px;">Açıklama</th>
              <th colspan="2" class="text-end" style="padding: 10px;font-size: 12px;"></th>
            </tr>
          </thead>
          <tbody id="serviceHistoryTableBody">
            
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="card cf1" style="margin-top: 10px;">
  <div class="card-header" style="padding: 3px 5px;">
    <div class="row">
      <div class="col-12 col-sm-1">
        <input type="button" class="btn btn-danger btn-sm btn-sm-custom servisSil2" data-id="" value="Sil"/>
      </div>
      <div class="col-sm-11" style="text-align: right;">
        <a href="#" class="btn btn-warning btn-sm btn-sm-custom servisMusteriAnketiBtn" data-id="{{ $service_id->id }}">Müşteri Anketi</a>
        <a href="{{ route('serviceto.pdf', [$firma->id, $service_id->id]) }}" target="_blank" class="btn btn-warning btn-sm btn-sm-custom servisA4YazdirBtn">Yazdır</a>
        <a href="#" class="btn btn-sm btn-warning btn-sm-custom servisYaziKopyala" data-servis-id="{{ $service_id->id }}"> Fiş Yazdır</a>
        <input style="background-color: #343a40;border-color:#343a40" type="button" class="btn btn-info btn-sm btn-sm-custom servisGuncelleBtn" value="Servis Güncelle"/>
        <div class="clearfix"></div>
      </div>
    </div>
  </div>
</div>
</div>

<script>
$(document).ready(function() {
  var serviceId = {{$service_id->id}};
    loadServiceHistory( serviceId );
});

function loadServiceHistory(service_id) {
    var firma_id = {{$firma->id}};
    $.ajax({
        url: "/" + firma_id + '/servis-asama/' + service_id + '/history',
        method: 'GET',
        success: function(data) {
            renderServiceHistory(data);
        },
        error: function() {
            alert('Veriler yüklenirken hata oluştu.');
        }
    });
    // Konsinye cihaz bilgilerini güncelleme
    $.ajax({
        url: "/" + firma_id + '/servis-konsinye-cihaz/' + service_id,
        method: 'GET',
        success: function(data) {
            $('.konsinye-cihaz-container').html(data);
        },
        error: function() {
        }
    });
}

//Müşteri Anketi
$(document).on('click', '.servisMusteriAnketiBtn', function(e) {
    e.preventDefault();
    let servisId = $(this).data('id');
    let tenantId = '{{ $firma->id }}'; 
    $('#anketModal').modal('show');

    $('#anketModalContent').html('<div class="text-center"><span class="spinner-border text-primary"></span><p>Yükleniyor...</p></div>');

    $.ajax({
        url: `/${tenantId}/anket/${servisId}/create`,
        method: 'GET',
        success: function(response) {
            $('#anketModalContent').html(response);
        },
        error: function() {
            $('#anketModalContent').html('<div class="alert alert-danger">Form yüklenemedi.</div>');
        }
    });
});


function renderServiceHistory(data) {
    var tbody = $('#serviceHistoryTableBody');
    tbody.empty();
    var currentUserId = {{ auth()->id() }};
    var currentUserIsPatron = {{ auth()->user()->hasRole('Patron') ? 'true' : 'false' }};

    // Acil durum
    if (data.acilIslem) {
        var acilRow = `
          <tr class="acilRow">
            <td class="kayitTarihiCS" style="vertical-align: middle;width: 100px; font-size: 13px; padding: 5px;">${data.acilIslem.tarih}</td>
            <td style="vertical-align: middle;font-size: 13px; padding: 5px;"><strong>NOT</strong></td>
            <td style="vertical-align: middle;font-size: 13px; padding: 5px;"><strong>Servis Acil Aşamasındadır.</strong></td>
            <td style="vertical-align: middle;font-size: 13px; padding: 5px;" colspan="3">Servis işlemi bittiğinde acil işaretini kaldırın.</td>
          </tr>
        `;
        tbody.append(acilRow);
    }
    
    // Notlar
    data.notlar.forEach(function(not) {
        var notRow = `
            <tr>
                <td class="kayitTarihiCS" style="vertical-align: middle;width: 100px; font-size: 13px; padding: 5px;">${not.tarih}</td>
                <td style="vertical-align: middle;font-size: 13px; padding: 5px;">${not.personel}</td>
                <td style="vertical-align: middle;font-size: 13px; padding: 5px;color:#ec0000;"><strong>Operatör Notu</strong></td>
                <td style="vertical-align: middle;font-size: 13px; padding: 5px;" colspan="3"><strong>${not.aciklama}</strong></td>
            </tr>
        `;
        tbody.append(notRow);
    });
    
    // Eski işlemler
    data.eskiIslemler.forEach(function(islem) {
        if (islem.type === 'para') {
            var paraRow = `
                <tr>
                    <td class="kayitTarihiCS" style="vertical-align: middle;width: 100px; font-size: 13px; padding: 5px;">${islem.tarih}</td>
                    <td style="vertical-align: middle;font-size: 13px; padding: 5px;">${islem.personel}</td>
                    <td style="vertical-align: middle;font-size: 13px; padding: 5px;"><strong>${islem.islem}</strong></td>
                    <td style="vertical-align: middle;font-size: 13px; padding: 5px;" colspan="3"><strong>${islem.aciklama}</strong></td>
                </tr>
            `;
            tbody.append(paraRow);
        } else {
            var buttons = '';
if (islem.pid == currentUserId || currentUserIsPatron) {
    // Hücreyi (td) oluştur
    buttons += '<td colspan="2">';
    
    // Değişiklik: İkonları sağa yaslamak ve aralarında boşluk bırakmak için bir div eklendi.
    // d-flex: Flexbox'ı etkinleştirir.
    // justify-content-end: İçeriği sağa yaslar.
    // gap-2: Elemanlar arasına boşluk ekler.
    buttons += '<div class="d-flex justify-content-end gap-2">';

    // Sil butonu (Gereksiz inline stiller kaldırıldı)
    buttons += `<a style="    padding: 6px 7px;" href="#" id="servisPlanSil" class="btn btn-outline-danger btn-sm btn-sm-custom servisPlanSil" data-id="${islem.id}" title="Sil"><i style="line-height: 1.2;" class="fas fa-trash-alt"></i></a>`;
    
    // Düzenle butonu (Gereksiz inline stiller kaldırıldı, boşluk 'gap-2' ile sağlandı)
    buttons += `<a style="padding: 6px 6px;color:#e39d23 " href="#" data-bs-id="${islem.id}" class="btn btn-outline-warning btn-sm btn-sm-custom servisPlanDuzenleBtn" title="Düzenle"><i class="fas fa-edit"></i></a>`;
     
    buttons += '</div>'; // Flexbox div'ini kapat
    buttons += '</td>';  // Hücreyi (td) kapat
} else {
    // Yetkiniz yoksa olan kısım (değişiklik yok)
    buttons += `
        <td colspan="2" style="font-size: 11px; color: red; text-align: center;">
            <strong>Yetkiniz yok</strong>
        </td>
    `;
}
            
            
            var islemRow = `
                <tr>
                    <td class="kayitTarihiCS" style="vertical-align: middle;width: 100px; font-size: 13px; padding: 0 5px;">${islem.tarih}</td>
                    <td style="vertical-align: middle;font-size: 13px; padding:  5px;">${islem.personel}</td>
                    <td class="islemAsamaCS" style="vertical-align: middle;font-size: 13px; padding:  5px;"><strong>${islem.asama}</strong></td>
                    <td class="islemAciklamaCS" style="vertical-align: middle;font-size: 13px;padding: 5px;width: 300px;text-transform: capitalize;">${islem.aciklamalar.join('<br>')}</td>
                    ${buttons}
                </tr>
            `;
            tbody.append(islemRow);
        }
    });
    
    // Para hareketleri
    data.paraHareketleri.forEach(function(para) {
        var paraRow = `
            <tr>
                <td class="kayitTarihiCS" style="vertical-align: middle;width: 100px; font-size: 13px; padding: 5px;">${para.tarih}</td>
                <td style="vertical-align: middle;font-size: 13px; padding: 5px;">${para.personel}</td>
                <td style="vertical-align: middle;font-size: 13px; padding: 5px;"><strong>${para.islem}</strong></td>
                <td style="vertical-align: middle;font-size: 13px; padding: 5px;" colspan="3"><strong>${para.aciklama}</strong></td>
            </tr>
        `;
        tbody.append(paraRow);
    });
}
</script>

<script type="text/javascript">
  $(".servisMusteriDuzenleBtn").click(function(){
    var id = {{$service_id->musteri_id}};
    var firma_id = {{$firma->id}};
    $('#editServiceCustomerModal').modal('show');
    $.ajax({
      url: "/" + firma_id + "/servis-musteri/duzenle/" + id
    }).done(function(data) {
      if($.trim(data)==="-1"){
        window.location.reload(true);
      }else{
        $('#editServiceCustomerModal .modal-body').html(data);
      }
    });
  });
  
</script>

<script>
  $(document).ready(function () {
    var musteriAdSoyad = "{{$service_id->musteri->adSoyad}}";
    var musteriFirmaAdi = "{{$service_id->id}}";
    $("#editServiceDescModal .modal-title").html(musteriAdSoyad + " (" + musteriFirmaAdi + ")");
  });
</script>

<script type="text/javascript">
  var csrfToken = $('meta[name="csrf-token"]').attr('content');
  $('.kategori').on('change', function(){
    var id = $(this).val();
    var musteri = "{{$service_id->id}}";
    $.ajax({
      url: "",
      method: "POST",
      data: {
        id:id,
        musteri:musteri,
        _token: csrfToken,
      },
    }).done(function(data){
      alert("Müşteri türü güncellendi");
    });
  });

  $('.kaynak').on('change', function(){
    var id = $(this).val();
    var musteri = "{{$service_id->id}}";
    $.ajax({
      url: "",
      method:"POST",
      data: {
        id:id,
        musteri:musteri,
        _token:csrfToken,
      },
    }).done(function(data){
      alert("Müşteri kaynağı güncellendi");
    });
  });

  $(".altAsamalar").on("change", function () {
    var id = $(this).val();
    var service = {{$service_id->id}};
    var firma_id = {{$firma->id}};
    if(id){
      $.ajax({
        url: "/" + firma_id + "/servis-asama-sorusu-getir/" + id + "/" + service 
      }).done(function(data) {
        if($.trim(data)==="-1"){
          window.location.reload(true);
        }else{ //Konsinye cihaz modal güncelleme
          $('.altSecenekler').html(data);
           // Eğer yüklenen form konsinye ile ilgiliyse, submit sonrasında güncelle
                $('.altSecenekler form').on('submit', function() {
                    setTimeout(function() {
                        loadServiceHistory(service); // Mevcut fonksiyonunu kullan
                    }, 1500);
                });
        }
      });
    }else{
      $('.altSecenekler').html("");
    }
  });

  $(".opNotEkleBtn").click(function() {
    var not = $(".opNot").val();
    var musteri = {{$service_id->id}};
    $.ajax({
      url: "",
      method: "POST",
      data: {
        cnote:not,
        id:musteri,
        _token:csrfToken,
      },
    }).done(function(data){
      if(data === false){
        window.location.reload(true);
      }else{
        $(".opNot").val("");
        $('#servisAsamaTable tbody').html(data);
        $('#datatableService').DataTable().ajax.reload();
        $('.nav1').trigger('click');
      }
    });
  });
</script>

<script>
  $(document).ready(function(){
    $('#servisAsamaTable').on('click', '.musNotDuzenle', function(e) {
      var id = $(this).attr("data-bs-id");
      $('#editCustomerNotModal').modal('show');
      $.ajax({
        url: ""
      }).done(function(data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {  
          $('#editCustomerNotModal .modal-body').html(data);              
        }
      });
    });

    $('#servisAsamaTable').on('click', '.servisPlanDuzenleBtn', function(e) {
      var id = $(this).attr("data-bs-id");
      $('#editServicePlanModal').modal('show');
      var firma_id = {{$firma->id}};
      $.ajax({
        url: "/" + firma_id + "/servis-plan/duzenle/" + id
      }).done(function(data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {       
          $('#editServicePlanModal .modal-body').html(data);               
        }
      });
    });
  });
</script>

<script>
  $(document).ready(function() {
    $('#servisAsamaTable').on('click', '.servisPlanSil', function(e) {
      e.preventDefault();
      var confirmDelete = confirm("Bu müşteri aşamasını silmek istediğinizden emin misiniz?");
      if (confirmDelete) {
        var id = $(this).attr('data-id');
        var firma_id = {{$firma->id}};
        $.ajax({
          url: '/' + firma_id + '/servis-plan-sil/' + id,
          type: 'POST',
          data: {
            _method: 'POST', 
            _token: '{{ csrf_token() }}'
          },
          success: function(data) {
            if (data) {
              $('#servisAsamaTable tbody').html(data);
              loadServiceHistory({{ $service_id->id }});
              $('#datatableService').DataTable().ajax.reload();

              if (data.altAsamalar) {
              var altAsamalarSelect = $('.servisAsamalari .altAsamalar');
              altAsamalarSelect.empty();
              altAsamalarSelect.append('<option value="">-Seçiniz-</option>');
              
              $.each(data.altAsamalar, function(index, item) {
                altAsamalarSelect.append('<option value="' + item.id + '">' + item.asama + '</option>');
              });
              
              // Hiçbir seçenek seçili olmasın
              altAsamalarSelect.prop('selectedIndex', 0);
            }

              $('.kayitAlan span').text(data.asama);
              
            } else {
              alert("Silme işlemi başarısız oldu.");
            }
          },
          error: function(xhr, status, error) {
            console.error(xhr.responseText);
          }
        });
      }
    });
  });
</script>

<script type="text/javascript">
$(document).ready(function() {
    // Acil butonuna tıklandığında
    $(".servisAcilBtn").on('click', function(e) {
        e.preventDefault();
        
        var checkbox = $(this).find('.acilCheckbox');
        var checkIcon = $(this).find('.checkmark i'); // Sadece check işaretini seç
        var hiddenInput = $('#acilHiddenInput');
        
        // Checkbox durumunu toggle et
        if (checkbox.is(':checked')) {
            checkbox.prop('checked', false);
            checkIcon.hide(); // Sadece check işaretini gizle
            hiddenInput.val('0');
            $(this).css('background', '#343a40'); // Normal renk
        } else {
            checkbox.prop('checked', true);
            checkIcon.show(); // Sadece check işaretini göster
            hiddenInput.val('1');
            $(this).css('background', '#343a40'); // Kırmızı renk (acil)
        }
        
        // Otomatik kaydetme (opsiyonel - hemen kaydetmek isterseniz)
        // autoSaveAcil();
    });
    
    // Sayfa yüklendiğinde acil durumunu kontrol et
    if ($('.acilCheckbox').is(':checked')) {
        $('.servisAcilBtn').css('background', '#dc3545');
        $('.checkmark i').show(); // Sadece check işaretini göster
    }
    
    // Servis güncelle butonu
    $(".servisGuncelleBtn").click(function(e) {
        e.preventDefault();
        $("#servisDuzenle").submit();
    });

    // Form submit işlemi
    $("#servisDuzenle").on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        var firma = {{$firma->id}};
        
        // Acil değerinin doğru gönderildiğinden emin ol
        var acilValue = $('#acilHiddenInput').val();
        formData.set('acil', acilValue);
        
        // Debug için
        console.log('Acil değeri:', acilValue);
        console.log('Servis ID:', formData.get('servisid'));
        
        $.ajax({
            url: "/" + firma + "/servis/guncelle",
            type: "POST",
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                if (data.success) {
                    // Başarı mesajı göster
                    alert('Servis başarıyla güncellendi.');
                    
                    // Modal'ı kapat veya sayfayı yenile
                    setTimeout(function() {
                        $('.nav1').trigger('click');
                        loadServiceHistory({{ $service_id->id }});
                        $('#datatableService').DataTable().ajax.reload();
                        // veya $('#servisModal').modal('hide');
                        // veya location.reload();
                    }, 1000);
                } else {
                    alert('Güncelleme başarısız: ' + (data.message || ''));
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = "Servis güncellenirken hata oluştu.";
                alert(errorMessage);
                console.error("Error:", xhr.responseJSON);
            }
        });
    });
});
</script>
<script>
  $(document).on('click', '.servisYaziKopyala', function(e) {
    e.preventDefault();
    
    var servisId = $(this).data('servis-id'); // veya nasıl alıyorsan
    var btn = $(this);
    var originalText = btn.html();
    var firma = {{$firma->id}};

    btn.html('<i class="fas fa-spinner fa-spin"></i> Yükleniyor...').prop('disabled', true);
    
    $.ajax({
        url: '/' + firma + '/servis/' + servisId + '/fis-icerigi',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Panoya kopyala
                navigator.clipboard.writeText(response.icerik).then(function() {
                    toastr.success('Fiş içeriği panoya kopyalandı!');
                    btn.html('<i class="fas fa-check"></i> Kopyalandı!');
                    
                    setTimeout(function() {
                        btn.html(originalText).prop('disabled', false);
                    }, 2000);
                }).catch(function(err) {
                    // Fallback - eski yöntem
                    fallbackCopyTextToClipboard(response.icerik);
                    toastr.success('Fiş içeriği panoya kopyalandı!');
                    btn.html(originalText).prop('disabled', false);
                });
            } else {
                toastr.error(response.message);
                btn.html(originalText).prop('disabled', false);
            }
        },
        error: function(xhr) {
            var errorMsg = xhr.responseJSON?.message || 'Bir hata oluştu';
            toastr.error(errorMsg);
            btn.html(originalText).prop('disabled', false);
        }
    });
});

// Eski tarayıcılar için fallback
function fallbackCopyTextToClipboard(text) {
    var textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
    } catch (err) {
        console.error('Kopyalama hatası:', err);
    }
    
    document.body.removeChild(textArea);
}
</script>