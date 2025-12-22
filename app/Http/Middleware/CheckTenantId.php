<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckTenantId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $tenantId = $request->route('tenant_id');

        // SuperAdmin her tenant'a erişebilir
        if ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return $next($request);
        }

        // Tenant var mı kontrol et
        $tenant = DB::table('tenants')->where('id', $tenantId)->first();

        if (!$tenant) {
            return redirect()->route('giris')->withErrors(['error' => 'Geçersiz tenant ID.']);
        }

        // Kullanıcıya ait tenant mı kontrol et
        if ($user && $user->tenant_id != $tenantId) {
            abort(403, 'Bu firmaya erişim yetkiniz yok.');
        }

        // Tenant bilgisini paylaş
        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}
