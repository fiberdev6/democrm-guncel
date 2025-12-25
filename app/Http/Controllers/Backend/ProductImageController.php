<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductImage;
use App\Models\Product;
use Image;
use Illuminate\Support\Carbon;

class ProductImageController extends Controller
{
    public function AllProductImage($id) {
        $product_images = ProductImage::where('product_id', '=', $id)->get();
        $products = Product::where('id', '=', $id)->first();
        return view('backend.product_images.all_product_images', compact('product_images', 'products'));
    }

    public function AddProductImage($id) {
        $products = Product::where('id', '=', $id)->first();
        $urunler = Product::orderBy('title', 'ASC')->get();
        return view('backend.product_images.add_product_images', compact('products', 'urunler'));
    }

    public function StoreProductImage(Request $request) {
        $validateData = $request->validate([
            'product_images.*'=> 'max:2000',
        ]);
        $image = $request->file('product_images');

        foreach ($image as $product_image) {
            
            $name_gen = hexdec(uniqid()).'.'.$product_image->getClientOriginalExtension();
            $watermark = public_path('frontend/img/watermark.png');
            $product_image = Image::make($product_image)->save('upload/product_images/'.$name_gen);
            $product_image->insert($watermark, 'bottom-right',5,5)->save('upload/product_images/'.$name_gen);
            $save_url = 'upload/product_images/'.$name_gen;

            ProductImage::insert([
                'product_id' => $request->product,
                'product_images' => $save_url,
                'created_at' => Carbon::now()
            ]);
        } //end of the foreach

        $notification = array(
            'message' => 'Ürün Resmi Başarıyla Eklendi',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }

    public function EditProductImage($id) {
        $product_image_id = ProductImage::findOrFail($id);
        $products = Product::orderBy('title', 'ASC')->get();
        return view('backend.product_images.edit_product_images', compact('product_image_id', 'products'));
    }

    public function UpdateProductImage(Request $request) {
        $validateData = $request->validate([
            'product_images'=> 'max:2048',
        ]);
        $product_image_id = $request->id;
        if($request->file('product_images')){
            $image = $request->file('product_images');
            $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
            $watermark = public_path('frontend/img/watermark.png');
            $image = Image::make($image)->save('upload/product_images/'.$name_gen);
            $image->insert($watermark, 'bottom-right',5,5)->save('upload/product_images/'.$name_gen);
            $save_url = 'upload/product_images/'.$name_gen;

            ProductImage::findOrFail($product_image_id)->update([
                'product_id' => $request->product,
                'product_images' => $save_url,
            ]);
            $notification = array(
                'message' => 'Ürün Resimleri Başarıyla Güncellendi',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }
    }

    public function DeleteProductImage($id) {
        $product_image_id = ProductImage::findOrFail($id);
        $img = $product_image_id->product_images;
        unlink($img);

        ProductImage::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Ürün Resmi Başarıyla Silindi',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
}
