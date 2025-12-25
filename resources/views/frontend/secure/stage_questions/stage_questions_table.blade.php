@php
  // Aşamalara göre soruları gruplandırma
  $groupedQuestions = $stageQuestions->groupBy('asama');
@endphp

@foreach($stages as $stage)
  <div class="accordion-item">
    <h2 class="accordion-header" id="heading{{ $stage->id }}">
      <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" 
        data-bs-target="#collapse{{ $stage->id }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" 
        aria-controls="collapse{{ $stage->id }}">
        <strong>{{ $loop->iteration }}. {{ $stage->asama }}</strong>
        {{-- <span class="badge bg-success ms-2">Tamamlandı</span> --}}
      </button>
    </h2>
    <div id="collapse{{ $stage->id }}" class="accordion-collapse collapse " 
      aria-labelledby="heading{{ $stage->id }}" data-bs-parent="#serviceStepsAccordion">
      <div class="accordion-body">
        @if(isset($groupedQuestions[$stage->id]) && $groupedQuestions[$stage->id]->count() > 0)
          <div class="table-responsive">
            <table class="table table-bordered dt-responsive nowrap">
              <thead class="title">
                <tr>
                  
                  <th style="width: 40%">Soru</th>
                  <th style="width: 40%">Cevap Formatı</th>
                  <th style="width: 15%">İşlemler</th>
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
                    <td style="padding-left: 2px;">
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