<?php

namespace App\Filament\Resources\WcEntries;

use App\Filament\Resources\WcEntries\Pages\CreateWcEntry;
use App\Filament\Resources\WcEntries\Pages\EditWcEntry;
use App\Filament\Resources\WcEntries\Pages\ListWcEntries;
use App\Filament\Resources\WcEntries\Schemas\WcEntryForm;
use App\Filament\Resources\WcEntries\Tables\WcEntriesTable;
use App\Models\WcEntry;
use App\Models\WcEntryPlayer;
use App\Models\WcEntryTeam;
use App\Models\WcPlayer;
use App\Models\WcTeam;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class WcEntryResource extends Resource
{
    protected static ?string $model = WcEntry::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static string|UnitEnum|null $navigationGroup = 'World Cup 2026';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'entry_name';

    protected static ?string $modelLabel = 'entry';

    public static function form(Schema $schema): Schema
    {
        return WcEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WcEntriesTable::configure($table);
    }

    /**
     * Team options for a seed tier, keyed by teamID: "Flag Name".
     */
    public static function teamOptions(int $tier): array
    {
        return WcTeam::query()
            ->where('seed_tier', $tier)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (WcTeam $t) => [
                $t->teamID => trim(($t->flag ? $t->flag . ' ' : '') . $t->name),
            ])
            ->all();
    }

    /**
     * Full active player pool, keyed by playerID: "Flag Team — #shirt Name".
     */
    public static function playerOptions(): array
    {
        return WcPlayer::query()
            ->with('team')
            ->where('is_active', true)
            ->orderBy('teamID')
            ->orderBy('shirt_number')
            ->get()
            ->mapWithKeys(fn (WcPlayer $p) => [
                $p->playerID => trim(
                    ($p->team?->flag ? $p->team->flag . ' ' : '')
                    . ($p->team?->name ? $p->team->name . ' — ' : '')
                    . ($p->shirt_number ? '#' . $p->shirt_number . ' ' : '')
                    . $p->name
                ),
            ])
            ->all();
    }

    /**
     * Persist the manual-draw selections into the pivot tables. Called from the
     * Create and Edit pages after the entry row itself is saved.
     */
    public static function syncDraw(WcEntry $entry, array $data): void
    {
        static::syncTeam($entry->entryID, 1, $data['tier1_team'] ?? null);
        static::syncTeam($entry->entryID, 2, $data['tier2_team'] ?? null);
        static::syncPlayer($entry->entryID, 1, $data['player_slot1'] ?? null);
        static::syncPlayer($entry->entryID, 2, $data['player_slot2'] ?? null);
    }

    protected static function syncTeam(int $entryID, int $tier, $teamID): void
    {
        if (blank($teamID)) {
            WcEntryTeam::where('entryID', $entryID)->where('tier', $tier)->delete();

            return;
        }

        WcEntryTeam::updateOrCreate(
            ['entryID' => $entryID, 'tier' => $tier],
            ['teamID' => (int) $teamID],
        );
    }

    protected static function syncPlayer(int $entryID, int $slot, $playerID): void
    {
        if (blank($playerID)) {
            WcEntryPlayer::where('entryID', $entryID)->where('slot', $slot)->delete();

            return;
        }

        WcEntryPlayer::updateOrCreate(
            ['entryID' => $entryID, 'slot' => $slot],
            ['playerID' => (int) $playerID],
        );
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWcEntries::route('/'),
            'create' => CreateWcEntry::route('/create'),
            'edit' => EditWcEntry::route('/{record}/edit'),
        ];
    }
}
