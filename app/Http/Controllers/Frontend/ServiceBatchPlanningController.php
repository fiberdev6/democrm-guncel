<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\DeviceType;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\Service;
use App\Models\ServicePlanning;
use App\Models\ServicePlanStatu;
use App\Models\ServiceResource;
use App\Models\ServiceStageAnswer;
use App\Models\StageQuestion;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceBatchPlanningController extends Controller
{
    public function ServiceBatchPlanning($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $teknisyen = User::role(['Teknisyen'])->where('tenant_id', $tenant_id)->get();
        $kid = auth()->user()->user_id;
        
        // Get planning statuses
        $planningStatuses = ServicePlanStatu::where('kid', $kid)->first();
        
        // Get districts for Istanbul
        $districts = Ilce::where('sehir_id', '34')->orderBy('ilceName')->get();
        $iller = Il::orderBy('name', 'asc')->get();

        $isBeyazEsya = $firma->sektor === 'beyaz-esya';

        // Get device types
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
        
        // Get service sources
        $serviceSources = ServiceResource::where('firma_id', $tenant_id)->orderBy('id')->get();
        
        // Tomorrow's date as default
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        return view('frontend.secure.all_services.service_batch_planning.batch_plannings', compact('firma','teknisyen','planningStatuses',
            'districts', 
            'deviceTypes',
            'serviceSources',
            'tomorrow','iller'));
    }

    public function getServiceList(Request $request, $tenant_id)
    {        
        // Gelen filtre verileri
        $kid = auth()->user()->user_id;
        $date = $request->input('planTarih');
        $il = $request->input('il');
        $districts = $request->input('bolgeler', []);
        $deviceTypes = $request->input('cihazlar', []);
        $sources = $request->input('kaynaklar', []);
        $statuses = $request->input('durumlar');
        $persID = $request->get('persID'); // AJAX'tan gelen persID

        $firma = Tenant::where('id', $tenant_id)->first();
        // Sorgu başlangıcı
        $query = Service::query()->with(['musteri', 'markaCihaz', 'turCihaz'])
            ->where('firma_id', $tenant_id)
            ->where('durum', '1');

        if (!empty($persID)) {
            // Personnel specific logic - bugün için atanan servisleri getir
            $today = Carbon::today()->format('Y-m-d');
            
            $selectedDates = ServiceStageAnswer::where('cevap', $today)
                ->where('firma_id', $tenant_id)
                ->where('soruid', 48)
                ->pluck('planid');

            $serviceAnswers = ServiceStageAnswer::where('cevap', $persID)
                ->whereIn('planid', $selectedDates)
                ->where('soruid', 45) // Personel atama sorusu ID'si
                ->pluck('servisid');

            if ($serviceAnswers->isEmpty()) {
                $query->whereRaw('1 = 0'); // Hiçbir sonuç döndürmemek için
            } else {
                $query->whereIn('id', $serviceAnswers);
            }
            
        } else {
            // Normal filtreleme işlemleri
            
            // Duruma göre filtreleme
            if ($statuses == "235-2") {
                $statuses = "235";
            }

            if (!empty($statuses) && $statuses !== '0') {
                $query->where('servisDurum', $statuses);
            }

            // İl ve İlçe filtreleme - Düzeltildi
            if (!empty($districts) && !in_array('0', $districts)) {
                $query->whereHas('musteri', function($q) use ($districts) {
                    $q->whereIn('ilce', $districts);
                });
            } elseif (!empty($il) && $il !== '0') {
                // İlçe seçilmemişse ama il seçilmişse
                $query->whereHas('musteri', function($q) use ($il) {
                    $q->where('il', $il);
                });
            }

            // Cihaz türü filtreleme - Düzeltildi
            if (!empty($deviceTypes) && !in_array('0', $deviceTypes)) {
                $query->whereIn('cihazTur', $deviceTypes);
            }

            // Kaynak filtreleme - Düzeltildi
            if (!empty($sources) && !in_array('0', $sources)) {
                $query->whereIn('servisKaynak', $sources);
            }

            // Tarih filtreleme - Düzeltildi
            if (!empty($date)) {
                try {
                    // 2025‑07‑08 veya 08‑07‑2025 → 2025‑07‑08
                    $dateFormatted = preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)
                        ? Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d')
                        : Carbon::parse($date)->format('Y-m-d');

                    switch ($statuses) {
                        // --- Yeni Servisler -------------------------------------------------
                        case '235':
                        case '264':
                            $query->whereDate('musaitTarih', $dateFormatted);
                            break;

                        // --- Parça Hazır -----------------------------------------------------
                        case '261':
                            $query->where('servisDurum', '261')
                                ->whereHas('plans', function ($q) use ($dateFormatted) {
                                    // sondan bir önceki plan (skip(1)) ve gidenIslem = 257
                                    $q->latest()
                                        ->skip(1)
                                        ->take(1)
                                        ->where('gidenIslem', 257)
                                        ->whereHas('answers', function ($q2) use ($dateFormatted) {
                                            $q2->where('soruid', 35)
                                            ->whereDate('cevap', $dateFormatted);
                                        });
                                });
                            break;

                        // --- Diğer Statüler -------------------------------------------------
                        default:
                            // Bu aşamada [Tarih] sorusu var mı?
                            $hasDateQuestion = StageQuestion::where('asama', $statuses)
                                                ->where('cevapTuru', '[Tarih]')
                                                ->exists();

                            if ($hasDateQuestion) {
                                // planDurum kolonu → o plana ait [Tarih] cevabı
                                $query->whereHas('currentPlan.answers', function ($q) use ($dateFormatted) {
                                    $q->whereDate('cevap', $dateFormatted)
                                    ->whereHas('question', function ($q2) {
                                        $q2->where('cevapTuru', '[Tarih]');
                                    });
                                });
                            }
                            // tarih sorusu yoksa bu statüde tarih filtresi uygulanmaz
                            break;
                    }
                } catch (\Exception $e) {
                    Log::error('Tarih format hatası veya filtreleme sorunu: '.$e->getMessage());
                }
            }
        }
        // Sonuçları getir
        $services = $query->orderByDesc('id')->get();


        /* Planlama ayarları (grup filtresi) */
            $defaultRoles = [
                'Teknisyen',
                'Teknisyen Yardımcısı',
                'Atölye Çırak',
                'Atölye Ustası',
            ];

            $personeller = User::query()
                ->role($defaultRoles)
                ->where('tenant_id', $tenant_id)
                ->where('status', 1)
                ->orderBy('name')
                ->get(['user_id', 'name']);

            /* Bugün atama yapılan planId'leri */
            $today = Carbon::now()->format('Y-m-d');
            $todayPlans = ServiceStageAnswer::where('soruid', 48)
                ->where('cevap', $today)
                ->where('firma_id', $tenant_id) // Firma filtresi eklendi
                ->pluck('planid');

            /* Kişi başına "bugün atanan servis" sayısı */
            $rawCounts = ServiceStageAnswer::select('cevap as personel_id', DB::raw('COUNT(*) as toplam'))
                ->whereIn('cevap', $personeller->pluck('user_id'))
                ->whereIn('planid', $todayPlans)
                ->where('soruid', 45) // Personel atama sorusu ID'si
                ->groupBy('cevap')
                ->pluck('toplam', 'personel_id')
                ->toArray();

            // Eksik olanlara 0 ver
            $personelAtamaSayilari = [];
            foreach ($personeller as $p) {
                $personelAtamaSayilari[$p->user_id] = $rawCounts[$p->user_id] ?? 0;
            }

        return view('frontend.secure.all_services.service_batch_planning.list', 
            compact('services', 'firma', 'personeller', 'personelAtamaSayilari','statuses','persID'));
    }

    public function getDistricts(Request $request, $tenant_id)
    {
        $city = $request->city_id;
        
        $districts = Ilce::where('sehir_id', $city)
            ->orderBy('ilceName')
            ->get(['id', 'ilceName']);
        
        return response()->json($districts);
    }

    public function getServicePlanForm(Request $request, $tenant_id)
    {
        $servisIds = $request->input('servisidler'); // 'servisidler' query string'den geliyor
        $gelenDurum = $request->input('gelenDurum');
        $gidenDurum = $request->input('gidenDurum');

        $idList = explode(', ', $servisIds);

        // Burada her servisin tenant_id'sini kontrol edebilirsiniz
        foreach ($idList as $serviceId) {
            $service = Service::where('id', $serviceId)->where('firma_id', $tenant_id)->first();
            
        }

        // PHP kodundaki $gelenDurum == "235-2" kontrolü
        if ($gelenDurum == "235-2") {
            $gelenDurum = "235";
        }

        // Atanacak aşamaya ait soruları çek
        $questions = StageQuestion::where('asama', $gidenDurum)
                                        ->orderBy('sira', 'ASC')
                                        ->get();

        $personnel = User::role(['Teknisyen', 'Teknisyen Yardımcısı', 'Atölye Çırak', 'Atölye Ustası']) // Rol bazlı personel çekimi
                         ->where('tenant_id', $tenant_id)
                         ->where('status', 1)
                         ->orderBy('name')
                         ->get(['user_id', 'name']);

        $vehicles = Car::where('firma_id', $tenant_id)->where('durum', '1')->get();

        // Bayi personellerini çek (PHP'deki grup=258)
        $dealers = User::role('Bayi') // Varsayılan olarak bayi rolü var ise
                        ->where('tenant_id', $tenant_id)
                        ->where('status', 1)
                        ->orderBy('name')
                        ->get(['user_id', 'name']);

        $defaultDate = Carbon::now();
        
        $bugun = date('w'); // 0: Pazar, 6: Cumartesi
         $date = ($bugun == 6)
                ? date('Y-m-d', strtotime('+2 days'))
                : date('Y-m-d', strtotime('+1 day'));
         $defaultDateFormatted = $date;     


        return view('frontend.secure.all_services.service_batch_planning.assignment_form', compact(
            'questions',
            'personnel',
            'vehicles',
            'dealers',
            'servisIds',
            'gelenDurum',
            'gidenDurum',
            'defaultDateFormatted',
            'tenant_id'
        ));
    }

    public function assignService(Request $request, $tenant_id)
    {
        try {
            $ids        = array_filter(explode(',', $request->servisidler));
            $gelenIslem = (int) $request->gelenIslem;
            $gidenIslem = (int) $request->gidenIslem;
            $today      = Carbon::now()->format('Y-m-d');

            $services = Service::where('firma_id', $tenant_id)
                            ->whereIn('id', $ids)
                            ->get(['id']);

            /* ----------------------------------------------------
            | Özel durum: hem gelen hem giden 264 ise -> eski bayi planlarını sil
            |----------------------------------------------------*/
            if ($gelenIslem === 264 && $gidenIslem === 264) {
                ServicePlanning::whereIn('servisid', $ids)->delete();
                ServiceStageAnswer::whereIn('servisid', $ids)->delete();
            }

            foreach ($ids as $sid) {
                /* servis_planlama kaydı */
                $plan = ServicePlanning::create([
                    'firma_id' => $tenant_id,
                    'kid'        => auth()->id(),
                    'pid'        => auth()->id(),
                    'servisid'   => $sid,
                    'gelenIslem' => $gelenIslem,
                    'gidenIslem' => $gidenIslem,
                    'tarihKontrol' => '0',
                    'tarihDurum' => '0'
                ]);
                
                $servisPlanlar = ServicePlanning::where('firma_id', $tenant_id)->where('tarihKontrol', '0')
                    ->get();

                foreach ($servisPlanlar as $servisRow) {
                    $tarihDurum = "0";
                    $cevaplar = ServiceStageAnswer::where('firma_id', $tenant_id)->where('planid', $plan->id)
                        ->get();

                    foreach ($cevaplar as $cevapRow) {
                        $soru = StageQuestion::where('id', $cevapRow->soruid)
                            ->first();

                        if ($soru && $soru->cevapTuru == "[Tarih]") {
                            $tarihDurum = "1";
                            break;
                        }
                    }

                    ServicePlanning::where('firma_id', $tenant_id)->where('id', $plan->id)
                        ->update([
                            'tarihDurum' => $tarihDurum,
                            'tarihKontrol' => "1",
                            'updated_at' => now()
                        ]);
                }
                
                /* servis güncelle */
                Service::where('id', $sid)->update([
                    'servisDurum' => $gidenIslem,
                    'planDurum'   => $plan->id,
                ]);

                /* dinamik soru cevapları */
                foreach ($request->all() as $key => $val) {
                    if (str_starts_with($key, 'soru') && $val !== null) {
                        $soruId = (int) substr($key, 4);  // "soru374" → 374
                        $soru = StageQuestion::where('id', $soruId)->first();
                        $answerData = [
                            'firma_id'      => $tenant_id,
                            'kid' => auth()->id(),
                            'servisid' => $sid,
                            'planid'   => $plan->id,
                            'soruid'   => $soruId,
                            'cevap'    => $val,
                            'created_at'    => Carbon::now(),
                            'cevapText' => $soru->cevapTuru,
                        ];

                        // özel durum: soru374 → cevapText ekle
                        if ($key === 'soru3') {
                            $answerData['cevapText'] = '[Bayi]';
                        }

                        ServiceStageAnswer::create($answerData);
                    }
                }
            }

            // JSON response döndür
            return response()->json([
                'success' => 'Toplu planlama tamamlandı',
                'status' => 'success'
            ], 200);
            
        } catch (\Exception $e) {
            // Hata durumunda JSON hata response'u döndür
            return response()->json([
                'error' => 'Bir hata oluştu: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    public function getServicePlanUpdateForm($tenant_id, Request $request) {
        $servisIds = $request->input('servisidler'); // 'servisidler' query string'den geliyor
        $gelenDurum = $request->input('gelenDurum');
        $gidenDurum = $request->input('gidenDurum');
        $personelid = $request->input('personel');

        $idList = explode(', ', $servisIds);

        // Burada her servisin tenant_id'sini kontrol edebilirsiniz
        foreach ($idList as $serviceId) {
            $service = Service::where('id', $serviceId)->where('firma_id', $tenant_id)->first();
            
        }

        // PHP kodundaki $gelenDurum == "235-2" kontrolü
        if ($gelenDurum == "235-2") {
            $gelenDurum = "235";
        }

        // Atanacak aşamaya ait soruları çek
        $questions = StageQuestion::where('asama', $gidenDurum)
                                        ->orderBy('sira', 'ASC')
                                        ->get();

        $personnel = User::role(['Teknisyen', 'Teknisyen Yardımcısı', 'Atölye Çırak', 'Atölye Ustası']) // Rol bazlı personel çekimi
                         ->where('tenant_id', $tenant_id)
                         ->where('status', 1)
                         ->orderBy('name')
                         ->get(['user_id', 'name']);

        $vehicles = Car::where('firma_id', $tenant_id)->where('durum', '1')->get();

        // Bayi personellerini çek (PHP'deki grup=258)
        $dealers = User::role('Bayi') // Varsayılan olarak bayi rolü var ise
                        ->where('tenant_id', $tenant_id)
                        ->where('status', 1)
                        ->orderBy('name')
                        ->get(['user_id', 'name']);

        $defaultDate = Carbon::now();
        
        $bugun = date('w'); // 0: Pazar, 6: Cumartesi
         $date = ($bugun == 6)
                ? date('Y-m-d', strtotime('+2 days'))
                : date('Y-m-d', strtotime('+1 day'));
         $defaultDateFormatted = $date;     


        return view('frontend.secure.all_services.service_batch_planning.appointment_to_staff', compact(
            'questions',
            'personnel',
            'vehicles',
            'dealers',
            'servisIds',
            'gelenDurum',
            'gidenDurum',
            'defaultDateFormatted',
            'tenant_id',
            'personelid',
        ));
    }

    public function updatePersonelBatch(Request $request, $tenant_id)
    {
        /* --- 0. Parametreler --- */
        $ids       = array_filter(explode(',', $request->servisidler));
        $giden     = (int) $request->gidenIslem;
        $today     = now()->format('Y-m-d');

        /* --- 1. Kira doğrulaması --- */
        $count = Service::where('firma_id', $tenant_id)->whereIn('id', $ids)->count();
        if ($count !== count($ids)) {
            return response()->json(['error' => '-1'], 403);
        }


        foreach ($ids as $sid) {
            $service = Service::where('firma_id', $tenant_id)->findOrFail($sid);
            $plan    = ServicePlanning::where('firma_id', $tenant_id)->findOrFail($service->planDurum);

            $plan->update([
                'kid' => auth()->id(),
                'pid' => auth()->id(),
            ]);

            /*eski cevapları sil */
            ServiceStageAnswer::where('planid', $plan->id)->delete();

            /* yeni cevapları ekle */
            foreach ($request->all() as $key => $val) {
                if (!str_starts_with($key, 'soru') || $val === null) {
                    continue;
                }

                $soruId = (int) substr($key, 4);
                $soru   = StageQuestion::find($soruId);

                $answerData = [
                    'firma_id'  => $tenant_id,
                    'kid'       => auth()->id(),
                    'servisid'  => $sid,
                    'planid'    => $plan->id,
                    'soruid'    => $soruId,
                    'cevap'     => $val,
                    'cevapText' => $soru->cevapTuru ?? null,
                    'created_at'=> now(),
                ];

                if ($key === 'soru3') {          // eski [Bayi] özel durumu
                    $answerData['cevapText'] = '[Bayi]';
                }

                ServiceStageAnswer::create($answerData);
            }

            /* 3.4 plan tarih kontrolleri */
            $tarihDurum = ServiceStageAnswer::where('planid', $plan->id)
                ->whereHas('question', fn($q)=>$q->where('cevapTuru', '[Tarih]'))
                ->exists();

            $plan->update([
                'tarihDurum'   => $tarihDurum ? '1' : '0',
                'tarihKontrol' => '1'
            ]);

            /* 3.5 servis durumunu güncelle */
            $service->update(['servisDurum' => $giden]);
        }
        return response()->json([
            'success' => 'Toplu planlama tamamlandı',
            'status'  => 'success'
        ]);
    }
}

