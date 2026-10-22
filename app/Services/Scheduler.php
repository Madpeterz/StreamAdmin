<?php

namespace App\Services;

use App\Api\ApiException;
use App\Models\Region;
use App\Models\SlConfig;
use App\Models\SlObject;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The scheduled tasks that replace the 2.0.x cron container (routes/console.php).
 *
 * The queue tasks do what the owner's in-world server objects do through the API, by calling the
 * same services directly. Each run is recorded as a virtual service object owned by the system
 * owner (the 2.0.x CronTab did the same), so the objects table and the Health page see the
 * service as online.
 */
class Scheduler
{
    /** A queue task that has not run for this long (seconds) is shown as failed. */
    public const QUEUE_MAX_AGE = 480;

    /** The daily export: a day plus two hours of slack. */
    public const EXPORT_MAX_AGE = 93600;

    /**
     * key => label, pause switch (sl_configs column), cadence shown in the UI, and for the queue
     * tasks the service call to make plus the virtual object (name, mode, number in its UUID).
     */
    public const TASKS = [
        'botcommandq' => [
            'label' => 'Bot command queue', 'column' => 'scheduler_botcommandq', 'every' => 'Every 5 seconds',
            'run' => [QueueService::class, 'nextBotCommand'], 'object' => ['Botcommandq', 'botcommandqserver', 1],
        ],
        'details-server' => [
            'label' => 'Stream details', 'column' => 'scheduler_details_server', 'every' => 'Every 10 seconds',
            'run' => [QueueService::class, 'processDetails'], 'object' => ['Detailsserver', 'detailsserver', 2],
        ],
        'dynamic-notecards' => [
            'label' => 'Bot notecards', 'column' => 'scheduler_dynamic_notecards', 'every' => 'Every 10 seconds',
            'run' => [NoticeService::class, 'syncBotNotecards'], 'object' => ['Dynamicnotecards', 'notecardsserver', 3],
        ],
        'export' => [
            'label' => 'Spreadsheet export', 'column' => 'scheduler_export', 'every' => 'Daily at 03:00',
        ],
    ];

    /** The master switch is on and the task is not paused. */
    public function enabled(string $task): bool
    {
        return config('streamadmin.scheduler.enabled') && (bool) SlConfig::whereKey(1)->value(self::TASKS[$task]['column']);
    }

    /** @return array{status: bool, message: string} */
    public function runQueueTask(string $task): array
    {
        $spec = self::TASKS[$task];
        $config = SlConfig::with('ownerAvatar')->findOrFail(1);
        if ($config->ownerAvatar === null) {
            return $this->record($task, false, 'Unable to load the system owner avatar');
        }
        [$service, $method] = $spec['run'];
        try {
            // a run that fails leaves nothing behind, as a failed API call does
            $reply = DB::transaction(function () use ($config, $spec, $service, $method) {
                $this->checkIn($config, ...$spec['object']);

                return app($service)->{$method}();
            });
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
        $maxAge = isset(self::TASKS[$task]['object']) ? self::QUEUE_MAX_AGE : self::EXPORT_MAX_AGE;
        $stale = now()->getTimestamp() - (int) $run['at'] > $maxAge;

        return [
            'at' => CarbonImmutable::createFromTimestamp($run['at']),
            'status' => ! $stale && (bool) $run['status'],
            'message' => $stale ? 'No run since '.CarbonImmutable::createFromTimestamp($run['at'])->diffForHumans().' - last result: '.$run['message'] : (string) $run['message'],
            'stale' => $stale,
        ];
    }

    /** Record the virtual service object as seen now. */
    private function checkIn(SlConfig $config, string $name, string $mode, int $number): void
    {
        SlObject::updateOrCreate(
            ['object_uuid' => $number.'0000000-0000-0000-0000-000000000000'],
            [
                'avatar_id' => $config->owner_avatar_id,
                'region_id' => Region::firstOrCreate(['name' => 'cron'])->id,
                'object_name' => $name,
                'object_mode' => $mode,
                'object_xyz' => '0,0,0',
                'last_seen' => time(),
            ],
        );
    }
}
