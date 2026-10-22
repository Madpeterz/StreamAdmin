<?php

namespace App\Models;

use App\Casts\UnixTimestamp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Payment for a rental, renewal or credit top-up.
 */
class Transaction extends Model
{
    protected $table = 'transactions';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'renew' => 'boolean',
            'via_hud' => 'boolean',
            'via_marketplace' => 'boolean',
            'from_credits' => 'boolean',
            'unixtime' => UnixTimestamp::class,
        ];
    }

    public const KINDS = [
        'new' => ['New rental', 'emerald'],
        'renewal' => ['Renewal', 'sky'],
        'hud' => ['HUD renewal', 'indigo'],
        'proxy' => ['Proxy renewal', 'cyan'],
        'credits' => ['Credits', 'amber'],
        'marketplace' => ['Marketplace', 'violet'],
    ];

    /** @return key-of<self::KINDS> */
    public function kind(): string
    {
        return match (true) {
            $this->via_marketplace => 'marketplace',
            $this->from_credits => 'credits',
            $this->renew && $this->via_hud => 'hud',
            $this->renew && $this->target_avatar_id !== null && $this->target_avatar_id !== $this->avatar_id => 'proxy',
            $this->renew => 'renewal',
            default => 'new',
        };
    }

    /** @return BelongsTo<Avatar, $this> */
    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Avatar::class, 'avatar_id');
    }

    /** @return BelongsTo<Package, $this> */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    /** @return BelongsTo<Stream, $this> */
    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class, 'stream_id');
    }

    /** @return BelongsTo<Reseller, $this> */
    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class, 'reseller_id');
    }

    /** @return BelongsTo<Region, $this> */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    /** @return BelongsTo<Avatar, $this> */
    public function targetAvatar(): BelongsTo
    {
        return $this->belongsTo(Avatar::class, 'target_avatar_id');
    }
}
