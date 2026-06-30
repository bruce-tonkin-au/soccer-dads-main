<?php

namespace App\Filament\Admin\Resources\Ratings\Tables;

use App\Filament\Admin\Resources\Ratings\RatingResource;
use App\Models\PlayerRating;
use Filament\Actions\Action;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class RatingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // One row per rated player (grouped aggregate), like the legacy
            // summary screen — so pagination over groups is disabled.
            ->paginated(false)
            ->defaultSort('compositeRating', 'desc')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->from('player-ratings as r')
                ->join('members as m', 'r.ratedMemberID', '=', 'm.memberID')
                ->groupBy('r.ratedMemberID', 'm.memberNameFirst', 'm.memberNameLast')
                ->select(
                    'r.ratedMemberID',
                    'm.memberNameFirst',
                    'm.memberNameLast',
                    DB::raw('COUNT(*) as "ratingCount"'),
                    DB::raw('ROUND(AVG(r."ratingGoal"), 1) as "avgGoal"'),
                    DB::raw('ROUND(AVG(r."ratingPassing"), 1) as "avgPassing"'),
                    DB::raw('ROUND(AVG(r."ratingWork"), 1) as "avgWork"'),
                    DB::raw('ROUND(AVG(r."ratingDefending"), 1) as "avgDefending"'),
                    DB::raw('ROUND(AVG(r."ratingOverall"), 1) as "avgOverall"'),
                    DB::raw('ROUND((AVG(r."ratingGoal") + AVG(r."ratingPassing") + AVG(r."ratingWork") + AVG(r."ratingDefending") + AVG(r."ratingOverall")) / 5 * 24.75, 0) as "compositeRating"')
                ))
            ->columns([
                TextColumn::make('memberNameLast')
                    ->label('Player')
                    ->formatStateUsing(fn (PlayerRating $record): string => trim($record->memberNameFirst . ' ' . $record->memberNameLast))
                    ->weight(FontWeight::Medium),
                TextColumn::make('ratingCount')
                    ->label('Ratings')
                    ->alignCenter()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('avgGoal')
                    ->label('Goal')
                    ->alignCenter()
                    ->badge()
                    ->color(Color::Green)
                    ->formatStateUsing(fn ($state): string => $state . '/4')
                    ->sortable(),
                TextColumn::make('avgPassing')
                    ->label('Passing')
                    ->alignCenter()
                    ->badge()
                    ->color(Color::Blue)
                    ->formatStateUsing(fn ($state): string => $state . '/4')
                    ->sortable(),
                TextColumn::make('avgWork')
                    ->label('Work')
                    ->alignCenter()
                    ->badge()
                    ->color(Color::Orange)
                    ->formatStateUsing(fn ($state): string => $state . '/4')
                    ->sortable(),
                TextColumn::make('avgDefending')
                    ->label('Defending')
                    ->alignCenter()
                    ->badge()
                    ->color(Color::Purple)
                    ->formatStateUsing(fn ($state): string => $state . '/4')
                    ->sortable(),
                TextColumn::make('avgOverall')
                    ->label('Overall')
                    ->alignCenter()
                    ->badge()
                    ->color(Color::Amber)
                    ->formatStateUsing(fn ($state): string => $state . '/4')
                    ->sortable(),
                TextColumn::make('compositeRating')
                    ->label('Composite')
                    ->alignCenter()
                    ->weight(FontWeight::Bold)
                    ->size(TextSize::Large)
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-chart-bar')
                    ->color('gray')
                    ->url(fn (PlayerRating $record): string => RatingResource::getUrl('player', [
                        'ratedMemberID' => $record->ratedMemberID,
                    ])),
            ]);
    }
}
