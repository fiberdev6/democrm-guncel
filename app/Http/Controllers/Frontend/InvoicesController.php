<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Il;
use App\Models\Integration;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\KdvKodu;
use App\Models\PaymentMethod;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\TevkifatKodu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use App\Services\ActivityLogger;
use App\Services\InvoiceIntegrationFactory;
use App\Services\InvoiceIntegrations\ParasutService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class InvoicesController extends Controller
{   
    public function __construct()
{
    $this->middleware('permission:Faturaları Görebilir');
}
    public function AllInvoice(Request $request, $tenant_id) {
        $invoices = Invoice::where('firma_id',$tenant_id)->where('durum', 1)->orderBy('id','desc')->get();
        //$musteriler = Customer::where('firma_id', $tenant_id)->orderBy('adSoyad', 'ASC')->get();
        $firma = Tenant::where('id', $tenant_id)->first();

        if ($request->ajax()) {           
            $data = Invoice::with('customer')->where('firma_id', $tenant_id)->where('durum', '1');

        // Tarih filtreleme mantığı 
        $hasUserSelectedInvoiceDate = $request->filled('from_date') && $request->filled('to_date') && !$this->isDefaultInvoiceDateRange($request);
        $hasSearchOrOtherFilters = !empty(trim($request->get('search', ''))) || 
                                    $request->filled('musteri') || 
                                    $request->filled('durum');

        if ($hasUserSelectedInvoiceDate) {
            // Fatura sayfasındaki tarih filtresi en yüksek önceliğe sahiptir
            $this->applyMainInvoiceDateRange($data, $request);
        } elseif (!$hasUserSelectedInvoiceDate && !$hasSearchOrOtherFilters) {
            // Hiçbir tarih veya arama/filtre yoksa, varsayılan son 3 günü uygula
            $from = Carbon::today()->subDays(2)->startOfDay();
            $to   = Carbon::today()->endOfDay();
            $data->whereBetween('faturaTarihi', [$from, $to]);
        }
        // Eğer $hasSearchOrOtherFilters true ise ancak tarih filtresi seçilmemişse,
        // herhangi bir tarih kısıtlaması uygulanmaz, bu da tüm kayıtlarda arama yapılmasını sağlar.


            if ($request->get('musteri')) {
                $musteriID = $request->get('musteri');
                $data->whereHas('customer', function ($query) use ($musteriID) {
                    $query->where('id', $musteriID);
                });
            }
            

            // $data->when($request->filled('from_date') && $request->filled('to_date'), function ($query) use ($request) {
            //     return $query->whereDate('faturaTarihi', '>=', $request->from_date)
            //                  ->whereDate('faturaTarihi', '<=', $request->to_date);
            // });

            if ($request->filled('durum')) {
                    $durum = $request->get('durum');
                     // 0, 1, 2, 3 değerlerini kabul et
                    if (in_array($durum, ['error', 'sent', 'draft'])) {
                        $data->where('faturaDurumu', $durum);
                       }
            }

            // Sıralama işlemi
            if ($request->has('order')) {
                $order = $request->get('order')[0];
                $columns = $request->get('columns');
                $orderColumn = $columns[$order['column']]['data'];
                $orderDir = $order['dir'];
                
                if($orderColumn == 'mid'){
                    $data->leftJoin('customers', 'invoices.musteriid', '=', 'customers.id')
                    ->addSelect(['invoices.*', 'customers.adSoyad as musAdi'])
                    ->orderBy('customers.adSoyad',$orderDir);
                }
                else {
                    $data->orderBy($orderColumn, $orderDir);
                }
            } else {
                $data->orderBy('faturaTarihi','desc');
            }
            return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('id', function($row){
                return '<a class="t-link editInvoice idWrap" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editInvoiceModal"><div class="mobileTitle">Id:</div>'.$row->id.'</a>';
            })
            ->addColumn('faturaTarihi', function($row){
                $faturaTarihi = Carbon::parse($row->faturaTarihi)->format('d/m/Y H:i');
                return '<a class="t-link editInvoice" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editInvoiceModal"><div class="mobileTitle">Fatura Tarihi:</div>'.$faturaTarihi.'</a>';
            })
            ->addColumn('faturaNumarasi', function($row){
                return '<a class="t-link editInvoice" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editInvoiceModal"><div class="mobileTitle">F. No:</div>'.$row->faturaNumarasi.'</a>';
            })
            ->addColumn('mid', function($row){
                return '<a class="t-link editInvoice address" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editInvoiceModal"><span class="mobileTitle">Müşteri:</span><strong>'.$row->customer?->adSoyad.'</strong><br><div style="font-size:12px;">'.$row->customer?->m_adi.'</div></a>';
            })
            ->addColumn('genelToplam', function($row){
                return '<a class="t-link editInvoice" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editInvoiceModal"><div class="mobileTitle">G. Toplam:</div>'.$row->genelToplam.' ₺</a>';
            })
            ->addColumn('odemeDurum', function($row){
                if($row->has_payment == '1'){
                    return '<a class="t-link editInvoice" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editInvoiceModal"><div class="mobileTitle">Durum:</div><div style="color: green; display: inline-block;font-weight:700;">Ödendi</div></a>';
                }elseif($row->has_payment == '0'){
                    return '<a class="t-link editInvoice" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editInvoiceModal"><div class="mobileTitle">Durum:</div><div style="color: red; display: inline-block;font-weight:700;">Ödenmedi</div></a>';
                }
            })
            ->addColumn('actions', function($row){ 
                $deleteUrl = route('delete.invoices', [$row->firma_id, $row->id]); 
                
                // Firma bilgilerini al
                $tenant = \App\Models\Tenant::find($row->firma_id);
                
                // Paraşüt entegrasyonunu kontrol et
                $parasutIntegration = \App\Models\Integration::where('slug', 'parasut')->first();
                $hasParasutIntegration = $tenant && $parasutIntegration && $tenant->hasIntegration($parasutIntegration->id);
                
                $buttons = '';
                
                // ⭐ Resmileştirme durumu gösterimi (Paraşüt entegrasyonu varsa)
                if($hasParasutIntegration) {
                    if($row->formalized) {
                        // Zaten resmileştirilmiş - SADECE DURUM BADGE'İ (tıklanamaz)
                        $statusColors = [
                            'pending' => 'warning',
                            'sent' => 'success',
                            'error' => 'danger'
                        ];
                        $statusIcon = [
                            'pending' => 'fa-clock',
                            'sent' => 'fa-check-circle',
                            'error' => 'fa-exclamation-triangle'
                        ];
                        
                        $color = $statusColors[$row->formalization_status] ?? 'secondary';
                        $icon = $statusIcon[$row->formalization_status] ?? 'fa-question-circle';
                        $typeText = $row->formalization_type === 'e-invoice' ? 'e-Fatura' : 'e-Arşiv';
                        
                        // ✅ Sadece badge göster - tıklanamaz span
                        $buttons .= '<span class="badge bg-'.$color.' mobilBtn mbuton1" title="'.$typeText.'">
                            <i class="fas '.$icon.'"></i>
                        </span> ';
                    } else {
                        // Henüz resmileştirilmemiş - Resmileştir butonu
                        $buttons .= '<button type="button" class="btn btn-outline-primary btn-sm mobilBtn mbuton1 formalizeInvoice" 
                            data-invoice-id="'.$row->id.'" 
                            title="Resmileştir">
                            <i class="fas fa-paper-plane"></i>
                        </button> ';
                    }
                } else {
                    // Paraşüt yoksa lokal PDF görüntüle
                    if(!empty($row->faturaPdf)) {
                        $buttons .= '<a href="'.asset($row->faturaPdf).'" target="_blank" class="btn btn-outline-primary btn-sm mobilBtn mbuton1" title="Faturayı görüntüle">
                            <i class="far fa-eye"></i>
                        </a> ';
                    }
                }
                
                $editButton = '<a href="javascript:void(0);" data-bs-id="'.$row->id.'" class="btn btn-outline-warning btn-sm editInvoice mobilBtn mbuton1" data-bs-toggle="modal" data-bs-target="#editInvoiceModal" title="Düzenle">
                    <i class="fas fa-edit"></i>
                </a>'; 
                
                $deleteButton = '<a href="'.$deleteUrl.'" class="btn btn-outline-danger btn-sm mobilBtn" id="delete" title="Sil">
                    <i class="fas fa-trash-alt"></i>
                </a>'; 
                
                return $buttons . ' ' . $editButton . ' ' . $deleteButton; 
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function($w) use($request){
                       $search = $request->get('search');
                       $w->where('id', 'LIKE', "%$search%")
                       ->orWhereHas('customer', function($q) use($search) {
                            $q->where('adSoyad', 'LIKE', "%$search%");
                        });
                   });
                }

            })
            ->rawColumns(['id','faturaTarihi','faturaNumarasi','mid','genelToplam','odemeDurum','actions'])
            ->make(true);

        }

        return view('frontend.secure.invoices.all_invoices',compact('invoices','firma'));
    }
    // Helper Methods
    private function isDefaultInvoiceDateRange(Request $request): bool
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

    private function applyMainInvoiceDateRange($query, Request $request): void
    {
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $from = Carbon::createFromFormat('Y-m-d', $request->from_date)->startOfDay();
            $to   = Carbon::createFromFormat('Y-m-d', $request->to_date)->endOfDay();
            $query->whereBetween('faturaTarihi', [$from, $to]);
        }
    }

    public function searchMusteri(Request $request, $tenant_id)
    {
        $searchField = $request->input('musteriGetir');
        
        // İlişkili verilerle beraber getir
        $musteriler = Customer::where('firma_id', $tenant_id)
            ->where(function($query) use ($searchField) {
                $query->where('adSoyad', 'like', '%' . $searchField . '%')
                    ->orWhere('tel1', 'like', '%' . $searchField . '%');
            })
            ->with(['state', 'country']) // İl ve ilçe ilişkileri
            ->orderBy('adSoyad', 'ASC')
            ->get();
        
        return response()->json($musteriler);
    }
    public function GetInvoices(Request $request, $tenant_id)
    {  
        $data = Invoice::query();

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $start = Carbon::parse($request->input('from_date'))->startOfDay();
            $end = Carbon::parse($request->input('to_date'))->endOfDay();

            $data->whereBetween('faturaTarihi', [$start, $end]);
        }

        if($request->filled('musteri')){
            $data->where('musteriid', $request->input('musteri'));
        }

        if ($request->filled('durum')) {
            if($request->get('durum') == 1){
                $data->where('odemeDurum', 1);
            }elseif($request->get('durum') == 0){
                $data->where('odemeDurum', 0);
            }else{
                $data->get();
            }
        }
        
        $filteredData = $data->where('firma_id',$tenant_id)->where('durum','1')->get();
        
        $response = [
            'toplamNakitTL' => 0.00,
            'toplamHavaleTL' => 0.00,
            'toplamKartTL' => 0.00,
            'kdvNakitTL' => 0.00,
            'kdvHavaleTL' => 0.00,
            'kdvKartTL' => 0.00,
            'genelNakitTL' => 0.00,
            'genelHavaleTL' => 0.00,
            'genelKartTL' => 0.00,
            'toplamTutarTL1' => 0.00,
            'toplamTutarTL2' => 0.00,
            'toplamTutarTL3' => 0.00
        ];
        
        
        foreach ($filteredData as $item) {
            $toplamTL = $item->toplam;
            $kdvTL = $item->kdv;
            $genelTL = $item->genelToplam;
            
            if ($item->odemeSekli == 1) {
                $response['toplamNakitTL'] += $item->toplam;
                $response['kdvNakitTL'] += $item->kdv;
                $response['genelNakitTL'] += $item->genelToplam;
            } elseif ($item->odemeSekli == 2) {
                $response['toplamHavaleTL'] += $item->toplam;
                $response['kdvHavaleTL'] += $item->kdv;
                $response['genelHavaleTL'] += $item->genelToplam;
            } elseif ($item->odemeSekli == 3) {
                $response['toplamKartTL'] += $item->toplam;
                $response['kdvKartTL'] += $item->kdv;
                $response['genelKartTL'] += $item->genelToplam;
            }

            $response['toplamTutarTL1'] += $item->toplam;
            $response['toplamTutarTL2'] += $item->kdv;
            $response['toplamTutarTL3'] += $item->genelToplam;
        }

        
        
        foreach ($response as $key => $value) {
            if (strpos($key, 'TL') !== false) {
                $response[$key] = number_format($value, 2, ',', '.') . ' TL';
            }
        }
        return response()->json($response);
    }

    public function AddInvoice($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if (!$firma) {
            return redirect()->route('giris')->with([
                'message' => 'Firma bulunamadı.',
                'alert-type' => 'danger',
            ]);
        }   

        $musteriler = Customer::where('firma_id', $tenant_id)->orderBy('adSoyad', 'ASC')->get();
        $payment_methods = PaymentMethod::where(function ($query) use ($tenant_id) {
            $query->whereNull('firma_id')->orWhere('firma_id', $tenant_id);
        })->orderBy('id', 'asc')->get();
        $countries = Il::orderBy('name', 'ASC')->get();
        $kdvKodlari = KdvKodu::orderBy('id', 'ASC')->get();
        $tevkifatKodlari = TevkifatKodu::orderBy('id', 'ASC')->get();
        return view('frontend.secure.invoices.add_invoices',compact('musteriler','payment_methods','firma','countries','tevkifatKodlari','kdvKodlari'));
    }

    public function musteriGetir(Request $request, $tenant_id)
    {
        $servisId = $request->input('servisId');
        $musteriAra = $request->input('musteriAra'); // YENİ: Müşteri arama

        // YENİ: Müşteri adına göre arama
        if ($musteriAra) {
            $veriler = DB::table('services')
                ->leftJoin('customers', 'services.musteri_id', '=', 'customers.id')
                ->leftJoin('device_brands', 'services.cihazMarka', '=', 'device_brands.id')
                ->leftJoin('device_types', 'services.cihazTur', '=', 'device_types.id')
                ->where('services.firma_id', $tenant_id)
                ->where('customers.adSoyad', 'LIKE', '%' . $musteriAra . '%')
                ->select(
                    'services.id as servis_id',
                    'services.musteri_id',
                    'customers.musteriTipi',
                    'customers.adSoyad',
                    'customers.tel1',
                    'customers.tel2',
                    'customers.il',
                    'customers.ilce',
                    'customers.adres',
                    'customers.tcNo',
                    'customers.vergiNo',
                    'customers.vergiDairesi',
                    'device_brands.marka',
                    'device_types.cihaz',
                    'services.cihazAriza',
                    'services.kayitTarihi'
                )
                ->orderBy('services.id', 'DESC')
                ->limit(10)
                ->get();

            if ($veriler->count() > 0) {
                return response()->json([
                    'success' => true,
                    'data' => $veriler,
                    'type' => 'multiple' // Birden fazla sonuç
                ], 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu müşteriye ait servis bulunamadı'
                ], 200);
            }
        }

        // Servis ID'ye göre arama (mevcut kod)
        if ($servisId) {
            $veriler = DB::table('services')
                ->leftJoin('customers', 'services.musteri_id', '=', 'customers.id')
                ->leftJoin('device_brands', 'services.cihazMarka', '=', 'device_brands.id')
                ->leftJoin('device_types', 'services.cihazTur', '=', 'device_types.id')
                ->where('services.id', $servisId)
                ->where('services.firma_id', $tenant_id)
                ->select(
                    'services.id',
                    'services.musteri_id',
                    'customers.musteriTipi',
                    'customers.adSoyad',
                    'customers.tel1',
                    'customers.tel2',
                    'customers.il',
                    'customers.ilce',
                    'customers.adres',
                    'customers.tcNo',
                    'customers.vergiNo',
                    'customers.vergiDairesi',
                    'device_brands.marka',
                    'device_types.cihaz'
                )
                ->first();

            if ($veriler) {
                return response()->json([
                    'success' => true,
                    'data' => $veriler,
                    'type' => 'single' // Tek sonuç
                ], 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Servis bulunamadı'
                ], 200);
            }
        }

        return response()->json(['error' => 'Servis ID veya müşteri adı eksik.'], 400);
    }

    public function StoreInvoice(Request $request, $tenant_id)
    {
        $token = $request->input('form_token');
        
        // Token boş mu kontrol et
        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz form token! Lütfen sayfayı yenileyin.'
            ], 403);
        }
        
        // Bu token daha önce kullanıldı mı kontrol et
        $cacheKey = 'invoice_form_token_' . $token;
        
        if (Cache::has($cacheKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Bu fatura zaten gönderildi! Lütfen bekleyin veya sayfayı yenileyin.'
            ], 429);
        }
        
        // Token'ı 10 dakika boyunca sakla
        Cache::put($cacheKey, true, now()->addMinutes(10));
        
        $validateData = $request->validate([
            'document' => 'max:2000',
        ]);

        // Sayısal değerleri doğru şekilde dönüştür
        $toplam = $this->convertToDecimal($request->toplam);
        $indirim = $this->convertToDecimal($request->indirim);
        $kdv = $this->convertToDecimal($request->kdv);
        $genelToplam = $this->convertToDecimal($request->genelToplam);
        
        $firma = Tenant::where('id', $tenant_id)->first();
        if (!$firma) {
            return response()->json([
                'success' => false,
                'message' => 'Firma bulunamadı'
            ], 404);
        }
        
        $document = $request->file('document');
        
        // Storage kontrolü
        if ($document && !$firma->canUploadFile($document->getSize())) {
            $storageInfo = $firma->getStorageInfo();
            return response()->json([
                'success' => false,
                'message' => "Storage limiti doldu! Dosya boyutu: " . $this->formatBytes($document->getSize()) . 
                            ", Kalan alan: " . $storageInfo['remaining_formatted'],
                'error_type' => 'storage_limit_exceeded'
            ], 422);
        }
        
        // Dosya türü ve boyut kontrolü
        if ($document) {
            $allowedExtensions = ['jpg', 'png', 'jpeg', 'pdf'];
            $extension = strtolower($document->getClientOriginalExtension());
            
            if (!in_array($extension, $allowedExtensions)) {
                return response()->json([
                    'success' => false,
                    'message' => "Geçersiz dosya türü: .{$extension}. Sadece JPG, JPEG, PNG ve PDF dosyaları kabul edilir."
                ], 422);
            }

            // Dosya boyutu kontrolü (5MB)
            if ($document->getSize() > 5120 * 1024) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dosya boyutu çok büyük. Maksimum 5MB olmalıdır.'
                ], 422);
            }
            
            $fileName = time() . '.' . $extension;  
            $save_url = $document->move('upload/uploads', $fileName);
        } else {
            $save_url = null;
        }
        
        $createdAt = Carbon::parse($request->faturaTarihi . ' ' . now()->format('H:i:s'));

        // ✅ Transaction başlat
        DB::beginTransaction();
        
        try {

            $aciklama = $request->aciklama;
    $miktar = $request->miktar;
    $fiyat = $request->fiyat;
    $tutar = $request->tutar;

    $invoiceProducts = [];
    
    // Döngüde açıklama kontrolünü daha güvenli hale getirelim
    if (is_array($aciklama)) {
        foreach($aciklama as $key => $val) {
            // Açıklama null veya boş string değilse (0 yazılsa bile kabul etmeli)
            if($val !== null && trim($val) !== '') {
                $invoiceProducts[] = [
                    'aciklama' => $val,
                    'miktar' => $miktar[$key] ?? 1,
                    'fiyat' => $this->convertToDecimal($fiyat[$key] ?? 0),
                    'tutar' => $this->convertToDecimal($tutar[$key] ?? 0),
                ];
            }
        }
    }

    // EĞER HİÇ ÜRÜN YOKSA HATA FIRLAT (Paraşüt hatasını engeller)
    if (empty($invoiceProducts)) {
        throw new \Exception('Faturaya en az bir ürün/hizmet satırı eklemelisiniz ve açıklama alanı boş olmamalıdır.');
    }
            // Faturayı oluştur
            $invoice = Invoice::create([
                'firma_id' => $firma->id,
                'servisid' => $request->servisid,
                'musteriid' => $request->mid,
                'faturaNumarasi' => $request->faturaNumarasi,
                'faturaTarihi' => $createdAt,
                'odemeSekli' => $request->odemeSekli,
                'toplam' => $toplam,
                'indirim' => $indirim,
                'kdv' => $kdv,
                'kdvTutar' => $request->kdvTutar,
                'genelToplam' => $genelToplam,
                'toplamYazi' => $request->toplamYazi,
                'kayitAlan' => auth()->user()->user_id,
                'faturaPdf' => $save_url,
                'faturaDurumu' => 'draft', 
                'tevkifatOrani' => $request->tevkifatOrani, 
                'tevkifatTutari' => $this->convertToDecimal($request->tevkifatTutari),
                'tevkifatKodu' => $request->tevkifatKodu,
                'kdvKodu' => $request->kdvKodu,
                'kdvAciklama' => $request->kdvAciklama,
                'faturaAciklama' => $request->faturaAciklama,
            ]);

            // Müşteri bilgisini al
            $customer = Customer::with(['country', 'state'])->find($request->mid);
            $customerName = $customer ? $customer->adSoyad : null;
            
            // Fatura oluşturma log kaydı
            ActivityLogger::logInvoiceCreated($invoice->id, $request->faturaNumarasi, $customerName);
            
            // Fatura müşterisi olarak işaretle
            Customer::where('id', $request->mid)->update(['faturaMusterisi' => '1']);

            // Ürünleri kaydet
            $aciklama = $request->aciklama;
            $miktar = $request->miktar;
            $fiyat = $request->fiyat;
            $tutar = $request->tutar;

            $invoiceProducts = [];
            foreach($aciklama as $key => $val) {
                if(!empty($val)) {
                    InvoiceProduct::create([
                        'firma_id' => $firma->id,
                        'faturaid' => $invoice->id,
                        'aciklama' => $val,
                        'miktar' => $miktar[$key],
                        'fiyat' => $this->convertToDecimal($fiyat[$key]),
                        'tutar' => $this->convertToDecimal($tutar[$key]),
                    ]);
                    
                    // Entegrasyon için de sakla
                    $invoiceProducts[] = [
                        'aciklama' => $val,
                        'miktar' => $miktar[$key],
                        'fiyat' => $this->convertToDecimal($fiyat[$key]),
                        'tutar' => $this->convertToDecimal($tutar[$key]),
                    ];
                }
            }

            // Transaction'ı commit et
            DB::commit();

            // ✅ PARAŞÜT ENTEGRASYONU - Senkron olarak gönder
            $integrationMessage = '';
            $integrationSuccess = false;
            
            if (InvoiceIntegrationFactory::hasIntegration($tenant_id)) {
                Log::info('Fatura entegrasyona gönderiliyor (senkron)', [
                    'invoice_id' => $invoice->id,
                    'tenant_id' => $tenant_id
                ]);
                
                try {
                    $integration = InvoiceIntegrationFactory::make($tenant_id);
                    
                    if ($integration) {
                        // Müşteri bilgilerini hazırla
                        $customerData = [
                            'adSoyad' => $customer->adSoyad,
                            'musteriTipi' => $customer->musteriTipi,
                            'email' => $customer->email ?? null,
                            'tel1' => $customer->tel1 ?? null,
                            'vergiNo' => $customer->vergiNo ?? null,
                            'vergiDairesi' => $customer->vergiDairesi ?? null,
                            'tcNo' => $customer->tcNo ?? '11111111111',
                            'adres' => $customer->adres ?? null,
                            'il' => $customer->country->name ?? null,
                            'ilce' => $customer->state->ilceName ?? null,
                        ];

                        // Müşteri senkronizasyonu
                        $customerSync = $integration->syncCustomer($customerData);
                        
                        if ($customerSync['success']) {
                            // Paraşüt contact ID'sini kaydet
                            $customer->update([
                                'parasut_contact_id' => $customerSync['customer_id']
                            ]);
                            
                            Log::info('Müşteri Paraşüt ID\'si kaydedildi', [
                                'customer_id' => $customer->id,
                                'parasut_contact_id' => $customerSync['customer_id']
                            ]);
                        }

                        // Fatura bilgilerini hazırla
                        $invoiceData = [
                            'id' => $invoice->id,
                            'faturaNumarasi' => $invoice->faturaNumarasi,
                            'faturaTarihi' => $invoice->faturaTarihi->format('Y-m-d'),
                            'indirim' => $invoice->indirim,
                            'odemeDurum' => $invoice->odemeDurum, 
                            'toplamTutar' => $invoice->genelToplam, 
                            'genelToplam' => $invoice->genelToplam, 
                            'kasaId' => $request->input('kasa') ?? 1, 
                            'kdvTutar' => $request->kdvTutar,
                            'faturaAciklama' => $request->faturaAciklama,
                            'customer' => $customerData,
                            'items' => $invoiceProducts,
                            'vat_rate' => $invoice->kdvKodu,
                            'vat_withholding_rate' => $invoice->tevkifatOrani,
                            'tevkifatKodu' => $request->tevkifatKodu, 
                            'kdvKodu' => $request->kdvKodu,             
                            'kdvAciklama' => $request->kdvAciklama, 
                        ];

                        // Entegrasyona gönder
                        $result = $integration->createInvoice($invoiceData);

                        if ($result['success']) {
                            // Başarılı - Faturayı güncelle
                            $invoice->update([
                                'faturaDurumu' => 'draft',
                                'integration_invoice_id' => $result['invoice_id'],
                                'integration_error' => null, // Önceki hatayı temizle
                            ]);
                            
                            $integrationSuccess = true;
                            $integrationMessage = ' Fatura Paraşüt\'e başarıyla gönderildi.';
                            
                            Log::info('Fatura entegrasyona başarıyla gönderildi', [
                                'invoice_id' => $invoice->id,
                                'integration_invoice_id' => $result['invoice_id'],
                                'invoice_number' => $result['invoice_number'] ?? null

                            ]);
                        } else {
                            // Başarısız - Hata durumunu kaydet
                            $invoice->update([
                                'faturaDurumu' => 'error',
                                'integration_error' => $result['error'] ?? 'Bilinmeyen hata'
                            ]);
                            
                            $integrationMessage = ' UYARI: Fatura kaydedildi ancak Paraşüt\'e gönderilemedi: ' . ($result['error'] ?? 'Bilinmeyen hata');
                            
                            Log::error('Fatura entegrasyona gönderilemedi', [
                                'invoice_id' => $invoice->id,
                                'error' => $result['error'] ?? 'Bilinmeyen hata'
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    // Exception durumu
                    $invoice->update([
                        'faturaDurumu' => 'error',
                        'integration_error' => $e->getMessage()
                    ]);
                    
                    $integrationMessage = ' UYARI: Fatura kaydedildi ancak entegrasyon hatası oluştu: ' . $e->getMessage();
                    
                    Log::error('Entegrasyon exception', [
                        'invoice_id' => $invoice->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            // Storage warning kontrolü
            $storageWarning = '';
            if (session()->has('storage_warning_info')) {
                $storageInfo = session()->get('storage_warning_info');
                $storageWarning = " Dikkat: Storage alanınız %{$storageInfo['usage_percentage']} dolu. Kalan alan: {$storageInfo['remaining_formatted']}.";
            }

            // Başarı mesajı
            $finalMessage = 'Fatura başarıyla eklendi.' . $integrationMessage . $storageWarning;

            return response()->json([
                'success' => true,
                'message' => $finalMessage,
                'invoice_id' => $invoice->id,
                'integration_success' => $integrationSuccess,
                'integration_invoice_id' => $invoice->integration_invoice_id ?? null
            ]);

        } catch (\Exception $e) {
            // Hata durumunda transaction geri al
            DB::rollBack();
            
            Log::error('Fatura oluşturma hatası', [
                'tenant_id' => $tenant_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Fatura eklenemedi: ' . $e->getMessage()
            ], 500);
        }
    }


// Helper method ekleyin
private function formatBytes($bytes, $precision = 2) 
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}


    public function EditInvoice($tenant_id,$id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if (!$firma) {
            return redirect()->route('giris')->with([
                'message' => 'Firma bulunamadı.',
                'alert-type' => 'danger',
            ]);
        }   
        $invoice_id = Invoice::findOrFail($id);
        $m_id = $invoice_id->musteriid;
        $musteri= Customer::where('id', $m_id)->where('firma_id', $tenant_id)->first();
        $musteriler = Customer::where('firma_id', $tenant_id)->orderBy('adSoyad', 'ASC')->get();
        $payment_methods = PaymentMethod::where(function ($query) use ($tenant_id) {
            $query->whereNull('firma_id')->orWhere('firma_id', $tenant_id);
        })->orderBy('id', 'asc')->get();
        $countries = Il::orderBy('name', 'ASC')->get();
        $invoice_products = InvoiceProduct::where('firma_id', $tenant_id)->where('faturaid',$id)->get();
        $kdvKodlari = KdvKodu::orderBy('id', 'ASC')->get();
        $tevkifatKodlari = TevkifatKodu::orderBy('id', 'ASC')->get();

        //Tahsilat durumunu önceden hesapla
        $paymentInfo = [
        'has_integration' => false,
        'remaining_amount' => $invoice_id->genelToplam,
        'payment_count' => 0,
        'is_paid' => false,
        'status' => 'unknown'
    ];
    
    if (InvoiceIntegrationFactory::hasIntegration($tenant_id)) {
        $paymentInfo['has_integration'] = true;
        
        // Eğer entegrasyona gönderildiyse
        if ($invoice_id->integration_invoice_id) {
            try {
                $parasutService = InvoiceIntegrationFactory::make($tenant_id);
                $paymentsResult = $parasutService->getInvoicePayments($invoice_id->integration_invoice_id);
                
                if ($paymentsResult['success']) {
                    $paymentInfo['remaining_amount'] = $paymentsResult['remaining_amount'];
                    $paymentInfo['payment_count'] = count($paymentsResult['payments'] ?? []);
                    $paymentInfo['is_paid'] = $paymentInfo['remaining_amount'] <= 0;
                    
                    if ($paymentInfo['is_paid']) {
                        $paymentInfo['status'] = 'paid';
                    } elseif ($paymentInfo['payment_count'] > 0) {
                        $paymentInfo['status'] = 'partial';
                    } else {
                        $paymentInfo['status'] = 'sent';
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Tahsilat durumu alınamadı', [
                    'invoice_id' => $id,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            // Henüz Paraşüt'e gönderilmemiş
            $paymentInfo['status'] = 'not_sent';
        }
    }
        return view('frontend.secure.invoices.edit_invoices',compact('invoice_id','musteri', 'musteriler','payment_methods','invoice_products', 'firma','countries','kdvKodlari','tevkifatKodlari','paymentInfo'));

    }

private function convertToDecimal($value)
{
    // Boş değer kontrolü
    if (empty($value)) {
        return 0.00;
    }
    
    // String'e çevir
    $value = (string) $value;
    
    // Türkçe format kontrolü (14,40 gibi)
    if (strpos($value, ',') !== false) {
        // Binlik ayracı noktaları kaldır (1.234,56 -> 1234,56)
        if (substr_count($value, '.') > 0 && strpos($value, ',') > strrpos($value, '.')) {
            $value = str_replace('.', '', $value);
        }
        // Virgülü noktaya çevir
        $value = str_replace(',', '.', $value);
    }
    
    // Float'a çevir ve 2 basamağa yuvarla
    return round(floatval($value), 2);
}
   public function UpdateInvoice(Request $request, $tenant_id) {
    $firma = Tenant::where('id', $tenant_id)->first();
    $invoice_id = $request->id;
    $pid = Auth::user()->user_id;
    $createdAt = Carbon::parse($request->faturaTarihi . ' ' . now()->format('H:i:s'));

    // Faturayı al
    $invoice = Invoice::where('firma_id', $tenant_id)->findOrFail($invoice_id);
    
    // ✅ Resmileştirilmiş fatura kontrolü
    if ($invoice->formalized) {
        return response()->json([
            'success' => false,
            'message' => 'Resmileştirilmiş faturalar güncellenemez!'
        ], 403);
    }

    // Fatura bilgilerini güncelle
    $invoice->faturaNumarasi = $request->faturaNumarasi;
    $invoice->faturaTarihi = $createdAt;
    $invoice->odemeSekli = $request->odemeSekli;
    $invoice->toplam = $this->convertToDecimal($request->toplam);
    $invoice->indirim = $this->convertToDecimal($request->indirim);
    $invoice->kdv = $this->convertToDecimal($request->kdv);
    $invoice->kdvTutar = $request->kdvTutar;
    $invoice->genelToplam = $this->convertToDecimal($request->genelToplam);
    $invoice->toplamYazi = $request->toplamYazi;
    $invoice->faturaDurumu = $request->faturaDurumu;
    
    // ✅ Yeni alanlar (varsa)
    if ($request->has('tevkifatOrani')) {
        $invoice->tevkifatOrani = $request->tevkifatOrani;
    }
    if ($request->has('tevkifatTutari')) {
        $invoice->tevkifatTutari = $this->convertToDecimal($request->tevkifatTutari);
    }
    if ($request->has('tevkifatKodu')) {
        $invoice->tevkifatKodu = $request->tevkifatKodu;
    }
    if ($request->has('kdvKodu')) {
        $invoice->kdvKodu = $request->kdvKodu;
    }
    if ($request->has('kdvAciklama')) {
        $invoice->kdvAciklama = $request->kdvAciklama;
    }
    if ($request->has('faturaAciklama')) {
        $invoice->faturaAciklama = $request->faturaAciklama;
    }
    
    $invoice->save();

    // Müşteri bilgisini al
    $customer = Customer::with(['country', 'state'])->find($invoice->musteriid);
    $customerName = $customer ? $customer->adSoyad : null;

    // Fatura güncelleme log kaydı
    ActivityLogger::logInvoiceUpdated($invoice_id, $request->faturaNumarasi, $customerName);

    // Eski ürünleri sil
    $oldProducts = InvoiceProduct::where('firma_id', $firma->id)->where('faturaid', $invoice_id)->get();
    foreach($oldProducts as $product){
        InvoiceProduct::where('firma_id', $tenant_id)->findOrFail($product->id)->delete();
    }

    // Yeni ürünleri ekle
    $aciklama = $request->aciklama;
    $miktar = $request->miktar;
    $fiyat = $request->fiyat;
    $tutar = $request->tutar;

    $invoiceProducts = [];
    foreach ($aciklama as $key => $val) {
        if (!empty($val)) {
            InvoiceProduct::create([
                'firma_id' => $firma->id,
                'faturaid' => $invoice_id,
                'aciklama' => $val,
                'miktar' => $miktar[$key],
                'fiyat' => $this->convertToDecimal($fiyat[$key]),
                'tutar' => $this->convertToDecimal($tutar[$key]),
            ]);
            
            // Paraşüt için de sakla
            $invoiceProducts[] = [
                'aciklama' => $val,
                'miktar' => $miktar[$key],
                'fiyat' => $this->convertToDecimal($fiyat[$key]),
                'tutar' => $this->convertToDecimal($tutar[$key]),
            ];
        }
    }
    
    if(isset($invoice->servisid)){
        if(!empty($invoice->servisid)){
            Service::findOrFail($invoice->servisid)->update([
                'faturaNumarasi' => $request->faturaNumarasi,
            ]);
        }
    }

    // PARAŞÜT ENTEGRASYONU - Faturayı Paraşüt'te de güncelle
    $integrationMessage = '';
    
    if (InvoiceIntegrationFactory::hasIntegration($tenant_id) && $invoice->integration_invoice_id) {
        try {
            $parasutService = InvoiceIntegrationFactory::make($tenant_id);
            
            // Müşteri bilgileri değiştiyse Paraşüt'te de güncelle
            if ($customer->parasut_contact_id) {
                $customerData = [
                    'adSoyad' => $customer->adSoyad,
                    'musteriTipi' => $customer->musteriTipi,
                    'email' => $customer->email ?? null,
                    'tel1' => $customer->tel1 ?? null,
                    'vergiNo' => $customer->vergiNo ?? null,
                    'vergiDairesi' => $customer->vergiDairesi ?? null,
                    'tcNo' => $customer->tcNo ?? '11111111111',
                    'adres' => $customer->adres ?? null,
                    'il' => $customer->country->name ?? null,
                    'ilce' => $customer->state->ilceName ?? null,
                ];
                
                $customerUpdateResult = $parasutService->updateCustomer(
                    $customer->parasut_contact_id,
                    $customerData
                );
                
                if ($customerUpdateResult['success']) {
                    Log::info('Müşteri Paraşüt\'te güncellendi', [
                        'customer_id' => $customer->id,
                        'parasut_contact_id' => $customer->parasut_contact_id
                    ]);
                }
            }
            if ($invoice->integration_invoice_id) {
                // Önce güncellenebilir mi kontrol et
                $canUpdate = $parasutService->canUpdateInvoice($invoice->integration_invoice_id);
                
                if ($canUpdate['can_update']) {
                    // Paraşüt için veri hazırla
                    $parasutData = [
                        'faturaNumarasi' => $invoice->faturaNumarasi,
                        'faturaTarihi' => $invoice->faturaTarihi->format('Y-m-d'),
                        'indirim' => $invoice->indirim,
                        'genelToplam' => $invoice->genelToplam,
                        'kdvTutar' => $invoice->kdvTutar,
                        'faturaAciklama' => $invoice->faturaAciklama,
                        'vat_withholding_rate' => $invoice->tevkifatOrani,
                        'items' => $invoiceProducts,
                        'tevkifatKodu' => $request->tevkifatKodu,
                        'kdvKodu' => $request->kdvKodu,
                        'kdvAciklama' => $request->kdvAciklama,
                    ];
                    
                    $updateResult = $parasutService->updateInvoice($invoice->integration_invoice_id, $parasutData);
                    
                    if ($updateResult['success']) {
                        $integrationMessage = ' Paraşüt\'te de güncellendi.';
                        Log::info('Fatura Paraşüt\'te güncellendi', [
                            'invoice_id' => $invoice->id,
                            'parasut_id' => $invoice->integration_invoice_id
                        ]);
                    } else {
                        $integrationMessage = ' Uyarı: Paraşüt güncellemesi başarısız - ' . ($updateResult['error'] ?? 'Bilinmeyen hata');
                        Log::warning('Paraşüt güncelleme başarısız', [
                            'invoice_id' => $invoice->id,
                            'error' => $updateResult['error'] ?? 'Bilinmeyen hata'
                        ]);
                    }
                } else {
                    $integrationMessage = ' (Paraşüt\'te güncellenemedi: ' . ($canUpdate['reason'] ?? 'Resmileştirilmiş') . ')';
                }
            }
            
        } catch (\Exception $e) {
            $integrationMessage = ' Uyarı: Paraşüt hatası - ' . $e->getMessage();
            Log::error('Paraşüt güncelleme exception', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    $notification = array(
        'message' => 'Fatura Bilgileri Başarıyla Güncellendi' . $integrationMessage,
        'alert-type' => 'success'
    );
    return response()->json(['success' => $notification]);
}

public function addPaymentToInvoice(Request $request, $firma_id)
{
    $request->validate([
        'invoice_id' => 'required',
        'account_id' => 'required',
        'date' => 'required|date',
        'description' => 'nullable|string'
    ]);

    try {
        $invoice = Invoice::where('id', $request->invoice_id)
            ->where('firma_id', $firma_id)
            ->firstOrFail();

        if (!InvoiceIntegrationFactory::hasIntegration($firma_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Paraşüt entegrasyonu aktif değil'
            ], 400);
        }

        $parasutService = InvoiceIntegrationFactory::make($firma_id);
        $parasutInvoiceId = $parasutService->getInvoiceId($invoice);
        
        if (!$parasutInvoiceId) {
            return response()->json([
                'success' => false,
                'message' => 'Fatura Paraşüt\'te bulunamadı'
            ], 404);
        }

        // Kalan tutarı al
        $invoicePayments = $parasutService->getInvoicePayments($parasutInvoiceId);
        $remainingAmount = $invoicePayments['remaining_amount'] ?? $invoice->genelToplam;

        if ($remainingAmount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Bu fatura zaten tamamen ödenmiş. Kalan tutar: 0 TL'
            ], 400);
        }

        // Kalan tutarın TAMAMINI ekle
        $paymentData = [
            'account_id' => $request->account_id,
            'amount' => $remainingAmount,
            'date' => $request->date,
            'description' => $request->description ?? 'Tahsilat'
        ];

        $paymentResult = $parasutService->addPayment(
            $parasutInvoiceId,
            $paymentData
        );

        if (!$paymentResult['success']) {
            Log::error('Paraşüt tahsilat ekleme başarısız', [
                'invoice_id' => $invoice->id,
                'error' => $paymentResult['error'] ?? 'Bilinmeyen hata'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $paymentResult['error'] ?? 'Ödeme eklenemedi'
            ], 400);
        }

        // Veritabanını güncelle
        $paymentIds = $invoice->parasutPaymentIds ?? [];
        $newPaymentId = (string) $paymentResult['payment_id'];
        
        if (!in_array($newPaymentId, $paymentIds, true)) {
            $paymentIds[] = $newPaymentId;
        }

        $invoice->has_payment = true;
        $invoice->parasutPaymentIds = $paymentIds;
        $invoice->integration_invoice_id = $parasutInvoiceId;
        $invoice->save();

        Log::info('Tahsilat eklendi', [
            'invoice_id' => $invoice->id,
            'payment_id' => $newPaymentId,
            'amount' => $remainingAmount,
            'total_payments' => count($paymentIds)
        ]);
        Cache::forget("invoice_payments_{$request->invoice_id}");
        // ✅ Tüm gerekli verileri döndür
        return response()->json([
            'success' => true,
            'message' => 'Tahsilat başarıyla eklendi',
            'payment_id' => $newPaymentId,
            'amount_paid' => (float) $remainingAmount, // ✅ Float olarak döndür
            'remaining_amount' => 0,
            'total_payments' => count($paymentIds)
        ], 200); // ✅ Status code ekle

    } catch (\Exception $e) {
        Log::error('Tahsilat ekleme hatası: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Bir hata oluştu: ' . $e->getMessage()
        ], 500);
    }
}

public function getParasutAccounts($firma_id)
{
    try {
        if (!InvoiceIntegrationFactory::hasIntegration($firma_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Paraşüt entegrasyonu aktif değil'
            ], 400);
        }

        // ✅ Cache'den al (1 saat)
        $cacheKey = "parasut_accounts_{$firma_id}";
        
        $accounts = Cache::remember($cacheKey, now()->addHour(), function() use ($firma_id) {
            $parasutService = InvoiceIntegrationFactory::make($firma_id);
            $result = $parasutService->getAccounts();
            
            return $result['success'] ? $result['accounts'] : [];
        });

        return response()->json([
            'success' => true,
            'accounts' => $accounts
        ]);

    } catch (\Exception $e) {
        Log::error('Hesaplar getirme hatası: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Hesaplar getirilemedi'
        ], 500);
    }
}

public function getInvoicePayments($firma_id, $invoice_id)
{
    try {
        $invoice = Invoice::where('id', $invoice_id)
            ->where('firma_id', $firma_id)
            ->firstOrFail();

        if (!InvoiceIntegrationFactory::hasIntegration($firma_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Paraşüt entegrasyonu aktif değil'
            ], 400);
        }

        // ✅ Cache'den al (5 dakika)
        $cacheKey = "invoice_payments_{$invoice_id}";
        
        $result = Cache::remember($cacheKey, now()->addMinutes(5), function() use ($firma_id, $invoice, $invoice_id) {
            $parasutService = InvoiceIntegrationFactory::make($firma_id);
            
            $parasutInvoiceId = $parasutService->getInvoiceId($invoice);
            
            if (!$parasutInvoiceId) {
                return [
                    'success' => false,
                    'message' => 'Bu fatura Paraşüt\'te bulunamadı',
                    'payments' => [],
                    'remaining_amount' => 0
                ];
            }

            return $parasutService->getInvoicePayments($parasutInvoiceId);
        });

        // Senkronizasyon
        if ($result['success'] && !empty($result['payments']) && !$invoice->has_payment) {
            $paymentIds = array_column($result['payments'], 'id');
            
            $invoice->update([
                'has_payment' => true,
                'parasutPaymentIds' => $paymentIds
            ]);

            Log::info('Tahsilatlar senkronize edildi', [
                'invoice_id' => $invoice->id,
                'payment_count' => count($paymentIds)
            ]);
        }

        return response()->json(array_merge($result, [
            'has_payment' => $invoice->has_payment,
            'stored_payment_ids' => $invoice->parasutPaymentIds ?? []
        ]));

    } catch (\Exception $e) {
        Log::error('Fatura ödemeleri getirme hatası: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Ödemeler getirilemedi'
        ], 500);
    }
}
public function deletePaymentFromInvoice(Request $request, $firma_id)
{
    $request->validate([
        'invoice_id' => 'required',
        'payment_id' => 'required'
    ]);

    try {
        $invoice = Invoice::where('id', $request->invoice_id)
            ->where('firma_id', $firma_id)
            ->firstOrFail();

        if (!$invoice->integration_invoice_id) {
            return response()->json([
                'success' => false,
                'message' => 'Bu fatura Paraşüt\'te bulunamadı'
            ], 404);
        }

        // Mevcut payment ID'leri kontrol et
        $paymentIds = $invoice->parasutPaymentIds ?? [];
        $paymentIdToDelete = (string) $request->payment_id;
        $paymentExists = false;
        
        foreach ($paymentIds as $id) {
            if ((string) $id === $paymentIdToDelete) {
                $paymentExists = true;
                break;
            }
        }

        if (!$paymentExists) {
            return response()->json([
                'success' => false,
                'message' => 'Bu tahsilat bu faturada bulunamadı'
            ], 404);
        }

        if (!InvoiceIntegrationFactory::hasIntegration($firma_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Paraşüt entegrasyonu aktif değil'
            ], 400);
        }

        // Paraşüt'ten sil
        $parasutService = InvoiceIntegrationFactory::make($firma_id);
        $deleteResult = $parasutService->deleteInvoicePayment(
            $invoice->integration_invoice_id,
            $paymentIdToDelete
        );

        if (!$deleteResult['success']) {
            return response()->json([
                'success' => false,
                'message' => $deleteResult['message']
            ], 400);
        }

        // Veritabanından sil - String karşılaştırması ile
        $updatedPaymentIds = [];
        foreach ($paymentIds as $id) {
            if ((string) $id !== $paymentIdToDelete) {
                $updatedPaymentIds[] = (string) $id;
            }
        }

        $updatedPaymentIds = array_values($updatedPaymentIds);
        $invoice->parasutPaymentIds = $updatedPaymentIds;
        
        // Eğer hiç tahsilat kalmadıysa has_payment'ı false yap
        if (empty($updatedPaymentIds)) {
            $invoice->has_payment = false;
        }
        
        $invoice->save();

        Log::info('Tahsilat silindi', [
            'invoice_id' => $invoice->id,
            'payment_id' => $paymentIdToDelete,
            'remaining_payments' => count($updatedPaymentIds)
        ]);
        Cache::forget("invoice_payments_{$request->invoice_id}");
        return response()->json([
            'success' => true,
            'message' => 'Tahsilat başarıyla silindi',
            'remaining_payments' => count($updatedPaymentIds)
        ]);

    } catch (\Exception $e) {
        Log::error('Tahsilat silme hatası: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Bir hata oluştu: ' . $e->getMessage()
        ], 500);
    }
}

public function formalizeInvoice(Request $request, $firma_id)
{
    $request->validate([
        'invoice_id' => 'required|integer'
    ]);

    try {
        $invoice = Invoice::where('id', $request->invoice_id)
            ->where('firma_id', $firma_id)
            ->firstOrFail();

        if (!InvoiceIntegrationFactory::hasIntegration($firma_id)) {
            return response()->json(['success' => false, 'message' => 'Entegrasyon aktif değil'], 400);
        }

        $parasutService = InvoiceIntegrationFactory::make($firma_id);
        
        // Fatura ID kontrolü
        $parasutInvoiceId = $parasutService->getInvoiceId($invoice);
        if (!$parasutInvoiceId) {
            return response()->json(['success' => false, 'message' => 'Fatura Paraşüt\'te bulunamadı.'], 404);
        }

        $customer = Customer::find($invoice->musteriid);
        
        // VKN / TCKN Belirleme ve Temizleme
        $vkn = null;
        if ($customer->musteriTipi == '2') { // Kurumsal
            $vkn = $customer->vergiNo;
        } else { // Bireysel
            $vkn = $customer->tcNo ?? '11111111111';
        }
        
        // Boşlukları temizle
        $vkn = preg_replace('/\s+/', '', $vkn);

        // 1. e-Fatura Mükellefiyet Kontrolü
        $eInvoiceStatus = $parasutService->checkCustomerEInvoiceStatus($vkn);
        $formalizationType = $eInvoiceStatus['type']; // 'e-invoice' veya 'e-archive'

        // 2. Resmileştirme İsteği
        if ($formalizationType === 'e-invoice') {
            // e-Fatura ise VKN ve Senaryo (basic/commercial) zorunludur.
            // Varsayılan olarak 'basic' (Temel Fatura) gönderiyoruz. Ticari için 'commercial' yapabilirsiniz.
            $result = $parasutService->formalizeAsEInvoice($parasutInvoiceId, $vkn, 'basic');
        } else {
            // e-Arşiv ise (İnternet satışı false gönderiyoruz, gerekirse true yapın)
            $result = $parasutService->formalizeAsEArchive($parasutInvoiceId, false);
        }

        if ($result['success']) {
            $invoice->update([
                'formalized' => true,
                'formalization_status' => 'pending', // Paraşüt job işlemi asenkrondur
                'formalization_type' => $formalizationType,
                'formalization_error' => null,
                'formalization_job_id' => $result['job_id']
            ]);

            return response()->json([
                'success' => true,
                'message' => "Fatura {$formalizationType} kuyruğuna alındı. Lütfen durum kontrolü yapınız.",
                'type' => $formalizationType,
                'status' => 'pending'
            ]);
        } else {
            // Hata detayını güncelle
            $invoice->update([
                'formalized' => false,
                'formalization_status' => 'error',
                'formalization_error' => $result['error']
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Hata: ' . $result['error']
            ], 400);
        }

    } catch (\Exception $e) {
        Log::error('Resmileştirme Controller Hatası: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Sistem hatası: ' . $e->getMessage()], 500);
    }
}


/**
 * Resmileştirme durumunu kontrol et
 */
public function checkFormalizationStatusController(Request $request, $firma_id, $invoice_id)
{
    try {
        $invoice = Invoice::where('id', $invoice_id)
            ->where('firma_id', $firma_id)
            ->firstOrFail();

        if (!$invoice->formalized) {
            return response()->json([
                'success' => true,
                'formalized' => false,
                'message' => 'Fatura henüz resmileştirilmemiş'
            ]);
        }

        if (!InvoiceIntegrationFactory::hasIntegration($firma_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Paraşüt entegrasyonu aktif değil'
            ], 400);
        }

        $parasutService = InvoiceIntegrationFactory::make($firma_id);
    
    // Durum kontrolü yap
        $statusCheck = $parasutService->checkFormalizationStatus($invoice->integration_invoice_id, true);

    if ($statusCheck['success']) {
            
            // Güncellenecek veriler
            $updateData = [
                'formalization_status' => $statusCheck['status'],
                'formalized' => $statusCheck['formalized']
            ];

            // Fatura numarasını güncelle
            if (!empty($statusCheck['invoice_number'])) {
                $updateData['faturaNumarasi'] = $statusCheck['invoice_number'];
                
                Log::info('Fatura numarası Paraşüt ile senkronize edildi', [
                    'old' => $invoice->faturaNumarasi,
                    'new' => $statusCheck['invoice_number']
                ]);
            }

            // ✅ PDF URL'i varsa kaydet
            if (!empty($statusCheck['pdf_url'])) {
                $updateData['formalization_pdf_url'] = $statusCheck['pdf_url'];
                $updateData['formalization_pdf_expires_at'] = $statusCheck['pdf_expires_at'];
                
                Log::info('Fatura PDF URL\'i güncellendi', [
                    'invoice_id' => $invoice->id,
                    'expires_at' => $statusCheck['pdf_expires_at']
                ]);
            }

            $invoice->update($updateData);

            // Status text mapping
            $statusTexts = [
                'pending' => 'Onay Bekliyor',
                'sent' => 'Gönderildi',
                'error' => 'Hata Oluştu'
            ];

            return response()->json([
                'success' => true,
                'formalized' => $statusCheck['formalized'],
                'status' => $statusCheck['status'],
                'status_text' => $statusTexts[$statusCheck['status']] ?? 'Bilinmiyor',
                'invoice_number' => $statusCheck['invoice_number'] ?? null,
                'pdf_url' => $statusCheck['pdf_url'] ?? null, // ✅ Paraşüt PDF URL'i
                'pdf_expires_at' => $statusCheck['pdf_expires_at'] ?? null,
                'type' => $invoice->formalization_type,
                'raw_status' => $statusCheck['raw_status'] ?? null
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $statusCheck['error'] ?? 'Durum kontrol edilemedi'
            ], 400);
        }

    } catch (\Exception $e) {
        Log::error('Durum kontrol hatası: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Bir hata oluştu'
        ], 500);
    }
}
    public function DeleteInvoice($tenant_id, $id) {
    // Önce faturayı bul
    $fatura = Invoice::where('firma_id', $tenant_id)->findOrFail($id);

    // ✅ Resmileştirilmiş fatura kontrolü
    if ($fatura->formalized) {
        $typeText = $fatura->formalization_type === 'e-invoice' ? 'e-Fatura' : 'e-Arşiv';
        
        $notification = [
            'message' => "Bu fatura {$typeText} olarak resmileştirildiği için silinemez.",
            'alert-type' => 'error'
        ];

        return redirect()->route('all.invoices', $tenant_id)->with($notification);
    }

    // Müşteri bilgisini al (silmeden önce)
    $customer = Customer::find($fatura->musteriid);
    $customerName = $customer ? $customer->adSoyad : null;
    $invoiceNumber = $fatura->faturaNumarasi;
    $invoiceId = $fatura->id;

    // Faturanın servis numarasını güncelle
    Service::where('id', $fatura->servisid)->update([
        'faturaNumarasi' => null,
    ]);

    // Faturaya bağlı ürünleri sil
    $eskiUrunler = InvoiceProduct::where('firma_id', $tenant_id)
        ->where('faturaid', $id)
        ->get();

    foreach ($eskiUrunler as $urun) {
        $urun->delete();
    }

    // Faturayı sil
    $fatura->delete();

    // Fatura silme log kaydı
    ActivityLogger::logInvoiceDeleted($invoiceId, $invoiceNumber, $customerName);

    $notification = [
        'message' => 'Fatura Başarıyla Silindi',
        'alert-type' => 'success'
    ];

    return redirect()->route('all.invoices', $tenant_id)->with($notification);
}

    public function ShowInvoice($tenant_id,$id) {
        $invoice_id = Invoice::findOrFail($id);
        $firma = Tenant::where('id', $tenant_id)->first();
        return view('frontend.secure.invoices.show_invoices',compact('invoice_id','firma'));

    }

    public function UploadInvoice(Request $request, $tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $invoice_id = $request->id;
        
        $document = $request->file('pdf');
        if($document) {
            $extension = $request->file('pdf')->extension();
            if($extension != "jpg" && $extension != "png" && $extension != "jpeg" && $extension != "pdf"){
                $notification = array(
                    'message' => ' Dosya  uzantısı sadece jpg,png,jpeg veya pdf olmalı',
                    'alert-type' => 'warning'
                );
                return redirect()->back()->with($notification);
            }
            $fileName = time().'.'.$document->getClientOriginalExtension();  
            $save_url = $document->move('upload/uploads', $fileName);
            Invoice::where('firma_id', $tenant_id)->find($invoice_id)->update([
                'faturaPdf' => $save_url,
            ]);

            return redirect()->back()->with('faturaPdf',$fileName);
        }
        $notification = array(
            'message' => 'Fatura başarıyla yüklendi',
            'alert-type' => 'success',
        );
        return response()->json(['success' => $notification]);
    }

    public function DeleteEinvoice($tenant_id, $id){
        $invoice_id = Invoice::findOrFail($id);
        $doc = $invoice_id->faturaPdf;
        
        Invoice::findOrFail($id)->update([
            'faturaPdf' => null,
        ]);

        $notification = array(
            'message' => 'Fatura başarıyla silindi',
            'alert-type' => 'success',
        );
        return response()->json(true);
    }

    public function testIntegration($tenant_id)
{
    try {
        $integration = InvoiceIntegrationFactory::make($tenant_id);
        
        if (!$integration) {
            return response()->json([
                'success' => false,
                'message' => 'Aktif entegrasyon bulunamadı'
            ]);
        }

        $testResult = $integration->testConnection();

        return response()->json([
            'success' => $testResult,
            'message' => $testResult ? 'Bağlantı başarılı' : 'Bağlantı başarısız'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Hata: ' . $e->getMessage()
        ], 500);
    }
}
}