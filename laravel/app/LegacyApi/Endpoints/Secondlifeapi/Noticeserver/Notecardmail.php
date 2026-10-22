<?php

namespace App\LegacyApi\Endpoints\Secondlifeapi\Noticeserver;

use App\LegacyApi\SlEndpoint;
use App\Models\NotecardMail as QueuedNotecard;
use App\Models\NoticeNotecard;

/**
 * The owner's notice server object takes the oldest static notecard to give. Entries that can
 * never be sent are dropped for good (fixed in 2.0.1.3; before, the queue stalled on them).
 */
class Notecardmail extends SlEndpoint
{
    public function process(): void
    {
        $this->set('recheck', false);
        if (! $this->hasAccessOwner()) {
            return;
        }
        $mail = QueuedNotecard::with('avatar')->orderBy('id')->first();
        if ($mail === null) {
            $this->set('status', true);
            $this->set('message', 'nowork');

            return;
        }
        $this->set('recheck', true);
        $notecard = NoticeNotecard::find($mail->notice_notecard_id);
        $problem = match (true) {
            $notecard === null => 'Unable to load notecard from db',
            $notecard->missing => 'Missing notecard please recheck',
            $notecard->id <= 1 => 'Invaild notecard please recheck',
            $mail->avatar === null => 'Unable to load avatar from db',
            default => null,
        };
        $mail->delete();
        if ($problem !== null) {
            $this->commitNow();
            $this->set('message', $problem);

            return;
        }
        $this->set('status', true);
        $this->set('message', 'send');
        $this->set('avatarUUID', $mail->avatar->avatar_uuid);
        $this->set('notecard', $notecard->name);
    }
}
