<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PrimController extends Controller
{
    public function hesaplaPrim($data)
    {
        $user = auth()->user();
        $personelId = $data['personel_id'];
        $tarih1 = Carbon::createFromFormat('Y-m-d', $data['tarih1prim'])->startOfDay();
        $tarih2 = Carbon::createFromFormat('Y-m-d', $data['tarih2prim'])->endOfDay();

        // Personel bilgilerini getir
        $personel = User::where('user_id', $personelId)->first();
        
        if (!$personel) {
            return ['error' => 'Personel bulunamadı'];
        }

        $tenantId = $personel->tenant_id;

        // Grup kontrolü ve prim hesaplama
        if ($personel->hasAnyRole(['Operatör'])) {
            return $this->operatorPrimHesapla($personelId, $tarih1, $tarih2, $tenantId);
        }

        if ($personel->hasAnyRole(['Atölye Ustası', 'Atölye Çırak'])) {
            return $this->atolyeUstasiPrimHesapla($personelId, $tarih1, $tarih2, $tenantId);
        }

        // Varsayılan: Teknisyen
        if ($personel->hasAnyRole(['Teknisyen', 'Teknisyen Yardımcısı'])) {
            return $this->teknisyenPrimHesapla($personelId, $tarih1, $tarih2, $tenantId);
        }

        return ['error' => 'Geçersiz rol'];
    }

    /**
     * Operator prim hesaplama - Aldığı iş sayısına göre
     */
    private function operatorPrimHesapla($personelId, $tarih1, $tarih2, $tenantId) {
    // Operator için prim ayarlarını getir
    $primAyarlari = DB::table('tenant_prims')
        ->where('firma_id', $tenantId)
        ->first();
    
    if (!$primAyarlari) {
        return ['error' => 'Operator için prim ayarları bulunamadı'];
    }
    
    $gunlukSinir = $primAyarlari->operatorPrimTutari; // Günlük kaç servis
    $primOrani = $primAyarlari->operatorPrim; // Prim oranı (%)
    
    // Tarih aralığındaki günleri al
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
        
        // O günkü biten servisleri getir (gidenIslem=255)
        // DÜZELTME 1: DISTINCT servis ID'leri al
        $bitenServisIds = DB::table('service_plannings')
            ->join('services', 'services.id', '=', 'service_plannings.servisid')
            ->select('service_plannings.servisid')  // ->where('service_plannings.gidenIslem', 255)
            ->whereBetween('service_plannings.created_at', [$gunBaslangic, $gunBitis])
            ->where('services.kayitAlan', $personelId)
            ->groupBy('service_plannings.servisid') // Duplicate'leri önle
            ->pluck('servisid');
        
        // DÜZELTME 2: İptal edilmiş servisleri toplu olarak filtrele
        $iptalEdilenServisIds = DB::table('service_plannings')
            ->whereIn('servisid', $bitenServisIds)
            ->where('gidenIslem', 244)
            ->pluck('servisid')
            ->toArray();
        
        // DÜZELTME 3: Valid servisleri hesapla
        $validServisIds = $bitenServisIds->diff($iptalEdilenServisIds);
        $gunlukServisSayisi = $validServisIds->count();
        
        // Debug için detaylı bilgi
        $validServisler = [];
        if ($gunlukServisSayisi > 0) {
            $validServisler = DB::table('service_plannings')
                ->join('services', 'services.id', '=', 'service_plannings.servisid')
                ->select('service_plannings.servisid', 'service_plannings.created_at', 
                         'service_plannings.gidenIslem', 'services.servisDurum', 
                         'services.kayitAlan', 'services.id')
                ->whereIn('service_plannings.servisid', $validServisIds)  //->where('service_plannings.gidenIslem', 255)
                ->whereBetween('service_plannings.created_at', [$gunBaslangic, $gunBitis])
                ->where('services.kayitAlan', $personelId)
                ->get();
        }
        
        // Günlük sınırı aştı mı?
        if ($gunlukServisSayisi >= $gunlukSinir) {
            $primTutari = $gunlukServisSayisi * $primOrani;
            $primSonuclari[] = [
                'tarih' => $gun,
                'servis_sayisi' => $gunlukServisSayisi,
                'prim_tutari' => $primTutari,
                'prim_orani' => $primOrani,
                'gunluk_sinir' => $gunlukSinir,
                'servisler' => $validServisler,
                // Debug bilgileri
                'debug' => [
                    'toplam_biten' => $bitenServisIds->count(),
                    'iptal_edilen' => count($iptalEdilenServisIds),
                    'valid_servis_ids' => $validServisIds->toArray()
                ]
            ];
        }
    }
    
    return $primSonuclari;
}

    /**
     * Teknisyen prim hesaplama - Günlük teklif toplamına göre
     */
    private function teknisyenPrimHesapla($personelId, $tarih1, $tarih2, $tenantId)
    {
        // Teknisyen için prim ayarlarını getir
        $primAyarlari = DB::table('tenant_prims')
            ->where('firma_id', $tenantId)
            ->first();

        if (!$primAyarlari) {
            return ['error' => 'Teknisyen için prim ayarları bulunamadı'];
        }

        $gunlukSinir = $primAyarlari->teknisyenPrimTutari; // Günlük tutar sınırı (örn: 10000 TL)
        $primOrani = $primAyarlari->teknisyenPrim; // Prim oranı (%)

        // Tarih aralığındaki günleri al
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

            // DÜZELTME 1: Personelin o gün yaptığı servisleri getir - DISTINCT
            $bitenServisIds = DB::table('service_plannings')
                ->join('services', 'services.id', '=', 'service_plannings.servisid')
                ->select('service_plannings.servisid')
                ->where('service_plannings.pid', $personelId)
                ->groupBy('service_plannings.servisid')
                ->pluck('servisid');

            if ($bitenServisIds->isEmpty()) {
                continue;
            }

            // DÜZELTME 2: İptal edilmiş servisleri toplu kontrol
            $musteriIptalIds = DB::table('service_plannings')
                ->whereIn('servisid', $bitenServisIds)
                ->where('gidenIslem', 244)
                ->pluck('servisid')
                ->toArray();

            // DÜZELTME 3: Cihaz tamir edilemiyor servisleri toplu kontrol
            $cihazTamirEdilemiyorIds = DB::table('service_plannings')
                ->whereIn('servisid', $bitenServisIds)
                ->where('gidenIslem', 246)
                ->pluck('servisid')
                ->toArray();

            // DÜZELTME 4: Valid servisleri hesapla
            $gecersizIds = array_unique(array_merge($musteriIptalIds, $cihazTamirEdilemiyorIds));
            $validServisIds = $bitenServisIds->diff($gecersizIds);

            if ($validServisIds->isEmpty()) {
                continue;
            }

            // DÜZELTME 5: O günkü planlamaları getir - tarih kontrolü eklendi
            $planlama = DB::table('service_plannings')
                ->whereBetween('created_at', [$gunBaslangic, $gunBitis])
                ->whereIn('servisid', $validServisIds)
                ->where('pid', $personelId)
                ->pluck('id')
                ->toArray();

            if (empty($planlama)) {
                continue;
            }

            // O günkü teklif cevaplarını getir
            $gunlukTeklifToplami = DB::table('service_stage_answers')
                ->join('stage_questions', 'service_stage_answers.soruid', '=', 'stage_questions.id')
                ->whereIn('service_stage_answers.planid', $planlama)
                ->where('stage_questions.cevapTuru', '[Teklif]')
                ->where('service_stage_answers.cevap', '>', 0)
                ->whereBetween('service_stage_answers.created_at', [$gunBaslangic, $gunBitis])
                ->sum('service_stage_answers.cevap');

            // Günlük sınırı aştı mı?
            if ($gunlukTeklifToplami >= $gunlukSinir) {
                $primTutari = ($gunlukTeklifToplami * $primOrani) / 100;
                
                $primSonuclari[] = [
                    'tarih' => $gun,
                    'teklif_toplami' => $gunlukTeklifToplami,
                    'prim_tutari' => $primTutari,
                    'prim_orani' => $primOrani,
                    'gunluk_sinir' => $gunlukSinir,
                    'servis_sayisi' => $validServisIds->count(),
                    // Debug bilgileri
                    'debug' => [
                        'toplam_servis' => $bitenServisIds->count(),
                        'iptal_edilen' => count($musteriIptalIds),
                        'tamir_edilemiyor' => count($cihazTamirEdilemiyorIds),
                        'valid_servis_ids' => $validServisIds->toArray(),
                        'planlama_sayisi' => count($planlama)
                    ]
                ];
            }
        }

        return $primSonuclari;
    }

    /**
     * Atölye ustası prim hesaplama
     */
    private function atolyeUstasiPrimHesapla($personelId, $tarih1, $tarih2, $tenantId)
    {
        // Atölye ustası için prim ayarlarını getir
        $primAyarlari = DB::table('tenant_prims')
            ->where('firma_id', $tenantId)
            ->first();

        if (!$primAyarlari) {
            return ['error' => 'Atölye ustası için prim ayarları bulunamadı'];
        }

        $gunlukSinir = $primAyarlari->atolyePrimTutari; // Günlük tamamlanan servis sayısı
        $primOrani = $primAyarlari->atolyePrim; // Prim oranı

        // Tarih aralığındaki günleri al
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

            // DÜZELTME 1: Personelin işlem yaptığı servisleri getir - DISTINCT
            $bitenServisIds = DB::table('service_plannings')
                ->join('services', 'services.id', '=', 'service_plannings.servisid')
                ->select('service_plannings.servisid')
                ->where('service_plannings.pid', $personelId)
                ->groupBy('service_plannings.servisid')
                ->pluck('servisid');

            if ($bitenServisIds->isEmpty()) {
                continue;
            }

            // DÜZELTME 2: İptal edilmiş servisleri toplu kontrol
            $musteriIptalIds = DB::table('service_plannings')
                ->whereIn('servisid', $bitenServisIds)
                ->where('gidenIslem', 244)
                ->pluck('servisid')
                ->toArray();

            // DÜZELTME 3: Cihaz tamir edilemiyor servisleri toplu kontrol
            $cihazTamirEdilemiyorIds = DB::table('service_plannings')
                ->whereIn('servisid', $bitenServisIds)
                ->where('gidenIslem', 246)
                ->pluck('servisid')
                ->toArray();

            // DÜZELTME 4: Valid servisleri hesapla
            $gecersizIds = array_unique(array_merge($musteriIptalIds, $cihazTamirEdilemiyorIds));
            $validServisIds = $bitenServisIds->diff($gecersizIds);

            if ($validServisIds->isEmpty()) {
                continue;
            }

            // DÜZELTME 5: O gün teslimata hazır işlemini seçenler - tarih ve duplicate kontrolü
            $gunlukTamamlananlar = DB::table('service_plannings')
                ->whereBetween('created_at', [$gunBaslangic, $gunBitis])
                ->whereIn('servisid', $validServisIds)
                ->where('pid', $personelId)
                ->where('gidenIslem', '252')
                ->groupBy('service_plannings.servisid')
                ->pluck('servisid');

            $gunlukTamamlanmaSayisi = $gunlukTamamlananlar->count();

            // Günlük sınırı aştı mı?
            if ($gunlukTamamlanmaSayisi >= $gunlukSinir) {
                $primTutari = $gunlukTamamlanmaSayisi * $primOrani;
                
                $primSonuclari[] = [
                    'tarih' => $gun,
                    'tamamlanan_sayisi' => $gunlukTamamlanmaSayisi,
                    'prim_tutari' => $primTutari,
                    'prim_orani' => $primOrani,
                    'gunluk_sinir' => $gunlukSinir,
                    // Debug bilgileri
                    'debug' => [
                        'toplam_servis' => $bitenServisIds->count(),
                        'iptal_edilen' => count($musteriIptalIds),
                        'tamir_edilemiyor' => count($cihazTamirEdilemiyorIds),
                        'valid_servis_ids' => $validServisIds->toArray(),
                        'tamamlanan_servis_ids' => $gunlukTamamlananlar->pluck('servisid')->toArray()
                    ]
                ];
            }
        }

        return $primSonuclari;
    }
    public function index($tenant_id)
    {
        $personeller = User::with('roles')
            ->where('tenant_id', $tenant_id)
            ->where('status', 1)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', ['Bayi', 'Depocu', 'Patron','Admin','Super Admin']);
            })
            ->orderBy('name')
            ->get();
        
        $firma = Tenant::where('id', $tenant_id)->first();
        
        // Prim ayarlarını getir
        $primAyarlari = DB::table('tenant_prims')
            ->where('firma_id', $tenant_id)
            ->get();
        
        return view('frontend.secure.prim.index', compact('personeller', 'firma', 'primAyarlari'));
    }

    /**
     * Prim hesaplama işlemi
     */
    public function hesapla(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'personel_id' => 'required|exists:tb_user,user_id',
            
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->only(['personel_id', 'tarih1prim', 'tarih2prim']);
            $sonuclar = $this->hesaplaPrim($data);

            if (isset($sonuclar['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $sonuclar['error']
                ], 400);
            }

            // Personel bilgisini getir
            $personel = User::where('user_id', $request->personel_id)->first();
            $grup = $personel?->getRoleNames()?->first();

            // Toplam prim hesapla
            $toplamPrim = array_sum(array_column($sonuclar, 'prim_tutari'));

            return response()->json([
                'success' => true,
                'data' => [
                    'sonuclar' => $sonuclar,
                    'personel' => $personel,
                    'grup' => $grup,
                    'tarih_araligi' => [
                        'baslangic' => $request->tarih1prim,
                        'bitis' => $request->tarih2prim
                    ],
                    'toplam_kayit' => count($sonuclar),
                    'toplam_prim' => $toplamPrim
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Prim hesaplama hatası: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Prim hesaplama sırasında bir hata oluştu.'
            ], 500);
        }
    }
    
    /**
     * Prim detayları
     */
    public function detay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'personel_id' => 'required|exists:tb_user,user_id',
            'tarih' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $personelId = $request->personel_id;
            $tarih = $request->tarih;
            
            // O günkü prim detaylarını getir
            $detaylar = $this->getGunlukPrimDetay($personelId, $tarih);

            return response()->json([
                'success' => true,
                'data' => $detaylar
            ]);

        } catch (\Exception $e) {
            \Log::error('Prim detay hatası: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Prim detayı getirilirken hata oluştu.'
            ], 500);
        }
    }

    private function getGunlukPrimDetay($personelId, $tarih)
    {
        $gunBaslangic = Carbon::createFromFormat('Y-m-d', $tarih)->startOfDay();
        $gunBitis = Carbon::createFromFormat('Y-m-d', $tarih)->endOfDay();

        $personel = User::where('user_id', $personelId)->first();
        $rol = $personel->getRoleNames()->first();

        $detay = [
            'tarih' => $tarih,
            'personel' => $personel,
            'rol' => $rol,
            'islemler' => []
        ];

        // Role göre detayları getir
        switch ($rol) {
            case 'Teknisyen':
                $detay['islemler'] = $this->getTeknisenGunlukDetay($personelId, $gunBaslangic, $gunBitis);
                break;
            case 'Teknisyen Yardımcısı':
                $detay['islemler'] = $this->getTeknisenGunlukDetay($personelId, $gunBaslangic, $gunBitis);
                break;
            case 'Operatör':
                $detay['islemler'] = $this->getOperatorGunlukDetay($personelId, $gunBaslangic, $gunBitis);
                break;
            case 'Atölye Ustası':
                $detay['islemler'] = $this->getAtolyeUstasiGunlukDetay($personelId, $gunBaslangic, $gunBitis);
                break;
            case 'Atölye Çırak':
                $detay['islemler'] = $this->getAtolyeUstasiGunlukDetay($personelId, $gunBaslangic, $gunBitis);
                break;
        }

        return $detay;
    }

    private function getTeknisenGunlukDetay($personelId, $gunBaslangic, $gunBitis)
    {
        return DB::table('service_stage_answers')
            ->join('stage_questions', 'service_stage_answers.soruid', '=', 'stage_questions.id')
            ->join('service_plannings', 'service_stage_answers.planid', '=', 'service_plannings.id')
            ->join('services', 'service_plannings.servisid', '=', 'services.id')
            ->join('customers', 'services.musteri_id', '=', 'customers.id')
            ->select(
                'service_stage_answers.*',
                'services.id as servis_id',
                'customers.adSoyad as musteri_adi'
            )
            ->where('service_plannings.pid', $personelId)
            ->where('stage_questions.cevapTuru', '[Teklif]')
            ->whereBetween('service_stage_answers.created_at', [$gunBaslangic, $gunBitis])
            ->get();
    }

    private function getOperatorGunlukDetay($personelId, $gunBaslangic, $gunBitis)
    {
        return DB::table('services')
            ->join('customers', 'services.musteri_id', '=', 'customers.id')
            ->select('services.*', 'customers.adSoyad as musteri_adi')
            ->where('services.kayitAlan', $personelId)
            ->whereBetween('services.created_at', [$gunBaslangic, $gunBitis])
            ->get();
    }

    private function getAtolyeUstasiGunlukDetay($personelId, $gunBaslangic, $gunBitis)
    {
        return DB::table('service_plannings')
            ->join('services', 'service_plannings.servisid', '=', 'services.id')
            ->join('customers', 'services.musteri_id', '=', 'customers.id')
            ->select(
                'service_plannings.*',
                'services.id as servis_id',
                'customers.adSoyad as musteri_adi'
            )
            ->where('service_plannings.pid', $personelId)
            ->where('service_plannings.gidenIslem', '252')
            ->whereBetween('service_plannings.created_at', [$gunBaslangic, $gunBitis])
            ->get();
    }

     public function kullaniciPrimSayfasi()
    {
        $user = Auth::user(); // Giriş yapmış kullanıcıyı al
        $tenant_id = $user->tenant_id;

        // Kullanıcının rolüne göre prim hesaplama yetkisi var mı kontrol et
        if (!$user->hasAnyRole(['Teknisyen', 'Teknisyen Yardımcısı', 'Operatör', 'Atölye Ustası', 'Atölye Çırak'])) {
            // Yetkisi yoksa anasayfaya veya bir hata sayfasına yönlendir
            return redirect()->route('home')->with('error', 'Prim görüntüleme yetkiniz bulunmamaktadır.');
        }

        $firma = Tenant::where('id', $tenant_id)->first();

        // Prim ayarlarını getir
        $primAyarlari = DB::table('tenant_prims')
            ->where('firma_id', $tenant_id)
            ->get();

        // Kullanıcının göreceği view'ı döndür
        return view('frontend.secure.prim.kullanici_prim', compact('user', 'firma', 'primAyarlari'));
    }

    /**
     * Giriş yapmış kullanıcı için prim hesaplama işlemi.
     */
    public function kullaniciHesapla(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tarih1prim' => 'required|date',
            'tarih2prim' => 'required|date|after_or_equal:tarih1prim',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = Auth::user(); // Giriş yapmış kullanıcıyı al

        try {
            // hesaplaPrim metoduna gönderilecek data hazırlanıyor.
            // Personel seçimi yerine giriş yapmış kullanıcının ID'si kullanılıyor.
            $data = [
                'personel_id' => $user->user_id,
                'tarih1prim'  => $request->tarih1prim,
                'tarih2prim'  => $request->tarih2prim,
            ];

            $sonuclar = $this->hesaplaPrim($data); // Mevcut hesaplama fonksiyonunu yeniden kullanıyoruz.

            if (isset($sonuclar['error'])) {
                return response()->json(['success' => false, 'message' => $sonuclar['error']], 400);
            }

            $grup = $user->getRoleNames()->first();
            $toplamPrim = array_sum(array_column($sonuclar, 'prim_tutari'));

            return response()->json([
                'success' => true,
                'data' => [
                    'sonuclar'     => $sonuclar,
                    'personel'     => $user,
                    'grup'         => $grup,
                    'tarih_araligi'=> [
                        'baslangic' => $request->tarih1prim,
                        'bitis'     => $request->tarih2prim
                    ],
                    'toplam_kayit' => count($sonuclar),
                    'toplam_prim'  => $toplamPrim
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Kullanıcı prim hesaplama hatası: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Prim hesaplama sırasında bir hata oluştu.'], 500);
        }
    }

    /**
     * Giriş yapmış kullanıcının günlük prim detaylarını getirir.
     */
    public function kullaniciDetay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tarih' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $user = Auth::user();
            $tarih = $request->tarih;

            // Mevcut detay getirme fonksiyonunu kullanıyoruz.
            // personel_id'yi dışarıdan almak yerine giriş yapmış kullanıcıdan alıyoruz.
            $detaylar = $this->getGunlukPrimDetay($user->user_id, $tarih);

            return response()->json([
                'success' => true,
                'data' => $detaylar
            ]);

        } catch (\Exception $e) {
            \Log::error('Kullanıcı prim detay hatası: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Prim detayı getirilirken hata oluştu.'], 500);
        }
    }
}