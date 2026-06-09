<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    protected $table = 'newsletters';
    protected $primaryKey = 'newsletterID';

    protected $fillable = [
        'subject',
        'body',
    ];
}
