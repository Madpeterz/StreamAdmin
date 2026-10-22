<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * System settings (single row).
 */
class SlConfig extends Model
{
    protected $table = 'sl_configs';

    public $timestamps = false;

    protected $guarded = ['id'];

    /** Cache key of the display timezone code (read on every web request, see UseDisplayTimezone). */
    public const TIMEZONE_CACHE_KEY = 'display-timezone';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::TIMEZONE_CACHE_KEY));
    }

    protected function casts(): array
    {
        return [
            'new_resellers' => 'boolean',
            'hud_allow_discord' => 'boolean',
            'hud_allow_group' => 'boolean',
            'hud_allow_details' => 'boolean',
            'hud_allow_renewal' => 'boolean',
            'events_api' => 'boolean',
            'clients_display_server' => 'boolean',
            'limit_streams' => 'boolean',
            'limit_time' => 'boolean',
            'enable_coupons' => 'boolean',
            'scheduler_botcommandq' => 'boolean',
            'scheduler_details_server' => 'boolean',
            'scheduler_dynamic_notecards' => 'boolean',
            'scheduler_export' => 'boolean',
        ];
    }

    /** @return BelongsTo<Avatar, $this> */
    public function ownerAvatar(): BelongsTo
    {
        return $this->belongsTo(Avatar::class, 'owner_avatar_id');
    }

    /** @return BelongsTo<Timezone, $this> */
    public function displayTimezone(): BelongsTo
    {
        return $this->belongsTo(Timezone::class, 'display_timezone_id');
    }
}
