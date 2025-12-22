<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckCompanySubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Firma kontrolü varsa
        if ($user && $user->tenant) {
            $subscriptionEnd = $user->tenant->bitisTarihi;

            // Süresi dolmuşsa
            if ($subscriptionEnd && Carbon::now()->gt(Carbon::parse($subscriptionEnd))) {
                return response()->view('frontend.secure.subscription_expired'); // özel bir view gösterebilirsin
            }
        }

        return $next($request);
    }
}
