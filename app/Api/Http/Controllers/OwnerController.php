<?php

namespace App\Api\Http\Controllers;

use App\Models\SlConfig;
use App\Services\NoticeService;
use App\Services\QueueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** The owner's own server objects: mail, events, details, bot commands, notices and notecards. */
class OwnerController extends ApiController
{
    public function __construct(private readonly QueueService $queues, private readonly NoticeService $notices) {}

    public function nextMail(): JsonResponse
    {
        return $this->reply($this->queues->nextMail());
    }

    public function nextEvent(): JsonResponse
    {
        return $this->reply($this->queues->nextEvent());
    }

    public function processDetails(): JsonResponse
    {
        return $this->reply($this->queues->processDetails());
    }

    public function nextBotCommand(): JsonResponse
    {
        return $this->reply($this->queues->nextBotCommand());
    }

    public function nextNotice(): JsonResponse
    {
        return $this->reply($this->notices->nextNotice());
    }

    public function nextNotecardMail(): JsonResponse
    {
        return $this->reply($this->notices->nextNotecardMail());
    }

    public function syncNotecards(Request $request): JsonResponse
    {
        $data = $this->data($request, ['notecards' => ['present', 'array'], 'notecards.*' => ['string', 'max:255']]);

        return $this->reply($this->notices->syncNotecards(array_map($this->text(...), array_values($data['notecards']))));
    }

    public function syncBotNotecards(): JsonResponse
    {
        return $this->reply($this->notices->syncBotNotecards());
    }

    /** The support key's code part, for the owner's server objects. */
    public function supportCode(): JsonResponse
    {
        return $this->ok('ok', ['code' => explode('*', (string) SlConfig::findOrFail(1)->payment_key)[0]]);
    }

    /** @param array{message: string, data: array<string, mixed>} $result */
    private function reply(array $result): JsonResponse
    {
        return $this->ok($result['message'], $result['data']);
    }
}
