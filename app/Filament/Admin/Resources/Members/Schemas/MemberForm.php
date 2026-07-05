<?php

namespace App\Filament\Admin\Resources\Members\Schemas;

use App\Models\Member;
use Filament\Forms\Components\CheckboxList;
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
                Select::make('memberCountry')
                    ->label('Country')
                    ->options([
                        'AU' => 'Australia',
                        'NZ' => 'New Zealand',
                        'GB' => 'United Kingdom',
                        'IE' => 'Ireland',
                        'US' => 'United States',
                        'CA' => 'Canada',
                        'ZA' => 'South Africa',
                        'IN' => 'India',
                        'DE' => 'Germany',
                        'FR' => 'France',
                        'IT' => 'Italy',
                        'ES' => 'Spain',
                        'NL' => 'Netherlands',
                        'BR' => 'Brazil',
                        'AR' => 'Argentina',
                        'JP' => 'Japan',
                        'CN' => 'China',
                        'PH' => 'Philippines',
                    ])
                    ->default('AU')
                    ->searchable()
                    ->native(false)
                    ->helperText('2-letter country code used for the flag on the players list.'),
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
                    ->placeholder('None')
                    // Legacy data stores 0 for "no parent" — treat 0 as None and
                    // never write it back (save null when None is chosen).
                    ->dehydrateStateUsing(fn ($state) => (filled($state) && (int) $state !== 0) ? $state : null),
                Select::make('memberActive')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ])
                    ->default(1)
                    ->required()
                    ->selectablePlaceholder(false),
                CheckboxList::make('nights')
                    ->label('Night access')
                    ->helperText('Which nights this player can access. The player can hide a night themselves later.')
                    ->relationship(
                        name: 'nights',
                        titleAttribute: 'nightName',
                        modifyQueryUsing: fn ($query) => $query->where('nightActive', 1),
                    )
                    ->pivotData(['allowed' => 1]),
                DatePicker::make('memberBirthday')
                    ->label('Birthday')
                    ->native(false)
                    ->displayFormat('j M Y'),
            ]);
    }
}
