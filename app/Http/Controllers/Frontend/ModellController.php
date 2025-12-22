<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Modell;
use App\Models\Marka;
use Illuminate\Support\Facades\Cache;
class ModellController extends Controller
{
    // Modelleri listele
    public function index($marka_id)
    {
        $modeller = Modell::where('mid', $marka_id)
                      ->orderBy('model', 'ASC') 
                      ->get();
        $markaSec = Marka::findOrFail($marka_id);
        return view('frontend.secure.super_admin.ariza_kodlari.modeller.index', compact('modeller', 'markaSec'));
    }

    // Model ekleme formu
    public function create($marka_id)
    {
        $markaSec = Marka::findOrFail($marka_id);
        return view('frontend.secure.super_admin.ariza_kodlari.modeller.create', compact('markaSec', 'marka_id'));
    }

     // Model kaydet
    public function store(Request $request)
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
        $cacheKey = 'model_form_token_' . $token;
        if (Cache::has($cacheKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Bu marka zaten eklendi! Lütfen bekleyin veya sayfayı yenileyin.'
            ], 429);
        }
        
        // Token'ı 10 dakika boyunca sakla
        Cache::put($cacheKey, true, now()->addMinutes(10));

        $request->validate([
            'mid' => 'required|exists:markalar,id',
            'model' => 'required|string|max:500',
            'resim' => 'nullable|image|mimes:jpeg,jpg,png,svg|max:2048'
        ]);
        
        $data = [
            'mid' => $request->mid,
            'model' => trim($request->model)
        ];
        
        // Resim yükleme
        if ($request->hasFile('resim')) {
            $resim = $request->file('resim');
            $resimAdi = bin2hex(random_bytes(10)) . '.' . $resim->getClientOriginalExtension();
            $resim->move(public_path('upload/ariza_kodlari'), $resimAdi);
            $data['resimyol'] = $resimAdi;
        }
        
        Modell::create($data);
        
        return response()->json(['message' => 'Model Eklendi']);
    }
        // Model düzenleme formu
        public function edit($id)
        {
            $modelSec = Modell::findOrFail($id);
            return view('frontend.secure.super_admin.ariza_kodlari.modeller.edit', compact('modelSec'));
        }

        // Model güncelle
        public function update(Request $request, $id)
        {
            $request->validate([
                'model' => 'required|string|max:500',
                'resim' => 'nullable|image|mimes:jpeg,jpg,png,svg|max:2048'
            ]);
            
            $model = Modell::findOrFail($id);
            
            $data = [
                'model' => trim($request->model)
            ];
            
        // Resim güncelleme
            if ($request->hasFile('resim')) {
                    $eskiResimYolu = public_path('upload/ariza_kodlari/' . $model->resimyol);
                    
                    if ($model->resimyol && file_exists($eskiResimYolu)) {
                        @unlink($eskiResimYolu);
                    }
                    
                    $resim = $request->file('resim');
                    $resimAdi = bin2hex(random_bytes(10)) . '.' . $resim->getClientOriginalExtension();
 
                    $resim->move(public_path('upload/ariza_kodlari'), $resimAdi);
                    
                    $data['resimyol'] = $resimAdi;
                }
            
            $model->update($data);
            
            return response()->json(['message' => 'Model Güncellendi']);
        }
        // Model sil
        public function destroy($id)
        {
            $model = Modell::findOrFail($id);

            $resimYolu = public_path('upload/ariza_kodlari/' . $model->resimyol);
            
            if ($model->resimyol && file_exists($resimYolu)) {
                @unlink($resimYolu);
            }
            
            $model->delete();
            
            return response()->json(['message' => 'Model Silindi']);
        }
    
}
