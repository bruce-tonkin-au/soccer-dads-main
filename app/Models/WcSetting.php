<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WcSetting extends Model
{
    protected $table = 'wc_settings';
    protected $primaryKey = 'settingID';

    protected $fillable = [
        'key',
        'value',
        'label',
    ];
}
