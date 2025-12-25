<div class="row mt-3 mb-2">
  <div class="col-12">
    <div class=" d-sm-flex align-items-center justify-content-center">
      <h4 class="mb-sm-0 fw-bold text-gray  pb-2" style="font-size: 19px;">Prim Sistemi Ayarları</h4>
    </div>
  </div>
</div>
    
<div class="d-flex justify-content-center align-items-center w-100">
        <form id="primSettings" method="post" action="{{ route('update.firm.prim',$firma->id)}}" enctype="multipart/form-data" class="needs-validation" novalidate style="width: 42%;">
          @csrf
          <input type="hidden" name="firma_id" value="{{ $firma->id }}">
          <input type="hidden" name="id" value="{{$prim->id}}">
  
          <div class="row align-items-center border-bottom custom-border-bottom mb-1">
            <label class="col-sm-4 col-form-label">Operatör Prim Oranı:<span style="font-weight: bold; color: red;">*</span></label>
            <div class="col-sm-8">
              <input class="form-control" name="operatorPrim" type="number" step="0.01" value="{{ $prim->operatorPrim}}" required>
            </div>
          </div>
          <!-- end row -->
  
          <div class="row align-items-center border-bottom custom-border-bottom mb-1">
            <label class="col-sm-4 col-form-label">Operatör Prim Servis Sınırı:<span style="font-weight: bold; color: red;">*</span></label>
            <div class="col-sm-8">
              <input class="form-control" name="operatorPrimTutari" type="number" step="0.01" value="{{ $prim->operatorPrimTutari}}" required>
            </div>
          </div>

          <div class="row align-items-center border-bottom custom-border-bottom mb-1">
            <label class="col-sm-4 col-form-label">Teknisyen Prim Oranı:<span style="font-weight: bold; color: red;">*</span></label>
            <div class="col-sm-8">
              <input class="form-control" name="teknisyenPrim" type="number" step="0.01" value="{{ $prim->teknisyenPrim}}" required>
            </div>
          </div>

          <div class="row align-items-center border-bottom custom-border-bottom mb-1">
            <label class="col-sm-4 col-form-label">Teknisyen Prim Tutarı:<span style="font-weight: bold; color: red;">*</span></label>
            <div class="col-sm-8">
              <input class="form-control" name="teknisyenPrimTutari" type="number" step="0.01" value="{{ $prim->teknisyenPrimTutari}}" required>
            </div>
          </div>

          <div class="row align-items-center border-bottom custom-border-bottom mb-1">
            <label class="col-sm-4 col-form-label">Atölye Ustası Prim Oranı:<span style="font-weight: bold; color: red;">*</span></label>
            <div class="col-sm-8">
              <input class="form-control" name="atolyePrim" type="number" step="0.01" value="{{ $prim->atolyePrim}}" required>
            </div>
          </div>

          <div class="row align-items-center border-bottom custom-border-bottom mb-1">
            <label class="col-sm-4 col-form-label">Atölye Ustası Prim Servis Sınırı:<span style="font-weight: bold; color: red;">*</span></label>
            <div class="col-sm-8">
              <input class="form-control" name="atolyePrimTutari" type="number" step="0.01" value="{{ $prim->atolyePrimTutari}}" required>
            </div>
          </div>
    
          <div class="row align-items-center mt-2 ">
            <label class="col-sm-4 col-form-label"></label>
            <div class="col-sm-8">
              <input type="submit" class="btn btn-info waves-effect waves-light" value="Kaydet">
            </div>
          </div>
        </form>
</div>
    
<script>
  $(document).ready(function () {
    $('#primSettings').submit(function (event) {
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
    $('#primSettings').submit(function(e){
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
            alert("Prim sistemi bilgileri güncellendi");
          },
          error: function(xhr, status, error) {
            console.error(xhr.responseText);
          }
        });
      }
    });
  });
</script>
  
    
    