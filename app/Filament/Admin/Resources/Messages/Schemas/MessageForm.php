<?php

namespace App\Filament\Admin\Resources\Messages\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Schema;

class MessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('messageSubject')
                    ->label('Subject')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g. Friday night — are you in?'),
                // Plain RichEditor: merge tags are typed/inserted as literal
                // {{tag}} text and stored verbatim in the HTML body, so the
                // public MessageController + MessageMerge regex keep working.
                // (Filament's native mergeTags() is deliberately NOT used — it
                // stores structured nodes, not literal {{tag}} text.)
                RichEditor::make('messageBody')
                    ->label('Message body')
                    ->required()
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link'],
                    ]),
                // Custom merge-variable helper: clickable chips that insert
                // {{tag}} at the cursor + a client-side preview. Not a real
                // column — excluded from the saved data.
                ViewField::make('mergeHelper')
                    ->view('filament.admin.messages.merge-helper')
                    ->dehydrated(false),
                Select::make('messageActive')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ])
                    ->default(1)
                    ->selectablePlaceholder(false)
                    // Legacy edit screen has the status select; create defaults
                    // to active with no field shown (set in the create page).
                    ->visibleOn('edit'),
            ]);
    }
}
