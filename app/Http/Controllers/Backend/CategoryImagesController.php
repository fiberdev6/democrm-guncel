<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CategoryImage;
use App\Models\Category;
use Image;
use Carbon\Carbon;

class CategoryImagesController extends Controller
{
    public function AllCategoryImage($id) {
        $category_images = CategoryImage::where('category_id', '=', $id)->get();
        $categories = Category::where('id', '=', $id)->first();
        return view('backend.category_images.all_category_images', compact('category_images', 'categories'));
    }

    public function AddCategoryImage($id) {
        $categories = Category::orderBy('title', 'ASC')->get();
        $kategoriler = Category::where('id', '=', $id)->first();
        return view('backend.category_images.add_category_images', compact('categories', 'kategoriler'));
    }

    public function StoreCategoryImage(Request $request) {
        $validateData = $request->validate([
            'image.*'=> 'max:2000',
        ]);
        $image = $request->file('image');

        foreach ($image as $room_image) {
            $name_gen = hexdec(uniqid()).'.'.$room_image->getClientOriginalExtension();
            //$watermark = public_path('frontend/img/watermark.png');
            $room_image = Image::make($room_image)->save('upload/category_images/'.$name_gen);
            // $room_image->insert($watermark, 'bottom-right',5,5)->save('upload/project_images/'.$name_gen);
            $save_url = 'upload/category_images/'.$name_gen;

            CategoryImage::insert([
                'category_id' => $request->category,
                'image' => $save_url,
                'created_at' => Carbon::now()
            ]);
        } //end of the foreach

        $notification = array(
            'message' => 'Kategori Resmi Başarıyla Eklendi',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }

    

    public function DeleteCategoryImage($id) {
        $category_image_id = CategoryImage::findOrFail($id);
        $img = $category_image_id->image;
        unlink($img);

        CategoryImage::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Kategori Resmi Başarıyla Silindi',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
}
