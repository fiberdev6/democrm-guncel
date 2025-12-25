<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\VerimorSantralService;
use App\Models\Tenant;
use App\Models\VerimorWebphoneToken;

class VerimorSantralController extends Controller
{  /**
     * Web telefonu sayfasını göster
     */
    public function showWebphone($tenant_id) 
    {
        $tenant = Tenant::findOrFail($tenant_id);
        $service = new VerimorSantralService($tenant_id);
        
        // Token al
        $result = $service->getWebphoneToken();
        
        if (!$result['success']) {
            return view('tenant.integrations.verimor-santral.error', [
                'error' => $result['message'],
                'tenant' => $tenant
            ]);
        }
        
        return view('tenant.integrations.verimor-santral.webphone', [
            'iframeUrl' => $service->getIframeUrl($result['token']),
            'token' => $result['token'],
            'expiresAt' => $result['expires_at'],
            'fromCache' => $result['from_cache'] ?? false,
            'tenant' => $tenant
        ]);
    }
    
    /**
     * AJAX ile iframe HTML döndür (modal için)
     */
    public function getIframe(Request $request, $tenant_id)  // tenant_id parametresi eklendi
    {
        $service = new VerimorSantralService($tenant_id);
        
        $width = $request->input('width', 275);
        $height = $request->input('height', 700);
        
        $result = $service->getWebphoneToken();
        
        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
        
        $iframeUrl = $service->getIframeUrl($result['token']);
        
        $html = sprintf(
            '<iframe id="verimorWebphone" style="border: none;" src="%s" width="%spx" height="%spx" allow="microphone"></iframe>',
            $iframeUrl,
            $width,
            $height
        );
        
        return response()->json([
            'success' => true,
            'html' => $html,
            'iframe_url' => $iframeUrl,
            'expires_at' => $result['expires_at']->format('d.m.Y H:i')
        ]);
    }
    
    /**
     * Token yenileme
     */
    public function refreshToken(Request $request, $tenant_id)  // tenant_id parametresi eklendi
    {
        $service = new VerimorSantralService($tenant_id);
        
        // Mevcut token'ı sil ve yeni al
        VerimorWebphoneToken::where('tenant_id', $tenant_id)->delete();
        
        $result = $service->getWebphoneToken();
        
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Token başarıyla yenilendi',
                'iframe_url' => $service->getIframeUrl($result['token']),
                'expires_at' => $result['expires_at']->format('d.m.Y H:i')
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 400);
    }
    
    /**
     * Bağlantı testi
     */
    public function testConnection($tenant_id)  // tenant_id parametresi eklendi
    {
        $service = new VerimorSantralService($tenant_id);
        
        $result = $service->testConnection();
        
        return response()->json($result);
    }
}
