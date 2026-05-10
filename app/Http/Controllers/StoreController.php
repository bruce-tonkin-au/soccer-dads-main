<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class StoreController extends Controller
{
    // ─── Helpers ───────────────────────────────────────────────────────────────

    private static function availabilityQuery()
    {
        return DB::table('products')
            ->where('productActive', true)
            ->where(function ($q) {
                $q->whereNull('productAvailableFrom')->orWhere('productAvailableFrom', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('productAvailableTo')->orWhere('productAvailableTo', '>=', now());
            });
    }

    // ─── Public store ──────────────────────────────────────────────────────────

    public function index()
    {
        $products = static::availabilityQuery()->orderBy('productName')->get();
        return view('store.index', compact('products'));
    }

    public function show(string $productSlug)
    {
        $product = static::availabilityQuery()
            ->where('productSlug', $productSlug)
            ->firstOrFail();

        $images = DB::table('product_images')
            ->where('productID', $product->productID)
            ->orderBy('imageOrder')
            ->get()
            ->map(fn($img) => (object) array_merge((array) $img, [
                'imageUrl' => asset('storage/' . $img->imagePath),
            ]));

        // Fall back to the primary productImage URL if no uploaded images exist
        if ($images->isEmpty() && $product->productImage) {
            $images = collect([(object) [
                'imageID'   => null,
                'imageUrl'  => $product->productImage,
                'imageAlt'  => null,
                'isPrimary' => true,
            ]]);
        }

        return view('store.show', compact('product', 'images'));
    }

    // ─── Direct (Buy now) checkout ─────────────────────────────────────────────

    public function checkout(Request $request, string $productSlug)
    {
        $product = static::availabilityQuery()
            ->where('productSlug', $productSlug)
            ->firstOrFail();

        if ($product->productStock <= 0) {
            return back()->with('error', 'Sorry, this product is out of stock.');
        }

        $maxQty   = min($product->productMaxQuantity, $product->productStock);
        $quantity = max(1, min((int) $request->input('quantity', 1), $maxQty));

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency'     => 'aud',
                    'product_data' => [
                        'name'   => $product->productName,
                        'images' => $product->productImage ? [$product->productImage] : [],
                    ],
                    'unit_amount' => (int) round($product->productPrice * 100),
                ],
                'quantity' => $quantity,
            ]],
            'mode'        => 'payment',
            'success_url' => url('/store/order-complete?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url'  => url('/store/' . $productSlug),
            'metadata'    => [
                'type'      => 'store_purchase',
                'productID' => $product->productID,
                'memberID'  => session('player_id') ?: '',
                'quantity'  => $quantity,
            ],
        ]);

        return redirect($session->url);
    }

    // ─── Cart ──────────────────────────────────────────────────────────────────

    public function addToCart(Request $request, string $productSlug)
    {
        $product = static::availabilityQuery()
            ->where('productSlug', $productSlug)
            ->firstOrFail();

        if ($product->productStock <= 0) {
            return back()->with('error', 'Sorry, this product is out of stock.');
        }

        $maxQty   = min($product->productMaxQuantity, $product->productStock);
        $quantity = max(1, min((int) $request->input('quantity', 1), $maxQty));

        $cart = session('store_cart', []);
        $cart[$product->productID] = [
            'productID'    => $product->productID,
            'productSlug'  => $product->productSlug,
            'productName'  => $product->productName,
            'productPrice' => $product->productPrice,
            'productImage' => $product->productImage,
            'quantity'     => $quantity,
        ];
        session(['store_cart' => $cart]);

        return redirect('/store/cart');
    }

    public function viewCart()
    {
        $cartData = session('store_cart', []);

        if (empty($cartData)) {
            return view('store.cart', ['items' => collect(), 'total' => 0]);
        }

        $productIDs = array_keys($cartData);
        $products   = DB::table('products')->whereIn('productID', $productIDs)->get()->keyBy('productID');

        $items = collect($cartData)->map(function ($item) use ($products) {
            $product = $products[$item['productID']] ?? null;
            if (!$product) return null;
            return (object) [
                'productID'    => $product->productID,
                'productSlug'  => $product->productSlug,
                'productName'  => $product->productName,
                'productPrice' => $product->productPrice,
                'productImage' => $product->productImage,
                'quantity'     => $item['quantity'],
                'lineTotal'    => $product->productPrice * $item['quantity'],
            ];
        })->filter()->values();

        $total = $items->sum('lineTotal');

        return view('store.cart', compact('items', 'total'));
    }

    public function removeFromCart(Request $request)
    {
        $productID = (int) $request->input('productID');
        $cart = session('store_cart', []);
        unset($cart[$productID]);
        session(['store_cart' => $cart]);
        return back();
    }

    public function cartCheckout()
    {
        $cartData = session('store_cart', []);

        if (empty($cartData)) {
            return redirect('/store/cart')->with('error', 'Your cart is empty.');
        }

        $productIDs = array_keys($cartData);
        $products   = DB::table('products')
            ->whereIn('productID', $productIDs)
            ->where('productActive', true)
            ->get()
            ->keyBy('productID');

        $lineItems      = [];
        $orderItemsData = [];
        $orderTotal     = 0;

        foreach ($cartData as $productID => $cartItem) {
            $product = $products[$productID] ?? null;
            if (!$product || $product->productStock <= 0) continue;

            $maxQty   = min($product->productMaxQuantity, $product->productStock);
            $quantity = max(1, min($cartItem['quantity'], $maxQty));
            $orderTotal += $product->productPrice * $quantity;

            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'aud',
                    'product_data' => [
                        'name'   => $product->productName,
                        'images' => $product->productImage ? [$product->productImage] : [],
                    ],
                    'unit_amount' => (int) round($product->productPrice * 100),
                ],
                'quantity' => $quantity,
            ];

            $orderItemsData[] = [
                'productID'    => $productID,
                'itemQuantity' => $quantity,
                'itemPrice'    => $product->productPrice,
            ];
        }

        if (empty($lineItems)) {
            return redirect('/store/cart')->with('error', 'No valid items in cart.');
        }

        $memberID = session('player_id');

        // Pre-create a pending order so we have an ID to pass to Stripe
        $orderID = DB::table('orders')->insertGetId([
            'memberID'        => $memberID,
            'orderStatus'     => 'pending',
            'orderTotal'      => $orderTotal,
            'stripeSessionID' => null,
            'orderNotes'      => null,
            'created_at'      => now(),
            'updated_at'      => now(),
        ], 'orderID');

        foreach ($orderItemsData as $item) {
            DB::table('order_items')->insert([
                'orderID'      => $orderID,
                'productID'    => $item['productID'],
                'itemQuantity' => $item['itemQuantity'],
                'itemPrice'    => $item['itemPrice'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items'  => $lineItems,
            'mode'        => 'payment',
            'success_url' => url('/store/order-complete?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url'  => url('/store/cart'),
            'metadata'    => [
                'type'     => 'store_cart',
                'orderID'  => $orderID,
                'memberID' => $memberID ?: '',
            ],
        ]);

        // Attach the session ID to the order for idempotency
        DB::table('orders')->where('orderID', $orderID)->update([
            'stripeSessionID' => $session->id,
            'updated_at'      => now(),
        ]);

        return redirect($session->url);
    }

    // ─── Order complete ────────────────────────────────────────────────────────

    public function orderComplete(Request $request)
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) return redirect('/store');

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $session = StripeSession::retrieve($sessionId);
        } catch (\Exception $e) {
            return redirect('/store');
        }

        if ($session->payment_status !== 'paid') {
            return redirect('/store')->with('error', 'Payment not completed.');
        }

        $memberID = !empty($session->metadata->memberID) ? (int) $session->metadata->memberID : null;
        $type     = $session->metadata->type ?? 'store_purchase';

        if ($type === 'store_cart') {
            $orderID = (int) ($session->metadata->orderID ?? 0);
            static::fulfillCartOrder($session->id, $orderID, $memberID);
            session()->forget('store_cart');
        } else {
            $productID = (int) ($session->metadata->productID ?? 0);
            $quantity  = (int) ($session->metadata->quantity ?? 1);
            static::fulfillStoreOrder($session->id, $productID, $memberID, $quantity);
        }

        $order = DB::table('orders')->where('stripeSessionID', $session->id)->first();
        $items = $order
            ? DB::table('order_items as oi')
                ->join('products as p', 'oi.productID', '=', 'p.productID')
                ->select('oi.*', 'p.productName', 'p.productImage', 'p.productSlug')
                ->where('oi.orderID', $order->orderID)
                ->get()
            : collect();

        return view('store.order-complete', compact('session', 'order', 'items'));
    }

    // ─── Fulfillment ───────────────────────────────────────────────────────────

    public static function fulfillStoreOrder(string $sessionId, int $productID, ?int $memberID, int $quantity = 1): void
    {
        if (DB::table('orders')->where('stripeSessionID', $sessionId)->exists()) return;

        $product = DB::table('products')->where('productID', $productID)->first();
        if (!$product) return;

        $orderID = DB::table('orders')->insertGetId([
            'memberID'        => $memberID,
            'orderStatus'     => 'paid',
            'orderTotal'      => $product->productPrice * $quantity,
            'stripeSessionID' => $sessionId,
            'orderNotes'      => null,
            'created_at'      => now(),
            'updated_at'      => now(),
        ], 'orderID');

        DB::table('order_items')->insert([
            'orderID'      => $orderID,
            'productID'    => $productID,
            'itemQuantity' => $quantity,
            'itemPrice'    => $product->productPrice,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        DB::table('products')
            ->where('productID', $productID)
            ->where('productStock', '>=', $quantity)
            ->decrement('productStock', $quantity);
    }

    public static function fulfillCartOrder(string $sessionId, int $orderID, ?int $memberID): void
    {
        $order = DB::table('orders')->where('orderID', $orderID)->first();
        if (!$order || $order->orderStatus === 'paid') return;

        DB::table('orders')->where('orderID', $orderID)->update([
            'orderStatus'     => 'paid',
            'stripeSessionID' => $sessionId,
            'memberID'        => $memberID,
            'updated_at'      => now(),
        ]);

        $items = DB::table('order_items')->where('orderID', $orderID)->get();
        foreach ($items as $item) {
            DB::table('products')
                ->where('productID', $item->productID)
                ->where('productStock', '>=', $item->itemQuantity)
                ->decrement('productStock', $item->itemQuantity);
        }
    }
}
