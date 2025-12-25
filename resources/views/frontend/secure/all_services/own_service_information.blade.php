<div class="servisModal teknisyenServisModal">
    <form method="POST" id="servisDuzenle" action="">
        @csrf        
        <div class="card card-own" style="margin-bottom: 3px">
            <div class="card-header  ch1" style="padding: 3px 5px!important;">
                <div class="row">
                    <div class="col-sm-12" style="text-align: left;">
                        <label style="text-align: left;width: auto;display: inline-block;margin: 0;">
                            Servis Kaynağı: 
                            <span style="background: #ec0000;border: 1px solid #ce0000;color: #fff;padding: 0px 5px;border-radius: 3px;margin-left: 5px;max-width: 215px">
                                {{ $servis->skaynak->kaynak ?? 'Belirtilmemiş' }}
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row1">
            <div class="col-sm-6 c1">
                <div class="card card-own">
                    <div class="card-header card-own-header">MÜŞTERİ BİLGİSİ</div>
                    <div class="card-body card-own-body">
                        <span class="musName">
                            {{ $servis->musteri->adSoyad }}
                            @if($servis->musteri->musteriTipi == 1)
                                (BİREYSEL)
                            @elseif($servis->musteri->musteriTipi == 2)
                                (KURUMSAL)
                            @endif
                        </span>
                        
                        <span>
                            <a href="tel:0{{ $servis->musteri->tel1 }}" style="color:red">{{ $servis->musteri->tel1 }}</a>
                            @if($servis->musteri->tel2)
                                - <a href="tel:0{{ $servis->musteri->tel2 }}" style="color:red">{{ $servis->musteri->tel2 }}</a>
                            @endif
                        </span>
                        
                        <span>
                            <a href="https://www.google.com/maps?daddr={{ $servis->musteri->adres }} {{ $servis->musteri->state->ilceName }}/{{ $servis->musteri->country->name }}" style="color:red">
                                {{ $servis->musteri->adres }} {{ $servis->musteri->state->ilceName }}/{{ $servis->musteri->country->name }}
                            </a>
                        </span>
                        
                        @if($servis->musteri->musteriTipi == 1 && $servis->musteri->tcNo)
                            <span>T.C. {{ $servis->musteri->tcNo }}</span>
                        @elseif($servis->musteri->musteriTipi == 2 && $servis->musteri->vergiNo)
                            <span>Vergi No: {{ $servis->musteri->vergiNo }}-{{ $servis->musteri->vergiDairesi }}</span>
                        @endif
                        
                        <span>
                            <label>Müsait Olma Zamanı: </label>
                            {{ $musaitTarih[2] }}/{{ $musaitTarih[1] }}/{{ $musaitTarih[0] }} {{ $servis->musaitSaat1 }}-{{ $servis->musaitSaat2 }}
                        </span>
                        
                        
                    </div>
                </div>
            </div>

            <div class="col-sm-6 c2">
                <div class="card card-own">
                    <div class="card-header card-own-header">CİHAZ BİLGİSİ</div>
                    <div class="card-body card-own-body">
                        <span class="cihazName">
                            {{ strtoupper($servis->markaCihaz->marka . ' - ' . $servis->turCihaz->cihaz . ' - ' . $servis->cihazAriza) }}
                        </span>
                        
                        <span>
                            <label>Operatör Notu: </label>
                            {{ $servis->operatorNotu }}
                        </span>
                        
                        <span>
                            <label>Garanti Süresi: </label>
                            @if($servis->garantiSuresi && $garantiBitis)
                                {{ \Carbon\Carbon::parse($garantiBitis)->format('d/m/Y') }} ({{ $kalanGun }} Gün Kaldı)
                            @else
                                Garanti Yok
                            @endif
                        </span>
                        
                        <span style="margin:0">
                            <label>Cihaz Modeli: </label>
                            <input type="text" name="cihazModel" class="form-control cihazModel" 
                                   value="{{ $servis->cihazModel }}" 
                                   style="display:inline-block;width:calc(100% - 147px);padding:3px 5px!important;">
                            <button type="button" class="btn btn-primary btn-sm servisGuncelleBtn" 
                                    style="font-size:12px;padding:2px 5px;position:relative;top:-3px;left:3px;">
                                Kaydet
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" name="acil" class="acil" value="{{ $servis->acil }}"/>
        <input type="hidden" name="markaid" class="markaid" value="{{ $servis->cihazMarka }}"/>
        <input type="hidden" name="servisid" class="servisid" value="{{ $servis->id }}"/>
        
        <input type="submit" class="btn btn-primary btn-sm" style="display: none;">
    </form>

    <div class="servisAsamalari">
        @if($kalanGun >= 0)
            @if(!$eskiIslemler2 || $eskiIslemler2->pid != auth()->user()->id)
                <div class="card card-own" style="margin-top: 5px;">
                    <div class="card-header card-own-header  ch1" style="padding: 3px 7px!important;">
                        <div class="row">
                            <div class="col-md-6 col1 mb-3 mb-lg-0">
                                <label class="servisAcilLabel servisAcilBtn" style="user-select: none;-ms-user-select: none;-moz-user-select: none;-webkit-user-select: none;-webkit-touch-callout: none;position: relative;margin: 0; color: #fff; background: #343a40; border: 1px solid #212529;padding: 0 5px;border-radius: 3px;top: -2px;cursor: pointer;">
                                    <span>Acil</span>
                                    <input type="checkbox" name="acil" {{ $servis->acil ? 'checked' : '' }} style="display: none;">
                                    <div class="checkmark"><i class="fas fa-check"></i></div>
                                </label>
                                <input type="hidden" name="acil" class="acil" value="0"/> 
                                <input type="hidden" class="servisDurum" value="{{ $servis->servisDurum }}">
                            </div>
                            <div class="col-md-6 col2">
                                <label style="margin: 0">Yapılacak İşlemi Seçiniz: </label>
                                <select class="form-control altAsamalar" name="altAsamalar"
                                        style="display: inline-block;width: 240px;margin-left: 2px">
                                    <option value="">Seçiniz</option>
                                    @foreach($altAsamalar as $asama)
                                        @if($asama->id != 244 && $asama->id != 247)
                                            @if($asama->id == 264)
                                                @if(auth()->user()->can('Bayileri Görebilir') && $user->tenant->canAccessDealersModule())
                                                    <option value="{{ $asama->id }}">{{ $asama->asama }}</option>
                                                @endif
                                            @else
                                                <option value="{{ $asama->id }}">{{ $asama->asama }}</option>
                                            @endif
                                        @endif
                                    @endforeach
                                </select>
                        </div>
                        </div>
                    </div>
                    <div class="card-body card-own-body altSecenekler" style="padding: 0"></div>
                </div>
            @endif
        @endif

        <div class="card card-own" style="margin-top: 5px;">
            <div class="card-body card-own-body" style="padding: 0">
                <div id="no-more-tables">
                    <div class="table-responsive" style="margin: 0">
                        <table class="table table-hover table-striped servisAsamaTable" width="100%" cellspacing="0" style="margin: 0">
                            <thead class="title">
                                <tr>
                                    <th style="padding: 5px 10px;font-size: 12px;">Tarih</th>
                                    <th style="padding: 5px 10px;font-size: 12px;">İşlemi Yapan</th>
                                    <th style="padding: 5px 10px;font-size: 12px;">İşlem Adı</th>
                                    <th style="padding: 5px 10px;font-size: 12px;">Açıklama</th>
                                    @if($kalanGun >= 0)
                                        <th colspan="2" style="padding: 10px;font-size: 12px;"></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="serviceHistoryTableBody">
                               
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($kalanGun >= 0)
        <div class="card card-own cf1" style="margin-top: 5px;">
            <div class="card-header card-own-header" style="padding: 3px 5px;">
                <div class="row">
                    <div class="col-sm-12" style="text-align: right;">
                                <a href="#" class="btn btn-sm btn-warning btn-sm-custom servisYaziKopyala" data-servis-id="{{ $servis->id }}"> Fiş Yazdır</a>

                        <button type="button" class="btn btn-info btn-sm btn-sm-custom servisGuncelleBtn"style="background-color: #343a40;border-color:#343a40">
                            Servis Güncelle
                        </button>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    $(".altAsamalar").on("change", function () {
    var id = $(this).val();
    var service = {{$servis->id}};
    var firma_id = {{$firma->id}};
    var cihazModel = $(".cihazModel").val();
    if(id!="248"){
      if(!cihazModel){  //ID 248 değilse, yani özel bir durum değilse, cihaz modeli girilmemişse uyarı verir ve işlem durdurulur.
        alert("Cihaz modeli girmeden devam edemezsiniz.");
        $('.altSecenekler').html(""); // display data
        return false;
      }
    }
    if(id){
      $.ajax({
        url: "/" + firma_id + "/servis-asama-sorusu-getir/" + id + "/" + service 
      }).done(function(data) {
        if($.trim(data)==="-1"){
          window.location.reload(true);
        }else{
          $('.altSecenekler').html(data);
        }
      });
    }else{
      $('.altSecenekler').html("");
    }
  });
</script>

<script>
    $('.teknisyenServisModal').on('click', '.servisPlanDuzenleBtn', function(e) {
      var id = $(this).attr("data-bs-id");
      $('#editServicePlanModal').modal('show');
      var firma_id = {{$firma->id}};
      $.ajax({
        url: "/" + firma_id + "/servis-plan/duzenle/" + id
      }).done(function(data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {       
          $('#editServicePlanModal .modal-body').html(data);               
        }
      });
    });
</script>
<script>
  $(document).ready(function() {
    $('.teknisyenServisModal').on('click', '.servisPlanSil', function(e) {
      e.preventDefault();
      var confirmDelete = confirm("Bu müşteri aşamasını silmek istediğinizden emin misiniz?");
      if (confirmDelete) {
        var id = $(this).attr('data-id');
        var firma_id = {{$firma->id}};
        $.ajax({
          url: '/' + firma_id + '/servis-plan-sil/' + id,
          type: 'POST',
          data: {
            _method: 'POST', 
            _token: '{{ csrf_token() }}'
          },
          success: function(data) {
            if (data) {
              $('#servisAsamaTable tbody').html(data);
              loadServiceHistory({{ $servis->id }});
              $('#datatableService').DataTable().ajax.reload();

              if (data.altAsamalar) {
              var altAsamalarSelect = $('.servisAsamalari .altAsamalar');
              altAsamalarSelect.empty();
              altAsamalarSelect.append('<option value="">-Seçiniz-</option>');
              
              $.each(data.altAsamalar, function(index, item) {
                altAsamalarSelect.append('<option value="' + item.id + '">' + item.asama + '</option>');
              });
              
              // Hiçbir seçenek seçili olmasın
              altAsamalarSelect.prop('selectedIndex', 0);
            }

              $('.kayitAlan span').text(data.asama);
              
            } else {
              alert("Silme işlemi başarısız oldu.");
            }
          },
          error: function(xhr, status, error) {
            console.error(xhr.responseText);
          }
        });
      }
    });
  });
</script>
<script>
var currentUserId = {{ auth()->user()->user_id }};
$(document).ready(function() {
  var serviceId = {{$servis->id}};
    loadServiceHistory( serviceId );
});

function loadServiceHistory(service_id) {
    var firma_id = {{$firma->id}};
    $.ajax({
        url: "/" + firma_id + '/servis-asama/' + service_id + '/history',
        method: 'GET',
        success: function(data) {
            renderServiceHistory(data);
        },
        error: function() {
            alert('Veriler yüklenirken hata oluştu.');
        }
    });
}

function renderServiceHistory(data) {
    var tbody = $('#serviceHistoryTableBody');
    tbody.empty();
    
    // Acil durum
  if (data.acilIslem) {
        var acilRow = `
          <tr class="acilRow">
            <td class="kayitTarihiCS" style="vertical-align: middle;width: 100px; font-size: 13px; padding: 5px;">${data.acilIslem.tarih}</td>
            <td style="vertical-align: middle;font-size: 13px; padding: 5px;"><strong>NOT</strong></td>
            <td style="vertical-align: middle;font-size: 13px; padding: 5px;"><strong>Servis Acil Aşamasındadır.</strong></td>
            <td style="vertical-align: middle;font-size: 13px; padding: 5px;" colspan="3">Servis işlemi bittiğinde acil işaretini kaldırın.</td>
          </tr>
        `;
        tbody.append(acilRow);
    }
    
    // Notlar
    data.notlar.forEach(function(not) {
        var notRow = `
            <tr>
                <td class="kayitTarihiCS" style="vertical-align: middle;width: 100px; font-size: 11px; padding: 5px;">${not.tarih}</td>
                <td style="vertical-align: middle;font-size: 11px; padding: 5px;">${not.personel}</td>
                <td style="vertical-align: middle;font-size: 11px; padding: 5px;color:#ec0000;"><strong>Operatör Notu</strong></td>
                <td style="vertical-align: middle;font-size: 11px; padding: 5px;" colspan="3"><strong>${not.aciklama}</strong></td>
            </tr>
        `;
        tbody.append(notRow);
    });
    
    // Eski işlemler
   data.eskiIslemler.forEach(function(islem) {
    if (islem.type === 'para') {
        // para işlemleri zaten buttons içermiyor
        var paraRow = `
            <tr>
                <td class="kayitTarihiCS" style="vertical-align: middle;width: 100px; font-size: 11px; padding: 5px;">${islem.tarih}</td>
                <td style="vertical-align: middle;font-size: 11px; padding: 5px;">${islem.personel}</td>
                <td style="vertical-align: middle;font-size: 11px; padding: 5px;"><strong>${islem.islem}</strong></td>
                <td style="vertical-align: middle;font-size: 11px; padding: 5px;" colspan="3"><strong>${islem.aciklama}</strong></td>
            </tr>
        `;
        tbody.append(paraRow);
    } else {
        var buttons = '';

        if (islem.pid == currentUserId) {
            // Hücreyi (td) oluştur
            buttons += '<td colspan="2">';
            
            // Değişiklik: İkonları sağa yaslamak ve aralarında boşluk bırakmak için bir div eklendi.
            // d-flex: Flexbox'ı etkinleştirir.
            // justify-content-end: İçeriği sağa yaslar.
            // gap-2: Elemanlar arasına boşluk ekler.
            buttons += '<div class="d-flex justify-content-center gap-2">';

            // Sil butonu (Gereksiz inline stiller kaldırıldı)
            buttons += `<a style="    padding: 6px 7px;" href="#" id="servisPlanSil" class="btn btn-outline-danger btn-sm btn-sm-custom servisPlanSil" data-id="${islem.id}" title="Sil"><i style="line-height: 1.2;" class="fas fa-trash-alt"></i></a>`;
            
            // Düzenle butonu (Gereksiz inline stiller kaldırıldı, boşluk 'gap-2' ile sağlandı)
            buttons += `<a style="padding: 6px 6px;color:#e39d23 " href="#" data-bs-id="${islem.id}" class="btn btn-outline-warning btn-sm btn-sm-custom servisPlanDuzenleBtn" title="Düzenle"><i class="fas fa-edit"></i></a>`;
            
            buttons += '</div>'; // Flexbox div'ini kapat
            buttons += '</td>';  // Hücreyi (td) kapat
        } else {
            // Yetkiniz yoksa olan kısım (değişiklik yok)
            buttons += `
                <td colspan="2" style="font-size: 11px; color: red; text-align: center;">
                    <strong>Yetkiniz yok</strong>
                </td>
            `;
        }
       

        var islemRow = `
            <tr>
                <td class="kayitTarihiCS" style="vertical-align: middle;width: 100px; font-size: 11px; padding: 0 5px;">${islem.tarih}</td>
                <td style="vertical-align: middle;font-size: 11px; padding: 0 5px;">${islem.personel}</td>
                <td class="islemAsamaCS" style="vertical-align: middle;font-size: 11px; padding: 0 5px;"><strong>${islem.asama}</strong></td>
                <td class="islemAciklamaCS" style="vertical-align: middle;font-size: 11px;padding: 0 5px;width: 300px;text-transform: capitalize;">${islem.aciklamalar.join('<br>')}</td>
                ${buttons}
            </tr>
        `;
        tbody.append(islemRow);
    }
});
    
    // Para hareketleri
    data.paraHareketleri.forEach(function(para) {
        var paraRow = `
            <tr>
                <td class="kayitTarihiCS" style="vertical-align: middle;width: 100px; font-size: 11px; padding: 5px;">${para.tarih}</td>
                <td style="vertical-align: middle;font-size: 11px; padding: 5px;">${para.personel}</td>
                <td style="vertical-align: middle;font-size: 11px; padding: 5px;"><strong>${para.islem}</strong></td>
                <td style="vertical-align: middle;font-size: 11px; padding: 5px;" colspan="3"><strong>${para.aciklama}</strong></td>
            </tr>
        `;
        tbody.append(paraRow);
    });
}
</script>

<script type="text/javascript">
  $(document).ready(function (e) {  
    // ACİL BUTONU İŞLEYİŞİ - GÜNCELLEME
    $(".servisAcilBtn").on('click', function(e) {
        e.preventDefault();
        
        var checkbox = $(this).find('input[type="checkbox"]');
        var checkIcon = $(this).find('.checkmark i');
        var hiddenInput = $("#servisDuzenle .acil");
        
        // Checkbox durumunu toggle et
        if (checkbox.is(':checked')) {
            checkbox.prop('checked', false);
            checkIcon.hide();
            hiddenInput.val('0');
            $(this).css('background', '#343a40');
        } else {
            checkbox.prop('checked', true);
            checkIcon.show();
            hiddenInput.val('1');
            $(this).css('background', '#dc3545');
        }
    });
    
    // Sayfa yüklendiğinde acil durumunu kontrol et
    if ($('.servisAcilBtn input[type="checkbox"]').is(':checked')) {
        $('.servisAcilBtn').css('background', '#dc3545');
        $('.servisAcilBtn .checkmark i').show();
    } else {
        $('.servisAcilBtn').css('background', '#343a40');
        $('.servisAcilBtn .checkmark i').hide();
    }
    
    $(".servisGuncelleBtn").click( function(){
      $("#servisDuzenle").submit();
    });

    $("#servisDuzenle").on('submit', (function (e) {
      var cihazModel = $.trim($("#servisDuzenle .cihazModel").val());
      var firma = {{$firma->id}};
     
      if (cihazModel.length === 0) {
        alert("Cihaz modeli boş geçilemez");
        $(".cihazModel").focus();
        return false;
      } else {
        e.preventDefault();
        $.ajax({
          url: "/" + firma +  "/servis/guncelle",
          type: "POST",
          data: new FormData(this),
          contentType: false,
          cache: false,
          processData: false,
          success: function (data) {
              alert("Servis başarıyla güncellendi.");
              
              // Modal içeriğini ve arka plandaki tabloyu güncelle
              loadServiceHistory({{$servis->id}});
              $('#datatableService').DataTable().ajax.reload();
              $('.nav1').trigger('click');
          },
          error: function (e) {
            alert("Servis güncellenirken hatayla karşılaşıldı." + e);
          }
        });
      }
    }));
  });
</script>
<script>
  $(document).on('click', '.servisYaziKopyala', function(e) {
    e.preventDefault();
    
    var servisId = $(this).data('servis-id'); // veya nasıl alıyorsan
    var btn = $(this);
    var originalText = btn.html();
    var firma = {{$firma->id}};

    btn.html('<i class="fas fa-spinner fa-spin"></i> Yükleniyor...').prop('disabled', true);
    
    $.ajax({
        url: '/' + firma + '/servis/' + servisId + '/fis-icerigi',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Panoya kopyala
                navigator.clipboard.writeText(response.icerik).then(function() {
                    toastr.success('Fiş içeriği panoya kopyalandı!');
                    btn.html('<i class="fas fa-check"></i> Kopyalandı!');
                    
                    setTimeout(function() {
                        btn.html(originalText).prop('disabled', false);
                    }, 2000);
                }).catch(function(err) {
                    // Fallback - eski yöntem
                    fallbackCopyTextToClipboard(response.icerik);
                    toastr.success('Fiş içeriği panoya kopyalandı!');
                    btn.html(originalText).prop('disabled', false);
                });
            } else {
                toastr.error(response.message);
                btn.html(originalText).prop('disabled', false);
            }
        },
        error: function(xhr) {
            var errorMsg = xhr.responseJSON?.message || 'Bir hata oluştu';
            toastr.error(errorMsg);
            btn.html(originalText).prop('disabled', false);
        }
    });
});

// Eski tarayıcılar için fallback
function fallbackCopyTextToClipboard(text) {
    var textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
    } catch (err) {
        console.error('Kopyalama hatası:', err);
    }
    
    document.body.removeChild(textArea);
}
</script>