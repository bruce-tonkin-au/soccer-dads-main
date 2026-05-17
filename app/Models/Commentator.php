<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commentator extends Model
{
    protected $table = 'commentators';
    protected $primaryKey = 'commentatorID';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'commentatorElevenLabsID',
        'commentatorNameFirst',
        'commentatorNameLast',
        'commentatorAge',
        'commentatorAccent',
        'commentatorBackground',
        'commentatorStyle',
        'commentatorFacts',
        'commentatorActive',
        'commentatorVisible',
    ];

    protected $casts = [
        'commentatorActive'  => 'boolean',
        'commentatorVisible' => 'boolean',
    ];
}
