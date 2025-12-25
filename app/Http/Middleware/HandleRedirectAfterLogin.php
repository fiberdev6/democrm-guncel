<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HandleRedirectAfterLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
      public function handle(Request $request, Closure $next)
        {
            if (Auth::check()) {
                // Kullanıcının 'Kendi Servislerini Görebilir' iznine sahip olup olmadığını kontrol edin
                if (Auth::user()->can('Kendi Servislerini Görebilir') && $request->routeIs('secure.home')) {
                    // Kullanıcı dashboard'a gitmeye çalışıyor ve belirli bir izne sahipse servisler sayfasına yönlendir
                    $tenantId = Auth::user()->tenant->id; // Tenant ID'yi alın
                    return redirect()->route('all.services', ['tenant_id' => $tenantId]);
                }
            }

            return $next($request);
        }
    
}
