<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OurDocument;
use Image;

class DocumentsController extends Controller
{
    public function AllDocuments(){
        $documents = OurDocument::get();
        $docu = OurDocument::first();
        return view('backend.documents.all_documents', compact('docu','documents'));
    }

    public function UpdateDocuments(Request $request) {
        $validateData = $request->validate([
            'files' => 'mimes:doc,docx,xlx,csv,pdf|max:1024',
            'image' => 'max:2000',
        ]);
        $pdf = $request->file('file');
        if($pdf) {
            $fileName = time().'.'.$pdf->getClientOriginalExtension();  
            $save_url = $pdf->move('upload/uploads', $fileName);
        }

        $image = $request->file('image');
        if($image){
            $extension = $request->file('image')->extension();
            if($extension != "jpg" && $extension != "png" && $extension != "jpeg"){
                $notification = array(
                    'message' => ' Dosya uzantısı sadece jpg,png,jpeg olmalı',
                    'alert-type' => 'warning'
                );
                return redirect()->back()->with($notification);
            }
            $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
            Image::make($image)->save('upload/belge_img/'.$name_gen);
            $save_url2 = 'upload/belge_img/'.$name_gen;
        }

       
        OurDocument::insert([
            'title' => $request->title,
            'files' => $save_url,
        ]);

        $notification = array(
            'message' => 'Belge başarıyla yüklendi',
            'alert-type' => 'success',
        );
        return redirect()->back()->with($notification)->with('files',$fileName);;
    }

    public function DeleteDocuments($id){
       

        OurDocument::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Belge Başarıyla Silindi',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
}
