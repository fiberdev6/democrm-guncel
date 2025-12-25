<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HomeSlide;
use Image;

class HomeSliderController extends Controller
{
    public function HomeImage(Request $request) {
        $homeimage = HomeSlide::latest()->get();
        return view('backend.slider.home_image_all',compact('homeimage'));
    }

    public function AddSlide(){
        return view('backend.slider.home_slide_add');
    }

    public function StoreSlide(Request $request){
        $validateData = $request->validate([
            'home_image'=> 'max:2000',
        ]);
        $image = $request->file('home_image');
        $extension = $request->file('home_image')->extension();
        if($extension != "jpg" && $extension != "png" && $extension != "jpeg"){
            $notification = array(
                'message' => ' Dosya uzantısı sadece jpg,png,jpeg olmalı',
                'alert-type' => 'warning'
            );
            return redirect()->back()->with($notification);
        }
        $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();

        Image::make($image)->save('upload/home_slide/'.$name_gen);
        $save_url = 'upload/home_slide/'.$name_gen;

        HomeSlide::insert([
            'title' => $request->title,
            'sub_title' => $request->sub_title,
            'slide_no' => $request->slide_no,
            'home_image' => $save_url,
        ]);

        $notification = array(
            'message' => 'Slide Başarıyla Eklendi',
            'alert-type' => 'success'
        );

        return redirect()->route('home.image')->with($notification);
    }

    public function EditSlide($id){
        $slide_id = HomeSlide::findOrFail($id);
        return view('backend.slider.edit_slide',compact('slide_id'));
    }

    public function UpdateSlide(Request $request) {
        $validateData = $request->validate([
            'home_image'=> 'max:2000',
        ]);
        $slide_id = $request->id;
        if($request->file('home_image')) {
            $image = $request->file('home_image');
            $extension = $request->file('home_image')->extension();
            if($extension != "jpg" && $extension != "png" && $extension != "jpeg"){
                $notification = array(
                    'message' => ' Dosya uzantısı sadece jpg,png,jpeg olmalı',
                    'alert-type' => 'warning'
                );
                return redirect()->back()->with($notification);
            }
            $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();  //3434434.jpg şeklinde resmi adlandıracak burası

            Image::make($image)->save('upload/home_slide/'.$name_gen);
            $save_url = 'upload/home_slide/'.$name_gen;

            HomeSlide::findOrFail($slide_id)->update([
                'title' => $request->title,
                'sub_title' => $request->sub_title,
                'slide_no' => $request->slide_no,
                'home_image' => $save_url,
            ]);
            $notification = array(
                'message' => 'Slide Başarıyla Güncellendi',
                'alert-type' => 'success'
              );
        return redirect()->route('home.image')->with($notification);
        }
        else {
            HomeSlide::findOrFail($slide_id)->update([
                'title' => $request->title,
                'sub_title' => $request->sub_title,
                'slide_no' => $request->slide_no,
            ]);
            $notification = array(
                'message' => 'Slide Başarıyla Güncellendi',
                'alert-type' => 'success'
              );
        return redirect()->route('home.image')->with($notification);

        }

    }

    public function DeleteSlide($id) {
        $slide_id = HomeSlide::findOrFail($id);
        $img = $slide_id->home_image;
        unlink($img);

        HomeSlide::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Slide Başarıyla Silindi',
            'alert-type' => 'success'
        );

        return redirect()->route('home.image')->with($notification);
    }

}
