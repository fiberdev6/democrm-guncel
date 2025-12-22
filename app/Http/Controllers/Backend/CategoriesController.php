<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Image;

class CategoriesController extends Controller
{
    public function AllCategories(Request $request) {
        $categories = Category::latest()->get();
        return view('backend.categories.all_category',compact('categories'));
    }

    public function AddCategories() {
        return view('backend.categories.add_category');
    }

    public function StoreCategories(Request $request){
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

        Image::make($image)->save('upload/categories/'.$name_gen);
        $save_url = 'upload/categories/'.$name_gen;

        Category::insert([
            'title' => $request->title,
            'slug' => $request->slug,
            'description' => $request->description,
            'sira' => $request->sira,
            'image' => $save_url,
        ]);

        $notification = array(
            'message' => 'Kategori Başarıyla Eklendi',
            'alert-type' => 'success'
        );

        return redirect()->route('all.categories')->with($notification);
    }

    public function EditCategories($id) {
        $category_id = Category::findOrFail($id);
        return view('backend.categories.edit_category', compact('category_id'));
    }

    public function UpdateCategories(Request $request) {
        $validateData = $request->validate([
            'image'=> 'max:2000',
        ]);
        $category_id = $request->id;
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

            Image::make($image)->save('upload/categories/'.$name_gen);
            $save_url = 'upload/categories/'.$name_gen;

            Category::findOrFail($category_id)->update([
                'title' => $request->title,
                'slug' => $request->slug,
                'description' => $request->description,
                'sira' => $request->sira,
                'image' => $save_url,
            ]);
            $notification = array(
                'message' => 'Kategori Bilgileri Başarıyla Güncellendi',
                'alert-type' => 'success'
              );
        return redirect()->route('all.categories')->with($notification);
        }
        else {
            Category::findOrFail($category_id)->update([
                'title' => $request->title,
                'slug' => $request->slug,
                'description' => $request->description,
                'sira' => $request->sira,
            ]);
            $notification = array(
                'message' => 'Kategori Bilgileri Başarıyla Güncellendi',
                'alert-type' => 'success'
              );
        return redirect()->route('all.categories')->with($notification);

        }
    }

    public function DeleteCategories($id) {
        $category_id = Category::findOrFail($id);
        $img = $category_id->image;
        unlink($img);

        Category::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Kategori Başarıyla Silindi',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
}
