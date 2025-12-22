<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class ImpersonationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Impersonation aktif mi kontrol et
        if (session()->has('impersonated_user_id') && session()->has('impersonator_id')) {
            $impersonatedUserId = session('impersonated_user_id');
            $impersonatorId = session('impersonator_id');

            // İmpersonate edilen kullanıcıyı bul
            $impersonatedUser = User::find($impersonatedUserId);
            
            if ($impersonatedUser) {
                // EKLENEN: Eğer impersonation aktifse ama mevcut auth kullanıcısı farklıysa düzelt
                if (Auth::id() !== $impersonatedUserId) {
                    Auth::setUser($impersonatedUser);
                    Auth::logout();
                    Auth::login($impersonatedUser);
                }
                
                // Spatie Permission cache'ini temizle
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
                
                // Kullanıcı rollerini fresh yükle
                $impersonatedUser->load('roles', 'permissions');
                
                // Request'e impersonation bilgilerini ekle
                $request->merge([
                    'is_impersonating' => true,
                    'impersonator_id' => $impersonatorId,
                    'original_user' => User::find($impersonatorId)
                ]);
            }
        }

        return $next($request);
    }
}