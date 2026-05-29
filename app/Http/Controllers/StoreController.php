<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
        $products = static::availabilityQuery()
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

        $maxQty    = min($product->productMaxQuantity, $product->productStock);
        $quantity  = max(1, min((int) $request->input('quantity', 1), $maxQty));
        $memberID  = session('player_id');

        // Resolve customer details
        $orderName     = null;
        $orderEmail    = null;
        $orderPhone    = null;
        $customerEmail = null;

        if ($memberID) {
            $member        = DB::table('members')->where('memberID', $memberID)->first();
            $customerEmail = $member->memberEmail ?? null;
        } else {
            $request->validate([
                'guestName'  => 'required|string|max:255',
                'guestEmail' => 'required|email|max:255',
                'guestPhone' => 'nullable|string|max:50',
            ]);
            $orderName     = trim($request->input('guestName'));
            $orderEmail    = trim($request->input('guestEmail'));
            $orderPhone    = trim($request->input('guestPhone', '')) ?: null;
            $customerEmail = $orderEmail;
        }

        // Pre-create pending order so we have an ID for Stripe metadata
        $orderID = DB::table('orders')->insertGetId([
            'memberID'    => $memberID,
            'orderStatus' => 'pending',
            'orderTotal'  => $product->productPrice * $quantity,
            'orderName'   => $orderName,
            'orderEmail'  => $orderEmail,
            'orderPhone'  => $orderPhone,
            'created_at'  => now(),
            'updated_at'  => now(),
        ], 'orderID');

        DB::table('order_items')->insert([
            'orderID'      => $orderID,
            'productID'    => $product->productID,
            'itemQuantity' => $quantity,
            'itemPrice'    => $product->productPrice,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        $sessionParams = [
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
                'type'     => 'store_purchase',
                'orderID'  => $orderID,
                'memberID' => $memberID ?: '',
            ],
        ];

        if ($customerEmail) {
            $sessionParams['customer_email'] = $customerEmail;
        }

        $session = StripeSession::create($sessionParams);

        DB::table('orders')->where('orderID', $orderID)->update([
            'stripeSessionID' => $session->id,
            'updated_at'      => now(),
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

        $member = session('player_id')
            ? DB::table('members')->where('memberID', session('player_id'))->first()
            : null;

        if (empty($cartData)) {
            return view('store.cart', ['items' => collect(), 'total' => 0, 'member' => $member]);
        }

        $productIDs    = array_keys($cartData);
        $products      = DB::table('products')->whereIn('productID', $productIDs)->get()->keyBy('productID');
        $primaryImages = DB::table('product_images')
            ->whereIn('productID', $productIDs)
            ->where('isPrimary', 1)
            ->get()
            ->keyBy('productID');

        $items = collect($cartData)->map(function ($item) use ($products, $primaryImages) {
            $product = $products[$item['productID']] ?? null;
            if (!$product) return null;
            $primaryImg = $primaryImages[$product->productID] ?? null;
            return (object) [
                'productID'    => $product->productID,
                'productSlug'  => $product->productSlug,
                'productName'  => $product->productName,
                'productPrice' => $product->productPrice,
                'displayImage' => $primaryImg
                    ? asset('storage/' . $primaryImg->imagePath)
                    : $product->productImage,
                'quantity'     => $item['quantity'],
                'lineTotal'    => $product->productPrice * $item['quantity'],
            ];
        })->filter()->values();

        $total = $items->sum('lineTotal');

        return view('store.cart', compact('items', 'total', 'member'));
    }

    public function removeFromCart(Request $request)
    {
        $productID = (int) $request->input('productID');
        $cart = session('store_cart', []);
        unset($cart[$productID]);
        session(['store_cart' => $cart]);
        return back();
    }

    public function cartCheckout(Request $request)
    {
        $cartData = session('store_cart', []);

        if (empty($cartData)) {
            return redirect('/store/cart')->with('error', 'Your cart is empty.');
        }

        $memberID = session('player_id');

        // Resolve customer details
        $orderName     = null;
        $orderEmail    = null;
        $orderPhone    = null;
        $customerEmail = null;

        if ($memberID) {
            $member        = DB::table('members')->where('memberID', $memberID)->first();
            $customerEmail = $member->memberEmail ?? null;
        } else {
            $request->validate([
                'guestName'  => 'required|string|max:255',
                'guestEmail' => 'required|email|max:255',
                'guestPhone' => 'nullable|string|max:50',
            ]);
            $orderName     = trim($request->input('guestName'));
            $orderEmail    = trim($request->input('guestEmail'));
            $orderPhone    = trim($request->input('guestPhone', '')) ?: null;
            $customerEmail = $orderEmail;
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

        // Pre-create a pending order so we have an ID to pass to Stripe
        $orderID = DB::table('orders')->insertGetId([
            'memberID'        => $memberID,
            'orderStatus'     => 'pending',
            'orderTotal'      => $orderTotal,
            'orderName'       => $orderName,
            'orderEmail'      => $orderEmail,
            'orderPhone'      => $orderPhone,
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

        $sessionParams = [
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
        ];

        if ($customerEmail) {
            $sessionParams['customer_email'] = $customerEmail;
        }

        $session = StripeSession::create($sessionParams);

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
        $orderID  = (int) ($session->metadata->orderID ?? 0);

        if ($session->metadata->type === 'store_cart') {
            static::fulfillOrder($session->id, $orderID, $memberID);
            session()->forget('store_cart');
        } else {
            static::fulfillOrder($session->id, $orderID, $memberID);
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

    public static function fulfillOrder(string $sessionId, int $orderID, ?int $memberID): void
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

        static::sendOrderConfirmationEmail($orderID, $memberID);
    }

    private static function sendOrderConfirmationEmail(int $orderID, ?int $memberID): void
    {
        $order = DB::table('orders')->where('orderID', $orderID)->first();
        if (!$order) return;

        $toEmail = null;
        $toName  = null;

        if ($memberID) {
            $member  = DB::table('members')->where('memberID', $memberID)->first();
            $toEmail = $member->memberEmail ?? null;
            $toName  = $member->memberNameFirst ?? null;
        }

        // Guest email as fallback (or primary if no member)
        if (!$toEmail && $order->orderEmail) {
            $toEmail = $order->orderEmail;
            $toName  = $order->orderName ?? null;
        }

        if (!$toEmail) return;

        $items = DB::table('order_items as oi')
            ->join('products as p', 'oi.productID', '=', 'p.productID')
            ->select('oi.*', 'p.productName')
            ->where('oi.orderID', $orderID)
            ->get();

        try {
            Mail::send('emails.order-confirmation', [
                'order'  => $order,
                'items'  => $items,
                'toName' => $toName,
            ], function ($message) use ($toEmail, $order) {
                $message->to($toEmail)
                        ->subject('Order #' . $order->orderID . ' confirmed — Soccer Dads Store');
            });
        } catch (\Exception $e) {
            Log::error('Order confirmation email failed', [
                'orderID' => $orderID,
                'email'   => $toEmail,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    // Keep these as aliases so any existing webhook/external callers don't break
    public static function fulfillStoreOrder(string $sessionId, int $orderID, ?int $memberID): void
    {
        static::fulfillOrder($sessionId, $orderID, $memberID);
    }

    public static function fulfillCartOrder(string $sessionId, int $orderID, ?int $memberID): void
    {
        static::fulfillOrder($sessionId, $orderID, $memberID);
    }
}
