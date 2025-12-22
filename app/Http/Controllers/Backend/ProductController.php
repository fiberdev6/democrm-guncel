<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Image;

class ProductController extends Controller
{
    public function AllProduct(Request $request) {
        $products = Product::latest()->get();
        return view('backend.products.product_all',compact('products'));
    }

    public function AddProduct() {
        return view('backend.products.product_add');
    }

    public function StoreProduct(Request $request){
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

        Image::make($image)->save('upload/products/'.$name_gen);
        $save_url = 'upload/products/'.$name_gen;

        Product::insert([
            'title' => $request->title,
            'slug' => $request->slug,
            'description' => $request->description,
            'image' => $save_url,
        ]);

        $notification = array(
            'message' => 'Ürün Başarıyla Eklendi',
            'alert-type' => 'success'
        );

        return redirect()->route('all.product')->with($notification);
    }

    public function EditProduct($id) {
        $product_id = Product::findOrFail($id);
        return view('backend.products.product_edit', compact('product_id'));
    }

    public function UpdateProduct(Request $request) {
        $validateData = $request->validate([
            'image'=> 'max:2000',
        ]);
        $product_id = $request->id;
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

            Image::make($image)->save('upload/products/'.$name_gen);
            $save_url = 'upload/products/'.$name_gen;

            Product::findOrFail($product_id)->update([
                'title' => $request->title,
                'slug' => $request->slug,
                'description' => $request->description,
                'image' => $save_url,
            ]);
            $notification = array(
                'message' => 'Ürün Bilgileri Başarıyla Güncellendi',
                'alert-type' => 'success'
              );
        return redirect()->route('all.product')->with($notification);
        }
        else {
            Product::findOrFail($product_id)->update([
                'title' => $request->title,
                'slug' => $request->slug,
                'description' => $request->description,
            ]);
            $notification = array(
                'message' => 'Ürün Bilgileri Başarıyla Güncellendi',
                'alert-type' => 'success'
              );
        return redirect()->route('all.product')->with($notification);

        }
    }

    public function DeleteProduct($id) {
        $product_id = Product::findOrFail($id);
        $img = $product_id->image;
        unlink($img);

        Product::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Ürün Başarıyla Silindi',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
}
