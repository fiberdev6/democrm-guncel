<?php

namespace App\Http\Controllers\Frontend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\Tenant;
use App\Models\User;
use App\Models\StockShelf;
use App\Models\DeviceBrand;
use App\Models\DeviceType;
use App\Models\StockSupplier;
use App\Models\Stock;
use App\Models\StockAction;
use App\Models\PersonelStock;
use App\Models\ServiceResource;
use App\Models\ServisStock;
use App\Models\stock_photos;
use App\Models\StockCategory;
use Illuminate\Validation\Rule;
use Image;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Milon\Barcode\Facades\DNS1D;
use Illuminate\Support\Facades\Validator;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Cache;

class StockController extends Controller
{

    public function __construct()
{
    $this->middleware('permission:Depoyu Görebilir');
}
public function AllStocks($tenant_id, Request $request)
{
    if (!Auth::check()) {
        return redirect()->route('giris')->with('error', 'Lütfen giriş yapınız.');
    }
    $user = Auth::user();
    if ($tenant_id == null || $user->tenant->id != $tenant_id) {
        return redirect()->route('giris')->with([
            'message' => 'Stoklara erişiminiz yoktur.',
            'alert-type' => 'danger',
        ]);
    }

    $firma = Tenant::findOrFail($tenant_id);

     // Trial sürecinde izin ver, değilse inventory feature kontrolü yap
    if (!$firma->isOnTrial() && !$firma->hasFeature('inventory')) {
        return view('frontend.secure.stocks.no_inventory_feature', compact('firma'));
    }

    if ($request->ajax()) {
    $query = Stock::select('stocks.*')
        ->join('stock_categories as kategori', 'kategori.id', '=', 'stocks.urunKategori')
        ->where('stocks.firma_id', $tenant_id)
        ->where('kategori.id', '!=', 3);;

          // ========== TARİH FİLTRELEME MANTĞI BAŞLANGIÇ ==========
        $hasUserSelectedStockDate = $request->filled('from_date_stock') && $request->filled('to_date_stock') && !$this->isDefaultStockDateRange($request);
        $hasDashboardDate = $request->filled('dashboard_istatistik_tarih1') && $request->filled('dashboard_istatistik_tarih2');
        $hasSearchOrOtherFilters = !empty(trim($request->get('search')['value'] ?? '')) || 
                                    $request->filled('marka') || 
                                    $request->filled('raf') || 
                                    $request->filled('cihaz') ||
                                    $request->filled('personel');

        if ($hasUserSelectedStockDate) {
            // Stok sayfasındaki tarih filtresi en yüksek önceliğe sahiptir
            $this->applyMainStockDateRange($query, $request);
        } elseif ($hasDashboardDate && !$hasSearchOrOtherFilters) {
            // Stok tarih filtresi yoksa ve dashboard tarihi varsa, onu uygula (ancak başka arama/filtre yoksa)
            $startDate = Carbon::parse($request->get('dashboard_istatistik_tarih1'))->startOfDay();
            $endDate = Carbon::parse($request->get('dashboard_istatistik_tarih2'))->endOfDay();
            $query->whereBetween('stocks.created_at', [$startDate, $endDate]);
        } elseif (!$hasUserSelectedStockDate && !$hasDashboardDate && !$hasSearchOrOtherFilters) {
            // Hiçbir tarih veya arama/filtre yoksa, varsayılan son 3 günü uygula
            $from = Carbon::today()->subDays(2)->startOfDay();
            $to   = Carbon::today()->endOfDay();
            $query->whereBetween('stocks.created_at', [$from, $to]);
        }
        // Eğer $hasSearchOrOtherFilters true ise ancak tarih filtresi seçilmemişse,
        // herhangi bir tarih kısıtlaması uygulanmaz, bu da tüm kayıtlarda arama yapılmasını sağlar.
        // ========== TARİH FİLTRELEME MANTĞI SON ==========


        // Filtreler (personel dahil)
        if ($request->filled('marka')) {
            $query->where('stok_marka', $request->marka);
        }
        if ($request->filled('raf')) {
            $query->where('urunDepo', $request->raf);
        }
        if ($request->filled('cihaz')) {
            $query->where('stok_cihaz', $request->cihaz);
        }
        // Personel filtresi için PersonelStock tablosu ile join
        if ($request->filled('personel')) {
            $query->join('personel_stocks', function($join) use ($request) {
                $join->on('personel_stocks.stokid', '=', 'stocks.id')
                     ->where('personel_stocks.pid', $request->personel)
                     ->where('personel_stocks.adet', '>', 0); // Sadece stoğu olan personeller
            });
        }

        // Sıralama DataTables yapısına göre
        if ($request->has('order')) {
            $order = $request->get('order')[0];
            $columns = $request->get('columns');
            $orderColumn = $columns[$order['column']]['data'];
            $orderDir = $order['dir'];
            $query->orderBy($orderColumn, $orderDir);
        } else {
            $query->orderBy('id', 'desc');
        }

        // Toplam hesaplamalar (filtreli tüm stoklar için)
        $stocksForTotal = $query->get();

        $toplamAdet = 0;
        $toplamFiyat = 0;

        foreach ($stocksForTotal as $stock) {
            $toplamGiris = \App\Models\StockAction::where('stokId', $stock->id)->where('islem', 1)->sum('adet'); //alış
            $toplamCikis = \App\Models\StockAction::where('stokId', $stock->id)->where('islem', 2)->sum('adet');  //serviste kullanım
            $kalanAdet = $toplamGiris - $toplamCikis;

            $toplamAdet += max($kalanAdet, 0);
            // Toplam fiyat hesaplama - Fiyat × Kalan Adet
            $fiyat = max($stock->fiyat, 0);
            $toplamFiyat += ($fiyat * $kalanAdet);
        }

        // DataTables
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('id', function($row) {
                return '<a href="javascript:void(0);" class="t-link editStock" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editStockModal">' . e($row->id) . '</a>';
            })
            ->addColumn('urunKodu', function($row) {
                return '<a href="javascript:void(0);" class="t-link editStock" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editStockModal">' . e($row->urunKodu) . '</a>';
            })
            ->addColumn('urunAdi', function($row) {
                return '<a href="javascript:void(0);" class="t-link editStock" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editStockModal">' . e($row->urunAdi) . '</a>';
            })
            ->addColumn('adet', function($row) {
                $toplamGiris = \App\Models\StockAction::where('stokId', $row->id)->where('islem', 1)->sum('adet');
                $toplamCikis = \App\Models\StockAction::where('stokId', $row->id)->where('islem', 2)->sum('adet');
                $kalanAdet = $toplamGiris - $toplamCikis;
                return '<a href="javascript:void(0);" class="t-link editStock" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editStockModal">' . $kalanAdet . '</a>';
            })
            ->addColumn('toplamTutar', function($row) {
                $toplamGiris = \App\Models\StockAction::where('stokId', $row->id)
                                    ->where('islem', 1)
                                    ->sum('adet');

                $tutar = $row->fiyat ?? 0;

                return '<a href="javascript:void(0);" class="t-link editStock" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editStockModal">'
                        . number_format($tutar, 2, ',', '.') . ' ₺</a>'; // TL simgesi sabit
            })
            ->addColumn('raf_adi', function($row) {
                $raf = $row->raf ? e($row->raf->raf_adi) : '-';
                return '<a href="javascript:void(0);" class="t-link editStock" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editStockModal">' . $raf . '</a>';
            })
            ->addColumn('marka_cihaz', function($row) {
                $marka = $row->marka ? e($row->marka->marka) : '';
                $cihaz = $row->cihaz ? e($row->cihaz->cihaz) : '';
                $text = trim($marka . ' / ' . $cihaz, ' / ');
                return '<a href="javascript:void(0);" class="t-link editStock" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editStockModal">' . $text . '</a>';
            })

            ->editColumn('created_at', function($row) {
                $date = $row->created_at ? $row->created_at->format('d.m.Y H:i') : '';
                return '<a href="javascript:void(0);" class="t-link editStock" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editStockModal">' . $date . '</a>';
            })

            ->addColumn('action', function($row) use ($tenant_id) {
                $deleteUrl = route('delete.stock', [$tenant_id, $row->id]);
                $editBtn = '<a href="javascript:void(0);" class="btn btn-outline-warning btn-sm editStock" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editStockModal" title="Düzenle"><i class="fas fa-edit"></i></a>';
                $viewBtn = '<a href="javascript:void(0);" class="btn btn-outline-primary btn-sm editStock" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editStockModal" title="Düzenle"><i class="fas fa-eye"></i></a>';
                $delBtn = '<a href="'.$deleteUrl.'" class="btn btn-outline-danger btn-sm" title="Sil" onclick="return confirm(\'Silmek istediğinize emin misiniz?\');"><i class="fas fa-trash-alt"></i></a>';
                return $viewBtn . ' ' . $editBtn . ' ' . $delBtn;
            })
            ->filter(function ($query) use ($request) {
                if ($search = $request->get('search')['value'] ?? null) {
                    $query->where(function ($q) use ($search) {
                        $q->where('urunAdi', 'like', "%{$search}%")
                          ->orWhere('urunKodu', 'like', "%{$search}%");
                    });
                }

            })
            ->rawColumns(['id','urunKodu', 'urunAdi', 'adet', 'toplamTutar', 'raf_adi', 'marka_cihaz', 'created_at', 'action'])
            ->with([
                'toplamAdet' => number_format($toplamAdet),
                'toplamFiyat' => number_format($toplamFiyat, 2, ',', '.') . ' ₺',
                'toplamAdetRaw' => $toplamAdet,
                'toplamFiyatRaw' => $toplamFiyat,
            ])
            ->make(true);
    }

    $personeller = User::role(['Teknisyen', 'Teknisyen Yardımcısı'])
        ->where('tenant_id', $tenant_id)
        ->get();
    $rafListesi = StockShelf::where('firma_id', $tenant_id)->get();
    $isBeyazEsya = $firma->sektor === 'beyaz-esya';
    $markalar = DeviceBrand::where(function($query) use ($firma, $isBeyazEsya) {
        if ($isBeyazEsya) {
            // Beyaz eşya sektörü: default + kendi eklediği
            $query->whereNull('firma_id')
                ->orWhere('firma_id', $firma->id);
        } else {
            // Diğer sektörler: sadece kendi eklediği
            $query->where('firma_id', $firma->id);
        }
    })->orderBy('marka', 'asc')->get();
    $cihazlar = DeviceType::where(function($query) use ($firma, $isBeyazEsya) {
        if ($isBeyazEsya) {
            // Beyaz eşya sektörü: default + kendi eklediği
            $query->whereNull('firma_id')
                ->orWhere('firma_id', $firma->id);
        } else {
            // Diğer sektörler: sadece kendi eklediği
            $query->where('firma_id', $firma->id);
        }
    })->orderBy('cihaz', 'asc')->get();

    return view('frontend.secure.stocks.all_stocks', compact('firma', 'personeller', 'markalar', 'cihazlar', 'rafListesi'));
}
// Helper Methods - Tarih Filtreleme İçin
private function isDefaultStockDateRange(Request $request): bool
{
    if (!$request->filled('from_date_stock') || !$request->filled('to_date_stock')) {
        return false;
    }
    
    try {
        $from = Carbon::createFromFormat('Y-m-d', $request->from_date_stock)->startOfDay();
        $to = Carbon::createFromFormat('Y-m-d', $request->to_date_stock)->endOfDay();
        $defaultFrom = Carbon::today()->subDays(2)->startOfDay();
        $defaultTo = Carbon::today()->endOfDay();
        
        return $from->equalTo($defaultFrom) && $to->equalTo($defaultTo);
    } catch (\Exception $e) {
        return false;
    }
}

private function applyMainStockDateRange($query, Request $request): void
{
    if ($request->filled('from_date_stock') && $request->filled('to_date_stock')) {
        $from = Carbon::createFromFormat('Y-m-d', $request->from_date_stock)->startOfDay();
        $to   = Carbon::createFromFormat('Y-m-d', $request->to_date_stock)->endOfDay();
        $query->whereBetween('stocks.created_at', [$from, $to]);
    }
}
    public function AddStock($tenant_id){

            $firma = Tenant::findOrFail($tenant_id);
            $rafListesi = StockShelf::where('firma_id', $tenant_id)->get();

            // Tenant'ın sektörünü kontrol et
            $isBeyazEsya = $firma->sektor === 'beyaz-esya';

            $markalar = DeviceBrand::where(function($query) use ($firma, $isBeyazEsya) {
                if ($isBeyazEsya) {
                    // Beyaz eşya sektörü: default + kendi eklediği
                    $query->whereNull('firma_id')
                        ->orWhere('firma_id', $firma->id);
                } else {
                    // Diğer sektörler: sadece kendi eklediği
                    $query->where('firma_id', $firma->id);
                }
            })->orderBy('marka', 'asc')->get();
            $cihazlar = DeviceType::where(function($query) use ($firma, $isBeyazEsya) {
                if ($isBeyazEsya) {
                    // Beyaz eşya sektörü: default + kendi eklediği
                    $query->whereNull('firma_id')
                        ->orWhere('firma_id', $firma->id);
                } else {
                    // Diğer sektörler: sadece kendi eklediği
                    $query->where('firma_id', $firma->id);
                }
            })->orderBy('cihaz', 'asc')->get();

             // Konsinye kategori (global) + firmaya özel kategoriler
            $kategoriler = StockCategory::where(function($query) use ($tenant_id) {
                $query->where('firma_id', $tenant_id) // firmaya özel kategoriler
                    ->orWhere('firma_id', null); // global kategoriler (konsinye)
            })
            ->where('kategori', '!=', 'Konsinye Cihaz') // konsinye kategoriyi hariç tut
            ->get();


            return view('frontend.secure.stocks.add_stock', compact('firma','rafListesi', 'markalar', 'cihazlar', 'kategoriler','tenant_id'));
        }

    public function StoreStock(Request $request, $tenant_id){
        $token = $request->input('form_token');
        // Token boş mu kontrol et
        if (empty($token)) {
            return back()->withInput()->with([
                'message' => 'Geçersiz form token! Lütfen sayfayı yenileyin.',
                'alert-type' => 'error'
            ]);
        }
        // Bu token daha önce kullanıldı mı kontrol et
        $cacheKey = 'stock_form_token_' . $token;
        if (Cache::has($cacheKey)) {
            return back()->withInput()->with([
                'message' => 'Bu form zaten gönderildi! Lütfen bekleyin veya sayfayı yenileyin.',
                'alert-type' => 'warning'
            ]);
        }
        // Token'ı 10 dakika boyunca sakla
        Cache::put($cacheKey, true, now()->addMinutes(10));

 
        $firma = Tenant::findOrFail($tenant_id);
        if (!$firma) {
        $notification = [
            'message' => 'Firma bulunamadı.',
            'alert-type' => 'danger',
        ];
        return redirect()->route('giris')->with($notification);
        }

        // 1. Mevcut personel sayısı
        $current = Stock::where('firma_id', $firma->id)
        ->where('durum','1')
        ->where('urunKategori', '!=', 3)
        ->count();

        // 2. Planın izin verdiği limit
        $limit = $firma->plan()?->limits['stocks'] ?? null;

        // 3. Limit dolmuş mu kontrol et
        if ($limit !== null && $limit !== -1 && $current >= $limit) {
            return back()->with('error', 'Maksimum stok limitine ulaştınız.');
        }
    // Ürün kodu kontrolü
    $existingStock = Stock::where('firma_id', $tenant_id)
                          ->where('urunKodu', $request->urunKodu)
                          ->first();

    if ($existingStock) {
        $notification = [
            'message' => 'Bu ürün kodu zaten mevcut. Lütfen farklı bir ürün kodu girin.',
            'alert-type' => 'warning',
        ];
        return redirect()->back()->withInput()->with($notification);
    }

     $request->validate([
        'urunKodu' => ['required', 'digits:13', 'unique:stocks,urunKodu,NULL,id,firma_id,'.$tenant_id],
        // 'digits:13' => tam 13 rakam olmalı,
        // unique kontrolü firma_id bazlı, yani aynı firmada tekrar olmasın
        // diğer alanlar için istersen validation ekleyebilirsin
    ],[
        'urunKodu.required' => 'Ürün kodu zorunludur.',
        'urunKodu.digits' => 'Ürün kodu tam 13 haneli olmalıdır.',
        'urunKodu.unique' => 'Bu ürün kodu zaten mevcut. Lütfen farklı bir ürün kodu girin.',
    ]);
    // Ürün adı kontrolü
    $existingName = Stock::where('firma_id', $tenant_id)
                        ->where('urunAdi', $request->urunAdi)
                        ->first();

    if ($existingName) {
        $notification = [
            'message' => 'Bu ürün adı zaten mevcut. Lütfen farklı bir ürün adı girin.',
            'alert-type' => 'warning',
        ];
        return redirect()->back()->withInput()->with($notification);
    }
    $request->validate([
    'urunKodu' => ['required', 'digits:13', 'unique:stocks,urunKodu,NULL,id,firma_id,'.$tenant_id],
    'urunAdi' => ['required', 'max:255'],
    ],[
        'urunKodu.required' => 'Ürün kodu zorunludur.',
        'urunKodu.digits' => 'Ürün kodu tam 13 haneli olmalıdır.',
        'urunKodu.unique' => 'Bu ürün kodu zaten mevcut. Lütfen farklı bir ürün kodu girin.',
        'urunAdi.required' => 'Ürün adı zorunludur.',
    ]);

            $personel_id = Auth::user()->user_id;

            $stock = new Stock();
            $stock->firma_id  = $firma->id;
            $stock->pid         = $personel_id; 
            $stock->urunAdi   = $request->urunAdi;
            $stock->urunKodu  = $request->urunKodu;
            $stock->urunKategori = $request->urunKategori;
            $stock->aciklama  = $request->aciklama;
            $stock->urunDepo = $request->raf_id;
            $stock->fiyat      = $request->fiyat;
            $stock->fiyatBirim = 1; // Her zaman TL (1) olarak kaydet
            $stock->stok_marka  = $request->marka_id;   // ilişkili marka tablosu id'si
            $stock->stok_cihaz  = $request->cihaz_id;   // ilişkili cihaz tablosu id'si
               
            $stock->save();
            // Activity log ekle
            ActivityLogger::logStockCreated($stock->id, $stock->urunAdi);

            $notification = [
                'message' => 'Stok başarıyla oluşturuldu.',
                'alert-type' => 'success'
            ];

            return redirect()->route('stocks', $tenant_id)->with($notification);
        }


public function EditStock($tenant_id, $id) {
    $firma = Tenant::findOrFail($tenant_id);
    $stock = Stock::with(['raf', 'marka', 'cihaz', 'sonHareket'])->findOrFail($id);
    $rafListesi = StockShelf::where('firma_id', $tenant_id)->get();
    $markalar = DeviceBrand::where(function($query) use ($firma, $isBeyazEsya) {
    if ($isBeyazEsya) {
        // Beyaz eşya sektörü: default + kendi eklediği
        $query->whereNull('firma_id')
              ->orWhere('firma_id', $firma->id);
    } else {
        // Diğer sektörler: sadece kendi eklediği
        $query->where('firma_id', $firma->id);
    }
})->orderBy('marka', 'asc')->get();
    $cihazlar = DeviceType::where(function($query) use ($firma, $isBeyazEsya) {
    if ($isBeyazEsya) {
        // Beyaz eşya sektörü: default + kendi eklediği
        $query->whereNull('firma_id')
              ->orWhere('firma_id', $firma->id);
    } else {
        // Diğer sektörler: sadece kendi eklediği
        $query->where('firma_id', $firma->id);
    }
})->orderBy('cihaz', 'asc')->get();

    // Konsinye kategori (global) + firmaya özel kategoriler
    $kategoriler = StockCategory::where(function($query) use ($tenant_id) {
        $query->where('firma_id', $tenant_id) // firmaya özel kategoriler
              ->orWhere('firma_id', null); // global kategoriler
    })
    ->where('kategori', '!=', 'Konsinye Cihaz') // konsinye kategoriyi hariç tut
    ->get();

    // 1. STOK HAREKETLERİNİ ÇEK
    $stokHareketleri = StockAction::with(['musteri'])
        ->select(
            'stock_actions.*',
            'stock_suppliers.tedarikci',
            'user_recipient.name as recipient_name',
            'user_performer.name as performer_name'
        )
        ->leftJoin('stock_suppliers', 'stock_suppliers.id', '=', 'stock_actions.tedarikci')
        ->leftJoin('tb_user as user_recipient', 'user_recipient.user_id', '=', 'stock_actions.pid')
        ->leftJoin('tb_user as user_performer', 'user_performer.user_id', '=', 'stock_actions.pid')
        ->where('stock_actions.stokId', $id)
        ->orderBy('stock_actions.id', 'desc')
        ->get();

    // 2. PERSONEL STOKLARINI ÇEKME
    $hareketler = StockAction::with('aliciPersonel')
        ->where('firma_id', $tenant_id)
        ->where('stokId', $id)
        ->where('islem', 3) // Sadece personele gönderilenler
        ->get()
        ->groupBy(function ($hareket) {
            return optional($hareket->aliciPersonel)->user_id;
        })
        ->map(function ($grouped) use ($id) {
            $hareket = $grouped->first();
            $aliciId = optional($hareket->aliciPersonel)->user_id;
            
            $hareket->guncel_adet = $aliciId
                ? PersonelStock::where('stokid', $id)->where('pid', $aliciId)->value('adet')
                : 0;
            
            return $hareket;
        })
        ->filter(function($item) { // Stoğu 0 olan personeli göstermeyebiliriz
            return $item->guncel_adet > 0;
        })
        ->values();

    // 3. FOTOĞRAFLARI ÇEK
    $photos = stock_photos::where('kid', $tenant_id)
                          ->where('stock_id', $id)
                          ->latest()
                          ->get();

    //Hareket Ekle Modal'ı için son 5 tedarikçi
    $sonTedarikciler =StockSupplier::where('firma_id', $tenant_id) 
                                                ->latest() // En son eklenene göre sırala
                                                ->take(5) // Sadece 5 tane al
                                                ->get();

    // Hareket Ekle Modal'ı için son 5 personel
    $sonPersoneller = User::where('tenant_id', $tenant_id)
    ->whereHas('roles', function ($query) {
        $query->whereIn('name', ['Teknisyen', 'Atölye Ustası']);
    })
    ->latest('created_at')
    ->take(5)
    ->get();

    $html = view('frontend.secure.stocks.edit_stock', compact('firma', 'stock', 'rafListesi', 'markalar',
        'kategoriler', 'cihazlar','stokHareketleri',
        'hareketler',  
        'photos'  ,'tenant_id','sonTedarikciler','sonPersoneller'))->render();


    return response()->json([
                'html' => $html,
                'urunAdi' => $stock->urunAdi,
            ]);
        }
//Tedarikçi Arama
public function searchSuppliers(Request $request, $tenant_id)
{
    $search = $request->input('q');
    $data =StockSupplier::where('firma_id', $tenant_id)
                                     ->where('tedarikci', 'LIKE', "%{$search}%")
                                     ->latest()
                                     ->take(5) // Arama sonucunda en fazla 5 sonuç göster
                                     ->get(['id', 'tedarikci as text']); // Select2 formatına uygun
    return response()->json($data);
}
//Marka Arama
public function searchBrands(Request $request, $tenant_id)
{
    $search = $request->input('q');
    $firma = Tenant::find($tenant_id);
    $isBeyazEsya = $firma && $firma->sektor === 'beyaz-esya';
    
    $data = DeviceBrand::where('marka', 'LIKE', "%{$search}%")
        ->where(function($query) use ($tenant_id, $isBeyazEsya) {
            if ($isBeyazEsya) {
                $query->whereNull('firma_id')
                      ->orWhere('firma_id', $tenant_id);
            } else {
                $query->where('firma_id', $tenant_id);
            }
        })
        ->latest()
        ->take(5)
        ->get(['id', 'marka as text']);
        
    return response()->json($data);
}

public function searchDevices(Request $request, $tenant_id)
{
    $search = $request->input('q');
    $firma = Tenant::find($tenant_id);
    $isBeyazEsya = $firma && $firma->sektor === 'beyaz-esya';
    
    $data = DeviceType::where('cihaz', 'LIKE', "%{$search}%")
        ->where(function($query) use ($tenant_id, $isBeyazEsya) {
            if ($isBeyazEsya) {
                $query->whereNull('firma_id')
                      ->orWhere('firma_id', $tenant_id);
            } else {
                $query->where('firma_id', $tenant_id);
            }
        })
        ->latest()
        ->take(5)
        ->get(['id', 'cihaz as text']);
        
    return response()->json($data);
}
//Kategori Arama
// Kategori arama fonksiyonu - konsinye hariç
public function searchCategories(Request $request, $tenant_id)
{
    $search = $request->input('q');
    $data = StockCategory::where(function($query) use ($tenant_id) {
        $query->where('firma_id', $tenant_id) // firmaya özel kategoriler
              ->orWhere('firma_id', null); // global kategoriler
    })
    ->where('kategori', '!=', 'Konsinye Cihaz') // konsinye kategoriyi hariç tut
    ->where('kategori', 'LIKE', "%{$search}%")
    ->latest()
    ->take(5)
    ->get(['id', 'kategori as text']);
    
    return response()->json($data);
}
//Raf Arama
public function searchShelves(Request $request, $tenant_id)
    {
        $search = $request->input('q');
        $data = StockShelf::where('firma_id', $tenant_id)
                                      ->where('raf_adi', 'LIKE', "%{$search}%")
                                      ->latest()
                                      ->take(5)
                                      ->get(['id', 'raf_adi as text']);
        return response()->json($data);
    }
//Personel Arama
public function searchPersonnel(Request $request, $tenant_id)
{
    $search = $request->input('q');
    $data = User::where('tenant_id', $tenant_id)
                            ->where('name', 'LIKE', "%{$search}%")
                            ->whereHas('roles', function ($query) {
                                    $query->whereIn('name', ['Teknisyen', 'Atölye Ustası']);
                                })
                            ->latest('user_id')
                              ->take(5)
                              ->get(['user_id as id', 'name as text']); // Select2 formatına uygun
    return response()->json($data);
}
//Marka ekleme
public function storeBrandAjax(Request $request, $tenant_id)
{
    // Hem stok hem servis formundan gelen token'ları kontrol et
    $token = $request->input('brand_form_token') ?? $request->input('device_brand_form_token');
    
    if (empty($token)) {
        return response()->json(['error' => 'Geçersiz token'], 400);
    }
    
    $cacheKey = 'brand_form_token_' . $token;
    if (Cache::has($cacheKey)) {
        return response()->json(['error' => 'Bu form zaten gönderildi'], 400);
    }
    Cache::put($cacheKey, true, now()->addMinutes(10));
    
    $request->validate(['marka' => 'required|string|max:255']);

    $brand = DeviceBrand::create([
        'firma_id' => $tenant_id,
        'marka' => $request->marka,
    ]);

    return response()->json(['id' => $brand->id, 'text' => $brand->marka]);
}

// Cihaz Türü ekleme - Hem stok hem servis formu için
public function storeDeviceTypeAjax(Request $request, $tenant_id)
{
    // Hem stok hem servis formundan gelen token'ları kontrol et
    $token = $request->input('device_form_token') ?? $request->input('device_type_service_form_token');
    
    if (empty($token)) {
        return response()->json(['error' => 'Geçersiz token'], 400);
    }
    
    $cacheKey = 'device_form_token_' . $token;
    if (Cache::has($cacheKey)) {
        return response()->json(['error' => 'Bu form zaten gönderildi'], 400);
    }
    Cache::put($cacheKey, true, now()->addMinutes(10));
    
    $request->validate(['cihaz' => 'required|string|max:255']);

    $deviceType = DeviceType::create([
        'firma_id' => $tenant_id,
        'cihaz' => $request->cihaz,
    ]);

    return response()->json(['id' => $deviceType->id, 'text' => $deviceType->cihaz]);
}
// YENİ: Servis Kaynağı ekleme
public function storeServiceResourceAjax(Request $request, $tenant_id)
{
    $token = $request->input('service_resource_form_token');
    
    if (empty($token)) {
        return response()->json(['error' => 'Geçersiz token'], 400);
    }
    
    $cacheKey = 'service_resource_form_token_' . $token;
    if (Cache::has($cacheKey)) {
        return response()->json(['error' => 'Bu form zaten gönderildi'], 400);
    }
    Cache::put($cacheKey, true, now()->addMinutes(10));
    
    $request->validate(['kaynak' => 'required|string|max:255']);

    $serviceResource = ServiceResource::create([
        'firma_id' => $tenant_id,
        'kaynak' => $request->kaynak,
    ]);

    return response()->json(['id' => $serviceResource->id, 'text' => $serviceResource->kaynak]);
}
// Kategori ekleme - konsinye kontrolü
public function storeCategoryAjax(Request $request, $tenant_id)
{
    $token = $request->input('category_form_token');
    if (empty($token)) {
        return response()->json(['error' => 'Geçersiz token'], 400);
    }
    
    $cacheKey = 'category_form_token_' . $token;
    if (Cache::has($cacheKey)) {
        return response()->json(['error' => 'Bu form zaten gönderildi'], 400);
    }
    Cache::put($cacheKey, true, now()->addMinutes(10));
    $request->validate([
        'kategori' => [
            'required',
            'string',
            'max:255',
            function ($attribute, $value, $fail) {
                // "Konsinye" kategorisinin eklenmesini engelle
                if (strcasecmp($value, 'konsinye') == 0) {
                    $fail('Konsinye kategorisi bu alandan eklenemez.');
                }
            }
        ]
    ]);
    
    $kullanici_id = Auth::user()->user_id;

    $category = StockCategory::create([
        'firma_id' => $tenant_id,
        'kategori' => $request->kategori,
        'kid' => $kullanici_id,
    ]);

    return response()->json(['id' => $category->id, 'text' => $category->kategori]);
}
// Raf ekleme
public function storeShelfAjax(Request $request, $tenant_id)
{
    $token = $request->input('shelf_form_token');
    if (empty($token)) {
        return response()->json(['error' => 'Geçersiz token'], 400);
    }
    
    $cacheKey = 'shelf_form_token_' . $token;
    if (Cache::has($cacheKey)) {
        return response()->json(['error' => 'Bu form zaten gönderildi'], 400);
    }
    Cache::put($cacheKey, true, now()->addMinutes(10));

    $request->validate(['raf_adi' => 'required|string|max:255']);
    $kullanici_id = Auth::user()->user_id;

    $shelf = StockShelf::create([
        'firma_id' => $tenant_id,
        'raf_adi' => $request->raf_adi,
        'kid'=> $kullanici_id,
    ]);

    return response()->json(['id' => $shelf->id, 'text' => $shelf->raf_adi]);
}
public function UpdateStock(Request $request, $tenant_id, $id){
    $firma = Tenant::findOrFail($tenant_id);
    $personel_id = Auth::user()->user_id;
    $stock = Stock::findOrFail($id);

    // Kategori kontrolü - konsinye kategorisi seçilememeli
    if ($request->urunKategori == 3) {
        return response()->json([
            'status' => 'error',
            'message' => 'Konsinye kategorisi bu alandan seçilemez.'
        ], 400);
    }


    // Ürün kodu kontrolü
    $existingStock = Stock::where('firma_id', $tenant_id)
                        ->where('urunKodu', $request->urunKodu)
                        ->where('id', '!=', $id)
                        ->first();

    if ($existingStock) {
        $notification = [
            'message' => 'Bu ürün kodu zaten mevcut. Lütfen farklı bir ürün kodu girin.',
            'alert-type' => 'warning',
        ];
        return redirect()->back()->withInput()->with($notification);
    }

    $request->validate([
        'urunKodu' => [
            'required',
            'digits:13',
            Rule::unique('stocks')->ignore($id)->where('firma_id', $tenant_id),
        ],
        // diğer validasyonlar...
    ],[
        'urunKodu.required' => 'Ürün kodu zorunludur.',
        'urunKodu.digits' => 'Ürün kodu tam 13 haneli olmalıdır.',
        'urunKodu.unique' => 'Bu ürün kodu zaten mevcut. Lütfen farklı bir ürün kodu girin.',
    ]);

    // Ürün adı benzersiz mi?
    $existingName = Stock::where('firma_id', $tenant_id)
        ->where('urunAdi', $request->urunAdi)
        ->where('id', '!=', $id)
        ->first();

    if ($existingName) {
        $notification = [
            'message' => 'Bu ürün adı zaten mevcut. Lütfen farklı bir ürün adı girin.',
            'alert-type' => 'warning',
        ];
        return redirect()->back()->withInput()->with($notification);
    }

    $stock->urunAdi   = $request->urunAdi;
    $stock->urunKodu    = $request->urunKodu;
    $stock->urunKategori = $request->urunKategori;
    $stock->urunDepo    = $request->raf_id;
    $stock->aciklama  = $request->aciklama;
    $stock->fiyat       = $request->fiyat;
    $stock->fiyatBirim = 1; // Her zaman TL (1) olarak kaydet
    $stock->stok_marka  = $request->marka_id;
    $stock->stok_cihaz  = $request->cihaz_id;
    $stock->save();


    // Başarı yanıtı
    return response()->json([
            'status' => 'success',
            'message' => 'Ürün bilgileri başarıyla güncellendi.'
        ]);


}


public function DeleteStock($tenant_id, $id) {
    $stock = Stock::where('firma_id', $tenant_id)->where('id', $id)->first();

    if (is_null($stock)) {
        $notification = [
            'message' => 'Silmek istediğiniz stok bulunamadı.',
            'alert-type' => 'danger'
        ];
        return redirect()->back()->with($notification);
    }

    // Stok hareketleri var mı kontrol et
    $stokHareketSayisi = StockAction::where('stokId', $id)->count();

    if ($stokHareketSayisi > 0) {
        $notification = [
            'message' => 'Ürün içerisinde stok hareket kaydı bulunurken  silme işlemi gerçekleştirilemez.',
            'alert-type' => 'warning'
        ];
        return redirect()->back()->with($notification);
    }

    try {
        $stock->delete();

        $notification = [
            'message' => 'Stok başarıyla silindi.',
            'alert-type' => 'success'
        ];
    } catch (\Exception $e) {
        $notification = [
            'message' => 'Silme işlemi sırasında bir hata oluştu.',
            'alert-type' => 'danger'
        ];
    }

    return redirect()->back()->with($notification);
}


/////////////////////////////////////////////STOCK ACTION////////////////////////////////////////////////////////////////////////////////////////


public function StokActions($tenant_id, $stock_id)
{
    $stock = Stock::with(['marka', 'cihaz', 'raf'])
        ->where('firma_id', $tenant_id)
        ->findOrFail($stock_id);

     $firma = Tenant::findOrFail($tenant_id);

    // Stok hareketlerini join ile getir
   $stokHareketleri = StockAction::with(['musteri'])
            ->select(
                'stock_actions.*',
                'stock_suppliers.tedarikci',
                'user_recipient.name as recipient_name', // islem=3 (Personel'e Gönder) için alıcı personel adı
                'user_performer.name as performer_name'  // islem=2 (Serviste Kullanım) için işlemi yapan personel adı
            )
            // Tedarikçi tablosu ile birleştirme
            ->leftJoin('stock_suppliers', 'stock_suppliers.id', '=', 'stock_actions.tedarikci')
            // 'pid' sütunu üzerinden kullanıcı tablosu ile birleştirme (alıcı personel için)
            ->leftJoin('tb_user as user_recipient', 'user_recipient.user_id', '=', 'stock_actions.pid')
            // 'kid' sütunu üzerinden kullanıcı tablosu ile birleştirme (işlemi yapan personel için)
            ->leftJoin('tb_user as user_performer', 'user_performer.user_id', '=', 'stock_actions.pid')
            ->where('stock_actions.stokId', $stock_id)
            ->orderBy('stock_actions.id', 'desc')
            ->get();

        return view('frontend.secure.stocks.action_stock', compact('stock', 'stokHareketleri','firma'));
    }

public function StoreStockAction(Request $request, $tenant_id)
{
    $firma = Tenant::findOrFail($tenant_id);

     // Token kontrolü
    $token = $request->input('form_token');
    
    if (empty($token)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Geçersiz form token! Lütfen sayfayı yenileyin.',
        ], 400);
    }
    
    $cacheKey = 'stock_action_token_' . $token;
    if (Cache::has($cacheKey)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Bu form zaten gönderildi! Lütfen bekleyin.',
        ], 400);
    }
    
    Cache::put($cacheKey, true, now()->addMinutes(10));

    // Temel doğrulama kuralları
    $rules = [
        'stok_id'    => 'required|integer',
        'islem'      => 'required|in:1,2,3',
        'adet'       => 'required|integer|min:1',
        'fiyat'      => $request->islem == 1 ? 'required' : 'nullable',
        'fiyatBirim' => 'nullable|numeric',
        'tedarikci'  => 'nullable|string|max:255',
    ];

    // Custom hata mesajları
    $messages = [
        'servisid.required' => 'Serviste kullanım işlemi için servis ID alanı zorunludur.',
        'personel.required' => 'Personele gönderme işlemi için personel alanı zorunludur.',
        'servisid.integer'  => 'Servis ID bir sayı olmalıdır.',
        'personel.integer'  => 'Personel ID bir sayı olmalıdır.',
    ];

    // Doğrulayıcıyı oluştur
    $validator = Validator::make($request->all(), $rules, $messages);

    // islem 2 ise servisid zorunlu
    $validator->sometimes('servisid', 'required|integer', function ($input) {
        return $input->islem == 2;
    });

    // islem 2 için personel alanı zorunlu (stok düşürülecek personel)
    $validator->sometimes('personel', 'required|integer', function ($input) {
        return $input->islem == 2 || $input->islem == 3;
    });

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Doğrulama hatası',
            'errors' => $validator->errors()
        ], 422); // 422 Unprocessable Entity
    }

    $personel_id = Auth::user()->user_id;
    $stokId = $request->stok_id;

    // Fiyatı temizle (nokta ve virgül fix)
    $fiyat = null;
    if ($request->islem == 1 && $request->filled('fiyat')) {
        $fiyat = floatval(str_replace(['.', ','], ['', '.'], $request->fiyat));
    }

    // --- Stok Kontrolleri ---

    if ($request->islem == 2) {
        // Serviste kullanım: Personel stoğundan kontrol et
        $personelStok = PersonelStock::where('firma_id', $firma->id)
            ->where('pid', $request->personel) // stok düşülecek personel
            ->where('stokid', $stokId)
            ->first();

        $kalanStok = $personelStok ? $personelStok->adet : 0;

         if ($request->adet > $kalanStok) {
            return response()->json([
                'status' => 'error',
                'message' => "Yetersiz personel stoğu! Mevcut: {$kalanStok} adet.",
            ], 400); // 400 Bad Request
        }
    }

    if ($request->islem == 3) {
        // Personele gönderme: Genel stoktan kontrol et
        $mevcutStok = StockAction::where('stokId', $stokId)
            ->where('firma_id', $firma->id)
            ->selectRaw("
                SUM(CASE WHEN islem = 1 THEN adet ELSE 0 END) as giren,
                SUM(CASE WHEN islem = 2 THEN adet ELSE 0 END) as serviste_kullanim,
                SUM(CASE WHEN islem = 3 THEN adet ELSE 0 END) as personele_giden
            ")
            ->first();

        $kalanStok = ($mevcutStok->giren ?? 0) - ($mevcutStok->serviste_kullanim ?? 0) - ($mevcutStok->personele_giden ?? 0);

        if ($request->adet > $kalanStok) {
            return response()->json([
                'status' => 'error',
                'message' => "Yetersiz genel stok! Mevcut: {$kalanStok} adet.",
            ], 400);
        }
    }

    // --- Stok Hareketi Kaydı ---

    $stockAction = new StockAction();
    $stockAction->firma_id   = $firma->id;
    $stockAction->stokId     = $stokId;
    $stockAction->islem      = $request->islem;
    $stockAction->adet       = $request->adet;
    $stockAction->fiyat      = $request->islem == 1 ? $fiyat : null;
    $stockAction->fiyatBirim = $request->islem == 1 ? $request->fiyatBirim : null;
    $stockAction->tedarikci  = $request->tedarikci;

    if ($request->islem == 1) {
        // Alış işlemi
        $stockAction->pid = $personel_id; // işlemi yapan kişi
        $stockAction->kid = $personel_id; // stoğa ekleyen personel
        $stockAction->servisid = null;
    } elseif ($request->islem == 2) {
        // Serviste kullanım - personel stoğundan düşme
        $stockAction->pid = $personel_id; // işlemi yapan kişi
        $stockAction->kid = $request->personel; // stoğu düşülecek personel
        $stockAction->servisid = $request->servisid; // servis id zorunlu
    } elseif ($request->islem == 3) {
        // Personele gönderme - genel stoktan düşme, personel stokuna ekleme
        $stockAction->pid = $request->personel; // stoğu alan personel
        $stockAction->kid = $personel_id;       // işlemi yapan kişi
        $stockAction->servisid = null;
    }

    $stockAction->save();

    // Activity log ekle
    ActivityLogger::logStockAction($stokId, $request->islem, $request->adet, $stockAction->id);

    // --- Stok Güncellemeleri ---
    if ($request->islem == 1) {
        $stock = \App\Models\Stock::find($stokId);
        if ($stock) {
            $stock->save();
        }
    }

    if ($request->islem == 2) {
        // Serviste kullanım: Personel stoğundan düş
        $personelStok = PersonelStock::where('firma_id', $firma->id)
            ->where('pid', $request->personel)
            ->where('stokid', $stokId)
            ->first();

        if ($personelStok) {
            $personelStok->adet -= $request->adet;
            if ($personelStok->adet < 0) {
                $personelStok->adet = 0; // Negatif stok olmasın
            }
            $personelStok->save();
        }
    }

    if ($request->islem == 3) {
        // Personele gönderme: Personel stokuna ekle/güncelle
        $personelStok = PersonelStock::where('firma_id', $firma->id)
            ->where('pid', $request->personel)
            ->where('stokid', $stokId)
            ->first();

        if ($personelStok) {
            $personelStok->adet += $request->adet;
            $personelStok->save();
            $actionMessage = 'Stok başarıyla personele eklendi (mevcut stok güncellendi).';
        } else {
            $personelStok = PersonelStock::create([
                'stokid'   => $stokId,
                'kid'      => $firma->id,
                'firma_id' => $firma->id,
                'pid'      => $request->personel,
                'adet'     => $request->adet,
            ]);
            $actionMessage = 'Stok başarıyla personele gönderildi (yeni kayıt oluşturuldu).';
        }

        // StockAction ile personel stok kaydını ilişkilendir
        $stockAction->perStokId = $personelStok->id;
        $stockAction->save();
        

        return response()->json([
        'status' => 'success',
        'message' => $actionMessage
    ]);
    }
    // Eğer işlem 2 veya 1 ise başarılı mesajı döndür
    return response()->json([
        'status' => 'success',
        'message' => 'Stok hareketi başarıyla kaydedildi.',
    ]);
}


public function DeleteStockAction(Request $request, $tenant_id, $id)
{
    $firma = Tenant::findOrFail($tenant_id);
    $stockAction = StockAction::where('firma_id', $firma->id)->where('id', $id)->first();

    if (!$stockAction) {
        return response()->json([
            'status' => 'error',
            'message' => 'Silmek istediğiniz stok hareketi bulunamadı.'
        ]);
    }

    // İşlem 2 ve 4 serviste kullanım kontrolü
    if (in_array($stockAction->islem,[2,4])) {
         return response()->json([
            'status' => 'warning',
            'message' => 'Serviste kullanılmış bir parçayı silemezsiniz. Silmek için servis içerisinden işlem yapmanız gerekmektedir.'
        ]);
    }
    // İşlem 3 (Personel'e Gönder) kontrolü - personel bu stoku serviste kullanmış mı?
    if ($stockAction->islem == 3) {
        $personelStokKullanimi = StockAction::where('stokId', $stockAction->stokId)
            ->where('firma_id', $firma->id)
            ->where('kid', $stockAction->pid) // stoğu alan personel
            ->where('islem', 2) // serviste kullanım
            ->where('created_at', '>', $stockAction->created_at) // bu gönderimden sonra
            ->exists();

        if ($personelStokKullanimi) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Stok personel tarafından serviste kullanıldığı için silinemez.'
            ]);
        }
    }
    
    if ($stockAction->islem == 1) {
        // Bu alış işleminden sonra çıkış yapılmış mı?
        $girisTarihi = $stockAction->created_at;

        $cikisVarMi = StockAction::where('stokId', $stockAction->stokId)
            ->where('firma_id', $firma->id)
            ->whereIn('islem', [2, 3]) // çıkış işlemleri
            ->where('created_at', '>', $girisTarihi) // bu alıştan sonra yapılmış mı?
            ->exists();

        if ($cikisVarMi) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Alış işleminden sonra stok çıkışı yapıldığı için silinemez.'
            ]);
        }
    }


   try {
     // İşlem 3 (Personele gönderme) ise personel stoğundan düş
        if ($stockAction->islem == 3) {
            $personelStok = PersonelStock::where('firma_id', $firma->id)
                ->where('pid', $stockAction->pid) // stoğu alan personel
                ->where('stokid', $stockAction->stokId)
                ->first();

            if ($personelStok) {
                $personelStok->adet -= $stockAction->adet; // Sadece bu hareketin miktarını düş
                
                // Eğer stok 0 veya negatif olursa kaydı sil
                if ($personelStok->adet <= 0) {
                    $personelStok->delete();
                } else {
                    $personelStok->save();
                }
            }
        }

        // İşlem 2 (Serviste kullanım) ise personel stoğuna geri ekle
        if ($stockAction->islem == 2) {
            $personelStok = PersonelStock::where('firma_id', $firma->id)
                ->where('pid', $stockAction->kid) // stoğu kullanılan personel
                ->where('stokid', $stockAction->stokId)
                ->first();

            if ($personelStok) {
                $personelStok->adet += $stockAction->adet; // Stoğu geri ekle
                $personelStok->save();
            } else {
                // Eğer personel stok kaydı yoksa yeni oluştur
                PersonelStock::create([
                    'stokid'   => $stockAction->stokId,
                    'kid'      => $firma->id,
                    'firma_id' => $firma->id,
                    'pid'      => $stockAction->kid,
                    'adet'     => $stockAction->adet,
                ]);
            }
        }

        $stockAction->delete();

    return response()->json([
        'status' => 'success',
        'message' => 'Stok hareketi başarıyla silindi.'
    ]);
} catch (\Exception $e) {
    return response()->json([
        'status' => 'error',
        'message' => 'Hata oluştu: ' . $e->getMessage(),
    ]);
}
   
}


//////Personel Stok////////
public function GetPersonelStocks($tenant_id, $stok_id)
{
     $firma = Tenant::findOrFail($tenant_id);
     $hareketler = StockAction::with('aliciPersonel')
    ->where('firma_id', $firma->id)
    ->where('stokId', $stok_id)
    ->where('islem', 3) // sadece personel'e gönderilenler
    ->get()
    ->groupBy(function ($hareket) {
        return optional($hareket->aliciPersonel)->user_id;
    })
    ->map(function ($grouped) use ($stok_id) {
        $hareket = $grouped->first(); // aynı personelin ilk hareketini al
        $aliciId = $hareket->aliciPersonel->user_id ?? null;

        $hareket->guncel_adet = $aliciId
            ? PersonelStock::where('stokid', $stok_id)
                ->where('pid', $aliciId)
                ->sum('adet')
            : 0;

        return $hareket;
    })
    ->values(); // map sonrası index'leri düzeltir
    return view('frontend.secure.stocks.personel_stocks', compact('hareketler'));
}


//////Stok Fotoğrafları////////
public function getPhotos($tenant_id, $stock_id)
{
    $photos = stock_photos::where('kid', $tenant_id)
                        ->where('stock_id', $stock_id)
                        ->latest()
                        ->get();

    return view('frontend.secure.stocks.stock_photos', compact('photos', 'stock_id', 'tenant_id'));
}

public function uploadPhoto(Request $request, $tenant_id)
{
    $request->validate([
        'resim' => 'required|file|mimes:jpg,jpeg,png|max:5120',
        'stock_id' => 'required|integer',

    ]);
    


    $image = $request->file('resim');
    $extension = $image->getClientOriginalExtension();

    //İlgili stok bilgisi
    $stock = Stock::findOrFail($request->stock_id);
    $stokAdi = $stock->urunAdi ?? 'bilinmeyen-urun';
    $stokSlug = Str::slug(Str::limit($stokAdi, 50)); 

    // Klasör ve dosya adı
    $today = now()->toDateString(); 
    $uuid = Str::uuid()->toString() . '.' . $extension;
    $path =  "stock_photos/stock_{$stock->id}_{$stokSlug}/{$today}";
    $fullPath = "{$path}/{$uuid}";

    // Resize işlemi (665px genişlik, oran korunsun)
    $resizedImage = Image::make($image->getPathname())
        ->resize(665, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })->encode($extension, 75); // kalite 

    // Storage'a kaydet (public disk)
    Storage::disk('public')->put($fullPath, $resizedImage);

   
    // Veritabanına kaydet
    $photo = stock_photos::create([
        'kid' => $tenant_id,
        'stock_id' => $request->stock_id,
        'resimyol' => $fullPath,
        'created_at' => now(),
    ]);

    return response()->json([
        'id' => $photo->id,
        'resim_yolu' => Storage::url($photo->resimyol),
        'message' => 'Fotoğraf başarıyla yüklendi.'
    ]);
} 

public function deletePhoto(Request $request, $tenant_id)
{
    $photo = stock_photos::where('id', $request->id)
                        ->where('kid', $tenant_id)
                        ->first(); // firstOrFail() yerine first() kullanıldı

    if (!$photo) {
        // Fotoğraf bulunamadıysa hata mesajı döndür
        return response()->json([
            'message' => 'Fotoğraf bulunamadı veya silme yetkiniz yok.',
            'alert_type' => 'error'
        ], 404); // 404 Not Found durum kodu gönderilir
    }

    // Dosyanın varlığını kontrol et ve sil
    if (Storage::disk('public')->exists($photo->resimyol)) {
        Storage::disk('public')->delete($photo->resimyol);
    }

    // Veritabanından kaydı sil
    $photo->delete();

    return response()->json([
        'message' => 'Fotoğraf başarıyla silindi.',
        'alert_type' => 'success'
    ]);
}

///////////Barkod PDF Oluşturma///////////////////
public function barkodPdf($tenant_id, $id) {
    $stock = Stock::where('firma_id', $tenant_id)->findOrFail($id);

    $pdf = Pdf::loadView('frontend.secure.stocks.stocks_barkod', compact('stock'))
        // 50mm x 25mm boyutları piksel olarak:
        ->setPaper('50mm', '25mm', 'portrait') // setPaper doğrudan mm değerlerini alabilir
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'dpi' => 300,
            'defaultFont' => 'Arial',
            'margin-top' => 0,
            'margin-right' => 0,
            'margin-bottom' => 0,
            'margin-left' => 0,
        ]);

    return $pdf->stream("barkod-{$stock->urunKodu}.pdf");
}
//Ürün Adı Kontolü
public function checkProductName(Request $request, $tenant_id)
{
    $urunAdi = $request->input('urunAdi');

    $stock = Stock::where('firma_id', $tenant_id)
                  ->where('urunAdi', $urunAdi)
                  ->first();

    if ($stock) {
        // Urun kategorisine göre route belirle
        if ($stock->urunKategori == 3) {
            // Konsinye cihaz
            $editUrl = route('edit.consignment.device', ['tenant_id' => $tenant_id, 'id' => $stock->id]);
        } else {
            // Normal stok
            $editUrl = route('edit.stock', ['tenant_id' => $tenant_id, 'id' => $stock->id]);
        }

        return response()->json([
            'exists' => true,
            'edit_url' => $editUrl
        ]);
    }

    return response()->json(['exists' => false]);
}


//////////////////////////////////////////////Konsinye Cihazlar///////////////////////////////////////////////////////////////////////////
public function consignmentDevice($tenant_id)
{
    $firma = Tenant::findOrFail($tenant_id);
    $personeller = User::where('tenant_id', $tenant_id)->get();
    $rafListesi = StockShelf::where('firma_id', $tenant_id)->get();

    // Tenant'ın sektörünü kontrol et
    $isBeyazEsya = $firma->sektor === 'beyaz-esya';
    $markalar = DeviceBrand::where(function($query) use ($firma, $isBeyazEsya) {
        if ($isBeyazEsya) {
            // Beyaz eşya sektörü: default + kendi eklediği
            $query->whereNull('firma_id')
                ->orWhere('firma_id', $firma->id);
        } else {
            // Diğer sektörler: sadece kendi eklediği
            $query->where('firma_id', $firma->id);
        }
    })->orderBy('marka', 'asc')->get();
    $cihazlar = DeviceType::where(function($query) use ($firma, $isBeyazEsya) {
        if ($isBeyazEsya) {
            // Beyaz eşya sektörü: default + kendi eklediği
            $query->whereNull('firma_id')
                ->orWhere('firma_id', $firma->id);
        } else {
            // Diğer sektörler: sadece kendi eklediği
            $query->where('firma_id', $firma->id);
        }
    })->orderBy('cihaz', 'asc')->get();


    return view('frontend.secure.stocks.consignment_device', compact('firma', 'personeller', 'rafListesi', 'markalar', 'cihazlar'));
}

public function consignmentDeviceData(Request $request, $tenant_id)
{
    $query = Stock::select('stocks.*')
        ->join('stock_categories as kategori', 'kategori.id', '=', 'stocks.urunKategori')
        ->where('stocks.firma_id', $tenant_id)
        ->where('kategori.id', '=', 3);

            // ========== TARİH FİLTRELEME MANTĞI BAŞLANGIÇ ==========
    $hasUserSelectedConsignmentDate = $request->filled('from_date_consignment') && $request->filled('to_date_consignment') && !$this->isDefaultConsignmentDateRange($request);
    $hasDashboardDate = $request->filled('dashboard_istatistik_tarih1') && $request->filled('dashboard_istatistik_tarih2');
    $hasSearchOrOtherFilters = !empty(trim($request->get('search')['value'] ?? '')) || 
                                $request->filled('marka') || 
                                $request->filled('raf') || 
                                $request->filled('cihaz') ||
                                $request->filled('personel');

    if ($hasUserSelectedConsignmentDate) {
        // Konsinye sayfasındaki tarih filtresi en yüksek önceliğe sahiptir
        $this->applyMainConsignmentDateRange($query, $request);
    } elseif ($hasDashboardDate && !$hasSearchOrOtherFilters) {
        // Konsinye tarih filtresi yoksa ve dashboard tarihi varsa, onu uygula (ancak başka arama/filtre yoksa)
        $startDate = Carbon::parse($request->get('dashboard_istatistik_tarih1'))->startOfDay();
        $endDate = Carbon::parse($request->get('dashboard_istatistik_tarih2'))->endOfDay();
        $query->whereBetween('stocks.created_at', [$startDate, $endDate]);
    } elseif (!$hasUserSelectedConsignmentDate && !$hasDashboardDate && !$hasSearchOrOtherFilters) {
        // Hiçbir tarih veya arama/filtre yoksa, varsayılan son 3 günü uygula
        $from = Carbon::today()->subDays(2)->startOfDay();
        $to   = Carbon::today()->endOfDay();
        $query->whereBetween('stocks.created_at', [$from, $to]);
    }
    // Eğer $hasSearchOrOtherFilters true ise ancak tarih filtresi seçilmemişse,
    // herhangi bir tarih kısıtlaması uygulanmaz, bu da tüm kayıtlarda arama yapılmasını sağlar.
    // ========== TARİH FİLTRELEME MANTĞI SON ==========

    if ($request->filled('marka')) {
        $query->where('stok_marka', $request->marka);
    }
    if ($request->filled('raf')) {
        $query->where('urunDepo', $request->raf);
    }
    if ($request->filled('cihaz')) {
        $query->where('stok_cihaz', $request->cihaz);
    }
    if ($request->filled('personel')) {
        $query->where('pid', $request->personel);
    }

    // Sıralama
    if ($request->has('order')) {
        $order = $request->get('order')[0];
        $columns = $request->get('columns');
        $orderColumn = $columns[$order['column']]['data'];
        $orderDir = $order['dir'];
        $query->orderBy($orderColumn, $orderDir);
    } else {
        $query->orderBy('id', 'desc');
    }

    // Toplamlar hesapla
    $stocksForTotal = $query->get();
    $toplamAdet = 0;
    $toplamFiyat = 0;

    foreach ($stocksForTotal as $stock) {
    $girisAdet  = \App\Models\StockAction::where('stokId', $stock->id)
        ->whereIn('islem', [1, 4])
        ->sum('adet');

    $cikisAdet = \App\Models\StockAction::where('stokId', $stock->id)
        ->whereIn('islem', [2])
        ->sum('adet');

    $kalanStok = $girisAdet - $cikisAdet;

    $toplamAdet += max($kalanStok, 0); // negatifse 0 yap
    // Toplam fiyat hesaplama - Fiyat × Kalan Adet
    $fiyat = max($stock->fiyat, 0);
    $toplamFiyat += ($fiyat * $kalanStok);
    }


    return DataTables::of($query)
        ->addIndexColumn()
        ->addColumn('id', function($row) {
            return '<a href="javascript:void(0);" class="t-link editConsignment" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editConsignmentModal">' . e($row->id) . '</a>';
        })
        ->addColumn('urunKodu', function($row) {
            return '<a href="javascript:void(0);" class="t-link editConsignment" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editConsignmentModal">' . e($row->urunKodu) . '</a>';
        })
        ->addColumn('urunAdi', function($row) {
            return '<a href="javascript:void(0);" class="t-link editConsignment" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editConsignmentModal">' . e($row->urunAdi) . '</a>';
        })
        ->addColumn('adet', function($row) {
            $girisAdet = \App\Models\StockAction::where('stokId', $row->id)
                ->whereIn('islem', [1, 4])->sum('adet');
            $cikisAdet = \App\Models\StockAction::where('stokId', $row->id)
                ->whereIn('islem', [2])->sum('adet');
            $kalan = $girisAdet - $cikisAdet;
            return '<a href="javascript:void(0);" class="t-link editConsignment" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editConsignmentModal">' . max($kalan, 0) . '</a>';
        })
        ->addColumn('toplamTutar', function($row) {
            $girisler = \App\Models\StockAction::where('stokId', $row->id)
                ->whereIn('islem', [1, 4])->get();

            $tutar = $row->fiyat ?? 0;

        return '<a href="javascript:void(0);" class="t-link editConsignment" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editConsignmentModal">' . number_format($tutar, 2, ',', '.') . ' ₺</a>';
        })
        ->addColumn('raf_adi', function($row) {
            $raf = $row->raf ? e($row->raf->raf_adi) : '-';
            return '<a href="javascript:void(0);" class="t-link editConsignment" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editConsignmentModal">' . $raf . '</a>';
        })
        ->addColumn('marka_cihaz', function($row) {
            $marka = $row->marka ? e($row->marka->marka) : '';
            $cihaz = $row->cihaz ? e($row->cihaz->cihaz) : '';
            $text = trim($marka . ' / ' . $cihaz, ' / ');
            return '<a href="javascript:void(0);" class="t-link editConsignment" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editConsignmentModal">' . $text . '</a>';
        })
        ->editColumn('created_at', function($row) {
            $date = $row->created_at ? $row->created_at->format('d.m.Y H:i') : '';
            return '<a href="javascript:void(0);" class="t-link editConsignment" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editConsignmentModal">' . $date . '</a>';
        })
        ->addColumn('action', function($row) use ($tenant_id) {
            $deleteUrl = route('delete.stock', [$tenant_id, $row->id]);
            $editBtn = '<a href="javascript:void(0);" class="btn btn-outline-warning btn-sm editConsignment" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editConsignmentModal" title="Düzenle"><i class="fas fa-edit"></i></a>';
            $viewBtn = '<a href="javascript:void(0);" class="btn btn-outline-primary btn-sm editConsignment" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editConsignmentModal" title="Düzenle"><i class="fas fa-eye"></i></a>';
            $delBtn = '<a href="'.$deleteUrl.'" class="btn btn-outline-danger btn-sm" title="Sil" onclick="return confirm(\'Silmek istediğinize emin misiniz?\');"><i class="fas fa-trash-alt"></i></a>';
            return $viewBtn . ' ' . $editBtn . ' ' . $delBtn;
        })
        ->filter(function ($query) use ($request) {
            if ($search = $request->get('search')['value'] ?? null) {
                $query->where(function ($q) use ($search) {
                    $q->where('urunAdi', 'like', "%{$search}%")
                      ->orWhere('urunKodu', 'like', "%{$search}%");
                });
            }
        })
        ->rawColumns(['id','urunKodu', 'urunAdi', 'adet', 'toplamTutar', 'raf_adi', 'marka_cihaz', 'created_at', 'action'])
        ->with([
            'toplamAdet' => number_format($toplamAdet),
            'toplamFiyat' => number_format($toplamFiyat, 2, ',', '.') . ' ₺',
            'toplamAdetRaw' => $toplamAdet,
            'toplamFiyatRaw' => $toplamFiyat,
        ])
        ->make(true);
}
// Helper Methods - Konsinye Tarih Filtreleme İçin
private function isDefaultConsignmentDateRange(Request $request): bool
{
    if (!$request->filled('from_date_consignment') || !$request->filled('to_date_consignment')) {
        return false;
    }
    
    try {
        $from = Carbon::createFromFormat('Y-m-d', $request->from_date_consignment)->startOfDay();
        $to = Carbon::createFromFormat('Y-m-d', $request->to_date_consignment)->endOfDay();
        $defaultFrom = Carbon::today()->subDays(2)->startOfDay();
        $defaultTo = Carbon::today()->endOfDay();
        
        return $from->equalTo($defaultFrom) && $to->equalTo($defaultTo);
    } catch (\Exception $e) {
        return false;
    }
}

private function applyMainConsignmentDateRange($query, Request $request): void
{
    if ($request->filled('from_date_consignment') && $request->filled('to_date_consignment')) {
        $from = Carbon::createFromFormat('Y-m-d', $request->from_date_consignment)->startOfDay();
        $to   = Carbon::createFromFormat('Y-m-d', $request->to_date_consignment)->endOfDay();
        $query->whereBetween('stocks.created_at', [$from, $to]);
    }
}
// Konsinye cihaz ekleme 
public function AddConsignmentDevice($tenant_id)
{
    $firma = Tenant::findOrFail($tenant_id);
    $rafListesi = StockShelf::where('firma_id', $tenant_id)->get();

    $isBeyazEsya = $firma->sektor === 'beyaz-esya';

    $markalar = DeviceBrand::where(function($query) use ($firma, $isBeyazEsya) {
        if ($isBeyazEsya) {
            // Beyaz eşya sektörü: default + kendi eklediği
            $query->whereNull('firma_id')
                ->orWhere('firma_id', $firma->id);
        } else {
            // Diğer sektörler: sadece kendi eklediği
            $query->where('firma_id', $firma->id);
        }
    })->orderBy('marka', 'asc')->get();
    $cihazlar = DeviceType::where(function($query) use ($firma, $isBeyazEsya) {
        if ($isBeyazEsya) {
            // Beyaz eşya sektörü: default + kendi eklediği
            $query->whereNull('firma_id')
                ->orWhere('firma_id', $firma->id);
        } else {
            // Diğer sektörler: sadece kendi eklediği
            $query->where('firma_id', $firma->id);
        }
    })->orderBy('cihaz', 'asc')->get();
    $kategoriler = StockCategory::where('firma_id', $tenant_id)->get();

    return view('frontend.secure.stocks.add_consignment_device', compact('firma', 'rafListesi', 'markalar', 'cihazlar', 'kategoriler', 'tenant_id'));
}

// Konsinye cihaz kayıt işlemi
public function StoreConsignmentDevice(Request $request, $tenant_id)
{
    $token = $request->input('form_token');
    // Token boş mu kontrol et
    if (empty($token)) {
        return back()->withInput()->with([
            'message' => 'Geçersiz form token! Lütfen sayfayı yenileyin.',
            'alert-type' => 'error'
        ]);
    }
    // Bu token daha önce kullanıldı mı kontrol et
    $cacheKey = 'consignment_form_token_' . $token;
    if (Cache::has($cacheKey)) {
        return back()->withInput()->with([
            'message' => 'Bu form zaten gönderildi! Lütfen bekleyin veya sayfayı yenileyin.',
            'alert-type' => 'warning'
        ]);
    }
    // Token'ı 10 dakika boyunca sakla
    Cache::put($cacheKey, true, now()->addMinutes(10));


    $firma = Tenant::findOrFail($tenant_id);
    if (!$firma) {
    $notification = [
        'message' => 'Firma bulunamadı.',
        'alert-type' => 'danger',
    ];
    return redirect()->route('giris')->with($notification);
    }

    // 1. Mevcut personel sayısı
        $current = Stock::where('firma_id', $firma->id)
        ->where('durum','1')
        ->where('urunKategori',  3)
        ->count();

        // 2. Planın izin verdiği limit
        $limit = $firma->plan()?->limits['konsinye'] ?? null;

        // 3. Limit dolmuş mu kontrol et
        if ($limit !== null && $limit !== -1 && $current >= $limit) {
            return back()->with('error', 'Maksimum stok limitine ulaştınız.');
        }

 // Ürün kodu kontrolü
    $existingStock = Stock::where('firma_id', $tenant_id)
                          ->where('urunKodu', $request->urunKodu)
                          ->first();

    if ($existingStock) {
        $notification = [
            'message' => 'Bu ürün kodu zaten mevcut. Lütfen farklı bir ürün kodu girin.',
            'alert-type' => 'warning',
        ];
        return redirect()->back()->withInput()->with($notification);
    }

     $request->validate([
        'urunKodu' => ['required', 'digits:13', 'unique:stocks,urunKodu,NULL,id,firma_id,'.$tenant_id],
        // 'digits:13' => tam 13 rakam olmalı,
        // unique kontrolü firma_id bazlı, yani aynı firmada tekrar olmasın
        
    ],[
        'urunKodu.required' => 'Ürün kodu zorunludur.',
        'urunKodu.digits' => 'Ürün kodu tam 13 haneli olmalıdır.',
        'urunKodu.unique' => 'Bu ürün kodu zaten mevcut. Lütfen farklı bir ürün kodu girin.',
    ]);
    // Ürün adı kontrolü
    $existingName = Stock::where('firma_id', $tenant_id)
                        ->where('urunAdi', $request->urunAdi)
                        ->first();

    if ($existingName) {
        $notification = [
            'message' => 'Bu ürün adı zaten mevcut. Lütfen farklı bir ürün adı girin.',
            'alert-type' => 'warning',
        ];
        return redirect()->back()->withInput()->with($notification);
    }
    $request->validate([
    'urunKodu' => ['required', 'digits:13', 'unique:stocks,urunKodu,NULL,id,firma_id,'.$tenant_id],
    'urunAdi' => ['required', 'max:255'],
    ],[
        'urunKodu.required' => 'Ürün kodu zorunludur.',
        'urunKodu.digits' => 'Ürün kodu tam 13 haneli olmalıdır.',
        'urunKodu.unique' => 'Bu ürün kodu zaten mevcut. Lütfen farklı bir ürün kodu girin.',
        'urunAdi.required' => 'Ürün adı zorunludur.',
    ]);
    
    $personel_id = Auth::user()->user_id;

    $stock = new Stock();
    $stock->firma_id = $firma->id;
    $stock->pid = $personel_id;
    $stock->urunAdi = $request->urunAdi;
    $stock->urunKodu = $request->urunKodu;
    $stock->urunKategori = 3; // Konsinye kategori ID'si
    $stock->aciklama = $request->aciklama;
    $stock->urunDepo = $request->raf_id;
    $stock->fiyat = $request->fiyat;
    $stock->fiyatBirim = 1; // Her zaman TL (1) olarak kaydet
    $stock->stok_marka = $request->marka_id;
    $stock->stok_cihaz = $request->cihaz_id;
    $stock->save();

    // Activity log ekle
    ActivityLogger::logConsignmentCreated($stock->id, $stock->urunAdi);


    return redirect()->route('consignmentdevice', $tenant_id)
                     ->with(['message' => 'Konsinye cihaz başarıyla kaydedildi.', 'alert-type' => 'success']);
}

public function EditConsignmentDevice($tenant_id, $id)
{
    $firma = Tenant::findOrFail($tenant_id);

   $stock = Stock::with(['raf', 'marka', 'cihaz', 'sonHareket'])->findOrFail($id);

    $rafListesi = StockShelf::where('firma_id', $tenant_id)->get();

    // Tenant'ın sektörünü kontrol et
    $isBeyazEsya = $firma->sektor === 'beyaz-esya';

    $markalar = DeviceBrand::where(function($query) use ($firma, $isBeyazEsya) {
        if ($isBeyazEsya) {
            // Beyaz eşya sektörü: default + kendi eklediği
            $query->whereNull('firma_id')
                ->orWhere('firma_id', $firma->id);
        } else {
            // Diğer sektörler: sadece kendi eklediği
            $query->where('firma_id', $firma->id);
        }
    })->orderBy('marka', 'asc')->get();
    $cihazlar = DeviceType::where(function($query) use ($firma, $isBeyazEsya) {
        if ($isBeyazEsya) {
            // Beyaz eşya sektörü: default + kendi eklediği
            $query->whereNull('firma_id')
                ->orWhere('firma_id', $firma->id);
        } else {
            // Diğer sektörler: sadece kendi eklediği
            $query->where('firma_id', $firma->id);
        }
    })->orderBy('cihaz', 'asc')->get();

    
    if ($stock->urunKategori != 3) {
        abort(404, "Bu ürün bir konsinye cihazı olarak kayıtlı değil.");
    }

    //Konsinyeye Özel Stok Hareketlerini Çek
    $stokHareketleri = StockAction::with(['servis.musteri'])
        ->select(
            'stock_actions.*',
            'ss.tedarikci as tedarikci_adi',
            'up.name as performer_name'  // pid alanından kullanıcı adını al
        )
        ->leftJoin('stock_suppliers as ss', 'ss.id', '=', 'stock_actions.tedarikci')
        ->leftJoin('tb_user as up', 'up.user_id', '=', 'stock_actions.pid') // pid ile JOIN
        ->where('stock_actions.stokId', $id)
        ->whereIn('stock_actions.islem', [1, 2, 4]) // 1: Alış, 2: Serviste Kullanım, 4: Müşteriden Geri Alma
        ->orderBy('stock_actions.id', 'desc')
        ->get();

    // Ürüne Ait Fotoğraflar
    $photos = stock_photos::where('kid', $tenant_id)
                          ->where('stock_id', $id)
                          ->latest()
                          ->get();

    // 5. "Hareket Ekle" modali için son kullanılan tedarikçileri çek
    $sonTedarikciler = StockSupplier::where('firma_id', $tenant_id)
                                    ->latest() // En son eklenene göre
                                    ->take(5)    // Sadece 5 tane al
                                    ->get();

    // 6. Tüm verileri view'e göndererek HTML'i oluştur
    $html = view('frontend.secure.stocks.edit_consignment_device', compact(
        'firma',
        'markalar',
        'cihazlar',
        'rafListesi',
        'stock',
        'stokHareketleri',
        'photos',
        'sonTedarikciler'
    ))->render();

    // 7. Oluşturulan HTML'i ve ürün adını JSON olarak döndür
    return response()->json([
        'html' => $html,
        'urunAdi' => $stock->urunAdi,
    ]);
}
public function UpdateConsignmentDevice(Request $request, $tenant_id, $id)
{
    $firma = Tenant::findOrFail($tenant_id);
    $personel_id = Auth::user()->user_id;
    $stock = Stock::findOrFail($id);

    if ($stock->urunKategori != 3) {
        return response()->json([
            'status' => 'error',
            'message' => 'Bu ürün konsinye cihaz değil.'
        ], 404);
    }

    // Ürün kodu kontrolü
    $existingStock = Stock::where('firma_id', $tenant_id)
        ->where('urunKodu', $request->urunKodu)
        ->where('id', '!=', $id)
        ->first();

    if ($existingStock) {
        return response()->json([
            'status' => 'error',
            'message' => 'Bu ürün kodu zaten mevcut. Lütfen farklı bir ürün kodu girin.'
        ], 400);
    }

    // Ürün adı benzersiz mi?
    $existingName = Stock::where('firma_id', $tenant_id)
        ->where('urunAdi', $request->urunAdi)
        ->where('id', '!=', $id)
        ->first();

    if ($existingName) {
        return response()->json([
            'status' => 'error',
            'message' => 'Bu ürün adı zaten mevcut. Lütfen farklı bir ürün adı girin.'
        ], 400);
    }

    // Validation
    $request->validate([
        'urunKodu' => ['required', 'digits:13', 'unique:stocks,urunKodu,'.$id.',id,firma_id,'.$tenant_id],
        'urunAdi' => 'required|max:255',
        'raf_id' => 'required',
    ],[
        'urunKodu.required' => 'Ürün kodu zorunludur.',
        'urunKodu.digits' => 'Ürün kodu tam 13 haneli olmalıdır.',
        'urunKodu.unique' => 'Bu ürün kodu zaten mevcut. Lütfen farklı bir ürün kodu girin.',
        'urunAdi.required' => 'Ürün adı zorunludur.',
    ]);

    $stock->urunAdi = $request->urunAdi;
    $stock->urunKodu = $request->urunKodu;
    $stock->urunKategori = 3;
    $stock->urunDepo = $request->raf_id;
    $stock->aciklama = $request->aciklama;
    $stock->fiyat = $request->fiyat;
    $stock->fiyatBirim = 1; // Her zaman TL (1) olarak kaydet
    $stock->stok_marka = $request->marka_id;
    $stock->stok_cihaz = $request->cihaz_id;
    $stock->save();

   return response()->json([
            'status' => 'success',
            'message' => 'Konsinye cihaz başarıyla güncellendi.'
    ]);
}


///////////Konsinye Cihaz Stok Haraketleri/////////////////
public function ConsignmentStockActions($tenant_id, $stock_id)
{
    $firma = Tenant::findOrFail($tenant_id);
    $stock = Stock::with(['marka', 'cihaz', 'raf'])
        ->where('firma_id', $tenant_id)
        ->where('urunKategori', 3) // konsinye cihaz
        ->findOrFail($stock_id);

    // Stok hareketlerini join ile getir
    $stokHareketleri = StockAction::with(['musteri'])
            ->select(
                'stock_actions.*',
                'stock_suppliers.tedarikci',
                'user_recipient.name as recipient_name', // islem=3 (Personel'e Gönder) için alıcı personel adı
                'user_performer.name as performer_name'  // islem=2 (Serviste Kullanım) için işlemi yapan personel adı
            )
            // Tedarikçi tablosu ile birleştirme
            ->leftJoin('stock_suppliers', 'stock_suppliers.id', '=', 'stock_actions.tedarikci')
            // 'pid' sütunu üzerinden kullanıcı tablosu ile birleştirme (alıcı personel için)
            ->leftJoin('tb_user as user_recipient', 'user_recipient.user_id', '=', 'stock_actions.pid')
            // 'kid' sütunu üzerinden kullanıcı tablosu ile birleştirme (işlemi yapan personel için)
            ->leftJoin('tb_user as user_performer', 'user_performer.user_id', '=', 'stock_actions.kid')
            ->where('stock_actions.stokId', $stock_id)
            ->orderBy('stock_actions.id', 'desc')
            ->get();
    return view('frontend.secure.stocks.consignment_stock_actions', compact('stock', 'stokHareketleri','firma'));
}

public function StoreConsignmentStockAction(Request $request, $tenant_id)
{
    $firma = Tenant::findOrFail($tenant_id);

    // Token kontrolü
    $token = $request->input('form_token');
    
    if (empty($token)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Geçersiz form token! Lütfen sayfayı yenileyin.',
        ], 400);
    }
    
    $cacheKey = 'stock_action_token_' . $token;
    if (Cache::has($cacheKey)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Bu form zaten gönderildi! Lütfen bekleyin.',
        ], 400);
    }
    
    Cache::put($cacheKey, true, now()->addMinutes(10));

    $rules = [
        'stok_id'    => 'required|integer',
        'islem'      => 'required|in:1,2,4',
        'adet'       => 'required|integer|min:1',
        'fiyat'      => 'nullable|numeric',
        'fiyatBirim' => 'nullable|numeric',
        'tedarikci'  => 'nullable|string|max:255',
    ];

    $messages = [
        'servisid.required' => 'Serviste kullanım işlemi için servis ID alanı zorunludur.',
        'servisid.integer'  => 'Servis ID bir sayı olmalıdır.',
    ];

    $validator = Validator::make($request->all(), $rules, $messages);

    // islem değeri 2 ise 'servisid' alanını zorunlu yap
    $validator->sometimes('servisid', 'required|integer', function ($input) {
        return $input->islem == 2;
    });

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Doğrulama hatası',
            'errors' => $validator->errors()
        ], 422);
    }

    $stokId = $request->stok_id;
    $personel_id = Auth::user()->user_id;

    // Fiyatı temizle (nokta ve virgül fix) 
    $fiyat = null;
    if ($request->islem == 1 && $request->filled('fiyat')) {
        $fiyat = floatval(str_replace(['.', ','], ['', '.'], $request->fiyat));
    }

    $toplamGiris = StockAction::where('stokId', $stokId)->whereIn('islem', [1,4])->sum('adet');
    $toplamCikis = StockAction::where('stokId', $stokId)->where('islem', 2)->sum('adet');
    $kalanStok = $toplamGiris - $toplamCikis;

    // Serviste kullanım için yeterli stok var mı?
    if ($request->islem == 2 && $request->adet > $kalanStok) {
        return response()->json([
            'status' => 'error',
            'message' => 'Yetersiz stok! Mevcut: ' . $kalanStok . ' adet.'
        ], 400);
    }

    $stockAction = new StockAction();
    $stockAction->firma_id   = $firma->id;
    $stockAction->pid        = $personel_id;
    $stockAction->stokId     = $stokId;
    $stockAction->servisid   = $request->servisid; 
    $stockAction->islem      = $request->islem;
    $stockAction->adet       = $request->adet;
    $stockAction->fiyat      = $request->islem == 1 ? $fiyat : null;
    $stockAction->fiyatBirim = $request->islem == 1 ? $request->fiyatBirim : null;
    $stockAction->tedarikci  = $request->tedarikci;
    $stockAction->save();

    // Activity log ekle
    ActivityLogger::logStockAction($stokId, $request->islem, $request->adet, $stockAction->id);
    
    // Alış işlemi: Stock tablosundaki fiyatı güncelle 
    if ($request->islem == 1 && $fiyat) {
        $stock = Stock::find($stokId);
        if ($stock) {
            $stock->save();
        }
    }
    
    return response()->json([
        'status' => 'success',
        'message' => 'Stok hareketi başarıyla kaydedildi.'
    ]);
}
public function DeleteConsignmentStockAction(Request $request, $tenant_id, $id) 
{
    $firma = Tenant::findOrFail($tenant_id);
    $stockAction = StockAction::where('firma_id', $firma->id)->where('id', $id)->first();

    if (!$stockAction) {
        return response()->json([
            'status' => 'error',
            'message' => 'Silmek istediğiniz stok hareketi bulunamadı.'
        ]);
    }

    // İşlem 2 ve 4 serviste kullanım kontrolü
    if (in_array($stockAction->islem, [2, 4])) {
        return response()->json([
            'status' => 'warning',
            'message' => 'Serviste kullanılmış bir parçayı silemezsiniz. Silmek için servis içerisinden işlem yapmanız gerekmektedir.'
        ]);
    }
    
    // İşlem 1 (Alış) kontrolü - Bu alıştan sonra çıkış yapılmış mı?
    if ($stockAction->islem == 1) {
        $girisTarihi = $stockAction->created_at;

        $cikisVarMi = StockAction::where('stokId', $stockAction->stokId)
            ->where('firma_id', $firma->id)
            ->whereIn('islem', [2]) // Konsinye için sadece serviste kullanım çıkışı
            ->where('created_at', '>', $girisTarihi)
            ->exists();

        if ($cikisVarMi) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Alış işleminden sonra stok çıkışı yapıldığı için silinemez.'
            ]);
        }
    }

    try {
        $stockAction->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Stok hareketi başarıyla silindi.'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Hata oluştu: ' . $e->getMessage(),
        ]);
    }
}


//////Konsinye Cihaz Fotoğrafları////////
public function GetConsignmentPhotos($tenant_id, $stock_id)
{
    $photos = stock_photos::where('kid', $tenant_id)
                        ->where('stock_id', $stock_id)
                        ->latest()
                        ->get();

    return view('frontend.secure.stocks.consignment_device_photos', compact('photos', 'stock_id', 'tenant_id'));
}

public function UploadConsignmentPhoto(Request $request, $tenant_id)
{
     $request->validate([
        'resim' => 'required|file|mimes:jpg,jpeg,png|max:5120',
        'stock_id' => 'required|integer'
    ]);


    $image = $request->file('resim');
    $extension = $image->getClientOriginalExtension();

    //İlgili stok bilgisi
    $stock = Stock::findOrFail($request->stock_id);
    $stokAdi = $stock->urunAdi ?? 'bilinmeyen-urun';
    $stokSlug = Str::slug(Str::limit($stokAdi, 50)); 

    // Klasör ve dosya adı
    $today = now()->toDateString(); 
    $uuid = Str::uuid()->toString() . '.' . $extension;
    $path =  "stock_photos/stock_{$stock->id}_{$stokSlug}/{$today}";
    $fullPath = "{$path}/{$uuid}";

    // Resize işlemi (665px genişlik, oran korunsun)
    $resizedImage = Image::make($image->getPathname())
        ->resize(665, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })->encode($extension, 75); // kalite 

    // Storage'a kaydet (public disk)
    Storage::disk('public')->put($fullPath, $resizedImage);

   
    // Veritabanına kaydet
    $photo = stock_photos::create([
        'kid' => $tenant_id,
        'stock_id' => $request->stock_id,
        'resimyol' => $fullPath,
        'created_at' => now(),
    ]);

    return response()->json([
        'id' => $photo->id,
        'resim_yolu' => Storage::url($photo->resimyol),
        'message' => 'Fotoğraf başarıyla yüklendi.'
    ]);
} 
public function DeleteConsignmentPhoto(Request $request, $tenant_id) 
{
    try {
        $photo = stock_photos::where('id', $request->id)
                            ->where('kid', $tenant_id)
                            ->first(); // firstOrFail() yerine first() kullan

        if (!$photo) {
            return response()->json([
                'message' => 'Fotoğraf bulunamadı veya silme yetkiniz yok.',
                'alert_type' => 'error'
            ], 404);
        }

        // Dosyanın varlığını kontrol et ve sil (Storage kullan)
        if (Storage::disk('public')->exists($photo->resimyol)) {
            Storage::disk('public')->delete($photo->resimyol);
        }

        // Veritabanından kaydı sil
        $photo->delete();

        return response()->json([
            'message' => 'Fotoğraf başarıyla silindi.',
            'alert_type' => 'success'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Fotoğraf silme işlemi sırasında hata oluştu: ' . $e->getMessage(),
            'alert_type' => 'danger'
        ], 500);
    }
}

public function ConsignmentBarcode($tenant_id, $id) {
    $stock = Stock::where('firma_id', $tenant_id)->findOrFail($id);
    
    $pdf = Pdf::loadView('frontend.secure.stocks.consignment_device_barcode', compact('stock'))
        ->setPaper([0, 0, 141.7, 70.85], 'portrait')
        ->setOptions([
            'isHtml5ParserEnabled' => true, 
            'isRemoteEnabled' => true,
            'dpi' => 300,
            'defaultFont' => 'Arial'
        ]);
    
    return $pdf->stream("barkod-{$stock->urunKodu}.pdf");
}


}