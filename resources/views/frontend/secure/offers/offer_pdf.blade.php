<!DOCTYPE html>
<html lang="tr">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-language" content="tr">
      <title>Teklif Yazdır</title>
      <link href="https://fonts.googleapis.com/css2?family=DejaVu+Sans&display=swap" rel="stylesheet">      <style>
        body{font-family: 'DejaVu Sans',sans-serif;background-color:  #ccc;margin: 0;padding: 0;font-size: 13px;}
        img{max-width: 70%}
        ul{list-style: none;margin: 0;padding: 0;}
        .wrap {padding: 0}
        .clearfix{clear: both;}
        .header {font-size: 12px;}
        .header .col {float: left;}
        .header .col ul li {display: block;}
        .header .col.c1 {width: 30%;}
        .header .col.c2 {width: 35%;}
        .header .col.c3 {width: 35%;}
        .header .col.c1 .logo img { margin-top: 17px;}
        .header .col.c2 h4 {margin: 0;font-weight: 400;font-size: 13px;}
        .header .col.c2 ul {margin-top: 15px;}
        .header .col.c2 ul li span {display: inline-block;width: 60px;}
        .header .col.c3 p {font-weight: 400}
        .header .col.c3 ul li {margin-bottom: 6px;}
        .header .col.c3 ul li span {font-weight: 400;display: block;width: 100%;color: #ef6823;}
        .title {margin-top: 0;font-size: 12px;}
        .title .col {position: relative;top:-20px;}
        .title .col.c1 {width: 85%;padding-top: 240px;}
        .title .col.c2 .thumb{padding-left: 6px;}
        .title .col.c1 ul li {margin-bottom: 1px;}
        .title .col.c1 ul li span {display: inline-block;width: 85px;color: #ef6823;font-weight: 400;}
        .title .col.c2 ul li {margin-bottom: 1px;text-align: right;}
        .title .col.c2 ul li span {width: 63px;color: #ef6823;font-weight: 400;}
        .title .col.c2 .desc{text-align: right;margin-top: 0;}
        .title .col.c2 .desc h4{margin: 0;}
        .title .col.c2 .desc h2{margin: 0;}
        .table{margin-top: 10px;}
        .table table{margin: 0;padding: 0;width: 100%;border-collapse:collapse;border-spacing:0;text-align: left;border: 1px solid #9c9c9c;}
        .table table thead{background: #3c4e65;color: #fff;font-size: 13px}
        .table table thead th{padding: 3px;font-size: 12px;}
        .table table tbody td{padding: 3px;font-size: 13px;border: 1px solid #9c9c9c;}
        .table table tbody td h4{margin: 0;}
        .descriptions{margin-top: 15px;}
        .descriptions h4{margin: 0}
        .descriptions p{margin: 0;line-height: 20px;font-size: 13px;font-weight: 400;margin-bottom: 10px;}
        .descriptions li{font-size: 12px;}
      </style>
    </head>
  <body>
    
    <div class="a4TeklifPage" style="height: 27.3cm;background: white; margin: 0 auto; padding: 0; word-break: break-word;box-sizing: border-box; position: relative;">
      <div class="wrap">
        <div class="header" >
          <div class="col c1" >
            <div class="logo"><img src="{{$firma->logo}}" style="margin-top: 0;padding-top:0;"></div>
          </div>
          <div class="col c2">
            <div class="thumb">
              <h4>{{$firma->firma_adi}}</h4>
              <ul>
                <li><span>Telefon</span>: 0{{$firma->tel1}}</li>
                <li><span>GSM</span>: {{$firma->tel2}}</li>
                <li><span>Email</span>: {{$firma->eposta}}</li>
              </ul>
            </div>
          </div>
          <div class="col c3">
            <div class="thumb">
              <ul>
                <li><span>{{$firma->vergiDairesi}}</span>Vergi No: {{$firma->vergiNo}}</li>
                <li><span>IBAN</span>{{$firma->iban}}</li>
                <li><span>Adres</span>{!!$firma->adres!!} - {!!$firma->ilces->ilceName!!}/{!!$firma->ils->name!!}</li>
              </ul>
              <strong>{{$firma->webSitesi}}</strong>
            </div>
          </div>
        </div>
        <div class="title">
          <div class="col c1" >
            <div class="thumb" style="left:0;">      
              <ul>
                <li><span>Sayın</span>: {{$customer->adSoyad}}</li>
                <li><span>Telefon 1</span>: {!! $customer->tel1 !!}</li>
                <li><span>Telefon 2</span>: {{$customer->tel2}}</li>
                <li><span>Adres</span>: {!! $customer->adres !!}</li>
                <li><span>TC</span>: {{$customer->tcNo}}</li>
              </ul>
            </div>
          </div>
          <div class="col c2">
            <div class="thumb" style="position:relative;right:0;float:right;top:-125px">
              @php 
                $sontarih = \Carbon\Carbon::parse($offers->created_at)->format('d/m/Y');
              @endphp
              <ul>
                <li><span>Teklif No :</span>{{$offers->id}}<div class="clearfix"></div></li>
                <li><span>Tarih :</span>  {{$sontarih}}<div class="clearfix"></div></li>
              </ul>
              <div class="desc">
                <h4>{{$offers->baslik1}}</h4>
                <h2>{{$offers->baslik2}}</h2>
              </div>
            </div>
          </div>
        </div>
          <div class="table">
            <table>
              <thead>
                <tr>
                  <th>AÇIKLAMA</th>
                  <th style="width: 110px;text-align: right;">MİKTAR</th>
                  <th style="width: 110px;text-align: right;">FİYAT</th>
                  <th style="width: 110px;text-align: right;">TUTAR</th>
                </tr>
              </thead>
              <tbody>
                @foreach($offer_products as $product)
                  <tr>
                    <td>{{$product->urun}}</td>
                    <td style="text-align: right;">{{number_format($product["miktar"],0,",",".")}} Adet</td>
                    <td style="text-align: right;">{{number_format($product["fiyat"],2,",",".")}} TL</td>
                    <td style="text-align: right;">{{number_format($product["tutar"],2,",",".")}} TL</td>
                  </tr>
                @endforeach       
                <tr>
                  <td rowspan="5"><h4></h4></td>
                  <td colspan="2" style="text-align: right;font-weight: 500">TOPLAM</td>
                  <td style="text-align: right;font-weight: 500">{{number_format($offers["toplam"],2,",",".")}} TL</td>
                </tr>
                <tr>
                  <td colspan="2" style="text-align: right;font-weight: 500">KDV %{{$offers->kdv}}</td>
                  <td style="text-align: right;font-weight: 500">{{number_format($offers["kdvTutar"],2,",",".")}} TL</td>
                </tr>
                <tr>
                  <td colspan="2" style="text-align: right;font-weight: 500">GENEL TOPLAM</td>
                  <td style="text-align: right;font-weight: 500">{{number_format($offers["genelToplam"],2,",",".")}} TL</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="descriptions" style="font-weight: 500">
            <p>Teklif Geçerlilik Süresi 15 Gündür.  Güncel Döviz Kuru: {{$offers->dovizKuru}} TL </p>
            <li>{!! str_replace(array("\r","\n\n","\n"),array('',"\n","</li>\n<li>"),trim($offers["aciklamalar"],"\n\r")) !!}</li>
          </div>
        </div>
      </div>
  </body>
</html>

