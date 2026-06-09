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
     * Live sweepstake points for this entry: team results + player goals,
     * using the configurable scoring values from wc_settings. Only completed
     * fixtures count.
     */
    public function calculatePoints(): int
    {
        $scoring = static::scoringSettings();

        $teamPoints = $this->entryTeams
            ->sum(fn (WcEntryTeam $et) => static::teamPoints((int) $et->teamID, $scoring));

        $playerIds = $this->entryPlayers->pluck('playerID')->all();

        $goals = empty($playerIds) ? 0 : WcGoal::query()
            ->whereIn('playerID', $playerIds)
            ->where('is_own_goal', false)
            ->whereHas('fixture', fn ($q) => $q->where('status', 'completed'))
            ->count();

        return $teamPoints + ($goals * $scoring['goal']);
    }

    /**
     * Points earned by a single team across its completed fixtures.
     */
    protected static function teamPoints(int $teamID, array $scoring): int
    {
        $fixtures = WcFixture::query()
            ->where('status', 'completed')
            ->whereNotNull('home_score')
            ->whereNotNull('away_score')
            ->where(fn ($q) => $q->where('home_team_id', $teamID)->orWhere('away_team_id', $teamID))
            ->get(['home_team_id', 'home_score', 'away_score']);

        $points = 0;

        foreach ($fixtures as $fixture) {
            $isHome = (int) $fixture->home_team_id === $teamID;
            $for = $isHome ? $fixture->home_score : $fixture->away_score;
            $against = $isHome ? $fixture->away_score : $fixture->home_score;

            if ($for > $against) {
                $points += $scoring['win'];
            } elseif ($for === $against) {
                $points += $scoring['draw'];
            }
        }

        return $points;
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
            ->whereIn('key', ['points_team_win', 'points_team_draw', 'points_player_goal'])
            ->pluck('value', 'key');

        return static::$scoringCache = [
            'win' => (int) ($values['points_team_win'] ?? 3),
            'draw' => (int) ($values['points_team_draw'] ?? 1),
            'goal' => (int) ($values['points_player_goal'] ?? 2),
        ];
    }
}
