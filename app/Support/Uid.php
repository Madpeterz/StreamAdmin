<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Short random public ids (rental_uid, stream_uid, package_uid, ...), unique within their column.
 */
class Uid
{
    /** @param class-string<Model> $model */
    public static function unique(string $model, string $column, int $length = 8): string
    {
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $uid = Str::random($length);
            if (! $model::where($column, $uid)->exists()) {
                return $uid;
            }
        }

        throw new RuntimeException('Unable to find a free '.$column);
    }
}
