<div class="kasaSubMenu" id="kasaSubeMenuId"  > {{-- Genel ayarlarda dropdown altta kaldığı zaman bu css'i eklemiştim. Daha sonra modalları bozmaktaydı. style="margin-top:15px;position:relative;z-index:10;"  --}}
  <ul class="nav nav-pills nav-justified" role="tablist" style="margin-bottom: 5px">
    <li class="nav-item" style="font-size: 14px;">
      <div class="dropdown">
        <a href="#" class="btn btn-secondary dropdown-toggle nav-link" data-bs-toggle="dropdown" aria-expanded="true">
          <span>Firma Ayarları</span> <i class="fa fa-angle-down custom-icon"></i>
        </a>
        <div class="dropdown-menu" style="">
          <a class="dropdown-item nav1 active" data-bs-toggle="pill" href="#tab1" data-id="" role="tab">
            <i class="fas fa-money custom-icon"></i>Firma Bilgileri
          </a>
          {{-- <a class="dropdown-item nav2" data-bs-toggle="pill" href="#tab2" data-id="" role="tab">
            <i class="fas fa-money custom-icon"></i>Sms Ayarları
          </a> --}}
          <a class="dropdown-item nav24" data-bs-toggle="pill" href="#tab24" data-id="" role="tab">
            <i class="fas fa-money custom-icon"></i>Prim Ayarları
          </a>
          {{-- <a class="dropdown-item nav27" data-bs-toggle="pill" href="#tab27" data-id="" role="tab">
            <i class="fas fa-money custom-icon"></i>Api Ayarları
          </a> --}}
        </div>
      </div>
    </li>
    
    <li class="nav-item" style="font-size: 14px;">
      <div class="dropdown">
        <a href="#" class="btn btn-secondary dropdown-toggle nav-link" data-bs-toggle="dropdown" aria-expanded="true">
          <span>Servis Ayarları</span> <i class="fa fa-angle-down custom-icon" ></i>
        </a>
        <div class="dropdown-menu" style="">
          <a class="dropdown-item nav3" data-bs-toggle="pill" href="#tab3" data-id="" role="tab">
            <i class="fas fa-money custom-icon"></i>Cihaz Markaları
          </a>
          <a class="dropdown-item nav4" data-bs-toggle="pill" href="#tab4" data-id="" role="tab">
            <i class="fas fa-money custom-icon"></i>Cihaz Türleri
          </a>
          <a class="dropdown-item nav5" data-bs-toggle="pill" href="#tab5" data-id="" role="tab">
            <i class="fas fa-money custom-icon"></i>Garanti Süreleri
          </a>
          <a class="dropdown-item nav6" data-bs-toggle="pill" href="#tab6" data-id="" role="tab">
            <i class="fas fa-money custom-icon"></i>Servis Araçları
          </a>
          @if(Auth::user()->isSuperAdmin())
          <a class="dropdown-item nav7" data-bs-toggle="pill" href="#tab7" data-id="" role="tab">
            <i class="fas fa-money custom-icon"></i>Servis Aşamaları
          </a>
          <a class="dropdown-item nav8" data-bs-toggle="pill" href="#tab8" data-id="" role="tab">
            <i class="fas fa-money custom-icon"></i>Servis Aşama Soruları
          </a>
          @endif
          <a class="dropdown-item nav9" data-bs-toggle="pill" href="#tab9" data-id="" role="tab">
            <i class="fas fa-money custom-icon"></i>Servis Görüntüleme Zamanı
          </a>
          <a class="dropdown-item nav10" data-bs-toggle="pill" href="#tab10" data-id="" role="tab">
            <i class="fas fa-money custom-icon"></i>Servis Kaynakları
          </a>
          {{-- <a class="dropdown-item " data-bs-toggle="pill" href="#tab11" data-id="" role="tab">
            <i class="fas fa-money custom-icon"></i>Servis Palanlama Personel Ayarları
          </a>
          <a class="dropdown-item " data-bs-toggle="pill" href="#tab12" data-id="" role="tab">
            <i class="fas fa-money custom-icon"></i>Servis Planlama Durumları
          </a> --}}
          <a class="dropdown-item nav13" data-bs-toggle="pill" href="#tab13" data-id="" role="tab">
            <i class="fas fa-money custom-icon"></i>Silinen Servisler
          </a>
        </div>
      </div>
    </li>
    
    @if(Auth::user()->isSuperAdmin())
    <li class="nav-item" style="font-size: 14px;">
      <div class="dropdown">
        <a href="#" class="btn btn-secondary dropdown-toggle nav-link" data-bs-toggle="dropdown" aria-expanded="true">
          <span>İzinler ve Roller</span> <i class="fa fa-angle-down custom-icon"></i>
        </a>
        <div class="dropdown-menu" style="">
          <a class="dropdown-item nav14" data-bs-toggle="pill" href="#tab14" data-id="" role="tab">
            <i class="fas fa-money custom-icon"></i>İzinler
          </a>
          <a class="dropdown-item nav15" data-bs-toggle="pill" href="#tab15" data-id="" role="tab">
            <i class="fas fa-money custom-icon"></i>Roller
          </a>
        </div>
      </div>
    </li>
    @endif

    <li class="nav-item" style="font-size: 14px;">
      <div class="dropdown">
        <a href="#" class="btn btn-secondary dropdown-toggle nav-link" data-bs-toggle="dropdown" aria-expanded="true">
          <span>Depo Ayarları</span> <i class="fa fa-angle-down custom-icon"></i>
        </a>
        <div class="dropdown-menu" style="">
          <a class="dropdown-item nav16" data-bs-toggle="pill" href="#tab16" data-id="" role="tab">
            <i class="fas fa-money custom-icon"></i>Ürün Grupları
          </a>
          <a class="dropdown-item nav17" data-bs-toggle="pill" href="#tab17" data-id="" role="tab">
            <i class="fas fa-money custom-icon"></i>Stok Rafları
          </a>
          <a class="dropdown-item nav18" data-bs-toggle="pill" href="#tab18" data-id="" role="tab">
            <i class="fas fa-money custom-icon"></i>Tedarikçiler
          </a>
        </div>
      </div>
    </li>

    <li class="nav-item" style="font-size: 14px;">
      <div class="dropdown">
        <a href="#" class="btn btn-secondary dropdown-toggle nav-link" data-bs-toggle="dropdown" aria-expanded="true">
          <span>Kasa Ayarları</span> <i class="fa fa-angle-down custom-icon"></i>
        </a>
        <div class="dropdown-menu" style="">
          <a class="dropdown-item nav19" data-bs-toggle="pill" href="#tab19" data-id="" role="tab">
            <i class="fas fa-money custom-icon"></i>Ödeme Türleri
          </a>
          @if(Auth::user()->isSuperAdmin())
          <a class="dropdown-item nav20" data-bs-toggle="pill" href="#tab20" data-id="" role="tab">
            <i class="fas fa-money custom-icon"></i>Ödeme Şekilleri
          </a>
          @endif
        </div>
      </div>
    </li>

    <li class="nav-item" style="font-size: 14px;">
      <div class="dropdown">
        <a href="#" class="btn btn-secondary dropdown-toggle nav-link" data-bs-toggle="dropdown" aria-expanded="true">
          <span>Yazıcı ve Uygulama Ayarları</span> <i class="fa fa-angle-down custom-icon"></i>
        </a>
        <div class="dropdown-menu" style="">
          
          <a class="dropdown-item nav22" data-bs-toggle="pill" href="#tab22" data-id="" role="tab">
            <i class="fas fa-money custom-icon"></i>Servis Form Ayarları
          </a>
          <a class="dropdown-item nav23" data-bs-toggle="pill" href="#tab23" data-id="" role="tab">
            <i class="fas fa-money custom-icon"></i>Yazıcı Fiş Tasarımı
          </a>
            <a class="dropdown-item nav25" data-bs-toggle="pill" href="#tab25" data-id="" role="tab">
            <i class="fas  custom-icon"></i>Sistem Log Kayıtları
          </a>
          @if(Auth::user()->isSuperAdmin())
          <a class="dropdown-item nav26" data-bs-toggle="pill" href="#tab26" data-id="" role="tab">
              Kullanım Koşulları & Gizlilik Politikası
          </a>
          @endif
        </div>
      </div>
    </li>
    
  </ul> 
  <div class="tab-content">
    <div id="tab1" class="tab-pane active" style="padding: 0" role="tabpanel"></div>
    <div id="tab2" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
    <div id="tab3" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
    <div id="tab4" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
    <div id="tab5" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
    <div id="tab6" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
    <div id="tab7" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
    <div id="tab8" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
    <div id="tab9" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
    <div id="tab10" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
    <div id="tab11" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
    <div id="tab12" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
    <div id="tab13" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
    <div id="tab14" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
    <div id="tab15" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
    <div id="tab16" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
    <div id="tab17" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
    <div id="tab18" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
    <div id="tab19" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
    <div id="tab20" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
    <div id="tab22" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
    <div id="tab23" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
    <div id="tab24" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
    <div id="tab25" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
    <div id="tab26" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
    <div id="tab27" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
  </div>
</div>
  
<script type="text/javascript">
  $(document).ready(function (e) {
    var firma_id = {{$firma->id}};
    $.ajax({
      url: "/"+ firma_id + "/firma-bilgileri"
    }).done(function(data) {
      if($.trim(data)==="-1"){
        window.location.reload(true);
      }else{
        $('#tab1').html(data);
      }
    });
  });
</script>
  
<script>
  $(document).ready(function () {
    // Dropdown'ların düzgün kapanması için
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.dropdown').length) {
                    $('.dropdown-menu').removeClass('show');
                    $('.dropdown-toggle').attr('aria-expanded', 'false');
                }
            });
    function loadData(url, tabId) {
      $.ajax({
        url: url,
      }).done(function (data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $(tabId).html(data);
        }
      });
    }
  
    // Nav-link'ler için click olaylarını ayarlayın
    $('.kasaSubMenu .nav-link').on('click', function () {
      var tabMap = {
        "nav1": "/{{$firma->id}}/firma-bilgileri",
        "nav2": "/{{$firma->id}}/sms-ayarlari",
        "nav3": "/{{$firma->id}}/cihaz-markalari",
        "nav4": "/{{$firma->id}}/cihaz-turleri",
        "nav5": "/{{$firma->id}}/garanti-sureleri",
        "nav6": "/{{$firma->id}}/araclar",
        "nav7": "/{{$firma->id}}/servis-asamalari",
        "nav8": "/{{$firma->id}}/servis-asama-sorulari",
        "nav9": "/{{$firma->id}}/servis-zamanlama",
        "nav10": "/{{$firma->id}}/servis-kaynaklari",
        "nav11": "",
        "nav12": "",
        "nav13": "/{{$firma->id}}/silinen-servisler",
        "nav14": "/{{$firma->id}}/izinler",
        "nav15": "/{{$firma->id}}/roller",
        "nav16": "/{{$firma->id}}/stok-kategorileri",
        "nav17": "/{{$firma->id}}/stok-raflari",
        "nav18": "/{{$firma->id}}/stok-tedarikcileri",
        "nav19": "/{{$firma->id}}/odeme-turleri",
        "nav20": "/{{$firma->id}}/odeme-sekilleri",
        "nav22": "/{{$firma->id}}/servis-form/ayarlari",
        "nav23": "/{{$firma->id}}/yazici-fis/tasarimi",
        "nav24": "/{{$firma->id}}/prim-ayarlari",
        "nav25": "/{{$firma->id}}/log-kayitlari",
        "nav26": "/{{$firma->id}}/yasal-metinler",
        "nav27": "/{{$firma->id}}/api-tokens",

      };
  
      var id = $(this).attr("class").split(' ')[1];
      var url = tabMap[id];
      var tabId = "#" + id.replace("nav", "tab");
  
      loadData(url, tabId);
    });
  
    // Dropdown-item'lar için click olaylarını ayarladık
    $('.kasaSubMenu .dropdown-item').on('click', function (e) {
      e.preventDefault();
      e.stopPropagation();

      // ÖNEMLİ: Tüm dropdown item'lardan active class'ını kaldır
      $('.kasaSubMenu .dropdown-item').removeClass('active');
      $(this).addClass('active');
  
      var tabMap = {
        "nav2": "/{{$firma->id}}/sms-ayarlari",
        "nav3": "/{{$firma->id}}/cihaz-markalari",
        "nav4": "/{{$firma->id}}/cihaz-turleri",
        "nav5": "/{{$firma->id}}/garanti-sureleri",
        "nav6": "/{{$firma->id}}/araclar",
        "nav7": "/{{$firma->id}}/servis-asamalari",
        "nav8": "/{{$firma->id}}/servis-asama-sorulari",
        "nav9": "/{{$firma->id}}/servis-zamanlama",
        "nav10": "/{{$firma->id}}/servis-kaynaklari",
        "nav11": "",
        "nav12": "",
        "nav13": "/{{$firma->id}}/silinen-servisler",
        "nav14": "/{{$firma->id}}/izinler",
        "nav15": "/{{$firma->id}}/roller",
        "nav16": "/{{$firma->id}}/stok-kategorileri",
        "nav17": "/{{$firma->id}}/stok-raflari",
        "nav18": "/{{$firma->id}}/stok-tedarikcileri",
        "nav19": "/{{$firma->id}}/odeme-turleri",
        "nav20": "/{{$firma->id}}/odeme-sekilleri",
        "nav22": "/{{$firma->id}}/servis-form/ayarlari",
        "nav23": "/{{$firma->id}}/yazici-fis/tasarimi",
        "nav24": "/{{$firma->id}}/prim-ayarlari",
        "nav25": "/{{$firma->id}}/log-kayitlari",
        "nav26": "/{{$firma->id}}/yasal-metinler",
        "nav27": "/{{$firma->id}}/api-tokens",
      };
  
      var id = $(this).attr("class").split(' ')[1]; // dropdown-item'in ikinci class'ını alır
      if (id === "nav1") return;
      var url = tabMap[id];
      var tabId = "#" + id.replace("nav", "tab"); // tab id'yi oluşturur
  
      loadData(url, tabId);
  
      
    });
  
    // Dropdown'un kapanmasını engelle
    $('.kasaSubMenu .dropdown-menu').on('click', function (e) {
      e.stopPropagation(); // Olayın yayılmasını durdurur
    });
  });
</script>
