<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use Image;
use App\Models\Category;

class RoomController extends Controller
{
    public function AllRoom(Request $request) {
        $rooms = Room::with('categori')->latest()->get();
        return view('backend.projects.rooms_all',compact('rooms'));
    }

    public function AddRoom() {
        $categories = Category::orderBy('title', 'ASC')->get();
        // $cate= Category::where('id', '=', $id)->first();
        return view('backend.projects.rooms_add', compact('categories'));
    }

    public function StoreRoom(Request $request){
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

        Image::make($image)->save('upload/projects/'.$name_gen);
        $save_url = 'upload/projects/'.$name_gen;

        Room::insert([
            'title' => $request->title,
            'slug' => $request->slug,
            'description' => $request->description,
            'kod' => $request->kod,
            'category' => $request->category,
            'image' => $save_url,
        ]);

        $notification = array(
            'message' => 'Ürün Başarıyla Eklendi',
            'alert-type' => 'success'
        );

        return redirect()->route('all.room')->with($notification);
    }

    public function EditRoom($id) {
        $room_id = Room::findOrFail($id);
        $categories = Category::orderBy('title', 'ASC')->get();
        return view('backend.projects.rooms_edit', compact('room_id','categories'));
    }

    public function UpdateRoom(Request $request) {
        $validateData = $request->validate([
            'image'=> 'max:2000',
        ]);
        $room_id = $request->id;
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

            Image::make($image)->save('upload/projects/'.$name_gen);
            $save_url = 'upload/projects/'.$name_gen;

            Room::findOrFail($room_id)->update([
                'title' => $request->title,
                'slug' => $request->slug,
                'description' => $request->description,
                'kod' => $request->kod,
                'category' => $request->category,
                'image' => $save_url,
            ]);
            $notification = array(
                'message' => 'Ürün Bilgileri Başarıyla Güncellendi',
                'alert-type' => 'success'
              );
        return redirect()->route('all.room')->with($notification);
        }
        else {
            Room::findOrFail($room_id)->update([
                'title' => $request->title,
                'slug' => $request->slug,
                'description' => $request->description,
                'kod' => $request->kod,
                'category' => $request->category,
            ]);
            $notification = array(
                'message' => 'Ürün Bilgileri Başarıyla Güncellendi',
                'alert-type' => 'success'
              );
        return redirect()->route('all.room')->with($notification);

        }
    }

    public function DeleteRoom($id) {
        $room_id = Room::findOrFail($id);
        $img = $room_id->image;
        unlink($img);

        Room::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Ürün Başarıyla Silindi',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
}
