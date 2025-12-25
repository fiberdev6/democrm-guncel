<!doctype html>
<html lang="tr">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8" />
    @php 
        $user = Auth::user();
    @endphp
    <title>{{ $user && $user->tenant && $user->tenant->firma_adi ? $user->tenant->firma_adi : 'Yönetim Paneli' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('backend/assets/images/favicon.ico') }}">
 
    <!-- DataTables -->
    <link href="{{ asset('backend/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- Bootstrap Css -->
    <link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('backend/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('backend/assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />

    <!--toastr messages css-->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">
    <!--tags css-->
    <link rel="stylesheet" href="{{asset('backend/assets/libs/select2/css/select2.min.css')}}" type="text/css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/bootstrap.tagsinput/0.8.0/bootstrap-tagsinput.css">
    <!--select 2-->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />






    <link href="{{ asset('backend/assets/css/custom.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('frontend/css/secure.css') }}" rel="stylesheet" type="text/css" />

    <script src="{{ asset('backend/assets/libs/jquery/jquery.min.js') }}"></script>
    

    <link href="{{asset('backend/assets/libs/dropzone/min/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.0/dropzone.min.css" integrity="sha512-jU/7UFiaW5UBGODEopEqnbIAHOI8fO6T99m7Tsmqs2gkdujByJfkCbbfPSN4Wlqlb9TGnsuC0YgUgWkRBK7B9A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js" integrity="sha512-STof4xm1wgkfm7heWqFJVn58Hm3EtS31XFaagaa8VMReCXAkQnJZ+jEy8PCC/iT18dFy95WcExNHFTqLyp72eQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.0/dropzone.js" integrity="sha512-tYefFVRPVQIZMI0CqDcVLTti7ajlO/l9qk1s8eswWduldmconu2sKCdYQOTRkn/f2k3eupgRbFzf55bM2moH8Q==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">


<link href="{{asset('backend/assets/libs/spectrum-colorpicker2/spectrum.min.css')}}" rel="stylesheet" type="text/css">

 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css">

</head>

<body data-topbar="dark">
    <!-- <body data-layout="horizontal" data-topbar="dark"> -->
        
    <!-- Begin page -->
    <div id="layout-wrapper">



        @include('frontend.secure.body.header')
        <!--header önce buradaydı ama kolayca üstünde işlem yapabilmek için header.blade.php içine aldık  -->

        <!-- ========== Left Sidebar Start ========== -->
        @include('frontend.secure.body.sidebar')
        <!-- Left Sidebar End -->

       


        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        
        <div class="main-content">

           <!-- Firmanın deneme süresine son 5 gün kala verdiğim uyarı    -->
           @if (session('warning'))
                <div class="fullwidth-app-alert warning-app-alert">
                    <div class="alert-left">
                        <span class="alert-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.964 0L.165 13.233c-.457.778.091 1.767.982 1.767h13.706c.89 0 1.438-.99.982-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1-2 .001 1 1 0 0 1 2-.001z"/>
                            </svg>
                        </span>
                        <span>{{ session('warning') }}</span>
                    </div>

                    <button type="button" class="close-app-alert-button" onclick="this.parentElement.style.display='none';">&times;</button>
                </div>
            @endif
            <!-- Impersonation kullanıcı rolüne göre giriş-çıkış kısmı   -->
            <div id="impersonationBanner" class="d-none">
                <div class="alert alert-warning m-0" style="border-radius: 0;padding:10px 0px; border: none; border-bottom: 3px solid #ffc107;">
                    <div class="container-fluid">
                        <div class="row align-items-center">
                            <div style="padding-right:3px" class="col-auto col-md-1 col-2 ms-auto text-center">
                                <i class="fas fa-user-secret fa-2x text-warning"></i>
                            </div>
                            <div style="padding-left:3px;padding-right:3px" class="col col-md-9 col-10">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <strong class="font-md">Kimliğe Bürünme Aktif</strong>
                                        <div id="impersonationDetails" class="small"></div>
                                    </div>
                                    <div class="me-3">
                                        <small class="text-muted">Başlangıç:</small>
                                        <div id="impersonationTime" class="small fw-bold"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto back-button col-md-2">
                                <button type="button" class="btn btn-outline-dark btn-sm" id="exitImpersonation">
                                    <i class="fas fa-sign-out-alt me-1"></i>
                                    Süper Admin Hesabıma Dön
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            @yield('user')

           
            <!-- End Page-content -->
             @include('frontend.secure.body.verimor-floating-phone')


            @include('frontend.secure.body.footer')

        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->



    <!-- Right bar overlay-->
    <!-- <div class="rightbar-overlay"></div> -->

    <!-- JAVASCRIPT -->
    <script src="{{ asset('backend/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/node-waves/waves.min.js') }}"></script>

    <!-- Datatable JS -->
    <script src="{{ asset('backend/assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <!-- Responsive Datatable -->
    <script src="https://cdn.datatables.net/colreorder/1.5.2/js/dataTables.colReorder.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>

    <!-- App js -->
    <script src="{{ asset('backend/assets/js/app.js') }}"></script>
    <!-- DataTables Buttons JS -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <script src="{{asset('backend/assets/libs/spectrum-colorpicker2/spectrum.min.js')}}"></script>

    <!--toastr messages js-->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
   
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>

    <script>

        toastr.options = {
            "positionClass": "toast-top-center" // En üstte ortada
        };
        @if (Session::has('message'))
            var type = "{{ Session::get('alert-type', 'info') }}"
            switch (type) {
                case 'info':
                    toastr.info(" {{ Session::get('message') }} ");
                    break;

                case 'success':
                    toastr.success(" {{ Session::get('message') }} ");
                    break;

                case 'warning':
                    toastr.warning(" {{ Session::get('message') }} ");
                    break;

                case 'error':
                    toastr.error(" {{ Session::get('message') }} ");
                    break;
            }
        @endif
    </script>

    <script>
    $(document).ready(function() {
        // Sayfa yüklendiğinde mevcut datepicker'ları başlat
        initializeDatePickers();
        
        // Modal açıldığında yeni datepicker'ları başlat
        $(document).on('shown.bs.modal', '.modal', function() {
            initializeDatePickers();
        });
        
        // AJAX ile içerik yüklendikten sonra datepicker'ları başlat
        $(document).ajaxComplete(function() {
            setTimeout(function() {
                initializeDatePickers();
            }, 100);
        });
    });

    function initializeDatePickers() {
        // Henüz initialize edilmemiş datepicker'ları bul ve başlat
        $('.datepicker:not(.flatpickr-input), .kayitTarihi:not(.flatpickr-input)').each(function() {
            const currentValue = this.value; // Input'taki mevcut değeri al
            flatpickr(this, {
                dateFormat: "Y-m-d",        // Form'a gönderilecek format (value)
            altInput: false,             // Alternatif input göster
            locale: "tr",
            allowInput: true,
            defaultDate: currentValue || "today",
            // Tarih seçildiğinde otomatik formatla
            onChange: function(selectedDates, dateStr, instance) {
                currentValue.value = dateStr; // Y-m-d formatında set et
            }
            });
        });
    }
    </script>
<script>
$(document).ready(function() {
    // Sayfa yüklendiğinde impersonation durumunu kontrol et
    checkImpersonationStatus();

    // Çıkış butonu
    $('#exitImpersonation').click(function() {
        exitImpersonation();
    });

    function checkImpersonationStatus() {
        $.get('/impersonation/status')
            .done(function(response) {
                if (response.is_impersonating) {
                    showImpersonationBanner(response);
                } else {
                    hideImpersonationBanner();
                }
            })
            .fail(function() {
                hideImpersonationBanner();
            });
    }

    function showImpersonationBanner(data) {
        var details = `${data.impersonated.name} olarak giriş yapmışsınız`;
        var startTime = new Date(data.started_at).toLocaleString('tr-TR');
        
        $('#impersonationDetails').text(details);
        $('#impersonationTime').text(startTime);
        $('#impersonationBanner').removeClass('d-none');
        
        // Body'ye class ekle (styling için)
        $('body').addClass('impersonating');
    }

    function hideImpersonationBanner() {
        $('#impersonationBanner').addClass('d-none');
        $('body').removeClass('impersonating');
    }

    function exitImpersonation() {
        $('#exitImpersonation').prop('disabled', true).html(`
            <span class="spinner-border spinner-border-sm me-1"></span>Çıkış yapılıyor...
        `);

        $.post('/impersonation/stop', {
            _token: $('meta[name="csrf-token"]').attr('content')
        })
        .done(function(response) {
        if (response.success && response.redirect_url) {
        // Sayfa yenilemesi ile yönlendirme
        window.location.href = response.redirect_url;
        // VEYA tam sayfa yenilemesi
        // window.location.reload();
    }
        })
        .fail(function(xhr) {
            var error = xhr.responseJSON?.message || 'Çıkış yapılamadı';
            showNotification(error, 'danger');
        })
        .always(function() {
            $('#exitImpersonation').prop('disabled', false).html(`
                <i class="fas fa-sign-out-alt me-1"></i>Kendi Hesabıma Dön
            `);
        });
    }

    // Global notification function
    window.showNotification = function(message, type) {
        var alertClass = `alert-${type}`;
        var icon = type === 'success' ? 'check' : 
                  type === 'danger' ? 'exclamation-triangle' : 
                  type === 'warning' ? 'exclamation-triangle' : 'info';
        
        var notification = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 80px; right: 20px; z-index: 9999; min-width: 350px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <i class="fas fa-${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('body').append(notification);
        
        setTimeout(() => {
            $('.alert').fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    };

    // Impersonation sırasında styling
    if ($('body').hasClass('impersonating')) {
        $('.navbar, .sidebar').addClass('impersonation-mode');
    }
});
</script>


    <!--tinymce js-->
    <script src="{{ asset('backend/assets/libs/tinymce/tinymce.min.js') }}"></script>
    <!-- init js -->
    <script src="{{ asset('backend/assets/js/pages/form-editor.init.js') }}"></script>
    <script src="{{asset('backend/assets/js/pages/form-validation.init.js')}}"></script>
    <script src="{{asset('backend/assets/libs/select2/js/select2.min.js')}}"></script>
    <script src="{{asset('backend/assets/js/pages/form-advanced.init.js')}}"></script>

    <!-- Datatable init js -->
    <script src="{{ asset('backend/assets/js/pages/datatables.init.js') }}"></script>

    <!-- sweetalert2 js -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    <script src="{{ asset('backend/assets/js/code.js') }}"></script>

    <script src="{{asset('backend/assets/libs/dropzone/min/dropzone.min.js')}}"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/tr.js"></script>

    <!-- select 2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

</body>
</html>
