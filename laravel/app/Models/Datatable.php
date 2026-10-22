<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Legacy list column settings.
 */
class Datatable extends Model
{
    protected $table = 'datatables';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'hide_col_zero' => 'boolean',
        ];
    }
}
