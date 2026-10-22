<?php

namespace App\LegacyApi\Endpoints\Secondlifeapi\Noticeserver;

use App\LegacyApi\SlEndpoint;
use App\Models\NotecardMail;
use App\Models\Notice;
use App\Models\NoticeNotecard;
use App\Models\Package;

/**
 * The owner's notice server object reports the notecards in its inventory ("notecards", comma
 * separated). Known ones are marked found or missing, new ones added, and notecards that were
 * already missing and are not used by a notice or queued are removed (packages fall back to none).
 */
class Updatenotecards extends SlEndpoint
{
    public function process(): void
    {
        if (! $this->hasAccessOwner()) {
            return;
        }
        $inventory = explode(',', $this->input->string('notecards') ?? '');

        // found again
        NoticeNotecard::whereKeyNot(1)->where('missing', true)->whereIn('name', $inventory)->update(['missing' => false]);

        // remove notecards that were missing before this sync and are not used
        $used = Notice::pluck('notice_notecard_id')->merge(NotecardMail::pluck('notice_notecard_id'))->unique();
        $purge = NoticeNotecard::whereKeyNot(1)->where('missing', true)->whereNotIn('id', $used)->pluck('id');
        if ($purge->isNotEmpty()) {
            Package::whereIn('welcome_notecard_id', $purge)->update(['welcome_notecard_id' => 1]);
            Package::whereIn('setup_notecard_id', $purge)->update(['setup_notecard_id' => 1]);
            NoticeNotecard::whereKey($purge)->delete();
        }

        // missing now
        NoticeNotecard::whereKeyNot(1)->whereNotIn('name', $inventory)->update(['missing' => true]);

        // new
        $known = NoticeNotecard::whereKeyNot(1)->pluck('name')->all();
        foreach (array_unique($inventory) as $name) {
            if ($name !== 'none' && ! in_array($name, $known, true)) {
                NoticeNotecard::create(['name' => $name, 'missing' => false]);
                $known[] = $name;
            }
        }

        $this->set('status', true);
        $this->set('message', $purge->isNotEmpty() ? 'ok - purged: '.$purge->count().' notecards' : 'ok');
    }
}
