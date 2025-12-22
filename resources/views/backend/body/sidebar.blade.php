 <!-- ========== Left Sidebar Start ========== -->
@php 
$siteurl= App\Models\Settings::find(1);
@endphp

<div class="vertical-menu">
  <div data-simplebar class="h-100">
    <!--- Sidebar -->
    <div id="sidebar-menu">
      <!-- Left Menu Start -->
      <div class="title">
          <a href="{{$siteurl->site_url}}" target="_blank" class="waves-effect">
            <span class="badge rounded-pill bg-success float-end"></span>
            <span>Siteyi Görüntüle</span>
          </a>
      </div>
      <ul class="metismenu list-unstyled" id="side-menu">

        <li>
          <a href="{{ route('dashboard')}}" class="waves-effect">
            <i class="ri-dashboard-line"></i><span class="badge rounded-pill bg-success float-end"></span>
            <span>Anasayfa</span>
          </a>
        </li>

        <li>
          <a href="javascript: void(0);" class="has-arrow waves-effect">
            <i class="ri-settings-3-fill"></i>
            <span>Genel Ayarlar</span>
          </a>
          <ul class="sub-menu" aria-expanded="false">
            <li><a href="{{ route('site.settings')}}">Site Ayarları</a></li>
            <li><a href="{{ route('email.settings')}}">Email Ayarları</a></li>
            <li><a href="{{ route('google.settings')}}">Google Ayarları</a></li>
            <li><a href="{{ route('company.settings')}}">Firma Ayarları</a></li>
            <li><a href="{{ route('social.media.settings')}}">Sosyal Medya Ayarları</a></li>
          </ul>
        </li>

        <li>
          <a href="{{ route('contact.message')}}" class="waves-effect">
            <i class="ri-mail-open-line"></i><span class="badge rounded-pill bg-success float-end"></span>
            <span>Mesajlar</span>
          </a>
        </li>

        <li>
          <a href="javascript: void(0);" class="has-arrow waves-effect">
            <i class="ri-grid-line"></i>
            <span>Anasayfa</span>
          </a>
          <ul class="sub-menu" aria-expanded="false">
            <li><a href="{{ route('home.image')}}">Slider</a></li>
            <!-- <li><a href="{{ route('all.home.card')}}">Anasayfa Hizmetler</a></li> -->
            <li><a href="{{ route('all.faq')}}">Anasayfa Hakkımızda</a></li>
            <!-- <li><a href="{{ route('all.privacy')}}">Gizlilik Politikası</a></li> -->
            <li><a href="{{ route('all.misyon')}}">Anasayfa İletişim</a></li>
            <li><a href="{{ route('all.pricing')}}">Paket Fiyatları</a></li>
            <li><a href="{{ route('all.client')}}">Sıkça Sorulan Sorular</a></li>
          </ul>
        </li>

       

        <li>
          <a href="{{ route('all.about')}}" class="waves-effect">
            <i class="ri-file-paper-2-line"></i><span class="badge rounded-pill bg-success float-end"></span>
            <span>Hakkımızda</span>
          </a>
        </li>

        <li>
          <a href="{{ route('all.categories')}}" class="waves-effect">
            <i class="ri-dashboard-line"></i><span class="badge rounded-pill bg-success float-end"></span>
            <span>Kullanım Alanları</span>
          </a>
        </li>

        <!-- <li>
          <a href="{{ route('all.product')}}" class="waves-effect">
            <i class="ri-dashboard-line"></i><span class="badge rounded-pill bg-success float-end"></span>
            <span>Hizmetlerimiz</span>
          </a>
        </li> -->


        

        <!-- <li>
          <a href="{{ route('all.client')}}" class="waves-effect">
            <i class="ri-message-3-line"></i><span class="badge rounded-pill bg-success float-end"></span>
            <span>Yorumlar</span>
          </a>
        </li> -->

        <li>
          <a href="{{ route('pages')}}" class="waves-effect">
            <i class="ri-book-3-line"></i><span class="badge rounded-pill bg-success float-end"></span>
            <span>Sayfalar</span>
          </a>
        </li>

        <!-- <li>
          <a href="{{ route('all.documents')}}" class="waves-effect">
            <i class="ri-book-3-line"></i><span class="badge rounded-pill bg-success float-end"></span>
            <span>Kataloglar</span>
          </a>
        </li> -->


        <li>
          <a href="{{ route('all.menus')}}" class="waves-effect">
            <i class="ri-menu-fill"></i><span class="badge rounded-pill bg-success float-end"></span>
            <span>Menuler</span>
          </a>
        </li>

        <li>
          <a href="{{ route('all.home.card')}}" class="waves-effect">
            <i class="ri-book-3-line"></i><span class="badge rounded-pill bg-success float-end"></span>
            <span>Entegre Edilebilen Modüller</span>
          </a>
        </li>

        <li>
          <a href="{{ route('add.references')}}" class="waves-effect">
            <i class="ri-book-3-line"></i><span class="badge rounded-pill bg-success float-end"></span>
            <span>Çözüm Ortakları</span>
          </a>
        </li>

        

        <li>
          <a href="{{ route('all.features')}}" class="waves-effect">
            <i class="ri-inbox-line"></i><span class="badge rounded-pill bg-success float-end"></span>
            <span>Özellikler</span>
          </a>
        </li>
      </ul>
    </div>
    <!-- Sidebar -->
  </div>
</div>
<!-- Left Sidebar End -->
