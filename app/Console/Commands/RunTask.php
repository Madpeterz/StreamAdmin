<?php

namespace App\Console\Commands;

use App\Services\Scheduler;
use Illuminate\Console\Command;

/**
 * One run of an in-world queue task (scheduled in routes/console.php; replaces 2.0.x CronTab.php).
 * Runs even while the task is paused, so it can be tried by hand.
 */
class RunTask extends Command
{
    protected $signature = 'streamadmin:task {task : botcommandq, details-server or dynamic-notecards}';

    protected $description = 'Run one in-world queue task as the system owner\'s service object';

    public function handle(Scheduler $scheduler): int
    {
        $task = (string) $this->argument('task');
        if (! isset(Scheduler::TASKS[$task]['path'])) {
            $this->error('Unknown task. Use one of: '.implode(', ', array_keys(array_filter(Scheduler::TASKS, fn (array $spec) => isset($spec['path'])))));

            return self::INVALID;
        }
        $result = $scheduler->runQueueTask($task);
        $this->line($task.': '.$result['message']);

        return $result['status'] ? self::SUCCESS : self::FAILURE;
    }
}
