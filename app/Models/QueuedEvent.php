<?php

namespace App\Models;

use App\Casts\UnixTimestamp;
use Illuminate\Database\Eloquent\Model;

/**
 * Event waiting for the events server object (events API).
 */
class QueuedEvent extends Model
{
    protected $table = 'queued_events';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'event_unixtime' => UnixTimestamp::class,
        ];
    }
}
