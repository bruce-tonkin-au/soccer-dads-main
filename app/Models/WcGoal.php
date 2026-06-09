<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WcGoal extends Model
{
    protected $table = 'wc_goals';
    protected $primaryKey = 'goalID';

    protected $fillable = [
        'fixtureID',
        'playerID',
        'teamID',
        'minute',
        'is_own_goal',
    ];

    protected $casts = [
        'fixtureID'   => 'integer',
        'playerID'    => 'integer',
        'teamID'      => 'integer',
        'minute'      => 'integer',
        'is_own_goal' => 'boolean',
    ];

    public function fixture(): BelongsTo
    {
        return $this->belongsTo(WcFixture::class, 'fixtureID', 'fixtureID');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(WcPlayer::class, 'playerID', 'playerID');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(WcTeam::class, 'teamID', 'teamID');
    }
}
