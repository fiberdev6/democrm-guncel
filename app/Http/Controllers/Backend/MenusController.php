<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Models\Menus;

class MenusController extends Controller
{
    public function AllMenus(){

        $menus = Menus::get();
        return view('backend.menus.all_menus',compact('menus'));
    }

    public function AddMenus(){

        return view('backend.menus.add_menus');
    }

    public function StoreMenus(Request $request) {

        Menus::create([
            'name' => $request->name,
            'link' => $request->link,
            'status' => $request->status,
            'sira' => $request->sira, 
        ]);
        $notification = array(
            'message' => 'Menu Başarıyla Eklendi',
            'alert-type' => 'success'
        );

        return redirect()->route('all.menus')->with($notification);
    }

    public function EditMenus($id) {

        $menus_id = Menus::findOrFail($id);
        return view('backend.menus.edit_menus',compact('menus_id'));
    }

    public function UpdateMenus(Request $request,$id){

        Menus::findOrFail($id)->update([
            'name' => $request->name,
            'link' => $request->link,
            'status' => $request->status,
            'sira' => $request->sira,
        ]);

        $notification = array(
            'message' => 'Menu Başarıyla Güncellendi',
            'alert-type' => 'success'
        );

        return redirect()->route('all.menus')->with($notification);
    }

    public function DeleteMenus($id) {

        Menus::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Menu Başarıyla Silindi',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
}
