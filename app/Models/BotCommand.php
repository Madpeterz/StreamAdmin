<?php

namespace App\Models;

use App\Casts\UnixTimestamp;
use Illuminate\Database\Eloquent\Model;

/**
 * Queued command for the SecondBot bot.
 */
class BotCommand extends Model
{
    protected $table = 'bot_commands';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'unixtime' => UnixTimestamp::class,
        ];
    }
}
