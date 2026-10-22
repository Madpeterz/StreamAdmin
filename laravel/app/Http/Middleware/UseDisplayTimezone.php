<?php

namespace App\Http\Middleware;

use App\Models\SlConfig;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Show times in the timezone chosen in System settings, as 2.0.x did. Times are stored as unix
 * timestamps, so this only changes how they are displayed (and the swap tag dates).
 */
class UseDisplayTimezone
{
    public function handle(Request $request, Closure $next): Response
    {
        // every page and every Livewire poll runs this; SlConfig drops the cached value when it is saved
        $zone = Cache::remember(SlConfig::TIMEZONE_CACHE_KEY, 3600, fn () => SlConfig::query()
            ->join('timezones', 'timezones.id', '=', 'sl_configs.display_timezone_id')
            ->where('sl_configs.id', 1)->value('timezones.code'));
        if (is_string($zone) && in_array($zone, timezone_identifiers_list(), true)) {
            date_default_timezone_set($zone);
            config(['app.timezone' => $zone]);
        }

        return $next($request);
    }
}
