<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Night extends Model
{
    protected $table = 'nights';

    protected $primaryKey = 'nightID';

    protected $keyType = 'int';

    // Legacy-style table — no created_at/updated_at columns.
    // nightCreated / nightEdited carry their own DB defaults.
    public $timestamps = false;

    protected $fillable = [
        'nightName',
        'nightVenue',
        'nightAddress',
        'nightDayOfWeek',
        'nightActive',
        'nightSort',
    ];

    protected $casts = [
        'nightActive'    => 'integer',
        'nightSort'      => 'integer',
        'nightDayOfWeek' => 'integer',
    ];
}
