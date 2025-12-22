<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OurDocument;
use App\Models\PageProduct;

class KatalogController extends Controller
{
    public function index() {
        $katalogs = OurDocument::orderBy('id', 'desc')->get();
        $page_katalog = PageProduct::find(1);
        return view('frontend.pages.catalogs', compact('katalogs', 'page_katalog'));
    }
}
