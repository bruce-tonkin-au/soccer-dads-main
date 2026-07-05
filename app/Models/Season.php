<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function night(): BelongsTo
    {
        return $this->belongsTo(Night::class, 'nightID', 'nightID');
    }
}
