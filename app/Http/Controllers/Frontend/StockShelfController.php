<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\StockShelf;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class StockShelfController extends Controller
{
    public function AllStockShelf($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı',
                'alert-type' => 'danger'
            );
            return redirect()->route('giris')->with($notification);
        }
        $shelves = StockShelf::where('firma_id', $firma->id)->orderBy('id','desc')->get();
        return view('frontend.secure.stock_shelves.all_shelves', compact('firma', 'shelves'));
    }

    public function AddStockShelf($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        return view('frontend.secure.stock_shelves.add_shelf', compact('firma'));
    }

    public function StoreStockShelf($tenant_id, Request $request) {
        $token = $request->input('form_token');
        // Token boş mu kontrol et
        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz form token! Lütfen sayfayı yenileyin.'
            ], 400);
        }
        // Bu token daha önce kullanıldı mı kontrol et
        $cacheKey = 'stockshelf_form_token_' . $token;
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
        $response = StockShelf::create([
            'firma_id' => $firma->id,
            'kid' => $userId,
            'raf_adi' => $request->raf_adi,
        ]);
        $createdShelf = StockShelf::find($response->id);
        return response()->json($createdShelf);
    }

    public function EditStockShelf($tenant_id, $id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $shelf_id = StockShelf::findOrFail($id);
        return view('frontend.secure.stock_shelves.edit_shelf', compact('firma', 'shelf_id'));
    }

    public function UpdateStockShelf($tenant_id, Request $request) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $shelf_id = $request->id;
         $userId = Auth::user()->user_id;
        StockShelf::findOrFail($shelf_id)->update([
            'firma_id' => $firma->id,
            'kid' => $userId,
            'raf_adi' => $request->raf_adi,
        ]);
        $updatedShelf = StockShelf::find($shelf_id);
        return response()->json($updatedShelf);
    }

    public function DeleteStockShelf($tenant_id, $id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if (!$firma) {
            return redirect()->back()->with([
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger',
            ]);
        }
        $stock_shelves = StockShelf::find($id);
        if($stock_shelves) {
            $stock_shelves->delete();
            return response()->json(['success' => true]);
        }
        else{
            return response()->json(['success' => false, 'message' => 'Stok kategorisi başarıyla silindi.']);
        }
    }
}
