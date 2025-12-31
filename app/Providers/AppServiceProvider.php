<?php

namespace App\Providers;

use App\Models\Tenant;
use App\Observers\CompanyObserver;
use Illuminate\Support\ServiceProvider;
use App\Models\HomepageContent;
use Illuminate\Support\Facades\View;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
        $navbarContent = HomepageContent::getSection('navbar_content');
        
        // URL'leri route'a çevir
        if($navbarContent && isset($navbarContent['menu_items'])) {
            foreach($navbarContent['menu_items'] as &$item) {
                if($item['type'] == 'dropdown' && isset($item['items'])) {
                    foreach($item['items'] as &$subItem) {
                        if(!isset($subItem['divider']) && isset($subItem['url'])) {
                            // Eğer /feature/ ile başlıyorsa route'a çevir
                            if(strpos($subItem['url'], '/feature/') === 0) {
                                $slug = str_replace('/feature/', '', $subItem['url']);
                                $subItem['url'] = route('feature.detail', $slug);
                            }
                        }
                    }
                }
            }
        }
        
        // Footer aynı şekilde
        $footerContent = HomepageContent::getSection('footer_content');

        // FOOTER: URL'leri route'a çevir
        if($footerContent) {
            // Features Menu
            if(isset($footerContent['features_menu']['links'])) {
                foreach($footerContent['features_menu']['links'] as &$link) {
                    if(isset($link['url']) && strpos($link['url'], '/feature/') === 0) {
                        $slug = str_replace('/feature/', '', $link['url']);
                        $link['url'] = route('feature.detail', $slug);
                    }
                }
            }
            
            // Product Menu (varsa /sektorler gibi route'lar için)
            if(isset($footerContent['product_menu']['links'])) {
                foreach($footerContent['product_menu']['links'] as &$link) {
                    if(isset($link['url'])) {
                        // Örnek: /sektorler -> route('sectors')
                        if($link['url'] == '/sektorler') {
                            $link['url'] = route('sectors');
                        }
                        // Diğer route'lar için ekle
                    }
                }
            }
        }
        
        $view->with([
            'navbarContent' => $navbarContent,
            'footerContent' => $footerContent
        ]);
});
        // Şifre sıfırlama rate limiter
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(3)
                ->by($request->ip())
                ->response(function () {
                    return back()->with([
                        'message' => 'Çok fazla deneme yaptınız. Lütfen 1 dakika sonra tekrar deneyin.',
                        'alert-type' => 'warning'
                    ]);
                });
        });

        // SMS doğrulama rate limiter
        RateLimiter::for('sms-verify', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Çok fazla deneme yaptınız. Lütfen 1 dakika sonra tekrar deneyin.'
                    ], 429);
                });
        });

        // İletişim formu rate limiter
        RateLimiter::for('contact-form', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(function () {
                    return back()->with([
                        'message' => 'Çok fazla mesaj gönderdiniz. Lütfen 1 dakika sonra tekrar deneyin.',
                        'alert-type' => 'warning'
                    ]);
                });
        });
    }
}
