<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Misyon;
use Image;

class MisyonController extends Controller
{
    public function AllMisyon() {
        $all_misyon = Misyon::find(1);
        return view('backend.misyon.misyon', compact('all_misyon'));
    }

    public function UpdateMisyon(Request $request) {
        $validateData = $request->validate([
            'image'=> 'max:2000',
        ]);
        $misyon_id = $request->id;
        if($request->file('image')) {
            $image = $request->file('image');
            $extension = $request->file('image')->extension();
            if($extension != "jpg" && $extension != "png" && $extension != "jpeg"){
                $notification = array(
                    'message' => ' Dosya uzantısı sadece jpg,png,jpeg olmalı',
                    'alert-type' => 'warning'
                );
                return redirect()->back()->with($notification);
            }

            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();

            Image::make($image)->save('upload/misyon_image/' . $name_gen);
            $save_url = 'upload/misyon_image/' . $name_gen;

            Misyon::findOrFail($misyon_id)->update([
                'title' => $request->title,
                'description' => $request->description,
                'image' => $save_url,
            ]);

            $notification = array(
                'message' => 'Misyonumuz Başarıyla Güncellendi',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }
        else {
            Misyon::findOrFail($misyon_id)->update([
                'title' => $request->title,
                'description' => $request->description,
            ]);

            $notification = array(
                'message' => 'Misyonumuz Başarıyla Güncellendi',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }
    }
}
