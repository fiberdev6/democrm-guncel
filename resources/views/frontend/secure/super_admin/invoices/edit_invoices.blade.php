
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="edit_invoices_superadmin">
<form method="post" id="editInvo" action="{{ route('super.admin.invoices.update')}}" enctype="multipart/form-data" class="needs-validation" novalidate>
  @csrf

  <div class="card card-invocies f5">
    <div class="card-header ch1" style="padding: 3px 10px;">
      <div class="tarihWrap">
        <label style="text-align: left;width: auto;display: inline-block;margin: 0;">Tarih<span style="font-weight: bold; color: red;">*</span></label>
        <input type="date" name="faturaTarihi" class="form-control datepicker kayitTarihi" value="{{ \Carbon\Carbon::parse($invoice_id->faturaTarihi)->format('Y-m-d')}}" style="width: 150px; display: inline-block; background:#fff" required>
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
            <div class="col-md-3 rw1"><label>Firma Ara <span style="font-weight: bold; color: red;">*</span></label></div>
            <div class="col-md-9 rw2">
              <input type="text" id="firmaArama" class="form-control" placeholder="Firma adı yazın..." autocomplete="off">
              <ul id="firmaListesi" class="list-group" style="position: absolute; z-index: 1000; width: 92%; display: none;"></ul>
              <input type="hidden" name="firma_id" id="seciliFirmaId" value="{{ $invoice_id->firma_id }}" required>
              <div id="seciliFirma" style="background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; border-radius: 4px; margin-top: 5px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                  <div style="flex: 1;">
                    <div style="font-weight: 600; color: #495057; font-size: 14px;  margin-bottom: 8px;" id="seciliFirmaAdi">
                      {{ $invoice_id->tenant->firma_adi }}
                    </div>
                    <div style="font-size: 12px; color: #6c757d; line-height: 1.5;" id="seciliFirmaDetay">
                      @if(!empty($invoice_id->tenant?->tel1))
                          <div >Telefon: {{ $invoice_id->tenant->tel1 }}</div>
                      @endif
                      <div >
                           Konum: {{ $invoice_id->tenant->ilces?->ilceName ?? 'Bilinmiyor' }}/{{ $invoice_id->tenant->ils?->name ?? 'Bilinmiyor' }}
                      </div>
                      @if(!empty($invoice_id->tenant?->vergiNo) || !empty($invoice_id->tenant?->vergiDairesi))
                          <div > Vergi No/Dairesi: {{ $invoice_id->tenant->vergiNo }} {{ $invoice_id->tenant->vergiDairesi ? ' - ' . $invoice_id->tenant->vergiDairesi : '' }}</div>
                      @endif
                      <div>Adres: {{ $invoice_id->tenant->adres }}</div>
                    </div>
                  </div>
                  <span style="cursor: pointer; color: #dc3545; font-size: 16px; font-weight: bold;" onclick="firmaTemizle()" title="Firmayı Temizle">&times;</span>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Hidden inputs for form submission -->
          <input type="hidden" name="vergiNo" value="{{ $invoice_id->tenant->vergiNo }}" class="vergiNo">
          <input type="hidden" name="vergiDairesi" value="{{ $invoice_id->tenant->vergiDairesi }}" class="vergiDairesi">
          <input type="hidden" name="tel1" value="{{ $invoice_id->tenant->tel1 }}" class="tel1">
          <input type="hidden" name="tel2" value="" class="tel2">
          <input type="hidden" name="il" value="{{ $invoice_id->tenant->ils?->name }}" class="il">
          <input type="hidden" name="ilce" value="{{ $invoice_id->tenant->ilces?->ilceName }}" class="ilce">
          <textarea name="adres" class="adres" style="display: none;">{{ $invoice_id->tenant->adres }}</textarea>
        </div>
      </div>
    </div>

    <!-- ÖDEME SEÇİMİ -->
    <div class="col-lg-6">
      <div class="card card-invocies f6">
        <div class="card-header card-invocies-header">ÖDEME SEÇİMİ</div>
        <div class="card-body card-invocies-body">
      <div class="alert alert-info" style="padding: 6px; font-size: 11px;">
        <strong>Önemli:</strong> Önce e-Arşiv belgesini silin, ardından mevcut ödemelerle işlem yapabilirsiniz.
      </div>
          <div id="odemeYukleniyor" style="display: none; text-align: center; padding: 15px;">
            <div class="loading-spinner"></div>
            <span style="margin-left: 10px; font-size: 13px;">Ödemeler yükleniyor...</span>
          </div>
          
          <div id="odemeListesi" style="display: none;">
            <h6 style="font-size: 14px; margin-bottom: 10px;">Fatura Oluşturulacak Ödemeler</h6>
            <div id="odemeSecenekleri" class="payment-selection"></div>
          </div>
          
          <div id="secilenOdemelerOzeti" style="display: none;">
              <h6 style="font-size: 14px; margin-bottom: 10px;">Seçilen Ödemeler:</h6>
              <div id="secilenOzemeler" class="selected-payments-summary"></div>
              {{-- <div style="text-align: right; margin-top: 8px;">
                  <strong>Toplam: <span id="toplamSecilenTutar">0</span> TL</strong>
              </div> --}}
          </div>

          
<!-- Mevcut ödeme bilgilerini göster -->
@if($invoice_id->payment_details)
  <div style="padding: 10px; border-radius: 4px;">
    <strong>Mevcut Bağlı Ödemeler:</strong>
    <div style="font-size: 12px; margin-top: 5px;">
      @php
        $existingPayments = json_decode($invoice_id->payment_details, true) ?? [];
        $kdvRate = $invoice_id->kdvTutar ?? 0; // Get KDV rate from the invoice
        $kdvFactor = (100 + $kdvRate) / 100;
      @endphp
      @foreach($existingPayments as $payment)
        @php
          // Assuming payment['amount'] is the KDV-exclusive amount
          $kdvInclusiveAmount = ($payment['amount'] ?? 0) * $kdvFactor;
        @endphp
        <div>• {{ $payment['description'] ?? 'Ödeme' }} - {{ number_format($kdvInclusiveAmount, 2) }} TL</div>
      @endforeach
    </div>
  </div>
@endif
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
    <div class="card col-lg-6 f3">
      <div class="card-body">
        <div class="row" style="border:0">
          <div class="col-md-4 rw1"><label>Ödeme Şekli<span style="font-weight: bold; color: red;"> *</span></label></div>
            <div class="col-md-8 rw2">
              <select class="form-select odemeSekilleri" name="odemeSekli" required>
                <option value="">Seçiniz</option>
                @foreach($payment_methods as $method)
                  <option value="{{$method->id}}" {{$method->id == $invoice_id->odemeSekli ? 'selected' : ''}}>{{$method->odemeSekli}}</option>
                @endforeach
              </select>
            </div>
        </div>

        <div class="row" style="border:0">
          <div class="col-md-4 rw1"><label>Fatura Durumu<span style="font-weight: bold; color: red;"> *</span></label></div>
            <div class="col-md-8 rw2">
              <select class="form-select faturaDurumu" name="faturaDurumu" required>
                <option value="">Seçiniz</option>
                  <option value="draft" {{$invoice_id->faturaDurumu == 'draft' ? 'selected' : ''}}>Beklemede</option>
                  <option value="sent" {{$invoice_id->faturaDurumu == 'sent' ? 'selected' : ''}}>Gönderildi</option>
                  <option value="error" {{$invoice_id->faturaDurumu == 'error' ? 'selected' : ''}}>Gönderilmedi</option>
              </select>
            </div>
        </div>

          <div class="row form-group" style="border:0">
            <div class="col-md-4 rw1"><label>Toplam Yazıyla</label></div>
            <div class="col-md-8 rw2"><input type="text" name="toplamYazi" autocomplete="off" value="{{$invoice_id->toplamYazi}}" class="form-control buyukYaz toplamYazi"></div>
          </div>

          <div class="row form-group" style="border:0">
            <div class="col-md-4 rw1"><label>Fatura No<span style="font-weight: bold; color: red;"> *</span></label></div>
            <div class="col-md-8 rw2">
              <input type="text" name="faturaNumarasi" class="form-control buyukYaz faturaNumarasi" value="{{$invoice_id->faturaNumarasi}}" required>
            </div>
          </div>

<div class="row form-group" style="border:0">
  <div class="col-md-4 rw1"><label>E-Arşiv<span style="font-weight: bold; color: red;"> *</span></label></div>
  <div class="col-md-8 rw2">
    <div class="btnWrap" style="display: flex; align-items: center; gap: 5px;">
      <!-- Yükleme butonu -->
      <div style="display: inline-block;">
        <input type="file" id="pdfFileInput" name="pdf" accept=".pdf,.jpg,.jpeg,.png" style="display: none;" data-invoice-id="{{$invoice_id->id}}">
        
        @if($invoice_id->faturaPdf != null)
          <!-- Dosya varsa dosya adını göster -->
          @php
            $fileName = basename($invoice_id->faturaPdf);
            $displayName = strlen($fileName) > 20 ? substr($fileName, 0, 17) . '...' : $fileName;
          @endphp
          <a href="{{asset($invoice_id->faturaPdf)}}" target="_blank" 
             style="display: inline-flex; align-items: center; padding: 6px 12px; border: 1px solid #dee2e6; border-radius: 4px; color: #495057; text-decoration: none; font-size: 12px; background: #f8f9fa; height: 32px;"
             id="uploadedFileBtn"
             title="{{$fileName}} - Görüntülemek için tıklayın">
            <i class="fas fa-file-pdf" style="color: #dc3545;"></i> {{$displayName}}
          </a>
          
          <!-- Sil butonu sadece dosya varsa görünsün -->
          <a href="" class="btn btn-outline-danger btn-sm mobilBtn mbuton1 eArsivSil" data-id="{{$invoice_id->id}}" title="Sil" style="height: 32px; display: inline-flex; align-items: center; padding: 6px 12px;">
            <i class="fas fa-trash-alt" style=" height:7px; "></i>
          </a>
        @else
          <!-- Dosya yoksa "Dosya Seç" butonu -->
          <button type="button" 
                  style="display: inline-flex; align-items: center; padding: 6px 12px; border: 1px solid #dee2e6; border-radius: 4px; color: #495057; background: #fff; font-size: 12px; cursor: pointer; height: 32px;"
                  id="uploadPdfBtn"
                  onclick="handleUploadClick()" 
                  title="Dosya Seç">
            <i class="fas fa-folder-open"></i> Dosya Seç
          </button>
        @endif
      </div>
    </div>
  </div>
</div>
        </div>
      </div>

      <div class="card col-lg-6 f4">
        <div class="card-body" style="padding:17px 5px">
          <div class="row form-group">
            <div class="col-md-8 rw1"><label>Toplam (KDV Hariç)<span style="font-weight: bold; color: red;"> *</span></label></div>
            <div class="col-md-4 rw2"><input type="text" onkeyup="sayiKontrol(this)" name="toplam" value="{{$invoice_id->toplam}}" autocomplete="off" class="form-control toplam" required></div>
          </div>

          <div class="row form-group">
          <div class="col-md-8 rw1"><label>İndirim</label></div>
          <div class="col-md-4 rw2"><input type="text" onkeyup="sayiKontrol(this)" name="indirim" value="{{$invoice_id->indirim}}" autocomplete="off" class="form-control indirim"></div>
        </div>
        <div class="row form-group">
          <div class="col-md-8 rw1"><label>Ara Toplam</label></div>
          <div class="col-md-4 rw2"><input type="text" onkeyup="sayiKontrol(this)" name="araToplam" value="{{$invoice_id->toplam-$invoice_id->indirim}}" autocomplete="off" class="form-control araToplam"></div>
        </div>

          <div class="row form-group">
            <div class="col-md-6 rw1"><label>KDV %</label></div>
            <div class="col-md-2 rw2 col-6"><input type="text" onkeyup="sayiKontrol(this)" name="kdvTutar" autocomplete="off" class="form-control kdvTutar" value="{{$invoice_id->kdvTutar}}"></div>
            <div class="col-md-4 col-6 rw2"><input type="text" onkeyup="sayiKontrol(this)" name="kdv" class="form-control kdv" value="{{$invoice_id->kdv}}"></div>
          </div>

          <div class="row form-group" style="padding-bottom: 0">  
            <div class="col-md-8 rw1"><label>Genel Toplam (KDV Dahil)<span style="font-weight: bold; color: red;"> *</span></label></div>
            <div class="col-md-4 rw2"><input type="text" onkeyup="sayiKontrol(this)" name="genelToplam" value="{{$invoice_id->genelToplam}}" autocomplete="off" class="form-control genelToplam" required></div>
          </div>
               
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-12 gonderBtn">
        <input type="hidden" name="id" value="{{ $invoice_id->id }}">
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
        '<div> ' + (tel1 || 'Belirtilmemiş') + (tel2 ? ' / ' + tel2 : '') + '</div>' +
        '<div> ' + (il || '') + '/' + (ilce || '') + '</div>' +
        '<div> ' + (vergiNo || 'Belirtilmemiş') + (vergiDairesi ? ' - ' + vergiDairesi : '') + '</div>' +
        '<div> ' + (adres || 'Adres belirtilmemiş') + '</div>'
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
        html += '<div style="margin-left: 10px; cursor: pointer; color: #dc3545; font-size: 20px; padding: 0 5px;" onclick="removePayment(\'' + payment.key + '\')" title="Kaldır">×</div>';;
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
        
        // Ürün bilgileri
        $('#multipleDescriptionInputs').append('<input type="hidden" name="aciklama[]" value="' + payment.description + '">');
        $('#multipleQuantityInputs').append('<input type="hidden" name="miktar[]" value="1">');
        $('#multiplePriceInputs').append('<input type="hidden" name="fiyat[]" value="' + kdvHaricTutar.toFixed(2) + '">');
        $('#multipleTotalInputs').append('<input type="hidden" name="tutar[]" value="' + kdvHaricTutar.toFixed(2) + '">');
    });
    
    // Toplam tutarları güncelle (sadece yeni ödemeler seçildiyse)
    if (selectedPayments.length > 0) {
        $('.toplam').val(totalAmount.toFixed(2));
        kdvHesapla(totalAmount);
        $('.toplamYazi').val(selectedPayments.length + ' adet ödeme toplamı');
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

  // Sayfa yüklendiğinde mevcut firma için ödemeleri yükle
  $(document).ready(function() {
    var firmaId = $('#seciliFirmaId').val();
    if (firmaId) {
      loadCompletedPayments(firmaId);
    }
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

   function firmaListesiGoster(firmalar) {
    const liste = $('#firmaListesi');
    liste.empty();

    if (firmalar.length === 0) {
        liste.append('<div class="no-results-message">Firma bulunamadı</div>');
        liste.show();
        return;
    }

    firmalar.forEach(function(firma) {
        var firmaAdi = firma.firma_adi ? firma.firma_adi.replace(/'/g, "\\'") : '';
        var tel1 = firma.tel1 ? firma.tel1.replace(/'/g, "\\'") : '';
        var tel2 = firma.tel2 ? firma.tel2.replace(/'/g, "\\'") : '';
        var il = firma.il ? firma.il.replace(/'/g, "\\'") : '';
        var ilce = firma.ilce ? firma.ilce.replace(/'/g, "\\'") : '';
        var adres = firma.adres ? firma.adres.replace(/'/g, "\\'") : '';
        var vergiNo = firma.vergiNo ? firma.vergiNo.replace(/'/g, "\\'") : '';
        var vergiDairesi = firma.vergiDairesi ? firma.vergiDairesi.replace(/'/g, "\\'") : '';

        var item = '<li class="list-group-item firma-list-item" onclick="firmaSec(' + 
            firma.id + ', \'' + firmaAdi + '\', \'' + tel1 + '\', \'' + tel2 + 
            '\', \'' + il + '\', \'' + ilce + '\', \'' + adres + '\', \'' + 
            vergiNo + '\', \'' + vergiDairesi + '\')">' +
            '<div style="font-weight: 600; margin-bottom: 2px; font-size: 13px;">' + firma.firma_adi + '</div>' +
            '<div style="font-size: 11px; line-height: 1.3;">' +
            '<div>' + (firma.tel1 || 'Belirtilmemiş') + '</div>' +
            '<div>' + (firma.il || '') + '/' + (firma.ilce || '') + '</div>' +
            '</div>' +
            '</li>';
        liste.append(item);
    });
    
    liste.show();
}

    // Dışarı tıklayınca listeyi kapat
    $(document).click(function(e) {
        if (!$(e.target).closest('#firmaArama, #firmaListesi').length) {
            $('#firmaListesi').hide();
        }
    });
  });
</script>

<script type="text/javascript">
$(document).ready(function(){
    $('#editInvo').on('click', '.invoic_e', function(e){
        var id = $(this).attr("data-bs-id");
        $.ajax({
            url: "{{ route('super.admin.invoices.show', '') }}/" + id
        }).done(function(data) {
            console.log(data);
            if ($.trim(data) === "-1") {
                window.location.reload(true);
            } else {
                $('#InvoiceModal').modal('show');
                $('#InvoiceModal .modal-body').html(data);
            }
        });
    });
});
</script>



<script>
  // E-arşiv silme butonuna tıklandığında
$('#editInvo').on('click', '.eArsivSil', function(e) {
    e.preventDefault();
    var confirmDelete = confirm("Bu e-faturayı silmek istediğinizden emin misiniz?");
    if (confirmDelete) {
        var id = $(this).attr('data-id');
        var button = $(this);
        
        $.ajax({
            url: '{{ route("super.admin.invoices.delete.einvoice", "") }}/' + id,
            type: 'POST',
            data: {
                _method: 'POST', 
                _token: '{{ csrf_token() }}'
            },
            success: function(data) {
                if (data && data.status === 'success') {
                    alert(data.message || "E-Arşiv belgesi başarıyla silindi.");
                    
                    // Görüntüle butonunu gizle
                    $('#viewPdfBtn').addClass('d-none').attr('href', '#');
                    
                    // Yüklü dosya butonunu "Dosya Seç" butonuna geri çevir
                    var uploadContainer = $('#uploadedFileBtn').parent();
                    $('#uploadedFileBtn').replaceWith(
                        '<button type="button" ' +
                        'style="display: inline-flex; align-items: center; padding: 6px 12px; border: 1px solid #dee2e6; border-radius: 4px; color: #495057; background: #fff; font-size: 12px; cursor: pointer; height: 32px;" ' +
                        'id="uploadPdfBtn" ' +
                        'onclick="handleUploadClick()" ' +
                        'title="Dosya Seç">' +
                        '<i class="fas fa-folder-open"></i> Dosya Seç' +
                        '</button>'
                    );

                    // Silme butonunu gizle
                    $('.eArsivSil').hide();
                    
                    // DataTable'ı yenile
                    if (typeof $('#datatableInvoice').DataTable === 'function') {
                        $('#datatableInvoice').DataTable().ajax.reload();
                    }
                    
                    // Modal içeriğini yenile
                    refreshModalContent(id);
                }
            },
            error: function(xhr, status, error) {
                alert("İşlem sırasında hata oluştu: " + error);
            }
        });
    }
});

// Modal yenileme fonksiyonu ekle
function refreshModalContent(invoiceId) {
    $.ajax({
        url: '{{ route("super.admin.invoices.edit", "") }}/' + invoiceId,
        type: 'GET',
        success: function(data) {
            $('#editInvoiceModal .modal-body').html(data);
        }
    });
}

</script>

<script>
function formatToTwoDecimalPlaces(value) {
    return Number(value).toFixed(2);
}
  
$(document).ready(function (e) {
    var sonucToplam = 0;
    var sonuc = 0;
    
    setTimeout(function (){
      $('.miktar').each(function(index, data) {
        var fiyat = Number($(".fiyat"+index).val());
        var miktar = Number($(this).val());
        sonuc = fiyat*miktar;
        sonucToplam = sonucToplam + sonuc;
        $(".tutar"+index).val(formatToTwoDecimalPlaces(sonuc));
      });     
    }, 500); 

    $('.satirBody').keyup(function() {
      sonucToplam = 0;
      $('.miktar').each(function(index, data) {
        var fiyat = Number($(".fiyat"+index).val());
        var miktar = Number($(this).val());
        sonuc = fiyat*miktar;
        sonucToplam = sonucToplam + sonuc;
        $(".tutar"+index).val(formatToTwoDecimalPlaces(sonuc));
        kdvHesapla(sonucToplam);
      });
    });

    function kdvHesapla(toplam){
      var indirim = Number($(".indirim").val());
      var kdvTutar = Number($(".kdvTutar").val());
      var araToplam = Number($(".araToplam").val());
      var kdv = (((toplam-indirim)*kdvTutar)/100);
      var araToplam = (toplam-indirim);
      var genelToplam = ((toplam-indirim) + kdv);

      $(".toplam").val(formatToTwoDecimalPlaces(toplam));
      $(".araToplam").val(formatToTwoDecimalPlaces(araToplam));
      $(".genelToplam").val(formatToTwoDecimalPlaces(genelToplam));
      $(".kdv").val(formatToTwoDecimalPlaces(kdv));
    }

    $('.kdvTutar').on('keyup', function() {
      var indirim = Number($(".indirim").val());
      var kdvTutar = Number($(".kdvTutar").val());
      var araToplam = Number($(".araToplam").val());
      var kdv = (((sonucToplam-indirim)*kdvTutar)/100);
      var araToplam = (sonucToplam-indirim);
      var genelToplam = ((sonucToplam-indirim) + kdv);

      $(".genelToplam").val(formatToTwoDecimalPlaces(genelToplam));
      $(".araToplam").val(formatToTwoDecimalPlaces(araToplam));
      $(".kdv").val(formatToTwoDecimalPlaces(kdv));
    });

    $('.indirim').on('keyup', function() {
      var indirim = Number($(".indirim").val());
      var kdvTutar = Number($(".kdvTutar").val());
      var araToplam = Number($(".araToplam").val());
      var kdv = (((sonucToplam-indirim)*kdvTutar)/100);
      var araToplam = (sonucToplam-indirim);
      var genelToplam = ((sonucToplam-indirim) + kdv);

      $(".araToplam").val(formatToTwoDecimalPlaces(araToplam));
      $(".genelToplam").val(formatToTwoDecimalPlaces(genelToplam));
      $(".kdv").val(formatToTwoDecimalPlaces(kdv));
    });

    // Virgülleri nokta yapıyor
    $("input:text").keyup(function() {
      $(this).val($(this).val().replace(/[,]/g, "."));
    });
});
</script>
<script>
$(document).ready(function() {
    // Sayfa yüklendiğinde buton durumlarını ayarla
    updateButtonStates();
    
    // Upload buton click handler
    window.handleUploadClick = function() {
        var uploadBtn = $('#uploadPdfBtn');
        if (!uploadBtn.prop('disabled')) {
            document.getElementById('pdfFileInput').click();
        }
    };
    
    // PDF dosya seçimi değiştiğinde otomatik yükleme
    $('#pdfFileInput').on('change', function() {
        var fileInput = this;
        var invoiceId = $(this).data('invoice-id');
        
        if (fileInput.files.length > 0) {
            var file = fileInput.files[0];
            
            // Dosya türü kontrolü
            var allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                alert('Sadece PDF, JPG, JPEG ve PNG dosyaları yüklenebilir.');
                fileInput.value = '';
                return;
            }
            
            // Dosya boyutu kontrolü (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('Dosya boyutu 2MB\'dan küçük olmalıdır.');
                fileInput.value = '';
                return;
            }
            
            // FormData oluştur
            var formData = new FormData();
            formData.append('pdf', file);
            formData.append('id', invoiceId);
            formData.append('_token', '{{ csrf_token() }}');
            
            // Yükleme durumu göster
            var uploadBtn = $('#uploadPdfBtn');
            var originalText = uploadBtn.html();
            uploadBtn.html('<i class="fas fa-spinner fa-spin"></i> Yükleniyor...');
            uploadBtn.prop('disabled', true);
            uploadBtn.removeClass('btn-upload-active').addClass('btn-secondary');
            
            // AJAX ile yükleme
            $.ajax({
                url: '{{ route("super.admin.invoices.upload") }}',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        
                        // Görüntüle butonunu aktif et
                        var viewBtn = $('#viewPdfBtn');
                        viewBtn.attr('href', response.file_url || '{{ asset("") }}' + 'upload/uploads/' + response.invoice_id);
                        viewBtn.removeClass('d-none');
                        
                        // Upload butonunu dosya adıyla değiştir
                        var fileName = file.name;
                        if (fileName.length > 20) {
                            fileName = fileName.substring(0, 17) + '...';
                        }
                        
// Upload butonunu dosya adıyla değiştir
                        var fileName = file.name;
                        var displayName = fileName;
                        if (displayName.length > 20) {
                            displayName = displayName.substring(0, 17) + '...';
                        }
                        
                        // Upload butonunu link haline getir
                        var uploadContainer = uploadBtn.parent();
                        var fileUrl = response.file_url || '{{ asset("") }}' + 'upload/uploads/' + response.invoice_id;
                        
                        uploadBtn.replaceWith(
                            '<a href="' + fileUrl + '" target="_blank" ' +
                            'style="display: inline-flex; align-items: center; padding: 6px 12px; border: 1px solid #dee2e6; border-radius: 4px; color: #495057; text-decoration: none; background: #f8f9fa; height: 32px;" ' +
                            'id="uploadedFileBtn" ' +
                            'title="' + fileName + ' - Görüntülemek için tıklayın">' +
                            '<i class="fas fa-file-pdf" style="color: #dc3545;"></i> ' + displayName +
                            '</a>'
                        );

                        // Silme butonunu göster
                        $('.eArsivSil').show();
                                                
                        // DataTable'ı yenile (eğer ana sayfadaysak)
                        if (typeof $('#datatableInvoice').DataTable === 'function') {
                            $('#datatableInvoice').DataTable().ajax.reload();
                        }
                        
                    } else {
                        alert(response.message || 'Yükleme başarısız oldu.');
                        resetUploadButton();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Upload error:', xhr.responseText);
                    alert('Yükleme sırasında hata oluştu: ' + error);
                    resetUploadButton();
                },
                complete: function() {
                    fileInput.value = '';
                }
            });
        }
    });
    
    // Upload butonunu eski haline getirme fonksiyonu
    function resetUploadButton() {
        var uploadBtn = $('#uploadPdfBtn');
        uploadBtn.html('<i class="fas fa-upload"></i>');
        uploadBtn.prop('disabled', false);
        uploadBtn.removeClass('btn-uploaded').addClass('btn-outline-success');
    }
    
    // Buton durumlarını güncelleme fonksiyonu
    function updateButtonStates() {
        var uploadBtn = $('#uploadPdfBtn');
        var viewBtn = $('#viewPdfBtn');
        
        // Eğer PDF varsa ve dosya adı varsa
        if (!viewBtn.hasClass('d-none')) {
            // Mevcut yüklenmiş dosya varsa, upload butonunu dosya adıyla değiştir
            var fileUrl = viewBtn.attr('href');
            if (fileUrl && fileUrl !== '#') {
                // Dosya adını URL'den çıkar
                var fileName = fileUrl.split('/').pop();
                
                // Çok uzunsa kısalt
                var displayName = fileName;
                if (displayName.length > 20) {
                    displayName = displayName.substring(0, 17) + '...';
                }
                
                uploadBtn.replaceWith(
                    '<a href="' + fileUrl + '" target="_blank" ' +
                    'class="btn btn-file-uploaded btn-sm mobilBtn mbuton1" ' +
                    'id="uploadedFileBtn" ' +
                    'title="' + fileName + ' - Görüntülemek için tıklayın">' +
                    '<i class="fas fa-file-pdf"></i> ' + displayName +
                    '</a>'
                );
            }
        }
    }
});
</script>

<script>
$('#editInvo').on('submit', function(e) {
    e.preventDefault();

    let formIsValid = true;
    $(this).find('input[required], select[required]').each(function() {
        if (!$(this).val()) {
            formIsValid = false;
            return false;
        }
    });

    if (!formIsValid) {
        alert('Lütfen zorunlu alanları doldurun.');
        return;
    }

    var formData = new FormData(this);
    $.ajax({
        url: $(this).attr("action"),
        type: "POST",
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        success: function(data) {
            console.log('Form Response:', data);
            
            if (data === false) {
                window.location.reload(true);
            } else if (data && data.status === 'success') {
                // Başarılı güncelleme
                alert(data.message || "Fatura güncellendi");
                $('#datatableInvoice').DataTable().ajax.reload();
                
                // Modal'ı kapat - Bu satırlar eklendi
                $('#editInvoiceModal').modal('hide');
                // Veya modal ID'si farklıysa:
                // $('.modal').modal('hide');
                
                // Başarı efekti
                $('#editInvo').addClass('border-success');
                setTimeout(function() {
                    $('#editInvo').removeClass('border-success');
                }, 3000);
                
            } else {
                // Eski format uyumluluğu
                alert("Fatura güncellendi");
                $('#datatableInvoice').DataTable().ajax.reload();
                
                // Modal'ı kapat - Bu satır eklendi
                $('#editInvoiceModal').modal('hide');
                // Veya modal ID'si farklıysa:
                // $('.modal').modal('hide');
            }
        },
        error: function(xhr, status, error) {
            console.error('Form Error:', xhr.responseText);
            alert("Güncelleme başarısız: " + error);
            
            // Hata efekti
            $('#editInvo').addClass('border-danger');
            setTimeout(function() {
                $('#editInvo').removeClass('border-danger');
            }, 3000);
            
            // Hata durumunda modal açık kalabilir, isteğe bağlı kapatabilirsiniz
            // $('#editInvoiceModal').modal('hide');
        }
    });
});
</script>