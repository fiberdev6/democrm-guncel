<form method="post" id="musteriDuz" class="editCust" action="{{ route('update.customer', [$firma->id, $customer->id]) }}" enctype="multipart/form-data">
  @csrf
  <div class="row">
    <label class="col-sm-4">Kayıt Tarihi<span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8">
      <input name="kayitTarihi" class="form-control datepicker kayitTarihi" type="date" value="{{\Carbon\Carbon::parse($customer->created_at)->format('Y-m-d')}}" style="border: 1px solid #ced4da;" required>
    </div>
  </div>
  
  <div class="row">
    <label class="col-sm-4">Müşteri Tipi: </label>
    <div class="col-sm-8">
      <select name="mTipi" class="form-select musteriTipi" id="musteriTipiSelect" required>
        <option value="1" {{ $customer->musteriTipi == "1" ? 'selected' : ''}}>BİREYSEL</option>
        <option value="2" {{ $customer->musteriTipi == "2" ? 'selected' : ''}}>KURUMSAL</option>
      </select>
    </div>
  </div> <!--end row-->
  
  <div class="row">
    <label class="col-sm-4"><span class="musteriAdiSpan">{{ $customer->musteriTipi == "2" ? 'Firma Adı' : 'Müşteri Adı' }}</span><span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8">
      <input name="name" class="form-control buyukYaz" type="text" placeholder="Müşteri Adı" value="{{$customer->adSoyad}}" required>
    </div>
  </div>
  
  <div class="row">
    <label class="col-sm-4">Telefon:</label>
    <div class="col-sm-4 col-6  custom-p-r-m-md">
      <input name="tel1" class="form-control phone" value="{{$customer->tel1}}" type="text" required>
    </div>
    <div class="col-sm-4 col-6 custom-p-m-md">
      <input name="tel2" class="form-control phone" value="{{$customer->tel2}}" type="text">
    </div>
  </div>

  <div class="row">
    <div class="col-sm-4"><label>İl/İlçe</label></div>
    <div class="col-sm-4 col-6 custom-p-r-m-md">
      <select name="il" id="sehirSelect" class="form-control form-select" style="width:100%!important;">
        <option value="" selected disabled>-Seçiniz-</option>
        @foreach($countries as $item)
          <option value="{{ $item->id }}" {{ $customer->il == $item->id ? 'selected' : ''}}>{{ $item->name}}</option>
        @endforeach
      </select>
    </div>
    <div class="col-sm-4 col-6 custom-p-m-md">
      <select name="ilce" id="ilceSelect" class="form-control form-select" style="width:100%!important;">
        <option value="" selected disabled>-Seçiniz-</option>                              
      </select>
    </div>
  </div> 
  
  <div class="row">
    <label class="col-sm-4">Adres:</label>
    <div class="col-sm-8">
      <textarea name="address" type="text" class="form-control" rows="2">{{$customer->adres}}</textarea>
    </div>
  </div>
    
  <!-- TC No - Sadece Bireysel için -->
  <div class="row" id="tcNoRow" style="display: {{ $customer->musteriTipi == '1' ? 'flex' : 'none' }};">
    <label class="col-sm-4">T.C. No <span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8">
      <input name="tcno" id="tcKimlik" class="form-control" type="text" placeholder="T.C No" value="{{$customer->tcNo}}">
    </div>
  </div>
    
  <!-- Vergi No/Dairesi - Sadece Kurumsal için -->
  <div class="row vergi-box" id="vergiBoxRow" style="display: {{ $customer->musteriTipi == '2' ? 'flex' : 'none' }};">
    <label class="col-sm-4">V. No/Dairesi <span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-4 col-6 custom-p-r-m-md">
      <input name="vergiNo" id="vergiNo" class="form-control" type="text" placeholder="Vergi No" value="{{$customer->vergiNo}}">
    </div>
    <div class="col-sm-4 col-6 custom-p-m-md">
      <input name="vergiDairesi" id="vergiDairesi" class="form-control" type="text" placeholder="Vergi Dairesi" value="{{$customer->vergiDairesi}}">
    </div>
  </div>
   
  <div class="row">
    <div class="col-sm-12 gonderBtn">
      <input type="hidden" name="id" value="{{$customer->id}}">
      <input style="background-color: #344a40;border-color:#344a40" type="submit" class="btn btn-sm btn-info waves-effect waves-light" value="Kaydet">
    </div>
  </div>
</form>

<script>
  $('.buyukYaz').keyup(function(){
    this.value = this.value.toUpperCase();
  });

  $(document).ready(function () {
    $(".phone").mask("9999 999 9999");
    $("#tcKimlik").mask("99999999999");
    $("#vergiNo").mask("9999999999");
  });

  // ✅ Müşteri Tipi Değişikliğinde Alanları Göster/Gizle
  $(document).ready(function() {
    function toggleCustomerFields() {
      var musteriTipi = $('#musteriTipiSelect').val();
      
      if (musteriTipi == '1') {
        // Bireysel
        $('#tcNoRow').show();
        $('#tcKimlik').prop('required', true);
        
        $('#vergiBoxRow').hide();
        $('#vergiNo').prop('required', false).val('');
        $('#vergiDairesi').prop('required', false).val('');
        
        $('.musteriAdiSpan').text('Müşteri Adı');
      } else if (musteriTipi == '2') {
        // Kurumsal
        $('#tcNoRow').hide();
        $('#tcKimlik').prop('required', false).val('');
        
        $('#vergiBoxRow').show();
        $('#vergiNo').prop('required', true);
        $('#vergiDairesi').prop('required', false); // Vergi dairesi zorunlu değilse false bırakın
        
        $('.musteriAdiSpan').text('Firma Adı');
      }
    }
    
    // Sayfa yüklendiğinde kontrol et
    toggleCustomerFields();
    
    // Müşteri tipi değiştiğinde kontrol et
    $('#musteriTipiSelect').change(function() {
      toggleCustomerFields();
    });
  });

  // İl/İlçe Select İşlemleri
  $(document).ready(function() {
    var selectedCountryId = {{ $customer->il == '' ? '0' : $customer->il }};
    if(selectedCountryId){
      $.get("/get-states/" + selectedCountryId, function(data) {
        var ilceSelect = $("#ilceSelect");
        $.each(data, function(index, city) {
          ilceSelect.append(new Option(city.ilceName, city.id));
          if(city.id == {{ $customer->ilce == '' ? '0' : $customer->ilce}}){
            $("#ilceSelect").val(city.id).change();
          } 
        });
      });
    }
    
    // İl seçildiğinde
    $("#sehirSelect").change(function() {
      var selectedCountryId = $(this).val();
      $.get("/get-states/" + selectedCountryId, function(data) {
        var citySelect = $("#ilceSelect");
        citySelect.empty();
        citySelect.append(new Option('-Seçiniz-', '', true, true));
        $.each(data, function(index, city) {
          citySelect.append(new Option(city.ilceName, city.id));
        });
      });
    });
  });

  // Form Validasyonu ve Submit
  $(document).ready(function () {
    $('#musteriDuz').submit(function (event) {
      event.preventDefault();
      
      var formIsValid = true;
      var errorMessage = '';
      
      // Müşteri tipi kontrolü
      var musteriTipi = $('#musteriTipiSelect').val();
      
      // Temel alanlar kontrolü
      if (!$('input[name="name"]').val()) {
        formIsValid = false;
        errorMessage = 'Lütfen müşteri/firma adını girin.';
      } else if (!$('input[name="tel1"]').val()) {
        formIsValid = false;
        errorMessage = 'Lütfen telefon numarasını girin.';
      } else if (!$('select[name="il"]').val()) {
        formIsValid = false;
        errorMessage = 'Lütfen il seçiniz.';
      } else if (!$('select[name="ilce"]').val()) {
        formIsValid = false;
        errorMessage = 'Lütfen ilçe seçiniz.';
      }
      
      // Müşteri tipine göre kontrol
      if (formIsValid) {
        if (musteriTipi == '1') {
          // Bireysel - TC No zorunlu
          var tcNo = $('#tcKimlik').val().replace(/\s/g, '');
          if (!tcNo || tcNo.length !== 11) {
            formIsValid = false;
            errorMessage = 'Bireysel müşteriler için 11 haneli TC No zorunludur.';
          }
        } else if (musteriTipi == '2') {
          // Kurumsal - Vergi No zorunlu
          var vergiNo = $('#vergiNo').val().replace(/\s/g, '');
          if (!vergiNo || vergiNo.length !== 10) {
            formIsValid = false;
            errorMessage = 'Kurumsal müşteriler için 10 haneli Vergi No zorunludur.';
          }
        }
      }
      
      if (!formIsValid) {
        alert(errorMessage);
        return false;
      }
      
      // Form gönder
      var formData = new FormData(this);
      $.ajax({
        url: $(this).attr("action"),
        type: "POST",
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        success: function (data) {
          if (data.customer) {
            var musteriTipiText = '';
            if (data.customer.musteriTipi == 1) {
              musteriTipiText = 'Bireysel';
            } else if (data.customer.musteriTipi == 2) {
              musteriTipiText = 'Kurumsal';
            }

            var tel1 = data.customer.tel1 || '';
            var tel2 = data.customer.tel2 || '';

            var telHtml = '';
            if (tel1) {
              telHtml += '<a href="tel:' + tel1 + '" style="color:red;">' + tel1 + '</a>';
            }
            if (tel2) {
              telHtml += ' - <a href="tel:' + tel2 + '" style="color:red;">' + tel2 + '</a>';
            }

            // Ana modaldaki müşteri bilgilerini güncelle
            $('#musBilCek strong').text(data.customer.adSoyad + ' ( ' + musteriTipiText + ' )');
            $('#tele').html(telHtml);          
            $('#maps').text(data.customer.adres);
            $('#vergi').text((data.customer.vergiNo || '') + ' / ' + (data.customer.vergiDairesi || ''));
            
            // Faturalardaki düzenleme işlemi için
            var musteriHtml = `
              <span><strong>${data.customer.adSoyad} (${musteriTipiText})</strong></span>
              <span>${data.customer.adres} ${data.customer.state.ilceName}/${data.customer.country.name}</span>
            `;
            if (data.customer.tcNo) {
              musteriHtml += `<span>TC: ${data.customer.tcNo}</span>`;
            }
            if (data.customer.vergiNo || data.customer.vergiDairesi) {
              musteriHtml += `<span>VERGİ NO/DAİRESİ: ${data.customer.vergiNo}/${data.customer.vergiDairesi}</span>`;
            }
            $('.kisaMusteriBil').html(musteriHtml);

            $('#editServiceCustomerModal').modal('hide');
            $('#editInvoiceCustomerModal').modal('hide');
            
            // DataTable varsa reload et
            if ($.fn.DataTable.isDataTable('#datatableService')) {
              $('#datatableService').DataTable().ajax.reload();
            }
            if ($.fn.DataTable.isDataTable('#datatableInvoice')) {
              $('#datatableInvoice').DataTable().ajax.reload();
            }
            
            $('.nav1').trigger('click');
            
            // Başarı mesajı
            alert('Müşteri bilgileri başarıyla güncellendi!');
          } else {
            alert(data.message || 'Bir hata oluştu');
          }
        },
        error: function (xhr, status, error) {
          console.error('Hata:', xhr.responseText);
          alert("Güncelleme başarısız! Lütfen tekrar deneyin.");
        },
      });
    });
  });
</script>