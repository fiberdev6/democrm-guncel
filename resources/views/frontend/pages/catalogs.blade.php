@extends('frontend.main_master')
@section('main')

<div class="breadcrumbs d-flex align-items-center" style="background-image: url({{asset($page_katalog->page_banner)}});">
      <div class="container position-relative d-flex flex-column align-items-center" data-aos="fade">

        <h2>Catalogs</h2>
        <ol>
          <li><a href="{{route('home')}}">Home</a></li>
          <li>Catalogs</li>
        </ol>

      </div>
    </div><!-- End Breadcrumbs -->

    <!-- ======= About Section ======= -->
   
    <div class="container kataloglarWrap py-4">
      <div class="container">
          <div class="row g-4">
            @foreach($katalogs as $katalog)
              <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.7s">
                  <div class="team-item rounded overflow-hidden">
                      <i class="fas fa-file-pdf"></i>
                      <h5>{{$katalog->title}}</h5>
                      <div class="btnWrap">
                          <a href="{{asset($katalog->files)}}" download class="btn btn-sari"><i class="fa fa-download me-1"></i> Download</a>
                          <a href="{{asset($katalog->files)}}" target="_blank" class="btn btn-sari" data-fancybox="galeriGroup"><i class="fa fa-eye me-1"></i> Preview</a>
                      </div>
                  </div>
              </div>
            @endforeach
          </div>
      </div>
  </div>

@endsection