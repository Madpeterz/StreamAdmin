<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Outbox message delivered in-world by the mail server.
 */
class Message extends Model
{
    protected $table = 'messages';

    public $timestamps = false;

    protected $guarded = ['id'];

    /** @return BelongsTo<Avatar, $this> */
    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Avatar::class, 'avatar_id');
    }
}
