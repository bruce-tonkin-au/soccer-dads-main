<?php

namespace App\Filament\Admin\Resources\Ratings\Pages;

use App\Filament\Admin\Resources\Ratings\RatingResource;
use App\Models\Member;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class PlayerRatingDetail extends Page
{
    protected static string $resource = RatingResource::class;

    protected string $view = 'filament.admin.ratings.player-detail';

    // Hidden from navigation — reached only via the summary list's Detail action.
    public ?Member $player = null;

    /** @var array<string, mixed>|null */
    public ?array $averages = null;

    /** @var array<int, array<string, mixed>> */
    public array $ratings = [];

    public function mount(int | string $ratedMemberID): void
    {
        $this->player = Member::query()
            ->where('memberID', $ratedMemberID)
            ->firstOrFail();

        $this->ratings = DB::table('player-ratings as r')
            ->join('members as rater', 'r.raterMemberID', '=', 'rater.memberID')
            ->where('r.ratedMemberID', $ratedMemberID)
            ->select('r.*', 'rater.memberNameFirst', 'rater.memberNameLast')
            ->orderBy('r.created_at', 'desc')
            ->get()
            ->map(fn ($row): array => (array) $row)
            ->all();

        $this->averages = (array) DB::table('player-ratings')
            ->where('ratedMemberID', $ratedMemberID)
            ->select(
                DB::raw('ROUND(AVG("ratingGoal"), 2) as "avgGoal"'),
                DB::raw('ROUND(AVG("ratingPassing"), 2) as "avgPassing"'),
                DB::raw('ROUND(AVG("ratingWork"), 2) as "avgWork"'),
                DB::raw('ROUND(AVG("ratingDefending"), 2) as "avgDefending"'),
                DB::raw('ROUND(AVG("ratingOverall"), 2) as "avgOverall"'),
                DB::raw('COUNT(*) as total')
            )
            ->first();
    }

    public function getTitle(): string
    {
        return trim($this->player->memberNameFirst . ' ' . $this->player->memberNameLast) . ' — Ratings';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to ratings')
                ->icon('heroicon-o-chevron-left')
                ->color('gray')
                ->url(RatingResource::getUrl('index')),
        ];
    }
}
