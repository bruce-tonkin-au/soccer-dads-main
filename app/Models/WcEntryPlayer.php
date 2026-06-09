<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WcEntryPlayer extends Model
{
    protected $table = 'wc_entry_players';
    protected $primaryKey = 'entryPlayerID';
    public $timestamps = false;

    protected $fillable = [
        'entryID',
        'playerID',
        'slot',
    ];

    protected $casts = [
        'entryID'  => 'integer',
        'playerID' => 'integer',
        'slot'     => 'integer',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(WcEntry::class, 'entryID', 'entryID');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(WcPlayer::class, 'playerID', 'playerID');
    }
}
