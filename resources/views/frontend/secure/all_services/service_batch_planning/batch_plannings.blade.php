<div class="row wrap" id="batchPlanning">
  <div class="col-lg-3 custom-service-r sol">
    <div id="planlamaSearch" class=" show">
      <div class="card" style="margin-bottom:0!important;">
        <div class="card-header" style="padding:0px 5px!important;">
          <div class="card-body" style="padding:0px 5px!important;">
          <form id="filterForm">
            <div class="row form-group">
              <div class="col-md-4 rw1"><label>Tarih</label></div>
              <div class="col-md-8  custom-p-min rw2">
                <input type="date" class="form-control datepicker planTarih" value="{{ $tomorrow }}" style="background:#fff">
              </div>
            </div>

            <div class="row form-group">
              <div class="col-md-4 rw1"><label>İl Seç</label></div>
              <div class="col-md-8 custom-p-min  rw2">
                <select class="form-control il" id="il">
                  @foreach ($iller as $item)
                    <option value="{{$item->id}}" {{ $item->id == 34 ? 'selected' : '' }}>{{$item->name}}</option>
                  @endforeach                               
                </select>
              </div>
            </div>

            <div class="row form-group">
              <div class="col-md-4 rw1"><label>Bölgeler</label></div>
              <div class="col-md-8  custom-p-min rw2">
                <select class="form-control bolgeler" id="ilce" multiple style="height: 155px">
                  <option value="0" selected>HEPSİ</option>
                </select>
              </div>
            </div>

            <div class="row form-group">
              <div class="col-md-4 rw1"><label>Cihazlar</label></div>
              <div class="col-md-8  custom-p-min rw2">
                <select class="form-control cihazlar" multiple style="height: 155px">
                  <option value="0" selected>HEPSİ</option>
                  @foreach($deviceTypes as $device)
                    <option style="text-transform: uppercase;" value="{{ $device->id }}">{{ $device->cihaz }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="row form-group">
              <div class="col-md-4 rw1"><label>Kaynaklar</label></div>
              <div class="col-md-8 rw2  custom-p-min">
                <select class="form-control kaynaklar" multiple style="height: 100px">
                  <option value="0" selected>HEPSİ</option>
                  @foreach($serviceSources as $source)
                    <option style="text-transform: uppercase;" value="{{ $source->id }}">{{ $source->kaynak }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="row form-group">
              <div class="col-md-4 rw1"><label>Durumlar</label></div>
              <div class="col-md-8 custom-p-min rw2">
                <select class="form-control durumlar">
                  <option value="240">Atölyeye Aldır (Nakliye Gönder)</option>
                  <option value="264">Bayiye Gönder</option>
                  <option value="237">Cihaz Atölyeye Alındı</option>
                  <option value="246">Cihaz Tamir Edilemiyor</option>
                  <option value="261">Parça Hazır</option>
                  <option value="254">Şikayetçi</option>
                  <option value="252">Teslimata Hazır (Tamamlandı)</option>
                  <option value="235" selected>Yeni Servisler</option>
                  <option value="235-2">Yeni Servisler (Bayiye Gönder)</option>
                  <option value="248">Yeniden Teknisyen Yönlendir</option>
                </select>
              </div>
            </div>

            <div class="col-md-12">
              <input type="submit" class="btn btn-block btn-primary btn-sm servisPlanListele" style="width:100%;" value="Listele">
            </div>
          </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-9 sag">
    <div class="card" style="margin-bottom: 0!important;">
      <div class="servisListe" style="padding: 0">
        <!-- Service list will be loaded here -->
      </div>         
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    // Load districts on city change
    $("#il").on("change", function() {
        loadDistricts($(this).val());
    });

    // Load initial districts
    loadDistricts($("#il").val());

    // Load initial service list
    loadServiceList();

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

    function loadServiceList() {
      var formData = {
        planTarih: $(".planTarih").val().replace(/\//g, '-'),
        il: $(".il").val(),
        bolgeler: $(".bolgeler").val(),
        cihazlar: $(".cihazlar").val(),
        kaynaklar: $(".kaynaklar").val(),
        durumlar: $(".durumlar").val()
      };

      $(".servisListe").html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Yükleniyor...</div>');
      $.ajax({
        url: "{{ route('service.list', $firma->id) }}",
        type: "GET",
        data: formData,
        success: function(data) {
          $(".servisListe").html(data);
        },
        error: function() {
          $(".servisListe").html('<div class="alert alert-danger">Bir hata oluştu!</div>');
        }
      });
    }

    // Responsive collapse
    if ($(window).width() < 992) {
      $("#planlamaSearch").removeClass("show");
    }

    // *** YENİ EKLENEN KOD - MODAL SORUNU ÇÖZÜMÜ ***
    // Modal kontrol fonksiyonu
    function checkAndRestoreServiceList() {

    // Eğer herhangi bir modal açıksa servis listesini yenileme
    if ($('.modal.show').length > 0) {
      return;
    }
    
    // Özellikle bu modallar açıksa kesinlikle yenileme
    if ($('#personelServisDuzenleModal').hasClass('show') || 
        $('#servisPersonelAtamaModal').hasClass('show') ||
        $('#addServiceModal').hasClass('show') ||
        $('#editServiceDescModal').hasClass('show')) {
      return;
    }
    
    if ($('.servisListe').children().length === 0 || 
        $('.servisListe').html().trim() === '' ||
        $('.servisListe').html().includes('Yükleniyor...')) {
      loadServiceList();
    }
  }

    // Toplu planlama modal açılmadan önce kontrol
    $(document).on('click', '.servisPlanlaBtn', function() {
      checkAndRestoreServiceList();
    });

    // Diğer modaller kapandığında servis listesini kontrol et
  //   $(document).on('hidden.bs.modal', '.modal', function() {
  //     var modalId = $(this).attr('id');
  //     if (modalId && modalId !== 'servisTopluPlanlaModal') {
  //       setTimeout(function() {
  //         checkAndRestoreServiceList();
  //       }, 300);
  //     }

  //     if ($('.modal.show').length === 0) {
  //   setTimeout(function() {
  //     checkAndRestoreServiceList();
  //   }, 400);
  // }
  //   });

    // Toplu planlama modalı açıldıktan sonra kontrol
    // $(document).on('shown.bs.modal', '#servisTopluPlanlaModal', function() {
    //   setTimeout(function() {
    //     checkAndRestoreServiceList();
    //   }, 100);
    // });
  });
</script>