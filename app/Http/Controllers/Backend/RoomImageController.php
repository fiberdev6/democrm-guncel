<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RoomImage;
use App\Models\Room;
use Image;
use Illuminate\Support\Carbon;


class RoomImageController extends Controller
{
    public function AllRoomImage($id) {
        $room_images = RoomImage::where('room_id', '=', $id)->get();
        $rooms = Room::where('id', '=', $id)->first();
        return view('backend.project_images.all_room_images', compact('room_images', 'rooms'));
    }

    public function AddRoomImage($id) {
        $rooms = Room::orderBy('title', 'ASC')->get();
        $odalar = Room::where('id', '=', $id)->first();
        return view('backend.project_images.add_room_images', compact('rooms', 'odalar'));
    }

    public function StoreRoomImage(Request $request) {
        $validateData = $request->validate([
            'room_images.*'=> 'max:2000',
        ]);
        $image = $request->file('room_images');

        foreach ($image as $room_image) {
            $name_gen = hexdec(uniqid()).'.'.$room_image->getClientOriginalExtension();
            $watermark = public_path('frontend/img/watermark.png');
            $room_image = Image::make($room_image)->save('upload/project_images/'.$name_gen);
            // $room_image->insert($watermark, 'bottom-right',5,5)->save('upload/project_images/'.$name_gen);
            $save_url = 'upload/project_images/'.$name_gen;

            RoomImage::insert([
                'room_id' => $request->room,
                'room_images' => $save_url,
                'created_at' => Carbon::now()
            ]);
        } //end of the foreach

        $notification = array(
            'message' => 'Oda Resmi Başarıyla Eklendi',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }

    public function EditRoomImage($id) {
        $room_image_id = RoomImage::findOrFail($id);
        $rooms = Room::orderBy('title', 'ASC')->get();
        return view('backend.project_images.edit_room_images', compact('room_image_id', 'rooms'));
    }

    public function UpdateRoomImage(Request $request) {
        $validateData = $request->validate([
            'room_images'=> 'max:2048',
        ]);
        $room_image_id = $request->id;
        if($request->file('room_images')){
            $image = $request->file('room_images');
            
            $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
            $watermark = public_path('frontend/img/watermark.png');
            $image = Image::make($image)->save('upload/project_images/'.$name_gen);
            // $image->insert($watermark, 'bottom-right',5,5)->save('upload/project_images/'.$name_gen);
            $save_url = 'upload/project_images/'.$name_gen;

            RoomImage::findOrFail($room_image_id)->update([
                'room_id' => $request->room,
                'room_images' => $save_url,
            ]);
            $notification = array(
                'message' => 'Oda Resimleri Başarıyla Güncellendi',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }
    }

    public function DeleteRoomImage($id) {
        $room_image_id = RoomImage::findOrFail($id);
        $img = $room_image_id->room_images;
        unlink($img);

        RoomImage::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Oda Resmi Başarıyla Silindi',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
}
