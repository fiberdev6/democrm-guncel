@extends('frontend.main_master')
@section('main')

<div class="breadcrumbs d-flex align-items-center" style="background-image: url({{asset($page_products->page_banner)}});">
  <div class="container position-relative d-flex flex-column align-items-center" data-aos="fade">
    <h2>Kullanım Alanları</h2>
    <ol>
      <li><a href="{{route('home')}}">Ana Sayfa</a></li>
      <li>Kullanım Alanları</li>
    </ol>
  </div>
    </div><!-- End Breadcrumbs -->

    
    <section>
  <div class="container-xxl urunlerWrap pt-3 pb-3">
    <div class="container" data-aos="fade-up">
      <div class="section-header">
        <h2>Kimler kullanabilir?</h2>
      </div>
      <div class="row g-4 project-carousel wow fadeInUp" data-wow-delay="0.1s">
        @foreach($all_products as $item)
          <div class="col-lg-3 col-md-6 col-sm-6 wow fadeInUp">
            <div class="project-item">
              <div class="position-relative">
                <img class="img-fluid" src="{{asset($item->image)}}" alt="">
                <a class="project-overlay" href="{{route('product.details', $item->slug)}}">
                  <button class="btn btn-lg-square btn-light m-1" type="button"><i class="fa fa-link"></i> İncele</button>
                </a>
              </div>
              <a class="d-block p-4 m-0 h5" href="{{route('product.details', $item->slug)}}" style="font-size:medium">{{$item->title}}</a>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</section>
@endsection