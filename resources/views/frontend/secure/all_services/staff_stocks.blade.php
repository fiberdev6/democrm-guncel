<div class="card" style="margin-bottom: 0!important;">
  <div class="card-body" style="padding: 0!important;">
    <div class="table-responsive" style="margin: 0!important;">
      <table class="table table-hover " width="100%" cellspacing="0" style="margin: 0!important;">
        <thead class="title">
          <tr>
            <th style="padding: 5px 10px;font-size: 12px;">ID</th>
            <th style="padding: 5px 10px;font-size: 12px;">Ürün Adı</th>
            <th style="padding: 5px 10px;font-size: 12px;">Fiyat</th>
            <th style="padding: 5px 10px;font-size: 12px;">Adet</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($staff_stocks as $stock)
            @if($stock->adet > 0)
              <tr>
                <td style="vertical-align: middle;font-size: 11px; padding: 0 10px;"><strong>{{$stock->stok->id}}</strong></td>
                <td style="vertical-align: middle;font-size: 11px; padding: 0 10px;"><strong>{{$stock->stok->urunAdi}}</strong></td>
                <td style="vertical-align: middle;font-size: 11px; padding: 0 10px;"><strong>{{$stock->stok->fiyat}} TL</strong></td>
                <td style="vertical-align: middle;font-size: 11px; padding: 0 10px;"><strong>{{$stock->adet}}</strong></td>
              </tr>
            @endif
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>