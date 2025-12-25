<div class="table-responsive" style="margin: 0">
  @if(!is_null($customer_services) && count($customer_services) > 0)
  <table class="table table-hover table-striped" width="100%" cellspacing="0" style="margin: 0">
    <thead class="title">
      <tr>
        <th style="padding: 5px 10px;font-size: 12px;width: 70px">Id</th>
        <th style="padding: 5px 10px;font-size: 12px;">Tarih</th>
        <th style="padding: 5px 10px;font-size: 12px;">Cihaz</th>
        <th style="padding: 5px 10px;font-size: 12px;"></th>
      </tr>
    </thead>
    <tbody>
      @foreach($customer_services as $service)
      <tr style="border-bottom: 1px solid #ddd;">
        <td style="vertical-align: middle;font-size: 11px; padding: 3px;border: 1px solid #ddd;text-align:center;">{{$service->id}}</td>
        <td style="vertical-align: middle;font-size: 11px; padding: 3px;border: 1px solid #ddd;text-align:center;"><strong>{{$service->created_at}}</strong></td>
        <td style="vertical-align:middle;font-size: 11px;padding:  3px;border: 1px solid #ddd;text-align:center;"><strong>{{$service->markaCihaz?->marka}} - {{$service->turCihaz?->cihaz}}</strong></td>
        <td style="vertical-align: middle;font-size: 11px; padding: 3px 0px;border: 1px solid #ddd;text-align:center;"><strong><a href="{{ route('all.services', [$firma->id, 'did' => $service->id]) }}" class="btn btn-customer-custom btn-sm editDomain"  style="font-size:12px;font-weight: bolder;" target="_blank">Servisi Aç</a></strong></td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @else
    <div style="color: black;text-align:center;">Müşterinin servisi bulunmamaktadır</div>
  @endif
</div>

  