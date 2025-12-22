<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\FeatureImage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Image;

class FeatureImagesController extends Controller
{
    public function AllFeaturesImage($id) {
        $feature_images = FeatureImage::where('feature_id', '=', $id)->get();
        $features = Feature::where('id', '=', $id)->first();
        return view('backend.feature_images.all_features_image', compact('feature_images', 'features'));
    }

    public function AddFeaturesImage($id) {
        $features = Feature::orderBy('title', 'ASC')->get();
        $feature = Feature::where('id', '=', $id)->first();
        return view('backend.feature_images.add_features_images', compact('features', 'feature'));
    }

    public function StoreFeaturesImage(Request $request) {
        $validateData = $request->validate([
            'image.*'=> 'max:2000',
        ]);
        $image = $request->file('image');

        foreach ($image as $room_image) {
            $name_gen = hexdec(uniqid()).'.'.$room_image->getClientOriginalExtension();
            //$watermark = public_path('frontend/img/watermark.png');
            $room_image = Image::make($room_image)->save('upload/feature_images/'.$name_gen);
            // $room_image->insert($watermark, 'bottom-right',5,5)->save('upload/project_images/'.$name_gen);
            $save_url = 'upload/feature_images/'.$name_gen;

            FeatureImage::insert([
                'feature_id' => $request->feature,
                'image' => $save_url,
                'created_at' => Carbon::now()
            ]);
        } //end of the foreach

        $notification = array(
            'message' => 'Özellik Resmi Başarıyla Eklendi',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }

    

    public function DeleteFeaturesImage($id) {
        $feature_image_id = FeatureImage::findOrFail($id);
        $img = $feature_image_id->image;
        unlink($img);

        FeatureImage::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Özellik Resmi Başarıyla Silindi',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
}
