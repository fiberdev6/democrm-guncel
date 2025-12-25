<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Pricing;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function AllPricing(Request $request) {
        $prices = Pricing::latest()->get();
        return view('backend.pricing.all_pricing',compact('prices'));
    }

    public function AddPricing() {
        return view('backend.pricing.add_pricing');
    }

    public function StorePricing(Request $request){

        Pricing::insert([
            'name' => $request->name,
            'price' => $request->price,
            'color' => $request->color,
            'icon' => $request->icon,
            'description' => $request->description,
        ]);

        $notification = array(
            'message' => 'Paket Başarıyla Eklendi',
            'alert-type' => 'success'
        );

        return redirect()->route('all.pricing')->with($notification);
    }

    public function EditPricing($id) {
        $pricing_id = Pricing::findOrFail($id);
        return view('backend.pricing.edit_pricing', compact('pricing_id'));
    }

    public function UpdatePricing(Request $request) {
        $pricing_id = $request->id;

            Pricing::findOrFail($pricing_id)->update([
                'name' => $request->name,
                'price' => $request->price,
                'color' => $request->color,
                'icon' => $request->icon,
                'description' => $request->description,
            ]);
            $notification = array(
                'message' => 'Fiyat Bilgileri Başarıyla Güncellendi',
                'alert-type' => 'success'
              );
        return redirect()->route('all.pricing')->with($notification);
        
    }

    public function DeletePricing($id) {
        

        Pricing::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Paket Başarıyla Silindi',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
}
