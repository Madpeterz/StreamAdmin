<?php

namespace App\LegacyApi\Endpoints\Secondlifeapi\Bot;

use App\LegacyApi\SlEndpoint;
use App\Models\Avatar;
use App\Models\BotCommand;
use App\Models\Notecard;
use App\Services\BotService;

/**
 * The owner's notecard server object checks whether the bot should fetch a dynamic notecard:
 * "nowork" when nothing is waiting, "pending static notecards" while notecards are queued, and
 * "ok" once a fetch request is queued for the bot.
 */
class Notecardsync extends SlEndpoint
{
    public function process(): void
    {
        if (! $this->ownerOverride) {
            $this->failed('This API is owner only');

            return;
        }
        $bot = app(BotService::class);
        $config = $bot->config();
        if (! $config?->notecards) {
            $this->failed('Notecards not enabled on bot');
            $this->set('hassyncmessage', '2');

            return;
        }
        if (Avatar::find($config->avatar_id)?->avatar_uuid === null) {
            $this->failed('Unable to load bot UUID');

            return;
        }
        $secret = (string) $this->slConfig()->http_inbound_secret;
        if (strlen($secret) < 5 || strlen($secret) > 30 || htmlentities($secret) !== $secret) {
            $this->failed('HTTP inbound secret has issues');

            return;
        }
        if (Notecard::exists()) {
            $this->failed('pending static notecards');

            return;
        }
        $this->ok('nowork');
        if (! BotCommand::where('command', 'FetchNextNotecard')->exists()) {
            return;
        }
        $this->set('haserrormessage', false);
        $bot->fetchNextNotecard();
        $this->ok('ok');
    }
}
