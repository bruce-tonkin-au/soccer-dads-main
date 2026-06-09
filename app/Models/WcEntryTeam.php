<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WcEntryTeam extends Model
{
    protected $table = 'wc_entry_teams';
    protected $primaryKey = 'entryTeamID';
    public $timestamps = false;

    protected $fillable = [
        'entryID',
        'teamID',
        'tier',
    ];

    protected $casts = [
        'entryID' => 'integer',
        'teamID'  => 'integer',
        'tier'    => 'integer',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(WcEntry::class, 'entryID', 'entryID');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(WcTeam::class, 'teamID', 'teamID');
    }
}
