<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PrivacyPolicy;

class PrivacyPolicyController extends Controller
{
    public function AllPrivacy() {
        $all_privacy = PrivacyPolicy::find(1);
        return view('backend.privacy_policy', compact('all_privacy'));
    }

    public function UpdatePrivacy(Request $request) {
        $privacy_id = $request->id;
        PrivacyPolicy::findOrFail($privacy_id)->update([
            'description' => $request->description,
        ]);

        $notification = array(
            'message' => 'Gizlilik Politikası Başarıyla Güncellendi',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }
}
