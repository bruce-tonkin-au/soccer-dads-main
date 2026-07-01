<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class Order extends Model
{
    protected $table = 'orders';

    protected $primaryKey = 'orderID';

    protected $keyType = 'int';

    // Real Laravel-managed created_at / updated_at (mirrors Product).
    public $timestamps = true;

    protected $fillable = [
        'memberID',
        'orderStatus',
        'orderTotal',
        'orderNotes',
        'stripeSessionID',
        'orderName',
        'orderEmail',
        'orderPhone',
    ];

    protected $casts = [
        'orderTotal' => 'decimal:2',
    ];

    // Nullable — guest orders leave memberID null.
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'memberID', 'memberID');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'orderID', 'orderID');
    }

    /**
     * Perform the full LIVE Stripe refund transaction for this order and
     * restore stock. This is the SINGLE implementation shared by the legacy
     * AdminStoreController::refundOrder and the Filament OrderResource refund
     * action, so the two paths cannot drift.
     *
     * Mirrors the original refundOrder exactly:
     *  - three guards (already refunded / pending / no stripeSessionID),
     *  - Stripe retrieve + payment_intent guard + Refund::create,
     *  - two catch blocks (Stripe ApiErrorException, generic Exception) that
     *    abort BEFORE any DB write,
     *  - then, only after Stripe confirms, orderStatus → 'refunded' and a
     *    per-item products.productStock increment.
     *
     * Returns a structured result so both a Blade redirect and a Filament
     * notification can consume it (no redirect/response side effects here).
     *
     * @return array{success: bool, message: string}
     */
    public function processRefund(): array
    {
        if ($this->orderStatus === 'refunded') {
            return ['success' => false, 'message' => 'Order #' . $this->orderID . ' has already been refunded.'];
        }

        if ($this->orderStatus === 'pending') {
            return ['success' => false, 'message' => 'Order #' . $this->orderID . ' has not been paid — there is nothing to refund.'];
        }

        if (! $this->stripeSessionID) {
            return ['success' => false, 'message' => 'Order #' . $this->orderID . ' has no Stripe session on record. Issue the refund manually in your Stripe dashboard, then update the order status here.'];
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = StripeSession::retrieve($this->stripeSessionID);

            if (! $session->payment_intent) {
                return ['success' => false, 'message' => 'No payment intent found on the Stripe session. Issue the refund manually in your Stripe dashboard.'];
            }

            \Stripe\Refund::create(['payment_intent' => $session->payment_intent]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return ['success' => false, 'message' => 'Stripe refund failed: ' . $e->getMessage()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Refund failed: ' . $e->getMessage()];
        }

        // Stripe confirmed — now update the order and restore stock.
        $this->update(['orderStatus' => 'refunded']);

        $items = DB::table('order_items')->where('orderID', $this->orderID)->get();
        foreach ($items as $item) {
            DB::table('products')
                ->where('productID', $item->productID)
                ->increment('productStock', $item->itemQuantity);
        }

        return ['success' => true, 'message' => 'Order #' . $this->orderID . ' has been refunded ($' . number_format($this->orderTotal, 2) . ') and stock restored.'];
    }
}
