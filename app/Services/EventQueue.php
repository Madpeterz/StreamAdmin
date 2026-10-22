<?php

namespace App\Services;

use App\Models\Avatar;
use App\Models\Package;
use App\Models\QueuedEvent;
use App\Models\Rental;
use App\Models\Server;
use App\Models\SlConfig;
use App\Models\Stream;

/**
 * Events for the in-world events server object (only queued when the events API is enabled).
 * The payload format is part of the in-world API (owner/events/next, docs/api-v3.md).
 */
class EventQueue
{
    public function push(
        string $name,
        ?Package $package = null,
        ?Avatar $avatar = null,
        ?Server $server = null,
        ?Stream $stream = null,
        ?Rental $rental = null,
        ?int $amountPaid = null,
        ?Avatar $paidBy = null,
    ): void {
        if (! SlConfig::find(1)?->events_api) {
            return;
        }
        $payload = array_filter([
            'package' => $package?->name,
            'uuid' => $avatar?->avatar_uuid,
            'name' => $avatar?->avatar_name,
            'server' => $server?->domain,
            'port' => $stream?->port,
            'uid' => $rental?->rental_uid,
            'amount' => $amountPaid ?: null,
        ], fn ($value) => $value !== null);
        if ($paidBy && $avatar && $paidBy->id !== $avatar->id) {
            $payload += ['viaProxy' => true, 'uuidProxy' => $paidBy->avatar_uuid, 'nameProxy' => $paidBy->avatar_name];
        }
        $payload['unixtime'] = time();

        QueuedEvent::create([
            'event_name' => $name,
            'event_message' => json_encode($payload),
            'event_unixtime' => time(),
        ]);
    }
}
