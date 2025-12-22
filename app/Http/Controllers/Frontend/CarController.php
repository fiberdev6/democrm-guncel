<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
class CarController extends Controller
{
    public function AllCars($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        if(!$firma) {
            $notification = array(
                'message' => 'Firma bulunamadı',
                'alert-type' => 'danger'
            );
            return redirect()->route('giris')->with($notification);
        }
        $all_cars = Car::where('firma_id', $firma->id)->orderBy('id', 'desc')->get();
        return view('frontend.secure.cars.all_cars', compact('firma', 'all_cars'));
    }

    public function AddCar($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        return view('frontend.secure.cars.add_car', compact('firma'));
    }

    public function StoreCar(Request $request, $tenant_id) {
        $token = $request->input('form_token');
        // Token boş mu kontrol et
        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz form token! Lütfen sayfayı yenileyin.'
            ], 400);
        }
        // Bu token daha önce kullanıldı mı kontrol et
        $cacheKey = 'car_form_token_' . $token;
        if (Cache::has($cacheKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Bu form zaten gönderildi! Lütfen bekleyin veya sayfayı yenileyin.'
            ], 400);
        }
        // Token'ı 10 dakika boyunca sakla
        Cache::put($cacheKey, true, now()->addMinutes(10));

        $firma = Tenant::where('id', $tenant_id)->first();
        $response = Car::create([
            'firma_id' => $firma->id,
            'arac' => $request->arac,
            'durum' => '1',
        ]);
        $createdCar = Car::find($response->id);
        return response()->json($createdCar);
    }

    public function EditCar($tenant_id, $id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $car_id = Car::findOrFail($id);
        return view('frontend.secure.cars.edit_car', compact('firma', 'car_id'));
    }

    public function UpdateCar($tenant_id, Request $request) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $car_id = $request->id;
        Car::findOrFail($car_id)->update([
            'firma_id' => $firma->id,
            'arac' => $request->arac,
        ]);
        $updatedCar = Car::find($car_id);
        return response()->json($updatedCar);
    }

    public function DeleteCar($tenant_id, $id) {
        $car = Car::find($id);
        if($car) {
            $car->delete();
            return response()->json(['success' => true]);
        }
        else {
            return response()->json(['success' => false, 'message' => 'Servis aracı bulunamadı.']);
        }
    }
}
