<?php

namespace App\Filament\Admin\Resources\Members\Pages;

use App\Filament\Admin\Resources\Members\MemberResource;
use App\Models\Member;
use App\Models\Night;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListMembers extends ListRecords
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    /**
     * "All players" (unfiltered) plus one tab per active night, driven from the
     * nights table (not hardcoded). Each night tab filters to members granted
     * access to that night (member_nights.allowed = 1) and shows a count badge.
     */
    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('All players')
                ->badge(Member::query()->count()),
        ];

        $nights = Night::query()
            ->where('nightActive', 1)
            ->orderBy('nightSort')
            ->get();

        foreach ($nights as $night) {
            $memberIDs = DB::table('member_nights')
                ->where('nightID', $night->nightID)
                ->where('allowed', 1)
                ->pluck('memberID');

            $tabs['night_' . $night->nightID] = Tab::make($night->nightName)
                ->badge($memberIDs->count())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('members.memberID', $memberIDs));
        }

        return $tabs;
    }
}
