<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PaymentType;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PaymentTypesController extends Controller
{
    public function AllPaymentTypes($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı',
                'alert-type' => 'danger'
            );
            return redirect()->route('giris')->with($notification);
        }
        $payment_types = PaymentType::where(function($query) use ($firma) {
            $query->whereNull('firma_id')
                ->orWhere('firma_id', $firma->id);
        })
        ->orderBy('id', 'desc')
        ->get();
        return view('frontend.secure.payment_types.all_payment_types', compact('firma', 'payment_types'));
    }

    public function AddPaymentType($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        return view('frontend.secure.payment_types.add_payment_type', compact('firma'));
    }

    public function StorePaymentType($tenant_id, Request $request) {
        $token = $request->input('form_token');
        // Token boş mu kontrol et
        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz form token! Lütfen sayfayı yenileyin.'
            ], 400);
        }
        // Bu token daha önce kullanıldı mı kontrol et
        $cacheKey = 'paymenttype_form_token_' . $token;
        
        if (Cache::has($cacheKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Bu form zaten gönderildi! Lütfen bekleyin veya sayfayı yenileyin.'
            ], 400);
        }
        // Token'ı 10 dakika boyunca sakla
        Cache::put($cacheKey, true, now()->addMinutes(10));
        $firma = Tenant::where('id', $tenant_id)->first();
        $userId = Auth::user()->user_id;
        // Checkbox'lar boş gelirse 0 kabul et
        $stokSor = $request->has('stokSor') ? 1 : 0;
        $servisSor = $request->has('servisSor') ? 1 : 0;
        $parcaSor = $request->has('parcaSor') ? 1 : 0;
        $personelSor = $request->has('personelSor') ? 1 : 0;

        // cevaplar dizisini virgülle birleştir
        $cevaplar = $request->has('cevaplar') 
            ? implode(", ", array_map('htmlspecialchars', $request->cevaplar)) 
            : null;

        $response = PaymentType::create([
            'firma_id'     => $firma->id,
            'kid'          => $userId,
            'odemeTuru'   => htmlspecialchars(trim($request->odemeTuru)),
            'stok'         => $stokSor,
            'servis'       => $servisSor,
            'parca'        => $parcaSor,
            'personel'     => $personelSor,
            'cevaplar'     => $cevaplar,
        ]);

        $createdType = PaymentType::find($response->id);
        return response()->json($createdType);
    }

    public function EditPaymentType($tenant_id, $id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $type_id = PaymentType::findOrFail($id);
        return view('frontend.secure.payment_types.edit_payment_type', compact('firma', 'type_id'));
    }

    public function UpdatePaymentType($tenant_id, Request $request) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $type_id = $request->id;
        $userId = Auth::user()->user_id;
        // Checkbox verileri: yoksa 0 kabul et
        $stok = $request->has('stokSor') ? '1' : '0';
        $servis = $request->has('servisSor') ? '1' : '0';
        $parca = $request->has('parcaSor') ? '1' : '0';
        $personel = $request->has('personelSor') ? '1' : '0';

        // Çoklu checkbox (cevaplar[]) varsa al, yoksa boş dizi
        $cevaplarArray = $request->input('cevaplar', []);
        $cevaplarImploded = implode(',', $cevaplarArray);

        PaymentType::findOrFail($type_id)->update([
            'firma_id'   => $firma->id,
            'kid'        => $userId,
            'odemeTuru'  => $request->odemeTuru,
            'stok'       => $stok,
            'servis'     => $servis,
            'parca'      => $parca,
            'personel'   => $personel,
            'cevaplar'   => $cevaplarImploded,
        ]);
        $updatedType = PaymentType::find($type_id);
        return response()->json($updatedType);
    }

    public function DeletePaymentType($tenant_id, $id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if (!$firma) {
            return redirect()->back()->with([
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger',
            ]);
        }
        $payment_types = PaymentType::find($id);
        if($payment_types) {
            $payment_types->delete();
            return response()->json(['success' => true]);
        }
        else{
            return response()->json(['success' => false, 'message' => 'Ödeme türü başarıyla silindi.']);
        }
    }
}
