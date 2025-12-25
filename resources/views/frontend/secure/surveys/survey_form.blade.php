@php
    $user = auth()->user();
    $isBayi = $user->hasRole('Bayi') || $user->roles->pluck('id')->contains(259);
    $anketYapan = $anket?->ekleyenUser ?? auth()->user();
    // Personel ID'sini belirle - önce mevcut anketten, sonra servis personelinden
    $personelId = null;
    if ($anket && $anket->personel) {
        $personelId = (int)$anket->personel;
    } elseif (isset($servisPersonelId) && $servisPersonelId) {
        $personelId = (int)$servisPersonelId;
    }
    $anketiYapilanPersonel = $personelId ? \App\Models\User::find($personelId) : null;
@endphp

<form method="POST" id="surveyForm" action="{{ route('survey.store', ['tenant_id' => $tenant_id, 'servisId' => $servis->id]) }}">
    @csrf
    <input type="hidden" name="servisid" value="{{ $servis->id }}">

    @if($anket)
    <div class="col-lg-12 rw1">
        <label>Anketi Yapan Personel</label>
        <input type="text" readonly class="form-control" value="{{ $anketYapan->name ?? 'Belirtilmemiş' }}">
        <input type="hidden" name="personel" value="{{ $personelId ?? ''}}">
    </div>
    @endif

    <!-- Soru 1 -->
    <div class="row form-group">
        <div class="col-lg-12 rw1"><label>Teknisyen dediği saatte geldi mi?</label></div>
        <div class="col-lg-3 col-6 custom-p-r-m-md rw2">
            <select class="form-control" name="soru1">
                <option value="0" {{ $anket?->soru1 == "0" ? 'selected' : '' }}>Belli Değil</option>
                <option value="1" {{ $anket?->soru1 == "1" ? 'selected' : '' }}>Evet</option>
                <option value="2" {{ $anket?->soru1 == "2" ? 'selected' : '' }}>Hayır</option>
            </select>
        </div>
        <div class="col-lg-9 col-6 custom-p-m-md rw2">
            <input type="text" class="form-control" name="soru1Text" placeholder="Açıklama" value="{{ $anket?->soru1Text }}">
        </div>
    </div>

    <!-- Soru 2 -->
    <div class="row form-group">
        <div class="col-lg-12 rw1"><label>Teknisyen davranışlarından, kılık ve kıyafetlerinden memnun musunuz?</label></div>
        <div class="col-lg-3 col-6 custom-p-r-m-md rw2">
            <select class="form-control" name="soru2">
                <option value="0" {{ $anket?->soru2 == "0" ? 'selected' : '' }}>Belli Değil</option>
                <option value="1" {{ $anket?->soru2 == "1" ? 'selected' : '' }}>Evet</option>
                <option value="2" {{ $anket?->soru2 == "2" ? 'selected' : '' }}>Hayır</option>
            </select>
        </div>
        <div class="col-lg-9 col-6 custom-p-m-md rw2">
            <input type="text" class="form-control" name="soru2Text" placeholder="Açıklama" value="{{ $anket?->soru2Text }}">
        </div>
    </div>

    <!-- Soru 3 -->
    <div class="row form-group">
        <div class="col-lg-12 rw1"><label>Teknisyen cihazınızla yeterince ilgilendi mi?</label></div>
        <div class="col-lg-3 col-6 custom-p-r-m-md rw2">
            <select class="form-control" name="soru3">
                <option value="0" {{ $anket?->soru3 == "0" ? 'selected' : '' }}>Belli Değil</option>
                <option value="1" {{ $anket?->soru3 == "1" ? 'selected' : '' }}>Evet</option>
                <option value="2" {{ $anket?->soru3 == "2" ? 'selected' : '' }}>Hayır</option>
            </select>
        </div>
        <div class="col-lg-9 col-6 custom-p-m-md rw2">
            <input type="text" class="form-control" name="soru3Text" placeholder="Açıklama" value="{{ $anket?->soru3Text }}">
        </div>
    </div>

    <!-- Soru 4 -->
    <div class="row form-group">
        <div class="col-lg-12 rw1"><label>Sizden Talep Edilen Ücret</label></div>
        <div class="col-lg-12 rw2">
            <input type="text" name="soru4Text" class="form-control" autocomplete="off" placeholder="0.00" value="{{ $anket?->soru4Text }}" onkeyup="sayiKontrol(this)">
        </div>
    </div>

    <!-- Soru 5 -->
    <div class="row form-group">
        <div class="col-lg-12 rw1"><label>Genel olarak servis hizmetimizden memnun musunuz?</label></div>
        <div class="col-lg-3 col-6 custom-p-r-m-md rw2">
            <select class="form-control" name="soru5">
                <option value="0" {{ $anket?->soru5 == "0" ? 'selected' : '' }}>Belli Değil</option>
                <option value="1" {{ $anket?->soru5 == "1" ? 'selected' : '' }}>Evet</option>
                <option value="2" {{ $anket?->soru5 == "2" ? 'selected' : '' }}>Hayır</option>
            </select>
        </div>
        <div class="col-lg-9 col-6 custom-p-m-md rw2">
            <input type="text" class="form-control" name="soru5Text" placeholder="Açıklama" value="{{ $anket?->soru5Text }}">
        </div>
    </div>

    <div class="text-end">
        <button style="background-color: #343a40;border-color: #343a40;" type="submit" class="btn btn-primary">Gönder</button>
    </div>
</form>


<script>
// sayiKontrol fonksiyonu
function sayiKontrol(input) {
    input.value = input.value.replace(/[^0-9.]/g, ''); 
    const parts = input.value.split('.');
    if (parts.length > 2) {
        input.value = parts[0] + '.' + parts.slice(1).join('');
    }
}

$(document).ready(function() {
    $('#surveyForm').on('submit', function(e) {
        e.preventDefault(); // Formun normal gönderimini engelle

        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();

        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            dataType: 'json', // Sunucudan JSON yanıt beklediğimizi belirtiyoruz
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    // location.reload(); // Sayfayı yenile
                } else {
                    // Laravel'den success: false gelirse veya beklenmedik bir durum olursa
                    alert(response.error || 'İşlem sırasında bir hata oluştu.');
                }
            },
            error: function(xhr) {
                let errors = xhr.responseJSON?.errors;
                if (errors) {
                    let messages = Object.values(errors).flat().join('\n');
                    alert('Hata:\n' + messages);
                } else {
                    // Sunucudan gelen genel hata mesajını veya varsayılan hatayı göster
                    alert(xhr.responseJSON?.error || 'Beklenmeyen bir hata oluştu.');
                }
            }
        });
    });
});
</script>