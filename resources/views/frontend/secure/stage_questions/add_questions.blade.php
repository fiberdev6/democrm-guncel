<form method="post" id="addQuestion" action="{{ route('store.stage.question', $firma->id)}}" enctype="multipart/form-data" class="needs-validation" novalidate>
    @csrf
    <input type="hidden" name="form_token" id="formTokenQuestion" value="">
    <div class="row mb-3">
        <label class="col-sm-4">Servis Aşaması:<span style="font-weight: bold; color: red;">*</span></label>
        <div class="col-sm-8">
            <select name="stage" id="stage" class="form-control form-select" style="width:100%!important;">
                <option value="" selected disabled>-Seçiniz-</option>
                @foreach($stages as $item)
                    <option value="{{ $item->id }}">{{ $item->asama}}</option>
                @endforeach
            </select>
        </div> 
    </div>

    <div class="row mb-3">
      <label class="col-sm-4">Soru Ekle:<span style="font-weight: bold; color: red;">*</span></label>
      <div class="col-sm-8">
        <input name="soru" class="form-control" type="text" required>
      </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-4">Cevap Formatı:<span style="font-weight: bold; color: red;">*</span></label>
        <div class="col-sm-8">
            <select class="form-control cevap" name="cevap">
                <option value="[Aciklama]">Açıklama</option>
                <option value="[Personel]">Personel </option>
                <option value="[Tarih]">Tarih</option>
                <option value="[Saat]">Saat Aralığı</option>
                <option value="[Arac]">Araç</option>
                <option value="[Parca]">Parça</option>
                <option value="[Fiyat]">Fiyat</option>
                <option value="[Teklif]">Teklif</option>
                <option value="[Bayi]">Bayi</option>
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
        <input name="sira" class="form-control" type="text" required>
      </div>
    </div>

    <div class="row">
      <div class="col-sm-12 gonderBtn">
        <input type="submit" class="btn btn-info btn-sm waves-effect waves-light" value="Kaydet">
      </div>
    </div>
  </form>
  
  <script>
    $(document).ready(function () {
      $('#addQuestion').submit(function (event) {
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

    $('#addQuestion .cevap').on('change', function() {
      if(this.value=="[Personel]"){
        $("#addQuestion .gruplar").show();
      }else{
        $("#addQuestion .gruplar").hide();
      }
    });
  </script>
  
  <script>
    $(document).ready(function(){
  // Servis aşama sorusu ekleme formu submit
  $('#addQuestion').submit(function(e){
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
          $('#addQuestion')[0].reset();
          
          // Modal'ı kapat
          $('#addStageQuestionModal').modal('hide');
          
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
  $('#addQuestion .cevap').on('change', function() {
    if(this.value=="[Personel]"){
      $("#addQuestion .gruplar").show();
    }else{
      $("#addQuestion .gruplar").hide();
    }
  });
});
  </script>

<script>
  $(document).ready(function() {
      let formSubmitting = false;
      
      // Benzersiz token oluştur
      function generateToken() {
          return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
      }
      
      // Sayfa yüklendiğinde ilk token'ı oluştur
      $('#formTokenQuestion').val(generateToken());
      
      // Form submit sonrası token yenileme
      $('#addQuestion').on('submit', function() {
          // Token kontrolü
          if (formSubmitting) {
              return false;
          }
          
          // Butonu disable et
          formSubmitting = true;
          $(this).find('input[type="submit"]').prop('disabled', true);
          
          // 3 saniye sonra yeniden aktif et
          setTimeout(function() {
              $('#formTokenQuestion').val(generateToken());
              formSubmitting = false;
              $('#addQuestion input[type="submit"]').prop('disabled', false);
          }, 3000);
      });
  });
</script>