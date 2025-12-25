<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\TenantApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class TenantApiTokenController extends Controller
{
    public function index(Request $request)
    {
        $firma_id = Auth::user()->tenant_id;
        $firma = \App\Models\Tenant::find($firma_id);
        
        // Firmanın token'ı var mı?
        $apiToken = TenantApiToken::where('tenant_id', $firma_id)->first();
        return view('frontend.secure.general_settings.api_tokens', compact('firma', 'apiToken'));
    }

    /**
     * Yeni token oluştur
     */
    public function create()
    {
        $firma_id = Auth::user()->tenant_id;
        $apiToken = TenantApiToken::where('tenant_id', $firma_id)->first();
        
        return view('frontend.secure.general_settings.api_token_form', compact('apiToken','firma_id'));
    }

    /**
     * Token oluştur veya güncelle
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $firma_id = Auth::user()->tenant_id;

        // Plain text token oluştur
        $plainTextToken = Str::random(60);
        $hashedToken = TenantApiToken::hashToken($plainTextToken);

        // Var olan token'ı kontrol et
        $apiToken = TenantApiToken::where('tenant_id', $firma_id)->first();

        if ($apiToken) {
            // Güncelle
            $apiToken->update([
                'name' => $request->name,
                'token' => $plainTextToken,
                'is_active' => true,
            ]);
            $message = 'API Token başarıyla güncellendi';
        } else {
            // Yeni oluştur
            $apiToken = TenantApiToken::create([
                'tenant_id' => $firma_id,
                'name' => $request->name,
                'token' => $plainTextToken,
                'is_active' => true,
            ]);
            $message = 'API Token başarıyla oluşturuldu';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'token' => $plainTextToken, // Token'ı frontend'e gönder
        ]);
    }

    /**
     * Token aktif/pasif yap
     */
    public function toggle()
    {
        $firma_id = Auth::user()->tenant_id;
        
        $apiToken = TenantApiToken::where('tenant_id', $firma_id)->firstOrFail();
        $apiToken->is_active = !$apiToken->is_active;
        $apiToken->save();

        return response()->json([
            'success' => true,
            'message' => 'Token durumu güncellendi',
            'is_active' => $apiToken->is_active
        ]);
    }

    /**
     * Token sil
     */
    public function destroy()
    {
        $firma_id = Auth::user()->tenant_id;
        
        $apiToken = TenantApiToken::where('tenant_id', $firma_id)->firstOrFail();
        $apiToken->delete();

        return response()->json([
            'success' => true,
            'message' => 'API Token silindi'
        ]);
    }

    /**
     * Token'ı kopyala (tekrar göster)
     */
    public function show()
    {
        $firma_id = Auth::user()->tenant_id;
        
        $apiToken = TenantApiToken::where('tenant_id', $firma_id)->firstOrFail();

        return response()->json([
            'success' => true,
            'token' => $apiToken->plain_token
        ]);
    }
}
