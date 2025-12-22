<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use Illuminate\Http\Request;
use Image;

class FeaturesController extends Controller
{
    public function AllFeatures(Request $request) {
        $features = Feature::latest()->get();
        return view('backend.features.all_features',compact('features'));
    }

    public function AddFeatures() {
        return view('backend.features.add_features');
    }

    public function StoreFeatures(Request $request){
        $validateData = $request->validate([
            'image'=> 'max:2000',
        ]);
        $image = $request->file('image');
        $extension = $request->file('image')->extension();
        if($extension != "jpg" && $extension != "png" && $extension != "jpeg"){
            $notification = array(
                'message' => ' Dosya uzantısı sadece jpg,png,jpeg olmalı',
                'alert-type' => 'warning'
            );
            return redirect()->back()->with($notification);
        }
        $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();

        Image::make($image)->save('upload/features/'.$name_gen);
        $save_url = 'upload/features/'.$name_gen;

        Feature::insert([
            'title' => $request->title,
            'slug' => $request->slug,
            'description' => $request->description,
            'sira' => $request->sira,
            'image' => $save_url,
        ]);

        $notification = array(
            'message' => 'Özellik Başarıyla Eklendi',
            'alert-type' => 'success'
        );

        return redirect()->route('all.features')->with($notification);
    }

    public function EditFeatures($id) {
        $features_id = Feature::findOrFail($id);
        return view('backend.features.edit_features', compact('features_id'));
    }

    public function UpdateFeatures(Request $request) {
        $validateData = $request->validate([
            'image'=> 'max:2000',
        ]);
        $features_id = $request->id;
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
            $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();  //3434434.jpg şeklinde resmi adlandıracak burası

            Image::make($image)->save('upload/categories/'.$name_gen);
            $save_url = 'upload/categories/'.$name_gen;

            Feature::findOrFail($features_id)->update([
                'title' => $request->title,
                'slug' => $request->slug,
                'description' => $request->description,
                'sira' => $request->sira,
                'image' => $save_url,
            ]);
            $notification = array(
                'message' => 'Program Özellikleri Başarıyla Güncellendi',
                'alert-type' => 'success'
              );
        return redirect()->route('all.features')->with($notification);
        }
        else {
            Feature::findOrFail($features_id)->update([
                'title' => $request->title,
                'slug' => $request->slug,
                'description' => $request->description,
                'sira' => $request->sira,
            ]);
            $notification = array(
                'message' => 'Program Özellikleri Başarıyla Güncellendi',
                'alert-type' => 'success'
              );
        return redirect()->route('all.features')->with($notification);

        }
    }

    public function DeleteFeatures($id) {
        $features_id = Feature::findOrFail($id);
        $img = $features_id->image;
        unlink($img);

        Feature::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Özellik Başarıyla Silindi',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
}
