<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckTrialStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {   
        if (Auth::check() && Auth::user()->tenant) {
            $company = Auth::user()->tenant; // Kullanıcının firmasına erişin

            if ($company->trial_ends_at && $company->trial_ends_at->diffInDays(Carbon::now()) <= 5 && $company->trial_ends_at->isFuture()) {
                // Deneme süresi bitimine 5 gün veya daha az kaldı ve henüz bitmedi
                session()->flash('warning', 'Deneme süreniz ' . $company->trial_ends_at->diffInDays(Carbon::now()) . ' gün sonra sona erecektir. Lütfen üyeliğinizi yenileyin.');
            }
        }
        return $next($request);
    }
}
