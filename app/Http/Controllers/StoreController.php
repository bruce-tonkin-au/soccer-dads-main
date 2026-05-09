<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

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

    public function checkout(Request $request, string $productSlug)
    {
        $product = DB::table('products')
            ->where('productSlug', $productSlug)
            ->where('productActive', true)
            ->firstOrFail();

        if ($product->productStock <= 0) {
            return back()->with('error', 'Sorry, this product is out of stock.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $memberID = session('player_id');

        $images = $product->productImage ? [$product->productImage] : [];

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency'     => 'aud',
                    'product_data' => [
                        'name'   => $product->productName,
                        'images' => $images,
                    ],
                    'unit_amount' => (int) round($product->productPrice * 100),
                ],
                'quantity' => 1,
            ]],
            'mode'        => 'payment',
            'success_url' => url('/store/order-complete?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url'  => url('/store/' . $productSlug),
            'metadata'    => [
                'type'      => 'store_purchase',
                'productID' => $product->productID,
                'memberID'  => $memberID ?: '',
            ],
        ]);

        return redirect($session->url);
    }

    public function orderComplete(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return redirect('/store');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $session = StripeSession::retrieve($sessionId);
        } catch (\Exception $e) {
            return redirect('/store');
        }

        if ($session->payment_status !== 'paid') {
            return redirect('/store')->with('error', 'Payment not completed.');
        }

        $productID = (int) ($session->metadata->productID ?? 0);
        $memberID  = !empty($session->metadata->memberID) ? (int) $session->metadata->memberID : null;

        static::fulfillStoreOrder($session->id, $productID, $memberID);

        $product = DB::table('products')->where('productID', $productID)->first();
        $order   = DB::table('orders')->where('stripeSessionID', $session->id)->first();

        return view('store.order-complete', compact('session', 'product', 'order'));
    }

    public static function fulfillStoreOrder(string $sessionId, int $productID, ?int $memberID): void
    {
        // Idempotent — skip if order already exists for this session
        if (DB::table('orders')->where('stripeSessionID', $sessionId)->exists()) {
            return;
        }

        $product = DB::table('products')->where('productID', $productID)->first();
        if (!$product) return;

        $orderID = DB::table('orders')->insertGetId([
            'memberID'        => $memberID,
            'orderStatus'     => 'paid',
            'orderTotal'      => $product->productPrice,
            'stripeSessionID' => $sessionId,
            'orderNotes'      => null,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        DB::table('order_items')->insert([
            'orderID'      => $orderID,
            'productID'    => $productID,
            'itemQuantity' => 1,
            'itemPrice'    => $product->productPrice,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        // Decrement stock, floor at 0
        DB::table('products')
            ->where('productID', $productID)
            ->where('productStock', '>', 0)
            ->decrement('productStock');
    }
}
