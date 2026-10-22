<?php

namespace App\Services;

use App\Api\ApiException;
use App\Models\Avatar;
use App\Models\BotCommand;
use App\Models\BotConfig;
use App\Models\DetailRequest;
use App\Models\Message;
use App\Models\Notecard;
use App\Models\QueuedEvent;
use App\Support\Swaps;

/**
 * The work queues the owner's server objects and the scheduler take from (port of the 2.0.x
 * Mailserver, Eventq, Detailsserver and Botcommandq endpoints). Each call either does its work and
 * returns the reply, or throws an ApiException so the work is rolled back and tried again.
 */
class QueueService
{
    public function __construct(private readonly BotService $bot) {}

    /**
     * The oldest queued IM for the mail server object.
     *
     * @return array{message: string, data: array<string, mixed>}
     */
    public function nextMail(): array
    {
        $message = Message::with('avatar')->orderBy('id')->first();
        if ($message === null) {
            return ['message' => 'nowork', 'data' => ['message' => null]];
        }
        if ($message->avatar === null) {
            throw ApiException::rejected('Unable to find the avatar attached to the message');
        }
        $message->delete();

        return ['message' => 'ok', 'data' => ['message' => ['avatarKey' => $message->avatar->avatar_uuid, 'text' => $message->message]]];
    }

    /**
     * The oldest queued rental event for the events server object (the message is JSON text).
     *
     * @return array{message: string, data: array<string, mixed>}
     */
    public function nextEvent(): array
    {
        $event = QueuedEvent::orderBy('id')->first();
        if ($event === null) {
            return ['message' => 'nowork', 'data' => ['event' => null]];
        }
        $event->delete();

        return ['message' => 'ok', 'data' => ['event' => ['name' => $event->event_name, 'message' => $event->event_message]]];
    }

    /**
     * Turn every queued details request into the package template's IM (and, when the bot writes
     * notecards, a notecard request). All or nothing.
     *
     * @return array{message: string, data: array<string, mixed>}
     */
    public function processDetails(): array
    {
        $requests = DetailRequest::with(['rental.avatar', 'rental.stream.server', 'rental.stream.package.template'])->orderBy('id')->get();
        if ($requests->isEmpty()) {
            return ['message' => 'nowork', 'data' => ['processed' => 0]];
        }
        $notecards = $this->bot->config()?->notecards;
        foreach ($requests as $request) {
            $rental = $request->rental;
            $template = $rental?->stream?->package?->template;
            if ($rental?->avatar === null || $rental->stream?->server === null || $template === null) {
                throw ApiException::rejected('Failed to unpack the details requests');
            }
            $this->bot->sendMessage($rental->avatar, Swaps::forRental($template->detail, $rental));
            if ($notecards) {
                Notecard::create(['rental_id' => $rental->id, 'as_notice' => false]);
                $this->bot->fetchNextNotecard();
            }
        }
        DetailRequest::whereKey($requests->modelKeys())->delete();

        return ['message' => 'ok', 'data' => ['processed' => $requests->count()]];
    }

    /**
     * Hand the oldest bot command to the bot: posted to it in HTTP mode, otherwise IMed to the bot
     * avatar through the mail server (one at a time).
     *
     * @return array{message: string, data: array<string, mixed>}
     */
    public function nextBotCommand(): array
    {
        $config = BotConfig::find(1) ?? throw ApiException::rejected('Unable to load the bot config');
        $botAvatar = Avatar::find($config->avatar_id) ?? throw ApiException::rejected('Unable to load the bot avatar');
        $command = BotCommand::orderBy('id')->first();
        if ($command === null) {
            return ['message' => 'nowork', 'data' => ['sent' => false]];
        }
        $command->delete();
        $args = $command->args !== null ? json_decode($command->args, true) : [];
        $args = is_array($args) ? array_values($args) : [];

        if ($config->http_mode) {
            $result = $this->bot->runOverHttp($command->command, $args);
            if (! $result['ok']) {
                throw ApiException::rejected($result['message']);
            }

            return ['message' => $result['message'], 'data' => ['sent' => true, 'via' => 'http']];
        }
        if (Message::where('avatar_id', $botAvatar->id)->exists()) {
            throw ApiException::rejected('Too many pending mail commands to the bot');
        }
        Message::create(['avatar_id' => $botAvatar->id, 'message' => $this->bot->commandLine($command->command, $args)]);

        return ['message' => 'passed command to mail server', 'data' => ['sent' => true, 'via' => 'mail']];
    }
}
