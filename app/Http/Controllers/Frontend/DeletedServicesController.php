<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServicePlanning;
use App\Models\ServiceStageAnswer;
use App\Models\StockAction;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DeletedServicesController extends Controller
{
    public function DeletedServices($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $this->deleteExpiredServices($tenant_id); // önce temizlik yap
        $deleted_services = Service::where('firma_id', $tenant_id)->where('durum', '0')->get();
        return view('frontend.secure.all_services.deleted_services', compact('firma','deleted_services'));
    }

    private function deleteExpiredServices($tenant_id)
    {
        $today = Carbon::today();

        $expired_services = Service::where('firma_id', $tenant_id)
            ->where('durum', 0)
            ->whereDate('silinmeTarihi', '<=', $today->copy()->subDays(7))
            ->get();
         $firma = Tenant::where('id', $tenant_id)->first();
        foreach ($expired_services as $service) {
            // Planları sil
            $plans = ServicePlanning::where('servisid', $service->id)->get();
            foreach ($plans as $plan) {
                StockAction::where('planId', $plan->id)->delete();
                $plan->delete();
            }

            // Aşama cevaplarını sil
            ServiceStageAnswer::where('servisid', $service->id)->delete();

            // Servisi sil
            $service->delete();

            Log::info( $firma->firma_adi . ' firmasının ' . $service->id. ' IDli servislerinin süresi geçtiği için sistemden silindi.');
        }
    }

    public function RestoreService($tenant_id, $service_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        Service::where('firma_id', $tenant_id)->findOrFail($service_id)->update([
            'durum' => 1,
            'silinmeTarihi' => null,
            'silenKisi' => null,
        ]);
        Log::info( $firma->firma_adi . ' firmasının ' . Auth::user()->name . '  personeli ' . $service_id. ' IDli servisi silinenlerden geri aldı.', [
            'ip_address' => request()->ip(),
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Servis başarıyla geri alındı.',
            'id' => $service_id
        ]);
    }

}
