
<div class="add_invoices_superadmin">
<form method="post" id="addInvo" action="{{ route('super.admin.invoices.store')}}" enctype="multipart/form-data" class="needs-validation" novalidate>
@csrf
<input type="hidden" name="form_token" id="formTokenInvoice" value="">
<div class="card card-invocies f5">
  <div class="card-header ch1" style="padding: 3px 10px;">
    <div class="tarihWrap">
      <label style="text-align: left;width: auto;display: inline-block;margin: 0;margin-right: 3px;">Tarih<span style="font-weight: bold; color: red;">*</span></label>
      <input type="date" name="faturaTarihi" class="form-control datepicker kayitTarihi"  value="{{date('Y-m-d')}}" style="width: 100px!important;display: inline-block;background:#fff" required>
    </div>
    <div class="clearfix"></div>
  </div>
</div> 
<div class="row">
  <!-- FİRMA BİLGİSİ -->
  <div class="col-lg-6">
    <div class="card card-invocies f2" style="min-height: 106px;">
      <div class="card-header card-invocies-header">FİRMA BİLGİSİ</div>
<div class="card-body card-invocies-body">
<div class="row form-group">
  <div class="col-md-3 rw1">
      <label>Firma Ara <span style="font-weight: bold; color: red;">*</span></label>
  </div>
  <div class="col-md-9 rw2">
      <div style="display: flex; gap: 5px; margin-bottom: 5px; align-items: stretch;">
          <input type="text" id="firmaArama" class="form-control" placeholder="Firma adı yazın..." autocomplete="off" style="flex: 1; height:30px;">
          <button type="button" id="bekleyenOdemelerBtn" class="btn btn-sm" style="background: #5dade2; color: white; white-space: nowrap; padding: 6px 10px; height:30px; border: none;" title="Faturası oluşturulmamış ödemesi olan firmaları göster">
              <i class="fas fa-file-invoice"></i>
          </button>
      </div>
      <ul id="firmaListesi" class="list-group" style="position: absolute; z-index: 1000; width: 92%; display: none;"></ul>
      <input type="hidden" name="firma_id" id="seciliFirmaId" required>
      <div id="seciliFirma" style="display: none; background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; margin-top: 5px; border-radius: 4px;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start;">
              <div>
                  <div style="font-weight: 600; color: #495057; margin-bottom: 8px; font-size: 14px;" id="seciliFirmaAdi"></div>
                  <div style="font-size: 12px; color: #6c757d; line-height: 1.4;" id="seciliFirmaDetay"></div>
              </div>
              <span style="cursor: pointer; color: #dc3545; font-size: 16px; font-weight: bold;" onclick="firmaTemizle()" title="Firmayı Temizle">&times;</span>
          </div>
      </div>
  </div>
</div>
        
        <!-- Hidden inputs for form submission -->
        <input type="hidden" name="vergiNo" class="vergiNo">
        <input type="hidden" name="vergiDairesi" class="vergiDairesi">
        <input type="hidden" name="tel1" class="tel1">
        <input type="hidden" name="tel2" class="tel2">
        <input type="hidden" name="il" class="il">
        <input type="hidden" name="ilce" class="ilce">
        <textarea name="adres" class="adres" style="display: none;"></textarea>
      </div>
    </div>
  </div>

  <!-- ÖDEME SEÇİMİ -->
  <div class="col-lg-6">
    <div class="card card-invocies f6">
      <div class="card-header card-invocies-header">ÖDEME SEÇİMİ</div>
      <div class="card-body card-invocies-body">
        <div class="alert alert-info" style="padding: 6px; font-size: 11px;">
          <strong>Bilgi:</strong> Önce bir firma seçin, ardından o firmaya ait tamamlanmış ödemeleri göreceksiniz. Birden fazla ödeme seçebilirsiniz.
        </div>
        
        <div id="odemeYukleniyor" style="display: none; text-align: center; padding: 15px;">
          <div class="loading-spinner"></div>
          <span style="margin-left: 10px; font-size: 13px;">Ödemeler yükleniyor...</span>
        </div>
        
        <div id="odemeListesi" style="display: none;">
          <h6 style="font-size: 14px; margin-bottom: 10px;">Fatura Oluşturulacak Ödemeler: </h6>
          <div id="odemeSecenekleri" class="payment-selection"></div>
        </div>
        
        <div id="secilenOdemelerOzeti" style="display: none;">
            <h6 style="font-size: 14px; margin-bottom: 10px;">Seçilen Ödemeler:</h6>
            <div id="secilenOzemeler" class="selected-payments-summary"></div>
            {{-- <div style="text-align: right; margin-top: 8px;">
                <strong>Toplam: <span id="toplamSecilenTutar">0</span> TL</strong>
            </div> --}}
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Çoklu ödeme için hidden inputlar -->
<div id="multiplePaymentInputs"></div>
<div id="multipleDescriptionInputs"></div>
<div id="multipleQuantityInputs"></div>
<div id="multiplePriceInputs"></div>
<div id="multipleTotalInputs"></div>
      
<div class="row cardRow1">
  <div class="card card-transform  col-lg-6 f3">
    <div class="card-body">
      <div class="row" style="border:0">
        <div class="col-md-4 rw1"><label>Ödeme Şekli<span style="font-weight: bold; color: red;"> *</span></label></div>
          <div class="col-md-8 rw2">
            <select class="form-select odemeSekilleri" name="odemeSekli" required>
              <option value="">Seçiniz</option>
              @foreach($payment_methods as $method)
                <option value="{{$method->id}}">{{$method->odemeSekli}}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="row form-group" style="border:0">
          <div class="col-md-4 rw1"><label>Toplam Yazıyla</label></div>
          <div class="col-md-8 rw2"><input type="text" name="toplamYazi" autocomplete="off" class="form-control buyukYaz toplamYazi" required></div>
        </div>

        <div class="row form-group" style="border:0">
          <div class="col-md-4 rw1"><label>Fatura No<span style="font-weight: bold; color: red;"> *</span></label></div>
          <div class="col-md-8 rw2">
            <input type="text" name="faturaNumarasi" class="form-control buyukYaz faturaNumarasi" value="" required>
          </div>
        </div>

        <div class="row form-group" style="border:0">
          <div class="col-md-4 rw1"><label>E-Arşiv<span style="font-weight: bold; color: red;"> *</span></label></div>
          <div class="col-md-8 rw2">
            <input type="file" class="form-control" name="document" id="customFile" required>
          </div>
        </div>       
      </div>
    </div>

    <div class="card col-lg-6 f4 custom-m">
      <div class="card-body" style="padding:17px 5px">
        <div class="row form-group">
          <div class="col-md-5 rw1"><label>Toplam (KDV Hariç)<span style="font-weight: bold; color: red;"> *</span></label></div>
          <div class="col-md-7 rw2"><input type="text" onkeyup="sayiKontrol(this)" name="toplam" autocomplete="off" class="form-control toplam" required></div>
        </div>

        <div class="row form-group">
          <div class="col-md-5 rw1"><label>İndirim</label></div>
          <div class="col-md-7 rw2"><input type="text" onkeyup="sayiKontrol(this)" name="indirim" autocomplete="off" class="form-control indirim" value="0.00"></div>
        </div>
        
        <div class="row form-group">
          <div class="col-md-5 rw1"><label>Ara Toplam</label></div>
          <div class="col-md-7 rw2"><input type="text" onkeyup="sayiKontrol(this)" name="araToplam" autocomplete="off" class="form-control araToplam"></div>
        </div>

        <div class="row form-group">
          <div class="col-md-5 rw1">
            <label>KDV %
              <div class="tooltip-container">
                <span class="tooltip-icon">i</span>
                <div class="tooltip-content">
                  <strong>💡 KDV Hesaplama:</strong><br>
                  • Ödeme seçildiğinde: KDV dahil tutar → KDV hariç tutara çevrilir<br>
                  • Tüm alanları manuel değiştirebilirsiniz<br>
                  • KDV oranı değiştirildiğinde otomatik yeniden hesaplanır
                </div>
              </div>
            </label>
          </div>
          <div class="col-md-3 rw2 col-6">
            <input type="text" onkeyup="sayiKontrol(this)" name="kdvTutar" autocomplete="off" class="form-control kdvTutar" value="20" style="text-align: center;" title="KDV oranını değiştirebilirsiniz">
          </div>
          <div class="col-md-4 rw2 col-6">
            <input type="text" onkeyup="sayiKontrol(this)" name="kdv" class="form-control kdv" value="0" title="KDV tutarını manuel değiştirebilirsiniz">
          </div>
        </div>

        <div class="row form-group" style="padding-bottom: 0">
          <div class="col-md-5 rw1"><label>Genel Toplam (KDV Dahil)<span style="font-weight: bold; color: red;"> *</span></label></div>
          <div class="col-md-7 rw2"><input type="text" onkeyup="sayiKontrol(this)" name="genelToplam" autocomplete="off" class="form-control genelToplam" required></div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="row">
    <div class="col-sm-12 gonderBtn">
      <input type="submit" class="btn btn-sm btn-info waves-effect waves-light" value="Kaydet">
    </div>
  </div>
</div>
</form>
</div>

<script type="text/javascript">
// Global değişkenler
window.selectedPayments = []; // Seçili ödemeleri tutacak array

// Global fonksiyonları window nesnesine ekle
window.sayiKontrol = function(v) {
  var isNum = /^[0-9-'.']*$/;
  if (!isNum.test(v.value)) { 
    v.value = v.value.replace(/[^0-9-',']/g, "");
  }                   
}

// Firma seçme fonksiyonunu global olarak tanımla
window.firmaSec = function(id, firmaAdi, tel1, tel2, il, ilce, adres, vergiNo, vergiDairesi) {
  $('#seciliFirmaId').val(id);
  $('#seciliFirmaAdi').text(firmaAdi);
  $('#seciliFirmaDetay').html(
      '<div>Telefon: ' + (tel1 || 'Belirtilmemiş') + (tel2 ? ' / ' + tel2 : '') + '</div>' +
      '<div>Konum: ' + (il || '') + '/' + (ilce || '') + '</div>' +
      '<div>Vergi No/Dairesi: ' + (vergiNo || 'Belirtilmemiş') + (vergiDairesi ? ' - ' + vergiDairesi : '') + '</div>' +
      '<div>Adres: ' + (adres || 'Adres belirtilmemiş') + '</div>'
  );
  
  // Hidden inputları form submit için doldur
  $('.vergiNo').val(vergiNo || '');
  $('.vergiDairesi').val(vergiDairesi || '');
  $('.tel1').val(tel1 || '');
  $('.tel2').val(tel2 || '');
  $('.il').val(il || '');
  $('.ilce').val(ilce || '');
  $('.adres').val(adres || '');
  
  $('#firmaArama').val('');
  $('#firmaListesi').hide();
  $('#seciliFirma').show();
  
  // Firma seçildiğinde ödemeleri yükle
  loadCompletedPayments(id);
}

window.firmaTemizle = function() {
  $('#seciliFirmaId').val('');
  $('#seciliFirma').hide();
  $('#odemeListesi').hide();
  $('#secilenOdemelerOzeti').hide();
  
  // Seçili ödemeleri temizle
  selectedPayments = [];
  
  // Form alanlarını temizle
  $('#multiplePaymentInputs').empty();
  $('#multipleDescriptionInputs').empty();
  $('#multipleQuantityInputs').empty();
  $('#multiplePriceInputs').empty();
  $('#multipleTotalInputs').empty();
  
  $('.toplam').val('');
  $('.araToplam').val('');
  $('.kdv').val('0');
  $('.genelToplam').val('');
  $('.toplamYazi').val('');
  
  // Hidden inputları da temizle
  $('.vergiNo').val('');
  $('.vergiDairesi').val('');
  $('.tel1').val('');
  $('.tel2').val('');
  $('.il').val('');
  $('.ilce').val('');
  $('.adres').val('');
}

// Çoklu ödeme seçimi fonksiyonu
window.selectPayment = function(paymentId, paymentType, amount, description) {
  var paymentKey = paymentType + '-' + paymentId;
  var paymentElement = $('#payment-' + paymentKey);
  
  // Eğer zaten seçili ise, seçimi kaldır
  if (paymentElement.hasClass('selected')) {
      paymentElement.removeClass('selected');
      // Array'den kaldır
      selectedPayments = selectedPayments.filter(function(payment) {
          return payment.key !== paymentKey;
      });
  } else {
      // Seçili değilse, ekle
      paymentElement.addClass('selected');
      selectedPayments.push({
          key: paymentKey,
          id: paymentId,
          type: paymentType,
          amount: parseFloat(amount),
          description: description
      });
  }
  
  // Özeti güncelle
  updateSelectedPaymentsSummary();
  
  // Form verilerini güncelle
  updateFormFromSelectedPayments();
}

// Seçili ödemeler özetini güncelle
function updateSelectedPaymentsSummary() {
  var summaryDiv = $('#secilenOzemeler');
  var totalAmount = 0;
  
  if (selectedPayments.length === 0) {
      $('#secilenOdemelerOzeti').hide();
      return;
  }
  
  var html = '';
  selectedPayments.forEach(function(payment, index) {
      totalAmount += payment.amount;
      html += '<div style="display: flex; justify-content: space-between; align-items: center; padding: 4px 0; border-bottom: 1px solid #dee2e6;">';
      html += '<div style="flex: 1;">' + (index + 1) + '. ' + payment.description + '</div>';
      html += '<div style="font-weight: 600;">' + payment.amount + ' TL</div>';
      html += '<div style="margin-left: 10px; cursor: pointer; color: #dc3545; font-size: 20px; padding: 0 5px;" onclick="removePayment(\'' + payment.key + '\')" title="Kaldır">×</div>';
      html += '</div>';
  });
  
  summaryDiv.html(html);
  $('#toplamSecilenTutar').text(totalAmount.toFixed(2));
  $('#secilenOdemelerOzeti').show();
}

// Ödeme seçimini kaldır
window.removePayment = function(paymentKey) {
  $('#payment-' + paymentKey).removeClass('selected');
  selectedPayments = selectedPayments.filter(function(payment) {
      return payment.key !== paymentKey;
  });
  updateSelectedPaymentsSummary();
  updateFormFromSelectedPayments();
}

// Form verilerini seçili ödemelerden güncelle
function updateFormFromSelectedPayments() {
  // Önceki inputları temizle
  $('#multiplePaymentInputs').empty();
  $('#multipleDescriptionInputs').empty();
  $('#multipleQuantityInputs').empty();
  $('#multiplePriceInputs').empty();
  $('#multipleTotalInputs').empty();
  
  var totalAmount = 0;
  var combinedDescription = [];
  
  selectedPayments.forEach(function(payment, index) {
      // Payment inputs
      $('#multiplePaymentInputs').append(
          '<input type="hidden" name="payment_type[]" value="' + payment.type + '">' +
          '<input type="hidden" name="payment_id[]" value="' + payment.id + '">'
      );
      
      // KDV dahil tutarı KDV hariç tutara çevir
      var kdvOrani = parseFloat($('.kdvTutar').val()) || 20;
      var kdvDahilTutar = payment.amount;
      var kdvOraniFaktor = (100 + kdvOrani) / 100;
      var kdvHaricTutar = kdvDahilTutar / kdvOraniFaktor;
      
      totalAmount += kdvHaricTutar;
      combinedDescription.push((index + 1) + '. ' + payment.description);
      
      // Ürün bilgileri
      $('#multipleDescriptionInputs').append('<input type="hidden" name="aciklama[]" value="' + payment.description + '">');
      $('#multipleQuantityInputs').append('<input type="hidden" name="miktar[]" value="1">');
      $('#multiplePriceInputs').append('<input type="hidden" name="fiyat[]" value="' + kdvHaricTutar.toFixed(2) + '">');
      $('#multipleTotalInputs').append('<input type="hidden" name="tutar[]" value="' + kdvHaricTutar.toFixed(2) + '">');
  });
  
  // Toplam tutarları güncelle
  if (selectedPayments.length > 0) {
      $('.toplam').val(totalAmount.toFixed(2));
      kdvHesapla(totalAmount);
      
      // Toplam yazısını placeholder olarak ayarla
      $('.toplamYazi').val('').attr('placeholder', selectedPayments.length + ' adet ödeme toplamı');
  } else {
      // Hiç seçili ödeme yoksa formu temizle
      $('.toplam').val('');
      $('.araToplam').val('');
      $('.kdv').val('0');
      $('.genelToplam').val('');
      $('.toplamYazi').val('').attr('placeholder', '');
  }
}
// Tamamlanmış ödemeleri yükle
function loadCompletedPayments(tenantId) {
  $('#odemeYukleniyor').show();
  $('#odemeListesi').hide();
  $('#secilenOdemelerOzeti').hide();
  selectedPayments = []; // Önceki seçimleri temizle
  
  $.ajax({
    url: '{{ route("super.admin.invoices.payments") }}',
    type: 'GET',
    data: { tenant_id: tenantId },
    success: function(payments) {
      $('#odemeYukleniyor').hide();
      
      if (payments.length === 0) {
        $('#odemeSecenekleri').html('<div class="alert alert-warning" style="padding: 8px; font-size: 12px;">Bu firmaya ait fatura oluşturulmamış tamamlanmış ödeme bulunamadı.</div>');
      } else {
        var html = '';
        payments.forEach(function(payment) {
          var paymentDate = new Date(payment.paid_at).toLocaleDateString('tr-TR');
          var paymentTime = new Date(payment.paid_at).toLocaleTimeString('tr-TR', {hour: '2-digit', minute: '2-digit'});
          
          html += '<div class="payment-item" id="payment-' + payment.type + '-' + payment.id + '" onclick="selectPayment(' + payment.id + ', \'' + payment.type + '\', ' + payment.amount + ', \'' + payment.description.replace(/'/g, "\\'") + '\')">';
          html += '  <div class="payment-info">';
          html += '    <div class="payment-details">';
          html += '      <div class="payment-description">' + payment.description + '</div>';
          html += '      <div class="payment-date">' + paymentDate + ' ' + paymentTime + ' - ' + payment.payment_method + '</div>';
          html += '    </div>';
          html += '    <div class="payment-amount">' + payment.amount + ' ' + payment.currency + '</div>';
          html += '  </div>';
          html += '</div>';
        });
        $('#odemeSecenekleri').html(html);
      }
      
      $('#odemeListesi').show();
    },
    error: function() {
      $('#odemeYukleniyor').hide();
      $('#odemeSecenekleri').html('<div class="alert alert-danger" style="padding: 8px; font-size: 12px;">Ödemeler yüklenirken hata oluştu.</div>');
      $('#odemeListesi').show();
    }
  });
}

// KDV hesaplama fonksiyonu
function kdvHesapla(toplam) {
  var indirim = Number($(".indirim").val()) || 0;
  var kdvTutar = Number($(".kdvTutar").val()) || 0;
  var kdv = ((toplam - indirim) * kdvTutar) / 100;
  var araToplam = toplam - indirim;
  var genelToplam = araToplam + kdv;

  $(".toplam").val(toplam.toFixed(2));
  $(".araToplam").val(araToplam.toFixed(2));
  $(".genelToplam").val(genelToplam.toFixed(2));
  $(".kdv").val(kdv.toFixed(2));
}

$('.buyukYaz').keyup(function(){
  this.value = this.value.toUpperCase();
});
</script>

<script>
$(document).ready(function (e) {
  // KDV oranı değiştirildiğinde yeniden hesapla
  $('.kdvTutar').on('keyup change', function() {
    var toplam = Number($(".toplam").val()) || 0;
    if (toplam > 0) {
      kdvHesapla(toplam);
    }
  });

  // İndirim değiştirildiğinde yeniden hesapla
  $('.indirim').on('keyup change', function() {
    var toplam = Number($(".toplam").val()) || 0;
    if (toplam > 0) {
      kdvHesapla(toplam);
    }
  });

  // Toplam manuel değiştirildiğinde KDV'yi yeniden hesapla
  $('.toplam').on('keyup change', function() {
    var toplam = Number($(this).val()) || 0;
    kdvHesapla(toplam);
  });

  // Ara toplam manuel değiştirildiğinde genel toplamı hesapla
  $('.araToplam').on('keyup change', function() {
    var araToplam = Number($(this).val()) || 0;
    var kdvTutar = Number($(".kdvTutar").val()) || 0;
    var kdv = (araToplam * kdvTutar) / 100;
    var genelToplam = araToplam + kdv;
    
    $(".kdv").val(kdv.toFixed(2));
    $(".genelToplam").val(genelToplam.toFixed(2));
    $(".toplam").val(araToplam.toFixed(2));
  });

  // KDV tutarı manuel değiştirildiğinde genel toplamı hesapla
  $('.kdv').on('keyup change', function() {
    var kdv = Number($(this).val()) || 0;
    var araToplam = Number($(".araToplam").val()) || 0;
    var genelToplam = araToplam + kdv;
    
    $(".genelToplam").val(genelToplam.toFixed(2));
  });

  // Genel toplam manuel değiştirildiğinde KDV'yi hesapla
  $('.genelToplam').on('keyup change', function() {
    var genelToplam = Number($(this).val()) || 0;
    var araToplam = Number($(".araToplam").val()) || 0;
    var kdv = genelToplam - araToplam;
    
    $(".kdv").val(kdv.toFixed(2));
    
    // KDV oranını da güncelle
    if (araToplam > 0) {
      var kdvOrani = (kdv / araToplam) * 100;
      $(".kdvTutar").val(kdvOrani.toFixed(0));
    }
  });

  // Firma seçildiğinde bilgileri doldur
  let firmaAramaTimeout;

  $('#firmaArama').on('input', function() {
      const aramaMetni = $(this).val().trim();
      
      clearTimeout(firmaAramaTimeout);
      
      if (aramaMetni.length < 2) {
          $('#firmaListesi').hide();
          return;
      }

      firmaAramaTimeout = setTimeout(function() {
          firmaAra(aramaMetni);
      }, 300);
  });

  function firmaAra(aramaMetni) {
      $.ajax({
          url: '{{ route("super.admin.firma.ara") }}',
          type: 'POST',
          data: {
              arama: aramaMetni,
              _token: '{{ csrf_token() }}'
          },
          success: function(response) {
              // Burada filtreleme ekleyin:
              var filteredFirmalar = response.filter(function(firma) {
                  return firma.firma_adi !== 'Super Admin Panel';
              });
              firmaListesiGoster(filteredFirmalar);
          },
          error: function() {
              console.log('Arama hatası');
          }
      });
  }
function firmaListesiGoster(firmalar, bekleyenOdemeMode = false) {
  const liste = $('#firmaListesi');
  liste.empty();

  if (firmalar.length === 0) {
      liste.append('<div class="no-results-message">Firma bulunamadı</div>');
      liste.show();
      return;
  }

  // Sadece bekleyen ödeme modunda arama kutusu ekle
  if (bekleyenOdemeMode) {
      var searchBox = '<div class="firma-search-container">' +
                      '<input type="text" class="firma-search-input" id="firmaListesiArama" placeholder="Liste içinde ara..." autocomplete="off">' +
                      '<div style="font-size: 11px; color: #6c757d; margin-top: 4px;">' + firmalar.length + ' firma bulundu</div>' +
                      '</div>';
      liste.append(searchBox);
  }

  // Firma listesini container'a ekle
  var firmaContainer = $('<div id="firmaListesiContainer"></div>');
  
  firmalar.forEach(function(firma) {
      var firmaAdi = firma.firma_adi ? firma.firma_adi.replace(/'/g, "\\'") : '';
      var tel1 = firma.tel1 ? firma.tel1.replace(/'/g, "\\'") : '';
      var tel2 = firma.tel2 ? firma.tel2.replace(/'/g, "\\'") : '';
      var il = firma.il ? firma.il.replace(/'/g, "\\'") : '';
      var ilce = firma.ilce ? firma.ilce.replace(/'/g, "\\'") : '';
      var adres = firma.adres ? firma.adres.replace(/'/g, "\\'") : '';
      var vergiNo = firma.vergiNo ? firma.vergiNo.replace(/'/g, "\\'") : '';
      var vergiDairesi = firma.vergiDairesi ? firma.vergiDairesi.replace(/'/g, "\\'") : '';

      // Bekleyen ödeme badge'i
      var pendingBadge = '';
      if (firma.pending_payments_count) {
          pendingBadge = '<span style="background: #dc3545; color: white; padding: 2px 6px; border-radius: 10px; font-size: 10px; margin-left: 5px;">' + 
                        firma.pending_payments_count + ' bekleyen</span>';
      }

      var item = '<li class="list-group-item firma-list-item" ' +
          'data-firma-name="' + firma.firma_adi.toLowerCase() + '" ' +
          'data-firma-tel="' + (firma.tel1 || '').toLowerCase() + '" ' +
          'data-firma-il="' + (firma.il || '').toLowerCase() + '" ' +
          'style="cursor: pointer; border: none; padding: 8px; margin-bottom: 2px; background: #f8f9fa; border-radius: 4px;" ' +
          'onclick="firmaSec(' + 
          firma.id + ', \'' + firmaAdi + '\', \'' + tel1 + '\', \'' + tel2 + 
          '\', \'' + il + '\', \'' + ilce + '\', \'' + adres + '\', \'' + 
          vergiNo + '\', \'' + vergiDairesi + '\')">' +
          '<div style="font-weight: 600; color: #495057; margin-bottom: 2px; font-size: 13px;">' + 
          firma.firma_adi + pendingBadge + '</div>' +
          '<div style="font-size: 11px; color: #6c757d; line-height: 1.3;">' +
          '<div>' + (firma.tel1 || 'Belirtilmemiş') + '</div>' +
          '<div>' + (firma.il || '') + '/' + (firma.ilce || '') + '</div>' +
          '</div>' +
          '</li>';
      
      firmaContainer.append(item);
  });

  liste.append(firmaContainer);
  liste.show();

  // Liste içi arama fonksiyonu (sadece bekleyen ödeme modunda)
  if (bekleyenOdemeMode) {
      $('#firmaListesiArama').on('input', function() {
          var searchTerm = $(this).val().toLowerCase().trim();
          var visibleCount = 0;

          if (searchTerm === '') {
              $('.firma-list-item').show();
              visibleCount = firmalar.length;
          } else {
              $('.firma-list-item').each(function() {
                  var firmaName = $(this).data('firma-name') || '';
                  var firmaTel = $(this).data('firma-tel') || '';
                  var firmaIl = $(this).data('firma-il') || '';
                  
                  if (firmaName.includes(searchTerm) || 
                      firmaTel.includes(searchTerm) || 
                      firmaIl.includes(searchTerm)) {
                      $(this).show();
                      visibleCount++;
                  } else {
                      $(this).hide();
                  }
              });
          }

          // Sonuç sayısını güncelle
          if (visibleCount === 0) {
              if ($('#firmaListesiContainer .no-results-message').length === 0) {
                  $('#firmaListesiContainer').append('<div class="no-results-message">Arama sonucu bulunamadı</div>');
              }
          } else {
              $('#firmaListesiContainer .no-results-message').remove();
          }
          
          $('.firma-search-container div').text(visibleCount + ' firma gösteriliyor');
      });
  }
}

// Firma arama fonksiyonunu güncelle
function firmaAra(aramaMetni) {
  $.ajax({
      url: '{{ route("super.admin.firma.ara") }}',
      type: 'POST',
      data: {
          arama: aramaMetni,
          _token: '{{ csrf_token() }}'
      },
      success: function(response) {
          var filteredFirmalar = response.filter(function(firma) {
              return firma.firma_adi !== 'Super Admin Panel';
          });
          firmaListesiGoster(filteredFirmalar, false); // Normal modda arama kutusu yok
      },
      error: function() {
          console.log('Arama hatası');
      }
  });
}

// Bekleyen ödemeli firmaları listele butonu
$('#bekleyenOdemelerBtn').on('click', function() {
  $(this).prop('disabled', true);
  $(this).html('<i class="fas fa-spinner fa-spin"></i>');
  
  $.ajax({
      url: '{{ route("super.admin.invoices.tenants.pending") }}',
      type: 'GET',
      success: function(firmalar) {
          $('#bekleyenOdemelerBtn').prop('disabled', false);
          $('#bekleyenOdemelerBtn').html('<i class="fas fa-file-invoice"></i>');
          
          if (firmalar.length === 0) {
              const liste = $('#firmaListesi');
              liste.empty();
              liste.html('<div class="no-results-message" style="padding: 15px; background: #fff3cd; color: #856404; border-radius: 4px;">Faturası oluşturulmamış ödemesi olan firma bulunamadı</div>');
              liste.show();
          } else {
              firmaListesiGoster(firmalar, true); // Bekleyen ödeme modunda arama kutusu var
          }
      },
      error: function() {
          $('#bekleyenOdemelerBtn').prop('disabled', false);
          $('#bekleyenOdemelerBtn').html('<i class="fas fa-file-invoice"></i>');
          alert('Firmalar yüklenirken hata oluştu');
      }
  });
});


// Dışarı tıklayınca listeyi kapat
$(document).click(function(e) {
  if (!$(e.target).closest('#firmaArama, #firmaListesi, #bekleyenOdemelerBtn').length) {
      $('#firmaListesi').hide();
  }
});
  // Form validasyonu
  $('#addInvo').submit(function (event) {
    let formIsValid = true;
    
    // Özel validasyon: En az bir ödeme seçilmiş mi?
    if (selectedPayments.length === 0) {
      alert('Lütfen en az bir ödeme seçin.');
      event.preventDefault();
      return false;
    }
    
    $(this).find('input, select, textarea').each(function () {
      if ($(this).prop('required') && !$(this).val()) {
        formIsValid = false;
        $(this).css('border-color', 'red');
      } else {
        $(this).css('border-color', '');
      }
    });

    if (!formIsValid) {
      event.preventDefault();
      alert('Lütfen zorunlu alanları doldurun.');
    }
  });
});
</script>
<script>
$(document).ready(function() {
    let invoiceFormSubmitting = false;
    
    // Benzersiz token oluştur
    function generateToken() {
        return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    // Sayfa yüklendiğinde ilk token'ı oluştur
    $('#formTokenInvoice').val(generateToken());
    
    // Mevcut form submit event'ini override et
    var originalSubmitHandler = $('#addInvo').data('events')?.submit;
    
    $('#addInvo').off('submit').on('submit', function(event) {
        // Token kontrolü
        if (invoiceFormSubmitting) {
            event.preventDefault();
            alert('Form gönderiliyor, lütfen bekleyin...');
            return false;
        }
        
        let formIsValid = true;
        
        // Özel validasyon: En az bir ödeme seçilmiş mi?
        if (selectedPayments.length === 0) {
            alert('Lütfen en az bir ödeme seçin.');
            event.preventDefault();
            return false;
        }
        
        $(this).find('input, select, textarea').each(function () {
            if ($(this).prop('required') && !$(this).val()) {
                formIsValid = false;
                $(this).css('border-color', 'red');
            } else {
                $(this).css('border-color', '');
            }
        });

        if (!formIsValid) {
            event.preventDefault();
            alert('Lütfen zorunlu alanları doldurun.');
            return false;
        }
        
        // Token işaretle ve butonu disable et
        invoiceFormSubmitting = true;
        $(this).find('input[type="submit"]').prop('disabled', true);
        

        return true;
    });
});
</script>