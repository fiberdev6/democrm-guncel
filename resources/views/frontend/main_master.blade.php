<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  @php 
  $settings = App\Models\Settings::find(1);
  @endphp

  <title>Serbis CRM</title>
  <meta content="{{$settings->site_description}}" name="description">
  <meta content="{{$settings->site_keywords}}" name="keywords">

  <!-- Favicons -->
  <link href="{{asset($settings->favicon)}}" rel="icon">
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,600;1,700&family=Roboto:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Work+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{asset('frontend/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
  <link href="{{asset('frontend/vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
  <link href="{{asset('frontend/vendor/fontawesome-free/css/all.min.css')}}" rel="stylesheet">
  <link href="{{asset('frontend/vendor/glightbox/css/glightbox.min.css')}}" rel="stylesheet">
  <link href="{{asset('frontend/vendor/swiper/swiper-bundle.min.css')}}" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  {{-- <link href="{{asset('frontend/css/main.css')}}" rel="stylesheet"> --}}
  {{-- <link href="{{asset('frontend/css/custom.css')}}" rel="stylesheet"> --}}
  <link rel="stylesheet" href="{{ asset('frontend/css/frontend_main.css') }}">
    

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
  
  

  {!! $settings->taghead_kod !!}
</head>

<body>
  {!! $settings->tagbody_kod !!}
  <!-- ======= Header ======= -->
  {{-- @include('frontend.body.header') --}}
  @include('frontend.frontend_partials.navbar')

  <!-- ======= Hero Section ======= -->
  

  <main id="main">

    @yield('main')
  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  {{-- @include('frontend.body.footer') --}}
  @include('frontend.frontend_partials.footer')
  <!-- End Footer -->
  <!-- <a href="#" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <div id="preloader"></div> -->


  {{-- <div class="wp-bottom-menu" id="wp-bottom-menu">
    <a href="tel:{{$settings->company_phone}}" class="wp-bottom-menu-item item-border">
      <div class="wp-bottom-menu-icon-wrapper">
      <i class="wp-bottom-menu-item-icons fa fa-phone"></i>
      </div>
      <span>Phone</span>
      </a>
    <a href="#kayitModal" data-bs-toggle="modal" data-target="#kayitModal" class="wp-bottom-menu-item item-border">
    <div class="wp-bottom-menu-icon-wrapper">
    <i class="wp-bottom-menu-item-icons fa fa-envelope"></i>
    </div>
    <span>Contact us</span>
    <span class="badge badge-dark badge-sm rounded-pill p-1 font-weight-extra-light"><i class="fas fa-circle text-color-success"></i> Çevrimiçi</span>
    </a>
    <a href="https://wa.me/905462916942" target="_blank" class="wp-bottom-menu-item ">
    <div class="wp-bottom-menu-icon-wrapper">
    <i class="wp-bottom-menu-item-icons fa-brands fa-square-whatsapp"></i>
    </div>
    <span>WhatsApp</span>
    </a>
    
    </div> --}}

    <div class="modal fade" id="kayitModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"> Contact Us</h5>
            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" aria-hidden="true">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form action="{{route('store.message')}}" method="post" class="php-email-form">
              @csrf
              @if(Session::has("success"))
                <div class="alert alert-success alert-dismissible"><button type="button" class="close">&times;</button>{{Session::get('success')}}</div>
              @elseif(Session::has("failed"))
                <div class="alert alert-danger alert-dismissible"><button type="button" class="close">&times;</button>{{Session::get('failed')}}</div>
              @endif
              <div class="row gy-3">

           
                <div class="col-md-6">
                  <input type="text" name="name" class="form-control" placeholder="Name" required>
                </div>

                <div class="col-md-6 ">
                  <input type="email" class="form-control" name="email" placeholder="Email" required>
                </div>

                <div class="col-md-12">
                  <input type="text" class="form-control" name="phone" placeholder="Phone" required>
                </div>

                <div class="col-md-12">
                  <textarea class="form-control" name="message" rows="3" placeholder="Message" required></textarea>
                </div>

                <div class="col-md-12 text-center">
                  
                  <button type="submit">Send</button>
                </div>

              </div>
            </form>
        </div>
      </div>
    </div>
  

  <link href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css" rel="stylesheet" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>
  <script type="text/javascript">
      $.fancybox.defaults.hash = false;
  </script>
  <!-- Vendor JS Files -->
  <script src="{{asset('frontend/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
  <script src="{{asset('frontend/vendor/aos/aos.js')}}"></script>
  <script src="{{asset('frontend/vendor/glightbox/js/glightbox.min.js')}}"></script>
  <script src="{{asset('frontend/vendor/isotope-layout/isotope.pkgd.min.js')}}"></script>
  <script src="{{asset('frontend/vendor/swiper/swiper-bundle.min.js')}}"></script>
  <script src="{{asset('frontend/vendor/purecounter/purecounter_vanilla.js')}}"></script>
  <script src="{{asset('frontend/vendor/php-email-form/validate.js')}}"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
 

  <!-- jquery mask -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

  <!-- Template Main JS File -->
  <script src="{{asset('frontend/js/main.js')}}"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
  <script>
    var owl = $('.owl-carousel');
owl.owlCarousel({
    items:5,
    loop:true,
    margin:10,
    autoplay:true,
    autoplayTimeout:5000,
    autoplayHoverPause:true,
    responsive:{
        0:{
            items:1,
            stagePadding: 60
        },
        600:{
            items:1,
            stagePadding: 100
        },
        1000:{
            items:1,
            stagePadding: 200
        },
        1200:{
            items:1,
            stagePadding: 250
        },
        1400:{
            items:1,
            stagePadding: 300
        },
        1600:{
            items:1,
            stagePadding: 350
        },
        1800:{
            items:1,
            stagePadding: 400
        }
    }
});
$('.play').on('click',function(){
    owl.trigger('play.owl.autoplay',[1000])
})
$('.stop').on('click',function(){
    owl.trigger('stop.owl.autoplay')
})
  </script>

<li style="display:none" id="google_translate_element2"></li>
    <script type="text/javascript">
      function googleTranslateElementInit2() {new google.translate.TranslateElement({pageLanguage: 'en',autoDisplay: false}, 'google_translate_element2');}
    </script><script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit2"></script>
    <script type="text/javascript">
      function GTranslateGetCurrentLang() {var keyValue = document.cookie.match('(^|;) ?googtrans=([^;]*)(;|$)');return keyValue ? keyValue[2].split('/')[2] : null;}
      function GTranslateFireEvent(element,event){try{if(document.createEventObject){var evt=document.createEventObject();element.fireEvent('on'+event,evt)}else{var evt=document.createEvent('HTMLEvents');evt.initEvent(event,true,true);element.dispatchEvent(evt)}}catch(e){}}
      function doGTranslate(lang_pair){if(lang_pair.value)lang_pair=lang_pair.value;if(lang_pair=='')return;var lang=lang_pair.split('|')[1];if(GTranslateGetCurrentLang() == null && lang == lang_pair.split('|')[0])return;var teCombo;var sel=document.getElementsByTagName('select');for(var i=0;i<sel.length;i++)if(sel[i].className=='goog-te-combo')teCombo=sel[i];if(document.getElementById('google_translate_element2')==null||document.getElementById('google_translate_element2').innerHTML.length==0||teCombo.length==0||teCombo.innerHTML.length==0){setTimeout(function(){doGTranslate(lang_pair)},500)}else{teCombo.value=lang;GTranslateFireEvent(teCombo,'change');GTranslateFireEvent(teCombo,'change')}}
      if(GTranslateGetCurrentLang() != null)jQuery(document).ready(function() {jQuery('div.switcher div.selected a').html(jQuery('div.switcher div.option').find('img[alt="'+GTranslateGetCurrentLang()+'"]').parent().html());});
    </script>
</body>

</html>