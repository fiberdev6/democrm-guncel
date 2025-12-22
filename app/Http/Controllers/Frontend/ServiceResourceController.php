<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ServiceResource;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ServiceResourceController extends Controller
{
    public function AllServiceResource($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı',
                'alert-type' => 'danger'
            );
            return redirect()->route('giris')->with($notification);
        }
        $resources = ServiceResource::where('firma_id', $firma->id)->orderBy('id','desc')->get();
        return view('frontend.secure.service_resources.all_resources', compact('firma', 'resources'));
    }

    public function AddServiceResource($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        return view('frontend.secure.service_resources.add_resource', compact('firma'));
    }

    public function StoreServiceResource($tenant_id, Request $request) {
        $token = $request->input('form_token');
        // Token boş mu kontrol et
        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz form token! Lütfen sayfayı yenileyin.'
            ], 400);
        }
        // Bu token daha önce kullanıldı mı kontrol et
        $cacheKey = 'serviceresource_form_token_' . $token;
        if (Cache::has($cacheKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Bu form zaten gönderildi! Lütfen bekleyin veya sayfayı yenileyin.'
            ], 400);
        }
        // Token'ı 10 dakika boyunca sakla
        Cache::put($cacheKey, true, now()->addMinutes(10));
        $firma = Tenant::where('id', $tenant_id)->first();
        $response = ServiceResource::create([
            'firma_id' => $firma->id,
            'kaynak' => $request->kaynak,
        ]);
        $createdResource = ServiceResource::find($response->id);
        return response()->json($createdResource);
    }

    public function EditServiceResource($tenant_id, $id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $resource_id = ServiceResource::findOrFail($id);
        return view('frontend.secure.service_resources.edit_resource', compact('firma', 'resource_id'));
    }

    public function UpdateServiceResource($tenant_id, Request $request) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $resource_id = $request->id;
        ServiceResource::findOrFail($resource_id)->update([
            'firma_id' => $firma->id,
            'kaynak' => $request->kaynak,
        ]);
        $updatedResource = ServiceResource::find($resource_id);
        return response()->json($updatedResource);
    }

    public function DeleteServiceResource($tenant_id, $id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if (!$firma) {
            return redirect()->back()->with([
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger',
            ]);
        }
        $service_resources = ServiceResource::find($id);
        if($service_resources) {
            $service_resources->delete();
            return response()->json(['success' => true]);
        }
        else{
            return response()->json(['success' => false, 'message' => 'Stok kategorisi başarıyla silindi.']);
        }
    }
}
