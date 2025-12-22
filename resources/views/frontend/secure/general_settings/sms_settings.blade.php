<div class="row mt-3 mb-2">
    <div class="col-12">
      <div class=" d-sm-flex align-items-center justify-content-center">
        <h4 class="mb-sm-0 fw-bold text-gray pb-2" style="font-size: 19px;">Sms Ayarları</h4>
      </div>
    </div>
  </div>
    
<div class="d-flex justify-content-center align-items-center w-100">
          <form id="smsSettings" method="post" action="{{ route('update.sms',$firma->id)}}" enctype="multipart/form-data" class="needs-validation" novalidate style="width: 30%;">
            @csrf
            <input type="hidden" name="id" value="{{ $firma->id }}">
  
            <div class="row align-items-center border-bottom custom-border-bottom mb-1">
              <label class="col-sm-4 col-form-label">Kullanıcı ID:<span style="font-weight: bold; color: red;">*</span></label>
              <div class="col-sm-8">
                <input class="form-control" name="smsKullanici" type="text" value="{{ $firma->smsKullanici}}" required>
              </div>
            </div>
            <!-- end row -->
  
            <div class="row align-items-center border-bottom custom-border-bottom mb-1">
                <label class="col-sm-4 col-form-label">Şifre:<span style="font-weight: bold; color: red;">*</span></label>
                <div class="col-sm-8">
                  <input class="form-control" name="smsSifre" type="text" value="{{ $firma->smsSifre}}" required>
                </div>
            </div>

            <div class="row align-items-center border-bottom custom-border-bottom mb-1">
                <label class="col-sm-4 col-form-label">Gönderici Adı:<span style="font-weight: bold; color: red;">*</span></label>
                <div class="col-sm-8">
                  <input class="form-control" name="smsGonderici" type="text" value="{{ $firma->smsGonderici}}" required>
                </div>
              </div>

              <div class="row align-items-center border-bottom custom-border-bottom mb-1">
                <label class="col-sm-4 col-form-label">Kara Liste:<span style="font-weight: bold; color: red;">*</span></label>
                <div class="col-sm-8">
                  <input class="form-control" name="smsKaraliste" type="text" value="{{ $firma->smsKaraliste}}" required>
                </div>
              </div>
    
            <div class="row align-items-center  mt-2">
              <label class="col-sm-4 col-form-label"></label>
              <div class="col-sm-8">
                <input type="submit" class="btn btn-info waves-effect waves-light" value="Kaydet">
              </div>
            </div>
          </form>
          </div>
    
  <script>
    $(document).ready(function () {
      $('#smsSettings').submit(function (event) {
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
  </script>
    
    <script>
        $(document).ready(function(){
          $('#smsSettings').submit(function(e){
            e.preventDefault();
            if (this.checkValidity() === false) {
              e.stopPropagation();
            } else {
            var formData = $(this).serialize();
            $.ajax({
              url: $(this).attr('action'),
              type: 'POST',
              data: formData,
              success: function(response) {
                alert("Sms entegrasyon bilgileri güncellendi");
              },
              error: function(xhr, status, error) {
                console.error(xhr.responseText);
              }
            });
          }
          });
        });
      </script>
  
    
    