@extends('frontend.secure.user_master')
@section('user')
<div class="page-content" id="arizaKodlariPage">
    <div class="container-fluid">
        <div class="row pageDetail">
            <div class="col-12 arizalar">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">{{ $titleSec }} - Arıza Kodları</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <!-- Sol Taraf: Butonlar -->
                            <div class="col-auto">
                                <button type="button" class="btn btn-success btn-sm kodEkleBtn px-3">
                                   <span> Kod Ekle</span>
                                </button>
                            </div>
                            
                            <!-- Sağ Taraf: Arama Kutusu Alanı -->
                            <div class="col-auto">
                                <div id="kodSearchPlaceholder"></div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="kodTable" class="table table-hover table-striped" style="width:100%">
                                <thead class="title">
                                    <tr>
                                        <th width="20%">Kod</th>
                                        <th width="40%">Başlık</th>
                                        <th width="40%">İşlemler</th>
                                    </tr>
                                </thead>
                               <tbody>
                                    @foreach($kodlar as $kod)
                                    <tr data-id="{{ $kod->id }}">
                                        <td class="align-middle">
                                            <strong class="kod-link" 
                                                    data-id="{{ $kod->id }}"
                                                    style="cursor: pointer; color: #007bff;"
                                                    title="Düzenlemek için tıklayın">
                                                {{ $kod->kodu }}
                                            </strong>
                                        </td>
                                        <td class="align-middle">
                                            <span class="baslik-link" 
                                                data-id="{{ $kod->id }}"
                                                style="cursor: pointer; color: #007bff;"
                                                title="Düzenlemek için tıklayın">
                                                {{ $kod->baslik }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-warning btn-sm kodDuzenleBtn" 
                                                        data-id="{{ $kod->id }}"
                                                        title="Düzenle">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm kodSil" 
                                                        data-id="{{ $kod->id }}"
                                                        title="Sil">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Arıza Kodu Ekle Modal --}}
<div class="modal fade" id="kodEkleModal" tabindex="-1" role="dialog" aria-labelledby="kodEkleTitle" aria-hidden="true">
    <div class="modal-dialog " role="document">
        <div class="modal-content">
            <div class="modal-header ">
                <h5 class="modal-title" id="kodEkleTitle">
                    Yeni Arıza Kodu Ekle
                </h5>
                <button type="button" class="close " data-dismiss="modal" aria-label="Kapat">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Yükleniyor...
            </div>
        </div>
    </div>
</div>

{{-- Arıza Kodu Düzenle Modal --}}
<div class="modal fade" id="kodDuzenleModal" tabindex="-1" role="dialog" aria-labelledby="kodDuzenleTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header ">
                <h5 class="modal-title" id="kodDuzenleTitle">
                    Arıza Kodu Düzenle
                </h5>
                <button type="button" class="close " data-dismiss="modal" aria-label="Kapat">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Yükleniyor...
            </div>
        </div>
    </div>
</div>



<script>
$(document).ready(function(){
    let kodTableInstance;

// DataTable başlat - Sıralama KAPALI
kodTableInstance = $('#kodTable').DataTable({
    "language": {
        "sSearch": "Arıza Kodu Ara:",
        "sEmptyTable": "Tabloda veri yok",
        "sZeroRecords": "Eşleşen kayıt bulunamadı"
    },
    "ordering": false,  // SIRALAMA TAMAMEN KAPALI
    "paging": false,
    "info": false,
    "responsive": true,
    "dom": 'frti'
});
    
    // Arama kutusunu sağ tarafa taşı
    $('#kodTable_filter').appendTo('#kodSearchPlaceholder');

    /** Kod Ekle Modal Aç **/
    $(document).on('click', '.kodEkleBtn', function(){
        $.ajax({
            url: "{{ route('super.admin.kodlar.create') }}?marka_id={{ $marka_id }}&model_id={{ $model_id }}"
        }).done(function(data) {
            $('#kodEkleModal .modal-body').html(data);
            $('#kodEkleModal').modal('show');
        });
    });

    /** Kod Düzenle Modal Aç **/
    $(document).on('click', '.kodDuzenleBtn', function(){
        let id = $(this).data('id');
        $.ajax({
            url: "{{ route('super.admin.kodlar.edit', '') }}/" + id
        }).done(function(data) {
            $('#kodDuzenleModal .modal-body').html(data);
            $('#kodDuzenleModal').modal('show');
        });
    });

    /** Kod Sil **/
    $(document).on('click', '.kodSil', function(){
        if(!confirm('Bu arıza kodunu silmek istediğinize emin misiniz?')) return;
        
        let id = $(this).data('id');
        let row = $(this).closest('tr');

        $.ajax({
            url: "{{ route('super.admin.kodlar.destroy', '') }}/" + id,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(res){
                kodTableInstance.row(row).remove().draw();
                alert(res.message);
            },
            error: function(xhr){
                let errorMsg = xhr.responseJSON?.message || "Silme işlemi başarısız!";
                alert("Hata: " + errorMsg);
            }
        });
    });

    // Modal kapatma olayları
    $(document).on('click', '[data-dismiss="modal"]', function(){
        $(this).closest('.modal').modal('hide');
    });

    $(document).on('click', '.modal', function(e){
        if($(e.target).hasClass('modal')) {
            $(this).modal('hide');
        }
    });

    $(document).on('keydown', function(e){
        if(e.key === 'Escape') {
            $('.modal').modal('hide');
        }
    });

    // Modal kapandığında içeriği temizle
    $('#kodEkleModal, #kodDuzenleModal').on('hidden.bs.modal', function () {
        $(this).find('.modal-body').html('Yükleniyor...');
    });
    /** Kod veya Başlığa Tıklayınca Düzenle Modal Aç **/
    $(document).on('click', '.kod-link, .baslik-link', function(){
        let id = $(this).data('id');
        $.ajax({
            url: "{{ route('super.admin.kodlar.edit', '') }}/" + id
        }).done(function(data) {
            $('#kodDuzenleModal .modal-body').html(data);
            $('#kodDuzenleModal').modal('show');
        });
    });
});
</script>
<script>
$(document).ready(function() {
    // Benzersiz token oluştur
    function generateKodToken() {
        return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    // Modal açıldığında token'ı hazırla
    $('#kodEkleModal').on('shown.bs.modal', function () {
        setTimeout(function() {
            if ($('#kodEkleFormToken').length) {
                $('#kodEkleFormToken').val(generateKodToken());
            }
        }, 100);
    });
    
    // Modal kapandığında token'ı temizle
    $('#kodEkleModal').on('hidden.bs.modal', function () {
        if ($('#kodEkleFormToken').length) {
            $('#kodEkleFormToken').val('');
        }
    });
});
</script>
@endsection