<?php

namespace App\LegacyApi\Endpoints\Secondlifeapi\Botcommandq;

use App\LegacyApi\SlEndpoint;
use App\Models\Avatar;
use App\Models\BotCommand;
use App\Models\BotConfig;
use App\Models\Message;
use App\Services\BotService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Hands the oldest bot command to the bot: posted to it in HTTP mode, otherwise IMed to the bot
 * avatar through the mail server (one at a time). Also run by the scheduler.
 */
class Next extends SlEndpoint
{
    public function process(): void
    {
        if (! $this->hasAccessOwner()) {
            return;
        }
        $config = BotConfig::find(1);
        if ($config === null) {
            $this->failed('Unable to load bot config');

            return;
        }
        $botAvatar = Avatar::find($config->avatar_id);
        if ($botAvatar === null) {
            $this->failed('Unable to load bot avatar config');

            return;
        }
        $command = BotCommand::orderBy('id')->first();
        if ($command === null) {
            $this->ok('nowork');

            return;
        }
        $command->delete();
        $args = $command->args !== null ? json_decode($command->args, true) : [];
        $args = is_array($args) ? array_values($args) : [];

        if ($config->http_mode) {
            $this->sendOverHttp($config, $command->command, $args);

            return;
        }
        if (Message::where('avatar_id', $botAvatar->id)->exists()) {
            $this->failed('To many pending mail commands to the bot');

            return;
        }
        Message::create(['avatar_id' => $botAvatar->id, 'message' => app(BotService::class)->commandLine($command->command, $args)]);
        $this->ok('passed command to mail server');
    }

    /** @param list<string> $args */
    private function sendOverHttp(BotConfig $config, string $commandName, array $args): void
    {
        $command = ['commandName' => $commandName, 'unixtime' => time(), 'signing' => '', 'args' => implode('~#~', $args)];
        $command['signing'] = sha1($command['commandName'].$command['args'].$command['unixtime'].$config->secret);
        try {
            $response = Http::asForm()->timeout(7)->baseUrl((string) $config->http_url)->post('api/Run', $command);
        } catch (ConnectionException) {
            $this->failed('[api/Run] Request failed in a fireball');

            return;
        }
        $raw = $response->body();
        if ($response->status() !== 200) {
            $this->failed('http error [api/Run] :'.$response->status().' : '.$raw);

            return;
        }
        $outer = json_decode($raw, true);
        if (! is_array($outer) || ! array_key_exists('Key', $outer)) {
            $this->failed('Reply is not formated as expected Missing Key in repl: '.$raw);

            return;
        }
        if (! array_key_exists('Value', $outer)) {
            $this->failed('Reply is not formated as expected Missing Value in reply: '.$raw);

            return;
        }
        if ($outer['Key'] != true) {
            $this->failed('Reply is not formated as expected Key is not true: '.$raw);

            return;
        }
        $inner = json_decode((string) $outer['Value'], true);
        if (! is_array($inner) || ! array_key_exists('status', $inner)) {
            $this->failed('Reply is not formated as expected missing status code in Value: '.$raw);

            return;
        }
        if (! array_key_exists('reply', $inner)) {
            $this->failed('Reply is not formated as expected missing reply code in Value: '.$raw);

            return;
        }
        if ($inner['status'] == false) {
            $this->failed((string) $inner['reply']);

            return;
        }
        $this->ok((string) $inner['reply']);
    }
}
