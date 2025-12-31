<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\PersonelRequest;
use App\Http\Requests\BayiRequest;

class PersonelController extends Controller
{   
        public function __construct()
    {
        $this->middleware('permission:Personelleri Görebilir');
    }

    public function AllStaffs($tenant_id, Request $request) {
        
        // Kullanıcı oturum kontrolü
        if (!Auth::check()) {
            return redirect()->route('giris')->with('error', 'Lütfen giriş yapınız.');
        }
        $user = Auth::user();
        // Kullanıcının tenant bilgisi kontrolü
        if ($tenant_id == null || $user->tenant->id != $tenant_id) {
            return redirect()->route('giris')->with([
                'message' => 'Personellere erişiminiz yoktur.',
                'alert-type' => 'danger',
            ]);
        }
        // Firma bilgisi
        $firma = Tenant::where('id', $tenant_id)->first();
        if (!$firma) {
            return redirect()->route('giris')->with([
                'message' => 'Firma bulunamadı.',
                'alert-type' => 'danger',
            ]);
        }
        // Firma personelleri
        $staffs = User::whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', ['Bayi', 'Admin', 'Super Admin']);
            });

        $roles = Role::whereNotIn('name', ['Bayi', 'Admin', 'Super Admin'])->get();
        if ($request->ajax()) {       
            
            $data = User::whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', ['Bayi', 'Admin', 'Super Admin']);
            });   
            // $data = User::query();  //personeller içinde bayileri de listeliyordu
            if ($request->filled('durum')) {
                if ($request->get('durum') == 1) {
                    $data->where('status', 1);
                } elseif ($request->get('durum') == 0) {
                    $data->where('status', 0);
                } elseif ($request->get('durum') == 2) {                
                }
            }
          
            if ($request->get('grup')) {
                $data->whereHas('roles', function ($query) use ($request) {
                    $query->where('id', $request->grup);
                });
            }

            // Sıralama işlemi
            if ($request->has('order')) {
                $order = $request->get('order')[0];
                $columns = $request->get('columns');
                $orderColumn = $columns[$order['column']]['data'];
                $orderDir = $order['dir'];
                // $data->where('tenant_id', $firma->id)->orderBy($orderColumn, $orderDir);
                $data->where('tenant_id', $firma->id)
                ->whereDoesntHave('roles', function ($query) {
                    $query->where('name', 'Bayi');
                })
                ->orderBy($orderColumn, $orderDir);
            } else {
                // $data->where('tenant_id', $firma->id)->orderBy('user_id','desc');
                                $data->where('tenant_id', $firma->id)
                ->whereDoesntHave('roles', function ($query) {
                    $query->where('name', 'Bayi');
                })
                ->orderBy('user_id','desc');
            }
          
            
            $filteredData = $data;
    
            return DataTables::of($filteredData)
                ->addIndexColumn()
                ->addColumn('user_id', function($row){  
                    return '<a class="t-link editPersonel address idWrap" href="javascript:void(0);" data-bs-id="'.$row->user_id.'" data-bs-toggle="modal" data-bs-target="#editPersonelModal">'.$row->user_id.'</a>'; 
                })
                ->addColumn('name', function($row){
                    return '<a class="t-link editPersonel address" href="javascript:void(0);" data-bs-id="'.$row->user_id.'" data-bs-toggle="modal" data-bs-target="#editPersonelModal"><div class="mobileTitle">Personel Adı:</div>'.$row->name.'</a>';     
                })
                ->addColumn('grup', function($row){
                    foreach($row->roles as $role){
                        return '<a class="t-link editPersonel" href="javascript:void(0);" data-bs-id="'.$row->user_id.'" data-bs-toggle="modal" data-bs-target="#editPersonelModal"><div class="mobileTitle">P. Grubu:</div><span class="badge badge-pill bg-danger badge-size-custom">'.$role->name.'</span></div></a>';
                    }          
                })
                ->addColumn('tel', function($row){     
                    $telefon = $row->tel;

                    // Eğer telefon numarası başında 0 yoksa ekle
                    if (substr($telefon, 0, 1) !== '0') {
                        $telefon = '0' . $telefon;
                    }
                    return '<a class="t-link editPersonel" href="javascript:void(0);" data-bs-id="'.$row->user_id.'" data-bs-toggle="modal" data-bs-target="#editPersonelModal"><div class="mobileTitle">Telefon:</div>'.$telefon.'</div></a>';
                })
                ->addColumn('address', function($row){  
                    $address = (!empty($row->country->name) && !empty($row->state->ilceName)) 
                    ? $row->country->name . ' - ' . $row->state->ilceName 
                    : '';
              
                    return '<a class="t-link editPersonel address" href="javascript:void(0);" data-bs-id="'.$row->user_id.'" data-bs-toggle="modal" data-bs-target="#editPersonelModal"><div class="mobileTitle">Adres:</div>'.$address.'</div></a>';
                })
                ->addColumn('status', function($row){
                    if($row->status == 1){
                        return '<a class="t-link editPersonel" href="javascript:void(0);" data-bs-id="'.$row->user_id.'" data-bs-toggle="modal" data-bs-target="#editPersonelModal"><div class="mobileTitle">Durum:</div><div style="color: green; display: inline-block;font-weight:700;">Çalışıyor</div></div></a>';
                    }else{
                        return '<a class="t-link editPersonel" href="javascript:void(0);" data-bs-id="'.$row->user_id.'" data-bs-toggle="modal" data-bs-target="#editPersonelModal"><div class="mobileTitle">Durum:</div><div style="color: red; display: inline-block;font-weight:700;">Ayrıldı</div></div></a>';
                    }
                })
                ->addColumn('action', function($row){
                    $deleteUrl = route('delete.personel', [$row->tenant_id,$row->user_id]);
                    $editButton = '';
                    $viewButton = '';
                    $deleteButton = '';
                    $editButton = '<a href="javascript:void(0);" data-bs-id="'.$row->user_id.'" class="btn btn-outline-warning btn-sm editPersonel mobilBtn mbuton1" data-bs-toggle="modal" data-bs-target="#editPersonelModal" title="Düzenle"><i class="fas fa-edit"></i></a>';
                    $viewButton = '<a href="javascript:void(0);" data-bs-id="'.$row->user_id.'" class="btn btn-outline-primary btn-sm editPersonel mobilBtn mbuton1" data-bs-toggle="modal" data-bs-target="#editPersonelModal" title="Düzenle"><i class="fas fa-eye"></i></a>';
                   
                    $deleteButton = '<a href="'.$deleteUrl.'" class="btn btn-outline-danger btn-sm mobilBtn" id="delete" title="Sil"><i class="fas fa-trash-alt"></i></a>';
                    
                    return $viewButton . ' ' . $editButton. ' ' .$deleteButton;
                })
                ->filter(function ($instance) use ($request) {
                    if (!empty($request->get('search'))) {
                        $instance->where(function($w) use($request){
                           $search = $request->get('search');
                           $w->where('name', 'LIKE', "%$search%");                        
                        });
                    }
                })
                ->rawColumns(['user_id','name','grup','tel','address','status','action'])
                ->make(true);                      
            }
        return view('frontend.secure.staffs.all_staffs', compact('staffs','firma','roles'));
    }

    public function AddStaff($tenant_id) {
        $roles= Role::whereNotIn('name', ['Bayi', 'Admin', 'Super Admin'])
        ->with('permissions') // İzinleri de yükle
        ->get();
        $firma = Tenant::where('id', $tenant_id)->first();
        $countries = DB::table('ils')->orderBy('name', 'ASC')->get();
        return view('frontend.secure.staffs.add_staff',compact('roles','firma','countries'));
    }
   

    protected function generateUserEmail($userEmail, $domain)
    {
        $username = explode('@', $userEmail)[0]; // E-postanın kullanıcı adını alır
        return $username . '@' . $domain; // Kullanıcı adı ve firma domainiyle yeni e-posta oluşturur
    }

    public function StoreStaff(PersonelRequest $request, $tenant_id) {

    $token = $request->input('form_token');
    if (empty($token)) {
        return back()->withInput()->with([
            'message' => 'Geçersiz form token! Lütfen sayfayı yenileyin.',
            'alert-type' => 'error'
        ]);
    }

    $cacheKey = 'staff_form_token_' . $token;
    if (Cache::has($cacheKey)) {
        return back()->withInput()->with([
            'message' => 'Bu form zaten gönderildi! Lütfen bekleyin veya sayfayı yenileyin.',
            'alert-type' => 'warning'
        ]);
    }
    Cache::put($cacheKey, true, now()->addMinutes(10));

    $firma = Tenant::where('id', $tenant_id)->first();

    if (!$firma) {
        return redirect()->route('giris')->with([
            'message' => 'Firma bulunamadı.',
            'alert-type' => 'danger',
        ]);
    }

    // 🔥 GÜNCELLEME: Username format kontrolü
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $request->username)) {
        return back()->withInput()->with([
            'message' => 'Kullanıcı adı sadece harf, rakam ve alt çizgi içerebilir.',
            'alert-type' => 'error'
        ]);
    }

    // 🔥 GÜNCELLEME: Username uzunluk kontrolü
    if (strlen($request->username) < 3 || strlen($request->username) > 50) {
        return back()->withInput()->with([
            'message' => 'Kullanıcı adı 3-50 karakter arasında olmalıdır.',
            'alert-type' => 'error'
        ]);
    }

    // 🔥 GÜNCELLEME: Aynı isimde personel kontrolü (sadece aktif olanları kontrol et)
    $existingUser = User::where('tenant_id', $firma->id)
        ->where('name', $request->name)
        ->where('status', 1)
        ->whereDoesntHave('roles', function ($query) {
            $query->where('name', 'Bayi');
        })
        ->first();

    if ($existingUser) {
        return back()->withInput()->with([
            'message' => 'Bu isimde bir personel zaten mevcut.',
            'alert-type' => 'error'
        ]);
    }

    // 🔥 GÜNCELLEME: Username kontrolü (Global veya Tenant bazında)
    // Seçenek 1: Tenant bazında unique (Önerilen - mevcut yapınız)
    $existingUsername = User::where('tenant_id', $firma->id)
        ->where('username', $request->username)
        ->first();

    // Seçenek 2: Global unique (tüm sistemde benzersiz)
    // $existingUsername = User::where('username', $request->username)->first();

    if ($existingUsername) {
        return back()->withInput()->with([
            'message' => 'Bu kullanıcı adı firmanızda zaten kullanılıyor. Lütfen farklı bir kullanıcı adı seçin.',
            'alert-type' => 'error'
        ]);
    }

    // Personel limiti kontrolü
    $current = User::where('tenant_id', $firma->id)
        ->where('status','1')
        ->whereHas('roles', function ($query) {
            $query->where('name', '!=', 'Bayi');
        })->count();

    $limit = $firma->plan()?->limits['users'] ?? null;

    if ($limit !== null && $limit !== -1 && $current >= $limit) {
        return back()->with([
            'message' => 'Maksimum personel limitine ulaştınız.',
            'alert-type' => 'error'
        ]);
    }

    // Personel oluşturma
    $username = Str::slug($request->username, '-');
    $user = new User();
    $user->tenant_id = $firma->id;
    $user->username = $request->username; // Slug kullanma, direkt kullanıcının girdiğini kullan
    $user->eposta = $this->generateUserEmail($username, $firma->username);
    $user->baslamaTarihi = $request->baslamaTarihi;
    $user->name = $request->name;
    $user->tel = $request->tel;
    $user->il = $request->il;
    $user->ilce = $request->ilce;
    $user->address = $request->address;
    $user->password = Hash::make($request->password);
    $user->status = 1;
    $user->save();

    $roleName = null;
    if($request->roles){
        $role = Role::findById($request->roles);
        $user->assignRole($role->name);
        $roleName = $role->name;
    }

    ActivityLogger::logStaffCreated($user->user_id, $request->name, $roleName);

    $notification = array(
        'message' => 'Personel kaydı başarıyla yapıldı.',
        'alert-type' => 'success'
    );

    return redirect()->route('staffs',$tenant_id)->with($notification);
}
public function checkUsernameAvailability(Request $request, $tenant_id)
{
    $firma = Tenant::findOrFail($tenant_id);
    
    $username = $request->username;
    $userId = $request->user_id ?? null; // Edit modunda mevcut kullanıcı ID'si
    
    // Format kontrolü
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return response()->json([
            'available' => false,
            'message' => 'Geçersiz format'
        ]);
    }
    
    // Tenant bazında kontrol (edit modunda kendisi hariç)
    $query = User::where('tenant_id', $firma->id)
        ->where('username', $username);
    
    if ($userId) {
        $query->where('user_id', '!=', $userId);
    }
    
    $exists = $query->exists();
    
    return response()->json([
        'available' => !$exists,
        'message' => $exists ? 'Kullanıcı adı zaten kullanılıyor' : 'Kullanıcı adı kullanılabilir'
    ]);
}

    public function EditStaff($tenant_id,$id) {
        $firma = Tenant::where('id', $tenant_id)->first();

        if (!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı.',
                'alert-type' => 'danger'
            );
            return redirect()->route('giris')->with($notification);
        }
        $staff = User::findOrFail($id);
        if(!$staff){
            $notification = array(
                'message' => 'Personel bulunamadı.',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }
        $roles = Role::whereNotIn('name', ['Bayi', 'Admin', 'Super Admin'])->get();
        $countries = DB::table('ils')->orderBy('name', 'ASC')->get();
        return view('frontend.secure.staffs.edit_staff', compact('staff','roles','firma','countries'));
    }

    public function UpdateStaff(PersonelRequest $request, $tenant_id, $id){
    $firma = Tenant::where('id', $tenant_id)->first();

    if (!$firma) {
        return response()->json([
            'error' => 'Firma bulunamadı.'
        ], 404);
    }

    $staff = User::findOrFail($id);
    if(!$staff){
        return response()->json([
            'error' => 'Personel bulunamadı.'
        ], 404);
    }

    // 🔥 Username format kontrolü
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $request->username)) {
        return response()->json([
            'error' => 'Kullanıcı adı sadece harf, rakam ve alt çizgi içerebilir.'
        ], 422);
    }

    // 🔥 Username uzunluk kontrolü
    if (strlen($request->username) < 3 || strlen($request->username) > 50) {
        return response()->json([
            'error' => 'Kullanıcı adı 3-50 karakter arasında olmalıdır.'
        ], 422);
    }

    // Güncelleme sırasında aynı isimde başka personel var mı kontrolü
    $existingUser = User::where('tenant_id', $firma->id)
        ->where('name', $request->name)
        ->where('user_id', '!=', $id)
        ->where('status', 1)
        ->whereDoesntHave('roles', function ($query) {
            $query->where('name', 'Bayi');
        })
        ->first();

    if ($existingUser) {
        return response()->json([
            'error' => 'Bu isimde başka bir personel zaten mevcut.'
        ], 422);
    }

    // 🔥 Username kontrolü (kendisi hariç)
    $existingUsername = User::where('tenant_id', $firma->id)
        ->where('username', $request->username)
        ->where('user_id', '!=', $id)
        ->first();

    if ($existingUsername) {
        return response()->json([
            'error' => 'Bu kullanıcı adı firmanızda zaten kullanılıyor.'
        ], 422);
    }

    $staff->username = $request->username;
    $staff->name = $request->name;
    $staff->baslamaTarihi = $request->baslamaTarihi;
    $staff->tel = $request->tel;
    $staff->address = $request->address;
    $staff->il = $request->il;
    $staff->ilce = $request->ilce;
    
    if($request->password){
        $staff->password = Hash::make($request->password);
    }
    
    $staff->status = $request->status;
    $staff->ayrilmaTarihi = $request->ayrilmaTarihi;
    $staff->save();

    $staff->roles()->detach();
    $roleName = null;
    if($request->roles){
        $role = Role::findById($request->roles);
        $staff->assignRole($role->name);
        $roleName = $role->name;
    }

    ActivityLogger::logStaffUpdated($staff->user_id, $request->name, $roleName);   
    
    $notification = array(
        'message' => 'Personel Bilgileri Başarıyla Güncellendi',
        'alert-type' => 'success'
    );
    
    return response()->json(['success' => $notification]);
}

    public function DeleteStaff($tenant_id, $id) {
        $staff = User::findOrFail($id);
        $authUser = Auth::user()->user_id;
        if($staff->user_id == $authUser) {
            $notification = array(
                'message' => 'Kendinizi silemezsiniz!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }

        // Personel silme log kaydı (silmeden önce bilgileri al)
        $staffName = $staff->name;
        $staffId = $staff->user_id;
        $roleName = $staff->roles->first()?->name;

        if(!is_null($staff)) {
            $staff->delete();
        }

        // Log kaydı
        ActivityLogger::logStaffDeleted($staffId, $staffName, $roleName);
        $notification = array(
            'message' => 'Personel başarıyla silindi.',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }

    
    //DEALERS

    public function AllDealers($tenant_id, Request $request) {
    if (!Auth::check()) {
        return redirect()->route('giris')->with('error', 'Lütfen giriş yapınız.');
    }
    $user = Auth::user();
    if ($tenant_id == null || $user->tenant->id != $tenant_id) {
        return redirect()->route('giris')->with([
            'message' => 'Bayilere erişiminiz yoktur.',
            'alert-type' => 'danger',
        ]);
    }

    $firma = Tenant::findOrFail($tenant_id);
    $dealerRole = Role::find(259); // bayi rolü ID'si

    $dealers = User::where('tenant_id', $tenant_id)
        ->whereHas('roles', function ($query) use ($dealerRole) {
            $query->where('id', $dealerRole->id);
        })
        ->get();

    return view('frontend.secure.dealers.all_dealers', compact('dealers', 'firma'));
}

public function AddDealer($tenant_id) {
    $firma = Tenant::findOrFail($tenant_id);
    $roles= Role::where('name','!=', 'Bayi')->get();
    $countries = DB::table('ils')->orderBy('name', 'ASC')->get();
    return view('frontend.secure.dealers.add_dealer', compact('firma','roles', 'countries'));
}

public function StoreDealer(BayiRequest $request, $tenant_id)
{
    $token = $request->input('form_token');
    if (empty($token)) {
        return back()->withInput()->with([
            'message' => 'Geçersiz form token! Lütfen sayfayı yenileyin.',
            'alert-type' => 'error'
        ]);
    }

    $cacheKey = 'dealer_form_token_' . $token;
    if (Cache::has($cacheKey)) {
        return back()->withInput()->with([
            'message' => 'Bu form zaten gönderildi! Lütfen bekleyin veya sayfayı yenileyin.',
            'alert-type' => 'warning'
        ]);
    }
    Cache::put($cacheKey, true, now()->addMinutes(10));

    $firma = Tenant::findOrFail($tenant_id);

    // 🔥 YENİ: Username format kontrolü
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $request->username)) {
        return back()->withInput()->with([
            'message' => 'Kullanıcı adı sadece harf, rakam ve alt çizgi içerebilir.',
            'alert-type' => 'error'
        ]);
    }

    // 🔥 YENİ: Username uzunluk kontrolü
    if (strlen($request->username) < 3 || strlen($request->username) > 50) {
        return back()->withInput()->with([
            'message' => 'Kullanıcı adı 3-50 karakter arasında olmalıdır.',
            'alert-type' => 'error'
        ]);
    }

    // Aynı isimde bayi kontrolü
    $existingDealer = User::where('tenant_id', $firma->id)
        ->where('name', $request->name)
        ->where('status', 1)
        ->whereHas('roles', function ($query) {
            $query->where('name', 'Bayi');
        })
        ->first();

    if ($existingDealer) {
        return back()->withInput()->with([
            'message' => 'Bu isimde bir bayi zaten mevcut.',
            'alert-type' => 'error'
        ]);
    }

    // 🔥 GÜNCELLEME: Username kontrolü
    $existingUsername = User::where('tenant_id', $firma->id)
        ->where('username', $request->username)
        ->first();

    if ($existingUsername) {
        return back()->withInput()->with([
            'message' => 'Bu kullanıcı adı firmanızda zaten kullanılıyor. Lütfen farklı bir kullanıcı adı seçin.',
            'alert-type' => 'error'
        ]);
    }

    // Vergi numarası kontrolü
    $existingTaxNumber = User::where('tenant_id', $firma->id)
        ->where('vergiNo', $request->vergiNo)
        ->whereNotNull('vergiNo')
        ->first();

    if ($existingTaxNumber) {
        return back()->withInput()->with([
            'message' => 'Bu vergi numarası zaten kullanılıyor.',
            'alert-type' => 'error'
        ]);
    }

    // Bayi limiti kontrolü
    $current = User::where('tenant_id', $firma->id)
        ->where('status','1')
        ->whereHas('roles', function ($query) {
            $query->where('name', 'Bayi');
        })->count();

    $limit = $firma->plan()?->limits['dealers'] ?? null;

    if ($limit !== null && $limit !== -1 && $current >= $limit) {
        return back()->with([
            'message' => 'Maksimum bayi limitine ulaştınız.',
            'alert-type' => 'error'
        ]);
    }

    $username = Str::slug($request->username, '-');
    $firmaSlug = $firma->firma_slug;

    // Belge yükleme
    $belgePdfPaths = [];
    if ($request->hasFile('belgePdf')) {
        $files = array_slice($request->file('belgePdf'), 0, 2);
        
        foreach ($files as $file) {
            $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'svg'];
            $extension = strtolower($file->getClientOriginalExtension());
            
            if (!in_array($extension, $allowedExtensions)) {
                return back()->withInput()->with([
                    'message' => "Geçersiz dosya türü: .{$extension}. Sadece PDF, JPG, JPEG, PNG ve SVG dosyaları kabul edilir.",
                    'alert-type' => 'error'
                ]);
            }

            if ($file->getSize() > 5120 * 1024) {
                return back()->withInput()->with([
                    'message' => 'Dosya boyutu çok büyük. Maksimum 5MB olmalıdır.',
                    'alert-type' => 'error'
                ]);
            }
            
            $fileName = time() . '_' . $username . '_' . uniqid() . '.' . $extension;
            $path = "dealers-documents/firma_{$firmaSlug}/bayi_{$username}/" . now()->toDateString();
            
            $fullPath = storage_path('app/public/' . $path);
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0775, true);
            }
            
            $file->move($fullPath, $fileName);
            $storedPath = $path . '/' . $fileName;
            $belgePdfPaths[] = $storedPath;
        }
    }

    // Bayi oluştur
    $user = new User();
    $user->tenant_id = $firma->id;
    $user->username = $request->username; // Direkt kullan, slug'a çevirme
    $user->eposta = $this->generateUserEmail($username, $firma->username);
    $user->baslamaTarihi = $request->baslamaTarihi;
    $user->name = $request->name;
    $user->tel = $request->tel;
    $user->il = $request->il;
    $user->ilce = $request->ilce;
    $user->address = $request->address;
    $user->vergiNo = $request->vergiNo; 
    $user->vergiDairesi = $request->vergiDairesi; 
    $user->belgePdf = json_encode($belgePdfPaths);
    $user->password = Hash::make($request->password);
    $user->status = 1;
    $user->save();

    $role = Role::find(259);
    if ($role) {
        $user->assignRole($role->name);
    }

    ActivityLogger::logDealerCreated($user->user_id, $request->name);

    $notification = [
        'message' => 'Bayi başarıyla kaydedildi.',
        'alert-type' => 'success'
    ];

    return redirect()->route('dealers', $tenant_id)->with($notification);
}
public function EditDealer($tenant_id, $id)
{
    $firma = Tenant::findOrFail($tenant_id);

    $bayi = User::where('tenant_id', $tenant_id)
                ->where('user_id', $id)
                ->whereHas('roles', function ($q) {
                    $q->where('id', 259); // bayi rolü ID
                })
                ->firstOrFail();

    $countries = DB::table('ils')->orderBy('name', 'ASC')->get();
  

    return view('frontend.secure.dealers.edit_dealer', compact('firma', 'bayi', 'countries'));
}

public function UpdateDealer(BayiRequest $request, $tenant_id, $id)
{
    $firma = Tenant::findOrFail($tenant_id);
    $bayi = User::where('tenant_id', $tenant_id)
                ->where('user_id', $id)
                ->whereHas('roles', function ($q) {
                    $q->where('id', 259);
                })
                ->firstOrFail();

    // 🔥 YENİ: Username format kontrolü
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $request->username)) {
        return response()->json([
            'error' => 'Kullanıcı adı sadece harf, rakam ve alt çizgi içerebilir.'
        ], 422);
    }

    // 🔥 YENİ: Username uzunluk kontrolü
    if (strlen($request->username) < 3 || strlen($request->username) > 50) {
        return response()->json([
            'error' => 'Kullanıcı adı 3-50 karakter arasında olmalıdır.'
        ], 422);
    }

    // Aynı isimde başka bayi var mı kontrolü
    $existingDealer = User::where('tenant_id', $tenant_id)
        ->where('name', $request->name)
        ->where('user_id', '!=', $id)
        ->where('status', 1)
        ->whereHas('roles', function ($query) {
            $query->where('name', 'Bayi');
        })
        ->first();

    if ($existingDealer) {
        return response()->json([
            'error' => 'Bu isimde başka bir bayi zaten mevcut.'
        ], 422);
    }

    // 🔥 GÜNCELLEME: Username kontrolü
    $existingUsername = User::where('tenant_id', $tenant_id)
        ->where('username', $request->username)
        ->where('user_id', '!=', $id)
        ->first();

    if ($existingUsername) {
        return response()->json([
            'error' => 'Bu kullanıcı adı firmanızda zaten kullanılıyor.'
        ], 422);
    }

    // Vergi numarası kontrolü
    $existingTaxNumber = User::where('tenant_id', $tenant_id)
        ->where('vergiNo', $request->vergiNo)
        ->where('user_id', '!=', $id)
        ->whereNotNull('vergiNo')
        ->first();

    if ($existingTaxNumber) {
        return response()->json([
            'error' => 'Bu vergi numarası zaten kullanılıyor.'
        ], 422);
    }

    $firmaSlug = $firma->firma_slug;

    // Belge güncellemesi
    if ($request->hasFile('belgePdf')) {
        $mevcutBelgeler = [];
        if ($bayi->belgePdf) {
            $mevcutBelgeler = json_decode($bayi->belgePdf, true) ?: [$bayi->belgePdf];
        }

        $yeniBelgeler = [];
        $files = array_slice($request->file('belgePdf'), 0, 2);
        
        foreach ($files as $file) {
            $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'svg'];
            $extension = strtolower($file->getClientOriginalExtension());
            
            if (!in_array($extension, $allowedExtensions)) {
                return back()->withInput()->with([
                    'message' => "Geçersiz dosya türü: .{$extension}. Sadece PDF, JPG, JPEG, PNG ve SVG dosyaları kabul edilir.",
                    'alert-type' => 'error'
                ]);
            }

            if ($file->getSize() > 5120 * 1024) {
                return back()->withInput()->with([
                    'message' => 'Dosya boyutu çok büyük. Maksimum 5MB olmalıdır.',
                    'alert-type' => 'error'
                ]);
            }
            
            $fileName = time() . '_' . Str::slug($request->username, '-') . '_' . uniqid() . '.' . $extension;
            $path = "dealers-documents/firma_{$firmaSlug}/bayi_" . Str::slug($request->username, '-') . "/" . now()->toDateString();
            
            $fullPath = storage_path('app/public/' . $path);
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0775, true);
            }
            
            $file->move($fullPath, $fileName);
            $storedPath = $path . '/' . $fileName;
            $yeniBelgeler[] = $storedPath;
        }

        $toplamBelgeSayisi = count($mevcutBelgeler) + count($yeniBelgeler);
        
        if ($toplamBelgeSayisi > 2) {
            $silinecekSayi = $toplamBelgeSayisi - 2;
            
            for ($i = 0; $i < $silinecekSayi; $i++) {
                if (isset($mevcutBelgeler[$i]) && Storage::disk('public')->exists($mevcutBelgeler[$i])) {
                    Storage::disk('public')->delete($mevcutBelgeler[$i]);
                }
                unset($mevcutBelgeler[$i]);
            }
            
            $mevcutBelgeler = array_values($mevcutBelgeler);
        }

        $tumBelgeler = array_merge($mevcutBelgeler, $yeniBelgeler);
        $tumBelgeler = array_slice($tumBelgeler, 0, 2);
        
        $bayi->belgePdf = json_encode($tumBelgeler);
    }

    $bayi->name = $request->name;
    $bayi->username = $request->username;
    $bayi->tel = $request->tel;
    $bayi->il = $request->il;
    $bayi->ilce = $request->ilce;
    $bayi->address = $request->address;
    $bayi->baslamaTarihi = $request->baslamaTarihi;
    $bayi->status = $request->status;
    $bayi->ayrilmaTarihi = $request->ayrilmaTarihi;
    $bayi->vergiNo = $request->vergiNo;
    $bayi->vergiDairesi = $request->vergiDairesi;
    
    if ($request->filled('password')) {
        $bayi->password = Hash::make($request->password);
    }

    $bayi->save();

    $bayi->roles()->detach();
    $bayi->assignRole('Bayi');

    ActivityLogger::logDealerUpdated($bayi->user_id, $request->name);

    $notification = [
        'message' => 'Bayi bilgileri başarıyla güncellendi.',
        'alert-type' => 'success'
    ];

    return response()->json(['success' => $notification]);
}
// Belge görüntüleme için yeni method
public function ShowDealerDocument($tenant_id, $user_id, $document_index = 0)
{
    $bayi = User::where('tenant_id', $tenant_id)
                ->where('user_id', $user_id)
                ->whereHas('roles', function ($q) {
                    $q->where('id', 259);
                })
                ->firstOrFail();

    if (!$bayi->belgePdf) {
        abort(404, 'Belge bulunamadı');
    }

    $belgeler = json_decode($bayi->belgePdf, true) ?: [$bayi->belgePdf];
    
    if (!isset($belgeler[$document_index])) {
        abort(404, 'Belge bulunamadı');
    }

    $documentPath = $belgeler[$document_index];
    
    if (!Storage::disk('public')->exists($documentPath)) {
        abort(404, 'Belge dosyası bulunamadı');
    }

    $file = Storage::disk('public')->get($documentPath);
    $mimeType = Storage::disk('public')->mimeType($documentPath);
    $fileName = basename($documentPath);

    return response($file, 200)
        ->header('Content-Type', $mimeType)
        ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
}


public function DeleteDealer($tenant_id, $id) {
    $dealer = User::findOrFail($id);

    // Giriş yapan kullanıcı kendi hesabını silemez
    if (Auth::user()->user_id == $dealer->user_id) {
        return redirect()->back()->with([
            'message' => 'Kendi hesabınızı silemezsiniz!',
            'alert-type' => 'danger'
        ]);
    }

    // Bayi silme log kaydı (silmeden önce bilgileri al)
    $dealerName = $dealer->name;
    $dealerId = $dealer->user_id;

    // Kullanıcı gerçekten bayi mi kontrolü (rol ID'si ile değil, isimle)
    if ($dealer->hasRole('Bayi')) {
        $dealer->delete();

         // Log kaydı
        ActivityLogger::logDealerDeleted($dealerId, $dealerName);

        return redirect()->back()->with([
            'message' => 'Bayi başarıyla silindi.',
            'alert-type' => 'success'
        ]);
    }

    return redirect()->back()->with([
        'message' => 'Bu kullanıcı bayi değildir.',
        'alert-type' => 'danger'
    ]);
}

public function GetDealersData(Request $request, $tenant_id)
{
    if ($request->ajax()) {
        $query = User::where('tenant_id', $tenant_id)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Bayi');
            });

        // Durum filtreleme (0: ayrıldı, 1: çalışıyor, 2: tümü)
        if ($request->filled('durum') && $request->durum !== '2') {
            $query->where('status', $request->durum);
        }

        // Sıralama işlemi
        if ($request->has('order')) {
            $order = $request->get('order')[0];
            $columns = $request->get('columns');
            $orderColumn = $columns[$order['column']]['data'];
            $orderDir = $order['dir'];
            $query->orderBy($orderColumn, $orderDir);
        } else {
            $query->orderBy('user_id', 'desc');
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('user_id', function($row){  
                return '<a class="t-link editBayi address idWrap" href="javascript:void(0);" data-bs-id="'.$row->user_id.'" data-bs-toggle="modal" data-bs-target="#editBayiModal">'.$row->user_id.'</a>'; 
            })
            ->addColumn('name', function($row){
                return '<a class="t-link editBayi address" href="javascript:void(0);" data-bs-id="'.$row->user_id.'" data-bs-toggle="modal" data-bs-target="#editBayiModal"><div class="mobileTitle">Bayi Adı:</div>'.$row->name.'</a>';     
            })
            ->addColumn('grup', function($row){
                foreach($row->roles as $role){
                    return '<a class="t-link editBayi" href="javascript:void(0);" data-bs-id="'.$row->user_id.'" data-bs-toggle="modal" data-bs-target="#editBayiModal"><div class="mobileTitle">B. Grubu:</div><span class="badge badge-pill bg-danger">'.$role->name.'</span></div></a>';
                }          
            })
            ->addColumn('tel', function($row){     
                $telefon = $row->tel;
                if (substr($telefon, 0, 1) !== '0') {
                    $telefon = '0' . $telefon;
                }
                return '<a class="t-link editBayi" href="javascript:void(0);" data-bs-id="'.$row->user_id.'" data-bs-toggle="modal" data-bs-target="#editBayiModal"><div class="mobileTitle">Telefon:</div>'.$telefon.'</div></a>';
            })
            ->addColumn('address', function($row){  
                $address = (!empty($row->country->name) && !empty($row->state->ilceName)) 
                    ? $row->country->name . ' - ' . $row->state->ilceName 
                    : '';
                return '<a class="t-link editBayi address" href="javascript:void(0);" data-bs-id="'.$row->user_id.'" data-bs-toggle="modal" data-bs-target="#editBayiModal"><div class="mobileTitle">Adres:</div>'.$address.'</div></a>';
            })
            ->addColumn('status', function($row){
                if($row->status == 1){
                    return '<a class="t-link editBayi" href="javascript:void(0);" data-bs-id="'.$row->user_id.'" data-bs-toggle="modal" data-bs-target="#editBayiModal"><div class="mobileTitle">Durum:</div><div style="color: green; display: inline-block;font-weight:700;">Çalışıyor</div></div></a>';
                }else{
                    return '<a class="t-link editBayi" href="javascript:void(0);" data-bs-id="'.$row->user_id.'" data-bs-toggle="modal" data-bs-target="#editBayiModal"><div class="mobileTitle">Durum:</div><div style="color: red; display: inline-block;font-weight:700;">Ayrıldı</div></div></a>';
                }
            })
            ->addColumn('action', function($row) use ($tenant_id){
                $deleteUrl = route('delete.dealer', [$tenant_id, $row->user_id]);
                $editButton = '<a href="javascript:void(0);" data-bs-id="'.$row->user_id.'" class="btn btn-outline-warning btn-sm editBayi mobilBtn mbuton1" data-bs-toggle="modal" data-bs-target="#editBayiModal" title="Düzenle"><i class="fas fa-edit"></i></a>';
                $viewButton = '<a href="javascript:void(0);" data-bs-id="'.$row->user_id.'" class="btn btn-outline-primary btn-sm editBayi mobilBtn mbuton1" data-bs-toggle="modal" data-bs-target="#editBayiModal" title="Düzenle"><i class="fas fa-eye"></i></a>';
                $deleteButton = '<a href="'.$deleteUrl.'" class="btn btn-outline-danger btn-sm mobilBtn" id="delete" title="Sil"><i class="fas fa-trash-alt"></i></a>';
                return $viewButton . ' ' . $editButton . ' ' . $deleteButton;
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function($w) use($request){
                        $search = $request->get('search');
                        $w->where('name', 'LIKE', "%$search%");
                    });
                }
            })
            ->rawColumns(['user_id','name','grup','tel','address','status','action'])
            ->make(true);
    }

    return response()->json(['error' => 'Yetkisiz erişim'], 403);
}




}
