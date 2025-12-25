<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="card" style="margin-bottom: 3px;margin-top:3px;">
  <div class="card-header" style="padding: 3px 5px!important;">
    <button type="button" class="btn btn-success btn-sm optNotuEkle" data-bs-id ={{$servis->id}}>Operatör Notu Ekle</button>
  </div>

  <div class="card-body optNotlari" style="padding: 0!important;"></div> 
</div>

<div class="card">
  <div class="card-body" style="padding: 0!important">
    @if(count($opt_notlari) > 0)
      <div class="table-responsive" style="margin: 0!important;">
        <table class="table table-hover table-striped" id="operatorNotuTablo" width="100%" cellspacing="0" style="margin: 0">
          <thead class="title">
            <tr>
              <th style="padding: 5px 10px;font-size: 12px;">Tarih</th>
              <th style="padding: 5px 10px;font-size: 12px;">İşlemi Yapan</th>
              <th style="padding: 5px 10px;font-size: 12px;">Açıklama</th>
               <th colspan="2" class="text-end" style="padding: 5px 10px;font-size: 12px;"></th>
            </tr>
          </thead>
          <tbody>
            @foreach($opt_notlari as $item)
              <tr>
                <td style="vertical-align: middle;width: 100px; font-size: 11px; padding: 0 10px;">
                  {{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}
                </td>
                <td style="vertical-align: middle;font-size: 11px; padding: 0 10px;">
                  {{ $item->user->name }}
                </td>
                <td style="vertical-align: middle;font-size: 11px; padding: 0 10px;">
                  <strong>{{ $item->aciklama }}</strong>
                </td>
                 <td colspan="2" style="vertical-align: middle; padding: 0 10px;">
          @if($item->pid == auth()->user()->user_id)
            <div class="d-flex justify-content-end gap-2">
                {{-- Düzenle Butonu (İkon) --}}
                <a href="#" style="padding: 6px 6px;color:#e39d23" class="btn btn-outline-warning btn-sm optNotuDuzenle" 
                   data-bs-id="{{ $item->id }}" title="Düzenle">
                    <i class="fas fa-edit"></i>
                </a>
                {{-- Sil Butonu (İkon) --}}
                <a style="padding: 6px 7px;" href="#" class="btn btn-outline-danger btn-sm optNotuSil" 
                   data-id="{{ $item->id }}" title="Sil">
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
            @endforeach
          </tbody>
        </table>
      </div>
    @else
      <div id="noOperatorNotu" class="text-center text-muted" style="padding: 20px;">
        <i class="fas fa-user-tie fa-3x mb-3" style="font-size: 2.5em; color: #ddd;"></i>
        <p style="font-size: 14px; color: #6c757d; margin: 0;">Henüz operatör notu eklenmemiş</p>
      </div>
    @endif
  </div>
</div>      

<script type="text/javascript">
  $(document).ready(function () {
    $(".optNotuEkle").click(function(){
      var id = $(this).attr("data-bs-id");
      var firma_id = {{$firma->id}};
      if(id){
        $.ajax({
          url: "/" + firma_id + "/servis-opt-notu/ekle/"+ id
        }).done(function(data) {
          if($.trim(data)==="-1"){
            window.location.reload(true);
          }else{
            $('.optNotlari').html(data).show();
          }
        });
      }
    });
  });
</script>

<script type="text/javascript">
  $(document).ready(function () {
    $(".optNotuDuzenle").click(function(){
      var id = $(this).attr("data-bs-id");
      var firma_id = {{$firma->id}};
      if(id){
        $.ajax({
          url: "/" + firma_id + "/servis-opt-notu/duzenle/"+ id
        }).done(function(data) {
          if($.trim(data)==="-1"){
            window.location.reload(true);
          }else{
            $('.optNotlari').html(data).show();
          }
        });
      }
    });
  });
</script>

<script>
  $(document).ready(function() {
    // Operatör notu sayısını kontrol eden fonksiyon
    function checkOperatorNotuCount() {
      const operatorNotuCount = $('#operatorNotuTablo tbody tr').length;
      if (operatorNotuCount === 0) {
        $('#operatorNotuTablo').closest('.table-responsive').hide();
        if ($('#noOperatorNotu').length === 0) {
          $('.card-body2').append(`
            <div id="noOperatorNotu" class="text-center text-muted" style="padding: 40px 20px;">
              <i class="fas fa-user-tie fa-3x mb-3" style="font-size: 2.5em; color: #ddd;"></i>
              <p style="font-size: 14px; color: #6c757d; margin: 0;">Henüz operatör notu eklenmemiş</p>
              <small style="color: #adb5bd;">Yukarıdaki "Operatör Notu Ekle" butonunu kullanarak not ekleyebilirsiniz</small>
            </div>
          `);
        } else {
          $('#noOperatorNotu').show();
        }
      } else {
        $('#noOperatorNotu').hide();
        $('#operatorNotuTablo').closest('.table-responsive').show();
      }
    }

    $('#operatorNotuTablo').on('click', '.optNotuSil', function(e) {
      e.preventDefault();
      var confirmDelete = confirm("Bu servis operatör notunu silmek istediğinize emin misiniz?");
      if (confirmDelete) {
        var id = $(this).attr('data-id');
        var firma_id = {{$firma->id}};
        var $row = $(this).closest('tr');
        
        $.ajax({
          url: '/' + firma_id + '/servis-opt-notu/sil/' + id,
          type: 'POST',
          data: {
            _method: 'DELETE', 
            _token: '{{ csrf_token() }}'
          },
          success: function(data) {
            if (data) {
              alert("Servis operatör notu başarıyla silindi.");
              $row.fadeOut(300, function() {
                $(this).remove();
                checkOperatorNotuCount(); // Silme işlemi sonrası kontrol et
              });
              $('#datatableService').DataTable().ajax.reload();
              loadServiceHistory({{ $servis->id }});
              $('.nav8').trigger('click');
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