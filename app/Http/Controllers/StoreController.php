<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    public function index()
    {
        $products = DB::table('products')
            ->where('productActive', true)
            ->orderBy('productName')
            ->get();

        return view('store.index', compact('products'));
    }

    public function show(string $productSlug)
    {
        $product = DB::table('products')
            ->where('productSlug', $productSlug)
            ->where('productActive', true)
            ->firstOrFail();

        return view('store.show', compact('product'));
    }
}
