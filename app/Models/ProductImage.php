<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $table = 'product_images';

    protected $primaryKey = 'imageID';

    protected $keyType = 'int';

    // Real Laravel-managed created_at / updated_at (mirrors PlayerRating).
    public $timestamps = true;

    protected $fillable = [
        'productID',
        'imagePath',
        'imageOrder',
        'imageAlt',
        'isPrimary',
    ];

    protected $casts = [
        'imageOrder' => 'integer',
        'isPrimary'  => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'productID', 'productID');
    }
}
