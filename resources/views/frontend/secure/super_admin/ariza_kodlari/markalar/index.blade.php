@extends('frontend.secure.user_master')
@section('user')
<div class="page-content" id="markalarPage">
    <div class="container-fluid">
        <div class="row pageDetail">
            <div class="col-12 arizaMarka">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Markalar</h4>
                    </div>
                        <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <!-- Sol Taraf: Marka Ekle Butonu -->
                                    <div class="col-auto">
                                        <button type="button" class="btn btn-success btn-sm markaEkleBtn px-3">
                                            <i class="fa fa-plus"></i> <span>Marka Ekle</span>
                                        </button>
                                    </div>
                                    
                                    <!-- Sağ Taraf: Arama Kutusu Alanı -->
                                    <div class="col-auto">
                                        <div id="searchPlaceholder"></div>
                                    </div>
                                </div>
                            <div class="table-responsive">
                                <table id="markaTable" class="table table-hover table-striped" style="width:100%">
                                    <thead class="title">
                                        <tr>
                                            <th width="60%">Marka</th>
                                            <th width="40%">İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($markalar as $marka)
                                        <tr data-id="{{ $marka->id }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($marka->resimyol)
                                                        <img src="{{ asset('upload/ariza_kodlari/'.$marka->resimyol) }}" 
                                                            width="50" 
                                                            height="50" 
            
                                                            class="mr-2 border rounded"
                                                            loading="lazy"
                                                            alt="{{ $marka->marka }}">
                                                    @else
                                                        <div class="mr-2 border rounded bg-light d-flex align-items-center justify-content-center" 
                                                            style="width: 50px; height: 50px; min-width: 50px;">
                                                            <i class="fa fa-image text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <strong class="marka-adi-link" 
                                                            data-id="{{ $marka->id }}"
                                                            data-marka="{{ $marka->marka }}"
                                                            data-resim="{{ $marka->resimyol }}"
                                                            style="cursor: pointer; color: #007bff;"
                                                            title="Düzenlemek için tıklayın">
                                                        {{ $marka->marka }}
                                                    </strong>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('super.admin.modeller.index', $marka->id) }}" 
                                                    class="btn btn-info" title="Modeller">
                                                        <i class="fa fa-list"></i> Modeller
                                                    </a>
                                                    <a href="{{ route('super.admin.kodlar.index', ['marka_id'=>$marka->id,'model_id'=>0]) }}" 
                                                    class="btn btn-warning" title="Arıza Kodları">
                                                        <i class="fa fa-wrench"></i> Kodlar
                                                    </a>
                                                    <button class="btn btn-primary markaDuzenleBtn" 
                                                            data-id="{{ $marka->id }}"
                                                            data-marka="{{ $marka->marka }}"
                                                            data-resim="{{ $marka->resimyol }}"
                                                            title="Düzenle">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-danger markaSil" 
                                                            data-id="{{ $marka->id }}"
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

{{-- Marka Ekle Modal --}}
<div class="modal fade" id="markaEkleModal" tabindex="-1" role="dialog" aria-labelledby="markaEkleTitle" aria-hidden="true">
    <div class="modal-dialog " role="document">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title" id="markaEkleTitle">
                     Yeni Marka Ekle
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Kapat">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="markaEkleForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="form_token" id="markaEkleFormToken" value="">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="marka_adi">Marka Adı <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="marka" 
                               id="marka_adi"
                               class="form-control" 
                               placeholder="Örn: Bosch, Siemens, Arçelik..." 
                               required>
                    </div>
                    <div class="form-group">
                        <label for="marka_resim">Marka Logosu</label>
                        <div class="custom-file">
                            <input type="file" 
                                   name="resim" 
                                   id="marka_resim"
                                   class="custom-file-input" 
                                   accept="image/jpeg,image/jpg,image/png,image/svg+xml">
                            <label class="custom-file-label" for="marka_resim">Dosya Seçin</label>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fa fa-info-circle"></i> Sadece JPG, PNG, SVG (Max: 2MB)
                        </small>
                        <div id="resimOnizleme" class="mt-2" style="display:none;">
                            <img src="" alt="Önizleme" class="img-thumbnail" style="max-width: 150px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-end">
                    <button type="submit" class="btn btn-info btn-sm" id="markaEkleSubmit">
                         Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Marka Düzenle Modal --}}
<div class="modal fade" id="markaDuzenleModal" tabindex="-1" role="dialog" aria-labelledby="markaDuzenleTitle" aria-hidden="true">
    <div class="modal-dialog " role="document">
        <div class="modal-content">
            <div class="modal-header  text-white">
                <h5 class="modal-title" id="markaDuzenleTitle">
                     Marka Düzenle
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Kapat">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="markaDuzenleForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="marka_id" id="duzenle_marka_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="duzenle_marka_adi">Marka Adı <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="marka" 
                               id="duzenle_marka_adi"
                               class="form-control" 
                               required>
                    </div>
                    <div class="form-group">
                        <label for="duzenle_marka_resim">Marka Logosu</label>
                        <div class="custom-file">
                            <input type="file" 
                                   name="resim" 
                                   id="duzenle_marka_resim"
                                   class="custom-file-input" 
                                   accept="image/jpeg,image/jpg,image/png,image/svg+xml">
                            <label class="custom-file-label" for="duzenle_marka_resim">Dosya Seçin</label>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fa fa-info-circle"></i> Değiştirmek için yeni dosya seçin
                        </small>
                        <div id="mevcutResim" class="mt-2">
                            <img src="" alt="Mevcut Logo" class="img-thumbnail" style="max-width: 150px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-end">
                    <button type="submit" class="btn btn-primary btn-sm" id="markaDuzenleSubmit">
                         Güncelle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
$(document).ready(function(){
    let table; // DataTable referansı
    // DataTable başlat - Sayfalama KAPALI, sadece arama
table = $('#markaTable').DataTable({
    "language": {
        "sSearch": "Marka Ara:",
        "sEmptyTable": "Tabloda veri yok",
        "sZeroRecords": "Eşleşen kayıt bulunamadı"
    },
    "order": [[0, "asc"]],
    "paging": false,
    "info": false,
    "responsive": true,
    "dom": 'frti',
    "columnDefs": [
        { "orderable": false, "targets": 1 }
    ]
});
    // Arama kutusunu sağ tarafa taşı
    $('#markaTable_filter').appendTo('#searchPlaceholder');
    // Custom file input label güncelleme
    $(document).on('change', '.custom-file-input', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass("selected").html(fileName);
        
        if($(this).attr('id') === 'marka_resim') {
            let reader = new FileReader();
            reader.onload = function(e) {
                $('#resimOnizleme img').attr('src', e.target.result);
                $('#resimOnizleme').show();
            }
            if(this.files[0]) {
                reader.readAsDataURL(this.files[0]);
            }
        }
    });

    /** Marka Ekle Modal **/
    $(document).on('click', '.markaEkleBtn', function(){
        $('#markaEkleForm')[0].reset();
        $('#resimOnizleme').hide();
        $('.custom-file-label').removeClass('selected').html('Dosya Seçin');
        $('#markaEkleModal').modal('show');
    });

    /** Marka Ekle Form Submit **/
    $(document).on('submit', '#markaEkleForm', function(e){
        e.preventDefault();
        
        let submitBtn = $('#markaEkleSubmit');
        let originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Kaydediliyor...');

        $.ajax({
            url: "{{ route('super.admin.markalar.store') }}",
            type: "POST",
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(res){
                $('#markaEkleModal').modal('hide');
                alert(res.message);
                location.reload();
            },
            error: function(xhr){
                let errorMsg = xhr.responseJSON?.message || "Bir hata oluştu!";
                alert("Hata: " + errorMsg);
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    /** Marka Düzenle Modal **/
    $(document).on('click', '.markaDuzenleBtn', function(){
        let id = $(this).data('id');
        let marka = $(this).data('marka');
        let resim = $(this).data('resim');
        
        $('#duzenle_marka_id').val(id);
        $('#duzenle_marka_adi').val(marka);
        
        if(resim) {
            $('#mevcutResim img').attr('src', '{{ asset("upload/ariza_kodlari/") }}/' + resim);
            $('#mevcutResim').show();
        } else {
            $('#mevcutResim').hide();
        }
        
        $('#duzenle_marka_resim').val('');
        $('.custom-file-label').removeClass('selected').html('Dosya Seçin');
        
        $('#markaDuzenleModal').modal('show');
    });

    /** Marka Düzenle Form Submit **/
    $(document).on('submit', '#markaDuzenleForm', function(e){
        e.preventDefault();
        
        let id = $('#duzenle_marka_id').val();
        let submitBtn = $('#markaDuzenleSubmit');
        let originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Güncelleniyor...');

        $.ajax({
            url: "/super-admin/marka-duzenle/" + id,
            type: "POST",
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(res){
                $('#markaDuzenleModal').modal('hide');
                alert(res.message);
                location.reload();
            },
            error: function(xhr){
                let errorMsg = xhr.responseJSON?.message || "Bir hata oluştu!";
                alert("Hata: " + errorMsg);
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    /** Marka Sil **/
    $(document).on('click', '.markaSil', function(){
        if(!confirm('Bu markayı silmek istediğinize emin misiniz?\n\nİlişkili tüm modeller ve arıza kodları da silinecektir!')) return;
        
        let id = $(this).data('id');
        let row = $(this).closest('tr');

        $.ajax({
            url: "/super-admin/marka-sil/" + id,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(res){
                table.row(row).remove().draw();
                alert(res.message);
            },
            error: function(xhr){
                let errorMsg = xhr.responseJSON?.message || "Silme işlemi başarısız!";
                alert("Hata: " + errorMsg);
            }
        });
    });

    // Modal kapatma olaylarını manuel ekle
    $(document).on('click', '[data-dismiss="modal"]', function(){
        $(this).closest('.modal').modal('hide');
    });

    // Modal dışına tıklayınca kapat
    $(document).on('click', '.modal', function(e){
        if($(e.target).hasClass('modal')) {
            $(this).modal('hide');
        }
    });

    // ESC tuşuyla kapat
    $(document).on('keydown', function(e){
        if(e.key === 'Escape') {
            $('.modal').modal('hide');
        }
    });

    // Modal kapandığında formu temizle
    $('#markaEkleModal, #markaDuzenleModal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        $(this).find('.custom-file-label').removeClass('selected').html('Dosya Seçin');
        $('#resimOnizleme').hide();
        $('#mevcutResim').hide();
    });
    /** Marka Adına Tıklayınca Düzenle Modal Aç **/
    $(document).on('click', '.marka-adi-link', function(){
        let id = $(this).data('id');
        let marka = $(this).data('marka');
        let resim = $(this).data('resim');
        
        $('#duzenle_marka_id').val(id);
        $('#duzenle_marka_adi').val(marka);
        
        if(resim) {
            $('#mevcutResim img').attr('src', '{{ asset("upload/ariza_kodlari/") }}/' + resim);
            $('#mevcutResim').show();
        } else {
            $('#mevcutResim').hide();
        }
        
        $('#duzenle_marka_resim').val('');
        $('.custom-file-label').removeClass('selected').html('Dosya Seçin');
        
        $('#markaDuzenleModal').modal('show');
    });
});
</script>
<script>
$(document).ready(function() {
    let markaFormSubmitting = false;
    
    // Benzersiz token oluştur
    function generateMarkaToken() {
        return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    // Sayfa yüklendiğinde ilk token'ı oluştur
    $('#markaEkleFormToken').val(generateMarkaToken());
    
    // Marka Ekle Modal açılınca yeni token oluştur
    $(document).on('click', '.markaEkleBtn', function(){
        $('#markaEkleFormToken').val(generateMarkaToken());
        markaFormSubmitting = false;
        $('#markaEkleSubmit').prop('disabled', false);
    });
    
    // Mevcut submit handler'ı override et
    $('#markaEkleForm').off('submit').on('submit', function(event) {
        event.preventDefault();
        
        // Token kontrolü
        if (markaFormSubmitting) {
            return false;
        }
        
        let submitBtn = $('#markaEkleSubmit');
        let originalText = submitBtn.html();
        
        // Butonu disable et
        markaFormSubmitting = true;
        submitBtn.prop('disabled', true);
        
        $.ajax({
            url: "{{ route('super.admin.markalar.store') }}",
            type: "POST",
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(res){
                $('#markaEkleModal').modal('hide');
                alert(res.message);
                location.reload();
            },
            error: function(xhr){
                let errorMsg = xhr.responseJSON?.message || "Bir hata oluştu!";
                alert("Hata: " + errorMsg);
                
                // Yeni token oluştur ve formu yeniden aktif et
                $('#markaEkleFormToken').val(generateMarkaToken());
                markaFormSubmitting = false;
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
        
        // 3 saniye sonra yeniden aktif et
        setTimeout(function() {
            $('#markaEkleFormToken').val(generateMarkaToken());
            markaFormSubmitting = false;
            $('#markaEkleSubmit').prop('disabled', false).html(originalText);
        }, 3000);
        
        return false;
    });
    
    // Modal kapandığında token yenile
    $('#markaEkleModal').on('hidden.bs.modal', function () {
        $('#markaEkleFormToken').val(generateMarkaToken());
        markaFormSubmitting = false;
        $('#markaEkleSubmit').prop('disabled', false);
    });
});
</script>
@endsection