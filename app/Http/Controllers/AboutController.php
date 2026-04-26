<?php

namespace App\Http\Controllers;


class AboutController extends Controller
{
    public function index()
    {
        return view('about.index');
    }

    public function history()
    {
        return view('about.history');
    }

    public function locations()
    {
        return view('about.locations');
    }

    public function honourBoard()
    {
        return view('about.honour-board');
    }
}
