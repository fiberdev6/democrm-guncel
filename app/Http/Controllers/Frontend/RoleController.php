<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function AllPermission($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }
        $permissions = Permission::orderBy('id','desc')->get();
        return view('frontend.secure.permission.all_permission',compact('permissions','firma'));
    }

    public function AddPermission($tenant_id){
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }
        return view('frontend.secure.permission.add_permission',compact('firma'));

    }

    public function StorePermission(Request $request, $tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }
        $permission = Permission::create([
            'name' => $request->name,
            'group_name' => $request->group_name,
        ]);

        $createdPermission = Permission::find($permission->id);
        return response()->json($createdPermission);
    }

    public function EditPermission($tenant_id,$id){
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }
        $permissions = Permission::findOrFail($id);
        return view('frontend.secure.permission.edit_permission',compact('permissions','tenant_id', 'firma'));
    }

    public function UpdatePermission(Request $request, $tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }
        $permission_id = $request->id;
        Permission::findOrFail($permission_id)->update([
            'name' => $request->name,
            'group_name' => $request->group_name,
        ]);
        $updatedPermission = Permission::find($permission_id);
        return response()->json($updatedPermission);
    }

    public function DeletePermission($tenant_id,$id){
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }
        $permission = Permission::find($id);
        if($permission) {
            $permission->delete();
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'message' => 'İzin bulunamadı.']);
        }
    }

    //All Roles Methods

    public function AllRoles($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }
        $roles = Role::where('name','!=', 'Admin')->orderBy('name', 'ASC')->get();
        return view('frontend.secure.roles.all_roles',compact('roles','firma'));
    }

    public function AddRoles($tenant_id){
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }
        return view('frontend.secure.roles.add_roles',compact('firma'));
    }

    public function StoreRoles(Request $request, $tenant_id){
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }
        $roles = Role::create([
            'name' => $request->name,
        ]);

        $createdRole = Role::find($roles->id);
        return response()->json($createdRole);
    }

    public function EditRoles($tenant_id,$id){
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }
        $roles = Role::findOrFail($id);
        $permissions = Permission::all();
        $rolePermissions = $roles->permissions->pluck('id')->toArray();

        $groupedPermissions = $permissions->groupBy('group_name'); // İzinleri grup adına göre ayır

        $selectedPermissions = $roles->permissions->pluck('id')->toArray(); // Seçili izinleri alın

        return view('frontend.secure.roles.edit_roles',compact('roles','permissions','rolePermissions','groupedPermissions', 'selectedPermissions','firma'));
    }

    public function UpdateRoles(Request $request, $tenant_id){
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }
       // Validasyon
       $request->validate([
            'name' => 'required|string|max:255',
            'permission' => 'required|array',
            'permission.*' => 'exists:permissions,id',
        ]);

        try {
            // Rol güncelle
            $role = Role::findOrFail($request->id);
            $role->update([
                'name' => $request->name,
            ]);

            // İzinleri güncelle
            $permissionIds = $request->permission;
            if (!empty($permissionIds)) {
                $permissionNames = \Spatie\Permission\Models\Permission::whereIn('id', $permissionIds)
                    ->pluck('name')
                    ->toArray();
                $role->syncPermissions($permissionNames);
            }
        
            return redirect()->back()->with('success', 'Rol başarıyla güncellendi');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Hata: ' . $e->getMessage())->withInput();
        }

        // Güncellenmiş rolü döndür
        return response()->json($role);
    }

    public function DeleteRoles($tenant_id,$id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }
        $role = Role::find($id);
        if($role) {
            $role->delete();
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'message' => 'Rol bulunamadı.']);
        }
    }

    //Add Role Permission All Method

    public function AddRolesPermission($tenant_id){
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }
        $roles = Role::all();
        $permissions = Permission::all();
        $permission_groups= User::getpermissionGroups();
        return view('frontend.secure.rolesetup.add_roles_permission',compact('roles','permissions','permission_groups','firma'));
    }

    public function StoreRolesPermission(Request $request,$tenant_id){
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }
        $data = array();
        $permissions = $request->permission;

        foreach($permissions as $key => $item){
            $data['role_id'] = $request->role_id;
            $data['permission_id'] = $item;

            DB::table('role_has_permissions')->insert($data);
        }

        $notification = array(
            'message' => 'Rollere izinler Başarıyla Eklendi',
            'alert-type' => 'success'
        );

        return response()->json($notification);
    }

    public function AllRolesPermission($tenant_id){
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }
        $roles = Role::orderBy('id','desc')->get();
        return view('frontend.secure.rolesetup.all_roles_permission',compact('roles','firma'));
    }

    public function EditRolesPermission($tenant_id,$id){
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }
        $role = Role::findOrFail($id);
        $permissions = Permission::all();
        $permission_groups= User::getpermissionGroups();
        return view('frontend.secure.rolesetup.edit_roles_permission',compact('role','permissions','permission_groups','firma'));
    }

    public function UpdateRolesPermission(Request $request,$id, $tenant_id){
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }
        $role = Role::findOrFail($id);
        $permissions = $request->permission;

        if(!empty($permissions)){
            $role->syncPermissions($permissions);
        }

        $notification = array(
            'message' => 'Rollere izinler Başarıyla Güncellendi',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);

    }

    public function DeleteRolesPermission($tenant_id,$id){
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı!',
                'alert-type' => 'danger'
            );
            return redirect()->back()->with($notification);
        }
        $role = Role::findOrFail($id);
        if(!is_null($role)){
            $role->delete();
        }
        $notification = array(
            'message' => 'Rollere izinler Başarıyla Silindi',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
}
