<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
