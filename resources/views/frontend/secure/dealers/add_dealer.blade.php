<form method="post" id="addBayi" action="{{ route('store.dealer',$firma->id)}}" enctype="multipart/form-data" >
  @csrf   
  <input type="hidden" name="form_token" id="formToken" value="">
  <div class="row">
    <label class="col-sm-4 custom-p-r">Başlama Tarihi<span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <input name="baslamaTarihi" class="form-control datepicker kayitTarihi" type="date" style="border: 1px solid #ced4da;" value="{{date('Y-m-d')}}" required>
    </div>
  </div>

  <div class="row">
    <label class="col-sm-4 custom-p-r">Ad Soyad<span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <input name="name" class="form-control" type="text" required>
    </div>
  </div>

<div class="row">
  <label class="col-sm-4 custom-p-r">Vergi No/Dairesi<span style="font-weight: bold; color: red;">*</span></label>
  <div class="col-sm-4 col-6 custom-p-r-m-md custom-p-l">
    <input name="vergiNo" id="vergiNo" class="form-control" type="text" required>
  </div>
  <div class="col-sm-4 col-6 custom-p-m-md custom-p-l">
    <input name="vergiDairesi" class="form-control" type="text" required>
  </div>
</div>

  <div class="row">
    <label class="col-sm-4 custom-p-r">Bayi Belgesi<span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <input name="belgePdf[]" id="belgePdf" class="form-control" type="file" accept=".pdf,.jpg,.jpeg,.png,.svg" multiple required>
      <small class="text-muted">Maksimum 2 dosya seçebilirsiniz. PDF, JPG, PNG, SVG formatları kabul edilir.</small>
    </div>
  </div>

  <div class="row">
    <label class="col-sm-4 custom-p-r">Telefon<span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <input name="tel" id="tel" class="form-control phone" type="text" required>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-4 custom-p-r"><label>İl/İlçe<span style="font-weight: bold; color: red;">*</span></label></div>
    <div class="col-sm-4 col-6 custom-p-r-m-md custom-p-l">
      <select name="il" id="countrySelect" class="form-control form-select" style="width:100%!important;" required>
        <option value="" selected disabled>-Seçiniz-</option>
        @foreach($countries as $item)
          <option value="{{ $item->id }}">{{ $item->name}}</option>
        @endforeach
      </select>
    </div>
    <div class="col-sm-4 col-6 custom-p-m-md custom-p-l">
      <select name="ilce" id="citySelect" class="form-control form-select" style="width:100%!important;" required>
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
    <label class="col-sm-4 custom-p-r">Kullanıcı Adı<span style="font-weight: bold; color: red;">*</span></label>
    <div class="col-sm-8 custom-p-l">
      <input name="username" id="dealerUsernameInput" class="form-control" type="text" required minlength="3" maxlength="50">
      <small id="dealerUsernameHelp" class="form-text text-muted">Sadece harf, rakam ve alt çizgi kullanabilirsiniz (3-50 karakter)</small>
      <div id="dealerUsernameError" class="text-danger" style="display: none; margin-top: 5px;"></div>
      <div id="dealerUsernameSuccess" class="text-success" style="display: none; margin-top: 5px;"></div>
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
    $(document).ready(function(){
    $("#vergiNo").mask("9999999999");
  });
</script>
{{-- <script>
  // Maksimum 2 dosya kontrolü
  $(document).ready(function() {
    $('#belgePdf').on('change', function() {
      if (this.files.length > 2) {
        alert('Maksimum 2 dosya seçebilirsiniz!');
        this.value = '';
      }
    });
  });
</script> --}}

<script>
  $(document).ready(function() {
    // İzin verilen dosya türleri
    const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'svg'];
    const allowedMimeTypes = [
        'application/pdf',
        'image/jpeg',
        'image/jpg', 
        'image/png',
        'image/svg+xml'
    ];

    $('#belgePdf').on('change', function() {
        const files = this.files;
        
        // Maksimum 2 dosya kontrolü
        if (files.length > 2) {
            alert('Maksimum 2 dosya seçebilirsiniz!');
            this.value = '';
            return false;
        }

        // Her dosyanın türünü kontrol et
        let invalidFiles = [];
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileName = file.name.toLowerCase();
            const fileExtension = fileName.split('.').pop();
            const fileMimeType = file.type;

            // Uzantı kontrolü
            if (!allowedExtensions.includes(fileExtension)) {
                invalidFiles.push(file.name);
            }
            
            // MIME type kontrolü (ekstra güvenlik)
            if (!allowedMimeTypes.includes(fileMimeType)) {
                if (!invalidFiles.includes(file.name)) {
                    invalidFiles.push(file.name);
                }
            }
        }

        // Geçersiz dosya varsa
        if (invalidFiles.length > 0) {
            alert('❌ Hatalı dosya türü!\n\n' + 
                  'Geçersiz dosyalar:\n• ' + invalidFiles.join('• ') + 
                  '\n✅ Sadece PDF, JPG, JPEG, PNG ve SVG dosyaları yükleyebilirsiniz.');
            this.value = ''; // Seçimi temizle
            return false;
        }

        // Dosya boyutu kontrolü (5MB)
        const maxSize = 5 * 1024 * 1024; // 5MB
        let oversizedFiles = [];
        
        for (let i = 0; i < files.length; i++) {
            if (files[i].size > maxSize) {
                oversizedFiles.push(files[i].name + ' (' + (files[i].size / 1024 / 1024).toFixed(2) + ' MB)');
            }
        }

        if (oversizedFiles.length > 0) {
            alert('❌ Dosya boyutu çok büyük!\n\n' + 
                  'Büyük dosyalar:\n• ' + oversizedFiles.join('\n• ') + 
                  '\n\n✅ Her dosya maksimum 5MB olmalıdır.');
            this.value = '';
            return false;
        }

        // Başarılı - kullanıcıya bilgi ver
        let fileNames = [];
        for (let i = 0; i < files.length; i++) {
            fileNames.push(files[i].name);
        }
        console.log('✅ Seçilen dosyalar: ', fileNames.join(', '));
    });
  });
</script>

<script>
$(document).ready(function () {
    $('#addBayi').submit(function (event) {
        var formIsValid = true;
        
        // Mevcut validation kodları...
        $(this).find('input, select').each(function () {
            var isRequired = $(this).prop('required');
            var isEmpty = !$(this).val();

            if (isRequired && isEmpty) {
                formIsValid = false;
                return false;
            }
        });

        // İsim uzunluğu kontrolü
        var name = $('input[name="name"]').val();
        if (name && name.length < 2) {
            formIsValid = false;
            alert('Bayi adı en az 2 karakter olmalıdır.');
            return false;
        }

        // Vergi numarası kontrolü
        var vergiNo = $('input[name="vergiNo"]').val();
        if (vergiNo && vergiNo.length !== 10) {
            formIsValid = false;
            alert('Vergi numarası 10 haneli olmalıdır.');
            return false;
        }

        if (!formIsValid) {
            event.preventDefault();
            if (!name) {
                alert('Lütfen zorunlu alanları doldurun.');
            }
            return false;
        }
    });
});
</script>

<script>
$(document).ready(function() {
  // Ülke seçildiğinde şehirleri getir
  $("#countrySelect").change(function() {  
    var selectedCountryId = $(this).val();
    if (selectedCountryId) {
      loadCities(selectedCountryId);
    }
  });

  // Şehirleri yüklemek için kullanılan fonksiyon
  function loadCities(countryId) {
    var citySelect = $("#citySelect");
    citySelect.empty(); // Önceki seçenekleri temizle
    citySelect.append(new Option("Yükleniyor...", "")); // Kullanıcıya yükleniyor bilgisi ver

    // AJAX isteğiyle şehirleri al
    $.get("/get-states/" + countryId, function(data) {
      citySelect.empty(); // Yükleniyor mesajını temizle
      citySelect.append(new Option("-Seçiniz-", "")); // İlk boş seçeneği ekle
      $.each(data, function(index, city) {
        citySelect.append(new Option(city.ilceName, city.id));
      });
    }).fail(function() {
      citySelect.empty(); // Hata durumunda temizle
      citySelect.append(new Option("Unable to load cities", ""));
    });
  }
});
</script>
<script>
$(document).ready(function() {
    let dealerUsernameCheckTimeout;
    let isDealerUsernameValid = false;
    
    // Username format kontrolü ve AJAX kontrolü
    $('#dealerUsernameInput').on('input', function() {
        let username = $(this).val();
        
        // Format kontrolü (sadece harf, rakam ve alt çizgi)
        username = username.replace(/[^a-zA-Z0-9_]/g, '');
        $(this).val(username);
        
        // Boşsa kontrol etme
        if (username.length === 0) {
            $('#dealerUsernameError').hide();
            $('#dealerUsernameSuccess').hide();
            isDealerUsernameValid = false;
            return;
        }
        
        // Minimum karakter kontrolü
        if (username.length < 3) {
            $('#dealerUsernameError').text('Kullanıcı adı en az 3 karakter olmalıdır.').show();
            $('#dealerUsernameSuccess').hide();
            isDealerUsernameValid = false;
            return;
        }
        
        // Debounce
        clearTimeout(dealerUsernameCheckTimeout);
        dealerUsernameCheckTimeout = setTimeout(function() {
            checkDealerUsernameAvailability(username);
        }, 500);
    });
    
    // AJAX ile username kontrolü
    function checkDealerUsernameAvailability(username) {
        $.ajax({
            url: '{{ route("check.username.availability", $firma->id) }}',
            method: 'POST',
            data: {
                username: username,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.available) {
                    $('#dealerUsernameError').hide();
                    //$('#dealerUsernameSuccess').text('✓ Kullanıcı adı kullanılabilir').show();
                    isDealerUsernameValid = true;
                } else {
                    $('#dealerUsernameSuccess').hide();
                    $('#dealerUsernameError').text('✗ Bu kullanıcı adı zaten kullanılıyor').show();
                    isDealerUsernameValid = false;
                }
            },
            error: function() {
                $('#dealerUsernameError').text('Kontrol yapılırken hata oluştu').show();
                $('#dealerUsernameSuccess').hide();
                isDealerUsernameValid = false;
            }
        });
    }
    
    // Mevcut form submit kontrolüne ekle
    $('#addBayi').submit(function(event) {
        // Username kontrolü ekle
        if (!isDealerUsernameValid) {
            event.preventDefault();
            alert('Lütfen geçerli bir kullanıcı adı girin.');
            $('#dealerUsernameInput').focus();
            return false;
        }
        
        // ... mevcut validasyonlar devam eder
    });
});
</script>
<script>
$(document).ready(function() {
    let formSubmitting = false;
    
    function generateToken() {
        return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    $('#formToken').val(generateToken());
    
    $('#addBayi').submit(function(event) {
        // Token kontrolü
        if (formSubmitting) {
            event.preventDefault();
            return false;
        }
        
        // Username kontrolü - global değişkeni kontrol et
        if (typeof isDealerUsernameValid !== 'undefined' && !isDealerUsernameValid) {
            event.preventDefault();
            alert('Lütfen geçerli bir kullanıcı adı girin.');
            $('#dealerUsernameInput').focus();
            return false;
        }
        
        var formIsValid = true;
        
        $(this).find('input[required], select[required]').each(function () {
            if (!$(this).val()) {
                formIsValid = false;
                return false;
            }
        });

        // İsim uzunluğu kontrolü
        var name = $('input[name="name"]').val();
        if (name && name.length < 2) {
            formIsValid = false;
            alert('Bayi adı en az 2 karakter olmalıdır.');
            return false;
        }

        // Kullanıcı adı uzunluğu kontrolü
        var username = $('#dealerUsernameInput').val();
        if (username && username.length < 3) {
            formIsValid = false;
            alert('Kullanıcı adı en az 3 karakter olmalıdır.');
            return false;
        }

        // Vergi numarası kontrolü
        var vergiNo = $('input[name="vergiNo"]').val();
        if (vergiNo && vergiNo.length !== 10) {
            formIsValid = false;
            alert('Vergi numarası 10 haneli olmalıdır.');
            return false;
        }

        if (!formIsValid) {
            event.preventDefault();
            alert('Lütfen zorunlu alanları doldurun.');
            return false;
        }
        
        formSubmitting = true;
        $(this).find('input[type="submit"]').prop('disabled', true);
        
        setTimeout(function() {
            $('#formToken').val(generateToken());
            formSubmitting = false;
            $('#addBayi input[type="submit"]').prop('disabled', false);
        }, 5000);
        
        return true;
    });
});
</script>
<script>
$(document).ready(function() {
    let isSubmitting = false;
    let shouldReload = false;
    
    // Form submit edildiğinde flag'i ayarla
    $('#addBayi').submit(function() {
        isSubmitting = true;
    });
    
    // Modal kapatılmaya çalışıldığında
    $('#addBayiModal').on('hide.bs.modal', function(e) {
        if (isSubmitting) {
            isSubmitting = false;
            return true;
        }
        
        // Her zaman onay iste
        if (!confirm('Kapatmak istediğinizden emin misiniz?')) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        
        shouldReload = true; // Tamam'a basıldı, yenile
        isSubmitting = false;
    });
    
    // Modal tamamen kapandığında sayfayı yenile
    $('#addBayiModal').on('hidden.bs.modal', function() {
        isSubmitting = false;
        if (shouldReload) {
            shouldReload = false;
            location.reload(); // Sayfayı yenile
        }
    });
});
</script>
