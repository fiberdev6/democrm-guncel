<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="" id="allServiceFoto">
<form id="servisFotoEkle" enctype="multipart/form-data">
    <div class="upload-zone" onclick="document.getElementById('resimInput').click()">
        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
        <p class="mb-2">Fotoğraf yüklemek için tıklayın veya dosyaları buraya sürükleyin</p>
        <p class="text-muted small" style="margin-bottom:0;">Desteklenen formatlar: JPG, PNG, JPEG (Max: 2MB - 2 Adet)
        </p>
        <input name="belge" class="d-none" id="resimInput" type="file" accept=".jpg,.jpeg,.png" multiple>
    </div>

    <div class="progress-container">
        <div class="progress">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">
            </div>
        </div>
        <small class="text-muted">Yükleniyor...</small>
    </div>

    <div id="uploadMessages" class="mt-2"></div>

    <input type="hidden" name="servisFotoEkle" value="Ekle">
    <input type="hidden" name="servisid" value="{{$servis->id}}">
</form>


<!-- Mevcut Fotoğraflar -->
<div class="card">
    <div class="card-header service-h5">
        <h5>Mevcut Fotoğraflar</h5>
    </div>
    <div class="card-body">
        <div class="row" id="photoGallery">
            <!-- Mevcut fotoğraflar buraya gelecek -->
            @foreach ($photos as $item)
                <div class="col-md-2 col-sm-6">
                    <div class="photo-item">
                        <a href="{{ Storage::url($item->resimyol)}}" data-fancybox="gallery">
                            <img src="{{ Storage::url($item->resimyol)}}" alt="Servis Fotoğrafı">
                        </a>
                        <button class="delete-btn servisFotoSil" data-id="{{$item->id}}" title="Sil">×</button>
                        <div class="photo-overlay">
                            <small>Yükleme: {{\Carbon\Carbon::parse($item->created_at)->format('d/m/Y')}}</small>
                        </div>
                    </div>
                </div>
            @endforeach


        </div>
        <div id="noPhotos" class="text-center text-muted" style="display: {{ count($photos) > 0 ? 'none' : 'block' }};">
            <i class="fas fa-images fa-3x mb-2" style="font-size: 2em;"></i>
            <p>Henüz fotoğraf yüklenmemiş</p>
        </div>
    </div>
</div>
</div>
<script>
        $(document).ready(function() {
            // CSRF token ayarı
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Fancybox başlatma
            $('[data-fancybox="gallery"]').fancybox({
                buttons: ['zoom', 'share', 'slideShow', 'fullScreen', 'download', 'thumbs', 'close'],
                animationEffect: 'fade',
                transitionEffect: 'slide'
            });

            // Drag & Drop işlemleri
            const uploadZone = $('.upload-zone');
            
            uploadZone.on('dragover dragenter', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('dragover');
            });

            uploadZone.on('dragleave dragend', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
            });

            uploadZone.on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
                
                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    handleFileUpload(files);
                }
            });

            // Dosya seçimi
            $('#resimInput').on('change', function() {
                const files = this.files;
                if (files.length > 0) {
                    handleFileUpload(files);
                }
            });

            // Dosya yükleme işlemi
            function handleFileUpload(files) {
                const maxSize = 2 * 1024 * 1024; // 5MB
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                const existing = $('#photoGallery .photo-item').length;  // hâlihazırdaki foto sayısı
                const max = 2;
                
                // Dosya validasyonu
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    
                    if (!allowedTypes.includes(file.type)) {
                        showMessage('Sadece JPG, PNG ve JPEG dosyaları yükleyebilirsiniz.', 'error');
                        return;
                    }
                    
                    if (file.size > maxSize) {
                        showMessage('Dosya boyutu 2MB\'dan büyük olamaz.', 'error');
                        return;
                    }
                }

                if (existing >= max) {
                    showMessage('Bu servise zaten 2 fotoğraf yüklendi.', 'error');
                    return;
                }
                if (existing + files.length > max) {
                    showMessage(`En fazla ${max - existing} fotoğraf daha yükleyebilirsiniz.`, 'error');
                    return;
                }

                // Her dosya için ayrı ayrı yükleme
                for (let i = 0; i < files.length; i++) {
                    uploadSingleFile(files[i]);
                }
            }

            // Tek dosya yükleme
            function uploadSingleFile(file) {
                const formData = new FormData();
                formData.append('belge', file);
                formData.append('servisFotoEkle', 'Ekle');
                formData.append('servisid', $('input[name="servisid"]').val());

                $('.progress-container').show();
                var firma_id = {{$firma->id}};
                $.ajax({
                    url: '/' + firma_id + '/servis-foto-yukle', // Gerçek URL'nizi buraya yazın
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        const xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                const percentComplete = (e.loaded / e.total) * 100;
                                $('.progress-bar').css('width', percentComplete + '%');
                            }
                        });
                        return xhr;
                    },
                    success: function(response) {
                        showMessage('Fotoğraf başarıyla yüklendi!', 'success');
                        addPhotoToGallery(response.photo);
                        resetUploadForm();
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Yükleme sırasında hata oluştu.';
                        showMessage(message, 'error');
                        resetUploadForm();
                    }
                });
            }

            // Fotoğrafı galeriye ekleme
            function addPhotoToGallery(photo) {
                const photoHtml = `
                    <div class="col-md-2 col-sm-6">
                        <div class="photo-item">
                            <a href="${photo.url}" data-fancybox="gallery">
                                <img src="${photo.url}" alt="Servis Fotoğrafı">
                            </a>
                            <button class="delete-btn servisFotoSil" data-id="${photo.id}" title="Sil">×</button>
                            <div class="photo-overlay">
                                <small>Yükleme: ${photo.created_at}</small>
                            </div>
                        </div>
                    </div>
                `;
                
                $('#photoGallery').prepend(photoHtml);
                $('#noPhotos').hide();
                
                // Yeni eklenen fotoğraf için fancybox başlatma
                $('[data-fancybox="gallery"]').fancybox();
            }


            // Fotoğraf silme
            $(document).on('click', '.servisFotoSil', function(e) {
        e.preventDefault();
        

         e.stopImmediatePropagation(); // Event'in birden fazla kez tetiklenmesini engelle

        const $button = $(this);
    
        // Zaten işlem yapılıyorsa çık
        if ($button.prop('disabled') || $button.hasClass('deleting')) {
            return false;

        }
        
        // İşlem bayrağı ekle
        $button.addClass('deleting');
        
        if (!confirm('Bu fotoğrafı silmek istediğinizden emin misiniz?')) {
            $button.removeClass('deleting'); // Bayrağı kaldır
            return false;
}

        const photoId = $(this).data('id');
        const $photoItem = $(this).closest('.col-md-2, .col-sm-6');
        
        // Butonu deaktive et
        $button.prop('disabled', true).text('...');

        $.ajax({
            url: `/{{ $firma->id }}/servis-foto-sil/${photoId}`,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $photoItem.fadeOut(300, function() {
                        $(this).remove();
                        
                        // Hiç fotoğraf kalmadıysa mesaj göster
                        if ($('#photoGallery .col-md-2, #photoGallery .col-sm-6').length === 0) {
                            $('#noPhotos').show();
                        }
                    });
                    
                    // Başarı mesajı göster
                    if (typeof showMessage === 'function') {
                        showMessage(response.message || 'Fotoğraf başarıyla silindi.', 'success');
                    } else {
                        alert(response.message || 'Fotoğraf başarıyla silindi.');
                    }
                } else {
                    alert(response.message || 'Silme işlemi başarısız oldu.');
                    $button.prop('disabled', false).text('×');
                }
            },
            error: function(xhr) {
                console.error('Silme hatası:', xhr);
                let message = 'Silme işlemi başarısız oldu.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.status === 404) {
                    message = 'Fotoğraf bulunamadı.';
                } else if (xhr.status === 403) {
                    message = 'Bu işlem için yetkiniz yok.';
                }
                
                alert(message);
                $button.prop('disabled', false).text('×');
            }
        });
    });

            // Mesaj gösterme
            function showMessage(message, type) {
                const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
                const messageHtml = `
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                
                $('#uploadMessages').html(messageHtml);
                
                // 5 saniye sonra mesajı otomatik kapat
                setTimeout(function() {
                    $('.alert').fadeOut();
                }, 2000);
            }

            // Upload formunu sıfırlama
            function resetUploadForm() {
                $('#resimInput').val('');
                $('.progress-container').hide();
                $('.progress-bar').css('width', '0%');
            }
        });

    </script>

