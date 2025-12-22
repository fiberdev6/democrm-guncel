@extends('frontend.secure.user_master')
@section('user')
<div class="page-content" id="modellerPage">
    <div class="container-fluid">
        <div class="row pageDetail">
            <div class="col-12 arizaModel">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">{{ $markaSec->marka }} - Modeller</h4>
                    </div>
                    <div class="card-body">
                        <!-- Üst Alan: Butonlar ve Arama Kutusu -->
                        <div class="d-flex align-items-center justify-content-between">
                            <!-- Sol Taraf: Butonlar -->
                            <div class="col-auto">
                                <button type="button" class="btn btn-success btn-sm modelEkleBtn px-3">
                                    <i class="fa fa-plus"></i> <span>Model Ekle</span>
                                </button>
                            </div>
                            
                            <!-- Sağ Taraf: Arama Kutusu Alanı -->
                            <div class="col-auto">
                                <div id="searchPlaceholder"></div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="modelTable" class="table table-hover table-striped" style="width:100%">
                                <thead class="title">
                                    <tr>
                                        <th width="60%">Model</th>
                                        <th width="40%">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($modeller as $model)
                                    <tr data-id="{{ $model->id }}">
                                       <td>
                                            <div class="d-flex align-items-center">
                                                @if($model->resimyol)
                                                    <img src="{{ asset('upload/ariza_kodlari/'.$model->resimyol) }}" 
                                                        width="50" 
                                                        height="50" 
                                                        style="object-fit: cover; min-width: 50px;" 
                                                        class="mr-2 border rounded"
                                                        loading="lazy"
                                                        alt="{{ $model->model }}">
                                                @else
                                                    <div class="mr-2 border rounded bg-light d-flex align-items-center justify-content-center" 
                                                        style="width: 50px; height: 50px; min-width: 50px;">
                                                        <i class="fa fa-image text-muted"></i>
                                                    </div>
                                                @endif
                                                <strong class="model-adi-link" 
                                                        data-id="{{ $model->id }}"
                                                        data-model="{{ $model->model }}"
                                                        data-resim="{{ $model->resimyol }}"
                                                        style="cursor: pointer; color: #007bff;"
                                                        title="Düzenlemek için tıklayın">
                                                    {{ $model->model }}
                                                </strong>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('super.admin.kodlar.index', ['model_id' => $model->id, 'marka_id' => $model->mid]) }}" 
                                                   class="btn btn-warning" title="Arıza Kodları">
                                                    <i class="fa fa-wrench"></i> Kodlar
                                                </a>
                                                <button class="btn btn-primary modelDuzenleBtn" 
                                                        data-id="{{ $model->id }}"
                                                        data-model="{{ $model->model }}"
                                                        data-resim="{{ $model->resimyol }}"
                                                        title="Düzenle">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger modelSil" 
                                                        data-id="{{ $model->id }}"
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

{{-- Model Ekle Modal --}}
<div class="modal fade" id="modelEkleModal" tabindex="-1" role="dialog" aria-labelledby="modelEkleTitle" aria-hidden="true">
    <div class="modal-dialog " role="document">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title" id="modelEkleTitle">
                    Yeni Model Ekle
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Kapat">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="modelEkleForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="form_token" id="modelEkleFormToken" value="">
                <input type="hidden" name="mid" value="{{ $markaSec->id }}">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="model_adi">Model Adı <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="model" 
                               id="model_adi"
                               class="form-control" 
                               placeholder="Örn: WAE24460TR, SMS46GI01E..." 
                               required>
                    </div>
                    <div class="form-group">
                        <label for="model_resim">Model Görseli</label>
                        <div class="custom-file">
                            <input type="file" 
                                   name="resim" 
                                   id="model_resim"
                                   class="custom-file-input" 
                                   accept="image/jpeg,image/jpg,image/png,image/svg+xml">
                            <label class="custom-file-label" for="model_resim">Dosya Seçin</label>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fa fa-info-circle"></i> Sadece JPG, PNG, SVG (Max: 2MB)
                        </small>
                        <div id="resimOnizleme" class="mt-2" style="display:none;">
                            <img src="" alt="Önizleme" class="img-thumbnail" style="max-width: 150px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info btn-sm" id="modelEkleSubmit">
                         Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Model Düzenle Modal --}}
<div class="modal fade" id="modelDuzenleModal" tabindex="-1" role="dialog" aria-labelledby="modelDuzenleTitle" aria-hidden="true">
    <div class="modal-dialog " role="document">
        <div class="modal-content">
            <div class="modal-header ">
                <h5 class="modal-title" id="modelDuzenleTitle">
                     Model Düzenle
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Kapat">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="modelDuzenleForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="model_id" id="duzenle_model_id">
                <input type="hidden" name="mid" id="duzenle_mid" value="{{ $markaSec->id }}">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="duzenle_model_adi">Model Adı <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="model" 
                               id="duzenle_model_adi"
                               class="form-control" 
                               required>
                    </div>
                    <div class="form-group">
                        <label for="duzenle_model_resim">Model Görseli</label>
                        <div class="custom-file">
                            <input type="file" 
                                   name="resim" 
                                   id="duzenle_model_resim"
                                   class="custom-file-input" 
                                   accept="image/jpeg,image/jpg,image/png,image/svg+xml">
                            <label class="custom-file-label" for="duzenle_model_resim">Dosya Seçin</label>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fa fa-info-circle"></i> Değiştirmek için yeni dosya seçin
                        </small>
                        <div id="mevcutResim" class="mt-2">
                            <img src="" alt="Mevcut Görsel" class="img-thumbnail" style="max-width: 150px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-sm" id="modelDuzenleSubmit">
                       Güncelle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
$(document).ready(function(){
    let table;

    // DataTable başlat
table = $('#modelTable').DataTable({
    "language": {
        "sSearch": "Model Ara:",
        "sEmptyTable": "Tabloda veri yok",
        "sZeroRecords": "Eşleşen kayıt bulunamadı"
    },
    "order": [[0, "asc"]],
    "paging": false,
    "info": false,
    "responsive": true,
    "dom": 'frti',
    "columnDefs": [
        { "orderable": false, "targets": 1 },
    ]
});
    
    // Arama kutusunu sağ tarafa taşı
    $('#modelTable_filter').appendTo('#searchPlaceholder');
    
    // Custom file input label güncelleme
    $(document).on('change', '.custom-file-input', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass("selected").html(fileName);
        
        if($(this).attr('id') === 'model_resim') {
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

    /** Model Ekle Modal **/
    $(document).on('click', '.modelEkleBtn', function(){
        $('#modelEkleForm')[0].reset();
        $('#resimOnizleme').hide();
        $('.custom-file-label').removeClass('selected').html('Dosya Seçin');
        $('#modelEkleModal').modal('show');
    });

    /** Model Ekle Form Submit **/
    $(document).on('submit', '#modelEkleForm', function(e){
        e.preventDefault();
        
        let submitBtn = $('#modelEkleSubmit');
        let originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Kaydediliyor...');

        $.ajax({
            url: "{{ route('super.admin.modeller.store') }}",
            type: "POST",
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(res){
                $('#modelEkleModal').modal('hide');
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

    /** Model Düzenle Modal **/
    $(document).on('click', '.modelDuzenleBtn', function(){
        let id = $(this).data('id');
        let model = $(this).data('model');
        let resim = $(this).data('resim');
        
        $('#duzenle_model_id').val(id);
        $('#duzenle_model_adi').val(model);
        
        if(resim) {
            $('#mevcutResim img').attr('src', '{{ asset("upload/ariza_kodlari") }}/' + resim);
            $('#mevcutResim').show();
        } else {
            $('#mevcutResim').hide();
        }
        
        $('#duzenle_model_resim').val('');
        $('.custom-file-label').removeClass('selected').html('Dosya Seçin');
        
        $('#modelDuzenleModal').modal('show');
    });

    /** Model Düzenle Form Submit **/
    $(document).on('submit', '#modelDuzenleForm', function(e){
        e.preventDefault();
        
        let id = $('#duzenle_model_id').val();
        let submitBtn = $('#modelDuzenleSubmit');
        let originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Güncelleniyor...');

        $.ajax({
            url: "{{ route('super.admin.modeller.update', '') }}/" + id,
            type: "POST",
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(res){
                $('#modelDuzenleModal').modal('hide');
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

    /** Model Sil **/
    $(document).on('click', '.modelSil', function(){
        if(!confirm('Bu modeli silmek istediğinize emin misiniz?\n\nİlişkili tüm arıza kodları da silinecektir!')) return;
        
        let id = $(this).data('id');
        let row = $(this).closest('tr');

        $.ajax({
            url: "{{ route('super.admin.modeller.destroy', '') }}/" + id,
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

    // Modal kapandığında formu temizle
    $('#modelEkleModal, #modelDuzenleModal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        $(this).find('.custom-file-label').removeClass('selected').html('Dosya Seçin');
        $('#resimOnizleme').hide();
        $('#mevcutResim').hide();
    });

    /** Model Adına Tıklayınca Düzenle Modal Aç **/
    $(document).on('click', '.model-adi-link', function(){
        let id = $(this).data('id');
        let model = $(this).data('model');
        let resim = $(this).data('resim');
        
        $('#duzenle_model_id').val(id);
        $('#duzenle_model_adi').val(model);
        
        if(resim) {
            $('#mevcutResim img').attr('src', '{{ asset("upload/ariza_kodlari") }}/' + resim);
            $('#mevcutResim').show();
        } else {
            $('#mevcutResim').hide();
        }
        
        $('#duzenle_model_resim').val('');
        $('.custom-file-label').removeClass('selected').html('Dosya Seçin');
        
        $('#modelDuzenleModal').modal('show');
    });
});
</script>
<script>
$(document).ready(function() {
    let modelFormSubmitting = false;
    
    // Benzersiz token oluştur
    function generateModelToken() {
        return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    // Sayfa yüklendiğinde ilk token'ı oluştur
    $('#modelEkleFormToken').val(generateModelToken());
    
    // Model Ekle Modal açılınca yeni token oluştur ve mevcut handler'ı override et
    $(document).off('click', '.modelEkleBtn').on('click', '.modelEkleBtn', function(){
        $('#modelEkleForm')[0].reset();
        $('#resimOnizleme').hide();
        $('.custom-file-label').removeClass('selected').html('Dosya Seçin');
        $('#modelEkleFormToken').val(generateModelToken());
        modelFormSubmitting = false;
        $('#modelEkleSubmit').prop('disabled', false);
        $('#modelEkleModal').modal('show');
    });
    
    // Form submit - mevcut handler'ı tamamen değiştir
    $('#modelEkleForm').off('submit').on('submit', function(event) {
        event.preventDefault();
        
        // Token kontrolü
        if (modelFormSubmitting) {
            return false;
        }
        
        let submitBtn = $('#modelEkleSubmit');
        let originalText = submitBtn.html();
        
        // Butonu disable et
        modelFormSubmitting = true;
        submitBtn.prop('disabled', true);
        
        $.ajax({
            url: "{{ route('super.admin.modeller.store') }}",
            type: "POST",
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(res){
                $('#modelEkleModal').modal('hide');
                alert(res.message);
                location.reload();
            },
            error: function(xhr){
                let errorMsg = xhr.responseJSON?.message || "Bir hata oluştu!";
                alert("Hata: " + errorMsg);
                
                // Yeni token oluştur ve formu yeniden aktif et
                $('#modelEkleFormToken').val(generateModelToken());
                modelFormSubmitting = false;
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
        
        // 3 saniye sonra yeniden aktif et
        setTimeout(function() {
            $('#modelEkleFormToken').val(generateModelToken());
            modelFormSubmitting = false;
            $('#modelEkleSubmit').prop('disabled', false);
        }, 3000);
        
        return false;
    });
    
    // Modal kapandığında token yenile
    $('#modelEkleModal').on('hidden.bs.modal', function () {
        $('#modelEkleFormToken').val(generateModelToken());
        modelFormSubmitting = false;
        $('#modelEkleSubmit').prop('disabled', false);
    });
});
</script>
@endsection