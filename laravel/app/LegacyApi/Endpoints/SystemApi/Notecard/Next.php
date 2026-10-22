<?php

namespace App\LegacyApi\Endpoints\SystemApi\Notecard;

use App\LegacyApi\SysEndpoint;
use App\Models\Notecard;
use App\Models\Notice;
use App\Support\Swaps;

/**
 * The bot (FetchNextNotecard) takes the oldest dynamic notecard to write: a rental's stream
 * details from its package template, or a notice's notecard text.
 */
class Next extends SysEndpoint
{
    public function process(): void
    {
        $notecard = Notecard::with(['rental.avatar', 'rental.stream.server', 'rental.package.template'])->orderBy('id')->first();
        if ($notecard === null) {
            $this->ok('nowork');

            return;
        }
        $rental = $notecard->rental;
        if ($rental?->avatar === null || $rental->stream?->server === null || $rental->package === null) {
            $this->failed('One or more required objects did not load!');

            return;
        }
        if (! $notecard->as_notice) {
            $template = $rental->package->template;
            if ($template === null) {
                $this->failed('Unable to load: Template');

                return;
            }
            $title = 'Details for '.explode(' ', $rental->avatar->avatar_name)[0].' port: '.$rental->stream->port.' ['.date('Y-m-d H:i:s').']';
            if ((string) $template->notecard_detail === '') {
                $this->failed('Selected template: '.$template->name.' is empty');

                return;
            }
            $content = Swaps::forRental($template->notecard_detail, $rental);
        } else {
            $notice = Notice::find($notecard->notice_id);
            if ($notice === null) {
                $this->failed('Unable to load: Notice');

                return;
            }
            $title = 'Reminder for '.$rental->avatar->avatar_name.' port: '.$rental->stream->port;
            if ((string) $notice->notecard_detail === '') {
                $this->failed('Selected notice: '.$notice->name.' is empty');

                return;
            }
            $content = Swaps::forRental($notice->notecard_detail, $rental);
        }
        $notecard->delete();
        $this->ok('ok');
        $this->set('AvatarUUID', $rental->avatar->avatar_uuid);
        $this->set('NotecardTitle', $title);
        $this->set('NotecardContent', $content);
    }
}
