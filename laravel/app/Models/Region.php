<?php

namespace App\Models;

use App\Casts\HtmlEncoded;
use Illuminate\Database\Eloquent\Model;

/**
 * Second Life region seen by in-world objects.
 */
class Region extends Model
{
    protected $table = 'regions';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'name' => HtmlEncoded::class,
        ];
    }
}
