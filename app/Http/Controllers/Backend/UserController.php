<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function register()
    {
        return view('backend.user.register');
    }

    public function register_action(Request $request)
    {   $request->validate([
        'name' => 'required',
        'username' => 'required|unique:tb_user',
        'password' => 'required',
        'password_confirmation' => 'required|same:password',
    ]);
    $user = new User([
        'name' => $request->name,
        'username' => $request->username,
        'password' => Hash::make($request->password),

    ]);
    $user->save();
    return redirect()->back()->with('success', 'Registration Success,Please Login!');
    }


    public function login()
    {
        return view('backend.user.login');
    }

    public function login_action(Request $request){
        $request->validate([
            
            'username' => 'required',
            
            'password' => 'required',
        ]);

        if (auth()->attempt(['username' => $request->username,'password' => $request->password])) {
            
            $request->session('user_id');
            $notification= array(
                'message' => 'Başarıyla Giriş Yaptınız',
                'alert-type' => 'success'
            );
            return redirect()->route('dashboard')->with($notification);
        }
        else
        {
            $notification= array(
                'message' => 'Kullanıcı adı ve şifre hatalı',
                'alert-type' => 'error'
            );
            
            return redirect()->route('login')->with($notification);
        }
       
}
}