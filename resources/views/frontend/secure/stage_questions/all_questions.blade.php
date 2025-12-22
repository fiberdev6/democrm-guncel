
    <div  id="servisAsamaSoru">
      <a  class="btn btn-success btn-sm mb-2 addStageQuestion" data-bs-toggle="modal" data-bs-target="#addStageQuestionModal"><i class="fas fa-plus"></i><span>Servis Aşama Sorusu Ekle</span></a>

<div class="accordion" id="serviceStepsAccordion">
  @php
    // Aşamalara göre soruları gruplandırma
    $groupedQuestions = $stageQuestions->groupBy('asama');
  @endphp
  
  @foreach($asamalar as $stage)
    <div class="accordion-item">
      <h2 class="accordion-header" id="heading{{ $stage->id }}">
        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" 
          data-bs-target="#collapse{{ $stage->id }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" 
          aria-controls="collapse{{ $stage->id }}">
          {{ $loop->iteration }}. {{ $stage->asama }}
          {{-- <span class="badge bg-success ms-2">Tamamlandı</span> --}}
        </button>
      </h2>
      <div id="collapse{{ $stage->id }}" class="accordion-collapse collapse" 
        aria-labelledby="heading{{ $stage->id }}" data-bs-parent="#serviceStepsAccordion">
        <div class="accordion-body">
          @if(isset($groupedQuestions[$stage->id]) && $groupedQuestions[$stage->id]->count() > 0)
            <div class="table-responsive">
              <table class="table table-bordered dt-responsive nowrap">
                <thead class="title">
                  <tr>
                    
                    <th style="width: 40%">Soru</th>
                    <th style="width: 40%">Cevap Formatı</th>
                    <th style="width: 6%">İşlemler</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($groupedQuestions[$stage->id] as $question)
                    <tr>                      
                      <td style="padding-left: 2px;">{{ $question->soru }}</td>
                      <td style="padding-left: 2px;">
                        @php
                          $cevapTipi = $question->cevapTuru;
                          if(strpos($cevapTipi, '[Grup-') !== false) {
                            echo '';
                            $gruplar = explode(', ', $cevapTipi);
                            foreach($gruplar as $grup) {
                              if($grup == '[Grup-0]') {
                                echo 'Tüm Personeller ';
                              } else {
                                $grupId = str_replace('[Grup-', '', str_replace(']', '', $grup));
                                $role = $roles->where('id', $grupId)->first();
                                echo $role ? $role->name . ' ' : '';
                              }
                            }
                          } else {
                            switch($cevapTipi) {
                              case '[Aciklama]': echo 'Açıklama'; break;
                              case '[Tarih]': echo 'Tarih'; break;
                              case '[Saat]': echo 'Saat Aralığı'; break;
                              case '[Arac]': echo 'Araç'; break;
                              case '[Parca]': echo 'Parça'; break;
                              case '[Fiyat]': echo 'Fiyat'; break;
                              case '[Teklif]': echo 'Teklif'; break;
                              case '[Bayi]': echo 'Bayi'; break;
                              default: echo $cevapTipi;
                            }
                          }
                        @endphp
                      </td>
                      <td class="d-flex justify-content-center gap-1">
                        <a href="javascript:void(0);" data-bs-id="{{ $question->id }}" 
                           class="btn btn-outline-warning btn-sm editStageQuestion" 
                           data-bs-toggle="modal" data-bs-target="#editStageQuestionModal" 
                           title="Düzenle">
                           <i class="fas fa-edit"></i>
                        </a>
                        <a href="javascript:void(0);" data-bs-id="{{ $question->id }}" 
                           class="btn btn-outline-danger btn-sm deleteStageQuestion" title="Sil">
                           <i class="fas fa-trash-alt"></i>
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="alert alert-info">
              Bu aşama için tanımlanmış soru bulunmamaktadır.
            </div>
          @endif
        </div>
      </div>
    </div>
  @endforeach
</div>
</div>

 <!-- add modal content -->
  <div id="addStageQuestionModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title" id="myModalLabel">Servis Aşama Ekle</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
  
  <!-- edit modal content -->
  <div id="editStageQuestionModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title" id="myModalLabel">Servis Aşama Düzenle</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
  
  
  <script type="text/javascript">
  $(document).ready(function(){
    $(".addStageQuestion").click(function(){
        var firma_id = {{$firma->id}};
      $.ajax({
        url: "/"+ firma_id + "/servis-asama-sorusu/ekle"
      }).done(function(data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#addStageQuestionModal').modal('show');
          $('#addStageQuestionModal .modal-body').html(data);
        }
      });
    });
  });
  </script>
  
  <script type="text/javascript">
  $(document).ready(function(){
    $('#servisAsamaSoru').on('click', '.editStageQuestion', function(e){
      var id = $(this).attr("data-bs-id");
      var firma_id = {{$firma->id}};
      $.ajax({
        url: "/"+ firma_id + "/servis-asama-sorusu/duzenle/" + id
      }).done(function(data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#editStageQuestionModal').modal('show');
          $('#editStageQuestionModal .modal-body').html(data);
        }
      });
    });
    $("#editStageQuestionModal").on("hidden.bs.modal", function() {
      $(".modal-body").html("");
    });
  
    // silme işlemi
    $('#servisAsamaSoru').on('click', '.deleteStageQuestion', function(e){
      e.preventDefault();
      var id = $(this).attr("data-bs-id");
      var row = $(this).closest('tr');
      var firma_id = {{$firma->id}};
      if(confirm('Bu servis aşama sorusunu silmek istediğinize emin misiniz?')) {
        $.ajax({
          url: "/"+ firma_id + "/servis-asama-sorusu/sil/" + id,
          type: "DELETE",
          data: {
            "_token": "{{ csrf_token() }}", // CSRF koruması için token ekleyin
          },
          success: function(response) {
            if(response.success) {
              row.remove(); // Satırı tablodan kaldır
              alert('Servis aşama sorusu başarıyla silindi.');
            } else {
              alert('Servis aşama sorusu silinirken bir hata oluştu.');
            }
          },
          error: function(xhr) {
            alert('Servis aşama sorusu silinirken bir hata oluştu.');
          }
        });
      }
    });
  });
</script>