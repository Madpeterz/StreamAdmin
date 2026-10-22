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
 *
 * Commands are signed with the bot secret: HMAC-SHA256 (hex) over "command, args, unixtime" joined
 * by newlines. They reach the bot as an IM, "{Command}|||{args}|||{unixtime}@@@{signature}", or as
 * a form post in HTTP mode.
 */
class BotService
{
    /** the path the bot fetches its next dynamic notecard from */
    public const NOTECARD_PATH = '/api/v3/bot/notecards/next';

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
     * Ask the bot to fetch the next dynamic notecard from the API (once; a queued request is
     * enough). It gets the notecard URL and a short lived key for that path, never the bot secret.
     */
    public function fetchNextNotecard(): void
    {
        if (BotCommand::where('command', 'FetchNextNotecard')->exists()) {
            return;
        }
        $secret = (string) SlConfig::find(1)?->http_inbound_secret;
        $this->queue('FetchNextNotecard', [rtrim((string) config('app.url'), '/').self::NOTECARD_PATH, SystemApiKey::current($secret, self::NOTECARD_PATH)]);
    }

    public function signature(string $command, string $args, int $unixtime): string
    {
        return hash_hmac('sha256', $command."\n".$args."\n".$unixtime, (string) $this->config()?->secret);
    }

    /**
     * A command as the bot reads it from an IM.
     *
     * @param  list<string>  $args
     */
    public function commandLine(string $command, array $args, ?int $unixtime = null): string
    {
        $args = implode('~#~', array_map('urldecode', array_map('strval', $args)));
        $unixtime ??= time();

        return $command.'|||'.$args.'|||'.$unixtime.'@@@'.$this->signature($command, $args, $unixtime);
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
     * Post a command to a bot running in HTTP mode and read its answer.
     *
     * @param  list<string>  $args
     * @return array{ok: bool, message: string}
     */
    public function runOverHttp(string $commandName, array $args = []): array
    {
        $config = $this->config();
        if (! $config?->http_mode || ! $config->http_url) {
            return ['ok' => false, 'message' => 'HTTP mode is not enabled.'];
        }
        $joined = implode('~#~', $args);
        $unixtime = time();
        $command = ['commandName' => $commandName, 'unixtime' => $unixtime, 'args' => $joined, 'signing' => $this->signature($commandName, $joined, $unixtime)];
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

        return ['ok' => true, 'message' => (string) $inner['reply']];
    }

    /**
     * Ask a bot running in HTTP mode to answer "Hello" with "world".
     *
     * @return array{ok: bool, message: string}
     */
    public function testHttp(): array
    {
        $result = $this->runOverHttp('Hello');
        if (! $result['ok']) {
            return $result;
        }
        if ($result['message'] !== 'world') {
            return ['ok' => false, 'message' => 'Expected the bot to reply "world" but it replied "'.$result['message'].'".'];
        }

        return ['ok' => true, 'message' => 'Bot HTTP test passed.'];
    }
}
