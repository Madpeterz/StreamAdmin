<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bot settings (single row).
 */
class BotConfig extends Model
{
    protected $table = 'bot_configs';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'notecards' => 'boolean',
            'ims' => 'boolean',
            'invites' => 'boolean',
            'http_mode' => 'boolean',
        ];
    }

    /** @return BelongsTo<Avatar, $this> */
    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Avatar::class, 'avatar_id');
    }
}
