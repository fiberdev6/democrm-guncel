<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Kullanıcı giriş yapmış mı?
        if (!Auth::check()) {
            return redirect()->route('login')->with([
                'message' => 'Lütfen giriş yapınız.',
                'alert-type' => 'warning'
            ]);
        }

        // Super Admin yetkisi var mı?
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Bu sayfaya erişim yetkiniz bulunmamaktadır.');
        }

        return $next($request);
    }
}