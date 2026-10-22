<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Static notecard in the notice server's inventory.
 */
class NoticeNotecard extends Model
{
    protected $table = 'notice_notecards';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'missing' => 'boolean',
        ];
    }
}
