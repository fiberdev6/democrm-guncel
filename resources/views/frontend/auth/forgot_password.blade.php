<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifremi Unuttum - Serbis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">
    <link href="{{asset('frontend/css/login.css')}}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="main-container">
        <div class="auth-card">
           <div class="logo-section">
                <div style="display: flex; align-items: center; justify-content: center; gap: 3px;">
                    <img src="{{ asset('frontend/img/logo_son.png') }}" alt="Serbis Logo" >
                </div>
            </div>
            <div class="form-container">
                <form id="forgotPasswordForm">
                    @csrf
                    <h4 class="text-center mb-3" style="color: #333; font-weight: 600;">Şifremi Unuttum</h4>
                    <p class="text-center mb-4" style="color: #666; font-size: 0.9rem;">
                        Şifre yenileme bağlantısını gönderebildiğimiz için e-posta adresinize ihtiyacımız var.
                    </p>
                    <div class="alert alert-info" style="font-size: 0.9rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" 
                             class="bi bi-info-circle" viewBox="0 0 16 16" style="vertical-align: middle;">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                            <path d="M8.93 6.588l-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                        </svg>
                        Sadece firma sahibi (Patron) şifresini bu sayfadan sıfırlayabilir. 
                        Çalışanlar için şifre sıfırlama işlemini firma sahibiniz yapmalıdır.
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label" style="color: #333; font-weight: 500;">
                            E-Posta <span style="color: #dc3545;">*</span>
                        </label>
                        <input type="email" name="email" id="email" class="form-control" 
                               placeholder="E-posta adresinizi giriniz" required>
                        <div id="emailError" class="text-danger mt-2" style="display: none;"></div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3" id="submitBtn">
                        <span id="btnText">Şifremi Yenile</span>
                        <span id="btnLoader" style="display: none;">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Gönderiliyor...
                        </span>
                    </button>
                    
                    <p class="text-center mb-0" style="color: #666; font-size: 0.9rem;">
                        <a href="{{ route('giris') }}" style="color: #3e546a; font-weight: 600; text-decoration: none;">
                            ← Önceki Sayfaya Dön
                        </a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="#28a745" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                        </svg>
                    </div>
                    <h5 class="mb-3">Başarılı!</h5>
                    <p class="mb-4" id="successMessage">Şifre sıfırlama bağlantısı e-posta adresinize gönderildi.</p>
                    <button type="button" class="btn btn-primary" id="closeModal">Tamam</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#forgotPasswordForm').on('submit', function(e) {
                e.preventDefault();
                
                // Clear previous errors
                $('#emailError').hide().text('');
                $('.form-control').removeClass('is-invalid');
                
                // Show loading
                $('#btnText').hide();
                $('#btnLoader').show();
                $('#submitBtn').prop('disabled', true);

                $.ajax({
                    url: '{{ route("password.email") }}',
                    method: 'POST',
                    data: {
                        email: $('#email').val(),
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        // Reset button
                        $('#btnText').show();
                        $('#btnLoader').hide();
                        $('#submitBtn').prop('disabled', false);
                        
                        // Show success modal
                        $('#successMessage').text(response.message);
                        const modal = new bootstrap.Modal(document.getElementById('successModal'));
                        modal.show();
                    },
                    error: function(xhr) {
                        // Reset button
                        $('#btnText').show();
                        $('#btnLoader').hide();
                        $('#submitBtn').prop('disabled', false);
                        
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            if (errors.email) {
                                $('#email').addClass('is-invalid');
                                $('#emailError').text(errors.email[0]).show();
                            }
                        } else {
                            const message = xhr.responseJSON?.message || 'Bir hata oluştu. Lütfen tekrar deneyin.';
                            toastr.error(message);
                        }
                    }
                });
            });

            // Modal kapatma ve yönlendirme
            $('#closeModal').on('click', function() {
                window.location.href = '{{ route("giris") }}';
            });

            // Input değiştiğinde hata mesajını temizle
            $('#email').on('input', function() {
                $(this).removeClass('is-invalid');
                $('#emailError').hide();
            });
        });
    </script>
</body>
</html>