<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Settings;
use Image;

class SettingsController extends Controller
{
    public function SiteSettings()
    {
        $site_settings_all = Settings::find(1);
        return view('backend.settings.site_settings_all', compact('site_settings_all'));

    }

    public function UpdateSiteSettings(Request $request)
    {
        $validateData = $request->validate([
            'site_logo'=> 'max:2000',
            'favicon'=> 'max:2000',
        ]);

        $settings_id = $request->id;
        if ($request->file('site_logo')) {
            $image = $request->file('site_logo');
            $extension = $request->file('site_logo')->extension();
            if($extension != "jpg" && $extension != "png" && $extension != "jpeg"){
                $notification = array(
                    'message' => 'Logo dosya uzantısı sadece jpg,png,jpeg olmalı',
                    'alert-type' => 'warning'
                );
                return redirect()->back()->with($notification);
            }

            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();

            Image::make($image)->save('upload/siteLogo/' . $name_gen);
            $save_url = 'upload/siteLogo/' . $name_gen;

            Settings::findOrFail($settings_id)->update([
                'site_name' => $request->site_name,
                'site_url' => $request->site_url,
                'site_description' => $request->site_description,
                'site_keywords' => $request->site_keywords,
                'copyright' => $request->copyright,
                'site_logo' => $save_url,
            ]);

            $notification = array(
                'message' => 'Site Ayarları Başarıyla Güncellendi',
                'alert-type' => 'success'
            );

            return redirect()->back()->with($notification);

        } elseif ($request->file('favicon')) {
            $favicon = $request->file('favicon');
            
            $extension = $request->file('favicon')->extension();
            if($extension != "jpg" && $extension != "png" && $extension != "jpeg"){
                $notification = array(
                    'message' => 'Favicon dosya uzantısı sadece jpg,png,jpeg olmalı',
                    'alert-type' => 'warning'
                );
                return redirect()->back()->with($notification);
            }
            
            $name_gen2 = hexdec(uniqid()) . '.' . $favicon->getClientOriginalExtension();
            Image::make($favicon)->save('upload/favicon/' . $name_gen2);
            $save_favicon = 'upload/favicon/' . $name_gen2;

            Settings::findOrFail($settings_id)->update([
                'site_name' => $request->site_name,
                'site_url' => $request->site_url,
                'site_description' => $request->site_description,
                'site_keywords' => $request->site_keywords,
                'copyright' => $request->copyright,
                'favicon' => $save_favicon,
            ]);
            $notification = array(
                'message' => 'Site Ayarları Başarıyla Güncellendi',
                'alert-type' => 'success'
            );
            
            return redirect()->back()->with($notification);
        } else {

            Settings::findOrFail($settings_id)->update([
                'site_name' => $request->site_name,
                'site_url' => $request->site_url,
                'site_description' => $request->site_description,
                'site_keywords' => $request->site_keywords,
                'copyright' => $request->copyright,

            ]);

            $notification = array(
                'message' => 'Site Ayarları Başarıyla Güncellendi',
                'alert-type' => 'success'
            );

            return redirect()->back()->with($notification);
        }

    }

    public function EmailSettings()
    {
        $email_settings_all = Settings::find(1);
        return view('backend.settings.email_settings_all', compact('email_settings_all'));
    }

    public function UpdateEmailSettings(Request $request)
    {
        $settings_id = $request->id;
        Settings::findOrFail($settings_id)->update([
            'mail_server' => $request->mail_server,
            'mail_port' => $request->mail_port,
            'protokol' => $request->protokol,
            'email' => $request->email,
            'mail_sifre' => $request->mail_sifre,

        ]);

        $notification = array(
            'message' => 'Email Ayarları Başarıyla Güncellendi',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }

    public function GoogleSettings()
    {

        $google_settings_all = Settings::find(1);
        return view('backend.settings.google_settings_all', compact('google_settings_all'));
    }

    public function UpdateGoogleSettings(Request $request)
    {
        $settings_id = $request->id;
        Settings::findOrFail($settings_id)->update([
            'maps_kod' => $request->maps_kod,
            'taghead_kod' => $request->taghead_kod,
            'tagbody_kod' => $request->tagbody_kod,

        ]);

        $notification = array(
            'message' => 'Google Ayarları Başarıyla Güncellendi',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }

    public function CompanySettings(Request $request)
    {
        $company_settings = Settings::find(1);
        return view('backend.settings.company_settings_all', compact('company_settings'));
    }

    public function UpdateCompanySettings(Request $request)
    {

        $company_settings_id = $request->id;

        Settings::findOrFail($company_settings_id)->update([
            'company_name' => $request->company_name,
            'company_address' => $request->company_address,
            'address_second' => $request->address_second,
            'company_email' => $request->company_email,
            'company_phone' => $request->company_phone,
            'company_number' => $request->company_number,
        ]);

        $notification = array(
            'message' => 'Firma Ayarları Başarıyla Güncellendi',
            'alert-type' => 'success'
        );

        return redirect()->route('company.settings')->with($notification);
    }

    public function SocialMediaSettings()
    {
        $socialmedia_settings_id = Settings::find(1);
        return view('backend.settings.socialmedia_settings_all', compact('socialmedia_settings_id'));
    }

    public function UpdateSocialMediaSettings(Request $request)
    {

        $socialmedia_settings_id = $request->id;

        Settings::findOrFail($socialmedia_settings_id)->update([
            'facebook' => $request->facebook,
            'twitter' => $request->twitter,
            'instagram' => $request->instagram,
            'linkedin' => $request->linkedin,
            'youtube' => $request->youtube,
        ]);

        $notification = array(
            'message' => 'Sosyal Medya Hesap Ayarları Başarıyla Güncellendi',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
}
