<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SuperAdminInvoice;
use App\Models\SuperAdminInvoiceProduct;
use App\Models\Tenant;
use App\Models\PaymentMethod;
use App\Models\StoragePurchase;
use App\Models\SubscriptionPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Cache;


class SuperAdminInvoicesController extends Controller
{
    public function AllInvoice(Request $request) {
        $invoices = SuperAdminInvoice::where('durum', 1)->orderBy('id','desc')->get();
        $tenants = Tenant::where('status', 1)->orderBy('firma_adi', 'ASC')->get();

        if ($request->ajax()) {           
            $data = SuperAdminInvoice::with('tenant')->where('durum', '1');
            
            if ($request->get('firma')) {
                $firmaID = $request->get('firma');
                $data->where('firma_id', $firmaID);
            }

            $data->when($request->filled('from_date') && $request->filled('to_date'), function ($query) use ($request) {
                return $query->whereDate('faturaTarihi', '>=', $request->from_date)
                             ->whereDate('faturaTarihi', '<=', $request->to_date);
            });

            // Sıralama işlemi
            if ($request->has('order')) {
                $order = $request->get('order')[0];
                $columns = $request->get('columns');
                $orderColumn = $columns[$order['column']]['data'];
                $orderDir = $order['dir'];
                
                if($orderColumn == 'firma_id'){
                    $data->leftJoin('tenants', 'super_admin_invoices.firma_id', '=', 'tenants.id')
                    ->addSelect(['super_admin_invoices.*', 'tenants.firma_adi as firmaAdi'])
                    ->orderBy('tenants.firma_adi',$orderDir);
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
          ->addColumn('firma_id', function($row){
                return '<a class="t-link editInvoice address" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editInvoiceModal">
                    <span class="mobileTitle">Firma:</span><strong>'.$row->tenant?->firma_adi.'</strong>
                </a>';
            })
            ->addColumn('genelToplam', function($row){
                return '<a class="t-link editInvoice" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editInvoiceModal"><div class="mobileTitle">G. Toplam:</div>'.$row->genelToplam.' ₺</a>';
            })
            ->addColumn('odemeDurum', function($row){
                if($row->faturaDurumu == 'sent'){
                    return '<a class="t-link editInvoice" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editInvoiceModal"><div class="mobileTitle">Durum:</div><div style="color: green; display: inline-block;font-weight:700;">Gönderildi</div></a>';
                }elseif($row->faturaDurumu == 'draft'){
                    return '<a class="t-link editInvoice" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editInvoiceModal"><div class="mobileTitle">Durum:</div><div style="color: #216dfd; display: inline-block;font-weight:700;">Beklemede</div></a>';
                }elseif($row->faturaDurumu == 'error'){
                    return '<a class="t-link editInvoice" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editInvoiceModal"><div class="mobileTitle">Durum:</div><div style="color: red; display: inline-block;font-weight:700;">Gönderilmedi</div></a>';
                }
            })
            ->addColumn('actions', function($row){
                $deleteUrl = route('super.admin.invoices.delete', $row->id);
                $earsivButton = '<a href="'.asset($row->faturaPdf).'" target="_blank" class="btn btn-outline-primary btn-sm mobilBtn mbuton1" title="Faturayı görüntüle"><i class="far fa-eye"></i></a>';
                $editButton = '<a href="javascript:void(0);" data-bs-id="'.$row->id.'" class="btn btn-outline-warning btn-sm editInvoice mobilBtn mbuton1" data-bs-toggle="modal" data-bs-target="#editInvoiceModal" title="Düzenle"><i class="fas fa-edit"></i></a>';
                $deleteButton = '<a href="'.$deleteUrl.'" class="btn btn-outline-danger btn-sm mobilBtn" id="delete" title="Sil"><i class="fas fa-trash-alt"></i></a>';
                return $earsivButton. '  '.$editButton. '  '.$deleteButton;
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function($w) use($request){
                       $search = $request->get('search');
                       $w->where('id', 'LIKE', "%$search%")
                       ->orWhere('faturaNumarasi', 'LIKE', "%$search%")
                       ->orWhereHas('tenant', function($q) use($search) {
                            $q->where('firma_adi', 'LIKE', "%$search%");
                        });
                   });
                }
            })
            ->rawColumns(['id','faturaTarihi','faturaNumarasi','firma_id','genelToplam','odemeDurum','actions'])
            ->make(true);
        }

        return view('frontend.secure.super_admin.invoices.all_invoices',compact('tenants','invoices'));
    }

    public function GetInvoices(Request $request)
    {  
        $data = SuperAdminInvoice::query();

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $start = Carbon::parse($request->input('from_date'))->startOfDay();
            $end = Carbon::parse($request->input('to_date'))->endOfDay();
            $data->whereBetween('faturaTarihi', [$start, $end]);
        }

        if($request->filled('firma')){
            $data->where('firma_id', $request->input('firma'));
        }
        
        $filteredData = $data->where('durum','1')->get();
        
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

    // Geliştirilmiş AddInvoice method'u - tamamlanmış ödemeleri getir
    public function AddInvoice() {
        $tenants = Tenant::where('status', 1)->orderBy('firma_adi', 'ASC')->get();
        $payment_methods = PaymentMethod::whereNull('firma_id')->orderBy('id', 'asc')->get();
        
        return view('frontend.secure.super_admin.invoices.add_invoices',compact('tenants','payment_methods'));
    }

    // Yeni method: Belirli bir firmaya ait tamamlanmış ödemeleri getir
   public function GetCompletedPayments(Request $request)
{
    $tenantId = $request->get('tenant_id');
    
    if (!$tenantId) {
        return response()->json([]);
    }

    // Abonelik ödemeleri - fatura oluşturulmamış olanlar
    $subscriptionPayments = SubscriptionPayment::where('tenant_id', $tenantId)
        ->where('status', 'completed')
        ->whereNull('invoice_path')
        ->select('id', 'subscription_id', 'payment_id', 'amount', 'currency', 'payment_method', 'paid_at', 'created_at')
        ->get()
        ->map(function($payment) {
            return [
                'id' => $payment->id,
                'type' => 'subscription',
                'description' => 'Abonelik Ödemesi - ID: ' . $payment->subscription_id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'payment_method' => $payment->payment_method,
                'paid_at' => $payment->paid_at,
                'created_at' => $payment->created_at,
            ];
        });

    // Ek depolama ödemeleri - fatura oluşturulmamış olanlar
    $storagePayments = StoragePurchase::where('tenant_id', $tenantId)
        ->where('status', 'completed')
        ->whereNull('invoice_path')
        ->select('id', 'storage_package_id', 'payment_token', 'amount', 'storage_gb', 'purchased_at', 'expires_at')
        ->get()
        ->map(function($payment) {
            return [
                'id' => $payment->id,
                'type' => 'storage',
                'description' => 'Ek Depolama - ' . $payment->storage_gb . ' GB',
                'amount' => $payment->amount,
                'currency' => 'TL',
                'payment_method' => 'Kredi Kartı',
                'paid_at' => $payment->purchased_at,
                'created_at' => $payment->purchased_at,
            ];
        });

    // Entegrasyon ödemeleri - YENİ EKLENEN
    $integrationPayments = \App\Models\IntegrationPurchase::where('tenant_id', $tenantId)
        ->where('status', 'completed')
        ->whereNull('invoice_path')
        ->with('integration')
        ->select('id', 'integration_id', 'amount', 'activated_at', 'payment_response')
        ->get()
        ->map(function($payment) {
            $paymentResponse = is_string($payment->payment_response) 
                ? json_decode($payment->payment_response, true) 
                : $payment->payment_response;
            
            $integrationName = $payment->integration ? $payment->integration->name : 'Bilinmeyen Entegrasyon';
            
            return [
                'id' => $payment->id,
                'type' => 'integration',
                'description' => 'Entegrasyon - ' . $integrationName,
                'amount' => $payment->amount,
                'currency' => $paymentResponse['currency'] ?? 'TL',
                'payment_method' => $paymentResponse['payment_method'] ?? 'Kredi Kartı',
                'paid_at' => $payment->purchased_at,
                'created_at' => $payment->purchased_at,
            ];
        });

    // Üç koleksiyonu birleştir ve sırala
    $allPayments = $subscriptionPayments
        ->concat($storagePayments)
        ->concat($integrationPayments)
        ->sortByDesc('paid_at')
        ->values();

    return response()->json($allPayments);
}

public function StoreInvoice(Request $request){
    $token = $request->input('form_token');
    if (empty($token)) {
        $notification = array(
            'message' => 'Geçersiz form token! Lütfen sayfayı yenileyin.',
            'alert-type' => 'error'
        );
        return redirect()->back()->with($notification);
    }
    
    $cacheKey = 'invoice_form_token_' . $token;
    
    if (Cache::has($cacheKey)) {
        $notification = array(
            'message' => 'Bu form zaten gönderildi! Lütfen bekleyin.',
            'alert-type' => 'warning'
        );
        return redirect()->back()->with($notification);
    }
    
    Cache::put($cacheKey, true, now()->addMinutes(10));
    $validateData = $request->validate([
        'document'=> 'max:2000',
        // Çoklu ödeme için validation'ları kaldır
    ]);
    
    $document = $request->file('document');
    $extension = $request->file('document')->extension();
    if($extension != "jpg" && $extension != "png" && $extension != "jpeg" && $extension != "pdf"){
        $notification = array(
            'message' => ' Dosya uzantısı sadece jpg,png,jpeg veya pdf olmalı',
            'alert-type' => 'warning'
        );
        return redirect()->back()->with($notification);
    }
    $fileName = time().'.'.$document->getClientOriginalExtension();  
    $save_url = $document->move('upload/uploads', $fileName);

    $createdAt = Carbon::parse($request->faturaTarihi . ' ' . now()->format('H:i:s'));
    
    // Sayısal değerleri doğru şekilde dönüştür
    $toplam = $this->convertToDecimal($request->toplam);
    $indirim = $this->convertToDecimal($request->indirim);
    $kdv = $this->convertToDecimal($request->kdv);
    $genelToplam = $this->convertToDecimal($request->genelToplam);

    // Çoklu ödeme bilgilerini JSON olarak hazırla
    $paymentDetails = [];
    if ($request->has('payment_id') && is_array($request->payment_id)) {
        foreach ($request->payment_id as $index => $paymentId) {
            $paymentDetails[] = [
                'payment_id' => $paymentId,
                'payment_type' => $request->payment_type[$index],
                'description' => $request->aciklama[$index] ?? '',
                'amount' => $this->convertToDecimal($request->fiyat[$index] ?? 0)
            ];
        }
    }
    
    DB::beginTransaction();
    
    try {
        $invoice = SuperAdminInvoice::create([
            'firma_id' => $request->firma_id,
            'faturaNumarasi' => $request->faturaNumarasi,
            'faturaTarihi' => $createdAt,
            'odemeSekli' => $request->odemeSekli,
            'toplam' => $toplam,
            'indirim' => $indirim,
            'kdv' => $kdv,
            'kdvTutar' => $request->kdvTutar,
            'genelToplam' => $genelToplam,
            'toplamYazi' => $request->toplamYazi,
            'kayitAlan' => auth()->user()->id,
            'faturaPdf' => $save_url,
            'payment_details' => json_encode($paymentDetails), // Çoklu ödeme detayları JSON olarak kaydet
        ]);

        $invoice_id = $invoice->id;
        
        if($invoice){
            // Ürünleri kaydet
            $aciklama = $request->aciklama;
            $miktar = $request->miktar;
            $fiyat = $request->fiyat;
            $tutar = $request->tutar;

            foreach($aciklama as $key => $val){
                if(!empty($val)){
                    SuperAdminInvoiceProduct::insert([
                        'faturaid' => $invoice_id,
                        'aciklama' => $val,
                        'miktar' => $miktar[$key],
                        'fiyat' => $this->convertToDecimal($fiyat[$key]),
                        'tutar' => $this->convertToDecimal($tutar[$key]),
                    ]);
                }
            }

            // Çoklu ödeme kayıtlarını güncelle
            foreach ($paymentDetails as $paymentDetail) {
                $this->updatePaymentInvoicePath(
                    $paymentDetail['payment_type'], 
                    $paymentDetail['payment_id'], 
                    $save_url
                );
            }
            
            DB::commit();
            
            $notification = array(
                'message' => 'Fatura Başarıyla Eklendi ve Ödeme Kayıtları Güncellendi',
                'alert-type' => 'success'
            );
        
            return redirect()->back()->with($notification);
        }
    } catch (\Exception $e) {
        DB::rollback();
        
        $notification = array(
            'message' => 'Fatura Eklenemedi: ' . $e->getMessage(),
            'alert-type' => 'error'
        );
    
        return redirect()->back()->with($notification);
    }
}
    // Ödeme tablosundaki invoice_path'i güncelle
    private function updatePaymentInvoicePath($paymentType, $paymentId, $invoicePath)
    {
        if ($paymentType === 'subscription') {
            SubscriptionPayment::where('id', $paymentId)
                ->update(['invoice_path' => $invoicePath]);
        } elseif ($paymentType === 'storage') {
            StoragePurchase::where('id', $paymentId)
                ->update(['invoice_path' => $invoicePath]);
        } elseif ($paymentType === 'integration') {
            \App\Models\IntegrationPurchase::where('id', $paymentId)
                ->update(['invoice_path' => $invoicePath]);
        }
    }

    // Mevcut EditInvoice method'u korunuyor...
    public function EditInvoice($id) {
        $invoice_id = SuperAdminInvoice::findOrFail($id);
        $tenants = Tenant::where('status', 1)->orderBy('firma_adi', 'ASC')->get();
        $payment_methods = PaymentMethod::whereNull('firma_id')->orderBy('id', 'asc')->get();
        $invoice_products = SuperAdminInvoiceProduct::where('faturaid',$id)->get();
        
        return view('frontend.secure.super_admin.invoices.edit_invoices',compact('invoice_id','tenants','payment_methods','invoice_products'));
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

    // Geliştirilmiş UpdateInvoice method'u
    public function UpdateInvoice(Request $request) {
    $invoice_id = $request->id;
    $createdAt = Carbon::parse($request->faturaTarihi . ' ' . now()->format('H:i:s'));

    DB::beginTransaction();
    
    try {
        $invoice = SuperAdminInvoice::findOrFail($invoice_id);
        
        // Eski payment_details'i al
        $oldPaymentDetails = json_decode($invoice->payment_details, true) ?? [];
        
        // Çoklu ödeme bilgilerini JSON olarak hazırla
        $paymentDetails = [];
        if ($request->has('payment_id') && is_array($request->payment_id)) {
            foreach ($request->payment_id as $index => $paymentId) {
                $paymentDetails[] = [
                    'payment_id' => $paymentId,
                    'payment_type' => $request->payment_type[$index],
                    'description' => $request->aciklama[$index] ?? '',
                    'amount' => $this->convertToDecimal($request->fiyat[$index] ?? 0)
                ];
            }
        }
        
        $invoice->firma_id = $request->firma_id;
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
        $invoice->payment_details = json_encode($paymentDetails); // Çoklu ödeme detayları güncelle
        
        $invoice->save();

        // Eski ürünleri sil
        $oldProducts = SuperAdminInvoiceProduct::where('faturaid', $invoice_id)->get();
        foreach($oldProducts as $product){
            SuperAdminInvoiceProduct::findOrFail($product->id)->delete();
        }

        // Yeni ürünleri ekle
        $aciklama = $request->aciklama;
        $miktar = $request->miktar;
        $fiyat = $request->fiyat;
        $tutar = $request->tutar;

        foreach ($aciklama as $key => $val) {
            if (!empty($val)) {
                SuperAdminInvoiceProduct::create([
                    'faturaid' => $invoice_id,
                    'aciklama' => $val,
                    'miktar' => $miktar[$key],
                    'fiyat' => $this->convertToDecimal($fiyat[$key]),
                    'tutar' => $this->convertToDecimal($tutar[$key]),
                ]);
            }
        }

        // Eski ödeme kayıtlarını temizle
        foreach ($oldPaymentDetails as $oldPayment) {
            $this->clearPaymentInvoicePath($oldPayment['payment_type'], $oldPayment['payment_id']);
        }

        // Yeni ödeme kayıtlarını güncelle
        foreach ($paymentDetails as $paymentDetail) {
            $this->updatePaymentInvoicePath(
                $paymentDetail['payment_type'], 
                $paymentDetail['payment_id'], 
                $invoice->faturaPdf
            );
        }
        
        DB::commit();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Fatura Bilgileri Başarıyla Güncellendi'
        ]);
        
    } catch (\Exception $e) {
        DB::rollback();
        
        $notification = array(
            'message' => 'Fatura Güncellenemedi: ' . $e->getMessage(),
            'alert-type' => 'error'
        );
        return response()->json(['error' => $notification]);
    }
}


    // Ödeme tablosundaki invoice_path'i temizle
    private function clearPaymentInvoicePath($paymentType, $paymentId)
    {
        if ($paymentType === 'subscription') {
            SubscriptionPayment::where('id', $paymentId)
                ->update(['invoice_path' => null]);
        } elseif ($paymentType === 'storage') {
            StoragePurchase::where('id', $paymentId)
                ->update(['invoice_path' => null]);
        } elseif ($paymentType === 'integration') {
            \App\Models\IntegrationPurchase::where('id', $paymentId)
                ->update(['invoice_path' => null]);
        }
    }

    // Geliştirilmiş DeleteInvoice method'u
    public function DeleteInvoice($id) {
    DB::beginTransaction();
    
    try {
        $fatura = SuperAdminInvoice::findOrFail($id);
        
        // 1. Yeni sistemdeki payment_details'i temizle
        $paymentDetails = json_decode($fatura->payment_details, true) ?? [];
        foreach ($paymentDetails as $payment) {
            $this->clearPaymentInvoicePath($payment['payment_type'], $payment['payment_id']);
        }

        // 2. Eski sistemle uyumluluk için (tek ödeme)
        if ($fatura->payment_type && $fatura->odeme_id) {
            $this->clearPaymentInvoicePath($fatura->payment_type, $fatura->odeme_id);
        }

        // 3. Faturaya bağlı ürünleri sil
        $eskiUrunler = SuperAdminInvoiceProduct::where('faturaid', $id)->get();
        foreach ($eskiUrunler as $urun) {
            $urun->delete();
        }

        // 4. Faturayı sil (durum = 0 yerine tamamen sil)
        $fatura->delete(); // Bu satır önemli - update yerine delete kullan

        DB::commit();

        $notification = [
            'message' => 'Fatura ve İlgili Ödeme Kayıtları Başarıyla Silindi',
            'alert-type' => 'success'
        ];

        return redirect()->route('super.admin.invoices')->with($notification);
        
    } catch (\Exception $e) {
        DB::rollback();
        
        $notification = [
            'message' => 'Fatura Silinemedi: ' . $e->getMessage(),
            'alert-type' => 'error'
        ];

        return redirect()->route('super.admin.invoices')->with($notification);
    }
}


    // Diğer mevcut method'lar korunuyor...
    public function ShowInvoice($id) {
        $invoice_id = SuperAdminInvoice::findOrFail($id);
        return view('frontend.secure.super_admin.invoices.show_invoices',compact('invoice_id'));
    }

  public function UploadInvoice(Request $request) {
    $invoice_id = $request->id;
    
    $document = $request->file('pdf');
    if($document) {
        $extension = $document->getClientOriginalExtension();
        if(!in_array(strtolower($extension), ['jpg', 'png', 'jpeg', 'pdf'])){
            return response()->json([
                'status' => 'error',
                'message' => 'Dosya uzantısı sadece jpg, png, jpeg veya pdf olmalı'
            ]);
        }
        
        // Dosya boyutu kontrolü (2MB = 2048KB)
        if ($document->getSize() > 2048 * 1024) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dosya boyutu 2MB\'dan küçük olmalıdır'
            ]);
        }
        
        $fileName = time().'.'.$extension;  
        $save_url = $document->move('upload/uploads', $fileName);
        
        DB::beginTransaction();
        
        try {
            $invoice = SuperAdminInvoice::find($invoice_id);
            $invoice->faturaPdf = $save_url;
            $invoice->save();
            
            // Eski sistem uyumluluğu için (tek ödeme)
            if ($invoice->payment_type && $invoice->odeme_id) {
                $this->updatePaymentInvoicePath($invoice->payment_type, $invoice->odeme_id, $save_url);
            }
            
            // Çoklu ödeme sistemi için güncelleme
            if ($invoice->payment_details) {
                $paymentDetails = json_decode($invoice->payment_details, true) ?? [];
                foreach ($paymentDetails as $payment) {
                    $this->updatePaymentInvoicePath(
                        $payment['payment_type'], 
                        $payment['payment_id'], 
                        $save_url
                    );
                }
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'E-arşiv belgesi başarıyla yüklendi ve ödeme kayıtları güncellendi',
                'invoice_id' => $invoice_id,
                'file_url' => asset($save_url)
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Dosya yüklenemedi: ' . $e->getMessage()
            ]);
        }
    }
      
    return response()->json([
        'status' => 'error',
        'message' => 'PDF dosyası bulunamadı.'
    ]);
}
    public function DeleteEinvoice($id){
    DB::beginTransaction();
    
    try {
        $invoice = SuperAdminInvoice::findOrFail($id);
        
        // Eski sistem uyumluluğu için (tek ödeme)
        if ($invoice->payment_type && $invoice->odeme_id) {
            $this->clearPaymentInvoicePath($invoice->payment_type, $invoice->odeme_id);
        }
        
        // YENİ: Çoklu ödeme sistemi için temizleme
        if ($invoice->payment_details) {
            $paymentDetails = json_decode($invoice->payment_details, true) ?? [];
            foreach ($paymentDetails as $payment) {
                $this->clearPaymentInvoicePath(
                    $payment['payment_type'], 
                    $payment['payment_id']
                );
            }
        }
        
        $invoice->update(['faturaPdf' => null]);
        
        DB::commit();
        
        $notification = array(
            'message' => 'Fatura başarıyla silindi ve ödeme kayıtları güncellendi',
            'alert-type' => 'success',
        );
        
    } catch (\Exception $e) {
        DB::rollback();
        
        $notification = array(
            'message' => 'İşlem başarısız: ' . $e->getMessage(),
            'alert-type' => 'error',
        );
    }
    
   return response()->json([
    'status' => 'success', 
    'message' => 'E-Arşiv belgesi başarıyla silindi'
]);
}
    // Mevcut FirmaAra method'u korunuyor...
    public function FirmaAra(Request $request) {
        $aramaMetni = $request->arama;
        
        $firmalar = Tenant::with(['ils', 'ilces'])
                         ->where('status', 1)
                         ->where('firma_adi', 'LIKE', '%' . $aramaMetni . '%')
                         ->select('id', 'firma_adi', 'tel1', 'tel2', 'il', 'ilce', 'adres', 'vergiNo', 'vergiDairesi')
                         ->orderBy('firma_adi')
                         ->limit(10)
                         ->get()
                         ->map(function($firma) {
                             return [
                                 'id' => $firma->id,
                                 'firma_adi' => $firma->firma_adi,
                                 'tel1' => $firma->tel1,
                                 'tel2' => $firma->tel2,
                                 'il' => $firma->ils?->name ?? 'Bilinmiyor',
                                 'ilce' => $firma->ilces?->ilceName ?? 'Bilinmiyor',
                                 'adres' => $firma->adres,
                                 'vergiNo' => $firma->vergiNo,
                                 'vergiDairesi' => $firma->vergiDairesi
                             ];
                         });
        
        return response()->json($firmalar);
    }
    public function GetTenantsWithPendingPayments()
    {
        // Faturası oluşturulmamış abonelik ödemesi olan firma ID'lerini al
        $subscriptionTenantIds = SubscriptionPayment::where('status', 'completed')
            ->whereNull('invoice_path')
            ->distinct()
            ->pluck('tenant_id');
        
        // Faturası oluşturulmamış depolama ödemesi olan firma ID'lerini al
        $storageTenantIds = StoragePurchase::where('status', 'completed')
            ->whereNull('invoice_path')
            ->distinct()
            ->pluck('tenant_id');
        
        // Faturası oluşturulmamış entegrasyon ödemesi olan firma ID'lerini al - YENİ
        $integrationTenantIds = \App\Models\IntegrationPurchase::where('status', 'completed')
            ->whereNull('invoice_path')
            ->distinct()
            ->pluck('tenant_id');
        
        // Üç listeyi birleştir ve unique yap
        $tenantIds = $subscriptionTenantIds
            ->merge($storageTenantIds)
            ->merge($integrationTenantIds)
            ->unique();
        
        // Firma bilgilerini getir
        $tenants = Tenant::with(['ils', 'ilces'])
            ->whereIn('id', $tenantIds)
            ->where('status', 1)
            ->where('firma_adi', '!=', 'Super Admin Panel')
            ->select('id', 'firma_adi', 'tel1', 'tel2', 'il', 'ilce', 'adres', 'vergiNo', 'vergiDairesi')
            ->orderBy('firma_adi')
            ->get()
            ->map(function($firma) {
                // Her firma için bekleyen ödeme sayısını hesapla
                $subscriptionCount = SubscriptionPayment::where('tenant_id', $firma->id)
                    ->where('status', 'completed')
                    ->whereNull('invoice_path')
                    ->count();
                
                $storageCount = StoragePurchase::where('tenant_id', $firma->id)
                    ->where('status', 'completed')
                    ->whereNull('invoice_path')
                    ->count();
                
                $integrationCount = \App\Models\IntegrationPurchase::where('tenant_id', $firma->id)
                    ->where('status', 'completed')
                    ->whereNull('invoice_path')
                    ->count();
                
                return [
                    'id' => $firma->id,
                    'firma_adi' => $firma->firma_adi,
                    'tel1' => $firma->tel1,
                    'tel2' => $firma->tel2,
                    'il' => $firma->ils?->name ?? 'Bilinmiyor',
                    'ilce' => $firma->ilces?->ilceName ?? 'Bilinmiyor',
                    'adres' => $firma->adres,
                    'vergiNo' => $firma->vergiNo,
                    'vergiDairesi' => $firma->vergiDairesi,
                    'pending_payments_count' => $subscriptionCount + $storageCount + $integrationCount
                ];
            });
        
        return response()->json($tenants);
    }
}