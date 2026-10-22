<?php

namespace App\Services;

use App\Models\BotConfig;
use App\Models\Region;
use App\Models\Reseller;
use App\Models\SlConfig;
use App\Models\SlObject;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Which in-world objects are checking in (port of the 2.0.x Health pages and dashboard checks).
 * An object counts as online when it was seen in the last 2 minutes.
 */
class ObjectHealth
{
    public const ONLINE_SECONDS = 120;

    /** object_mode of the owner's service objects; everything else is a vendor or similar */
    public const SERVER_MODES = ['mailserver', 'noticeserver', 'detailsserver', 'botcommandqserver', 'notecardsserver', 'eventsserver'];

    /**
     * The owner's service objects that should be running with the current settings, and when each
     * was last seen.
     *
     * @return Collection<int, array{mode: string, name: string, last_seen: ?CarbonImmutable, region: ?string}>
     */
    public function serverObjects(): Collection
    {
        $config = SlConfig::find(1);
        $modes = ['mailserver', 'noticeserver', 'detailsserver'];
        $bot = BotConfig::find(1);
        if ($bot && ($bot->ims || $bot->invites || $bot->notecards)) {
            $modes[] = 'botcommandqserver';
            if ($bot->notecards) {
                $modes[] = 'notecardsserver';
            }
        }
        if ($config?->events_api) {
            $modes[] = 'eventsserver';
        }
        $seen = SlObject::with('region')
            ->where('avatar_id', $config?->owner_avatar_id)
            ->whereIn('object_mode', $modes)
            ->orderByDesc('last_seen')
            ->get()
            ->unique('object_mode')
            ->keyBy('object_mode');

        return collect($modes)->map(fn (string $mode) => [
            'mode' => $mode,
            'name' => self::serverName($mode),
            'last_seen' => $seen[$mode]->last_seen ?? null,
            'region' => $seen[$mode]->region->name ?? null,
        ]);
    }

    public static function serverName(string $mode): string
    {
        return match ($mode) {
            'botcommandqserver' => 'Bot command server',
            default => ucfirst(str_replace('server', '', $mode)).' server',
        };
    }

    public static function isOnline(?CarbonImmutable $lastSeen): bool
    {
        return $lastSeen !== null && $lastSeen->getTimestamp() >= time() - self::ONLINE_SECONDS;
    }

    /** @return Builder<SlObject> resellers' vendors and other non-server objects */
    public function vendors(): Builder
    {
        return SlObject::query()
            ->whereIn('avatar_id', Reseller::query()->select('avatar_id'))
            ->whereNotIn('object_mode', self::SERVER_MODES);
    }

    /** Percentage of vendors online (null when there are none). */
    public function vendorPercent(): ?float
    {
        $total = $this->vendors()->count();
        if ($total === 0) {
            return null;
        }

        return round($this->vendors()->where('last_seen', '>=', time() - self::ONLINE_SECONDS)->count() / $total * 100, 1);
    }

    /**
     * Vendors per region: how many are up and down, worst first.
     *
     * @return Collection<int, array{region: Region, up: int, down: int, percent: float}>
     */
    public function regions(): Collection
    {
        $rows = $this->vendors()->toBase()
            ->selectRaw('region_id, SUM(last_seen >= ?) AS up, SUM(last_seen < ?) AS down', [time() - self::ONLINE_SECONDS, time() - self::ONLINE_SECONDS])
            ->groupBy('region_id')
            ->get();
        $regions = Region::whereIn('id', $rows->pluck('region_id'))->get()->keyBy('id');

        return $rows->filter(fn ($row) => $regions->has($row->region_id))
            ->map(fn ($row) => [
                'region' => $regions[$row->region_id],
                'up' => (int) $row->up,
                'down' => (int) $row->down,
                'percent' => round((int) $row->up / max(1, (int) $row->up + (int) $row->down) * 100, 1),
            ])
            ->sortBy([['percent', 'asc'], [fn ($a, $b) => strcmp($a['region']->name, $b['region']->name)]])
            ->values();
    }

    /** Badge colour for an online percentage, using the 2.0.x thresholds. */
    public static function percentColor(?float $percent): string
    {
        return match (true) {
            $percent === null => 'zinc',
            $percent >= 75 => 'emerald',
            $percent >= 50 => 'amber',
            default => 'red',
        };
    }

    /** SL map link to the object's spot (object_xyz is LSL's vector text, "<x, y, z>"). */
    public static function mapUrl(SlObject $object): ?string
    {
        if ($object->region === null) {
            return null;
        }
        $coords = array_map(fn ($part) => (int) round((float) trim($part)), explode(',', trim((string) $object->object_xyz, '<> ')));

        return 'https://maps.secondlife.com/secondlife/'.rawurlencode($object->region->name).'/'.implode('/', $coords);
    }
}
