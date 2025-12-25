<section id="alt-services" class="alt-services container-xxl">
  <div class="container" data-aos="fade-up">
    <div class="row justify-content-around gy-4">
      <div class="col-lg-12 d-flex flex-column justify-content-center">
        <h3>{{$home_about->question}}</h3>
        <p>{!! $home_about->description !!}</p>
        <div class="row">
          <div class="col-lg-3">              
            <div class="icon-box d-flex position-relative " data-aos="fade-up" data-aos-delay="100">
              <div class="box">
                <i class="bi bi-people-fill float-left"></i>
                <h4><a class="stretched-link">Müşteri Yönetimi</a></h4>
                <p class="">Tüm müşterilerinizi tek ekranda listeleyebilir, kategorilere ayırabilir ve detaylı aramalar yapabilirsiniz.</p>
              </div>
            </div><!-- End Icon Box -->
          </div>           
          <div class="col-lg-3">
            <div class="icon-box d-flex position-relative" data-aos="fade-up" data-aos-delay="200">
              <div class="box">
                <i class="bi bi-bar-chart-fill float-left"></i>
                <h4><a  class="stretched-link">Personel Yönetimi</a></h4>
                <p>Tüm personelinizi tek ekranda listeleyebilir, gruplara ayırabilir ve detaylı aramalar yapabilirsiniz.</p>
              </div>
            </div><!-- End Icon Box -->
          </div>
          <div class="col-lg-3">               
            <div class="icon-box d-flex position-relative " data-aos="fade-up" data-aos-delay="100">
              <div class="box">
                <i class="bi bi-briefcase-fill float-left"></i>
                <h4><a class="stretched-link">Kasa Takibi</a></h4>
                <p class="">Tüm kasa işlemlerinizi tek bir ekranda listeleyebilir, gelir ve giderlerinizi detaylı olarak listeleyebilirsiniz./p>
              </div>
            </div><!-- End Icon Box -->
          </div>
          <div class="col-lg-3">               
            <div class="icon-box d-flex position-relative " data-aos="fade-up" data-aos-delay="100">
              <div class="box">
                <i class="bi bi-box2-fill float-left"></i>
                <h4><a class="stretched-link">Stok Yönetimi</a></h4>
                <p class="">Stoktaki tüm ürünleri tek ekranda listeleyebilir, cihaz ve markaya göre ayırabilir, arama yapabilirsiniz.</p>
              </div>
            </div><!-- End Icon Box -->
          </div>
        </div>
      </div>
    </div>
  </div>
</section><!-- End Alt Services Section -->

<section class="container-xxl" id="faq-section">
  <div class="container" data-aos="fade-up">
    <div class="col-lg-12 d-flex flex-column justify-content-center">
    <div class="section-header"  >
          <h2>Gelişmiş Servis Yönetimi</h2>
        </div>
      <p>{!! $home_about->description !!}</p>
      <div class="row">
        <div class="col-lg-6">
		      <div class="accordion" id="accordionPanelsStayOpenExample">
  @foreach($faqs as $faq)
  <div class="accordion-item">
    <h2 class="accordion-header" id="heading{{$faq->id}}">
      <button class="accordion-button" type="button" data-bs-toggle="collapse" 
        data-bs-target="#collapse{{$faq->id}}" aria-expanded="true" 
        aria-controls="collapse{{$faq->id}}">
        {{$faq->name}}
      </button>
    </h2>
    <div id="collapse{{$faq->id}}" 
         class="accordion-collapse collapse {{$faq->job == '1' ? 'show' : ''}}" 
         aria-labelledby="heading{{$faq->id}}" 
         data-bs-parent="#accordionPanelsStayOpenExample" style="visibility: visible;">
      <div class="accordion-body">
        {!! $faq->message !!}
      </div>
    </div>
  </div>
  @endforeach
</div>

        </div>
        <div class="col-lg-6">
          <iframe class="desktopVideo" width="90%" height="330" src="https://www.youtube.com/embed/Caa1CJUFFIs"  title="SERBİS - Servis Bilişim Sistemleri Teknik Servis Programı" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen=""></iframe>
        </div>
      </div>
    </div>
  </div>
</section>

   