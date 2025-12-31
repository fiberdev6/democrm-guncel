<?php
namespace App\Http\Controllers\Frontend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Survey;
use App\Models\ServiceStageAnswer;
use App\Models\StageQuestion;
use App\Models\ServiceResource;
use App\Models\Service;
use App\Models\DeviceBrand;
use App\Models\DeviceType;
use App\Models\ServicePlanning;
use App\Models\CashTransaction;
use App\Models\PaymentMethod;
use App\Models\PaymentType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class StatisticController extends Controller
{

    public function __construct()
{
    $this->middleware('permission:İstatistikleri Görebilir');
}
///////////////////////////////////////////////////////Service Statistics///////////////////////////////////////////////////////////////////
    public function ServiceStatistics(Request $request,$tenant_id)
    {
        // Personeller (sadece aktif personeller)
        $personeller = User::where('tenant_id', $tenant_id)
                          ->where('status', 1)
                          ->orderBy('name', 'ASC')
                          ->get();
        
        // Servis Kaynakları
        $servisKaynaklari = ServiceResource::where('firma_id', $tenant_id)
                                          ->orderBy('id', 'DESC')
                                          ->get();

        if ($request->has('servisSayListele')) {
            return $this->getFilteredStatistics($request, $tenant_id, $personeller, $servisKaynaklari);
        }

        return $this->getDefaultStatistics($tenant_id, $personeller, $servisKaynaklari);
    }

    private function getFilteredStatistics($request, $tenant_id, $personeller, $servisKaynaklari)
    {
        
        $personeller = User::where('tenant_id', $tenant_id)
            ->whereNull('ayrilmaTarihi')
            ->whereIn('user_id', function ($query) {
                $query->select('model_id')
                    ->from('model_has_roles')
                    ->whereIn('role_id', [1, 5, 263]);
            })
            ->get();

        // Tarihleri formatla
        $tarih1 = Carbon::createFromFormat('Y-m-d', $request->tarih1)->format('Y-m-d');
        $tarih2 = Carbon::createFromFormat('Y-m-d', $request->tarih2)->format('Y-m-d');

        // Temel sorgu
        $query = Service::where('firma_id', $tenant_id)
                       ->where('durum', 1)
                       ->whereBetween('kayitTarihi', [$tarih1 . ' 00:00:00', $tarih2 . ' 23:59:59']);

        // Personel filtresi
        if ($request->personeller != '0') {
            $query->where('kayitAlan', $request->personeller);
        }

        // Servis kaynağı filtresi
        if ($request->servisKaynak != '0') {
            $query->where('servisKaynak', $request->servisKaynak);
        }

        $servisler = $query->get();

        // İptal edilmemiş servisleri filtrele
        $validServisler = $this->filterCancelledServices($servisler);
        $validServisIds = $validServisler->pluck('id')->toArray();

        $statistics = [
            'toplam' => count($validServisIds),
            'markalar' => $this->getDeviceBrandStats($validServisIds),
            'turler' => $this->getDeviceTypeStats($validServisIds),
            'kaynaklar' => $this->getServiceResourceStats($validServisIds),
            'operatorler' => $this->getOperatorStats($validServisIds),
            'chartData' => $this->getChartData($tarih1, $tarih2, $tenant_id),
            'hourlyData' => $this->getHourlyData($tarih1, $tarih2, $tenant_id)
        ];

        return view('frontend.secure.statistics.service_statistics', compact(
            'tenant_id', 'personeller', 'servisKaynaklari', 'statistics', 'request'
        ));
    }

    private function getDefaultStatistics($tenant_id, $personeller, $servisKaynaklari)
    {
        $today = Carbon::today();
        
        $periods = [
            'bugun' => [
                'start' => $today->copy(),
                'end' => $today->copy(),
                'label' => 'Bugün'
            ],
            'son2gun' => [
                'start' => $today->copy()->subDay(),
                'end' => $today->copy(),
                'label' => 'Son İki Gün'
            ],
            'son3gun' => [
                'start' => $today->copy()->subDays(2),
                'end' => $today->copy(),
                'label' => 'Son Üç Gün'
            ],
            'son5gun' => [
                'start' => $today->copy()->subDays(4),
                'end' => $today->copy(),
                'label' => 'Son Beş Gün'
            ],
            'son7gun' => [
                'start' => $today->copy()->subDays(6),
                'end' => $today->copy(),
                'label' => 'Son Yedi Gün'
            ],
            'ayinBasi' => [
                'start' => $today->copy()->startOfMonth(),
                'end' => $today->copy(),
                'label' => 'Ayın Başından İtibaren'
            ]
        ];

        $periodStats = [];
        foreach ($periods as $key => $period) {
            $servisler = Service::where('firma_id', $tenant_id)
                              ->where('durum', 1)
                              ->whereBetween('kayitTarihi', [
                                  $period['start']->format('Y-m-d') . ' 00:00:00',
                                  $period['end']->format('Y-m-d') . ' 23:59:59'
                              ])->get();

            $personeller = User::where('tenant_id', $tenant_id)
            ->whereNull('ayrilmaTarihi')
            ->whereIn('user_id', function ($query) {
                $query->select('model_id')
                    ->from('model_has_roles')
                    ->whereIn('role_id', [1, 5, 263]);
            })
            ->get();


            $validServisler = $this->filterCancelledServices($servisler);
            $validServisIds = $validServisler->pluck('id')->toArray();

            $periodStats[$key] = [
                'label' => $period['label'],
                'toplam' => count($validServisIds),
                'markalar' => $this->getDeviceBrandStats($validServisIds),
                'turler' => $this->getDeviceTypeStats($validServisIds),
                'kaynaklar' => $this->getServiceResourceStats($validServisIds),
                'operatorler' => $this->getOperatorStats($validServisIds)
            ];
        }

        $chartData = $this->getChartData($today->copy()->subDays(30)->format('Y-m-d'), $today->format('Y-m-d'), $tenant_id);
        $hourlyData = $this->getHourlyData($today->format('Y-m-d'), $today->format('Y-m-d'), $tenant_id);

        return view('frontend.secure.statistics.service_statistics', compact(
            'tenant_id', 'personeller', 'servisKaynaklari', 'periodStats', 'chartData', 'hourlyData'
        ));
    }

    private function filterCancelledServices($servisler)
    {
        return $servisler->filter(function ($servis) {
            return !ServicePlanning::where('servisid', $servis->id)
                                 ->where('gidenIslem', 244)
                                 ->exists();
        });
    }

    private function getDeviceBrandStats($servisIds)
    {
        if (empty($servisIds)) return [];
        return Service::join('device_brands', 'services.cihazMarka', '=', 'device_brands.id')
              ->whereIn('services.id', $servisIds) 
              ->select('device_brands.marka', DB::raw('count(*) as sayi'))
              ->groupBy('device_brands.marka')
              ->orderBy('sayi', 'desc')
              ->get();

    }
    private function getDeviceTypeStats($servisIds)
    {
        if (empty($servisIds)) return [];

          return Service::whereIn('services.id', $servisIds) 
                 ->join('device_types', 'services.cihazTur', '=', 'device_types.id')
                 ->select('device_types.cihaz', DB::raw('count(*) as sayi'))
                 ->groupBy('device_types.cihaz')
                 ->orderBy('sayi', 'desc')
                 ->get();
    }
    private function getServiceResourceStats($servisIds)
    {
        if (empty($servisIds)) return [];

           return Service::whereIn('services.id', $servisIds) 
                 ->join('service_resources', 'services.servisKaynak', '=', 'service_resources.id')
                 ->select('service_resources.kaynak', DB::raw('count(*) as sayi'))
                 ->groupBy('service_resources.kaynak')
                 ->orderBy('sayi', 'desc')
                 ->get();
    }
    private function getOperatorStats($servisIds)
    {
        if (empty($servisIds)) return [];

        return Service::whereIn('services.id', $servisIds)
                 ->join('tb_user', 'services.kayitAlan', '=', 'tb_user.user_id')
                 ->select('tb_user.name', DB::raw('count(*) as sayi'))
                 ->groupBy('tb_user.name')
                 ->orderBy('sayi', 'desc')
                 ->get();
    }
    private function getChartData($startDate, $endDate, $tenant_id)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $data = [];

        while ($start <= $end) {
            $dayServices = Service::where('firma_id', $tenant_id)
                                 ->where('durum', 1)
                                 ->whereDate('kayitTarihi', $start->format('Y-m-d'))
                                 ->get();

            $validServices = $this->filterCancelledServices($dayServices);

            $data[] = [
                'date' => $start->format('Y-m-d'),
                'count' => $validServices->count()
            ];

            $start->addDay();
        }

        return $data;
    }
    public function getChartDataAjax(Request $request, $tenant_id)
{
    $days = $request->get('days', 7);
    
    // Gün sonunu al
    $endDate = Carbon::now()->endOfDay();
    $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
    
    $chartData = [];
    $currentDate = $startDate->copy();
    
    while ($currentDate <= $endDate) {
        $dayServices = Service::where('firma_id', $tenant_id)
                             ->where('durum', 1)
                             ->whereDate('kayitTarihi', $currentDate->format('Y-m-d'))
                             ->get();
        
        $validServices = $this->filterCancelledServices($dayServices);
        
        $chartData[] = [
            'tarih' => $currentDate->format('Y-m-d'),
            'count' => $validServices->count()
        ];
        
        $currentDate->addDay();
    }
    
    return response()->json($chartData);
}
    private function getHourlyData($startDate, $endDate, $tenant_id)
    {
        //Saatlik istatistikleri çekme
        $hourlyStats = Service::where('firma_id', $tenant_id)
                            ->where('durum', 1)
                            ->whereBetween('created_at', [
                                Carbon::parse($startDate)->startOfDay(), 
                                Carbon::parse($endDate)->endOfDay()     
                            ])
                            ->select(DB::raw('HOUR(created_at) as hour, COUNT(*) as count')) 
                            ->groupBy(DB::raw('HOUR(created_at)')) 
                            ->orderBy('hour')
                            ->get();
        // Kolay erişim için saatleri anahtar olarak kullanan bir koleksiyona dönüştürme
        $hourlyStatsIndexed = $hourlyStats->keyBy('hour');

        $data = [];
        for ($i = 0; $i < 24; $i++) {
            $count = 0;
            if ($hourlyStatsIndexed->has($i)) {
                $count = $hourlyStatsIndexed[$i]->count;
            }
            $data[] = [
                'hour' => str_pad($i, 2, '0', STR_PAD_LEFT) . ':00', // Saati HH:00 formatına getir (örn. "09:00")
                'count' => $count
            ];
        }
        return $data;
    }
    public function getHourlyDataAjax(Request $request, $tenant_id)
    {
        $type = $request->get('type'); 
        $date = $request->get('date'); // Eğer belirli bir tarih seçilmişse
        
        $query = Service::where('firma_id', $tenant_id)->where('durum', 1);
        
        // Eğer kullanıcı tarih seçtiyse, o günün verisini getir
        if ($date) {
            $targetDate = Carbon::parse($date);
            $query->whereDate('created_at', $targetDate->format('Y-m-d'));
        } else {
            // Seçilmemişse type'a göre aralık uygula
            switch ($type) {
                case '7days':
                    $query->whereBetween('created_at', [
                        Carbon::now()->subDays(6)->startOfDay(),
                        Carbon::now()->endOfDay()
                    ]);
                    break;
                case '15days':
                    $query->whereBetween('created_at', [
                        Carbon::now()->subDays(14)->startOfDay(),
                        Carbon::now()->endOfDay()
                    ]);
                    break;
                case '30days':
                    $query->whereBetween('created_at', [
                        Carbon::now()->subDays(29)->startOfDay(),
                        Carbon::now()->endOfDay()
                    ]);
                    break;
            }
        }
        
        $services = $query->get();
        $validServices = $this->filterCancelledServices($services);
        
        // Saatlik dağılımı hesapla
        $hourlyData = [];
        for ($i = 0; $i < 24; $i++) {
            $hourlyData[$i] = 0;
        }
        
        foreach ($validServices as $service) {
            $hour = (int) Carbon::parse($service->created_at)->format('H');
            $hourlyData[$hour]++;
        }
        
        // Formatla ve döndür
        $result = [];
        for ($i = 1; $i <= 24; $i++) {
            $hour = $i === 24 ? 0 : $i; // 24:00'ı 00:00 olarak göster
            $result[] = $hourlyData[$hour];
        }
        return response()->json($result);
    }
///////////////////////////////////////////////////////Technician Statistics///////////////////////////////////////////////////////////////////
public function TechnicianStatistics($tenant_id)
{   $firma = Tenant::findOrFail($tenant_id);
    $isBeyazEsya = $firma->sektor === 'beyaz-esya';

    // Cihaz türlerini al
    $cihazTurleri = DeviceType::where(function($query) use ($firma, $isBeyazEsya) {
        if ($isBeyazEsya) {
            // Beyaz eşya sektörü: default + kendi eklediği
            $query->whereNull('firma_id')
                ->orWhere('firma_id', $firma->id);
        } else {
            // Diğer sektörler: sadece kendi eklediği
            $query->where('firma_id', $firma->id);
        }
    })->orderBy('cihaz', 'asc')->get();

    return view('frontend.secure.statistics.technician_statistics', compact(
        'tenant_id', 
        'cihazTurleri'
    ));
}

public function getTechnicianStatisticsData(Request $request, $tenant_id)
{
    try {
        // Debug: Request parametrelerini kontrol et
        \Log::info('Request parametreleri:', [
            'tarihAraligi' => $request->tarihAraligi,
            'cihazTur' => $request->cihazTur,
            'tenant_id' => $tenant_id
        ]);

        // Teknisyenleri al
        $teknisyenler = User::where('tenant_id', $tenant_id)
                           ->where('status', 1)
                           ->whereHas('roles', function($query) {
                               $query->where('name', 'Teknisyen');
                           })
                           ->orderBy('name', 'ASC')
                           ->get();

        \Log::info('Bulunan teknisyen sayısı: ' . $teknisyenler->count());

        // Tarih aralığını parse et
        $tarihler = explode('---', $request->tarihAraligi);
        $tarih1 = Carbon::createFromFormat('d/m/Y', $tarihler[0])->format('Y-m-d');
        $tarih2 = Carbon::createFromFormat('d/m/Y', $tarihler[1])->format('Y-m-d');

        \Log::info('Tarih aralığı:', ['tarih1' => $tarih1, 'tarih2' => $tarih2]);

        // Tarih aralığındaki tüm tarihleri al
        $tarihListesi = $this->getDatesFromRange($tarih1, $tarih2);
        \Log::info('Tarih listesi:', $tarihListesi);

        // Seçilen tarihlerdeki servis planlarını al
        $servisPlanlar = $this->getServicePlansForDates($tarihListesi, $tenant_id, $request->cihazTur);
        \Log::info('Bulunan servis planları:', $servisPlanlar);

        // Her teknisyen için istatistikleri hesapla
        $teknisyenIstatistikleri = [];
        
        foreach ($teknisyenler as $teknisyen) {
            $teknisyenServisleri = $this->getTechnicianServices($teknisyen->user_id, $servisPlanlar);
            \Log::info('Teknisyen: ' . $teknisyen->name . ' - Servis sayısı: ' . count($teknisyenServisleri));
            
            if (!empty($teknisyenServisleri)) {
                $istatistik = $this->calculateTechnicianStats($teknisyen, $teknisyenServisleri, $tarih1, $tarih2);
                $teknisyenIstatistikleri[] = $istatistik;
            }
        }

        // Hiç servisi olmayan teknisyenleri de ekle
        $mevcutTeknisyenIds = collect($teknisyenIstatistikleri)->pluck('id')->toArray();
        $digerTeknisyenler = $teknisyenler->whereNotIn('user_id', $mevcutTeknisyenIds);
        
        foreach ($digerTeknisyenler as $teknisyen) {
            $teknisyenIstatistikleri[] = [
                'id' => $teknisyen->user_id,
                'name' => $teknisyen->name,
                'atanan_servis' => 0,
                'tamamlanan_servis' => 0,
                'sikayetci_servis' => 0,
                'iptal_servis' => 0,
                'haber_verecek' => 0,
                'fiyat_anlasma' => 0,
                'alinan_ucret' => 0,
                'verilen_teklif' => 0
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $teknisyenIstatistikleri
        ]);

    } catch (\Exception $e) {
        \Log::error('Teknisyen istatistikleri hatası: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Hata: ' . $e->getMessage()
        ]);
    }
}


private function getDatesFromRange($start, $end)
{
    $dates = [];
    $current = Carbon::parse($start);
    $endDate = Carbon::parse($end);

    while ($current <= $endDate) {
        $dates[] = $current->format('d/m/Y');
        $current->addDay();
    }

    return $dates;
}

private function getServicePlansForDates($tarihListesi, $tenant_id, $cihazTur = null)
{
    // Önce hangi tabloların mevcut olduğunu kontrol edin
    $query = DB::table('service_stage_answers as sc')
               ->leftJoin('stage_questions as ss', 'ss.id', '=', 'sc.soruid')
               ->leftJoin('services', 'services.id', '=', 'sc.servisid')
               ->where('ss.cevapTuru', 'LIKE', '%Tarih%')
               ->where('services.firma_id', $tenant_id);

    // Tarih formatını kontrol et - hem d/m/Y hem de Y-m-d formatlarını dene
    $tarihListesiYmd = array_map(function($tarih) {
        return Carbon::createFromFormat('d/m/Y', $tarih)->format('Y-m-d');
    }, $tarihListesi);

    $query->where(function($q) use ($tarihListesi, $tarihListesiYmd) {
        $q->whereIn('sc.cevap', $tarihListesi)
          ->orWhereIn('sc.cevap', $tarihListesiYmd);
    });

    if ($cihazTur) {
        $query->where('services.cihazTur', $cihazTur);
    }

    
    $seciliTarihler = $query->whereIn('sc.soruid',[48, 51, 27, 35, 41])
                           ->pluck('sc.planid')
                           ->toArray();

    \Log::info('Servis plan sorgusu sonucu:', [
        'tarih_listesi' => $tarihListesi,
        'tarih_listesi_ymd' => $tarihListesiYmd,
        'bulunan_planlar' => count($seciliTarihler),
        'plan_ids' => $seciliTarihler
    ]);

    return $seciliTarihler;
}


private function getTechnicianServices($teknisyenId, $servisPlanlar)
{
    if (empty($servisPlanlar)) {
        return [];
    }

    $servisler1 = DB::table('service_stage_answers as sc')
        ->leftJoin('stage_questions as ss', 'ss.id', '=', 'sc.soruid')
        ->where('sc.cevap', $teknisyenId)
        ->where('ss.cevapTuru', 'LIKE', '%Grup%')
        ->whereIn('sc.planid', $servisPlanlar)
        ->pluck('sc.servisid')
        ->unique()
        ->toArray();

    // Alternatif olarak service_plannings tablosundan da kontrol et
    $servisler2 = DB::table('service_plannings')
        ->where('pid', $teknisyenId)
        ->whereIn('id', $servisPlanlar)
        ->pluck('servisid')
        ->unique()
        ->toArray();

    // İki sonucu birleştir
    $servisler = array_unique(array_merge($servisler1, $servisler2));

    \Log::info('Teknisyen servisleri:', [
        'teknisyen_id' => $teknisyenId,
        'stage_answers_servisleri' => count($servisler1),
        'plannings_servisleri' => count($servisler2),
        'toplam_servisler' => count($servisler)
    ]);

    return $servisler;
}


private function calculateTechnicianStats($teknisyen, $servisler, $tarih1, $tarih2)
{
    // Tamamlanan servisler
    $tamamlanan = Service::whereIn('id', $servisler)
                         ->where('servisDurum', 255) // 255 = Tamamlandı
                         ->count();

    // Şikayetçi servisler
    $sikayetciSay = $this->calculateComplaintServices($teknisyen->user_id, $servisler);

    // İptal servisler
    $iptalSay = $this->calculateCancelledServices($teknisyen->user_id, $servisler);

    // Haber verecek servisler
    $haberSay = $this->calculateNotificationServices($teknisyen->user_id, $servisler);

    // Fiyat anlaşma servisler
    $fiyatSay = $this->calculatePriceDisagreementServices($teknisyen->user_id, $servisler);

    // Alınan ücret (kasa hareketlerinden)
    $alinanUcret = $this->calculateCollectedAmount($teknisyen->user_id, $servisler, $tarih1, $tarih2);

    // Verilen teklifler
    $verilenTeklif = $this->calculateOfferedAmount($teknisyen->user_id, $servisler, $tarih1, $tarih2);

    return [
        'id' => $teknisyen->user_id,
        'name' => $teknisyen->name,
        'atanan_servis' => count($servisler),
        'tamamlanan_servis' => $tamamlanan,
        'sikayetci_servis' => $sikayetciSay,
        'iptal_servis' => $iptalSay,
        'haber_verecek' => $haberSay,
        'fiyat_anlasma' => $fiyatSay,
        'alinan_ucret' => $alinanUcret,
        'verilen_teklif' => $verilenTeklif
    ];
}
//Şikayetçi Servisler
private function calculateComplaintServices($teknisyenId, $servisler)
{
    $sikayetciSay = 0;
    $sikayetciler = DB::table('service_plannings')
                      ->whereIn('servisid', $servisler)
                      ->where('gidenIslem', 254) // 254 = Şikayetçi
                      ->get();

    foreach ($sikayetciler as $servis) {
        $ilkServis = DB::table('service_plannings')
                       ->where('servisid', $servis->servisid)
                       ->where('pid', $teknisyenId) // pid = personel id
                       ->orderBy('created_at', 'ASC')
                       ->first();
                                  
        $sonSikayet = DB::table('service_plannings')
                        ->where('servisid', $servis->servisid)
                        ->where('gidenIslem', 254)
                        ->orderBy('created_at', 'DESC')
                        ->first();

        if ($ilkServis && $sonSikayet) {
            $ilkTarih = strtotime(explode(" ", $ilkServis->created_at)[0]);
            $sikayetTarih = strtotime(explode(" ", $sonSikayet->created_at)[0]);
            
            if ($sikayetTarih > $ilkTarih) {
                $sikayetciSay++;
            }
        }
    }

    return $sikayetciSay;
}
//İptal  Olan Servisler
private function calculateCancelledServices($teknisyenId, $servisler)
{
    $iptalSay = 0;
    
    $iptalSay = DB::table('service_plannings')
              ->whereIn('servisid', $servisler)
              ->where('gidenIslem', 244)
              ->distinct('servisid')
              ->count();

    return $iptalSay;
}
//Haber Verecek Servisler
private function calculateNotificationServices($teknisyenId, $servisler)
{
    $haberSay = 0;
    
    $haberciler = DB::table('service_plannings')
                    ->whereIn('servisid', $servisler)
                    ->where('gidenIslem', 247) // 247 = Haber verecek
                    ->get();

    foreach ($haberciler as $haber) {
        $ilkServis = DB::table('service_plannings')
                       ->where('servisid', $haber->servisid)
                       ->where('pid', $teknisyenId)
                       ->orderBy('created_at', 'ASC')
                       ->first();
                                  
        $sonHaber = DB::table('service_plannings')
                      ->where('servisid', $haber->servisid)
                      ->where('gidenIslem', 247)
                      ->orderBy('created_at', 'DESC')
                      ->first();

        if ($ilkServis && $sonHaber) {
            $ilkTarih = strtotime(explode(" ", $ilkServis->created_at)[0]);
            $haberTarih = strtotime(explode(" ", $sonHaber->created_at)[0]);
            
            if ($haberTarih > ($ilkTarih - 1)) {
                $haberSay++;
            }
        }
    }

    return $haberSay;
}
//Fiyatta Anlaşılamadı Servsiler
private function calculatePriceDisagreementServices($teknisyenId, $servisler)
{
    $fiyatSay = 0;
    
    $fiyatlar = DB::table('service_plannings')
                  ->whereIn('servisid', $servisler)
                  ->where('gidenIslem', 241) // 241 = Fiyatta anlaşılamadı
                  ->get();
               


    foreach ($fiyatlar as $fiyat) {
        $ilkServis = DB::table('service_plannings')
                       ->where('servisid', $fiyat->servisid)
                       ->where('pid', $teknisyenId)
                       ->orderBy('created_at', 'ASC')
                       ->first();
                                  
        $sonFiyat = DB::table('service_plannings')
                      ->where('servisid', $fiyat->servisid)
                      ->where('gidenIslem', 241)
                      ->orderBy('created_at', 'DESC')
                      ->first();

        if ($ilkServis && $sonFiyat) {
            $ilkTarih = strtotime(explode(" ", $ilkServis->created_at)[0]);
            $fiyatTarih = strtotime(explode(" ", $sonFiyat->created_at)[0]);
            
            if ($fiyatTarih > ($ilkTarih - 1)) {
                $fiyatSay++;
            }
        }
    }

    return $fiyatSay;
}
//Kasa Haraketleri ----- Alınan ÜCret
private function calculateCollectedAmount($teknisyenId, $servisler, $tarih1, $tarih2)
{
    if (empty($servisler)) {
        return 0;
    }

    $paraToplam = 0;

    try {
        if (Schema::hasTable('cash_transactions')) {
            $paraToplam = DB::table('cash_transactions')
                ->whereIn('servis', $servisler)
                ->where('odemeYonu', 1) // 1 = tahsilat
                ->where('personel', $teknisyenId)
                ->whereBetween('created_at', [$tarih1 . ' 00:00:00', $tarih2 . ' 23:59:59'])
                ->sum('fiyat');

            \Log::info('Tahsilat bulundu:', [
                'tablo' => 'cash_transactions',
                'teknisyen' => $teknisyenId,
                'toplam' => $paraToplam
            ]);
        }
    } catch (\Exception $e) {
        \Log::error('calculateCollectedAmount hata: ' . $e->getMessage());
    }

    return $paraToplam ?: 0;
}

//Verilen Teklif 
private function calculateOfferedAmount($teknisyenId, $servisler, $tarih1, $tarih2)
{
    if (empty($servisler)) {
        return 0;
    }

    $teklifToplam = 0;

    try {
        $teklifToplam = DB::table('service_stage_answers as sc')
            ->leftJoin('service_plannings as sp', 'sp.id', '=', 'sc.planid')
            ->where('sc.cevapText', '[Teklif]')
            ->whereIn('sc.servisid', $servisler)
            ->where('sp.pid', $teknisyenId)
            ->whereBetween('sp.created_at', [$tarih1 . ' 00:00:00', $tarih2 . ' 23:59:59'])
            ->where('sc.cevap', '!=', '')
            ->where('sc.cevap', '!=', '0')
            ->sum(DB::raw('CAST(sc.cevap AS DECIMAL(10,2))'));

        $teklifToplam = floatval($teklifToplam ?: 0);

        \Log::info('Teklif tutarları bulundu:', [
            'teknisyen' => $teknisyenId,
            'toplam' => $teklifToplam
        ]);
    } catch (\Exception $e) {
        \Log::error('calculateOfferedAmount hata: ' . $e->getMessage());
    }

    return $teklifToplam;
}

/////////////Teknisyen Detay//////////////////
public function getTechnicianDetailData(Request $request, $tenant_id)
{
    try {
        $teknisyenId = $request->teknisyen_id;
        $tarihler = explode('---', $request->tarihAraligi);
        $tarih1 = Carbon::createFromFormat('d/m/Y', $tarihler[0])->format('Y-m-d');
        $tarih2 = Carbon::createFromFormat('d/m/Y', $tarihler[1])->format('Y-m-d');
        $cihazTur = $request->cihazTur;

       
        // Teknisyen bilgisini al
        $teknisyen = User::where('tenant_id', $tenant_id)
                         ->where('user_id', $teknisyenId)
                         ->first();

        if (!$teknisyen) {
            return response()->json(['success' => false, 'message' => 'Teknisyen bulunamadı']);
        }

        // Tarih aralığındaki tüm tarihleri al
        $tarihListesi = $this->getDatesFromRange($tarih1, $tarih2);
        
        // Seçilen tarihlerdeki servis planlarını al
        $servisPlanlar = $this->getServicePlansForDates($tarihListesi, $tenant_id, $cihazTur);
        
        // Teknisyenin servislerini al
        $servisler = $this->getTechnicianServices($teknisyenId, $servisPlanlar);

        if (empty($servisler)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'teknisyen_adi' => $teknisyen->name,
                    'grafik_data' => $this->getEmptyGraphData($tarihListesi),
                    'detay_sayilari' => $this->getEmptyDetailCounts()
                ]
            ]);
        }

        // Grafik verileri için günlük dağılımı hesapla
        $grafikData = $this->calculateDailyGraphData($servisler, $tarihListesi, $teknisyenId);
        
        // Detaylı aşama sayılarını hesapla
        $detaySayilari = $this->calculateDetailedStats($teknisyenId, $servisler);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $teknisyen->user_id,
                'teknisyen_adi' => $teknisyen->name,
                'grafik_data' => $grafikData,
                'detay_sayilari' => $detaySayilari
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Teknisyen detay hatası: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Hata: ' . $e->getMessage()
        ]);
    }
}



private function calculateDailyGraphData($servisler, $tarihListesi, $teknisyenId)
{
    $tamamlananData = [];
    $iptalData = [];
    $gelirData = [];
    $labels = [];

    \Log::info('calculateDailyGraphData başladı:', [
        'servis_sayisi' => count($servisler),
        'tarih_sayisi' => count($tarihListesi),
        'teknisyen_id' => $teknisyenId
    ]);

    foreach ($tarihListesi as $tarih) {
        $tarihYmd = Carbon::createFromFormat('d/m/Y', $tarih)->format('Y-m-d');
        $labels[] = Carbon::createFromFormat('d/m/Y', $tarih)->format('d/m');

        \Log::info("Tarih işleniyor: $tarih ($tarihYmd)");

        // O tarihteki planları bul
        $gunlukPlanlar = DB::table('service_stage_answers as sc')
            ->leftJoin('stage_questions as ss', 'ss.id', '=', 'sc.soruid')
            ->where('ss.cevapTuru', 'LIKE', '%Tarih%')
            ->where(function($query) use ($tarih, $tarihYmd) {
                $query->where('sc.cevap', $tarih)
                      ->orWhere('sc.cevap', $tarihYmd);
            })
            ->whereIn('sc.soruid', [48, 51, 27, 35, 41])
            ->pluck('sc.planid')
            ->toArray();

        \Log::info("Günlük planlar bulundu:", [
            'tarih' => $tarih,
            'plan_sayisi' => count($gunlukPlanlar)
        ]);

        if (empty($gunlukPlanlar)) {
            $tamamlananData[] = 0;
            $iptalData[] = 0;
            $gelirData[] = 0;
            continue;
        }

        // O gün teknisyene atanan servisler
        $gunlukServisler = DB::table('service_stage_answers as sc')
            ->leftJoin('stage_questions as ss', 'ss.id', '=', 'sc.soruid')
            ->where('sc.cevap', $teknisyenId)
            ->where('ss.cevapTuru', 'LIKE', '%Grup%')
            ->whereIn('sc.planid', $gunlukPlanlar)
            ->pluck('sc.servisid')
            ->unique()
            ->toArray();

        // Alternatif olarak service_plannings tablosundan da kontrol et
        $gunlukServisler2 = DB::table('service_plannings')
            ->where('pid', $teknisyenId)
            ->whereIn('id', $gunlukPlanlar)
            ->pluck('servisid')
            ->unique()
            ->toArray();

        // İki sonucu birleştir
        $gunlukServisler = array_unique(array_merge($gunlukServisler, $gunlukServisler2));

        \Log::info("Günlük servisler:", [
            'tarih' => $tarih,
            'servis_sayisi' => count($gunlukServisler),
            'servis_ids' => $gunlukServisler
        ]);
        // Servis durumunu kontrol et, sadece o tarihte tamamlananları say
        $tamamlanan = 0;
        if (!empty($gunlukServisler)) {
            // Bu servislerin tamamlanma durumunu kontrol et
            $tamamlanan = Service::whereIn('id', $gunlukServisler)
                                ->where('servisDurum', 255) // 255 = Tamamlandı
                                ->count();
            
            // Alternatif: O tarihte tamamlanan planları kontrol et
            $tamamlananAlternatif = DB::table('service_plannings')
                ->whereIn('servisid', $gunlukServisler)
                ->where('gidenIslem', 255) // Tamamlandı durumu
                ->whereDate('created_at', $tarihYmd)
                ->distinct('servisid')
                ->count();

            // İkisinden büyük olanı al
            $tamamlanan = max($tamamlanan, $tamamlananAlternatif);
        }

        $iptal = 0;
        if (!empty($gunlukServisler)) {
            $iptal = DB::table('service_plannings')
                       ->whereIn('servisid', $gunlukServisler)
                       ->where('gidenIslem', 244) // 244 = İptal
                       ->whereDate('created_at', $tarihYmd)
                       ->distinct('servisid')
                       ->count();
        }

        $gelir = 0;
        if (!empty($gunlukServisler)) {
            try {
                $gelir = DB::table('cash_transactions')
                           ->whereIn('servis', $gunlukServisler)
                           ->where('odemeYonu', 1) // 1 = tahsilat
                           ->where('personel', $teknisyenId)
                           ->whereDate('created_at', $tarihYmd)
                           ->sum('fiyat');

                $gelir = floatval($gelir ?: 0);
            } catch (\Exception $e) {
                \Log::error("Gelir hesaplama hatası: " . $e->getMessage());
                $gelir = 0;
            }
        }

        $tamamlananData[] = intval($tamamlanan);
        $iptalData[] = intval($iptal);
        $gelirData[] = $gelir;

        \Log::info("Günlük sonuçlar:", [
            'tarih' => $tarih,
            'tamamlanan' => $tamamlanan,
            'iptal' => $iptal,
            'gelir' => $gelir
        ]);
    }

    $result = [
        'labels' => $labels,
        'tamamlanan' => $tamamlananData,
        'iptal' => $iptalData,
        'gelir' => $gelirData
    ];

    \Log::info('calculateDailyGraphData tamamlandı:', $result);

    return $result;
}


private function calculateDetailedStats($teknisyenId, $servisler)
{
    $servislerStr = implode(',', $servisler);
    
    return [
        'atanan_servis' => count($servisler),
        'tamamlanan_servis' => Service::whereIn('id', $servisler)->where('servisDurum', 255)->count(),
        'sikayetci_servis' => $this->calculateComplaintServices($teknisyenId, $servisler),
        'iptal_servis' => $this->calculateStageCount($teknisyenId, $servisler, 244),
        'haber_verecek' => $this->calculateStageCount($teknisyenId, $servisler, 247),
        'atolyede_tamir' => $this->calculateStageCount($teknisyenId, $servisler, 250),
        'atolyeye_aldir' => $this->calculateStageCount($teknisyenId, $servisler, 240),
        'cihaz_atolyede' => $this->calculateStageCount($teknisyenId, $servisler, 237),
        'tamir_edilemiyor' => $this->calculateStageCount($teknisyenId, $servisler, 246),
        'cihaz_teslim' => $this->calculateStageCount($teknisyenId, $servisler, 253),
        'cihaz_teslim_parca' => $this->calculateStageCount($teknisyenId, $servisler, 260),
        'fiyat_anlasilamadi' => $this->calculateStageCount($teknisyenId, $servisler, 241),
        'musteri_atolyeye_getirdi' => $this->calculateStageCount($teknisyenId, $servisler, 249),
        'musteriye_ulasilamadi' => $this->calculateStageCount($teknisyenId, $servisler, 243),
        'nakliye_gonder' => $this->calculateStageCount($teknisyenId, $servisler, 262),
        'nakliye_teslim' => $this->calculateStageCount($teknisyenId, $servisler, 251),
        'parca_hazir' => $this->calculateStageCount($teknisyenId, $servisler, 261),
        'parca_sipariste' => $this->calculateStageCount($teknisyenId, $servisler, 263),
        'parca_tek_yon' => $this->calculateStageCount($teknisyenId, $servisler, 257),
        'parca_talep_et' => $this->calculateStageCount($teknisyenId, $servisler, 238),
        'parca_teslim_et' => $this->calculateStageCount($teknisyenId, $servisler, 259),
        'parca_atolyeye_alindi' => $this->calculateStageCount($teknisyenId, $servisler, 245),
        'tahsilata_gonder' => $this->calculateStageCount($teknisyenId, $servisler, 258),
        'teslimata_hazir' => $this->calculateStageCount($teknisyenId, $servisler, 252),
        'garantili_cikti' => $this->calculateStageCount($teknisyenId, $servisler, 242),
        'yeniden_tek_yon' => $this->calculateStageCount($teknisyenId, $servisler, 248),
        'yerinde_bakim' => $this->calculateStageCount($teknisyenId, $servisler, 239),
        'cihaz_satisi_yapildi' => $this->calculateStageCount($teknisyenId, $servisler, 256),
        'bayiye_gonder' => $this->calculateStageCount($teknisyenId, $servisler, 264),
        'musteri_para_iade_edilecek' => $this->calculateStageCount($teknisyenId, $servisler, 266),
        'musteri_para_iade_edildi' => $this->calculateStageCount($teknisyenId, $servisler, 267),
        'fiyat_yukseltildi' => $this->calculateStageCount($teknisyenId, $servisler, 268),
        'konsinye_cihaz_ata' => $this->calculateStageCount($teknisyenId, $servisler, 271),
        'konsinye_cihaz_geri_alindi' => $this->calculateStageCount($teknisyenId, $servisler, 272),
        

    ];
}

private function calculateStageCount($teknisyenId, $servisler, $gidenIslem)
{
    $count = 0;
    
    $planlamalar = DB::table('service_plannings')
                     ->whereIn('servisid', $servisler)
                     ->where('gidenIslem', $gidenIslem)
                     ->get();

    foreach ($planlamalar as $planlama) {
        $ilkServis = DB::table('service_plannings')
                       ->where('servisid', $planlama->servisid)
                       ->where('pid', $teknisyenId)
                       ->orderBy('created_at', 'ASC')
                       ->first();
                                  
        $sonIslem = DB::table('service_plannings')
                      ->where('servisid', $planlama->servisid)
                      ->where('gidenIslem', $gidenIslem)
                      ->orderBy('created_at', 'DESC')
                      ->first();

        if ($ilkServis && $sonIslem) {
            $ilkTarih = strtotime(explode(" ", $ilkServis->created_at)[0]);
            $sonTarih = strtotime(explode(" ", $sonIslem->created_at)[0]);
            
            if ($sonTarih > ($ilkTarih - 1)) {
                $count++;
            }
        }
    }

    return $count;
}

private function getEmptyGraphData($tarihListesi)
{
    $labels = [];
    foreach ($tarihListesi as $tarih) {
        $labels[] = Carbon::createFromFormat('d/m/Y', $tarih)->format('d/m');
    }
    
    return [
        'labels' => $labels,
        'tamamlanan' => array_fill(0, count($labels), 0),
        'iptal' => array_fill(0, count($labels), 0),
        'gelir' => array_fill(0, count($labels), 0)
    ];
}

private function getEmptyDetailCounts()
{
    return [
        'atanan_servis' => 0,
        'tamamlanan_servis' => 0,
        'sikayetci_servis' => 0,
        'iptal_servis' => 0,
        'haber_verecek' => 0,
        'atolyede_tamir' => 0,
        'atolyeye_aldir' => 0,
        'cihaz_atolyede' => 0,
        'tamir_edilemiyor' => 0,
        'cihaz_teslim' => 0,
        'cihaz_teslim_parca' => 0,
        'fiyat_anlasilamadi' => 0,
        'musteri_atolyeye_getirdi' => 0,
        'musteriye_ulasilamadi' => 0,
        'nakliye_gonder' => 0,
        'nakliye_teslim' => 0,
        'parca_hazir' => 0,
        'parca_sipariste' => 0,
        'parca_tek_yon' => 0,
        'parca_talep_et' => 0,
        'parca_teslim_et' => 0,
        'parca_atolyeye_alindi' => 0,
        'tahsilata_gonder' => 0,
        'teslimata_hazir' => 0,
        'garantili_cikti' => 0,
        'yeniden_tek_yon' => 0,
        'yerinde_bakim' => 0,
        'cihaz_satisi_yapildi' => 0,
        'bayiye_gonder' => 0,
        'musteri_para_iade_edilecek' => 0,
        'musteri_para_iade_edildi' => 0,
        'fiyat_yukseltildi' => 0,
        'konsinye_cihaz_ata' => 0,
        'konsinye_cihaz_geri_alindi' => 0,
        
        
    ];
}

///////////////////////////////////////////////////////Operatör Statistics//////////////////////////////////////////////////////////////////
public function OperatorStatistics(Request $request, $tenant_id) 
{     
    if ($request->ajax()) {         
        // AJAX request için DataTable verisi döndür
        $query = DB::table('services as s')
            ->join('tb_user as u', 's.kayitAlan', '=', 'u.user_id')
            ->select('u.user_id as id', 'u.name', DB::raw('COUNT(s.id) as toplam'))
            ->where('s.firma_id', $tenant_id)
            ->where('s.durum', 1);
            
        // Tarih filtreleme 
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = Carbon::createFromFormat('Y-m-d', $request->from_date)->startOfDay();
            $to_date = Carbon::createFromFormat('Y-m-d', $request->to_date)->endOfDay();
                      
            $query->whereBetween('s.kayitTarihi', [$from_date, $to_date]);
        }

        // Arama filtreleme 
        if ($request->has('search') && !empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $query->where('u.name', 'LIKE', '%' . $searchValue . '%');
        }

        $query->groupBy('u.user_id', 'u.name');

        // Sıralama 
        if ($request->has('order')) {
            $orderColumn = $request->order[0]['column'];
            $orderDirection = $request->order[0]['dir'];
                      
            $columns = ['name', 'toplam'];
                      
            if (isset($columns[$orderColumn])) {
                if ($columns[$orderColumn] == 'name') {
                    $query->orderBy('u.name', $orderDirection);
                } else {
                    $query->orderBy('toplam', $orderDirection);
                }
            }
        } else {
            $query->orderByDesc('toplam');
        }

        // Sayfalama için toplam kayıt sayısı (filtreleme öncesi)
        $totalQuery = DB::table('services as s')
            ->join('tb_user as u', 's.kayitAlan', '=', 'u.user_id')
            ->where('s.firma_id', $tenant_id)
            ->where('s.durum', 1);
            
        // Tarih filtresi varsa toplam kayıt sayısına da uygula
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = Carbon::createFromFormat('Y-m-d', $request->from_date)->startOfDay();
            $to_date = Carbon::createFromFormat('Y-m-d', $request->to_date)->endOfDay();
            $totalQuery->whereBetween('s.kayitTarihi', [$from_date, $to_date]);
        }
        
        $totalRecords = $totalQuery
            ->select(DB::raw('COUNT(DISTINCT u.user_id) as total'))
            ->first()
            ->total;

        // Filtrelenmiş kayıt sayısını al (sayfalama öncesi)
        $filteredQuery = clone $query;
        $filteredRecords = $filteredQuery->get()->count();

        // Sayfalama
        if ($request->has('start') && $request->has('length')) {
            $start = (int) $request->start;
            $length = (int) $request->length;
            
            // Negatif olmayan değerler için kontrol
            $start = max(0, $start);
            $length = $length > 0 ? $length : 10; // Varsayılan 10
            
            $query->limit($length)->offset($start);
        } else {
            // Eğer sayfalama parametreleri yoksa varsayılan limit koy
            $query->limit(25)->offset(0);
        }

        $data = $query->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    // Normal sayfa yüklemesi 
    return view('frontend.secure.statistics.operator_statistics', compact('tenant_id')); 
}
///////////////////////////////////////////////////////State Statistics//////////////////////////////////////////////////////////////////
public function StateStatistics(Request $request, $tenant_id) 
{     
    if ($request->ajax()) {         
        $query = DB::table('services as s')
            ->join('service_stages as ss', 's.servisDurum', '=', 'ss.id')
            ->select('ss.id as durum_id','ss.asama as durum', DB::raw('COUNT(s.id) as toplam'))
            ->where('s.firma_id', $tenant_id)
            ->where('s.durum', 1);

        // Tarih filtresi 
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = Carbon::createFromFormat('Y-m-d', $request->from_date)->startOfDay();
            $to_date = Carbon::createFromFormat('Y-m-d', $request->to_date)->endOfDay();

            $query->whereBetween('s.kayitTarihi', [$from_date, $to_date]);
        }

        // Arama filtresi (durum adı)
        if ($request->has('search') && !empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $query->where('ss.asama', 'LIKE', "%$searchValue%");
        }
        
        $query->groupBy('ss.id', 'ss.asama');
        
        // Sıralama
        if ($request->has('order')) {
            $orderColumn = $request->order[0]['column'];
            $orderDirection = $request->order[0]['dir'];
            $columns = ['durum', 'toplam'];

            if (isset($columns[$orderColumn])) {
                if ($columns[$orderColumn] == 'durum') {
                    $query->orderBy('ss.asama', $orderDirection);
                } else {
                    $query->orderBy('toplam', $orderDirection);
                }
            }
        } else {
            $query->orderByDesc('toplam');
        }

        // Toplam kayıt sayısı (filtreleme öncesi)
        $totalQuery = DB::table('services as s')
            ->join('service_stages as ss', 's.servisDurum', '=', 'ss.id')
            ->where('s.firma_id', $tenant_id)
            ->where('s.durum', 1);
            
        // Tarih filtresi varsa toplam kayıt sayısına da uygula
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = Carbon::createFromFormat('Y-m-d', $request->from_date)->startOfDay();
            $to_date = Carbon::createFromFormat('Y-m-d', $request->to_date)->endOfDay();
            $totalQuery->whereBetween('s.kayitTarihi', [$from_date, $to_date]);
        }
        
        $totalRecords = $totalQuery
            ->select(DB::raw('COUNT(DISTINCT ss.id) as total'))
            ->first()
            ->total;

        // Filtrelenmiş kayıt sayısını al (sayfalama öncesi)
        $filteredQuery = clone $query;
        $filteredRecords = $filteredQuery->get()->count();

        // Sayfalama - ÖNEMLİ: Her zaman limit() ve offset() birlikte kullan
        if ($request->has('start') && $request->has('length')) {
            $start = (int) $request->start;
            $length = (int) $request->length;
            
            // Negatif olmayan değerler için kontrol
            $start = max(0, $start);
            $length = $length > 0 ? $length : 10; // Varsayılan 10
            
            $query->limit($length)->offset($start);
        } else {
            // Eğer sayfalama parametreleri yoksa varsayılan limit koy
            $query->limit(25)->offset(0);
        }

        $data = $query->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    return view('frontend.secure.statistics.state_statistics', compact('tenant_id')); 
}
///////////////////////////////////////////////////////Stage Statistics//////////////////////////////////////////////////////////////////
public function StageStatistics(Request $request, $tenant_id) 
{
    if ($request->ajax()) {
        // Tarih aralığını al
        $from_date = $request->from_date ? Carbon::createFromFormat('Y-m-d', $request->from_date)->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        $to_date = $request->to_date ? Carbon::createFromFormat('Y-m-d', $request->to_date)->endOfDay() : Carbon::now()->endOfDay();
        
        // service_plannings tablosundan verileri al - sadece aktif servislere ait olanlar
        $plannings = DB::table('service_plannings as sp')
            ->join('services as s', 'sp.servisid', '=', 's.id')
            ->where('s.firma_id', $tenant_id)
            ->where('s.durum', 1) // Sadece aktif servisler
            ->whereBetween('sp.created_at', [$from_date, $to_date])
            ->select('sp.servisid', 'sp.gidenIslem')
            ->orderBy('sp.servisid', 'ASC')
            ->get();
        
        // Servis ID'ye göre grupla ve işlemleri birleştir
        $groupConcat = [];
        foreach ($plannings as $row) {
            if (!isset($groupConcat[$row->servisid])) {
                $groupConcat[$row->servisid] = $row->gidenIslem . ", ";
            } else {
                $groupConcat[$row->servisid] .= $row->gidenIslem . ", ";
            }
        }
        
        // Her servis için benzersiz aşamaları çıkar
        $arrayUnique = [];
        foreach ($groupConcat as $key => $value) {
            $newVal = trim($value);
            $newVal = substr($newVal, 0, -1);
            $newVal = explode(", ", $newVal);
            $newVal = array_unique($newVal);
            $newVal = implode(", ", $newVal);
            $arrayUnique[$key] = $newVal;
        }
        
        // Tüm aşamaları topla
        $asamalar = [];
        foreach ($arrayUnique as $key => $value) {
            $newVal = explode(", ", $value);
            $newVal = array_unique($newVal);
            foreach ($newVal as $val) {
                if (!empty($val)) {
                    $asamalar[] = $val;
                }
            }
        }
        
        sort($asamalar);
        
        // Her aşamadan kaç tane olduğunu say
        $sayilar = [];
        foreach ($asamalar as $asama) {
            if (!isset($sayilar[$asama])) {
                $sayilar[$asama] = 0;
            }
            $sayilar[$asama]++;
        }
        
        // Aşama isimlerini veritabanından çek
        $data = [];
        foreach ($sayilar as $asama_id => $count) {
            $asamaInfo = DB::table('service_stages')->where('id', $asama_id)->first();
            if ($asamaInfo) {
                $data[] = [
                    'asama_id' => $asama_id,
                    'asama' => $asamaInfo->asama,
                    'toplam' => $count
                ];
            }
        }
        
        // Sıralama
        if ($request->has('order')) {
            $orderColumn = $request->order[0]['column'];
            $orderDirection = $request->order[0]['dir'];
            
            if ($orderColumn == 1) { // toplam sütunu
                usort($data, function($a, $b) use ($orderDirection) {
                    return $orderDirection === 'asc' ? $a['toplam'] <=> $b['toplam'] : $b['toplam'] <=> $a['toplam'];
                });
            }
        } else {
            // Varsayılan olarak toplama göre azalan sırala
            usort($data, function($a, $b) {
                return $b['toplam'] <=> $a['toplam'];
            });
        }
        
        // Arama filtresi
        if ($request->has('search') && !empty($request->search['value'])) {
            $searchValue = strtolower($request->search['value']);
            $data = array_filter($data, function($item) use ($searchValue) {
                return strpos(strtolower($item['asama']), $searchValue) !== false;
            });
        }
        
        // Toplam kayıt sayısını hesapla (aktif servislerin aşama sayısı)
        $totalPlannings = DB::table('service_plannings as sp')
            ->join('services as s', 'sp.servisid', '=', 's.id')
            ->where('s.firma_id', $tenant_id)
            ->where('s.durum', 1)
            ->distinct('sp.gidenIslem')
            ->count('sp.gidenIslem');
            
        $totalRecords = $totalPlannings;
        $filteredRecords = count($data);
        
        // Sayfalama
        if ($request->has('start') && $request->has('length')) {
            $data = array_slice($data, $request->start, $request->length);
        }
        
        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => array_values($data)
        ]);
    }
    
    return view('frontend.secure.statistics.stage_statistics', compact('tenant_id'));
}
///////////////////////////////////////////////////////Stocks Statistics///////////////////////////////////////////////////////////////////
public function StockStatistics($tenant_id)
{
    return view('frontend.secure.statistics.stock_statistics', compact('tenant_id'));
}

public function getPersonelDepoData(Request $request, $tenant_id)
{
    if ($request->ajax()) {
        // Tarih parametrelerini al
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');

        $usersWithRoles = User::role(['Teknisyen', 'Teknisyen Yardımcısı'])
            ->where('tenant_id', $tenant_id)
            ->withSum(['personelStocks as toplam_adet' => function ($query) use ($from_date, $to_date) {
                $query->where('adet', '!=', 0);
                
                // Tarih filtresi ekle - 'tarih' sütununu kullan
                if ($from_date && $to_date) {
                    $query->whereBetween('tarih', [$from_date . ' 00:00:00', $to_date . ' 23:59:59']);
                }
            }], 'adet')
            ->having('toplam_adet', '>', 0)
            ->get();

        return Datatables::of($usersWithRoles)
            ->addIndexColumn()
            ->addColumn('personel_name', function($row){
                return '<strong>' . $row->name . '</strong>';
            })
            ->addColumn('toplam_adet', function($row){
                return '<strong>' . $row->toplam_adet . '</strong>';
            })
            ->addColumn('action', function($row) use ($tenant_id){
                return '';
            })
            ->rawColumns(['personel_name', 'toplam_adet', 'action'])
            ->make(true);
    }
}

///////////////////////////////////////////////////////İlçe Statistics///////////////////////////////////////////////////////////////////
public function IlceStatistics(Request $request, $tenant_id) 
{     
    if ($request->ajax()) {         
        $query = DB::table('services as s')
            ->join('customers as c', 's.musteri_id', '=', 'c.id')
            ->join('ilces as i', 'c.ilce', '=', 'i.id') // ilces tablosuna join
            ->join('ils as il', 'c.il', '=', 'il.id')
            ->select('il.name as ilName', 'i.ilceName', DB::raw('COUNT(s.id) as toplam'))
            ->where('s.firma_id', $tenant_id)
            ->where('s.durum', 1);

        // Tarih aralığı filtresi
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = Carbon::createFromFormat('Y-m-d', $request->from_date)->startOfDay();
            $to_date = Carbon::createFromFormat('Y-m-d', $request->to_date)->endOfDay();
            $query->whereBetween('s.kayitTarihi', [$from_date, $to_date]);
        }

        // İl filtresi
        if ($request->has('il') && !empty($request->il)) {
            $query->where('il.id', $request->il);
        }

        // Arama filtresi
        if ($request->has('search') && !empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $query->where(function($q) use ($searchValue) { 
                $q->where('il.name', 'LIKE', '%' . $searchValue . '%')
                  ->orWhere('i.ilceName', 'LIKE', '%' . $searchValue . '%');
            });
        }

        // Gruplama
        $query->groupBy('il.name', 'i.ilceName');

        // Sıralama
        if ($request->has('order')) {
            $orderColumn = $request->order[0]['column'];
            $orderDirection = $request->order[0]['dir'];

            $columns = ['ilceName', 'toplam'];

            if (isset($columns[$orderColumn])) {
                if ($columns[$orderColumn] == 'ilceName') {
                    $query->orderBy('i.ilceName', $orderDirection);
                } else {
                    $query->orderBy('toplam', $orderDirection);
                }
            }
        } else {
            $query->orderByDesc('toplam');
        }

        // Toplam kayıt sayısı (filtreleme öncesi)
        $totalQuery = DB::table('services as s')
            ->join('customers as c', 's.musteri_id', '=', 'c.id')
            ->join('ilces as i', 'c.ilce', '=', 'i.id')
            ->join('ils as il', 'c.il', '=', 'il.id')
            ->where('s.firma_id', $tenant_id)
            ->where('s.durum', 1);

        // Tarih filtresi varsa toplam kayıt sayısına da uygula
        if ($request->has('from_date') && $request->has('to_date')) {
            $from_date = Carbon::createFromFormat('Y-m-d', $request->from_date)->startOfDay();
            $to_date = Carbon::createFromFormat('Y-m-d', $request->to_date)->endOfDay();
            $totalQuery->whereBetween('s.kayitTarihi', [$from_date, $to_date]);
        }

        // İl filtresi varsa toplam kayıt sayısına da uygula
        if ($request->has('il') && !empty($request->il)) {
            $totalQuery->where('il.id', $request->il);
        }

        $totalRecords = $totalQuery
            ->select(DB::raw('COUNT(DISTINCT CONCAT(il.id, "-", i.id)) as total'))
            ->first()
            ->total;

        // Filtrelenmiş kayıt sayısını al (sayfalama öncesi)
        $filteredQuery = clone $query;
        $filteredRecords = $filteredQuery->get()->count();

        // Sayfalama - ÖNEMLİ: Her zaman limit() ve offset() birlikte kullan
        if ($request->has('start') && $request->has('length')) {
            $start = (int) $request->start;
            $length = (int) $request->length;
            
            // Negatif olmayan değerler için kontrol
            $start = max(0, $start);
            $length = $length > 0 ? $length : 10; // Varsayılan 10
            
            $query->limit($length)->offset($start);
        } else {
            // Eğer sayfalama parametreleri yoksa varsayılan limit koy
            $query->limit(25)->offset(0);
        }

        $data = $query->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    // Normal sayfa yüklemesi için il listesini al
    $iller = DB::table('ils')->select('id', 'name')->orderBy('name')->get();
    return view('frontend.secure.statistics.ilce_statistics', compact('tenant_id', 'iller')); 
}

///////////////////////////////////////////////////////Survey Statistics///////////////////////////////////////////////////////////////////
public function SurveyStatistics($tenant_id)
{
        return view('frontend.secure.statistics.survey_statistics', compact('tenant_id'));
}
public function getSurveyStatisticsData(Request $request, $tenant_id)
{
    $fromDate = $request->from_date ?: Carbon::yesterday()->format('Y-m-d');
    $toDate = $request->to_date ?: Carbon::yesterday()->format('Y-m-d');
    $deviceTypeId = $request->device_type_id;

    // Tarih aralığı için başlangıç ve bitiş zamanları
    $startDateTime = $fromDate . ' 00:00:00';
    $endDateTime = $toDate . ' 23:59:59';

    $validUserIds = User::role(['Operatör', 'Patron', 'Müdür'])->pluck('user_id')->toArray();

    // 1. Önce tamamlanan servisleri personel bazında getir (service_plannings tablosundan)
    $completedServicesQuery = ServicePlanning::select(
        'service_plannings.kid as personel_id',
        'tb_user.name as adsoyad',
        'service_plannings.servisid'
    )
    ->leftJoin('tb_user', 'service_plannings.kid', '=', 'tb_user.user_id')
    ->where('service_plannings.gidenIslem', 255) // Tamamlanan servisler
    ->whereBetween('service_plannings.created_at', [$startDateTime, $endDateTime])
    ->where('service_plannings.firma_id', $tenant_id)
    ->whereIn('service_plannings.kid', $validUserIds);

    // Cihaz türü filtresi varsa ekle
    if ($deviceTypeId) {
        $completedServicesQuery->leftJoin('services', 'service_plannings.servisid', '=', 'services.id')
                              ->where('services.cihazTur', $deviceTypeId);
    }

    $completedServices = $completedServicesQuery->get();

    // 2. Yapılan anketleri personel bazında getir
    $surveysQuery = Survey::select(
        'surveys.ekleyen as personel_id', // surveys tablosunda ekleyen personel alanı kullanılıyor
        'surveys.servisid'
    )
    ->whereBetween('surveys.created_at', [$startDateTime, $endDateTime])
    ->where('surveys.firma_id', $tenant_id);

    // Cihaz türü filtresi varsa ekle
    if ($deviceTypeId) {
        $surveysQuery->leftJoin('services', 'surveys.servisid', '=', 'services.id')
                    ->where('services.cihazTur', $deviceTypeId)
                    ->whereIn('surveys.ekleyen', $validUserIds);
    }

    $surveys = $surveysQuery->get();

    // 3. Personel bazında grupla
    $groupedStats = [];

    // Önce tamamlanan servisleri grupla
    foreach ($completedServices as $service) {
        if (!isset($groupedStats[$service->personel_id])) {
            $groupedStats[$service->personel_id] = [
                'personel_id' => $service->personel_id,
                'adsoyad' => $service->adsoyad,
                'tamamlanan_servisler' => [],
                'anket_yapilan_servisler' => []
            ];
        }
        // Aynı servisi birden fazla kez eklememek için kontrol et
        if (!in_array($service->servisid, $groupedStats[$service->personel_id]['tamamlanan_servisler'])) {
            $groupedStats[$service->personel_id]['tamamlanan_servisler'][] = $service->servisid;
        }
    }

    // Sonra anket yapılan servisleri ekle
    foreach ($surveys as $survey) {
        // Eğer bu personel daha önce eklenmemişse (tamamlanan servisi yoksa), ekle
        if (!isset($groupedStats[$survey->personel_id])) {
            // Personel adını almak için ayrı sorgu
            $user = User::find($survey->personel_id);
            $groupedStats[$survey->personel_id] = [
                'personel_id' => $survey->personel_id,
                'adsoyad' => $user ? $user->name : 'Bilinmeyen Personel',
                'tamamlanan_servisler' => [],
                'anket_yapilan_servisler' => []
            ];
        }
        
        // Aynı servisi birden fazla kez eklememek için kontrol et
        if (!in_array($survey->servisid, $groupedStats[$survey->personel_id]['anket_yapilan_servisler'])) {
            $groupedStats[$survey->personel_id]['anket_yapilan_servisler'][] = $survey->servisid;
        }
    }

    // 4. Sayıları hesapla ve final formatı oluştur
    $finalStats = [];
    foreach ($groupedStats as $personelId => $stat) {
        $finalStats[$personelId] = [
            'personel_id' => $stat['personel_id'],
            'adsoyad' => $stat['adsoyad'],
            'tamamlanan_servis_sayisi' => count($stat['tamamlanan_servisler']),
            'anket_yapilan_servis_sayisi' => count($stat['anket_yapilan_servisler']),
            'servisler' => $stat['tamamlanan_servisler'] // Detay butonu için kullanılabilir
        ];
    }

    // 5. Toplam sayıları hesapla
    $totalCompletedServices = 0;
    $totalSurveyedServices = 0;
    
    foreach ($finalStats as $stat) {
        $totalCompletedServices += $stat['tamamlanan_servis_sayisi'];
        $totalSurveyedServices += $stat['anket_yapilan_servis_sayisi'];
    }
    $firma = Tenant::findOrFail($tenant_id);
    $isBeyazEsya = $firma->sektor === 'beyaz-esya';

    // Cihaz türleri listesi
    $deviceTypes = DeviceType::where(function($query) use ($firma, $isBeyazEsya) {
        if ($isBeyazEsya) {
            // Beyaz eşya sektörü: default + kendi eklediği
            $query->whereNull('firma_id')
                ->orWhere('firma_id', $firma->id);
        } else {
            // Diğer sektörler: sadece kendi eklediği
            $query->where('firma_id', $firma->id);
        }
    })->orderBy('cihaz', 'asc')->get();

    return response()->json([
        'personnelStats' => $finalStats,
        'totalCompletedServices' => $totalCompletedServices,
        'totalSurveyedServices' => $totalSurveyedServices,
        'deviceTypes' => $deviceTypes
    ]);
}

public function getSurveyResults(Request $request, $tenant_id) 
{
    $fromDate = $request->from_date ?: Carbon::yesterday()->format('Y-m-d');
    $toDate = $request->to_date ?: Carbon::yesterday()->format('Y-m-d');
    $deviceTypeId = $request->device_type_id;
    $bayiId = $request->bayi_id;

    // Tarih aralığı için başlangıç ve bitiş zamanları
    $startDateTime = $fromDate . ' 00:00:00';
    $endDateTime = $toDate . ' 23:59:59';

    // Anket sonuçlarını getir
    $surveysQuery = Survey::select(
        'surveys.*',
        'tb_user.name as personel_adi'
    )
    ->leftJoin('tb_user', 'surveys.personel', '=', 'tb_user.user_id')
    ->whereBetween('surveys.created_at', [$startDateTime, $endDateTime])
    ->where('surveys.firma_id', $tenant_id);

    // Bayi filtresi
    if ($bayiId) {
        $surveysQuery->where('surveys.bayi', $bayiId);
    }

    // Cihaz türü filtresi
    if ($deviceTypeId) {
        $surveysQuery->leftJoin('services', 'surveys.servisid', '=', 'services.id')
                    ->where('services.cihazTur', $deviceTypeId);
    }

    $surveys = $surveysQuery->get();

    // Soru istatistiklerini hesapla
    $questionStats = [
        'soru1' => ['evet' => 0, 'hayir' => 0, 'belli_degil' => 0],
        'soru2' => ['evet' => 0, 'hayir' => 0, 'belli_degil' => 0],
        'soru3' => ['evet' => 0, 'hayir' => 0, 'belli_degil' => 0],
        'soru5' => ['evet' => 0, 'hayir' => 0, 'belli_degil' => 0]
    ];

    foreach ($surveys as $survey) {
        // Soru 1 istatistikleri
        if ($survey->soru1 == 1) $questionStats['soru1']['evet']++;
        elseif ($survey->soru1 == 2) $questionStats['soru1']['hayir']++;
        elseif ($survey->soru1 == 0) $questionStats['soru1']['belli_degil']++;

        // Soru 2 istatistikleri
        if ($survey->soru2 == 1) $questionStats['soru2']['evet']++;
        elseif ($survey->soru2 == 2) $questionStats['soru2']['hayir']++;
        elseif ($survey->soru2 == 0) $questionStats['soru2']['belli_degil']++;

        // Soru 3 istatistikleri
        if ($survey->soru3 == 1) $questionStats['soru3']['evet']++;
        elseif ($survey->soru3 == 2) $questionStats['soru3']['hayir']++;
        elseif ($survey->soru3 == 0) $questionStats['soru3']['belli_degil']++;

        // Soru 5 istatistikleri
        if ($survey->soru5 == 1) $questionStats['soru5']['evet']++;
        elseif ($survey->soru5 == 2) $questionStats['soru5']['hayir']++;
        elseif ($survey->soru5 == 0) $questionStats['soru5']['belli_degil']++;
    }
    // Toplam anket sayısı
    $totalSurveys = $surveys->count();
    // Yüzdelik hesaplamaları
    $questionPercentages = [];
    foreach ($questionStats as $questionKey => $stats) {
        $total = array_sum($stats); // Tüm cevapların toplamı (evet + hayir + belli_degil)

        $questionPercentages[$questionKey] = [
            'evet_percentage'       => $total > 0 ? round(($stats['evet'] / $total) * 100, 1) : 0,
            'hayir_percentage'      => $total > 0 ? round(($stats['hayir'] / $total) * 100, 1) : 0,
            'belli_degil_percentage' => $total > 0 ? round(($stats['belli_degil'] / $total) * 100, 1) : 0,
        ];
    }
    // Bayiler listesi
    $bayiRole = Role::where('name', 'Bayi')->first();
    $bayiRoleId = $bayiRole ? $bayiRole->id : null;
    $bayiler = User::where('tenant_id', $tenant_id)
        ->whereHas('roles', function ($query) use ($bayiRoleId) {
            $query->where('id', $bayiRoleId);
        })
        ->get();

        $firma = Tenant::findOrFail($tenant_id);
        $isBeyazEsya = $firma->sektor === 'beyaz-esya';

    // Cihaz türleri
    $deviceTypes = DeviceType::where(function($query) use ($firma, $isBeyazEsya) {
        if ($isBeyazEsya) {
            // Beyaz eşya sektörü: default + kendi eklediği
            $query->whereNull('firma_id')
                ->orWhere('firma_id', $firma->id);
        } else {
            // Diğer sektörler: sadece kendi eklediği
            $query->where('firma_id', $firma->id);
        }
    })->orderBy('cihaz', 'asc')->get();

    return response()->json([
        'questionStats' => $questionStats,
        'questionPercentages' => $questionPercentages,
        'totalSurveys' => $totalSurveys,
        'bayiler' => $bayiler,
        'deviceTypes' => $deviceTypes,
        'surveys' => $surveys->toArray()
    ]);
}
///////////////////////////////////////////////////////Cash Statistics///////////////////////////////////////////////////////////////////
public function CashStatistics($tenant_id) {

    $firma_id = $tenant_id;
    $today = date('Y-m-d');  // Bugünün tarihi
    
    // Mevcut gün için kasa hareketlerini getir
    $kasaHareketleri = CashTransaction::where('firma_id', $firma_id)
        ->whereDate('created_at', $today)
        ->where('odemeYonu', '1')
        ->get();

    $nakit = 0;
    $eft = 0;
    $kart = 0;

    \Log::info('Found transactions:', ['count' => $kasaHareketleri->count()]);


    foreach ($kasaHareketleri as $row){
        \Log::info('Processing record:', [
            'id' => $row->id,
            'odemeSekli' => $row->odemeSekli,
            'fiyat' => $row->fiyat,
            'created_at' => $row->created_at
        ]);

        $odemeSekli = (int)$row->odemeSekli;
        $fiyat = (float)$row->fiyat;
        
        if($odemeSekli === 1){ // Nakit
            $nakit += $fiyat;
        }elseif($odemeSekli === 2){ // EFT/Havale  
            $eft += $fiyat;
        }elseif($odemeSekli === 3){ // Kredi Kartı
            $kart += $fiyat;
        }
    }

    $nakit = round($nakit);
    $eft = round($eft);
    $kart = round($kart);
    $gelirler = $nakit + $eft + $kart;

    \Log::info('Final calculated values:', [
        'nakit' => $nakit,
        'eft' => $eft, 
        'kart' => $kart,
        'toplam' => $gelirler
    ]);

    // Ödeme şekillerini getir
    $odemeSekliAll = "";
    $odemeSekilleri = PaymentMethod::where(function($query) use ($firma_id) {
        $query->where('firma_id', $firma_id)
              ->orWhereNull('firma_id');
    })
    ->orderBy('id','asc')
    ->get();

    foreach ($odemeSekilleri as $odemeSekli){
        if(empty($odemeSekliAll)){
            $odemeSekliAll = '"'.$odemeSekli->odemeSekli.'"';
        }else{
            $odemeSekliAll .= ',"'.$odemeSekli->odemeSekli.'"';
        }
    }

    // Gider tablosu için
    $odemeTurleri = PaymentType::orderBy('odemeTuru', 'asc')->get();
    $odemeTuruAll = [];
    
    foreach($odemeTurleri as $odemeTuru){
        $gelir = CashTransaction::where('firma_id', $firma_id)
            ->whereDate('created_at', $today)
            ->where('odemeYonu', '2') // Giderler
            ->where('odemeTuru', $odemeTuru->id)
            ->sum('fiyat');
        $odemeTuruAll[$odemeTuru->odemeTuru] = round($gelir);
    }
    
    $giderlerToplam = array_sum($odemeTuruAll);
    
    // Grafik için veri hazırlama
    $odemeTuruSonuc = json_encode(array_keys($odemeTuruAll));
    $renkler = [
    '#E91E63', // Canlı Pembe
    '#FF5722', // Domates Kırmızısı
    '#FF9800', // Parlak Turuncu
    '#FFC107', // Amber Sarısı
    '#8BC34A', // Canlı Yeşil
    '#4CAF50', // Klasik Yeşil
    '#00BCD4', // Turkuaz (Cyan)
    '#009688', // Deniz Yeşili (Teal)
    '#2196F3', // Gökyüzü Mavisi
    '#3F51B5', // Lacivert (Indigo)
    '#673AB7', // Derin Mor
    '#9C27B0', // Fuşya
    '#F44336'  // Klasik Kırmızı
];
    $odemeTuruRenkler = json_encode(array_slice($renkler, 0, count($odemeTuruAll)));
    $giderler = rtrim(implode(",", $odemeTuruAll), ",");
    
    return view('frontend.secure.statistics.cash_statistics', compact(
        'tenant_id','nakit','eft','kart','gelirler','odemeSekliAll',
        'odemeTuruAll','giderlerToplam','odemeTuruSonuc','odemeTuruRenkler','giderler'
    ));
}

public function kasaFilteredData(Request $request, $tenant_id){
    $firma_id = $tenant_id;
    $startDate = $request->input('startDate');
    $endDate = $request->input('endDate');

    // Veritabanından belirli bir tarih aralığına göre verileri al
    $kasaHareketleri = CashTransaction::where('firma_id', $firma_id)
        ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
        ->where('odemeYonu', '1')
        ->get();

    // Debug
    \Log::info('Filtered Kasa Hareketleri:', $kasaHareketleri->toArray());
  
    // Verileri işle
    $nakit = 0;
    $eft = 0;
    $kart = 0;
    
    foreach ($kasaHareketleri as $row) {
        if ($row->odemeSekli == 1) { // Nakit
            $nakit += $row->fiyat;
        } elseif ($row->odemeSekli == 2) { // EFT/Havale
            $eft += $row->fiyat;
        } elseif ($row->odemeSekli == 3) { // Kredi Kartı
            $kart += $row->fiyat;
        }
    }
    
    $nakit = round($nakit);
    $eft = round($eft);
    $kart = round($kart);
    
    $gelirler = $nakit + $eft + $kart;

    \Log::info('Filtered hesaplanan değerler:', [
        'nakit' => $nakit,
        'eft' => $eft,
        'kart' => $kart,
        'toplam' => $gelirler
    ]);

    // Verileri JSON formatında geri gönder
    return response()->json([
        'nakit' => number_format($nakit, 0, ',', '.'),
        'eft' => number_format($eft, 0, ',', '.'),
        'kart' => number_format($kart, 0, ',', '.'),
        'toplam' => number_format($gelirler, 0, ',', '.')
    ]);
}
public function giderTabloGetir(Request $request, $tenant_id){
    $firma_id = $tenant_id; 
    
    $startDate = Carbon::parse($request->input('startDate'))->startOfDay();
    $endDate = Carbon::parse($request->input('endDate'))->endOfDay();

    // Giderleri hesapla
    $odemeTurleri = PaymentType::orderBy('odemeTuru', 'asc')->get();
    $odemeTuruAll = [];
    
    foreach($odemeTurleri as $odemeTuru){
        $gelir = CashTransaction::where('firma_id', $firma_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('odemeYonu', '2')
            ->where('odemeTuru', $odemeTuru->id)
            ->sum('fiyat');
        $odemeTuruAll[$odemeTuru->odemeTuru] = round($gelir);
    }
    
    $giderlerToplam = array_sum($odemeTuruAll);

    // Varsayılan renk paleti
    $renkler = [
    '#E91E63', // Canlı Pembe
    '#FF5722', // Domates Kırmızısı
    '#FF9800', // Parlak Turuncu
    '#FFC107', // Amber Sarısı
    '#8BC34A', // Canlı Yeşil
    '#4CAF50', // Klasik Yeşil
    '#00BCD4', // Turkuaz (Cyan)
    '#009688', // Deniz Yeşili (Teal)
    '#2196F3', // Gökyüzü Mavisi
    '#3F51B5', // Lacivert (Indigo)
    '#673AB7', // Derin Mor
    '#9C27B0', // Fuşya
    '#F44336'  // Klasik Kırmızı
];


    $html = '';
$colorIndex = 0;
$renkler = [
    '#E91E63', '#FF5722', '#FF9800', '#FFC107', '#8BC34A', '#4CAF50',
    '#00BCD4', '#009688', '#2196F3', '#3F51B5', '#673AB7', '#9C27B0', '#F44336'
];

foreach ($odemeTuruAll as $key => $value) {
    $renk = $renkler[$colorIndex % count($renkler)];
    $html .= '<li class="gider">
                <div class="renk" style="background:'.$renk.'"></div>
                <div class="adi">'.$key.'</div>
                <div class="para">'.number_format($value, 0, ',', '.').' TL</div>
              </li>';
    $colorIndex++;
}

// Toplam satırını ekle
$html .= '<li class="gider toplam-satir">
            <div class="renk" style="background:#000"></div>
            <div class="adi">Toplam</div>
            <div class="para">'.number_format($giderlerToplam, 0, ',', '.').' TL</div>
          </li>';

return response()->json(['html' => $html]);
}

public function gelirGrafikGetir(Request $request, $tenant_id){
    $firma_id = $tenant_id; 
    $startDate = $request->input('startDate');
    $endDate = $request->input('endDate');

    // Veritabanından belirli bir tarih aralığına göre verileri al
    $kasaHareketleri = CashTransaction::where('firma_id', $firma_id)
        ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
        ->where('odemeYonu', '1')
        ->get();

    // Debug
    \Log::info('Grafik için Kasa Hareketleri:', $kasaHareketleri->toArray());
  
    // Verileri işle
    $nakit = 0;
    $eft = 0;
    $kart = 0;
    
    foreach ($kasaHareketleri as $row) {
        if ($row->odemeSekli == 1) { // Nakit
            $nakit += $row->fiyat;
        } elseif ($row->odemeSekli == 2) { // EFT/Havale
            $eft += $row->fiyat;
        } elseif ($row->odemeSekli == 3) { // Kredi Kartı
            $kart += $row->fiyat;
        }
    }
    
    $gelirler = $nakit + $eft + $kart;

    \Log::info('Grafik hesaplanan değerler:', [
        'nakit' => $nakit,
        'eft' => $eft,
        'kart' => $kart,
        'toplam' => $gelirler
    ]);

    // Verileri JSON formatında geri gönder
    return response()->json([
        'nakit' => $nakit,
        'eft' => $eft,
        'kart' => $kart,
        'toplam' => $gelirler
    ]);
}
public function giderGrafikGetir(Request $request, $tenant_id){
    $firma_id = $tenant_id; 
    $startDate = $request->input('startDate');
    $endDate = $request->input('endDate');

    // Giderleri hesapla
    $odemeTurleri = PaymentType::orderBy('odemeTuru', 'asc')->get();
    $odemeTuruAll = [];
    
    foreach($odemeTurleri as $odemeTuru){
        $gelir = CashTransaction::where('firma_id', $firma_id)
            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->where('odemeYonu', '2')
            ->where('odemeTuru', $odemeTuru->id)
            ->sum('fiyat');
        $odemeTuruAll[$odemeTuru->odemeTuru] = round($gelir);
    }

    $giderlerToplam = array_sum($odemeTuruAll);
    $odemeTuruSonuc = json_encode(array_keys($odemeTuruAll));
    
    // Varsayılan renk paleti
   $renkler = [
    '#E91E63', // Canlı Pembe
    '#FF5722', // Domates Kırmızısı
    '#FF9800', // Parlak Turuncu
    '#FFC107', // Amber Sarısı
    '#8BC34A', // Canlı Yeşil
    '#4CAF50', // Klasik Yeşil
    '#00BCD4', // Turkuaz (Cyan)
    '#009688', // Deniz Yeşili (Teal)
    '#2196F3', // Gökyüzü Mavisi
    '#3F51B5', // Lacivert (Indigo)
    '#673AB7', // Derin Mor
    '#9C27B0', // Fuşya
    '#F44336'  // Klasik Kırmızı
];

    $odemeTuruRenkler = json_encode(array_slice($renkler, 0, count($odemeTuruAll)));
    $giderler = rtrim(implode(",", $odemeTuruAll), ",");

    return response()->json([
        'giderler' => $giderler,
        'labels' => $odemeTuruSonuc,
        'colors' => $odemeTuruRenkler
    ]);
}


}
