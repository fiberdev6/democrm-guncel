<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Cache;

class SuperAdminIntegrationController extends Controller
{
    public function AllIntegrations(Request $request) {
        $integrations = Integration::where('is_active', 1)->orderBy('id','desc')->get();
        $categories = [
            'invoice' => 'Fatura',
            'sms' => 'SMS',
            'accounting' => 'Muhasebe',
            'other' => 'Diğer'
        ];
        if ($request->ajax()) {           
            $data = Integration::orderBy('id', 'asc');
            
            if ($request->get('kategori')) {
                $category = $request->get('kategori');
                $data->where('category', $category);
            }

            if ($request->filled('entegreDurumu')) {
                $data->where('is_active', $request->entegreDurumu);
            }

            $data->when($request->filled('from_date') && $request->filled('to_date'), function ($query) use ($request) {
                return $query->whereDate('created_at', '>=', $request->from_date)
                             ->whereDate('created_at', '<=', $request->to_date);
            });

            // Sıralama işlemi
            if ($request->has('order')) {
                $order = $request->get('order')[0];
                $columns = $request->get('columns');
                $orderColumn = $columns[$order['column']]['data'];
                $orderDir = $order['dir'];
                
                $data->orderBy($orderColumn, $orderDir);
            } else {
                $data->orderBy('id','desc');
            }

            return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('id', function($row){
                return '<a class="t-link editIntegration idWrap" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editIntegrationModal"><div class="mobileTitle">Id:</div>'.$row->id.'</a>';
            })
            ->addColumn('name', function($row){
                return '<a class="t-link editIntegration" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editIntegrationModal"><div class="mobileTitle">Entegrasyon Adı:</div><strong>'.$row->name.'</strong></a>';
            })
            ->addColumn('logo', function($row){
                if($row->logo){
                    return '<a class="t-link editIntegration" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editIntegrationModal"><img src="'.asset($row->logo).'" alt="'.$row->name.'" style="width: 50px; height: 50px; object-fit: contain;"></a>';
                }
                return '<a class="t-link editIntegration" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editIntegrationModal"><div class="mobileTitle">Logo:</div>-</a>';
            })
            ->addColumn('category', function($row){
                $categories = [
                    'invoice' => 'Fatura',
                    'sms' => 'SMS',
                    'accounting' => 'Muhasebe',
                    'other' => 'Diğer'
                ];
                $categoryName = $categories[$row->category] ?? $row->category;
                return '<a class="t-link editIntegration" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editIntegrationModal"><div class="mobileTitle">Kategori:</div><span class="badge bg-info">'.$categoryName.'</span></a>';
            })
            ->addColumn('price', function($row){
                if($row->price > 0){
                    return '<a class="t-link editIntegration" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editIntegrationModal"><div class="mobileTitle">Fiyat:</div>'.$row->price.' ₺</a>';
                }
                return '<a class="t-link editIntegration" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editIntegrationModal"><div class="mobileTitle">Fiyat:</div><span class="badge bg-success">Ücretsiz</span></a>';
            })
            ->addColumn('created_at', function($row){
                $createdAt = Carbon::parse($row->created_at)->format('d/m/Y H:i');
                return '<a class="t-link editIntegration" href="javascript:void(0);" data-bs-id="'.$row->id.'" data-bs-toggle="modal" data-bs-target="#editIntegrationModal"><div class="mobileTitle">Oluşturma Tarihi:</div>'.$createdAt.'</a>';
            })
            ->addColumn('actions', function($row){
                $deleteUrl = route('super.admin.integration.delete', $row->id);
                $editButton = '<a href="javascript:void(0);" data-bs-id="'.$row->id.'" class="btn btn-outline-warning btn-sm editIntegration mobilBtn mbuton1" data-bs-toggle="modal" data-bs-target="#editIntegrationModal" title="Düzenle"><i class="fas fa-edit"></i></a>';
                $deleteButton = '<a href="'.$deleteUrl.'" class="btn btn-outline-danger btn-sm mobilBtn" id="delete" title="Sil"><i class="fas fa-trash-alt"></i></a>';
                return $editButton. '  '.$deleteButton;
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function($w) use($request){
                       $search = $request->get('search');
                       $w->where('id', 'LIKE', "%$search%")
                       ->orWhere('name', 'LIKE', "%$search%")
                       ->orWhere('slug', 'LIKE', "%$search%")
                       ->orWhere('category', 'LIKE', "%$search%")
                       ->orWhere('description', 'LIKE', "%$search%");
                   });
                }
            })
            ->rawColumns(['id','created_at','name','category','price','actions'])
            ->make(true);
        }

        return view('frontend.secure.super_admin.integrations.all_integrations', compact('integrations','categories'));
    }

    public function AddIntegration() {
        $categories = [
            'invoice' => 'Fatura',
            'sms' => 'SMS',
            'accounting' => 'Muhasebe',
            'other' => 'Diğer'
        ];
        return view('frontend.secure.super_admin.integrations.add_integration', compact('categories'));
    }

    public function StoreIntegration(Request $request) {
            $token = $request->input('form_token');
        if (empty($token)) {
            $notification = array(
                'message' => 'Geçersiz form token! Lütfen sayfayı yenileyin.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with($notification);
        }
        
        $cacheKey = 'integration_form_token_' . $token;
        
        if (Cache::has($cacheKey)) {
            $notification = array(
                'message' => 'Bu form zaten gönderildi! Lütfen bekleyin.',
                'alert-type' => 'warning'
            );
            return redirect()->back()->with($notification);
        }
        
        Cache::put($cacheKey, true, now()->addMinutes(10));
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required',
            'description' => 'nullable',
            'explanation' => 'nullable',
            'price' => 'nullable|numeric',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'is_active' => 'nullable'
        ]);

        // Slug oluştur
        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;

        // Benzersiz slug kontrolü
        while (Integration::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Logo yükleme
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = time() . '_' . Str::slug($request->name) . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('upload/integrations'), $logoName);
            $logoPath = 'upload/integrations/' . $logoName;
        }

        $integration = Integration::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'explanation' => $request->explanation,
            'api_fields' => $request->api_fields,
            'logo' => $logoPath,
            'price' => $request->price ?? 0,
            'category' => $request->category,
            'is_active' => $request->has('is_active') && $request->is_active == '1' ? 1 : 0, // Düzeltme burada
            'created_at' => Carbon::now(),
        ]);

        $notification = array(
            'message' => 'Entegrasyon başarıyla eklendi.',
            'alert-type' => 'success'
        );

        return redirect()->route('super.admin.integrations')->with($notification);
    }

    public function EditIntegration($id) {
        $integration = Integration::findOrFail($id);
        
        if(!$integration) {
            $notification = array(
                'message' => 'Entegrasyon bulunamadı!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }

        $categories = [
            'invoice' => 'Fatura',
            'sms' => 'SMS',
            'accounting' => 'Muhasebe',
            'other' => 'Diğer'
        ];

        return view('frontend.secure.super_admin.integrations.edit_integration', compact('integration','categories'));
    }

    public function UpdateIntegration($id, Request $request){
        $integration = Integration::findOrFail($id);
        
        if(!$integration) {
            $notification = array(
                'message' => 'Entegrasyon bulunamadı!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'explanation' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'nullable|boolean'
        ]);

        // Slug güncelleme (eğer isim değiştiyse)
        $slug = $integration->slug;
        if($request->name != $integration->name) {
            $slug = Str::slug($request->name);
            $originalSlug = $slug;
            $counter = 1;

            // Benzersiz slug kontrolü (kendi id'si hariç)
            while (Integration::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        // Logo güncelleme
        $logoPath = $integration->logo;
        if ($request->hasFile('logo')) {
            // Eski logoyu sil
            if($integration->logo && file_exists(public_path($integration->logo))) {
                unlink(public_path($integration->logo));
            }

            $logo = $request->file('logo');
            $logoName = time() . '_' . Str::slug($request->name) . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('upload/integrations'), $logoName);
            $logoPath = 'upload/integrations/' . $logoName;
        }

        Integration::findOrFail($id)->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'explanation' => $request->explanation,
            'api_fields' => $request->api_fields,
            'logo' => $logoPath,
            'price' => $request->price ?? 0,
            'category' => $request->category,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        $integration = Integration::findOrFail($id);
        
        $notification = array(
            'message' => 'Entegrasyon başarıyla güncellendi.',
            'alert-type' => 'success'
        );

        return redirect()->route('super.admin.integrations')->with($notification);
    }

    public function DeleteIntegration($id) {
        $integration = Integration::findOrFail($id);
        
        if(is_null($integration)) {
            $notification = array(
                'message' => 'Entegrasyonu silemezsiniz!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }
        else {
            // Logo dosyasını sil
            if($integration->logo && file_exists(public_path($integration->logo))) {
                unlink(public_path($integration->logo));
            }

            $integrationName = $integration->name;
            $integration->delete();

            $notification = array(
                'message' => 'Entegrasyon başarıyla silindi.',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }
    }

    // AJAX ile entegrasyon detayları getirme (Modal için)
    public function GetIntegration($id) {
        $integration = Integration::findOrFail($id);
        
        if(!$integration) {
            return response()->json([
                'success' => false,
                'message' => 'Entegrasyon bulunamadı!'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'integration' => $integration
        ]);
    }
}
