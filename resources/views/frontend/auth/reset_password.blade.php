<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Sıfırla - Serbis</title>
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
                <form id="resetPasswordForm">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">
                    
                    <h4 class="text-center mb-3" style="color: #333; font-weight: 600;">Yeni Şifre Oluştur</h4>
                    <p class="text-center mb-4" style="color: #666; font-size: 0.9rem;">
                        Lütfen yeni şifrenizi belirleyin.
                    </p>

                    <div class="mb-3">
                        <label for="password" class="form-label" style="color: #333; font-weight: 500;">
                            Yeni Şifre <span style="color: #dc3545;">*</span>
                        </label>
                        <input type="password" name="password" id="password" class="form-control">
                        <small class="form-text" style="color: #020303;">
                        <div id="passwordError" class="text-danger mt-2" style="display: none;"></div>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label" style="color: #333; font-weight: 500;">
                            Yeni Şifre (Tekrar) <span style="color: #dc3545;">*</span>
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation" 
                               class="form-control">
                        <div id="passwordConfirmationError" class="text-danger mt-2" style="display: none;"></div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3" id="submitBtn">
                        <span id="btnText">Şifremi Güncelle</span>
                        <span id="btnLoader" style="display: none;">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Güncelleniyor...
                        </span>
                    </button>
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
                    <p class="mb-4">Şifreniz başarıyla güncellendi. Yeni şifrenizle giriş yapabilirsiniz.</p>
                    <button type="button" class="btn btn-primary" id="closeModal">Giriş Sayfasına Git</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#resetPasswordForm').on('submit', function(e) {
                e.preventDefault();
                
                // Clear previous errors
                $('.text-danger').hide().text('');
                $('.form-control').removeClass('is-invalid');
                
                // Show loading
                $('#btnText').hide();
                $('#btnLoader').show();
                $('#submitBtn').prop('disabled', true);

                $.ajax({
                    url: '{{ route("password.update") }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        // Reset button
                        $('#btnText').show();
                        $('#btnLoader').hide();
                        $('#submitBtn').prop('disabled', false);
                        
                        // Show success modal
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
                            if (errors.password) {
                                $('#password').addClass('is-invalid');
                                $('#passwordError').text(errors.password[0]).show();
                            }
                            if (errors.password_confirmation) {
                                $('#password_confirmation').addClass('is-invalid');
                                $('#passwordConfirmationError').text(errors.password_confirmation[0]).show();
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

            // Input değiştiğinde hata mesajlarını temizle
            $('input').on('input', function() {
                $(this).removeClass('is-invalid');
                $(this).siblings('.text-danger').hide();
            });
        });
    </script>
</body>
</html>