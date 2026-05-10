<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminStoreController extends Controller
{
    // PRODUCTS

    public function products()
    {
        $products = DB::table('products')
            ->leftJoin('product_images as pi', function ($join) {
                $join->on('pi.productID', '=', 'products.productID')
                     ->where('pi.isPrimary', 1);
            })
            ->select('products.*', 'pi.imagePath as primaryImagePath')
            ->orderBy('products.productName')
            ->get()
            ->map(function ($product) {
                $product->displayImage = $product->primaryImagePath
                    ? asset('storage/' . $product->primaryImagePath)
                    : $product->productImage;
                return $product;
            });

        return view('admin.store.products.index', compact('products'));
    }

    public function createProduct()
    {
        return view('admin.store.products.create');
    }

    public function storeProduct(Request $request)
    {
        $request->validate([
            'productName'          => 'required|string|max:255',
            'productPrice'         => 'required|numeric|min:0',
            'productStock'         => 'required|integer|min:0',
            'productMaxQuantity'   => 'required|integer|min:1',
            'productAvailableFrom' => 'nullable|date',
            'productAvailableTo'   => 'nullable|date|after_or_equal:productAvailableFrom',
        ]);

        $slug = Str::slug($request->productName);
        $base = $slug;
        $i = 2;
        while (DB::table('products')->where('productSlug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        DB::table('products')->insert([
            'productName'          => $request->productName,
            'productDescription'   => $request->productDescription,
            'productPrice'         => $request->productPrice,
            'productImage'         => $request->productImage ?: null,
            'productStock'         => $request->productStock,
            'productActive'        => $request->has('productActive') ? 1 : 0,
            'productAvailableFrom' => $request->productAvailableFrom ? \Carbon\Carbon::parse($request->productAvailableFrom)->format('Y-m-d H:i:s') : null,
            'productAvailableTo'   => $request->productAvailableTo   ? \Carbon\Carbon::parse($request->productAvailableTo)->format('Y-m-d H:i:s')   : null,
            'productMaxQuantity'   => $request->productMaxQuantity,
            'productSlug'          => $slug,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        return redirect('/admin/store/products')->with('success', 'Product created.');
    }

    public function editProduct(int $productID)
    {
        $product = DB::table('products')->where('productID', $productID)->firstOrFail();
        $images  = DB::table('product_images')
            ->where('productID', $productID)
            ->orderBy('imageOrder')
            ->get()
            ->map(fn($img) => (object) array_merge((array) $img, [
                'imageUrl' => asset('storage/' . $img->imagePath),
            ]));
        return view('admin.store.products.edit', compact('product', 'images'));
    }

    // IMAGE MANAGEMENT

    public function uploadProductImage(Request $request, int $productID)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $product       = DB::table('products')->where('productID', $productID)->firstOrFail();
        $existingCount = DB::table('product_images')->where('productID', $productID)->count();
        $maxOrder      = DB::table('product_images')->where('productID', $productID)->max('imageOrder') ?? -1;

        $file     = $request->file('image');
        $ext      = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = uniqid('img_', true) . '.' . $ext;
        $dir      = Storage::disk('public')->path('products/' . $productID);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        try {
            $path = $file->storeAs('products/' . $productID, $filename, 'public');
        } catch (\Throwable $e) {
            Log::error('Product image upload failed', [
                'productID'    => $productID,
                'file'         => $file->getClientOriginalName(),
                'filename'     => $filename,
                'disk_root'    => Storage::disk('public')->path(''),
                'dir'          => $dir,
                'dir_exists'   => is_dir($dir),
                'dir_writable' => is_writable($dir),
                'error'        => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Storage error: ' . $e->getMessage()], 500);
        }

        if (!$path) {
            Log::error('Product image storeAs() returned false', [
                'productID'    => $productID,
                'filename'     => $filename,
                'disk_root'    => Storage::disk('public')->path(''),
                'dir'          => $dir,
                'dir_exists'   => is_dir($dir),
                'dir_writable' => is_writable($dir),
            ]);
            return response()->json(['error' => 'File could not be saved. Check storage path and permissions.'], 500);
        }

        $imageUrl  = asset('storage/' . $path);
        $isPrimary = $existingCount === 0;

        $imageID = DB::table('product_images')->insertGetId([
            'productID'  => $productID,
            'imagePath'  => $path,
            'imageOrder' => $maxOrder + 1,
            'imageAlt'   => null,
            'isPrimary'  => $isPrimary ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'imageID');

        if ($isPrimary) {
            DB::table('products')->where('productID', $productID)->update([
                'productImage' => $imageUrl,
                'updated_at'   => now(),
            ]);
        }

        return response()->json([
            'imageID'   => $imageID,
            'imageUrl'  => $imageUrl,
            'isPrimary' => $isPrimary,
        ]);
    }

    public function deleteProductImage(Request $request, int $productID, int $imageID)
    {
        $image   = DB::table('product_images')->where('imageID', $imageID)->where('productID', $productID)->firstOrFail();
        $product = DB::table('products')->where('productID', $productID)->firstOrFail();

        Storage::disk('public')->delete($image->imagePath);
        DB::table('product_images')->where('imageID', $imageID)->delete();

        $newPrimaryUrl = null;
        $newPrimaryID  = null;

        if ($image->isPrimary) {
            $next = DB::table('product_images')->where('productID', $productID)->orderBy('imageOrder')->first();
            if ($next) {
                $newPrimaryUrl = asset('storage/' . $next->imagePath);
                $newPrimaryID  = $next->imageID;
                DB::table('product_images')->where('imageID', $next->imageID)->update(['isPrimary' => 1, 'updated_at' => now()]);
            }
            DB::table('products')->where('productID', $productID)->update([
                'productImage' => $newPrimaryUrl,
                'updated_at'   => now(),
            ]);
        }

        return response()->json(['success' => true, 'newPrimaryID' => $newPrimaryID]);
    }

    public function setPrimaryImage(Request $request, int $productID, int $imageID)
    {
        $image = DB::table('product_images')->where('imageID', $imageID)->where('productID', $productID)->firstOrFail();

        DB::table('product_images')->where('productID', $productID)->update(['isPrimary' => 0, 'updated_at' => now()]);
        DB::table('product_images')->where('imageID', $imageID)->update(['isPrimary' => 1, 'updated_at' => now()]);
        DB::table('products')->where('productID', $productID)->update([
            'productImage' => asset('storage/' . $image->imagePath),
            'updated_at'   => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function reorderProductImages(Request $request, int $productID)
    {
        $order = $request->input('order', []);
        foreach ($order as $idx => $imgID) {
            DB::table('product_images')
                ->where('imageID', $imgID)
                ->where('productID', $productID)
                ->update(['imageOrder' => $idx, 'updated_at' => now()]);
        }
        return response()->json(['success' => true]);
    }

    public function updateProduct(Request $request, int $productID)
    {
        $request->validate([
            'productName'          => 'required|string|max:255',
            'productPrice'         => 'required|numeric|min:0',
            'productStock'         => 'required|integer|min:0',
            'productMaxQuantity'   => 'required|integer|min:1',
            'productAvailableFrom' => 'nullable|date',
            'productAvailableTo'   => 'nullable|date|after_or_equal:productAvailableFrom',
            'productSlug'          => [
                'required',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                function ($attribute, $value, $fail) use ($productID) {
                    if (DB::table('products')->where('productSlug', $value)->where('productID', '!=', $productID)->exists()) {
                        $fail('This slug is already in use by another product.');
                    }
                },
            ],
        ]);

        DB::table('products')->where('productID', $productID)->update([
            'productName'          => $request->productName,
            'productDescription'   => $request->productDescription,
            'productPrice'         => $request->productPrice,
            'productImage'         => $request->productImage ?: null,
            'productStock'         => $request->productStock,
            'productActive'        => $request->has('productActive') ? 1 : 0,
            'productAvailableFrom' => $request->productAvailableFrom ? \Carbon\Carbon::parse($request->productAvailableFrom)->format('Y-m-d H:i:s') : null,
            'productAvailableTo'   => $request->productAvailableTo   ? \Carbon\Carbon::parse($request->productAvailableTo)->format('Y-m-d H:i:s')   : null,
            'productMaxQuantity'   => $request->productMaxQuantity,
            'productSlug'          => $request->productSlug,
            'updated_at'           => now(),
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
