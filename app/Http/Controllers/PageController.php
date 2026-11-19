<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function pricing()
    {
        return view('pages.pricing');
    }

    public function apiDocs()
    {
        return view('pages.api');
    }

    public function support()
    {
        return view('pages.support');
    }
}
