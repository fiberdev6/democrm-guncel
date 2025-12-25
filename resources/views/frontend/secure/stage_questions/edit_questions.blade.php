<form method="post" id="editQuestion" action="{{ route('update.stage.question', $firma->id)}}" enctype="multipart/form-data" class="needs-validation" novalidate>
    @csrf
    <div class="row mb-3">
        <label class="col-sm-4">Servis Aşaması:<span style="font-weight: bold; color: red;">*</span></label>
        <div class="col-sm-8">
            <select name="stage" id="stage" class="form-control form-select" style="width:100%!important;">
                <option value="" selected disabled>-Seçiniz-</option>
                @foreach($stages as $item)
                    <option value="{{ $item->id }}" {{ $question_id->asama == $item->id ? 'selected' : ''}}>{{ $item->asama}}</option>
                @endforeach
            </select>
        </div> 
    </div>

    <div class="row mb-3">
      <label class="col-sm-4">Soru Ekle:<span style="font-weight: bold; color: red;">*</span></label>
      <div class="col-sm-8">
        <input name="soru" class="form-control" type="text" value="{{$question_id->soru}}" required>
      </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-4">Cevap Formatı:<span style="font-weight: bold; color: red;">*</span></label>
        <div class="col-sm-8">
            <select class="form-control cevap" name="cevap">
                <option value="[Aciklama]" {{ $question_id->cevapTuru == '[Aciklama]' ? 'selected' : '' }}>Açıklama</option>
                <option value="[Personel]" {{ $question_id->cevapTuru == '[Personel]' ? 'selected' : '' }}>Personel</option>
                <option value="[Tarih]" {{ $question_id->cevapTuru == '[Tarih]' ? 'selected' : '' }}>Tarih</option>
                <option value="[Saat]" {{ $question_id->cevapTuru == '[Saat]' ? 'selected' : '' }}>Saat Aralığı</option>
                <option value="[Arac]" {{ $question_id->cevapTuru == '[Arac]' ? 'selected' : '' }}>Araç</option>
                <option value="[Parca]" {{ $question_id->cevapTuru == '[Parca]' ? 'selected' : '' }}>Parça</option>
                <option value="[Fiyat]" {{ $question_id->cevapTuru == '[Fiyat]' ? 'selected' : '' }}>Fiyat</option>
                <option value="[Teklif]" {{ $question_id->cevapTuru == '[Teklif]' ? 'selected' : '' }}>Teklif</option>
                <option value="[Bayi]" {{ $question_id->cevapTuru == '[Bayi]' ? 'selected' : '' }}>Bayi</option>
            </select>
        </div>
    </div>

    <div class="row mb-3 gruplar" style="display: none;">
      <label class="col-sm-4">Personel Grubu:</label>
      <div class="col-sm-8">
        <div><input type="checkbox"  name="grup[]" value="[Grup-0]" class="form-check-input me-2"> Tüm Personeller</div>
        @foreach($roles as $role)
            <div class="d-flex align-items-center ">
                <input type="checkbox" id="grup{{$role->id}}" name="grup[]" value="[Grup-{{$role->id}}]" class="form-check-input me-2">
                <label for="grup{{$role->id}}" class="form-check-label w-100 text-truncate">
                    {{ $role->name }}
                </label>
            </div>
            @endforeach
      </div>
    </div>

    <div class="row mb-3">
      <label class="col-sm-4">Sıra:</label>
      <div class="col-sm-8">
        <input name="sira" class="form-control" type="text" value="{{$question_id->sira}}" required>
      </div>
    </div>
    
    <div class="row">
      <div class="col-sm-12 gonderBtn">
        <input type="hidden" name="id" id="question_id" value="{{ $question_id->id }}">
        <input type="submit" class="btn btn-info btn-sm waves-effect waves-light" value="Kaydet">
      </div>
    </div>
</form>



<script>
    $(document).ready(function(){
  var cevapTuru = "{{ $question_id->cevapTuru }}";
  
  // Eğer cevapTuru [Grup-X] formatını içeriyorsa
  if(cevapTuru && cevapTuru.includes("[Grup-")) {
    // Cevap dropdown'ında [Personel] seçeneğini seç
    $('#editQuestion .cevap').val("[Personel]");
    
    // Gruplar bölümünü göster
    $("#editQuestion .gruplar").show();
    
    // Virgülle ayrılmış grupları diziye çevirelim
    var gruplar = cevapTuru.split(',');
    
    // Her bir grup değeri için ilgili checkbox'ı işaretle
    gruplar.forEach(function(grup) {
      // Boşlukları temizleyelim
      grup = grup.trim();
      // İlgili grup checkbox'ını seç
      $('input[name="grup[]"][value="' + grup + '"]').prop('checked', true);
    });
  }
  
  // Cevap türü değiştiğinde çalışacak fonksiyon
  $('#editQuestion .cevap').on('change', function() {
    if(this.value == "[Personel]"){
      $("#editQuestion .gruplar").show();
    } else {
      $("#editQuestion .gruplar").hide();
      // Diğer cevap türleri seçildiğinde checkboxları temizle
      $('input[name="grup[]"]').prop('checked', false);
    }
  });

});
  </script>
  
  <script>
    $(document).ready(function () {
      $('#editQuestion').submit(function (event) {
        var formIsValid = true;
        $(this).find('input, select').each(function () {
          var isRequired = $(this).prop('required');
          var isEmpty = !$(this).val();
          if (isRequired && isEmpty) {
            formIsValid = false;
            return false;
          }
        });
        if (!formIsValid) {
          event.preventDefault();
          alert('Lütfen zorunlu alanları doldurun.');
          return false;
        }
      });
    });

    $('#editQuestion .cevap').on('change', function() {
      if(this.value=="[Personel]"){
        $("#editQuestion .gruplar").show();
      }else{
        $("#editQuestion .gruplar").hide();
      }
    });
  </script>
  
  <script>
    $(document).ready(function(){
  // Servis aşama sorusu ekleme formu submit
  $('#editQuestion').submit(function(e){
    e.preventDefault();
    if (this.checkValidity() === false) {
      e.stopPropagation();
    } else {
      var formData = $(this).serialize();
      var selectedStageId = $('#stage').val(); // Seçilen aşama ID'sini alıyoruz
      
      $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        success: function(response) {
          // Başarılı mesajı göster
          alert('Servis aşama sorusu başarıyla eklendi');
          
          // Formu temizle
          $('#editQuestion')[0].reset();
          
          // Modal'ı kapat
          $('#editStageQuestionModal').modal('hide');
          
          // Tüm accordion yapısını yeniden yükle
          refreshAccordion(selectedStageId);
        },
        error: function(xhr, status, error) {
          alert('Hata oluştu: ' + xhr.responseText);
          console.error(xhr.responseText);
        }
      });
    }
  });
  
  // Tüm accordion yapısını yeniden yükleme fonksiyonu
  function refreshAccordion(activeStageId) {
    $.ajax({
      url: '{{ route("get.stage.questions", $firma->id) }}',
      type: 'GET',
      success: function(response) {
        // Tüm accordion içeriğini güncelle
        $('#serviceStepsAccordion').html(response);
        
        if (activeStageId) {
          $('#collapse' + activeStageId).addClass('show');
          $('#heading' + activeStageId + ' .accordion-button').removeClass('collapsed');
        }
        // Event listenerları yeniden ekle (gerekli olabilir)
        reattachEventListeners();
      },
      error: function(xhr, status, error) {
        console.error(xhr.responseText);
      }
    });
  }
  
  // Olay dinleyicilerini yeniden ekleme
  function reattachEventListeners() {
    // Edit ve Delete butonları için olay dinleyicileri
    $('.editQuestion').on('click', function() {
      var questionId = $(this).data('bs-id');
      // Soru düzenleme işlemleri...
    });
    
    $('.deleteStageQuestion').on('click', function() {
      var questionId = $(this).data('bs-id');
      // Soru silme işlemleri...
    });
  }
  
  // Personel seçeneğini değiştirme
  $('#editQuestion .cevap').on('change', function() {
    if(this.value=="[Personel]"){
      $("#editQuestion .gruplar").show();
    }else{
      $("#editQuestion .gruplar").hide();
    }
  });
});
  </script>