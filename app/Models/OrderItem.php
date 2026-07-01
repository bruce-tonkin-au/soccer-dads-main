<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $table = 'order_items';

    protected $primaryKey = 'itemID';

    protected $keyType = 'int';

    // Real Laravel-managed created_at / updated_at (mirrors Product).
    public $timestamps = true;

    protected $fillable = [
        'orderID',
        'productID',
        'itemQuantity',
        'itemPrice',
    ];

    protected $casts = [
        'itemQuantity' => 'integer',
        'itemPrice'    => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'orderID', 'orderID');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'productID', 'productID');
    }
}
