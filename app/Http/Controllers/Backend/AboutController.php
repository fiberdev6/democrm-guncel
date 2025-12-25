<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\About;
use Image;

class AboutController extends Controller
{
    public function AllAbout() {
        $all_about = About::find(1);
        return view('backend.about.all_about', compact('all_about'));
    }

    public function UpdateAbout(Request $request) {
        $validateData = $request->validate([
            'image'=> 'max:2000',
        ]);
        $about_id = $request->id;
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

            Image::make($image)->save('upload/about_image/' . $name_gen);
            $save_url = 'upload/about_image/' . $name_gen;

            About::findOrFail($about_id)->update([
                'title' => $request->title,
                'description' => $request->description,
                'image' => $save_url,
            ]);

            $notification = array(
                'message' => 'Site Ayarları Başarıyla Güncellendi',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }
        else {
            About::findOrFail($about_id)->update([
                'title' => $request->title,
                'description' => $request->description,
            ]);

            $notification = array(
                'message' => 'Site Ayarları Başarıyla Güncellendi',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }
        
       
    }
}
