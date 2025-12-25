<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ServiceFormSetting;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceFormSetController extends Controller
{
    public function ServiceFormSettings($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı',
                'alert-type' => 'danger'
            );
            return redirect()->route('giris')->with($notification);
        }
        $ayar = ServiceFormSetting::where('firma_id', $firma->id)->first();
        return view('frontend.secure.service_form_settings.form_settings', compact('firma','ayar'));
    }

    public function UpdateServiceFormSettings(Request $request,$tenant_id) {
        
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı',
                'alert-type' => 'danger'
            );
            return redirect()->route('giris')->with($notification);
        }

        $service_form_id = $request->id;

        if ($service_form_id) {
            // Güncelleme
            $ayar = ServiceFormSetting::find($service_form_id);
            if (!$ayar) {
                return response()->json(['error' => 'Kayıt bulunamadı'], 404);
            }

            $ayar->update([
                'firma_id' => $firma->id,
                'kid' => Auth::user()->user_id,
                'mesaj' => $request->mesaj,
            ]);
        } else {
            // İlk kez kayıt
            ServiceFormSetting::create([
                'firma_id' => $firma->id,
                'kid' => Auth::user()->user_id,
                'mesaj' => $request->mesaj,
            ]);
        }

        return response()->json(['success', 'Servis form ayarları bilgileri güncellendi.']);
    }
}
