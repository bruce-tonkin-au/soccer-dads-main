<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WcTeam extends Model
{
    protected $table = 'wc_teams';
    protected $primaryKey = 'teamID';

    protected $fillable = [
        'name',
        'code',
        'flag',
        'confederation',
        'fifa_ranking',
        'pot',
        'seed_tier',
        'group_letter',
        'group_position',
        'qualified',
    ];

    protected $casts = [
        'fifa_ranking'   => 'integer',
        'pot'            => 'integer',
        'seed_tier'      => 'integer',
        'group_position' => 'integer',
        'qualified'      => 'boolean',
    ];

    public function players(): HasMany
    {
        return $this->hasMany(WcPlayer::class, 'teamID', 'teamID');
    }

    public function homeFixtures(): HasMany
    {
        return $this->hasMany(WcFixture::class, 'home_team_id', 'teamID');
    }

    public function awayFixtures(): HasMany
    {
        return $this->hasMany(WcFixture::class, 'away_team_id', 'teamID');
    }

    public function goals(): HasMany
    {
        return $this->hasMany(WcGoal::class, 'teamID', 'teamID');
    }
}
