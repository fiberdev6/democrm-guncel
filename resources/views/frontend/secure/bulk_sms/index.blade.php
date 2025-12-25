@extends('frontend.secure.user_master')
@section('user')

<div class="page-content" id="cash_transactions">
<div class="container-fluid">
    <div class="card mb-3 pageDetail smsPage">
        <div class="card-header">Toplu SMS</div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-4 sol">  
                    <div id="planlamaSearch" class="">
                        <div class="card">
                            <div class="card-header" style="padding: 5px;">
                                <form id="filtreForm">
                                    <!-- Tarih Aralığı -->
                                    <div class="row form-group">
                                        <div class="col-md-4 rw1"><label>Tarih Aralığı</label></div>
                                        <div class="col-md-8 rw2">
                                            <input type="text" name="tarih1" class="form-control datepicker tarih1" readonly 
                                                value="{{ now()->subMonth() }}" style="background:#fff;margin-bottom: 3px;">
                                            <input type="text" name="tarih2" class="form-control datepicker tarih2" readonly 
                                                value="{{ now() }}" style="background:#fff;margin-bottom: 2px;">
                                            
                                            <div class="tarihAraliklari" style="margin-top: 5px">
                                                <button type="button" class="btn btn-primary btn-sm tarihDegistirBtn" 
                                                        data-tarih1="{{ now()->subYear()->format('Y-m-d') }}" 
                                                        data-tarih2="{{ now()->format('Y-m-d') }}" 
                                                        style="padding: 0 5px">Son 1 Yıl</button>
                                                <button type="button" class="btn btn-primary btn-sm tarihDegistirBtn" 
                                                        data-tarih1="{{ now()->subMonth()->format('Y-m-d') }}" 
                                                        data-tarih2="{{ now()->format('Y-m-d') }}" 
                                                        style="padding: 0 5px">Son 1 Ay</button>
                                                <button type="button" class="btn btn-primary btn-sm tarihDegistirBtn" 
                                                        data-tarih1="{{ now()->subDays(7)->format('Y-m-d') }}" 
                                                        data-tarih2="{{ now()->format('Y-m-d') }}" 
                                                        style="padding: 0 5px">Son 7 Gün</button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- İl Seçimi -->
                                    <div class="row form-group">
                                        <div class="col-md-4 rw1"><label>İl Seç</label></div>
                                        <div class="col-md-8 rw2">
                                            <select class="form-control il" id="il" name="il">
                                                @foreach ($iller as $item)
                                                    <option value="{{$item->id}}" {{ $item->id == 34 ? 'selected' : '' }}>{{$item->name}}</option>
                                                @endforeach 
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Bölgeler -->
                                    <div class="row form-group">
                                        <div class="col-md-4 rw1"><label>Bölgeler</label></div>
                                        <div class="col-md-8 rw2">
                                            <select class="form-control bolgeler" id="ilce" multiple style="height: 155px">
                                                <option value="0" selected>HEPSİ</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Markalar -->
                                    <div class="row form-group">
                                        <div class="col-md-4 rw1"><label>Markalar</label></div>
                                        <div class="col-md-8 rw2">
                                            <select class="form-control markalar" name="markalar[]" multiple style="height: 170px">
                                                <option value="0" selected>HEPSİ</option>
                                                @foreach($markalar as $marka)
                                                    <option value="{{ $marka->id }}">{{ $marka->marka }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Cihazlar -->
                                    <div class="row form-group">
                                        <div class="col-md-4 rw1"><label>Cihazlar</label></div>
                                        <div class="col-md-8 rw2">
                                            <select class="form-control cihazlar" name="cihazlar[]" multiple style="height: 170px">
                                                <option value="0" selected>HEPSİ</option>
                                                @foreach($cihazlar as $cihaz)
                                                    <option value="{{ $cihaz->id }}">{{ $cihaz->cihaz }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Kaynaklar -->
                                    <div class="row form-group">
                                        <div class="col-md-4 rw1"><label>Kaynaklar</label></div>
                                        <div class="col-md-8 rw2">
                                            <select class="form-control kaynaklar" name="kaynaklar[]" multiple style="height: 100px">
                                                <option value="0" selected>HEPSİ</option>
                                                @foreach($servisKaynaklari as $kaynak)
                                                    <option value="{{ $kaynak->id }}">{{ $kaynak->kaynak }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Durumlar -->
                                    <div class="row form-group">
                                        <div class="col-md-4 rw1"><label>Durumlar</label></div>
                                        <div class="col-md-8 rw2">
                                            <select class="form-control durumlar" name="durumlar">
                                                <option value="0" selected>HEPSİ</option>
                                                @foreach($servisAsamalari as $asama)
                                                    <option value="{{ $asama->id }}">{{ $asama->asama }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12" style="padding: 5px">
                                        <button type="button" class="btn btn-primary btn-block btn-sm topluSmsListele" >Listele</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 sag">
                    <div class="servisListe"></div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Servis Düzenle Modal -->
<div id="personelServisDuzenleModal" class="modal fade" data-bs-backdrop="static" tabindex='-1'
  style="padding-top: 20px;background: rgba(0, 0, 0, 0.50);"> {{--data-bs-backdrop="static" data-bs-keyboard="false"
  modalın hemen kapanmaması için bunu eklemiştim. Eğer eklenmesi gerekirse aria-hidden in yanına ekleyebilirsin--}}
  <div class="modal-dialog modal-lg" style="width: 980px;">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="editCustomerLabel">Servis Düzenle</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Yükleniyor...
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- SMS Provider Seçim Modal -->
<div id="smsProviderModal" class="modal fade" data-bs-backdrop="static" tabindex='-1'>
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h6 class="modal-title">
            <i class="fas fa-sms me-2"></i>SMS Sağlayıcı Seçimi
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="padding: 20px;">
        <div class="alert alert-info" style="margin-bottom: 15px;">
            <i class="fas fa-info-circle me-2"></i>
            Birden fazla aktif SMS entegrasyonunuz bulunmaktadır. 
            Lütfen mesajları göndermek için kullanmak istediğiniz entegrasyonu seçin.
        </div>
        
        <div class="form-group">
            <label style="font-weight: 600; margin-bottom: 10px;">SMS Entegrasyonu:</label>
            <select class="form-control form-control-lg" id="selectedSmsProvider" style="font-size: 14px;">
                <!-- JavaScript ile doldurulacak -->
            </select>
        </div>

        <div id="providerInfo" class="mt-3" style="display: none;">
            <div class="card bg-light">
                <div class="card-body" style="padding: 10px;">
                    <small class="text-muted">
                        <i class="fas fa-check-circle text-success me-1"></i>
                        <span id="providerInfoText"></span>
                    </small>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>İptal
        </button>
        <button type="button" class="btn btn-success" id="confirmSmsProvider">
            <i class="fas fa-paper-plane me-1"></i>Devam Et ve Gönder
        </button>
      </div>
    </div>
  </div>
</div>



<script>
$(document).ready(function () {
    window.smsIntegrations = @json($smsIntegrations);
    window.selectedProviderId = null;
    window.pendingSmsData = null;

    var width = $(window).width();
    if(width < 992){
        $("#planlamaSearch").removeClass("show");
    }

    // Tarih değiştir butonları
    $(".tarihDegistirBtn").click(function(){
        var tarih1 = $(this).attr('data-tarih1');
        var tarih2 = $(this).attr('data-tarih2');
        $(".tarih1").val(tarih1);
        $(".tarih2").val(tarih2);
    });

    // İl değiştiğinde ilçeleri getir
    $("#il").on("change", function() {
        loadDistricts($(this).val());
    });

    // Load initial districts
    loadDistricts($("#il").val());

    // Filter form submission
    $("#filterForm").on("submit", function(e) {
        e.preventDefault();
        loadServiceList();
    });

    function loadDistricts(cityId) {
      $('#ilce').html('<option value="0" selected>HEPSİ</option>');

      $.get('{{ route('service.districts', $firma->id) }}',
          { city_id: cityId },
          function (districts) {
              districts.forEach(d =>
                  $('#ilce').append(
                      `<option value="${d.id}">${d.ilceName}</option>`
                  )
              );
          }
      ).fail(function (xhr) {                                
          console.error('Hata', xhr.status, xhr.responseText);
      });
    }

    // Listele butonu
    $(".topluSmsListele").click(function(){
        if(width < 992){
            $(".planlamaSearchBtn").click();
        }

        var formData = $("#filtreForm").serializeArray();
        var data = {};
        
        $.each(formData, function(i, field){
            if(field.name.includes('[]')){
                var name = field.name.replace('[]', '');
                if(!data[name]) data[name] = [];
                data[name].push(field.value);
            } else {
                data[field.name] = field.value;
            }
        });


        $.ajax({
            url: "{{ route('toplu-sms.listele', $firma->id) }}",
            type: "GET",
            data: {
                _token: "{{ csrf_token() }}",
                ...data
            },
            success: function(response) {
                $('.servisListe').html(response);
            },
            error: function() {
                $('.servisListe').html('<div class="alert alert-danger">Bir hata oluştu</div>');
            }
        });
    });

     // Provider seçimi değiştiğinde bilgi göster 
    $(document).on('change', '#selectedSmsProvider', function(){
        var selectedId = $(this).val();
        var selectedProvider = window.smsIntegrations.find(p => p.purchase_id == selectedId);
        
        if(selectedProvider){
            $('#providerInfo').show();
            $('#providerInfoText').text('Seçilen: ' + selectedProvider.name);
        }
    });

    // Provider seçimini onayla 
    $(document).on('click', '#confirmSmsProvider', function(){
        window.selectedProviderId = $('#selectedSmsProvider').val();
        
        if(!window.selectedProviderId){
            alert('Lütfen bir SMS sağlayıcı seçin.');
            return;
        }

        // Modal'ı kapat
        $('#smsProviderModal').modal('hide');

        // Bekleyen SMS verisini gönder
        if(window.pendingSmsData){
            sendSmsWithProvider(window.pendingSmsData, window.selectedProviderId);
        }
    });
});
</script>
@endsection