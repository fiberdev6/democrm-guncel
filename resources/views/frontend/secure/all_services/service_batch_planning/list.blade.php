<div id="servisBatchListe">
<div class="card-header cardBaslik" style="padding: 5px 10px;font-size: 14px">
  @if(!empty($persID))
    @php $selectedPersonnel = $personeller->firstWhere('user_id', $persID) @endphp
    {{ 'Bugün Atanan' . $selectedPersonnel ? $selectedPersonnel->name . ' Servisleri' : 'Personel Servisleri' }}
  @else
    {{ request('planTarih') }} - Servisler
  @endif
  ({{ $services->count() }})
</div>
<div class="card-body" style="padding: 0!important;height: 450px;overflow: auto;">
  <table class="table table-hover table-striped" id="serviceTable" width="100%" cellspacing="0">
    <thead class="title">
      <tr>
        <th>Seç</th>
        <th>ID</th>
        <th>Müşteri Adı</th>
        <th>İlçe</th>
        <th>Cihaz</th>
        <th>Arıza</th>
      </tr>
    </thead>
    <tbody>
      @forelse($services as $service)
        <tr>
          <td style="vertical-align: middle; padding: 6px;cursor:pointer;"><input type="checkbox" class="selectService"
              value="{{ $service->id }}"></td>
          <td style="vertical-align: middle;font-size: 11px; padding: 3px 10px;cursor:pointer;">
            <strong>{{ $service->id }}</strong></td>
          <td style="vertical-align: middle;font-size: 11px; padding: 3px 10px;cursor:pointer;"
            class="personelServisDuzenle" data-bs-id="{{$service->id}}"
            data-bs-name="{{ $service->musteri->adSoyad ?? '-' }}">
            <strong>{{ \Illuminate\Support\Str::upper($service->musteri->adSoyad ?? '-') }}</strong></td>
          <td style="vertical-align: middle;font-size: 11px; padding: 3px 10px;cursor:pointer;"
            class="personelServisDuzenle" data-bs-id="{{$service->id}}"
            data-bs-name="{{ $service->musteri->adSoyad ?? '-' }}">
            <strong>{{ \Illuminate\Support\Str::upper($service->musteri->state->ilceName ?? '-') }}</strong></td>
          <td style="vertical-align: middle;font-size: 11px; padding: 3px 10px;cursor:pointer;"
            class="personelServisDuzenle" data-bs-id="{{$service->id}}"
            data-bs-name="{{ $service->musteri->adSoyad ?? '-' }}">
            <strong>{{ \Illuminate\Support\Str::upper($service->markaCihaz->marka ?? '-') }},
              {{ \Illuminate\Support\Str::upper($service->turCihaz->cihaz ?? '-') }}</strong></td>
          <td style="vertical-align: middle;font-size: 11px; padding: 3px 10px;cursor:pointer;"
            class="personelServisDuzenle" data-bs-id="{{$service->id}}"
            data-bs-name="{{ $service->musteri->adSoyad ?? '-' }}">
            <strong>{{ \Illuminate\Support\Str::upper($service->cihazAriza ?? '-') }}</strong></td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="text-center">Kayıt bulunamadı</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="card-footer card-footer-service" style="padding: 0px 10px;font-size: 14px">
  <div class="row align-items-center">

    {{-- Sol Sütun: Personel Seçimi ve Servisleri Göster Butonu --}}
    <div class="col-md-8 custom-p-r-min">
      <div class="form-group row mb-0"> {{-- mb-0 alt boşluğu kaldırır --}}
        <label for="personel" class="col-md-2 col-form-label">Personel</label>
        <div class="col-md-6 col-form-label">
          <select id="personel" class="form-control personelList">
            @foreach ($personeller as $pers)
              @php
                $count = $personelAtamaSayilari[$pers->user_id] ?? 0;
              @endphp
              <option value="{{ $pers->user_id }}">{{ $pers->name }} ({{ $count }})</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4 custom-p-min col-form-label ">
          <button type="button"
            class=" btn btn-primary btn-block btn-sm personelServisListele">
            <div class="d-flex  justify-content-center align-items-center text gap-2">
              <span>Servisleri Göster</span>
              <i class="fas fa-info-circle d-none d-md-inline" data-toggle="tooltip"
                title="Servisleri göster butonu solda seçilen personele ait sadece bugün atanan servisleri listelemeye yaramaktadır.">
              </i>
            </div>
          </button>
        </div>
      </div>
    </div>

    {{-- Sağ Sütun: Atama Yap Butonu --}}
    <div class="col-md-4 custom-p-min custom-p-r-min">
      @php
        use App\Models\User;

        // 1) gelenDurum dropdown’daki değer
        $gelenDurum = $statuses;
        // 2) varsayılan gidenDurum haritası
        $map = [
          '237' => 250,
          '245' => 250,
          '252' => 251,
          '246' => 251,
          '240' => 262,
          '235-2' => 264,
          '264' => 264,
        ];
        $gidenDurum = $map[$gelenDurum] ?? 236;   // default teknisyen yönlendir

        // 3) personele özel kural
        $dataPers = null;
        if (!empty($persID)) {
          $dataPers = $persID;
          $perSec = User::find($persID);
          if ($perSec->hasAnyRole(['Atölye Ustası', 'Atölye Çırak'])) {
            $gidenDurum = 250;
          } else {
            $gidenDurum = 236;
          }
        }
      @endphp
      {{-- mt-1 (margin-top) sınıfı kaldırıldı --}}
      <button id="assignBtn" class="btn btn-success btn-sm atamaBtn" data-id="{{ $gidenDurum }}" @if($dataPers)
      data-pers="{{ $dataPers }}" @endif>Atama Yap 
        <i class="fas fa-info-circle d-none d-md-inline" data-toggle="tooltip"
                title="Atama yap butonu listeleme yaptığınız servislere personel atamak için kullanılır. Atama yaparken belirli bir tarih seçilmediği sürece otomatik yarına atama yapmaktadır.">
              </i>
    </button>
    </div>
  </div>

</div>
</div>

<div id="servisPersonelAtamaModal" class="modal fade" style="padding-top: 50px;background: rgba(0, 0, 0, 0.50);">
  <div class="modal-dialog ">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title">Servis Planlama</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Yükleniyor...
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="personelServisDuzenleModal" class="modal fade" data-bs-backdrop="static" tabindex='-1'
  style="padding-top: 20px;background: rgba(0, 0, 0, 0.50);"> {{--data-bs-backdrop="static" data-bs-keyboard="false"
  modalın hemen kapanmaması için bunu eklemiştim. Eğer eklenmesi gerekirse aria-hidden in yanına ekleyebilirsin--}}
  <div class="modal-dialog modal-lg">
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

<script type="text/javascript">
  $(document).ready(function () {
    $(".personelServisDuzenle").click(function (e) {
      var id = $(this).attr("data-bs-id");
      var name = $(this).attr("data-bs-name");
      var firma_id = {{$firma->id}};
      $.ajax({
        url: "/" + firma_id + "/servis/duzenle/" + id
      }).done(function (data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#personelServisDuzenleModal').modal('show');
          $('#personelServisDuzenleModal .modal-title').html(name + " (" + id + ")");
          $('#personelServisDuzenleModal .modal-body').html(data);

        }
      });
    });
  });
</script>

<script>
  $(function () {
    // firma_id'yi JavaScript'te erişilebilir yapın
    const firmaId = {{ $firma->id }};

    // Personel servisleri listeleme
    $('.personelServisListele').on('click', function () {
      // Seçili personelin ID'sini alın
      const persID = $('.personelList').val();

      if (!persID) {
        alert('Lütfen bir personel seçin!');
        return;
      }

      $('.servisListe').html('<div class="text-center p-3"><i class="fa fa-spinner fa-spin"></i> Yükleniyor...</div>');

      $.ajax({
        // URL'yi doğru bir şekilde template literal kullanarak oluşturun
        url: `/${firmaId}/servis-liste-getir/`,
        method: 'GET',
        data: { persID: persID }, // persID'yi açıkça gönderin
        success: function (res) {
          $('.servisListe').html(res);
        },
        error: function () {
          $('.servisListe').html('<div class="alert alert-danger">Liste alınamadı. Lütfen tekrar deneyin.</div></div>');
        }
      });
    });
  });
</script>

<script>
  $(function () {
    /* === Atama Yap === */
    $('#assignBtn').click(function () {
      /* Seçili servisler */
      const ids = $('input.selectService:checked').map((_, e) => e.value).get();
      if (!ids.length) { alert('Servis seçiniz'); return; }
      const servisidler = ids.join(',');
      const gidenDurum = $(this).data('id');
      const personelID = $(this).data('pers') || null;
      const gelenDurum = $('.durumlar').val();
      const tenantID = {{ $firma->id }};

      /* ----- personel varsa: doğrudan güncelle ----- */
      if (personelID) {
        $.get("{{ route('service.plan.update.form', $firma->id) }}",
          { servisidler, personel: personelID, gidenDurum })
          .done(html => {
            $('#servisPersonelAtamaModal .modal-body').html(html);
            $('#servisPersonelAtamaModal').modal('show');
            $('#datatableService').DataTable().ajax.reload();
          });
        return;
      }

      /* ----- personel yok: planlama formu ----- */
      $('#servisPersonelAtamaModal .modal-body')
        .html('<div class="text-center p-3"><i class="fa fa-spinner fa-spin"></i> Form yükleniyor…</div>');
      $('#servisPersonelAtamaModal').modal('show');

      $.get("{{ route('service.plan.form', $firma->id) }}",
        { servisidler, gelenDurum, gidenDurum })
        .done(html => $('#servisPersonelAtamaModal .modal-body').html(html))
        .fail(() => $('#servisPersonelAtamaModal .modal-body')
          .html('<div class="alert alert-danger">Form yüklenemedi.</div>'));
    });
  });
</script>
<script>
  // Sayfadaki tüm tooltip'leri etkinleştirmek için bu kodu kullanın.
  // Bu, Bootstrap 5 için standart yöntemdir.
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  })
</script>