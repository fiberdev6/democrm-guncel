<form method="POST" id="topluPlanKaydet" action="{{route('service.assign', $tenant_id)}}" style="padding:0 10px;">
   @csrf 

  @foreach ($questions as $question)
    <div class="row form-group">
      <div class="col-lg-4"><label>{{ $question->soru }}</label></div>
      <div class="col-lg-8">
        @if ($question->cevapTuru == "[Aciklama]")
          <input type="text" name="soru{{ $question->id }}" class="form-control" />
        @elseif (Str::contains($question->cevapTuru, "Grup")) {{-- Str::contains kullanıldı --}}
          <select class="form-control" name="soru{{ $question->id }}" required>
            <option value="">-Seçiniz-</option>
           
            @php
              $filteredPersonnel = App\Models\User::where('tenant_id', $tenant_id)
                ->where('status', '1')
                ->whereHas('roles', function($query) {
                  $query->whereIn('name', ['Teknisyen', 'Teknisyen Yardımcısı', 'Atölye Çırak', 'Atölye Ustası']);
                })
                ->with('roles') // roles ilişkisini önceden yükle
                ->orderBy('name', 'asc')
                ->get();
            @endphp

            @foreach ($filteredPersonnel as $person)
              <option value="{{ $person->user_id }}">{{ $person->name }}</option>
            @endforeach
          </select>
        @elseif ($question->cevapTuru == "[Tarih]")
          <input type="date" name="soru{{ $question->id }}" required class="form-control datepicker" value="{{ $defaultDateFormatted }}" style="background:#fff;">
        @elseif ($question->cevapTuru == "[Saat]")
          <select class="form-control" name="soru{{ $question->id }}">
            <option value="">-Seçiniz-</option>
            <option value="08:00-10:00">08:00-10:00</option>
            <option value="09:00-11:00">09:00-11:00</option>
            <option value="10:00-12:00">10:00-12:00</option>
            <option value="11:00-13:00">11:00-13:00</option>
            <option value="12:00-14:00">12:00-14:00</option>
            <option value="13:00-15:00">13:00-15:00</option>
            <option value="14:00-16:00">14:00-16:00</option>
            <option value="15:00-17:00">15:00-17:00</option>
            <option value="16:00-18:00">16:00-18:00</option>
            <option value="17:00-19:00">17:00-19:00</option>
            <option value="18:00-20:00">18:00-20:00</option>
            <option value="19:00-21:00">19:00-21:00</option>
            <option value="20:00-22:00">20:00-22:00</option>
            <option value="21:00-23:00">21:00-23:00</option>
          </select>
        @elseif ($question->cevapTuru == "[Arac]")
          <select class="form-control" name="soru{{ $question->id }}" required>
            <option value="">-Seçiniz-</option>
            @foreach ($vehicles as $vehicle)
              <option value="{{ $vehicle->id }}">{{ $vehicle->arac }}</option>
            @endforeach
          </select>
        @elseif ($question->cevapTuru == "[Parca]")
          <span style="font-size: 12px; color: red; line-height: initial; display: block;">Parçalar bu modülde sorulamamaktır. Parçalar Cihaz ve Modele göre değişiklik gösterir.</span>
        @elseif ($question->cevapTuru == "[Fiyat]")
          <input type="number" name="soru{{ $question->id }}" class="form-control" required/>
        @elseif ($question->cevapTuru == "[Teklif]")
          <input type="number" name="soru{{ $question->id }}" class="form-control" />
        @elseif ($question->cevapTuru == "[Bayi]")
          <select class="form-control" name="soru{{ $question->id }}" required>
            <option value="">-Seçiniz-</option>
            @foreach ($dealers as $dealer)
              <option value="{{ $dealer->user_id }}">{{ $dealer->name }}</option>
            @endforeach
          </select>
        @endif
      </div>
    </div>
  @endforeach

  <div class="row">
    <div class="col-lg-12" style="text-align: center;margin-top: 2px;">
      <input type="hidden" name="servisidler" class="servisidler" value="{{ $servisIds }}"/>
      <input type="hidden" name="gelenIslem" value="{{ $gelenDurum }}"/>
      <input type="hidden" name="gidenIslem" value="{{ $gidenDurum }}"/>
      <input type="submit" class="btn btn-primary btn-sm" value="Kaydet"/>
    </div>
  </div>
</form>

<script>
  $(document).on('submit', '#topluPlanKaydet', function(e) {
    e.preventDefault();
    var form = $(this);
    var formData = new FormData(this);
    $.ajax({
      url: form.attr('action'),
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      success: function(res) {
        alert(res.success);
        $('#servisPersonelAtamaModal').modal('hide');
        $('#servisPersonelAtamaModal .modal-body').html(""); // display data
        $(".servisPlanListele").trigger("click");
      },
      error: function(xhr) {
        alert("Hata oluştu");
        console.log(xhr.responseText);
      }
    });
  });
</script>