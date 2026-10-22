<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Second Life region seen by in-world objects.
 */
class Region extends Model
{
    protected $table = 'regions';

    public $timestamps = false;

    protected $guarded = ['id'];
}
