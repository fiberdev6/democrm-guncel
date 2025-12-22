@extends('frontend.secure.user_master')
@section('user')
<link rel="preload" as="image" href="{{ asset('frontend/img/alarm.gif') }}">

 <!-- <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>-->
  <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<div class="page-content" id="servicesPage">
    <div class="container-fluid">
      <div class="row pageDetail">
        <div class="col-12">
          <div class="card">
            {{-- Başlık ve Butonlar burada --}}
            <div class="card-header  d-flex flex-wrap justify-content-between align-items-center">
              <h5 style="color: #505d69 !important;" class="sayfaBaslik mb-0">Servisler</h5>
              <div
                class="header-buttons d-flex flex-wrap gap-1 w-100 w-md-auto mt-2 mt-md-0 justify-content-center justify-content-md-end">
                @if(auth()->user()->can('Tüm Servisleri Görebilir'))
                  <button type="button" class="btn btn-primary btn-sm servisRaporlaModalBtn flex-grow-1 flex-md-grow-0"
                    data-toggle="modal" data-target="#servisRaporlaModal">Raporlar</button>
                  <button type="button" class="btn btn-primary btn-sm anketModalBtn flex-grow-1 flex-md-grow-0"
                    data-toggle="modal" data-target="#anketModal">Anketler</button>
                  @if(Auth::check() && Auth::user()->hasAnyRole(['Operatör',]))
                    <button type="button" class="btn btn-primary btn-sm kullaniciPrimGoster flex-grow-1 flex-md-grow-0"
                      data-toggle="modal" data-target="#kullaniciPrimModal"> Primlerim </button>
                  @else
                    <button type="button" class="btn btn-primary btn-sm primModalBtn flex-grow-1 flex-md-grow-0"
                      data-toggle="modal" data-target="#primModal">Primler</button>
                  @endif
                @endif
              </div>
            </div>
            <div class="card-body">
              @if(auth()->user()->can('Tüm Servisleri Görebilir'))
               <div class="action-buttons-container">
                  <div class="col-3">
                    <a class="btn btn-success btn-sm addService" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                      <i class="fas fa-plus"></i><span class="d-inline d-md-none">Ekle</span><span class="d-none d-md-inline">Servis Ekle</span>
                    </a>
                  </div>
                  
                  <div class="col-3">
                    <a type="button" class="btn btn-success btn-sm gelenCagriButon" data-bs-toggle="modal" data-bs-target="#gelenCagriModal">
                      <div class="text">
                        <i class="fas fa-headset d-inline d-md-none" data-toggle="tooltip" title="Gereksiz çağrıları kaydetmek için kullanılır."></i>
                        <span class="d-inline d-md-none">Çağrılar</span>
                        <span class="d-none d-md-inline">Gelen Çağrılar</span>
                        <i class="fas fa-info-circle d-none d-md-inline" data-toggle="tooltip" title="Gereksiz çağrıları kaydetmek için kullanılır."></i>
                      </div>
                    </a>
                  </div>

                  <div class="col-3">
                    <button type="button" class="btn btn-danger btn-sm servisPlanlaBtn">
                      <div class="text">
                        <i class="fas fa-location-arrow d-inline d-md-none" data-toggle="tooltip" title="Toplu servis yönlendirmeleri yapmak için kullanılır."></i>
                        <span class="d-inline d-md-none">Planlama</span>
                        <span class="d-none d-md-inline">Servis Planlama</span>
                        <i class="fas fa-info-circle d-none d-md-inline" data-toggle="tooltip" title="Toplu servis yönlendirmeleri yapmak için kullanılır."></i>
                      </div>
                    </button>
                  </div>
                  
                  <div class="col-3">
                    <a href="javascript:void(0);" class="btn btn-warning btn-sm printServices">
                      <i class="fas fa-print d-inline d-md-none"></i>
                      <span class="d-inline d-md-none">Yazdır</span>
                      <span class="d-none d-md-inline"><i class="fas fa-print"></i> Yazdır</span>
                    </a>
                  </div>
                </div>
                {{-- Raporlar, Anketler, Primler butonları taşındı--}}
              @endif
              <div>
                @if(auth()->user()->hasAnyRole(['Teknisyen', 'Teknisyen Yardımcısı', 'Atölye Ustası', 'Atölye Çırak']))
                  <button type="button" class="btn btn-primary btn-sm teknisyenDepoGoster" data-toggle="modal"
                    data-target="#teknisyenDepoModal"> Depo </button>
                @endif
                @if(Auth::check() && Auth::user()->hasAnyRole(['Teknisyen', 'Teknisyen Yardımcısı', 'Atölye Ustası', 'Atölye Çırak']))
                  <button type="button" class="btn btn-primary btn-sm kullanici_teknisyenPrimGoster" data-toggle="modal"
                    data-target="#kullaniciPrimModal"> Primlerim </button>
                @endif
              </div>

              <div class="searchWrap float-end kullanici_teknisyenfiltre">
                <div class="btn-group" id="servisFilterDropdownContainer">
                  @if(auth()->user()->can('Tüm Servisleri Görebilir'))
                    <button class="btn btn-dark btn-sm dropdown-toggle filtrele kullanici_teknisyenfiltre" type="button"
                      data-bs-toggle="dropdown" aria-expanded="false"> Filtrele <i class="mdi mdi-chevron-down"></i>
                    </button>
                  @endif

                  <div class="dropdown-menu servisDrop">
                    <!-- Mobil ve Masaüstü için Esnek Grid Kapsayıcısı -->
                    <div class="row">

                      <!-- Her bir filtre elemanı mobil için 6, masaüstü için 12 birim yer kaplar -->
                      <div class="mt-1 item col-12 col-lg-12">
                        <div class="row">
                          <label class="col-4 custom-p col-sm-4">Cihaz Marka:</label>
                          <div class="col-8 custom-p custom-p-m col-sm-8">
                            <select name="device_brands" id="device_brands" class="form-select">
                              <option value="">Hepsi</option>
                              @foreach($device_brands as $brand)
                                <option value="{{$brand->id}}">{{$brand->marka}}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="item col-12 col-lg-12">
                        <div class="row">
                          <label class=" col-4 custom-p col-sm-4">Cihaz Türü:</label>
                          <div class="col-8 custom-p custom-p-m col-sm-8">
                            <select name="device_types" id="device_types" class="form-select">
                              <option value="">Hepsi</option>
                              @foreach($device_types as $type)
                                <option value="{{$type->id}}">{{$type->cihaz}}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="item col-12 col-lg-12">
                        <div class="row">
                          <label class="col-4 custom-p col-sm-4">Servis Durumu:</label>
                          <div class="col-8 custom-p custom-p-m col-sm-8">
                            <select name="stages" id="stages" class="form-select">
                              <option value="">Hepsi</option>
                              @foreach($service_stages as $stage)
                                <option value="{{$stage->id}}">{{$stage->asama}}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="item col-12 col-lg-12">
                        <div class="row">
                          <label class="col-4 custom-p col-sm-4">Servis Kaynağı:</label>
                          <div class="col-8 custom-p custom-p-m col-sm-8">
                            <select name="service_resource" id="service_resource" class="form-select">
                              <option value="">Hepsi</option>
                              @foreach($service_resources as $resource)
                                <option value="{{$resource->id}}">{{$resource->kaynak}}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="item col-12 col-lg-12">
                        <div class="row">
                          <label class="col-4 custom-p col-sm-4">İl:</label>
                          <div class="col-8 custom-p custom-p-m col-sm-8">
                            <select name="il" id="country2" class="form-control form-select"
                              style="width:100%!important;">
                              <option value="" selected>-Seçiniz-</option>
                              @foreach($states as $item)
                                <option value="{{ $item->id }}">{{ $item->name}}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="item col-12 col-lg-12">
                        <div class="row">
                          <label class="col-4 custom-p col-sm-4">İlçe:</label>
                          <div class="col-8 custom-p custom-p-m col-sm-8">
                            <select name="ilce" id="city2" class="form-control form-select" style="width:100%!important;">
                              <option value="" selected disabled>-Seçiniz-</option>
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="item col-12">
                        <div class="row">
                          <label class="col-4 custom-p col-sm-4">Tarih Aralığı:</label>
                          <div class="col-8 custom-p custom-p-m col-sm-8">
                            <input id="daterange" class="tarih-araligi w-100">
                            <div class="tarihAraligi mt-2 mb-2">
                              <button id="today" class="btn btn-sm btn-secondary">Bugün</button>
                              <button id="yesterday" class="btn btn-sm btn-secondary">Dün</button>
                              <button id="lastWeek" class="btn btn-sm btn-secondary">Son 7 Gün</button>
                              <button id="lastMonth" class="btn btn-sm btn-secondary">Son 1 Ay</button>
                              <button id="lastYear" class="btn btn-sm btn-secondary">Son 1 Yıl</button>
                          
                            </div>
                          </div>
                        </div>
                      </div>

                    </div> <!-- row kapanışı -->
                  </div>
                </div><!-- /btn-group -->
              </div> 
              
              <!-- Servisler Tablosu -->
              <div id="servicesTableSection">
                <table id="datatableService" class="table table-bordered dt-responsive nowrap"
                  style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                  <thead class="title">
                    <tr>
                      <th class="custom-table-size-id" style="width: 10px">ID</th>
                      <th style="width: 10px">Tarih</th>
                      <th style="width: 250px">Müşteri</th>
                      <th style="width: 250px">Cihaz</th>
                      <th class="custom-table-size" style="width: 205px !important">Servis Durumu</th>
                      <th class="custom-table-size-edit" data-priority="1" style="width: 68px;">Düzenle</th>
                      <th style="max-width: 40px!important;">Kapat</th>
                      <th class="custom-table-size-select" style="width: 50px; text-align: center;">Seç</th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>
              
              {{-- Burası raporlar modalında gelen çağrıları filtrelerken oluşturulan gelen çağrılar tablosu --}}
              <div id="incomingCallsSection" class="" style="display: none;">
                <table id="incomingCallsTable" class="table table-striped table-bordered dt-responsive nowrap"
                  style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                  <thead class="title">
                    <tr>
                      <th>ID</th>
                      <th>Tarih</th>
                      <th>Telefon</th>
                      <th>Marka</th>
                      <th>Açıklama</th>
                      <th>Personel</th>
                      <th>İşlemler</th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </div> <!-- end col -->
      </div> <!-- end row -->
    </div>
  </div>
  <!-- add modal content -->
  <div id="addServiceModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addCustomerLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg ekle-modal" style="width: 930px;">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title" id="addCustomerLabel">Servis Ekle</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->

  <!-- Yeni Marka Ekle Modal -->
<div class="modal fade" id="addDeviceBrandModal" tabindex="-1" aria-hidden="true" style="background: rgba(0, 0, 0, 0.7); padding-top:100px;">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cihaz Markası Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addDeviceBrandForm" action="{{ route('store.brand.ajax', $firma->id) }}">
                    @csrf
                    <input type="hidden" name="device_brand_form_token" id="deviceBrandFormToken" value="">
                    <div class="row mb-3">
                        <label class="col-sm-4">Marka:<span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input name="marka" class="form-control" type="text" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 text-end">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                            <input type="submit" class="btn btn-info btn-sm" value="Kaydet">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Yeni Cihaz Türü Ekle Modal -->
<div class="modal fade" id="addDeviceTypeServiceModal" tabindex="-1" aria-hidden="true" style="background: rgba(0, 0, 0, 0.7); padding-top:100px;">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cihaz Türü Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addDeviceTypeServiceForm" action="{{ route('store.device.type.ajax', $firma->id) }}">
                    @csrf
                    <input type="hidden" name="device_type_service_form_token" id="deviceTypeServiceFormToken" value="">
                    <div class="row mb-3">
                        <label class="col-sm-4">Cihaz:<span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input name="cihaz" class="form-control" type="text" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 text-end">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                            <input type="submit" class="btn btn-info btn-sm" value="Kaydet">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Yeni Servis Kaynağı Ekle Modal -->
<div class="modal fade" id="addServiceResourceModal" tabindex="-1" aria-hidden="true" style="background: rgba(0, 0, 0, 0.7); padding-top:100px;">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Servis Kaynağı Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addServiceResourceForm" action="{{ route('store.service.resource.ajax', $firma->id) }}">
                    @csrf
                    <input type="hidden" name="service_resource_form_token" id="serviceResourceFormToken" value="">
                    <div class="row mb-3">
                        <label class="col-sm-4">Kaynak:<span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input name="kaynak" id="kaynakInput" class="form-control" type="text" maxlength="18"  required><small class="text-muted">
                                <span id="kaynakCounter">0</span> / 18
                            </small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 text-end">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                            <input type="submit" class="btn btn-info btn-sm" value="Kaydet">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
  <!-- edit modal content -->
  <div id="editServiceDescModal" class="modal fade" data-bs-backdrop="static" tabindex='-1'> {{--data-bs-backdrop="static"
    data-bs-keyboard="false" modalın hemen kapanmaması için bunu eklemiştim. Eğer eklenmesi gerekirse aria-hidden in
    yanına ekleyebilirsin--}}
    <div class="modal-dialog modal-lg service-modal" style="width: 980px;">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title" id="editCustomerLabel">Servis Bilgileri Düzenle</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
  <!-- edit modal content -->
  <div id="editServiceCustomerModal" class="modal fade" style="padding-top: 50px;background: rgba(0, 0, 0, 0.50);">
    <div class="modal-dialog custom-size">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title" id="editCustomerLabel">Servis Müşteri Düzenle</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
  <div id="editServiceNotModal" class="modal fade" style="padding-top: 50px;background: rgba(0, 0, 0, 0.50);">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title" id="editCustomerLabel">Müşteri Notu Düzenle</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
  <div id="editServicePlanModal" class="modal fade" style="padding-top: 50px;background: rgba(0, 0, 0, 0.50);">
    <div class="modal-dialog ">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title">Servis Plan Düzenle</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
  <!-- Anket Modalı -->
  <div class="modal fade" id="anketModal" aria-labelledby="anketModalLabel"
    style="padding-top: 50px;background: rgba(0, 0, 0, 0.50);">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-fade-title">Müşteri Anketi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
        </div>
        <div class="modal-body" style="max-height: 80vh; overflow-y: auto; overflow-x: hidden;" id="anketModalContent">
          <!-- Form buraya yüklenecek -->
        </div>
      </div>
    </div>
  </div>
  <div id="servisRaporlaModal" class="modal fade" style="padding-top: 50px;background: rgba(0, 0, 0, 0.50);">
    <div class="modal-dialog ">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title">Servis Raporları</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
  <div id="anketModal" class="modal fade" style="padding-top: 50px;background: rgba(0, 0, 0, 0.50);">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title">Anket Raporları</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
  <div id="primModal" class="modal fade" style="padding-top: 50px;background: rgba(0, 0, 0, 0.50);">
    <div class="modal-dialog modal-lg" style="max-width: 800px!important;">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title">Prim Hesaplama</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
  <div id="servisTopluPlanlaModal" class="modal fade" data-bs-backdrop="static" tabindex='-1'>
    <div class="modal-dialog modal-lg" style="max-width: 1000px!important;">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title">Toplu Servis Planlama</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
  <div id="teknisyenDepoModal" class="modal fade" style="padding-top: 50px;background: rgba(0, 0, 0, 0.50);">
    <div class="modal-dialog ">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title">Depo Stoklarım</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="padding: 5px!important;">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
  <div id="kullaniciPrimModal" class="modal fade" style="padding-top: 50px;background: rgba(0, 0, 0, 0.50);">
    <div class="modal-dialog " style="max-width: 800px!important;">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title">Primlerim</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="padding: 5px!important;">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
  <div id="gelenCagriModal" class="modal fade" data-bs-backdrop="static" tabindex='-1'>
    <div class="modal-dialog  custom-modal-width ">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title">Yeni Çağrı Ekle</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
  <!-- edit modal content -->
  <div id="editIncomingCallModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog custom-modal-width ">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title" id="myModalLabel">Çağrı Düzenle</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Yükleniyor...
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->


  <script type="text/javascript">
    $(document).ready(function () {
      $(".addService").click(function () {
        var firma_id = {{$firma->id}};
        $.ajax({
          url: "/" + firma_id + "/servis/ekle/"
        }).done(function (data) {
          if ($.trim(data) === "-1") {
            window.location.reload(true);
          } else {
            $('#addServiceModal').modal('show');
            $('#addServiceModal .modal-body').html(data);
          }
        });
      });
      $("#addServiceModal").on("hidden.bs.modal", function () {
        $('#addServiceModal .modal-body').html("");

      });
    });
  </script>
<script type="text/javascript">
  $(document).ready(function () {
    // Edit Service Modal - Button Click 
    $('#datatableService').on('click', '.serBilgiDuzenle', function (e) {
      var id = $(this).attr("data-bs-id");
      var firma_id = {{$firma->id}};
      $.ajax({
        url: "/" + firma_id + "/servis/duzenle/" + id
      }).done(function (data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#editServiceDescModal .modal-body').html(data);
          $('#editServiceDescModal').modal('show');
        }
      });
    });

    // Mobilde ve masaüstünde satırın boş alanlarına tıklayınca da açılsın
    $('#datatableService tbody').on('click', 'tr', function(e) {
      var $target = $(e.target);

      if ($target.closest('.delete-button').length > 0) {
    return;
  }

      // Sadece Kapat (6), Seç (7) butonlarına, switch'lere ve checkbox'lara tıklanmışsa engelle
      // Düzenle butonunu (5. kolon) ENGELLEME - kendi event'i çalışsın
      if ($target.closest('.serBilgiDuzenle').length > 0 ||  // Düzenle butonuna tıklandıysa, bu tr event'ini çalıştırma
          $target.closest('input[type="checkbox"]').length > 0 ||
          $target.closest('.servis-sonlandir-switch').length > 0 ||
          $target.closest('.servis-sonlanmis-switch').length > 0 ||
          $target.closest('td').index() === 6 ||  // Kapat kolonu
          $target.closest('td').index() === 7) {  // Seç kolonu
        return;
      }
      
      var id = $(this).find('.serBilgiDuzenle').first().attr('data-bs-id');
      
      if (id) {
        // 1. MODAL'I HEMEN AÇ (AJAX beklemeden)
        $('#editServiceDescModal').modal('show');
        
        // 2. AYNI ANDA AJAX BAŞLAT
        var firma_id = {{$firma->id}};
        $.ajax({
          url: "/" + firma_id + "/servis/duzenle/" + id
        }).done(function (data) {
          if ($.trim(data) === "-1") {
            window.location.reload(true);
          } else {
            $('#editServiceDescModal .modal-body').html(data);
          }
        });
      }
    });

    $("#editServiceDescModal").on("hidden.bs.modal", function () {
      $('#editServiceDescModal .modal-body').html("");
    });
  });
</script>
  <script>
    var getUrlParameter = function getUrlParameter(sParam) {
      var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;
      for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
          return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
      }
    };

    $(document).ready(function() {
    var mid = getUrlParameter('did');
    var firma_id = {{$firma->id}};
    if (mid) {
      $.ajax({
        url: "/" + firma_id + "/servis/duzenle/" + mid
      }).done(function (data) {
        if ($.trim(data) === "-1") {
          window.location.reload(true);
        } else {
          $('#editServiceDescModal .modal-body').html(data);
          $('#editServiceDescModal').modal('show');
        }
      });
    }
     });
  </script>
  <script type="text/javascript">
    $(document).ready(function () {
      $(".servisRaporlaModalBtn").click(function () {
        var firma_id = {{$firma->id}};
        $.ajax({
          url: "/" + firma_id + "/servis-rapor-modal/"
        }).done(function (data) {
          if ($.trim(data) === "-1") {
            window.location.reload(true);
          } else {
            $('#servisRaporlaModal').modal('show');
            $('#servisRaporlaModal .modal-body').html(data);
          }
        });
      });
      $("#servisRaporlaModal").on("hidden.bs.modal", function () {
        $('#servisRaporlaModal .modal-body').html("");

      });
    });
  </script>
  <script type="text/javascript">
    $(document).ready(function () {
      $(".anketModalBtn").click(function () {
        var firma_id = {{$firma->id}};
        $.ajax({
          url: "/" + firma_id + "/anket-rapor-modal/"
        }).done(function (data) {
          if ($.trim(data) === "-1") {
            window.location.reload(true);
          } else {
            $('#anketModal').modal('show');
            $('#anketModal .modal-body').html(data);
          }
        });
      });

      $("#anketModal").on("hidden.bs.modal", function () {
        $("#anketModal .modal-body").html("");
      });
    });
  </script>
  <script type="text/javascript">
    $(document).ready(function () {
      $(".primModalBtn").click(function () {
        var firma_id = {{$firma->id}};
        $.ajax({
          url: "/" + firma_id + "/prim/"
        }).done(function (data) {
          if ($.trim(data) === "-1") {
            window.location.reload(true);
          } else {
            $('#primModal').modal('show');
            $('#primModal .modal-body').html(data);
          }
        });
      });


    });
  </script>
  <script type="text/javascript">
    $(document).ready(function () {
      $(".servisPlanlaBtn").click(function () {
        var firma_id = {{$firma->id}};
        $.ajax({
          url: "/" + firma_id + "/servis-toplu-planlama/"
        }).done(function (data) {
          if ($.trim(data) === "-1") {
            window.location.reload(true);
          } else {
            $('#servisTopluPlanlaModal').modal('show');
            $('#servisTopluPlanlaModal .modal-body').html(data);
          }
        });
      });

      // Modal kapatma butonuna basıldığında sayfayı yenile
    $('#servisTopluPlanlaModal').on('hide.bs.modal', function (event) {
        // Eğer kapanan modal, doğrudan servisTopluPlanlaModal'ın kendisiyse
        // ve bu olay bir child modal tarafından bubble edilmiyorsa
        if (event.target === this) {
            window.location.reload(true);
        }
    });
    });
  </script>
  <script type="text/javascript">
    $(document).ready(function () {
      $(".teknisyenDepoGoster").click(function () {
        var firma_id = {{$firma->id}};
        var personel_id = {{auth()->user()->user_id}}
          $.ajax({
            url: "/" + firma_id + "/teknisyen-depo/" + personel_id
          }).done(function (data) {
            if ($.trim(data) === "-1") {
              window.location.reload(true);
            } else {
              $('#teknisyenDepoModal').modal('show');
              $('#teknisyenDepoModal .modal-body').html(data);
            }
          });
      });
    });
  </script>
  <script type="text/javascript">
    $(document).ready(function () {
      $(".kullaniciPrimGoster").click(function () {
        var firma_id = {{$firma->id}};
        $.ajax({
          url: "/" + firma_id + "/primlerim/"
        }).done(function (data) {
          if ($.trim(data) === "-1") {
            window.location.reload(true);
          } else {
            $('#kullaniciPrimModal').modal('show');
            $('#kullaniciPrimModal .modal-body').html(data);
          }
        });
      });
    });
  </script>
  <script type="text/javascript">
    $(document).ready(function () {
      $(".kullanici_teknisyenPrimGoster").click(function () {
        var firma_id = {{$firma->id}};
        $.ajax({
          url: "/" + firma_id + "/primlerim/"
        }).done(function (data) {
          if ($.trim(data) === "-1") {
            window.location.reload(true);
          } else {
            $('#kullaniciPrimModal').modal('show');
            $('#kullaniciPrimModal .modal-body').html(data);
          }
        });
      });
    });
  </script>
  <script type="text/javascript">
    $(document).ready(function () {
      $(".gelenCagriButon").click(function () {
        var firma_id = {{$firma->id}};
        $.ajax({
          url: "/" + firma_id + "/yeni-cagri-ekle/"
        }).done(function (data) {
          if ($.trim(data) === "-1") {
            window.location.reload(true);
          } else {
            $('#gelenCagriModal').modal('show');
            $('#gelenCagriModal .modal-body').html(data);
          }
        });
      });
    });
  </script>
  <script type="text/javascript">
    // Çağrı düzenleme modalı
    $(document).ready(function () {
      $('#incomingCallsSection').on('click', '.editIncomingCall', function (e) {
        var id = $(this).attr("data-bs-id");
        var firma_id = {{$firma->id}};
        $.ajax({
          url: "/" + firma_id + "/yeni-cagri-duzenle/" + id
        }).done(function (data) {
          if ($.trim(data) === "-1") {
            window.location.reload(true);
          } else {
            $('#editIncomingCallModal').modal('show');
            $('#editIncomingCallModal .modal-body').html(data);
          }
        });
      });
      $("#editIncomingCallModal").on("hidden.bs.modal", function () {
        $('#editIncomingCallModal .modal-body').html("");

      });

      // Çağrı silme işlemi
      $('#incomingCallsSection').on('click', '.deleteIncomingCall', function (e) {
        e.preventDefault();
        var id = $(this).attr("data-bs-id");
        var row = $(this).closest('tr');
        var firma_id = {{$firma->id}};
        if (confirm('Bu cihazı silmek istediğinize emin misiniz?')) {
          $.ajax({
            url: "/" + firma_id + "/yeni-cagri-sil/" + id,
            type: "DELETE",
            data: {
              "_token": "{{ csrf_token() }}", // CSRF koruması için token ekleyin
            },
            success: function (response) {
              if (response.success) {
                row.remove(); // Satırı tablodan kaldır
                alert('Gelen çağrı başarıyla silindi.');
              } else {
                alert('Gelen çağrı silinirken bir hata oluştu.');
              }
            },
            error: function (xhr) {
              alert('Gelen çağrı silinirken bir hata oluştu.');
            }
          });
        }
      });
    });
  </script>
  <script>
    $(document).ready(function () {
      let preventDropdownHide = false;
      // Dropdown'ın kapanma olayını dinliyoruz
      $('#servisFilterDropdownContainer').on('hide.bs.dropdown', function (e) {
        // Eğer bayrak 'true' ise, yani tıklama daterangepicker'dan geldiyse...
        if (preventDropdownHide) {
          e.preventDefault(); // Bootstrap'ın dropdown'ı kapatmasını engelle.
        }
        // Olay kontrol edildikten sonra bayrağı her zaman sıfırla ki bir sonraki normal tıklamada dropdown kapanabilsin.
        preventDropdownHide = false;
      });
      // daterangepicker'ın takvim arayüzü içindeki herhangi bir tıklamayı yakala
      $(document).on('mousedown', function (e) {
        // Eğer tıklama .daterangepicker sınıfına sahip bir elementin içindeyse...
        if ($(e.target).closest('.daterangepicker').length) {
          preventDropdownHide = true; // Bayrağı ayarla.
        }
      });
      // Dropdown içindeki daterangepicker input alanına tıklandığında bayrağı ayarla
      $('#servisFilterDropdownContainer').find('#daterange').on('focus mousedown', function () {
        preventDropdownHide = true;
      });
      // Dropdown içindeki tarih kısayol butonlarına tıklandığında bayrağı ayarla
      $('#servisFilterDropdownContainer').find('.tarihAraligi button').on('mousedown', function () {
        preventDropdownHide = true;
      });
      // daterangepicker "Uygula", "İptal" butonlarına basıldığında veya kapandığında bayrağı sıfırla.
      // Bu, daterangepicker ile işimiz bittikten sonra dropdown'ın normal şekilde kapanabilmesini sağlar.
      $('#daterange').on('apply.daterangepicker cancel.daterangepicker hide.daterangepicker', function () {
        preventDropdownHide = false;
      });


      // Tarih aralığı seçenekleri
      var lastYear = moment().subtract(1, 'year');
      var lastMonth = moment().subtract(1, 'month');
      var lastWeek = moment().subtract(7, 'days');
      var yesterday = moment().subtract(1, 'days');
      var today = moment();

      // Butonları oluştur ve tarih aralığını güncelle
      $('#lastYear').on('click', function () {
        activeFilters = {};
        activeFilterType = '';
        window.reportFilters.filters = {};
        window.reportFilters.filterType = '';
        $('#daterange').data('daterangepicker').setStartDate(lastYear);
        $('#daterange').data('daterangepicker').setEndDate(today);
        // Filtreleme fonksiyonunu çağır
        filterData();
      });

      $('#lastMonth').on('click', function () {
        activeFilters = {};
        activeFilterType = '';
        window.reportFilters.filters = {};
        window.reportFilters.filterType = '';
        $('#daterange').data('daterangepicker').setStartDate(lastMonth);
        $('#daterange').data('daterangepicker').setEndDate(today);
        // Filtreleme fonksiyonunu çağır
        filterData();
      });

      $('#lastWeek').on('click', function () {
        activeFilters = {};
        activeFilterType = '';
        window.reportFilters.filters = {};
        window.reportFilters.filterType = '';
        $('#daterange').data('daterangepicker').setStartDate(lastWeek);
        $('#daterange').data('daterangepicker').setEndDate(today);
        // Filtreleme fonksiyonunu çağır
        filterData();
      });

      $('#yesterday').on('click', function () {
        activeFilters = {};
        activeFilterType = '';
        window.reportFilters.filters = {};
        window.reportFilters.filterType = '';
        $('#daterange').data('daterangepicker').setStartDate(yesterday);
        $('#daterange').data('daterangepicker').setEndDate(yesterday);
        // Filtreleme fonksiyonunu çağır
        filterData();
      });

      $('#today').on('click', function () {
        activeFilters = {};
        activeFilterType = '';
        window.reportFilters.filters = {};
        window.reportFilters.filterType = '';
        $('#daterange').data('daterangepicker').setStartDate(today);
        $('#daterange').data('daterangepicker').setEndDate(today);
        // Filtreleme fonksiyonunu çağır
        filterData();
      });

      // Filtreleme fonksiyonu
      function filterData() {
        $('#datatableService').DataTable().draw();
      }
    });
  </script>
  <script>
    $(document).ready(function () {
      // var start_date = '01-01-2025';
      // var end_date = moment().add(1, 'day');

      //(Son 3 gün)
      var start_date = moment().subtract(2, 'days').startOf('day');
      var end_date = moment().endOf('day');

      $('#daterange').daterangepicker({
        startDate: start_date,
        endDate: end_date,
        locale: {
          format: 'DD-MM-YYYY',
          separator: ' - ',
          applyLabel: 'Uygula',
          cancelLabel: 'İptal',
          weekLabel: 'H',
          daysOfWeek: ['Pz', 'Pzt', 'Sal', 'Çrş', 'Prş', 'Cm', 'Cmt'],
          monthNames: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'],
          firstDay: 1
        }
      },

        function (start_date, end_date) {
          $('#daterange').html(start_date.format('DD-MM-YYYY') + '-' + end_date.format('DD-MM-YYYY'));
          table.draw();
        });

      //Operatör istatistikleride istenilen filtreye göre datatbale güncelleme
      var operator_id = getUrlParameter('operator_id');
      var opeator_istatistik_tarih1 = getUrlParameter('opeator_istatistik_tarih1');
      var opeator_istatistik_tarih2 = getUrlParameter('opeator_istatistik_tarih2');
      if (operator_id && opeator_istatistik_tarih1 && opeator_istatistik_tarih2) {
        $('#daterange').data('daterangepicker').setStartDate(moment(opeator_istatistik_tarih1));
        $('#daterange').data('daterangepicker').setEndDate(moment(opeator_istatistik_tarih2));
      }

      //Durum istatistikleride istenilen filtreye göre datatbale güncelleme
      var state_id = getUrlParameter('state_id');
      var state_istatistik_tarih1 = getUrlParameter('state_istatistik_tarih1');
      var state_istatistik_tarih2 = getUrlParameter('state_istatistik_tarih2');
      if (state_id && state_istatistik_tarih1 && state_istatistik_tarih2) {
        $('#daterange').data('daterangepicker').setStartDate(moment(state_istatistik_tarih1));
        $('#daterange').data('daterangepicker').setEndDate(moment(state_istatistik_tarih2));
      }

      //Aşama istatistikleride istenilen filtreye göre datatbale güncelleme
      var stage_id = getUrlParameter('stage_id');
      var stage_istatistik_tarih1 = getUrlParameter('stage_istatistik_tarih1');
      var stage_istatistik_tarih2 = getUrlParameter('stage_istatistik_tarih2');
      if (stage_id && state_istatistik_tarih1 && state_istatistik_tarih2) {
        $('#daterange').data('daterangepicker').setStartDate(moment(stage_istatistik_tarih1));
        $('#daterange').data('daterangepicker').setEndDate(moment(stage_istatistik_tarih2));
      }

      //İlçe istatistikleride istenilen filtreye göre datatbale güncelleme
      var ilceArama = getUrlParameter('ilceArama');
      var ilce_istatistik_tarih1 = getUrlParameter('ilce_istatistik_tarih1');
      var ilce_istatistik_tarih2 = getUrlParameter('ilce_istatistik_tarih2');
      if (ilceArama && ilce_istatistik_tarih1 && ilce_istatistik_tarih2) {
        $('#daterange').data('daterangepicker').setStartDate(moment(ilce_istatistik_tarih1));
        $('#daterange').data('daterangepicker').setEndDate(moment(ilce_istatistik_tarih2));
      }
      //Anket istatistikleride istenilen filtreye göre datatbale güncelleme
      var personel_id = getUrlParameter('personel_id');
      var deviceType = getUrlParameter('deviceType');
      var personel_istatistik_tarih1 = getUrlParameter('personel_istatistik_tarih1');
      var personel_istatistik_tarih2 = getUrlParameter('personel_istatistik_tarih2');
      if (personel_id && personel_istatistik_tarih1 && personel_istatistik_tarih2) {
        $('#daterange').data('daterangepicker').setStartDate(moment(personel_istatistik_tarih1));
        $('#daterange').data('daterangepicker').setEndDate(moment(personel_istatistik_tarih2));
      }
      // Dashboard istatistiklerinden gelen filtreyi kontrol et
      var dashboard_filter = getUrlParameter('dashboard_filter');
      var dashboard_istatistik_tarih1 = getUrlParameter('dashboard_istatistik_tarih1');
      var dashboard_istatistik_tarih2 = getUrlParameter('dashboard_istatistik_tarih2');
      var status_group = getUrlParameter('status_group');
      if (dashboard_filter && dashboard_istatistik_tarih1 && dashboard_istatistik_tarih2) {
        $('#daterange').data('daterangepicker').setStartDate(moment(dashboard_istatistik_tarih1));
        $('#daterange').data('daterangepicker').setEndDate(moment(dashboard_istatistik_tarih2));
      }


      var firma_id = {{$firma->id}};
      // let activeFilters = {};
      // let activeFilterType = '';

      var activeFilters = {};  // let yerine var!
      var activeFilterType = '';  // let yerine var!

      // Window'a da bağla
      window.reportFilters = {
          filters: {},
          filterType: ''
      };

      

      var table = $('#datatableService').DataTable({
        processing: true,
        serverSide: true,
        ordering: true,
        deferRender: true,
        language: {
          paginate: {
            previous: "<i class='mdi mdi-chevron-left'>",
            next: "<i class='mdi mdi-chevron-right'>"
          }
        },

        ajax: {
          url: "{{ route('all.services', $firma->id) }}",
          type: 'GET',
          data: function (data) {
            //Window'dan al
            var currentFilters = window.reportFilters.filters;
            var currentFilterType = window.reportFilters.filterType;

            data.search = $('input[type="search"]').val();
            data.device_brands = $('#device_brands').val();
            data.device_types = $('#device_types').val();
            data.stages = $('#stages').val();
            data.service_resource = $('#service_resource').val();
            data.il = $('#country2').val();
            data.ilce = $('#city2').val();
            data.from_date = $('#daterange').data('daterangepicker').startDate.format('YYYY-MM-DD');
            data.to_date = $('#daterange').data('daterangepicker').endDate.format('YYYY-MM-DD');

            //Raporlama filtreleri
            // Window'dan oku
            data.filters = currentFilters;
            data.filterType = currentFilterType;

            //Operatör istatistikleri filtreleme için URL parametresi aktarma
            data.operator_id = getUrlParameter('operator_id');
            data.opeator_istatistik_tarih1 = getUrlParameter('opeator_istatistik_tarih1');
            data.opeator_istatistik_tarih2 = getUrlParameter('opeator_istatistik_tarih2');

            //Durum istatistikleri filtreleme için URL parametresi aktarma
            data.state_id = getUrlParameter('state_id');
            data.state_istatistik_tarih1 = getUrlParameter('state_istatistik_tarih1');
            data.state_istatistik_tarih2 = getUrlParameter('state_istatistik_tarih2');

            //Aşama istatistikleri filtreleme için URL parametresi aktarma
            data.stage_id = getUrlParameter('stage_id');
            data.stage_istatistik_tarih1 = getUrlParameter('stage_istatistik_tarih1');
            data.stage_istatistik_tarih2 = getUrlParameter('stage_istatistik_tarih2');

            //İlçe istatistikleri filtreleme için URL parametresi aktarma
            data.ilceArama = getUrlParameter('ilceArama');
            data.ilce_istatistik_tarih1 = getUrlParameter('ilce_istatistik_tarih1');
            data.ilce_istatistik_tarih1 = getUrlParameter('ilce_istatistik_tarih2');

            //Anket istatistikleri filtreleme için URL parametresi aktarma
            data.personel_id = getUrlParameter('personel_id');
            data.deviceType = getUrlParameter('deviceType');
            data.personel_istatistik_tarih1 = getUrlParameter('personel_istatistik_tarih1');
            data.personel_istatistik_tarih2 = getUrlParameter('personel_istatistik_tarih2');
            // Dashboard filtreleri
            data.dashboard_filter = getUrlParameter('dashboard_filter');
            data.status_group = getUrlParameter('status_group');
            data.dashboard_istatistik_tarih1 = getUrlParameter('dashboard_istatistik_tarih1');
            data.dashboard_istatistik_tarih2 = getUrlParameter('dashboard_istatistik_tarih2');


          }
        },
        'columns': [
          { data: 'id', name: 'id', orderable: true },
          { data: 'created_at', name: 'created_at', orderable: true },
          { data: 'm_adi', name: 'm_adi', orderable: false },
          { data: 'cihaz', name: 'cihaz', orderable: false },
          { data: 'asama_id', name: 'durum', orderable: false },
          { data: 'action', name: 'action', orderable: false, searchable: false },
          { data: 'sonlandir_action', name: 'sonlandir_action', orderable: false, searchable: false },
          { data: 'sec_checkbox', name: 'sec_checkbox', orderable: false, searchable: false }
        ],
        
        createdRow: function (row, data) {
          // 5. sütundaki <strong> içeriği (0‑bazlı => 4. index)
          const asama = $('td:eq(4) strong', row).text().trim();

          // Veritabanından gelen özel renk (örneğin '#f0f0f0')
          const dbRenk = data.asamalar?.asama_renk || null;

          /** Varsayılan Durum → Renk eşlemesi */
          const varsayilanRenkHaritasi = {
            'Şikayetçi': '#e96464',   // kırmızımsı
            'Yeni Servisler': '#87ff87', // yeşil
            'Tekrar Aranacak': '#f2ff2a',// sarı
            'Parça Takmak İçin Teknisyen Yönlendir': '#62daff' // mavi
          };

          // Öncelik: veritabanındaki renk varsa onu kullan, yoksa varsayılana bak
          const arkaplanRenk = dbRenk?.trim() || varsayilanRenkHaritasi[asama];

          if (arkaplanRenk) {
            //eski renklendirme
            //$(row).css('background-color', arkaplanRenk);

           // Tüm hücrelere uygula, ancak son üç sütun hariç (Düzenle, Kapat, Seç)
           $('td', row).not(':last').not(':nth-last-child(2)').not(':nth-last-child(3)').css('background-color', arkaplanRenk);
          }

          // Uzun metin sarması gereken sütunlara sınıf ekleyelim
          $('td', row).eq(5).addClass('tdRowWrap');
          $('td', row).eq(6).addClass('tdRowWrap');
        },
        drawCallback: function () {
          $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
        },
        order: [[0, 'desc']],
        "columnDefs": [{
          "targets": 0,
          "className": "gizli"
        }],
        "oLanguage": {
          "sDecimal": ",",
          "sEmptyTable": "Tabloda herhangi bir veri mevcut değil",
          "sInfo": "Servis Sayısı: _TOTAL_",
          "sInfoEmpty": "Kayıt yok",
          "sInfoFiltered": "",
          "sInfoPostFix": "",
          "sInfoThousands": ".",
          "sLengthMenu": "_MENU_",
          "sLoadingRecords": "Yükleniyor...",
          "sProcessing": "İşleniyor...",
          "sSearch": "",
          "sZeroRecords": "Eşleşen kayıt bulunamadı",
          "oPaginate": {
            "sFirst": "İlk",
            "sLast": "Son",
            "sNext": '<i class="fas fa-angle-double-right"></i>',
            "sPrevious": '<i class="fas fa-angle-double-left"></i>'
          },
          "oAria": {
            "sSortAscending": ": artan sütun sıralamasını aktifleştir",
            "sSortDescending": ": azalan sütun sıralamasını aktifleştir"
          },
          "select": {
            "rows": {
              "_": "%d kayıt seçildi",
              "0": "",
              "1": "1 kayıt seçildi"
            }
          }
        },
        dom: 'B<"top"f>rt<"bottom"i<"float-end"lp>><"clear">',
        buttons: [{
        extend: 'print',
        text: 'Yazdır',
        autoPrint: true,
        exportOptions: {
          columns: [0, 1, 2, 3, 4],
          format: {
            body: function (data, row, column, node) {
              if (data === null || data === undefined) {
                return '';
              }
              
              if (typeof data === 'object') {
                data = $(data).text();
              }
              
              data = String(data);
              
              // Etiketleri temizle
              data = data.replace(/ID\s*:/gi, '');
              data = data.replace(/Tarih\s*:/gi, '');
              data = data.replace(/Müşteri\s*:/gi, '');
              data = data.replace(/Cihaz\s*:/gi, '');
              data = data.replace(/S. Durumu:/gi, '');
              data = data.replace(/Servis\s+Durumu\s*:/gi, '');
              
              // Satır sonları ve boşlukları temizle
              data = data.replace(/\n/g, ' ');
              data = data.replace(/\s+/g, ' ');
              
              return data.trim();
            }
          }
        },
        customize: function (win) {
          $(win.document.head).find('style, link').remove();
          
          $(win.document.head).append(
            '<style>' +
            '.print-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px;}' +
            '.print-title { text-align: left; font-size: 18px; font-weight: bold; margin-bottom: 13px; }' +
            'table { width: 100%; border-collapse: collapse; }' +
            'table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; color: #000 !important; }' +
            'table thead { display: table-header-group !important; }' +
            'table tbody { display: table-row-group !important; }' +
            'table tbody td * { color: #000 !important; font-weight: normal !important; }' +
            'table tbody td span { color: #000 !important; background-color: transparent !important; font-weight: normal !important; }' +
            'table tbody td { font-weight: normal !important; background-color: white !important; }' + // Arkaplan renklerini temizle
            'table tbody td strong { font-weight: normal !important; }' + // Bold'ları kaldır
            'a, a:link, a:visited, a:hover, a:active { color: #000 !important; text-decoration: none !important; }' +
            '.print-footer { margin-top: 15px; text-align: left; border-top: 1px solid #ddd; padding-top: 10px; }' +
            '.page-number-bottom { text-align: center; margin-top: 30px; font-size: 14px; color: #666; font-weight: bold; }' +
            '@page { margin: 5mm; }' +
            '</style>'
          );
          
          var printDate = moment().format('DD.MM.YYYY HH:mm');
          var totalRecords = table.page.info().recordsDisplay;
          var firmaAdi = '{{ $firma->firma_adi ?? "Firma Adı" }}';
          
          $(win.document.body).find('h1').remove();
          
          // Inline style'ları temizle, font-weight'i normal yap VE arkaplan renklerini kaldır
          $(win.document.body).find('table tbody td').each(function() {
            $(this).find('*').removeAttr('style').css('font-weight', 'normal');
            $(this).css({
              'font-weight': 'normal',
              'background-color': 'white'  // Tüm arkaplan renklerini beyaz yap
            });
          });
          
          var header = '<div class="print-header">' +
                      '  <span>' + printDate + '</span>' +
                      '  <span>' + firmaAdi.toUpperCase() + '</span>' +
                      '</div>';
          $(win.document.body).prepend(header);
          
          var title = '<div class="print-title">Servisler</div>';
          $(win.document.body).find('table').before(title);
          
          var footer = '<div class="print-footer">' +
                      '  <span>Listelenen Servis Sayısı: ' + totalRecords + ' - Tarih: ' + moment().format('DD/MM/YYYY') + '</span>' +
                      '</div>';
          $(win.document.body).find('table').after(footer);
          
          var pageInfo = '<div class="page-number-bottom">1/1</div>';
          $(win.document.body).append(pageInfo);
        }
      }],
        "lengthMenu": [[25, 50, 100, -1], [25, 50, 100, "Tümü"]],
        "preDrawCallback": function(settings) {
      // İlk çizimden önce tabloyu göster
      $('#datatableService').show();
    },
        "initComplete": function (settings, json) {
          // 1. Gerekli ana elemanları seçiyoruz
          var topContainer = $('#datatableService_wrapper .top');
          var searchContainer = $('#datatableService_filter');
          var searchInput = searchContainer.find('input');
          // Filtre butonu için doğru seçiciyi kullandığınızdan emin olun
          var filterWrapper = $('.searchWrap.kullanici_teknisyenfiltre');

          // 2. Arama kutusu ve filtre butonu için yeni bir flexbox kapsayıcısı oluşturuyoruz
          // Bu kapsayıcı, içindeki elemanları yan yana dizer ve tüm genişliği kaplar.
          var flexContainer = $('<div class="d-flex align-items-center w-100"></div>');

          // 3. DataTables'in varsayılan "Search:" metnini kaldırıyoruz.
          searchContainer.find('label').contents().filter(function () {
            return this.nodeType == 3; // Sadece text node'larını hedefler
          }).remove();

          // Placeholder metnini ayarlıyoruz
          searchInput.attr('placeholder', 'Servis Ara...');

          // 4. Arama kutusunun div'ine esneklik kazandırıyoruz.
          // flex-grow-1: "Mevcut olan tüm boş alanı sen doldur" demektir.
          // me-2: Filtre butonu ile arasına sağdan küçük bir boşluk bırakır.
          searchContainer.addClass('flex-grow-1 me-1');

          // Arama input'unun, kendi kapsayıcısı içinde tam genişlikte olmasını sağlıyoruz.
          searchInput.addClass('w-100');

          // 5. Hazırladığımız elemanları yeni flexbox kapsayıcısına ekliyoruz.
          flexContainer.append(searchContainer); // Önce arama kutusu
          flexContainer.append(filterWrapper);   // Sonra filtre butonu

          // 6. Orijinal 'top' container'ını temizleyip, yeni ve düzenli yapımızı içine yerleştiriyoruz.
          topContainer.empty().append(flexContainer);
          $('#datatableService').fadeIn(300);
          // 7. Son olarak, işlem bittiğinde filtre butonunu görünür hale getiriyoruz.
          filterWrapper.css({ visibility: 'visible', opacity: 1 });
        }
      });


      // Yazdır butonu click event'i
      $('.printServices').on('click', function(e) {
        e.preventDefault();
        table.button('.buttons-print').trigger();
      });

      $('#device_brands').change(function () {
        table.draw();
      });

      $('#device_types').change(function () {
        table.draw();
      });

      $('#stages').change(function () {
        table.draw();
      });

      $('#service_resource').change(function () {
        table.draw();
      });

      $('#country2').change(function () {
        table.draw();
      });

      $('#city2').change(function () {
        table.draw();
      });

      /* ---------- FORM SUBMIT HANDLER'LARI ---------- */
      function formToObj($form) {
        return $form.serializeArray().reduce((acc, f) => {
          acc[f.name] = f.value;
          return acc;
        }, {});
      }

      $(document).on('submit', '#operatorArama', function (e) {
        e.preventDefault();
        activeFilterType = 'operator';
        activeFilters = formToObj($(this)); //form verilerini objeye çevir
          
        // Window'a da yaz
        window.reportFilters.filterType = 'operator';
        window.reportFilters.filters = formToObj($(this));

        table.draw(); // datatable’ı güncelle
        $('#servisRaporlaModal').modal('hide');
      });

      $(document).on('submit', '#yapilanAnketler', function (e) {
        e.preventDefault();
        activeFilterType = 'yapilananketler';
        activeFilters = formToObj($(this)); //form verilerini objeye çevir
          
        // Window'a da yaz
        window.reportFilters.filterType = 'yapilananketler';
        window.reportFilters.filters = formToObj($(this));
        
        table.draw(); // datatable’ı güncelle
        $('#anketModal').modal('hide');
      });

      $(document).on('submit', '#yapilmayanAnketler', function (e) {
        e.preventDefault();
        activeFilterType = 'yapilmayanAnketler';
        activeFilters = formToObj($(this)); //form verilerini objeye çevir

        // Window'a da yaz
        window.reportFilters.filterType = 'yapilmayanAnketler';
        window.reportFilters.filters = formToObj($(this));

        table.draw(); // datatable’ı güncelle
        $('#anketModal').modal('hide');
      });

      $(document).on('submit', '#teknisyenArama', function (e) {
        e.preventDefault();
        activeFilterType = 'teknisyen';
        activeFilters = formToObj($(this));

        // Window'a da yaz
        window.reportFilters.filterType = 'teknisyen';
        window.reportFilters.filters = formToObj($(this));

        table.draw(); // datatable’ı güncelle
        $('#servisRaporlaModal').modal('hide');
      });

      $(document).on('submit', '#urunSatisArama', function (e) {
        e.preventDefault();

        let tarih1 = $('.satis_tarih1').val();
        let tarih2 = $('.satis_tarih2').val();

        let postData = {
          filterType: 'urunSatis',
          filters: {
            tarih1: tarih1,
            tarih2: tarih2
          }
        };

        activeFilterType = 'urunSatis';
        activeFilters = postData.filters;

        // Window'a da yaz
        window.reportFilters.filterType = 'urunSatis';
        window.reportFilters.filters = postData.filters;

        table.draw(); // DataTable yeniden yükle

        $('#servisRaporlaModal').modal('hide');
      });

      $(document).on('submit', '#bayiArama', function (e) {
        e.preventDefault();

        let tarih1 = $('.bayi_tarih1').val();
        let tarih2 = $('.bayi_tarih2').val();

        let postData = {
          filterType: 'bayiArama',
          filters: {
            bayi_tarih1: tarih1,
            bayi_tarih2: tarih2
          }
        };

        activeFilterType = 'bayiArama';
        activeFilters = postData.filters;

        // Window'a da yaz
        window.reportFilters.filterType = 'bayiArama';
        window.reportFilters.filters = postData.filters;

        table.draw(); // DataTable yeniden yükle
        $('#servisRaporlaModal').modal('hide');
      });

      $(document).on('submit', '#acilArama', function (e) {
        e.preventDefault();

        let tarih1 = $('.acil_tarih1').val();
        let tarih2 = $('.acil_tarih2').val();

        let postData = {
          filterType: 'acilArama',
          filters: {
            acil_tarih1: tarih1,
            acil_tarih2: tarih2
          }
        };

        activeFilterType = 'acilArama';
        activeFilters = postData.filters;

        // Window'a da yaz
        window.reportFilters.filterType = 'acilArama';
        window.reportFilters.filters = postData.filters;

        table.draw(); // DataTable yeniden yükle
        $('#servisRaporlaModal').modal('hide');
      });


    });
  </script>
  <script>
    // Servisi Sonlandır radio button handler'ı
    $(document).ready(function () {
      $('#datatableService').on('click', '.servis-sonlandir-switch', function (e) {
        // Switch'in hemen durum değiştirmesini engelliyoruz.
        e.preventDefault();

        var aSwitch = $(this); // Tıklanan switch elemanı

        // Eğer switch zaten pasifse (örn: işlem sırasında) hiçbir şey yapma.
        if (aSwitch.is(':disabled')) {
          return;
        }

        // Gerekli verileri data-* etiketlerinden alıyoruz.
        var servisId = aSwitch.data('servis-id');
        var gelenIslemId = aSwitch.data('gelen-islem-id');
        var gidenIslemId = aSwitch.data('giden-islem-id');
        var firmaId = '{{ $firma->id }}';

        // Kullanıcıdan onay alıyoruz.
        if (confirm('Bu servisi sonlandırmak istediğinizden emin misiniz?')) {

          // İşlem sırasında switch'i pasif hale getiriyoruz.
          aSwitch.prop('disabled', true);

          $.ajax({
            url: `/${firmaId}/servis-plan-kaydet`,
            type: 'POST',
            data: {
              _token: '{{ csrf_token() }}',
              servis: servisId,
              gelenIslem: JSON.stringify({ id: gelenIslemId }),
              gidenIslem: gidenIslemId
            },
            success: function (response) {
              if (response.status === 'success') {
                // İşlem başarılı olursa DataTables'ı yeniden çizdiriyoruz.
                // Tablo yenilendiğinde switch, PHP tarafında doğru (checked ve disabled) halde gelecektir.
                $('#datatableService').DataTable().ajax.reload(null, false);
              } else {
                alert('Hata: ' + response.message);
                // Hata durumunda switch'i tekrar aktif hale getiriyoruz.
                aSwitch.prop('disabled', false);
              }
            },
            error: function (xhr) {
              alert('Servis sonlandırılırken bir sunucu hatası oluştu.');
              console.error('AJAX Hatası:', xhr.responseText);
              // Hata durumunda switch'i tekrar aktif hale getiriyoruz.
              aSwitch.prop('disabled', false);
            }
          });

        }
        // Kullanıcı onay vermezse hiçbir şey yapmıyoruz. 
        // e.preventDefault() sayesinde switch kapalı kalmaya devam eder.
      });
    });
  </script>
  <script>
    // Raporlar modalında gelen çağrıları filtreleme butonuna bastığımızda gelecek datatable ı getiren script. Bunu çalıştırırken servisler tablosu kısmını gizleyerek gelen çağrılar datatable ını görünür yapıyoruz.
    $(document).on('submit', '#gelenCagriArama', function (e) {
      e.preventDefault();

      let personel = $('select[name="cagri_pers"]').val();
      let marka = $('select[name="cagri_marka"]').val();
      let kaynak = $('select[name="cagri_kaynak"]').val();
      let tarih1 = $('.cagri_tarih1').val();
      let tarih2 = $('.cagri_tarih2').val();

      // Servisler tablosunu gizle
      $('#servicesTableSection').hide();

      // Gelen çağrılar tablosunu göster
      $('#incomingCallsSection').show();

      // DataTable varsa destroy et
      if ($.fn.DataTable.isDataTable('#incomingCallsTable')) {
        $('#incomingCallsTable').DataTable().destroy();
      }

      // Yeni DataTable oluştur
      var incomingCallsTable = $('#incomingCallsTable').DataTable({
        processing: true,
        serverSide: true,
        ordering: true,
        language: {
          paginate: {
            previous: "<i class='mdi mdi-chevron-left'>",
            next: "<i class='mdi mdi-chevron-right'>"
          }
        },
        ajax: {
          url: "{{ route('gelen-cagrilar.datatable', $firma->id) }}", // Bu route'u oluşturmanız gerekecek
          type: 'GET',
          data: {
            personel: personel,
            marka: marka,
            kaynak: kaynak,
            tarih1: tarih1,
            tarih2: tarih2
          }
        },
        'columns': [
          { data: 'id', name: 'id', orderable: true },
          { data: 'created_at', name: 'created_at', orderable: true },
          { data: 'telefon', name: 'telefon', orderable: true },
          { data: 'marka', name: 'marka', orderable: true },
          { data: 'aciklama', name: 'aciklama', orderable: true },
          { data: 'personel', name: 'personel', orderable: true },
          { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        drawCallback: function () {
          $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
        },
        order: [[0, 'desc']],
        "columnDefs": [{
          "targets": 0,
          "className": "gizli"
        }],
        "oLanguage": {
          "sDecimal": ",",
          "sEmptyTable": "Tabloda herhangi bir veri mevcut değil",
          "sInfo": "Çağrı Sayısı: _TOTAL_",
          "sInfoEmpty": "Kayıt yok",
          "sInfoFiltered": "",
          "sInfoPostFix": "",
          "sInfoThousands": ".",
          "sLengthMenu": "_MENU_",
          "sLoadingRecords": "Yükleniyor...",
          "sProcessing": "İşleniyor...",
          "sSearch": "Çağrı Ara:",
          "sZeroRecords": "Eşleşen kayıt bulunamadı",
          "oPaginate": {
            "sFirst": "İlk",
            "sLast": "Son",
            "sNext": '<i class="fas fa-angle-double-right"></i>',
            "sPrevious": '<i class="fas fa-angle-double-left"></i>'
          },
          "oAria": {
            "sSortAscending": ": artan sütun sıralamasını aktifleştir",
            "sSortDescending": ": azalan sütun sıralamasını aktifleştir"
          },
          "select": {
            "rows": {
              "_": "%d kayıt seçildi",
              "0": "",
              "1": "1 kayıt seçildi"
            }
          }
        },
        dom: '<"top"f>rt<"bottom"i<"float-end"lp>><"clear">',
        "lengthMenu": [[25, 50, 100, -1], [25, 50, 100, "Tümü"]],
      });

      $('#servisRaporlaModal').modal('hide');
    });
  </script>
  <script>
    $(document).ready(function () {
      // Ülke seçildiğinde şehirleri getir
      $("#country2").change(function () {
        var selectedCountryId = $(this).val();
        if (selectedCountryId) {
          loadCities(selectedCountryId);
        }
      });
      // Şehirleri yüklemek için kullanılan fonksiyon
      function loadCities(countryId) {
        var citySelect = $("#city2");
        citySelect.empty(); // Önceki seçenekleri temizle
        citySelect.append(new Option("Yükleniyor...", "")); // Kullanıcıya yükleniyor bilgisi ver

        // AJAX isteğiyle şehirleri al
        $.get("/get-states/" + countryId, function (data) {
          citySelect.empty(); // Yükleniyor mesajını temizle
          citySelect.append(new Option("-Seçiniz-", "")); // İlk boş seçeneği ekle
          $.each(data, function (index, city) {
            citySelect.append(new Option(city.ilceName, city.id));
          });
        }).fail(function () {
          citySelect.empty(); // Hata durumunda temizle
          citySelect.append(new Option("Unable to load cities", ""));
        });
      }
    });
  </script>
  <script>
    // Servisi Sonlandır radio button handler'ı
    $(document).ready(function () {
      // Aktif servisleri sonlandırmak için kullanılan switch
      $('#datatableService').on('click', '.servis-sonlandir-switch', function (e) {
        e.preventDefault();

        var aSwitch = $(this);

        if (aSwitch.is(':disabled')) {
          return;
        }

        var servisId = aSwitch.data('servis-id');
        var gelenIslemId = aSwitch.data('gelen-islem-id');
        var gidenIslemId = aSwitch.data('giden-islem-id');
        var firmaId = '{{ $firma->id }}';

        if (confirm('Bu servisi sonlandırmak istediğinizden emin misiniz?')) {
          aSwitch.prop('disabled', true);

          // Benzersiz token oluştur (form ile aynı şekilde)
          var formToken = Date.now() + '_' + Math.random().toString(36).substr(2, 9);

          $.ajax({
            url: `/${firmaId}/servis-plan-kaydet`,
            type: 'POST',
            data: {
              _token: '{{ csrf_token() }}',
              form_token: formToken,  // ← Controller'da beklenen isim: 'form_token'
              servis: servisId,
              gelenIslem: JSON.stringify({ id: gelenIslemId }),
              gidenIslem: gidenIslemId
            },
            success: function (response) {
              if (response.status === 'success') {
                $('#datatableService').DataTable().ajax.reload(null, false);
              } else {
                alert('Hata: ' + response.message);
                aSwitch.prop('disabled', false);
              }
            },
            error: function (xhr) {
              alert('Servis sonlandırılırken bir sunucu hatası oluştu.');
              console.error('AJAX Hatası:', xhr.responseText);
              aSwitch.prop('disabled', false);
            }
          });
        }
      });
      // Sonlandırılmış servislerin switch'ine tıklandığında modal açma
      $('#datatableService').on('click', '.servis-sonlanmis-switch', function (e) {
        // Checkbox'ın durumunu değiştirmesini engelle
        e.preventDefault();

        var servisId = $(this).data('bs-id');
        var firmaId = '{{ $firma->id }}';

        // Modal açma işlemi (serBilgiDuzenle)
        $.ajax({
          url: "/" + firmaId + "/servis/duzenle/" + servisId
        }).done(function (data) {
          if ($.trim(data) === "-1") {
            window.location.reload(true);
          } else {
            $('#editServiceDescModal .modal-body').html(data);
            $('#editServiceDescModal').modal('show');
          }
        });
      });
    });
  </script>

    <script>
    $(document).ready(function () {
      var dropdownContainer = $('#servisFilterDropdownContainer');
      var filterButton = dropdownContainer.find('.filtrele');
      dropdownContainer.on('show.bs.dropdown', function () {
        filterButton.html('Kapat <i class="mdi mdi-chevron-down"></i>');
      });
      dropdownContainer.on('hide.bs.dropdown', function () {
        filterButton.html('Filtrele <i class="mdi mdi-chevron-down"></i>');
      });
    });
  </script>

  <script>
$(document).ready(function() {
    // Karakter sayacı
    $('#kaynakInput').on('input', function() {
        var length = $(this).val().length;
        $('#kaynakCounter').text(length);
        
        // 16 karaktere ulaşınca kırmızı yap
        if (length >= 18) {
            $('#kaynakCounter').addClass('text-danger').removeClass('text-muted');
        } else {
            $('#kaynakCounter').removeClass('text-danger').addClass('text-muted');
        }
    });
});
</script>

@endsection