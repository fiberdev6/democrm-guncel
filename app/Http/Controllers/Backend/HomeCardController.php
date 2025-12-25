<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HomeCard;
use Image;

class HomeCardController extends Controller
{
    public function AllHomeCard(){

        $home_cards = HomeCard::latest()->get();
        return view('backend.home_card.home_card_all',compact('home_cards')); 

    }

    public function AddHomeCard(){

        return view('backend.home_card.home_card_add');
    }

     public function StoreHomeCard(Request $request){
        $validateData = $request->validate([
            'card_icon'=> 'max:2000',
        ]);
        $image = $request->file('card_icon');
        $extension = $request->file('card_icon')->extension();
        if($extension != "jpg" && $extension != "png" && $extension != "jpeg"){
            $notification = array(
                'message' => ' Dosya uzantısı sadece jpg,png,jpeg olmalı',
                'alert-type' => 'warning'
            );
            return redirect()->back()->with($notification);
        }
        $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();

        Image::make($image)->save('upload/home_icons/'.$name_gen);
        $save_url = 'upload/home_icons/'.$name_gen;
        HomeCard::insert([
            'card_title' => $request->card_title,
            'card_subtitle' => $request->card_subtitle,
            'card_icon' => $save_url,
        ]);

        $notification = array(
            'message' => 'Hizmet Başarıyla Eklendi',
            'alert-type' => 'success'
        );

        return redirect()->route('all.home.card')->with($notification);

     }

    public function EditHomeCard($id){

        $card_id = HomeCard::findOrFail($id);
         return view('backend.home_card.edit_home_card',compact('card_id'));
    }

    public function UpdateHomeCard(Request $request){
        
        $validateData = $request->validate([
            'card_icon'=> 'max:2000',
        ]);
        $card_id = $request->id;
        if($request->file('card_icon')) {
            $image = $request->file('card_icon');
            $extension = $request->file('card_icon')->extension();
            if($extension != "jpg" && $extension != "png" && $extension != "jpeg"){
                $notification = array(
                    'message' => ' Dosya uzantısı sadece jpg,png,jpeg olmalı',
                    'alert-type' => 'warning'
                );
                return redirect()->back()->with($notification);
            }
            $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();  //3434434.jpg şeklinde resmi adlandıracak burası

            Image::make($image)->save('upload/home_icons/'.$name_gen);
            $save_url = 'upload/home_icons/'.$name_gen;

            HomeCard::findOrFail($card_id)->update([
                'card_title' => $request->card_title,
                'card_subtitle' => $request->card_subtitle,
                'card_icon' => $save_url,
            ]);
            $notification = array(
                'message' => 'Hizmet Başarıyla Güncellendi',
                'alert-type' => 'success'
              );
        return redirect()->route('all.home.card')->with($notification);
        }
        else {
            HomeCard::findOrFail($card_id)->update([
                'card_title' => $request->card_title,
                'card_subtitle' => $request->card_subtitle,
            ]);
            $notification = array(
                'message' => 'Hizmet Başarıyla Güncellendi',
                'alert-type' => 'success'
              );
        return redirect()->route('all.home.card')->with($notification);

        }
        
    }

    public function DeleteHomeCard($id){
        $card_id = HomeCard::findOrFail($id);
        $img = $card_id->card_icon;
        unlink($img);

        HomeCard::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Hizmet Başarıyla Silindi',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
}
