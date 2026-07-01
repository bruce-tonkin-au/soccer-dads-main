<?php

namespace App\Filament\Admin\Resources\Products\RelationManagers;

use App\Models\ProductImage;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'Images';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Writes to the SAME disk + directory as the legacy admin
                // (AdminStoreController::uploadProductImage): disk 'public',
                // directory 'products/{productID}'. Filament stores the
                // disk-relative path ('products/{id}/{file}') into
                // product_images.imagePath — the exact shape the storefront
                // resolves via asset('storage/'.imagePath).
                FileUpload::make('imagePath')
                    ->label('Image')
                    ->image()
                    ->disk('public')
                    ->directory(fn (): string => 'products/' . $this->getOwnerRecord()->productID)
                    ->visibility('public')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(5120)
                    ->required(),
                TextInput::make('imageAlt')
                    ->label('Alt text')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('imagePath')
            ->defaultSort('imageOrder')
            ->reorderable('imageOrder')
            ->columns([
                ImageColumn::make('imagePath')
                    ->label('Image')
                    ->disk('public')
                    ->square()
                    ->size(64),
                TextColumn::make('imageAlt')
                    ->label('Alt text')
                    ->placeholder('—'),
                TextColumn::make('isPrimary')
                    ->label('Primary')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Primary' : '—')
                    ->color(fn ($state): string => $state ? 'success' : 'gray'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add image')
                    // Append to the end of the current order.
                    ->mutateDataUsing(function (array $data): array {
                        $data['imageOrder'] = (int) ($this->getOwnerRecord()->images()->max('imageOrder') ?? -1) + 1;
                        return $data;
                    })
                    // Promote the first image to primary + refresh the
                    // denormalised products.productImage thumbnail.
                    ->after(fn () => $this->getOwnerRecord()->syncPrimaryImage()),
            ])
            ->recordActions([
                // Mirrors AdminStoreController::setPrimaryImage.
                Action::make('makePrimary')
                    ->label('Make primary')
                    ->icon('heroicon-o-star')
                    ->visible(fn (ProductImage $record): bool => ! $record->isPrimary)
                    ->action(function (ProductImage $record): void {
                        ProductImage::where('productID', $record->productID)
                            ->update(['isPrimary' => false]);
                        $record->update(['isPrimary' => true]);
                        $this->getOwnerRecord()->syncPrimaryImage();
                    }),
                // Mirrors AdminStoreController::deleteProductImage — remove the
                // physical file, then re-elect a primary and resync the thumbnail.
                DeleteAction::make()
                    ->after(function (ProductImage $record): void {
                        Storage::disk('public')->delete($record->imagePath);
                        $this->getOwnerRecord()->syncPrimaryImage();
                    }),
            ]);
    }
}
