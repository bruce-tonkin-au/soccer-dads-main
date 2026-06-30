<?php

namespace App\Filament\Admin\Resources\Commentators\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CommentatorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('commentatorNameFirst')
                    ->label('First Name')
                    ->required()
                    ->maxLength(32),
                TextInput::make('commentatorNameLast')
                    ->label('Last Name')
                    ->required()
                    ->maxLength(32),
                TextInput::make('commentatorAge')
                    ->label('Age')
                    ->maxLength(2),
                TextInput::make('commentatorElevenLabsID')
                    ->label('ElevenLabs Voice ID')
                    ->maxLength(32)
                    ->helperText('Used verbatim by the scoring app for TTS.'),
                Textarea::make('commentatorAccent')
                    ->label('Accent / Voice')
                    ->rows(3),
                Textarea::make('commentatorBackground')
                    ->label('Background & Personality')
                    ->rows(5),
                Textarea::make('commentatorStyle')
                    ->label('Commentary Style')
                    ->rows(5),
                Textarea::make('commentatorFacts')
                    ->label('Fun Facts / Catchphrases')
                    ->rows(5),
                Toggle::make('commentatorActive')
                    ->label('Active')
                    ->default(true),
                Toggle::make('commentatorVisible')
                    ->label('Visible')
                    ->default(true),
                Toggle::make('commentatorDefault')
                    ->label('Set as default')
                    ->default(false),
            ]);
    }
}
