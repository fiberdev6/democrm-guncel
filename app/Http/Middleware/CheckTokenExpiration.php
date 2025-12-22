<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class CheckTokenExpiration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Token'ı al
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token bulunamadı'
            ], 401);
        }

        // Token'ı veritabanından bul
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz token'
            ], 401);
        }

        // Token oluşturulma tarihini kontrol et (1 gün = 24 saat)
        $tokenCreatedAt = $accessToken->created_at;
        $expirationTime = $tokenCreatedAt->addDay(); // 1 gün ekle

        if (now()->gt($expirationTime)) {
            // Token süresi dolmuş, sil
            $accessToken->delete();

            return response()->json([
                'success' => false,
                'message' => 'Token süresi dolmuş. Lütfen tekrar giriş yapın.',
                'error_code' => 'TOKEN_EXPIRED'
            ], 401);
        }

        // Token geçerli, devam et
        return $next($request);
    }
}
