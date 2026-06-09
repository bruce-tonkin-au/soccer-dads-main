<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WcPlayer extends Model
{
    protected $table = 'wc_players';
    protected $primaryKey = 'playerID';

    protected $fillable = [
        'teamID',
        'name',
        'position',
        'shirt_number',
        'is_active',
    ];

    protected $casts = [
        'teamID'       => 'integer',
        'shirt_number' => 'integer',
        'is_active'    => 'boolean',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(WcTeam::class, 'teamID', 'teamID');
    }

    public function goals(): HasMany
    {
        return $this->hasMany(WcGoal::class, 'playerID', 'playerID');
    }
}
