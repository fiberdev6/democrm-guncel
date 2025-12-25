<ul class="nav nav-pills " role="tablist" style="margin-bottom: 5px;">
  <li class="nav-item" style="font-size: 14px;"><a class="nav-link nav1 active" data-bs-toggle="pill" href="#tab1" data-id="{{$customer->id}}" role="tab">Müşteri Bilgileri</a></li>
  <li class="nav-item" style="font-size: 14px;"><a class="nav-link nav2" data-bs-toggle="pill" href="#tab2" data-id="{{$customer->id}}" role="tab">Servisleri</a></li>
</ul>

<div class="tab-content">
  <div id="tab1" class="tab-pane active" style="padding: 0">
    <form method="post" id="editCust" class="editCust" action="{{ route('update.customer', [$firma->id, $customer->id]) }}" enctype="multipart/form-data">
      @csrf
      <div class="row">
        <label class="col-sm-3 custom-p-r">Kayıt Tarihi<span style="font-weight: bold; color: red;">*</span></label>
        <div class="col-sm-9 custom-p-l">
          <input name="kayitTarihi" class="form-control datepicker kayitTarihi" type="date" value="{{\Carbon\Carbon::parse($customer->created_at)->format('Y-m-d')}}" style="border: 1px solid #ced4da;" required>
        </div>
      </div>
  
      <div class="row">
        <label class="col-sm-3 custom-p-r">Müşteri Tipi: </label>
        <div class="col-sm-9 custom-p-l">
          <select name="mTipi" class="form-select musteriTipi" required>
            <option value="1" {{ $customer->musteriTipi == "1" ? 'selected' : ''}}>BİREYSEL</option>
            <option value="2" {{ $customer->musteriTipi == "2" ? 'selected' : ''}}>KURUMSAL</option>
          </select>
        </div>
      </div> <!--end row-->
  
      <div class="row">
        <label class="col-sm-3 custom-p-r"><span class="musteriAdiSpan">Müşteri Adı</span><span style="font-weight: bold; color: red;">*</span></label>
        <div class="col-sm-9 custom-p-l">
          <input name="name" class="form-control buyukYaz" type="text" placeholder="Müşteri Adı" value="{{$customer->adSoyad}}" required>
        </div>
      </div>
  
      <div class="row">
        <label class="col-sm-3 custom-p-r">Telefon:</label>
        <div class="col-sm-4 col-sm-custom col-6 custom-p-r-m-md custom-p-l">
          <input name="tel1" class="form-control phone" value="{{$customer->tel1}}" type="text" required>
        </div>
        <div class="col-sm-4 col-sm-custom col-6 custom-p-m-md custom-p-l">
          <input name="tel2" class="form-control phone" value="{{$customer->tel2}}" type="text">
        </div>
      </div>

      <div class="row">
        <div class="col-sm-3 custom-p-r "><label>İl/İlçe</label></div>
        <div class="col-sm-4 col-sm-custom custom-p-r-m-md col-6 custom-p-l">
          <select name="il" id="sehirSelect" class="form-control form-select" style="width:100%!important;">
            <option value="" selected disabled>-Seçiniz-</option>
            @foreach($countries as $item)
              <option value="{{ $item->id }}" {{ $customer->il == $item->id ? 'selected' : ''}}>{{ $item->name}}</option>
            @endforeach
          </select>
        </div>
        <div class="col-sm-4 col-sm-custom custom-p-m-md col-6 custom-p-l">
          <select name="ilce" id="ilceSelect" class="form-control form-select" style="width:100%!important;">
            <option value="" selected disabled>-Seçiniz-</option>                              
          </select>
        </div>
      </div> 
  
      <div class="row">
        <label class="col-sm-3 custom-p-r">Adress:</label>
        <div class="col-sm-9 custom-p-l">
          <textarea name="address" type="text" class="form-control" rows="2">{{$customer->adres}}</textarea>
        </div>
      </div>
    
    
      <div class="row" id="tcNo">
        <label class="col-sm-3 custom-p-r">T.C. No <span style="font-weight: bold; color: red;">*</span></label>
        <div class="col-sm-9 custom-p-l">
          <input name="tcno" id="tcKimlik" class="form-control" type="text" placeholder="T.C No" value="{{$customer->tcNo}}">
        </div>
      </div>
    
      <div class="row vergi-box" id="vergiBox">
        <label class="col-sm-3 custom-p-r">Vergi No/Dairesi</label>
        <div class="col-sm-4 col-sm-custom custom-p-l">
          <input name="vergiNo" id="vergiNo" class="form-control " type="text" placeholder="Vergi No" value="{{$customer->vergiNo}}">
        </div>
        <div class="col-sm-4 col-sm-custom custom-p-l">
          <input name="vergiDairesi" class="form-control " type="text" placeholder="Vergi Dairesi" value="{{$customer->vergiDairesi}}">
        </div>
      </div>
   
      <div class="row">
        <div class="col-sm-12 gonderBtn">
          <input type="hidden" name="id" value="{{$customer->id}}">
          <input type="submit" class="btn btn-sm btn-info waves-effect waves-light" value="Kaydet">
        </div>
      </div>
    </form>
  </div>
  <div id="tab2" class="tab-pane fade" style="padding: 0">
</div>
  
<script>
  $('.buyukYaz').keyup(function(){
    this.value = this.value.toUpperCase();
  });

  $(document).ready(function () {
    $(".phone").mask("999 999 9999");
  });

  $(document).ready(function(){
    $("#tcKimlik").mask("99999999999");
  });

  $(document).ready(function(){
    $("#vergiNo").mask("9999999999");
  });
    
  $(document).ready(function (e) {
    $('#editCust #vergiBox').hide();

    var mTip = $('#editCust .musteriTipi').val();
    if(mTip>0){
      if (mTip == 2) {
        $("#editCust .musteriAdiSpan").text("Firma Adı");
        $('#editCust #vergiBox').show();
        $('#editCust #tcNo').hide();
      } else {
        $("#editCust .musteriAdiSpan").text("Müşteri Adı");
        $('#editCust #vergiBox').hide();
        $('#editCust #tcNo').show();
      }
    }

    $('#editCust .musteriTipi').on('change', function () {
      var val = $(this).val();
      if (val == 2) {
        $("#editCust .musteriAdiSpan").text("Firma Adı");
        $('#editCust #vergiBox').show();
        $('#editCust #tcNo').hide();
      } else {
        $("#editCust .musteriAdiSpan").text("Müşteri Adı");
        $('#editCust #vergiBox').hide();
        $('#editCust #tcNo').show();
      }
    });
  });
</script>
  
<script>
  $(document).ready(function () {
    $('#editCust').submit(function (event) {
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
  $(document).ready(function() {
    var selectedCountryId ={{ $customer->il == '' ? '0' : $customer->il}} ;
    if(selectedCountryId){
      $.get("/get-states/" + selectedCountryId, function(data) {
        $.each(data, function(index, city) {
          ilceSelect.append(new Option(city.ilceName, city.id));
          if(city.id == {{ $customer->ilce == '' ? '0' : $customer->ilce}}){
            $("#ilceSelect").val(city.id).change();
          } 
        });
      });
    }
    // Ülke seçildiğinde
    $("#sehirSelect").change(function() {
      var selectedCountryId = $(this).val();
      // Şehirleri getir ve ikinci select'i güncelle
      $.get("/get-states/" + selectedCountryId, function(data) {
        var citySelect = $("#ilceSelect");
        citySelect.empty(); // Önceki seçenekleri temizle
        $.each(data, function(index, city) {         // 'each()' fonksiyonuyla dolaşılıp ikinci selecte eklenir.
          citySelect.append(new Option(city.ilceName, city.id));
        });
      });
    });
  });
</script>
  
<script>
  $(document).ready(function (e) {
    $("#editCust").submit(function (event) {
      event.preventDefault();
      var formData = new FormData(this);
      $.ajax({
        url: $(this).attr("action"),
        type: "POST",
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        beforeSend: function () {
          $(".btnWrap").html("Yükleniyor. Bekleyin..");
        },
        success: function (data) {
          if (data === false) {
            
            window.location.reload(true);
          } else {
            alert("Müşteri bilgileri güncellendi");
            $('#datatableCustomer').DataTable().ajax.reload();
            $('#editCustomerModal').modal('hide');
            
          }
        },
        error: function (xhr, status, error) {
          alert("Güncelleme başarısız!");
          window.location.reload(true);
        },
      });
    });
  });
</script>

<script type="text/javascript">
  $(".nav2").click(function(){
    var id = $(this).attr("data-id");
    var firma_id = {{$firma->id}};
    if(id){
      $.ajax({
        url: "/"+ firma_id +"/musteri-servisleri/" + id
      }).done(function(data) {
        if($.trim(data)==="-1"){
          window.location.reload(true);
        }else{
          $('#tab2').html(data); // display data
        }
      });
    }
  });
</script>