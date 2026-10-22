<?php

namespace App\LegacyApi\Endpoints\Secondlifeapi\Detailsserver;

use App\LegacyApi\SlEndpoint;
use App\Models\DetailRequest;
use App\Models\Notecard;
use App\Services\BotService;
use App\Support\Swaps;

/**
 * The owner's details server object turns every queued details request into the package
 * template's IM (and, when the bot writes notecards, a notecard request). All or nothing.
 */
class Next extends SlEndpoint
{
    public function process(): void
    {
        if (! $this->hasAccessOwner()) {
            return;
        }
        $requests = DetailRequest::with(['rental.avatar', 'rental.stream.server', 'rental.stream.package.template'])->orderBy('id')->get();
        if ($requests->isEmpty()) {
            $this->ok('nowork');

            return;
        }
        $bot = app(BotService::class);
        $botConfig = $bot->config();
        foreach ($requests as $request) {
            $rental = $request->rental;
            $template = $rental?->stream?->package?->template;
            if ($rental?->avatar === null || $rental->stream?->server === null || $template === null) {
                $this->failed('Failed to unpack detail requests');

                return;
            }
            $bot->sendMessage($rental->avatar, Swaps::forRental($template->detail, $rental));
            if ($botConfig?->notecards) {
                Notecard::create(['rental_id' => $rental->id, 'as_notice' => false]);
                $bot->fetchNextNotecard();
            }
        }
        DetailRequest::whereKey($requests->modelKeys())->delete();
        $this->ok('ok');
    }
}
