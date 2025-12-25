<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ServiceStage;
use App\Models\StageQuestion;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Cache;


class StageQuestionController extends Controller
{
    public function AllStageQuestions($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger'
            );
            return redirect()->route('giris')->with($notification);
        }
        $stageQuestions = StageQuestion::where(function ($query) use ($tenant_id) {
            $query->whereNull('firma_id')->orWhere('firma_id', $tenant_id);
        })->orderBy('id', 'desc')->get();
        $asamalar = ServiceStage::where(function ($query) use ($tenant_id) {
            $query->whereNull('firma_id')->orWhere('firma_id', $tenant_id);
        })->orderBy('asama', 'asc')->get();
        $roles = Role::where('name','!=', 'Admin')->get();
        return view('frontend.secure.stage_questions.all_questions', compact('firma', 'stageQuestions','asamalar','roles'));
    }

    public function AddStageQuestion($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }
        $stages = ServiceStage::where(function ($query) use ($tenant_id) {
            $query->whereNull('firma_id')->orWhere('firma_id', $tenant_id);
        })->orderBy('asama', 'asc')->get();
        $stageQuestions = StageQuestion::where(function ($query) use ($tenant_id) {
            $query->whereNull('firma_id')->orWhere('firma_id', $tenant_id);
        })->orderBy('asama', 'asc')->get();

        $roles = Role::where('name','!=', 'Admin')->get();
        return view('frontend.secure.stage_questions.add_questions', compact('firma', 'stageQuestions', 'stages', 'roles'));
    }

    public function StoreStageQuestion(Request $request, $tenant_id) {
        $token = $request->input('form_token');
        // Token boş mu kontrol et
        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz form token! Lütfen sayfayı yenileyin.'
            ], 400);
        }
        // Bu token daha önce kullanıldı mı kontrol et
        $cacheKey = 'stagequestion_form_token_' . $token;
        if (Cache::has($cacheKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Bu form zaten gönderildi! Lütfen bekleyin veya sayfayı yenileyin.'
            ], 400);
        }
        // Token'ı 10 dakika boyunca sakla
        Cache::put($cacheKey, true, now()->addMinutes(10));
        $firma = Tenant::where('id', $tenant_id)->first();
        if (!$firma) {
            return redirect()->back()->with([
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger',
            ]);
        }

        // Request verilerini hazırlama
        $data = [
            'firma_id' => $firma->id,
            'asama' => $request->stage,
            'soru' => $request->soru,
            'sira' => $request->sira,
        ];
    
        // Cevap tipini kontrol etme
        if ($request->cevap == "[Personel]") {
            // Eğer Personel seçeneği seçildiyse, grup işlemlerini yap
            $grupAll = "";
            
            if ($request->has('grup')) {
                foreach ($request->grup as $grupValue) {
                    if (empty($grupAll)) {
                        $grupAll = $grupValue;
                    } else {
                        $grupAll .= ", " . $grupValue;
                    }
                }
                $data['cevapTuru'] = $grupAll;
            } else {
                // Grup seçilmediyse varsayılan değer
                $data['cevapTuru'] = "[Grup-0]";
            }
        } else {
            // Diğer cevap formatları için doğrudan değeri kullan
            $data['cevapTuru'] = $request->cevap;
        }
    
        // Veritabanına kayıt etme
        $response = StageQuestion::create($data);
    
        // Oluşturulan kaydı döndürme
        $createdQuestion= StageQuestion::find($response->id);
        return response()->json($createdQuestion);
    }

    // Controller metodu - Aşamaya göre soruları getirme
    public function getStageQuestions(Request $request, $tenant_id)
    {
        $firma = Tenant::where('id', $tenant_id)->first();
        if (!$firma) {
            return response()->json(['error' => 'Firma bulunamadı'], 404);
        }
        
        $stages = ServiceStage::where(function ($query) use ($tenant_id) {
            $query->whereNull('firma_id')->orWhere('firma_id', $tenant_id);
        })->orderBy('asama', 'asc')->get();
        $stageQuestions = StageQuestion::where(function ($query) use ($tenant_id) {
            $query->whereNull('firma_id')->orWhere('firma_id', $tenant_id);
        })->orderBy('asama', 'asc')->get();
        
        $roles = Role::where('name','!=', 'Admin')->get();
        
        // Accordion partial view'i render et
        $view = view('frontend.secure.stage_questions.stage_questions_table', 
                    compact('stages', 'stageQuestions', 'roles', 'firma'))
                    ->render();
        
        return $view;
    }

    public function EditStageQuestion($tenant_id, $id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }
        $question_id = StageQuestion::findOrFail($id);
        $stages = ServiceStage::where(function ($query) use ($tenant_id) {
            $query->whereNull('firma_id')->orWhere('firma_id', $tenant_id);
        })->orderBy('asama', 'asc')->get();
        $stageQuestions = StageQuestion::where(function ($query) use ($tenant_id) {
            $query->whereNull('firma_id')->orWhere('firma_id', $tenant_id);
        })->orderBy('asama', 'asc')->get();

        $roles = Role::where('name','!=', 'Admin')->get();

        return view('frontend.secure.stage_questions.edit_questions', compact('firma', 'question_id','stages', 'roles'));
    }

    public function UpdateStageQuestion(Request $request, $tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if (!$firma) {
            return redirect()->back()->with([
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger',
            ]);
        }

        $question_id = $request->id;
        $question = StageQuestion::findOrFail($question_id);
        $question->asama = $request->stage;
        $question->soru = $request->soru;
        $question->sira = $request->sira;

        // Cevap tipini kontrol etme
        if ($request->cevap == "[Personel]") {
            // Eğer Personel seçeneği seçildiyse, grup işlemlerini yap
            $grupAll = "";
            
            if ($request->has('grup')) {
                foreach ($request->grup as $grupValue) {
                    if (empty($grupAll)) {
                        $grupAll = $grupValue;
                    } else {
                        $grupAll .= ", " . $grupValue;
                    }
                }
                $question['cevapTuru'] = $grupAll;
            } else {
                // Grup seçilmediyse varsayılan değer
                $question['cevapTuru'] = "[Grup-0]";
            }
        } else {
            // Diğer cevap formatları için doğrudan değeri kullan
            $question['cevapTuru'] = $request->cevap;
        }

        $question->save();

        $updatedStages = StageQuestion::find($question_id);
        return response()->json($updatedStages);
    }

    public function DeleteStageQuestion($tenant_id, $id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if (!$firma) {
            return redirect()->back()->with([
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger',
            ]);
        }
        $stage_questions = StageQuestion::find($id);
        if($stage_questions) {
            $stage_questions->delete();
            return response()->json(['success' => true]);
        }
        else{
            return response()->json(['success' => false, 'message' => 'Servis aşama sorusu başarıyla silindi.']);
        }
    }
}
