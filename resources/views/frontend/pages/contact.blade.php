@extends('frontend.main_master')
@section('main')

<div class="breadcrumbs d-flex align-items-center" style="background-image: url({{asset($page_contact->page_banner)}});">
  <div class="container position-relative d-flex flex-column align-items-center" data-aos="fade">
    <h2>Contact</h2>
    <ol>
      <li><a href="{{route('home')}}">Ana Sayfa</a></li>
      <li>İletişim</li>
    </ol>
  </div>
</div><!-- End Breadcrumbs -->

<!-- ======= Contact Section ======= -->
<section id="contact" class="contact">
  <div class="container" data-aos="fade-up" data-aos-delay="100">
    <div class="row gy-4">
      <!-- <div class="col-lg-6">
        <div class="info-item  d-flex flex-column justify-content-center align-items-center">
          <i class="bi bi-map"></i>
          <h3>Address</h3>
          <p><strong>İstanbul Şubesi:</strong> {{$settings->company_address}}</p>
        </div>
      </div> -->
      <div class="col-lg-6 col-md-6">
        <div class="info-item d-flex flex-column justify-content-center align-items-center">
          <i class="bi bi-envelope"></i>
          <h3>Eposta</h3>
          <p>{{$settings->company_email}}</p>
          <p></p>
        </div>
      </div><!-- End Info Item -->
      <div class="col-lg-6 col-md-6">
        <div class="info-item  d-flex flex-column justify-content-center align-items-center">
          <i class="bi bi-telephone"></i>
          <h3>Telefon Numarası</h3>
          <p>{{$settings->company_phone}}</p>
          <p>{{$settings->company_number}}</p>
        </div>
      </div><!-- End Info Item -->
    </div>
    <div class="row gy-4 mt-1" >
      <div class="col-lg-6">
      <div class="social-links d-flex mt-3">
      <h4>Serbis CRM'i denemeye hazır mısınız? Lütfen yandaki formu doldurun</h4>
              </div>
             
                <div class="column left"><br>
				          <h5>Uygulamayı Şimdi İndirin</h5>				
                  <a href="" target="_blank"><img src="{{asset('frontend/img/google-1.png')}}" alt="Service CRM for Android" width="150"></a>&nbsp;&nbsp;
                  <a href="" target="_blank"><img alt="Service CRM for iOS" src="{{asset('frontend/img/app-1.png')}}" width="150"></a>

                </p></div>
      </div><!-- End Google Maps -->         
      <div class="col-lg-6">
        <form action="{{route('store.message')}}" method="post"  class="php-email-form">
          @csrf

            @if(Session::has("success"))
              <div class="alert alert-success alert-dismissible"><button type="button" class="close">&times;</button>{{Session::get('success')}}</div>
            @elseif(Session::has("failed"))
              <div class="alert alert-danger alert-dismissible"><button type="button" class="close">&times;</button>{{Session::get('failed')}}</div>
            @endif
          <h5 class="mobil-h3">Bize Ulaşın</h5>
          <div class="row gy-4">
            <div class="col-lg-6 form-group">
              <input type="text" name="name" class="form-control" id="name" placeholder="Ad-Soyad" required>
            </div>
            <div class="col-lg-6 form-group">
              <input type="email" class="form-control" name="email" id="email" placeholder="Eposta" required>
            </div>
          </div>
          <div class="form-group">
            <input type="subject" class="form-control" name="phone" id="subject" placeholder="Telefon Numarası" required>
          </div>
          <div class="form-group">
            <textarea class="form-control" name="message" rows="5" placeholder="Mesaj" required></textarea>
          </div>
          
          <div class="text-center"><button type="submit">Gönder</button></div>
        </form>
      </div><!-- End Contact Form -->
    </div>
  </div>
</section><!-- End Contact Section -->

@endsection