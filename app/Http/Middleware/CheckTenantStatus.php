<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Super Admin'ler için kontrol yapma
            if ($user->isSuperAdmin()) {
                return $next($request);
            }
            
            // Kullanıcının tenant'ının status'unu kontrol et
            if ($user->tenant && $user->tenant->status == 0) {
                Auth::logout();
                
                $notification = array(
                    'message' => 'Firma hesabı askıya alınmıştır. Oturum sonlandırıldı.',
                    'alert-type' => 'warning'
                );
                
                return redirect()->route('giris')->with($notification);
            }
        }

        return $next($request);
    }
}
