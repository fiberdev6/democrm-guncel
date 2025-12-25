@php 
$user = Auth::user();
@endphp
<header id="page-topbar">
  <div class="mobile-top-logo d-block d-md-none text-center py-2">
    @if(!empty($user->tenant->firma_adi))
        {{ $user->tenant->firma_adi }}
    @else 
        Yönetim Paneli
    @endif
</div>
    <div class="navbar-header">
      <div class="d-flex">
        <button type="button" class="btn btn-sm px-3 font-size-24 header-item" id="vertical-menu-btn">
          <i class="ri-menu-2-line align-middle"></i>
        </button>
        <div class="navbar-brand-box">
            @if($user->hasRole('Super Admin'))
                <a href="{{ route('super.admin.dashboard') }}" class="logo logo-light">
            @else
                <a href="{{ route('secure.home', $user->tenant_id) }}" class="logo logo-light">
            @endif
                <span class="logo-lg">
                    @if(!empty($user->tenant->firma_adi))
                        {{$user->tenant->firma_adi}}
                    @else 
                        Yönetim Paneli
                    @endif
                </span>
                
            </a>
        </div>

      </div>
  
      <div class="d-flex">
  
        @php 
        $id=Auth::user()->user_id;
        $adminData = App\Models\User::find($id);
        @endphp
  
        <div class="dropdown d-inline-block user-dropdown">
          <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="ms-1 username-ellipsis">
  <i class="ri-user-line align-middle me-1"></i> 
  {{ $adminData->name }} 
  (<span class="fst-italic small">{{ $adminData->getRoleNames()->first() ?? 'Rol Yok' }}</span>)
</span>
            <i class="mdi mdi-chevron-down"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end">
          <!-- item-->
            {{-- <a class="dropdown-item" href=""><i class="ri-user-line align-middle me-1"></i>Profil Güncelle</a> --}}
            {{-- Destek Taleplerim menüsü - sadece Patron ve Müdür rollerinde görünür --}}
            @if(Auth::user()->hasAnyRole(['Patron', 'Müdür']))
                <a class="dropdown-item" href="{{ route('subscription.plans', $user->tenant_id) }}"><i class="fas fa-tag me-1"></i>Aboneliklerim</a> 
                <a class="dropdown-item" href="{{ route('support.index', $user->tenant_id) }}"><i class="fas fa-life-ring align-middle me-1"></i>Destek Taleplerim</a>
                <a class="dropdown-item" href="{{ route('payment-history.index', $user->tenant_id) }}"><i class="fas fa-receipt align-middle me-1"></i>Ödeme Geçmişi</a>

            @endif
             @if(Auth::user()->hasAnyRole(['Patron', 'Super Admin']))
             <a class="dropdown-item" href="{{route('general.settings', $user->tenant_id)}}"><i class="ri-settings-3-fill align-middle me-1"></i>Genel Ayarlar</a>
            @endif
            <div class="dropdown-divider"></div>
              <a class="dropdown-item text-danger" href="{{ route('logout') }}"><i class="ri-shut-down-line align-middle me-1 text-danger"></i> Çıkış yap</a>
            </div>
          </div>
          
       
                
        </div>
    </div>
    
  </header>