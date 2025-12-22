<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use Illuminate\Support\Facades\Hash;





class AdminController extends Controller
{
  public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $notification = array(
          'message' => 'Başarıyla Çıkış Yapıldı',
          'alert-type' => 'success'
        );

        return redirect('/login')->with($notification);


    } //end method

    public function Profile() {  //burada idsini veritabanından aldığımız adminin profil sayfasına gitmesi için bi fonksiyon atadık
      $id = Auth::user()->user_id;    //hangi id girdiyse admin panele onun idsini bulur
      $adminData = User::find($id); //bulduğunu adminData ya atar
      return view('backend.admin_profile_view', compact('adminData')); //oluşturduğumuz admin_profile_viewi compact ile karşılaştırıp ile adminin profilini gösterir
    }//end method

    public function EditProfile() {
      $id = Auth::user()->user_id;
      $editData = User::find($id);
      return view('backend.admin_profile_edit', compact('editData'));

    }
    
    public function StoreProfile(Request $request) {
      $id = Auth::user()->user_id;  //burada database deki name username email i $data değişkenine attık kullanıcı edit profile sayfasında adını mailini kullancı adını yazdıığında güncelleyecek
      $data = User::find($id);
      $data->name = $request->name;
      $data->username = $request->username;

      if ($request->file('profile_image')) {
        $file = $request->file('profile_image'); //burada profil resmini oluşturduğumuz klasöre ve database e ekleyecek

        $filename = date('YmdHi').$file->getClientOriginalName();
        $file->move(public_path('upload/admin_images'),$filename);
        $data['profile_image']= $filename;
      }
      $data->save();
      $validateData = $request->validate([
        'newpassword'=> 'required',
        'confirm_password'=> 'required|same:newpassword',
      ]);

      $hashedPassword = Auth::user()->password;
      
        $users = User::find(Auth::id());
        $users->password = bcrypt($request->newpassword);
        $users->save();

        //session()->flash('message','password updated successfully');
        return redirect()->back();

      $notification = array(
        'message' => 'Admin Profili Başarıyla Güncellendi',
        'alert-type' => 'success'
      );


      return redirect()->route('backend.profile')->with($notification);

    }

    public function ChangePassword(){

      return view('backend.admin_change_password');
    }

    public function UpdatePassword(Request $request)
    {
      $validateData = $request->validate([
        'newpassword'=> 'required',
        'confirm_password'=> 'required|same:newpassword',
      ]);

      $hashedPassword = Auth::user()->password;
      
        $users = User::find(Auth::id());
        $users->password = bcrypt($request->newpassword);
        $users->save();

        session()->flash('message','password updated successfully');
        return redirect()->back();
      

      //burada database den alınan password ün girilen eskisiyle uyuşup uyuşmadığını control ediyoruz
    
    }
}