<?php

namespace App\Http\Controllers\Frontend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Survey;
use App\Models\Service;
use App\Models\User;
use App\Models\Tenant;
use Carbon\Carbon; 
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 
use App\Models\ServiceStageAnswer;

class SurveyController extends Controller
{
public function SurveyCreate($tenant_id, $servisId)
{
    $firma = Tenant::findOrFail($tenant_id);
    $servis = Service::findOrFail($servisId);

    $mevcutAnket = Survey::where('servisid', $servisId)
                         ->where('firma_id', $tenant_id)
                         ->first();
    $personelYapilanServis = ServiceStageAnswer::where('servisid', $servisId)
                                            ->where('soruid', 45)
                                            ->first();
    $servisPersonelId = $personelYapilanServis ? (int)$personelYapilanServis->cevap : null;
    $bayiRole =Role::find(259);
    $bayiRoleId = $bayiRole->id;
    $anketYapilanPersonelId = $mevcutAnket?->personel; 
    $personeller = User::where('tenant_id', $tenant_id)
            ->whereDoesntHave('roles', function($query) use ($bayiRoleId) {
                $query->where('id', $bayiRoleId);
            })
            ->get(); 
    return view('frontend.secure.surveys.survey_form', [
        'servis' => $servis,
        'anket' => $mevcutAnket,
        'tenant_id' => $tenant_id,
        'personeller' => $personeller,
        'anketYapilanPersonelId' => $anketYapilanPersonelId,
        'servisPersonelId' => $servisPersonelId,
    ]);
}

public function SurveyStore(Request $request, $tenant_id, $servisId)
{
    $request->validate([
        'soru1' => 'required|in:0,1,2',
        'soru2' => 'required|in:0,1,2',
        'soru3' => 'required|in:0,1,2',
        'soru4Text' => 'nullable|string|max:255',
        'soru5' => 'required|in:0,1,2',
        'soru1Text' => 'nullable|string|max:500',
        'soru2Text' => 'nullable|string|max:500',
        'soru3Text' => 'nullable|string|max:500',
        'soru5Text' => 'nullable|string|max:500',
        'personel' => 'nullable|integer|exists:tb_user,user_id',
    ]);

    try {
        $servis = Service::findOrFail($servisId);
        $anket = Survey::where('servisid', $servisId)
                        ->where('firma_id', $tenant_id)
                        ->first();
        $isNew = false;

        if (!$anket) {
            $anket = new Survey();
            $anket->servisid = $servisId;
            $anket->firma_id = $tenant_id;
            $anket->ekleyen = Auth::id();
            $isNew = true;
        }

        // Bayi bilgisi - soruid = 3
        $bayiAnswer = ServiceStageAnswer::where('servisid', $servisId)
                            ->where('soruid', 3)
                            ->first();
        $bayiId = $bayiAnswer ? (int) $bayiAnswer->cevap : null;

        // Personel bilgisi - soruid = 45
        $personelAnswer = ServiceStageAnswer::where('servisid', $servisId)
                            ->where('soruid', 45)
                            ->first();
        $personelId = $personelAnswer ? (int) $personelAnswer->cevap : null;

        // Formdan manuel personel seçildiyse onu al
        if ($request->filled('personel')) {
            $personelId = $request->input('personel');

        }

        // Atamaları yap
        $anket->personel = $personelId ?? 0;
        $anket->bayi = $bayiId ?? null; 

        // Diğer soruları ata
        $anket->soru1 = $request->input('soru1');
        $anket->soru1Text = $request->input('soru1Text');
        $anket->soru2 = $request->input('soru2');
        $anket->soru2Text = $request->input('soru2Text');
        $anket->soru3 = $request->input('soru3');
        $anket->soru3Text = $request->input('soru3Text');
        $anket->soru4 = 0;
        $anket->soru4Text = $request->input('soru4Text');
        $anket->soru5 = $request->input('soru5');
        $anket->soru5Text = $request->input('soru5Text');

        $anket->save();

        $message = $isNew ? 'Anket başarıyla kaydedildi.' : 'Anket başarıyla güncellendi.';
        return response()->json(['success' => true, 'message' => $message], 200);

    } catch (\Exception $e) {
        Log::error('Anket kaydetme hatası: ' . $e->getMessage(), [
            'servisId' => $servisId,
            'request_data' => $request->all()
        ]);
        return response()->json(['success' => false, 'error' => 'Beklenmeyen bir hata oluştu.'], 500);
    }
}

public function SurveyReports($tenant_id) 
{
    try {
        $firma = Tenant::where('id', $tenant_id)->first();
        if (!$firma) {
            return response()->json(['error' => 'Firma bulunamadı'], 404);
        }
        //anketler
        $anketler = Survey::where('firma_id', $tenant_id)->get();
        //bayiler
        $bayiRole = Role::where('name', 'Bayi')->first();
        $bayiRoleId = $bayiRole ? $bayiRole->id : null;
        $bayiler = User::where('tenant_id', $tenant_id)
            ->whereHas('roles', function ($query) use ($bayiRoleId) {
                $query->where('id', $bayiRoleId);
            })
            ->get();
        //personeller
        $personeller = User::where('tenant_id', $tenant_id)
            ->whereDoesntHave('roles', function ($query) use ($bayiRoleId) {
                $query->where('id', $bayiRoleId);
            })
            ->get();
        return view('frontend.secure.surveys.survey_reports_modal', compact('firma', 'anketler','bayiler','personeller'));
        
    } catch (\Exception $e) {
        Log::error('Anket raporları hatası: ' . $e->getMessage(), [
            'tenant_id' => $tenant_id
        ]);
        return response()->json(['error' => 'Beklenmeyen bir hata oluştu.'], 500);
    }
}

}