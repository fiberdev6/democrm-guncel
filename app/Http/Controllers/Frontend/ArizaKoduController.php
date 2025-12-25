<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ArizaKodu;
use App\Models\Marka;
use App\Models\Modell;
use Illuminate\Support\Facades\Cache;

class ArizaKoduController extends Controller
{

// Arıza kodlarını listele
public function index(Request $request)
{
    $marka_id = $request->get('marka_id');
    $model_id = $request->get('model_id', 0); 
    if ($model_id && $model_id > 0) {
        // Model bazlı kodlar
        $kodlar = ArizaKodu::where('model_id', $model_id)
            ->orderByRaw('LENGTH(kodu), kodu')  // NATURAL SORT
            ->get();
        $titleSec = Modell::findOrFail($model_id);
        $markaSec = Marka::findOrFail($titleSec->mid);
        $titleSec = $markaSec->marka . " - " . $titleSec->model;
    } elseif ($marka_id && $marka_id > 0) {
        // Marka bazlı kodlar
        $kodlar = ArizaKodu::where('marka_id', $marka_id)
            ->orderByRaw('LENGTH(kodu), kodu')  // NATURAL SORT
            ->get();
        $titleSec = Marka::findOrFail($marka_id);
        $titleSec = $titleSec->marka;
    } else {
        abort(404);
    }
    
   return view('frontend.secure.super_admin.ariza_kodlari.kodlar.index', compact('kodlar', 'titleSec', 'marka_id', 'model_id'));
}
    // Kod ekleme formu (Modal için)
    public function create(Request $request)
    {
        $marka_id = $request->get('marka_id');
        $model_id = $request->get('model_id', 0); 

        
        return view('frontend.secure.super_admin.ariza_kodlari.kodlar.create', compact('marka_id', 'model_id'));

    }
    // Kod kaydet
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
        $cacheKey = 'kod_form_token_' . $token;
        if (Cache::has($cacheKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Bu arıza kodu zaten eklendi! Lütfen bekleyin veya sayfayı yenileyin.'
            ], 429);
        }
        
        // Token'ı 10 dakika boyunca sakla
        Cache::put($cacheKey, true, now()->addMinutes(10));
        $request->validate([
            'marka_id' => 'required|exists:markalar,id',
            'model_id' => 'required|integer',
            'kod' => 'required|string|max:500',
            'baslik' => 'nullable|string|max:500',
            'aciklama' => 'required|string'
        ]);
        
        $data = [
            'marka_id' => $request->marka_id,
            'model_id' => $request->model_id,
            'kodu' => trim($request->kod),
            'baslik' => trim($request->baslik),
            'aciklama' => trim($request->aciklama),
            'durum' => '1'
        ];
        
        ArizaKodu::create($data);
        
        return response()->json(['message' => 'Hata Kodu Eklendi']);
    }
     // Kod düzenleme formu (Modal için)
    public function edit($id)
    {
        $kodSec = ArizaKodu::findOrFail($id);
        return view('frontend.secure.super_admin.ariza_kodlari.kodlar.edit', compact('kodSec'));
    }
    // Kod güncelle
    public function update(Request $request, $id)
    {
        $request->validate([
            'kod' => 'required|string|max:500',
            'baslik' => 'nullable|string|max:500',
            'aciklama' => 'required|string'
        ]);
        
        $arizaKodu = ArizaKodu::findOrFail($id);
        
        $data = [
            'kodu' => trim($request->kod),
            'baslik' => trim($request->baslik),
            'aciklama' => trim($request->aciklama)
        ];
        
        $arizaKodu->update($data);
        
        return response()->json(['message' => 'Hata Kodu Güncellendi']);
    }
    // Kod sil
    public function destroy($id)
    {
        $arizaKodu = ArizaKodu::findOrFail($id);
        $arizaKodu->delete();
        
        return response()->json(['message' => 'Hata Kodu Silindi']);
    }
     // API: Kod arama (Servis kaydı için)
    public function search(Request $request)
    {
        $kodu = $request->get('kodu');
        $marka_id = $request->get('marka_id');
        $model_id = $request->get('model_id');
        
        $query = ArizaKodu::where('durum', '1');
        
        if ($kodu) {
            $query->where('kodu', $kodu);
        }
        
        if ($marka_id) {
            $query->where('marka_id', $marka_id);
        }
        
        if ($model_id) {
            $query->where(function($q) use ($model_id) {
                $q->where('model_id', $model_id)
                  ->orWhereNull('model_id')
                  ->orWhere('model_id', 0);
            });
        }
        
        $arizaKodu = $query->first();
        
        if ($arizaKodu) {
            return response()->json([
                'success' => true,
                'data' => $arizaKodu
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Arıza kodu bulunamadı'
        ]);
    }
}
