<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PaymentMethodsController extends Controller
{
    public function AllPaymentMethods($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı',
                'alert-type' => 'danger'
            );
            return redirect()->route('giris')->with($notification);
        }
        $payment_methods = PaymentMethod::where(function($query) use ($firma) {
                $query->whereNull('firma_id')
                    ->orWhere('firma_id', $firma->id);
            })
            ->orderBy('id', 'desc')
            ->get();
        return view('frontend.secure.payment_methods.all_payment_methods', compact('firma', 'payment_methods'));
    }

    public function AddPaymentMethod($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        return view('frontend.secure.payment_methods.add_payment_method', compact('firma'));
    }

    public function StorePaymentMethod($tenant_id, Request $request) {
        $token = $request->input('form_token');
        // Token boş mu kontrol et
        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz form token! Lütfen sayfayı yenileyin.'
            ], 400);
        }
        // Bu token daha önce kullanıldı mı kontrol et
        $cacheKey = 'paymentmethod_form_token_' . $token;
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
        $response = PaymentMethod::create([
            'firma_id' => $firma->id,
            'kid' => $userId,
            'odemeSekli' => $request->odemeSekli,
        ]);
        $createdMethod = PaymentMethod::find($response->id);
        return response()->json($createdMethod);
    }

    public function EditPaymentMethod($tenant_id, $id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $method_id = PaymentMethod::findOrFail($id);
        return view('frontend.secure.payment_methods.edit_payment_method', compact('firma', 'method_id'));
    }

    public function UpdatePaymentMethod($tenant_id, Request $request) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $method_id = $request->id;
         $userId = Auth::user()->user_id;
        PaymentMethod::findOrFail($method_id)->update([
            'firma_id' => $firma->id,
            'kid' => $userId,
            'odemeSekli' => $request->odemeSekli,
        ]);
        $updatedMethod = PaymentMethod::find($method_id);
        return response()->json($updatedMethod);
    }

    public function DeletePaymentMethod($tenant_id, $id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if (!$firma) {
            return redirect()->back()->with([
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger',
            ]);
        }
        $payment_methods = PaymentMethod::find($id);
        if($payment_methods) {
            $payment_methods->delete();
            return response()->json(['success' => true]);
        }
        else{
            return response()->json(['success' => false, 'message' => 'Ödeme şekli başarıyla silindi.']);
        }
    }
}
