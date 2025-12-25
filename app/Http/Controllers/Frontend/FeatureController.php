<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\FeatureImage;
use App\Models\PageRoom;
use Illuminate\Http\Request;

class FeatureController extends Controller
{
    public function FeatureDetails($slug, Feature $id) {
        $feature_id = Feature::where('slug', $slug)->pluck('id')->first();
        $feature_details = Feature::where('slug', $slug)->first();
        $banner = PageRoom::find(1);
        $feature_images = FeatureImage::where('feature_id', $feature_id)->get();
        return view('frontend.pages.feature_details', compact('feature_id', 'feature_images' ,'feature_details', 'banner'));
    }
}
