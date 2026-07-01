<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $table = 'products';

    protected $primaryKey = 'productID';

    protected $keyType = 'int';

    // Real Laravel-managed created_at / updated_at (mirrors PlayerRating).
    public $timestamps = true;

    protected $fillable = [
        'productName',
        'productDescription',
        'productPrice',
        'productImage',
        'productStock',
        'productActive',
        'productSlug',
        'productAvailableFrom',
        'productAvailableTo',
        'productMaxQuantity',
    ];

    protected $casts = [
        'productPrice'         => 'decimal:2',
        'productStock'         => 'integer',
        'productActive'        => 'boolean',
        'productAvailableFrom' => 'datetime',
        'productAvailableTo'   => 'datetime',
        'productMaxQuantity'   => 'integer',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'productID', 'productID');
    }

    /**
     * Reconcile the denormalised products.productImage thumbnail from the
     * current product_images set — mirrors the legacy AdminStoreController
     * (uploadProductImage / setPrimaryImage / deleteProductImage): exactly one
     * image is flagged primary, and productImage holds that image's PUBLIC URL
     * (asset('storage/'.imagePath)). The storefront and Stripe read
     * productImage as an absolute URL, so it must stay absolute here.
     * productImage is set to null when there are no images.
     */
    public function syncPrimaryImage(): void
    {
        $images = $this->images()->orderBy('imageOrder')->get();

        if ($images->isEmpty()) {
            $this->forceFill(['productImage' => null])->save();
            return;
        }

        $primary = $images->firstWhere('isPrimary', true);

        // No primary flagged (e.g. first upload, or the previous primary was
        // deleted) — promote the first by order, matching the legacy default.
        if (! $primary) {
            $primary = $images->first();
            ProductImage::where('imageID', $primary->imageID)->update(['isPrimary' => true]);
        }

        // Guarantee a single primary.
        ProductImage::where('productID', $this->productID)
            ->where('imageID', '!=', $primary->imageID)
            ->update(['isPrimary' => false]);

        $this->forceFill([
            'productImage' => asset('storage/' . $primary->imagePath),
        ])->save();
    }
}
