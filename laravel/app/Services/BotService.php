<?php

namespace App\Services;

use App\Models\Avatar;
use App\Models\BotCommand;
use App\Models\BotConfig;
use App\Models\Message;
use App\Models\SlConfig;
use App\Support\SystemApiKey;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Queues messages for the mail server object and commands for the SL bot (port of 2.0.x BotHelper).
 */
class BotService
{
    public function config(): ?BotConfig
    {
        return BotConfig::find(1);
    }

    /** The mail server object always gets the message; the bot also IMs it when IMs are on. */
    public function sendMessage(Avatar $avatar, string $message): void
    {
        Message::create(['avatar_id' => $avatar->id, 'message' => $message]);
        if ($this->config()?->ims) {
            $this->queue('IM', [$avatar->avatar_uuid, $message]);
        }
    }

    /** Invite to the bot's group (with the everyone role) when group invites are on. */
    public function inviteToGroup(Avatar $avatar): void
    {
        $config = $this->config();
        if ($config?->invites) {
            $this->queue('GroupInvite', [(string) $config->invite_group_uuid, $avatar->avatar_uuid, 'everyone']);
        }
    }

    /**
     * Ask the bot to fetch the next dynamic notecard from sys.php (once; a queued request is
     * enough). It gets a short lived key for sys.php Notecard/Next, never the inbound secret.
     */
    public function fetchNextNotecard(): void
    {
        if (BotCommand::where('command', 'FetchNextNotecard')->exists()) {
            return;
        }
        $secret = (string) SlConfig::find(1)?->http_inbound_secret;
        $this->queue('FetchNextNotecard', [rtrim((string) config('app.url'), '/').'/', SystemApiKey::current($secret, 'Notecard', 'Next')]);
    }

    /**
     * A command as the bot reads it from an IM: "{Command}|||{arg}~#~{arg}@@@{signature}", signed
     * with the bot secret (2.0.x BotHelper::getBotCommand).
     *
     * @param  list<string>  $args
     */
    public function commandLine(string $command, array $args): string
    {
        $args = implode('~#~', array_map('urldecode', array_map('strval', $args)));

        return $command.'|||'.$args.'@@@'.sha1($command.$args.$this->config()?->secret);
    }

    /** @param list<string> $args */
    public function queue(string $command, array $args = []): BotCommand
    {
        return BotCommand::create([
            'command' => $command,
            'args' => $args === [] ? null : json_encode($args),
            'unixtime' => time(),
        ]);
    }

    /**
     * Ask a bot running in HTTP mode to answer "Hello" with "world".
     *
     * @return array{ok: bool, message: string}
     */
    public function testHttp(): array
    {
        $config = $this->config();
        if (! $config?->http_mode || ! $config->http_url) {
            return ['ok' => false, 'message' => 'HTTP mode is not enabled.'];
        }
        $command = ['commandName' => 'Hello', 'unixtime' => time(), 'args' => ''];
        $command['signing'] = sha1($command['commandName'].$command['args'].$command['unixtime'].$config->secret);
        try {
            $response = Http::asForm()->timeout(7)->baseUrl($config->http_url)->post('api/Run', $command);
        } catch (ConnectionException) {
            return ['ok' => false, 'message' => 'Unable to reach the bot at '.$config->http_url.'.'];
        }
        if (! $response->ok()) {
            return ['ok' => false, 'message' => 'The bot answered with HTTP '.$response->status().'.'];
        }
        $outer = $response->json();
        if (! is_array($outer) || ($outer['Key'] ?? false) !== true || ! isset($outer['Value'])) {
            return ['ok' => false, 'message' => 'The bot reply is not in the expected format.'];
        }
        $inner = json_decode((string) $outer['Value'], true);
        if (! is_array($inner) || ! array_key_exists('status', $inner) || ! array_key_exists('reply', $inner)) {
            return ['ok' => false, 'message' => 'The bot reply is missing its status or reply.'];
        }
        if ($inner['status'] == false) {
            return ['ok' => false, 'message' => 'The bot said: '.$inner['reply']];
        }
        if ($inner['reply'] !== 'world') {
            return ['ok' => false, 'message' => 'Expected the bot to reply "world" but it replied "'.$inner['reply'].'".'];
        }

        return ['ok' => true, 'message' => 'Bot HTTP test passed.'];
    }
}
