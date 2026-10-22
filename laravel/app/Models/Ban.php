<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Avatar banned from renting.
 */
class Ban extends Model
{
    protected $table = 'bans';

    public $timestamps = false;

    protected $guarded = ['id'];

    /** @return BelongsTo<Avatar, $this> */
    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Avatar::class, 'avatar_id');
    }
}
