@php 
$settings = App\Models\Settings::find(1);
@endphp

@php 
$products = App\Models\Category::get();
@endphp

<footer id="footer" class="footer">

    <div class="footer-content position-relative">
      <div class="container">
        <div class="row">

          <div class="col-lg-6 col-md-6">
            <div class="footer-info">
              <a href="{{route('home')}}" class="logo-my d-flex align-items-center pb-2">
                <img class="img-fluid" src="{{asset($settings->site_logo)}}" alt="">
              </a>
               <p>
                <!-- <strong>İstanbul Şubesi:</strong> {{$settings->company_address}}<br> -->
                <strong>Telefon:</strong> {{$settings->company_phone}}<br>
                <!-- <strong>Ankara Şubesi:</strong> {{$settings->address_second}}<br>
                <strong>Telefon:</strong> {{$settings->company_number}}<br> -->
                <strong>Eposta:</strong> {{$settings->company_email}}<br>
              </p>
              <div class="social-links d-flex mt-3">
                <a href="{{$settings->twitter}}" class="d-flex align-items-center justify-content-center"><i class="bi bi-twitter"></i></a>
                <a href="{{$settings->faebook}}" class="d-flex align-items-center justify-content-center"><i class="bi bi-facebook"></i></a>
                <a href="{{$settings->instagram}}" class="d-flex align-items-center justify-content-center"><i class="bi bi-instagram"></i></a>
                <a href="{{$settings->linkedin}}" class="d-flex align-items-center justify-content-center"><i class="bi bi-linkedin"></i></a>
              </div>
             
                <div class="column left"><br>
				          <h4>Uygulamayı Şimdi İndirin</h4>				
                  <a href="" target="_blank"><img src="{{asset('frontend/img/google-1.png')}}" alt="Service CRM for Android" width="50" style="display: inline;"></a>&nbsp;&nbsp;
                  <a href="" target="_blank"><img alt="Service CRM for iOS" src="{{asset('frontend/img/app-1.png')}}" width="50" style="display: inline;"></a>

                </p></div>
              
            </div>
          </div><!-- End footer info column-->

          <div class="col-lg-3 col-md-3 footer-links">
            <h4>Menu</h4>
            <ul>
              <li><a href="{{route('home')}}">Ana Sayfa</a></li>
              <li><a href="{{route('about')}}">Hakkkımızda</a></li>
              <li><a href="{{route('pricing')}}">Fiyatlandırma</a></li>
              <li><a href="{{route('products')}}">Kullanım Alanları</a></li>
              <li><a href="{{route('contact')}}">İletişim</a></li>
            </ul>
          </div><!-- End footer links column-->

          <div class="col-lg-3 col-md-3 footer-links">
            <h4>Kullanım Alanları</h4>
            <ul>
              @foreach($products as $item)
              <li><a href="{{route('product.details', $item->slug)}}">{{$item->title}}</a></li>
              @endforeach
            </ul>
          </div><!-- End footer links column-->
        </div>
      </div>
    </div>

    <div class="footer-legal position-relative">
      <div class="container">
        <div class="row copy-row">
        <div class="col-lg-7">
          <div class="copyright">
            &copy; Copyright 2024. Tüm Hakları Saklıdır
          </div>
        </div>

        <div class="col-lg-5">
          <div class="footer__copyright__links">
            <a href="https://www.fibermedya.com.tr/" target="_blank"><img src="{{asset('frontend/img/fibermedya.svg')}}" alt="Fiber Medya" width="100%" height="100%"></a>
          </div>
        </div>
      </div>
        
      </div>
    </div>

  </footer>