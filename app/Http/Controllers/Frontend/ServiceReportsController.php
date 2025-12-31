<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\DeviceBrand;
use App\Models\ServiceResource;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;

class ServiceReportsController extends Controller
{
    public function ServiceReports($tenant_id) {
        $firma = Tenant::where('id', $tenant_id)->first();
        $operators = User::role(['Operatör'])->where('tenant_id', $tenant_id)->get();
        $teknisyen = User::role(['Teknisyen'])->where('tenant_id', $tenant_id)->get();
        $yardimciTeknisyen = User::role(['Teknisyen Yardımcısı'])->where('tenant_id', $tenant_id)->get();
        $cars = Car::where('firma_id', $tenant_id)->where('durum', '1')->get();
        $isBeyazEsya = $firma->sektor === 'beyaz-esya';

        $marka = DeviceBrand::where(function($query) use ($firma, $isBeyazEsya) {
            if ($isBeyazEsya) {
                // Beyaz eşya sektörü: default + kendi eklediği
                $query->whereNull('firma_id')
                    ->orWhere('firma_id', $firma->id);
            } else {
                // Diğer sektörler: sadece kendi eklediği
                $query->where('firma_id', $firma->id);
            }
        })->orderBy('marka', 'asc')->get();
        $servisKaynak = ServiceResource::where('firma_id',$tenant_id)->get();
        return view('frontend.secure.all_services.service_reports.reports_modal', compact('firma','operators','teknisyen','yardimciTeknisyen','cars','marka','servisKaynak'));
    }
}
