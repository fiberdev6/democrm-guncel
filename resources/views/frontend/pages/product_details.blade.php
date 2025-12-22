@extends('frontend.main_master')
@section('main')

  <div class="breadcrumbs d-flex align-items-center" style="background-image: url({{asset($banner->page_banner)}});">
      <div class="container position-relative d-flex flex-column align-items-center" data-aos="fade">

        <h2>{{$category_details->title}}</h2>
        <ol>
          <li><a href="{{route('home')}}">Ana Sayfa</a></li>
          <li><a href="{{route('products')}}">Kullanım Alanları</a></li>
          <li>{{$category_details->title}}</li>
        </ol>

      </div>
    </div><!-- End Breadcrumbs -->

    
    <section id="alt-services" class="alt-services">
      <div class="container" data-aos="fade-up">

        <div class="row justify-content-around gy-4">
          <div class="col-lg-5 img-bg" style="background-image: url({{asset($category_details->image)}});" data-aos="zoom-in" data-aos-delay="100"></div>

          <div class="col-lg-7 d-flex flex-column ">
            <h3>{{$category_details->title}}</h3>
            <p>{!! $category_details->description !!}</p>
            <div class="row">
            </div>
          </div>
        </div>

      </div>
    </section><!-- End Alt Services Section -->

    @if($products_all)
    <div class="container-xxl urunlerWrap urunlerPageWrap pt-3 pb-3">
      <div class="container">
          <div class="row g-4 project-carousel wow fadeInUp" data-wow-delay="0.1s">
          @foreach($products_all as $pro)
            <div class="col-lg-3 col-md-6 col-sm-6 wow fadeInUp">
                  <div class="project-item">
                      <div class="position-relative">
                          <img class="img-fluid" src="{{asset($pro->image)}}" alt="">
                          <a class="project-overlay" href="{{route('products.alt', $pro->slug)}}">
                              <button class="btn btn-lg-square btn-light m-1" type="button"><i class="fa fa-link"></i> İncele</button>
                          </a>
                      </div>
                      <a class="d-block p-4 m-0 h5" href="{{route('products.alt', $pro->slug)}}">{{$pro->title}}</a>
                  </div>
            </div>
          @endforeach
      
          </div>
      </div>
    </div>
   @endif

  @if(count($category_images) > 0)
  <div class="container galeriWrap py-3">
    <div class="container">
        <div class="row g-4">
            @foreach($category_images as $images)
            <div class="col-lg-3 col-md-6 col-6 wow fadeInUp" data-wow-delay="0.1s">
                <a href="{{asset($images->image)}}" data-fancybox="galeriGroup" class="team-item rounded overflow-hidden"><img class="img-fluid" src="{{asset($images->image)}}" alt=""><i class="far fa-eye"></i></a>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
@endsection