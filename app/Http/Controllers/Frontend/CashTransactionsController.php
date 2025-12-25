<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\DeviceBrand;
use App\Models\DeviceType;
use App\Models\PaymentMethod;
use App\Models\PaymentType;
use App\Models\Service;
use App\Models\ServiceMoneyAction;
use App\Models\StockSupplier;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Cache;


class CashTransactionsController extends Controller
{
    public function Filter(Request $request, $tenant_id)
    {   
        $firma = Tenant::where('id', $tenant_id)->first();
        if (!$firma) {
            return redirect()->route('giris')->with([
                'message' => 'Firma bulunamadı.',
                'alert-type' => 'danger',
            ]);
        }
        $cash_transactions = CashTransaction::with('perso_nel','islem_yapan','odemeturu','payment_method','servisler')->where('firma_id', $tenant_id)->get();
        $payment_types = PaymentType::orderBy('odemeTuru', 'ASC')->get();
        $payment_methods = PaymentMethod::orderBy('odemeSekli','asc')->get();
        $personel = User::where('tenant_id', $tenant_id)->whereDoesntHave('roles', function ($query) {
            $query->whereIn('name', ['Admin','Super Admin']);
        })->where('status', 1)->orderBy('name', 'asc')->get();
        //$musteriler = Customer::where('firma_id', $tenant_id)->orderBy('adSoyad', 'ASC')->get();
        $musteriler = [];
        $bayiler = User::where('tenant_id', $tenant_id)->role('Bayi')->where('status', 1)->orderBy('name', 'asc')->get();
        $tedarikciler = StockSupplier::where('firma_id', $tenant_id)->get();
        $markalar = DeviceBrand::where(function($query) use ($firma) {
                $query->whereNull('firma_id')
                    ->orWhere('firma_id', $firma->id);
            })->orderBy('marka', 'asc')->get();
        $cihazlar = DeviceType::where(function($query) use ($firma) {
                $query->whereNull('firma_id')
                    ->orWhere('firma_id', $firma->id);
            })->orderBy('cihaz', 'asc')->get();

        if ($request->ajax()) {
            
            $data = CashTransaction::with('perso_nel','islem_yapan','odemeturu','payment_method','servisler')->where('firma_id', $tenant_id);
            // Tarih filtreleme
            $hasUserSelectedDate = $request->filled('from_date') && $request->filled('to_date') && !$this->isDefaultDateRange($request);
            
            $hasSearchOrOtherFilters = !empty(trim($request->get('search', ''))) || 
                                        $request->filled('odemeSekil') ||
                                        $request->filled('staff') ||
                                        $request->filled('tedarikci') ||
                                        $request->filled('marka') ||
                                        $request->filled('cihaz') ||
                                        $request->filled('odemeYonu') ||
                                        ($request->filled('odemeDurum') && $request->get('odemeDurum') != '0') ||
                                        $request->filled('bayi') ||
                                        $request->filled('odemeTuru');

            if ($hasUserSelectedDate) {
                // Kullanıcı özel tarih seçtiyse
                $this->applyMainDateRange($data, $request);
            } elseif (!$hasUserSelectedDate && !$hasSearchOrOtherFilters) {
                // Hiçbir tarih veya arama/filtre yoksa, varsayılan olarak BUGÜNÜ göster
                $from = Carbon::today()->startOfDay();
                $to   = Carbon::today()->endOfDay();
                $data->whereBetween('created_at', [$from, $to]);
            }
            // Eğer $hasSearchOrOtherFilters true ise ancak tarih filtresi seçilmemişse,
            // herhangi bir tarih kısıtlaması uygulanmaz, bu da TÜM kayıtlarda arama yapılmasını sağlar.


            //Dashboard kasa total filtreleme 
            if ($request->has('dashboard_filter') && $request->dashboard_filter == 1) {
                if ($request->has('dashboard_istatistik_tarih1') && $request->has('dashboard_istatistik_tarih2')) {
                    $data->whereDate('created_at', '>=', $request->dashboard_istatistik_tarih1)
                        ->whereDate('created_at', '<=', $request->dashboard_istatistik_tarih2);
                }
            }
            
            // Ödeme şekli filtreleme
            if($request->filled('odemeSekil') && $request->get('odemeSekil') != '') {
                $data->where('odemeSekli', $request->get('odemeSekil'));
            }

            // Personel filtreleme
            if($request->filled('staff') && $request->get('staff') != '') {
                $data->where('personel', $request->get('staff'));
            }

            // Tedarikçi filtreleme
            if($request->filled('tedarikci') && $request->get('tedarikci') != '') {
                $data->where('tedarikci', $request->get('tedarikci'));
            }

            // Marka filtreleme
            if($request->filled('marka') && $request->get('marka') != '') {
                $data->where('marka', $request->get('marka'));
            }

            // Cihaz filtreleme
            if($request->filled('cihaz') && $request->get('cihaz') != '') {
                $data->where('cihaz', $request->get('cihaz'));
            }

            // Ödeme yönü filtreleme
            if($request->filled('odemeYonu') && $request->get('odemeYonu') != '') {
                $data->where('odemeYonu', $request->get('odemeYonu'));
            }

            // Ödeme durumu filtreleme - Özel durum: 0 değeri "Hepsi" anlamında
            if($request->filled('odemeDurum') && $request->get('odemeDurum') != '0') {
                $data->where('odemeDurum', $request->get('odemeDurum'));
            }

            // Bayi filtreleme
            if($request->filled('bayi') && $request->get('bayi') != '') {
                $data->where('personel', $request->get('bayi'));
            }

            // Ödeme türü filtreleme
            if($request->filled('odemeTuru') && $request->get('odemeTuru') != '') {
                $data->where('odemeTuru', $request->get('odemeTuru'));
            }

            // Sıralama işlemi
            if ($request->has('order')) {
                $order = $request->get('order')[0];
                $columns = $request->get('columns');
                $orderColumn = $columns[$order['column']]['data'];
                $orderDir = $order['dir'];
                
                if($orderColumn == 'odemeTuru'){
                    $data->leftJoin('payment_types', 'cash_transactions.odemeTuru', '=', 'payment_types.id')
                    ->addSelect(['cash_transactions.*', 'payment_types.odemeTuru as odemeType'])
                    ->orderBy('payment_types.odemeTuru',$orderDir);
                }
                else {
                    $data->orderBy($orderColumn, $orderDir);
                }
            } else {
                $data->orderBy('created_at','desc');
            }
            
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('id', function($row){
                 
                    return '<a class="t-link editCashTransactions address idWrap" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editCashTransactionsModal">'.$row->id.'</a>';
                
                })
                ->addColumn('created_at', function($row){
                    $sontarih = Carbon::parse($row->created_at)->format('d/m/Y');
                    return '<a class="t-link editCashTransactions address" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editCashTransactionsModal"><div class="mobileTitle">Tarih:</div>'.$sontarih.'</a>';
                
                })
                 ->addColumn('pid', function($row){
                    return '<a class="t-link editCashTransactions " href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editCashTransactionsModal"><div class="mobileTitle">Tarih:</div>'.$row->islem_yapan?->name.'</a>';
                
                })
                ->addColumn('odemeTuru', function($row){
                 
                    return '<a class="t-link editCashTransactions" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editCashTransactionsModal"><div class="mobileTitle">Ö. Türü:</div>'.$row->odemeturu?->odemeTuru.'</a>';
                
                })
                ->addColumn('aciklama', function($row){
                    if(!is_null($row->servis)){
                        return '<a class="t-link editCashTransactions address" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editCashTransactionsModal"><div class="mobileTitle">Açıklama:</div>Servis No: '.$row->servisler?->id.'  ( '.$row->servisler?->asamalar?->asama.' )</a>';
                    }
                    elseif(!is_null($row->personel)){
                        return '<a class="t-link editCashTransactions address" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editCashTransactionsModal"><div class="mobileTitle">Açıklama:</div>'.$row->perso_nel?->name.' : '.$row->aciklama.'</a>';
                    }elseif(!is_null($row->stokIslem)){
                        return '<a class="t-link editCashTransactions address" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editCashTransactionsModal"><div class="mobileTitle">Açıklama:</div>Stok Id: '.$row->stok_hareket?->id.'  '.$row->stok_hareket?->stok?->urunAdi.'</a>';
                    }else{
                        return '<a class="t-link editCashTransactions address" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editCashTransactionsModal"><div class="mobileTitle">Açıklama:</div>'.$row->aciklama.'</a>';
                    }              
                })
                ->addColumn('odemeSekli', function($row){
                 
                    return '<a class="t-link editCashTransactions address" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editCashTransactionsModal"><div class="mobileTitle">Ö. Şekli:</div>'.$row->payment_method?->odemeSekli.'</a>';
                
                })
                
                ->addColumn('odemeYonuBorc', function($row) {
                    if ($row->odemeYonu == "1") {
                        return '<a class="t-link editCashTransactions address" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editCashTransactionsModal"><div class="mobileTitle">Borç(Gelen):</div><span style="color: green;font-weight:700;">+ '.number_format($row->fiyat, 2).' TL</span></a>';
                    } else {
                        return '';
                    }
                })
                ->addColumn('odemeYonuAlacak', function($row) {
                    if ($row->odemeYonu == "2") {
                        return '<a class="t-link editCashTransactions address" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editCashTransactionsModal"><div class="mobileTitle">Alacak(Giden):</div><span style="color: red;font-weight:700;">- '.number_format($row->fiyat, 2).' TL</span></a>';
                    } else {
                        return '';
                    }
                })
                ->addColumn('fiyat', function($row) {
                    $borc = 0;
                    $alacak = 0;
                    if ($row->odemeYonu == "1") {
                        $borc += $row->fiyat;
                    } else if($row->odemeYonu == "2") {
                        $alacak += $row->fiyat;
                    }
                    $bakiye = $borc - $alacak;
                    if($bakiye > 0){
                        return '<a class="t-link editCashTransactions address" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editCashTransactionsModal"><div class="mobileTitle">Bakiye(Toplam):</div><span style="color: green;font-weight:700;">+ '.number_format($bakiye, 2).' TL</span></a>';
                    }else {
                        return '<a class="t-link editCashTransactions address" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editCashTransactionsModal"><div class="mobileTitle">Bakiye(Toplam):</div><span style="color: red;font-weight:700;"> '.number_format($bakiye, 2).' TL</span></a>';
                    }

                })
                ->addColumn('action', function($row){
                    $deleteUrl = route('delete.cash.transaction', [$row->firma_id, $row->id]);

                    $editButton = '';
                    $viewButton = '';
                    $deleteButton = '';

                    if(Auth::user()->can('Kasa Hareketi Düzenleyebilir')){
                        $editButton = '<a href="javascript:void(0);" data-bs-id="'.$row->id.'" class="btn btn-outline-warning btn-sm editCashTransactions mobilBtn mbuton1" data-bs-toggle="modal" data-bs-target="#editCashTransactionsModal" title="Düzenle"><i class="fas fa-edit"></i> <span> Düzenle</span></a>';
                        $viewButton = '<a href="javascript:void(0);" data-bs-id="'.$row->id.'" class="btn btn-outline-primary btn-sm editCashTransactions mobilBtn mbuton1" data-bs-toggle="modal" data-bs-target="#editCashTransactionsModal" title="Düzenle"><i class="fas fa-eye"></i> <span> Düzenle</span></a>';
                    
                    }
                    if(Auth::user()->can('Kasa Hareketi Silebilir')){
                        $deleteButton = '<a href="'.$deleteUrl.'" class="btn btn-outline-danger btn-sm mobilBtn" id="delete" title="Sil"><i class="fas fa-trash-alt"></i> <span> Sil</span></a>';
                    }
                    return  $viewButton . ' '. $editButton . '  ' . $deleteButton;
                })
                ->filter(function ($instance) use ($request){
                    if($request->get('odemeTuru')){
                        $instance->where('odemeTuru', $request->get('odemeTuru'));
                    }

                    if (!empty($request->get('search'))) {
                        $instance->where(function($w) use($request){
                           $search = $request->get('search');
                           $w->where('fiyat', 'LIKE', "%$search%")
                           ->orWhereHas('servisler', function($q) use($search) {
                            $q->where('id', 'LIKE', "%$search%");
                         });
                       });
                   }

                   
                })
                ->rawColumns(['id','created_at','pid','odemeTuru','aciklama','odemeSekli','odemeYonuBorc', 'odemeYonuAlacak','fiyat','action'])         
                ->make(true);  
        }
        
        return view('frontend.secure.cash_transactions.all_cash_transaction',compact('cash_transactions', 'payment_types', 'payment_methods' ,'personel','musteriler','firma','bayiler','tedarikciler','markalar','cihazlar'));
        
    }
// ============================================
// YARDIMCI METODLAR (Helper Methods)
// ============================================

/**
 * Gelen tarih aralığının varsayılan (bugün) olup olmadığını kontrol eder
 */
private function isDefaultDateRange(Request $request): bool
{
    if (!$request->filled('from_date') || !$request->filled('to_date')) {
        return false;
    }
    
    try {
        $from = Carbon::createFromFormat('Y-m-d', $request->from_date)->startOfDay();
        $to = Carbon::createFromFormat('Y-m-d', $request->to_date)->endOfDay();
        $defaultFrom = Carbon::today()->startOfDay();
        $defaultTo = Carbon::today()->endOfDay();
        
        return $from->equalTo($defaultFrom) && $to->equalTo($defaultTo);
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * Ana tarih aralığı filtresini uygular
 */
private function applyMainDateRange($query, Request $request): void
{
    if ($request->filled('from_date') && $request->filled('to_date')) {
        $from = Carbon::createFromFormat('Y-m-d', $request->from_date)->startOfDay();
        $to   = Carbon::createFromFormat('Y-m-d', $request->to_date)->endOfDay();
        $query->whereBetween('created_at', [$from, $to]);
    }
}
    public function searchMusteri(Request $request, $tenant_id)
{
    $searchField = $request->input('musteriGetir');
    
    $musteriler = Customer::where('firma_id', $tenant_id)
        ->where('durum', '1')
        ->where(function($query) use ($searchField) {
            $query->where('adSoyad', 'like', '%' . $searchField . '%')
                  ->orWhere('tel1', 'like', '%' . $searchField . '%');
        })
        ->with(['state', 'country'])
        ->orderBy('adSoyad', 'ASC')
        ->limit(20)
        ->get();
    
    return response()->json($musteriler);
}

    public function updateTotalValues(Request $request, $tenant_id)
    {  
        $data = CashTransaction::query();
        
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $start = Carbon::parse($request->input('from_date'))->startOfDay();
            $end = Carbon::parse($request->input('to_date'))->endOfDay();

            $data->whereBetween('created_at', [$start, $end]);
        }

        if($request->get('odemeSekil')){
                $data->where('odemeSekli', $request->get('odemeSekil'));
            }

            if($request->get('staff')){
                $data->where('personel', $request->get('staff'));
            }

            if($request->get('tedarikci')){
                $data->where('tedarikci', $request->get('tedarikci'));
            }

            if($request->get('marka')){
                $data->where('marka', $request->get('marka'));
            }

            if($request->get('cihaz')){
                $data->where('cihaz', $request->get('cihaz'));
            }

            if($request->get('odemeYonu')){
                $data->where('odemeYonu', $request->get('odemeYonu'));
            }

            if($request->get('odemeDurum')){
                $data->where('odemeDurum', $request->get('odemeDurum'));
            }

            if($request->get('bayi')){
                $data->where('personel', $request->get('bayi'));
            }
        
        $filteredData = $data->where('firma_id', $tenant_id)->get();
        
        $response = [
            'gelenNakitTL' => 0.00,
            'gelenHavaleTL' => 0.00,
            'gelenKartTL' => 0.00,
            'gidenNakitTL' => 0.00,
            'gidenHavaleTL' => 0.00,
            'gidenKartTL' => 0.00,
            'gelenToplamTL' => 0.00,
            'gidenToplamTL' => 0.00,
            'genelToplamTL' => 0.00,
        ];
        
        foreach ($filteredData as $item) {
            $fiyatTL = $item->fiyat;
            
            if ($item->odemeYonu == 1) {
                $response['gelenToplamTL'] += $fiyatTL;
                $response['gelenNakitTL'] += ($item->odemeSekli == 1) ? $fiyatTL : 0;
                $response['gelenHavaleTL'] += ($item->odemeSekli == 2) ? $fiyatTL : 0;
                $response['gelenKartTL'] += ($item->odemeSekli == 3) ? $fiyatTL : 0;
            } elseif ($item->odemeYonu == 2) {
                $response['gidenToplamTL'] += $fiyatTL;
                $response['gidenNakitTL'] += ($item->odemeSekli == 1) ? $fiyatTL : 0;
                $response['gidenHavaleTL'] += ($item->odemeSekli == 2) ? $fiyatTL : 0;
                $response['gidenKartTL'] += ($item->odemeSekli == 3) ? $fiyatTL : 0;
            }
        }

        $response['genelToplamTL'] = $response['gelenToplamTL'] - $response['gidenToplamTL'];
        
        foreach ($response as $key => $value) {
            if (strpos($key, 'TL') !== false) {
                $response[$key] = number_format($value, 2, ',', '.') . ' TL';
            }
        }
        return response()->json($response);
    }

    public function AddCashTransaction($tenant_id) {    
        $firma = Tenant::where('id', $tenant_id)->first();
        if (!$firma) {
            return redirect()->route('giris')->with([
                'message' => 'Firma bulunamadı.',
                'alert-type' => 'danger',
            ]);
        }   
        $payment_methods = PaymentMethod::where(function ($query) use ($tenant_id) {
            $query->whereNull('firma_id')->orWhere('firma_id', $tenant_id);
        })->orderBy('id', 'asc')->get();
        $payment_types = PaymentType::where(function ($query) use ($tenant_id) {
            $query->whereNull('firma_id')->orWhere('firma_id', $tenant_id);
        })->orderBy('odemeTuru','asc')->get();
        

        return view('frontend.secure.cash_transactions.add_cash_transaction', compact('payment_methods','payment_types','firma'));
    }

    public function GetCashPayment($tenant_id,$id){
        $firma = Tenant::where('id', $tenant_id)->first();
        $cash_payment_id = PaymentType::findOrFail($id);
        $ans = $cash_payment_id->cevaplar;
        
        $answers = explode(", ", $ans);
        $personeller = User::where('tenant_id', $tenant_id)->whereDoesntHave('roles', function ($query) {
            $query->whereIn('name', ['Admin','Super Admin']);
        })->where('status', 1)->orderBy('name', 'asc')->get();
        $tedarikciler = StockSupplier::where('firma_id', $tenant_id)->orderBy('id','asc')->get();
        $markalar = DeviceBrand::where(function($query) use ($firma) {
                $query->whereNull('firma_id')
                    ->orWhere('firma_id', $firma->id);
            })->orderBy('marka', 'asc')->get();
        $cihazlar = DeviceType::where(function($query) use ($firma) {
                $query->whereNull('firma_id')
                    ->orWhere('firma_id', $firma->id);
            })->orderBy('cihaz', 'asc')->get();
        return view('frontend.secure.cash_transactions.get_cash_payments',compact('cash_payment_id','answers','personeller','tedarikciler','markalar','cihazlar'));
    }

    public function StoreCashTransaction(Request $request, $tenant_id){
        $token = $request->input('form_token');
        // Token boş mu kontrol et
        if (empty($token)) {
            return response()->json([
                'success' => false,
                'error' => 'Geçersiz form token! Lütfen sayfayı yenileyin.'
            ], 403);
        }
        
        // Bu token daha önce kullanıldı mı kontrol et
        $cacheKey = 'cash_transaction_form_token_' . $token;
        
        if (Cache::has($cacheKey)) {
            return response()->json([
                'success' => false,
                'error' => 'Bu işlem zaten gönderildi! Lütfen bekleyin veya sayfayı yenileyin.'
            ], 429);
        }
        // Token'ı 10 dakika boyunca sakla
        Cache::put($cacheKey, true, now()->addMinutes(10));

        $firma = Tenant::where('id', $tenant_id)->first();
        $pid = Auth::user()->user_id;
        $odemeYonu = $request->odeme_durum;
        if($odemeYonu=="1"){
			$odemeDurum = $request->odeme_durum;
		}else{
			$odemeDurum = "1";
		}
        $createdAt = Carbon::parse($request->islemTarihi . ' ' . now()->format('H:i:s'));

        $fiyat = str_replace(',', '.', str_replace('.', '', $request->fiyat));

        if (!empty($request->servis)) {
            $servisVarMi = Service::where('id', $request->servis)->where('firma_id', $firma->id)
                ->where('durum', 1)
                ->exists();

            if (!$servisVarMi) {
            return response()->json(['error' => 'Geçersiz servis ID.'], 422);
        }
        }
        $response = CashTransaction::create([
            'firma_id' => $firma->id,
            'pid' => $pid,
            'kid' => auth()->user()->user_id,
            'created_at' => $createdAt,
            'odemeYonu' => $request->odeme_yonu,
            'odemeSekli' => $request->odeme_sekli,
            'odemeTuru' => $request->odeme_turu,
            'odemeDurum' => $odemeDurum,
            'fiyat' => $fiyat,
            'fiyatBirim' => 1, // Her zaman TL (1) olarak kaydet
            'aciklama' => $request->aciklama,
            'personel' => $request->personeller,
            'servis' => $request->servis,
            'tedarikci' => $request->tedarikciler,
            'marka' => $request->markalar,
            'cihaz' => $request->cihazlar,
        ]);

        // Activity log ekle
        ActivityLogger::logCashTransaction($fiyat, $request->odeme_yonu, $request->aciklama);
        return response()->json($response);
    }

    public function EditCashTransaction($tenant_id, $id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if (!$firma) {
            return redirect()->route('giris')->with([
                'message' => 'Firma bulunamadı.',
                'alert-type' => 'danger',
            ]);
        }   

        $cash_transaction_id = CashTransaction::where('firma_id',$tenant_id)->findOrFail($id);
        
        $payment_methods = PaymentMethod::where(function ($query) use ($tenant_id) {
            $query->whereNull('firma_id')->orWhere('firma_id', $tenant_id);
        })->orderBy('id', 'asc')->get();
        $payment_types = PaymentType::where(function ($query) use ($tenant_id) {
            $query->whereNull('firma_id')->orWhere('firma_id', $tenant_id);
        })->orderBy('odemeTuru','asc')->get();

        $type_id = $cash_transaction_id->odemeTuru;
        $cash_payment_id = PaymentType::where('id',$type_id)->first();
        $personeller = User::where('tenant_id', $tenant_id)->whereDoesntHave('roles', function ($query) {
            $query->whereIn('name', ['Admin','Super Admin']);
        })->where('status', 1)->orderBy('name', 'asc')->get();
        $tedarikciler = StockSupplier::where('firma_id', $tenant_id)->orderBy('id','asc')->get();
        $markalar = DeviceBrand::where(function($query) use ($firma) {
                $query->whereNull('firma_id')
                    ->orWhere('firma_id', $firma->id);
            })->orderBy('marka', 'asc')->get();
        $cihazlar = DeviceType::where(function($query) use ($firma) {
                $query->whereNull('firma_id')
                    ->orWhere('firma_id', $firma->id);
            })->orderBy('cihaz', 'asc')->get();
        return view('frontend.secure.cash_transactions.edit_cash_transaction',compact('firma','cash_transaction_id','payment_methods','payment_types','cash_payment_id','personeller','tedarikciler','markalar','cihazlar'));

    }

    public function UpdateCashTransaction(Request $request, $tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $cash_transaction_id = $request->id;
        

        $createdAt = Carbon::parse($request->islemTarihi . ' ' . now()->format('H:i:s'));        

        $kasa = CashTransaction::findOrFail($cash_transaction_id);

        // Güncelleme öncesi değerleri kaydet (log için)
        $oldAmount = $kasa->fiyat;
        $oldType = $kasa->odemeYonu;
        $oldDescription = $kasa->aciklama;

        $kasa->update([
            'pid' => auth()->user()->user_id,
            'created_at' => $createdAt,
            'odemeYonu' => $request->odeme_yonu,
            'odemeSekli' => $request->odeme_sekli,
            'odemeTuru' => $request->odeme_turu,
            'odemeDurum' => $request->odeme_durum,
            'fiyat' => $request->fiyat,
            'fiyatBirim' => 1, // Her zaman TL (1) olarak kaydet
            'aciklama' => $request->aciklama,
            'personel' => $request->personeller,
            'servis' => $request->servisler,
            'tedarikci' => $request->tedarikciler,
            'marka' => $request->markalar,
            'cihaz' => $request->cihazlar,       
        ]);

        // Güncelleme sonrası tekrar al (güncel veri)
        $updatedKasa = CashTransaction::find($cash_transaction_id);
        // Activity log ekle - güncellenmiş bilgilerle
        ActivityLogger::logCashTransactionUpdated(
        $cash_transaction_id, 
        $updatedKasa->fiyat, 
        $updatedKasa->odemeYonu, 
        $updatedKasa->aciklama
    );

        // Eğer servisIslem alanı null değilse, servis_para_hareketleri tablosunu da güncelle
        if (!is_null($updatedKasa->servisIslem)) {
            ServiceMoneyAction::where('id', $updatedKasa->servisIslem)
                ->update([
                    'kid' => auth()->user()->user_id,
                    'pid' => $updatedKasa->personel,
                    'servisid' => $updatedKasa->servis,
                    'created_at' => $updatedKasa->created_at,
                    'odemeSekli' => $updatedKasa->odemeSekli,
                    'odemeDurum' => $updatedKasa->odemeDurum,
                    'fiyat' => $updatedKasa->fiyat,
                    'aciklama' => $updatedKasa->aciklama,
                    'odemeYonu' => $updatedKasa->odemeYonu,
                ]);
        }
        $notification = array(
            'message' => 'Kasa Hareketi Başarıyla Güncellendi',
            'alert-type' => 'success'
        );
        return response()->json(['success' => $notification]);
    }

    public function DeleteCashTransaction($tenant_id,$id) {
        // Önce kayıt var mı ve firmaya ait mi kontrol et
        $cash = CashTransaction::where('firma_id', $tenant_id)->findOrFail($id);

        $amount = $cash->fiyat;
        $type = $cash->odemeYonu;
        $description = $cash->aciklama;

        // Eğer servisIslem varsa, servis_para_hareketleri kaydını da sil
        if (!is_null($cash->servisIslem)) {
            ServiceMoneyAction::where('id', $cash->servisIslem)->delete();
        }


        // Activity log ekle
         ActivityLogger::logCashTransactionDeleted($id, $amount, $type, $description);
        // Ardından asıl kasa hareketini sil
        $cash->delete();

        $notification = array(
            'message' => 'Kasa Hareketi Başarıyla Silindi',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
}
