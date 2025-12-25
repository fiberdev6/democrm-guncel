@extends('frontend.main_master')
@section('main')

<div class="breadcrumbs d-flex align-items-center" style="background-image: url({{asset($page_about->page_banner)}});">
  <div class="container position-relative d-flex flex-column align-items-center" data-aos="fade">
    <h2>Hakkımızda</h2>
    <ol>
      <li><a href="{{route('home')}}">Anasayfa</a></li>
      <li>Hakkımızda</li>
    </ol>
  </div>
</div><!-- End Breadcrumbs -->

<!-- ======= About Section ======= -->
<section id="about" class="about">
  <div class="container" data-aos="fade-up">
    <div class="row position-relative">
      <div class="col-lg-7 about-img" style="background-image: url({{asset($about_all->image)}});"></div>
      <div class="col-lg-7">
        <h2>Hakkımızda</h2>
        <div class="our-story">
          <h3>{{$about_all->title}}</h3>
          <p>{!! $about_all->description !!}</p>
          <!-- <ul>
            <li><i class="bi bi-check-circle"></i> <span>Ullamco laboris nisi ut aliquip ex ea commo</span></li>
            <li><i class="bi bi-check-circle"></i> <span>Duis aute irure dolor in reprehenderit in</span></li>
            <li><i class="bi bi-check-circle"></i> <span>Ullamco laboris nisi ut aliquip ex ea</span></li>
          </ul> -->
          <div class="watch-video d-flex align-items-center position-relative">
            <i class="bi bi-arrow-right-circle"></i>
            <a data-aos="fade-up" data-aos-delay="200" href="{{route('contact')}}" class="btn-get-started">Bize Ulaşın</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- End About Section -->

<div class="container surecWrap py-5">
  <div class="text-center mx-auto wow fadeInUp" data-wow-delay="0.1s" style="max-width: 500px;">
    <h3 class="display-6 mb-5">Entegre Edilebilen Modüller</h3>
  </div>
  <div class="">
    <div class="row g-0 feature-row">
      @php
        $p = 0;
      @endphp
      @foreach($surec_yonetim as $surec)
        @php
          $p++;
        @endphp           
      <div class="col-md-6 col-lg-4 wow fadeIn " data-wow-delay="0.7s">
        <div class="featugitre-item border h-100 container-feature" >
          <span class="number">{{$p}}</span>
          <div class="btn-square bg-light rounded-circle mb-4" style="width: 64px; height: 64px;">
            <img class="img-fluid" src="{{asset($surec->card_icon)}}" alt="Icon">
          </div>
          <h5 class="mb-3">{{$surec->card_title}}</h5>
          <p class="mb-0">{{$surec->card_subtitle}}</p>
        </div>
      </div>
      @endforeach 
    </div>
  </div>
</div>

@endsection