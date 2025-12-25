<section id="hero" class="hero">
  <div id="hero-carousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
    @php
      $slideSay = 0;
    @endphp

    @foreach($slide as $slider)
      @php
        $slideSay++;
      @endphp

      <div class="carousel-item {{ $slideSay == 1 ? 'active' : '' }}" style="background-image: url({{ asset($slider->home_image) }})">
        <div class="info d-flex align-items-center">
          <div class="container">
            <div class="row justify-content-start">
              <div class="col-lg-7 text-start">
                <p data-aos="fade-up text-white">{{ $slider->title }}</p>
                <h2 data-aos="fade-down text-white animated slideInRight">{{ $slider->sub_title }}</h2>
                <a data-aos="fade-up" data-aos-delay="200" href="tel:{{$settings->company_phone}}" class="btn-get-started">Demo Talep Edin</a>
                
              </div>
            </div>
          </div>
        </div>
      </div>
    @endforeach

    <a class="carousel-control-prev" href="#hero-carousel" role="button" data-bs-slide="prev">
      <span class="carousel-control-prev-icon bi bi-chevron-left" aria-hidden="true"></span>
    </a>

    <a class="carousel-control-next" href="#hero-carousel" role="button" data-bs-slide="next">
      <span class="carousel-control-next-icon bi bi-chevron-right" aria-hidden="true"></span>
    </a>
  </div>

</section><!-- End Hero Section -->