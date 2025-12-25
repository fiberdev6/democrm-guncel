<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Faq;
use Image;

class FaqController extends Controller
{
    public function AllFaq() {
        $faqs = Faq::find(1);
        return view('backend.faq.all_faq', compact('faqs'));
    }

    public function AddFaq() {
        return view('backend.faq.add_faq');
    }

    public function StoreFaq(Request $request) {
        Faq::insert([
            'question' => $request->question,
            'description' => $request->description
        ]);

        $notification = array(
            'message' => 'Anasayfa Sectionu Eklendi',
            'alert-type' => 'success'
        );

        return redirect()->route('all.faq')->with($notification);
    }

    public function EditFaq($id) {
        $faq_id = Faq::findOrFail($id);
        return view('backend.faq.edit_faq', compact('faq_id'));
    }

    public function UpdateFaq(Request $request) {      
        $validateData = $request->validate([
            'image'=> 'max:2000',
        ]);
        $faq_id = $request->id;
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

            Image::make($image)->save('upload/section_image/' . $name_gen);
            $save_url = 'upload/section_image/' . $name_gen;

            Faq::findOrFail($faq_id)->update([
                'question' => $request->question,
                'description' => $request->description,
                'image' => $save_url,
            ]);

            $notification = array(
                'message' => 'Anasayfa Section Başarıyla Güncellendi',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }
        else {
            Faq::findOrFail($faq_id)->update([
                'question' => $request->question,
                'description' => $request->description,
            ]);

            $notification = array(
                'message' => 'Anasayfa Section Başarıyla Güncellendi',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }
    }

    // public function DeleteFaq($id) {
    //     Faq::findOrFail($id)->delete();

    //     $notification = array(
    //         'message' => 'Sık Sorulan Sorular Başarıyla Silindi',
    //         'alert-type' => 'success'
    //     );

    //     return redirect()->back()->with($notification);
    // }
}
