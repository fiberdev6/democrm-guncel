<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="card" style="margin-bottom: 3px;margin-top:3px;">
  <div class="card-header" style="padding: 3px 5px!important;">
    <button type="button" class="btn btn-success btn-sm fisNotuEkle" data-bs-id ={{$servis->id}}>Fiş Notu Ekle</button>
  </div>

  <div class="card-body fisNotlari" style="padding: 0!important;"></div> 
</div>

<div class="card">
  <div class="card-body" style="padding: 0!important">
    @if(count($servis_fis_notlari) > 0)
      <div class="table-responsive" style="margin: 0!important;">
        <table class="table table-hover table-striped" id="fisNotuTablo" width="100%" cellspacing="0" style="margin: 0">
          <thead class="title">
            <tr>
              <th style="padding: 5px 10px;font-size: 12px;">Tarih</th>
              <th style="padding: 5px 10px;font-size: 12px;">İşlemi Yapan</th>
              <th style="padding: 5px 10px;font-size: 12px;">Açıklama</th>
              <th style="padding: 5px 10px;font-size: 12px;"></th>
              <th style="padding: 5px 10px;font-size: 12px;"></th>
            </tr>
          </thead>
          <tbody>
            @foreach($servis_fis_notlari as $item)
              <tr>
                <td style="vertical-align: middle;width: 100px; font-size: 11px; padding: 0 10px;">
                  {{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}
                </td>
                <td style="vertical-align: middle;font-size: 11px; padding: 0 10px;">
                  {{ $item->personel->name }}
                </td>
                <td style="vertical-align: middle;font-size: 11px; padding: 0 10px;">
                  <strong>{{ $item->aciklama }}</strong>
                </td>
               @if($item->kid == auth()->user()->user_id)
  {{-- Düzenle Butonu (İkonlu) --}}
  <td style="vertical-align: middle; width: 45px; text-align: center; padding: 0 10px;">
    <a style="padding: 6px 6px;color:#e39d23" href="#" class="btn btn-outline-warning btn-sm fisNotuDuzenle" data-bs-id="{{ $item->id }}" title="Düzenle">
      <i class="fas fa-edit"></i>
    </a>
  </td>
  
  {{-- Sil Butonu (İkonlu) --}}
  <td style="vertical-align: middle; width: 45px; text-align: center; padding: 0 10px;">
    <a style="padding: 6px 7px;" href="#" class="btn btn-outline-danger  btn-sm fisNotuSil" data-id="{{ $item->id }}" title="Sil">
      <i class="fas fa-trash-alt"></i>
    </a>
  </td>
@else
  {{-- Yetki Yok Durumu (Tek hücrede birleştirilmiş) --}}
  <td colspan="2" style="vertical-align: middle; text-align: center; padding: 0 10px; font-size: 11px;">
    Yetkiniz Yok
  </td>
@endif                                 
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @else
      <div id="noFisNotu" class="text-center text-muted" style="padding: 20px;">
        <i class="fas fa-sticky-note fa-3x mb-3" style="font-size: 2.5em; color: #ddd;"></i>
        <p style="font-size: 14px; color: #6c757d; margin: 0;">Henüz fiş notu eklenmemiş</p>
      </div>
    @endif
  </div>
</div>      

<script type="text/javascript">
  $(document).ready(function () {
    $(".fisNotuEkle").click(function(){
      var id = $(this).attr("data-bs-id");
      var firma_id = {{$firma->id}};
      if(id){
        $.ajax({
          url: "/" + firma_id + "/servis-fis-notu/ekle/"+ id
        }).done(function(data) {
          if($.trim(data)==="-1"){
            window.location.reload(true);
          }else{
            $('.fisNotlari').html(data).show();
          }
        });
      }
    });
  });
</script>

<script type="text/javascript">
  $(document).ready(function () {
    $(".fisNotuDuzenle").click(function(){
      var id = $(this).attr("data-bs-id");
      var firma_id = {{$firma->id}};
      if(id){
        $.ajax({
          url: "/" + firma_id + "/servis-fis-notu/duzenle/"+ id
        }).done(function(data) {
          if($.trim(data)==="-1"){
            window.location.reload(true);
          }else{
            $('.fisNotlari').html(data).show();
          }
        });
      }
    });
  });
</script>

<script>
  $(document).ready(function() {
    // Fiş notu sayısını kontrol eden fonksiyon
    function checkFisNotuCount() {
      const fisNotuCount = $('#fisNotuTablo tbody tr').length;
      if (fisNotuCount === 0) {
        $('#fisNotuTablo').closest('.table-responsive').hide();
        if ($('#noFisNotu').length === 0) {
          $('.card-body').append(`
            <div id="noFisNotu" class="text-center text-muted" style="padding: 40px 20px;">
              <i class="fas fa-sticky-note fa-3x mb-3" style="font-size: 2.5em; color: #ddd;"></i>
              <p style="font-size: 14px; color: #6c757d; margin: 0;">Henüz fiş notu eklenmemiş</p>
              <small style="color: #adb5bd;">Yukarıdaki "Fiş Notu Ekle" butonunu kullanarak not ekleyebilirsiniz</small>
            </div>
          `);
        } else {
          $('#noFisNotu').show();
        }
      } else {
        $('#noFisNotu').hide();
        $('#fisNotuTablo').closest('.table-responsive').show();
      }
    }

    $('#fisNotuTablo').on('click', '.fisNotuSil', function(e) {
      e.preventDefault();
      var confirmDelete = confirm("Bu servis fiş notunu silmek istediğinize emin misiniz?");
      if (confirmDelete) {
        var id = $(this).attr('data-id');
        var firma_id = {{$firma->id}};
        var $row = $(this).closest('tr');
        
        $.ajax({
          url: '/' + firma_id + '/servis-fis-notu/sil/' + id,
          type: 'POST',
          data: {
            _method: 'DELETE', 
            _token: '{{ csrf_token() }}'
          },
          success: function(data) {
            if (data) {
              alert("Servis fiş notu başarıyla silindi.");
              $row.fadeOut(300, function() {
                $(this).remove();
                checkFisNotuCount(); // Silme işlemi sonrası kontrol et
              });
              $('#datatableService').DataTable().ajax.reload();
              $('.nav7').trigger('click');
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