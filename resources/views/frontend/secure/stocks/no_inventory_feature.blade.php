@extends('frontend.secure.user_master')
@section('user')

<div class="page-content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card mt-5">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i class="ri-stock-fill" style="font-size: 80px; color: #f1b44c;"></i>
                        </div>
                        <h3 class="mb-3">Depo Modülü Gerekli</h3>
                        <p class="text-muted mb-4">
                            Depo modülünü kullanabilmek için planınızı yükseltmeniz gerekmektedir.
                            Profesyonel veya Kurumsal planlarımızdan birini seçerek stok yönetimi, 
                            ürün takibi ve envanter kontrolü özelliklerinden faydalanabilirsiniz.
                        </p>
                        
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <a href="{{ route('secure.home', $firma->id) }}" class="btn btn-secondary btn-sm">
                                {{-- <i class="ri-arrow-left-line me-2"></i> --}}
                                Ana Sayfaya Dön
                            </a>
                            <a href="{{ route('abonelikler', ['tenant_id' => $firma->id, 'feature' => 'inventory']) }}" 
                                class="btn btn-primary btn-sm" 
                                style="background:linear-gradient(135deg, #2d3748 0%, #4a5568 100%)">
                                    Planları Görüntüle
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Depo Modülü Özellikleri -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Depo Modülü ile Neler Yapabilirsiniz?</h5>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="ri-check-line text-success me-2"></i>
                                Stok takibi ve yönetimi
                            </li>
                            <li class="mb-2">
                                <i class="ri-check-line text-success me-2"></i>
                                Ürün kategorilendirme
                            </li>
                            <li class="mb-2">
                                <i class="ri-check-line text-success me-2"></i>
                                Raf yönetimi
                            </li>
                            <li class="mb-2">
                                <i class="ri-check-line text-success me-2"></i>
                                Stok giriş/çıkış takibi
                            </li>
                            <li class="mb-2">
                                <i class="ri-check-line text-success me-2"></i>
                                Barkod ile ürün yönetimi
                            </li>
                            <li class="mb-2">
                                <i class="ri-check-line text-success me-2"></i>
                                Detaylı stok raporları
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection