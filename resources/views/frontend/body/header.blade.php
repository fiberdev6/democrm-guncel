@php 
$route = Route::current()->getName();
@endphp


@php 
$menus = App\Models\Menus::where('status','enable')->orderBy('sira', 'ASC')->get();
@endphp

@php 
$features = App\Models\Feature::orderBy('sira', 'ASC')->get();
@endphp

@php 
$settings = App\Models\Settings::find(1);
@endphp

<header id="header" class="header d-flex align-items-center sticky-top">
  <div class="container-fluid container-xl d-flex align-items-center justify-content-between">
    <a href="{{route('home')}}" class="logo-my d-flex align-items-center">
      <img class="img-fluid" src="{{asset($settings->site_logo)}}" alt="">
    </a>
    {{-- <div class="mobile-div">
      <div class="dropdown mobileDilBtnWrap">
        <button class="btn btn-secondary dilBtn dropdown-toggle rounded-pill mobilbtn" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false"><img src="{{asset('frontend/img/england.svg')}}" style="width: 20px;"> EN</button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
          <li><a class="dropdown-item" href="#" onclick="doGTranslate('en|en');return false;"><img src="{{asset('frontend/img/england.svg')}}" width="20px"> English</a></li>
          <li><a class="dropdown-item" href="#" onclick="doGTranslate('en|es');return false;"><img src="{{asset('frontend/img/spain.png')}}" width="20px"> Spanish</a></li>
          <li><a class="dropdown-item" href="#" onclick="doGTranslate('en|fr');return false;"><img src="{{asset('frontend/img/france.png')}}" width="20">French</a></li>
          <li><a class="dropdown-item" href="#" onclick="doGTranslate('en|de');return false;"><img src="{{asset('frontend/img/germany.png')}}" width="20">German</a></li>
          <li><a class="dropdown-item" href="#" onclick="doGTranslate('en|nl');return false;"><img src="{{asset('frontend/img/holland.png')}}" width="20">Dutch</a></li>
          <li><a class="dropdown-item" href="#" onclick="doGTranslate('en|it');return false;"><img src="{{asset('frontend/img/italy.png')}}" width="20">Italian</a></li>
        </ul>
      </div>
      <i class="mobile-nav-toggle mobile-nav-show bi bi-list"></i>
      <i class="mobile-nav-toggle mobile-nav-hide d-none bi bi-x"></i>
    </div> --}}
      
    <nav id="navbar" class="navbar navbar-expand-lg">
      <ul>
        @foreach($menus as $item)
          @if($item->ustMenu == "0")
            <li><a href="{{route($item->link)}}" class="nav-link {{ ($route == $item->link) ? 'active': '' }}">{{$item->name}}</a></li>
          @else
            <li>
              <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">{{$item->name}}</a>
                <div class="dropdown-menu bg-light rounded-0 rounded-bottom m-0" aria-labelledby="dropdownMenuLink">
                  @foreach($features as $feature)
                    <a href="{{route('feature.details', $feature->slug)}}" class="dropdown-item">{{$feature->title}} </a>
                  @endforeach
                </div>
              </div>            
            </li>          
          @endif
        @endforeach
      </ul>
      <a data-aos="fade-up" data-aos-delay="200" href="tel:{{$settings->company_phone}}" class="btn-get-started phone-button"><i class="fa fa-phone-alt"></i> {{$settings->company_phone}}</a>
      <!-- <a href="tel:{{$settings->company_phone}}" class="nav-item nav-link btn-get-started btn-sari rounded-pill mobil-gizle"><i class="fa fa-phone-alt"></i> {{$settings->company_phone}}</a> -->
      {{-- <div class="dropdown dilBtnWrap">
        <button class="btn btn-secondary dilBtn dropdown-toggle rounded-pill" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false"><img src="{{asset('frontend/img/england.svg')}}" style="width: 20px;"> EN</button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
          <li><a class="dropdown-item" href="#" onclick="doGTranslate('en|en');return false;"><img src="{{asset('frontend/img/england.svg')}}"> English</a></li>
          <li><a class="dropdown-item" href="#" onclick="doGTranslate('en|es');return false;"><img src="{{asset('frontend/img/spain.png')}}"> Spanish</a></li>
          <li><a class="dropdown-item" href="#" onclick="doGTranslate('en|fr');return false;"><img src="{{asset('frontend/img/france.png')}}">French</a></li>
          <li><a class="dropdown-item" href="#" onclick="doGTranslate('en|de');return false;"><img src="{{asset('frontend/img/germany.png')}}">German</a></li>
          <li><a class="dropdown-item" href="#" onclick="doGTranslate('en|nl');return false;"><img src="{{asset('frontend/img/holland.png')}}">Dutch</a></li>
          <li><a class="dropdown-item" href="#" onclick="doGTranslate('en|it');return false;"><img src="{{asset('frontend/img/italy.png')}}">Italian</a></li>
        </ul>
      </div> --}}
    </nav><!-- .navbar -->
  </div>
</header><!-- End Header -->