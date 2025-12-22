<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Models\PageHome;
use App\Models\PageAbout;
use App\Models\PageRoom;
use App\Models\PageGallery;
use App\Models\PageContact;
use Image;
use App\Models\PageProduct;
use App\Models\PageMisyon;
use App\Models\PageReference;

class PagesController extends Controller
{
    public function Pages(){
        return view('backend.pages');
    }

    public function PagesHome(){
        $pageshome = PageHome::find(1);
        return view('backend.pages.home_pages',compact('pageshome'));
    }

    public function UpdatePagesHome(Request $request) {
        $home_id = $request->id;

        PageHome::findOrFail($home_id)->update([
            'title' => $request->title,
            'description' => $request->description,
            'keywords' => $request->keywords,

        ]);
        $notification = array(
            'message' => 'Anasayfa Başarıyla Güncellendi',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }

    public function PagesAbout(){
        $pagesabout = PageAbout::find(1);
        return view('backend.pages.about_pages',compact('pagesabout'));
    }

    public function UpdatePagesAbout(Request $request) {
        $validateData = $request->validate([
            'page_banner'=> 'max:2000',
        ]);
        $about_id = $request->id;

        if($request->file('page_banner')){
            $image = $request->file('page_banner');
            $extension = $request->file('page_banner')->extension();
            if($extension != "jpg" && $extension != "png" && $extension != "jpeg"){
                $notification = array(
                    'message' => ' Dosya uzantısı sadece jpg,png,jpeg olmalı',
                    'alert-type' => 'warning'
                );
                return redirect()->back()->with($notification);
            }
            $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
        
            Image::make($image)->save('upload/pages/'.$name_gen);
            $save_url = 'upload/pages/'.$name_gen;

            PageAbout::findOrFail($about_id)->update([
                'title' => $request->title,
                'description' => $request->description,
                'keywords' => $request->keywords,
                'page_banner' => $save_url,
                
            ]);

            $notification = array(
                'message' => 'Hakkımızda Sayfası Başarıyla Güncellendi',
                'alert-type' => 'success'
            );

            return redirect()->back()->with($notification);

        }
        else {
            PageAbout::findOrFail($about_id)->update([
                'title' => $request->title,
                'description' => $request->description,
                'keywords' => $request->keywords,
                
            ]);

            $notification = array(
                'message' => 'Hakkımızda Sayfası Başarıyla Güncellendi',
                'alert-type' => 'success'
            );

            return redirect()->back()->with($notification);
        }

    }

    public function PagesRooms(){
        $pagesrooms = PageRoom::find(1);
        return view('backend.pages.room_pages', compact('pagesrooms'));
    }

    public function UpdatePagesRoom(Request $request){
        $validateData = $request->validate([
            'page_banner'=> 'max:2000',
        ]);
        $room_id = $request->id;
        if($request->file('page_banner')){
            $image = $request->file('page_banner');
            $extension = $request->file('page_banner')->extension();
            if($extension != "jpg" && $extension != "png" && $extension != "jpeg"){
                $notification = array(
                    'message' => ' Dosya uzantısı sadece jpg,png,jpeg olmalı',
                    'alert-type' => 'warning'
                );
                return redirect()->back()->with($notification);
            }
            $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
            Image::make($image)->save('upload/pages/'.$name_gen);
            $save_url = 'upload/pages/'.$name_gen;

            PageRoom::findOrFail($room_id)->update([
                'title' => $request->title,
                'description' => $request->description,
                'keywords' => $request->keywords,
                'page_banner' => $save_url,
            ]);
            $notification = array(
                'message' => 'Odalar Sayfası Başarıyla Güncellendi',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }
        else{
            PageRoom::findOrFail($room_id)->update([
                'title' => $request->title,
                'description' => $request->description,
                'keywords' => $request->keywords,
            ]);
            $notification = array(
                'message' => 'Odalar Sayfası Başarıyla Güncellendi',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }
    }

    

    public function PagesGallery(){
        $pagesgallery = PageGallery::find(1);
        return view('backend.pages.gallery_pages', compact('pagesgallery'));
    }

    public function UpdatePagesGallery(Request $request){
        $validateData = $request->validate([
            'page_banner'=> 'max:2000',
        ]);
        $gallery_id = $request->id;
        if($request->file('page_banner')){
            $image = $request->file('page_banner');
            $extension = $request->file('page_banner')->extension();
            if($extension != "jpg" && $extension != "png" && $extension != "jpeg"){
                $notification = array(
                    'message' => ' Dosya uzantısı sadece jpg,png,jpeg olmalı',
                    'alert-type' => 'warning'
                );
                return redirect()->back()->with($notification);
            }
            $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
            Image::make($image)->save('upload/pages/'.$name_gen);
            $save_url = 'upload/pages/'.$name_gen;

            PageGallery::findOrFail($gallery_id)->update([
                'title' => $request->title,
                'description' => $request->description,
                'keywords' => $request->keywords,
                'page_banner' => $save_url,
            ]);
            $notification = array(
                'message' => 'İletişim Sayfası Başarıyla Güncellendi',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }
        else{
            PageGallery::findOrFail($gallery_id)->update([
                'title' => $request->title,
                'description' => $request->description,
                'keywords' => $request->keywords,
            ]);
            $notification = array(
                'message' => 'İletişim Sayfası Başarıyla Güncellendi',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }
    }

    public function PagesContact(){
        $pagescontact = PageContact::find(1);
        return view('backend.pages.contact_pages', compact('pagescontact'));
    }

    public function UpdatePagesContact(Request $request){
        $validateData = $request->validate([
            'page_banner'=> 'max:2000',
        ]);
        $contact_id = $request->id;
        if($request->file('page_banner')){
            $image = $request->file('page_banner');
            $extension = $request->file('page_banner')->extension();
            if($extension != "jpg" && $extension != "png" && $extension != "jpeg"){
                $notification = array(
                    'message' => ' Dosya uzantısı sadece jpg,png,jpeg olmalı',
                    'alert-type' => 'warning'
                );
                return redirect()->back()->with($notification);
            }
            $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
            Image::make($image)->save('upload/pages/'.$name_gen);
            $save_url = 'upload/pages/'.$name_gen;

            PageContact::findOrFail($contact_id)->update([
                'title' => $request->title,
                'description' => $request->description,
                'keywords' => $request->keywords,
                'page_banner' => $save_url,
            ]);
            $notification = array(
                'message' => 'İletişim Sayfası Başarıyla Güncellendi',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }
        else{
            PageContact::findOrFail($contact_id)->update([
                'title' => $request->title,
                'description' => $request->description,
                'keywords' => $request->keywords,
            ]);
            $notification = array(
                'message' => 'İletişim Sayfası Başarıyla Güncellendi',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }
    }

    public function PagesProducts(){
        $pageproducts = PageProduct::find(1);
        return view('backend.pages.products_page', compact('pageproducts'));
    }

    public function UpdatePagesProducts(Request $request){
        $validateData = $request->validate([
            'page_banner'=> 'max:2000',
        ]);

        $product_id = $request->id;
        if($request->file('page_banner')){
            $image = $request->file('page_banner');
            $extension = $request->file('page_banner')->extension();
            if($extension != "jpg" && $extension != "png" && $extension != "jpeg"){
                $notification = array(
                    'message' => ' Dosya uzantısı sadece jpg,png,jpeg olmalı',
                    'alert-type' => 'warning'
                );
                return redirect()->back()->with($notification);
            }
            $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
            Image::make($image)->save('upload/pages/'.$name_gen);
            $save_url = 'upload/pages/'.$name_gen;

            PageProduct::findOrFail($product_id)->update([
                'title' => $request->title,
                'description' => $request->description,
                'keywords' => $request->keywords,
                'page_banner' => $save_url,
            ]);
            $notification = array(
                'message' => 'Ürünler Sayfası Başarıyla Güncellendi',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }
        else{
            PageProduct::findOrFail($product_id)->update([
                'title' => $request->title,
                'description' => $request->description,
                'keywords' => $request->keywords,
            ]);
            $notification = array(
                'message' => 'Ürünler Sayfası Başarıyla Güncellendi',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }

    }

    public function PagesMisyon(){
        $pagemisyon = PageMisyon::find(1);
        return view('backend.pages.misyon_page', compact('pagemisyon'));
    }

    public function UpdatePagesMisyon(Request $request){
        $validateData = $request->validate([
            'page_banner'=> 'max:2000',
        ]);

        $misyon_id = $request->id;
        if($request->file('page_banner')){
            $image = $request->file('page_banner');
            $extension = $request->file('page_banner')->extension();
            if($extension != "jpg" && $extension != "png" && $extension != "jpeg"){
                $notification = array(
                    'message' => ' Dosya uzantısı sadece jpg,png,jpeg olmalı',
                    'alert-type' => 'warning'
                );
                return redirect()->back()->with($notification);
            }
            $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
            Image::make($image)->save('upload/pages/'.$name_gen);
            $save_url = 'upload/pages/'.$name_gen;

            PageMisyon::findOrFail($misyon_id)->update([
                'title' => $request->title,
                'description' => $request->description,
                'keywords' => $request->keywords,
                'page_banner' => $save_url,
            ]);
            $notification = array(
                'message' => 'Misyonumuz Sayfası Başarıyla Güncellendi',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }
        else{
            PageMisyon::findOrFail($misyon_id)->update([
                'title' => $request->title,
                'description' => $request->description,
                'keywords' => $request->keywords,
            ]);
            $notification = array(
                'message' => 'Misyonumuz Sayfası Başarıyla Güncellendi',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }

    }

    public function PagesReferences(){
        $pagereference = PageReference::find(1);
        return view('backend.pages.references_page', compact('pagereference'));
    }

    public function UpdatePagesReferences(Request $request){
        $validateData = $request->validate([
            'page_banner'=> 'max:2000',
        ]);

        $reference_id = $request->id;
        if($request->file('page_banner')){
            $image = $request->file('page_banner');
            $extension = $request->file('page_banner')->extension();
            if($extension != "jpg" && $extension != "png" && $extension != "jpeg"){
                $notification = array(
                    'message' => ' Dosya uzantısı sadece jpg,png,jpeg olmalı',
                    'alert-type' => 'warning'
                );
                return redirect()->back()->with($notification);
            }
            $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
            Image::make($image)->save('upload/pages/'.$name_gen);
            $save_url = 'upload/pages/'.$name_gen;

            PageReference::findOrFail($reference_id)->update([
                'title' => $request->title,
                'description' => $request->description,
                'keywords' => $request->keywords,
                'page_banner' => $save_url,
            ]);
            $notification = array(
                'message' => 'Çözüm ortakları Sayfası Başarıyla Güncellendi',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }
        else{
            PageReference::findOrFail($reference_id)->update([
                'title' => $request->title,
                'description' => $request->description,
                'keywords' => $request->keywords,
            ]);
            $notification = array(
                'message' => 'Çözüm ortakları Sayfası Başarıyla Güncellendi',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }

    }

    



}
