<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminStoreController extends Controller
{
    // PRODUCTS

    public function products()
    {
        $products = DB::table('products')->orderBy('productName')->get();
        return view('admin.store.products.index', compact('products'));
    }

    public function createProduct()
    {
        return view('admin.store.products.create');
    }

    public function storeProduct(Request $request)
    {
        $request->validate([
            'productName'  => 'required|string|max:255',
            'productPrice' => 'required|numeric|min:0',
            'productStock' => 'required|integer|min:0',
        ]);

        $slug = Str::slug($request->productName);
        $base = $slug;
        $i = 2;
        while (DB::table('products')->where('productSlug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        DB::table('products')->insert([
            'productName'        => $request->productName,
            'productDescription' => $request->productDescription,
            'productPrice'       => $request->productPrice,
            'productImage'       => $request->productImage ?: null,
            'productStock'       => $request->productStock,
            'productActive'      => $request->has('productActive') ? 1 : 0,
            'productSlug'        => $slug,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        return redirect('/admin/store/products')->with('success', 'Product created.');
    }

    public function editProduct(int $productID)
    {
        $product = DB::table('products')->where('productID', $productID)->firstOrFail();
        return view('admin.store.products.edit', compact('product'));
    }

    public function updateProduct(Request $request, int $productID)
    {
        $request->validate([
            'productName'  => 'required|string|max:255',
            'productPrice' => 'required|numeric|min:0',
            'productStock' => 'required|integer|min:0',
        ]);

        DB::table('products')->where('productID', $productID)->update([
            'productName'        => $request->productName,
            'productDescription' => $request->productDescription,
            'productPrice'       => $request->productPrice,
            'productImage'       => $request->productImage ?: null,
            'productStock'       => $request->productStock,
            'productActive'      => $request->has('productActive') ? 1 : 0,
            'updated_at'         => now(),
        ]);

        return redirect('/admin/store/products')->with('success', 'Product updated.');
    }

    public function toggleProduct(int $productID)
    {
        $product = DB::table('products')->where('productID', $productID)->firstOrFail();
        DB::table('products')->where('productID', $productID)->update([
            'productActive' => $product->productActive ? 0 : 1,
            'updated_at'    => now(),
        ]);

        $state = $product->productActive ? 'deactivated' : 'activated';
        return back()->with('success', "Product {$state}.");
    }

    // ORDERS

    public function orders()
    {
        $orders = DB::table('orders as o')
            ->leftJoin('members as m', 'o.memberID', '=', 'm.memberID')
            ->select(
                'o.*',
                DB::raw("CONCAT(COALESCE(m.memberNameFirst,''), ' ', COALESCE(m.memberNameLast,'')) as memberName")
            )
            ->orderBy('o.created_at', 'desc')
            ->get();

        return view('admin.store.orders.index', compact('orders'));
    }

    public function editOrder(int $orderID)
    {
        $order = DB::table('orders as o')
            ->leftJoin('members as m', 'o.memberID', '=', 'm.memberID')
            ->select(
                'o.*',
                DB::raw("CONCAT(COALESCE(m.memberNameFirst,''), ' ', COALESCE(m.memberNameLast,'')) as memberName")
            )
            ->where('o.orderID', $orderID)
            ->firstOrFail();

        $items = DB::table('order_items as oi')
            ->join('products as p', 'oi.productID', '=', 'p.productID')
            ->select('oi.*', 'p.productName')
            ->where('oi.orderID', $orderID)
            ->get();

        return view('admin.store.orders.edit', compact('order', 'items'));
    }

    public function updateOrder(Request $request, int $orderID)
    {
        $request->validate([
            'orderStatus' => 'required|in:pending,paid,shipped,complete',
        ]);

        DB::table('orders')->where('orderID', $orderID)->update([
            'orderStatus' => $request->orderStatus,
            'orderNotes'  => $request->orderNotes,
            'updated_at'  => now(),
        ]);

        return redirect('/admin/store/orders')->with('success', 'Order updated.');
    }
}
