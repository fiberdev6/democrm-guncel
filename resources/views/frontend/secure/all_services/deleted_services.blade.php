
<style>
div.dataTables_wrapper div.dataTables_filter {
    display: none;
}
</style>
    <div id="deletedServices">
    <div class="d-none d-lg-block">
        <p>Bu sayfada, sistemden son 7 gün içerisinde silinen servisler görüntülenir. Sayfayı yenileyip kontrol ediniz. Silmekten vazgeçtiyseniz, servisi geri alabilirsiniz. </p>
        <table id="datatableDeletedService" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
            <thead class="title">
                <tr>
                    <th style="width: 10px">ID</th>
                    <th data-priority="2">Tarih</th>
                    <th>Silen Kişi</th>
                    <th>Müşteri Adı</th>
                    <th>Cihaz</th>
                    <th>Servis Durumu</th>
                    <th data-priority="1" style="width: 96px;">Geri Al</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deleted_services as $item)
                <tr data-id="{{$item->id}}">
                    <td class="gizli">{{$item->id}}</td>
                    <td><div class="mobileTitle">Tarih:</div>{{ Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i')}}</td>
                    <td><div class="mobileTitle">Silen Kişi:</div>{{$item->staffwhodeleted->name ?? ''}}</td>
                    <td><div class="mobileTitle">Müşteri:</div>{{$item->musteri->adSoyad ?? ''}}</td>
                    <td><div class="mobileTitle">Cihaz:</div>{{$item->markaCihaz->marka ?? ''}}, {{$item->turCihaz->cihaz?? ''}}</td>
                    <td><div class="mobileTitle">S. Durumu:</div>{{$item->asamalar->asama ?? ''}}</td>
                    <td class="tabloBtn">
                        <button class="btn btn-outline-danger btn-sm mobilBtn restoreService" title="Geri Al" data-id="{{ $item->id }}"> <i class="fas fa-undo"></i> </button></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>


    <div class="d-lg-none">
        <p>Bu sayfada, sistemden son 7 gün içerisinde silinen servisler görüntülenir. Sayfayı yenileyip kontrol ediniz. Silmekten vazgeçtiyseniz, servisi geri alabilirsiniz. </p>
        
        @foreach($deleted_services as $item)
            <div class="card shadow-sm mb-3" data-id="{{$item->id}}">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-muted">Müşteri</span>
                            <h6 class="mb-0 fw-bold">{{$item->musteri->adSoyad ?? ''}}</h6>
                        </div>
                        <span class="badge bg-light text-primary border">ID: {{ $item->id }}</span>
                    </div>

                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Tarih:</span>
                            <span class="fw-bold">{{ Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i')}}</span>
                        </li>
                         <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Cihaz:</span>
                            <span class="fw-bold">{{$item->markaCihaz->marka ?? ''}}, {{$item->turCihaz->cihaz?? ''}}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Servis Durumu:</span>
                            <span class="fw-bold">{{$item->asamalar->asama ?? ''}}</span>
                        </li>
                         <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Silen Kişi:</span>
                            <span class="fw-bold">{{$item->staffwhodeleted->name ?? ''}}</span>
                        </li>
                    </ul>
                </div>
                
                <div class="card-footer bg-white d-flex justify-content-end gap-2 p-2">
                    <button class="btn btn-outline-danger btn-sm mobilBtn restoreService" title="Geri Al" data-id="{{ $item->id }}"> 
                        <i class="fas fa-undo me-1"></i> Geri Al 
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
$(document).on('click', '.restoreService', function () {
    const btn = $(this);
    const servisId = btn.data('id');

    if (!confirm("Bu servisi geri almak istediğinize emin misiniz?")) {
        return;
    }

    $.ajax({
        url: `/{{ $firma->id }}/servis-geri-al/${servisId}`,
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function (response) {
            if (response.success) {
                // Tablo satırını kaldır
                let table = $('#datatableDeletedService').DataTable();
                table.row(btn.closest('tr')).remove().draw();

                // (İsteğe bağlı) Toast veya alert mesajı
                alert(response.message);
            }
        },
        error: function (xhr) {
            alert('Bir hata oluştu: ' + xhr.responseText);
        }
    });
});
</script>  
  