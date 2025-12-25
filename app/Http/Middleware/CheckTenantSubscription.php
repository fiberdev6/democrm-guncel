<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $feature = null): Response
    {
        if (!Auth::check()) {
            return redirect()->route('giris');
        }

        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('giris')->with('error', 'Firma bilgisi bulunamadı.');
        }

        // Aktif abonelik kontrolü
        if (!$tenant->hasActiveSubscription()) {
            if ($tenant->isExpired()) {
                return redirect()->route('subscription.expired');
            }
            
            return redirect()->route('subscription.plans', $tenant->id)->with('warning', 'Devam etmek için abonelik satın almalısınız.');
        }

        // Özellik tabanlı kontrol
        if ($feature && !$tenant->canAccessFeature($feature)) {
            return redirect()->route('subscription.upgrade', $tenant->id)
                           ->with('error', 'Bu özellik için aboneliğinizi yükseltmeniz gerekiyor.');
        }

        return $next($request);
    }
}
