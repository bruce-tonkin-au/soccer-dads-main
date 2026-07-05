<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Member extends Model
{
    protected $table = 'members';

    protected $primaryKey = 'memberID';

    protected $keyType = 'int';

    // Legacy table from the SQL dump — no created_at/updated_at columns.
    // memberCreated / memberEdited carry their own DB defaults.
    public $timestamps = false;

    protected $fillable = [
        'memberNameFirst',
        'memberNameLast',
        'memberEmail',
        'memberPhoneMobile',
        'memberCode',
        'memberSlug',
        'memberActive',
        'memberParent',
        'memberBirthday',
        'memberCountry',
    ];

    protected $casts = [
        'memberActive'    => 'integer',
        'memberParent'    => 'integer',
        'memberBirthday'  => 'date',
        'memberClaimed'   => 'boolean',
        'memberClaimedAt' => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'memberParent', 'memberID');
    }

    /**
     * Nights this member is linked to, with the access flags on the pivot:
     *   allowed (admin grant) and hidden (member's own hide switch).
     * Explicit keys because the legacy PKs are memberID / nightID, not id.
     * Added for the foundation only — nothing consumes it yet.
     */
    public function nights(): BelongsToMany
    {
        return $this->belongsToMany(
            Night::class,
            'member_nights',
            'memberID',   // this model's FK on the pivot
            'nightID',    // related model's FK on the pivot
            'memberID',   // this model's PK
            'nightID',    // related model's PK
        )->withPivot(['allowed', 'hidden']);
    }
}
