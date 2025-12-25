<div class="table-responsive" style="margin: 0">
  @if(!is_null($customer_offers) && count($customer_offers) > 0)
  <table class="table table-hover table-striped" width="100%" cellspacing="0" style="margin: 0">
    <thead class="title">
      <tr>
        <th style="padding: 5px 10px;font-size: 12px;width: 70px">Tarih</th>
        <th style="padding: 5px 10px;font-size: 12px;">Genel Toplam</th>
        <th style="padding: 5px 10px;font-size: 12px;">Durum</th>
        <th style="padding: 5px 10px;font-size: 12px;width: 50px"></th>
      </tr>
    </thead>
    <tbody>
      @foreach($customer_offers as $offer)  
        @php 
          $sontarih = \Carbon\Carbon::parse($offer->created_at)->format('d/m/Y');
        @endphp  	 
      <tr>
        <td style="vertical-align: middle;font-size: 11px; padding:  10px;">{{$sontarih}}</td>
        <td style="vertical-align: middle;font-size: 11px; padding:  10px;"><strong>{{$offer->genelToplam}}</strong></td>
        @if($offer->durum == "0")
          <td style="vertical-align: middle;font-size: 11px; padding:  10px;"><strong><div style="color: #0089ff;display:inline-block">Beklemede</div></strong></td>
        @elseif($offer->durum == "1")
          <td style="vertical-align: middle;font-size: 11px; padding:  10px;"><strong><div style="color: green;display:inline-block">Onaylandı</div></strong></td>
        @elseif($offer->durum == "2")
          <td style="vertical-align: middle;font-size: 11px; padding:  10px;"><strong><div style="color: red;display:inline-block">Onaylanmadı</div></strong></td>
        @elseif($offer->durum == "3")
          <td style="vertical-align: middle;font-size: 11px; padding:  10px;"><strong><div style="color: #ff890f;display:inline-block">Cevap Gelmedi</div></strong></td>
        @endif
        <td style="vertical-align: middle;font-size: 11px; padding:  10px;"><strong><a href="{{ route('offers', [$firma->id, 'did' => $offer->id]) }}" class="btn btn-danger btn-sm editDomain" style="font-size:11px" target="_blank">Detaylar</a></strong></td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @else
     <div class="text-center text-muted" style="padding: 20px;">
      <i class="fas fa-file-invoice-dollar fa-2x mb-2" style="color: #ddd;"></i>
      <p style="font-size: 13px; color: #6c757d; margin: 0;">Henüz teklif eklenmemiş</p>
    </div>
  @endif
</div>
