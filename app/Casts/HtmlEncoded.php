<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Text the in-world API stores with htmlentities() applied (2.0.x did this to every posted value
 * and request hashes depend on it). The database keeps that form so existing data and lookups by
 * posted value still match; the app reads it decoded, so "Nova's Isle" is not shown as
 * "Nova&#039;s Isle".
 *
 * @implements CastsAttributes<string|null, string|null>
 */
class HtmlEncoded implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value === null ? null : html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML401);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        // decode first so a value that is already in the stored form is not encoded twice
        return $value === null ? null : htmlentities(html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML401));
    }
}
