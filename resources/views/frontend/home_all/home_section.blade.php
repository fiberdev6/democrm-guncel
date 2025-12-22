 <!-- ======= Get Started Section ======= -->
 <section id="get-started" class="get-started container-xxl">
      <div class="container">

        <div class="row justify-content-between gy-4">

          <div class="col-lg-5" data-aos="fade">
            <form action="{{route('store.message')}}" method="post" class="php-email-form">
              @csrf

              @if(Session::has("success"))
                <div class="alert alert-success alert-dismissible"><button type="button" class="close">&times;</button>{{Session::get('success')}}</div>
              @elseif(Session::has("failed"))
                <div class="alert alert-danger alert-dismissible"><button type="button" class="close">&times;</button>{{Session::get('failed')}}</div>
              @endif
              <h3>Bize Ulaşın</h3>
              <p>Programımızla ilgili herhangi bir soru, öneri veya talebiniz varsa aşağıdaki iletişim formumuzu doldurabilirsiniz.</p>
              <div class="row gy-3">

               
                  <div class="col-md-6">
                    <input type="text" name="name" class="form-control" placeholder="Ad-Soyad" required>
                  </div>

                  <div class="col-md-6 ">
                    <input type="email" class="form-control" name="email" placeholder="Eposta" required>
                  </div>

                <div class="col-md-12">
                  <input type="text" class="form-control" name="phone" placeholder="Telefon Numarası" required>
                </div>

                <div class="col-md-12">
                  <textarea class="form-control" name="message" rows="6" placeholder="Mesaj" required></textarea>
                </div>

                <div class="col-md-12 text-center">
                  <div class="loading">Gönderiliyor</div>
                  <div class="error-message"></div>
                  <div class="sent-message">Teklif talebiniz başarıyla iletildi. Teşekkür ederiz!</div>

                  <button type="submit">Gönder</button>
                </div>

              </div>
            </form>
          </div><!-- End Quote Form -->
          <div class="col-lg-6 d-flex " >
            <div class="content iletisim-content">
              <h2>Daha Fazla Bilgi İçin</h2>
              <h3>{{$home_section->title}}</h3>
              <p>{!! $home_section->description !!}</p>
              <a data-aos="fade-up" data-aos-delay="200" href="tel:{{$settings->company_phone}}" class="btn-get-started">Bize Ulaşın</a>
            </div>
          </div>

          

        </div>

      </div>
    </section><!-- End Get Started Section -->