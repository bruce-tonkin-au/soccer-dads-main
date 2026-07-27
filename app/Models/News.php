<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $table = 'news';

    protected $primaryKey = 'newsID';

    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'newsTitle',
        'newsImage',
        'newsBody',
        'newsDate',
        'newsActive',
    ];

    protected $casts = [
        'newsDate' => 'date',
        'newsActive' => 'integer',
    ];
}
