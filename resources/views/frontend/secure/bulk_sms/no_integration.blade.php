@extends('frontend.secure.user_master')
@section('user')

<div class="page-content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card mt-5">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i class="ri-message-3-line" style="font-size: 80px; color: #f1b44c;"></i>
                        </div>
                        <h3 class="mb-3">SMS Entegrasyonu Gerekli</h3>
                        <p class="text-muted mb-4">
                            Toplu SMS gönderebilmek için önce bir SMS entegrasyonu satın almanız gerekmektedir.
                            Entegrasyonlar pazarından istediğiniz SMS entegrasyonlarından birini satın alabilirsiniz.
                        </p>
                        <a href="{{ route('tenant.integrations.marketplace', [
                                'tenant_id' => $firma->id,
                                'category' => 'sms'
                            ]) }}" class="btn btn-primary btn-sm" style="background:linear-gradient(135deg, #2d3748 0%, #4a5568 100%)"> 
                            Entegrasyonlar Pazarına Git
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection