<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\UserRegisteredMail;
use App\Models\Tenant;
use App\Models\User;
use App\Models\TenantSubscription;
use App\Models\SubscriptionPlan;
use App\Models\Il;
use App\Models\Service;
use App\Models\ServicePlanning;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Models\FrontendSetting;
use App\Models\HomepageContent;
use Illuminate\Support\Facades\Log;

class SuperAdminController extends Controller
{
    
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user() || !Auth::user()->isSuperAdmin()) {
                abort(403, 'Super Admin yetkisi gereklidir.');
            }
            return $next($request);
        });
    }

public function dashboard()
{
    // Super Admin panelini hariç tutmak için
    $superAdminTenant = Tenant::where('firma_adi', 'Super Admin Panel')
                             ->orWhere('name', 'Super Admin')
                             ->first();
    
    $superAdminTenantId = $superAdminTenant ? $superAdminTenant->id : null;

    // Temel istatistikler - Super Admin hariç
    $stats = [
        'total_tenants' => Tenant::when($superAdminTenantId, function($query) use ($superAdminTenantId) {
            return $query->where('id', '!=', $superAdminTenantId);
        })->count(),
        
        'active_tenants' => Tenant::where('status', 1)
                                  ->when($superAdminTenantId, function($query) use ($superAdminTenantId) {
                                      return $query->where('id', '!=', $superAdminTenantId);
                                  })->count(),
        
        'total_users' => User::when($superAdminTenantId, function($query) use ($superAdminTenantId) {
            return $query->where('tenant_id', '!=', $superAdminTenantId);
        })->count(),
        
        'active_users' => User::where('status', 1)
                             ->when($superAdminTenantId, function($query) use ($superAdminTenantId) {
                                 return $query->where('tenant_id', '!=', $superAdminTenantId);
                             })->count(),
    ];

    // Yüzdelik hesaplamalar
    $stats['active_tenant_percentage'] = $stats['total_tenants'] > 0 
        ? round(($stats['active_tenants'] / $stats['total_tenants']) * 100)
        : 0;
    
    $stats['active_user_percentage'] = $stats['total_users'] > 0 
        ? round(($stats['active_users'] / $stats['total_users']) * 100)
        : 0;

    // Destek talepleri istatistikleri
    $supportStats = [
        'urgent_tickets' => \App\Models\SupportTicket::where('priority', 'acil')
                          ->where('status', '!=', 'kapali')->count(),
        'new_tickets' => \App\Models\SupportTicket::where('status', 'acik')->count(),
        'total_tickets' => \App\Models\SupportTicket::count(),
    ];

    // Son 7 günlük grafik verileri - Super Admin hariç
    $chartData = $this->getChartData($superAdminTenantId);

    return view('frontend.secure.super_admin.dashboard', compact('stats', 'supportStats', 'chartData'));
}

// Grafik verilerini hazırlayan yardımcı method - Super Admin hariç
private function getChartData($superAdminTenantId = null)
{
    $labels = [];
    $newRegistrations = [];
    $activeUsers = [];

    // Son 7 günlük verileri hesapla
    for ($i = 6; $i >= 0; $i--) {
        $date = Carbon::now()->subDays($i);
        $labels[] = $date->format('D'); // Gün kısaltması (Mon, Tue, etc.)
        
        // O gün kayıt olan kullanıcı sayısı - Super Admin hariç
        $dailyRegistrations = User::whereDate('created_at', $date->format('Y-m-d'))
                                 ->when($superAdminTenantId, function($query) use ($superAdminTenantId) {
                                     return $query->where('tenant_id', '!=', $superAdminTenantId);
                                 })
                                 ->count();
        $newRegistrations[] = $dailyRegistrations;
        
        // O gün aktif olan kullanıcı sayısı - Super Admin hariç
        $dailyActiveUsers = User::where('status', 1)
                               ->whereDate('updated_at', '<=', $date->format('Y-m-d'))
                               ->when($superAdminTenantId, function($query) use ($superAdminTenantId) {
                                   return $query->where('tenant_id', '!=', $superAdminTenantId);
                               })
                               ->count();
        $activeUsers[] = min($dailyActiveUsers, 100); // Grafik için makul bir üst limit
    }

    return [
        'labels' => $labels,
        'new_registrations' => $newRegistrations,
        'active_users' => $activeUsers
    ];
}
public function allTenants(Request $request)
{
    $countries = Il::orderBy('name', 'ASC')->get();
    
    if ($request->ajax()) {
        // Super Admin panelini hariç tut ve plan bilgileri ile birlikte getir
        $data = Tenant::with([
            'ils', 
            'ilces', 
            'currentSubscription.plansubs',
            'activeSubscription.plansubs'
        ])
        ->where('firma_adi', '!=', 'Super Admin Panel')
        ->where('name', '!=', 'Super Admin');
        
        if (!empty($request->get('search')['value'])) {
            $search = $request->get('search')['value'];
            $data->where(function($w) use($search) {
               $w->where('firma_adi', 'LIKE', "%$search%")
                 ->orWhere('adres', 'LIKE', "%$search%");
            });
        }

        // Status filtering - aktif/pasif durumu
        if ($request->filled('status')) {
            $data->where('status', $request->get('status'));
        }
        
        if ($request->get('il')) {
            $data->where('il', $request->get('il'));
        }
        
        if ($request->get('ilce')) {
            $data->where('ilce', $request->get('ilce'));
        }

        if ($request->has('order')) {
            $order = $request->get('order')[0];
            $columns = $request->get('columns');
            $orderColumnIndex = $order['column'];
            $orderColumnName = $columns[$orderColumnIndex]['name'];
            $orderDir = $order['dir'];
            
            // Special handling for related columns
            if ($orderColumnName == 'name') $orderColumnName = 'firma_adi';
            if ($orderColumnName == 'address') $orderColumnName = 'adres';
            if ($orderColumnName == 'durum') $orderColumnName = 'status';

            $data->orderBy($orderColumnName, $orderDir);
        } else {
            $data->orderBy('id', 'desc');
        }

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('id', function($row) {
                return '<a class="t-link editTenant address idWrap" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editTenantModal">'.$row->id.'</a>';
            })
            ->addColumn('name', function($row) {
                return '<a class="t-link editTenant address" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editTenantModal"><div class="mobileTitle">Firma Adı:</div>'.$row->firma_adi.'</a>';
            })
            ->addColumn('plan', function($row) {
                $planInfo = 'Tanımsız';
                
                // Önce trial durumunu kontrol et (tenant tablosundan)
                if ($row->subscription_status === 'trial' && $row->trial_ends_at && $row->trial_ends_at->isFuture()) {
                    $planInfo = 'Deneme';
                }
                // Sonra active subscription kontrol et
                elseif ($row->activeSubscription && $row->activeSubscription->plansubs) {
                    $planName = $row->activeSubscription->plansubs->name ?? $row->activeSubscription->plansubs->plan_name ?? 'Plan';
                    
                    if ($row->activeSubscription->status === 'active') {
                        $planInfo = $planName;
                    } elseif ($row->activeSubscription->status === 'trial') {
                        $planInfo = $planName . ' (Deneme)';
                    } elseif ($row->activeSubscription->status === 'expired') {
                        $planInfo = $planName . ' (Süresi Dolmuş)';
                    } elseif ($row->activeSubscription->status === 'suspended') {
                        $planInfo = $planName . ' (Askıya Alınmış)';
                    } else {
                        $planInfo = $planName;
                    }
                }
                // Son olarak current subscription kontrol et
                elseif ($row->currentSubscription && $row->currentSubscription->plansubs) {
                    $planName = $row->currentSubscription->plansubs->name ?? $row->currentSubscription->plansubs->plan_name ?? 'Plan';
                    
                    if ($row->currentSubscription->status === 'active') {
                        $planInfo = $planName;
                    } elseif ($row->currentSubscription->status === 'trial') {
                        $planInfo = $planName . ' (Deneme)';
                    } elseif ($row->currentSubscription->status === 'expired') {
                        $planInfo = $planName . ' (Süresi Dolmuş)';
                    } elseif ($row->currentSubscription->status === 'suspended') {
                        $planInfo = $planName . ' (Askıya Alınmış)';
                    } else {
                        $planInfo = $planName;
                    }
                }
                
                return '<a class="t-link editTenant" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editTenantModal">
                           <div class="mobileTitle">Plan:</div>
                           '.$planInfo.'
                        </a>';
            })
            ->addColumn('plan_end_date', function($row) {
                $endDate = 'Belirsiz';
                
                // Önce trial durumunu kontrol et (tenant tablosundan)
                if ($row->subscription_status === 'trial' && $row->trial_ends_at) {
                    $trialEnd = \Carbon\Carbon::parse($row->trial_ends_at);
                    if ($trialEnd->isFuture()) {
                        $endDate = $trialEnd->format('d.m.Y');
                        $daysLeft = $trialEnd->diffInDays(now());
                        if ($daysLeft <= 15) {
                            $endDate .= " ($daysLeft gün kaldı)";
                        }
                    } else {
                        $endDate = 'Süresi Dolmuş';
                    }
                }
                // Active subscription kontrol et
                elseif ($row->activeSubscription && $row->activeSubscription->ends_at) {
                    $subscriptionEnd = \Carbon\Carbon::parse($row->activeSubscription->ends_at);
                    if ($subscriptionEnd->isFuture()) {
                        $endDate = $subscriptionEnd->format('d.m.Y');
                        $daysLeft = $subscriptionEnd->diffInDays(now());
                        if ($daysLeft <= 15) {
                            $endDate .= " ($daysLeft gün kaldı)";
                        }
                    } else {
                        $endDate = 'Süresi Dolmuş';
                    }
                }
                // Current subscription kontrol et
                elseif ($row->currentSubscription && $row->currentSubscription->ends_at) {
                    $subscriptionEnd = \Carbon\Carbon::parse($row->currentSubscription->ends_at);
                    if ($subscriptionEnd->isFuture()) {
                        $endDate = $subscriptionEnd->format('d.m.Y');
                        $daysLeft = $subscriptionEnd->diffInDays(now());
                        if ($daysLeft <= 15) {
                            $endDate .= " ($daysLeft gün kaldı)";
                        }
                    } else {
                        $endDate = 'Süresi Dolmuş';
                    }
                }
                // Tenant tablosundaki subscription_ends_at kontrol et
                elseif ($row->subscription_ends_at) {
                    $subscriptionEnd = \Carbon\Carbon::parse($row->subscription_ends_at);
                    if ($subscriptionEnd->isFuture()) {
                        $endDate = $subscriptionEnd->format('d.m.Y');
                        $daysLeft = $subscriptionEnd->diffInDays(now());
                        if ($daysLeft <= 15) {
                            $endDate .= " ($daysLeft gün kaldı)";
                        }
                    } else {
                        $endDate = 'Süresi Dolmuş';
                    }
                }
                
                return '<a class="t-link editTenant" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editTenantModal">
                           <div class="mobileTitle">Plan Bitiş:</div>
                           '.$endDate.'
                        </a>';
            })
            ->addColumn('address', function($row) {
                $fullAddress = $row->adres ?? '';
                return '<a class="t-link editTenant address" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editTenantModal">
                            <div class="mobileTitle">Adres:</div>' . $fullAddress . '
                        </a>';
            })
           ->addColumn('durum', function($row) {
                $statusBadge = $row->status == 1
                    ? '<span class="badge" style="background-color: #28a745; color: white; padding: 4px 8px; font-size: 11px;"><i class="mdi mdi-check-circle"></i> Aktif</span>'
                    : '<span class="badge bg-danger" style="padding: 4px 8px; font-size: 11px;"><i class="mdi mdi-close-circle"></i> Pasif</span>';
                return '<a class="t-link editTenant" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editTenantModal"><div class="mobileTitle">Durum:</div>'.$statusBadge.'</a>';
            })
            ->addColumn('action', function($row) {
                $editButton = '<a href="javascript:void(0);" data-bs-id="'.$row->id.'" 
                class="btn btn-sm btn-outline-primary editTenant mobilBtn me-1" 
                data-bs-toggle="modal" data-bs-target="#editTenantModal" 
                title="Detay"><i class="fas fa-eye"></i></a>';
                
                $usersButton = '<button class="btn btn-sm btn-outline-danger 
                    mobilBtn view-tenant-users me-1" data-tenant-id="'.$row->id.'" 
                    title="Kullanıcıları Görüntüle">
                    <i class="fas fa-users"></i></button>';
                
                $impersonateButton = '';
                if ($row->status == 1) {
                    $tenantOwner = User::where('tenant_id', $row->id)
                                      ->whereHas('roles', function($query) {
                                          $query->whereIn('name', ['Patron', 'Müdür']);
                                      })
                                      ->first();
                    
                    if ($tenantOwner) {
                        $impersonateButton = '<button class="btn btn-sm btn-outline-success mobilBtn impersonate-tenant-owner me-1" 
                        data-tenant-id="'.$row->id.'" 
                        data-owner-id="'.$tenantOwner->user_id.'"
                        data-owner-name="'.$tenantOwner->name.'"
                        data-company-name="'.$row->firma_adi.'"
                        title="Firma Yetkilisi Olarak Giriş Yap">
                        <i class="fas fa-user-secret"></i>
                    </button>';
                    }
                }
                
                return '<div class="d-flex gap-1">' . $editButton . $usersButton . $impersonateButton . '</div>';
            })
            ->rawColumns(['id','name','plan','plan_end_date','address','durum','action'])
            ->make(true);
    }

    return view('frontend.secure.super_admin.all_tenants', compact('countries'));
}
    public function editTenant($id)
    {
        // İlişkileri dahil ederek tenant'ı getir
        $tenant = Tenant::with([
            'ils', 
            'ilces', 
            'activeSubscription.plansubs', // Aktif abonelik ve plan bilgisi
            'currentSubscription.plansubs' // Güncel abonelik ve plan bilgisi
        ])->findOrFail($id);
        
        if(!$tenant) {
            $notification = array(
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }

        $countries = Il::orderBy('name','asc')->get();
        $today = Carbon::today();
        
        // Mevcut period kodları...
        $periods = [
            'bugun' => [
                'start' => $today->copy(),
                'end' => $today->copy(),
                'label' => 'Bugün'
            ],
            'dun' => [
                'start' => $today->copy()->subDay(),
                'end' => $today->copy()->subDay(),
                'label' => 'Dün'
            ],
            'onceki_gun' => [
                'start' => $today->copy()->subDays(2),
                'end' => $today->copy()->subDays(2),
                'label' => 'Önceki Gün'
            ],
            'ayinBasi' => [
                'start' => $today->copy()->startOfMonth(),
                'end' => $today->copy(),
                'label' => 'Son Ay'
            ]
        ];

        // Mevcut period stats kodları...
        $periodStats = [];
        foreach ($periods as $key => $period) {
            $servisler = Service::where('firma_id', $id) 
            ->where('durum', 1)
            ->whereBetween('kayitTarihi', [
                $period['start']->format('Y-m-d') . ' 00:00:00',
                $period['end']->format('Y-m-d') . ' 23:59:59'
            ])->get();
            
            $personeller = User::where('tenant_id', $id)
            ->whereNull('ayrilmaTarihi')
            ->whereIn('user_id', function ($query) {
                $query->select('model_id')
                    ->from('model_has_roles')
                    ->whereIn('role_id', [1, 5, 263]);
            })
            ->get();

            $validServisler = $this->filterCancelledServices($servisler);
            $validServisIds = $validServisler->pluck('id')->toArray();

            $periodStats[$key] = [
                'label' => $period['label'],
                'toplam' => count($validServisIds),
                'markalar' => $this->getDeviceBrandStats($validServisIds),
                'turler' => $this->getDeviceTypeStats($validServisIds),
                'kaynaklar' => $this->getServiceResourceStats($validServisIds),
                'operatorler' => $this->getOperatorStats($validServisIds)
            ];
        }
        
        $topServisSayisi = Service::where('firma_id', $id) 
            ->where('durum', 1)
            ->count();

        // Abonelik geçmişini getir
        $subscriptionHistory = $tenant->subscriptions()
            ->with('plansubs') // plansubs kullanıyoruz
            ->orderBy('created_at', 'desc')
            ->get();

        // Depolama bilgilerini al
        $storageInfo = $tenant->getStorageInfo();
    
            
        return view('frontend.secure.super_admin.edit_tenants', compact(
            'tenant',
            'countries', 
            'periodStats',
            'topServisSayisi',
            'subscriptionHistory',
            'storageInfo'
        ));
    }

    public function updateTenant(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);
        
        $request->validate([
            'firma_adi' => 'required|string|max:255',
            'eposta' => 'required|email|max:255',
            'tel1' => 'required|string|max:20',
            'status' => 'required|boolean',
        ]);

        $tenant->update($request->only([
            'firma_adi', 'eposta', 'tel1', 'adres', 'il', 'ilce', 'status'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Firma başarıyla güncellendi.'
        ]);
    }

    public function deleteTenant($id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->status = 0;
        $tenant->save();

        $notification = array(
            'message' => 'Firma başarıyla pasif hale getirildi.',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }


public function changeTenantStatus($id)
{
    // İlgili firmayı bul, bulamazsan hata ver
    $tenant = Tenant::findOrFail($id);

    $tenant->status = $tenant->status == 1 ? 0 : 1;
    $tenant->save();

    $message = $tenant->status == 1 
        ? 'Firma başarıyla aktif edildi!' 
        : 'Firma başarıyla pasif hale getirildi.';

    $notification = array(
        'message' => $message,
        'alert-type' => 'success'
    );
    
    // Firma aktif edildiğinde mail gönder
    if ($tenant->status == 1) {
        try {
            // Patron (Patron rolüne sahip) kullanıcıyı bul
            $patronUser = User::where('tenant_id', $tenant->id)
                ->whereHas('roles', function($query) {
                    $query->where('name', 'Patron');
                })
                ->first();
            
            // Eğer patron bulunamazsa, tenant'a ait ilk kullanıcıyı al
            if (!$patronUser) {
                $patronUser = User::where('tenant_id', $tenant->id)->first();
            }
            
            // Trial days hesapla
            $trialDaysRemaining = 0;
            if ($tenant->trial_ends_at) {
                $trialDaysRemaining = $tenant->trial_ends_at->isFuture() 
                    ? now()->diffInDays($tenant->trial_ends_at) 
                    : 0;
            }
            
            // Trial aktif mi kontrol et
            $isTrialActive = $tenant->trial_used == 1 
                && $tenant->trial_ends_at 
                && $tenant->trial_ends_at->isFuture();
            
            // Mail data hazırla
            $mailData = [
                'username' => $patronUser ? $patronUser->username : 'Belirsiz', // ✅ Gerçek username
                'firma_kodu' => $tenant->firma_kodu, // ✅ Firma kodu eklendi
                'tenant' => $tenant,
                'trialDaysRemaining' => $trialDaysRemaining,
                'isTrialActive' => $isTrialActive
            ];
            
            // Mail gönder
            Mail::to($tenant->eposta)->queue(new UserRegisteredMail($mailData));
            
            Log::info('Firma Aktifleştirme Maili Gönderildi', [
                'tenant_id' => $tenant->id,
                'firma_kodu' => $tenant->firma_kodu,
                'email' => $tenant->eposta
            ]);
            
        } catch (\Exception $e) {
            Log::error('Firma Aktifleştirme Mail Hatası', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);
            // Mail hatası olsa bile status değişikliği devam etsin
        }
    }
    
    return redirect()->back()->with($notification);
}



    private function filterCancelledServices($servisler)
    {
        return $servisler->filter(function ($servis) {
            return !ServicePlanning::where('servisid', $servis->id)
                                 ->where('gidenIslem', 244)
                                 ->exists();
        });
    }

    private function getDeviceBrandStats($servisIds)
    {
        if (empty($servisIds)) return [];
        return Service::join('device_brands', 'services.cihazMarka', '=', 'device_brands.id')
              ->whereIn('services.id', $servisIds) 
              ->select('device_brands.marka', DB::raw('count(*) as sayi'))
              ->groupBy('device_brands.marka')
              ->orderBy('sayi', 'desc')
              ->get();
    }

    private function getDeviceTypeStats($servisIds)
    {
        if (empty($servisIds)) return [];

          return Service::whereIn('services.id', $servisIds) 
                 ->join('device_types', 'services.cihazTur', '=', 'device_types.id')
                 ->select('device_types.cihaz', DB::raw('count(*) as sayi'))
                 ->groupBy('device_types.cihaz')
                 ->orderBy('sayi', 'desc')
                 ->get();
    }
    
    private function getServiceResourceStats($servisIds)
    {
        if (empty($servisIds)) return [];

           return Service::whereIn('services.id', $servisIds) 
                 ->join('service_resources', 'services.servisKaynak', '=', 'service_resources.id')
                 ->select('service_resources.kaynak', DB::raw('count(*) as sayi'))
                 ->groupBy('service_resources.kaynak')
                 ->orderBy('sayi', 'desc')
                 ->get();
    }
    
    private function getOperatorStats($servisIds)
    {
        if (empty($servisIds)) return [];

        return Service::whereIn('services.id', $servisIds)
                 ->join('tb_user', 'services.kayitAlan', '=', 'tb_user.user_id')
                 ->select('tb_user.name', DB::raw('count(*) as sayi'))
                 ->groupBy('tb_user.name')
                 ->orderBy('sayi', 'desc')
                 ->get();
    }

public function getTenantPayments($id)
{
    try {
        $tenant = Tenant::findOrFail($id);
        
        // Tüm ödemeleri getir
        $allPayments = $tenant->getAllPayments();
        
        // Ödeme özetini hesapla
        $summary = [
            'completed' => 0,
            'pending' => 0,
            'failed' => 0,
            'refunded' => 0,
            'canceled' => 0,
            'total_amount' => 0
        ];
        
        foreach ($allPayments as $payment) {
            $amount = floatval($payment['amount'] ?? 0);
            $status = $payment['status'];
            
            // Toplam tutarı hesapla (sadece completed ödemeler)
            if ($status === 'completed') {
                $summary['total_amount'] += $amount;
            }
            
            // Durum bazlı toplamları hesapla
            if (isset($summary[$status])) {
                $summary[$status] += $amount;
            }
        }
        
        // Raw değerler ile formatlanmış değerleri ayrı ayrı gönder
        $summaryResponse = [];
        foreach ($summary as $key => $value) {
            // Raw değer
            $summaryResponse[$key] = $value;
            // Formatlanmış değer - ayrı key ile
            $summaryResponse[$key . '_formatted'] = number_format($value, 2, '.', ',');
        }
        
        // Ödemeleri tarih sırasına göre sırala
        $sortedPayments = $allPayments->sortByDesc('created_at')->values();
        
        return response()->json([
            'success' => true,
            'summary' => $summaryResponse,  // Bu şekilde gönder
            'payments' => $sortedPayments,
            'tenant_info' => [
                'id' => $tenant->id,
                'name' => $tenant->firma_adi,
                'email' => $tenant->eposta
            ]
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Tenant ödeme bilgileri getirme hatası: ' . $e->getMessage(), [
            'tenant_id' => $id,
            'error' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Ödeme bilgileri yüklenirken bir hata oluştu.',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function getPaymentDetail($tenantId, $paymentType, $paymentId)
{
    try {
        $tenant = Tenant::findOrFail($tenantId);
        
        $paymentDetail = null;
        
        if ($paymentType === 'subscription') {
            $paymentDetail = \App\Models\SubscriptionPayment::where('tenant_id', $tenantId)
                                                           ->where('id', $paymentId)
                                                           ->with(['subscription.plansubs'])
                                                           ->first();
            
            if ($paymentDetail) {
                $paymentDetail->type = 'subscription';
                $paymentDetail->type_label = 'Abonelik Ödemesi';
                $paymentDetail->plan_name = $paymentDetail->subscription->plansubs->name ?? 'Bilinmeyen Plan';
            }
            
        } elseif ($paymentType === 'storage') {
            $paymentDetail = \App\Models\StoragePurchase::where('tenant_id', $tenantId)
                                                       ->where('id', $paymentId)
                                                       ->first();
            
            if ($paymentDetail) {
                $paymentDetail->type = 'storage';
                $paymentDetail->type_label = 'Depolama Paketi';
                $paymentDetail->plan_name = $paymentDetail->storage_gb . ' GB Ek Depolama';
            }
        } elseif ($paymentType === 'integration') {
            $paymentDetail = \App\Models\IntegrationPurchase::where('tenant_id', $tenantId)
                                                       ->where('id', $paymentId)
                                                       ->first();
            
            if ($paymentDetail) {
                $paymentDetail->type = 'integration';
                $paymentDetail->type_label = 'Entegrasyon';
                $paymentDetail->plan_name = $paymentDetail->integration->name . ' Entegrasyonu';

            }
        }
        
        if (!$paymentDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Ödeme kaydı bulunamadı.'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'payment' => $paymentDetail,
            'tenant_info' => [
                'id' => $tenant->id,
                'name' => $tenant->firma_adi
            ]
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Ödeme detayı getirme hatası: ' . $e->getMessage(), [
            'tenant_id' => $tenantId,
            'payment_type' => $paymentType,
            'payment_id' => $paymentId
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Ödeme detayı yüklenirken bir hata oluştu.'
        ], 500);
    }
}
public function getPaymentStatistics($tenantId)
{
    try {
        $tenant = Tenant::findOrFail($tenantId);
        
        // Abonelik ödemeleri istatistikleri
        $subscriptionStats = DB::table('subscription_payments')
            ->where('tenant_id', $tenantId)
            ->select([
                DB::raw('COUNT(*) as total_count'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN amount ELSE 0 END) as total_completed'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as total_pending'),
                DB::raw('SUM(CASE WHEN status = "failed" THEN amount ELSE 0 END) as total_failed'),
                DB::raw('COUNT(CASE WHEN status = "completed" THEN 1 END) as completed_count'),
                DB::raw('COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count'),
                DB::raw('COUNT(CASE WHEN status = "failed" THEN 1 END) as failed_count')
            ])
            ->first();
        
        // Depolama ödemeleri istatistikleri
        $storageStats = DB::table('storage_purchases')
            ->where('tenant_id', $tenantId)
            ->select([
                DB::raw('COUNT(*) as total_count'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN amount ELSE 0 END) as total_completed'),
                DB::raw('SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as total_pending'),
                DB::raw('SUM(CASE WHEN status = "failed" THEN amount ELSE 0 END) as total_failed'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN storage_gb ELSE 0 END) as total_storage_gb'),
                DB::raw('COUNT(CASE WHEN status = "completed" THEN 1 END) as completed_count'),
                DB::raw('COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count'),
                DB::raw('COUNT(CASE WHEN status = "failed" THEN 1 END) as failed_count')
            ])
            ->first();
        
        // Son 12 aylık ödeme trendi
        $monthlyTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            
            $monthlyAmount = DB::table('subscription_payments')
                ->where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->whereRaw('DATE_FORMAT(paid_at, "%Y-%m") = ?', [$monthKey])
                ->sum('amount');
            
            $monthlyStorageAmount = DB::table('storage_purchases')
                ->where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->whereRaw('DATE_FORMAT(created_at, "%Y-%m") = ?', [$monthKey])
                ->sum('amount');
            
            $monthlyTrend[] = [
                'month' => $date->format('M Y'),
                'subscription_amount' => floatval($monthlyAmount),
                'storage_amount' => floatval($monthlyStorageAmount),
                'total_amount' => floatval($monthlyAmount) + floatval($monthlyStorageAmount)
            ];
        }
        
        return response()->json([
            'success' => true,
            'subscription_stats' => $subscriptionStats,
            'storage_stats' => $storageStats,
            'monthly_trend' => $monthlyTrend,
            'tenant_info' => [
                'id' => $tenant->id,
                'name' => $tenant->firma_adi
            ]
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Ödeme istatistikleri getirme hatası: ' . $e->getMessage(), [
            'tenant_id' => $tenantId
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'İstatistikler yüklenirken bir hata oluştu.'
        ], 500);
    }
}
public function getStorageDetails($tenant_id)
{
    try {
        $tenant = Tenant::findOrFail($tenant_id);
        
        $storageInfo = $tenant->getStorageInfo();
        
        $details = [
            'service_photos' => $this->getServicePhotosBreakdown($tenant),
            'stock_photos' => $this->getStockPhotosBreakdown($tenant),
            'other_files' => $this->getOtherFilesBreakdown($tenant)
        ];
        
        return response()->json([
            'success' => true,
            'storage_info' => $storageInfo,
            'details' => $details
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Storage details error', [
            'tenant_id' => $tenant_id,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Storage detayları alınırken hata oluştu.'
        ], 500);
    }
}
/**
 * Servis fotoğrafları breakdown'ı
 */
private function getServicePhotosBreakdown($tenant)
{
    $photos = \App\Models\ServicePhoto::where('firma_id', $tenant->id)
                         ->selectRaw('COUNT(*) as count, SUM(file_size) as total_size, AVG(file_size) as avg_size')
                         ->first();
    
    return [
        'count' => $photos->count ?? 0,
        'total_size' => $photos->total_size ?? 0,
        'total_size_formatted' => $this->formatBytes($photos->total_size ?? 0),
        'average_size' => $photos->avg_size ?? 0,
        'average_size_formatted' => $this->formatBytes($photos->avg_size ?? 0),
    ];
}

/**
 * Stok fotoğrafları breakdown'ı
 */
private function getStockPhotosBreakdown($tenant)
{
    $stockPhotos = \App\Models\stock_photos::where('kid', $tenant->id)
                              ->selectRaw('COUNT(*) as count, COALESCE(SUM(file_size), 0) as total_size, AVG(file_size) as avg_size')
                              ->first();
    
    return [
        'count' => $stockPhotos->count ?? 0,
        'total_size' => $stockPhotos->total_size ?? 0,
        'total_size_formatted' => $this->formatBytes($stockPhotos->total_size ?? 0),
        'average_size' => $stockPhotos->avg_size ?? 0,
        'average_size_formatted' => $this->formatBytes($stockPhotos->avg_size ?? 0),
    ];
}

/**
 * Diğer dosyalar breakdown'ı
 */
private function getOtherFilesBreakdown($tenant)
{
    $breakdown = [
        'support_attachments' => $this->getSupportAttachmentsBreakdown($tenant),
        'dealer_documents' => $this->getDealerDocumentsBreakdown($tenant),
        'invoice_documents' => $this->getInvoiceDocumentsBreakdown($tenant)
    ];
    
    $totalSize = array_sum(array_column($breakdown, 'size'));
    $totalCount = array_sum(array_column($breakdown, 'count'));
    
    return [
        'total_count' => $totalCount,
        'total_size' => $totalSize,
        'total_size_formatted' => $this->formatBytes($totalSize),
        'breakdown' => $breakdown
    ];
}

private function getSupportAttachmentsBreakdown($tenant)
{
    $count = 0;
    $totalSize = 0;

    // Önce tenant'ın user ID'lerini al
    $userIds = DB::table('tb_user')
                 ->where('tenant_id', $tenant->id)
                 ->pluck('user_id');

    $supportReplies = DB::table('support_ticket_replies')
                        ->whereIn('user_id', $userIds)
                        ->whereNotNull('attachments')
                        ->where('attachments', '!=', '')
                        ->get();

    foreach ($supportReplies as $reply) {
        $attachments = json_decode($reply->attachments, true);
        if (is_array($attachments)) {
            foreach ($attachments as $attachment) {
                if (isset($attachment['path'])) {
                    $filePath = storage_path('app/public/' . $attachment['path']);
                    if (file_exists($filePath)) {
                        $size = filesize($filePath);
                        $totalSize += $size;
                        $count++;
                    }
                }
            }
        }
    }

    return [
        'count' => $count,
        'size' => $totalSize,
        'size_formatted' => $this->formatBytes($totalSize)
    ];
}

private function countFilesInDirectory($directory)
{
    try {
        if (!is_dir($directory) || !is_readable($directory)) {
            return 0;
        }

        $count = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $count++;
            }
        }
        
        return $count;
    } catch (\Exception $e) {
        \Log::warning("File count error for directory: {$directory}", [
            'error' => $e->getMessage()
        ]);
        return 0;
    }
}

/**
 * Klasörün toplam boyutunu hesapla - Güvenli versiyon
 */
private function calculateDirectorySize($directory)
{
    $totalSize = 0;
    
    try {
        if (!is_dir($directory) || !is_readable($directory)) {
            return 0;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->isReadable()) {
                $size = @$file->getSize();
                if ($size !== false) {
                    $totalSize += $size;
                }
            }
        }
    } catch (\Exception $e) {
        \Log::warning("Storage calculation error for directory: {$directory}", [
            'error' => $e->getMessage()
        ]);
    }
    
    return $totalSize;
}

private function getDealerDocumentsBreakdown($tenant)
{
    $dealerDocsPath = storage_path("app/public/dealers-documents/firma_{$tenant->firma_slug}");
    
    if (!is_dir($dealerDocsPath)) {
        return ['count' => 0, 'size' => 0, 'size_formatted' => '0 B'];
    }
    
    $count = $this->countFilesInDirectory($dealerDocsPath);
    $size = $this->calculateDirectorySize($dealerDocsPath);
    
    return [
        'count' => $count,
        'size' => $size,
        'size_formatted' => $this->formatBytes($size)
    ];
}

private function getInvoiceDocumentsBreakdown($tenant)
{
    $count = 0;
    $totalSize = 0;
    
    $invoices = DB::table('invoices')
                   ->where('firma_id', $tenant->id)
                   ->whereNotNull('faturaPdf')
                   ->where('faturaPdf', '!=', '')
                   ->get();
    
    foreach ($invoices as $invoice) {
        $filePath = public_path($invoice->faturaPdf);
        if (file_exists($filePath)) {
            $size = filesize($filePath);
            $totalSize += $size;
            $count++;
        }
    }
    
    return [
        'count' => $count,
        'size' => $totalSize,
        'size_formatted' => $this->formatBytes($totalSize)
    ];
}

private function formatBytes($bytes, $precision = 2)
{
    if ($bytes === null || $bytes < 0) {
        return '0 B';
    }
    
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Tüm firmaların ödeme geçmişini listele
 */
public function allPaymentHistory(Request $request)
{
    $superAdminTenant = Tenant::where('firma_adi', 'Super Admin Panel')
                             ->orWhere('name', 'Super Admin')
                             ->first();
    
    $superAdminTenantId = $superAdminTenant ? $superAdminTenant->id : null;

    if ($request->ajax()) {
        // Filtreleme parametreleri
        $dateFrom = $request->get('date_from', now()->subMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $type = $request->get('type', 'all');
        $tenantId = $request->get('tenant_id');
        $search = $request->get('search')['value'] ?? '';

        $tenants = Tenant::when($superAdminTenantId, function($query) use ($superAdminTenantId) {
            return $query->where('id', '!=', $superAdminTenantId);
        })->orderBy('firma_adi')->get();

        $allPayments = collect();

        $tenantsToProcess = $tenantId 
            ? Tenant::where('id', $tenantId)->get() 
            : $tenants;

        foreach ($tenantsToProcess as $tenant) {
            // Abonelik ödemeleri
            if (in_array($type, ['all', 'subscription'])) {
                if (method_exists($tenant, 'subscriptionPayments')) {
                    $subscriptionPayments = $tenant->subscriptionPayments()
                        ->when($dateFrom, function($query) use ($dateFrom) {
                            return $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
                        })
                        ->when($dateTo, function($query) use ($dateTo) {
                            return $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
                        })
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->map(function($payment) use ($tenant) {
                            return [
                                'id' => $payment->id,
                                'tenant_id' => $tenant->id,
                                'tenant_name' => $tenant->firma_adi,
                                'type' => 'subscription',
                                'type_label' => 'Abonelik',
                                'description' => $this->getSubscriptionPaymentDescriptionForAdmin($payment),
                                'amount' => number_format($payment->amount ?? 0, 2) . ' ' . strtoupper($payment->currency ?? 'TL'),
                                'status' => $payment->status,
                                'status_label' => $this->getStatusLabel($payment->status),
                                'invoice_status' => !empty($payment->invoice_path) ? '<span class=""><i class="fas fa-check mr-1"></i>Mevcut</span>' : '<span class=""><i class="fas fa-clock mr-1"></i>Bekleniyor</span>',
                                'created_at' => $payment->created_at->format('d.m.Y'),
                                'created_at_timestamp' => $payment->created_at->timestamp
                            ];
                        });

                    $allPayments = $allPayments->concat($subscriptionPayments);
                }
            }

            // Depolama ödemeleri
            if (in_array($type, ['all', 'storage'])) {
                if (method_exists($tenant, 'storagePurchases')) {
                    $storagePurchases = $tenant->storagePurchases()
                        ->when($dateFrom, function($query) use ($dateFrom) {
                            return $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
                        })
                        ->when($dateTo, function($query) use ($dateTo) {
                            return $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
                        })
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->map(function($purchase) use ($tenant) {
                            $paymentResponse = is_string($purchase->payment_response) 
                                ? json_decode($purchase->payment_response, true) 
                                : $purchase->payment_response;
                                
                            return [
                                'id' => $purchase->id,
                                'tenant_id' => $tenant->id,
                                'tenant_name' => $tenant->firma_adi,
                                'type' => 'storage',
                                'type_label' => 'Depolama',
                                'description' => $this->getStorageDescriptionForAdmin($purchase),
                                'amount' => number_format($purchase->amount, 2) . ' ' . strtoupper($paymentResponse['currency'] ?? 'TL'),
                                'status' => $purchase->status,
                                'status_label' => $this->getStatusLabel($purchase->status),
                                'invoice_status' => !empty($purchase->invoice_path) ? '<span class=""><i class="fas fa-check mr-1"></i>Mevcut</span>' : '<span class=""><i class="fas fa-clock mr-1"></i>Bekleniyor</span>',
                                'created_at' => $purchase->created_at->format('d.m.Y'),
                                'created_at_timestamp' => $purchase->created_at->timestamp
                            ];
                        });

                    $allPayments = $allPayments->concat($storagePurchases);
                }
            }

            // Entegrasyon ödemeleri - YENİ EKLENEN
            if (in_array($type, ['all', 'integration'])) {
                if (method_exists($tenant, 'integrationPurchases')) {
                    $integrationPurchases = $tenant->integrationPurchases()
                        ->with('integration')
                        ->when($dateFrom, function($query) use ($dateFrom) {
                            return $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
                        })
                        ->when($dateTo, function($query) use ($dateTo) {
                            return $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
                        })
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->map(function($purchase) use ($tenant) {
                            $paymentResponse = is_string($purchase->payment_response) 
                                ? json_decode($purchase->payment_response, true) 
                                : $purchase->payment_response;
                            
                            $integrationName = $purchase->integration ? $purchase->integration->name : 'Bilinmeyen Entegrasyon';
                                
                            return [
                                'id' => $purchase->id,
                                'tenant_id' => $tenant->id,
                                'tenant_name' => $tenant->firma_adi,
                                'type' => 'integration',
                                'type_label' => 'Entegrasyon',
                                'description' => "Entegrasyon - {$integrationName}",
                                'amount' => number_format($purchase->amount, 2) . ' ' . strtoupper($paymentResponse['currency'] ?? 'TL'),
                                'status' => $purchase->status,
                                'status_label' => $this->getStatusLabel($purchase->status),
                                'invoice_status' => !empty($purchase->invoice_path) ? '<span class=""><i class="fas fa-check mr-1"></i>Mevcut</span>' : '<span class=""><i class="fas fa-clock mr-1"></i>Bekleniyor</span>',
                                'created_at' => $purchase->created_at->format('d.m.Y'),
                                'created_at_timestamp' => $purchase->created_at->timestamp
                            ];
                        });

                    $allPayments = $allPayments->concat($integrationPurchases);
                }
            }
        }
        

        // Arama filtresi
        if (!empty($search)) {
            $allPayments = $allPayments->filter(function($payment) use ($search) {
                return stripos($payment['tenant_name'], $search) !== false ||
                       stripos($payment['description'], $search) !== false ||
                       stripos($payment['type_label'], $search) !== false;
            });
        }

        // Sıralama
        $allPayments = $allPayments->sortByDesc('created_at_timestamp')->values();

        return DataTables::of($allPayments)
            ->addIndexColumn()
            ->editColumn('id', function($row) {
                return '<a href="javascript:void(0);" class="t-link">' . $row['id'] . '</a>';
            })
            ->editColumn('tenant_name', function($row) {
                return '<a href="javascript:void(0);" class="t-link">' . $row['tenant_name'] . '</a>';
            })
            ->editColumn('type_label', function($row) {
                return '<a href="javascript:void(0);" class="t-link">' . $row['type_label'] . '</a>';
            })
            ->editColumn('description', function($row) {
                return '<a href="javascript:void(0);" class="t-link">' . $row['description'] . '</a>';
            })
            ->editColumn('amount', function($row) {
                return '<a href="javascript:void(0);" class="t-link">' . $row['amount'] . '</a>';
            })
            ->editColumn('status_label', function($row) {
                $statusColor = match($row['status']) {
                    'active', 'completed' => '#28a745',
                    'pending' => '#fd7e14',
                    'cancelled', 'failed' => '#dc3545',
                    'expired' => '#6c757d',
                    default => '#343a40'
                };
                
                $icon = match($row['status']) {
                    'active', 'completed' => '<i class="fas fa-check-circle me-1"></i>',
                    'pending' => '<i class="fas fa-clock me-1"></i>',
                    'cancelled', 'failed' => '<i class="fas fa-times-circle me-1"></i>',
                    'expired' => '<i class="fas fa-ban me-1"></i>',
                    default => ''
                };
                
                return '<a href="javascript:void(0);" class="t-link" style="color: ' . $statusColor . ' !important; font-weight: 600;">' 
                       . $icon . $row['status_label'] . 
                       '</a>';
            })
            ->editColumn('created_at', function($row) {
                return '<a href="javascript:void(0);" class="t-link">' . $row['created_at'] . '</a>';
            })
            ->editColumn('invoice_status', function($row) {
                $hasInvoice = strpos($row['invoice_status'], 'Mevcut') !== false;
                
                $invoiceColor = $hasInvoice ? '#17a2b8' : '#fd7e14';
                $icon = $hasInvoice 
                    ? '<i class="fas fa-check-circle me-1"></i>' 
                    : '<i class="fas fa-clock me-1"></i>';
                
                $text = $hasInvoice ? 'Mevcut' : 'Bekleniyor';
                
                return '<a href="javascript:void(0);" class="t-link" style="color: ' . $invoiceColor . ' !important; font-weight: 600;">' 
                       . $icon . $text . 
                       '</a>';
            })
            ->rawColumns(['id', 'tenant_name', 'type_label', 'description', 'amount', 'status_label', 'invoice_status', 'created_at'])
            ->make(true);
    }

    // Normal sayfa yüklemesi için
    $tenants = Tenant::when($superAdminTenantId, function($query) use ($superAdminTenantId) {
        return $query->where('id', '!=', $superAdminTenantId);
    })->orderBy('firma_adi')->get();

    $dateFrom = $request->get('date_from', now()->subMonth()->format('Y-m-d'));
    $dateTo = $request->get('date_to', now()->format('Y-m-d'));
    $type = $request->get('type', 'all');
    $tenantId = $request->get('tenant_id');

    return view('frontend.secure.super_admin.payment_history', compact(
        'tenants',
        'dateFrom',
        'dateTo',
        'type',
        'tenantId'
    ));
}
/**
 * Yardımcı methodlar
 */
private function getSubscriptionPaymentDescriptionForAdmin($payment)
{
    $description = 'Abonelik Ödemesi';
    
    if (!empty($payment->subscription_id)) {
        $description .= " (ID: {$payment->subscription_id})";
    }
    
    return $description;
}

private function getStorageDescriptionForAdmin($purchase)
{
    return "Ek Depolama - " . ($purchase->storage_gb ?? 0) . " GB";
}

private function extractPaymentMethodUnifiedForAdmin($purchase)
{
    if (!empty($purchase->payment_method)) {
        return $this->formatPaymentType($purchase->payment_method);
    }
    
    if (!empty($purchase->payment_response)) {
        $paymentResponse = is_string($purchase->payment_response) 
            ? json_decode($purchase->payment_response, true) 
            : $purchase->payment_response;
        
        if (is_array($paymentResponse) && isset($paymentResponse['payment_type'])) {
            return $this->formatPaymentType($paymentResponse['payment_type']);
        }
    }
    
    return 'Belirtilmemiş';
}

private function getAllPaymentMethods($tenants)
{
    $methods = collect();

    foreach ($tenants as $tenant) {
        if (method_exists($tenant, 'subscriptionPayments')) {
            $subscriptionMethods = $tenant->subscriptionPayments()
                ->whereNotNull('payment_method')
                ->pluck('payment_method')
                ->unique();
            $methods = $methods->concat($subscriptionMethods);
        }
    }

    return $methods->unique()->sort()->values();
}

private function formatPaymentType($paymentType)
{
    $types = [
        'card' => 'Kredi Kartı',
        'credit_card' => 'Kredi Kartı',
        'bank_transfer' => 'Banka Havalesi',
        'eft' => 'EFT',
        'cash' => 'Nakit',
        'paytr' => 'PayTR',
        'iyzico' => 'Iyzico'
    ];

    return $types[$paymentType] ?? ucfirst(str_replace('_', ' ', $paymentType));
}

private function getStatusLabel($status)
{
    $labels = [
        'active' => 'Aktif',
        'completed' => 'Tamamlandı',
        'pending' => 'Beklemede',
        'cancelled' => 'İptal Edildi',
        'expired' => 'Süresi Doldu',
        'failed' => 'Başarısız',
        'paid' => 'Ödendi'
    ];

    return $labels[$status] ?? ucfirst($status);
}
/**
 * Tüm firmaların ödeme geçmişini Excel'e aktar
 */
public function exportAllPayments(Request $request)
{
    // Super Admin panelini hariç tut
    $superAdminTenant = Tenant::where('firma_adi', 'Super Admin Panel')
                             ->orWhere('name', 'Super Admin')
                             ->first();
    
    $superAdminTenantId = $superAdminTenant ? $superAdminTenant->id : null;

    // Filtreleme parametreleri
    $dateFrom = $request->get('date_from', now()->subMonth()->format('Y-m-d'));
    $dateTo = $request->get('date_to', now()->format('Y-m-d'));
    $paymentMethod = $request->get('payment_method');
    $status = $request->get('status');
    $type = $request->get('type', 'all');
    $tenantId = $request->get('tenant_id');

    // Tüm firmaları getir
    $tenants = Tenant::when($superAdminTenantId, function($query) use ($superAdminTenantId) {
        return $query->where('id', '!=', $superAdminTenantId);
    })->orderBy('firma_adi')->get();

    $allPayments = collect();

    // Seçili firma varsa sadece o firmayı, yoksa tüm firmaları işle
    $tenantsToProcess = $tenantId 
        ? Tenant::where('id', $tenantId)->get() 
        : $tenants;

    foreach ($tenantsToProcess as $tenant) {
        try {
            // Abonelik ödemeleri
            $subscriptionPayments = collect();
            if (method_exists($tenant, 'subscriptionPayments')) {
                $subscriptionPayments = $tenant->subscriptionPayments()
                    ->when($dateFrom, function($query) use ($dateFrom) {
                        return $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
                    })
                    ->when($dateTo, function($query) use ($dateTo) {
                        return $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
                    })
                    ->when($paymentMethod, function($query) use ($paymentMethod) {
                        return $query->where('payment_method', $paymentMethod);
                    })
                    ->when($status, function($query) use ($status) {
                        return $query->where('status', $status);
                    })
                    ->when($type && $type !== 'all', function($query) use ($type) {
                        if ($type === 'storage') {
                            return $query->whereRaw('1=0');
                        }
                        return $query;
                    })
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function($payment) use ($tenant) {
                        return [
                            'id' => $payment->id,
                            'tenant_name' => $tenant->firma_adi,
                            'type_label' => 'Abonelik',
                            'description' => $this->getSubscriptionPaymentDescriptionForAdmin($payment),
                            'amount' => number_format($payment->amount ?? 0, 2, ',', '.'),
                            'currency' => $payment->currency ?? 'TL',
                            'payment_method' => $payment->payment_method ?: 'Belirtilmemiş',
                            'status_label' => $this->getStatusLabel($payment->status),
                            'created_at' => $payment->created_at->format('d.m.Y H:i'),
                            'paid_at' => $payment->paid_at ? $payment->paid_at->format('d.m.Y H:i') : '-',
                            'transaction_id' => $payment->transaction_id ?: '-',
                            'gateway' => $payment->gateway ?: '-',
                            'has_invoice' => !empty($payment->invoice_path) ? 'Mevcut' : 'Bekleniyor'
                        ];
                    });
            }

            // Depolama ödemeleri
            $storagePurchases = collect();
            if (method_exists($tenant, 'storagePurchases')) {
                $storagePurchases = $tenant->storagePurchases()
                    ->when($dateFrom, function($query) use ($dateFrom) {
                        return $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
                    })
                    ->when($dateTo, function($query) use ($dateTo) {
                        return $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
                    })
                    ->when($paymentMethod, function($query) use ($paymentMethod) {
                        return $query->where(function($q) use ($paymentMethod) {
                            $q->whereJsonContains('payment_response->payment_type', 'card')
                              ->orWhere('payment_method', $paymentMethod);
                        });
                    })
                    ->when($status, function($query) use ($status) {
                        return $query->where('status', $status)
                                     ->orWhereJsonContains('payment_response->status', $status);
                    })
                    ->when($type && $type !== 'all', function($query) use ($type) {
                        if ($type === 'subscription') {
                            return $query->whereRaw('1=0');
                        }
                        return $query;
                    })
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function($purchase) use ($tenant) {
                        $paymentResponse = is_string($purchase->payment_response) 
                            ? json_decode($purchase->payment_response, true) 
                            : ($purchase->payment_response ?? []);
                            
                        return [
                            'id' => $purchase->id,
                            'tenant_name' => $tenant->firma_adi,
                            'type_label' => 'Depolama',
                            'description' => $this->getStorageDescriptionForAdmin($purchase),
                            'amount' => number_format($purchase->amount ?? 0, 2, ',', '.'),
                            'currency' => $paymentResponse['currency'] ?? 'TL',
                            'payment_method' => $this->formatPaymentType($paymentResponse['payment_type'] ?? 'Belirtilmemiş'),
                            'status_label' => $this->getStatusLabel($purchase->status),
                            'created_at' => $purchase->created_at->format('d.m.Y H:i'),
                            'paid_at' => isset($purchase->purchased_at) ? $purchase->purchased_at->format('d.m.Y H:i') : '-',
                            'transaction_id' => $paymentResponse['merchant_oid'] ?? ($purchase->payment_token ?? '-'),
                            'gateway' => isset($paymentResponse['payment_type']) ? 'PayTR' : 'Depolama Sistemi',
                            'has_invoice' => !empty($purchase->invoice_path) ? 'Mevcut' : 'Bekleniyor'
                        ];
                    });
            }

            $allPayments = $allPayments->concat($subscriptionPayments)->concat($storagePurchases);

        } catch (\Exception $e) {
            \Log::error('Export error for tenant: ' . $tenant->id, [
                'error' => $e->getMessage()
            ]);
            continue;
        }
    }

    // Tarihe göre sırala
    $payments = $allPayments->sortByDesc('created_at')->values();

    // CSV dosya adı
    $filename = 'tum-firmalar-odeme-gecmisi-' . now()->format('Y-m-d') . '.csv';
    
    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        'Cache-Control' => 'no-cache, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0'
    ];

    $callback = function() use ($payments) {
        $file = fopen('php://output', 'w');
        
        // BOM for UTF-8 Excel compatibility
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Header
        fputcsv($file, [
            'ID',
            'Firma',
            'Tür', 
            'Açıklama',
            'Tutar',
            'Para Birimi',
            'Ödeme Yöntemi',
            'Durum',
            'Oluşturma Tarihi',
            'Ödeme Tarihi',
            'İşlem ID',
            'Gateway',
            'Fatura Durumu'
        ], ';');

        // Data
        foreach ($payments as $payment) {
            try {
                fputcsv($file, [
                    $payment['id'] ?? '',
                    $payment['tenant_name'] ?? '',
                    $payment['type_label'] ?? '',
                    $payment['description'] ?? '',
                    $payment['amount'] ?? '0,00',
                    $payment['currency'] ?? 'TL',
                    $payment['payment_method'] ?? 'Belirtilmemiş',
                    $payment['status_label'] ?? '',
                    $payment['created_at'] ?? '',
                    $payment['paid_at'] ?? '-',
                    $payment['transaction_id'] ?? '-',
                    $payment['gateway'] ?? '-',
                    $payment['has_invoice'] ?? 'Bekleniyor'
                ], ';');
            } catch (\Exception $e) {
                \Log::error('CSV row error: ' . $e->getMessage());
                continue;
            }
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

public function getPaymentTotals(Request $request)
{
    $superAdminTenant = Tenant::where('firma_adi', 'Super Admin Panel')
                             ->orWhere('name', 'Super Admin')
                             ->first();
    
    $superAdminTenantId = $superAdminTenant ? $superAdminTenant->id : null;

    $dateFrom = $request->get('date_from', now()->subMonth()->format('Y-m-d'));
    $dateTo = $request->get('date_to', now()->format('Y-m-d'));
    $type = $request->get('type', 'all');
    $tenantId = $request->get('tenant_id');

    $tenants = Tenant::when($superAdminTenantId, function($query) use ($superAdminTenantId) {
        return $query->where('id', '!=', $superAdminTenantId);
    })->orderBy('firma_adi')->get();

    $allPayments = collect();

    $tenantsToProcess = $tenantId 
        ? Tenant::where('id', $tenantId)->get() 
        : $tenants;

    foreach ($tenantsToProcess as $tenant) {
        // Abonelik ödemeleri
        if (in_array($type, ['all', 'subscription'])) {
            if (method_exists($tenant, 'subscriptionPayments')) {
                $subscriptionPayments = $tenant->subscriptionPayments()
                    ->when($dateFrom, function($query) use ($dateFrom) {
                        return $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
                    })
                    ->when($dateTo, function($query) use ($dateTo) {
                        return $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
                    })
                    ->get()
                    ->map(function($payment) use ($tenant) {
                        return [
                            'type' => 'subscription',
                            'status' => $payment->status,
                            'amount' => $payment->amount ?? 0
                        ];
                    });

                $allPayments = $allPayments->concat($subscriptionPayments);
            }
        }

        // Depolama ödemeleri
        if (in_array($type, ['all', 'storage'])) {
            if (method_exists($tenant, 'storagePurchases')) {
                $storagePurchases = $tenant->storagePurchases()
                    ->when($dateFrom, function($query) use ($dateFrom) {
                        return $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
                    })
                    ->when($dateTo, function($query) use ($dateTo) {
                        return $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
                    })
                    ->get()
                    ->map(function($purchase) use ($tenant) {
                        return [
                            'type' => 'storage',
                            'status' => $purchase->status,
                            'amount' => $purchase->amount ?? 0
                        ];
                    });

                $allPayments = $allPayments->concat($storagePurchases);
            }
        }

        // Entegrasyon ödemeleri - YENİ EKLENEN
        if (in_array($type, ['all', 'integration'])) {
            if (method_exists($tenant, 'integrationPurchases')) {
                $integrationPurchases = $tenant->integrationPurchases()
                    ->when($dateFrom, function($query) use ($dateFrom) {
                        return $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
                    })
                    ->when($dateTo, function($query) use ($dateTo) {
                        return $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
                    })
                    ->get()
                    ->map(function($purchase) use ($tenant) {
                        return [
                            'type' => 'integration',
                            'status' => $purchase->status,
                            'amount' => $purchase->amount ?? 0
                        ];
                    });

                $allPayments = $allPayments->concat($integrationPurchases);
            }
        }
    }

    // Toplamları hesapla
    $summaryStats = [
        'completed' => number_format($allPayments->where('status', 'completed')->sum('amount'), 2, ',', '.') . ' ₺',
        'pending' => number_format($allPayments->where('status', 'pending')->sum('amount'), 2, ',', '.') . ' ₺',
        'failed' => number_format($allPayments->where('status', 'failed')->sum('amount'), 2, ',', '.') . ' ₺',
        'total' => number_format($allPayments->sum('amount'), 2, ',', '.') . ' ₺',
        
        'subscription_completed' => number_format($allPayments->where('type', 'subscription')->where('status', 'completed')->sum('amount'), 2, ',', '.') . ' ₺',
        'subscription_pending' => number_format($allPayments->where('type', 'subscription')->where('status', 'pending')->sum('amount'), 2, ',', '.') . ' ₺',
        'subscription_failed' => number_format($allPayments->where('type', 'subscription')->where('status', 'failed')->sum('amount'), 2, ',', '.') . ' ₺',
        'subscription_total' => number_format($allPayments->where('type', 'subscription')->sum('amount'), 2, ',', '.') . ' ₺',
        
        'storage_completed' => number_format($allPayments->where('type', 'storage')->where('status', 'completed')->sum('amount'), 2, ',', '.') . ' ₺',
        'storage_pending' => number_format($allPayments->where('type', 'storage')->where('status', 'pending')->sum('amount'), 2, ',', '.') . ' ₺',
        'storage_failed' => number_format($allPayments->where('type', 'storage')->where('status', 'failed')->sum('amount'), 2, ',', '.') . ' ₺',
        'storage_total' => number_format($allPayments->where('type', 'storage')->sum('amount'), 2, ',', '.') . ' ₺',
        
        // Entegrasyon toplamları - YENİ EKLENEN
        'integration_completed' => number_format($allPayments->where('type', 'integration')->where('status', 'completed')->sum('amount'), 2, ',', '.') . ' ₺',
        'integration_pending' => number_format($allPayments->where('type', 'integration')->where('status', 'pending')->sum('amount'), 2, ',', '.') . ' ₺',
        'integration_failed' => number_format($allPayments->where('type', 'integration')->where('status', 'failed')->sum('amount'), 2, ',', '.') . ' ₺',
        'integration_total' => number_format($allPayments->where('type', 'integration')->sum('amount'), 2, ',', '.') . ' ₺',
    ];

    return response()->json($summaryStats);
}


/*************************************************** Frontend Düzenelemeleri *****************************************************/
// Frontend Yönetimi
public function frontendSettings()
{
    return view('frontend.secure.superadmin.frontend.index');
}

// Ana Sayfa Ayarları
public function homeSettings()
{
    $stats = FrontendSetting::where('section', 'home_stats')->orderBy('order')->get();
    $modules = FrontendSetting::where('section', 'home_modules')->orderBy('order')->get();
    $sectors = FrontendSetting::where('section', 'home_sectors')->orderBy('order')->get();
    $integrations = FrontendSetting::where('section', 'home_integrations')->orderBy('order')->get();
    $testimonials = FrontendSetting::where('section', 'home_testimonials')->orderBy('order')->get();
    $faqs = FrontendSetting::where('section', 'home_faqs')->orderBy('order')->get();
    
    return view('frontend.secure.super_admin.frontend.home', compact('stats', 'modules', 'sectors', 'integrations', 'testimonials', 'faqs'));
}
// İstatistik GET
public function getStat($id)
{
    $stat = FrontendSetting::findOrFail($id);
    return response()->json($stat);
}

// İstatistik STORE
public function storeStat(Request $request)
{
    $validated = $request->validate([
        'section' => 'required|string',
        'order' => 'required|integer',
        'is_active' => 'required|boolean',
        'data' => 'required|array'
    ]);
    
    FrontendSetting::create($validated);
    
    return response()->json(['success' => true, 'message' => 'İstatistik eklendi']);
}

// İstatistik UPDATE
public function updateStat(Request $request, $id)
{
    $stat = FrontendSetting::findOrFail($id);
    
    $validated = $request->validate([
        'order' => 'required|integer',
        'is_active' => 'required|boolean',
        'data' => 'required|array'
    ]);
    
    $stat->update($validated);
    
    return response()->json(['success' => true, 'message' => 'İstatistik güncellendi']);
}

// İstatistik DELETE
public function deleteStat($id)
{
    $stat = FrontendSetting::findOrFail($id);
    $stat->delete();
    
    return response()->json(['success' => true, 'message' => 'İstatistik silindi']);
}

// Modül GET
public function getModule($id)
{
    $module = FrontendSetting::findOrFail($id);
    return response()->json($module);
}

// Modül STORE
public function storeModule(Request $request)
{
    $validated = $request->validate([
        'section' => 'required|string',
        'order' => 'required|integer',
        'is_active' => 'required|boolean',
        'data' => 'required|array',
        'data.icon' => 'required|string',
        'data.title' => 'required|string',
        'data.description' => 'required|string',
        'data.color' => 'required|string'
    ]);
    
    FrontendSetting::create($validated);
    
    return response()->json(['success' => true, 'message' => 'Modül eklendi']);
}

// Modül UPDATE
public function updateModule(Request $request, $id)
{
    $module = FrontendSetting::findOrFail($id);
    
    $validated = $request->validate([
        'order' => 'required|integer',
        'is_active' => 'required|boolean',
        'data' => 'required|array',
        'data.icon' => 'required|string',
        'data.title' => 'required|string',
        'data.description' => 'required|string',
        'data.color' => 'required|string'
    ]);
    
    $module->update($validated);
    
    return response()->json(['success' => true, 'message' => 'Modül güncellendi']);
}

// Modül DELETE
public function deleteModule($id)
{
    $module = FrontendSetting::findOrFail($id);
    $module->delete();
    
    return response()->json(['success' => true, 'message' => 'Modül silindi']);
}
// Sektör GET
public function getSector($id)
{
    $sector = FrontendSetting::findOrFail($id);
    return response()->json($sector);
}

// Sektör STORE
public function storeSector(Request $request)
{
    $validated = $request->validate([
        'section' => 'required|string',
        'order' => 'required|integer',
        'is_active' => 'required|boolean',
        'slug' => 'required|string',
        'title' => 'required|string',
        'description' => 'required|string',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif'
    ]);
    
    $data = [
        'slug' => $request->slug,
        'title' => $request->title,
        'description' => $request->description,
    ];
    
    // Resim varsa yükle
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = 'sector_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('frontend/img/sectors'), $imageName);
        $data['image'] = 'frontend/img/sectors/' . $imageName;
    }
    
    FrontendSetting::create([
        'section' => $request->section,
        'order' => $request->order,
        'is_active' => $request->is_active,
        'data' => $data
    ]);
    
    return response()->json(['success' => true, 'message' => 'Sektör eklendi']);
}

// Sektör UPDATE
public function updateSector(Request $request, $id)
{
    $sector = FrontendSetting::findOrFail($id);
    
    $validated = $request->validate([
        'order' => 'required|integer',
        'is_active' => 'required|boolean',
        'slug' => 'required|string',
        'title' => 'required|string',
        'description' => 'required|string',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif'
    ]);
    
    $data = [
        'slug' => $request->slug,
        'title' => $request->title,
        'description' => $request->description,
        'image' => $sector->data['image'] ?? null // Mevcut resmi koru
    ];
    
    // Yeni resim varsa
    if ($request->hasFile('image')) {
        // Eski resmi sil
        if (isset($sector->data['image']) && file_exists(public_path($sector->data['image']))) {
            unlink(public_path($sector->data['image']));
        }
        
        // Yeni resmi yükle
        $image = $request->file('image');
        $imageName = 'sector_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('frontend/img/sectors'), $imageName);
        $data['image'] = 'frontend/img/sectors/' . $imageName;
    }
    
    $sector->update([
        'order' => $request->order,
        'is_active' => $request->is_active,
        'data' => $data
    ]);
    
    return response()->json(['success' => true, 'message' => 'Sektör güncellendi']);
}

// Sektör DELETE
public function deleteSector($id)
{
    $sector = FrontendSetting::findOrFail($id);
    
    // Resmi sil
    if (isset($sector->data['image']) && file_exists(public_path($sector->data['image']))) {
        unlink(public_path($sector->data['image']));
    }
    
    $sector->delete();
    
    return response()->json(['success' => true, 'message' => 'Sektör silindi']);
}
// Entegrasyon GET
public function getIntegration($id)
{
    $integration = FrontendSetting::findOrFail($id);
    return response()->json($integration);
}

// Entegrasyon STORE
public function storeIntegration(Request $request)
{
    $validated = $request->validate([
        'section' => 'required|string',
        'order' => 'required|integer',
        'is_active' => 'required|boolean',
        'data' => 'required|array',
        'data.icon' => 'required|string',
        'data.title' => 'required|string',
        'data.description' => 'required|string',
        'data.color' => 'required|string'
    ]);
    
    FrontendSetting::create($validated);
    
    return response()->json(['success' => true, 'message' => 'Entegrasyon eklendi']);
}

// Entegrasyon UPDATE
public function updateIntegration(Request $request, $id)
{
    $integration = FrontendSetting::findOrFail($id);
    
    $validated = $request->validate([
        'order' => 'required|integer',
        'is_active' => 'required|boolean',
        'data' => 'required|array',
        'data.icon' => 'required|string',
        'data.title' => 'required|string',
        'data.description' => 'required|string',
        'data.color' => 'required|string'
    ]);
    
    $integration->update($validated);
    
    return response()->json(['success' => true, 'message' => 'Entegrasyon güncellendi']);
}

// Entegrasyon DELETE
public function deleteIntegration($id)
{
    $integration = FrontendSetting::findOrFail($id);
    $integration->delete();
    
    return response()->json(['success' => true, 'message' => 'Entegrasyon silindi']);
}
// Yorum GET
public function getTestimonial($id)
{
    $testimonial = FrontendSetting::findOrFail($id);
    return response()->json($testimonial);
}

// Yorum STORE
public function storeTestimonial(Request $request)
{
    $validated = $request->validate([
        'section' => 'required|string',
        'order' => 'required|integer',
        'is_active' => 'required|boolean',
        'data' => 'required|array',
        'data.name' => 'required|string',
        'data.initials' => 'required|string|max:3',
        'data.position' => 'required|string',
        'data.quote' => 'required|string',
        'data.color' => 'required|string'
    ]);
    
    FrontendSetting::create($validated);
    
    return response()->json(['success' => true, 'message' => 'Yorum eklendi']);
}

// Yorum UPDATE
public function updateTestimonial(Request $request, $id)
{
    $testimonial = FrontendSetting::findOrFail($id);
    
    $validated = $request->validate([
        'order' => 'required|integer',
        'is_active' => 'required|boolean',
        'data' => 'required|array',
        'data.name' => 'required|string',
        'data.initials' => 'required|string|max:3',
        'data.position' => 'required|string',
        'data.quote' => 'required|string',
        'data.color' => 'required|string'
    ]);
    
    $testimonial->update($validated);
    
    return response()->json(['success' => true, 'message' => 'Yorum güncellendi']);
}

// Yorum DELETE
public function deleteTestimonial($id)
{
    $testimonial = FrontendSetting::findOrFail($id);
    $testimonial->delete();
    
    return response()->json(['success' => true, 'message' => 'Yorum silindi']);
}
// SSS GET
public function getFaq($id)
{
    $faq = FrontendSetting::findOrFail($id);
    return response()->json($faq);
}

// SSS STORE
public function storeFaq(Request $request)
{
    $validated = $request->validate([
        'section' => 'required|string',
        'order' => 'required|integer',
        'is_active' => 'required|boolean',
        'data' => 'required|array',
        'data.question' => 'required|string',
        'data.answer' => 'required|string'
    ]);
    
    FrontendSetting::create($validated);
    
    return response()->json(['success' => true, 'message' => 'SSS eklendi']);
}

// SSS UPDATE
public function updateFaq(Request $request, $id)
{
    $faq = FrontendSetting::findOrFail($id);
    
    $validated = $request->validate([
        'order' => 'required|integer',
        'is_active' => 'required|boolean',
        'data' => 'required|array',
        'data.question' => 'required|string',
        'data.answer' => 'required|string'
    ]);
    
    $faq->update($validated);
    
    return response()->json(['success' => true, 'message' => 'SSS güncellendi']);
}

// SSS DELETE
public function deleteFaq($id)
{
    $faq = FrontendSetting::findOrFail($id);
    $faq->delete();
    
    return response()->json(['success' => true, 'message' => 'SSS silindi']);
}
// Ana Sayfa İçerik Yönetimi
public function homepageContent()
{
    $hero = HomepageContent::where('section', 'hero')->first();
    $sectionHeaders = HomepageContent::where('section', 'section_headers')->first();
    $contact = HomepageContent::where('section', 'contact')->first();
    $cta = HomepageContent::where('section', 'cta')->first();
    $video = HomepageContent::where('section', 'video')->first();
    
    // Eğer hero yoksa boş bir object oluştur
    if (!$hero) {
        $hero = (object)['content' => []];
    }
    if (!$sectionHeaders) {
        $sectionHeaders = (object)['content' => []];
    }
    if (!$contact) {
        $contact = (object)['content' => []];
    }
    if (!$cta) {
        $cta = (object)['content' => []];
    }
    if (!$video) {
        $video = (object)['content' => []];
    }
    
    return view('frontend.secure.super_admin.frontend.content', compact('hero', 'sectionHeaders', 'contact', 'cta', 'video'));
}

// İçerik Güncelleme
public function updateHomepageContent(Request $request)
{
    try {
        $section = $request->input('section');
        
        // Hero section için özel işlem
        if($section == 'hero') {
            // Mevcut hero verisini al
            $existingHero = HomepageContent::where('section', 'hero')->first();
            
            // Content'i hazırla
            $content = [
                'badge' => $request->input('badge'),
                'title' => $request->input('title'),
                'highlight' => $request->input('highlight'),
                'description' => $request->input('description'),
                'primary_button_text' => $request->input('primary_button_text'),
                'primary_button_icon' => $request->input('primary_button_icon'),
                'secondary_button_text' => $request->input('secondary_button_text'),
                'secondary_button_icon' => $request->input('secondary_button_icon'),
                'features' => json_decode($request->input('features'), true),
                'floating_stat' => json_decode($request->input('floating_stat'), true),
            ];
            
            // Mevcut resimleri koru
            if($existingHero && isset($existingHero->content['image'])) {
                $content['image'] = $existingHero->content['image'];
            }
            if($existingHero && isset($existingHero->content['mobile_image'])) {
                $content['mobile_image'] = $existingHero->content['mobile_image'];
            }
            
            // Ana resim yüklendiyse
            if ($request->hasFile('image')) {
                // Eski resmi sil (default resim değilse)
                if($existingHero && isset($existingHero->content['image']) && $existingHero->content['image'] != 'frontend/img/anasayfa2.png') {
                    $oldImagePath = public_path($existingHero->content['image']);
                    if(file_exists($oldImagePath)) {
                        @unlink($oldImagePath);
                    }
                }
                
                // Yeni resmi yükle
                $image = $request->file('image');
                $imageName = 'hero_' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('frontend/img'), $imageName);
                $content['image'] = 'frontend/img/' . $imageName;
            }
            
            // Mobil resim yüklendiyse
            if ($request->hasFile('mobile_image')) {
                // Eski mobil resmi sil
                if($existingHero && isset($existingHero->content['mobile_image'])) {
                    $oldMobileImagePath = public_path($existingHero->content['mobile_image']);
                    if(file_exists($oldMobileImagePath)) {
                        @unlink($oldMobileImagePath);
                    }
                }
                
                // Yeni mobil resmi yükle
                $mobileImage = $request->file('mobile_image');
                $mobileImageName = 'hero_mobile_' . time() . '.' . $mobileImage->getClientOriginalExtension();
                $mobileImage->move(public_path('frontend/img'), $mobileImageName);
                $content['mobile_image'] = 'frontend/img/' . $mobileImageName;
            }
            
            // Database'e kaydet
            HomepageContent::updateOrCreate(
                ['section' => $section],
                [
                    'content' => $content,
                    'is_active' => true
                ]
            );
            
            return response()->json(['success' => true, 'message' => 'Hero bölümü güncellendi']);
        }
        
        // Diğer section'lar için
        $content = $request->input('content');
        
        // JSON string ise decode et
        if (is_string($content)) {
            $content = json_decode($content, true);
        }
        
        // Navbar için logo upload kontrolü
        if ($section === 'navbar_content' && $request->hasFile('logo')) {
            $logoFile = $request->file('logo');
            
            // Eski logoyu sil (default değilse)
            $existingContent = HomepageContent::where('section', 'navbar_content')->first();
            if ($existingContent && isset($existingContent->content['logo'])) {
                $oldLogo = public_path($existingContent->content['logo']);
                if (file_exists($oldLogo) && $existingContent->content['logo'] !== 'frontend/img/logo_turkce.png') {
                    @unlink($oldLogo);
                }
            }
            
            // Yeni logoyu kaydet
            $filename = 'logo_' . time() . '.' . $logoFile->getClientOriginalExtension();
            $logoFile->move(public_path('frontend/img'), $filename);
            
            // Content'e logo path'i ekle
            $content['logo'] = 'frontend/img/' . $filename;
        }
        
        // Database'e kaydet
        HomepageContent::updateOrCreate(
            ['section' => $section],
            [
                'content' => $content,
                'is_active' => true
            ]
        );
        
        return response()->json(['success' => true, 'message' => 'İçerik güncellendi']);
        
    } catch (\Exception $e) {
        \Log::error('Homepage Content Update Error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Bir hata oluştu: ' . $e->getMessage()], 500);
    }
}
// Navbar & Footer Yönetimi
public function navigationSettings()
{
    $navbar = HomepageContent::where('section', 'navbar_content')->first();
    $footer = HomepageContent::where('section', 'footer_content')->first();
    
    
    return view('frontend.secure.super_admin.frontend.navigation', compact('navbar', 'footer'));
}
// Yasal Sayfalar Listesi
public function legalPages()
{
    $pages = HomepageContent::whereIn('section', ['gizlilik', 'kullanim-kosullari', 'kvkk'])
        ->get();
    
    return view('frontend.secure.super_admin.frontend.legal_pages', compact('pages'));
}

// Yasal Sayfa Düzenle
public function editLegalPage($section = null)
{
    $page = null;
    if ($section) {
        $page = HomepageContent::where('section', $section)->first();
    }
    
    return view('frontend.secure.super_admin.frontend.legal_page_edit', compact('page', 'section'));
}

// Yasal Sayfa Kaydet
public function storeLegalPage(Request $request, $section = null)
{
    $validated = $request->validate([
        'section' => 'required|string|in:gizlilik,kullanim-kosullari,kvkk',
        'title' => 'required|string|max:255',
        'content' => 'required'
    ]);

    $content = [
        'title' => $validated['title'],
        'content' => $validated['content']
    ];

    HomepageContent::updateOrCreate(
        ['section' => $validated['section']],
        [
            'content' => $content,
            'is_active' => true
        ]
    );

    return redirect()->route('super.admin.frontend.legal-pages')->with('success', 'Sayfa kaydedildi');
}

// Yasal Sayfa Sil
public function deleteLegalPage($section)
{
    HomepageContent::where('section', $section)->delete();
    
    return redirect()->route('super.admin.frontend.legal-pages')->with('success', 'Sayfa silindi');
}
public function aboutContent()
{
    $about = HomepageContent::where('section', 'about-content')->first();
    
    return view('frontend.secure.super_admin.frontend.about_content', compact('about'));
}

public function updateAboutContent(Request $request)
{
    $content = json_decode($request->input('content'), true);
    
    // Hero Image Upload
    if ($request->hasFile('hero_image')) {
        $oldImage = $content['hero']['image'] ?? null;
        
        // Eski resmi sil (default değilse)
        if ($oldImage && $oldImage !== 'frontend/img/about/hakkimizda1.jpeg' && file_exists(public_path($oldImage))) {
            unlink(public_path($oldImage));
        }
        
        // Yeni resmi yükle
        $image = $request->file('hero_image');
        $imageName = 'hero_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('frontend/img/about'), $imageName);
        
        $content['hero']['image'] = 'frontend/img/about/' . $imageName;
    }
    
    // Story Image Upload
    if ($request->hasFile('story_image')) {
        $oldImage = $content['story']['image'] ?? null;
        
        // Eski resmi sil (default değilse)
        if ($oldImage && $oldImage !== 'frontend/img/about/hakkimizda2.jpeg' && file_exists(public_path($oldImage))) {
            unlink(public_path($oldImage));
        }
        
        // Yeni resmi yükle
        $image = $request->file('story_image');
        $imageName = 'story_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('frontend/img/about'), $imageName);
        
        $content['story']['image'] = 'frontend/img/about/' . $imageName;
    }
    
    // Database'e kaydet
    HomepageContent::updateOrCreate(
        ['section' => 'about-content'],
        [
            'content' => $content,
            'is_active' => true
        ]
    );
    
    return back()->with('success', 'Hakkımızda içeriği başarıyla güncellendi!');
}
public function sectorsContent()
{
    $sectors = HomepageContent::where('section', 'sectors_content')->first();
    
    // Eğer yoksa boş obje oluştur
    if (!$sectors) {
        $sectors = new HomepageContent();
        $sectors->section = 'sectors_content';
        $sectors->content = [
            'page_header' => [],
            'sectors' => [],
            'cta' => []
        ];
    }
    
    return view('frontend.secure.super_admin.frontend.sectors_content', compact('sectors'));
}

public function updateSectorsContent(Request $request)
{
    $content = json_decode($request->input('content'), true);
    
    // Sektör resimlerini upload et
    foreach ($content['sectors'] as $index => &$sector) {
        $fileInputName = 'sector_image_' . $index;
        
        if ($request->hasFile($fileInputName)) {
            $oldImage = $sector['image'] ?? null;
            
            // Eski resmi sil (default değilse)
            if ($oldImage && strpos($oldImage, 'frontend/img/sectors/') !== false && file_exists(public_path($oldImage))) {
                @unlink(public_path($oldImage));
            }
            
            // Yeni resmi yükle
            $image = $request->file($fileInputName);
            $imageName = $sector['slug'] . '_' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('frontend/img/sectors'), $imageName);
            
            $sector['image'] = 'frontend/img/sectors/' . $imageName;
        }
    }
    
    // Database'e kaydet
    HomepageContent::updateOrCreate(
        ['section' => 'sectors_content'],
        [
            'content' => $content,
            'is_active' => true
        ]
    );
    
    return back()->with('success', 'Sektörler içeriği başarıyla güncellendi!');
}
public function sectorDetail($slug)
{
    $sectorSection = 'sector_' . $slug;
    $sector = HomepageContent::where('section', $sectorSection)->first();
    
    // Eğer yoksa boş obje oluştur
    if (!$sector) {
        $sector = new HomepageContent();
        $sector->section = $sectorSection;
        $sector->content = [
            'title' => '',
            'icon' => '',
            'hero_image' => '',
            'description' => '',
            'stats' => [],
            'features' => [],
            'services' => [],
            'benefits' => [],
            'faqs' => []
        ];
    }
    
    return view('frontend.secure.super_admin.frontend.sector_detail', compact('sector', 'slug'));
}

public function updateSectorDetail(Request $request, $slug)
{
    $content = json_decode($request->input('content'), true);
    
    // Hero Image Upload
    if ($request->hasFile('hero_image')) {
        $oldImage = $content['hero_image'] ?? null;
        
        // Eski resmi sil
        if ($oldImage && file_exists(public_path($oldImage))) {
            @unlink(public_path($oldImage));
        }
        
        // Yeni resmi yükle
        $image = $request->file('hero_image');
        $imageName = $slug . '_hero_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('frontend/img/sectors'), $imageName);
        
        $content['hero_image'] = 'frontend/img/sectors/' . $imageName;
    }
    
    // Database'e kaydet
    $sectorSection = 'sector_' . $slug;
    HomepageContent::updateOrCreate(
        ['section' => $sectorSection],
        [
            'content' => $content,
            'is_active' => true
        ]
    );
    
    return back()->with('success', 'Sektör detay içeriği başarıyla güncellendi!');
}
public function featuresContent()
{
    $features = HomepageContent::where('section', 'features_content')->first();
    
    // Eğer yoksa boş obje oluştur
    if (!$features) {
        $features = new HomepageContent();
        $features->section = 'features_content';
        $features->content = [
            'page_header' => [],
            'categories' => [],
            'cta' => []
        ];
    }
    
    return view('frontend.secure.super_admin.frontend.features_content', compact('features'));
}

public function updateFeaturesContent(Request $request)
{
    $content = json_decode($request->input('content'), true);
    
    // Database'e kaydet
    HomepageContent::updateOrCreate(
        ['section' => 'features_content'],
        [
            'content' => $content,
            'is_active' => true
        ]
    );
    
    return back()->with('success', 'Özellikler içeriği başarıyla güncellendi!');
}
public function featureDetail($slug)
{
    $featureSection = 'feature_' . $slug;
    $feature = HomepageContent::where('section', $featureSection)->first();
    
    // Eğer yoksa boş obje oluştur
    if (!$feature) {
        $feature = new HomepageContent();
        $feature->section = $featureSection;
        $feature->content = [
            'title' => '',
            'subtitle' => '',
            'hero_image' => '',
            'description' => '',
            'benefits' => [],
            'features_list' => [],
            'stats' => [],
            'faqs' => [],
            'cta' => []
        ];
    }
    
    return view('frontend.secure.super_admin.frontend.feature_detail', compact('feature', 'slug'));
}

public function updateFeatureDetail(Request $request, $slug)
{
    $content = json_decode($request->input('content'), true);
    
    // Hero Image Upload
    if ($request->hasFile('hero_image')) {
        $oldImage = $content['hero_image'] ?? null;
        
        // Eski resmi sil
        if ($oldImage && file_exists(public_path($oldImage))) {
            @unlink(public_path($oldImage));
        }
        
        // Yeni resmi yükle
        $image = $request->file('hero_image');
        $imageName = $slug . '_hero_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('frontend/img/features'), $imageName);
        
        $content['hero_image'] = 'frontend/img/features/' . $imageName;
    }
    
    // Database'e kaydet
    $featureSection = 'feature_' . $slug;
    HomepageContent::updateOrCreate(
        ['section' => $featureSection],
        [
            'content' => $content,
            'is_active' => true
        ]
    );
    
    return back()->with('success', 'Özellik detay içeriği başarıyla güncellendi!');
}
public function integrationsContent()
{
    $integrations = HomepageContent::where('section', 'integrations_content')->first();
    
    // Eğer yoksa boş yapı oluştur
    if (!$integrations) {
        $integrations = new HomepageContent();
        $integrations->section = 'integrations_content';
        $integrations->content = [
            'page_header' => [
                'title' => 'Serbis Entegrasyonları ile Tüm Süreçlerinizi Entegre Edin',
                'subtitle' => 'Serbis uygulama mağazasındaki uygulama ve entegrasyonlar ile teknik servis sitenizi çok yönlü hale getirin.',
                'button_text' => 'Deneme Hesabı Oluştur',
                'button_url' => '/kullanici-girisi'
            ],
            'marquee_logos' => [],
            'categories' => [],
            'faqs' => []
        ];
    }
    
    return view('frontend.secure.super_admin.frontend.integrations_content', compact('integrations'));
}

public function updateIntegrationsContent(Request $request)
{
    $content = json_decode($request->input('content'), true);
    
    // Logo yüklemeleri - Marquee logoları
    if ($request->has('marquee_logo_files')) {
        foreach ($request->file('marquee_logo_files') as $index => $file) {
            if ($file) {
                // Eski logoyu sil
                $oldLogo = $content['marquee_logos'][$index]['logo'] ?? null;
                if ($oldLogo && file_exists(public_path($oldLogo))) {
                    @unlink(public_path($oldLogo));
                }
                
                // Yeni logoyu yükle
                $logoName = 'marquee_' . time() . '_' . $index . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('frontend/img/integrations'), $logoName);
                
                $content['marquee_logos'][$index]['logo'] = 'frontend/img/integrations/' . $logoName;
            }
        }
    }
    
    // Kategori entegrasyon logoları
    if ($request->has('integration_logo_files')) {
        foreach ($request->file('integration_logo_files') as $catIndex => $categoryFiles) {
            if (is_array($categoryFiles)) {
                foreach ($categoryFiles as $intIndex => $file) {
                    if ($file) {
                        // Eski logoyu sil
                        $oldLogo = $content['categories'][$catIndex]['integrations'][$intIndex]['logo'] ?? null;
                        if ($oldLogo && file_exists(public_path($oldLogo))) {
                            @unlink(public_path($oldLogo));
                        }
                        
                        // Yeni logoyu yükle
                        $logoName = 'integration_' . time() . '_' . $catIndex . '_' . $intIndex . '.' . $file->getClientOriginalExtension();
                        $file->move(public_path('frontend/img/integrations'), $logoName);
                        
                        $content['categories'][$catIndex]['integrations'][$intIndex]['logo'] = 'frontend/img/integrations/' . $logoName;
                    }
                }
            }
        }
    }
    
    // Database'e kaydet
    HomepageContent::updateOrCreate(
        ['section' => 'integrations_content'],
        [
            'content' => $content,
            'is_active' => true
        ]
    );
    
    return back()->with('success', 'Entegrasyonlar içeriği başarıyla güncellendi!');
}
public function pricingContent()
{
    $pricing = HomepageContent::where('section', 'pricing_content')->first();
    
    // Eğer yoksa boş yapı oluştur
    if (!$pricing) {
        $pricing = new HomepageContent();
        $pricing->section = 'pricing_content';
        $pricing->content = [
            'page_header' => [
                'badge_icon' => 'fas fa-tag',
                'badge_text' => '14 Gün Ücretsiz Deneme',
                'title' => 'Size Uygun',
                'title_highlight' => 'Planı',
                'title_suffix' => 'Seçin',
                'subtitle' => 'Her ölçekteki teknik servis için uygun fiyatlı çözümler. Kredi kartı gerektirmeden hemen başlayın, işinizi büyütün.',
                'hero_features' => []
            ],
            'pricing_plans' => [],
            'faqs' => [],
            'cta' => [
                'title' => '14 Gün Ücretsiz Deneyin!',
                'description' => 'Kredi kartı gerektirmez. Anında başlayın, tüm özellikleri keşfedin.',
                'button_text' => 'Hemen Ücretsiz Başla',
                'button_url' => '/kullanici-girisi'
            ]
        ];
    }
    
    return view('frontend.secure.super_admin.frontend.pricing_content', compact('pricing'));
}

public function updatePricingContent(Request $request)
{
    $content = json_decode($request->input('content'), true);
    
    // Database'e kaydet
    HomepageContent::updateOrCreate(
        ['section' => 'pricing_content'],
        [
            'content' => $content,
            'is_active' => true
        ]
    );
    
    return back()->with('success', 'Fiyatlandırma içeriği başarıyla güncellendi!');
}

public function contactContent()
{
    $contact = HomepageContent::where('section', 'contact_content')->first();
    
    // Eğer yoksa boş yapı oluştur
    if (!$contact) {
        $contact = new HomepageContent();
        $contact->section = 'contact_content';
        $contact->content = [
            'page_header' => [
                'title' => 'İletişim',
                'subtitle' => 'Sorularınız için bize ulaşın, size yardımcı olmaktan mutluluk duyarız.',
                'breadcrumb_home' => 'Ana Sayfa',
                'breadcrumb_current' => 'İletişim'
            ],
            'contact_cards' => [],
            'left_panel' => [
                'title' => 'Serbis CRM ile',
                'title_highlight' => 'İşinizi Büyütün',
                'description' => 'Teknik servis süreçlerinizi dijitalleştirmek için formu doldurun.',
                'features' => [],
                'apps_label' => 'Mobil Uygulamamızı İndirin:',
                'google_play_link' => '#',
                'app_store_link' => '#'
            ],
            'form_section' => [
                'title' => 'Bize Ulaşın',
                'subtitle' => 'Aşağıdaki formu doldurarak bize mesaj gönderin.',
                'name_label' => 'Ad-Soyad',
                'name_placeholder' => 'Adınız Soyadınız',
                'email_label' => 'E-posta',
                'email_placeholder' => 'ornek@email.com',
                'phone_label' => 'Telefon',
                'phone_placeholder' => '0555 555 55 55',
                'message_label' => 'Mesajınız',
                'message_placeholder' => 'Size nasıl yardımcı olabiliriz?',
                'button_text' => 'Mesajı Gönder'
            ]
        ];
    }
    
    return view('frontend.secure.super_admin.frontend.contact_content', compact('contact'));
}

public function updateContactContent(Request $request)
{
    $content = json_decode($request->input('content'), true);
    
    // Database'e kaydet
    HomepageContent::updateOrCreate(
        ['section' => 'contact_content'],
        [
            'content' => $content,
            'is_active' => true
        ]
    );
    
    return back()->with('success', 'İletişim sayfası içeriği başarıyla güncellendi!');
}
/*************************************************** Frontend Düzenlemeleri *****************************************************/

}