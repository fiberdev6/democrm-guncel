<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\DeviceBrand;
use App\Models\DeviceType;
use App\Models\EmergencyService;
use App\Models\Il;
use App\Models\Ilce;
use App\Models\PaymentMethod;
use App\Models\PaymentType;
use App\Models\Service;
use App\Models\ServiceFormSetting;
use App\Models\ServiceMoneyAction;
use App\Models\ServiceOptNote;
use App\Models\ServicePhoto;
use App\Models\ServicePlanning;
use App\Models\ServiceResource;
use App\Models\ServiceStage;
use App\Models\ServiceStageAnswer;
use App\Models\StageQuestion;
use App\Models\Stock;
use App\Models\StockAction;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WarrantyPeriod;
use App\Models\ServiceReceiptNote;
use App\Models\Offer;
use App\Models\Invoice;
use App\Models\PersonelStock;
use App\Models\ServiceTime;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;
use Image;
use App\Models\IncomingCall;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Cache;
use App\Models\IntegrationPurchase;
use App\Models\ReceiptDesign;
use App\Services\HipcallService;
use App\Services\InvoiceIntegrationFactory;

class ServicesController extends Controller
{
    public function AllServices($tenant_id, Request $request)
    {
        $firma = Tenant::where('id', $tenant_id)->first();
        if (!$firma) {
            $notification = [
                'message' => 'Firma bulunamadı',
                'alert-type' => 'danger',
            ];
            return redirect()->route('giris')->with($notification);
        }
        //$services         = Service::where('firma_id', $firma->id)->get();
        $isBeyazEsya = $firma->sektor === 'beyaz-esya';

        $device_brands    = DeviceBrand::where(function($query) use ($firma, $isBeyazEsya) {
            if ($isBeyazEsya) {
                // Beyaz eşya sektörü: default + kendi eklediği
                $query->whereNull('firma_id')
                    ->orWhere('firma_id', $firma->id);
            } else {
                // Diğer sektörler: sadece kendi eklediği
                $query->where('firma_id', $firma->id);
            }
        })->orderBy('marka', 'asc')->get();
        $device_types     = DeviceType::where(function($query) use ($firma, $isBeyazEsya) {
            if ($isBeyazEsya) {
                // Beyaz eşya sektörü: default + kendi eklediği
                $query->whereNull('firma_id')
                    ->orWhere('firma_id', $firma->id);
            } else {
                // Diğer sektörler: sadece kendi eklediği
                $query->where('firma_id', $firma->id);
            }
        })->orderBy('cihaz', 'asc')->get();
        $service_stages   = ServiceStage::where(function ($q) use ($tenant_id) {
                                $q->whereNull('firma_id')->orWhere('firma_id', $tenant_id);
                            })->orderBy('asama', 'asc')->get();
        $service_resources= ServiceResource::where('firma_id', $tenant_id)->orderBy('kaynak', 'asc')->get();
        $states           = Il::orderBy('name', 'ASC')->get();

        // İstatistik query paramları
        $operator_id                 = $request->operator_id;
        $opeator_istatistik_tarih1   = $request->opeator_istatistik_tarih1;
        $opeator_istatistik_tarih2   = $request->opeator_istatistik_tarih2;
        $state_id                    = $request->state_id;
        $state_istatistik_tarih1     = $request->state_istatistik_tarih1;
        $state_istatistik_tarih2     = $request->state_istatistik_tarih2;
        $stage_id                    = $request->stage_id;
        $stage_istatistik_tarih1     = $request->stage_istatistik_tarih1;
        $stage_istatistik_tarih2     = $request->stage_istatistik_tarih2;
        $ilceArama                   = $request->ilceArama;
        $ilce_istatistik_tarih1      = $request->ilce_istatistik_tarih1;
        $ilce_istatistik_tarih2      = $request->ilce_istatistik_tarih2;
        $personel_id                 = $request->personel_id;
        $personel_istatistik_tarih1  = $request->personel_istatistik_tarih1;
        $personel_istatistik_tarih2  = $request->personel_istatistik_tarih2;

if ($request->ajax()) {
    // SADECE TABLODA GÖRÜNEN ALANLAR
    $data = Service::query()
        ->select([
            'services.id',
            'services.created_at',
            'services.musteri_id',
            'services.cihazMarka',
            'services.cihazTur',
            'services.servisDurum',
            'services.kayitAlan',
            'services.acil',
            'services.firma_id',
            'services.cihazAriza'
        ])
        ->with([
            // Sadece gerekli kolonlar
            'musteri:id,adSoyad,tel1,tel2,adres',
            'markaCihaz:id,marka',
            'turCihaz:id,cihaz',
            'asamalar:id,asama,asama_renk',
            'users:user_id,name',
        ])
        ->where('firma_id', $firma->id)
        ->where('durum', 1);

    // Yetki filtresi
    if ($user = auth()->user()) {
        if ($user->can('Kendi Servislerini Görebilir')) {
            $servisIDleri = $this->getYetkiliServisIDleri($user, $firma->id);
            $data->whereIn('id', $servisIDleri);
        }
    }

    // Tarih ve diğer filtreler 
    // SADECE dashboard filtresi aktif DEĞİLSE varsayılan tarih filtrelerini uygula
    if (!$request->filled('dashboard_filter')) {
        $this->applyDefaultLast3DaysIfEmpty($data, $request);
        $this->applyMainDateRange($data, $request);
    }

    $this->applyOperatorFilters($data, $request);
    $this->applyStateFilters($data, $request);
    $this->applyBrandTypeFilters($data, $request);
    $this->applyStageFilters($data, $request);
    $this->applyLocationFilters($data, $request);
    $this->applySurveyFilters($data, $request);
    $this->applyReportingFilters($data, $request);
    $this->applyOrdering($data, $request);
    $this->applyDashBoardFilters($data, $request);

    // DataTables dönüşü
    return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('id',          fn($row) => $this->colId($row))
        ->addColumn('created_at',  fn($row) => $this->colCreatedAt($row))
        ->addColumn('m_adi',       fn($row) => $this->colMusteri($row))
        ->addColumn('cihaz',       fn($row) => $this->colCihaz($row))
        ->addColumn('asama_id',    fn($row) => $this->colAsama($row))
        ->addColumn('action',      fn($row) => $this->colActions($row))
        ->addColumn('sonlandir_action', fn($row) => $this->colSonlandirAction($row))
        ->addColumn('sec_checkbox', fn($row) => $this->colSecCheckbox($row))
        ->filter(function ($instance) use ($request) {
            if (!empty($request->get('search'))) {
                $search = $request->get('search');
                $instance->where(function ($w) use ($search) {
                    $w->where('id', 'LIKE', "%$search%")
                    ->orWhereHas('musteri', function ($q) use ($search) {
                        $q->where('adSoyad', 'LIKE', "%$search%")
                            ->orWhere('tel1', 'LIKE', "%$search%");
                    });
                });
            }
        })
        ->rawColumns(['id', 'created_at', 'm_adi', 'cihaz', 'asama_id',  'action','sonlandir_action','sec_checkbox'])
        ->make(true);
}

        return view(
            'frontend.secure.all_services.services',
            compact(
                //'services',
                'device_brands',
                'device_types',
                'service_stages',
                'firma',
                'service_resources',
                'states',
                'operator_id',
                'opeator_istatistik_tarih1',
                'opeator_istatistik_tarih2'
            )
        );
    }

    /* ===========================
    ===== Helper Methods ======
    =========================== */
    private function isDefaultDateRange(Request $request): bool
    {
        if (!$request->filled('from_date') || !$request->filled('to_date')) {
            return false;
        }
        
        try {
            $from = Carbon::createFromFormat('Y-m-d', $request->from_date)->startOfDay();
            $to = Carbon::createFromFormat('Y-m-d', $request->to_date)->endOfDay();
            $defaultFrom = Carbon::today()->subDays(2)->startOfDay();
            $defaultTo = Carbon::today()->endOfDay();
            
            return $from->equalTo($defaultFrom) && $to->equalTo($defaultTo);
        } catch (\Exception $e) {
            return false;
        }
    }

    // Eğer hiçbir tarih gelmezse: varsayılan son 3 gün
    private function applyDefaultLast3DaysIfEmpty($query, Request $request): void
    {
        // 1. Ana daterangepicker'dan gelen tarih var mı? (ve varsayılan değil mi?)
        $hasMainDateRange = $request->filled('from_date') && 
                        $request->filled('to_date') && 
                        !$this->isDefaultDateRange($request);

        // 2. İstatistik sayfalarından gelen bir tarih aralığı var mı?
        $hasStatsDateRange =
            ($request->filled('opeator_istatistik_tarih1') && $request->filled('opeator_istatistik_tarih2')) ||
            ($request->filled('state_istatistik_tarih1')   && $request->filled('state_istatistik_tarih2'))   ||
            ($request->filled('stage_istatistik_tarih1')   && $request->filled('stage_istatistik_tarih2'))   ||
            ($request->filled('ilce_istatistik_tarih1')    && $request->filled('ilce_istatistik_tarih2'))    ||
            ($request->filled('personel_istatistik_tarih1')&& $request->filled('personel_istatistik_tarih2'));

        // 3. Raporlama filtrelerinden gelen bir tarih aralığı var mı?
        $hasReportingFilter = $request->filled('filterType') && $request->filled('filters');

        // 4. Dashboard filtrelerinden gelen tarih var mı?
        $hasDashboardFilter = $request->filled('dashboard_filter') && 
                            $request->filled('dashboard_istatistik_tarih1') && 
                            $request->filled('dashboard_istatistik_tarih2');

        // 5. Kullanıcı arama veya filtre yapıyor mu?
        $hasSearch = !empty(trim($request->get('search', '')));
        $hasFilters = $request->filled('device_brands') || 
                    $request->filled('device_types') || 
                    $request->filled('stages') ||
                    $request->filled('service_resource') ||
                    $request->filled('il') ||
                    $request->filled('ilce');
        
        if ($hasMainDateRange || $hasStatsDateRange || $hasReportingFilter || $hasDashboardFilter) {
            // Tarih filtresi var, ilgili fonksiyonlar zaten uygulayacak
            return;
        }
        
        if ($hasSearch || $hasFilters) {
            // Arama/filtre var ama tarih seçilmemiş
            // TÜM kayıtlarda arama yapılacak, tarih kısıtlaması uygulanmayacak
            return;
        }
        
        if(auth()->user()->can('Kendi Servislerini Görebilir')) {
            $from = Carbon::today()->subDays(2)->startOfDay();
            $to   = Carbon::today()->endOfDay();
            $query->whereBetween('services.updated_at', [$from, $to]);
        }
        else {
            // Hiçbir şey yoksa (sayfa ilk açıldığında), varsayılan son 3 günü uygula
            $from = Carbon::today()->subDays(2)->startOfDay();
            $to   = Carbon::today()->endOfDay();
            $query->whereBetween('services.created_at', [$from, $to]);
        }
        
    }

    // Datatable’ın gönderdiği ana tarih aralığı
        private function applyMainDateRange($query, Request $request): void
        {
            // Sadece kullanıcı gerçekten tarih seçtiyse uygula (varsayılan tarih değilse)
            if ($request->filled('from_date') && $request->filled('to_date') && !$this->isDefaultDateRange($request)) {
                $from = Carbon::createFromFormat('Y-m-d', $request->from_date)->startOfDay();
                $to   = Carbon::createFromFormat('Y-m-d', $request->to_date)->endOfDay();
                $query->whereBetween('created_at', [$from, $to]);
            }
        }
    // Operatör filtreleri
    private function applyOperatorFilters($query, Request $request): void
    {
       // Operatör istatistiklerine göre filtre
        if ($request->filled('operator_id')) {
            $query->where('kayitAlan', $request->operator_id);
        }

        // Operatör istatistikleri tarih aralığı filtresi
        if ($request->filled('opeator_istatistik_tarih1') && $request->filled('opeator_istatistik_tarih2')) {
            $from = Carbon::createFromFormat('Y-m-d', $request->opeator_istatistik_tarih1)->startOfDay();
            $to = Carbon::createFromFormat('Y-m-d', $request->opeator_istatistik_tarih2)->endOfDay();
            $query->whereBetween('services.created_at', [$from, $to]);
        }
    }

    // Durum filtreleri
    private function applyStateFilters($query, Request $request): void
    {
        if ($request->filled('state_id')) {
            $query->where('servisDurum', $request->state_id);
        }
        if ($request->filled('state_istatistik_tarih1') && $request->filled('state_istatistik_tarih2')) {
            $from = Carbon::createFromFormat('Y-m-d', $request->state_istatistik_tarih1)->startOfDay();
            $to   = Carbon::createFromFormat('Y-m-d', $request->state_istatistik_tarih2)->endOfDay();
            $query->whereBetween('created_at', [$from, $to]);
        }
    }

    // Marka & Tür
    private function applyBrandTypeFilters($query, Request $request): void
    {
        if ($request->get('device_brands')) {
            $query->where('cihazMarka', $request->get('device_brands'));
        }
        if ($request->get('device_types') || $request->filled('deviceType')) {
            $query->where('cihazTur', $request->get('device_types') ?? $request->get('deviceType'));
        }
        if ($request->get('stages')) {
            $query->where('servisDurum', $request->get('stages'));
        }
        if ($request->get('service_resource')) {
            $query->where('servisKaynak', $request->get('service_resource'));
        }
    }

    // Aşama (service_plannings üzerinden) + sadece tarih
    private function applyStageFilters($query, Request $request): void
    {
        if ($request->filled('stage_id')) {
            $stageId = $request->stage_id;
            $query->whereExists(function ($q) use ($stageId, $request) {
                $q->select(DB::raw(1))
                ->from('service_plannings')
                ->whereColumn('service_plannings.servisid', 'services.id')
                ->where('service_plannings.gidenIslem', $stageId);

                if ($request->filled('stage_istatistik_tarih1') && $request->filled('stage_istatistik_tarih2')) {
                    $from = Carbon::createFromFormat('Y-m-d', $request->stage_istatistik_tarih1)->startOfDay();
                    $to   = Carbon::createFromFormat('Y-m-d', $request->stage_istatistik_tarih2)->endOfDay();
                    $q->whereBetween('service_plannings.created_at', [$from, $to]);
                }
            });
        }

        if ($request->filled('stage_istatistik_tarih1') && $request->filled('stage_istatistik_tarih2') && !$request->filled('stage_id')) {
            $from = Carbon::createFromFormat('Y-m-d', $request->stage_istatistik_tarih1)->startOfDay();
            $to   = Carbon::createFromFormat('Y-m-d', $request->stage_istatistik_tarih2)->endOfDay();
            $query->whereBetween('created_at', [$from, $to]);
        }
    }

    // İl / İlçe
    private function applyLocationFilters($query, Request $request): void
    {
        if ($request->get('il')) {
            $query->whereRelation('musteri', 'il', $request->get('il'));
        }
        if ($request->get('ilce')) {
            $query->whereRelation('musteri', 'ilce', $request->get('ilce'));
        }

        // İlçe istatistikleri
        $ilceId = null;
        if ($request->filled('ilceArama')) {
            $ilceId = DB::table('ilces')->where('ilceName', $request->ilceArama)->value('id');
        }
        if ($ilceId) {
            $query->whereHas('musteri', function ($q) use ($ilceId) {
                $q->where('ilce', $ilceId);
            });
        }
        if ($request->filled('ilce_istatistik_tarih1') && $request->filled('ilce_istatistik_tarih2')) {
            $from = Carbon::createFromFormat('Y-m-d', $request->ilce_istatistik_tarih1)->startOfDay();
            $to   = Carbon::createFromFormat('Y-m-d', $request->ilce_istatistik_tarih2)->endOfDay();
            $query->whereBetween('created_at', [$from, $to]);
        }
    }

  private function applyDashBoardFilters($query, Request $request): void
{
    if ($request->filled('dashboard_filter')) {
        
        // Tarih filtresi
        if ($request->filled('dashboard_istatistik_tarih1') && $request->filled('dashboard_istatistik_tarih2')) {
            
            $startDate = $request->dashboard_istatistik_tarih1;
            $endDate = $request->dashboard_istatistik_tarih2;

            // Statü grubuna göre hangi tarih sütununun kullanılacağını belirle
            $dateColumn = 'kayitTarihi'; // Varsayılan olarak kayıt tarihi
            if ($request->status_group === 'cancelled') {
                $dateColumn = 'updated_at'; // İptal edilenler için GÜNCELLEME tarihi
            }

            // whereDate metodu, datetime sütunundan sadece tarih kısmını alır.
            $query->whereDate($dateColumn, '>=', $startDate)
                  ->whereDate($dateColumn, '<=', $endDate);
        }

        // Statü filtresi
        if ($request->filled('status_group')) {
            $cancelled_statuses = [244];
            $new_service_status = [235];
            $excluded_statuses = array_merge($cancelled_statuses, $new_service_status);

            if ($request->status_group === 'cancelled') {
                $query->whereIn('servisDurum', $cancelled_statuses);
            } 
            elseif ($request->status_group === 'in_process') {
                $query->whereNotIn('servisDurum', $excluded_statuses);
            }
        }
    }
}
        

    // Anket
    private function applySurveyFilters($query, Request $request): void
    {
        if ($request->filled('personel_id')) {
            $query->whereHas('surveys', function ($q) use ($request) {
                $q->where('ekleyen', $request->personel_id);
            });
        }
        if ($request->has('anket_yapilan') && $request->anket_yapilan == '1') {
            $query->whereHas('surveys');
        }
        if ($request->has('personel_istatistik_tarih1') && $request->has('personel_istatistik_tarih2')) {
            $startDate = $request->personel_istatistik_tarih1 . ' 00:00:00';
            $endDate   = $request->personel_istatistik_tarih2 . ' 23:59:59';
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
    }

    // Raporlama (switch-case aynı ama break eklendi, küçük toparlama)
    private function applyReportingFilters($query, Request $request): void
    {
        if ($request->filterType && $request->filters) {
            $filters = is_array($request->filters) ? $request->filters : json_decode($request->filters, true);

            switch ($request->filterType) {
                case 'operator':
                    $t1 = !empty($filters['operator_tarih1']) ? Carbon::parse($filters['operator_tarih1'])->startOfDay() : null;
                    $t2 = !empty($filters['operator_tarih2']) ? Carbon::parse($filters['operator_tarih2'])->endOfDay()   : null;

                    if (!empty($filters['operator_pers']) && $filters['operator_pers'] != '0') {
                        $query->where('kayitAlan', $filters['operator_pers']);
                    }
                    if ($t1 && $t2) {
                        $query->whereBetween('created_at', [$t1, $t2]);
                    }
                    break;

                case 'teknisyen':
                    if (!empty($filters['teknisyen']) && $filters['teknisyen'] != '0') {
                        $pid = (string) $filters['teknisyen'];
                        $query->whereHas('cevaplar', function ($q) use ($pid) {
                            $q->where('soruid', 45)->where('cevap', $pid);
                        });
                    }
                    if (!empty($filters['tekArac']) && $filters['tekArac'] != '0') {
                        $aid = (string) $filters['tekArac'];
                        $query->whereHas('cevaplar', function ($q) use ($aid) {
                            $q->where('soruid', 47)->where('cevap', $aid);
                        });
                    }
                    if (!empty($filters['tekTarih'])) {
                        $date = Carbon::parse($filters['tekTarih'])->format('Y-m-d');
                        $query->whereHas('cevaplar', function ($q) use ($date) {
                            $q->where('soruid', 48)->where('cevap', $date);
                        });
                    }
                    break;

                case 'urunSatis':
                    $t1 = Carbon::parse($filters['tarih1'])->startOfDay();
                    $t2 = Carbon::parse($filters['tarih2'])->endOfDay();
                    $query->whereHas('plans', function ($q) use ($t1, $t2) {
                        $q->where('gidenIslem', 256)->whereBetween('created_at', [$t1, $t2]);
                    });
                    break;

                case 'bayiArama':
                    $t1 = Carbon::parse($filters['bayi_tarih1'])->startOfDay();
                    $t2 = Carbon::parse($filters['bayi_tarih2'])->endOfDay();
                    $query->whereHas('plans', function ($q) use ($t1, $t2) {
                        $q->where('gidenIslem', 264)->whereBetween('created_at', [$t1, $t2]);
                    });
                    break;

                case 'acilArama':
                    $t1 = Carbon::parse($filters['acil_tarih1'])->startOfDay();
                    $t2 = Carbon::parse($filters['acil_tarih2'])->endOfDay();
                    $query->where('acil', 1)->whereBetween('created_at', [$t1, $t2]);
                    break;

                case 'yapilananketler':
                    $t1 = Carbon::parse($filters['yapilananket_tarih1'])->startOfDay();
                    $t2 = Carbon::parse($filters['yapilananket_tarih2'])->endOfDay();

                    $query->whereHas('surveys', function ($q) use ($t1, $t2, $filters) {
                        $q->whereBetween('created_at', [$t1, $t2]);

                        if (!empty($filters['anketi_yapilan_personel']) && $filters['anketi_yapilan_personel'] != '0') {
                            $q->where('personel', $filters['anketi_yapilan_personel']);
                        }
                        if (!empty($filters['anketi_yapan_personel']) && $filters['anketi_yapan_personel'] != '0') {
                            $q->where('ekleyen', $filters['anketi_yapan_personel']);
                        }
                        if (!empty($filters['bayiler']) && $filters['bayiler'] != '0') {
                            $q->where('bayi', $filters['bayiler']);
                        }
                    });
                    break;

                case 'yapilmayanAnketler':
                    $t1 = Carbon::parse($filters['yapilmayananket_tarih1'])->startOfDay();
                    $t2 = Carbon::parse($filters['yapilmayananket_tarih2'])->endOfDay();
                    $query->whereBetween('created_at', [$t1, $t2]);

                    if (!empty($filters['yapilmayan_personel']) && $filters['yapilmayan_personel'] != '0') {
                        $pid = (string) $filters['yapilmayan_personel'];
                        $query->whereHas('cevaplar', function ($q) use ($pid) {
                            $q->where('soruid', 45)->where('cevap', $pid);
                        });
                    }
                    if (!empty($filters['bayiler']) && $filters['bayiler'] != '0') {
                        $bid = (string) $filters['bayiler'];
                        $query->whereHas('cevaplar', function ($q) use ($bid) {
                            $q->where('soruid', 3)->where('cevap', $bid);
                        });
                    }
                    $query->doesntHave('surveys');
                    break; // <-- eksik break düzeltildi

                case 'gelen-cagrilar':
                    // mevcutta boş, davranışı bozmayalım
                    break;
            }
        }
    }

    // Sıralama
    private function applyOrdering($query, Request $request): void
    {
        if ($request->has('order')) {
            $order   = $request->get('order')[0];
            $columns = $request->get('columns');
            $orderColumn = $columns[$order['column']]['data'];
            $orderDir = $order['dir'];
            $query->orderBy($orderColumn, $orderDir);
        } else {
            $query->orderBy('id', 'desc');
        }
    }

    /* ====== DataTables sütun render yardımcıları ====== */

    private function colId($row): string
    {
        return '<a class="t-link serBilgiDuzenle address idWrap" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editServiceDescModal">'.$row->id.'</a>';
    }

    private function colCreatedAt($row): string
    {
        $sontarih = Carbon::parse($row->created_at)->format('d/m/Y H:i:s');
        return '<a class="t-link serBilgiDuzenle address" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editServiceDescModal"><span class="mobileTitle">Tarih:</span>'.$sontarih.'</a>';
    }

    private function colMusteri($row): string
    {
        $alarmIcon = ($row->acil == 1)
            ? "<img src='" . asset('frontend/img/alarm.gif') . "' style='width:12px;height:12px;margin-right:4px;margin-bottom:2px;' title='Acil Servis'>"
            : '';

        $tel1 = $row->musteri->tel1 ?? '';
        $tel2 = $row->musteri->tel2 ?? '';
        $adres= $row->musteri->adres ?? '';

        $mobileId = '<span class="mobile-servis-id">' . $row->id . ' - </span>';

        return '<a class="t-link serBilgiDuzenle address" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editServiceDescModal"><span class="mobileTitle">Müşteri:</span><strong>'. $alarmIcon . $mobileId . e($row->musteri->adSoyad) .'</strong><br><div style="font-size:12px;">'.e($tel1).' - '.e($tel2).'</div><div style="font-size:12px;">'. e(Str::limit($adres, 40)) .'</div></a>';
    }

    private function colCihaz($row): string
    {
        $marka = $row->markaCihaz->marka ?? '';
        $tur   = $row->turCihaz->cihaz ?? '';
         $ariza = $row->cihazAriza ?  $row->cihazAriza : '';
        return '<a class="t-link serBilgiDuzenle" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editServiceDescModal"><span class="mobileTitle">Cihaz:</span><strong>'.e($marka).' - '.e($tur).'</strong><br/><p class="p-cihaz">'.e($ariza).'</p></a>';
    }

private function colAsama($row): string
{
    $html  = '<a class="t-link serBilgiDuzenle address" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editServiceDescModal">';
    $html .= '<span class="mobileTitle">S. Durumu:</span>';
    $html .= '<strong>'. e($row->asamalar?->asama) .'</strong><br>';

    // Sadece yeni servisler için personel adı göster
    if ($row->asamalar?->id == 235) {
        $html .= '<div style="font-size:12px;">('. e($row->users?->name) .')</div>';
    }

    // Detaylar modal açılınca yüklenecek - burada göstermeye gerek yok
    $html .= '</a>';
    return $html;
}

private function formatStokList(string $value): string
{
    $items = explode(", ", $value);
    $stokIds = [];
    $stokAdetler = [];
    
    // Önce ID'leri topla
    foreach ($items as $item) {
        if (!str_contains($item, '---')) continue;
        [$stokId, $adet] = explode('---', $item);
        $stokIds[] = $stokId;
        $stokAdetler[$stokId] = $adet;
    }
    
    if (empty($stokIds)) {
        return '';
    }
    
    // TEK SORGU ile tüm stokları al
    $stoklar = Stock::whereIn('id', $stokIds)
        ->pluck('urunAdi', 'id');
    
    // Formatla
    $out = [];
    foreach ($stokAdetler as $stokId => $adet) {
        $urunAdi = $stoklar[$stokId] ?? 'Stok';
        $out[] = trim($urunAdi . " ($adet)");
    }
    
    return implode(', ', $out);
}

    private function colSonlandirAction($row): string
    {
        // Servisi "Sonlandırıldı" durumuna geçirmek için kullanılacak buton.
                    $sonlandirabilir_asamalar = 
                        ['Yerinde Bakım Yapıldı', 'Fiyatta Anlaşılamadı','Ürün Garantili Çıktı','Müşteriye Ulaşılamadı',
                        'Müşteri İptal Etti','Cihaz Tamir Edilemiyor','Haber Verecek','Cihaz Teslim Edildi','Şikayetçi','Cihaz Satışı Yapıldı',
                        'Tahsilata Gönder','Cihaz Teslim Edildi (Parça Takıldı)','Müşteri Para İade Edilecek','Müşteri Para İade Edildi',
                        'Fiyat Yükseltildi','Deneme Aşaması','Konsinye Cihaz Ata','Konsinye Cihaz Geri Alındı'
                        ];
                    $zaten_sonlanmis_asama_adi = 'Servisi Sonlandır';

                    if ($row->asamalar) {
                        // DURUM 1: Servis, sonlandırılabilecek bir aşamadaysa (Switch 'OFF' ve tıklanabilir durumda)
                        if (in_array($row->asamalar->asama, $sonlandirabilir_asamalar)) {
                            $hedef_sonlanmis_asama_id = 255;
                            return '
                                <div class="form-check form-switch d-flex justify-content-center">
                                    <input class="form-check-input  servis-sonlandir-switch" type="checkbox" role="switch"
                                        style="cursor:pointer; width: 2.7em; height: 1.2em;"
                                        data-servis-id="' . $row->id . '" 
                                        data-gelen-islem-id="' . $row->servisDurum . '"
                                        data-giden-islem-id="' . $hedef_sonlanmis_asama_id . '"
                                        title="Servisi tamamlandı olarak işaretlemek için tıklayın">
                                </div>';
                        }
                        
                        // DURUM 2: Servis zaten sonlanmış bir aşamadaysa (Switch 'ON' ve tıklanabilir durumda - servis modalı açılacak)
                        if ($row->asamalar->asama == $zaten_sonlanmis_asama_adi) {
                            return '
                                <div class="form-check form-switch d-flex justify-content-center">
                                    <input class="form-check-input servis-sonlanmis-switch serBilgiDuzenle" type="checkbox" role="switch" 
                                        checked 
                                        style="cursor:pointer; width: 2.7em; height: 1.2em;"
                                        data-bs-id="' . $row->id . '"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editServiceDescModal"
                                        title="Servis tamamlanmış - Detayları görüntülemek için tıklayın">
                                </div>';
                        }
                    }
                                    
                        // DURUM 3: Sonlandırılamayan aşamalar - Pasif switch göster
                         return '
                        <div class="form-check form-switch d-flex justify-content-center" 
                            data-bs-toggle="tooltip" 
                            data-bs-placement="top" 
                            title="Bu aşamadan sonra servisi sonlandıramazsınız"
                            style="cursor: not-allowed;">
                            <input class="form-check-input servis-sonlandirilamaz-switch" 
                                type="checkbox" 
                                role="switch"
                                disabled
                                style="width: 2.7em; height: 1.2em; opacity: 0.5; pointer-events: none;">
                        </div>';

      
    }

    private function colActions($row): string
    {
        $deleteUrl   = route('delete.service', [$row->firma_id, $row->id]);

    $editButton  = '<a style="padding:6px" href="javascript:void(0);" data-bs-id="'.$row->id.'" 
                       class="btn btn-outline-warning btn-sm mobilBtn mbuton1 serBilgiDuzenle " 
                       data-bs-toggle="modal" data-bs-target="#editServiceDescModal" 
                       title="Düzenle">
                       <i class="fas fa-edit"></i><span> Düzenle</span>
                    </a>';

    $viewButton  = '<a style="padding:6px" href="javascript:void(0);" data-bs-id="'.$row->id.'" 
                       class="btn btn-outline-primary btn-sm mobilBtn mbuton2 serBilgiDuzenle serBilgiDuzenle-custom-l"
                       data-bs-toggle="modal" data-bs-target="#editServiceDescModal" 
                       title="Görüntüle">
                       <i class="fas fa-eye"></i><span> Görüntüle</span>
                    </a>';

    $deleteButton = '';
    if (Auth::user()->can('Servisleri Silebilir')) {
        $deleteButton = '<a style="padding:6px 7px" href="'.$deleteUrl.'" 
                            class="btn btn-sm btn-outline-danger mobilBtn mbuton3 delete-button serBilgiDuzenle-custom-r" 
                            id="delete" title="Sil">
                            <i class="fas fa-trash-alt"></i> <span> Sil</span>
                         </a>';
    }

    return $viewButton . ' ' .  $editButton . ' ' . $deleteButton;
    }
    private function colSecCheckbox($row): string
    {
        return '
            <div class="d-flex justify-content-center">
                <input class="form-check-input servis-sec-checkbox" 
                    type="checkbox" 
                    value="' . $row->id . '"
                    data-servis-id="' . $row->id . '"
                    style="width: 1.2em; height: 1.2em; cursor: pointer;"
                    title="Servisi seç">
            </div>';
    }
    


    //Sadece kendine atanan servisleri gören kişilerin koşullarını kontrol eden fonksiyon.(üstteki AllServices fonksiyonunda kullandım)
private function getYetkiliServisIDleri($user, $tenant_id)
{
    $bugun = now()->format('Y-m-d');
    $simdikiSaat = now()->format('H:i');
    $yetkiliServisIDler = collect();
    
    //Tüm gerekli veriyi TEK SORGUDA çek
    $adayServicler = ServiceStageAnswer::query()
        ->select([
            'service_stage_answers.servisid',
            'service_stage_answers.planid',
            'service_stage_answers.soruid',
            'stage_questions.cevapTuru',
            'service_plannings.tarihDurum',
            'service_plannings.pid as plan_pid',
            'service_plannings.created_at as plan_created_at',
            'services.planDurum as servis_planDurum',
            'services.servisDurum'
        ])
        ->join('stage_questions', 'stage_questions.id', '=', 'service_stage_answers.soruid')
        ->join('service_plannings', 'service_plannings.id', '=', 'service_stage_answers.planid')
        ->join('services', 'services.id', '=', 'service_stage_answers.servisid')
        ->where('service_stage_answers.firma_id', $tenant_id)
        ->where('service_stage_answers.cevap', $user->user_id)
        ->where(function($query) {
            $query->where('stage_questions.cevapTuru', 'LIKE', '%Grup%')
                  ->orWhere('stage_questions.cevapTuru', 'LIKE', '%Bayi%');
        })
        ->get();

    if ($adayServicler->isEmpty()) {
        return [];
    }

    //Tarih cevaplarını toplu çek (N+1 önleme)
    $planIdler = $adayServicler->pluck('planid')->unique()->toArray();
    
    $tarihCevaplar = ServiceStageAnswer::query()
        ->select('planid', 'servisid', 'cevap')
        ->whereIn('planid', $planIdler)
        ->where('cevapText', '[Tarih]')
        ->where('firma_id', $tenant_id)
        ->get()
        ->keyBy('planid'); // planid'ye göre key'le

    //ServiceTime'ı bir kez çek (cache'lenebilir)
    $zaman = ServiceTime::where('firma_id', $tenant_id)->first();
    $saatKontrolAktif = false;
    $sonsaat = null;
    
    if ($zaman) {
        $sonsaat = str_replace('.', ':', $zaman->zaman);
        $saatKontrolAktif = strtotime($simdikiSaat) >= strtotime($sonsaat);
    }

    //Koleksiyon üzerinde filtreleme (memory'de hızlı)
    $isDepocu = $user->hasRole('Depocu');
    
    foreach ($adayServicler as $servisRow) {
        $eklenmeli = false;
        $tarihDurum = $servisRow->tarihDurum > 0;

        //Tarih durumu VAR
        if ($tarihDurum) {
            $tarihCevap = $tarihCevaplar->get($servisRow->planid);
            
            if ($tarihCevap) {
                $tarih = $tarihCevap->cevap;
                
                // Bugünkü servisler
                if ($bugun == $tarih) {
                    // Depocu değilse ve saat kontrolü geçtiyse
                    if (!$isDepocu && $saatKontrolAktif) {
                        $eklenmeli = true;
                    }
                }
                
                // Depocu için özel durum (tarih kontrolü yok)
                if ($isDepocu && in_array($servisRow->servisDurum, ['257', '263'])) {
                    $eklenmeli = true;
                }
            }
        }
        // Tarih durumu YOK
        else {
            // Aktif plan kontrolü
            if ($servisRow->servis_planDurum == $servisRow->planid) {
                $eklenmeli = true;
            }
            
            // Bugün eklenen planlar (Depocu hariç)
            if (!$isDepocu && $servisRow->plan_pid == $user->user_id) {
                $planTarih = Carbon::parse($servisRow->plan_created_at)->format('Y-m-d');
                
                if ($planTarih == $bugun) {
                    $eklenmeli = true;
                }
            }
        }
        
        if ($eklenmeli) {
            $yetkiliServisIDler->push($servisRow->servisid);
        }
    }

    return $yetkiliServisIDler->unique()->toArray();
}

    //teknisyene atanan depo stoklarını gösteren fonksiyon
    public function StaffStocks($tenant_id, $personel_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $staff_stocks = PersonelStock::with(['personel','stok'])->where('firma_id', $firma->id)->where('pid', $personel_id)->get();
        return view('frontend.secure.all_services.staff_stocks', compact('staff_stocks'));
    }

    public function searchCustomer(Request $request, $firma_id)
    {
        try {
            $searchTerm = $request->input('musteriGetir');
            
            // Minimum karakter kontrolü
            if (strlen($searchTerm) < 2) {
                return response()->json([]);
            }
            
            // Müşteri arama - firma_id'ye göre filtreleme
            $customers = Customer::where('firma_id', $firma_id)
                ->where(function($query) use ($searchTerm) {
                    $query->where('adSoyad', 'LIKE', '%' . $searchTerm . '%')
                          ->orWhere('tel1', 'LIKE', '%' . $searchTerm . '%')
                          ->orWhereRaw('REPLACE(tel1, "-", "") LIKE ?', ['%' . str_replace(['-', '(', ')', ' '], '', $searchTerm) . '%'])
                          ->orWhere('tcNo', 'LIKE', '%' . $searchTerm . '%')
                          ->orWhere('vergiNo', 'LIKE', '%' . $searchTerm . '%');
                })
                ->select([
                    'id',
                    'adSoyad', 
                    'tel1', 
                    'tel2', 
                    'adres', 
                    'il', 
                    'ilce', 
                    'musteriTipi',
                    'tcNo',
                    'vergiNo',
                    'vergiDairesi'
                ])
                ->orderBy('adSoyad')
                ->limit(10) // Maksimum 10 sonuç
                ->get();
            
            // İl ve ilçe isimlerini çek (eğer ID olarak saklanıyorsa)
            $results = $customers->map(function($customer) {
                // Eğer il ve ilçe ID olarak saklanıyorsa, isimlerini çek
                $il = DB::table('ils')->where('id', $customer->il)->value('name') ?? $customer->il;
                $ilce = DB::table('ilces')->where('id', $customer->ilce)->value('ilceName') ?? $customer->ilce;
                
                return [
                    'id' => $customer->id,
                    'adSoyad' => $customer->adSoyad,
                    'tel1' => $customer->tel1,
                    'tel2' => $customer->tel2,
                    'adres' => $customer->adres,
                    'il' => $il,
                    'ilce' => $ilce,
                    'musteriTipi' => $customer->musteriTipi,
                    'tcNo' => $customer->tcNo,
                    'vergiNo' => $customer->vergiNo,
                    'vergiDairesi' => $customer->vergiDairesi
                ];
            });
            
            return response()->json($results);
            
        } catch (\Exception $e) {
            
            return response()->json(['error' => 'Arama sırasında hata oluştu'], 500);
        }
    }

    public function AddService($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $service_resources = ServiceResource::where('firma_id', $firma->id)->orderBy('kaynak', 'asc')->get();
        $iller = DB::table('ils')->orderBy('name', 'ASC')->get();

        $isBeyazEsya = $firma->sektor === 'beyaz-esya';

        $device_brands = DeviceBrand::where(function($query) use ($firma, $isBeyazEsya) {
            if ($isBeyazEsya) {
                // Beyaz eşya sektörü: default + kendi eklediği
                $query->whereNull('firma_id')
                    ->orWhere('firma_id', $firma->id);
            } else {
                // Diğer sektörler: sadece kendi eklediği
                $query->where('firma_id', $firma->id);
            }
        })->orderBy('marka', 'asc')->get();

        $device_types = DeviceType::where(function($query) use ($firma, $isBeyazEsya) {
            if ($isBeyazEsya) {
                // Beyaz eşya sektörü: default + kendi eklediği
                $query->whereNull('firma_id')
                    ->orWhere('firma_id', $firma->id);
            } else {
                // Diğer sektörler: sadece kendi eklediği
                $query->where('firma_id', $firma->id);
            }
        })->orderBy('cihaz', 'asc')->get();
        $warranty_periods = WarrantyPeriod::orderBy('garanti', 'asc')->get();
        $hasInvoiceIntegration = InvoiceIntegrationFactory::hasIntegration($tenant_id);
        return view('frontend.secure.all_services.add_service', compact('firma','service_resources','iller','device_brands','device_types','warranty_periods','hasInvoiceIntegration'));
    }

    public function StoreService($tenant_id, Request $request) {       
            $token = $request->input('form_token');
            // Token boş mu kontrol et
            if (empty($token)) {
                $notification = array(
                    'message' => 'Geçersiz form token! Lütfen sayfayı yenileyin.',
                    'alert-type' => 'error'
                );
                return redirect()->back()->with($notification);
            }

            // Bu token daha önce kullanıldı mı kontrol et
            $cacheKey = 'form_token_' . $token;

            if (Cache::has($cacheKey)) {
                $notification = array(
                    'message' => 'Bu form zaten gönderildi! Lütfen bekleyin veya sayfayı yenileyin.',
                    'alert-type' => 'warning'
                );
                return redirect()->back()->with($notification);
            }

            // Token'ı 10 dakika boyunca sakla (duplicate önleme için)
            Cache::put($cacheKey, true, now()->addMinutes(10));

        $firma = Tenant::where('id', $tenant_id)->first();
        
        if (!$firma) {
            return redirect()->route('giris');
        }

        $raw1 = preg_replace('/\D/', '', $request->tel1); // Sadece rakamlar
        $tel1 = preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1 $2 $3', $raw1);

        $raw2 = preg_replace('/\D/', '', $request->tel2); // Sadece rakamlar
        $tel2 = preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1 $2 $3', $raw2);

        $musteriData = [
            'firma_id' => $firma->id,
            'personel_id' => auth()->id(),
            'musteriTipi' => $request->musteriTipi,
            'adSoyad' => $request->adSoyad,
            'tel1' => $tel1,
            'tel2' => $tel2,
            'il' => $request->il,
            'ilce' => $request->ilce,
            'adres' => $request->adres,
            'tcNo' => $request->tcNo,
            'vergiNo' => $request->vergiNo,
            'vergiDairesi' => $request->vergiDairesi,
            'created_at' => now(),
        ];
        $eskiMusteriId = $request->eskiMusteriId;

        if (empty($eskiMusteriId)) {
            // Yeni müşteri - önce aynı bilgilerle müşteri var mı kontrol et
            $musteriKontrol = Customer::where('firma_id', $firma->id)->where([
                'musteriTipi' => $request->musteriTipi,
                'adSoyad' => $request->adSoyad,
                'tel1' => $request->tel1,
                'tel2' => $request->tel2,
                'il' => $request->il,
                'ilce' => $request->ilce,
                'adres' => $request->adres,
                'tcNo' => $request->tcNo,
                'vergiNo' => $request->vergiNo,
                'vergiDairesi' => $request->vergiDairesi,
            ])->first();

            if ($musteriKontrol) {
                $musteriId = $musteriKontrol->id;
            } else {
                $musteri = Customer::create($musteriData);
                $musteriId = $musteri->id;

            // Yeni müşteri oluşturulduğunda log ekle
            ActivityLogger::logCustomerCreated($musteriId);

            // Hipcall entegrasyonu kontrolü ve müşteri ekleme
            try {
                $hipcallPurchase = IntegrationPurchase::where('tenant_id', $tenant_id)
                    ->whereHas('integration', function($q) {
                        $q->where('slug', 'hipcall');
                    })
                    ->where('status', 'completed')
                    ->where('is_active', true)
                    ->first();
                
                if ($hipcallPurchase) {
                    $hipcallService = new HipcallService($tenant_id);
                    
                    $contactData = [
                        'name' => $request->adSoyad,
                        'phone' => $raw1, 
                        'phone2' => $raw2,
                        'tc_no' => $request->tcNo,
                        'vergi_no' => $request->vergiNo,
                        'vergi_dairesi' => $request->vergiDairesi
                    ];
                    
                    $hipcallResult = $hipcallService->createContact($contactData);
                    
                    if ($hipcallResult['success']) {
                        Log::info('Servis eklenirken müşteri Hipcall rehberine de eklendi', [
                            'customer_id' => $musteriId,
                            'service_context' => true,
                            'hipcall_response' => $hipcallResult
                        ]);
                    } else {
                        // Hipcall'a eklenemese bile servis kaydı devam eder
                        Log::warning('Servis eklenirken müşteri Hipcall rehberine eklenemedi', [
                            'customer_id' => $musteriId,
                            'error' => $hipcallResult['message']
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Hipcall hatası servis oluşturmayı engellemez
                Log::error('Hipcall entegrasyon hatası (servis ekleme)', [
                    'customer_id' => $musteriId,
                    'error' => $e->getMessage()
                ]);
            }
            
            }
        } else {
            // Eski müşteri seçilmiş - sadece güncelle, yeni müşteri oluşturma
            $mevcutMusteri = Customer::find($eskiMusteriId);
            if ($mevcutMusteri) {
                $mevcutMusteri->update($musteriData);
                $musteriId = $eskiMusteriId;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Seçilen müşteri bulunamadı.'
                ], 404);
            }
        }

        if ($musteriId) {

            // İlk servis durumunu al
            $servisDurum = ServiceStage::where('ilkServis', '1')->first();

            $servisData = [
                'firma_id' => $firma->id,
                'kid' => auth()->id(),
                'bid' => '0',
                'pid' => '0',
                'musteri_id' => $musteriId,
                'kayitTarihi' => now(),
                'servisKaynak' => $request->input('servisReso'),
                'musaitTarih' => $request->kayitTarihi,
                'musaitSaat1' => $request->input('musaitSaat1'),
                'musaitSaat2' => $request->input('musaitSaat2'),
                'cihazMarka' => $request->input('cihazMarka'),
                'cihazTur' => $request->input('cihazTur'),
                'cihazModel' => $request->input('cihazModel'),
                'cihazSeriNo' => $request->input('cihazSeriNo'),
                'cihazAriza' => $request->cihazAriza,
                'operatorNotu' => $request->input('opNot'),
                'garantiSuresi' => $request->input('cihazGaranti'),
                'servisDurum' => $servisDurum->id ?? null,
                'kayitAlan' => auth()->id(),
                'planDurum' => '0',
                'pbDurum' => 0,
                'durum' => 1,
                'acil' => 0,
            ];

            // Aynı servis kontrolü
            $servisKontrol = Service::orderBy('id', 'desc')->first();
            
            $ayniServis = false;
            if ($servisKontrol && 
                $servisKontrol->musteriid == $musteriId && 
                $servisKontrol->kayitTarihi->format('Y-m-d H:i:s') == now()->format('Y-m-d H:i:s') && 
                $servisKontrol->pid == auth()->id()) {
                $ayniServis = true;
            }

            if (!$ayniServis) {
                $servis = Service::create($servisData);
                $servisId = $servis->id;

                // Acil servis kontrolü
                // if ($request->input('acil') == "1") {
                //     $acilData = [
                //         'pid' => auth()->id(),
                //         'servisid' => $servisId,
                //     ];

                //     $acilServis = EmergencyService::create($acilData);
                    
                //     Service::where('id', $servisId)->update([
                //         'acil' => $acilServis->id
                //     ]);
                // }

                if ($servisId) {
                    
                    // SMS gönderimi için kod buraya eklenebilir
                    // ...
                    ActivityLogger::logServiceCreated($servisId);

                    $notification = array(
                        'message' => 'Servis Başarıyla Eklendi',
                        'alert-type' => 'success'
                    );

                    return redirect()->back()->with($notification);
                } else {
                    $notification = array(
                        'message' => 'Servis Kayıt Edilemedi',
                        'alert-type' => 'warning'
                    );
                }
            } else {
                $notification = array(
                'message' => 'Aynı Servis Zaten Mevcut',
                'alert-type' => 'warning'
            );
            }
        } else {
            $notification = array(
                'message' => 'Servis Kayıt Edilemedi',
                'alert-type' => 'warning'
            );
        }
    }

    public function EditService($tenant_id, $id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $service_id = Service::where('firma_id', $firma->id)->findOrFail($id);
        
        return view('frontend.secure.all_services.edit_service', compact('firma', 'service_id'));
    }

    //servis-bilgileri kısmı(Tüm servisleri görmeye izni olanlar)
    public function TumServiceDesc($tenant_id, $id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        // MODAL AÇILINCA DETAYLI YÜKLENİYOR
        $service_id = Service::with([
            'musteri',
            'markaCihaz',
            'turCihaz',
            'asamalar',
            'users',
            'warranty',
            // Modal için gerekli detaylar
            'cevaplar' => function($q) {
                $q->with('question:id,soru,cevapTuru');
            },
            'plans' => function($q) {
                $q->with('user:user_id,name')
                ->orderBy('id', 'desc')
                ->limit(10);
            }
    ])->where('firma_id', $firma->id)->findOrFail($id);
        $service_resources = ServiceResource::where('firma_id', $firma->id)->orderBy('kaynak', 'asc')->get();
        $iller = DB::table('ils')->orderBy('name', 'ASC')->get();

        $isBeyazEsya = $firma->sektor === 'beyaz-esya';

        $device_brands = DeviceBrand::where(function($query) use ($firma, $isBeyazEsya) {
            if ($isBeyazEsya) {
                // Beyaz eşya sektörü: default + kendi eklediği
                $query->whereNull('firma_id')
                    ->orWhere('firma_id', $firma->id);
            } else {
                // Diğer sektörler: sadece kendi eklediği
                $query->where('firma_id', $firma->id);
            }
        })->orderBy('marka', 'asc')->get();

        $device_types = DeviceType::where(function($query) use ($firma, $isBeyazEsya) {
            if ($isBeyazEsya) {
                // Beyaz eşya sektörü: default + kendi eklediği
                $query->whereNull('firma_id')
                    ->orWhere('firma_id', $firma->id);
            } else {
                // Diğer sektörler: sadece kendi eklediği
                $query->where('firma_id', $firma->id);
            }
        })->orderBy('cihaz', 'asc')->get();
        $warranty_periods = WarrantyPeriod::orderBy('garanti', 'asc')->get();
        
        $konsinyeKategoriId = 3;
        $konsinyeCihazlar = Stock::where('firma_id', $firma->id)
            ->where('urunKategori', $konsinyeKategoriId)
            ->get();
        // Konsinye cihaz verilerini parse et
        $seciliKonsinyeCihazlar = [];
        // ServiceStageAnswer tablosundan konsinye cevaplarını al
        $konsinyeAnswers = ServiceStageAnswer::whereIn('planid', function($query) use ($service_id) {
                $query->select('id')
                    ->from('service_plannings')
                    ->where('servisid', $service_id->id);
            })
            ->where('cevap', 'LIKE', '%---%')
            ->get();
        foreach ($konsinyeAnswers as $answer) {
            // Cevabın "1---2, 3---1" formatında olup olmadığını kontrol et
            if (preg_match('/\d+---\d+/', $answer->cevap)) {
                $cevapParts = explode(', ', $answer->cevap);
                
                foreach ($cevapParts as $part) {
                    if (strpos($part, '---') !== false) {
                        $partData = explode('---', $part);
                        if (count($partData) >= 2) {
                            $cihazId = (int)$partData[0];
                            $adet = (int)$partData[1];
                            
                            // Eğer bu cihaz konsinye kategorisindeyse ekle
                            $cihaz = $konsinyeCihazlar->firstWhere('id', $cihazId);
                            if ($cihaz) {
                                $seciliKonsinyeCihazlar[$cihazId] = $adet;
                            }
                        }
                    }
                }
            }
        }

        $altAsamaIDs = [];
        $altAsamalar = collect(); // boş koleksiyon

        if (!empty($service_id->asamalar->altAsamalar)) {
            // Virgül ile ayrılmış ID listesini array'e dönüştür
            $altAsamaIDs = explode(',', $service_id->asamalar->altAsamalar);
            $altAsamalar = ServiceStage::whereIn('id', $altAsamaIDs)->orderBy('asama')->get();
        }
        return view('frontend.secure.all_services.service_information', compact('firma', 'service_id', 'service_resources', 'iller', 'device_brands', 'device_types', 'warranty_periods','altAsamalar','seciliKonsinyeCihazlar', 'konsinyeCihazlar'));
    }
    //Servisler modalında konsinye cihaz güncellemesi
    public function getServicesKonsinyeCihaz($firma_id, $service_id)
    {
        $firma = Tenant::where('id', $firma_id)->first();
        $service_id_obj = Service::where('firma_id', $firma->id)->findOrFail($service_id);
        
        if (!$service_id_obj) {
            return response()->json(['error' => 'Servis bulunamadı'], 404);
        }

        // Konsinye cihazları getir (aynı mantıkla)
        $konsinyeKategoriId = 3;
        $konsinyeCihazlar = Stock::where('firma_id', $firma->id)
            ->where('urunKategori', $konsinyeKategoriId)
            ->get();
            
        // Konsinye cihaz verilerini parse et
        $seciliKonsinyeCihazlar = [];
        
        // ServiceStageAnswer tablosundan konsinye cevaplarını al
        $konsinyeAnswers = ServiceStageAnswer::whereIn('planid', function($query) use ($service_id_obj) {
                $query->select('id')
                    ->from('service_plannings')
                    ->where('servisid', $service_id_obj->id);
            })
            ->where('cevap', 'LIKE', '%---%')
            ->get();
            
        foreach ($konsinyeAnswers as $answer) {
            // Cevabın "1---2, 3---1" formatında olup olmadığını kontrol et
            if (preg_match('/\d+---\d+/', $answer->cevap)) {
                $cevapParts = explode(', ', $answer->cevap);
                
                foreach ($cevapParts as $part) {
                    if (strpos($part, '---') !== false) {
                        $partData = explode('---', $part);
                        if (count($partData) >= 2) {
                            $cihazId = (int)$partData[0];
                            $adet = (int)$partData[1];
                            
                            // Eğer bu cihaz konsinye kategorisindeyse ekle
                            $cihaz = $konsinyeCihazlar->firstWhere('id', $cihazId);
                            if ($cihaz) {
                                $seciliKonsinyeCihazlar[$cihazId] = $adet;
                            }
                        }
                    }
                }
            }
        }
        
        $html = '';
        
        if (count($seciliKonsinyeCihazlar) > 0) {
            foreach ($seciliKonsinyeCihazlar as $konsinyeId => $adet) {
                $urun = $konsinyeCihazlar->firstWhere('id', $konsinyeId);
                if ($urun) {
                    $html .= '<div><strong style="color:red;">' . $urun->urunAdi . '</strong></div>';
                }
            }
        } else {
            $html = '<span>Konsinye cihaz atanmadı.</span>';
        }
        
        return response($html);
    }


    //servis-bilgileri2 kısmı(Sadece kendine atanan servisleri görebildiği ekran)
    public function KendiServiceDesc($tenant_id, $id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        // MODAL AÇILINCA YÜKLENİYOR
        $servis = Service::with([
            'asamalar',
            'musteri',
            'markaCihaz',
            'turCihaz',
            'warranty',
            'cevaplar.question'
        ])->where('firma_id', $firma->id)->findOrFail($id);

        if (!$servis) {
            return response()->json(['error' => 'Servis bulunamadı'], 404);
        }

        // Yetkilendirme kontrolü - Kullanıcının firma ID'si ile servisin firma ID'si eşleşmeli
        if ($servis->firma_id != $firma->id) {
            return response()->json(['error' => 'Bu servise erişim yetkiniz yok'], 403);
        }

        // Alt aşamaları getir
        $altAsamalar = [];
        if ($servis->asamalar && $servis->asamalar->altAsamalar) {
            $altAsamaIds = explode(',', $servis->asamalar->altAsamalar);
            $altAsamalar = ServiceStage::whereIn('id', $altAsamaIds)
                ->orderBy('asama')
                ->get();
        }

        // Konsinye cihaz bilgisi
        // $konsinyeCihaz = null;
        // if ($servis->konsinye) {
        //     $konsinyeCihaz = Stock::find($servis->konsinye);
        // }

        // Eski işlemler
        $eskiIslemler = ServicePlanning::where('servisid', $id)
            ->with('user')
            ->orderBy('id', 'desc')
            ->get();

        $eskiIslemler2 = ServicePlanning::where('servisid', $id)
            ->orderBy('id', 'desc')
            ->first();

        // Müsait tarih formatla
        $musaitTarih = explode('-', $servis->musaitTarih);

        // Garanti hesaplama
        $garantiBitis = null;
        $kalanGun = -1;
        
        if ($servis->warranty && $servis->warranty->garanti) {
            $garantiBitis = \Carbon\Carbon::parse($servis->created_at)
                ->addMonths($servis->warranty->garanti)
                ->format('Y-m-d');
                
            $garantiBitisFormatted = explode('-', $garantiBitis);
            
            // Kalan gün hesaplama
            $kalanGun = Carbon::now()->diffInDays(Carbon::parse($garantiBitis), false);
        }

        // Acil işlem kontrol
        $acilIslem = null;
        if ($servis->acil != 0) {
            $acilIslem = Service::where('id', $id)
                ->first();
        }

        // Servis notları
        $servisNotlari = ServiceOptNote::where('servisid', $id)
            ->with('user')
            ->orderBy('id', 'desc')
            ->get();
        return view('frontend.secure.all_services.own_service_information', compact('firma','servis',
            'altAsamalar',
            'eskiIslemler',
            'eskiIslemler2',
            'musaitTarih',
            'garantiBitis',
            'kalanGun',
            'acilIslem',
            'servisNotlari',));
    }

    //Servis Bilgileri düzenleme modalında yapılacak işlemler selectini seçince çıkan formun olduğu sayfayı gösteren fonksiyon
    public function ServiceStageQuestionShow($tenant_id ,$asamaid, $serviceid) {
        $firma = Tenant::where('id', $tenant_id)->first();
        
        $stage_id = ServiceStage::findOrFail($asamaid);
        $service_id = Service::where('firma_id', $firma->id)->findOrFail($serviceid);
        $stage_questions = StageQuestion::where('asama', $asamaid)->orderBy('sira', 'asc')->get();

         // İşlem türünü belirle (request'ten gelen islem parametresi)
        $islem = $stage_id;
        
        // Servis bilgilerini kontrol et
        $servisSec = Service::where('id', $serviceid)->first();

        // Normal servis işlemleri 
            
// Yetkili rolleri kontrol et
$yetkiliRoller = ['Teknisyen', 'Teknisyen Yardımcısı', 'Atölye Ustası', 'Atölye Çırak'];
$kullaniciYetkili = auth()->user()->hasAnyRole($yetkiliRoller);

$stoklar = collect();
$toplamPersonelStokAdedi = 0;

if ($kullaniciYetkili) {
    if (auth()->user()->hasRole('Patron')) {
        $stoklar = PersonelStock::where('firma_id', $firma->id)
                    ->orderBy('id', 'asc')
                    ->get();
    } else {  
        $stoklar = PersonelStock::where('firma_id', $firma->id)
                    ->where('pid', auth()->user()->user_id)
                    ->orderBy('id', 'asc')
                    ->get();
    }
    
    $toplamPersonelStokAdedi = $stoklar->sum('adet');
}

        //Konsinye Cihaz Stok İşlemleri
        $konsinyeKategoriId = 3;
        $konsinyeCihazlar = Stock::where('firma_id', $firma->id)
            ->where('urunKategori', $konsinyeKategoriId)
            ->get();

        $toplamKonsinyeCihazAdedi = 0;

        foreach ($konsinyeCihazlar as $device) {
            // Giriş işlemleri (1: Alış, 4: Müşteriden İade)
            $girisAdet = StockAction::where('stokId', $device->id)
                ->whereIn('islem', [1, 4])
                ->sum('adet');
            
            // Çıkış işlemleri (2: Serviste Kullanım)
            $cikisAdet = StockAction::where('stokId', $device->id)
                ->where('islem', 2)
                ->sum('adet');
            
            // Güncel stok miktarını hesapla
            $device->current_stock_quantity = $girisAdet - $cikisAdet;
            
            // Sadece pozitif stokları toplama dahil et
            if ($device->current_stock_quantity > 0) {
                $toplamKonsinyeCihazAdedi += $device->current_stock_quantity;
            }
        }

        // Sadece stoku olan cihazları filtrele
        $konsinyeCihazlar = $konsinyeCihazlar->filter(function($device) {
            return $device->current_stock_quantity > 0;
        });

                        
        // Personel listesi al (grup kontrolü için)
            $personeller = User::where('tenant_id', $firma->id)
                            ->where('status', '1')
                            ->orderBy('name', 'asc')
                            ->get();
                            
        // Araç listesi al
            $araclar = Car::where('firma_id', $firma->id)
                        ->where('durum', '1')
                        ->orderBy('id', 'asc')
                        ->get();
                        
         // Bayi listesi al
            // $bayiler = DB::table('personeller')
            //             ->where('grup', '258')
            //             ->where('firma_id', $firma->id)
            //             ->where('durum', '1')
            //             ->orderBy('adsoyad', 'asc')
            //             ->get();
            
         return view('frontend.secure.all_services.service_stage_questions_show', 
                    compact('stage_questions', 'stage_id', 'service_id', 'firma', 'islem', 'personeller', 
                    'araclar','stoklar','toplamPersonelStokAdedi','konsinyeCihazlar','toplamKonsinyeCihazAdedi'));

        
    }

    //Servis Alt Aşamalarını veritabanına kaydederken yapılan işlemleri içeren fonksiyonlar
    public function SaveServicePlan(Request $request, $tenant_id) {
            $firma = Tenant::where('id', $tenant_id)->first();
            // TOKEN KONTROLÜ
            $token = $request->input('form_token');
            //Token varsa kontrol et, yoksa devam et (switch işlemleri için)
            if (!empty($token)) {
                $cacheKey = 'service_plan_form_token_' . $token;
                
                if (Cache::has($cacheKey)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Bu form zaten gönderildi! Lütfen bekleyin.'
                    ]);
                }
                
                Cache::put($cacheKey, true, now()->addMinutes(10));
            }



        try {
            
            $servisId = $request->input('servis');
            $gelenIslem = json_decode($request->input('gelenIslem'), true);
            $gidenIslem = $request->input('gidenIslem');

            // Servis durumu kontrolü
            $servisDurum = Service::where('firma_id',$firma->id)->where('id', $servisId)->first();
            if (!$servisDurum || $servisDurum->firma_id != $tenant_id) {
                return response()->json(['status' => 'error', 'message' => '-1']);
            }

            // Stok kontrolü
           $stokHatasiVar = $this->stokKontrolEt($request, $gelenIslem);
            if ($stokHatasiVar !== null) { 
                return response()->json(['status' => 'error', 'message' => $stokHatasiVar]);
            }

            
            $kid = Auth()->user()->user_id;
            // Servis planlama kaydı
            $planData = [
                'firma_id' => $tenant_id,
                'kid' => $kid,
                'pid' => $kid,
                'servisid' => $servisId,
                'gelenIslem' => $gelenIslem['id'],
                'gidenIslem' => $gidenIslem,
                'tarihDurum' => 0,
                'tarihKontrol' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ];

            $planId = ServicePlanning::insertGetId($planData);

            if ($planId) {
                // Log kaydı
                

                // Servis durumunu güncelle
                Service::where('id', $servisId)
                    ->update([
                        'servisDurum' => $gidenIslem,
                        'planDurum' => $planId,
                        'updated_at' => now()
                    ]);
                      
                // Aşama adını al
                $stageName = ServiceStage::find($gidenIslem)->asama ?? 'Bilinmeyen Aşama';
                ActivityLogger::logServicePlanAdded($servisId, $planId, $stageName);
                $servis = Service::find($servisId);

                // Soru cevaplarını işle
                $this->soruCevaplariniIsle($request, $servisId, $planId, $tenant_id, $gelenIslem);

                // Özel durumları işle
                $this->ozelDurumlariIsle($request, $servisId, $planId, $tenant_id, $gidenIslem, $servisDurum);
                // Eğer yeni aşama 'Konsinye Cihaz Geri Alındı' ise ve kategori 3 olanları al
                if ($gidenIslem == 272) {
                    $konsinyeCihazlar = StockAction::where('servisid', $servisId)
                        ->where('planId', '<>', $planId)
                        ->where('islem', 2)
                        ->where('firma_id', $tenant_id)
                        ->get();

                    foreach ($konsinyeCihazlar as $cihaz) {
                        $stok = Stock::find($cihaz->stokId);
                        if ($stok && $stok->urunKategori == 3) {
                            $this->geriAlConsignmentDevice($cihaz->stokId, $cihaz->adet, $servisId, $planId, $tenant_id);
                        }
                    }
                }


                // Tarih durumu kontrolü
                $this->tarihDurumuKontrolEt($tenant_id);
                $currentStage = $servis->servisDurum; // veya hangi field'dan alıyorsanız
        
                // Bu aşamaya ait alt aşamaları getir. Servis planı eklendikten sonra altAsamalar kısmını güncellemek için bunu yaptım.
                $altAsamaIDs = explode(',', $servis->asamalar->altAsamalar);
                $altAsamalar = ServiceStage::whereIn('id', $altAsamaIDs)->orderBy('asama')->get();

              
                $guncellenmisAsamaBilgisi = $servis->asamalar->asama;
                return response()->json([
                    'status' => 'success',
                    'message' => 'Servis planı başarıyla kaydedildi.',
                    'asama' => $guncellenmisAsamaBilgisi,
                    'altAsamalar' => $altAsamalar,
                ]);

            } else {
               
                
                return response()->json(['status' => 'error', 'message' => 'HATA! Servis aşama eklenemedi.']);
            }

        } catch (\Exception $e) {
            
            return response()->json(['status' => 'error', 'message' => 'Bir hata oluştu: ' . $e->getMessage()]);
        }
    }
private function stokKontrolEt(Request $request, $gelenIslem)
{
    foreach ($request->all() as $key => $value) {
        if (strpos($key, 'soru') !== false && $value == "Parca") {
             // Formdan gelen 'parca' verisini kontrol et
            if ($request->has('parca') && is_array($request->input('parca'))) {
                foreach ($request->input('parca') as $stageId => $selectedParts) {
                    foreach ($selectedParts as $stokId => $selectedStokValue) {
                        // Kullanılmak istenen adet
                        $adet = abs($request->input("adet.{$stageId}.{$stokId}", 0));

                        if ($adet > 0) {
                            // Personelin stoğunu direkt PersonelStock tablosundan kontrol et
                            $personelStok = \App\Models\PersonelStock::where('pid', $personel_id)
                                ->where('stokid', $stokId)
                                ->first();

                            // Personelin stoğu yoksa VEYA stoğu yetersizse hata döndür
                            if (!$personelStok || $personelStok->adet < $adet) {
                                $stok = \App\Models\Stock::find($stokId);
                                $mevcutAdet = $personelStok ? $personelStok->adet : 0;
                                $urunAdi = $stok ? mb_convert_case($stok->urunAdi, MB_CASE_TITLE, "UTF-8") : "Bilinmeyen Ürün";
                                
                                return "STOK HATA: '{$urunAdi}' için personel stoğunuz yetersiz. Mevcut: {$mevcutAdet}, İstenen: {$adet}.";
                            }
                        }
                    }
                }
            }
            // Parça teslim et işleminde stok seçimi zorunlu
            if ($gelenIslem == "238") {
                $stokSecildi = false;
                if ($request->has('parca') && is_array($request->input('parca'))) {
                    foreach ($request->input('parca') as $stageId => $selectedParts) {
                        if (!empty($selectedParts)) {
                            $stokSecildi = true;
                            break;
                        }
                    }
                }

                if (!$stokSecildi) {
                    return "STOKHATA: Parça Teslim Ederken Stok Seçmeni Zorunludur.";
                }
            }
        }

        if (strpos($key, 'soru') !== false && $value == "Konsinye Cihaz") {
            if ($request->has('konsinye_cihaz') && is_array($request->input('konsinye_cihaz'))) {
                foreach ($request->input('konsinye_cihaz') as $stageId => $selectedConsignments) {
                    foreach ($selectedConsignments as $consignmentId => $selectedConsignmentValue) {
                        $consignmentAdet = abs($request->input("konsinye_adet.{$stageId}.{$consignmentId}", 0));
                        if ($consignmentAdet > 0) { // Sadece adet girildiyse kontrol yap
                            $girisAdet = StockAction::where('stokId', $consignmentId)
                                ->whereIn('islem', [1, 4]) // 1: Alış, 4: Müşteriden İade (Konsinye için giriş)
                                ->sum('adet');
                            $cikisAdet = StockAction::where('stokId', $consignmentId)
                                ->where('islem', 2) // 2: Serviste Kullanım (Konsinye için çıkış)
                                ->sum('adet');

                            $currentConsignmentStock = $girisAdet - $cikisAdet;
                            if ($consignmentAdet > $currentConsignmentStock) {
                                $consignmentStock = Stock::where('id', $consignmentId)->first();
                                return "STOKHATA: " . mb_convert_case($consignmentStock->urun_adi, MB_CASE_TITLE, "UTF-8") . " Konsinye Cihaz Stok Adedi Yetersizdir.";
                            }
                        }
                    }
                }
            }
        }
    }
    return null;
}

private function soruCevaplariniIsle(Request $request, $servisId, $planId, $tenantId, $gelenIslem)
{
         if ($request->has('soru')) {
            foreach ($request->input('soru') as $soruId => $cevap) {
                if ($cevap == "Parca") {
                    $this->parcaIslemleriniYap($request, $servisId, $planId, $tenantId, $soruId, $gelenIslem);
                } elseif ($cevap == "Konsinye Cihaz") {
                    if ($request->has("konsinye_cihaz.{$soruId}")) {
                    foreach ($request->input("konsinye_cihaz.{$soruId}") as $konsinyeId => $value) {
                        $adet = abs($request->input("konsinye_adet.{$soruId}.{$konsinyeId}", 1));

                        if ($adet > 0) { // Sadece adet girildiyse işlem yap
                            $this->useConsignmentDevice($konsinyeId, $adet, $servisId, $planId, $tenantId,$soruId);
                        }
                    }
                }
                } else {
                    $kid = Auth()->user()->user_id;
                    if (is_array($cevap)) {
                        // Çoklu cevap (checkbox)
                        foreach ($cevap as $cevapItem) {
                            ServiceStageAnswer::create([
                                'firma_id' => $tenantId,
                                'kid' => $kid,
                                'servisid' => $servisId,
                                'planid' => $planId,
                                'soruid' => $soruId,
                                'cevap' => $cevapItem,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    } else {
                        // Tekli cevap
                        ServiceStageAnswer::create([
                            'firma_id' => $tenantId,
                            'kid' => $kid,
                            'servisid' => $servisId,
                            'planid' => $planId,
                            'soruid' => $soruId,
                            'cevap' => $cevap,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }
        }
    }

private function useConsignmentDevice($stokId, $adet, $servisId, $planId, $tenantId, $soruId)
{
    // Stok kaydını al
    $stok = Stock::where('id', $stokId)->first();

    if (!$stok || $stok->urunKategori != 3) {
        throw new \Exception("Bu stok bir konsinye cihaz değildir.");
    }

    // Önce aynı planId, stokId için eski çıkış kayıtlarını sil
    StockAction::where('firma_id', $tenantId)
        ->where('stokId', $stokId)
        ->where('planId', $planId)
        ->where('islem', 2)  // çıkış işlemi
        ->delete();


    //StokAction ile çıkış işlemini kaydet
    StockAction::create([
        'firma_id' => $tenantId,
        'kid' => auth()->id(),
        'pid' => auth()->id(),
        'stokId' => $stokId,
        'islem' => 2, // Konsinye cihaz kullanımı
        'servisid' => $servisId,
        'adet' => $adet,
        'planId' => $planId,
        'depo' => 1,
        'created_at' => now(),
        'updated_at' => now()
    ]);

    //ServiceStageAnswer kaydı
    ServiceStageAnswer::create([
        'firma_id' => $tenantId,
        'servisid' => $servisId,
        'planid' => $planId,
        'soruid' => $soruId,
        'cevap' => "{$stokId}---{$adet}", 
        'kid' =>  auth()->user()->user_id,
        'created_at' => now(),
        'updated_at' => now()
    ]);
}
private function geriAlConsignmentDevice($stokId, $adet, $servisId, $planId, $tenantId, $soruId = null)
{

    // Yeni giriş işlemi
    StockAction::create([
        'firma_id' => $tenantId,
        'kid' => auth()->id(),
        'pid' => auth()->id(),
        'stokId' => $stokId,
        'islem' => 4, // Geri alma
        'servisid' => $servisId,
        'adet' => $adet,
        'planId' => $planId,
        'depo' => 1,
        'created_at' => now(),
        'updated_at' => now()
    ]);


    // Cevap olarak kaydet
    if ($soruId) {
        ServiceStageAnswer::create([
            'firma_id' => $tenantId,
            'servisid' => $servisId,
            'planid' => $planId,
            'soruid' => $soruId,
            'cevap' => $cevapText,
            'kid' => auth()->user()->user_id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}

private function parcaIslemleriniYap(Request $request, $servisId, $planId, $tenantId, $soruId, $gelenIslem)
{
    $stokCevapArray = [];
    if ($request->has("parca.{$soruId}")) {
        foreach ($request->input("parca.{$soruId}") as $stokId => $value) {
            $adet = abs($request->input("adet.{$soruId}.{$stokId}", 1));
            $stokCevapArray[] = "{$stokId}---{$adet}";

            // GÜNCELLEME: Gelen işlem nesnesinin ID'sini kontrol et
            if (isset($gelenIslem->id) && $gelenIslem->id == 238) { // "Parça Teslim Et" aşaması
                $this->parcaTeslimEt($stokId, $adet, $servisId, $planId, $tenantId);
            } else {
                // Diğer tüm durumlar parçanın serviste kullanıldığı anlamına gelir.
                $this->parcaKullan($stokId, $adet, $servisId, $planId, $tenantId);
            }
        }
    }

    $stokCevap = implode(', ', $stokCevapArray);

    if (!empty($stokCevap)) {
        \App\Models\ServiceStageAnswer::create([
            'firma_id' => $tenantId,
            'kid'      => auth()->user()->user_id, 
            'servisid' => $servisId,
            'planid'   => $planId,
            'soruid'   => $soruId,
            'cevap'    => $stokCevap, 
        ]);
    }
}
 private function parcaTeslimEt($stokId, $adet, $servisId, $planId, $tenantId)
    {
        // Önceki planı bul
        $sonPlan = ServicePlanning::where('servisid', $servisId)
            ->orderBy('id', 'desc')
            ->skip(1)
            ->first();

        // Personel stok ekle/güncelle
            $perStok = PersonelStock::where('pid', $sonPlan->pid ?? $sonPlan->kid ?? auth()->id()) 
                ->where('stokid', $stokId)
                ->first();

                if ($perStok) {
                    PersonelStock::where('id', $perStok->id)
                        ->update([
                            'adet' => $perStok->adet + $adet,
                            'updated_at' => now()
                        ]);
                    $perStokId = $perStok->id;
                } else {
                    $perStokId = PersonelStock::insertGetId([
                    'firma_id' => $tenantId, 
                    'pid' => $sonPlan->pid ?? $sonPlan->kid ?? auth()->id(),
                    'stokid' => $stokId,
                    'adet' => $adet,
                    'created_at' => now(),
                    'updated_at' => now()
                    ]);
                }


        // Stok hareketi kaydet
        StockAction::create([
            'firma_id' => $tenantId,
            'stokId' => $stokId,
            'islem' => 3,
            'adet' => $adet,
            'servisid' => $servisId,
            'fiyat' => 0,
            'fiyatBirim' => 1,
            'planId' => $planId,
             //'personel_stok_id' => $perStokId,
            'personel' => $sonPlan->user_id,
            'pid' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        
    }

   private function parcaKullan($stokId, $adet, $servisId, $planId, $tenantId)
{
    $personel_id = auth()->user()->user_id;
    $stok = \App\Models\Stock::find($stokId);

    // Fiyat bilgisi stoktan alınmalı, yoksa 0 kabul edilmeli.
    $fiyat = $stok ? ($adet * $stok->fiyat) : 0;

    // Stok Hareketi Kaydet
    $stokHareket = \App\Models\StockAction::create([
        'firma_id' => $tenantId,
        'stokId'   => $stokId,
        'islem'    => 2, // Serviste Kullanım
        'adet'     => $adet,
        'pid'      => $personel_id, // İşlemi yapan personel
        'kid'      => $personel_id, // Stoğu düşen personel
        'servisid' => $servisId,
        'planId'   => $planId,
        'fiyat'    => $fiyat, // Hesaplanan fiyat
    ]);

    // Personel Stoğundan Düşür
    $perStok = \App\Models\PersonelStock::where('pid', $personel_id)
        ->where('stokid', $stokId)
        ->first();

    if ($perStok) {
        $yeniAdet = $perStok->adet - $adet;
        if ($yeniAdet <= 0) {
            // Stok bittiyse kaydı sil
            $perStok->delete();
        } else {
            // Stok kaldıysa adedi güncelle
            $perStok->adet = $yeniAdet;
            $perStok->save();
        }
    }

    //Finansal Kayıtları Oluştur 
    $servisDurum = \App\Models\Service::find($servisId);
    $stokIslemTuru = \App\Models\PaymentType::where('parca', '1')->first();

    if ($servisDurum && $stokIslemTuru) {
        \App\Models\CashTransaction::create([
            'firma_id' => $tenantId,
            'kid' => $personel_id,
            'pid' => $personel_id,
            'odemeYonu' => 2,
            'odemeSekli' => 1,
            'odemeTuru' => $stokIslemTuru->id,
            'odemeDurum' => 1,
            'fiyat' => $fiyat,
            'aciklama' => "Stok ID: {$stokId} (" . ($stok->urunAdi ?? 'Bilinmeyen') . ")",
            'servis' => $servisId,
            'stokIslem' => $stokHareket->id,
        ]);

        \App\Models\ServiceMoneyAction::create([
            'firma_id' => $tenantId,
            'servisid' => $servisId,
            'odemeSekli' => 1,
            'odemeDurum' => 1,
            'fiyat' => $fiyat,
            'aciklama' => "Stok ID: {$stokId} (" . ($stok->urunAdi ?? 'Bilinmeyen') . ")",
            'odemeYonu' => 2,
            'stokIslem' => $stokHareket->id,
            'kid' => $personel_id,
            'pid' => $personel_id,
        ]);
    }
}
    private function ozelDurumlariIsle(Request $request, $servisId, $planId, $tenantId, $gidenIslem, $servisDurum)
    {
        // Parça Teslim Et (259) özel durumu
        if ($gidenIslem == "259") {
            $this->parcaTeslimEtOzelDurum($servisId, $planId, $tenantId);
        }

        // Diğer özel durumlar (254, 267, 268)
        if ($gidenIslem == "254") {
            $planlama = ServicePlanning::where('servisid', $servisId)
                ->orderBy('id', 'desc')
                ->skip(1)
                ->first();

            if ($planlama && $planlama->gidenIslem == "255") {
                ServicePlanning::where('id', $planlama->id)->delete();
            }
        }

        if ($gidenIslem == "267") {
            $this->musteriIadeEdildiIslem($request, $servisId, $planId, $tenantId, $servisDurum);
        }

        if ($gidenIslem == "268") {
            $this->fiyatYukseltildiIslem($request, $servisId, $planId, $tenantId, $servisDurum);
        }
    }

    private function parcaTeslimEtOzelDurum($servisId, $planId, $tenantId)
    {
        $planlama = ServicePlanning::where('servisid', $servisId)
            ->orderBy('id', 'desc')
            ->skip(1)
            ->first();

        // Yeni plan oluştur
        $yeniPlanId = ServicePlanning::insertGetId([
            'firma_id' => $tenantId,
            'servisid' => $servisId,
            'gelenIslem' => 259,
            'gidenIslem' => $planlama->gelen_islem,
            'kid' => auth()->id(),
            'pid' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Servis durumunu güncelle
        Service::where('id', $servisId)
            ->update([
                'servisDurum' => $planlama->gelen_islem,
                'planDurum' => $yeniPlanId,
                'updated_at' => now()
            ]);

        // Önceki cevapları kopyala
        $planlama2 = ServicePlanning::where('servisid', $servisId)
            ->orderBy('id', 'desc')
            ->skip(2)
            ->first();

        $cevaplar = ServiceStageAnswer::where('planid', $planlama2->id)
            ->orderBy('id', 'asc')
            ->get();

        foreach ($cevaplar as $cevap) {
            $soru = StageQuestion::where('id', $cevap->soru_id)->first();
            $cevapText = ($soru->cevap == "[Tarih]") ? now()->format('d/m/Y') : $cevap->cevap;
            
            ServiceStageAnswer::insert([
                'firma_id' => $tenantId,
                'servisid' => $servisId,
                'planid' => $yeniPlanId,
                'soruid' => $cevap->soru_id,
                'cevap' => $cevapText,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    private function musteriIadeEdildiIslem(Request $request, $servisId, $planId, $tenantId, $servisDurum)
    {
        $sorular = $request->input('soru'); // tüm dizi olarak al
        $fiyat = $sorular['22'] ?? null;
        $aciklama = $sorular['21'] ?? null;

        //Servis para hareketi
        $paraHareketId = ServiceMoneyAction::insertGetId([
            'firma_id' => $tenantId,
            'kid' => auth()->id(),
            'pid' => auth()->id(),
            'servisid' => $servisId,
            'odemeYonu' => 2,
            'odemeSekli' => 1,
            'odemeDurum' => 1,
            'fiyat' => $fiyat,
            'aciklama' => $aciklama,
            'planIslem' => $planId,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        //Kasa hareketi
        CashTransaction::create([
            'firma_id' => $tenantId,
            'kid' => auth()->id(),
            'pid' => auth()->id(),
            'odemeYonu' => 2,
            'odemeSekli' => 1,
            'odemeTuru' => 14,
            'odemeDurum' => 1,
            'fiyat' => $fiyat,
            'fiyatBirim' => 1,
            'aciklama' => $aciklama,
            'servis' => $servisId,
            'personel' => auth()->user()->user_id,
            'marka' => $servisDurum->cihazMarka,
            'cihaz' => $servisDurum->cihazTur,
            'servisIslem' => $paraHareketId,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function fiyatYukseltildiIslem(Request $request, $servisId, $planId, $tenantId, $servisDurum)
    {
        $sorular = $request->input('soru'); // tüm dizi olarak al
        $fiyat = $sorular['12'] ?? null;
        $aciklama = $sorular['11'] ?? null;

        //Servis para hareketi
        $paraHareketId = ServiceMoneyAction::insertGetId([
            'firma_id' => $tenantId,
            'kid' => auth()->id(),
            'pid' => auth()->id(),
            'servisid' => $servisId,
            'odemeYonu' => 1,
            'odemeSekli' => 1,
            'odemeDurum' => 2,
            'fiyat' => $fiyat,
            'aciklama' => $aciklama,
            'planIslem' => $planId,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        //Kasa hareketi
        CashTransaction::create([
            'firma_id' => $tenantId,
            'kid' => auth()->id(),
            'pid' => auth()->id(),
            'odemeYonu' => 1,
            'odemeSekli' => 1,
            'odemeTuru' => 2,
            'odemeDurum' => 2,
            'fiyat' => $fiyat,
            'fiyatBirim' => 1,
            'aciklama' => $aciklama,
            'servis' => $servisId,
            'personel' => auth()->user()->user_id,
            'marka' => $servisDurum->cihazMarka,
            'cihaz' => $servisDurum->cihazTur,
            'servisIslem' => $paraHareketId,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function tarihDurumuKontrolEt($tenant_id)
    {
        // Tarih durumu kontrolü - performans optimizasyonu
        $servisPlanlar = ServicePlanning::where('firma_id', $tenant_id)->where('tarihKontrol', '0')
            ->get();

        foreach ($servisPlanlar as $servisRow) {
            $tarihDurum = "0";
            $cevaplar = ServiceStageAnswer::where('firma_id', $tenant_id)->where('planid', $servisRow->id)
                ->get();

            foreach ($cevaplar as $cevapRow) {
                $soru = StageQuestion::where('id', $cevapRow->soruid)
                    ->first();

                if ($soru && $soru->cevapTuru == "[Tarih]") {
                    $tarihDurum = "1";
                    break;
                }
            }

            ServicePlanning::where('firma_id', $tenant_id)->where('id', $servisRow->id)
                ->update([
                    'tarihDurum' => $tarihDurum,
                    'tarihKontrol' => "1",
                    'updated_at' => now()
                ]);
        }

        // Cevap text güncelleme
        $cevaplar = ServiceStageAnswer::where('firma_id', $tenant_id)->where('cevapText', null)
            ->get();

        foreach ($cevaplar as $cevapRow) {
            $soru = StageQuestion::where('id', $cevapRow->soruid)
                ->first();

            if ($soru) {
                ServiceStageAnswer::where('firma_id', $tenant_id)->where('id', $cevapRow->id)
                    ->update([
                        'cevapText' => $soru->cevapTuru,
                        'updated_at' => now()
                    ]);
            }
        }
    }
    //Servis Alt Aşamalarının veritabanına kaydını yapan fonksiyonların SONU

    //Servis Alt Aşamalarını silme fonksiyonu
    public function DeleteServicePlan($tenant_id, $planid) {
        $servisPlanID = $planid;

        $plan = ServicePlanning::where('firma_id', $tenant_id)->findOrFail($servisPlanID);
        $servis = Service::where('firma_id', $tenant_id)->findOrFail($plan->servisid);
        $cevaplar = ServiceStageAnswer::where('planid', $servisPlanID)->get();

        $kullanici = auth()->user();

        try {
            // alt bayi işlemi silme (gidenIslem == 264)
            if ($plan->gidenIslem == 264) {
                // bayi ve ilgili tüm veriler silinir
                // aynı mantıkla çalıştırılır
            }

            // stok silme işlemi (gidenIslem == 259)
            if ($plan->gidenIslem == 259) {
                $stok_cevap = ServiceStageAnswer::where('firma_id', $tenant_id)->where('planid', $plan->id)->first();
                $stoklar = explode(', ', $stok_cevap->cevap);

                foreach ($stoklar as $stokCevap) {
                    [$stokID, $adet] = explode('---', $stokCevap);
                    $stok = StockAction::where('stokId', $stokID)->where('planId', $plan->id)->first();
                    // $perStok = PersonelStok::find($stok->perStokID);
                    // $perStok->update(['adet' => $perStok->adet - $adet]);
                    // $stok->delete();
                }
            }

            // ödeme silme işlemleri
            if (in_array($plan->gidenIslem, [267, 268])) {
                $servisPara = ServiceMoneyAction::where('planIslem', $servisPlanID)->first();
                if ($servisPara) {
                    CashTransaction::where('servisIslem', $servisPara->id)->delete();
                    $servisPara->delete();
                }
            }

            // stokları geri al
            $stokHareketleri = StockAction::where('planId', $servisPlanID)->get();

            foreach ($stokHareketleri as $stok) {
                // Personel stoğunu bul ve artır
                $personelStok = PersonelStock::where('pid', $plan->pid)
                                            ->where('stokid', $stok->stokId)
                                            ->first();
                
                if ($personelStok) {
                    $personelStok->increment('adet', $stok->adet);
                } else {
                    // Eğer personel stoğunda yoksa, yeni kayıt oluştur
                    PersonelStock::create([
                        'firma_id' => $tenant_id,
                        'kid' => $plan->kid ?? null,
                        'pid' => $plan->pid,
                        'stokid' => $stok->stokId,
                        'adet' => $stok->adet,
                        'tarih' => now()
                    ]);
                }

                $stok->delete();
            }  

            $stageName = ServiceStage::find($plan->gidenIslem)->asama ?? 'Bilinmeyen Aşama';
            ActivityLogger::logServicePlanDeleted($plan->servisid, $planid, $stageName);

            // cevapları sil
            ServiceStageAnswer::where('planid', $servisPlanID)->delete();

            $plan->delete();

            // son plan mıydı? servisDurum güncelle
            if ($servis->servisDurum == $plan->gidenIslem) {
                $sonPlan = ServicePlanning::where('servisid', $plan->servisid)->latest()->first();
                if ($sonPlan) {
                    $servis->update([
                        'servisDurum' => $sonPlan->gidenIslem,
                        'planDurum' => $sonPlan->id,
                    ]);
                } else {
                    $ilkAsama = ServiceStage::where('ilkServis', 1)->first();
                    $servis->update([
                        'servisDurum' => $ilkAsama->id,
                        'planDurum' => 0,
                    ]);
                }
            }

            // Bu aşamaya ait alt aşamaları getir. Servis planı eklendikten sonra altAsamalar kısmını güncellemek için bunu yaptım.
                $altAsamaIDs = explode(',', $servis->asamalar->altAsamalar);
                $altAsamalar = ServiceStage::whereIn('id', $altAsamaIDs)->orderBy('asama')->get();

              
                $guncellenmisAsamaBilgisi = $servis->asamalar->asama;
                return response()->json([
                    'asama' => $guncellenmisAsamaBilgisi,
                    'altAsamalar' => $altAsamalar,
                ]);

            $guncellenmisAsamaBilgisi = $servis->asamalar->asama;
            return response()->json([
                'asama' => $guncellenmisAsamaBilgisi // örn: $servis->asama->asama
            ]);

        } catch (\Exception $e) {
            return response("HATA! Servis Plan Silinemedi.", 500);
        }
    }

      //Servis planı düzenleme viewını açan fonksiyon
    public function EditServicePlan($tenant_id, $planid) {
        $firma = Tenant::where('id', $tenant_id)->first();
        
        if (!$firma) {
            return response()->json(['error' => 'Firma bulunamadı'], 404);
        }

        // Servis planı bilgilerini al
        $servisPlan = ServicePlanning::where('id', $planid)
            ->where('firma_id', $tenant_id)
            ->first();

        if (!$servisPlan) {
            return response()->json(['error' => 'Plan bulunamadı'], 404);
        }

        // Plan cevaplarını al
        $planCevaplar = ServiceStageAnswer::where('planid', $planid)
            ->orderBy('id', 'ASC')
            ->get();

        // Servis bilgilerini al
        $servis = Service::find($servisPlan->servisid);

        // Personelleri al
        $personellerAll = User::where('tenant_id', $tenant_id)
            ->where('status', '1')
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', ['Admin', 'Super Admin']);
            })
            ->orderBy('name', 'ASC')
            ->get();

// Stokları al - sadece yetkili roller için
$personel_id = Auth::user()->user_id;
$stoklar = collect();

// Yetkili rolleri kontrol et
$yetkiliRoller = ['Teknisyen', 'Teknisyen Yardımcısı', 'Atölye Ustası', 'Atölye Çırak'];
$kullaniciYetkili = Auth::user()->hasAnyRole($yetkiliRoller);

if ($servisPlan->gidenIslem != "259" && $kullaniciYetkili) {
    $stoklar = PersonelStock::where('firma_id', $tenant_id)
        ->where('pid', $personel_id)
        ->with('stok')
        ->orderBy('id', 'ASC')
        ->get()
        ->filter(function($item) {
            return $item->stok !== null;
        });
}

// Toplam stok - sadece yetkili roller için
$toplamPersonelStokAdedi = 0;
if ($kullaniciYetkili) {
    $toplamPersonelStokAdedi = PersonelStock::where('firma_id', $tenant_id)
        ->where('pid', $personel_id)
        ->sum('adet');
}
         // Konsinye Cihaz Stok İşlemleri
        $konsinyeKategoriId = 3; // İkinci fonksiyonda olduğu gibi konsinye kategori ID'si
        $konsinyeCihazlar = Stock::where('firma_id', $tenant_id)
            ->where('urunKategori', $konsinyeKategoriId)
            ->get();

        $toplamKonsinyeCihazAdedi = 0;

        foreach ($konsinyeCihazlar as $device) {
            // Giriş işlemleri (1: Alış, 4: Müşteriden İade)
            $girisAdet = StockAction::where('stokId', $device->id)
                ->whereIn('islem', [1, 4])
                ->sum('adet');

            // Çıkış işlemleri (2: Serviste Kullanım)
            $cikisAdet = StockAction::where('stokId', $device->id)
                ->where('islem', 2)
                ->sum('adet');

            // Güncel stok miktarını hesapla
            $device->current_stock_quantity = $girisAdet - $cikisAdet;

            // Sadece pozitif stokları toplama dahil et
            if ($device->current_stock_quantity > 0) {
                $toplamKonsinyeCihazAdedi += $device->current_stock_quantity;
            }
        }

        // Sadece stoku olan cihazları filtrele
        $konsinyeCihazlar = $konsinyeCihazlar->filter(function($device) {
            return $device->current_stock_quantity > 0;
        });   
        
        // Kullanıcı bilgilerini al
        $kullanici = auth()->user();

        return view('frontend.secure.all_services.edit_service_plan', compact(
            'servisPlan',
            'planCevaplar', 
            'servis',
            'personellerAll',
            'stoklar',
            'toplamPersonelStokAdedi',
            'kullanici',
            'tenant_id',
            'konsinyeCihazlar',
            'toplamKonsinyeCihazAdedi'

        ));
    }
    //servis planı düzenleme viewını açma fonksiyonu SONU
    
//Servis plan aşama düzenleme güncelleme fonksiyonu
public function UpdateServicePlan(Request $request, $tenant_id)
{
    $planid = $request->input('planid');

    try {
        $servisPlan = ServicePlanning::where('id', $planid)
            ->where('firma_id', $tenant_id)
            ->first();

        if (!$servisPlan) {
            return response()->json(['error' => 'Plan bulunamadı'], 404);
        }

        if ($request->has('planIslemiYapan')) {
            $servisPlan->pid = $request->input('planIslemiYapan');
            $servisPlan->save();
        }

        $planCevaplar = ServiceStageAnswer::where('firma_id', $tenant_id)
            ->where('planid', $planid)
            ->get();
       
        foreach ($planCevaplar as $cevap) {
            $soruKey = 'soru' . $cevap->id;
           
            if ($request->has($soruKey)) {
                $yeniCevap = $request->input($soruKey);
               
                // PARÇA VE KONSİNYE İÇİN MEVCUT CEVABI KORU
                if ($yeniCevap == 'Parca' || $yeniCevap == 'Konsinye Cihaz') {
                    // Cevap değişmez, mevcut parça/konsinye seçimi korunur
                    continue;
                } else {
                    // Diğer cevaplar normal şekilde güncellenir
                    $cevap->cevap = $yeniCevap;
                }
               
                $cevap->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Plan başarıyla güncellendi',
            'servis_id' => $servisPlan->servisid
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Güncelleme sırasında hata oluştu: ' . $e->getMessage()
        ], 500);
    }
}


// private function processParcaSelection(Request $request, $tenant_id, $eskiCevap = null)
// {
//     $planid = $request->input('planid');
//     $servisPlan = ServicePlanning::where('id', $planid)
//         ->where('firma_id', $tenant_id)
//         ->first();

//     if (!$servisPlan) {
//         throw new \Exception("Servis planı bulunamadı");
//     }
   
//     $servisid = $servisPlan->servisid;
   
//     // ÖNCEDEKİ PARÇALARI AL
//     $mevcutParcalar = [];
//     if ($eskiCevap && !empty($eskiCevap)) {
//         $eskiParcaListesi = explode(', ', $eskiCevap);
//         foreach ($eskiParcaListesi as $parca) {
//             if (strpos($parca, '---') !== false) {
//                 list($stokId, $adet) = explode('---', $parca);
//                 $mevcutParcalar[$stokId] = $adet;
//             }
//         }
//     }
   
//     // YENİ SEÇİLEN PARÇALARI AL
//     $yeniSecimler = [];
//     foreach ($request->all() as $key => $value) {
//         if (strpos($key, 'stokCheck') !== false) {
//             $stokId = (int) filter_var($key, FILTER_SANITIZE_NUMBER_INT);
//             $adet = abs($request->input("stokAdet{$stokId}", 1));
           
//             $yeniSecimler[$stokId] = $adet;
           
//             // Sadece yeni eklenen parçalar için işlem yap (daha önce yoksa)
//             if (!isset($mevcutParcalar[$stokId])) {
//                 if ($servisPlan->gelenIslem == "238") {
//                     $this->parcaTeslimEt($stokId, $adet, $servisid, $planid, $tenant_id);
//                 } else {
//                     $this->parcaKullan($stokId, $adet, $servisid, $planid, $tenant_id);
//                 }
//             }
//         }
//     }
   
//     // ESKİ VE YENİ PARÇALARI BİRLEŞTİR
//     // Eğer form'dan hiç checkbox gönderilmediyse, eski parçaları koru
//     if (empty($yeniSecimler) && !empty($mevcutParcalar)) {
//         $tumParcalar = $mevcutParcalar;
//     } else {
//         // Yeni seçim varsa, eski + yeni birleştir (yeni olanlar öncelikli)
//         $tumParcalar = array_merge($mevcutParcalar, $yeniSecimler);
//     }
   
//     // CEVAP FORMATINI OLUŞTUR
//     $stokCevap = [];
//     foreach ($tumParcalar as $stokId => $adet) {
//         $stokCevap[] = "{$stokId}---{$adet}";
//     }

//     return implode(', ', $stokCevap);
// }

private function processKonsinyeSelection(Request $request, $tenant_id)
{
    $konsinyeCevap = [];
    $planid = $request->input('planid');
    $servisPlan = ServicePlanning::where('id', $planid)
        ->where('firma_id', $tenant_id)
        ->first();

    if (!$servisPlan) {
        throw new \Exception("Servis planı bulunamadı");
    }
    $servisid = $servisPlan->servisid;

    foreach ($request->all() as $key => $value) {
        if (strpos($key, 'konsinyeCheck') !== false) {
            $stokId = (int) filter_var($key, FILTER_SANITIZE_NUMBER_INT);
            $adet = abs($request->input("konsinyeAdet{$stokId}", 1));

            // konsinyeCevap dizisine ekle
            $konsinyeCevap[] = "{$stokId}---{$adet}";

            // Kullanım mı teslim mi kontrol et
            if ($servisPlan->gelenIslem == "238") {
                $this->konsinyeTeslimEt($stokId, $adet, $servisid, $planid, $tenant_id);
            } else {
                $this->konsinyeKullan($stokId, $adet, $servisid, $planid, $tenant_id);
            }
        }
    }
    return implode(', ', $konsinyeCevap); // View'da input name="soru..." olan alanın cevabına atanır
}
private function konsinyeKullan($stokId, $adet, $servisId, $planId, $tenantId)
{
    // Stok bilgilerini al
    $stok = Stock::where('id', $stokId)->first();
    if (!$stok) {
        throw new \Exception("Stok bulunamadı (ID: $stokId)");
    }
    $fiyat = $adet * $stok->fiyat;

    // Stok hareketi kaydet (islem=2: Serviste Kullanım)
    $stokHareketId = StockAction::insertGetId([
        'firma_id'    => $tenantId,
        'kid'         => auth()->id(),
        'stokId'      => $stokId,
        'islem'       => 2, // Konsinye kullanımı
        'servisid'    => $servisId,
        'depo'        => 1,
        'adet'        => $adet,
        'fiyat'       => $fiyat,
        'fiyatBirim'  => 1,
        'planId'      => $planId,
        'created_at'  => now(),
        'updated_at'  => now()
    ]);

    // Servis bilgilerini al
    $servisDurum = Service::find($servisId);

    // Kasa hareketi tipi (parça gibi işleniyor)
    $stokIslem = PaymentType::where('parca', '1')->first();

}
    //Servis Aşamalarının servis-information blade'inde görüntülenmesini sağlayan ajaxı çalıştıran fonksionlar
    public function getServiceStageHistory($tenant_id, $servisId)
    {
        $servis = Service::with(['asamalar','users','cevaplar','plans'])->where('firma_id', $tenant_id)->findOrFail($servisId);
    
        $data = [
            'acilIslem' => null,
            'notlar' => [],
            'eskiIslemler' => [],
            'paraHareketleri' => []
        ];
        
        // Acil durum kontrolü
        if ( $servis->acil != 0) {
            $acilIslem = $servis->acil;
                
            if ($acilIslem) {
                $data['acilIslem'] = [
                    'tarih' => $servis->updated_at->format('d/m/Y'),
                    'personel' => auth()->user()->name ?? ''
                ];
            }
        }
        
        // Operatör notları - with kullan
        $notlar = ServiceOptNote::with('user:user_id,name')
            ->where('firma_id', $tenant_id)
            ->where('servisid', $servisId)
            ->orderBy('id', 'desc')
            ->get();
            
        foreach ($notlar as $not) {
            $data['notlar'][] = [
                'tarih' => $not->created_at->format('d/m/Y H:i'),
                'personel' => $not->user->name ?? '',
                'aciklama' => $not->aciklama
            ];
        }
        
        // Eski işlemler - nested with kullan
        $eskiIslemler = ServicePlanning::with([
            'user:user_id,name',
            'serviceStage:id,asama',
            'answers.question:id,soru,cevapTuru'
        ])->where('servisid', $servisId)
        ->orderBy('created_at', 'desc')
        ->get();
        
        $eklenenPara = [];
        
        foreach ($eskiIslemler as $eskiIslem) {
            $aciklamalar = [];
            foreach ($eskiIslem->answers as $cevap) {
                if (!empty($cevap->cevap)) {
                    $aciklamalar[] = $this->formatCevap($cevap->question, $cevap->cevap);
                }
            }
            
            $islemData = [
                'id' => $eskiIslem->id,
                'tarih' => $eskiIslem->created_at->format('d/m/Y H:i'),
                'personel' => $eskiIslem->user->name ?? '',
                'asama' => $eskiIslem->serviceStage->asama ?? '',
                'aciklamalar' => $aciklamalar,
                'pid' => $eskiIslem->pid,
            ];
            
            $data['eskiIslemler'][] = $islemData;
            
            // Para hareketleri için tarih
            $tarih = $eskiIslem->created_at->format('Y-m-d');
            $paraHareketleri = ServiceMoneyAction::with([
                'personel:user_id,name',
                'odemeSekliRelation:id,odemeSekli'
            ])->where('firma_id', $tenant_id)
            ->where('servisid', $servisId)
            ->where('odemeYonu', 1)
            ->whereDate('created_at', $tarih)
            ->get();
                
            foreach ($paraHareketleri as $paraIslem) {
                if (!in_array($paraIslem->id, $eklenenPara)) {
                    $eklenenPara[] = $paraIslem->id;
                    $data['eskiIslemler'][] = $this->formatParaHareketi($paraIslem);
                }
            }
        }
        
        // Kalan para hareketleri
        $kalanParaHareketleri = ServiceMoneyAction::with([
            'personel:user_id,name',
            'odemeSekliRelation:id,odemeSekli'
        ])->where('firma_id', $tenant_id)
        ->where('servisid', $servisId)
        ->where('odemeYonu', 1)
        ->whereNotIn('id', $eklenenPara)
        ->orderBy('id', 'desc')
        ->get();
            
        foreach ($kalanParaHareketleri as $paraIslem) {
            $data['paraHareketleri'][] = $this->formatParaHareketi($paraIslem);
        }
        
        return response()->json($data);
    }
    
    private function formatCevap($soru, $cevap)
    {
        if (!$soru) return '';
        
        $result = '<strong>' . $soru->soru . '</strong>: ';
        
        if (strpos($soru->cevapTuru, 'Grup') !== false) {
            $personel = User::find($cevap);
            $result .= $personel->name ?? '';
        } elseif ($soru->cevapTuru == '[Arac]') {
            $arac = Car::find($cevap);
            $result .= $arac->arac ?? '';
        } elseif ($soru->cevapTuru == '[Parca]') {
            $parcalar = explode(', ', $cevap);
            $parcaMetinler = [];
            foreach ($parcalar as $parca) {
                $parcaData = explode('---', $parca);
                if (count($parcaData) >= 2) {
                    $parcaId = $parcaData[0];
                    $adet = $parcaData[1];
                    $stok = Stock::find($parcaId);
                    if ($stok) {
                        $parcaMetinler[] = $stok->urunAdi . ' (' . $adet . ')';
                    }
                }
            }
            $result .= implode(', ', $parcaMetinler);
        } elseif ($soru->cevapTuru == '[Konsinye Cihaz]') {
            $parcalar = explode(', ', $cevap);
            $parcaMetinler = [];
            foreach ($parcalar as $parca) {
                $parcaData = explode('---', $parca);
                if (count($parcaData) >= 2) {
                    $parcaId = $parcaData[0];
                    $adet = $parcaData[1];
                    $stok = Stock::find($parcaId);
                    if ($stok) {
                        $parcaMetinler[] = $stok->urunAdi . ' (' . $adet . ')';
                    }
                }
            }
            $result .= implode(', ', $parcaMetinler);
        }
        elseif ($soru->cevapTuru == '[Bayi]') {
            $bayi = User::find($cevap);
            $result .= $bayi->name ?? '';
        } else {
            $result .= $cevap;
        }
        
        return $result;
    }
    
    private function formatParaHareketi($paraIslem)
    {
        $personel = User::find($paraIslem->pid);
        $odemeSekli = PaymentMethod::find($paraIslem->odemeSekli);
        
        $odemeDurum = '';
        if ($paraIslem->odemeDurum == 2) {
            $odemeDurum = '<span style="color:red">Beklemede</span>';
        } elseif ($paraIslem->odemeDurum == 1) {
            $odemeDurum = '<span style="color:green">Tamamlandı</span>';
        }
        
        $odemeYonu = '';
        if ($paraIslem->odemeYonu == 2) {
            $odemeYonu = '<i style="color: red;">Gider - ' . ($odemeSekli->odemeSekli ?? '') . '</i>';
        } elseif ($paraIslem->odemeYonu == 1) {
            $odemeYonu = '<i style="color: green;">Gelir - ' . ($odemeSekli->odemeSekli ?? '') . '</i>';
        }
        
        $fiyat = number_format($paraIslem->fiyat, 2, ',', '.') . ' TL';
        
        return [
            'type' => 'para',
            'tarih' => Carbon::parse($paraIslem->created_at)->format('d/m/Y H:i'),
            'personel' => $personel->name ?? '',
            'islem' => 'Para Hareketi: ' . $odemeDurum,
            'aciklama' => $fiyat . ' (' . $odemeYonu . ' ) <br>' . ucfirst($paraIslem->aciklama)
        ];
    }
    

    //Servis Aşamalarının servis-bilgileri blade'inde görüntülenmesini sağlayan fonk SONU

    public function EditServiceCustomer($tenant_id, $id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $customer = Customer::where('firma_id', $firma->id)->find($id);
        $countries = Il::orderBy('name','asc')->get();
        return view('frontend.secure.all_services.edit_service_customer', compact('firma', 'customer', 'countries'));
    }

    
    public function UpdateService($tenant_id, Request $request) 
    {
        $firma = Tenant::where('id', $tenant_id)->first();
        
        if (!$firma) {
            return response()->json(['error' => 'Firma bulunamadı'], 404);
        }
        
        $user = auth()->user();
        
        if ($user->can('Servisleri Göremez')) {
            return response()->json(['error' => 'Yetkiniz yok'], 403);
        }
        
        $rules = [
            'cihazModel' => $user->can('Tüm Servisleri Görebilir') 
                ? 'nullable|string|max:255'
                : 'required|string|max:255',
        ];

        // Eğer kullanıcı admin ise ek validasyon kuralları
        if ($user->can('Tüm Servisleri Görebilir')) {
            $rules = array_merge($rules, [
                'servisKaynak' => 'nullable|integer',
                'musaitSaat1' => 'nullable|string|max:10',
                'musaitSaat2' => 'nullable|string|max:10',
                'cihazSeriNo' => 'nullable|string|max:255',
                'cihazAriza' => 'nullable|string|max:1000',
                'operatorNotu' => 'nullable|string|max:1000',
                'garantiSuresi' => 'nullable|integer',
                'faturaNumarasi' => 'nullable|string|max:255',
                'konsinye' => 'nullable|integer',
            ]);
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }
        
        $resource_id = $request->servisid;
        
        // Servisi bul ve kullanıcının bu servisi güncelleyebilir olup olmadığını kontrol et
        $service = Service::findOrFail($resource_id);
        
        try {
            // Güncelleme verilerini hazırla
            $data = [
                'kid' => $user->user_id,
                'cihazModel' => strip_tags(trim($request->cihazModel)),
                'acil' => $request->acil ?: '0',
                'updated_at' => now(),
            ];
            
            // Eğer kullanıcı admin (1 nolu grup) ise tüm alanları güncelleyebilir
            if ($user->can('Tüm Servisleri Görebilir')) {
                $data = array_merge($data, [
                    'kid' => $user->user_id,
                    'acil' => $request->acil ?: '0',
                    'servisKaynak' => $request->kaynak ?: null,
                    'musaitTarih' => $request->musaitTarih,
                    'musaitSaat1' => $request->musaitSaat1 ?: null,
                    'musaitSaat2' => $request->musaitSaat2 ?: null,
                    'cihazMarka' => $request->cihazMarka,
                    'cihazTur' => $request->cihazTur,
                    'cihazModel' => $request->cihazModel,
                    'cihazSeriNo' => $request->cihazSeriNo ?: null,
                    'cihazAriza' => $request->cihazAriza ?: null,
                    'operatorNotu' => $request->opNot ?: null,
                    'garantiSuresi' => $request->cihazGaranti ?: null,
                    'faturaNumarasi' => $request->faturaNumarasi ?: null,
                    'konsinye' => $request->konsinye ?: null,
                ]);
            }
            
            // Servisi güncelle
            $service->update($data);
            // Basit servis güncelleme logunu ekle
            ActivityLogger::logServiceUpdated($resource_id);
            // Güncellenmiş servisi döndür
            $updatedResource = Service::with([
                'musteri:id,adSoyad,tel1,tel2',
                'markaCihaz:id,marka',
                'turCihaz:id,cihaz'
            ])->find($resource_id);
            
            return response()->json([
                'success' => true,
                'data' => $updatedResource,
                'message' => 'Servis başarıyla güncellendi'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Güncelleme sırasında hata oluştu',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function DeleteService($tenant_id, $id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if (!$firma) {
            return redirect()->back()->with([
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger',
            ]);
        }
        Service::where('firma_id', $tenant_id)->findOrFail($id)->update([
            'durum' => 0,
            'silinmeTarihi' => Carbon::now(),
            'silenKisi' => auth()->id(),
        ]);

        // Servis silme logunu ekle
        ActivityLogger::logServiceDeleted($id);
    
        Log::info( $firma->firma_adi . ' firmasının ' . Auth::user()->name . '  personeli ' . $id. ' IDli servisi sildi.', [
            'ip_address' => request()->ip(),
        ]);
        $notification = array(
            'message' => 'Servis Başarıyla Silindi',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }

    //Servis yazdırma fonksiyonu
    public function ServicetoPdf($tenant_id, $id) {
        $servis = Service::findOrFail($id); 
        $kid = Auth()->user()->user_id;
        // Tarih ve saat bilgilerini ayır
        $data = $this->getServisDetay($tenant_id, $id);
        
        if (!$data) {
            return abort(404, 'Servis bulunamadı');
        }
        
        

        $pdf = Pdf::loadView('frontend.secure.all_services.service_to_pdf',$data)->setPaper('A4', 'portrait')
        ->setOption('isPhpEnabled', true)
        ->setOption('isHtml5ParserEnabled', true);
        return $pdf->stream();
    }

    private function getServisDetay($tenant_id, $servisId)
    {
        // Servis bilgisini al
        $servis = Service::where('firma_id', $tenant_id)->where('id', $servisId)->first();
        
        if (!$servis) {
            return null;
        }
        
        // Tarih ve saat bilgilerini ayır
        $tarihSaat = explode(' ', $servis->created_at);
        $tarih = explode('-', $tarihSaat[0]);
        $saat = explode(':', $tarihSaat[1]);
        
        // İlgili tabloların bilgilerini al
        $musteri = Customer::where('firma_id', $tenant_id)->where('id', $servis->musteri_id)->first();
        $firma = Tenant::find($tenant_id);
        $isBeyazEsya = $firma && $firma->sektor === 'beyaz-esya';

        $cihazMarka = DeviceBrand::where('id', $servis->cihazMarka)
            ->where(function($query) use ($tenant_id, $isBeyazEsya) {
                if ($isBeyazEsya) {
                    $query->whereNull('firma_id')
                        ->orWhere('firma_id', $tenant_id);
                } else {
                    $query->where('firma_id', $tenant_id);
                }
            })
            ->first();

        $cihazTur = DeviceType::where('id', $servis->cihazTur)
            ->where(function($query) use ($tenant_id, $isBeyazEsya) {
                if ($isBeyazEsya) {
                    $query->whereNull('firma_id')
                        ->orWhere('firma_id', $tenant_id);
                } else {
                    $query->where('firma_id', $tenant_id);
                }
            })
            ->first();
        $servisDurum = ServiceStage::where(function ($query) use ($tenant_id) {
                $query->whereNull('firma_id')->orWhere('firma_id', $tenant_id);
            })->where('id', $servis->servisDurum)->first();
        
        // Son 5 işlemi al
        $eskiIslemler = ServicePlanning::where('firma_id', $tenant_id)->where('servisid', $servis->id)
                         ->orderBy('id', 'DESC')
                         ->limit(5)
                         ->get();
        
        // Para hareketlerini al
        $paraIslemler = ServiceMoneyAction::where('firma_id', $tenant_id)->where('servisid', $servis->id)
                         ->orderBy('id', 'DESC')
                         ->get();
        
        // Mesaj ayarlarını al
        $mesajObj = ServiceFormSetting::where('firma_id', $tenant_id)->first();
        $mesaj = $mesajObj ? $mesajObj->mesaj : '';
        
        // Garanti kontrolü
        $garantiBitis = null;
        if ($servis->garantiSuresi != "0") {
            $garanti = WarrantyPeriod::where('id', $servis->garantiSuresi)->first();
            
            if ($garanti) {
                $garantiBitisTarihi = Carbon::parse($tarihSaat[0])->addMonths($garanti->garanti);
                $garantiBitis = [
                    $garantiBitisTarihi->day,
                    $garantiBitisTarihi->month,
                    $garantiBitisTarihi->year
                ];
            }
        }
        
        // Servis planlama bilgilerini al
        $servisPlanlama = ServiceStageAnswer::where('firma_id', $tenant_id)
                           ->where('servisid', $servis->id)
                           ->orderBy('id', 'DESC')
                           ->get();
        
        // Bayi personel bilgilerini kontrol et
        $getUye = null;
        $logoPath = null;
        $webSitesi = " ";
        
        foreach ($servisPlanlama as $asama) {
            if ($asama && $asama->cevapText == '[Bayi]') {
                $bayiPersonelId = $asama->cevap;
                $getUye = User::where('tenant_id', $tenant_id)
                              ->where('status', '1')
                              ->whereHas('roles', function($query) {
                                  $query->whereIn('id', ['259']);
                              })->where('user_id', $bayiPersonelId)->first();
                
                if ($getUye) {
                    $logoPath = $getUye->image;
                    $webSitesi = " ";
                    $mesaj = str_replace("[TEL]", $getUye->tel, $mesaj);
                }
                break;
            }
        }
        
        if (!$getUye) {
            $getUye = Tenant::where('id', $tenant_id)->first();
            if ($getUye) {
                $logoPath = $getUye->logo;
                $webSitesi = $getUye->webSitesi ?? " ";
                $mesaj = str_replace("[TEL]", $getUye->tel1, $mesaj);
            }
        }
        
        // İşlem detaylarını hazırla
        $islemDetaylari = [];
        foreach ($eskiIslemler as $eskiIslem) {
            $tarihSaat = explode(" ", $eskiIslem->created_at);
            $tarihArray = explode("-", $tarihSaat[0]);
            $saatArray = explode(":", $tarihSaat[1]);
            
            $asama = ServiceStage::where('id', $eskiIslem->gidenIslem)->first();
            $aciklamalar = ServiceStageAnswer::where('firma_id', $tenant_id)
                            ->where('planid', $eskiIslem->id)
                            ->orderBy('id', 'ASC')
                            ->get();
            
            $aciklamaMetni = '';
            foreach ($aciklamalar as $aciklama) {
                if (!empty($aciklama->cevap)) {
                    $soru = StageQuestion::where('id', $aciklama->soruid)->first();
                    
                    if (strpos($soru->cevapTuru, "[Grup") !== false) {
                        $personel = User::where('tenant_id', $tenant_id)->where('user_id', $aciklama->cevap)->first();
                        $aciklamaMetni .= '<strong>' . $soru->soru . '</strong>: ' . ($personel->name ?? '') . "<br>";
                    } else if ($soru->cevapTuru == "[Arac]") {
                        $arac = Car::where('firma_id', $tenant_id)->where('id', $aciklama->cevap)->first();
                        $aciklamaMetni .= '<strong>' . $soru->soru . '</strong>: ' . ($arac->arac ?? '') . "<br>";
                    } else if ($soru->cevapTuru == "[Parca]") {
                        $aciklamaMetni .= '<strong>' . $soru->soru . '</strong>: ';
                        $parcalar = explode(", ", $aciklama->cevap);
                        foreach ($parcalar as $parca) {
                            $parcaArray = explode("---", $parca);
                            $parcaId = $parcaArray[0];
                            $adet = $parcaArray[1] ?? 1;
                            $stok = Stock::where('firma_id', $tenant_id)->where('id', $parcaId)->first();
                            $aciklamaMetni .= ($stok->urunAdi ?? '') . " (" . $adet . "), ";
                        }
                        $aciklamaMetni .= "<br>";
                    } else if ($soru->cevapTuru == "[Bayi]") {
                        $bayi = User::where('tenant_id', $tenant_id)
                              ->where('status', '1')
                              ->whereHas('roles', function($query) {
                                  $query->whereIn('id', ['259']);
                              })->where('id', $aciklama->cevap)->first();
                        $aciklamaMetni .= '<strong>' . $soru->soru . '</strong>: ' . ($bayi->name ?? '') . "<br>";
                    } else {               
                        $aciklamaMetni .= '<strong>' . $soru->soru . '</strong>: ' . $aciklama->cevap . "<br>"; 
                    }
                    
                }
            }
            
            $islemDetaylari[] = [
                'tarih' => $tarihArray[2] . "/" . $tarihArray[1] . "/" . $tarihArray[0] . ' - ' . $saatArray[0] . ":" . $saatArray[1],
                'asama' => $asama->asama ?? '',
                'aciklama' => $aciklamaMetni
            ];
        }
        
        // Para işlem detaylarını hazırla
        $paraDetaylari = [];
        foreach ($paraIslemler as $paraIslem) {
            $tarihSaat = explode(" ", $paraIslem->created_at);
            $tarihArray = explode("-", $tarihSaat[0]);
            
            $personel = User::where('tenant_id', $tenant_id)->where('user_id', $paraIslem->pid)->first();
            $odemeSekli = PaymentMethod::where('firma_id', $tenant_id)->where('id', $paraIslem->odemeSekli)->first();
            
            $odemeDurum = "";
            if ($paraIslem->odemeDurum == "2") {
                $odemeDurum = 'Beklemede';
            } else if ($paraIslem->odemeDurum == "1") {
                $odemeDurum = 'Tamamlandı';
            }
            
            $paraDetaylari[] = [
                'tarih' => $tarihArray[2] . "/" . $tarihArray[1] . "/" . $tarihArray[0],
                'personel' => $personel->name ?? '',
                'odemeSekli' => $odemeSekli->odemeSekli ?? '',
                'odemeDurum' => $odemeDurum,
                'fiyat' => number_format($paraIslem->fiyat, 2, ',', '.') . ' TL'
            ];
        }
        
        return [
            'servis' => $servis,
            'tarih' => $tarih,
            'saat' => $saat,
            'musteri' => $musteri,
            'cihazMarka' => $cihazMarka,
            'cihazTur' => $cihazTur,
            'servisDurum' => $servisDurum,
            'garantiBitis' => $garantiBitis,
            'getUye' => $getUye,
            'logoPath' => $logoPath,
            'webSitesi' => $webSitesi,
            'mesaj' => $mesaj,
            'islemDetaylari' => $islemDetaylari,
            'paraDetaylari' => $paraDetaylari
        ];
    }
    //Servis yazdırma fonksiyonu SONU 

    //Servisler modalında servis para hareketleri 
    public function ServiceMoneyActions($tenant_id, $service_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $servis = Service::where('firma_id', $tenant_id)->where('id', $service_id)->first();
        // Servis para hareketlerini personel bilgileri ile beraber al
        $servisParaHareketleri = ServiceMoneyAction::where('firma_id', $firma->id)
            ->where('servisid', $servis->id)
            ->with(['personel:user_id,name', 'odemeSekliRelation:id,odemeSekli'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Toplam hesaplama
        $toplamSonuc = 0;
        foreach ($servisParaHareketleri as $hareket) {
            if ($hareket->odemeYonu == 2) { // Gider
                $toplamSonuc -= $hareket->fiyat;
            } elseif ($hareket->odemeYonu == 1) { // Gelir
                $toplamSonuc += $hareket->fiyat;
            }
        }

        return view('frontend.secure.all_services.service_money_actions.service_money_actions', compact('firma', 'servis','servisParaHareketleri', 'toplamSonuc'));
    }

    public function AddServiceIncome($tenant_id, $service_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $servis = Service::where('firma_id', $tenant_id)->where('id', $service_id)->first();
        $personeller = User::where('tenant_id', $tenant_id)->where('status', '1')->whereDoesntHave('roles', function ($query) {
        $query->whereIn('name', ['Admin', 'Super Admin']);
    })->get();
        $odemeSekilleri = PaymentMethod::get();
        return view('frontend.secure.all_services.service_money_actions.add_service_income', compact('firma', 'servis', 'personeller', 'odemeSekilleri'));
    }

    public function StoreServiceIncome($tenant_id, Request $request) {
        $token = $request->input('form_token');
        if (empty($token)) {
            return response()->json([
                'error' => 'Geçersiz form token! Lütfen sayfayı yenileyin.'
            ], 400);
        }
        
        $cacheKey = 'service_income_form_token_' . $token;
        
        if (Cache::has($cacheKey)) {
            return response()->json([
                'error' => 'Bu form zaten gönderildi! Lütfen bekleyin.'
            ], 429);
        }
        Cache::put($cacheKey, true, now()->addMinutes(10));
        $rules = [
            'servisid' => 'required|numeric',
            'odemeSekli' => 'required|numeric',
            'odemeDurum' => 'required|in:1,2',
            'fiyat' => 'required|numeric|min:0',
            'aciklama' => 'nullable|string|max:255',
        ];

        // Patron ise ek validasyon kuralları
        if (auth()->user()->hasRole('Patron')) {
            $rules['tarih'] = 'required|date';
            $rules['personeller'] = 'required|numeric|exists:tb_user,user_id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validasyon hatası', 
                'messages' => $validator->errors()
            ], 422);
        }

         // Temel verileri al
        $servisid = $request->input('servisid');
        $fiyat = str_replace(",", ".", trim($request->input('fiyat')));
        
        // Tarih değişkenini doğru yerde tanımla
        $tarih = Carbon::now(); // Varsayılan olarak şu anki tarih
        
        // Eğer kullanıcı Patron ise ve tarih gönderilmişse, o tarihi kullan
        if (auth()->user()->hasRole('Patron') && $request->input('tarih')) {
           $tarih = Carbon::parse($request->input('tarih') . ' ' . now()->format('H:i:s'));
        }

        // Ana tabloya eklenecek veri
        $data = [
            'firma_id' => $tenant_id,
            'kid'          => auth()->user()->user_id,
            'servisid'     => $servisid,
            'created_at'   => $tarih,
            'odemeSekli'   => $request->input('odemeSekli'),
            'odemeDurum'   => $request->input('odemeDurum'),
            'fiyat'        => $fiyat,
            'aciklama'     => $request->input('aciklama'),
            'odemeYonu'    => 1,
        ];

        // Personel ID'sini belirle
        if (auth()->user()->hasRole('Patron') && $request->input('personeller')) {
            $data['pid'] = $request->input('personeller');
        } else {
            $data['pid'] = auth()->user()->user_id;
        }

        // servis_para_hareketleri tablosuna veri ekle
        $sonuc = ServiceMoneyAction::where('firma_id', $tenant_id)->create($data);
    
        if ($sonuc) {
            ActivityLogger::logServiceMoneyAdded($servisid, $fiyat, 1, $request->input('aciklama'));
            // kasa_hareketleri için veri hazırlığı
            $kasaData = [
                'firma_id' => $tenant_id,
                'kid'          => auth()->user()->user_id,
                'created_at'   => $tarih, // Aynı tarih değişkenini kullan
                'odemeYonu'    => 1,
                'odemeSekli'   => $request->input('odemeSekli'),
                'odemeDurum'   => $request->input('odemeDurum'),
                'fiyat'        => $fiyat,
                'fiyatBirim'   => 1,
                'aciklama'     => $request->input('aciklama'),
                'marka'        => $request->input('markaid'),
                'cihaz'        => $request->input('cihazid'),
                'servis'       => $servisid,
                'servisIslem'  => $sonuc->id, // ID'yi al
            ];

            // Personel bilgilerini ekle
            if (auth()->user()->hasRole('Patron') && $request->input('personeller')) {
                $kasaData['pid'] = $request->input('personeller');
                $kasaData['personel'] = $request->input('personeller');
            } else {
                $kasaData['pid'] = auth()->user()->user_id;
                $kasaData['personel'] = auth()->user()->user_id;
            }

            // Ödeme türünü belirle
            $odemeTuru = PaymentType::where('servis', 1)->first();
            if ($odemeTuru) {
                $kasaData['odemeTuru'] = $odemeTuru->id;
            }

            // kasa_hareketleri tablosuna ekle
            $kasaID = CashTransaction::create($kasaData);

            return response()->json(['success' => 'Ödeme eklendi.']);
        } else {
            return response()->json(['error' => 'HATA! Ödeme eklenemedi.'], 500);
        }

    }

    public function AddServiceExpense($tenant_id, $service_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $servis = Service::where('firma_id', $tenant_id)->where('id', $service_id)->first();
        $personeller = User::where('tenant_id', $tenant_id)->where('status', '1')->whereDoesntHave('roles', function ($query) {
        $query->whereIn('name', ['Admin', 'Super Admin']);
    })->get();
        $odemeSekilleri = PaymentMethod::get();
        return view('frontend.secure.all_services.service_money_actions.add_service_expense', compact('firma', 'servis','personeller','odemeSekilleri'));
    }

    public function StoreServiceExpense($tenant_id, Request $request) {
        $token = $request->input('form_token');
        if (empty($token)) {
            return response()->json([
                'error' => 'Geçersiz form token! Lütfen sayfayı yenileyin.'
            ], 400);
        }
        $cacheKey = 'service_expense_form_token_' . $token;
        if (Cache::has($cacheKey)) {
            return response()->json([
                'error' => 'Bu form zaten gönderildi! Lütfen bekleyin.'
            ], 429);
        }
        Cache::put($cacheKey, true, now()->addMinutes(10));
         $rules = [
            'servisid' => 'required|numeric',
            'odemeSekli' => 'required|numeric',
            'odemeDurum' => 'required|in:1,2',
            'fiyat' => 'required|numeric|min:0',
            'aciklama' => 'nullable|string|max:255',
        ];

        // Patron ise ek validasyon kuralları
        if (auth()->user()->hasRole('Patron')) {
            $rules['tarih'] = 'required|date';
            $rules['personeller'] = 'required|numeric|exists:tb_user,user_id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validasyon hatası', 
                'messages' => $validator->errors()
            ], 422);
        }

         // Temel verileri al
        $servisid = $request->input('servisid');
        $fiyat = str_replace(",", ".", trim($request->input('fiyat')));
        
        // Tarih değişkenini doğru yerde tanımla
        $tarih = Carbon::now(); // Varsayılan olarak şu anki tarih
        
        // Eğer kullanıcı Patron ise ve tarih gönderilmişse, o tarihi kullan
        if (auth()->user()->hasRole('Patron') && $request->input('tarih')) {
            $tarih = Carbon::parse($request->input('tarih') . ' ' . now()->format('H:i:s'));
        }

        // Ana tabloya eklenecek veri
        $data = [
            'firma_id' => $tenant_id,
            'kid'          => auth()->user()->user_id,
            'servisid'     => $servisid,
            'created_at'   => $tarih,
            'odemeSekli'   => $request->input('odemeSekli'),
            'odemeDurum'   => $request->input('odemeDurum'),
            'fiyat'        => $fiyat,
            'aciklama'     => $request->input('aciklama'),
            'odemeYonu'    => 2,
        ];

        // Personel ID'sini belirle
        if (auth()->user()->hasRole('Patron') && $request->input('personeller')) {
            $data['pid'] = $request->input('personeller');
        } else {
            $data['pid'] = auth()->user()->user_id;
        }

        // servis_para_hareketleri tablosuna veri ekle
        $sonuc = ServiceMoneyAction::where('firma_id', $tenant_id)->create($data);
    
        if ($sonuc) {
            ActivityLogger::logServiceMoneyAdded($servisid, $fiyat, 2, $request->input('aciklama'));
            // kasa_hareketleri için veri hazırlığı
            $kasaData = [
                'firma_id' => $tenant_id,
                'kid'          => auth()->user()->user_id,
                'created_at'   => $tarih,
                'odemeYonu'    => 2,
                'odemeSekli'   => $request->input('odemeSekli'),
                'odemeDurum'   => $request->input('odemeDurum'),
                'fiyat'        => $fiyat,
                'fiyatBirim'   => 1,
                'aciklama'     => $request->input('aciklama'),
                'marka'        => $request->input('markaid'),
                'cihaz'        => $request->input('cihazid'),
                'servis'       => $servisid,
                'servisIslem'  => $sonuc->id, // ID'yi al
            ];

            // Personel bilgilerini ekle
            if (auth()->user()->hasRole('Patron') && $request->input('personeller')) {
                $kasaData['pid'] = $request->input('personeller');
                $kasaData['personel'] = $request->input('personeller');
            } else {
                $kasaData['pid'] = auth()->user()->user_id;
                $kasaData['personel'] = auth()->user()->user_id;
            }

            // Ödeme türünü belirle
            $odemeTuru = PaymentType::where('servis', 1)->first();
            if ($odemeTuru) {
                $kasaData['odemeTuru'] = $odemeTuru->id;
            }

            // kasa_hareketleri tablosuna ekle
            $kasaID = CashTransaction::create($kasaData);

            return response()->json(['success' => 'Ödeme eklendi.']);
        } else {
            return response()->json(['error' => 'HATA! Ödeme eklenemedi.'], 500);
        }
    }

    public function EditServiceMoneyAction($tenant_id, $payment_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $servisPara = ServiceMoneyAction::where('firma_id', $tenant_id)
            ->where('id', $payment_id)
            ->with(['personel', 'odemeSekliRelation'])
            ->first();
        
        if (!$servisPara) {
            abort(404, 'Ödeme kaydı bulunamadı.');
        }
        
        $personeller = User::where('tenant_id', $tenant_id)->where('status', '1')->get();
        $odemeSekli = PaymentMethod::get();
        
        return view('frontend.secure.all_services.service_money_actions.edit_service_money_action', 
            compact('firma', 'servisPara', 'personeller', 'odemeSekli'));
    }

    public function UpdateServiceMoneyAction(Request $request, $tenant_id)
    {
        try {
            // Validation
            $request->validate([
                'odemeSekli' => 'required|integer',
                'odemeDurum' => 'required|integer',
                'fiyat' => 'required|numeric|min:0',
                'odemeYonu' => 'required|integer|in:1,2',
                'aciklama' => 'nullable|string|max:255',
            ]);
            
            $user = Auth::user();
            $kid = $user->user_id;
            $id = $request->payment_id;
            
            // Mevcut kaydı getir
            $asamaSec = ServiceMoneyAction::where('firma_id', $tenant_id)
                ->where('id', $id)
                ->where('kid', $kid)
                ->first();
            
            if (!$asamaSec) {
                return response()->json(['error' => 'Kayıt bulunamadı'], 404);
            }
            
            // Tarih işlemi
            $tarih = null;
            if ($request->has('tarih') && !empty($request->tarih)) {
                $tarihArray = explode("/", $request->tarih);
                if (count($tarihArray) == 3) {
                    $tarih = $tarihArray[2] . "-" . $tarihArray[1] . "-" . $tarihArray[0] . " " . now()->format("H:i:s");
                }
            }
            
            // Fiyat formatı düzeltme
            $fiyat = str_replace(",", ".", $request->fiyat);
            
            // Güncelleme verilerini hazırla
            $updateData = [
                'kid' => $kid,
                'odemeSekli' => $request->odemeSekli,
                'odemeDurum' => $request->odemeDurum,
                'fiyat' => $fiyat,
                'aciklama' => $request->aciklama,
                'odemeYonu' => $request->odemeYonu,
                'updated_at' => now(),
            ];
            
            // Personel bilgisi (sadece yetkili kullanıcılar için)
            if (auth()->user()->hasRole('Patron')) {
                $updateData['pid'] = $request->personeller;
                if ($tarih) {
                    $updateData['created_at'] = $tarih;
                }
            }
            
            if (!$tarih) {
                $updateData['created_at'] = now();
            }
                    
            // Servis para hareketini güncelle
            $servisGuncellendi = ServiceMoneyAction::where('firma_id', $tenant_id)
                ->where('id', $id)
                ->update($updateData);
            
            if ($servisGuncellendi) {                
                // Kasa hareketini güncelle
                $kasaSec = CashTransaction::where('firma_id', $tenant_id)
                    ->where('servisIslem', $id)
                    ->first();
                
                if ($kasaSec) {
                    $kasaUpdateData = [
                        'kid' => $kid,
                        'odemeYonu' => $request->odemeYonu,
                        'odemeSekli' => $request->odemeSekli,
                        'odemeDurum' => $request->odemeDurum,
                        'fiyat' => $fiyat,
                        'fiyatBirim' => "1",
                        'aciklama' => $request->aciklama,
                        'servis' => $asamaSec->servisid,
                        'updated_at' => now(),
                    ];
                    
                    if (auth()->user()->hasRole('Patron')) {
                        $kasaUpdateData['pid'] = $request->personeller;
                        $kasaUpdateData['personel'] = $request->personeller;
                        if ($tarih) {
                            $kasaUpdateData['created_at'] = $tarih;
                        }
                    }
                    
                    if (!$tarih) {
                        $kasaUpdateData['created_at'] = now();
                    }
                    
                    // Ödeme türünü getir
                    $servisIslem = PaymentType::where('firma_id', $tenant_id)
                        ->where('servis', '1')
                        ->first();
                    
                    if ($servisIslem) {
                        $kasaUpdateData['odemeTuru'] = $servisIslem->id;
                    }
                    
                    CashTransaction::where('firma_id', $tenant_id)
                        ->where('id', $kasaSec->id)
                        ->update($kasaUpdateData);
                    
                }
                
                
                return response()->json([
                    'success' => true,
                    'message' => 'Ödeme güncellendi.'
                ]);
                
            } else {
                
                return response()->json([
                    'success' => false,
                    'message' => 'HATA! Ödeme güncellenemedi.'
                ]);
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasyon hatası',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            
            return response()->json([
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    public function DeleteServiceMoneyAction($tenant_id, $payment_id) {
        $paymentId = $payment_id;
    
        try {
            $payment = ServiceMoneyAction::where('firma_id', $tenant_id)
                ->where('id', $paymentId)
                ->first();
            
            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'Ödeme kaydı bulunamadı.'], 404);
            }
            
            // İlgili kasa hareketini de sil
            CashTransaction::where('servisIslem', $payment->id)->delete();
            
            $payment->delete();
            
            return response()->json(['success' => true, 'message' => 'Ödeme kaydı başarıyla silindi.']);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Bir hata oluştu: ' . $e->getMessage()], 500);
        }
    }
    //Servisler modalında servis para hareketleri SONU

    //Servisler modalında servis fotoğrafları kısmı başlangıcı
    public function ServicePhotos($tenant_id, $service_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $servis = Service::where('firma_id', $tenant_id)->where('id', $service_id)->first();
        $photos = ServicePhoto::where('firma_id', $firma->id)->where('servisid', $servis->id)->orderBy('created_at', 'desc')->get();
        return view('frontend.secure.all_services.service_photos.all_service_photos', compact('firma', 'servis', 'photos'));
    }

    public function StoreServicePhoto($tenant_id, Request $request) {
        try {
        // Validasyon kuralları
        $validator = Validator::make($request->all(), [
            'belge' => 'required|file|mimes:jpg,jpeg,png|max:5120', // 5MB = 5120KB
            'servisid' => 'required|integer|exists:services,id'
        ], [
            'belge.required' => 'Lütfen bir dosya seçiniz.',
            'belge.mimes' => 'Sadece JPG, JPEG ve PNG dosyaları yükleyebilirsiniz.',
            'belge.max' => 'Dosya boyutu 5MB\'dan büyük olamaz.',
            'servisid.required' => 'Servis ID gerekli.',
            'servisid.exists' => 'Geçersiz servis.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // Firma kontrolü
        $firma = Tenant::find($tenant_id);
        
        if (!$firma) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı.'
            ], 404);
        }

        // Dosya
        $file = $request->file('belge');
        $fileSize = $file->getSize();
        
        // Storage limit kontrolü (middleware'de de kontrol ediliyor ama ekstra güvenlik için)
        if (!$firma->canUploadFile($fileSize)) {
            $storageInfo = $firma->getStorageInfo();
            
            return response()->json([
                'success' => false,
                'message' => 'Storage limiti aşıldı! Dosya yükleyemezsiniz.',
                'error_type' => 'storage_limit_exceeded',
                'storage_info' => $storageInfo
            ], 422);
        }

        // Servis başına fotoğraf sayısı kontrolü
        $currentCount = ServicePhoto::where('firma_id', $tenant_id)
            ->where('servisid', $request->servisid)
            ->count();

        if ($currentCount >= 4 || $currentCount + 1 > 4) {
            return response()->json([
                'success' => false,
                'message' => 'Bu servise en fazla 4 fotoğraf yükleyebilirsiniz.'
            ], 422);
        }

        $firma = Tenant::where('id', $tenant_id)->first();

        // Dosya işlemleri
        $file = $request->file('belge');
        $ext = $file->getClientOriginalExtension();
        $uuid = Str::uuid()->toString() . '.' . $ext;

        $path = "service_photos/firma_{$firma->firma_slug}/servis_{$request->servisid}/" . now()->toDateString();
        $fullPath = storage_path('app/public/' . $path);

        // Klasörü oluştur
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0775, true);
        }

        // Resmi boyutlandır ve kaliteyi düşür (1024px genişlik, kalite: 75)
        $image = Image::make($file)->resize(1024, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $image->save($fullPath . '/' . $uuid, 75); // kalite 0-100 arasında

        $storedPath = $path . '/' . $uuid;

        // Veritabanına kayıt
        $photo = ServicePhoto::create([
            'firma_id' => $tenant_id,
            'kid' => auth()->user()->user_id ?? null,
            'servisid' => $request->servisid,
            'resimyol' => $storedPath,
            'created_at' => Carbon::now(),
        ]);
         if ($request->attributes->get('storage_warning')) {
            $response['storage_warning'] = 'Storage alanınız dolmak üzere!';
        }


        ActivityLogger::logServicePhotoAdded($request->servisid, $photo->id);
        return response()->json([
            'success' => true,
            'message' => 'Fotoğraf başarıyla yüklendi.',
            'photo' => [
                'id' => $photo->id,
                'url' => Storage::url($photo->resimyol),
                'created_at' => $photo->created_at->format('d/m/Y')
            ]
        ]);


    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Dosya yüklenirken bir hata oluştu. Lütfen tekrar deneyiniz.'
        ], 500);
    }
    }

    public function DeleteServicePhoto($tenant_id, $photo_id)
    {
        ActivityLogger::logServicePhotoDeleted($tenant_id, $photo_id);
        try {
            $photo = ServicePhoto::where('firma_id', $tenant_id)
                                ->where('id', $photo_id)
                                ->firstOrFail();

            // resimyol = "service_photos/firma_3/servis_5/2025-06-26/uuid.jpg"
            if (Storage::disk('public')->exists($photo->resimyol)) {
                Storage::disk('public')->delete($photo->resimyol);
            }
            // Veritabanından sil
            $photo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Fotoğraf başarıyla silindi.'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fotoğraf bulunamadı.'
            ], 404);

        } catch (\Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Fotoğraf silinirken bir hata oluştu.'
            ], 500);
        }
    }
    //Servisler modalında servis fotoğrafları kısmı SONU

    //Servisler modalında fiş notu kısmı başlangıcı
    public function ServiceReceiptNotes($tenant_id, $service_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $servis  = Service::where('id', $service_id)->first();
        $servis_fis_notlari = ServiceReceiptNote::where('firma_id',$firma->id)->where('servisid', $servis->id)->get();
        return view('frontend.secure.all_services.service_receipt_notes.receipt_notes', compact('firma', 'servis','servis_fis_notlari'));
    }

    public function AddServiceReceiptNote($tenant_id, $service_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $servis  = Service::where('id', $service_id)->first();
        return view('frontend.secure.all_services.service_receipt_notes.add_receipt_note', compact('firma', 'servis'));
    }

    public function StoreReceiptNote($tenant_id, Request $request) {
        $token = $request->input('form_token');
        if (empty($token)) {
            return response()->json([
                'error' => 'Geçersiz form token! Lütfen sayfayı yenileyin.'
            ], 400);
        }
        $cacheKey = 'receipt_note_form_token_' . $token;
        if (Cache::has($cacheKey)) {
            return response()->json([
                'error' => 'Bu form zaten gönderildi! Lütfen bekleyin.'
            ], 429);
        }
        Cache::put($cacheKey, true, now()->addMinutes(10));
        $kid = Auth::user()->user_id;
        $receiptNotes = ServiceReceiptNote::create([
            'firma_id' => $tenant_id,
            'kid' => $kid,
            'servisid' => $request->servisid,
            'aciklama' => $request->aciklama,
            'created_at' => Carbon::now(),
        ]);
        ActivityLogger::logServiceNoteAdded($request->servisid, 'receipt', $receiptNotes->id);
        return response()->json([
                'success' => true,
                'message' => 'Servis fiş notu başarıyla yüklendi.',
                'note' => $receiptNotes,
            ]);
    }

    public function EditServiceReceiptNote($tenant_id, $note_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $note_id = ServiceReceiptNote::where('firma_id', $tenant_id)->where('id', $note_id)->first();

        return view('frontend.secure.all_services.service_receipt_notes.edit_receipt_note', compact('firma','note_id'));
    }

    public function UpdateServiceReceiptNote($tenant_id, Request $request) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $user = Auth::user();
        $kid = $user->user_id;
        $id = $request->note_id;

        ServiceReceiptNote::findOrFail($id)->update([
            'kid' => $kid,
            'servisid' => $request->servisid,
            'aciklama' => $request->aciklama,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Servis fiş notu başarıyla güncellendi.'
        ]);
    }

    public function DeleteReceiptNote($tenant_id, $note_id) {

        try {
            $service_receipt_note = ServiceReceiptNote::where('firma_id', $tenant_id)->where('id', $note_id)->firstOrFail();
            
            $service_receipt_note->delete();

            return response()->json([
                'success' => true,
                'message' => 'Servis fiş notu başarıyla silindi.'
            ]);

        } catch (\Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Fiş notu silinirken bir hata oluştu.'
            ], 500);
        }
    }
    //Servisler modalında fiş notu kısmı SONU

    //Servisler modalında operatör notu kısmı başlangıcı
    public function ServiceOptNotes($tenant_id, $service_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $servis  = Service::where('id', $service_id)->first();
        $opt_notlari = ServiceOptNote::where('firma_id', $firma->id)->where('servisid', $servis->id)->get();
        return view('frontend.secure.all_services.service_opt_notes.service_operator_notes', compact('firma', 'servis', 'opt_notlari'));
    }

    public function AddServiceOptNote($tenant_id, $service_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $servis  = Service::where('id', $service_id)->first();

        return view('frontend.secure.all_services.service_opt_notes.add_opt_note', compact('firma', 'servis'));
    }

    public function StoreServiceOptNote($tenant_id, Request $request) {
        $token = $request->input('form_token');
        if (empty($token)) {
            return response()->json([
                'error' => 'Geçersiz form token! Lütfen sayfayı yenileyin.'
            ], 400);
        }
        $cacheKey = 'opt_note_form_token_' . $token;
        if (Cache::has($cacheKey)) {
            return response()->json([
                'error' => 'Bu form zaten gönderildi! Lütfen bekleyin.'
            ], 429);
        }
        Cache::put($cacheKey, true, now()->addMinutes(10));
        $kid = Auth::user()->user_id;
        $optNotes = ServiceOptNote::create([
            'firma_id' => $tenant_id,
            'pid' => $kid,
            'servisid' => $request->servisid,
            'aciklama' => $request->aciklama,
            'created_at' => Carbon::now(),
        ]);
        ActivityLogger::logServiceNoteAdded($request->servisid, 'opt', $optNotes->id);
        return response()->json([
                'success' => true,
                'message' => 'Servis fiş notu başarıyla yüklendi.',
                'note' => $optNotes,
        ]);
    }

    public function EditServiceOptNote($tenant_id, $note_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $note_id = ServiceOptNote::where('firma_id', $firma->id)->where('id', $note_id)->first();
        $servis = Service::where('firma_id', $tenant_id)->where('id', $note_id->servisid)->first();
        return view('frontend.secure.all_services.service_opt_notes.edit_opt_note', compact('firma', 'note_id', 'servis'));
    }

    public function UpdateServiceOptNote($tenant_id, Request $request) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $user = Auth::user();
        $kid = $user->user_id;
        $id = $request->note_id;

        ServiceOptNote::findOrFail($id)->update([
            'pid' => $kid,
            'servisid' => $request->servisid,
            'aciklama' => $request->aciklama,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Servis fiş notu başarıyla güncellendi.'
        ]);
    }

    public function DeleteServiceOptNote($tenant_id, $note_id) {

        try {
            $service_receipt_note = ServiceOptNote::where('firma_id', $tenant_id)->where('id', $note_id)->firstOrFail();
            
            $service_receipt_note->delete();

            return response()->json([
                'success' => true,
                'message' => 'Servis operatör notu başarıyla silindi.'
            ]);

        } catch (\Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Operatör notu silinirken bir hata oluştu.'
            ], 500);
        }
    }
    //Servisler modalında operatör notu kısmı SONU

    //Servisler modalındaki teklifler bölümü
    public function CustomerOffers($tenant_id, $service_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $servis = Service::where('firma_id', $tenant_id)->where('id', $service_id)->first();
        $customer_offers = Offer::where('firma_id', $tenant_id)->where('musteri_id', $servis->musteri_id)->get();
        return view('frontend.secure.all_services.customer_offers', compact('servis','customer_offers','firma'));
    }

    //Servisler modalındaki faturalar Bölümü
    public function  CustomerInvoices($tenant_id, $service_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $servis = Service::where('firma_id', $tenant_id)->where('id', $service_id)->first();
        $customer_invoices = Invoice::with('invoice_products')->where('firma_id', $tenant_id)->where('servisid',$servis->id)->get();
        return view('frontend.secure.all_services.customer_invoices', compact('servis','firma','customer_invoices'));
    }

    //Servis fiş kopyalama fonksiyonu
    public function getFisIcerigi(Request $request, $tenant_id, $servis_id)
    {
        $user = auth()->user();
        $tenant = Tenant::where('id', $tenant_id)->first();

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

            $fisTemplate = ReceiptDesign::where('firma_id', $tenant->id)->first();

            if (!$fisTemplate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fiş şablonu bulunamadı. Lütfen yöneticinizle iletişime geçin.'
                ], 404);
            }

            $fisIcerigi = $this->olusturFisIcerigi($servis, $tenant, $user, $fisTemplate);

            return response()->json([
                'success' => true,
                'icerik' => $fisIcerigi
            ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('Web fiş yazdırma hatası: ' . $e->getMessage(), [
                'user_id' => $user->user_id,
                'servis_id' => $servis_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Fiş oluşturulurken bir hata oluştu'
            ], 500);
        }
    }
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

        // Yasal uyarıyı fiş tasarımına ekle (str_replace'den ÖNCE)
        $tamFisTasarimi = $fisTemplate->fisTasarimi;
        if (!empty($yasalUyari)) {
            $tamFisTasarimi .= "\r\n" .$yasalUyari;
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

        $mesaj = str_replace($search, $replace, $tamFisTasarimi);

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

        // Fiş notlarını yapılan işlemlerin sonuna ekle
        $fisNotlari = ServiceReceiptNote::where('servisid', $servisId)
            ->where('firma_id', $tenantId)
            ->orderBy('id', 'desc')
            ->get();

        if ($fisNotlari->count() > 0) {
            if (!empty($islemler)) {
                $islemler .= "\r\n"; // Boşluk bırak
            }
            
            foreach ($fisNotlari as $not) {
                $islemler .= $not->aciklama . "\r\n";
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

