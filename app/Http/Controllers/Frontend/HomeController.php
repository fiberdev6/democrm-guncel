<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\UserRegisteredMail;
use Illuminate\Http\Request;
use App\Models\HomeSlide;
use App\Models\Faq;
use App\Models\Misyon;
use App\Models\Category;
use App\Models\Clients;
use App\Models\Feature;
use App\Models\Pricing;
use App\Models\Reference;
use App\Models\ServiceTime;
use App\Models\Settings;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantPrim;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Services\ActivityLogger;
use App\Mail\PasswordResetMail;
use App\Mail\RegistrationSuccessMail;
use App\Services\TescomService;
use Illuminate\Support\Facades\Log;
use App\Models\FrontendSetting;
use App\Models\HomepageContent;
use App\Models\ReceiptDesign;
use App\Mail\ContactFormMail;

class HomeController extends Controller
{
    public function index()
    {

        //İstatistikler
        $stats = FrontendSetting::where('section', 'home_stats')
            ->where('is_active', true)
            ->orderBy('order')
            ->get()
            ->map(function($item) {
                return $item->data;
            });
        
        if($stats->isEmpty()) {
            $stats = collect([
                ['number' => '500+', 'label' => 'Aktif Firma'],
                ['number' => '50K+', 'label' => 'Tamamlanan Servis'],
                ['number' => '99.9%', 'label' => 'Uptime Garantisi'],
                ['number' => '7/24', 'label' => 'Destek Hizmeti']
            ]);
        }
        //Özellikler
        $modules = FrontendSetting::where('section', 'home_modules')
            ->where('is_active', true)
            ->orderBy('order')
            ->get()
            ->map(function($item) {
                return $item->data;
            });
        
        // Sektörler
        $sectors = FrontendSetting::where('section', 'home_sectors')
            ->where('is_active', true)
            ->orderBy('order')
            ->get()
            ->map(function($item) {
                return $item->data;
            });

        // Entegrasyonlar
        $integrations = FrontendSetting::where('section', 'home_integrations')
            ->where('is_active', true)
            ->orderBy('order')
            ->get()
            ->map(function($item) {
                return $item->data;
            });

        // Yorumlar
        $testimonials = FrontendSetting::where('section', 'home_testimonials')
            ->where('is_active', true)
            ->orderBy('order')
            ->get()
            ->map(function($item) {
                return $item->data;
            });

        // SSS
        $faqs = FrontendSetting::where('section', 'home_faqs')
            ->where('is_active', true)
            ->orderBy('order')
            ->get()
            ->map(function($item) {
                return $item->data;
            });

// Hero İçeriği
$hero = HomepageContent::getSection('hero');

       // Bölüm Başlıkları
        $sectionHeaders = HomepageContent::getSection('section_headers');
        if(!$sectionHeaders) {
            $sectionHeaders = [
                'modules' => [
                    'badge' => 'Özellikler',
                    'title' => 'Güçlü',
                    'highlight' => 'Özellikler',
                    'subtitle' => 'İşletmenizi yönetmek için ihtiyacınız olan tüm özellikler tek platformda'
                ],
                'sectors' => [
                    'badge' => 'SEKTÖRLER',
                    'title' => 'Hangi',
                    'highlight' => 'Sektörlere',
                    'title_end' => 'Hizmet Veriyoruz?',
                    'subtitle' => 'Farklı sektörlerdeki teknik servis işletmelerinin ihtiyaçlarına özel çözümler'
                ],
                'integrations' => [
                    'badge' => 'ENTEGRASYONLAR',
                    'title' => 'Güçlü',
                    'highlight' => 'Entegrasyonlar',
                    'subtitle' => 'Kullandığınız tüm araçlarla sorunsuz entegre olun'
                ],
                'testimonials' => [
                    'badge' => 'MÜŞTERİ YORUMLARI',
                    'title' => 'Müşterilerimiz',
                    'highlight' => 'Ne Diyor?',
                    'subtitle' => 'Binlerce mutlu müşterimizden bazı görüşler'
                ],
                'faqs' => [
                    'badge' => 'SIK SORULAN SORULAR',
                    'title' => 'Sıkça Sorulan',
                    'highlight' => 'Sorular',
                    'subtitle' => 'Merak ettiğiniz soruların cevaplarını burada bulabilirsiniz'
                ]
            ];
        }
        // İletişim İçeriği
        $contact = HomepageContent::getSection('contact');
        if(!$contact) {
            $contact = [
                'badge' => 'İLETİŞİM',
                'title' => 'Bizimle',
                'highlight' => 'İletişime Geçin',
                'subtitle' => 'Sorularınız mı var? Size yardımcı olmaktan memnuniyet duyarız',
                'items' => [
                    [
                        'icon' => 'fas fa-phone',
                        'title' => 'Telefon',
                        'info' => '0212 909 2861'
                    ],
                    [
                        'icon' => 'fas fa-envelope',
                        'title' => 'E-posta',
                        'info' => 'info@serbis.com'
                    ],
                    [
                        'icon' => 'fas fa-map-marker-alt',
                        'title' => 'Adres',
                        'info' => 'İstanbul, Türkiye'
                    ]
                ]
            ];
        }

        // CTA İçeriği
        $cta = HomepageContent::getSection('cta');
        if(!$cta) {
            $cta = [
                'title' => 'Hemen Başlamaya Hazır mısınız?',
                'description' => '14 gün ücretsiz deneyin. Kredi kartı gerekmez. İstediğiniz zaman iptal edebilirsiniz.',
                'button_text' => 'Ücretsiz Denemeyi Başlat',
                'button_icon' => 'fas fa-rocket'
            ];
        }

        // Video İçeriği
        $video = HomepageContent::getSection('video');

        // // Navbar Content
        // $navbarContent = HomepageContent::getSection('navbar_content');
        
        // // Footer Content
        // $footerContent = HomepageContent::getSection('footer_content');


        return view('frontend.index', compact('stats', 'modules', 'sectors', 'integrations', 'testimonials', 'faqs', 'hero', 'sectionHeaders', 'contact', 'cta', 'video',));
    }


public function Sectors()
{
    $sectorsContent = HomepageContent::getSection('sectors_content');
    
    return view('frontend.frontend_pages.sectors', compact('sectorsContent'));
}
public function SectorDetail($slug)
{
    // Database'den sektör detayını al
    $sectorSection = 'sector_' . $slug;
    $sectorData = HomepageContent::where('section', $sectorSection)->first();
    
    // Eğer database'de varsa, oradan al
    if ($sectorData && $sectorData->is_active) {
        $sector = $sectorData->content;
    } else {
        // Database'de yoksa 404
        abort(404);
    }
    
    return view('frontend.frontend_pages.sector_detail', compact('sector'));
}
public function legalPage($slug)
{
    $page = HomepageContent::where('section', $slug)->where('is_active', true)->first();
    
    if (!$page) {
        abort(404);
    }
    
    return view('frontend.frontend_pages.legal_page', compact('page'));
}
 public function Features()
{
    $featuresContent = HomepageContent::getSection('features_content');
    
    return view('frontend.frontend_pages.features', compact('featuresContent'));
}

public function FeatureDetail($slug)
{
    // Database'den özellik detayını al
    $featureSection = 'feature_' . $slug;
    $featureData = HomepageContent::where('section', $featureSection)->first();
    
    // Eğer database'de varsa, oradan al
    if ($featureData && $featureData->is_active) {
        $feature = $featureData->content;
    } else {
        // Database'de yoksa 404
        abort(404);
    }
    
    return view('frontend.frontend_pages.feature_detail', compact('feature'));
}
// public function FeatureDetail($slug)
// {
//     $featureDetails = [
//         'musteri-yonetimi' => [
//             'title' => 'Müşteri Yönetimi',
//             'subtitle' => 'Tüm müşteri bilgilerinizi tek merkezden yönetin, geçmişi takip edin',
//             'hero_image' => 'frontend/img/features/musteri_yonetimi.jpg',
//             'description' => 'Serbis Müşteri Yönetimi modülü ile müşterilerinizin tüm bilgilerini, geçmiş işlemlerini, cihaz kayıtlarını ve iletişim geçmişini tek ekranda görüntüleyin. Detaylı müşteri profilleri oluşturun, notlar ekleyin ve müşteri memnuniyetini artırın.',
//             'benefits' => [
//                 [
//                     'title' => 'Detaylı Müşteri Profilleri',
//                     'description' => 'Her müşteri için ad, soyad, telefon, email, adres gibi temel bilgilerin yanı sıra özel notlar, etiketler ve kategoriler ekleyin.',
//                     'mini_features' => [
//                         ['icon' => 'fas fa-bolt', 'label' => 'Hızlı Kayıt'],
//                         ['icon' => 'fas fa-database', 'label' => 'Detaylı Bilgi'],
//                         ['icon' => 'fas fa-tag', 'label' => 'Etiketleme']
//                     ]
//                 ],
//                 [
//                     'title' => 'Geçmiş İşlem Takibi',
//                     'description' => 'Müşterinin daha önce yaptırdığı tüm servisleri, satın aldığı parçaları ve ödemelerini kronolojik sırada görün.',
//                     'mini_features' => [
//                         ['icon' => 'fas fa-history', 'label' => 'Tüm Geçmiş'],
//                         ['icon' => 'fas fa-chart-line', 'label' => 'Analiz'],
//                         ['icon' => 'fas fa-filter', 'label' => 'Filtreleme']
//                     ]
//                 ],
//                 [
//                     'title' => 'Hızlı Arama ve Filtreleme',
//                     'description' => 'Binlerce müşteri arasında isim, telefon, email veya müşteri numarası ile anında arama yapın.',
//                     'mini_features' => [
//                         ['icon' => 'fas fa-search', 'label' => 'Anında Arama'],
//                         ['icon' => 'fas fa-sliders-h', 'label' => 'Gelişmiş Filtre'],
//                         ['icon' => 'fas fa-list', 'label' => 'Listeleme']
//                     ]
//                 ],
//                 [
//                     'title' => 'Otomatik SMS/Email',
//                     'description' => 'Doğum günü kutlamaları, servis hazır bildirimleri gibi otomatik mesajlar gönderin.',
//                     'mini_features' => [
//                         ['icon' => 'fas fa-sms', 'label' => 'Toplu SMS'],
//                         ['icon' => 'fas fa-envelope', 'label' => 'Email'],
//                         ['icon' => 'fas fa-clock', 'label' => 'Zamanlama']
//                     ]
//                 ]
//             ],
//             'features_list' => [
//                 'Detaylı müşteri kartları ve profil yönetimi',
//                 'Müşteri geçmişi ve işlem kronolojisi',
//                 'Toplu SMS ve Email gönderimi',
//                 'Müşteri segmentasyonu ve etiketleme',
//                 'Özel not ve hatırlatma sistemi',
//                 'Excel içe-dışa aktarım',
//                 'Müşteri bazlı rapor ve analizler',
//                 'Cari hesap takibi ve borç/alacak durumu'
//             ],
//             'stats' => [
//                 ['number' => '%60', 'label' => 'Daha Hızlı Kayıt'],
//                 ['number' => '3 sn', 'label' => 'Ortalama Arama Süresi'],
//                 ['number' => '500+', 'label' => 'Aktif Kullanıcı'],
//                 ['number' => '%99', 'label' => 'Müşteri Memnuniyeti'],
//             ],
//             'faqs' => [
//                 [
//                     'question' => 'Müşteri bilgilerimi nasıl içe aktarabilirim?',
//                     'answer' => 'Excel veya CSV formatında müşteri listenizi hazırlayıp sisteme toplu olarak yükleyebilirsiniz. Sistem otomatik olarak müşteri kartlarını oluşturur ve gerekli alanları eşleştirir. İçe aktarma sırasında hatalı veya eksik kayıtlar için uyarı alırsınız.'
//                 ],
//                 [
//                     'question' => 'Müşteri verilerim güvende mi?',
//                     'answer' => 'Evet, tüm müşteri verileri AWS sunucularında şifrelenmiş olarak saklanır. Günlük otomatik yedekleme yapılır ve KVKK uyumlu veri koruma politikalarımız bulunur. Verilerinize sadece sizin yetkilendirdiğiniz personel erişebilir.'
//                 ],
//                 [
//                     'question' => 'Müşterilerimi gruplandırabilir miyim?',
//                     'answer' => 'Evet, müşteri segmentasyonu için etiket ve kategori sistemimiz bulunur. VIP müşteriler, kurumsal müşteriler veya özel kampanya grupları gibi istediğiniz kategorileri oluşturabilir ve bu gruplara özel işlemler yapabilirsiniz.'
//                 ],
//                 [
//                     'question' => 'Müşteri iletişim geçmişi tutulur mu?',
//                     'answer' => 'Evet, her müşteri için gönderilen SMS, email, yapılan aramalar ve not kayıtları zaman damgalı olarak tutulur. Hangi personelin ne zaman müşteriyle iletişime geçtiğini detaylı şekilde görebilirsiniz.'
//                 ],
//                 [
//                     'question' => 'Kaç müşteri kaydı tutabilirim?',
//                     'answer' => 'Serbis\'te müşteri sayısı konusunda bir sınırlama yoktur. İster 100, ister 100.000 müşteri olsun, sistem aynı performansla çalışır. Hızlı arama ve filtreleme özellikleri sayesinde büyük müşteri tabanlarını da kolayca yönetebilirsiniz.'
//                 ]
//             ]
//         ],
        

//         'is-talep-yonetimi' => [
//             'title' => 'İş Talep Yönetimi',
//             'subtitle' => 'Servis taleplerini kaydedin, teknisyen atayın, süreçleri takip edin',
//             'hero_image' => 'frontend/img/features/is-talep.jpg',
//             'description' => 'İş Talep Yönetimi modülü ile gelen her servis talebini sistematik bir şekilde kaydedin. Müşteri bilgisi, cihaz bilgisi, arıza açıklaması ve öncelik durumunu belirleyin. Teknisyen ataması yapın ve iş sürecini baştan sona eksiksiz takip edin.',
//             'benefits' => [
//                 [
//                     'title' => 'Hızlı Servis Kaydı',
//                     'description' => 'Müşteri seçimi, cihaz bilgisi, arıza açıklaması işlemlerini tek ekrandan hızlıca tamamlayın. Seri no veya barkod ile hatasız kayıt oluşturun.',
//                     'mini_features' => [
//                         ['icon' => 'fas fa-barcode', 'label' => 'Barkod Okuma'],
//                         ['icon' => 'fas fa-edit', 'label' => 'Hızlı Giriş'],
//                         ['icon' => 'fas fa-camera', 'label' => 'Fotoğraf Ekleme']
//                     ]
//                 ],
//                 [
//                     'title' => 'Teknisyen Atama',
//                     'description' => 'Servisleri uygun teknisyenlere atayın, iş yüklerini dengeleyin. Kimin elinde kaç iş var anlık olarak görün.',
//                     'mini_features' => [
//                         ['icon' => 'fas fa-user-cog', 'label' => 'Personel Atama'],
//                         ['icon' => 'fas fa-balance-scale', 'label' => 'İş Yükü'],
//                         ['icon' => 'fas fa-tasks', 'label' => 'Görev Takibi']
//                     ]
//                 ],
//                 [
//                     'title' => 'Durum Takibi ve Bildirim',
//                     'description' => 'Cihazın durumunu (Beklemede, Onarımda, Hazır) adım adım izleyin. Her aşamada müşteriye otomatik bilgilendirme gitsin.',
//                     'mini_features' => [
//                         ['icon' => 'fas fa-bell', 'label' => 'Oto Bildirim'],
//                         ['icon' => 'fas fa-step-forward', 'label' => 'Süreç Adımları'],
//                         ['icon' => 'fas fa-check-circle', 'label' => 'Onay Mekanizması']
//                     ]
//                 ],
//                 [
//                     'title' => 'Öncelik ve Garanti',
//                     'description' => 'Acil işleri öne alın, garanti kapsamındaki cihazları otomatik tespit edin. VIP müşterilere özel servis önceliği sağlayın.',
//                     'mini_features' => [
//                         ['icon' => 'fas fa-star', 'label' => 'VIP Öncelik'],
//                         ['icon' => 'fas fa-shield-alt', 'label' => 'Garanti Takibi'],
//                         ['icon' => 'fas fa-exclamation-triangle', 'label' => 'Acil İşler']
//                     ]
//                 ]
//             ],
//             'features_list' => [
//                 'Hızlı servis kaydı ve barkod desteği',
//                 'Sürükle bırak teknisyen atama',
//                 'Otomatik SMS ve WhatsApp bildirimleri',
//                 'Fotoğraflı ve videolu arıza kaydı',
//                 'Parça talep ve onay sistemi',
//                 'Servis formu ve etiket yazdırma',
//                 'Garanti süresi sorgulama',
//                 'Cihaz seri no/IMEI takibi'
//             ],
//             'stats' => [
//                 ['number' => '%40', 'label' => 'Daha Hızlı Servis'],
//                 ['number' => '2 Kat', 'label' => 'Müşteri Geri Dönüşü'],
//                 ['number' => '7/24', 'label' => 'Kesintisiz Takip'],
//                 ['number' => '%100', 'label' => 'Kayıt Güvenliği'],
//             ],
//         ],

//         'mobil-saha-yonetimi' => [
//             'title' => 'Mobil Saha Yönetimi',
//             'subtitle' => 'Teknisyenleriniz sahadan mobil cihazlarla işlem yapabilir',
//             'hero_image' => 'frontend/img/features/mobil-saha-yonetimi.jpg',
//             'description' => 'Mobil uyumlu Serbis arayüzü ile teknisyenleriniz sahada tablet veya telefonlarından tüm işlemleri gerçekleştirebilir. İş listesini görüntüleme, durum güncelleme, fotoğraf yükleme ve müşteri imzası alma işlemleri artık cebinizde.',
//             'benefits' => [
//                 [
//                     'title' => 'Her Yerden Erişim',
//                     'description' => 'Responsive tasarım sayesinde uygulama yüklemeden telefon veya tabletten sisteme girin. Sahada ofis konforunu yaşayın.',
//                     'mini_features' => [
//                         ['icon' => 'fas fa-mobile-alt', 'label' => 'Mobil Uyumlu'],
//                         ['icon' => 'fas fa-cloud', 'label' => 'Bulut Tabanlı'],
//                         ['icon' => 'fas fa-wifi', 'label' => 'Her Yerden']
//                     ]
//                 ],
//                 [
//                     'title' => 'Anlık İş Emri',
//                     'description' => 'Teknisyenler kendilerine atanan işleri anında bildirim olarak görür. Adres tarifi alarak müşteriye en kısa yoldan ulaşır.',
//                     'mini_features' => [
//                         ['icon' => 'fas fa-map-marker-alt', 'label' => 'Navigasyon'],
//                         ['icon' => 'fas fa-bolt', 'label' => 'Anlık Bildirim'],
//                         ['icon' => 'fas fa-route', 'label' => 'Rota Planı']
//                     ]
//                 ],
//                 [
//                     'title' => 'Fotoğraf ve Dijital İmza',
//                     'description' => 'Onarım öncesi ve sonrası fotoğrafları sisteme yükleyin. İş bitiminde müşteri imzasını tablet ekranından dijital olarak alın.',
//                     'mini_features' => [
//                         ['icon' => 'fas fa-camera', 'label' => 'Fotoğraf Yükle'],
//                         ['icon' => 'fas fa-pen-nib', 'label' => 'Dijital İmza'],
//                         ['icon' => 'fas fa-file-pdf', 'label' => 'Servis Fişi']
//                     ]
//                 ],
//                 [
//                     'title' => 'Sahadan Parça Talebi',
//                     'description' => 'Teknisyen sahada ihtiyaç duyduğu parçayı sistemden talep edebilir veya aracındaki stoktan düşebilir.',
//                     'mini_features' => [
//                         ['icon' => 'fas fa-cubes', 'label' => 'Stok Kontrol'],
//                         ['icon' => 'fas fa-share-square', 'label' => 'Parça İsteme'],
//                         ['icon' => 'fas fa-calculator', 'label' => 'Fiyatlandırma']
//                     ]
//                 ]
//             ],
//             'features_list' => [
//                 'Mobil uyumlu responsive arayüz',
//                 'Google Maps entegrasyonu',
//                 'Sahadan fotoğraf ve video yükleme',
//                 'Ekranda müşteri imzası alma',
//                 'Mobil cihazdan fatura/tahsilat girişi',
//                 'Araç stoğu yönetimi',
//                 'Konum bazlı teknisyen takibi',
//                 'QR Kod ile cihaz sorgulama'
//             ],
//             'stats' => [
//                 ['number' => '%75', 'label' => 'Kağıt Tasarrufu'],
//                 ['number' => '15 dk', 'label' => 'Servis Başı Kazanç'],
//                 ['number' => '%95', 'label' => 'Doğru Konum'],
//                 ['number' => '0', 'label' => 'Veri Kaybı'],
//             ],
//         ],

//         'stok-parca' => [
//             'title' => 'Stok ve Yedek Parça',
//             'subtitle' => 'Parça stoklarınızı takip edin, kritik seviyelerde uyarı alın',
//             'hero_image' => 'frontend/img/features/stok-yonetimi.jpg',
//             'description' => 'Stok Yönetimi modülü ile tüm yedek parça, sarf malzemeleri ve aksesuarlarınızın envanterini profesyonelce yönetin. Giriş-çıkış hareketlerini kaydedin, kritik stok seviyelerinde otomatik uyarılar alın ve maliyetlerinizi kontrol altında tutun.',
//             'benefits' => [
//                 [
//                     'title' => 'Akıllı Stok Kartları',
//                     'description' => 'Her parça için alış/satış fiyatı, KDV oranı, raf yeri ve uyumlu modelleri içeren detaylı kartlar oluşturun.',
//                     'mini_features' => [
//                         ['icon' => 'fas fa-box-open', 'label' => 'Ürün Kartı'],
//                         ['icon' => 'fas fa-barcode', 'label' => 'Barkodlama'],
//                         ['icon' => 'fas fa-tags', 'label' => 'Fiyat Yönetimi']
//                     ]
//                 ],
//                 [
//                     'title' => 'Kritik Stok Uyarıları',
//                     'description' => 'Belirlediğiniz adedin altına düşen ürünler için sistem sizi uyarır. Parça bitmeden sipariş vererek iş kaybını önleyin.',
//                     'mini_features' => [
//                         ['icon' => 'fas fa-exclamation-circle', 'label' => 'Azalan Stok'],
//                         ['icon' => 'fas fa-envelope-open-text', 'label' => 'Mail Uyarısı'],
//                         ['icon' => 'fas fa-shopping-cart', 'label' => 'Oto Sipariş']
//                     ]
//                 ],
//                 [
//                     'title' => 'Hareket Geçmişi',
//                     'description' => 'Hangi parça hangi serviste kullanıldı, ne zaman alındı, kime satıldı? Tüm envanter hareketlerini şeffafça izleyin.',
//                     'mini_features' => [
//                         ['icon' => 'fas fa-exchange-alt', 'label' => 'Giriş/Çıkış'],
//                         ['icon' => 'fas fa-user-check', 'label' => 'Personel Takibi'],
//                         ['icon' => 'fas fa-calendar-alt', 'label' => 'Tarihçe']
//                     ]
//                 ],
//                 [
//                     'title' => 'Sayım ve Raporlama',
//                     'description' => 'Dönemsel stok sayımları yapın, fireleri kaydedin. En çok giden parçaları analiz ederek karlılığınızı artırın.',
//                     'mini_features' => [
//                         ['icon' => 'fas fa-clipboard-list', 'label' => 'Sayım Modülü'],
//                         ['icon' => 'fas fa-chart-pie', 'label' => 'Karlılık Analizi'],
//                         ['icon' => 'fas fa-file-excel', 'label' => 'Excel Çıktı']
//                     ]
//                 ]
//             ],
//             'features_list' => [
//                 'Barkod ve QR kod destekli stok takibi',
//                 'Kritik stok seviyesi bildirimleri',
//                 'Tedarikçi ve satın alma yönetimi',
//                 'Çoklu depo ve raf sistemi',
//                 'Servis bağlantılı otomatik stok düşümü',
//                 'Sayım ve envanter eşitleme',
//                 'Alış/Satış raporları',
//                 'Toplu Excel ile ürün yükleme'
//             ],
//             'stats' => [
//                 ['number' => '%30', 'label' => 'Maliyet Avantajı'],
//                 ['number' => '%100', 'label' => 'Stok Doğruluğu'],
//                 ['number' => '0', 'label' => 'Parça Bekleme'],
//                 ['number' => '10k+', 'label' => 'Ürün Kapasitesi'],
//             ],
//         ],
//     ];

//     if (!isset($featureDetails[$slug])) {
//         abort(404);
//     }

//     $feature = $featureDetails[$slug];

//     return view('frontend.frontend_pages.feature_detail', compact('feature'));
// }
public function Integrations()
{
    // Database'den entegrasyonlar içeriğini al
    $integrationsData = HomepageContent::where('section', 'integrations_content')->first();
    
    // Eğer database'de varsa, oradan al
    if ($integrationsData && $integrationsData->is_active) {
        $integrationsContent = $integrationsData->content;
    } else {
        // Database'de yoksa varsayılan yapı
        $integrationsContent = [
            'page_header' => [
                'title' => 'Serbis Entegrasyonları ile Tüm Süreçlerinizi Entegre Edin',
                'subtitle' => 'Serbis uygulama mağazasındaki uygulama ve entegrasyonlar ile teknik servis sitenizi çok yönlü hale getirin.',
                'button_text' => 'Deneme Hesabı Oluştur',
                'button_url' => '/kullanici-girisi'
            ],
            'marquee_logos' => [],
            'categories' => [],
            'faqs' => []
        ];
    }
    
    return view('frontend.frontend_pages.integrations', compact('integrationsContent'));
}

public function About()
{
    $aboutContent = HomepageContent::getSection('about-content');
    
    return view('frontend.frontend_pages.about', compact('aboutContent'));
}

public function Pricing()
{
    // Database'den fiyatlandırma içeriğini al
    $pricingData = HomepageContent::where('section', 'pricing_content')->first();
    
    // Eğer database'de varsa, oradan al
    if ($pricingData && $pricingData->is_active) {
        $pricingContent = $pricingData->content;
    } else {
        // Database'de yoksa varsayılan yapı
        $pricingContent = [
            'page_header' => [
                'badge_icon' => 'fas fa-tag',
                'badge_text' => '14 Gün Ücretsiz Deneme',
                'title' => 'Size Uygun',
                'title_highlight' => 'Planı',
                'title_suffix' => 'Seçin',
                'subtitle' => 'Her ölçekteki teknik servis için uygun fiyatlı çözümler. Kredi kartı gerektirmeden hemen başlayın, işinizi büyütün.',
                'hero_features' => []
            ],
            'pricing_plans' => [],
            'faqs' => [],
            'cta' => [
                'title' => '14 Gün Ücretsiz Deneyin!',
                'description' => 'Kredi kartı gerektirmez. Anında başlayın, tüm özellikleri keşfedin.',
                'button_text' => 'Hemen Ücretsiz Başla',
                'button_url' => '/kullanici-girisi'
            ]
        ];
    }
    
    return view('frontend.frontend_pages.pricing', compact('pricingContent'));
}
public function Contact()
{
    // Database'den iletişim içeriğini al
    $contactData = HomepageContent::where('section', 'contact_content')->first();
    
    // Eğer database'de varsa, oradan al
    if ($contactData && $contactData->is_active) {
        $contactContent = $contactData->content;
    } else {
        // Database'de yoksa varsayılan yapı
        $contactContent = [
            'page_header' => [
                'title' => 'İletişim',
                'subtitle' => 'Sorularınız için bize ulaşın, size yardımcı olmaktan mutluluk duyarız.',
                'breadcrumb_home' => 'Ana Sayfa',
                'breadcrumb_current' => 'İletişim'
            ],
            'contact_cards' => [],
            'left_panel' => [
                'title' => 'Serbis CRM ile',
                'title_highlight' => 'İşinizi Büyütün',
                'description' => 'Teknik servis süreçlerinizi dijitalleştirmek için formu doldurun.',
                'features' => [],
                'apps_label' => 'Mobil Uygulamamızı İndirin:',
                'google_play_link' => '#',
                'app_store_link' => '#'
            ],
            'form_section' => [
                'title' => 'Bize Ulaşın',
                'subtitle' => 'Aşağıdaki formu doldurarak bize mesaj gönderin.',
                'name_label' => 'Ad-Soyad',
                'name_placeholder' => 'Adınız Soyadınız',
                'email_label' => 'E-posta',
                'email_placeholder' => 'ornek@email.com',
                'phone_label' => 'Telefon',
                'phone_placeholder' => '0555 555 55 55',
                'message_label' => 'Mesajınız',
                'message_placeholder' => 'Size nasıl yardımcı olabiliriz?',
                'button_text' => 'Mesajı Gönder'
            ]
        ];
    }
    
    return view('frontend.frontend_pages.contact', compact('contactContent'));
}

public function ContactSubmit(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'phone' => 'nullable|string|max:20',
        'message' => 'required|string|min:10',
    ], [
        'name.required' => 'Ad soyad alanı zorunludur.',
        'email.required' => 'E-posta alanı zorunludur.',
        'email.email' => 'Geçerli bir e-posta adresi giriniz.',
        'message.required' => 'Mesaj alanı zorunludur.',
        'message.min' => 'Mesajınız en az 10 karakter olmalıdır.',
    ]);

    try {
        // Form verilerini hazırla
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'message' => $request->message,
        ];

        // Mail gönder
        Mail::to('serbiscrmyazilimi@gmail.com')->send(new ContactFormMail($data));

        // Başarılı mesajı
        return back()->with('success', 'Mesajınız başarıyla gönderildi! En kısa sürede size dönüş yapılacaktır.');
        
    } catch (\Exception $e) {
        // Hata durumunda
        \Log::error('Contact form mail error: ' . $e->getMessage());
        
        return back()->with('error', 'Mesaj gönderilirken bir hata oluştu. Lütfen tekrar deneyin.')
                    ->withInput();
    }
}

public function selectPlan($planIndex)
{
    // Fiyatlandırma verilerini al
    $pricingData = HomepageContent::where('section', 'pricing_content')->first();
    
    if ($pricingData && $pricingData->is_active && isset($pricingData->content['pricing_plans'][$planIndex])) {
        $selectedPlan = $pricingData->content['pricing_plans'][$planIndex];
        
        // Plan adına göre database'deki planı bul
        $dbPlan = SubscriptionPlan::where('name', $selectedPlan['name'])
            ->where('is_active', 1)
            ->first();
        
        if ($dbPlan) {
            // Session'a kaydet
            session([
                'selected_plan' => $dbPlan->id, // Database ID
                'selected_plan_index' => $planIndex, // Frontend index
                'selected_plan_info' => [ // Detaylı bilgiler
                    'name' => $selectedPlan['name'],
                    'price' => $selectedPlan['price'],
                    'users' => $selectedPlan['users'],
                    'storage' => $selectedPlan['storage'] ?? null,
                    'description' => $selectedPlan['description'],
                    'features' => $selectedPlan['features'] ?? []
                ],
                'show_register' => true
            ]);
            
            return redirect()->route('giris')->with([
                'plan_selected' => true,
            ]);
        }
        session(['show_register' => true]);
    }
    
    // Plan bulunamazsa normal kayıt sayfasına yönlendir
    return redirect()->route('giris')->with([
        'message' => 'Plan seçilemedi, lütfen tekrar deneyin.',
        'alert-type' => 'warning'
    ]);
}

    public function Seo($s) {
        $tr = array('ş','Ş','ı','I','İ','ğ','Ğ','ü','Ü','ö','Ö','Ç','ç','(',')','/',':',',',"'",'+','_','!','?','.');
        $eng = array('s','s','i','i','i','g','g','u','u','o','o','c','c','','','-','-','','','-','','','','');
        $s = str_replace($tr, $eng, $s);
        $s = mb_strtolower($s, 'UTF-8');
        $s = preg_replace('/&amp;amp;amp;amp;amp;amp;amp;amp;amp;.+?;/', '', $s);
        $s = preg_replace('/\s+/', '-', $s);
        $s = preg_replace('|-+|', '-', $s);
        $s = preg_replace('/#/', '', $s);
        $s = trim($s, '-');
        return $s;
    }

    protected function generateUserEmail($userEmail, $domain)
    {
        $username = explode('@', $userEmail)[0]; // E-postanın kullanıcı adını alır
        return $username . '@' . $domain; // Kullanıcı adı ve firma domainiyle yeni e-posta oluşturur
    }

    public function Register() {
        return view('frontend.auth.register');
    }

        public function getSectors()
{
    $sectorsContent = HomepageContent::getSection('sectors_content');
    
    $sectors = [];
    if ($sectorsContent && isset($sectorsContent['sectors'])) {
        foreach ($sectorsContent['sectors'] as $sector) {
            $sectors[] = [
                'slug' => $sector['slug'] ?? '',
                'title' => $sector['title'] ?? ''
            ];
        }
    }
    
    return response()->json([
        'success' => true,
        'sectors' => $sectors
    ]);
}

/**
 * validateStep - GÜNCELLENMİŞ
 */
public function validateStep(Request $request) 
{
    $step = $request->input('step');
    
    try {
        if ($step == 1) {
            // Step 1: Plan selection validation
            $validatedData = $request->validate([
                'subscription_plan' => 'required|exists:subscription_plans,id',
            ], [
                'subscription_plan.required' => 'Lütfen bir abonelik planı seçiniz.',
                'subscription_plan.exists' => 'Seçilen plan geçerli değil.',
            ]);
            
        } elseif ($step == 2) {
            // Step 2: Personal information validation
            if ($request->has('vergiNo')) {
                $request->merge([
                    'vergiNo' => preg_replace('/\D/', '', $request->vergiNo),
                ]);
            }
            $validatedData = $request->validate([
                'subscription_plan' => 'required|exists:subscription_plans,id',
                'name' => 'required|string|max:255',
                'username' => 'required|string|min:3|max:50|unique:tb_user,username|regex:/^[a-zA-Z0-9_]+$/',
                'email' => 'required|email|max:255|unique:tenants,eposta',
                'vergiNo' => 'required|max:10|unique:tenants,vergiNo',
            ], [
                'subscription_plan.required' => 'Lütfen bir abonelik planı seçiniz.',
                'subscription_plan.exists' => 'Seçilen plan geçerli değil.',
                'name.required' => 'Ad Soyad alanı zorunludur.',
                'name.max' => 'Ad Soyad alanı en fazla 255 karakter olmalıdır.',
                'username.required' => 'Kullanıcı adı alanı zorunludur.',
                'username.min' => 'Kullanıcı adı en az 3 karakter olmalıdır.',
                'username.max' => 'Kullanıcı adı en fazla 50 karakter olmalıdır.',
                'username.unique' => 'Bu kullanıcı adı zaten kullanılıyor.',
                'username.regex' => 'Kullanıcı adı sadece harf, rakam ve alt çizgi içerebilir.',
                'email.required' => 'E-posta alanı zorunludur.',
                'email.email' => 'Geçerli bir e-posta adresi giriniz.',
                'email.max' => 'E-posta alanı en fazla 255 karakter olmalıdır.',
                'email.unique' => 'Bu e-posta adresi zaten kayıtlı.',
                'vergiNo.required' => 'Vergi numarası alanı zorunludur.',
                'vergiNo.max' => 'Vergi numarası alanı en fazla 10 karakter olmalıdır.',
                'vergiNo.unique' => 'Bu vergi numarası zaten kayıtlı.',
            ]);
            
        } elseif ($step == 3) {
            // Step 3: Company information and password validation - GÜNCELLENDİ
            $request->merge([
                'tel' => preg_replace('/\D/', '', $request->tel),
                'vergiNo' => preg_replace('/\D/', '', $request->vergiNo),
            ]);
            $validatedData = $request->validate([
                'subscription_plan' => 'required|exists:subscription_plans,id',
                'name' => 'required|string|max:255',
                'username' => 'required|string|min:3|max:50|unique:tb_user,username|regex:/^[a-zA-Z0-9_]+$/',
                'email' => 'required|email|max:255|unique:tenants,eposta',
                'vergiNo' => 'required|max:10|unique:tenants,vergiNo',
                'firma_adi' => 'required|string|max:50',
                'sektor' => 'required|string|max:100', // YENİ - Sektör
                'il_id' => 'required|exists:ils,id',
                'ilce_id' => 'required|exists:ilces,id',
                'adres' => 'nullable|string|max:255', // YENİ - Açık Adres (opsiyonel)
                'tel' => 'required|digits_between:10,11|unique:tenants,tel1',
                'password' => [
                    'required',
                    'min:6',
                    'confirmed',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
                ],
            ], [
                'subscription_plan.required' => 'Lütfen bir abonelik planı seçiniz.',
                'subscription_plan.exists' => 'Seçilen plan geçerli değil.',
                'name.required' => 'Ad Soyad alanı zorunludur.',
                'name.max' => 'Ad Soyad alanı en fazla 255 karakter olmalıdır.',
                'username.required' => 'Kullanıcı adı alanı zorunludur.',
                'username.min' => 'Kullanıcı adı en az 3 karakter olmalıdır.',
                'username.max' => 'Kullanıcı adı en fazla 50 karakter olmalıdır.',
                'username.unique' => 'Bu kullanıcı adı zaten kullanılıyor.',
                'username.regex' => 'Kullanıcı adı sadece harf, rakam ve alt çizgi içerebilir.',
                'email.required' => 'E-posta alanı zorunludur.',
                'email.email' => 'Geçerli bir e-posta adresi giriniz.',
                'email.max' => 'E-posta alanı en fazla 255 karakter olmalıdır.',
                'email.unique' => 'Bu e-posta adresi zaten kayıtlı.',
                'vergiNo.required' => 'Vergi numarası alanı zorunludur.',
                'vergiNo.max' => 'Vergi numarası alanı en fazla 10 karakter olmalıdır.',
                'vergiNo.unique' => 'Bu vergi numarası zaten kayıtlı.',
                'firma_adi.required' => 'Firma Adı alanı zorunludur.',
                'firma_adi.max' => 'Firma Adı alanı en fazla 50 karakter olmalıdır.',
                'sektor.required' => 'Sektör seçimi zorunludur.', // YENİ
                'sektor.max' => 'Sektör alanı en fazla 100 karakter olmalıdır.', // YENİ
                'il_id.required' => 'İl seçimi zorunludur.',
                'il_id.exists' => 'Geçersiz il seçimi.',
                'ilce_id.required' => 'İlçe seçimi zorunludur.',
                'ilce_id.exists' => 'Geçersiz ilçe seçimi.',
                'adres.max' => 'Açık adres alanı en fazla 255 karakter olmalıdır.', // YENİ
                'tel.required' => 'Telefon alanı zorunludur.',
                'tel.digits_between' => 'Telefon numarası 10-11 haneli olmalıdır.',
                'tel.unique' => 'Bu telefon numarası zaten kayıtlı.',
                'password.required' => 'Şifre alanı zorunludur.',
                'password.min' => 'Şifre en az 6 karakter olmalıdır.',
                'password.confirmed' => 'Şifreler eşleşmiyor.',
                'password.regex' => 'Şifre en az bir büyük harf, bir küçük harf, bir rakam ve bir özel karakter içermelidir.',
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Validasyon başarılı'
        ]);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'errors' => $e->errors()
        ], 422);
    }
}

/**
 * Updated RegisterAction method to handle plan selection
 */
public function RegisterAction(Request $request) 
{
    $request->merge([
        'tel' => preg_replace('/\D/', '', $request->tel),
        'vergiNo' => preg_replace('/\D/', '', $request->vergiNo),
    ]);
    
    $validatedData = $request->validate([
        'subscription_plan' => 'required|exists:subscription_plans,id',
        'name' => 'required|string|max:255',
        'username' => 'required|string|min:3|max:50|unique:tb_user,username|regex:/^[a-zA-Z0-9_]+$/',
        'firma_adi' => 'required|string|max:50|regex:/^[a-zA-ZğüşıöçĞÜŞİÖÇ0-9\s]+$/',
        'sektor' => 'required|string|max:100', // YENİ
        'il_id' => 'required|exists:ils,id',
        'ilce_id' => 'required|exists:ilces,id',
        'adres' => 'nullable|string|max:255', // YENİ
        'vergiNo' => 'required|digits:10|unique:tenants,vergiNo',
        'tel' => 'required|digits_between:10,11|unique:tenants,tel1',
        'email' => 'required|email|max:255|unique:tenants,eposta',
        'password' => [
            'required',
            'min:6',
            'confirmed',
            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
        ],
    ], [
        'subscription_plan.required' => 'Lütfen bir abonelik planı seçiniz.',
        'subscription_plan.exists' => 'Seçilen plan geçerli değil.',
        'name.required' => 'Ad Soyad alanı zorunludur.',
        'name.max' => 'Ad Soyad alanı en fazla 255 karakter olmalıdır.',
        'username.required' => 'Kullanıcı adı alanı zorunludur.',
        'username.min' => 'Kullanıcı adı en az 3 karakter olmalıdır.',
        'username.max' => 'Kullanıcı adı en fazla 50 karakter olmalıdır.',
        'username.unique' => 'Bu kullanıcı adı zaten kullanılıyor.',
        'username.regex' => 'Kullanıcı adı sadece harf, rakam ve alt çizgi içerebilir.',
        'firma_adi.required' => 'Firma Adı alanı zorunludur.',
        'firma_adi.max' => 'Firma Adı alanı en fazla 50 karakter olmalıdır.',
        'firma_adi.regex' => 'Firma adı sadece harf, rakam ve boşluk içerebilir. Noktalama işaretleri kullanılamaz.',
        'sektor.required' => 'Sektör seçimi zorunludur.', // YENİ
        'il_id.required' => 'İl seçimi zorunludur.',
        'il_id.exists' => 'Geçersiz il seçimi.',
        'ilce_id.required' => 'İlçe seçimi zorunludur.',
        'ilce_id.exists' => 'Geçersiz ilçe seçimi.',
        'adres.max' => 'Açık adres alanı en fazla 255 karakter olmalıdır.', // YENİ
        'vergiNo.required' => 'Vergi numarası alanı zorunludur.',
        'vergiNo.digits' => 'Vergi numarası 10 haneli olmalıdır.',
        'vergiNo.unique' => 'Bu vergi numarası zaten kayıtlı.',
        'tel.required' => 'Telefon alanı zorunludur.',
        'tel.digits_between' => 'Telefon numarası 10-11 haneli olmalıdır.',
        'tel.unique' => 'Bu telefon numarası zaten kayıtlı.',
        'email.required' => 'E-posta alanı zorunludur.',
        'email.email' => 'Geçerli bir e-posta adresi giriniz.',
        'email.max' => 'E-posta alanı en fazla 255 karakter olmalıdır.',
        'email.unique' => 'Bu e-posta adresi zaten kayıtlı.',
        'password.required' => 'Şifre alanı zorunludur.',
        'password.min' => 'Şifre en az 6 karakter olmalıdır.',
        'password.confirmed' => 'Şifreler eşleşmiyor.',
        'password.regex' => 'Şifre en az bir büyük harf, bir küçük harf, bir rakam ve bir özel karakter içermelidir.',
    ]);

    // 6 haneli rastgele bir doğrulama kodu oluştur
    $verificationCode = rand(100000, 999999);

    // Store selected plan in session
    session(['selected_plan' => $validatedData['subscription_plan']]);

    // Kullanıcı verilerini ve kodu session'a kaydet
    $request->session()->put('registration_data', $validatedData);
    $request->session()->put('sms_verification_code', $verificationCode);
    $request->session()->put('sms_code_created_at', now());
    session(['phone_number' => $request->tel]);
    $request->session()->put('verification_attempts', 0);
    
    try {
        Log::info('SMS Gönderimi Başlıyor', [
            'phone' => $validatedData['tel'],
            'code' => $verificationCode
        ]);

        // SMS Servisi ile doğrulama kodu gönder
        $smsService = new TescomService();
        $smsResult = $smsService->sendVerificationCode($validatedData['tel'], $verificationCode);

        Log::info('SMS Servis Sonucu', [
            'result' => $smsResult
        ]);

        if (!$smsResult['success']) {
            Log::error('SMS Başarısız', [
                'result' => $smsResult
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'SMS gönderilemedi: ' . $smsResult['message']
            ], 500);
        }

        Log::info('SMS Başarılı, Response Hazırlanıyor');

        return response()->json([
            'success' => true,
            'message' => 'Doğrulama kodu telefonunuza gönderildi',
            'csrf_token' => csrf_token()
        ]);

    } catch (\Exception $e) {
        Log::error('SMS Gönderim Exception', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'phone' => $validatedData['tel']
        ]);

        return response()->json([
            'success' => false,
            'message' => 'SMS gönderilirken bir hata oluştu: ' . $e->getMessage()
        ], 500);
    }
}

    public function getSubscriptionPlans()
    {
        $plans = SubscriptionPlan::where('is_active', 1)
            ->orderBy('price', 'asc')
            ->get();

        $selectedPlanId = session('selected_plan');

        return response()->json([
            'success' => true,
            'plans' => $plans,
            'selected_plan_id' => $selectedPlanId
        ]);
    }

//    public function getSubscriptionPlans()
// {
//     $plans = SubscriptionPlan::where('is_active', 1)
//         ->orderBy('price', 'asc')
//         ->get();

//     // Session'dan seçili plan ID'sini al
//     $selectedPlanId = session('selected_plan');
    
//     // Eğer session'da plan index bilgisi varsa (fiyatlandırma sayfasından geliyorsa)
//     if (session()->has('selected_plan_index')) {
//         $selectedPlanIndex = session('selected_plan_index');
        
//         // Fiyatlandırma sayfasındaki plan bilgilerini al
//         $pricingData = HomepageContent::where('section', 'pricing_content')->first();
        
//         if ($pricingData && isset($pricingData->content['pricing_plans'][$selectedPlanIndex])) {
//             $selectedPlanFromPricing = $pricingData->content['pricing_plans'][$selectedPlanIndex];
            
//             // Plan adına göre database'deki planı bul
//             $matchedPlan = SubscriptionPlan::where('name', $selectedPlanFromPricing['name'])
//                 ->where('is_active', 1)
//                 ->first();
            
//             if ($matchedPlan) {
//                 $selectedPlanId = $matchedPlan->id;
                
//                 // Session'a bu ID'yi kaydet (tekrar kullanmak için)
//                 session(['selected_plan' => $matchedPlan->id]);
//             }
//         }
//     }

//     return response()->json([
//         'success' => true,
//         'plans' => $plans,
//         'selected_plan_id' => $selectedPlanId,
//         'selected_plan_info' => session('selected_plan_info') // Ek bilgiler için
//     ]);
// }


    public function verifySmsCode(Request $request) 
{
    $request->validate([
        'code' => 'required|numeric|digits:6'
    ], [
        'code.required' => 'Doğrulama kodu alanı zorunludur.',
        'code.numeric' => 'Doğrulama kodu sadece rakamlardan oluşmalıdır.',
        'code.digits' => 'Doğrulama kodu 6 haneli olmalıdır.'
    ]);

    $storedCode = $request->session()->get('sms_verification_code');
    $registrationData = $request->session()->get('registration_data');
    $codeCreatedAt = $request->session()->get('sms_code_created_at');
    $attempts = $request->session()->get('verification_attempts', 0);

    // Session kontrolü
    if (!$storedCode || !$registrationData) {
        return response()->json([
            'success' => false,
            'message' => 'Oturum süresi doldu. Lütfen kayıt işlemini baştan başlatın.'
        ], 400);
    }

    // Maksimum deneme kontrolü
    $maxAttempts = config('sms.verification.max_attempts', 3);
    if ($attempts >= $maxAttempts) {
        // Session'ı temizle
        $request->session()->forget([
            'registration_data', 
            'sms_verification_code', 
            'sms_code_created_at',
            'phone_number',
            'verification_attempts'
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Maksimum deneme hakkınız doldu. Lütfen yeniden kayıt olun.',
            'redirect' => route('giris')
        ], 400);
    }

    // Kod süresi kontrolü (3 dakika)
    $expiryMinutes = config('sms.verification.code_expiry_minutes', 3);
    if (now()->diffInMinutes($codeCreatedAt) >= $expiryMinutes) {
        $request->session()->forget([
            'registration_data', 
            'sms_verification_code', 
            'sms_code_created_at',
            'phone_number',
            'verification_attempts'
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Doğrulama kodu süresi doldu. Lütfen yeniden kayıt olun.',
            'redirect' => route('giris')
        ], 400);
    }

    // Kod kontrolü
    if ($request->code != $storedCode) {
        // Deneme sayısını artır
        $attempts++;
        $request->session()->put('verification_attempts', $attempts);
        
        $remainingAttempts = $maxAttempts - $attempts;
        
        return response()->json([
            'success' => false,
            'message' => "Doğrulama kodu hatalı. Kalan deneme hakkınız: {$remainingAttempts}"
        ], 400);
    }

    // Kod doğru - Kayıt işlemini tamamla
    try {
        $determinedPlanId = $registrationData['subscription_plan'] ?? session('selected_plan');
        $plan = SubscriptionPlan::find($determinedPlanId);

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Abonelik planı bulunamadı.'
            ], 400);
        }

        // Tenant ve User oluştur
        $tenant = $this->createTenantAndUser($registrationData, $plan);

        // Firma kodunu session'dan al (createTenantAndUser metodunda kaydedildi)
        $firmaKodu = session('firma_kodu');

        // KAYIT BAŞARILI SMS GÖNDER (Firma kodunu dahil et)
        try {
            $smsService = new TescomService();
            $smsMessage = "Serbis ailesine hoşgeldiniz.\n\nDemo talebiniz alınmıştır. Hesap bilgileriniz e-posta adresinize gönderilecektir.";
            $smsResult = $smsService->sendSms($registrationData['tel'], $smsMessage);
            
            Log::info('Kayıt Başarı SMS\'i Gönderildi', [
                'phone' => $registrationData['tel'],
                'firma_kodu' => $firmaKodu,
                'result' => $smsResult
            ]);
        } catch (\Exception $e) {
            Log::error('Kayıt Başarı SMS Hatası', [
                'error' => $e->getMessage(),
                'phone' => $registrationData['tel']
            ]);
        }

        // KAYIT BAŞARILI E-POSTA GÖNDER (Firma kodunu dahil et)
        try {
            Mail::to($registrationData['email'])->send(
                new RegistrationSuccessMail(
                    $registrationData['name'],
                    $registrationData['email'],
                    $registrationData['firma_adi'],
                    $firmaKodu // Firma kodunu mail'e ekle
                )
            );
            
            Log::info('Kayıt Başarı E-postası Gönderildi', [
                'email' => $registrationData['email'],
                'firma_kodu' => $firmaKodu
            ]);
        } catch (\Exception $e) {
            Log::error('Kayıt Başarı E-posta Hatası', [
                'error' => $e->getMessage(),
                'email' => $registrationData['email']
            ]);
        }

        // Session'dan gereksiz verileri temizle (firma_kodu hariç - success sayfasında göstereceğiz)
        $request->session()->forget([
            'registration_data', 
            'sms_verification_code', 
            'sms_code_created_at',
            'phone_number',
            'verification_attempts',
            'selected_plan'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kaydınız başarıyla tamamlandı!',
            'redirect' => route('register.success')
        ], 200);

    } catch (\Exception $e) {
        Log::error('Kayıt Tamamlama Hatası', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Kayıt sırasında bir hata oluştu: ' . $e->getMessage()
        ], 500);
    }
}


/**
 * SMS kodunu yeniden gönder
 */
public function resendSmsCode(Request $request)
{
    $registrationData = $request->session()->get('registration_data');
    $phoneNumber = $request->session()->get('phone_number');

    if (!$registrationData || !$phoneNumber) {
        return response()->json([
            'success' => false,
            'message' => 'Oturum süresi doldu. Lütfen kayıt işlemini baştan başlatın.'
        ], 400);
    }

    // Yeni doğrulama kodu oluştur 
    $verificationCode = rand(100000, 999999);

    // Session'ı güncelle
    $request->session()->put('sms_verification_code', $verificationCode);
    $request->session()->put('sms_code_created_at', now());
    $request->session()->put('verification_attempts', 0); // Deneme sayacını sıfırla

    try {
        // SMS gönder
        $smsService = new TescomService();
        $smsResult = $smsService->sendVerificationCode($phoneNumber, $verificationCode);

        if (!$smsResult['success']) {
            return response()->json([
                'success' => false,
                'message' => 'SMS gönderilemedi: ' . $smsResult['message']
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Doğrulama kodu tekrar gönderildi'
        ]);

    } catch (\Exception $e) {
        Log::error('SMS Yeniden Gönderim Hatası', [
            'error' => $e->getMessage(),
            'phone' => $phoneNumber
        ]);

        return response()->json([
            'success' => false,
            'message' => 'SMS gönderilirken bir hata oluştu'
        ], 500);
    }
}
    
    /**
     * Tenant ve User oluşturma mantığını içeren özel bir metod.
     * Bu, kodu tekrar etmemek için iyi bir yöntemdir.
     */
    private function createTenantAndUser(array $data, $planId = null) {
    // Benzersiz 6 haneli firma kodu oluştur
    do {
        $firmaKodu = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    } while (Tenant::where('firma_kodu', $firmaKodu)->exists());

    // Username kontrolü - artık username kullanıcı tarafından giriliyor
    $username = $data['username'];

    $firmaAdiSlug = Str::slug($data['firma_adi'], '-');
    
    // Tenant username (firma alan adı)
    $tenantUsername = $this->Seo($data['firma_adi']) . '.com';
    $originalTenantUsername = $tenantUsername;
    $counterTenant = 1;

    while (Tenant::where('username', $tenantUsername)->exists()) {
        $baseName = str_replace('.com', '', $originalTenantUsername);
        $tenantUsername = $baseName . '-' . $counterTenant . '.com';
        $counterTenant++;
    }

    // Get the selected plan from form data or session
    $determinedPlanId = $data['subscription_plan'] ?? $planId ?? session('selected_plan');
    $plan = SubscriptionPlan::find($determinedPlanId);

    if (!$plan) {
        \Illuminate\Support\Facades\Log::warning("SubscriptionPlan not found for ID: {$determinedPlanId}. Using default plan.");
        $plan = SubscriptionPlan::where('is_active', 1)->orderBy('price', 'asc')->first();
        
        if (!$plan) {
            throw new \Exception("No active subscription plan found. Cannot create tenant.");
        }
    }

    // Parse limits and features if they are JSON
    $limits = is_string($plan->limits) ? json_decode($plan->limits, true) : $plan->limits;
    $features = is_string($plan->features) ? json_decode($plan->features, true) : $plan->features;

    $tenant = new Tenant([
        'name' => $data['name'],
        'firma_adi' => $data['firma_adi'],
        'firma_kodu' => $firmaKodu,
        'vergiNo' => $data['vergiNo'],
        'firma_slug' => $firmaAdiSlug,
        'sektor' => $data['sektor'] ?? null, // YENİ - Sektör
        'il' => $data['il_id'],
        'ilce' => $data['ilce_id'],
        'adres' => $data['adres'] ?? null, // YENİ - Açık Adres
        'tel1' => $data['tel'],
        'eposta' => $data['email'],
        'username' => $tenantUsername,
        'kayitTarihi' => Carbon::now(),
        'bitisTarihi' => Carbon::now()->addDays(14),
        'status' => 0,
        'trial_starts_at' => Carbon::now(),
        'trial_ends_at' => Carbon::now()->addDays(14),
        'subscription_ends_at' => Carbon::now()->addDays(14),
        'trial_used' => 1,
        'personelSayisi' => $limits['users'] ?? '3',
        'bayiSayisi' => $limits['dealers'] ?? '0',
        'stokSayisi' => $limits['stocks'] ?? '10',
        'konsinyeSayisi' => $limits['konsinye'] ?? '1',
    ]);
    $tenant->save();

    $tenant_id = $tenant->id;

    $user = new User([
        'name' => $data['name'],
        'username' => $username,
        'tel' => $data['tel'],
        'eposta' => $data['email'],
        'tenant_id' => $tenant_id,
        'password' => Hash::make($data['password']),
        'status' => '1',
        'baslamaTarihi' => Carbon::now()->format('Y-m-d'),
    ]);
    $user->save();
    $user->syncRoles("Patron");

    TenantPrim::create([
        'firma_id' => $tenant_id,
        'operatorPrim' => 0.00,
        'operatorPrimTutari' => 0,
        'teknisyenPrim' => 0.00,
        'teknisyenPrimTutari' => 0,
        'atolyePrim' => 0.00,
        'atolyePrimTutari' => 0,
    ]);

    ServiceTime::create([
        'firma_id' => $tenant_id,
        'zaman' => '08:30',
        'created_at' => Carbon::now(),
    ]);

    // Varsayılan Fiş Tasarımı Oluştur
    $defaultReceiptDesign = "[FIRMAADI]
TEL : [TEL]-[TEL2]
ADRES : [ADRES]
--------------------------------
BEYAZ ESYA - KLIMA - KOMBI - TV
================================
- MUSTERI BILGISI - 
--------------------------------
[MUSTERIBILGILERI]
================================
- CIHAZ BILGISI - 
--------------------------------
[CIHAZBILGILERI]
================================
- YAPILAN ISLEMLER - 
--------------------------------
[YAPILANISLEMLER]
================================
- KASA HAREKETLERI - 
--------------------------------
[KASAHAREKETLERI]
================================
- TEKNISYEN ADI VE IMZASI - 
--------------------------------
[TEKNISYENADI]
TARIH : [TARIHSAAT]
================================
- MUSTERI ADI VE IMZASI - 
--------------------------------
[MUSTERIADI]
TARIH : [TARIHSAAT]
================================";

    ReceiptDesign::create([
        'firma_id' => $tenant_id,
        'fisTasarimi' => $defaultReceiptDesign,
        'boyut' => 58,
        'created_at' => Carbon::now(),
    ]);

    // Session'dan firma kodunu kaydet
    session(['firma_kodu' => $firmaKodu]);
    session()->forget('selected_plan');
    
    return $tenant;
}

public function getCities()
{
    $cities = DB::table('ils')->orderBy('name', 'asc')->get(['id', 'name']);
    
    return response()->json([
        'success' => true,
        'cities' => $cities
    ]);
}

/**
 * Seçilen ile ait ilçeleri getir
 */
public function getDistricts(Request $request)
{
    $ilId = $request->input('il_id');
    
    $districts = DB::table('ilces')->where('sehir_id', $ilId)
                        ->orderBy('ilceName', 'asc')
                        ->get(['id', 'ilceName']);
    
    return response()->json([
        'success' => true,
        'districts' => $districts
    ]);
}

    public function RegisterSuccess() {
        return view('frontend.auth.register_success');
    }

    public function Login(){
        
        return view('frontend.auth.login');
    }

    public function LoginAction(Request $request) {
    $request->validate([
        'username' => 'required|string',
        'firma_kodu' => 'required|digits:6',
        'password' => 'required|min:6',
        'g-recaptcha-response' => 'required|recaptcha',
    ], [
        'username.required' => 'Kullanıcı adı zorunludur.',
        'firma_kodu.required' => 'Firma kodu zorunludur.',
        'firma_kodu.digits' => 'Firma kodu 6 haneli olmalıdır.',
        'password.required' => 'Şifre zorunludur.',
        'password.min' => 'Şifre en az 6 karakter olmalıdır.',
        'g-recaptcha-response.required' => 'Güvenlik doğrulaması zorunludur.',
        'g-recaptcha-response.recaptcha' => 'Güvenlik doğrulaması başarısız. Lütfen tekrar deneyin.',
    ]);

    // Firma kodunu kullanarak tenant'ı bul
    $tenant = Tenant::where('firma_kodu', $request->firma_kodu)->first();

    if (!$tenant) {
        // Başarısız giriş denemesini logla
        ActivityLogger::log('login_failed', 'Geçersiz firma kodu girişi denemesi: ' . $request->firma_kodu, [
            'module' => 'auth',
            'tenant_id' => null,
            'user_name' => $request->username
        ]);
        
        $notification = array(
            'message' => 'Geçersiz firma kodu veya kullanıcı adı!',
            'alert-type' => 'danger'
        );
        return redirect()->back()->with($notification);
    }

    // Tenant status kontrolü - 0 ise pasif
    if ($tenant->status == 0) {
        // Askıya alınmış hesap giriş denemesi
        ActivityLogger::log('login_blocked', 'Askıya alınmış hesap giriş denemesi: ' . $request->username, [
            'module' => 'auth',
            'tenant_id' => $tenant->id,
            'user_name' => $request->username
        ]);

        $notification = array(
            'message' => 'Bu firma hesabı askıya alınmıştır. Lütfen sistem yöneticisi ile iletişime geçiniz.',
            'alert-type' => 'warning'
        );
        return redirect()->back()->with($notification);
    }

    // Kullanıcıyı doğrula
    $credentials = [
        'username' => $request->username,
        'password' => $request->password,
        'tenant_id' => $tenant->id, 
    ];
    
    if (Auth::attempt($credentials)) {
        $user = Auth::user();

        // Super Admin kontrolü
        if ($user->isSuperAdmin()) {
            ActivityLogger::logLogin($user);
            $notification = [
                'message' => 'Super Admin olarak giriş yaptınız.',
                'alert-type' => 'success'
            ];
            return redirect()->route('super.admin.dashboard')->with($notification);
        }
        
        // Kullanıcının tenant'ının tekrar status kontrolü (ekstra güvenlik)
        if ($user->tenant->status == 0) {
            Auth::logout();
            ActivityLogger::log('login_blocked_after_auth', 'Askıya alınmış hesap - oturum kapatıldı: ' . $request->username, [
                'module' => 'auth',
                'tenant_id' => $tenant->id,
                'user_name' => $user->name,
                'user_role' => $user->getRoleNames()->first()
            ]);
            
            $notification = array(
                'message' => 'Bu firma hesabı askıya alınmıştır. Lütfen sistem yöneticisi ile iletişime geçiniz.',
                'alert-type' => 'warning'
            );
            return redirect()->back()->with($notification);
        }
        
        // Başarılı girişi logla
        ActivityLogger::logLogin($user);
        
        $tenantId = $user->tenant->id;
        $notification = array(
            'message' => 'Başarıyla giriş yapıldı.',
            'alert-type' => 'success'
        );
        return redirect()->route('secure.home', ['tenant_id' => $tenantId])->with($notification);
    }
    else {
        // Yanlış şifre denemesi
        ActivityLogger::log('login_failed', 'Yanlış şifre veya kullanıcı adı: ' . $request->username, [
            'module' => 'auth',
            'tenant_id' => $tenant->id,
            'user_name' => $request->username
        ]);
        
        $notification = array(
            'message' => 'Geçersiz giriş bilgileri!',
            'alert-type' => 'danger'
        );
        return redirect()->back()->with($notification);
    }
}
    // Şifre sıfırlama formu
    public function showForgotPasswordForm()
    {
        return view('frontend.auth.forgot_password');
    }
    // Şifre sıfırlama e-postası gönderme
public function sendResetLinkEmail(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:tenants,eposta',
    ], [
        'email.required' => 'E-posta alanı zorunludur.',
        'email.email' => 'Geçerli bir e-posta adresi giriniz.',
        'email.exists' => 'Bu e-posta adresi sistemde kayıtlı değil.',
    ]);

    // Sadece Tenant (Patron) e-postası ile çalışır
    $tenant = Tenant::where('eposta', $request->email)->first();

    if (!$tenant) {
        return response()->json([
            'success' => false,
            'errors' => [
                'email' => ['Bu e-posta adresi sistemde kayıtlı değil.']
            ]
        ], 422);
    }

    // Eski tokenları temizle
    DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->delete();

    // Yeni token oluştur
    $token = Str::random(64);
    $expiresAt = Carbon::now()->addHours(1);

    DB::table('password_reset_tokens')->insert([
        'email' => $request->email,
        'token' => $token,
        'created_at' => Carbon::now(),
        'expires_at' => $expiresAt,
    ]);

    // Reset URL oluştur
    $resetUrl = route('password.reset', ['token' => $token, 'email' => $request->email]);

    // E-posta gönder
    try {
        Mail::to($request->email)->send(new PasswordResetMail(
            $resetUrl,
            $tenant->name,
            $expiresAt->format('d.m.Y H:i')
        ));

        // Activity log
        ActivityLogger::log('password_reset_requested', 'Şifre sıfırlama talebi (Patron): ' . $request->email, [
            'module' => 'auth',
            'tenant_id' => $tenant->id,
            'user_name' => $tenant->name
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Şifre sıfırlama bağlantısı e-posta adresinize gönderildi.'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'E-posta gönderilirken bir hata oluştu. Lütfen tekrar deneyin.'
        ], 500);
    }
}
// Şifre sıfırlama formu göster
public function showResetPasswordForm(Request $request, $token)
{
    $email = $request->query('email');
    
    // Token kontrolü
    $resetToken = DB::table('password_reset_tokens')
        ->where('email', $email)
        ->where('token', $token)
        ->where('expires_at', '>', Carbon::now())
        ->first();

    if (!$resetToken) {
        $notification = [
            'message' => 'Şifre sıfırlama bağlantısı geçersiz veya süresi dolmuş.',
            'alert-type' => 'error'
        ];
        return redirect()->route('giris')->with($notification);
    }

    return view('frontend.auth.reset_password', compact('token', 'email'));
}
// Şifreyi sıfırla
// Şifreyi sıfırla
public function resetPassword(Request $request)
{
    $request->validate([
        'token' => 'required',
        'email' => 'required|email|exists:tenants,eposta',
        'password' => 'required|min:6|confirmed',
    ], [
        'email.required' => 'E-posta alanı zorunludur.',
        'email.email' => 'Geçerli bir e-posta adresi giriniz.',
        'email.exists' => 'Bu e-posta adresi sistemde kayıtlı değil.',
        'password.required' => 'Şifre alanı zorunludur.',
        'password.min' => 'Şifre en az 6 karakter olmalıdır.',
        'password.confirmed' => 'Şifreler eşleşmiyor.',
    ]);

    // Token kontrolü
    $resetToken = DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->where('token', $request->token)
        ->where('expires_at', '>', Carbon::now())
        ->first();

    if (!$resetToken) {
        return response()->json([
            'success' => false,
            'message' => 'Şifre sıfırlama bağlantısı geçersiz veya süresi dolmuş.'
        ], 400);
    }

    // Tenant'ı bul
    $tenant = Tenant::where('eposta', $request->email)->first();
    
    if (!$tenant) {
        return response()->json([
            'success' => false,
            'message' => 'Firma bulunamadı.'
        ], 404);
    }

    // Patron kullanıcısını bul
    $user = User::where('tenant_id', $tenant->id)
        ->whereHas('roles', function($query) {
            $query->where('name', 'Patron');
        })
        ->first();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Bu firmaya ait yetkili kullanıcı bulunamadı.'
        ], 404);
    }

    // Patron şifresini güncelle
    $user->password = Hash::make($request->password);
    $user->save();

    // Tokeni sil
    DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->delete();

    // Activity log
    ActivityLogger::log('password_reset_completed', 'Şifre başarıyla sıfırlandı (Patron)', [
        'module' => 'auth',
        'tenant_id' => $tenant->id,
        'tenant_email' => $request->email,
        'user_id' => $user->user_id,
        'user_name' => $user->name,
        'user_email' => $user->eposta,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Şifreniz başarıyla güncellendi. Yeni şifrenizle giriş yapabilirsiniz.'
    ]);
}
    public function Dashboard($tenant_id) {
    $user = Auth::user();
    if ($user->tenant->id != $tenant_id) {
        $notification = array(
            'message' => 'Yetkisiz erişim yapıldı',
            'alert-type' => 'danger'
        );
        return redirect()->back()->with($notification);
    }

    //verileri view'e gönder
    $last_services = $this->getLastServices($tenant_id);
    $stock_alerts = $this->getStockAlerts($tenant_id); // tenant_id parametresini ekleyin

    return view('frontend.secure.index', compact('user', 'last_services', 'stock_alerts'));
}
// Dashboard istatistikleri
public function getStats($tenant_id)
{
    try {
        $today = Carbon::today();
        $thirtyDaysAgo = $today->copy()->subMonth(); // Son 1 Ay

        // AYLIK kasa hesaplama
        $monthly_income = DB::table('cash_transactions')
            ->where('odemeDurum', 1)
            ->where('odemeYonu', 1)
            ->where('firma_id', $tenant_id)
            ->whereBetween('created_at', [$thirtyDaysAgo, $today->copy()->endOfDay()])
            ->sum('fiyat');

        $monthly_expense = DB::table('cash_transactions')
            ->where('odemeDurum', 1)
            ->where('odemeYonu', 2)
            ->where('firma_id', $tenant_id)
            ->whereBetween('created_at', [$thirtyDaysAgo, $today->copy()->endOfDay()])
            ->sum('fiyat');

         // İptal durumlarını ve Yeni Servis durumunu tanımla
        $cancelled_statuses = [244]; // İptal  //[244, 246, 241, 243, 242]; // İptal, Tamir Edilemiyor, Fiyat Anlaşılamadı vb.
        $new_service_status = [235]; // Yeni Servis
        
        // İşlemde olanları bulmak için hariç tutulacak durumlar
        $excluded_statuses = array_merge($cancelled_statuses, $new_service_status);
            
        $stats = [
            // Aylık servis sayısı
            'total_services' => DB::table('services')
                ->where('durum', '!=', 0)
                ->where('firma_id', $tenant_id) 
                ->whereBetween('kayitTarihi', [$thirtyDaysAgo->format('Y-m-d'), $today->format('Y-m-d')])
                ->count(),
            
            // Aylık yeni müşteri sayısı
            'total_customers' => DB::table('customers')
             ->where('firma_id', $tenant_id)
             ->whereBetween('created_at', [$thirtyDaysAgo, $today->copy()->endOfDay()])
             ->count(),
            
            // Aktif Personel sayısı
            'total_personnel' => DB::table('tb_user')
                ->join('model_has_roles', 'tb_user.user_id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->whereIn('roles.name', ['Patron','Teknisyen', 'Teknisyen Yardımcısı', 'Operatör', 'Atölye Çırağı', 'Atölye Ustası', 'Depocu','Müdür'])
                ->where('tb_user.status', 1) // Sadece aktif olanlar
                ->where('tb_user.tenant_id', $tenant_id)
                ->count(),
            
            // AYLIK kasa toplamı
            'monthly_cash' => [
                'net' => $monthly_income - $monthly_expense
            ],
            
            // Günlük servis sayıları 
            'today_services' => DB::table('services')
                ->where('kayitTarihi', $today->format('Y-m-d'))
                ->where('durum', '!=', 0)
                ->where('firma_id', $tenant_id)
                ->count(),
                
            'today_cancelled_services' => DB::table('services')
                // 'updated_at' sütununun tarih kısmının bugüne eşit olup olmadığını kontrol et
                ->whereDate('updated_at', $today) 
                ->whereIn('servisDurum', $cancelled_statuses) // Sadece iptal durumundakileri al
                ->where('durum', '!=', 0)
                ->where('firma_id', $tenant_id)
                ->count(),
                            
            'today_in_process_services' => DB::table('services')
                ->where('kayitTarihi', $today->format('Y-m-d'))
                ->whereNotIn('servisDurum', $excluded_statuses) // Yeni veya İptal olmayanlar
                ->where('durum', '!=', 0)
                ->where('firma_id', $tenant_id)
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Veri alınırken hata oluştu: ' . $e->getMessage()
        ], 500);
    }
}
//Son servis talepleri
private function getLastServices($tenant_id)
{
    //Veritabanından son 4 servis
    $services_query = DB::table('services as s')
        ->join('customers as c', 's.musteri_id', '=', 'c.id')
        ->leftJoin('tb_user as u', 's.kid', '=', 'u.user_id')
        ->select(
            's.id as service_id',
            'c.adSoyad as customer_name',
            's.cihazAriza as service_description',
            'u.name as technician_name',
            's.updated_at as estimated_date',
            's.servisDurum as status_id' 
        )
        ->where('s.firma_id', $tenant_id)
        ->where('s.durum', 1)
        ->orderBy('s.created_at', 'desc')
        ->take(4)
        ->get();

    //Servis durum ID'lerini metne ve CSS sınıfına çeviren harita.
    $statusMap = [
        // Tamamlandı / Teslimat (Yeşil)
        
        252 => ['name' => 'Teslimata Hazır(Tamamlandı)', 'class' => 'status-completed'],
        253 => ['name' => 'Cihaz Teslim Edildi', 'class' => 'status-completed'],
        254 => ['name' => 'Şikayetçi', 'class' => 'status-completed'],
        255 => ['name' => 'Servisi Sonlandır', 'class' => 'status-completed'],
        256 => ['name' => 'Cihaz Satışı Yapıldı', 'class' => 'status-completed'],
        260 => ['name' => 'Cihaz Teslim Edildi (Parça Takıldı)', 'class' => 'status-completed'],
        272 => ['name' => 'Konsinye Cihaz Geri Alındı', 'class' => 'status-completed'],

        // Sorun / Problem / İptal (Kırmızı)
        241 => ['name' => 'Fiyat Anlaşılamadı', 'class' => 'status-high'],
        242 => ['name' => 'Ürün Garantili Çıktı', 'class' => 'status-high'],
        243 => ['name' => 'Müşteriye Ulaşılamadı', 'class' => 'status-high'],
        244 => ['name' => 'Müşteri İptal Etti', 'class' => 'status-high'],
        246 => ['name' => 'Cihaz Tamir Edilemiyor', 'class' => 'status-high'],
        
        // İşlemde / Atölyede (Turuncu)
        236 => ['name' => 'Teknisyen Yönledir', 'class' => 'status-medium'],
        237 => ['name' => 'Cihaz Atölyeye Alındı', 'class' => 'status-medium'],
        238 => ['name' => 'Parça Talep Et', 'class' => 'status-medium'],
        239 => ['name' => 'Yerinde Bakım Yapıldı', 'class' => 'status-medium'],
        245 => ['name' => 'Parçası Atölyeye Alındı', 'class' => 'status-medium'],
        250 => ['name' => 'Atölyede Tamir Ediliyor', 'class' => 'status-medium'],
        240 => ['name' => 'Atölyeye Aldır', 'class' => 'status-medium'],
        258 => ['name' => 'Tahsilata Gönder', 'class' => 'status-medium'],
        259 => ['name' => 'Parça Teslim Et', 'class' => 'status-medium'],
        261 => ['name' => 'Parça Hazır', 'class' => 'status-medium'],
        262 => ['name' => 'Nakliye Gönder', 'class' => 'status-medium'],
        271 => ['name' => 'Konsinye Cihaz Ata', 'class' => 'status-medium'],

        // Beklemede / Bilgi 
        235 => ['name' => 'Yeni Servisler', 'class' => 'status-pending'],
        247 => ['name' => 'Haber Verecek', 'class' => 'status-pending'],
        248 => ['name' => 'Yeniden Teknisyen Yölendir', 'class' => 'status-pending'],
        249 => ['name' => 'Müşteri Atölyeye Getirdi', 'class' => 'status-pending'],
        251 => ['name' => 'Teknisyen Yönlendir(Teslim Edilecek)', 'class' => 'status-pending'],
        257 => ['name' => 'Parça Takmak İçin Teknisyen Yönlendir', 'class' => 'status-pending'],
        263 => ['name' => 'Parça Siparişte', 'class' => 'status-pending'],
        264 => ['name' => 'Bayiye Gönnder', 'class' => 'status-pending'],
        266 => ['name' => 'Müşteri Para İade Edilecek', 'class' => 'status-pending'],
        267 => ['name' => 'Müşteri Para İade Edildi', 'class' => 'status-pending'],
        268 => ['name' => 'Fiyat Yükseltildi', 'class' => 'status-pending'],
        
        
        
    ];

    //Çektiğimiz verilere, yukarıdaki haritaya göre durum metnini ve CSS sınıfını ekleyelim.
    foreach ($services_query as $service) {
        // Eğer services tablosundan gelen status_id, $statusMap'te varsa onu kullan, yoksa default olanı kullan.
        $service->status_info = $statusMap[$service->status_id] ?? $statusMap['default'];
    }

    return $services_query;
}

//Kritik stoklar
//Kritik stoklar
private function getStockAlerts($tenant_id) 
{     
    $critical_level = 3; // Bu seviye ve altı KRİTİK     
    $low_level = 5;      // Bu seviye ve altı DÜŞÜK (ama kritik değil)     

    // Operatör rolüne sahipse boş array döndür
    if (Auth::user()->hasRole('Operatör')) {
        return ['critical' => [], 'low' => []];
    }

    try {
        // Önce tüm aktif ürünleri getir
        $allProducts = DB::table('stocks')
            ->where('firma_id', $tenant_id)
            ->where('durum', '1') // Aktif ürünler
            ->where('urunKategori', '!=', 3) // Konsinye kategorisini hariç tut
            ->select('id', 'urunAdi', 'urunKodu', 'urunKategori')
            ->get();

        \Log::info('All active products count: ' . count($allProducts));

        $alerts = [
            'critical' => [],
            'low' => []
        ];

        foreach ($allProducts as $product) {
            // Her ürün için güncel stok hesapla
            $currentStockData = DB::table('stock_actions')
                ->where('stokId', $product->id)
                ->where('firma_id', $tenant_id)
                ->selectRaw('
                    SUM(CASE 
                        WHEN islem = 1 THEN adet 
                        WHEN islem = 2 THEN -adet 
                        ELSE 0 
                    END) as current_stock,
                    COUNT(*) as total_actions,
                    MAX(created_at) as last_action_date
                ')
                ->first();

            $currentStock = $currentStockData ? (int)$currentStockData->current_stock : 0;

            \Log::info("Product ID {$product->id} ({$product->urunAdi}): Current stock = {$currentStock}");

            // Stok 0'dan büyük olmalı (negatif stokları gösterme)
            if ($currentStock > 0) {
                if ($currentStock <= $critical_level) {
                    $product->current_stock = $currentStock;
                    $product->threshold = $critical_level;
                    $product->alert_type = 'critical';
                    $product->total_actions = $currentStockData->total_actions ?? 0;
                    $product->last_action_date = $currentStockData->last_action_date ?? null;
                    
                    $alerts['critical'][] = $product;
                    \Log::info("Added to critical: {$product->urunAdi} (Stock: {$currentStock})");
                    
                } elseif ($currentStock <= $low_level) {
                    $product->current_stock = $currentStock;
                    $product->threshold = $low_level;
                    $product->alert_type = 'low';
                    $product->total_actions = $currentStockData->total_actions ?? 0;
                    $product->last_action_date = $currentStockData->last_action_date ?? null;
                    
                    $alerts['low'][] = $product;
                    \Log::info("Added to low: {$product->urunAdi} (Stock: {$currentStock})");
                }
            } else {
                \Log::info("Product {$product->urunAdi} has zero or negative stock: {$currentStock}");
            }
        }

        // Stok seviyesine göre sırala (en düşük önce)
        usort($alerts['critical'], function($a, $b) {
            return $a->current_stock <=> $b->current_stock;
        });
        
        usort($alerts['low'], function($a, $b) {
            return $a->current_stock <=> $b->current_stock;
        });

        // Her listeden en fazla 2'şer tane göster (dashboard'da daha fazla görünür olması için)
        $alerts['critical'] = array_slice($alerts['critical'], 0, 2);
        $alerts['low'] = array_slice($alerts['low'], 0, 2);

        \Log::info('Final Alerts Result:', [
            'critical_count' => count($alerts['critical']),
            'low_count' => count($alerts['low']),
            'total_products_checked' => count($allProducts)
        ]);

        return $alerts;

    } catch (\Exception $e) {
        \Log::error('Stock alerts error: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        return ['critical' => [], 'low' => []];
    }
}
    // Dashboard grafik verileri
    public function getChartData(Request $request,$tenant_id)
    {
         // Kullanıcı yetkisi kontrolü
        $user = Auth::user();
        if ($user->tenant->id != $tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'Yetkisiz erişim'
            ], 403);
        }
        try {
            $period = $request->get('period', 7);
            $type = $request->get('type', 'daily');

           if ($type === 'daily') {
                return $this->getDailyChartData($period, $tenant_id);
            } else {
                return $this->getHourlyChartData($period, $tenant_id);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Grafik verisi alınırken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getDailyChartData($period,$tenant_id)
    {
        $startDate = Carbon::now()->subDays($period - 1);
        $endDate = Carbon::now();

        $services = DB::table('services')
            ->select(
                'kayitTarihi as date',
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('kayitTarihi', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('durum', '!=', 0) // Silinmeyenler
            ->where('firma_id', $tenant_id)
            ->groupBy('kayitTarihi')
            ->orderBy('kayitTarihi')
            ->get();

        // Tüm günleri içeren array oluştur
        $labels = [];
        $data = [];
        
        for ($i = 0; $i < $period; $i++) {
            $currentDate = $startDate->copy()->addDays($i);
            $labels[] = $currentDate->format('d/m');
            
            // Bu günde servis var mı kontrol et
            $serviceCount = $services->where('date', $currentDate->format('Y-m-d'))->first();
            $data[] = $serviceCount ? $serviceCount->count : 0;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'data' => $data
            ]
        ]);
    }

    private function getHourlyChartData($period,$tenant_id)
    {
        $startDate = Carbon::now()->subDays($period - 1);
        $endDate = Carbon::now();

        // created_at sütunu var, saatlik dağılım için kullanabiliriz
        $services = DB::table('services')
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('durum', '!=', 0)
            ->where('firma_id', $tenant_id)
            ->whereNotNull('created_at')
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour')
            ->get();

        // 8-18 saatleri arası
        $labels = [];
        $data = [];
        
        for ($hour = 8; $hour <= 18; $hour++) {
            $labels[] = sprintf('%02d:00', $hour);
            
            $serviceCount = $services->where('hour', $hour)->first();
            $data[] = $serviceCount ? $serviceCount->count : 0;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'data' => $data
            ]
        ]);
    }

    public function logout(Request $request)
    {
        
        $user = Auth::user();
        if ($user) {
            ActivityLogger::logLogout($user);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $notification = array(
            'message' => 'Başarıyla çıkış yapıldı!',
            'alert-type' => 'success'
        );
        return redirect()->route('giris')->with($notification);
    }

    public function getStatesByCountry($countryId)
    {   $cities = DB::table('ilces')->where('sehir_id', $countryId)->orderBy('ilceName','asc')->get();
        return response()->json($cities);
    }

    
}
