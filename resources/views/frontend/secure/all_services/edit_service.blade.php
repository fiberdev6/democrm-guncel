<ul class="nav nav-pills nav-justified" role="tablist" style="margin-bottom: 5px">
  <li class="nav-item" style="font-size: 14px;"><a class="nav-link nav1 active" data-bs-toggle="pill" href="#tab1" data-id="" role="tab"><i class="fas fa-user"></i><span>Servis Bilgileri</span></a></li>
  <li class="nav-item" style="font-size: 14px;"><a class="nav-link nav4" data-bs-toggle="pill" href="#tab4" data-id="{{ $service_id->id }}" role="tab"><i class="fas fa-lira-sign"></i><span>Para Hareketleri</span></a></li>
  <li class="nav-item" style="font-size: 14px;"><a class="nav-link nav5" data-bs-toggle="pill" href="#tab5" data-id="{{ $service_id->id }}" role="tab"><i class="fas fa-text-width"></i><span>Fotoğraflar</span></a></li>
  <li class="nav-item" style="font-size: 14px;"><a class="nav-link nav7" data-bs-toggle="pill" href="#tab7" data-id="{{ $service_id->id }}" role="tab"><i class="fab fa-buysellads"></i><span>Fiş Notu</span></a></li>
  @hasanyrole('Admin|Patron|Operatör|Müdür')
  <li class="nav-item" style="font-size: 14px;"><a class="nav-link nav8" data-bs-toggle="pill" href="#tab8" data-id="{{ $service_id->id }}" role="tab"><i class="fas fa-file-alt"></i><span>Opt. Notu</span></a></li>
  @endhasanyrole
  @if(auth()->user()->can('Teklifleri Görür'))
  <li class="nav-item" style="font-size: 14px;"><a class="nav-link nav3" data-bs-toggle="pill" href="#tab3" data-id="{{ $service_id->id }}" role="tab"><i class="fas fa-receipt"></i><span>Teklifler</span></a></li>
  @endif
  @if(auth()->user()->can('Faturaları Görebilir'))
  <li class="nav-item" style="font-size: 14px;"><a class="nav-link nav2" data-bs-toggle="pill" href="#tab2" data-id="{{ $service_id->id }}" role="tab"><i class="fas fa-coins"></i><span>Faturalar</span></a></li>
  @endif
</ul>

<div class="tab-content">
  <div id="tab1" class="tab-pane active" style="padding: 0" role="tabpanel"></div>
  <div id="tab2" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
  <div id="tab3" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
  <div id="tab4" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
  <div id="tab5" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
  <div id="tab7" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
  <div id="tab8" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
  <div id="tab6" class="tab-pane fade in" style="padding: 0" role="tabpanel"></div>
</div>

<input type="hidden" name="id" value="{{ $service_id->id }}">

@php
    $firma_id = $firma->id;
    $service_id = $service_id->id;

    if(auth()->user()->can('Tüm Servisleri Görebilir')) {
        $servisUrl = "/{$firma_id}/servis-bilgileri/tum/{$service_id}";
    } else {
        $servisUrl = "/{$firma_id}/servis-bilgileri/kendi/{$service_id}";
    }
@endphp

<script type="text/javascript">
  $(document).ready(function (e) {
    var firma_id = {{$firma->id}};
    var servisUrl = "{{ $servisUrl }}";
    $.ajax({
      url: servisUrl
    }).done(function(data) {
      if($.trim(data)==="-1"){
        window.location.reload(true);
      }else{
        $('#tab1').html(data); // display data
      }
    });
  });
</script>
<script type="text/javascript">
  $(".nav1").click(function(){
    var id = $(this).attr("data-id");
    var firma_id = {{$firma->id}};
    var servisUrl = "{{ $servisUrl }}";
    if(id){
      $.ajax({
        url: servisUrl
      }).done(function(data) {
        if($.trim(data)==="-1"){
          window.location.reload(true);
        }else{
          $('#tab1').html(data); // display data
        }
      });
    }
  });

  $(".nav2").click(function(){
    var id = $(this).attr("data-id");
    var firma_id = {{$firma->id}};
    if(id){
      $.ajax({
        url: "/" + firma_id + "/musteri-faturalari/" + id
      }).done(function(data) {
        if($.trim(data)==="-1"){
          window.location.reload(true);
        }else{
          $('#tab2').html(data); // display data
        }
      });
    }
  });

  $(".nav3").click(function(){
    var id = $(this).attr("data-id");
    var firma_id = {{$firma->id}};
    if(id){
      $.ajax({
        url: "/" + firma_id + "/musteri-teklifleri/" + id
      }).done(function(data) {
        if($.trim(data)==="-1"){
          window.location.reload(true);
        }else{
          $('#tab3').html(data); // display data
        }
      });
    }
  });

  $(".nav4").click(function(){
    var id = $(this).attr("data-id");
    var firma_id = {{$firma->id}};
    if(id){
      $.ajax({
        url: "/" + firma_id + "/servis-para-hareketleri/" + id
      }).done(function(data) {
        if($.trim(data)==="-1"){
          window.location.reload(true);
        }else{
          $('#tab4').html(data);            
        }
      });  
    }
  });

  $(".nav5").click(function(){
    var id = $(this).attr("data-id");
    var firma_id = {{$firma->id}};
    if(id){
      $.ajax({
        url: "/" + firma_id + "/servis-fotolari/" + id
      }).done(function(data) {
        if($.trim(data)==="-1"){
          window.location.reload(true);
        }else{
          $('#tab5').html(data);            
        }
      }); 
    }
  });

  $(".nav7").click(function(){
    var id = $(this).attr("data-id");
    var firma_id = {{$firma->id}};
    if(id){
      $.ajax({
        url: "/" + firma_id + "/servis-fis-notlari/" + id
      }).done(function(data) {
        if($.trim(data)==="-1"){
          window.location.reload(true);
        }else{
          $('#tab7').html(data);            
        }
      });    
    }
  });

  $(".nav8").click(function(){
    var id = $(this).attr("data-id");
    var firma_id = {{$firma->id}};
    if(id){
      $.ajax({
        url: "/" + firma_id + "/servis-operator-notlari/" + id
      }).done(function(data) {
        if($.trim(data)==="-1"){
          window.location.reload(true);
        }else{
          $('#tab8').html(data);            
        }
      });
    }
  });

  $(".nav6").click(function(){
    var id = $(this).attr("data-id");
    var firma_id = {{$firma->id}};
    if(id){
      $.ajax({
        url: "/" + firma_id + ""
      }).done(function(data) {
        if($.trim(data)==="-1"){
          window.location.reload(true);
        }else{
          $('#tab6').html(data);            
        }
      });
    }
  });
</script>