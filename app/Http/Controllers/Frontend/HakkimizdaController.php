<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\About;
use App\Models\HomeCard;
use App\Models\PageAbout;

class HakkimizdaController extends Controller
{
    public function About() {
        $about_all = About::find(1);
        $surec_yonetim = HomeCard::orderBy('id', 'asc')->get();
        $page_about = PageAbout::find(1);
        return view('frontend.pages.about', compact('about_all', 'surec_yonetim', 'page_about'));
    }
}
