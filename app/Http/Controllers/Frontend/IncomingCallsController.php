<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\DeviceBrand;
use App\Models\IncomingCall;
use App\Models\Service;
use App\Models\ServiceResource;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Cache;

class IncomingCallsController extends Controller
{
    public function gelenCagrilarDatatable(Request $request, $tenant_id)
    {
       $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            return response()->json(['error' => 'Firma bulunamadı'], 404);
        }

        if ($request->ajax()) {
            // incoming_calls tablosundan verileri çek
            $data = DB::table('incoming_calls')
                ->leftJoin('tb_user', 'incoming_calls.kayitAlan', '=', 'tb_user.user_id')
                ->leftJoin('device_brands', 'incoming_calls.marka', '=', 'device_brands.id')
                ->leftJoin('service_resources', 'incoming_calls.servisKaynak', '=', 'service_resources.id')
                ->select(
                    'incoming_calls.*',
                    'tb_user.name as personel_name',
                    'device_brands.marka as marka_name',
                    'service_resources.kaynak as kaynak_name'
                )
                ->where('incoming_calls.firma_id', $firma->id);

            // Filtreleri uygula
            if ($request->filled('personel') && $request->personel != '0') {
                $data->where('incoming_calls.kayitAlan', $request->personel);
            }

            if ($request->filled('marka') && $request->marka != '0') {
                $data->where('incoming_calls.marka', $request->marka);
            }

            if ($request->filled('kaynak') && $request->kaynak != '0') {
                $data->where('incoming_calls.servisKaynak', $request->kaynak);
            }

            if ($request->filled('tarih1') && $request->filled('tarih2')) {
                $tarih1 = Carbon::parse($request->tarih1)->startOfDay();
                $tarih2 = Carbon::parse($request->tarih2)->endOfDay();
                $data->whereBetween('incoming_calls.created_at', [$tarih1, $tarih2]);
            }

            // Sıralama
            if ($request->has('order')) {
                $order = $request->get('order')[0];
                $columns = $request->get('columns');
                $orderColumn = $columns[$order['column']]['data'];
                $orderDir = $order['dir'];
                
                // Kolon isimlerini tablo prefix'i ile eşleştir
                $columnMap = [
                    'id' => 'incoming_calls.id',
                    'created_at' => 'incoming_calls.created_at',
                    'personel' => 'tb_user.name',
                    'marka' => 'device_brands.marka',
                    'kaynak' => 'service_resources.kaynak'
                ];
                
                $orderColumn = $columnMap[$orderColumn] ?? 'incoming_calls.id';
                $data->orderBy($orderColumn, $orderDir);
            } else {
                $data->orderBy('incoming_calls.id', 'desc');
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('id', function($row) {
                    return '<a class="t-link editIncomingCall address idWrap" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editIncomingCallModal"><span class="mobileTitle">Id:</span>'.$row->id.'</a>';
                })
                ->addColumn('created_at', function($row) {
                    $sontarih = Carbon::parse($row->created_at)->format('d/m/Y H:i');
                    return '<a class="t-link editIncomingCall address idWrap" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editIncomingCallModal"><span class="mobileTitle">Tarih:</span>'.$sontarih.'</a>';
                })
                ->addColumn('telefon', function($row) {
                   
                    return '<a class="t-link editIncomingCall address" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editIncomingCallModal"><span class="mobileTitle">Tarih:</span>'.$row->kaynak_name.'</a>';

                })
                ->addColumn('marka', function($row) {
                    
                    return '<a class="t-link editIncomingCall address" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editIncomingCallModal"><span class="mobileTitle">Marka:</span>'.$row->marka_name.'</a>';

                })
                ->addColumn('aciklama', function($row) {
                    
                    return '<a class="t-link editIncomingCall address" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editIncomingCallModal"><span class="mobileTitle">Açıklama:</span>'.$row->ariza.'</a>';

                })
                ->addColumn('personel', function($row) {
                    return '<a class="t-link editIncomingCall address" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editIncomingCallModal"><span class="mobileTitle">Personel:</span>'.$row->personel_name.'</a>';

                })
                ->addColumn('action', function($row) {
                    $buttons = '';

                    // Görüntüleme butonu
                    $buttons .= '<a href="javascript:void(0);" class="btn btn-outline-primary btn-sm editIncomingCall mobilBtn mbuton1" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editIncomingCallModal" title="Görüntüle"><i class="far fa-eye"></i> <span> Görüntüle</span></a>';
                    
                    // Detay butonu
                    $buttons .= '<a href="javascript:void(0);" class="btn btn-outline-warning btn-sm editIncomingCall mobilBtn mbuton1" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editIncomingCallModal" title="Düzenle"><i class="fas fa-edit"></i> <span> Düzenle</span></a>';
                    
                    // Düzenleme butonu (eğer gerekirse)
                    $buttons .= '<a href="javascript:void(0);"  class="btn btn-outline-danger btn-sm mobilBtn deleteIncomingCall" data-bs-id="'.$row->id.'" title="Sil"><i class="fas fa-trash-alt"></i> <span> Sil</span></a>';
                    
                    return $buttons;
                })
                ->filter(function ($instance) use ($request) {
                    if (!empty($request->get('search'))) {
                        $search = $request->get('search');
                        
                    }
                })
                ->rawColumns(['id','created_at','telefon','marka','aciklama','personel', 'action'])
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    public function AddCall($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $service_resources = ServiceResource::where('firma_id', $tenant_id)->get();

        $isBeyazEsya = $firma->sektor === 'beyaz-esya';

        $device_brands = DeviceBrand::where(function($query) use ($firma, $isBeyazEsya) {
            if ($isBeyazEsya) {
                // Beyaz eşya sektörü: default + kendi eklediği
                $query->whereNull('firma_id')
                    ->orWhere('firma_id', $firma->id);
            } else {
                // Diğer sektörler: sadece kendi eklediği
                $query->where('firma_id', $firma->id);
            }
        })->orderBy('marka', 'asc')->get();
        return view('frontend.secure.incoming_calls.add_call', compact('firma','service_resources','device_brands'));
    }

    public function getPhone(Request $request, $tenant_id)
    {
        $brandId = $request->input('brand_id');

        // Örnek: 'tel' kolonu varsa
        $brand = DeviceBrand::find($brandId); // Model ismini senin projene göre düzenle

        if ($brand) {
            return response()->json(['phone' => $brand->aciklama]);
        } else {
            return response()->json(['phone' => ''], 404);
        }
    }

    public function arizaSearch(Request $request,$tenant_id)
    {
        $term = $request->input('q');

        if (!$term) {
            return response()->json([]);
        }

        // Son 10 benzersiz arızayı getir
        $arizalar = IncomingCall::where('firma_id', $tenant_id)->where('ariza', 'like', "%{$term}%")
            ->selectRaw('DISTINCT id, ariza as ariza')
            ->orderBy('ariza')
            ->limit(10)
            ->get();

        return response()->json($arizalar);
    }

    public function StoreCall($tenant_id, Request $request) {
        $token = $request->input('form_token');
        if (empty($token)) {
            return response()->json([
                'error' => 'Geçersiz form token! Lütfen sayfayı yenileyin.'
            ], 400);
        }
        $cacheKey = 'incoming_call_form_token_' . $token;
        if (Cache::has($cacheKey)) {
            return response()->json([
                'error' => 'Bu form zaten gönderildi! Lütfen bekleyin.'
            ], 429);
        }
        Cache::put($cacheKey, true, now()->addMinutes(10));

        $firma = Tenant::where('id', $tenant_id)->first();
        if (!$firma) {
            return redirect()->back()->with([
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger',
            ]);
        }

        $request->validate([
            'serviceResource' => 'required',
            'deviceBrand' => 'required',
            'cihazAriza' => 'required|string',
        ]);

        $response = IncomingCall::create([
            'firma_id' => $firma->id,
            'servisKaynak' => $request->serviceResource,
            'marka' => $request->deviceBrand,
            'kayitAlan' => auth()->user()->user_id,
            'ariza' => $request->cihazAriza,
        ]);
        // Kullanıcı girişi loglarken
        Log::info( Auth::user()->name . '  yeni çağrı ekledi.', [
            'user_id' => Auth::id(),
            'firma_id' => $firma->id, // Firma ID'sini ekleyin
            'ip_address' => request()->ip(),
        ]);

        $createdCalls = IncomingCall::with(['serviskaynak', 'brand', 'kayit_alan'])->find($response->id);
        $createdCalls->formatted_created_at = $createdCalls->created_at->format('d/m/Y H:i');
        return response()->json($createdCalls);
    }

    public function EditCall($tenant_id, $call_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $call_id = IncomingCall::where('firma_id', $tenant_id)->where('id', $call_id)->first();
        $operators = User::where('tenant_id', $tenant_id)->get();
        $service_resources = ServiceResource::where('firma_id', $tenant_id)->get();
        $isBeyazEsya = $firma->sektor === 'beyaz-esya';

        $device_brands = DeviceBrand::where(function($query) use ($firma, $isBeyazEsya) {
            if ($isBeyazEsya) {
                // Beyaz eşya sektörü: default + kendi eklediği
                $query->whereNull('firma_id')
                    ->orWhere('firma_id', $firma->id);
            } else {
                // Diğer sektörler: sadece kendi eklediği
                $query->where('firma_id', $firma->id);
            }
        })->orderBy('marka', 'asc')->get();
        return view('frontend.secure.incoming_calls.edit_call', compact('firma','call_id','operators','service_resources','device_brands'));
    }

    public function UpdateCall($tenant_id, Request $request) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı',
                'alert-type' => 'danger'
            );
            return redirect()->route('giris')->with($notification);
        }

        $call_id = $request->id;
        IncomingCall::findOrFail($call_id)->update([
            'firma_id' => $firma->id,
            'servisKaynak' => $request->serviceResource,
            'marka' => $request->deviceBrand,
            'kayitAlan' => $request->personel,
            'ariza' => $request->cihazAriza,
        ]);

        $updatedCall = IncomingCall::with(['serviskaynak', 'brand', 'kayit_alan'])->find($call_id);
        $updatedCall->formatted_created_at = $updatedCall->created_at->format('d/m/Y H:i');
        return response()->json($updatedCall);
    }

    public function DeleteCall($tenant_id, $id) {
        $incoming_call = IncomingCall::find($id);
        if($incoming_call) {
            $incoming_call->delete();
            return response()->json(['success' => true]);
        }
        else{
            return response()->json(['success' => false, 'message' => 'Çağrı bulunamadı.']);
        }
    }
}
