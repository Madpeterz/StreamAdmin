<?php

namespace App\LegacyApi\Endpoints\Secondlifeapi\Eventq;

use App\LegacyApi\SlEndpoint;
use App\Models\QueuedEvent;

/** The owner's events server object takes the oldest queued rental event (eventMessage is JSON text). */
class Next extends SlEndpoint
{
    public function process(): void
    {
        $this->set('hasmessage', false);
        if (! $this->hasAccessOwner()) {
            return;
        }
        $event = QueuedEvent::orderBy('id')->first();
        if ($event === null) {
            $this->ok('nowork');

            return;
        }
        $this->set('hasmessage', true);
        $this->set('eventName', $event->event_name);
        $this->set('eventMessage', $event->event_message);
        $event->delete();
        $this->ok('ok');
    }
}
