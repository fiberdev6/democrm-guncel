<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$features): Response
    {   $tenant = $request->user()->tenant;
        
        foreach ($features as $feature) {
            if (!$tenant->canAccessFeature($feature)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Bu özellik için aboneliğinizi yükseltmeniz gerekiyor.',
                        'feature' => $feature,
                        'upgrade_url' => route('subscription.plans')
                    ], 403);
                }
                
                return redirect()->route('subscription.upgrade')
                               ->with('error', 'Bu özellik mevcut paketinizde bulunmuyor.');
            }
        }
        return $next($request);
    }
}
