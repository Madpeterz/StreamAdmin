<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Display timezone option.
 */
class Timezone extends Model
{
    protected $table = 'timezones';

    public $timestamps = false;

    protected $guarded = ['id'];
}
