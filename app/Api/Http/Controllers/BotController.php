<?php

namespace App\Api\Http\Controllers;

use App\Api\ApiException;
use App\Models\Notecard;
use App\Models\Notice;
use App\Support\Swaps;
use Illuminate\Http\JsonResponse;

/**
 * Calls from the Second Life bot. The bot signs with the bot secret, or with the short lived key
 * for this path that FetchNextNotecard gives it.
 */
class BotController extends ApiController
{
    /**
     * The oldest dynamic notecard to write: a rental's stream details from its package template,
     * or a notice's notecard text.
     */
    public function nextNotecard(): JsonResponse
    {
        $notecard = Notecard::with(['rental.avatar', 'rental.stream.server', 'rental.package.template'])->orderBy('id')->first();
        if ($notecard === null) {
            return $this->ok('nowork', ['notecard' => null]);
        }
        $rental = $notecard->rental;
        if ($rental?->avatar === null || $rental->stream?->server === null || $rental->package === null) {
            throw ApiException::rejected('One or more required objects did not load');
        }
        if (! $notecard->as_notice) {
            $template = $rental->package->template ?? throw ApiException::rejected('Unable to load the template');
            if ((string) $template->notecard_detail === '') {
                throw ApiException::rejected('The selected template ('.$template->name.') has no notecard text');
            }
            $title = 'Details for '.explode(' ', $rental->avatar->avatar_name)[0].' port: '.$rental->stream->port.' ['.date('Y-m-d H:i:s').']';
            $content = Swaps::forRental($template->notecard_detail, $rental);
        } else {
            $notice = Notice::find($notecard->notice_id) ?? throw ApiException::rejected('Unable to load the notice');
            if ((string) $notice->notecard_detail === '') {
                throw ApiException::rejected('The selected notice ('.$notice->name.') has no notecard text');
            }
            $title = 'Reminder for '.$rental->avatar->avatar_name.' port: '.$rental->stream->port;
            $content = Swaps::forRental($notice->notecard_detail, $rental);
        }
        $notecard->delete();

        return $this->ok('ok', ['notecard' => ['avatarKey' => $rental->avatar->avatar_uuid, 'title' => $title, 'content' => $content]]);
    }
}
