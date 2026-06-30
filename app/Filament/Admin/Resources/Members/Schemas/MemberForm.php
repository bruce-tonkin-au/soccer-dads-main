<?php

namespace App\Filament\Admin\Resources\Members\Schemas;

use App\Models\Member;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('memberNameFirst')
                    ->label('First name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('memberNameLast')
                    ->label('Last name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('memberEmail')
                    ->label('Email address')
                    ->email()
                    ->maxLength(255),
                TextInput::make('memberPhoneMobile')
                    ->label('Mobile number')
                    ->tel()
                    ->maxLength(255),
                Select::make('memberParent')
                    ->label('Parent member (if child)')
                    ->options(fn (?Member $record): array => Member::query()
                        ->where('memberActive', 1)
                        ->when($record, fn ($query) => $query->where('memberID', '!=', $record->memberID))
                        ->orderBy('memberNameLast')
                        ->orderBy('memberNameFirst')
                        ->get()
                        ->mapWithKeys(fn (Member $member): array => [
                            $member->memberID => trim($member->memberNameFirst . ' ' . $member->memberNameLast),
                        ])
                        ->all())
                    ->searchable()
                    ->placeholder('None'),
                Select::make('memberActive')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ])
                    ->default(1)
                    ->required()
                    ->selectablePlaceholder(false),
                DatePicker::make('memberBirthday')
                    ->label('Birthday')
                    ->native(false)
                    ->displayFormat('j M Y'),
            ]);
    }
}
