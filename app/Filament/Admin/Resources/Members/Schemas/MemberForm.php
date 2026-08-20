<?php

namespace App\Filament\Admin\Resources\Members\Schemas;

use App\Models\Member;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Registration code + public /reg link, with copy-to-clipboard.
                // Only shown once the player has a code (i.e. on the edit screen).
                Section::make('Registration')
                    ->description('Public registration link for this player.')
                    ->visible(fn (?Member $record): bool => filled($record?->memberCode))
                    ->schema([
                        Placeholder::make('registration_link')
                            ->hiddenLabel()
                            ->content(fn (?Member $record): HtmlString => self::registrationLinkHtml($record)),
                    ]),
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
                Textarea::make('memberCommentaryNote')
                    ->label('Commentary note')
                    ->rows(3)
                    ->maxLength(600)
                    ->columnSpanFull()
                    ->helperText('A personal touch for the AI commentary — a nickname or AKA, a running joke, or a Soccer Dads story about them. One short paragraph; the commentary uses it sparingly, only where it lands naturally.'),
            ]);
    }

    /**
     * The player's code and their full public /reg URL, with a copy-to-clipboard
     * button. Uses the same client-side navigator.clipboard pattern as the list.
     */
    protected static function registrationLinkHtml(?Member $record): HtmlString
    {
        $code = e($record?->memberCode ?? '');
        $url  = e(url('/reg/' . ($record?->memberCode ?? '')));

        return new HtmlString(<<<HTML
            <div x-data="{ copied: false }" class="flex flex-wrap items-center gap-3 text-sm">
                <span class="inline-flex items-center gap-2">
                    <span class="text-gray-500 dark:text-gray-400">Code</span>
                    <span class="rounded-md bg-gray-100 px-2 py-0.5 font-mono font-semibold text-gray-700 dark:bg-white/10 dark:text-gray-200">{$code}</span>
                </span>
                <a href="{$url}" target="_blank" rel="noopener"
                   class="font-mono text-primary-600 underline decoration-dotted hover:decoration-solid dark:text-primary-400">{$url}</a>
                <button type="button"
                    x-on:click="navigator.clipboard && navigator.clipboard.writeText('{$url}'); copied = true; setTimeout(() => copied = false, 1500)"
                    class="inline-flex items-center gap-1 rounded-md border border-gray-300 px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5">
                    <span x-show="!copied">Copy link</span>
                    <span x-show="copied" x-cloak class="text-success-600 dark:text-success-400">Copied!</span>
                </button>
            </div>
            HTML);
    }
}
