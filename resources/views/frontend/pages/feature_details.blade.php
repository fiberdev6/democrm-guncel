@extends('frontend.main_master')
@section('main')

<div class="breadcrumbs d-flex align-items-center" style="background-image: url({{asset($banner->page_banner)}});">
  <div class="container position-relative d-flex flex-column align-items-center" data-aos="fade">
    <h2>{{$feature_details->title}}</h2>
    <ol>
      <li><a href="{{route('home')}}">Ana Sayfa</a></li>
      <li><a href="">Özellikler</a></li>
      <li>{{$feature_details->title}}</li>
    </ol>
  </div>
</div><!-- End Breadcrumbs -->

<section id="alt-services" class="alt-services">
  <div class="container" data-aos="fade-up">
    <div class="row justify-content-around gy-4">
      <div class="col-lg-5 img-bg" style="background-image: url({{asset($feature_details->image)}});" data-aos="zoom-in" data-aos-delay="100"></div>
      <div class="col-lg-7 d-flex flex-column ">
        <h3>{{$feature_details->title}}</h3>
        <p>{!! $feature_details->description !!}</p>
        <div class="row"></div>
      </div>
    </div>
  </div>
</section><!-- End Alt Services Section -->

<div class="container galeriWrap py-3">
    <div class="container">
        <div class="row g-4">
            @foreach($feature_images as $images)
            <div class="col-lg-3 col-md-6 col-6 wow fadeInUp" data-wow-delay="0.1s">
                <a href="{{asset($images->image)}}" data-fancybox="galeriGroup" class="team-item rounded overflow-hidden"><img class="img-fluid" src="{{asset($images->image)}}" alt=""><i class="far fa-eye"></i></a>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection