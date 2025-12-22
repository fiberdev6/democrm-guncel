<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ServiceTime;
use App\Models\Tenant;
use Illuminate\Http\Request;

class ServiceTimeController extends Controller
{
    public function ServiceTime($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı',
                'alert-type' => 'danger'
            );
            return redirect()->route('giris')->with($notification);
        }
        $default_time = '00:00';
        $service_time = ServiceTime::where('firma_id', $firma->id)->first() ?? null;
        
        return view('frontend.secure.service_times.service_time', compact('firma', 'service_time'));
    }

    public function UpdateServiceTime(Request $request, $tenant_id) {
        $firma = Tenant::findOrFail($tenant_id);

        $serviceTime = ServiceTime::updateOrCreate(
            ['id' => $request->id],
            [
                'firma_id' => $firma->id,
                'zaman' => sprintf('%02d:%02d', $request->hour, $request->minute),
            ]
        );

        return response()->json([
            'message' => 'Servis zamanı başarıyla güncellendi.',
            'serviceTime' => $serviceTime,
        ]);
    }
}
