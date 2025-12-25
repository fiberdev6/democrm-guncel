
<form method="post" id="addPers" action="{{ route('store.staff',$firma->id)}}" enctype="multipart/form-data" >
  @csrf   
  <input type="hidden" name="form_token" id="formToken" value="">
  
  <div class="row">
    <label class="col-sm-4 custom-p-r">Başlama Tarihi<span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <input name="baslamaTarihi" class="form-control datepicker kayitTarihi" type="date" style="border: 1px solid #ced4da;" value="{{date('Y-m-d')}}" required>
    </div>
  </div>

  <div class="row">
    <label class="col-sm-4 custom-p-r">Personel Adı<span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <input name="name" class="form-control" type="text" required>
    </div>
  </div>

  <div class="row">
    <label class="col-sm-4 custom-p-r">Telefon<span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <input name="tel" class="form-control phone" type="text" required>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-4 custom-p-r"><label>İl/İlçe</label></div>
    <div class="col-sm-4 col-6 custom-p-r-m-md custom-p-l">
      <select name="il" id="countrySelect" class="form-control form-select" style="width:100%!important;">
        <option value="" selected disabled>-Seçiniz-</option>
        @foreach($countries as $item)
          <option value="{{ $item->id }}">{{ $item->name}}</option>
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
      <textarea name="address" type="text" class="form-control" rows="2"></textarea>
    </div>
  </div>

  <div class="row">
    <label class="col-sm-4 custom-p-r">Personel Grubu<span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <select name="roles" id="roleSelect" class="form-select" required>
        <option selected value="">-Seçiniz-</option>
        @foreach($roles as $role)
          <option value="{{$role->id}}" 
                  data-permissions='@json($role->permissions->pluck("name"))'>
            {{$role->name}}
          </option>
        @endforeach
      </select>
    </div>
  </div>

  <!-- İzinleri Gösterme Alanı -->
  <div class="row" id="permissionsArea" style="display: none; margin-top: 10px;">
    <label class="col-sm-4 custom-p-r">Yetkileri:</label>
    <div class="col-sm-8 custom-p-l">
      <div class="card">
        <div class="card-body" style="padding: 10px; max-height: 200px; overflow-y: auto;">
          <ul id="permissionsList" style="margin: 0; padding-left: 20px; list-style-type: disc;">
            <!-- İzinler buraya gelecek -->
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <label class="col-sm-4 custom-p-r">Kullanıcı Adı<span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <input name="username" id="usernameInput" class="form-control" type="text" required minlength="3" maxlength="50">
      <small id="usernameHelp" class="form-text text-muted">Sadece harf, rakam ve alt çizgi kullanabilirsiniz (3-50 karakter)</small>
      <div id="usernameError" class="text-danger" style="display: none; margin-top: 5px;"></div>
      <div id="usernameSuccess" class="text-success" style="display: none; margin-top: 5px;"></div>
    </div>
</div>
            
  <div class="row mb-3">
    <label class="col-sm-4 custom-p-r">Şifre:<span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <input name="password" class="form-control" type="password" required>
    </div>
  </div>

  <div class="row">               
    <div class="col-sm-12 gonderBtn">
      <input type="submit" class="btn btn-sm btn-info waves-effect waves-light" value="Kaydet">
    </div>
  </div>
</form>

<script>
  $(document).ready(function () {
    $(".phone").mask("999 999 9999");
  });
</script>

<script>
// Role değiştiğinde izinleri göster
$(document).ready(function() {
    $('#roleSelect').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var permissions = selectedOption.data('permissions');
        
        if (permissions && permissions.length > 0) {
            // İzinleri listele
            var permissionsHtml = '';
            permissions.forEach(function(permission) {
                // İzin isimlerini Türkçeleştir (isteğe bağlı)
                var permissionName = translatePermission(permission);
                permissionsHtml += '<li style="margin-bottom: 5px;">' + permissionName + '</li>';
            });
            
            $('#permissionsList').html(permissionsHtml);
            $('#permissionsArea').slideDown();
        } else {
            $('#permissionsArea').slideUp();
        }
    });
    
    // İzin isimlerini Türkçeleştirme fonksiyonu (isteğe bağlı)
    function translatePermission(permission) {
        var translations = {
            'Anasayfayı Görebilir': 'Anasayfayı Görebilir',
            'Servisleri Göremez': 'Servisleri Göremez',
            'Müşterileri Görebilir': 'Müşterileri Görebilir',
            'Personelleri Görebilir': 'Personelleri Görebilir',
            'Bayileri Görebilir': 'Bayileri Görebilir',
            'Depoyu Görebilir': 'Depoyu Görebilir',
            'Faturaları Görebilir': 'Faturaları Görebilir',
            'Teklifleri Görür': 'Teklifleri Görebilir',
            'İstatistikleri Görebilir': 'İstatistikleri Görebilir',
            'Kasayı Görebilir': 'Kasayı Görebilir',
            'Firmaları Görebilir': 'Firmaları Görebilir',
            'Servisleri Yazdırabilir': 'Servisleri Yazdırabilir',
            'Servisleri Silebilir': 'Servisleri Silebilir',
            'Faturaları Silebilir': 'Faturaları Silebilir',
            'Faturaları Düzenleyebilir': 'Faturaları Düzenleyebilir',
            'Teklifleri Silebilir': 'Teklifleri Silebilir',
            'Personelleri Ekleyebilir': 'Personelleri Ekleyebilir',
            'Personelleri Düzenleyebilir': 'Personelleri Düzenleyebilir',
            'Personelleri Silebilir': 'Personelleri Silebilir',
            'Bayileri Ekleyebilir': 'Bayileri Ekleyebilir',
            'Bayileri Düzenleyebilir': 'Bayileri Düzenleyebilir',
            'Bayileri Silebilir': 'Bayileri Silebilir'
        };
        
        return translations[permission] || permission;
    }
});
</script>

<script>
$(document).ready(function () {
    $('#addPers').submit(function (event) {
        var formIsValid = true;
        
        $(this).find('input, select').each(function () {
            var isRequired = $(this).prop('required');
            var isEmpty = !$(this).val();

            if (isRequired && isEmpty) {
                formIsValid = false;
                return false;
            }
        });

        var name = $('input[name="name"]').val();
        if (name && name.length < 2) {
            formIsValid = false;
            alert('Personel adı en az 2 karakter olmalıdır.');
            return false;
        }

        var username = $('input[name="username"]').val();
        if (username && username.length < 3) {
            formIsValid = false;
            alert('Kullanıcı adı en az 3 karakter olmalıdır.');
            return false;
        }

        if (!formIsValid) {
            event.preventDefault();
            if (!name || !username) {
                alert('Lütfen zorunlu alanları doldurun.');
            }
            return false;
        }
    });
});
</script>

<script>
$(document).ready(function() {
  $("#countrySelect").change(function() {
    var selectedCountryId = $(this).val();
    if (selectedCountryId) {
      loadCities(selectedCountryId);
    }
  });

  function loadCities(countryId) {
    var citySelect = $("#citySelect");
    citySelect.empty();
    citySelect.append(new Option("Yükleniyor...", ""));

    $.get("/get-states/" + countryId, function(data) {
      citySelect.empty();
      citySelect.append(new Option("-Seçiniz-", ""));
      $.each(data, function(index, city) {
        citySelect.append(new Option(city.ilceName, city.id));
      });
    }).fail(function() {
      citySelect.empty();
      citySelect.append(new Option("Unable to load cities", ""));
    });
  }
});
</script>

<script>
$(document).ready(function() {
    let formSubmitting = false;
    
    function generateToken() {
        return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    $('#formToken').val(generateToken());
    
    $('#addPers').submit(function(event) {
        if (formSubmitting) {
            event.preventDefault();
            return false;
        }
        
        var formIsValid = true;
        
        $(this).find('input, select').each(function () {
            var isRequired = $(this).prop('required');
            var isEmpty = !$(this).val();

            if (isRequired && isEmpty) {
                formIsValid = false;
                return false;
            }
        });

        var name = $('input[name="name"]').val();
        if (name && name.length < 2) {
            formIsValid = false;
            alert('Personel adı en az 2 karakter olmalıdır.');
            return false;
        }

        var username = $('input[name="username"]').val();
        if (username && username.length < 3) {
            formIsValid = false;
            alert('Kullanıcı adı en az 3 karakter olmalıdır.');
            return false;
        }

        if (!formIsValid) {
            event.preventDefault();
            if (!name || !username) {
                alert('Lütfen zorunlu alanları doldurun.');
            }
            return false;
        }
        
        formSubmitting = true;
        $(this).find('input[type="submit"]').prop('disabled', true);
        
        setTimeout(function() {
            $('#formToken').val(generateToken());
            formSubmitting = false;
            $('#addPers input[type="submit"]').prop('disabled', false);
        }, 3000);
        
        return true;
    });
});
</script>

<script>
$(document).ready(function() {
    let isSubmitting = false;
    let shouldReload = false;
    
    $('#addPers').submit(function() {
        isSubmitting = true;
    });
    
    $('#addPersonelModal').on('hide.bs.modal', function(e) {
        if (isSubmitting) {
            isSubmitting = false;
            return true;
        }
        
        if (!confirm('Kapatmak istediğinizden emin misiniz?')) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        
        shouldReload = true;
        isSubmitting = false;
    });
    
    $('#addPersonelModal').on('hidden.bs.modal', function() {
        isSubmitting = false;
        if (shouldReload) {
            shouldReload = false;
            location.reload();
        }
    });
});
</script>
<script>
$(document).ready(function() {
    let usernameCheckTimeout;
    let isUsernameValid = false;
    
    // Username format kontrolü ve AJAX kontrolü
    $('#usernameInput').on('input', function() {
        let username = $(this).val();
        
        // Format kontrolü (sadece harf, rakam ve alt çizgi)
        username = username.replace(/[^a-zA-Z0-9_]/g, '');
        $(this).val(username);
        
        // Boşsa kontrol etme
        if (username.length === 0) {
            $('#usernameError').hide();
            $('#usernameSuccess').hide();
            isUsernameValid = false;
            return;
        }
        
        // Minimum karakter kontrolü
        if (username.length < 3) {
            $('#usernameError').text('Kullanıcı adı en az 3 karakter olmalıdır.').show();
            $('#usernameSuccess').hide();
            isUsernameValid = false;
            return;
        }
        
        // Debounce: Her tuş vuruşunda istek atmamak için bekle
        clearTimeout(usernameCheckTimeout);
        usernameCheckTimeout = setTimeout(function() {
            checkUsernameAvailability(username);
        }, 500);
    });
    
    // AJAX ile username kontrolü
    function checkUsernameAvailability(username) {
        $.ajax({
            url: '{{ route("check.username.availability", $firma->id) }}',
            method: 'POST',
            data: {
                username: username,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.available) {
                    $('#usernameError').hide();
                    //$('#usernameSuccess').text('✓ Kullanıcı adı kullanılabilir').show();
                    isUsernameValid = true;
                } else {
                    $('#usernameSuccess').hide();
                    $('#usernameError').text('✗ Bu kullanıcı adı zaten kullanılıyor').show();
                    isUsernameValid = false;
                }
            },
            error: function() {
                $('#usernameError').text('Kontrol yapılırken hata oluştu').show();
                $('#usernameSuccess').hide();
                isUsernameValid = false;
            }
        });
    }
    
    // Form submit kontrolü
    $('#addPers').submit(function(event) {
        if (!isUsernameValid) {
            event.preventDefault();
            alert('Lütfen geçerli bir kullanıcı adı girin.');
            $('#usernameInput').focus();
            return false;
        }
        
        // Diğer validasyonlar...
        var formIsValid = true;
        
        $(this).find('input[required], select[required]').each(function () {
            if (!$(this).val()) {
                formIsValid = false;
                return false;
            }
        });

        if (!formIsValid) {
            event.preventDefault();
            alert('Lütfen zorunlu alanları doldurun.');
            return false;
        }
        
        // Form gönderiliyorsa submit butonunu devre dışı bırak
        $(this).find('input[type="submit"]').prop('disabled', true);
    });
});
</script>