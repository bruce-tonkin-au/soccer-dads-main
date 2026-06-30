<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerRating extends Model
{
    protected $table = 'player-ratings';

    protected $primaryKey = 'ratingID';

    protected $keyType = 'int';

    // Unlike members/seasons, this table DOES carry Laravel-managed
    // created_at / updated_at columns, so leave timestamps on.
    public $timestamps = true;

    protected $casts = [
        'ratingGoal'      => 'integer',
        'ratingPassing'   => 'integer',
        'ratingWork'      => 'integer',
        'ratingDefending' => 'integer',
        'ratingOverall'   => 'integer',
    ];

    public function rater(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'raterMemberID', 'memberID');
    }

    public function rated(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'ratedMemberID', 'memberID');
    }
}
