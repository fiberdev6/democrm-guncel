<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    $hasParasutIntegration = App\Services\InvoiceIntegrationFactory::hasIntegration($firma->id);
@endphp


<form method="post" id="editInvo" action="{{ route('update.invoices', $firma->id)}}" enctype="multipart/form-data" class="needs-validation" novalidate>
  @csrf
  <div class="card f5 card-invoices">
    <div class=" ch1" style="padding: 3px 10px;">
      <div class="tarihWrap d-flex justify-content-between align-items-center">
    
    <!-- Sol Taraf Grubu (Etiket ve Input) -->
    <div class="d-flex align-items-center">
        <label class="me-2 mb-0">Tarih<span style="font-weight: bold; color: red;">*</span></label>
        <input type="date" name="faturaTarihi" class="form-control datepicker kayitTarihi" value="{{ \Carbon\Carbon::parse($invoice_id->faturaTarihi)->format('Y-m-d')}}" style="width: 150px; background:#fff" required>
    </div>

    <!-- Sağ Taraf Grubu (İkon) -->
    @if(!$invoice_id->formalized)
    <span>
        <a href="#" data-id="{{$invoice_id->musteriid}}" class="faturaMusteriDuzenleBtn">
            <i class="fas fa-edit" style="font-size: 15px; color: red; text-shadow: none;"></i>
        </a>
    </span>
    @endif
</div>
    </div>
  </div> 

  <div class="card card-invoices f2">
     <div class="card-header card-invoices-header">MÜŞTERİ BİLGİSİ</div>
     <div class="card-body card-invoices-body">
        <div class="row" style="font-size: 14px;">
    <!-- Sol sütun: Servis -->
    <div class="col-md-6 d-flex flex-row align-items-center gap-2 border-end" style="padding-right: 15px;">
    <span> <strong> SERVİS İD: {{ $invoice_id->servisid }} </strong> </span>
    <a href="{{ route('all.services', [$firma->id, 'did' => $invoice_id->servisid]) }}" target="_blank" class="servisiAc btn btn-outline-danger btn-outline-danger-custom col-md-3  px-2 py-1" style="font-size: 13px; line-height: 1.3;">
        Servisi Aç
    </a>
</div>

    <!-- Sağ sütun: Müşteri Bilgisi -->
    <div class="col-md-6 d-flex flex-column gap-1" style="padding-left: 15px;">
        <span><strong>{{ $invoice_id->customer->adSoyad }}
            @if($invoice_id->customer->musteriTipi == '1')
                (BİREYSEL)
            @elseif($invoice_id->customer->musteriTipi == '2')
                (KURUMSAL)
            @endif
        </strong></span>

        <span>{{ $invoice_id->customer?->adres }} {{ $invoice_id->customer?->state?->ilceName }}/{{ $invoice_id->customer?->country?->name }}</span>

        @if(!empty($invoice_id->customer?->tcNo))
            <span>TC: {{ $invoice_id->customer->tcNo }}</span>
        @endif

        @if(!empty($invoice_id->customer?->vergiNo) || !empty($invoice_id->customer?->vergiDairesi))
            <span>VERGİ NO/DAİRESİ: {{ $invoice_id->customer->vergiNo }}/{{ $invoice_id->customer->vergiDairesi }}</span>
        @endif
    </div>
</div>


     </div>
  </div>

  <div class="card card-invoices f2">
    <div class="card-body card-invoices-body">
        <div class="row form-group head">
            <div class="col-5 rw1 col-sm-6"><label>Cinsi</label></div>
            <div class="col-2 rw2 col-sm-2"><label>Miktar</label></div>
            <div class="col-2 rw3 col-sm-2"><label>Fiyat</label></div>
            <div class="col-3 rw4 col-sm-2"><label>Tutar</label></div>
        </div>

        <div class="satirBody">
            @foreach($invoice_id->invoice_products as $key => $product)
                <div class="row form-group">
                    <div class="col-5 rw1 col-sm-6">
                        <input type="text" name="aciklama[]" value="{{ $product->aciklama }}" class="form-control aciklama aciklama{{ $key }} buyukYaz" placeholder="Ürün" autocomplete="off">
                    </div>
                    <div class="col-2 col-sm-2 rw2 custom-rw1">
                        <input type="text" name="miktar[]" value="{{ $product->miktar }}" onkeyup="sayiKontrol(this)" class="form-control miktar miktar{{ $key }}" autocomplete="off">
                    </div>
                    <div class="col-2 col-sm-2 rw3 custom-rw1">
                        <input type="text" name="fiyat[]" value="{{ $product->fiyat }}" onkeyup="sayiKontrol(this)" class="form-control fiyat fiyat{{ $key }}" autocomplete="off">
                    </div>
                    <div class="col-3 rw4 col-sm-2 custom-rw1 pr-custom">
                        <input type="text" name="tutar[]" value="{{ $product->tutar }}" onkeyup="sayiKontrol(this)" class="form-control tutar tutar{{ $key }}" autocomplete="off">
                    </div>
                </div>
            @endforeach
        </div>

      @if(!$invoice_id->formalized)
      <div class="row form-group" style="margin: 0;border: 0;">
        <button type="button" class="col-xs-12 form-control btn btn-primary2 satirEkle" data-id="1" style="color: #fff;display: inline-block;">Satır Ekle</button>
      </div>
      @endif
    </div>
  </div>
       
 
<div class="row cardRow1 mb-1">

    <!-- 1. Sütun (Sol Taraf) -->
    <div class="col-lg-6 mb-3 mb-lg-0 custom-p-m custom-p-r-m-k">
      <div class="card card-invoices f3 h-100">
        <div class="card-body card-invoices-body">

          <div class="row form-group" style="border:0">
            <div class="col-md-4 rw1"><label>Fatura No</label></div>
            <div class="col-md-8 rw2">
              @if($hasParasutIntegration)
                <input type="text" name="faturaNumarasi" class="form-control buyukYaz faturaNumarasi" value="" disabled placeholder="Paraşüt tarafından atanacak">
              @else
                <input type="text" name="faturaNumarasi" class="form-control buyukYaz faturaNumarasi" value="">
              @endif
            </div>
          </div>

          {{-- YENİ EKLENEN: Tevkifat Kodu --}}
          <div class="row form-group" style="border:0">
    <div class="col-md-4 rw1"><label>Tevkifat Kodu</label></div>
    <div class="col-md-8 rw2">
        <select class="form-select tevkifatKodu" name="tevkifatKodu" {{ $invoice_id->formalized ? 'disabled' : '' }}>
            <option value="" data-oran="0">Tevkifat Yok</option>
            @if(isset($tevkifatKodlari))
                @foreach($tevkifatKodlari as $kod)
                    @if($kod->durum == 1)
                        <option value="{{ $kod->kodu }}" 
                                data-oran="{{ $kod->orani }}"
                                {{ $invoice_id->tevkifatKodu == $kod->kodu ? 'selected' : '' }}>
                            {{ $kod->kodu }} - {{ $kod->adi }} ({{ $kod->orani }}/10)
                        </option>
                    @endif
                @endforeach
            @endif
        </select>
    </div>
</div>


          {{-- YENİ EKLENEN: KDV Kodu --}}
          <div class="row form-group" style="border:0">
    <div class="col-md-4 rw1"><label>KDV İstisna Kodu</label></div>
    <div class="col-md-8 rw2">
        <select class="form-select kdvKodu" name="kdvKodu" {{ $invoice_id->formalized ? 'disabled' : '' }}>
            <option value="" data-kdv-oran="">KDV İstisnası Yok</option>
            @if(isset($kdvKodlari))
                @foreach($kdvKodlari as $kod)
                    @if($kod->durum == 1)
                        <option value="{{ $kod->kodu }}" 
                                data-kdv-oran="{{ $kod->orani }}"
                                {{ $invoice_id->kdvKodu == $kod->kodu ? 'selected' : '' }}>
                            {{ $kod->kodu }} - {{ $kod->adi }} (%{{ $kod->orani }} KDV)
                        </option>
                    @endif
                @endforeach
            @endif
        </select>
    </div>
</div>


          {{-- YENİ EKLENEN: KDV Açıklaması --}}
          <div class="row form-group" style="border:0">
            <div class="col-md-4 rw1"><label>KDV Açıklaması</label></div>
            <div class="col-md-8 rw2">
              <input type="text" name="kdvAciklama" class="form-control kdvAciklama" value="{{$invoice_id->kdvAciklama ?? ''}}">
            </div>
          </div>

          {{-- YENİ EKLENEN: Fatura Açıklaması --}}
          <div class="row form-group" style="border:0">
            <div class="col-md-4 rw1"><label>Fatura Açıklaması</label></div>
            <div class="col-md-8 rw2">
              <input type="text" name="faturaAciklama" class="form-control faturaAciklama" {{ $invoice_id->formalized ? 'disabled' : '' }} value="{{$invoice_id->faturaAciklama ?? ''}}">
            </div>
          </div>

          @if(!$hasParasutIntegration)
          <div class="row" style="border:0">
            <div class="col-md-4 rw1"><label>Ödeme Şekli<span style="font-weight: bold; color: red;">*</span></label></div>
            <div class="col-md-8 rw2">
              <select class="form-select odemeSekilleri" name="odemeSekli" required>
                <option value="">Seçiniz</option>
                @foreach($payment_methods as $method)
                  <option value="{{$method->id}}" {{$method->id == $invoice_id->odemeSekli ? 'selected' : ''}}>{{$method->odemeSekli}}</option>
                @endforeach
              </select>
            </div>
          </div>
          @endif

          @if(!$hasParasutIntegration)
            <div class="row" style="border:0">
                <div class="col-md-4 rw1"><label>Fatura Durumu<span style="font-weight: bold; color: red;">*</span></label></div>
                <div class="col-md-8 rw2">
                  <select class="form-select faturaDurumu" name="faturaDurumu" required>
                    <option value="">Seçiniz</option>
                    <option value="draft" {{$invoice_id->faturaDurumu == 'draft' ? 'selected' : ''}}>Beklemede</option>
                    <option value="sent" {{$invoice_id->faturaDurumu == 'sent' ? 'selected' : ''}}>Gönderildi</option>
                    <option value="error" {{$invoice_id->faturaDurumu == 'error' ? 'selected' : ''}}>Gönderilmedi</option>
                  </select>
                </div>
            </div>
            @else
            {{-- ✅ Paraşüt aktifken hidden olarak mevcut değeri gönder --}}
            <input type="hidden" name="faturaDurumu" value="{{ $invoice_id->faturaDurumu ?? 'draft' }}">
            @endif

          @if(!$hasParasutIntegration)
            <div class="row form-group" style="border:0">
              <div class="col-md-4 rw1"><label>Toplam Yazıyla</label></div>
              <div class="col-md-8 rw2"><input type="text" name="toplamYazi" autocomplete="off" value="{{$invoice_id->toplamYazi}}" class="form-control buyukYaz toplamYazi"></div>
            </div>
                <div class="row form-group" style="border:0">
            <div class="col-md-4 rw1"><label>E-Arşiv<span style="font-weight: bold; color: red;">*</span></label></div>
            <div class="col-md-8 rw2">
              <div class="btnWrap ">
                @if($invoice_id->faturaPdf == null)
                <a href="{{asset($invoice_id->faturaPdf)}}" target="_blank" class="btn btn-warning btn-sm btn-block d-none">Görüntüle</a>
                @else
                <a href="{{asset($invoice_id->faturaPdf)}}" target="_blank" class="btn btn-warning btn-sm btn-block">Görüntüle</a>
                @endif
                <a href="javascript:void(0);" data-bs-id="{{$invoice_id->id}}" class="btn btn-warning btn-sm invoic_e" title="Düzenle"><i class="fas fa-edit"></i></a>
                <a href="" class="btn btn-danger btn-sm  eArsivSil" data-id="{{$invoice_id->id}}">Sil</a>
              </div>
            </div>
            </div>
            @else 
            @if($hasParasutIntegration && $invoice_id->formalized)
              <div class="row form-group" style="border:0">
                  <div class="col-md-4 rw1">
                      <label>
                          @if($invoice_id->formalization_type === 'e-invoice')
                              e-Fatura
                          @else
                              e-Arşiv
                          @endif
                          Durumu
                      </label>
                  </div>
                  <div class="col-md-8 rw2">
                      <div class="btnWrap">
                          @php
                              $statusColors = [
                                  'pending' => 'warning',
                                  'sent' => 'success',
                                  'error' => 'danger'
                              ];
                              $statusTexts = [
                                  'pending' => 'Onay Bekliyor',
                                  'sent' => 'Gönderildi',
                                  'error' => 'Hata'
                              ];
                              $color = $statusColors[$invoice_id->formalization_status] ?? 'secondary';
                              $text = $statusTexts[$invoice_id->formalization_status] ?? 'Bilinmiyor';
                          @endphp
                          
                          <span class="badge bg-{{ $color }} me-2">{{ $text }}</span>
                          
                          @if($invoice_id->formalization_status === 'sent')
                              @if(!empty($invoice_id->formalization_pdf_url))
                                  <a href="{{ $invoice_id->formalization_pdf_url }}" 
                                    target="_blank" 
                                    class="btn btn-warning btn-sm"
                                    title="Son Kullanma: {{ $invoice_id->formalization_pdf_expires_at ? \Carbon\Carbon::parse($invoice_id->formalization_pdf_expires_at)->format('d.m.Y H:i') : 'Belirtilmemiş' }}"
                                    data-bs-toggle="tooltip">
                                      <i class="far fa-file-pdf"></i> PDF'i Görüntüle
                                  </a>
                              @else
                                  <button type="button" 
                                          class="btn btn-info btn-sm checkPdfBtn" 
                                          data-invoice-id="{{ $invoice_id->id }}"
                                          title="PDF henüz hazırlanıyor, kontrol etmek için tıklayın"
                                          data-bs-toggle="tooltip">
                                      <i class="fas fa-sync-alt"></i> PDF Durumunu Kontrol Et
                                  </button>
                              @endif
                          @elseif($invoice_id->formalization_status === 'pending')
                              <button type="button" 
                                      class="btn btn-warning btn-sm checkPdfBtn" 
                                      data-invoice-id="{{ $invoice_id->id }}"
                                      title="İşlem devam ediyor, durumu kontrol etmek için tıklayın"
                                      data-bs-toggle="tooltip">
                                  <i class="fas fa-sync-alt"></i> Kontrol Et
                              </button>
                          @elseif($invoice_id->formalization_status === 'error')
                              <span class="badge bg-danger" 
                                    title="{{ $invoice_id->formalization_error ?? 'Resmileştirme hatası' }}"
                                    data-bs-toggle="tooltip"
                                    style="cursor: help;">
                                  <i class="fas fa-exclamation-triangle"></i> Hata
                              </span>
                          @endif
                      </div>
                  </div>
              </div>
              @endif
          @endif
        </div>
      </div>
    </div>

    <!-- 2. Sütun (Sağ Taraf) -->
    <div class="col-lg-6 custom-p-r-min custom-p-m-k custom-p-r-m-k">
      <div class="card card-invoices f4 h-100">
        <div class="card-body card-invoices-body" style="padding:17px 5px">
          <div class="row form-group">
            <div class="col-md-4 rw1"><label>Toplam<span style="font-weight: bold; color: red;">*</span></label></div>
            <div class="col-md-8 rw2 custom-rw2"><input type="text" onkeyup="sayiKontrol(this)" name="toplam" value="{{$invoice_id->toplam}}" autocomplete="off" class="form-control toplam" readonly required></div>
          </div>

          @if(!$hasParasutIntegration)
          <div class="row form-group">
            <div class="col-md-4 rw1"><label>İndirim</label></div>
            <div class="col-md-8 rw2 custom-rw2"><input type="text" onkeyup="sayiKontrol(this)" name="indirim" value="{{$invoice_id->indirim}}" autocomplete="off" class="form-control indirim"></div>
          </div>
          <div class="row form-group">
            <div class="col-md-4 rw1"><label>Ara Toplam</label></div>
            <div class="col-md-8 rw2 custom-rw2"><input type="text" onkeyup="sayiKontrol(this)" name="araToplam" value="{{number_format($invoice_id->toplam - $invoice_id->indirim, 2, '.', '')}}" autocomplete="off" class="form-control araToplam"></div>
          </div>
          @else
          {{-- Paraşüt aktifken hidden olarak gönder --}}
          <input type="hidden" name="indirim" class="indirim" value="{{$invoice_id->indirim ?? 0}}">
          <input type="hidden" name="araToplam" class="araToplam" value="{{number_format($invoice_id->toplam - ($invoice_id->indirim ?? 0), 2, '.', '')}}">
          @endif

          <div class="row form-group">
            <div class="col-md-2 rw1"><label>KDV %</label></div>
            <div class="col-md-2 rw2 col-6"><input type="text" {{ $invoice_id->formalized ? 'disabled' : '' }} onkeyup="sayiKontrol(this)" name="kdvTutar" autocomplete="off" class="form-control kdvTutar" value="{{$invoice_id->kdvTutar ?? 20}}"></div>
            <div class="col-md-8 rw2 custom-rw2 col-6"><input type="text" readonly  onkeyup="sayiKontrol(this)" name="kdv" class="form-control kdv" value="{{$invoice_id->kdv}}"></div>
          </div>

          {{-- YENİ EKLENEN: Tevkifat Oranı ve Tutarı --}}
          <div class="row form-group">
    <div class="col-md-6 rw1"><label>Tevkifat Oranı</label></div>
    <div class="col-md-2 col-6 rw2">
        <select class="form-select tevkifatOrani" name="tevkifatOrani" {{ $invoice_id->formalized ? 'disabled' : '' }}>
            <option value="0" {{ ($invoice_id->tevkifatOrani ?? 0) == 0 ? 'selected' : '' }}>Yok</option>
            <option value="2" {{ ($invoice_id->tevkifatOrani ?? 0) == 2 ? 'selected' : '' }}>2/10</option>
            <option value="3" {{ ($invoice_id->tevkifatOrani ?? 0) == 3 ? 'selected' : '' }}>3/10</option>
            <option value="4" {{ ($invoice_id->tevkifatOrani ?? 0) == 4 ? 'selected' : '' }}>4/10</option>
            <option value="5" {{ ($invoice_id->tevkifatOrani ?? 0) == 5 ? 'selected' : '' }}>5/10</option>
            <option value="7" {{ ($invoice_id->tevkifatOrani ?? 0) == 7 ? 'selected' : '' }}>7/10</option>
            <option value="9" {{ ($invoice_id->tevkifatOrani ?? 0) == 9 ? 'selected' : '' }}>9/10</option>
            <option value="10" {{ ($invoice_id->tevkifatOrani ?? 0) == 10 ? 'selected' : '' }}>10/10</option>
        </select>
    </div>
    <div class="col-md-4 custom-rw2 col-6 rw2">
        <input type="text" class="form-control tevkifatTutariGoster" value="{{ number_format($invoice_id->tevkifatTutari ?? 0, 2, '.', '') }}" readonly>
        <input type="hidden" name="tevkifatTutari" class="tevkifatTutari" value="{{ $invoice_id->tevkifatTutari ?? 0 }}">
    </div>
</div>

          <div class="row form-group" style="padding-bottom: 0">
            <div class="col-md-4 rw1"><label>Genel Toplam<span style="font-weight: bold; color: red;">*</span></label></div>
            <div class="col-md-8 rw2 custom-rw2"><input type="text" readonly  onkeyup="sayiKontrol(this)" name="genelToplam" value="{{$invoice_id->genelToplam}}" autocomplete="off" class="form-control genelToplam" required></div>
          </div>

        </div>
      </div>
    </div>
</div>
    <div class="row align-items-center mb-3">
    {{-- Sol: Tahsilat Butonları --}}
    <div class="col-md-8 col-sm-6">
        @if($hasParasutIntegration)
            <div class="d-flex flex-wrap align-items-center gap-2" id="tahsilatButtonsContainer">
                {{-- ⭐ Her zaman "Tahsilat Ekle" butonu göster, JavaScript ile kontrol edilecek --}}
                <button type="button" class="btn btn-success btn-sm tahsilatEkleBtn" data-invoice-id="{{$invoice_id->id}}">
                    <i class="fas fa-plus"></i> Tahsilat Ekle
                </button>
                
                @if($invoice_id->has_payment)
                    <button type="button" class="btn btn-info btn-sm tahsilatlariGorBtn" data-invoice-id="{{$invoice_id->id}}">
                        <i class="fas fa-list"></i> Tahsilatları Gör
                        @if($invoice_id->parasutPaymentIds && count($invoice_id->parasutPaymentIds) > 0)
                            ({{count($invoice_id->parasutPaymentIds)}} adet)
                        @endif
                    </button>
                @endif
                
                {{-- Durum bilgisi - JavaScript ile güncellenecek --}}
                <small class="text-muted" id="paymentStatusText">
                    <i class="fas fa-spinner fa-spin"></i> Kontrol ediliyor...
                </small>
            </div>
        @endif
    </div>

    {{-- Sağ: Kaydet Butonu --}}
    <div class="col-md-4 col-sm-6 text-end">
        <input type="hidden" name="id" value="{{ $invoice_id->id }}">
        @if(!$invoice_id->formalized)
            <input type="submit" class="btn btn-info btn-sm waves-effect waves-light" value="Kaydet" >
        @endif
    </div>
</div>
  </div>
</form>



<script type="text/javascript">
$(document).ready(function(){
    $('#editInvo').on('click', '.invoic_e', function(e){
        var id = $(this).attr("data-bs-id");
        var firma_id = {{$firma->id}};
        $.ajax({
            url: "/" + firma_id + "/fatura/goruntule/" + id
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
<script type="text/javascript">
  $(".faturaMusteriDuzenleBtn").click(function(){
    var id = {{$invoice_id->musteriid}};
    var firma_id = {{$firma->id}};
    $('#editInvoiceCustomerModal').modal('show');
    $.ajax({
      url: "/" + firma_id + "/servis-musteri/duzenle/" + id
    }).done(function(data) {
      if($.trim(data)==="-1"){
        window.location.reload(true);
      }else{
        $('#editInvoiceCustomerModal .modal-body').html(data);
      }
    });
  });
  
</script>

<script type="text/javascript">
  function sayiKontrol(v) {
    var isNum = /^[0-9-'.']*$/;
    if (!isNum.test(v.value)) { 
      v.value = v.value.replace(/[^0-9-',']/g, "");
    }                   
  }

  $('.buyukYaz').keyup(function(){
    this.value = this.value.toUpperCase();
  });

  $('.satirBody').on('keyup', '.buyukYaz', function () {
    this.value = this.value.toUpperCase();
  });

  $(".satirEkle").click(function () {
    var index = $(".satirBody .row").length; // Mevcut satır sayısı
    var satirClone = `
      <div class="row form-group align-items-center satir">
        <div class="col-5 rw1 col-sm-6">
          <input type="text" name="aciklama[]" class="form-control aciklama aciklama${index} buyukYaz" placeholder="Ürün" autocomplete="off">
        </div>
        <div class="col-2 rw2 custom-rw1 col-sm-2">
          <input type="text" name="miktar[]" onkeyup="sayiKontrol(this)" class="form-control miktar miktar${index}" autocomplete="off">
        </div>
        <div class="col-2 rw3 custom-rw1 col-sm-2">
          <input type="text" name="fiyat[]" onkeyup="sayiKontrol(this)" class="form-control fiyat fiyat${index}" autocomplete="off">
        </div>
        <div class="col-3 rw4 custom-rw1 pr-custom col-sm-2">
          <input type="text" name="tutar[]" onkeyup="sayiKontrol(this)" class="form-control tutar tutar${index}" autocomplete="off">
        </div>
      </div>
    `;
    $(".satirBody").append(satirClone);
  });
  
  $(document).on('click', '.satirSil', function () {
    $(this).closest('.satir').remove();
  });
</script>

<script>
$(document).ready(function() {
    // ⭐ Event namespace ile çalış - önceki event'leri temizle
    const eventNamespace = '.invoicePayment';
    
    // ⭐ Önce tüm eski event'leri temizle
    $(document).off(eventNamespace);
    $('.tahsilatEkleBtn').off('click' + eventNamespace);
    $('#tahsilatKaydetBtn').off('click' + eventNamespace);
    
    let remainingAmountGlobal = 0; 

    $('#tahsilatModal, #tahsilatlariGorModal').on('hidden.bs.modal', function () {
        if ($('#editInvoiceModal').hasClass('show')) {
            $('body').addClass('modal-open');
            if ($('.modal-backdrop').length === 0) {
                $('<div class="modal-backdrop fade show"></div>').appendTo('body');
            }
        }
    });

    checkRemainingAmountOnLoad();

    function checkRemainingAmountOnLoad() {
        const invoiceId = $('.tahsilatEkleBtn').data('invoice-id');
        if (!invoiceId) return;

        const firmaId = {{ $firma->id }};
        
        $.ajax({
            url: `/${firmaId}/fatura/${invoiceId}/odemeler`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const remaining = parseFloat(response.remaining_amount || 0);
                    updatePaymentButtons(remaining, response.payments ? response.payments.length : 0);
                } else {
                    $('#paymentStatusText').html('<i class="fas fa-exclamation-triangle text-warning"></i> Durum kontrol edilemedi');
                }
            },
            error: function(xhr) {
                if (xhr.status === 404) {
                    $('#paymentStatusText').html('<i class="fas fa-info-circle text-info"></i> Henüz Paraşüt\'e gönderilmemiş');
                    $('.tahsilatEkleBtn').prop('disabled', true).attr('title', 'Önce faturayı Paraşüt\'e gönderin');
                } else {
                    $('#paymentStatusText').html('<i class="fas fa-exclamation-triangle text-warning"></i> Kontrol edilemedi');
                }
            }
        });
    }

    function updatePaymentButtons(remainingAmount, paymentCount) {
        const $tahsilatBtn = $('.tahsilatEkleBtn');
        const $statusText = $('#paymentStatusText');
        
        if (remainingAmount <= 0) {
            $tahsilatBtn.hide();
            $statusText.html('<i class="fas fa-check-circle text-success"></i> Fatura tamamen ödendi');
        } else if (paymentCount > 0) {
            $tahsilatBtn.show().prop('disabled', false);
            $statusText.html(
                `<i class="fas fa-exclamation-circle text-warning"></i> Kısmi ödeme yapılmış - Kalan: <strong>${remainingAmount.toFixed(2)} TL</strong>`
            );
        } else {
            $tahsilatBtn.show().prop('disabled', false);
            $statusText.html('<i class="fas fa-info-circle text-muted"></i> Henüz tahsilat yapılmamış');
        }
    }

    // ⭐ Tahsilat Ekle Butonu - Event namespace ile
    $('.tahsilatEkleBtn').off('click' + eventNamespace).on('click' + eventNamespace, function(e) {
        e.preventDefault();
        e.stopImmediatePropagation(); // Çoklu tetiklenmeyi engelle
        
        const invoiceId = $(this).data('invoice-id');
        
        $('#invoiceId').val(invoiceId);
        $('#tahsilatForm')[0].reset();
        $('#paymentDate').val('{{date('Y-m-d')}}');
        
        loadParasutAccounts();
        loadRemainingAmount(invoiceId);
        
        $('#tahsilatModal').modal('show');
    });

    // Paraşüt hesaplarını yükle
    function loadParasutAccounts() {
        const firmaId = {{ $firma->id }};
        
        $.ajax({
            url: `/${firmaId}/parasut/hesaplar`,
            type: 'GET',
            success: function(response) {
                if (response.success && response.accounts) {
                    let options = '<option value="">Seçiniz</option>';
                    
                    response.accounts.forEach(function(account) {
                        const name = account.attributes.name || 'İsimsiz Hesap';
                        const currency = account.attributes.currency || 'TRL';
                        options += `<option value="${account.id}">${name} (${currency})</option>`;
                    });
                    
                    $('#accountId').html(options);
                } else {
                    $('#accountId').html('<option value="">Hesap bulunamadı</option>');
                }
            },
            error: function() {
                $('#accountId').html('<option value="">Yüklenemedi</option>');
                alert('Hesaplar yüklenemedi.');
            }
        });
    }

    function loadRemainingAmount(invoiceId) {
        const firmaId = {{ $firma->id }};
        
        $.ajax({
            url: `/${firmaId}/fatura/${invoiceId}/odemeler`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const remaining = parseFloat(response.remaining_amount || 0);
                    remainingAmountGlobal = remaining;
                    
                    $('#remainingAmountDisplay').text(remaining.toFixed(2));
                    
                    if (remaining <= 0) {
                        $('#remainingAmountDisplay').parent().removeClass('alert-info').addClass('alert-success');
                        $('#remainingAmountDisplay').parent().html(
                            '<i class="fas fa-check-circle"></i> Fatura tamamen ödendi'
                        );
                        $('#tahsilatKaydetBtn').prop('disabled', true).html('<i class="fas fa-check"></i> Ödeme Tamamlandı');
                    } else {
                        $('#tahsilatKaydetBtn').prop('disabled', false).html('<i class="fas fa-check-circle"></i> Tamamını Tahsil Et');
                    }
                } else {
                    $('#remainingAmountDisplay').text('Hesaplanamadı');
                }
            },
            error: function(xhr) {
                if (xhr.status === 404) {
                    $('#remainingAmountDisplay').text('Fatura bulunamadı');
                    alert('Bu fatura Paraşüt\'te bulunamadı.');
                    $('#tahsilatModal').modal('hide');
                } else {
                    $('#remainingAmountDisplay').text('Hata');
                }
            }
        });
    }

    // ⭐ Tahsilat Kaydet - Event namespace ile ve sadece bir kez çalışacak şekilde
    $('#tahsilatKaydetBtn').off('click' + eventNamespace).on('click' + eventNamespace, function(e) {
        e.preventDefault();
        e.stopImmediatePropagation(); // Çoklu tetiklenmeyi engelle
        
        // ⭐ Buton zaten disabled ise işlem yapma (double click önleme)
        if ($(this).prop('disabled')) {
            return false;
        }
        
        const form = $('#tahsilatForm');
        
        if (!$('#accountId').val()) {
            alert('Lütfen kasa/banka hesabı seçiniz.');
            return false;
        }
        
        if (!$('#paymentDate').val()) {
            alert('Lütfen tarih giriniz.');
            return false;
        }

        if (remainingAmountGlobal <= 0) {
            alert('Fatura zaten tamamen ödendi.');
            return false;
        }

        const formData = {
            invoice_id: $('#invoiceId').val(),
            account_id: $('#accountId').val(),
            date: $('#paymentDate').val(),
            description: $('#paymentDescription').val() || 'Tahsilat'
        };

        const firmaId = {{ $firma->id }};
        const $btn = $(this);
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Kaydediliyor...');

        $.ajax({
            url: `/${firmaId}/fatura/tahsilat-ekle`,
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Tahsilat response:', response); 
                
                if (response.success) {
                    const amountPaid = response.amount_paid ? parseFloat(response.amount_paid).toFixed(2) : '0.00';
                    
                    alert(`Tahsilat başarıyla eklendi!\n\nÖdenen Tutar: ${amountPaid} TL\n\nFatura tamamen ödendi.`);
                    
                    $('#tahsilatModal').modal('hide');
                    
                    if (typeof $('#datatableInvoice').DataTable === 'function') {
                        $('#datatableInvoice').DataTable().ajax.reload(null, false);
                    }
                    
                    // Edit modalını yenile
                    var invoiceId = formData.invoice_id;
                    var firma_id = {{ $firma->id }};
                    
                    $.ajax({
                        url: "/" + firma_id + "/fatura/duzenle/" + invoiceId
                    }).done(function (data) {
                        if ($.trim(data) !== "-1") {
                            $('#editInvoiceModal .modal-body').html(data);
                        }
                    });
                } else {
                    alert('Hata: ' + (response.message || 'Bilinmeyen hata'));
                    $btn.prop('disabled', false).html('<i class="fas fa-check-circle"></i> Tamamını Tahsil Et');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Hatası:', xhr.responseText); 
                
                let errorMsg = 'Tahsilat eklenemedi.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    errorMsg = 'Sunucu hatası: ' + xhr.status;
                }
                
                alert(errorMsg);
                $btn.prop('disabled', false).html('<i class="fas fa-check-circle"></i> Tamamını Tahsil Et');
            }
        });
        
        return false; // Form submit'i engelle
    });

    // ⭐ Tahsilatları Gör
    $('.tahsilatlariGorBtn').off('click' + eventNamespace).on('click' + eventNamespace, function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        const invoiceId = $(this).data('invoice-id');
        const firmaId = {{ $firma->id }};
        
        $('#paymentsListContainer').html('<div class="text-center"><div class="spinner-border text-primary"></div></div>');
        $('#tahsilatlariGorModal').modal('show');
        
        $.ajax({
            url: `/${firmaId}/fatura/${invoiceId}/odemeler`,
            type: 'GET',
            success: function(response) {
                if (response.success && response.payments.length > 0) {
                    let html = '<div class="table-responsive"><table class="table table-striped">';
                    html += '<thead><tr><th>Tarih</th><th>Tutar</th><th>Açıklama</th><th>Tahsilat ID</th><th>İşlem</th></tr></thead><tbody>';
                    
                    response.payments.forEach(function(payment) {
                        const date = payment.attributes.date || '-';
                        const amount = parseFloat(payment.attributes.amount || 0).toFixed(2);
                        const desc = payment.attributes.description || '-';
                        const paymentId = payment.id || '-';
                        
                        html += `<tr>
                            <td>${date}</td>
                            <td><strong>${amount} TL</strong></td>
                            <td>${desc}</td>
                            <td><code>${paymentId}</code></td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm deleteTahsilatBtn" 
                                    data-invoice-id="${invoiceId}" 
                                    data-payment-id="${paymentId}"
                                    data-payment-amount="${amount}"
                                    title="Tahsilatı Sil">
                                     Sil
                                </button>
                            </td>
                        </tr>`;
                    });
                    
                    html += '</tbody></table></div>';
                    
                    const remaining = parseFloat(response.remaining_amount || 0);
                    if (remaining > 0) {
                        html += `<div class="alert alert-warning">
                            <strong><i class="fas fa-exclamation-triangle"></i> Kalan Tutar:</strong> ${remaining.toFixed(2)} TL
                        </div>`;
                    } else {
                        html += `<div class="alert alert-success">
                            <strong><i class="fas fa-check-circle"></i> Fatura Tamamen Ödendi</strong>
                        </div>`;
                    }
                    
                    $('#paymentsListContainer').html(html);
                } else {
                    $('#paymentsListContainer').html('<div class="alert alert-warning">Henüz tahsilat yapılmamış.</div>');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Tahsilatlar yüklenemedi.';
                if (xhr.status === 404) {
                    errorMsg = 'Bu fatura Paraşüt\'te bulunamadı.';
                }
                $('#paymentsListContainer').html(`<div class="alert alert-danger">${errorMsg}</div>`);
            }
        });
    });

    // ⭐ TAHSİLAT SİLME - Delegate event ile (document üzerinden)
    $(document).off('click' + eventNamespace, '.deleteTahsilatBtn').on('click' + eventNamespace, '.deleteTahsilatBtn', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation(); // Çoklu tetiklenmeyi engelle
        
        // ⭐ Buton zaten disabled ise işlem yapma
        if ($(this).prop('disabled')) {
            return false;
        }
        
        const invoiceId = $(this).data('invoice-id');
        const paymentId = $(this).data('payment-id');
        const paymentAmount = $(this).data('payment-amount');
        const firmaId = {{ $firma->id }};
        
        if (!confirm(`Bu tahsilatı silmek istediğinizden emin misiniz?\n\nTahsilat Tutarı: ${paymentAmount} TL\nID: ${paymentId}\n\nBu işlem geri alınamaz!`)) {
            return false;
        }

        const $btn = $(this);
        const originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: `/${firmaId}/fatura/tahsilat-sil`,
            type: 'DELETE',
            data: {
                invoice_id: invoiceId,
                payment_id: paymentId
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    const remainingMsg = response.remaining_payments > 0 
                        ? `\n\nKalan tahsilat sayısı: ${response.remaining_payments}` 
                        : '\n\nFaturanın tüm tahsilatları silindi.';
                    
                    alert('Tahsilat başarıyla silindi!' + remainingMsg);
                    
                    if (typeof $('#datatableInvoice').DataTable === 'function') {
                        $('#datatableInvoice').DataTable().ajax.reload(null, false);
                    }
                    
                    // Edit modalını yenile
                    $.ajax({
                        url: "/" + firmaId + "/fatura/duzenle/" + invoiceId
                    }).done(function (data) {
                        if ($.trim(data) !== "-1") {
                            $('#editInvoiceModal .modal-body').html(data);
                        }
                    });
                    
                    $('#tahsilatlariGorModal').modal('hide');
                } else {
                    alert('Hata: ' + (response.message || 'Tahsilat silinemedi'));
                    $btn.prop('disabled', false).html(originalHtml);
                }
            },
            error: function(xhr) {
                let errorMsg = 'Tahsilat silinemedi.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                alert(errorMsg);
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
        
        return false;
    });
});
</script>

<script>
  $(document).ready(function() {
    $('#editInvo').on('click', '.eArsivSil', function(e) {
      e.preventDefault();
      var confirmDelete = confirm("Bu e-faturayı silmek istediğinizden emin misiniz?");
      if (confirmDelete) {
        var id = $(this).attr('data-id');
        var firma_id = {{$firma->id}};
        $.ajax({
          url: '/' + firma_id + '/eArsiv/sil/' + id,
          type: 'POST',
          data: {
            _method: 'POST', 
            _token: '{{ csrf_token() }}'
          },
          success: function(data) {
            if (data) {
              $('#datatableInvoice').DataTable().ajax.reload();
              $('#InvoiceModal').modal('hide');
              $('#editInvoiceModal').modal('hide');
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
function formatToTwoDecimalPlaces(value) {
    return Number(value).toFixed(2);
  }
  
  $(document).ready(function (e) {
    var sonucToplam = 0;
    var sonuc = 0;
        var $form = $('#editInvo');

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
        $(".tutar"+index).val(formatToTwoDecimalPlaces(sonuc))
        kdvHesapla(sonucToplam)
      });
    });

    function kdvHesapla(toplam){
      // ✅ Form içinden al
      var indirim = Number($form.find(".indirim").val()) || 0;
      var kdvTutar = Number($form.find(".kdvTutar").val()) || 20;
      var tevkifatOrani = Number($form.find(".tevkifatOrani").val()) || 0;
      var araToplam = toplam - indirim;
      var kdv = ((araToplam * kdvTutar) / 100);
      
      var tevkifatHesapla = 0;
      var genelToplam = 0;

      if (tevkifatOrani > 0) {
        tevkifatHesapla = (kdv * tevkifatOrani) / 10;
        genelToplam = araToplam + (kdv - tevkifatHesapla);
      } else {
        genelToplam = araToplam + kdv;
      }

      kdv = parseFloat(kdv.toFixed(2));
      tevkifatHesapla = parseFloat(tevkifatHesapla.toFixed(2));
      genelToplam = parseFloat(genelToplam.toFixed(2));

      // ✅ Form içine yaz
      $form.find(".toplam").val(toplam.toFixed(2));
      $form.find(".araToplam").val(araToplam.toFixed(2));
      $form.find(".kdv").val(kdv);
      $form.find(".tevkifatTutari").val(tevkifatHesapla);
      $form.find(".genelToplam").val(genelToplam);
    }

    // ✅ Event handler'ları da form-spesifik yap
    $form.find('.kdvTutar').on('keyup', function() {
      if (sonucToplam === 0) {
        return;
      }

      var indirim = Number($form.find(".indirim").val()) || 0;
      var kdvTutar = Number($form.find(".kdvTutar").val()) || 0;
      var tevkifatOrani = Number($form.find(".tevkifatOrani").val()) || 0;
      var araToplam = sonucToplam - indirim;
      var kdv = ((araToplam * kdvTutar) / 100);

      var tevkifatHesapla = 0;
      var genelToplam = 0;

      if (tevkifatOrani > 0) {
        tevkifatHesapla = (kdv * tevkifatOrani) / 10;
        genelToplam = araToplam + (kdv - tevkifatHesapla);
      } else {
        genelToplam = araToplam + kdv;
      }

      kdv = parseFloat(kdv.toFixed(2));
      tevkifatHesapla = parseFloat(tevkifatHesapla.toFixed(2));
      genelToplam = parseFloat(genelToplam.toFixed(2));

      $form.find(".araToplam").val(araToplam.toFixed(2));
      $form.find(".kdv").val(kdv);
      $form.find(".tevkifatTutari").val(tevkifatHesapla);
      $form.find(".genelToplam").val(genelToplam);
    });

    $form.find('.indirim').on('keyup', function() {
      if (sonucToplam === 0) {
        return;
      }

      var indirim = Number($form.find(".indirim").val()) || 0;
      var kdvTutar = Number($form.find(".kdvTutar").val()) || 20;
      var tevkifatOrani = Number($form.find(".tevkifatOrani").val()) || 0;
      var araToplam = sonucToplam - indirim;
      var kdv = ((araToplam * kdvTutar) / 100);

      var tevkifatHesapla = 0;
      var genelToplam = 0;

      if (tevkifatOrani > 0) {
        tevkifatHesapla = (kdv * tevkifatOrani) / 10;
        genelToplam = araToplam + (kdv - tevkifatHesapla);
      } else {
        genelToplam = araToplam + kdv;
      }

      kdv = parseFloat(kdv.toFixed(2));
      tevkifatHesapla = parseFloat(tevkifatHesapla.toFixed(2));
      genelToplam = parseFloat(genelToplam.toFixed(2));

      $form.find(".araToplam").val(araToplam.toFixed(2));
      $form.find(".kdv").val(kdv);
      $form.find(".tevkifatTutari").val(tevkifatHesapla);
      $form.find(".genelToplam").val(genelToplam);
    });

    // ✅ Tevkifat oranı değişimi
    $form.find('.tevkifatOrani').on('change', function() {
      if (sonucToplam === 0) {
        $form.find(".tevkifatTutari").val(0);
        return;
      }

      var indirim = Number($form.find(".indirim").val()) || 0;
      var kdvTutar = Number($form.find(".kdvTutar").val()) || 20;
      var tevkifatOrani = Number($(this).val()) || 0;
      var araToplam = sonucToplam - indirim;
      var kdv = ((araToplam * kdvTutar) / 100);

      var tevkifatHesapla = 0;
      var genelToplam = 0;

      if (tevkifatOrani > 0) {
        tevkifatHesapla = (kdv * tevkifatOrani) / 10;
        genelToplam = araToplam + (kdv - tevkifatHesapla);
      } else {
        genelToplam = araToplam + kdv;
      }

      kdv = parseFloat(kdv.toFixed(2));
      tevkifatHesapla = parseFloat(tevkifatHesapla.toFixed(2));
      genelToplam = parseFloat(genelToplam.toFixed(2));

      $form.find(".kdv").val(kdv);
      $form.find(".tevkifatTutari").val(tevkifatHesapla);
      $form.find(".genelToplam").val(genelToplam);
    });

    //Virgülleri nokta yapıyor
    $("input:text").keyup(function() {
      $(this).val($(this).val().replace(/[,]/g, "."));
    });
  });
</script>

<script>
  $('#editInvo').on('submit', function(e) {
  e.preventDefault(); // Her zaman engelle

  let formIsValid = true;
  $(this).find('input[required], select[required]').each(function() {
    if (!$(this).val()) {
      formIsValid = false;
      return false; // .each döngüsünü kır
    }
  });

  if (!formIsValid) {
    alert('Lütfen zorunlu alanları doldurun.');
    return;
  }

  // ↘ Eğer form geçerliyse AJAX işlemini başlat
  var formData = new FormData(this);
  $.ajax({
    url: $(this).attr("action"),
    type: "POST",
    data: formData,
    contentType: false,
    cache: false,
    processData: false,
    success: function(data) {
      if (data === false) {
        window.location.reload(true);
      } else {
        alert("Fatura güncellendi");
        $('#datatableInvoice').DataTable().ajax.reload();
        $('#editInvoiceModal').modal('hide');
      }
    },
    error: function(xhr, status, error) {
      alert("Güncelleme başarısız!");
      window.location.reload(true);
    },
  });
});
</script>

<script>
$(document).ready(function() {
    // ⭐ PDF/Durum Kontrol Butonu
    $(document).on('click', '.checkPdfBtn', function() {
        const invoiceId = $(this).data('invoice-id');
        const button = $(this);
        const originalHtml = button.html();
        const firmaId = {{ $firma->id }};
        
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Kontrol ediliyor...');
        // ✅ Timeout mekanizması ekle (15 saniye)
        const timeoutId = setTimeout(function() {
            button.prop('disabled', false).html(originalHtml);
            
            Swal.fire({
                title: 'Zaman Aşımı',
                text: 'İstek çok uzun sürdü. Paraşüt sunucusu yanıt vermiyor olabilir.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sayfayı Yenile',
                cancelButtonText: 'Kapat',
                confirmButtonColor: '#28a745'
            }).then((result) => {
                if (result.isConfirmed) {
                    reloadEditModal(invoiceId, firmaId);
                }
            });
        }, 15000); 
        $.ajax({
            url: `/${firmaId}/fatura/${invoiceId}/resmilestirme-durumu`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    // Status'a göre mesaj göster
                    if (response.status === 'sent' && response.pdf_url) {
                        // PDF hazır
                        Swal.fire({
                            title: 'PDF Hazır!',
                            html: `
                                <p>Fatura başarıyla resmileştirildi.</p>
                                ${response.invoice_number ? `<p><strong>Fatura No:</strong> ${response.invoice_number}</p>` : ''}
                            `,
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonText: '<i class="fas fa-external-link-alt"></i> PDF\'i Aç',
                            cancelButtonText: 'Kapat',
                            confirmButtonColor: '#28a745'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.open(response.pdf_url, '_blank');
                            }
                            // Edit modalını yeniden yükle
                            reloadEditModal(invoiceId, firmaId);
                        });
                    } else if (response.status === 'sent' && !response.pdf_url) {
                        // Gönderildi ama PDF henüz hazır değil
                        Swal.fire({
                            title: 'İşlem Tamamlandı',
                            text: 'Fatura resmileştirildi ancak PDF henüz hazırlanıyor. Lütfen birkaç dakika sonra tekrar deneyin.',
                            icon: 'info'
                        });
                        button.prop('disabled', false).html(originalHtml);
                    } else if (response.status === 'pending') {
                        Swal.fire({
                            title: 'İşlem Devam Ediyor',
                            text: 'Resmileştirme işlemi henüz tamamlanmadı. Lütfen birkaç dakika sonra tekrar kontrol edin.',
                            icon: 'warning'
                        });
                        button.prop('disabled', false).html(originalHtml);
                    } else if (response.status === 'error') {
                        Swal.fire({
                            title: 'Hata',
                            text: 'Resmileştirme işleminde hata oluştu.',
                            icon: 'error'
                        });
                        button.prop('disabled', false).html(originalHtml);
                    }
                } else {
                    Swal.fire('Hata', response.message || 'Durum kontrol edilemedi', 'error');
                    button.prop('disabled', false).html(originalHtml);
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Durum kontrol edilemedi';
                Swal.fire('Hata', errorMsg, 'error');
                button.prop('disabled', false).html(originalHtml);
            }
        });
    });
    
    // Helper function - Edit modalını yeniden yükle
    function reloadEditModal(invoiceId, firmaId) {
        $.ajax({
            url: "/" + firmaId + "/fatura/duzenle/" + invoiceId
        }).done(function (data) {
            if ($.trim(data) !== "-1") {
                $('#editInvoiceModal .modal-body').html(data);
            }
        });
        
        // DataTable'ı da yenile
        if (typeof $('#datatableInvoice').DataTable === 'function') {
            $('#datatableInvoice').DataTable().ajax.reload(null, false);
        }
    }
});
</script>
<script>
$(document).ready(function() {
    var $form = $('#editInvo');
    var sonucToplam = 0;
    
    // Sayfa yüklendiğinde mevcut toplamı hesapla
    setTimeout(function() {
        sonucToplam = 0;
        $form.find('.miktar').each(function(index) {
            var fiyat = parseFloat($form.find(".fiyat").eq(index).val()) || 0;
            var miktar = parseFloat($(this).val()) || 0;
            sonucToplam += fiyat * miktar;
        });
    }, 500);
    
    // ⭐ Genel hesaplama fonksiyonu
    function hesaplaGenelToplam() {
    // Önce satır toplamını hesapla
    sonucToplam = 0;
    
    // ✅ Düzeltilmiş: index bazlı class kullanımı (fiyat0, fiyat1, miktar0, miktar1 vb.)
    var satirSayisi = $form.find('.satirBody .row.form-group').length;
    
    for (var i = 0; i < satirSayisi; i++) {
        var fiyat = parseFloat($form.find(".fiyat" + i).val()) || 0;
        var miktar = parseFloat($form.find(".miktar" + i).val()) || 0;
        var tutar = fiyat * miktar;
        sonucToplam += tutar;
        $form.find(".tutar" + i).val(tutar.toFixed(2));
    }
        var toplam = sonucToplam;
        var indirim = parseFloat($form.find(".indirim").val()) || 0;
        var kdvOraniVal = $form.find(".kdvTutar").val();
        var kdvOrani = (kdvOraniVal !== '' && kdvOraniVal !== null) ? parseFloat(kdvOraniVal) : 20;
        var tevkifatOrani = parseInt($form.find(".tevkifatOrani").val()) || 0;
        
        var araToplam = toplam - indirim;
        var kdvTutari = (araToplam * kdvOrani) / 100;
        
        var tevkifatTutari = 0;
        var genelToplam = 0;
        
        if (tevkifatOrani > 0) {
            tevkifatTutari = (kdvTutari * tevkifatOrani) / 10;
            genelToplam = araToplam + (kdvTutari - tevkifatTutari);
        } else {
            genelToplam = araToplam + kdvTutari;
        }
        
        // Değerleri yuvarla
        kdvTutari = parseFloat(kdvTutari.toFixed(2));
        tevkifatTutari = parseFloat(tevkifatTutari.toFixed(2));
        genelToplam = parseFloat(genelToplam.toFixed(2));
        
        // ✅ Tüm alanları güncelle
        $form.find(".toplam").val(toplam.toFixed(2));
        $form.find(".araToplam").val(araToplam.toFixed(2));
        $form.find(".kdv").val(kdvTutari.toFixed(2));                    // KDV tutarı
        $form.find(".tevkifatTutari").val(tevkifatTutari.toFixed(2));    // Hidden input
        $form.find(".tevkifatTutariGoster").val(tevkifatTutari.toFixed(2)); // Görünen input
        $form.find(".genelToplam").val(genelToplam.toFixed(2));
        
        console.log('Hesaplama yapıldı:', {
            toplam: toplam,
            indirim: indirim,
            araToplam: araToplam,
            kdvOrani: kdvOrani,
            kdvTutari: kdvTutari,
            tevkifatOrani: tevkifatOrani,
            tevkifatTutari: tevkifatTutari,
            genelToplam: genelToplam
        });
    }
    
    // ⭐ KDV Kodu (İstisna) değiştiğinde
    $form.find('.kdvKodu').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const kdvOrani = selectedOption.data('kdv-oran');
        
        console.log('KDV Kodu değişti:', {
            kod: $(this).val(),
            oran: kdvOrani
        });
        
        if (kdvOrani !== undefined && kdvOrani !== '' && kdvOrani !== null) {
            const yeniOran = parseInt(kdvOrani);
            const $kdvTutarInput = $form.find('.kdvTutar');
            const currentKdv = parseInt($kdvTutarInput.val()) || 20;
            
            if (yeniOran !== currentKdv) {
                Swal.fire({
                    title: 'KDV Oranı Güncellendi',
                    html: `Seçilen KDV istisna kodu için KDV oranı <strong>%${yeniOran}</strong> olarak ayarlandı.`,
                    icon: 'info',
                    confirmButtonText: 'Tamam'
                });
                
                // ✅ KDV oranını güncelle
                $kdvTutarInput.val(yeniOran);
            }
            
            // ✅ Her durumda hesaplamayı yenile
            hesaplaGenelToplam();
        } else {
            // KDV istisnası kaldırıldı, varsayılan %20'ye dön
            $form.find('.kdvTutar').val(20);
            hesaplaGenelToplam();
        }
    });
    
    // ⭐ KDV Oranı manuel değiştiğinde
    $form.find('.kdvTutar').on('change keyup', function() {
        hesaplaGenelToplam();
    });
    
    // ⭐ Tevkifat Kodu değiştiğinde
    $form.find('.tevkifatKodu').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const tevkifatOrani = parseInt(selectedOption.data('oran')) || 0;
        const $tevkifatOraniSelect = $form.find('.tevkifatOrani');
        const currentOran = parseInt($tevkifatOraniSelect.val()) || 0;
        
        console.log('Tevkifat Kodu değişti:', {
            kod: $(this).val(),
            oran: tevkifatOrani
        });
        
        if (tevkifatOrani > 0) {
            if (currentOran !== tevkifatOrani) {
                Swal.fire({
                    title: 'Tevkifat Oranı Güncellendi',
                    html: `Seçilen tevkifat kodu için oran <strong>${tevkifatOrani}/10</strong> olarak ayarlandı.`,
                    icon: 'info',
                    confirmButtonText: 'Tamam'
                });
                
                $tevkifatOraniSelect.val(tevkifatOrani);
            }
        } else {
            $tevkifatOraniSelect.val(0);
        }
        
        // ✅ Hesaplamayı yenile
        hesaplaGenelToplam();
    });
    
    // ⭐ Tevkifat Oranı manuel değiştirildiğinde
    $form.find('.tevkifatOrani').on('change', function() {
        const selectedOran = parseInt($(this).val()) || 0;
        const $tevkifatKoduSelect = $form.find('.tevkifatKodu');
        const selectedKodu = $tevkifatKoduSelect.val();
        
        if (selectedKodu) {
            const koduOrani = parseInt($tevkifatKoduSelect.find('option:selected').data('oran')) || 0;
            
            if (selectedOran !== koduOrani && selectedOran > 0) {
                Swal.fire({
                    title: 'Uyarı!',
                    html: `Seçilen tevkifat kodu için doğru oran <strong>${koduOrani}/10</strong> olmalıdır.<br><br>Mevcut seçiminiz: <strong>${selectedOran}/10</strong>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Doğru Oranı Kullan',
                    cancelButtonText: 'Bu Şekilde Kalsın',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(this).val(koduOrani);
                        hesaplaGenelToplam();
                    }
                });
            }
        }
        
        // ✅ Hesaplamayı yenile
        hesaplaGenelToplam();
    });
    
    // ⭐ İndirim değiştiğinde
    $form.find('.indirim').on('keyup change', function() {
        hesaplaGenelToplam();
    });
    
    // ⭐ Satır değerleri değiştiğinde
    $form.find('.satirBody').on('keyup change', '.miktar, .fiyat', function() {
        hesaplaGenelToplam();
    });
    
    // ⭐ Form submit öncesi kontrol
    $form.on('submit', function(e) {
        const tevkifatKodu = $form.find('.tevkifatKodu').val();
        const tevkifatOrani = parseInt($form.find('.tevkifatOrani').val()) || 0;
        
        if (tevkifatKodu) {
            const koduOrani = parseInt($form.find('.tevkifatKodu option:selected').data('oran')) || 0;
            
            if (tevkifatOrani !== koduOrani) {
                e.preventDefault();
                
                Swal.fire({
                    title: 'Hata!',
                    html: `Tevkifat kodu (${tevkifatKodu}) için oran <strong>${koduOrani}/10</strong> olmalıdır.<br><br>Lütfen oranı düzeltin veya tevkifat kodunu kaldırın.`,
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                });
                
                return false;
            }
        }
        
        if (tevkifatOrani > 0 && !tevkifatKodu) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Uyarı!',
                text: 'Tevkifat oranı seçtiniz ancak tevkifat kodu seçmediniz. Lütfen ilgili tevkifat kodunu seçin.',
                icon: 'warning',
                confirmButtonText: 'Tamam'
            });
            
            return false;
        }
    });
});
</script>