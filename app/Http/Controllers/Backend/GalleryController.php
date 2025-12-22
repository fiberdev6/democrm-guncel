<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gallery;
use Image;

class GalleryController extends Controller
{

    public function AddGallery() {
        $allGalleryImage = Gallery::all();
        return view('backend.gallery.add_gallery_image', compact('allGalleryImage'));
    }

    //çoklu resim ekleme metodu
    public function StoreGallery(Request $request){
        
        if($request->file('file') !== null){
            $image = $request->file('file');
           
            $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();

            Image::make($image)->save('upload/gallery/'.$name_gen);
            $save_url = 'upload/gallery/'.$name_gen;

            $imageUpload = new Gallery();
            $imageUpload->multi_image = $save_url;
            $imageUpload->save(); 
            return redirect()->route('add.gallery');  
        }

        $notification = array(
            'message' => 'Resim Başarıyla Eklendi',
            'alert-type' => 'success'
        );

        return redirect()->route('all.gallery')->with($notification);
   
    }

    public function StoreSort(Request $request) {
      
       Gallery::findOrFail($request->galeriId)->update([
        'sira' => $request->sira,
    ]);
    
    }

    public function EditGallery($id){

        $galleryImage = Gallery::findOrFail($id);
        return view('backend.gallery.edit_gallery_image',compact('galleryImage'));

    }

    public function UpdateGallery(Request $request) {
        $gallery_image_id = $request->id;

        if($request->file('multi_image')) {
            $image = $request->file('multi_image');
            $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();

            Image::make($image)->save('upload/gallery/'.$name_gen);
            $save_url = 'upload/gallery/'.$name_gen;

            Gallery::findOrFail($gallery_image_id)->update([
                'multi_image' => $save_url,
                'sira' => $request->sira,

            ]);
            $notification = array(
                'message' => 'Resim Başarıyla Güncellendi',
                'alert-type' => 'success'
            );
            return redirect()->route('all.gallery')->with($notification);
        }
    }

    public function DeleteGallery($id) {

        $multi_id = Gallery::findOrFail($id);
        $img = $multi_id->multi_image;
        unlink($img);

        Gallery::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Resim Başarıyla Silindi',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }
}
