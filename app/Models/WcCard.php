<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WcCard extends Model
{
    protected $table = 'wc_cards';
    protected $primaryKey = 'cardID';

    protected $fillable = [
        'fixtureID',
        'playerID',
        'teamID',
        'type',
        'is_second_yellow',
    ];

    protected $casts = [
        'fixtureID'        => 'integer',
        'playerID'         => 'integer',
        'teamID'           => 'integer',
        'is_second_yellow' => 'boolean',
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
