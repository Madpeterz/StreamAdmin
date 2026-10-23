<?php

namespace App\Services;

use App\Api\ApiException;
use App\Models\SlConfig;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The scheduled tasks that replace the 2.0.x cron container (routes/console.php).
 *
 * The queue tasks do what the owner's in-world server objects do through the API, by calling the
 * same services directly. Each run is recorded in the cache, and the Health page counts a passing
 * queue task as its service being online.
 */
class Scheduler
{
    /** A queue task that has not run for this long (seconds) is shown as failed. */
    public const QUEUE_MAX_AGE = 480;

    /** The daily export: a day plus two hours of slack. */
    public const EXPORT_MAX_AGE = 93600;

    /**
     * key => label, pause switch (sl_configs column), cadence shown in the UI, and for the queue
     * tasks the service call to make plus the object_mode of the in-world server it stands in for.
     */
    public const TASKS = [
        'botcommandq' => [
            'label' => 'Bot command queue', 'column' => 'scheduler_botcommandq', 'every' => 'Every 5 seconds',
            'run' => [QueueService::class, 'nextBotCommand'], 'mode' => 'botcommandqserver',
        ],
        'details-server' => [
            'label' => 'Stream details', 'column' => 'scheduler_details_server', 'every' => 'Every 10 seconds',
            'run' => [QueueService::class, 'processDetails'], 'mode' => 'detailsserver',
        ],
        'dynamic-notecards' => [
            'label' => 'Bot notecards', 'column' => 'scheduler_dynamic_notecards', 'every' => 'Every 10 seconds',
            'run' => [NoticeService::class, 'syncBotNotecards'], 'mode' => 'notecardsserver',
        ],
        'export' => [
            'label' => 'Spreadsheet export', 'column' => 'scheduler_export', 'every' => 'Daily at 03:00',
        ],
    ];

    /**
     * When the queue task standing in for this in-world server last ran with no error, if it is on.
     */
    public function lastPassed(string $mode): ?CarbonImmutable
    {
        foreach (self::TASKS as $key => $spec) {
            if (($spec['mode'] ?? null) === $mode && $this->enabled($key)) {
                $run = $this->lastRun($key);

                return $run !== null && $run['status'] ? $run['at'] : null;
            }
        }

        return null;
    }

    /** The master switch is on and the task is not paused. */
    public function enabled(string $task): bool
    {
        return config('streamadmin.scheduler.enabled') && (bool) SlConfig::whereKey(1)->value(self::TASKS[$task]['column']);
    }

    /** @return array{status: bool, message: string} */
    public function runQueueTask(string $task): array
    {
        $spec = self::TASKS[$task];
        [$service, $method] = $spec['run'];
        try {
            // a run that fails leaves nothing behind, as a failed API call does
            $reply = DB::transaction(fn () => app($service)->{$method}());
        } catch (ApiException $e) {
            return $this->record($task, false, $e->getMessage());
        }

        return $this->record($task, true, $reply['message']);
    }

    /** @return array{status: bool, message: string} */
    public function record(string $task, bool $status, string $message): array
    {
        Cache::forever('scheduler:last-run:'.$task, ['at' => now()->getTimestamp(), 'status' => $status, 'message' => $message]);

        return ['status' => $status, 'message' => $message];
    }

    /**
     * The last run, with `stale` set when it is older than the task should ever go (the scheduler
     * is down or stuck): such a run is reported as failed whatever it said at the time.
     *
     * @return array{at: CarbonImmutable, status: bool, message: string, stale: bool}|null
     */
    public function lastRun(string $task): ?array
    {
        $run = Cache::get('scheduler:last-run:'.$task);
        if (! is_array($run)) {
            return null;
        }
        $maxAge = isset(self::TASKS[$task]['mode']) ? self::QUEUE_MAX_AGE : self::EXPORT_MAX_AGE;
        $stale = now()->getTimestamp() - (int) $run['at'] > $maxAge;

        return [
            'at' => CarbonImmutable::createFromTimestamp($run['at']),
            'status' => ! $stale && (bool) $run['status'],
            'message' => $stale ? 'No run since '.CarbonImmutable::createFromTimestamp($run['at'])->diffForHumans().' - last result: '.$run['message'] : (string) $run['message'],
            'stale' => $stale,
        ];
    }
}
