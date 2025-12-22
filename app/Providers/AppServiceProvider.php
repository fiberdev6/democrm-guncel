<?php

namespace App\Providers;

use App\Models\Tenant;
use App\Observers\CompanyObserver;
use Illuminate\Support\ServiceProvider;
use App\Models\HomepageContent;
use Illuminate\Support\Facades\View;

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
    }
}
