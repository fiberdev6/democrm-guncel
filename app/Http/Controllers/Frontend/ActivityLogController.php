<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    // Tenant (Firma) Log Sayfası
    public function index($tenant_id)
    {
        $user = Auth::user();
        
        // Super Admin ise direkt super admin sayfasını göster
        if ($user->isSuperAdmin()) {
            return $this->superAdminIndex();
        }
        
        // Yetkili mi kontrol et (Patron veya Müdür olmalı)
        if (!$user->hasRole(['Patron', 'Müdür'])) {
            $notification = array(
                'message' => 'Bu sayfayı görüntüleme yetkiniz yok.',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }

        // Tenant kontrolü
        if ($user->tenant->id != $tenant_id) {
            $notification = array(
                'message' => 'Yetkisiz erişim yapıldı',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }

        // Bu firmadaki kullanıcıları getir - tb_user tablosuna göre düzeltildi
        $users = User::where('tenant_id', $tenant_id)
                    ->where('status', 1)
                    ->select('user_id', 'name') // name alanını da çektik
                    ->get();

        return view('frontend.secure.general_settings.activity_logs', compact('users', 'tenant_id'));
    }

    // Super Admin Log Sayfası  
    public function superAdminIndex()
    {
        $user = Auth::user();
        
        // Super Admin kontrolü
        if (!$user->isSuperAdmin()) {
            $notification = array(
                'message' => 'Bu sayfayı görüntüleme yetkiniz yok.',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }

        // Tüm aktif kullanıcıları getir
        $users = User::where('status', 1)
                    ->with('tenant:id,firma_adi')
                    ->select('user_id', 'name', 'tenant_id') // name alanını da çektik
                    ->get();

        return view('frontend.secure.super_admin.activity_logs', compact('users'));
    }

    // Log verilerini getir (AJAX)
    public function getLogs(Request $request, $tenant_id = null)
    {
        try {
            $user = Auth::user();
            
            // Super Admin ise tüm logları, değilse sadece kendi tenant'ının loglarını görebilir
            if ($user->isSuperAdmin()) {
                $query = ActivityLog::query();
                
                // Tenant filtresi varsa uygula
                if ($request->tenant_id) {
                    $query->where('tenant_id', $request->tenant_id);
                }
            } else {
                // Normal kullanıcı sadece kendi tenant'ının loglarını görebilir
                if ($user->tenant->id != $tenant_id) {
                    return response()->json(['error' => 'Yetkisiz erişim'], 403);
                }
                
                $query = ActivityLog::where('tenant_id', $tenant_id);
            }

            // Tarih filtresi
            if ($request->start_date && $request->end_date) {
                $query->byDateRange($request->start_date, $request->end_date);
            } else {
                // Varsayılan olarak son 30 günü göster
                $query->where('created_at', '>=', Carbon::now()->subDays(30));
            }

            // Kullanıcı filtresi
            if ($request->user_id && $request->user_id != 'all') {
                $query->byUser($request->user_id);
            }

            // Aksiyon filtresi
            if ($request->action && $request->action != 'all') {
                $query->byAction($request->action);
            }

            // Modül filtresi
            if ($request->module && $request->module != 'all') {
                $query->byModule($request->module);
            }

            // Arama
            if ($request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('description', 'LIKE', "%{$search}%")
                      ->orWhere('user_name', 'LIKE', "%{$search}%")
                      ->orWhere('ip_address', 'LIKE', "%{$search}%");
                });
            }

            // Sayfalama ile getir
            $logs = $query->with(['user', 'tenant'])
                         ->orderBy('created_at', 'desc')
                         ->paginate($request->per_page ?? 100);

            // Verileri formatla
            $formattedLogs = $logs->getCollection()->map(function($log) {
                return [
                    'id' => $log->id,
                    'formatted_text' => $log->formatted_description,
                    'date' => $log->formatted_date,
                    'user_name' => $log->user_name,
                    'user_role' => $log->user_role,
                    'user_id' => $log->user_id,
                    'ip_address' => $log->ip_address,
                    'action' => $log->action,
                    'module' => $log->module,
                    'description' => $log->description,
                    'tenant_name' => $log->tenant ? $log->tenant->firma_adi : null
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedLogs,
                'pagination' => [
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                    'per_page' => $logs->perPage(),
                    'total' => $logs->total()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Loglar alınırken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    // Log temizleme (Super Admin için)
    // public function clearLogs(Request $request)
    // {
    //     $user = Auth::user();
        
    //     if (!$user->isSuperAdmin()) {
    //         return response()->json(['error' => 'Yetkisiz işlem'], 403);
    //     }

    //     try {
    //         $deletedCount = 0;

    //         if ($request->tenant_id) {
    //             // Belirli tenant'ın loglarını temizle
    //             $deletedCount = ActivityLog::where('tenant_id', $request->tenant_id)->delete();
    //         } elseif ($request->older_than_days) {
    //             // X günden eski logları temizle
    //             $date = Carbon::now()->subDays($request->older_than_days);
    //             $deletedCount = ActivityLog::where('created_at', '<', $date)->delete();
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => "{$deletedCount} log kaydı temizlendi."
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Loglar temizlenirken hata oluştu: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }
}