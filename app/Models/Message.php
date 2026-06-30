<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';

    protected $primaryKey = 'messageID';

    protected $keyType = 'int';

    // Unlike Season/Commentator, this table uses real Laravel-managed
    // created_at / updated_at columns (no *Created/*Edited, no DB trigger).
    public $timestamps = true;

    protected $fillable = [
        'messageCode',
        'messageSubject',
        'messageBody',
        'messageActive',
    ];

    protected $casts = [
        'messageActive' => 'integer',
    ];

    // Filament resolves records by the primary key (messageID), which is fine
    // here — the public/legacy routes key on messageCode, but the Filament
    // panel does not need to. Default key kept deliberately.
}
