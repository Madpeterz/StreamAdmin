<?php

namespace App\LegacyApi;

use App\Models\SlConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * One in-world API endpoint (a 2.0.x Secondlifeapi / Hudapi / SystemApi / Ans class).
 *
 * boot() runs the entry point's request checks (2.0.x did this in the constructor); when they
 * pass, the gateway adds the module and area tags and calls process().
 */
abstract class Endpoint
{
    protected bool $loadOk = true;

    private ?SlConfig $slConfig = null;

    /**
     * @param  string  $module  path segment as sent, first letter upper-cased (used in request hashes)
     * @param  string  $area  path segment as sent, first letter upper-cased
     */
    public function __construct(
        protected readonly Input $input,
        protected readonly Output $output,
        protected readonly Request $request,
        protected readonly string $module,
        protected readonly string $area,
    ) {}

    public function boot(): void {}

    public function loadOk(): bool
    {
        return $this->loadOk;
    }

    abstract public function process(): void;

    protected function ok(string $message): void
    {
        $this->output->ok($message);
    }

    protected function failed(string $message): void
    {
        $this->output->failed($message);
    }

    protected function set(string $tag, mixed $value): void
    {
        $this->output->set($tag, $value);
    }

    /**
     * Keep what was done so far even though the request may still end with status false (which
     * rolls back the rest), as 2.0.x did with sqlSave(false).
     */
    protected function commitNow(): void
    {
        DB::commit();
        DB::beginTransaction();
    }

    protected function slConfig(): SlConfig
    {
        return $this->slConfig ??= SlConfig::findOrFail(1);
    }

    /** ±120 seconds, as every 2.0.x entry point checked. */
    protected function inTimeWindow(int $unixtime): bool
    {
        return abs($unixtime - time()) <= 120;
    }
}
