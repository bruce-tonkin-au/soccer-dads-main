<?php

namespace App\Filament\Admin\Resources\Products\Pages;

use App\Filament\Admin\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    // Auto-generate a unique slug from the name when left blank, mirroring
    // AdminStoreController::storeProduct (Str::slug + "-2", "-3", … on clash).
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['productSlug'])) {
            $data['productSlug'] = $this->uniqueSlug(Str::slug($data['productName']));
        }

        return $data;
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base;
        $i = 2;
        while (Product::where('productSlug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
