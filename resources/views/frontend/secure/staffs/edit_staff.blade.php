
<form method="post" id="editPers" action="{{ route('update.personel', [$firma->id, $staff->user_id]) }}" enctype="multipart/form-data">
    @csrf
    <div class="row">
      <label class="col-sm-4 custom-p-r">Başlama Tarihi<span style="font-weight: bold; color: red;">*</span></label>
      <div class="col-sm-8 custom-p-l">
        <input name="baslamaTarihi" class="form-control datepicker kayitTarihi" type="date" value="{{$staff->baslamaTarihi}}" style="border: 1px solid #ced4da;" required>
      </div>
    </div>
  
    <div class="row">
      <label class="col-sm-4 custom-p-r">Personel Durumu: </label>
      <div class="col-sm-8 custom-p-l">
        <select name="status" class="form-select durum" required>
          <option value="1" {{ $staff->status == "1" ? 'selected' : ''}}>Çalışıyor</option>
          <option value="0" {{ $staff->status == "0" ? 'selected' : ''}}>Ayrıldı</option>
        </select>
      </div>
    </div> <!--end row-->
  
    <div class="row ayrilmaTarihi">
      <label class="col-sm-4 custom-p-r">Ayrılma Tarihi:</label>
      <div class="col-sm-8 custom-p-l">
          <input name="ayrilmaTarihi" class="form-control datepicker ayrilmaTarihi" type="date" value="{{$staff->ayrilmaTarihi}}" style="border: 1px solid #ced4da;">
      </div>
    </div>
  
    <div class="row">
      <label class="col-sm-4 custom-p-r">Personel Adı<span style="font-weight: bold; color: red;">*</span></label>
      <div class="col-sm-8 custom-p-l">
        <input name="name" class="form-control" value="{{$staff->name}}" type="text" required>
      </div>
    </div>
  
    <div class="row">
      <label class="col-sm-4 custom-p-r">Telefon:</label>
      <div class="col-sm-8 custom-p-l">
        <input name="tel" class="form-control phone" value="{{$staff->tel}}" type="text" required>
      </div>
    </div>

    <div class="row">
        <div class="col-sm-4 custom-p-r"><label>İl/İlçe</label></div>
        <div class="col-sm-4 col-6 custom-p-r-m-md custom-p-l">
          <select name="il" id="countrySelect" class="form-control form-select" style="width:100%!important;">
            <option value="" selected disabled>-Seçiniz-</option>
            @foreach($countries as $item)
              <option value="{{ $item->id }}" {{ $staff->il == $item->id ? 'selected' : ''}}>{{ $item->name}}</option>
            @endforeach
          </select>
        </div>
        <div class="col-sm-4 col-6 custom-p-m-md custom-p-l">
          <select name="ilce" id="citySelect" class="form-control form-select" style="width:100%!important;">
            <option value="" selected disabled>-Seçiniz-</option>                              
          </select>
        </div>
      </div> 
  
    <div class="row">
      <label class="col-sm-4 custom-p-r">Adress:</label>
      <div class="col-sm-8 custom-p-l">
      <textarea name="address" type="text" class="form-control" rows="2">{{$staff->address}}</textarea>
      </div>
    </div>
  
    <div class="row">
      <label class="col-sm-4 custom-p-r">Personel Grubu<span style="font-weight: bold; color: red;">*</span></label>
      <div class="col-sm-8 custom-p-l">
        <select name="roles" class="form-select" required>
          <option selected value="">-Seçiniz-</option>
          @foreach($roles as $role)
            <option value="{{$role->id}}" {{ $staff->hasRole($role->name) ? 'selected' : ''}}>{{$role->name}}</option>
          @endforeach
        </select>
      </div>
    </div> <!--end row-->
  
    <div class="row">
      <label class="col-sm-4 custom-p-r">Kullanıcı Adı<span style="font-weight: bold; color: red;">*</span></label>
      <div class="col-sm-8 custom-p-l">
        <input name="username" id="editUsernameInput" class="form-control" value="{{$staff->username}}" type="text" required minlength="3" maxlength="50">
        <small id="editUsernameHelp" class="form-text text-muted">Sadece harf, rakam ve alt çizgi kullanabilirsiniz</small>
        <div id="editUsernameError" class="text-danger" style="display: none; margin-top: 5px;"></div>
        <div id="editUsernameSuccess" class="text-success" style="display: none; margin-top: 5px;"></div>
      </div>
    </div>
              
    <div class="row mb-3">
      <label class="col-sm-4 custom-p-r">Şifre</label>
      <div class="col-sm-8 custom-p-l">
        <input name="password" class="form-control" type="password" placeholder="**********">
      </div>
    </div>
  
    <div class="row">
      <div class="col-sm-12 gonderBtn">
        <input type="hidden" name="id" value="{{$staff->user_id}}">
        <input type="submit" class="btn btn-sm btn-info waves-effect waves-light" value="Kaydet">
      </div>
    </div>
  </form>
  
  <script>
    $(document).ready(function () {
      $(".phone").mask("999 999 9999");
    });
  </script>
  
  <script type="text/javascript">
  
    var getDurum = $(".durum").val();
    if(getDurum==1){
      $(".ayrilmaTarihi").hide();
    }else if(getDurum==0){
      $(".ayrilmaTarihi").show();
    }
  
    $(".durum").change(function(){
      var getDurum = $(".durum").val();
      console.log(getDurum);
      if(getDurum==1){
        $(".ayrilmaTarihi").hide();
      }else if(getDurum==0){
        $(".ayrilmaTarihi").show();
      }
    });
  
  </script>
  
  <script>
    $(document).ready(function () {
      $('#editPers').submit(function (event) {
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
      var selectedCountryId ={{ $staff->il == '' ? '0' : $staff->il}} ;
      if(selectedCountryId){
        $.get("/get-states/" + selectedCountryId, function(data) {
          $.each(data, function(index, city) {
            citySelect.append(new Option(city.ilceName, city.id));
            if(city.id == {{ $staff->ilce == '' ? '0' : $staff->ilce}}){
              $("#citySelect").val(city.id).change();
            } 
          });
        });
      }
      // Ülke seçildiğinde
      $("#countrySelect").change(function() {
        var selectedCountryId = $(this).val();
        // Şehirleri getir ve ikinci select'i güncelle
        $.get("/get-states/" + selectedCountryId, function(data) {
          var citySelect = $("#citySelect");
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
    $("#editPers").submit(function (event) {
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
            alert("Personel bilgileri güncellendi");
            $('#datatablePersonel').DataTable().ajax.reload();
            $('#editPersonelModal').modal('hide');
            
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
  <script>
$(document).ready(function() {
    let editUsernameCheckTimeout;
    let isEditUsernameValid = true; // Edit'te başlangıçta mevcut username geçerlidir
    const originalUsername = '{{$staff->username}}'; // Orijinal username'i sakla
    
    $('#editUsernameInput').on('input', function() {
        let username = $(this).val();
        
        // Format kontrolü
        username = username.replace(/[^a-zA-Z0-9_]/g, '');
        $(this).val(username);
        
        // Eğer orijinal username ise kontrol etmeye gerek yok
        if (username === originalUsername) {
            $('#editUsernameError').hide();
            $('#editUsernameSuccess').hide();
            isEditUsernameValid = true;
            return;
        }
        
        if (username.length === 0) {
            $('#editUsernameError').hide();
            $('#editUsernameSuccess').hide();
            isEditUsernameValid = false;
            return;
        }
        
        if (username.length < 3) {
            $('#editUsernameError').text('Kullanıcı adı en az 3 karakter olmalıdır.').show();
            $('#editUsernameSuccess').hide();
            isEditUsernameValid = false;
            return;
        }
        
        clearTimeout(editUsernameCheckTimeout);
        editUsernameCheckTimeout = setTimeout(function() {
            checkEditUsernameAvailability(username);
        }, 500);
    });
    
    function checkEditUsernameAvailability(username) {
        $.ajax({
            url: '{{ route("check.username.availability", $firma->id) }}',
            method: 'POST',
            data: {
                username: username,
                user_id: {{ $staff->user_id }}, // Mevcut kullanıcıyı hariç tut
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.available) {
                    $('#editUsernameError').hide();
                    //$('#editUsernameSuccess').text('✓ Kullanıcı adı kullanılabilir').show();
                    isEditUsernameValid = true;
                } else {
                    $('#editUsernameSuccess').hide();
                    $('#editUsernameError').text('✗ Bu kullanıcı adı zaten kullanılıyor').show();
                    isEditUsernameValid = false;
                }
            }
        });
    }
    
    // Form submit kontrolü
    $('#editPers').submit(function(event) {
        if (!isEditUsernameValid) {
            event.preventDefault();
            alert('Lütfen geçerli bir kullanıcı adı girin.');
            $('#editUsernameInput').focus();
            return false;
        }
    });
});
</script>