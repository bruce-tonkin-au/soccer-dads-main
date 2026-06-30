<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WcEntry extends Model
{
    protected $table = 'wc_entries';
    protected $primaryKey = 'entryID';

    protected $fillable = [
        'memberID',
        'orderID',
        'entry_name',
        'draw_completed',
    ];

    protected $casts = [
        'memberID'       => 'integer',
        'orderID'        => 'integer',
        'draw_completed' => 'boolean',
    ];

    public function entryTeams(): HasMany
    {
        return $this->hasMany(WcEntryTeam::class, 'entryID', 'entryID');
    }

    public function entryPlayers(): HasMany
    {
        return $this->hasMany(WcEntryPlayer::class, 'entryID', 'entryID');
    }

    public function teams()
    {
        return $this->belongsToMany(WcTeam::class, 'wc_entry_teams', 'entryID', 'teamID')
            ->withPivot('tier');
    }

    public function players()
    {
        return $this->belongsToMany(WcPlayer::class, 'wc_entry_players', 'entryID', 'playerID')
            ->withPivot('slot');
    }

    /**
     * Whether the draw is fully assigned: both team tiers (top-24 + bottom-24)
     * and all four player slots. Read fresh from the pivots so it is correct
     * immediately after WcEntryResource::syncDraw() persists selections.
     */
    public function isDrawComplete(): bool
    {
        $tiers = $this->entryTeams()->pluck('tier');
        $slots = $this->entryPlayers()->pluck('slot');

        return $tiers->contains(1) && $tiers->contains(2)
            && collect([1, 2, 3, 4])->every(fn (int $s) => $slots->contains($s));
    }

    /**
     * Live sweepstake points for this entry: one point (× the configurable
     * scoring values from wc_settings) for every goal scored by an assigned
     * team, plus one for every goal scored by an assigned player. Own goals
     * are excluded for players (they don't reward the scorer). Shootout goals
     * are excluded for both — wc_fixtures stores the pre-shootout (90+ET)
     * score, so points must match. Bulk-counted — no per-fixture queries.
     */
    public function calculatePoints(): int
    {
        $scoring = static::scoringSettings();

        $teamIds = $this->entryTeams->pluck('teamID')->all();
        $playerIds = $this->entryPlayers->pluck('playerID')->all();

        // Own goals are included for teams — wc_goals.teamID is the credited
        // (benefiting) team — but excluded for players below.
        $teamGoals = empty($teamIds) ? 0 : WcGoal::query()
            ->whereIn('teamID', $teamIds)
            ->where('is_shootout', false)
            ->count();

        $playerGoals = empty($playerIds) ? 0 : WcGoal::query()
            ->whereIn('playerID', $playerIds)
            ->where('is_own_goal', false)
            ->where('is_shootout', false)
            ->count();

        return ($teamGoals * $scoring['team_goal']) + ($playerGoals * $scoring['player_goal']);
    }

    /**
     * Scoring values from wc_settings, resolved once per request.
     */
    protected static array $scoringCache = [];

    protected static function scoringSettings(): array
    {
        if (! empty(static::$scoringCache)) {
            return static::$scoringCache;
        }

        $values = WcSetting::query()
            ->whereIn('key', ['points_team_goal', 'points_player_goal'])
            ->pluck('value', 'key');

        return static::$scoringCache = [
            'team_goal' => (int) ($values['points_team_goal'] ?? 1),
            'player_goal' => (int) ($values['points_player_goal'] ?? 1),
        ];
    }
}
