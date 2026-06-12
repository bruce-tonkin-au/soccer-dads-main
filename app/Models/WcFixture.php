<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WcFixture extends Model
{
    protected $table = 'wc_fixtures';
    protected $primaryKey = 'fixtureID';

    protected $fillable = [
        'stage',
        'group_letter',
        'home_team_id',
        'away_team_id',
        'home_placeholder',
        'away_placeholder',
        'match_datetime',
        'venue',
        'home_score',
        'away_score',
        'status',
        'api_football_id',
    ];

    protected $casts = [
        'home_team_id'    => 'integer',
        'away_team_id'    => 'integer',
        'match_datetime'  => 'datetime',
        'home_score'      => 'integer',
        'away_score'      => 'integer',
        'api_football_id' => 'integer',
    ];

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(WcTeam::class, 'home_team_id', 'teamID');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(WcTeam::class, 'away_team_id', 'teamID');
    }

    public function goals(): HasMany
    {
        return $this->hasMany(WcGoal::class, 'fixtureID', 'fixtureID');
    }
}
