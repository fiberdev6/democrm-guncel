<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\StockCategory;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Svg\Gradient\Stop;
use Illuminate\Support\Facades\Cache;

class StockCategoryController extends Controller
{
    public function AllStockCategory($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı',
                'alert-type' => 'danger'
            );
            return redirect()->route('giris')->with($notification);
        }
        $categories = StockCategory::where('firma_id', $firma->id)->orderBy('id','desc')->get();
        return view('frontend.secure.stock_categories.all_stock_categories', compact('firma', 'categories'));
    }

    public function AddStockCategory($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        return view('frontend.secure.stock_categories.add_stock_category', compact('firma'));
    }

    public function StoreStockCategory($tenant_id, Request $request) {
        $token = $request->input('form_token');
        // Token boş mu kontrol et
        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz form token! Lütfen sayfayı yenileyin.'
            ], 400);
        }
        // Bu token daha önce kullanıldı mı kontrol et
        $cacheKey = 'stockcategory_form_token_' . $token;
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
        $response = StockCategory::create([
            'firma_id' => $firma->id,
            'kid' => $userId,
            'kategori' => $request->kategori,
        ]);
        $createdCategory = StockCategory::find($response->id);
        return response()->json($createdCategory);
    }

    public function EditStockCategory($tenant_id, $id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $category_id = StockCategory::findOrFail($id);
        return view('frontend.secure.stock_categories.edit_stock_category', compact('firma', 'category_id'));
    }

    public function UpdateStockCategory($tenant_id, Request $request) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $category_id = $request->id;
        StockCategory::findOrFail($category_id)->update([
            'firma_id' => $firma->id,
            'kategori' => $request->kategori,
        ]);
        $updatedCategory = StockCategory::find($category_id);
        return response()->json($updatedCategory);
    }

    public function DeleteStockCategory($tenant_id, $id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if (!$firma) {
            return redirect()->back()->with([
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger',
            ]);
        }
        $stock_categories = StockCategory::find($id);
        if($stock_categories) {
            $stock_categories->delete();
            return response()->json(['success' => true]);
        }
        else{
            return response()->json(['success' => false, 'message' => 'Stok kategorisi başarıyla silindi.']);
        }
    }
}
