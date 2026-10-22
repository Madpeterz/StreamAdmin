<?php

namespace App\Casts;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Integer unix time columns (kept as integers in 3.0.0, the in-world API sends them as numbers).
 * 0 and null read as null.
 *
 * @implements CastsAttributes<CarbonImmutable|null, CarbonImmutable|int|null>
 */
class UnixTimestamp implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?CarbonImmutable
    {
        if ($value === null || (int) $value === 0) {
            return null;
        }

        return CarbonImmutable::createFromTimestamp((int) $value, config('app.timezone'));
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof \DateTimeInterface ? $value->getTimestamp() : (int) $value;
    }
}
