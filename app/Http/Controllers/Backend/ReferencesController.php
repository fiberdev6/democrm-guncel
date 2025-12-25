<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reference;
use Image;

class ReferencesController extends Controller
{
    public function AllReferences() {
        $references = Reference::all();
        return view('backend.references.all_references', compact('references'));
    }

    public function AddReferences() {
        $references = Reference::all();
        return view('backend.references.add_references', compact('references'));
    }

    public function StoreReferences(Request $request) {
        
        if($request->file('file') !== null){
            $image = $request->file('file');
            $extension = $request->file('file')->extension();
            if($extension != "jpg" && $extension != "png" && $extension != "jpeg"){
                $notification = array(
                    'message' => ' Dosya uzantısı sadece jpg,png,jpeg olmalı',
                    'alert-type' => 'warning'
                );
                return redirect()->back()->with($notification);
            }
            $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();

            Image::make($image)->save('upload/references/'.$name_gen);
            $save_url = 'upload/references/'.$name_gen;

            $imageUpload = new Reference();
            $imageUpload->logo = $save_url;
            $imageUpload->save(); 
            return redirect()->route('add.references');         
        }

        $notification = array(
            'message' => 'Resim Başarıyla Eklendi',
            'alert-type' => 'success'
        );

        return redirect()->route('all.gallery')->with($notification);

    }

    public function StoreReferencesSort(Request $request) {
        Reference::findOrFail($request->galeriId)->update([
            'sira' => $request->sira,
        ]);
    }

    public function EditReferences($id) {
        $reference_id = Reference::findOrFail($id);
        return view('backend.references.edit_references', compact('reference_id'));
    }

    public function UpdateReferences(Request $request) {
        $reference_id = $request->id;
        $image = $request->file('logo');
        $extension = $request->file('file')->extension();
        if($extension != "jpg" && $extension != "png" && $extension != "jpeg"){
            $notification = array(
                'message' => ' Dosya uzantısı sadece jpg,png,jpeg olmalı',
                'alert-type' => 'warning'
            );
            return redirect()->back()->with($notification);
        }
        $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();  //3434434.jpg şeklinde resmi adlandıracak burası

        Image::make($image)->save('upload/references/'.$name_gen);
        $save_url = 'upload/references/'.$name_gen;

        Reference::findOrFail($reference_id)->update([
            'logo' => $save_url,
        ]);

        $notification = array(
            'message' => 'Referanslar Başarıyla Güncellendi',
            'alert-type' => 'success'
        );

        return redirect()->route('all.references')->with($notification);
    }

    public function DeleteReferences($id) {
        $reference_id = Reference::findOrFail($id);
        $img = $reference_id->logo;
        unlink($img);

        Reference::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Referans Başarıyla Silindi',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
}
