<?php

namespace App\Filament\Admin\Resources\Products\Pages;

use App\Filament\Admin\Resources\Products\ProductResource;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    // No delete action — the legacy admin has no product delete, only the
    // active/inactive toggle. The image gallery relation manager renders
    // beneath the form on this edit page.
    protected function getHeaderActions(): array
    {
        return [];
    }
}
