<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="card" style="margin-bottom: 3px;margin-top:3px;">
  <div class="card-header card-border d-flex gap-2" style="padding: 3px 5px!important; ">
    <button type="button" class="btn btn-success btn-sm odemeEkleBtn" data-bs-id={{$servis->id}}>Gelir Ekle</button>
    <button type="button" class="btn btn-danger btn-sm giderEkleBtn" data-bs-id={{$servis->id}}>Gider Ekle</button>
  </div>

  <div class="card-body card-border odemeList" style="padding: 0!important;"></div>
</div>

<div class="card">
  <div class="card-body card-border" style="padding: 0!important">
    <div class="table-responsive" style="margin: 0!important;">
      <table class="table table-hover table-striped " id="paraHareketTablo" width="100%" cellspacing="0"
        style="margin: 0">
        <thead class="title">
          <tr>
            <th style="padding: 5px 10px;font-size: 10px;">Tarih</th>
            <th style="padding: 5px 10px;font-size: 10px;">İşlemi Yapan</th>
            <th style="padding: 5px 10px;font-size: 10px;">Ödeme Şekli</th>
            <th style="padding: 5px 10px;font-size: 10px;">Açıklama</th>
            <th style="padding: 5px 10px;font-size: 10px;">Durum</th>
            <th style="padding: 5px 10px;font-size: 10px;">Fiyat</th>
            <th colspan="2" class="text-end" style="padding: 5px 10px;font-size: 10px;"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($servisParaHareketleri as $hareket)
            <tr>
              {{-- Tarih --}}
              <td style="vertical-align: middle;width: 100px; font-size: 11px; padding: 0 10px;">
                {{ \Carbon\Carbon::parse($hareket->created_at)->format('d/m/Y') }}<br>
                {{ \Carbon\Carbon::parse($hareket->created_at)->format('H:i') }}
              </td>

              {{-- İşlemi Yapan --}}
              <td style="vertical-align: middle;font-size: 11px; padding: 0 10px;">
                {{ $hareket->personel->name ?? 'Bilinmiyor' }}
              </td>

              {{-- Ödeme Şekli --}}
              <td style="vertical-align: middle;font-size: 11px; padding: 0 10px;">
                <strong>
                  @if($hareket->odemeYonu == 2)
                    <i style="color: red;">Gider</i>
                  @elseif($hareket->odemeYonu == 1)
                    <i style="color: green;">Gelir</i>
                  @endif
                  - {{ $hareket->odemeSekliRelation->odemeSekli ?? 'Bilinmiyor' }}
                </strong>
              </td>

              {{-- Açıklama --}}
              <td style="vertical-align: middle;font-size: 11px; padding: 0 10px;">
                <strong>{{ $hareket->aciklama ?? '-' }}</strong>
              </td>

              {{-- Durum --}}
              <td style="vertical-align: middle;font-size: 11px; padding: 0 10px;">
                <strong>
                  @if($hareket->odemeDurum == 2)
                    <i style="color: red;">Beklemede</i>
                  @elseif($hareket->odemeDurum == 1)
                    <i style="color: green;">Tamamlandı</i>
                  @endif
                </strong>
              </td>

              {{-- Fiyat --}}
              <td style="vertical-align: middle;font-size: 11px; padding: 0 10px;">
                <strong>{{ number_format($hareket->fiyat, 2, ',', '.') }} TL</strong>
              </td>

              {{-- Sil Butonu --}}
              <td colspan="2" style="vertical-align: middle; padding: 0 10px;">
                @if(\Carbon\Carbon::parse($hareket->created_at)->diffInDays(now()) <= 30)
                  <div class="d-flex justify-content-end gap-2">
                    {{-- Düzenle Butonu (İkon) --}}
                    <a href="#" style="padding: 6px 6px;color:#e39d23"
                      class="btn btn-outline-warning btn-sm servisOdemeDuzenle" data-bs-id="{{ $hareket->id }}"
                      title="Düzenle">
                      <i class="fas fa-edit"></i>
                    </a>
                    {{-- Sil Butonu (İkon) --}}
                    <a style="padding: 6px 7px;" href="#" class="btn btn-outline-danger btn-sm servisOdemeSil"
                      data-id="{{ $hareket->id }}" title="Sil">
                      <i class="fas fa-trash-alt"></i>
                    </a>
                  </div>
                @else
                  <div class="text-end">
                    <span style="font-size:11px; color: #6c757d;">Yetkiniz Yok</span>
                  </div>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" style="text-align: center; padding: 5px; font-size: 14px;">
                Henüz para hareketi bulunmamaktadır.
              </td>
            </tr>
          @endforelse

          {{-- Toplam Satırı --}}
          @if($servisParaHareketleri->count() > 0)
            <tr style="background-color: #f8f9fa; border-top: 2px solid #dee2e6;">
              <td style="vertical-align: middle;font-size: 11px; padding: 0 10px;"></td>
              <td style="vertical-align: middle;font-size: 11px; padding: 0 10px;"></td>
              <td style="vertical-align: middle;font-size: 11px; padding: 0 10px;"></td>
              <td style="vertical-align: middle;font-size: 11px; padding: 0 10px;"></td>
              <td style="vertical-align: middle;font-size: 11px; padding: 3px 10px;">
                <strong><i>Toplam</i></strong>
              </td>
              <td style="vertical-align: middle;font-size: 11px; padding: 3px 10px;">
                <strong>
                  @if($toplamSonuc >= 0)
                    <span style="color: green;">{{ number_format($toplamSonuc, 2, ',', '.') }} TL</span>
                  @else
                    <span style="color: red;">{{ number_format($toplamSonuc, 2, ',', '.') }} TL</span>
                  @endif
                </strong>
              </td>
              <td style="vertical-align: middle;font-size: 11px; padding: 0 10px;"></td>
              <td style="vertical-align: middle;font-size: 11px; padding: 0 10px;"></td>
            </tr>
          @endif
        </tbody>
      </table>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function () {
    $(".odemeEkleBtn").click(function () {
      var id = $(this).attr("data-bs-id");
      var firma_id = {{$firma->id}};
      if (id) {
        $.ajax({
          url: "/" + firma_id + "/servis-gelir-ekle/" + id
        }).done(function (data) {
          if ($.trim(data) === "-1") {
            window.location.reload(true);
          } else {
            $('.odemeList').html(data).show();
          }
        });
      }
    });
  });
</script>

<script type="text/javascript">
  $(document).ready(function () {
    $(".giderEkleBtn").click(function () {
      var id = $(this).attr("data-bs-id");
      var firma_id = {{$firma->id}};
      if (id) {
        $.ajax({
          url: "/" + firma_id + "/servis-gider-ekle/" + id
        }).done(function (data) {
          if ($.trim(data) === "-1") {
            window.location.reload(true);
          } else {
            $('.odemeList').html(data).show();
          }
        });
      }
    });
  });
</script>

<script>
  $(document).ready(function () {
    $('#paraHareketTablo').on('click', '.servisOdemeSil', function (e) {
      e.preventDefault();
      var confirmDelete = confirm("Bu para hareketini silmek istediğinizden emin misiniz?");
      if (confirmDelete) {
        var id = $(this).attr('data-id');
        var firma_id = {{$firma->id}};
        $.ajax({
          url: '/' + firma_id + '/servis-para-hareketi/sil/' + id,
          type: 'POST',
          data: {
            _method: 'DELETE',
            _token: '{{ csrf_token() }}'
          },
          success: function (data) {
            if (data) {
              alert("Para hareketi başarıyla silindi.");
              $('#datatableService').DataTable().ajax.reload();
              loadServiceHistory({{ $servis->id }});
              $('.nav4').trigger('click');
            } else {
              alert("Silme işlemi başarısız oldu.");
            }
          },
          error: function (xhr, status, error) {
            console.error(xhr.responseText);
          }
        });
      }
    });
  });
</script>

<script type="text/javascript">
  $(document).ready(function () {
    $(".servisOdemeDuzenle").click(function () {
      var id = $(this).attr("data-bs-id");
      var firma_id = {{$firma->id}};
      if (id) {
        $.ajax({
          url: "/" + firma_id + "/servis-para-hareketi/duzenle/" + id
        }).done(function (data) {
          if ($.trim(data) === "-1") {
            window.location.reload(true);
          } else {
            $('.odemeList').html(data).show();
          }
        });
      }
    });
  });
</script>