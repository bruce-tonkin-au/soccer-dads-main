<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    protected $table = 'seasons';

    protected $primaryKey = 'seasonID';

    protected $keyType = 'int';

    // Legacy table from the SQL dump — no created_at/updated_at columns.
    // seasonCreated / seasonEdited carry their own DB defaults;
    // seasonEdited is maintained by a DB trigger, not Laravel.
    public $timestamps = false;

    protected $fillable = [
        'seasonName',
        'seasonLink',
        'seasonVisible',
        'nightID',
    ];

    protected $casts = [
        'seasonVisible' => 'integer',
    ];
}
