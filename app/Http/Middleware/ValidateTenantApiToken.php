<?php

namespace App\Http\Middleware;

use App\Models\TenantApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateTenantApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {// Token'ı al
        $token = $request->bearerToken();

        // Token debug için log'a yaz
        Log::info('API Token Geldi:', ['token' => $token]);

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'API token gereklidir'
            ], 401);
        }

        // Token'ı hashle
        $hashedToken = hash('sha256', $token);
        
        Log::info('Hashed Token:', ['hashed' => $token]);

        // Veritabanında ara
        $apiToken = TenantApiToken::where('token', $token)
            ->where('is_active', true)
            ->with('tenant')
            ->first();

        Log::info('DB Sonucu:', ['found' => $apiToken ? 'Bulundu' : 'Bulunamadı']);

        if (!$apiToken) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz veya pasif API token'
            ], 401);
        }

        // Tenant bilgisini request'e ekle
        $request->merge([
            'tenant_id' => $apiToken->tenant_id,
            'tenant' => $apiToken->tenant,
            'api_token_id' => $apiToken->id
        ]);

        // Son kullanım tarihini güncelle
        $apiToken->updateLastUsed();

        return $next($request);
    }
}
