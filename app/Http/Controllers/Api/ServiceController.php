<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ArizaKodu;
use App\Models\Car;
use App\Models\CashTransaction;
use App\Models\DeviceBrand;
use App\Models\DeviceType;
use App\Models\PaymentMethod;
use App\Models\PaymentType;
use App\Models\PersonelStock;
use App\Models\ReceiptDesign;
use App\Models\Service;
use App\Models\ServiceFormSetting;
use App\Models\ServiceMoneyAction;
use App\Models\ServiceOptNote;
use App\Models\ServicePhoto;
use App\Models\ServicePlanning;
use App\Models\ServiceReceiptNote;
use App\Models\ServiceStage;
use App\Models\ServiceStageAnswer;
use App\Models\ServiceTime;
use App\Models\StageQuestion;
use App\Models\Stock;
use App\Models\StockAction;
use App\Models\User;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Image;


class ServiceController extends Controller
{
    const ISLEM_SIKAYET = 254;
    const ISLEM_PARCA_BEKLIYOR = 257;

    public function myAssignedServices(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json(['success' => false, 'message' => 'Firma bulunamadı'], 404);
        }

        $atananServisIDleri = $this->getYetkiliServisIDleri($user, $tenant->id);

        if (empty($atananServisIDleri)) {
            return response()->json([
                'success' => true,
                'message' => 'Bugün size atanmış servis bulunmamaktadır',
                'data' => []
            ], 200);
        }

        $services = Service::with([
            'musteri:id,adSoyad,tel1,tel2,adres,il,ilce',
            'markaCihaz:id,marka',
            'turCihaz:id,cihaz',
            'asamalar:id,asama',
        ])
            ->whereIn('id', $atananServisIDleri)
            ->where('firma_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $services->map(function ($service) use ($tenant) {
            
            $renk = "";
            
            $maviKontrol = ServicePlanning::where('servisid', $service->id)
                ->where('gidenIslem', self::ISLEM_PARCA_BEKLIYOR)
                ->exists();

            if ($maviKontrol) {
                $renk = "62daff";
            }

            $sikayetSayisi = ServicePlanning::where('servisid', $service->id)
                ->where('gidenIslem', self::ISLEM_SIKAYET)
                ->count();

            if ($sikayetSayisi == 1) $renk = "ffdf40";
            else if ($sikayetSayisi == 2) $renk = "ff8c00";
            else if ($sikayetSayisi == 3) $renk = "ff0000";
            else if ($sikayetSayisi > 3) $renk = "cf0000";

            $asamaDetay = $this->getAsamaDetaylari($service->planDurum, $tenant->id);

            return [
                'id' => $service->id,
                'plan_id' => $service->planDurum, 
                'renk' => $renk,
                'musteri' => [
                    'ad_soyad' => $service->musteri?->adSoyad,
                    'tel1' => $service->musteri?->tel1,
                    'tel2' => $service->musteri?->tel2,
                    'adres' => $service->musteri?->adres,
                    'il' => $service->musteri?->country?->name,
                    'ilce' => $service->musteri?->state?->ilceName,
                ],
                'cihaz' => [
                    'marka' => $service->markaCihaz?->marka,
                    'tur' => $service->turCihaz?->cihaz,
                    'model' => $service->cihazModel,
                    'ariza' => $service->cihazAriza,
                ],
                'asama' => $service->asamalar?->asama,
                'asama_detay' => $asamaDetay,
                'acil' => $service->acil != 0,
                'created_at' => $service->created_at->format('Y-m-d H:i'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count()
        ], 200);
    }

    private function getYetkiliServisIDleri($user, $tenantId)
    {
        $servisIzinler = [];
        $bugunYmd = date('Y-m-d');

        // Mesai başlangıç saati kontrolü
        $zamanAyar = ServiceTime::where('firma_id', $tenantId)->first(); 
        $mesaiBaslangic = $zamanAyar ? $zamanAyar->zaman : "08:00";

        $simdikiSaat = strtotime(date("H:i"));
        $baslangicSaati = strtotime($mesaiBaslangic);

        $servisCevaplari = ServiceStageAnswer::where('firma_id', $tenantId)
            ->where('cevap', $user->user_id)
            ->selectRaw('servisid, MAX(planid) as planid')
            ->groupBy('servisid')
            ->get();

        foreach ($servisCevaplari as $row) {
            $servisId = $row->servisid;
            $planId = $row->planid;

            $tarihCevap = ServiceStageAnswer::where('planid', $planId)
                ->where('cevapText', '[Tarih]')
                ->first();

            // Eğer "Gidiş Tarihi" yoksa bu servisi atla
            if (!$tarihCevap) {
                continue;
            }
            $gidisTarihi = $tarihCevap->cevap;
            
            if (strpos($gidisTarihi, '.') !== false) {
                $parts = explode('.', $gidisTarihi);
                if (count($parts) == 3) {
                    $gidisTarihi = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                }
            } else if (strpos($gidisTarihi, '/') !== false) {
                $parts = explode('/', $gidisTarihi);
                if (count($parts) == 3) {
                    $gidisTarihi = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                }
            }

            // ÖNEMLİ: Sadece "Gidiş Tarihi" BUGÜN olanları işle
            if ($bugunYmd != $gidisTarihi) {
                continue; 
            }

            // Saat Kontrolü
            if ($simdikiSaat < $baslangicSaati) {
                continue;
            }

            // Bugün bu kullanıcı bu serviste işlem yaptı mı?
            $bugunIslemYapti = ServicePlanning::where('servisid', $servisId)
                ->where('kid', $user->user_id)
                ->whereDate('created_at', $bugunYmd)
                ->exists();

            if (!$bugunIslemYapti) {
                $servisIzinler[] = $servisId;
            }
        }

        return array_unique($servisIzinler);
    }

    private function getAsamaDetaylari($planId, $tenantId)
    {
        $detaylar = [];
        
        $cevaplar = ServiceStageAnswer::where('planid', $planId)
            ->where('firma_id', $tenantId)
            ->get();

        foreach ($cevaplar as $cevap) {
            if (empty($cevap->cevap)) continue;

            $soru = StageQuestion::find($cevap->soruid);
            if (!$soru) continue;

            if (str_contains($soru->cevap, 'Grup')) {
                $personel = \App\Models\User::where('user_id', $cevap->cevap)->first();
                $detaylar[$soru->soru] = $personel ? $personel->name : 'Personel #' . $cevap->cevap;
            }
            else if ($soru->cevap == '[Arac]') {
                $arac = Car::find($cevap->cevap);
                $detaylar[$soru->soru] = $arac ? $arac->arac : $cevap->cevap;
            }
            else if ($soru->cevap == '[Parca]') {
                $parcaString = "";
                $parcalar = explode(", ", $cevap->cevap);
                
                foreach ($parcalar as $parcaItem) {
                    $parts = explode("---", $parcaItem);
                    if (count($parts) < 2) continue;
                    
                    $stokId = $parts[0];
                    $adet = $parts[1];

                    $stok = Stock::find($stokId);
                    if ($stok) {
                        $parcaString .= $stok->urunAdi . " (" . $adet . "), ";
                    }
                }
                $detaylar[$soru->soru] = rtrim($parcaString, ", ");
            }
            else if ($soru->cevap == '[Tarih]' || $cevap->cevapText == '[Tarih]') {
                $tarih = $cevap->cevap;
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tarih)) {
                    $detaylar[$soru->soru] = Carbon::parse($tarih)->format('d/m/Y');
                } else {
                    $detaylar[$soru->soru] = $tarih;
                }
            }
            else {
                $detaylar[$soru->soru] = $cevap->cevap;
            }
        }

        return $detaylar;
    }

    public function myAssignedServiceDetail(Request $request, $id)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        // Servis bugün kendisine atanmış mı kontrol et
        // $atananServisIDleri = $this->getYetkiliServisIDleri($user, $tenant->id);

        // if (!in_array($id, $atananServisIDleri)) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Bu servis bugün size atanmamış veya üzerinde işlem yapmışsınız'
        //     ], 403);
        // }

        $servis = Service::with([
            'asamalar',
            'musteri',
            'markaCihaz',
            'turCihaz',
            'warranty',
        ])
        ->where('firma_id', $tenant->id)
        ->find($id);

        if (!$servis) {
            return response()->json([
                'success' => false,
                'message' => 'Servis bulunamadı'
            ], 404);
        }

        $altAsamalar = [];
        if ($servis->asamalar && $servis->asamalar->altAsamalar) {
            $altAsamaIds = explode(',', $servis->asamalar->altAsamalar);
            $altAsamalar = ServiceStage::whereIn('id', $altAsamaIds)
                ->orderBy('asama')
                ->get()
                ->map(function ($asama) {
                    return [
                        'id' => $asama->id,
                        'asama' => $asama->asama,
                        'asama_renk' => $asama->asama_renk,
                    ];
                });
        }

        $eskiIslemler = ServicePlanning::where('servisid', $id)
        ->orderBy('id', 'desc')
        ->get()
        ->map(function ($planning) use ($tenant) {
            $islemYapan = null;
            if ($planning->pid) {
                $user = User::where('user_id', $planning->pid)->first();
                $islemYapan = $user ? $user->name : null;
            }

            // Aşama adını bulma (gidenIslem)
            $asamaTitle = null;
            $asamaId = null;
            if ($planning->gidenIslem) {
                $asama = ServiceStage::find($planning->gidenIslem);
                $asamaTitle = $asama ? $asama->asama : null;
                $asamaId = $planning->gidenIslem;
            }

            // (planid'ye göre cevaplar)
            $aciklama = $this->getAsamaDetaylari($planning->id, $tenant->id);

            return [
                'id' => (string) $planning->id,
                'pid' => (string) $planning->pid,
                'tarih' => $planning->created_at->format('d/m/Y H:i'),
                'islem_yapan' => $islemYapan,
                'title' => $asamaTitle,
                'asama_id' => (string) $asamaId,
                'aciklama' => $aciklama ?: new \stdClass(),
            ];
        });

        // Garanti hesaplama
        $garantiInfo = null;
        if ($servis->warranty && $servis->warranty->garanti) {
            $garantiBitis = Carbon::parse($servis->created_at)
                ->addMonths($servis->warranty->garanti);
            $kalanGun = Carbon::now()->diffInDays($garantiBitis, false);

            $garantiInfo = [
                'garanti_suresi' => $servis->warranty->garanti . ' ay',
                'garanti_bitis' => $garantiBitis->format('Y-m-d'),
                'kalan_gun' => $kalanGun,
                'garanti_gecerli' => $kalanGun >= 0,
            ];
        }

        // Servis notları
        $servisNotlari = ServiceOptNote::where('servisid', $id)
            ->with('user:user_id,name')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($note) {
                return [
                    'id' => $note->id,
                    'not' => $note->not,
                    'user' => $note->user ? $note->user->name : null,
                    'created_at' => $note->created_at->format('Y-m-d H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'servis' => [
                    'id' => $servis->id,
                    'musteri' => [
                        'id' => $servis->musteri?->id,
                        'ad_soyad' => $servis->musteri?->adSoyad,
                        'tel1' => $servis->musteri?->tel1,
                        'tel2' => $servis->musteri?->tel2,
                        'adres' => $servis->musteri?->adres,
                    ],
                    'cihaz' => [
                        'marka' => $servis->markaCihaz?->marka,
                        'tur' => $servis->turCihaz?->cihaz,
                        'model' => $servis->cihazModel,
                        'seri_no' => $servis->cihazSeriNo,
                        'ariza' => $servis->cihazAriza,
                        'cihaz_sifresi' => $servis->cihazSifresi,
                        'cihaz_deseni' => $servis->cihazDeseni,
                    ],
                    'asama' => [
                        'id' => $servis->asamalar?->id,
                        'asama' => $servis->asamalar?->asama,
                        'renk' => $servis->asamalar?->asama_renk,
                    ],
                    'acil' => $servis->acil != 0 ? true : false,
                    'musait_tarih' => $servis->musaitTarih,
                    'created_at' => $servis->created_at->format('Y-m-d H:i'),
                ],
                'alt_asamalar' => $altAsamalar,
                'eski_islemler' => $eskiIslemler,
                'garanti' => $garantiInfo,
                'notlar' => $servisNotlari,
            ]
        ], 200);
    }

    // Personele atanan depo ürünleri
    public function myStocks(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        $staffStocks = PersonelStock::with(['stok'])
            ->where('firma_id', $tenant->id)
            ->where('pid', $user->user_id)
            ->get();

        if ($staffStocks->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Size atanmış stok bulunmamaktadır',
                'data' => []
            ], 200);
        }

        $data = $staffStocks->map(function ($item) {
            return [
                'id' => $item->stok?->id,
                'urun_adi' => $item->stok?->urunAdi,
                'fiyat' => $item->stok?->fiyat,
                'adet' => $item->adet,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count()
        ], 200);
    }

    // Aşama sorularını detaylı getir (seçeneklerle birlikte)
    public function getStageQuestions(Request $request, $asama_id)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        $sorular = StageQuestion::where('asama', $asama_id)
            ->where(function($q) use ($tenant) {
                $q->whereNull('firma_id')
                ->orWhere('firma_id', $tenant->id);
            })
            ->orderBy('sira', 'asc')
            ->get();

        if ($sorular->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Bu aşamaya ait soru bulunmamaktadır',
                'altAsamalar' => []
            ], 200);
        }

        $altAsamalarArray = [];

        foreach ($sorular as $soru) {
            $inArray = [
                'id' => (string) $soru->id,
                'asama' => (string) $soru->asama,
                'soru' => $soru->soru,
                'cevap' => $soru->cevapTuru,
            ];

            if ($soru->cevapTuru == '[Aciklama]') {
                $inArray['type'] = 'input';
            }
            else if (str_contains($soru->cevapTuru, 'Grup')) {
                $inArray['type'] = 'select';
                $inArray['aciklama'] = $this->getPersonelListByGroup($soru->cevapTuru, $tenant->id);
            }
            else if ($soru->cevapTuru == '[Tarih]') {
                $inArray['type'] = 'datepicker';
                $inArray['aciklama'] = $this->getDefaultDate();
            }
            else if ($soru->cevapTuru == '[Saat]') {
                $inArray['type'] = 'timepicker';
                $inArray['aciklama'] = "08:00-10:00,09:00-11:00,10:00-12:00,11:00-13:00,12:00-14:00,13:00-15:00,14:00-16:00,15:00-17:00,16:00-18:00,17:00-19:00,18:00-20:00,19:00-21:00,20:00-22:00,21:00-23:00";
            }
            else if ($soru->cevapTuru == '[Arac]') {
                $inArray['type'] = 'select';
                $inArray['aciklama'] = $this->getAracList($tenant->id);
            }
            else if ($soru->cevapTuru == '[Fiyat]') {
                $inArray['type'] = 'money';
            }
            else if ($soru->cevapTuru == '[Teklif]') {
                $inArray['type'] = 'money';
            }
            else if ($soru->cevapTuru == '[Parca]') {
                $inArray['type'] = 'checkbox';
                $inArray['aciklama'] = $this->getPersonelStokList($user->user_id, $tenant->id);
            }
            else if ($soru->cevapTuru == '[Konsinye Cihaz]') {
                $inArray['type'] = 'checkbox';
                $inArray['aciklama'] = $this->getKonsinyeCihazList($user->user_id, $tenant->id);
            }
            else if ($soru->cevapTuru == '[Bayi]') {
                $inArray['type'] = 'select';
                $inArray['aciklama'] = $this->getBayiList($tenant->id);
            }

            $altAsamalarArray[] = $inArray;
        }

        return response()->json([
            'altAsamalar' => $altAsamalarArray
        ], 200);
    }

    // Grup numarasına göre personel listesi
    private function getPersonelListByGroup($cevap, $tenantId)
    {
        preg_match_all('/\[Grup-(\d+)\]/', $cevap, $matches);
        $roleIds = $matches[1] ?? [];

        if (empty($roleIds)) {
            return [];
        }

        $personeller = User::where('tenant_id', $tenantId)
            ->where('status', '1')
            ->whereHas('roles', function($query) use ($roleIds) {
                $query->whereIn('roles.id', $roleIds);
            })
            ->orderBy('name', 'asc')
            ->get()
            ->map(function($personel) {
                return [
                    'id' => (string) $personel->user_id,
                    'adsoyad' => $personel->name,
                ];
            })
            ->toArray();

        return $personeller;
    }

    private function getDefaultDate()
    {
        $bugun = date('w');
        $date = ($bugun == 6)
            ? date('Y-m-d', strtotime('+2 days'))
            : date('Y-m-d', strtotime('+1 day'));
        
        return date('d/m/Y', strtotime($date));
    }

    // Araç listesi
    private function getAracList($tenantId)
    {
        $araclar = Car::where('firma_id', $tenantId)
            ->where('durum', '1')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function($arac) {
                return [
                    'id' => (string) $arac->id,
                    'arac' => $arac->arac,
                ];
            })
            ->toArray();

        return $araclar;
    }

    // Personel stok listesi
    private function getPersonelStokList($userId, $tenantId)
    {
        $stoklar = DB::table('personel_stocks as ps')
            ->join('stocks as s', 's.id', '=', 'ps.stokid')
            ->where('ps.pid', $userId)
            ->where('ps.firma_id', $tenantId)
            ->where('ps.adet', '>', 0)
            ->select([
                'ps.id',
                'ps.stokid as stokid',
                'ps.adet',
                's.urunAdi',
                's.urunKodu'
            ])
            ->orderBy('ps.created_at', 'desc')
            ->get()
            ->map(function($stok) {
                return [
                    'id' => (string) $stok->id,
                    'stokid' => (string) $stok->stokid,
                    'adet' => (string) $stok->adet,
                    'urunAdi' => $stok->urunAdi,
                    'urunKodu' => $stok->urunKodu,
                ];
            })
            ->toArray();

        return $stoklar;
    }

    // Konsinye cihaz listesi
    private function getKonsinyeCihazList($userId, $tenantId)
    {
        $cihazlar = DB::table('stocks as s')
            ->join('stock_categories as kategori', 'kategori.id', '=', 's.urunKategori')
            ->where('s.firma_id', $tenantId)
            ->where('kategori.id', 3)
            ->where('s.stokAdedi', '>', 0) //bu kısım için meryem stoklar tablosuna toplam adeti eklemeli
            ->select([
                's.id',
                's.urunAdi',
                's.urunKodu',
                's.stokAdedi as adet'
            ])
            ->orderBy('s.urunAdi', 'asc')
            ->get()
            ->map(function($cihaz) {
                return [
                    'id' => (string) $cihaz->id,
                    'adet' => (string) $cihaz->adet,
                    'urunAdi' => $cihaz->urunAdi,
                    'urunKodu' => $cihaz->urunKodu,
                ];
            })
            ->toArray();

        return $cihazlar;
    }

    // Bayi listesi
    private function getBayiList($tenantId)
    {
        $bayiler = User::where('tenant_id', $tenantId)
            ->where('status', '1')
            ->whereHas('roles', function($query) {
                $query->where('name', 'Bayi');
            })
            ->orderBy('name', 'asc')
            ->get()
            ->map(function($bayi) {
                return [
                    'id' => (string) $bayi->user_id,
                    'adsoyad' => $bayi->name,
                ];
            })
            ->toArray();

        return $bayiler;
    }

    public function saveServicePlan(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'servis_id' => 'required|integer',
            'gelen_islem' => 'required|integer',
            'giden_islem' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz veri',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $servisId = $request->input('servis_id');
            $gelenIslem = $request->input('gelen_islem');
            $gidenIslem = $request->input('giden_islem');

            $servis = Service::where('firma_id', $tenant->id)
                ->where('id', $servisId)
                ->first();

            if (!$servis) {
                return response()->json([
                    'success' => false,
                    'message' => 'Servis bulunamadı veya bu servise erişim yetkiniz yok'
                ], 404);
            }

            $stokHatasi = $this->mobilStokKontrol($request, $gelenIslem, $user->user_id, $tenant->id);
            if ($stokHatasi) {
                return response()->json([
                    'success' => false,
                    'message' => $stokHatasi
                ], 400);
            }

            DB::beginTransaction();

            $planData = [
                'firma_id' => $tenant->id,
                'kid' => $user->user_id,
                'pid' => $user->user_id,
                'servisid' => $servisId,
                'gelenIslem' => $gelenIslem,
                'gidenIslem' => $gidenIslem,
                'tarihDurum' => 0,
                'tarihKontrol' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ];

            $planId = ServicePlanning::insertGetId($planData);

            if (!$planId) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Servis planı oluşturulamadı'
                ], 500);
            }

            Service::where('id', $servisId)->update([
                'servisDurum' => $gidenIslem,
                'planDurum' => $planId,
                'updated_at' => now()
            ]);

            // Soru cevaplarını kaydet
            $this->mobilSoruCevapKaydet($request, $servisId, $planId, $tenant->id, $user->user_id, $gelenIslem);

            // Özel durumları işle
            $this->mobilOzelDurumIsle($request, $servisId, $planId, $tenant->id, $gidenIslem, $servis);

            // Tarih durumu kontrolü
            $this->tarihDurumuKontrolEt($tenant->id);

            DB::commit();

            $stageName = ServiceStage::find($gidenIslem)->asama ?? 'Bilinmeyen Aşama';
            ActivityLogger::logServicePlanAdded($servisId, $planId, $stageName);

            return response()->json([
                'success' => true,
                'message' => 'Servis planı başarıyla kaydedildi',
                'data' => [
                    'plan_id' => $planId,
                    'asama' => $stageName
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mobil servis plan kayıt hatası: ' . $e->getMessage(), [
                'user_id' => $user->user_id,
                'servis_id' => $servisId ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    private function mobilStokKontrol($request, $gelenIslem, $userId, $tenantId)
    {
        if ($request->has('parca')) {
            foreach ($request->input('parca') as $soruId => $parcalar) {
                foreach ($parcalar as $parca) {
                    $stokId = $parca['stok_id'] ?? null;
                    $adet = abs($parca['adet'] ?? 0);

                    if ($stokId && $adet > 0) {
                        $personelStok = PersonelStock::where('pid', $userId)
                            ->where('stokid', $stokId)
                            ->where('firma_id', $tenantId)
                            ->first();

                        if (!$personelStok || $personelStok->adet < $adet) {
                            $stok = Stock::find($stokId);
                            $mevcutAdet = $personelStok ? $personelStok->adet : 0;
                            $urunAdi = $stok ? $stok->urunAdi : "Bilinmeyen Ürün";
                            
                            return "'{$urunAdi}' için personel stoğunuz yetersiz. Mevcut: {$mevcutAdet}, İstenen: {$adet}";
                        }
                    }
                }
            }

            // Parça teslim et aşaması için parça seçimi zorunluluğu
            if ($gelenIslem == "238" && empty($request->input('parca'))) {
                return "Parça Teslim Ederken Stok Seçimi Zorunludur";
            }
        }

        // Konsinye cihaz kontrolü
        if ($request->has('konsinye_cihaz')) {
            foreach ($request->input('konsinye_cihaz') as $soruId => $cihazlar) {
                foreach ($cihazlar as $cihaz) {
                    $cihazId = $cihaz['cihaz_id'] ?? null;
                    $adet = abs($cihaz['adet'] ?? 0);

                    if ($cihazId && $adet > 0) {
                        $girisAdet = StockAction::where('stokId', $cihazId)
                            ->whereIn('islem', [1, 4])
                            ->sum('adet');
                        
                        $cikisAdet = StockAction::where('stokId', $cihazId)
                            ->where('islem', 2)
                            ->sum('adet');

                        $mevcutAdet = $girisAdet - $cikisAdet;

                        if ($adet > $mevcutAdet) {
                            $stok = Stock::find($cihazId);
                            $urunAdi = $stok ? $stok->urunAdi : "Bilinmeyen Cihaz";
                            return "'{$urunAdi}' Konsinye Cihaz Stok Adedi Yetersiz";
                        }
                    }
                }
            }
        }

        return null;
    }

    private function mobilSoruCevapKaydet($request, $servisId, $planId, $tenantId, $userId, $gelenIslem)
    {
        foreach ($request->all() as $key => $value) {
            if (strpos($key, 'soru_') === 0) {
                $soruId = str_replace('soru_', '', $key);
                
                ServiceStageAnswer::create([
                    'firma_id' => $tenantId,
                    'kid' => $userId,
                    'servisid' => $servisId,
                    'planid' => $planId,
                    'soruid' => $soruId,
                    'cevap' => $value,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        if ($request->has('parca')) {
            foreach ($request->input('parca') as $soruId => $parcalar) {
                foreach ($parcalar as $parca) {
                    $stokId = $parca['stok_id'] ?? null;
                    $adet = abs($parca['adet'] ?? 0);

                    if ($stokId && $adet > 0) {
                        $this->mobilParcaIsle($stokId, $adet, $servisId, $planId, $tenantId, $userId, $soruId, $gelenIslem);
                    }
                }
            }
        }

        if ($request->has('konsinye_cihaz')) {
            foreach ($request->input('konsinye_cihaz') as $soruId => $cihazlar) {
                foreach ($cihazlar as $cihaz) {
                    $cihazId = $cihaz['cihaz_id'] ?? null;
                    $adet = abs($cihaz['adet'] ?? 0);

                    if ($cihazId && $adet > 0) {
                        $this->mobilKonsinyeIsle($cihazId, $adet, $servisId, $planId, $tenantId, $userId, $soruId);
                    }
                }
            }
        }
    }

    private function mobilParcaIsle($stokId, $adet, $servisId, $planId, $tenantId, $userId, $soruId, $gelenIslem)
    {
        // Personel stoğundan düş
        $personelStok = PersonelStock::where('pid', $userId)
            ->where('stokid', $stokId)
            ->where('firma_id', $tenantId)
            ->first();

        if ($personelStok) {
            $personelStok->adet -= $adet;
            $personelStok->save();
        }

        StockAction::create([
            'firma_id' => $tenantId,
            'kid' => $userId,
            'pid' => $userId,
            'stokId' => $stokId,
            'islem' => 2, 
            'servisid' => $servisId,
            'adet' => $adet,
            'planId' => $planId,
            'depo' => 2,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $cevapText = $stokId . "---" . $adet;
        
        ServiceStageAnswer::create([
            'firma_id' => $tenantId,
            'kid' => $userId,
            'servisid' => $servisId,
            'planid' => $planId,
            'soruid' => $soruId,
            'cevap' => $cevapText,
            'cevapText' => '[Parca]',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function mobilKonsinyeIsle($cihazId, $adet, $servisId, $planId, $tenantId, $userId, $soruId)
    {
        StockAction::create([
            'firma_id' => $tenantId,
            'kid' => $userId,
            'pid' => $userId,
            'stokId' => $cihazId,
            'islem' => 2, 
            'servisid' => $servisId,
            'adet' => $adet,
            'planId' => $planId,
            'depo' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $cevapText = $cihazId . "---" . $adet;
        
        ServiceStageAnswer::create([
            'firma_id' => $tenantId,
            'kid' => $userId,
            'servisid' => $servisId,
            'planid' => $planId,
            'soruid' => $soruId,
            'cevap' => $cevapText,
            'cevapText' => '[Konsinye Cihaz]',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    
    private function mobilOzelDurumIsle($request, $servisId, $planId, $tenantId, $gidenIslem, $servis)
    {
        // Konsinye cihaz geri alındı (272)
        if ($gidenIslem == 272) {
            $konsinyeCihazlar = StockAction::where('servisid', $servisId)
                ->where('planId', '<>', $planId)
                ->where('islem', 2)
                ->where('firma_id', $tenantId)
                ->get();

            foreach ($konsinyeCihazlar as $cihaz) {
                $stok = Stock::find($cihaz->stokId);
                if ($stok && $stok->urunKategori == 3) {
                    $this->geriAlConsignmentDevice($cihaz->stokId, $cihaz->adet, $servisId, $planId, $tenantId);
                }
            }
        }

        // Parça teslim et (259)
        if ($gidenIslem == "259") {
            $this->parcaTeslimEtOzelDurum($servisId, $planId, $tenantId);
        }

        // Diğer özel durumlar (254)
        if ($gidenIslem == "254") {
            $planlama = ServicePlanning::where('servisid', $servisId)
                ->orderBy('id', 'desc')
                ->skip(1)
                ->first();

            if ($planlama && $planlama->gidenIslem == "255") {
                ServicePlanning::where('id', $planlama->id)->delete();
            }
        }
    }
    
    private function tarihDurumuKontrolEt($tenantId)
    {
        $servisPlanlar = ServicePlanning::where('firma_id', $tenantId)
            ->where('tarihKontrol', '0')
            ->get();

        foreach ($servisPlanlar as $servisRow) {
            $tarihDurum = "0";
            $cevaplar = ServiceStageAnswer::where('firma_id', $tenantId)
                ->where('planid', $servisRow->id)
                ->get();

            foreach ($cevaplar as $cevapRow) {
                $soru = StageQuestion::where('id', $cevapRow->soruid)->first();

                if ($soru && $soru->cevapTuru == "[Tarih]") {
                    $tarihDurum = "1";
                    break;
                }
            }

            ServicePlanning::where('firma_id', $tenantId)
                ->where('id', $servisRow->id)
                ->update([
                    'tarihDurum' => $tarihDurum,
                    'tarihKontrol' => "1",
                    'updated_at' => now()
                ]);
        }

        $cevaplar = ServiceStageAnswer::where('firma_id', $tenantId)
            ->whereNull('cevapText')
            ->get();

        foreach ($cevaplar as $cevapRow) {
            $soru = StageQuestion::where('id', $cevapRow->soruid)->first();

            if ($soru) {
                ServiceStageAnswer::where('firma_id', $tenantId)
                    ->where('id', $cevapRow->id)
                    ->update([
                        'cevapText' => $soru->cevapTuru,
                        'updated_at' => now()
                    ]);
            }
        }
    }

    private function geriAlConsignmentDevice($stokId, $adet, $servisId, $planId, $tenantId, $soruId = null)
    {
        StockAction::create([
            'firma_id' => $tenantId,
            'kid' => auth()->user()->user_id,
            'pid' => auth()->user()->user_id,
            'stokId' => $stokId,
            'islem' => 4,
            'servisid' => $servisId,
            'adet' => $adet,
            'planId' => $planId,
            'depo' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        if ($soruId) {
            $cevapText = $stokId . "---" . $adet;
            
            ServiceStageAnswer::create([
                'firma_id' => $tenantId,
                'servisid' => $servisId,
                'planid' => $planId,
                'soruid' => $soruId,
                'cevap' => $cevapText,
                'cevapText' => '[Konsinye Cihaz]',
                'kid' => auth()->user()->user_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    private function parcaTeslimEtOzelDurum($servisId, $planId, $tenantId)
    {
        $kullanılanParcalar = StockAction::where('servisid', $servisId)
            ->where('islem', 2)
            ->where('firma_id', $tenantId)
            ->get();

        foreach ($kullanılanParcalar as $parca) {
            // Ana stoktan düş
            $stok = Stock::find($parca->stokId);
            if ($stok) {
                $stok->stokAdedi -= $parca->adet;
                $stok->save();
            }

            StockAction::where('id', $parca->id)->update([
                'depo' => 0,
                'updated_at' => now()
            ]);
        }
    }

    public function deleteServicePlan(Request $request, $plan_id)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        try {
            $plan = ServicePlanning::where('firma_id', $tenant->id)
                ->where('id', $plan_id)
                ->first();

            if (!$plan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Servis planı bulunamadı'
                ], 404);
            }

            $servis = Service::where('firma_id', $tenant->id)
                ->where('id', $plan->servisid)
                ->first();

            if (!$servis) {
                return response()->json([
                    'success' => false,
                    'message' => 'Servis bulunamadı'
                ], 404);
            }

            if ($plan->pid != $user->user_id && $plan->kid != $user->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu aşamayı silemezsiniz. Bu işlemi siz yapmadınız.'
                ], 403);
            }

            if ($servis->servisDurum != $plan->gidenIslem || $servis->planDurum != $plan_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Serviste yapılan son işlem size ait olmadığı için bu aşamayı silemezsiniz'
                ], 403);
            }

            DB::beginTransaction();

            // Stok silme işlemi (gidenIslem == 259)
            if ($plan->gidenIslem == 259) {
                $stok_cevap = ServiceStageAnswer::where('firma_id', $tenant->id)
                    ->where('planid', $plan->id)
                    ->first();

                if ($stok_cevap && $stok_cevap->cevap) {
                    $stoklar = explode(', ', $stok_cevap->cevap);

                    foreach ($stoklar as $stokCevap) {
                        if (strpos($stokCevap, '---') !== false) {
                            [$stokID, $adet] = explode('---', $stokCevap);
                            
                            // Ana stoktan geri ekle
                            $stok = Stock::find($stokID);
                            if ($stok) {
                                $stok->stokAdedi += $adet;
                                $stok->save();
                            }
                        }
                    }
                }
            }

            if (in_array($plan->gidenIslem, [267, 268])) {
                $servisPara = ServiceMoneyAction::where('planIslem', $plan_id)->first();
                if ($servisPara) {
                    CashTransaction::where('servisIslem', $servisPara->id)->delete();
                    $servisPara->delete();
                }
            }

            $stokHareketleri = StockAction::where('planId', $plan_id)->get();

            foreach ($stokHareketleri as $stok) {
                $personelStok = PersonelStock::where('pid', $plan->pid)
                    ->where('stokid', $stok->stokId)
                    ->where('firma_id', $tenant->id)
                    ->first();

                if ($personelStok) {
                    $personelStok->increment('adet', $stok->adet);
                } else {
                    PersonelStock::create([
                        'firma_id' => $tenant->id,
                        'kid' => $plan->kid ?? $user->user_id,
                        'pid' => $plan->pid,
                        'stokid' => $stok->stokId,
                        'adet' => $stok->adet,
                        'tarih' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                $stok->delete();
            }

            $stageName = ServiceStage::find($plan->gidenIslem)->asama ?? 'Bilinmeyen Aşama';
            ActivityLogger::logServicePlanDeleted($plan->servisid, $plan_id, $stageName);

            ServiceStageAnswer::where('planid', $plan_id)->delete();

            $plan->delete();

            if ($servis->servisDurum == $plan->gidenIslem || $servis->planDurum == $plan_id) {
                $sonPlan = ServicePlanning::where('servisid', $plan->servisid)
                    ->where('firma_id', $tenant->id)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($sonPlan) {
                    $servis->update([
                        'servisDurum' => $sonPlan->gidenIslem,
                        'planDurum' => $sonPlan->id,
                        'updated_at' => now()
                    ]);
                } else {
                    $ilkAsama = ServiceStage::where('ilkServis', 1)->first();
                    $servis->update([
                        'servisDurum' => $ilkAsama ? $ilkAsama->id : null,
                        'planDurum' => 0,
                        'updated_at' => now()
                    ]);
                }
            }

            DB::commit();

            $servis->refresh();
            
            $altAsamalar = [];
            if ($servis->asamalar && $servis->asamalar->altAsamalar) {
                $altAsamaIDs = explode(',', $servis->asamalar->altAsamalar);
                $altAsamalar = ServiceStage::whereIn('id', $altAsamaIDs)
                    ->orderBy('asama')
                    ->get()
                    ->map(function ($asama) {
                        return [
                            'id' => $asama->id,
                            'asama' => $asama->asama,
                            'asama_renk' => $asama->asama_renk,
                        ];
                    });
            }

            return response()->json([
                'success' => true,
                'message' => 'Servis planı başarıyla silindi',
                'data' => [
                    'asama' => $servis->asamalar->asama ?? null,
                    'altAsamalar' => $altAsamalar
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mobil servis plan silme hatası: ' . $e->getMessage(), [
                'user_id' => $user->user_id,
                'plan_id' => $plan_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Servis planı silinemedi: ' . $e->getMessage()
            ], 500);
        }
    }
   
    public function getServicePlanUpdateForm(Request $request, $plan_id)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        try {
            $servisPlan = ServicePlanning::where('id', $plan_id)
                ->where('firma_id', $tenant->id)
                ->first();

            if (!$servisPlan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Servis planı bulunamadı'
                ], 404);
            }

            $asama = ServiceStage::find($servisPlan->gidenIslem);

            if (!$asama) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aşama bulunamadı'
                ], 404);
            }

            $sorular = StageQuestion::where('asama', $asama->id)
                ->where(function($q) use ($tenant) {
                    $q->whereNull('firma_id')
                    ->orWhere('firma_id', $tenant->id);
                })
                ->orderBy('sira', 'asc')
                ->get();

            $altAsamalarArray = [];

            foreach ($sorular as $soru) {
                $mevcutCevap = ServiceStageAnswer::where('planid', $plan_id)
                    ->where('soruid', $soru->id)
                    ->where('firma_id', $tenant->id)
                    ->first();

                $inArray = [
                    'id' => (string) $soru->id,
                    'asama' => (string) $soru->asama,
                    'soru' => $soru->soru,
                    'cevap' => $soru->cevapTuru,
                    'servisid' => (string) $servisPlan->servisid,
                    'planid' => (string) $plan_id,
                    'cevapid' => $mevcutCevap ? (string) $mevcutCevap->id : null,
                ];

                if ($soru->cevapTuru == '[Aciklama]') {
                    $inArray['type'] = 'input';
                    $inArray['cevapText'] = $mevcutCevap ? $mevcutCevap->cevap : '';
                }
                else if (str_contains($soru->cevapTuru, 'Grup')) {
                    $inArray['type'] = 'select';
                    $inArray['aciklama'] = $this->getPersonelListByGroup($soru->cevapTuru, $tenant->id);
                    $inArray['cevapText'] = $mevcutCevap ? $mevcutCevap->cevap : '';
                }
                else if ($soru->cevapTuru == '[Tarih]') {
                    $inArray['type'] = 'datepicker';
                    $inArray['aciklama'] = $this->getDefaultDate();
                    $inArray['cevapText'] = $mevcutCevap ? $mevcutCevap->cevap : '';
                }
                else if ($soru->cevapTuru == '[Saat]') {
                    $inArray['type'] = 'timepicker';
                    $inArray['aciklama'] = "08:00-10:00,09:00-11:00,10:00-12:00,11:00-13:00,12:00-14:00,13:00-15:00,14:00-16:00,15:00-17:00,16:00-18:00,17:00-19:00,18:00-20:00,19:00-21:00,20:00-22:00,21:00-23:00";
                    $inArray['cevapText'] = $mevcutCevap ? $mevcutCevap->cevap : '';
                }
                else if ($soru->cevapTuru == '[Arac]') {
                    $inArray['type'] = 'select';
                    $inArray['aciklama'] = $this->getAracList($tenant->id);
                    $inArray['cevapText'] = $mevcutCevap ? $mevcutCevap->cevap : '';
                }
                else if ($soru->cevapTuru == '[Fiyat]') {
                    $inArray['type'] = 'money';
                    $inArray['cevapText'] = $mevcutCevap ? $mevcutCevap->cevap : '';
                }
                else if ($soru->cevapTuru == '[Teklif]') {
                    $inArray['type'] = 'money';
                    $inArray['cevapText'] = $mevcutCevap ? $mevcutCevap->cevap : '';
                }
                else if ($soru->cevapTuru == '[Parca]') {
                    $inArray['type'] = 'checkbox';
                    $inArray['aciklama'] = $this->getPersonelStokList($user->user_id, $tenant->id);
                    
                    if ($mevcutCevap && $mevcutCevap->cevap) {
                        $parcaArray = [];
                        $inParcalar = explode(", ", $mevcutCevap->cevap);
                        
                        foreach ($inParcalar as $parca) {
                            if (strpos($parca, '---') !== false) {
                                $parcaSec = explode("---", $parca);
                                $parcaArray[] = [
                                    'id' => (string) $parcaSec[0],
                                    'adet' => (string) $parcaSec[1]
                                ];
                            }
                        }
                        $inArray['cevapText'] = $parcaArray;
                    } else {
                        $inArray['cevapText'] = [];
                    }
                }
                else if ($soru->cevapTuru == '[Konsinye Cihaz]') {
                    $inArray['type'] = 'checkbox';
                    $inArray['aciklama'] = $this->getKonsinyeCihazList($user->user_id, $tenant->id);
                    
                    if ($mevcutCevap && $mevcutCevap->cevap) {
                        $cihazArray = [];
                        $inCihazlar = explode(", ", $mevcutCevap->cevap);
                        
                        foreach ($inCihazlar as $cihaz) {
                            if (strpos($cihaz, '---') !== false) {
                                $cihazSec = explode("---", $cihaz);
                                $cihazArray[] = [
                                    'id' => (string) $cihazSec[0],
                                    'adet' => (string) $cihazSec[1]
                                ];
                            }
                        }
                        $inArray['cevapText'] = $cihazArray;
                    } else {
                        $inArray['cevapText'] = [];
                    }
                }
                else if ($soru->cevapTuru == '[Bayi]') {
                    $inArray['type'] = 'select';
                    $inArray['aciklama'] = $this->getBayiList($tenant->id);
                    $inArray['cevapText'] = $mevcutCevap ? $mevcutCevap->cevap : '';
                }

                $altAsamalarArray[] = $inArray;
            }

            return response()->json([
                'success' => true,
                'altAsamalar' => $altAsamalarArray
            ], 200);

        } catch (\Exception $e) {
            Log::error('Servis plan güncelleme formu hatası: ' . $e->getMessage(), [
                'user_id' => $user->user_id,
                'plan_id' => $plan_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function updateServicePlan(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz veri',
                'errors' => $validator->errors()
            ], 422);
        }

        $planId = $request->input('plan_id');

        try {
            $servisPlan = ServicePlanning::where('id', $planId)
                ->where('firma_id', $tenant->id)
                ->first();

            if (!$servisPlan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Plan bulunamadı'
                ], 404);
            }

            DB::beginTransaction();

            if ($request->has('plan_islemi_yapan')) {
                $yeniPid = $request->input('plan_islemi_yapan');
                
                $yeniPersonel = User::where('user_id', $yeniPid)
                    ->where('tenant_id', $tenant->id)
                    ->where('status', '1')
                    ->first();

                if ($yeniPersonel) {
                    $servisPlan->pid = $yeniPid;
                    $servisPlan->updated_at = now();
                    $servisPlan->save();
                }
            }

            $planCevaplar = ServiceStageAnswer::where('firma_id', $tenant->id)
                ->where('planid', $planId)
                ->get();

            $guncellenenSayisi = 0;

            foreach ($planCevaplar as $cevap) {
                $cevapKey = 'cevap_' . $cevap->id;
                $soruKey = 'soru_' . $cevap->id;

                $yeniCevap = null;

                if ($request->has($cevapKey)) {
                    $yeniCevap = $request->input($cevapKey);
                } else if ($request->has($soruKey)) {
                    $yeniCevap = $request->input($soruKey);
                }

                if ($yeniCevap !== null) {
                    if ($yeniCevap == 'Parca' || $yeniCevap == 'Konsinye Cihaz') {
                        continue;
                    } else {
                        $cevap->cevap = $yeniCevap;
                        $cevap->updated_at = now();
                        $cevap->save();
                        $guncellenenSayisi++;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Plan başarıyla güncellendi',
                'data' => [
                    'servis_id' => $servisPlan->servisid,
                    'plan_id' => $planId,
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mobil servis plan güncelleme hatası: ' . $e->getMessage(), [
                'user_id' => $user->user_id,
                'plan_id' => $planId ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Güncelleme sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateService(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        if ($user->can('Servisleri Göremez')) {
            return response()->json([
                'success' => false,
                'message' => 'Bu işlem için yetkiniz yok'
            ], 403);
        }

        $rules = [
            'servis_id' => 'required|integer',
            'cihaz_model' => $user->can('Tüm Servisleri Görebilir') 
                ? 'nullable|string|max:255'
                : 'required|string|max:255',
        ];

        if ($user->can('Tüm Servisleri Görebilir')) {
            $rules = array_merge($rules, [
                'servis_kaynak' => 'nullable|integer',
                'musait_tarih' => 'nullable|date',
                'musait_saat1' => 'nullable|string|max:10',
                'musait_saat2' => 'nullable|string|max:10',
                'cihaz_marka' => 'nullable|integer',
                'cihaz_tur' => 'nullable|integer',
                'cihaz_seri_no' => 'nullable|string|max:255',
                'cihaz_ariza' => 'nullable|string|max:1000',
                'operator_notu' => 'nullable|string|max:1000',
                'garanti_suresi' => 'nullable|integer',
                'fatura_numarasi' => 'nullable|string|max:255',
                'konsinye' => 'nullable|integer',
                'acil' => 'nullable|in:0,1',
            ]);
        } else {
            $rules['acil'] = 'nullable|in:0,1';
        }

        $validator = Validator::make($request->all(), $rules, [
            'servis_id.required' => 'Servis ID gerekli',
            'cihaz_model.required' => 'Cihaz model bilgisi gerekli',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasyon hatası',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $servisId = $request->input('servis_id');

            $service = Service::where('id', $servisId)
                ->where('firma_id', $tenant->id)
                ->first();

            if (!$service) {
                return response()->json([
                    'success' => false,
                    'message' => 'Servis bulunamadı veya bu servise erişim yetkiniz yok'
                ], 404);
            }

            DB::beginTransaction();

            $data = [
                'kid' => $user->user_id,
                'cihazModel' => strip_tags(trim($request->input('cihaz_model', $service->cihazModel))),
                'acil' => $request->input('acil', '0'),
                'updated_at' => now(),
            ];

            if ($user->can('Tüm Servisleri Görebilir')) {
                $data = array_merge($data, [
                    'servisKaynak' => $request->input('servis_kaynak'),
                    'musaitTarih' => $request->input('musait_tarih'),
                    'musaitSaat1' => $request->input('musait_saat1'),
                    'musaitSaat2' => $request->input('musait_saat2'),
                    'cihazMarka' => $request->input('cihaz_marka', $service->cihazMarka),
                    'cihazTur' => $request->input('cihaz_tur', $service->cihazTur),
                    'cihazSeriNo' => $request->input('cihaz_seri_no'),
                    'cihazAriza' => $request->input('cihaz_ariza'),
                    'operatorNotu' => $request->input('operator_notu'),
                    'garantiSuresi' => $request->input('garanti_suresi'),
                    'faturaNumarasi' => $request->input('fatura_numarasi'),
                    'konsinye' => $request->input('konsinye'),
                ]);
            }

            $service->update($data);

            DB::commit();

            ActivityLogger::logServiceUpdated($servisId);

            $updatedService = Service::with([
                'musteri:id,adSoyad,tel1,tel2,adres,il,ilce',
                'markaCihaz:id,marka',
                'turCihaz:id,cihaz',
                'asamalar:id,asama,asama_renk'
            ])->find($servisId);

            $responseData = [
                'id' => $updatedService->id,
                'musteri' => [
                    'ad_soyad' => $updatedService->musteri?->adSoyad,
                    'tel1' => $updatedService->musteri?->tel1,
                    'tel2' => $updatedService->musteri?->tel2,
                    'adres' => $updatedService->musteri?->adres,
                    'il' => $updatedService->musteri?->country?->name,
                    'ilce' => $updatedService->musteri?->state?->ilceName,
                ],
                'cihaz' => [
                    'marka' => $updatedService->markaCihaz?->marka,
                    'tur' => $updatedService->turCihaz?->cihaz,
                    'model' => $updatedService->cihazModel,
                    'seri_no' => $updatedService->cihazSeriNo,
                    'ariza' => $updatedService->cihazAriza,
                ],
                'asama' => [
                    'id' => $updatedService->asamalar?->id,
                    'asama' => $updatedService->asamalar?->asama,
                    'renk' => $updatedService->asamalar?->asama_renk,
                ],
                'acil' => $updatedService->acil != 0,
                'musait_tarih' => $updatedService->musaitTarih,
                'operator_notu' => $updatedService->operatorNotu,
                'garanti_suresi' => $updatedService->garantiSuresi,
                'fatura_numarasi' => $updatedService->faturaNumarasi,
                'konsinye' => $updatedService->konsinye,
                'created_at' => $updatedService->created_at->format('Y-m-d H:i'),
                'updated_at' => $updatedService->updated_at->format('Y-m-d H:i'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Servis başarıyla güncellendi',
                'data' => $responseData
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mobil servis güncelleme hatası: ' . $e->getMessage(), [
                'user_id' => $user->user_id,
                'servis_id' => $request->input('servis_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Güncelleme sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getServiceNotes(Request $request, $servis_id)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        $servis = Service::where('id', $servis_id)
            ->where('firma_id', $tenant->id)
            ->first();

        if (!$servis) {
            return response()->json([
                'success' => false,
                'message' => 'Servis bulunamadı veya bu servise erişim yetkiniz yok'
            ], 404);
        }

        $notlar = ServiceReceiptNote::where('servisid', $servis_id)
            ->where('firma_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($not) {
                return [
                    'id' =>  $not->id,
                    'kid' => $not->kid,
                    'servisid' => $not->servisid,
                    'aciklama' => $not->aciklama,
                    'kayitTarihi' => Carbon::parse($not->created_at)->format('Y-m-d H:i:s'),
                ];
            })
            ->toArray();

        return response()->json([
            'success' => true,
            'notlar' => $notlar,
        ], 200);
    }

    public function addServiceNote(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'servis_id' => 'required|integer',
            'aciklama' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz veri',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $servisId = $request->input('servis_id');
            $aciklama = $request->input('aciklama');

            $servis = Service::where('id', $servisId)
                ->where('firma_id', $tenant->id)
                ->first();

            if (!$servis) {
                return response()->json([
                    'success' => false,
                    'message' => 'Servis bulunamadı veya bu servise erişim yetkiniz yok'
                ], 404);
            }

            $receiptNote = ServiceReceiptNote::create([
                'firma_id' => $tenant->id,
                'kid' => $user->user_id,
                'servisid' => $servisId,
                'aciklama' => $aciklama,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            ActivityLogger::logServiceNoteAdded($servisId, 'receipt', $receiptNote->id);

            $noteData = [
                'id' => $receiptNote->id,
                'not' => $receiptNote->aciklama,
                'user' => $user->name,
                'created_at' => $receiptNote->created_at->format('Y-m-d H:i'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Servis fiş notu başarıyla eklendi',
                'data' => $noteData
            ], 201);

        } catch (\Exception $e) {
            Log::error('Mobil servis fiş notu ekleme hatası: ' . $e->getMessage(), [
                'user_id' => $user->user_id,
                'servis_id' => $request->input('servis_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Not eklenirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteServiceNote(Request $request, $note_id)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        try {
            $serviceNote = ServiceReceiptNote::where('firma_id', $tenant->id)
                ->where('id', $note_id)
                ->first();

            if (!$serviceNote) {
                return response()->json([
                    'success' => false,
                    'message' => 'Servis fiş notu bulunamadı'
                ], 404);
            }

            
            if ($serviceNote->kid != $user->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu notu silme yetkiniz yok'
                ], 403);
            }

            $servisId = $serviceNote->servisid;
            $noteContent = $serviceNote->aciklama;

            $serviceNote->delete();

            ActivityLogger::log(
                $tenant->id,
                $user->user_id,
                'service_note_deleted',
                "Servis fiş notu silindi",
                [
                    'servis_id' => $servisId,
                    'note_id' => $note_id,
                    'deleted_content' => $noteContent
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Servis fiş notu başarıyla silindi'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Mobil servis fiş notu silme hatası: ' . $e->getMessage(), [
                'user_id' => $user->user_id,
                'note_id' => $note_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Fiş notu silinirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getServicePhotos(Request $request, $servis_id)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        $servis = Service::where('id', $servis_id)
            ->where('firma_id', $tenant->id)
            ->first();

        if (!$servis) {
            return response()->json([
                'success' => false,
                'message' => 'Servis bulunamadı veya bu servise erişim yetkiniz yok'
            ], 404);
        }

        $fotolar = ServicePhoto::where('servisid', $servis_id)
            ->where('firma_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($foto) {
                $baseUrl = config('app.url');
                
                $fullUrl = $baseUrl . '/storage/' . $foto->resimyol;
                
                return [
                    'id' => (string) $foto->id,
                    'servisid' => (string) $foto->servisid,
                    'resimyol' => $fullUrl,
                ];
            })
            ->toArray();

        return response()->json([
            'success' => true,
            'resimler' => $fotolar,
        ], 200);
    }

    
    public function addServicePhoto(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        try {
            $validator = Validator::make($request->all(), [
                'belge' => 'required|file|mimes:jpg,jpeg,png|max:5120', // 5MB = 5120KB
                'servis_id' => 'required|integer'
            ], [
                'belge.required' => 'Lütfen bir dosya seçiniz.',
                'belge.mimes' => 'Sadece JPG, JPEG ve PNG dosyaları yükleyebilirsiniz.',
                'belge.max' => 'Dosya boyutu 5MB\'dan büyük olamaz.',
                'servis_id.required' => 'Servis ID gerekli.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $servisId = $request->input('servis_id');

            $servis = Service::where('id', $servisId)
                ->where('firma_id', $tenant->id)
                ->first();

            if (!$servis) {
                return response()->json([
                    'success' => false,
                    'message' => 'Servis bulunamadı veya bu servise erişim yetkiniz yok'
                ], 404);
            }

            $file = $request->file('belge');
            $fileSize = $file->getSize();

            // Storage limit kontrolü
            if (!$tenant->canUploadFile($fileSize)) {
                $storageInfo = $tenant->getStorageInfo();

                return response()->json([
                    'success' => false,
                    'message' => 'Storage limiti aşıldı! Dosya yükleyemezsiniz.',
                    'error_type' => 'storage_limit_exceeded',
                    'storage_info' => $storageInfo
                ], 422);
            }

            $currentCount = ServicePhoto::where('firma_id', $tenant->id)
                ->where('servisid', $servisId)
                ->count();

            if ($currentCount >= 4) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu servise en fazla 4 fotoğraf yükleyebilirsiniz.'
                ], 422);
            }

            $ext = $file->getClientOriginalExtension();
            $uuid = Str::uuid()->toString() . '.' . $ext;

            $path = "service_photos/firma_{$tenant->firma_slug}/servis_{$servisId}/" . now()->toDateString();
            $fullPath = storage_path('app/public/' . $path);

            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0775, true);
            }

            $image = Image::make($file)->resize(1024, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $image->save($fullPath . '/' . $uuid, 75);

            $storedPath = $path . '/' . $uuid;

            $photo = ServicePhoto::create([
                'firma_id' => $tenant->id,
                'kid' => $user->user_id,
                'servisid' => $servisId,
                'resimyol' => $storedPath,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            

            ActivityLogger::logServicePhotoAdded($servisId, $photo->id);

            $photoUrl = asset('storage/' . $photo->resimyol);

            return response()->json([
                'success' => true,
                'message' => 'Fotoğraf başarıyla yüklendi',
                'data' => [
                    'id' => $photo->id,
                    'servisid' => $photo->servisid,
                    'resimyol' => $photoUrl,
                    'created_at' => $photo->created_at->format('d/m/Y')
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('Mobil servis fotoğraf ekleme hatası: ' . $e->getMessage(), [
                'user_id' => $user->user_id,
                'servis_id' => $request->input('servis_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Dosya yüklenirken bir hata oluştu. Lütfen tekrar deneyiniz.'
            ], 500);
        }
    }

    public function deleteServicePhoto(Request $request, $photo_id)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        try {
            $photo = ServicePhoto::where('firma_id', $tenant->id)
                ->where('id', $photo_id)
                ->first();

            if (!$photo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fotoğraf bulunamadı'
                ], 404);
            }

            
            if ($photo->kid != $user->user_id) {
            
                return response()->json([
                    'success' => false,
                    'message' => 'Bu fotoğrafı silme yetkiniz yok'
                ], 403);
            }

            if (Storage::disk('public')->exists($photo->resimyol)) {
                Storage::disk('public')->delete($photo->resimyol);
            }

            $servisId = $photo->servisid;

            $photo->delete();

            ActivityLogger::logServicePhotoDeleted($servisId, $photo_id);

            return response()->json([
                'success' => true,
                'message' => 'Fotoğraf başarıyla silindi'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Mobil servis fotoğraf silme hatası: ' . $e->getMessage(), [
                'user_id' => $user->user_id,
                'photo_id' => $photo_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Fotoğraf silinirken bir hata oluştu.'
            ], 500);
        }
    }

    public function getPaymentMethods(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        $odemeSekilleri = PaymentMethod::where(function($query) use ($tenant) {
                $query->whereNull('firma_id')
                    ->orWhere('firma_id', $tenant->id);
            })
            ->orderBy('odemeSekli', 'asc')
            ->get()
            ->map(function($odeme) {
                return [
                    'id' => (string) $odeme->id,
                    'odemeSekli' => $odeme->odemeSekli,
                ];
            })
            ->toArray();

        return response()->json([
            'success' => true,
            'odemeSekilleri' => $odemeSekilleri,
        ], 200);
    }

    public function getServicePayments(Request $request, $servis_id)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        $servis = Service::where('id', $servis_id)
            ->where('firma_id', $tenant->id)
            ->first();

        if (!$servis) {
            return response()->json([
                'success' => false,
                'message' => 'Servis bulunamadı veya bu servise erişim yetkiniz yok'
            ], 404);
        }

        $paraHareketleri = DB::table('service_money_actions as so')
            ->leftJoin('payment_methods as os', 'os.id', '=', 'so.odemeSekli')
            ->leftJoin('tb_user as u', 'u.user_id', '=', 'so.pid')
            ->where('so.servisid', $servis_id)
            ->where('so.firma_id', $tenant->id)
            ->select([
                'so.id',
                'so.servisid',
                'so.odemeSekli',
                'os.odemeSekli as sekli',
                'so.odemeDurum',
                'so.fiyat',
                'so.aciklama',
                'so.odemeYonu',
                'so.created_at',
                'u.name as adsoyad'
            ])
            ->orderBy('so.created_at', 'desc')
            ->get()
            ->map(function($odeme) {
                return [
                    'id' => (string) $odeme->id,
                    'servisid' => (string) $odeme->servisid,
                    'tarih' => Carbon::parse($odeme->created_at)->format('d/m/Y H:i'),
                    'odemeSekli' => (string) $odeme->odemeSekli,
                    'sekli' => $odeme->sekli ?? 'Belirtilmemiş',
                    'odemeDurum' => $odeme->odemeDurum ?? '',
                    'fiyat' => number_format($odeme->fiyat, 2, '.', ''),
                    'aciklama' => $odeme->aciklama ?? '',
                    'odemeYonu' => (string) $odeme->odemeYonu,
                    'adsoyad' => $odeme->adsoyad ?? 'Bilinmiyor',
                ];
            })
            ->toArray();

        $toplam = ServiceMoneyAction::where('servisid', $servis_id)
            ->where('firma_id', $tenant->id)
            ->sum('fiyat');

        return response()->json([
            'success' => true,
            'toplam' => number_format($toplam, 2, '.', ''),
            'para_hareketleri' => $paraHareketleri,
        ], 200);
    }

    public function addServiceIncome(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        $rules = [
            'servis_id' => 'required|integer',
            'odeme_sekli' => 'required|integer',
            'odeme_durum' => 'required|in:1,2',
            'fiyat' => 'required|numeric|min:0',
            'aciklama' => 'nullable|string|max:255',
        ];

        if ($user->hasRole('Patron')) {
            $rules['tarih'] = 'nullable|date';
            $rules['personel_id'] = 'nullable|integer';
        }

        $validator = Validator::make($request->all(), $rules, [
            'servis_id.required' => 'Servis ID gerekli',
            'odeme_sekli.required' => 'Ödeme şekli gerekli',
            'odeme_durum.required' => 'Ödeme durumu gerekli',
            'fiyat.required' => 'Fiyat gerekli',
            'fiyat.min' => 'Fiyat sıfırdan küçük olamaz',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasyon hatası',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $servisId = $request->input('servis_id');
            $fiyat = str_replace(",", ".", trim($request->input('fiyat')));

            $servis = Service::where('id', $servisId)
                ->where('firma_id', $tenant->id)
                ->first();

            if (!$servis) {
                return response()->json([
                    'success' => false,
                    'message' => 'Servis bulunamadı veya bu servise erişim yetkiniz yok'
                ], 404);
            }

            $tarih = now();
            if ($user->hasRole('Patron') && $request->has('tarih')) {
                $tarih = Carbon::parse($request->input('tarih') . ' ' . now()->format('H:i:s'));
            }

            $personelId = $user->user_id;
            if ($user->hasRole('Patron') && $request->has('personel_id')) {
                $personelId = $request->input('personel_id');
                
                $personelKontrol = User::where('user_id', $personelId)
                    ->where('tenant_id', $tenant->id)
                    ->where('status', '1')
                    ->first();

                if (!$personelKontrol) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Geçersiz personel seçimi'
                    ], 422);
                }
            }

            DB::beginTransaction();

            $serviceMoneyAction = ServiceMoneyAction::create([
                'firma_id' => $tenant->id,
                'kid' => $user->user_id,
                'pid' => $personelId,
                'servisid' => $servisId,
                'odemeSekli' => $request->input('odeme_sekli'),
                'odemeDurum' => $request->input('odeme_durum'),
                'fiyat' => $fiyat,
                'aciklama' => $request->input('aciklama'),
                'odemeYonu' => 1,
                'created_at' => $tarih,
                'updated_at' => $tarih
            ]);

            $kasaData = [
                'firma_id' => $tenant->id,
                'kid' => $user->user_id,
                'pid' => $personelId,
                'personel' => $personelId,
                'odemeYonu' => 1,
                'odemeSekli' => $request->input('odeme_sekli'),
                'odemeDurum' => $request->input('odeme_durum'),
                'fiyat' => $fiyat,
                'fiyatBirim' => 1,
                'aciklama' => $request->input('aciklama'),
                'marka' => $servis->cihazMarka,
                'cihaz' => $servis->cihazTur,
                'servis' => $servisId,
                'servisIslem' => $serviceMoneyAction->id,
                'created_at' => $tarih,
                'updated_at' => $tarih
            ];

            $odemeTuru = PaymentType::where('servis', 1)->first();
            if ($odemeTuru) {
                $kasaData['odemeTuru'] = $odemeTuru->id;
            }

            CashTransaction::create($kasaData);

            DB::commit();

            ActivityLogger::logServiceMoneyAdded($servisId, $fiyat, 1, $request->input('aciklama'));

            return response()->json([
                'success' => true,
                'message' => 'Gelir başarıyla eklendi',
                'data' => [
                    'id' => $serviceMoneyAction->id,
                    'servis_id' => $servisId,
                    'fiyat' => number_format($fiyat, 2, '.', ''),
                    'odeme_yonu' => 'Gelir'
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mobil servis gelir ekleme hatası: ' . $e->getMessage(), [
                'user_id' => $user->user_id,
                'servis_id' => $request->input('servis_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gelir eklenirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    
    public function addServiceExpense(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        $rules = [
            'servis_id' => 'required|integer',
            'odeme_sekli' => 'required|integer',
            'odeme_durum' => 'required|in:1,2',
            'fiyat' => 'required|numeric|min:0',
            'aciklama' => 'nullable|string|max:255',
        ];

        if ($user->hasRole('Patron')) {
            $rules['tarih'] = 'nullable|date';
            $rules['personel_id'] = 'nullable|integer';
        }

        $validator = Validator::make($request->all(), $rules, [
            'servis_id.required' => 'Servis ID gerekli',
            'odeme_sekli.required' => 'Ödeme şekli gerekli',
            'odeme_durum.required' => 'Ödeme durumu gerekli',
            'fiyat.required' => 'Fiyat gerekli',
            'fiyat.min' => 'Fiyat sıfırdan küçük olamaz',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasyon hatası',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $servisId = $request->input('servis_id');
            $fiyat = str_replace(",", ".", trim($request->input('fiyat')));

            $servis = Service::where('id', $servisId)
                ->where('firma_id', $tenant->id)
                ->first();

            if (!$servis) {
                return response()->json([
                    'success' => false,
                    'message' => 'Servis bulunamadı veya bu servise erişim yetkiniz yok'
                ], 404);
            }

            $tarih = now();
            if ($user->hasRole('Patron') && $request->has('tarih')) {
                $tarih = Carbon::parse($request->input('tarih') . ' ' . now()->format('H:i:s'));
            }

            $personelId = $user->user_id;
            if ($user->hasRole('Patron') && $request->has('personel_id')) {
                $personelId = $request->input('personel_id');
                
                $personelKontrol = User::where('user_id', $personelId)
                    ->where('tenant_id', $tenant->id)
                    ->where('status', '1')
                    ->first();

                if (!$personelKontrol) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Geçersiz personel seçimi'
                    ], 422);
                }
            }

            DB::beginTransaction();

            $serviceMoneyAction = ServiceMoneyAction::create([
                'firma_id' => $tenant->id,
                'kid' => $user->user_id,
                'pid' => $personelId,
                'servisid' => $servisId,
                'odemeSekli' => $request->input('odeme_sekli'),
                'odemeDurum' => $request->input('odeme_durum'),
                'fiyat' => $fiyat,
                'aciklama' => $request->input('aciklama'),
                'odemeYonu' => 2, 
                'created_at' => $tarih,
                'updated_at' => $tarih
            ]);

            $kasaData = [
                'firma_id' => $tenant->id,
                'kid' => $user->user_id,
                'pid' => $personelId,
                'personel' => $personelId,
                'odemeYonu' => 2, 
                'odemeSekli' => $request->input('odeme_sekli'),
                'odemeDurum' => $request->input('odeme_durum'),
                'fiyat' => $fiyat,
                'fiyatBirim' => 1,
                'aciklama' => $request->input('aciklama'),
                'marka' => $servis->cihazMarka,
                'cihaz' => $servis->cihazTur,
                'servis' => $servisId,
                'servisIslem' => $serviceMoneyAction->id,
                'created_at' => $tarih,
                'updated_at' => $tarih
            ];

            $odemeTuru = PaymentType::where('servis', 1)->first();
            if ($odemeTuru) {
                $kasaData['odemeTuru'] = $odemeTuru->id;
            }

            CashTransaction::create($kasaData);

            DB::commit();

            ActivityLogger::logServiceMoneyAdded($servisId, $fiyat, 2, $request->input('aciklama'));

            return response()->json([
                'success' => true,
                'message' => 'Gider başarıyla eklendi',
                'data' => [
                    'id' => $serviceMoneyAction->id,
                    'servis_id' => $servisId,
                    'fiyat' => number_format($fiyat, 2, '.', ''),
                    'odeme_yonu' => 'Gider'
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mobil servis gider ekleme hatası: ' . $e->getMessage(), [
                'user_id' => $user->user_id,
                'servis_id' => $request->input('servis_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gider eklenirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    // Cihaz Markaları
    public function getDeviceBrands(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        $markalar = DeviceBrand::where(function($query) use ($tenant) {
                $query->whereNull('firma_id') 
                    ->orWhere('firma_id', $tenant->id); 
            })
            ->select([
                'id',
                'firma_id as kid',
                'marka',
                'aciklama',
                'servisUcreti as ucret',
                'operatorPrim as optPrim',
                'atolyePrim as atyPrim'
            ])
            ->orderBy('marka', 'asc')
            ->get()
            ->map(function($marka) {
                return [
                    'id' => (string) $marka->id,
                    'kid' => (string) ($marka->kid ?? ''),
                    'marka' => $marka->marka,
                    'aciklama' => $marka->aciklama ?? '',
                    'ucret' => $marka->ucret,
                    'optPrim' => number_format($marka->optPrim, 2, '.', ''),
                    'atyPrim' => number_format($marka->atyPrim, 2, '.', ''),
                ];
            })
            ->toArray();

        return response()->json([
            'success' => true,
            'markalar' => $markalar,
        ], 200);
    }

    //  Cihaz Türleri
    public function getDeviceTypes(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        $turler = DeviceType::where(function($query) use ($tenant) {
                $query->whereNull('firma_id')
                    ->orWhere('firma_id', $tenant->id);
            })
            ->select([
                'id',
                'firma_id as kid',
                'cihaz',
                'operatorPrim as optPrim',
                'atolyePrim as atyPrim'
            ])
            ->orderBy('cihaz', 'asc')
            ->get()
            ->map(function($tur) {
                return [
                    'id' => (string) $tur->id,
                    'kid' => (string) ($tur->kid ?? ''),
                    'cihaz' => $tur->cihaz,
                    'optPrim' => number_format($tur->optPrim, 2, '.', ''),
                    'atyPrim' => number_format($tur->atyPrim, 2, '.', ''),
                ];
            })
            ->toArray();

        return response()->json([
            'success' => true,
            'turler' => $turler,
        ], 200);
    }

    public function getBrands(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        try {
            $markalar = DB::table('markalar')
                ->select([
                    'id',
                    'marka',
                    'resimyol'
                ])
                ->orderBy('marka', 'asc')
                ->get()
                ->map(function($marka) {
                    $resimUrl = null;
                    if (!empty($marka->resimyol)) {
                        $resimUrl = asset('public/upload/ariza_kodlari/' . $marka->resimyol);
                        
                    }

                    return [
                        'id' => (string) $marka->id,
                        'marka' => $marka->marka,
                        'resimyol' => $resimUrl ?? $marka->resimyol
                    ];
                })
                ->toArray();

            return response()->json([
                'success' => true,
                'markalar' => $markalar,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Mobil marka listesi hatası: ' . $e->getMessage(), [
                'user_id' => $user->user_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Markalar getirilirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getModels(Request $request, $marka_id)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        try {
            $marka = DB::table('markalar')
                ->where('id', $marka_id)
                ->first();

            if (!$marka) {
                return response()->json([
                    'success' => false,
                    'message' => 'Marka bulunamadı'
                ], 404);
            }

            $modeller = DB::table('modeller')
                ->where('mid', $marka_id)
                ->select([
                    'id',
                    'mid',
                    'model',
                    'resimyol'
                ])
                ->orderBy('model', 'asc')
                ->get()
                ->map(function($model) {
                    $resimUrl = null;
                    if (!empty($model->resimyol)) {
                        $resimUrl = asset('public/upload/ariza_kodlari' . $model->resimyol);
                       
                    }

                    return [
                        'id' => (string) $model->id,
                        'marka_id' => (string) $model->mid,
                        'model' => $model->model,
                        'resimyol' => $resimUrl ?? $model->resimyol
                    ];
                })
                ->toArray();

            return response()->json([
                'success' => true,
                'marka' => [
                    'id' => (string) $marka->id,
                    'marka' => $marka->marka
                ],
                'modeller' => $modeller,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Mobil model listesi hatası: ' . $e->getMessage(), [
                'user_id' => $user->user_id,
                'marka_id' => $marka_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Modeller getirilirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getFaultCodesByBrand(Request $request, $marka_id)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        try {
            $marka = DB::table('markalar')
                ->where('id', $marka_id)
                ->first();

            if (!$marka) {
                return response()->json([
                    'success' => false,
                    'message' => 'Marka bulunamadı'
                ], 404);
            }

            $arizaKodlari = ArizaKodu::where('marka_id', $marka_id)
                ->select([
                    'id',
                    'marka_id',
                    'model_id',
                    'kodu',
                    'baslik',
                    'aciklama'
                ])
                ->orderBy('kodu', 'asc')
                ->get()
                ->map(function($ariza) {
                    return [
                        'id' => (string) $ariza->id,
                        'marka_id' => (string) $ariza->marka_id,
                        'model_id' => $ariza->model_id ? (string) $ariza->model_id : null,
                        'kod' => $ariza->kodu,
                        'baslik' => $ariza->baslik,
                        'aciklama' => $ariza->aciklama
                    ];
                })
                ->toArray();

            return response()->json([
                'success' => true,
                'marka' => [
                    'id' => (string) $marka->id,
                    'marka' => $marka->marka
                ],
                'ariza_kodlari' => $arizaKodlari,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Mobil arıza kodları (marka) hatası: ' . $e->getMessage(), [
                'user_id' => $user->user_id,
                'marka_id' => $marka_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Arıza kodları getirilirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getFaultCodesByModel(Request $request, $marka_id, $model_id)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        try {
            $marka = DB::table('markalar')
                ->where('id', $marka_id)
                ->first();

            if (!$marka) {
                return response()->json([
                    'success' => false,
                    'message' => 'Marka bulunamadı'
                ], 404);
            }

            $model = DB::table('modeller')
                ->where('id', $model_id)
                ->where('mid', $marka_id)
                ->first();

            if (!$model) {
                return response()->json([
                    'success' => false,
                    'message' => 'Model bulunamadı'
                ], 404);
            }

            $arizaKodlari = DB::table('ariza_kodlari')
                ->where('marka_id', $marka_id)
                ->where(function($query) use ($model_id) {
                    $query->where('model_id', $model_id)
                        ->orWhereNull('model_id');
                })
                ->select([
                    'id',
                    'marka_id',
                    'model_id',
                    'kodu',
                    'baslik',
                    'aciklama'
                ])
                ->orderBy('kodu', 'asc')
                ->get()
                ->map(function($ariza) {
                    return [
                        'id' => (string) $ariza->id,
                        'marka_id' => (string) $ariza->marka_id,
                        'model_id' => $ariza->model_id ? (string) $ariza->model_id : null,
                        'kod' => $ariza->kodu,
                        'baslik' => $ariza->baslik,
                        'aciklama' => $ariza->aciklama,
                    ];
                })
                ->toArray();

            return response()->json([
                'success' => true,
                'marka' => [
                    'id' => (string) $marka->id,
                    'marka' => $marka->marka
                ],
                'model' => [
                    'id' => (string) $model->id,
                    'model' => $model->model
                ],
                'ariza_kodlari' => $arizaKodlari,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Mobil arıza kodları (model) hatası: ' . $e->getMessage(), [
                'user_id' => $user->user_id,
                'marka_id' => $marka_id,
                'model_id' => $model_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Arıza kodları getirilirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getFaultCodesByCode(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        try {
            $searchKod = $kod ?? $request->query('kod');
            
            if (empty($searchKod)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arıza kodu belirtilmedi'
                ], 422);
            }

            $query = DB::table('ariza_kodlari')
                ->where('kodu', 'LIKE', '%' . $searchKod . '%');

            if ($request->has('marka_id')) {
                $query->where('marka_id', $request->query('marka_id'));
            }

            if ($request->has('model_id')) {
                $query->where(function($q) use ($request) {
                    $q->where('model_id', $request->query('model_id'))
                    ->orWhereNull('model_id');
                });
            }

            $arizaKodlari = $query->select([
                    'ariza_kodlari.id',
                    'ariza_kodlari.marka_id',
                    'ariza_kodlari.model_id',
                    'ariza_kodlari.kodu',
                    'ariza_kodlari.baslik',
                    'ariza_kodlari.aciklama'
                ])
                ->orderBy('ariza_kodlari.kodu', 'asc')
                ->get()
                ->map(function($ariza) {
                    $marka = DB::table('markalar')->where('id', $ariza->marka_id)->first();
                    
                    $model = null;
                    if ($ariza->model_id) {
                        $model = DB::table('modeller')->where('id', $ariza->model_id)->first();
                    }

                    return [
                        'id' => (string) $ariza->id,
                        'marka' => $marka ? [
                            'id' => (string) $marka->id,
                            'marka' => $marka->marka
                        ] : null,
                        'model' => $model ? [
                            'id' => (string) $model->id,
                            'model' => $model->model
                        ] : null,
                        'kod' => $ariza->kodu,
                        'baslik' => $ariza->baslik,
                        'aciklama' => $ariza->aciklama,
                    ];
                })
                ->toArray();

            return response()->json([
                'success' => true,
                'arama' => $searchKod,
                'ariza_kodlari' => $arizaKodlari,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Mobil arıza kodları (kod arama) hatası: ' . $e->getMessage(), [
                'user_id' => $user->user_id,
                'kod' => $searchKod ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Arıza kodları aranırken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    //Primlerimi hesaplama
    public function calculateMyBonus(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        if (!$user->hasAnyRole(['Teknisyen', 'Teknisyen Yardımcısı', 'Operatör', 'Atölye Ustası', 'Atölye Çırak'])) {
            return response()->json([
                'success' => false,
                'message' => 'Prim görüntüleme yetkiniz bulunmamaktadır'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'baslangic_tarihi' => 'required|date',
            'bitis_tarihi' => 'required|date|after_or_equal:baslangic_tarihi',
        ], [
            'baslangic_tarihi.required' => 'Başlangıç tarihi gerekli',
            'bitis_tarihi.required' => 'Bitiş tarihi gerekli',
            'bitis_tarihi.after_or_equal' => 'Bitiş tarihi başlangıç tarihinden önce olamaz',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasyon hatası',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $tarih1 = Carbon::createFromFormat('Y-m-d', $request->input('baslangic_tarihi'))->startOfDay();
            $tarih2 = Carbon::createFromFormat('Y-m-d', $request->input('bitis_tarihi'))->endOfDay();

            // Role göre prim hesapla
            $sonuclar = null;
            $rol = $user->getRoleNames()->first();

            if ($user->hasAnyRole(['Teknisyen', 'Teknisyen Yardımcısı'])) {
                $sonuclar = $this->mobilTeknisyenPrimHesapla($user->user_id, $tarih1, $tarih2, $tenant->id);
            } elseif ($user->hasAnyRole(['Atölye Ustası', 'Atölye Çırak'])) {
                $sonuclar = $this->mobilAtolyeUstasiPrimHesapla($user->user_id, $tarih1, $tarih2, $tenant->id);
            } elseif ($user->hasAnyRole(['Operatör'])) {
                $sonuclar = $this->mobilOperatorPrimHesapla($user->user_id, $tarih1, $tarih2, $tenant->id);
            }

            if (isset($sonuclar['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $sonuclar['error']
                ], 400);
            }

            $toplamPrim = array_sum(array_column($sonuclar, 'prim_tutari'));
            $toplamGun = count($sonuclar);

            return response()->json([
                'success' => true,
                'data' => [
                    'personel' => [
                        'id' => $user->user_id,
                        'ad_soyad' => $user->name,
                        'rol' => $rol
                    ],
                    'tarih_araligi' => [
                        'baslangic' => $request->input('baslangic_tarihi'),
                        'bitis' => $request->input('bitis_tarihi')
                    ],
                    'toplam_prim' => number_format($toplamPrim, 2, '.', ''),
                    'toplam_gun' => $toplamGun,
                    'gunluk_detaylar' => $sonuclar
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Mobil prim hesaplama hatası: ' . $e->getMessage(), [
                'user_id' => $user->user_id,
                'tarih1' => $request->input('baslangic_tarihi'),
                'tarih2' => $request->input('bitis_tarihi')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Prim hesaplama sırasında bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    private function mobilTeknisyenPrimHesapla($personelId, $tarih1, $tarih2, $tenantId)
    {
        $primAyarlari = DB::table('tenant_prims')
            ->where('firma_id', $tenantId)
            ->first();

        if (!$primAyarlari) {
            return ['error' => 'Prim ayarları bulunamadı'];
        }

        $gunlukSinir = $primAyarlari->teknisyenPrimTutari; // Günlük tutar sınırı
        $primOrani = $primAyarlari->teknisyenPrim; // Prim oranı (%)

        $gunler = [];
        $currentDate = $tarih1->copy();
        
        while ($currentDate <= $tarih2) {
            $gunler[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

        $primSonuclari = [];

        foreach ($gunler as $gun) {
            $gunBaslangic = Carbon::createFromFormat('Y-m-d', $gun)->startOfDay();
            $gunBitis = Carbon::createFromFormat('Y-m-d', $gun)->endOfDay();

            $bitenServisIds = DB::table('service_plannings')
                ->select('servisid')
                ->where('pid', $personelId)
                ->where('firma_id', $tenantId)
                ->whereBetween('created_at', [$gunBaslangic, $gunBitis])
                ->groupBy('servisid')
                ->pluck('servisid');

            if ($bitenServisIds->isEmpty()) {
                continue;
            }

            $musteriIptalIds = DB::table('service_plannings')
                ->whereIn('servisid', $bitenServisIds)
                ->where('gidenIslem', 244) // Müşteri iptal etti
                ->where('firma_id', $tenantId)
                ->pluck('servisid')
                ->toArray();

            // Cihaz tamir edilemiyor servisleri filtrele
            $cihazTamirEdilemiyorIds = DB::table('service_plannings')
                ->whereIn('servisid', $bitenServisIds)
                ->where('gidenIslem', 246) // Cihaz tamir edilemiyor
                ->where('firma_id', $tenantId)
                ->pluck('servisid')
                ->toArray();

            $gecersizIds = array_unique(array_merge($musteriIptalIds, $cihazTamirEdilemiyorIds));
            $validServisIds = $bitenServisIds->diff($gecersizIds);

            if ($validServisIds->isEmpty()) {
                continue;
            }

            $planlama = DB::table('service_plannings')
                ->whereBetween('created_at', [$gunBaslangic, $gunBitis])
                ->whereIn('servisid', $validServisIds)
                ->where('pid', $personelId)
                ->where('firma_id', $tenantId)
                ->pluck('id')
                ->toArray();

            if (empty($planlama)) {
                continue;
            }

            $gunlukTeklifToplami = DB::table('service_stage_answers')
                ->join('stage_questions', 'service_stage_answers.soruid', '=', 'stage_questions.id')
                ->whereIn('service_stage_answers.planid', $planlama)
                ->where('stage_questions.cevapTuru', '[Teklif]')
                ->where('service_stage_answers.cevap', '>', 0)
                ->where('service_stage_answers.firma_id', $tenantId)
                ->whereBetween('service_stage_answers.created_at', [$gunBaslangic, $gunBitis])
                ->sum('service_stage_answers.cevap');

            // Günlük sınırı aştı mı
            if ($gunlukTeklifToplami >= $gunlukSinir) {
                $primTutari = ($gunlukTeklifToplami * $primOrani) / 100;
                
                $primSonuclari[] = [
                    'tarih' => $gun,
                    'teklif_toplami' => number_format($gunlukTeklifToplami, 2, '.', ''),
                    'prim_tutari' => number_format($primTutari, 2, '.', ''),
                    'prim_orani' => $primOrani,
                    'gunluk_sinir' => number_format($gunlukSinir, 2, '.', ''),
                    'servis_sayisi' => $validServisIds->count()
                ];
            }
        }

        return $primSonuclari;
    }

    private function mobilAtolyeUstasiPrimHesapla($personelId, $tarih1, $tarih2, $tenantId)
    {
        $primAyarlari = DB::table('tenant_prims')
            ->where('firma_id', $tenantId)
            ->first();

        if (!$primAyarlari) {
            return ['error' => 'Prim ayarları bulunamadı'];
        }

        $gunlukSinir = $primAyarlari->atolyePrimTutari; // Günlük tamamlanan servis sayısı
        $primOrani = $primAyarlari->atolyePrim; // Prim oranı

        $gunler = [];
        $currentDate = $tarih1->copy();
        
        while ($currentDate <= $tarih2) {
            $gunler[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

        $primSonuclari = [];

        foreach ($gunler as $gun) {
            $gunBaslangic = Carbon::createFromFormat('Y-m-d', $gun)->startOfDay();
            $gunBitis = Carbon::createFromFormat('Y-m-d', $gun)->endOfDay();

            $bitenServisIds = DB::table('service_plannings')
                ->select('servisid')
                ->where('pid', $personelId)
                ->where('firma_id', $tenantId)
                ->whereBetween('created_at', [$gunBaslangic, $gunBitis])
                ->groupBy('servisid')
                ->pluck('servisid');

            if ($bitenServisIds->isEmpty()) {
                continue;
            }

            $musteriIptalIds = DB::table('service_plannings')
                ->whereIn('servisid', $bitenServisIds)
                ->where('gidenIslem', 244)
                ->where('firma_id', $tenantId)
                ->pluck('servisid')
                ->toArray();

            $cihazTamirEdilemiyorIds = DB::table('service_plannings')
                ->whereIn('servisid', $bitenServisIds)
                ->where('gidenIslem', 246)
                ->where('firma_id', $tenantId)
                ->pluck('servisid')
                ->toArray();

            $gecersizIds = array_unique(array_merge($musteriIptalIds, $cihazTamirEdilemiyorIds));
            $validServisIds = $bitenServisIds->diff($gecersizIds);

            if ($validServisIds->isEmpty()) {
                continue;
            }

            // O gün teslimata hazır (252) işlemini yapanları say
            $gunlukTamamlananlar = DB::table('service_plannings')
                ->whereBetween('created_at', [$gunBaslangic, $gunBitis])
                ->whereIn('servisid', $validServisIds)
                ->where('pid', $personelId)
                ->where('gidenIslem', 252)
                ->where('firma_id', $tenantId)
                ->groupBy('servisid')
                ->pluck('servisid');

            $gunlukTamamlanmaSayisi = $gunlukTamamlananlar->count();

            if ($gunlukTamamlanmaSayisi >= $gunlukSinir) {
                $primTutari = $gunlukTamamlanmaSayisi * $primOrani;
                
                $primSonuclari[] = [
                    'tarih' => $gun,
                    'tamamlanan_sayisi' => $gunlukTamamlanmaSayisi,
                    'prim_tutari' => number_format($primTutari, 2, '.', ''),
                    'prim_orani' => $primOrani,
                    'gunluk_sinir' => $gunlukSinir
                ];
            }
        }

        return $primSonuclari;
    }

    private function mobilOperatorPrimHesapla($personelId, $tarih1, $tarih2, $tenantId)
    {
        $primAyarlari = DB::table('tenant_prims')
            ->where('firma_id', $tenantId)
            ->first();

        if (!$primAyarlari) {
            return ['error' => 'Prim ayarları bulunamadı'];
        }

        $gunlukSinir = $primAyarlari->operatorPrimTutari; // Günlük servis sayısı
        $primOrani = $primAyarlari->operatorPrim; // Prim oranı

        $gunler = [];
        $currentDate = $tarih1->copy();
        
        while ($currentDate <= $tarih2) {
            $gunler[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

        $primSonuclari = [];

        foreach ($gunler as $gun) {
            $gunBaslangic = Carbon::createFromFormat('Y-m-d', $gun)->startOfDay();
            $gunBitis = Carbon::createFromFormat('Y-m-d', $gun)->endOfDay();

            // O günkü kayıt aldığı servisleri getir
            $bitenServisIds = DB::table('services')
                ->select('id')
                ->where('kayitAlan', $personelId)
                ->where('firma_id', $tenantId)
                ->whereBetween('created_at', [$gunBaslangic, $gunBitis])
                ->pluck('id');

            if ($bitenServisIds->isEmpty()) {
                continue;
            }

            // İptal edilmiş servisleri filtrele
            $iptalEdilenServisIds = DB::table('service_plannings')
                ->whereIn('servisid', $bitenServisIds)
                ->where('gidenIslem', 244)
                ->where('firma_id', $tenantId)
                ->pluck('servisid')
                ->toArray();

            $validServisIds = $bitenServisIds->diff($iptalEdilenServisIds);
            $gunlukServisSayisi = $validServisIds->count();

            if ($gunlukServisSayisi >= $gunlukSinir) {
                $primTutari = $gunlukServisSayisi * $primOrani;
                
                $primSonuclari[] = [
                    'tarih' => $gun,
                    'servis_sayisi' => $gunlukServisSayisi,
                    'prim_tutari' => number_format($primTutari, 2, '.', ''),
                    'prim_orani' => $primOrani,
                    'gunluk_sinir' => $gunlukSinir
                ];
            }
        }

        return $primSonuclari;
    }

    //FİŞYAZDIRMA İŞLEMLERİ ENDPOİNTİ
    public function printServiceReceipt(Request $request, $servis_id)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }

        try {
            $servis = Service::with([
                'musteri',
                'markaCihaz',
                'turCihaz',
                'warranty',
                'asamalar'
            ])
            ->where('id', $servis_id)
            ->where('firma_id', $tenant->id)
            ->first();

            if (!$servis) {
                return response()->json([
                    'success' => false,
                    'message' => 'Servis bulunamadı'
                ], 404);
            }

            // Fiş şablonu
            $fisTemplate = ReceiptDesign::where('firma_id', $tenant->id)
                ->first();

            if (!$fisTemplate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fiş şablonu bulunamadı. Lütfen yöneticinizle iletişime geçin.'
                ], 404);
            }

            // Fiş içeriğini oluşturma kısmı
            $fisIcerigi = $this->olusturFisIcerigi($servis, $tenant, $user, $fisTemplate);

            return response()->json(
                $fisIcerigi,
                200,
                ['Content-Type' => 'application/json; charset=utf-8'],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        } catch (\Exception $e) {
            Log::error('Mobil servis fiş yazdırma hatası: ' . $e->getMessage(), [
                'user_id' => $user->user_id,
                'servis_id' => $servis_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Fiş oluşturulurken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    //Fiş içeriğini oluşturma fonksiyonu   
    private function olusturFisIcerigi($servis, $tenant, $user, $fisTemplate)
    {
        $yapilanIslemler = $this->getYapilanIslemler($servis->id, $servis->servisDurum, $tenant->id);
        
        $kasaHareketleri = $this->getKasaHareketleri($servis->id, $tenant->id);
        
        $fisNotlari = $this->getFisNotlari($servis->id, $tenant->id);

        $musteriBilgileri = $servis->musteri->adSoyad . "\r\n" .
                        $servis->musteri->tel1 . " " . ($servis->musteri->tel2 ?? '') . "\r\n" .
                        $servis->musteri->adres . "\r\n" .
                        $servis->musteri->state->ilceName . " / " . $servis->musteri->country->name;

        $cihazBilgileri = ($servis->markaCihaz->marka ?? '') . " " . ($servis->turCihaz->cihaz ?? '') . "\r\n" .
                        ($servis->cihazAriza ?? '');

        $yasalUyari = ServiceFormSetting::where('firma_id', $tenant->id)
            ->value('mesaj') ?? '';

        $isBayi = $user->hasRole('Bayi');
        
        if ($isBayi) {
            $firmaAdi = $user->firma_adi ?? $user->name;
            $tel1 = $user->tel ?? '';
            $tel2= $user->tel ?? '';
            $adres = ($user->address ?? '') . " " . ($user->state->ilceName ?? '') . "/" . ($user->country->name ?? '');
            $vergiNo = $user->vergiNo ?? '';
            $vergiDairesi = $user->vergiDairesi ?? '';
        } else {
            $firmaAdi = $tenant->firma_adi ?? '';
            $tel1 = $tenant->tel1 ?? '';
            $tel2 = $tenant->tel2 ?? '';
            $adres = ($tenant->adres ?? '') . " " . ($tenant->ilces->ilceName ?? '') . "/" . ($tenant->ils->name ?? '');
            $vergiNo = $tenant->vergiNo ?? '';
            $vergiDairesi = $tenant->vergiDairesi ?? '';
        }

        $search = [
            '[FIRMAADI]', 
            '[TEL]', 
            '[TEL2]', 
            '[ADRES]',
            '[FVERGINO]',
            '[FVERGIDAIRESI]',
            '[SNO]', 
            '[MID]',
            '[MUSTERIBILGILERI]', 
            '[CIHAZBILGILERI]',  
            '[YAPILANISLEMLER]',
            '[KASAHAREKETLERI]',
            '[TEKNISYENADI]',
            '[TARIHSAAT]',
            '[MUSTERIADI]',
            '[FISNOTLARI]',
        ];

        $replace = [
            $firmaAdi,
            $tel1,
            $tel2,
            $adres,
            $vergiNo,
            $vergiDairesi,
            $servis->id,
            $servis->musteri_id,
            $musteriBilgileri,
            $cihazBilgileri,
            $yapilanIslemler,
            $kasaHareketleri,
            $user->name,
            now()->format('d/m/Y H:i'),
            $servis->musteri->adSoyad,
            $fisNotlari,
        ];

        $mesaj = str_replace($search, $replace, $fisTemplate->fisTasarimi);

        if (!empty($yasalUyari)) {
            $mesaj .= "\r\n\r\nONEMLI UYARILAR\r\n\r\n";
            $mesaj .= $yasalUyari;
        }

        $mesaj = mb_strtoupper($mesaj, 'UTF-8');

        $turkish = ['İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç', 'ı', 'ğ', 'ü', 'ş', 'ö', 'ç'];
        $english = ['I', 'G', 'U', 'S', 'O', 'C', 'i', 'g', 'u', 's', 'o', 'c'];
        $mesaj = str_replace($turkish, $english, $mesaj);

        return $mesaj;
    }

    private function getYapilanIslemler($servisId, $servisDurum, $tenantId)
    {
        $islemler = "";
        
        if ($servisDurum == 239) { 
            $planlar = ServicePlanning::where('servisid', $servisId)
                ->where('firma_id', $tenantId)
                ->orderBy('id', 'desc')
                ->limit(2)
                ->get();
        } else {
            $planlar = ServicePlanning::where('servisid', $servisId)
                ->where('firma_id', $tenantId)
                ->orderBy('id', 'desc')
                ->limit(1)
                ->get();
        }

        $i = 0;
        foreach ($planlar as $plan) {
            $i++;
            
            $asama = ServiceStage::find($plan->gidenIslem);
            $islemler .= '- ' . ($asama->asama ?? 'Bilinmeyen Aşama') . ' -' . "\r\n";

            $cevaplar = ServiceStageAnswer::where('planid', $plan->id)
                ->where('firma_id', $tenantId)
                ->get();

            foreach ($cevaplar as $cevap) {
                if (empty($cevap->cevap)) continue;

                $soru = StageQuestion::find($cevap->soruid);
                if (!$soru) continue;

                if (str_contains($soru->cevapTuru, 'Grup')) {
                    $personel = User::where('user_id', $cevap->cevap)->first();
                    $islemler .= $soru->soru . ': ' . ($personel->name ?? 'Personel #' . $cevap->cevap) . "\r\n";
                }
                else if ($soru->cevapTuru == '[Arac]') {
                    $arac = Car::find($cevap->cevap);
                    $islemler .= $soru->soru . ': ' . ($arac->arac ?? $cevap->cevap) . "\r\n";
                }
                else if ($soru->cevapTuru == '[Parca]') {
                    $islemler .= $soru->soru . ': ';
                    $parcalar = explode(", ", $cevap->cevap);
                    
                    foreach ($parcalar as $parca) {
                        if (strpos($parca, '---') !== false) {
                            $parts = explode("---", $parca);
                            $stokId = $parts[0];
                            $adet = $parts[1];
                            
                            $stok = Stock::find($stokId);
                            if ($stok) {
                                $islemler .= $stok->urunAdi . " (" . $adet . "), ";
                            }
                        }
                    }
                    $islemler .= "\r\n";
                }
                else if ($soru->cevapTuru == '[Tarih]' || $cevap->cevapText == '[Tarih]') {
                    $tarih = $cevap->cevap;
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tarih)) {
                        $tarih = Carbon::parse($tarih)->format('d/m/Y');
                    }
                    $islemler .= $soru->soru . ': ' . $tarih . "\r\n";
                }
                else {
                    $islemler .= $soru->soru . ': ' . $cevap->cevap . "\r\n";
                }
            }

            if ($i < count($planlar)) {
                $islemler .= "\r\n";
            }
        }

        return $islemler;
    }

    private function getKasaHareketleri($servisId, $tenantId)
    {
        $hareketler = "";
        
        $paraHareketleri = ServiceMoneyAction::where('servisid', $servisId)
            ->where('firma_id', $tenantId)
            ->where('odemeYonu', 1) // Sadece gelirler
            ->orderBy('id', 'desc')
            ->get();

        foreach ($paraHareketleri as $para) {
            $odemeDurum = "";
            if ($para->odemeDurum == "0" || $para->odemeDurum == "2") {
                $odemeDurum = ' - BEKLEMEDE';
            } else if ($para->odemeDurum == "1") {
                $odemeDurum = ' - TAMAMLANDI';
            }

            $hareketler .= number_format($para->fiyat, 2, '.', '.') . ' TL' . $odemeDurum . "\r\n";
        }

        return $hareketler;
    }

    private function getFisNotlari($servisId, $tenantId)
    {
        $notlar = "";
        
        $fisNotlari = ServiceReceiptNote::where('servisid', $servisId)
            ->where('firma_id', $tenantId)
            ->orderBy('id', 'desc')
            ->get();

        foreach ($fisNotlari as $not) {
            $notlar .= $not->aciklama . "\r\n";
        }

        return $notlar;
    }
}
