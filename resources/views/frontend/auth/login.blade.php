<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serbis - Giriş / Kayıt</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- jQuery Mask Plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://www.google.com/recaptcha/enterprise.js" async defer></script>
    
    <link href="{{asset('frontend/css/login.css')}}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* Şifre Güvenliği Göstergesi Stilleri */
        .password-strength-container {
            margin-top: 8px;
        }
        .password-strength-bar {
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 5px;
        }
        .password-strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }
        .password-strength-text {
            font-size: 0.75rem;
            font-weight: 500;
        }
        .strength-weak { background: #dc3545; }
        .strength-fair { background: #fd7e14; }
        .strength-good { background: #ffc107; }
        .strength-strong { background: #28a745; }
        .text-weak { color: #dc3545; }
        .text-fair { color: #fd7e14; }
        .text-good { color: #ffc107; }
        .text-strong { color: #28a745; }
        
        .password-requirements {
            margin-top: 8px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
            font-size: 0.75rem;
        }
        .password-requirements ul {
            margin: 0;
            padding-left: 18px;
        }
        .password-requirements li {
            color: #6c757d;
            margin-bottom: 2px;
        }
        .password-requirements li.valid {
            color: #28a745;
        }
        .password-requirements li.valid::marker {
            content: "✓ ";
        }
    </style>
   
</head>
<body>
    <div class="main-container">
        <div class="auth-card">
            <!-- Logo Section -->
            <div class="logo-section">
                <div style="display: flex; align-items: center; justify-content: center; gap: 3px;">
                    <img src="{{ asset('frontend/img/logo_son.png') }}" alt="Serbis Logo" >
                </div>
            </div>

            <!-- Form Container -->
            <div class="form-container">
                <!-- Form Toggle -->
                <div class="form-toggle">
                    <div class="toggle-slider" id="toggleSlider"></div>
                    <button class="toggle-btn active" id="loginToggle">Giriş Yap</button>
                    <button class="toggle-btn" id="registerToggle">Kayıt Ol</button>
                </div>

                <!-- Login Form -->
                <div class="form-section active" id="loginForm">
                    <form method="POST" action="{{ route('giris.action') }}">
                        @csrf
                        <h4 class="text-center mb-3" style="color: #333; font-weight: 600;">Hoş Geldiniz</h4>
                        <p class="text-center mb-3" style="color: #666; font-size: 0.9rem;">
                            Kullanıcı adı, firma kodu ve şifreniz ile güvenli giriş yapabilirsiniz.
                        </p>

                        <!-- Success/Error Messages -->
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @error('username')
                            <div class="alert alert-danger">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="mb-3">
                           
                            <input type="text" name="username" id="loginUsername" class="form-control" 
                                   placeholder="Kullanıcı Adı" required value="{{ old('username') }}">
                        </div>

                        <div class="mb-3">
                           
                            <div class="input-wrapper">
                                <input type="text" name="firma_kodu" id="loginFirmaKodu" class="form-control firma-kodu" 
                                       placeholder="Firma Kodu" required maxlength="6" value="{{ old('firma_kodu') }}">
                                <div class="info-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" 
                                         class="bi bi-info-circle-fill" viewBox="0 0 16 16">
                                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                                    </svg>
                                    <span class="tooltip-text">
                                        Firma kodu, kayıt sırasında size verilen 6 haneli benzersiz numaradır.
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            
                            <input type="password" name="password" id="loginPassword" class="form-control" 
                                   placeholder="Şifre" required>
                        </div>

                        <!-- Şifremi Unuttum Linki -->
                        <div class="mb-3 d-flex justify-content-end align-items-center" style="gap: 8px;">
                            <a href="{{ route('password.request') }}" style="color: #3e546a; font-size: 0.9rem; text-decoration: none;">
                                Şifremi Unuttum
                            </a>
                            <div class="info-icon" style="position: relative; display: inline-block;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" 
                                    class="bi bi-info-circle-fill" viewBox="0 0 16 16">
                                    <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                                </svg>
                                <span class="tooltip-text">
                                    Sadece firma sahibi (Patron) şifresini sıfırlayabilir. Çalışanlar için şifre sıfırlama işlemini firma sahibiniz yapmalıdır.
                                </span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="g-recaptcha" data-sitekey="6Ldl86UrAAAAAIo9asM85k5ajB363yYtf8FuKQgu" data-action="LOGIN"></div>
                            @if($errors->has('g-recaptcha-response'))
                                <div class="text-danger mt-2">
                                    <small>{{$errors->first('g-recaptcha-response')}}</small>
                                </div>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">Giriş Yap</button>
                        
                        <p class="text-center mb-0" style="color: #666; font-size: 0.9rem;">
                            Hesabınız yok mu? 
                            <a href="#" id="switchToRegister" style="color: #3e546a; font-weight: 600; text-decoration: none;">
                                Kayıt Ol
                            </a>
                        </p>
                    </form>
                </div>

                <!-- Register Form -->
                <div class="form-section" id="registerForm">
                    <!-- Multi-step register form -->
                    <form id="multiStepForm" method="POST">
                        @csrf
                        <h4 class="text-center mb-4" style="color: #333; font-weight: 600;">Hesap Oluşturun</h4>

                        <!-- Step Indicators -->
                        <div class="step-indicator">
                            <div class="step active" data-step="1">
                                <div class="step-icon">1</div>
                                <div class="step-label">Plan</div>
                                <div class="step-connector"></div>
                            </div>
                            <div class="step" data-step="2">
                                <div class="step-icon">2</div>
                                <div class="step-label">Kişisel</div>
                                <div class="step-connector"></div>
                            </div>
                            <div class="step" data-step="3">
                                <div class="step-icon">3</div>
                                <div class="step-label">Firma</div>
                                <div class="step-connector"></div>
                            </div>
                            <div class="step" data-step="4">
                                <div class="step-icon">4</div>
                                <div class="step-label">SMS</div>
                            </div>
                        </div>

                        <!-- Step 1: Plan Selection -->
                        <div class="form-step active">
                            <div class="mb-3">
                                <label for="subscription_plan" class="form-label" style="color: #333; font-weight: 500;">
                                    Abonelik Planı Seçin <span style="color: #dc3545;">*</span>
                                </label>
                                <select name="subscription_plan" id="subscription_plan" class="form-select" required>
                                    <option value="">Plan Seçiniz...</option>
                                    <!-- Plans will be loaded via JavaScript -->
                                </select>
                                <div class="trial-info" style="margin-top: 12px;
                                    padding: 12px 16px;
                                    background: linear-gradient(135deg, #3e546a 0%, #4a6280 100%);
                                    border-radius: 8px;
                                    display: flex;
                                    align-items: center;
                                    gap: 10px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white" viewBox="0 0 16 16">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                    </svg>
                                    <span style="color: white; font-size: 0.9rem; font-weight: 500;">
                                        14 gün boyunca ücretsiz
                                    </span>
                                </div>
                                <div id="planInfo" class="plan-info" style="display: none;">
                                    <div class="plan-features"></div>
                                </div>
                                @error('subscription_plan')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Step 2: Personal Information -->
                        <div class="form-step">
                            <div class="mb-3">
                                <label for="vergiNo" class="form-label" style="color: #333; font-weight: 500;">
                                    Vergi Numarası <span style="color: #dc3545;">*</span>
                                </label>
                                <input type="text" name="vergiNo" id="vergiNo" class="form-control vergiNo" 
                                       placeholder="Vergi Numarası" required>
                                @error('vergiNo')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label" style="color: #333; font-weight: 500;">
                                    Ad Soyad <span style="color: #dc3545;">*</span>
                                </label>
                                <input type="text" name="name" id="name" class="form-control" 
                                       placeholder="Ad Soyad" required>
                                @error('name')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label" style="color: #333; font-weight: 500;">
                                    Kullanıcı Adı <span style="color: #dc3545;">*</span>
                                </label>
                                <input type="text" name="username" id="username" class="form-control" 
                                       placeholder="Kullanıcı Adı" required minlength="3" maxlength="50">
                                <small class="form-text" style="color: #6c757d;">Sadece harf, rakam ve alt çizgi kullanabilirsiniz.</small>
                                @error('username')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label" style="color: #333; font-weight: 500;">
                                    E-posta Adresiniz <span style="color: #dc3545;">*</span>
                                </label>
                                <input type="email" name="email" id="registerEmail" class="form-control" 
                                       placeholder="E-posta" required>
                                @error('email')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Step 3: Company Information & Password -->
                        <div class="form-step">
                            <div class="mb-3">
                                <label for="firma_adi" class="form-label" style="color: #333; font-weight: 500;">
                                    Firma Adı <span style="color: #dc3545;">*</span>
                                </label>
                                <input type="text" name="firma_adi" id="firma_adi" maxlength="50" 
                                       class="form-control" placeholder="Firma Adı" required>
                                <small id="firmaAdiCounter" class="character-counter">0 / 50</small>
                                @error('firma_adi')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Sektör Seçimi - YENİ -->
                            <div class="mb-3">
                                <label for="sektor" class="form-label" style="color: #333; font-weight: 500;">
                                    Sektör <span style="color: #dc3545;">*</span>
                                </label>
                                <select name="sektor" id="sektor" class="form-select" required>
                                    <option value="">Sektör Seçiniz...</option>
                                    <!-- Sektörler JavaScript ile yüklenecek -->
                                </select>
                                @error('sektor')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- İl ve İlçe Seçimi - Yan Yana -->
                            <div class="mb-3">
                                <label class="form-label" style="color: #333; font-weight: 500;">
                                    İl / İlçe <span style="color: #dc3545;">*</span>
                                </label>
                                <div style="display: flex; gap: 10px;">
                                    <div style="flex: 1;">
                                        <select name="il_id" id="il_id" class="form-select" required>
                                            <option value="">İl Seçiniz...</option>
                                            <!-- İller JavaScript ile yüklenecek -->
                                        </select>
                                        @error('il_id')
                                        <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div style="flex: 1;">
                                        <select name="ilce_id" id="ilce_id" class="form-select" required disabled>
                                            <option value="">Önce İl Seçiniz...</option>
                                        </select>
                                        @error('ilce_id')
                                        <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Açık Adres - YENİ -->
                            <div class="mb-3">
                                <label for="adres" class="form-label" style="color: #333; font-weight: 500;">
                                    Açık Adres
                                </label>
                                <textarea name="adres" id="adres" class="form-control" rows="2" 
                                          placeholder="Mahalle, Sokak, Bina No, Daire No..." maxlength="255"></textarea>
                                <small id="adresCounter" class="character-counter">0 / 255</small>
                                @error('adres')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="tel" class="form-label" style="color: #333; font-weight: 500;">
                                    Firma Telefon Numarası <span style="color: #dc3545;">*</span>
                                </label>
                                <input type="text" name="tel" id="tel" class="form-control tel" 
                                       placeholder="5xx xxx xx xx" required>
                                @error('tel')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label" style="color: #333; font-weight: 500;">
                                    Şifre <span style="color: #dc3545;">*</span>
                                </label>
                                <input type="password" name="password" id="registerPassword" class="form-control" 
                                       placeholder="Şifre" required minlength="6">
                                
                                <!-- Şifre Güvenliği Göstergesi - YENİ -->
                                <div class="password-strength-container">
                                    <div class="password-strength-bar">
                                        <div class="password-strength-fill" id="passwordStrengthFill"></div>
                                    </div>
                                    <div class="password-strength-text" id="passwordStrengthText"></div>
                                </div>
                                
                                <!-- Şifre Gereksinimleri - YENİ -->
                                <div class="password-requirements" id="passwordRequirements">
                                    <ul>
                                        <li id="req-length">En az 6 karakter</li>
                                        <li id="req-uppercase">En az 1 büyük harf (A-Z)</li>
                                        <li id="req-lowercase">En az 1 küçük harf (a-z)</li>
                                        <li id="req-number">En az 1 rakam (0-9)</li>
                                        <li id="req-special">En az 1 özel karakter (!@#$%^&*)</li>
                                    </ul>
                                </div>
                                
                                @error('password')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label" style="color: #333; font-weight: 500;">
                                    Şifre Tekrar <span style="color: #dc3545;">*</span>
                                </label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" 
                                       placeholder="Şifre Tekrar" required minlength="6">
                                <small class="form-text" style="color: #6c757d;">Lütfen şifrenizi tekrar giriniz.</small>
                            </div>
                        </div>

                        <!-- Step 4: SMS Verification -->
                        <div class="form-step">
                            <div class="sms-info">
                                <h5>SMS Doğrulama</h5>
                                <p id="smsInfoText" style="margin: 0; color: #666;">
                                    Lütfen <strong id="phoneDisplay">+90 --- --- -- --</strong> numaralı telefona gönderilen 6 haneli doğrulama kodunu giriniz.
                                </p>
                            </div>

                            <div class="countdown-timer" id="countdownTimer" style="display: none;">
                                <p style="margin: 0; color: #666;">Kalan süre:</p>
                                <span class="timer" id="countdown">3:00</span>
                            </div>

                            <div class="mb-3">
                                <label for="smsCode" class="form-label" style="color: #333; font-weight: 500;">
                                    Doğrulama Kodu <span style="color: #dc3545;">*</span>
                                </label>
                                <input type="text" name="smsCode" id="smsCode" class="form-control" 
                                       placeholder="6 haneli kod" required maxlength="6">
                                <div id="smsCodeError" class="text-danger mt-2"></div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="button-group">
                            <button type="button" class="btn btn-secondary btn-sm" id="prevBtn" style="display: none;">
                                ← Geri
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" id="nextBtn" style="flex: 1;">
                                İleri →
                            </button>
                        </div>

                        <p class="text-center mt-3 mb-0" style="color: #666; font-size: 0.9rem;">
                            Zaten hesabın var mı? 
                            <a href="#" id="switchToLogin" style="color: #3e546a; font-weight: 600; text-decoration: none;">
                                Giriş Yap
                            </a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $(document).ready(function() {
            // Load subscription plans on page load
            loadSubscriptionPlans();
            
            // Sektörleri yükle
            loadSectors();

            // Input masks
            $(".tel").mask("999 999 99 99");
            $(".vergiNo").mask("0000000000");
            $(".firma-kodu").mask("000000");
            $("#smsCode").mask("000000");

            // Username validation - sadece harf, rakam ve alt çizgi
            $('#username').on('input', function() {
                let value = $(this).val();
                // Türkçe karakterleri değiştir
                value = value.replace(/[İıĞğÜüŞşÖöÇç]/g, function(match) {
                    const map = {
                        'İ': 'I', 'ı': 'i', 'Ğ': 'G', 'ğ': 'g',
                        'Ü': 'U', 'ü': 'u', 'Ş': 'S', 'ş': 's',
                        'Ö': 'O', 'ö': 'o', 'Ç': 'C', 'ç': 'c'
                    };
                    return map[match];
                });
                // Sadece harf, rakam ve alt çizgi bırak
                value = value.replace(/[^a-zA-Z0-9_]/g, '');
                $(this).val(value);
            });

            // Açık adres karakter sayacı
            $('#adres').on('input', function() {
                var currentLength = $(this).val().length;
                var maxLength = $(this).attr('maxlength');
                $('#adresCounter').text(currentLength + " / " + maxLength);
                
                if (currentLength >= maxLength) {
                    $('#adresCounter').removeClass('text-muted').addClass('text-danger');
                } else {
                    $('#adresCounter').removeClass('text-danger').addClass('text-muted');
                }
            });

            // Şifre güvenliği kontrolü - YENİ
            $('#registerPassword').on('input', function() {
                const password = $(this).val();
                checkPasswordStrength(password);
            });

            // Password confirmation validation
            $('#password_confirmation').on('input', function() {
                const password = $('#registerPassword').val();
                const confirmation = $(this).val();
                
                if (confirmation && password !== confirmation) {
                    $(this).addClass('is-invalid');
                    if (!$(this).siblings('.text-danger').length) {
                        $(this).after('<small class="text-danger">Şifreler eşleşmiyor.</small>');
                    }
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).siblings('.text-danger').remove();
                }
            });

            $('#registerPassword').on('input', function() {
                const password = $(this).val();
                const confirmation = $('#password_confirmation').val();
                
                if (confirmation && password !== confirmation) {
                    $('#password_confirmation').addClass('is-invalid');
                    if (!$('#password_confirmation').siblings('.text-danger').length) {
                        $('#password_confirmation').after('<small class="text-danger">Şifreler eşleşmiyor.</small>');
                    }
                } else {
                    $('#password_confirmation').removeClass('is-invalid');
                    $('#password_confirmation').siblings('.text-danger').remove();
                }
            });

            // Character counter for company name
            $('#firma_adi').on('input', function() {
                var currentLength = $(this).val().length;
                var maxLength = $(this).attr('maxlength');
                $('#firmaAdiCounter').text(currentLength + " / " + maxLength);
                
                if (currentLength >= maxLength) {
                    $('#firmaAdiCounter').removeClass('text-muted').addClass('text-danger');
                } else {
                    $('#firmaAdiCounter').removeClass('text-danger').addClass('text-muted');
                }
            });

            // Form toggle functionality
            let isLoginMode = true;
            
            $('#loginToggle, #switchToLogin').on('click', function(e) {
                e.preventDefault();
                if (!isLoginMode) {
                    switchToLogin();
                }
            });
            
            $('#registerToggle, #switchToRegister').on('click', function(e) {
                e.preventDefault();
                if (isLoginMode) {
                    switchToRegister();
                }
            });

            function switchToLogin() {
                isLoginMode = true;
                $('#toggleSlider').removeClass('register');
                $('#loginToggle').addClass('active');
                $('#registerToggle').removeClass('active');
                $('#loginForm').addClass('active');
                $('#registerForm').removeClass('active');
                
                currentStep = 0;
                showStep(currentStep);
                clearInterval(countdownInterval);
                $('#countdownTimer').hide();
                smsSent = false;
                
                $('.text-danger').remove();
                $('.form-control, .form-select').removeClass('is-invalid');
            }

            function switchToRegister() {
                isLoginMode = false;
                $('#toggleSlider').addClass('register');
                $('#registerToggle').addClass('active');
                $('#loginToggle').removeClass('active');
                $('#registerForm').addClass('active');
                $('#loginForm').removeClass('active');
                
                if (!$('#subscription_plan').val()) {
                    loadSubscriptionPlans();
                }
            }

            @if(session('show_register'))
                setTimeout(function() {
                    switchToRegister();
                }, 100);
            @endif

            // Şifre Güvenliği Kontrol Fonksiyonu - YENİ
            function checkPasswordStrength(password) {
                let strength = 0;
                let requirements = {
                    length: password.length >= 6,
                    uppercase: /[A-Z]/.test(password),
                    lowercase: /[a-z]/.test(password),
                    number: /[0-9]/.test(password),
                    special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
                };

                // Gereksinimleri güncelle
                $('#req-length').toggleClass('valid', requirements.length);
                $('#req-uppercase').toggleClass('valid', requirements.uppercase);
                $('#req-lowercase').toggleClass('valid', requirements.lowercase);
                $('#req-number').toggleClass('valid', requirements.number);
                $('#req-special').toggleClass('valid', requirements.special);

                // Güç hesapla
                if (requirements.length) strength++;
                if (requirements.uppercase) strength++;
                if (requirements.lowercase) strength++;
                if (requirements.number) strength++;
                if (requirements.special) strength++;

                // Bar ve metin güncelle
                const fill = $('#passwordStrengthFill');
                const text = $('#passwordStrengthText');

                fill.removeClass('strength-weak strength-fair strength-good strength-strong');
                text.removeClass('text-weak text-fair text-good text-strong');

                if (password.length === 0) {
                    fill.css('width', '0%');
                    text.text('');
                } else if (strength <= 2) {
                    fill.css('width', '25%').addClass('strength-weak');
                    text.text('Zayıf').addClass('text-weak');
                } else if (strength === 3) {
                    fill.css('width', '50%').addClass('strength-fair');
                    text.text('Orta').addClass('text-fair');
                } else if (strength === 4) {
                    fill.css('width', '75%').addClass('strength-good');
                    text.text('İyi').addClass('text-good');
                } else {
                    fill.css('width', '100%').addClass('strength-strong');
                    text.text('Güçlü').addClass('text-strong');
                }

                return strength;
            }

            // Sektörleri yükle - YENİ
            function loadSectors() {
                $.ajax({
                    url: '{{ route("get.sectors") }}',
                    method: 'GET',
                    success: function(response) {
                        const select = $('#sektor');
                        select.empty().append('<option value="">Sektör Seçiniz...</option>');
                        
                        if (response.sectors && response.sectors.length > 0) {
                            response.sectors.forEach(function(sector) {
                                select.append(`<option value="${sector.slug}">${sector.title}</option>`);
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Sektörler yüklenirken hata:', xhr);
                    }
                });
            }

            function loadSubscriptionPlans() {
                $.ajax({
                    url: '{{ route("get.subscription.plans") }}',
                    method: 'GET',
                    success: function(response) {
                        const select = $('#subscription_plan');
                        select.empty().append('<option value="">Plan Seçiniz...</option>');
                        
                        let defaultPlan = null;
                        
                        response.plans.forEach(function(plan) {
                            // Limit bilgilerini formatla
                            let limitText = '';
                            const users = plan.limits.users || 0;
                            const dealers = plan.limits.dealers || 0;
                            
                            // Kullanıcı sayısı
                            if (users == -1) {
                                limitText = 'Sınırsız Kullanıcı';
                            } else if (users > 0) {
                                limitText = users + ' Kullanıcı';
                            }
                            
                            // Bayi sayısı (varsa)
                            if (dealers == -1) {
                                limitText += limitText ? ', Sınırsız Bayi' : 'Sınırsız Bayi';
                            } else if (dealers > 0) {
                                limitText += limitText ? ', ' + dealers + ' Bayi' : dealers + ' Bayi';
                            }
                            
                            const option = $('<option></option>')
                                .attr('value', plan.id)
                                .text(`${plan.name}${limitText ? ' - ' + limitText : ''}`)
                                .data('name', plan.name)
                                .data('price', plan.price)
                                .data('billing_cycle', plan.billing_cycle)
                                .data('users', plan.limits.users || '0')
                                .data('dealers', plan.limits.dealers || '0')
                                .data('stocks', plan.limits.stocks || '0')
                                .data('tickets', plan.features.tickets || false)
                                .data('basic_reports', plan.features.basic_reports || false)
                                .data('inventory', plan.features.inventory || false)
                                .data('priority_support', plan.features.priority_support || false);
                            
                            select.append(option);
                            
                            if (!defaultPlan || parseFloat(plan.price) < parseFloat(defaultPlan.price)) {
                                defaultPlan = plan;
                            }
                        });
                        
                        if (response.selected_plan_id) {
                            select.val(response.selected_plan_id);
                        } else if (defaultPlan) {
                            select.val(defaultPlan.id);
                        }
                    },
                    error: function(xhr) {
                        console.error('Plans yüklenirken hata oluştu:', xhr);
                        toastr.error('Planlar yüklenirken bir hata oluştu.');
                    }
                });
            }

            function showPlanInfo(planData) {
                const planInfo = $('#planInfo');
                const featuresDiv = planInfo.find('.plan-features');
                
                function formatLimitValue(value) {
                    if (value == -1) return 'Sınırsız';
                    if (value == 0) return null;
                    return value.toLocaleString('tr-TR');
                }

                let limitItems = [];
                
                const limits = [
                    { key: 'users', label: 'Kullanıcı Sayısı', value: planData.users },
                    { key: 'dealers', label: 'Alt Bayi Sayısı', value: planData.dealers },
                    { key: 'stocks', label: 'Stok-Ürün Sayısı', value: planData.stocks },
                ];

                limits.forEach(limit => {
                    const formattedValue = formatLimitValue(limit.value);
                    if (formattedValue !== null) {
                        const isUnlimited = limit.value == -1;
                        limitItems.push(`
                            <div class="limit-item">
                                <span class="limit-value ${isUnlimited ? 'unlimited' : ''}">${isUnlimited ? '∞' : limit.value}</span>
                                <span>${limit.label}</span>
                            </div>
                        `);
                    }
                });

                let featureTags = [];
                if (planData.tickets === 'true' || planData.tickets === true) {
                    featureTags.push('<span class="feature-tag">Destek Talebi</span>');
                }
                if (planData.basic_reports === 'true' || planData.basic_reports === true) {
                    featureTags.push('<span class="feature-tag">Raporlar</span>');
                }
                if (planData.inventory === 'true' || planData.inventory === true) {
                    featureTags.push('<span class="feature-tag">Stok Yönetimi</span>');
                }
                if (planData.priority_support === 'true' || planData.priority_support === true) {
                    featureTags.push('<span class="feature-tag">Öncelikli Destek</span>');
                }

                const compactHTML = `
                    <div class="plan-info-compact">
                        ${limitItems.length > 0 ? `
                            <div class="limits-row">
                                ${limitItems.join('')}
                            </div>
                        ` : ''}
                        
                        ${featureTags.length > 0 ? `
                            <div class="features-compact">
                                ${featureTags.join('')}
                            </div>
                        ` : ''}
                    </div>
                `;
                
                featuresDiv.html(compactHTML);
                planInfo.show();
            }

            function hidePlanInfo() {
                $('#planInfo').hide();
            }

            // Multi-step form functionality
            let currentStep = 0;
            let smsSent = false;
            let countdownInterval = null;
            const steps = $(".form-step");
            
            function showStep(stepIndex) {
                steps.removeClass('active');
                $(steps[stepIndex]).addClass('active');

                if (stepIndex === 0) {
                    $('#prevBtn').hide();
                } else {
                    $('#prevBtn').show();
                }

                if (stepIndex === steps.length - 1) {
                    if (smsSent) {
                        $('#nextBtn').text('Doğrula ve Kaydı Tamamla');
                    } else {
                        $('#nextBtn').text('SMS Gönder');
                    }
                } else {
                    $('#nextBtn').text('İleri →');
                }

                $('.step').removeClass('active finish');
                $('.step').each(function(index) {
                    if (index < stepIndex) {
                        $(this).addClass('finish');
                    } else if (index === stepIndex) {
                        $(this).addClass('active');
                    }
                });

                if (stepIndex === 3) {
                    const phoneNumber = $('#tel').val();
                    if (phoneNumber) {
                        $('#phoneDisplay').text('+90 ' + phoneNumber);
                    }
                }
            }

            function validateStepFields(stepIndex) {
                let isValid = true;
                $(steps[stepIndex]).find('input[required], select[required]').each(function() {
                    let value = $(this).val();
                    if (!value || value.trim() === '') {
                        isValid = false;
                        $(this).addClass('is-invalid');
                        
                        let errorDiv = $(this).siblings('.text-danger');
                        if (errorDiv.length === 0) {
                            $(this).after('<small class="text-danger">Bu alan zorunludur.</small>');
                        }
                    } else {
                        $(this).removeClass('is-invalid');
                        $(this).siblings('.text-danger').remove();
                    }
                });

                // Password confirmation kontrolü
                if (stepIndex === 2) {
                    const password = $('#registerPassword').val();
                    const confirmation = $('#password_confirmation').val();
                    
                    if (password !== confirmation) {
                        isValid = false;
                        $('#password_confirmation').addClass('is-invalid');
                        if (!$('#password_confirmation').siblings('.text-danger').length) {
                            $('#password_confirmation').after('<small class="text-danger">Şifreler eşleşmiyor.</small>');
                        }
                    }
                }

                return isValid;
            }

            function validateStepOnServer(stepIndex) {
                return new Promise((resolve, reject) => {
                    let formData = {};
                    
                    if (stepIndex === 0) {
                        formData = {
                            subscription_plan: $('#subscription_plan').val(),
                            step: 1,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        };
                    } else if (stepIndex === 1) {
                        formData = {
                            subscription_plan: $('#subscription_plan').val(),
                            name: $('#name').val(),
                            username: $('#username').val(),
                            email: $('#registerEmail').val(),
                            vergiNo: $('#vergiNo').val(),
                            step: 2,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        };
                    } else if (stepIndex === 2) {
                        formData = {
                            subscription_plan: $('#subscription_plan').val(),
                            name: $('#name').val(),
                            username: $('#username').val(),
                            email: $('#registerEmail').val(),
                            vergiNo: $('#vergiNo').val(),
                            firma_adi: $('#firma_adi').val(),
                            sektor: $('#sektor').val(),
                            il_id: $('#il_id').val(),          
                            ilce_id: $('#ilce_id').val(),
                            adres: $('#adres').val(),
                            tel: $('#tel').val(),
                            password: $('#registerPassword').val(),
                            password_confirmation: $('#password_confirmation').val(),
                            step: 3,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        };
                    }

                    $.ajax({
                        url: '{{ route("validate.step") }}',
                        method: 'POST',
                        data: formData,
                        success: function(response) {
                            resolve(response);
                        },
                        error: function(xhr) {
                            reject(xhr.responseJSON);
                        }
                    });
                });
            }

            function displayErrors(errors) {
                $('.text-danger').remove();
                $('.form-control, .form-select').removeClass('is-invalid');

                Object.keys(errors).forEach(function(field) {
                    const input = $(`[name="${field}"], #${field}`);
                    if (input.length > 0) {
                        input.addClass('is-invalid');
                        input.after(`<small class="text-danger">${errors[field][0]}</small>`);
                    }
                });
            }

            $('#nextBtn').on('click', async function(e) {
                e.preventDefault();
                
                if (currentStep < 3) {
                    if (!validateStepFields(currentStep)) {
                        return;
                    }

                    try {
                        await validateStepOnServer(currentStep);
                        
                        // Step 2'den Step 3'e geçiyorsak, önce SMS gönder
                        if (currentStep === 2) {
                            await sendSMS();
                        }
                        
                        currentStep++;
                        showStep(currentStep);
                        
                    } catch (error) {
                        if (error.errors) {
                            displayErrors(error.errors);
                        } else {
                            toastr.error(error.message || 'Bir hata oluştu.');
                        }
                    }
                } else if (currentStep === 3) {
                    verifySMSAndComplete();
                }
            });

            function sendSMS() {
                return new Promise((resolve, reject) => {
                    const formData = {
                        subscription_plan: $('#subscription_plan').val(),
                        vergiNo: $('#vergiNo').val(),
                        name: $('#name').val(),
                        username: $('#username').val(),
                        email: $('#registerEmail').val(),
                        firma_adi: $('#firma_adi').val(),
                        sektor: $('#sektor').val(),
                        il_id: $('#il_id').val(),
                        ilce_id: $('#ilce_id').val(),
                        adres: $('#adres').val(),
                        tel: $('#tel').val(),
                        password: $('#registerPassword').val(),
                        password_confirmation: $('#password_confirmation').val(),
                        _token: $('meta[name="csrf-token"]').attr('content')
                    };

                    $.ajax({
                        url: '{{ route("kayit.action") }}',
                        method: 'POST',
                        data: formData,
                        success: function(response) {
                            smsSent = true;
                            $('#nextBtn').text('Doğrula ve Kaydı Tamamla');
                            $('#countdownTimer').show();
                            startCountdown();
                            toastr.success('SMS başarıyla gönderildi!');
                            resolve(response);
                        },
                        error: function(xhr) {
                            const errors = xhr.responseJSON?.errors;
                            if (errors) {
                                displayErrors(errors);
                            } else {
                                const message = xhr.responseJSON?.message || 'SMS gönderilirken bir hata oluştu.';
                                toastr.error(message);
                            }
                            reject(xhr.responseJSON);
                        }
                    });
                });
            }

            $('input, select, textarea').on('input change', function() {
                $(this).removeClass('is-invalid');
                $(this).siblings('.text-danger').remove();
            });

            function verifySMSAndComplete() {
                const smsCode = $('#smsCode').val();
                
                if (!smsCode || smsCode.length !== 6) {
                    $('#smsCodeError').text('Lütfen 6 haneli doğrulama kodunu giriniz.').css('display', 'block');
                    return;
                }

                $.ajax({
                    url: '{{ route("sms.verification.verify") }}',
                    method: 'POST',
                    data: {
                        code: smsCode,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        toastr.success('Hesabınız başarıyla oluşturuldu!');
                        setTimeout(function() {
                            window.location.href = '{{ route("register.success") }}';
                        }, 2000);
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Doğrulama kodu hatalı veya süresi dolmuş.';
                        $('#smsCodeError').text(errorMsg).css('display', 'block');
                        toastr.error(errorMsg);
                        $('#smsCode').addClass('is-invalid');
                    }
                });
            }

            function startCountdown() {
                let duration = 180;
                
                countdownInterval = setInterval(function() {
                    const minutes = Math.floor(duration / 60);
                    const seconds = duration % 60;
                    
                    $('#countdown').text(
                        minutes + ':' + (seconds < 10 ? '0' : '') + seconds
                    );
                    
                    if (duration <= 0) {
                        clearInterval(countdownInterval);
                        $('#countdownTimer').hide();
                        smsSent = false;
                        $('#nextBtn').text('SMS Gönder');
                        toastr.warning('SMS doğrulama süresi doldu. Lütfen yeniden deneyin.');
                    }
                    
                    duration--;
                }, 1000);
            }

            $('#prevBtn').on('click', function() {
                if (currentStep > 0) {
                    currentStep--;
                    showStep(currentStep);
                    
                    if (currentStep < 3) {
                        clearInterval(countdownInterval);
                        $('#countdownTimer').hide();
                        smsSent = false;
                        $('#smsCodeError').hide();
                    }
                }
            });

            showStep(currentStep);

            $('#smsCode').on('input', function() {
                $('#smsCodeError').hide();
            });

            @if (Session::has('message'))
                var type = "{{ Session::get('alert-type', 'info') }}"
                toastr.options.positionClass = "toast-top-right";
                toastr.options.timeOut = 5000;
                toastr.options.extendedTimeOut = 1000;
                
                switch (type) {
                    case 'info':
                        toastr.info("{{ Session::get('message') }}");
                        break;
                    case 'success':
                        toastr.success("{{ Session::get('message') }}");
                        break;
                    case 'warning':
                        toastr.warning("{{ Session::get('message') }}");
                        break;
                    case 'error':
                    case 'danger':
                        toastr.error("{{ Session::get('message') }}");
                        break;
                }
            @endif
        });
    </script>
    <script>
        $(document).ready(function() {
            // İlleri yükle
            loadCities();

            // İl seçildiğinde ilçeleri yükle
            $('#il_id').on('change', function() {
                const ilId = $(this).val();
                const ilceSelect = $('#ilce_id');
                
                if (ilId) {
                    ilceSelect.prop('disabled', true).html('<option value="">Yükleniyor...</option>');
                    
                    $.ajax({
                        url: '{{ route("get.districts") }}',
                        method: 'GET',
                        data: { il_id: ilId },
                        success: function(response) {
                            ilceSelect.empty().append('<option value="">İlçe Seçiniz...</option>');
                            
                            response.districts.forEach(function(district) {
                                ilceSelect.append(`<option value="${district.id}">${district.ilceName}</option>`);
                            });
                            
                            ilceSelect.prop('disabled', false);
                        },
                        error: function(xhr) {
                            console.error('İlçeler yüklenirken hata:', xhr);
                            ilceSelect.empty().append('<option value="">Hata oluştu</option>');
                            toastr.error('İlçeler yüklenirken bir hata oluştu.');
                        }
                    });
                } else {
                    ilceSelect.prop('disabled', true).empty().append('<option value="">Önce İl Seçiniz...</option>');
                }
            });

            function loadCities() {
                $.ajax({
                    url: '{{ route("get.cities") }}',
                    method: 'GET',
                    success: function(response) {
                        const select = $('#il_id');
                        select.empty().append('<option value="">İl Seçiniz...</option>');
                        
                        response.cities.forEach(function(city) {
                            select.append(`<option value="${city.id}">${city.name}</option>`);
                        });
                    },
                    error: function(xhr) {
                        console.error('İller yüklenirken hata:', xhr);
                        toastr.error('İller yüklenirken bir hata oluştu.');
                    }
                });
            }
        });
    </script>
</body>
</html>