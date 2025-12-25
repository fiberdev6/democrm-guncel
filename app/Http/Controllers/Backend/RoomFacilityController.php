<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RoomFacility;
use App\Models\Room;
use Image;

class RoomFacilityController extends Controller
{
    public function AllRoomFacility() {
        $room_facility = RoomFacility::all();
        return view('backend.room_facility.all_facility', compact('room_facility'));
    }

    public function AddRoomFacility() {
        $rooms = Room::orderBy('title', 'ASC')->get();
        return view('backend.room_facility.add_facility', compact('rooms'));
    }

    public function StoreRoomFacility(Request $request) {
        $image = $request->file('image');
        $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();

        Image::make($image)->save('upload/room_facility/'.$name_gen);
        $save_url = 'upload/room_facility/'.$name_gen;

        RoomFacility::insert([
            'room_id' => $request->room,
            'name' => $request->name,
            'image' => $save_url,
        ]);

        $notification = array(
            'message' => 'Oda Ayrıcalığı Başarıyla Eklendi',
            'alert-type' => 'success'
        );

        return redirect()->route('all.room.facility')->with($notification);
    }

    public function EditRoomFacility($id) {
        $room_facility_id = RoomFacility::findOrFail($id);
        $rooms = Room::orderBy('title', 'ASC')->get();
        return view('backend.room_facility.edit_facility', compact('room_facility_id', 'rooms'));
    }

    public function UpdateRoomFacility(Request $request) {
        $room_facility_id = $request->id;
        if($request->file('image')){
            $image = $request->file('image');
            $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();

            Image::make($image)->save('upload/room_facility/'.$name_gen);
            $save_url = 'upload/room_facility/'.$name_gen;

            RoomFacility::findOrFail($room_facility_id)->update([
                'room_id' => $request->room,
                'name' => $request->name,
                'image' => $save_url,
            ]);
            $notification = array(
                'message' => 'Oda Ayrıcalıkları Başarıyla Güncellendi',
                'alert-type' => 'success'
            );
            return redirect()->route('all.room.facility')->with($notification);
        }

        else {
            RoomFacility::findOrFail($room_facility_id)->update([
                'room_id' => $request->room,
                'name' => $request->name,
            ]);
            $notification = array(
                'message' => 'Oda Ayrıcalıkları Başarıyla Güncellendi',
                'alert-type' => 'success'
              );
        return redirect()->route('all.room.facility')->with($notification);

        }
    }

    public function DeleteRoomFacility($id) {
        $room_facility_id = RoomFacility::findOrFail($id);
        $img = $room_facility_id->image;
        unlink($img);

        RoomFacility::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Oda Ayrıntıları Başarıyla Silindi',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
}
