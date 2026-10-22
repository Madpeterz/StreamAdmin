<?php

namespace App\Models;

use App\Casts\UnixTimestamp;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A client's rental of a stream.
 */
class Rental extends Model
{
    protected $table = 'rentals';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'start_unixtime' => UnixTimestamp::class,
            'expire_unixtime' => UnixTimestamp::class,
        ];
    }

    /** Hours left at which a rental counts as expiring soon (matches the 24h notice level). */
    public const EXPIRING_HOURS = 24;

    /** @param Builder<Rental> $query */
    public function scopeExpired(Builder $query): void
    {
        $query->where('expire_unixtime', '<=', time());
    }

    /** @param Builder<Rental> $query */
    public function scopeExpiringSoon(Builder $query): void
    {
        $query->whereBetween('expire_unixtime', [time() + 1, time() + self::EXPIRING_HOURS * 3600]);
    }

    /** @param Builder<Rental> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('expire_unixtime', '>', time() + self::EXPIRING_HOURS * 3600);
    }

    /** @return 'expired'|'expiring'|'active' */
    public function status(): string
    {
        $left = $this->expire_unixtime->getTimestamp() - time();

        return match (true) {
            $left <= 0 => 'expired',
            $left <= self::EXPIRING_HOURS * 3600 => 'expiring',
            default => 'active',
        };
    }

    /** @return BelongsTo<Avatar, $this> */
    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Avatar::class, 'avatar_id');
    }

    /** @return BelongsTo<Stream, $this> */
    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class, 'stream_id');
    }

    /** @return BelongsTo<Package, $this> */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    /** @return BelongsTo<Notice, $this> */
    public function notice(): BelongsTo
    {
        return $this->belongsTo(Notice::class, 'notice_id');
    }

    /** @return HasMany<RentalNoticeOptOut, $this> */
    public function noticeOptOuts(): HasMany
    {
        return $this->hasMany(RentalNoticeOptOut::class, 'rental_id');
    }
}
