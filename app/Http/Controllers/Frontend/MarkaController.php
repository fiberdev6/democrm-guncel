<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Marka;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;


class MarkaController extends Controller
{
    // Markaları listele
    public function index()
    {
        $markalar = Marka::orderBy('marka', 'ASC')->get();
        return view('frontend.secure.super_admin.ariza_kodlari.markalar.index', compact('markalar'));
    }

    // Marka ekleme formu (Modal için)
    public function create()
    {
        return view('frontend.secure.super_admin.ariza_kodlari.markalar.create');
    }

    // Marka kaydet
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
        $cacheKey = 'marka_form_token_' . $token;
        if (Cache::has($cacheKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Bu marka zaten eklendi! Lütfen bekleyin veya sayfayı yenileyin.'
            ], 429);
        }
        
        // Token'ı 10 dakika boyunca sakla
        Cache::put($cacheKey, true, now()->addMinutes(10));
        $request->validate([
            'marka' => 'required|string|max:500',
            'resim' => 'nullable|image|mimes:jpeg,jpg,png,svg|max:2048'
        ]);
        
        $data = [
            'marka' => trim($request->marka)
        ];
        
        // Resim yükleme
        if ($request->hasFile('resim')) {
            $resim = $request->file('resim');
            $resimAdi = bin2hex(random_bytes(10)) . '.' . $resim->getClientOriginalExtension();
             $resim->move(public_path('upload/ariza_kodlari'), $resimAdi);
            $data['resimyol'] = $resimAdi;
        }
        
        Marka::create($data);
        
        return response()->json(['message' => 'Marka Eklendi']);
    }
     // Marka düzenleme formu (Modal için)
    public function edit($id)
    {
        $markaSec = Marka::findOrFail($id);
        return view('frontend.secure.super_admin.ariza_kodlari.markalar.edit', compact('markaSec'));
    }

    // Marka güncelle
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'marka' => 'required|string|max:500',
                'resim' => 'nullable|image|mimes:jpeg,jpg,png,svg|max:2048'
            ]);
            
            $marka = Marka::findOrFail($id);
            
            $data = [
                'marka' => trim($request->marka)
            ];
            
            // Resim güncelleme
            if ($request->hasFile('resim')) {
                    
                    $eskiResimYolu = public_path('upload/ariza_kodlari/' . $marka->resimyol);
                    
                    if ($marka->resimyol && file_exists($eskiResimYolu)) {
                        @unlink($eskiResimYolu);
                    }
                    
                    $resim = $request->file('resim');
                    $resimAdi = bin2hex(random_bytes(10)) . '.' . $resim->getClientOriginalExtension();
                    
                    
                    $resim->move(public_path('upload/ariza_kodlari'), $resimAdi);
                    
                    $data['resimyol'] = $resimAdi;
                }
            
            $marka->update($data);
            
            return response()->json(['message' => 'Marka başarıyla güncellendi!']);
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'Güncelleme sırasında hata: ' . $e->getMessage()], 500);
        }
    }

        // Marka sil
        public function destroy($id)
        {
            $marka = Marka::findOrFail($id);
            
            $resimYolu = public_path('upload/ariza_kodlari/' . $marka->resimyol);
            
            if ($marka->resimyol && file_exists($resimYolu)) {
                @unlink($resimYolu);
            }
            
            $marka->delete();
            
            return response()->json(['message' => 'Marka Silindi']);
        }
        
    

}
