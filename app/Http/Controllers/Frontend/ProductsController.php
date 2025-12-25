<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\PageRoom;
use App\Models\CategoryImage;
use App\Models\Room;
use App\Models\RoomImage;

class ProductsController extends Controller
{
    public function index() {
        $all_products = Category::orderBy('id', 'desc')->get();
        $page_products = PageRoom::find(1);
        return view('frontend.pages.products', compact('all_products', 'page_products'));
    }

    public function UrunDetails($slug, Category $id) {
        $category_id = Category::where('slug', $slug)->pluck('id')->first();
        $category_details = Category::where('slug', $slug)->first();
        $category_images = CategoryImage::where('category_id', $category_id)->get();
        $banner = PageRoom::find(1);
        $products_all = Room::where('category', '=', $category_id)->orderBy('id', 'desc')->get();
        return view('frontend.pages.product_details', compact('category_id', 'category_details', 'category_images', 'banner', 'products_all'));
    }

    public function Products($slug, Room $id) {
        $urun_id= Room::where('slug', $slug)->pluck('id')->first();
        $urun_details = Room::where('slug', $slug)->first();
        $urun_images = RoomImage::where('room_id', $urun_id)->get();
        $banner = PageRoom::find(1);
        return view('frontend.pages.urun_details', compact('urun_id', 'urun_details', 'urun_images', 'banner'));
    }
}
