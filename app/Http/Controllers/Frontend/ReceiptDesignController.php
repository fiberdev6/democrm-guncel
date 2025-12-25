<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ReceiptDesign;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceiptDesignController extends Controller
{
    public function ReceiptDesign($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı',
                'alert-type' => 'danger'
            );
            return redirect()->route('giris')->with($notification);
        }
        $receiptDesign = ReceiptDesign::where('firma_id', $firma->id)->first();
        return view('frontend.secure.receipt_design.receipt_design', compact('firma','receiptDesign'));
    }

    public function UpdateReceiptDesign(Request $request,$tenant_id) {
        
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı',
                'alert-type' => 'danger'
            );
            return redirect()->route('giris')->with($notification);
        }
        $receipt_design_id = $request->id;

        if ($receipt_design_id) {
            // Güncelleme
            $ayar = ReceiptDesign::find($receipt_design_id);
            if (!$ayar) {
                return response()->json(['error' => 'Kayıt bulunamadı'], 404);
            }

            $ayar->update([
                'firma_id' => $firma->id,
                'kid' => Auth::user()->user_id,
                'fisTasarimi' => $request->mesaj,
                'boyut' => $request->yaziciBoyut,
            ]);
        } else {
            // İlk kez kayıt
            ReceiptDesign::create([
                'firma_id' => $firma->id,
                'kid' => Auth::user()->user_id,
                'fisTasarimi' => $request->mesaj,
                'boyut' => $request->yaziciBoyut,
            ]);
        }

        return response()->json(['success', 'Yazici fiş ayarları bilgileri güncellendi.']);
    }
}
