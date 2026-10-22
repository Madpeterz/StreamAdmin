<?php

namespace App\LegacyApi\Endpoints\Secondlifeapi\Mailserver;

use App\LegacyApi\SlEndpoint;
use App\Models\Message;

/** The owner's mail server object takes the oldest queued IM (the message is the IM text). */
class Next extends SlEndpoint
{
    public function process(): void
    {
        $this->set('hasmessage', false);
        if (! $this->hasAccessOwner()) {
            return;
        }
        $message = Message::with('avatar')->orderBy('id')->first();
        if ($message === null) {
            $this->ok('nowork');

            return;
        }
        if ($message->avatar === null) {
            $this->failed('Unable to find avatar attached to message');

            return;
        }
        $message->delete();
        $this->set('hasmessage', true);
        $this->set('avatarUUID', $message->avatar->avatar_uuid);
        $this->ok($message->message);
    }
}
