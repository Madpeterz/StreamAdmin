<?php

namespace App\Services;

use App\LegacyApi\Gateway;
use App\Models\SlConfig;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * The scheduled tasks that replace the 2.0.x cron container (routes/console.php).
 *
 * The in-world queue tasks run exactly as 2.0.x CronTab.php ran them: as a signed api.php call from
 * a virtual service object owned by the system owner, so the objects table (and the Health page)
 * sees the service as online and the endpoint behaves the same as for a real in-world server.
 */
class Scheduler
{
    /** A queue task that has not run for this long (seconds) is shown as failed. */
    public const QUEUE_MAX_AGE = 480;

    /** The daily export: a day plus two hours of slack. */
    public const EXPORT_MAX_AGE = 93600;

    /**
     * key => label, pause switch (sl_configs column), cadence shown in the UI, and for the queue
     * tasks the api.php endpoint plus the 2.0.x virtual object (name, mode, number in its UUID).
     */
    public const TASKS = [
        'botcommandq' => [
            'label' => 'Bot command queue', 'column' => 'scheduler_botcommandq', 'every' => 'Every 5 seconds',
            'path' => 'Botcommandq/Next', 'object' => ['Botcommandq', 'botcommandqserver', 1],
        ],
        'details-server' => [
            'label' => 'Stream details', 'column' => 'scheduler_details_server', 'every' => 'Every 10 seconds',
            'path' => 'Detailsserver/Next', 'object' => ['Detailsserver', 'detailsserver', 2],
        ],
        'dynamic-notecards' => [
            'label' => 'Bot notecards', 'column' => 'scheduler_dynamic_notecards', 'every' => 'Every 10 seconds',
            'path' => 'Bot/Notecardsync', 'object' => ['Dynamicnotecards', 'notecardsserver', 3],
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
        [$module, $area] = explode('/', $spec['path']);
        [$name, $mode, $number] = $spec['object'];
        // sent the way an in-world object sends them: the gateway htmlentities() every value
        // (names are stored that way), and the hash is over the values as the gateway sees them
        $fields = [
            'version' => (string) $config->db_version,
            'mode' => $mode,
            'objectuuid' => $number.'0000000-0000-0000-0000-000000000000',
            'regionname' => 'cron',
            'ownerkey' => $config->ownerAvatar->avatar_uuid,
            'ownername' => html_entity_decode($config->ownerAvatar->avatar_name, ENT_QUOTES | ENT_HTML401),
            'pos' => '0,0,0',
            'objectname' => $name,
            'objecttype' => $mode,
        ];
        $unixtime = time();
        $signed = implode('', array_map(fn (string $value) => htmlentities($value), $fields));
        $fields['unixtime'] = $unixtime;
        $fields['hash'] = sha1($unixtime.$module.$area.$signed.$config->sl_link_code);

        $request = Request::create('/api.php/'.$spec['path'], 'POST', $fields, server: ['HTTP_X_SECONDLIFE_SHARD' => 'Production']);
        $reply = json_decode((string) app(Gateway::class)->api($request, $spec['path'])->getContent(), true);

        return $this->record($task, ($reply['status'] ?? '0') === '1', (string) ($reply['message'] ?? 'No reply'));
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
        $maxAge = isset(self::TASKS[$task]['path']) ? self::QUEUE_MAX_AGE : self::EXPORT_MAX_AGE;
        $stale = now()->getTimestamp() - (int) $run['at'] > $maxAge;

        return [
            'at' => CarbonImmutable::createFromTimestamp($run['at']),
            'status' => ! $stale && (bool) $run['status'],
            'message' => $stale ? 'No run since '.CarbonImmutable::createFromTimestamp($run['at'])->diffForHumans().' - last result: '.$run['message'] : (string) $run['message'],
            'stale' => $stale,
        ];
    }
}
