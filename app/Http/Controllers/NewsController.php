<?php

namespace App\Http\Controllers;

use App\Models\News;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::where('newsActive', 1)
            ->orderByDesc('newsDate')
            ->orderByDesc('newsID')
            ->get();

        return view('news.index', compact('news'));
    }

    public function show($id)
    {
        $item = News::where('newsID', $id)->where('newsActive', 1)->firstOrFail();

        return view('news.show', compact('item'));
    }
}
