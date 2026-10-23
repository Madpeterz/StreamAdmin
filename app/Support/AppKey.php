<?php

namespace App\Support;

use Illuminate\Encryption\Encrypter;

/**
 * APP_KEY that Laravel will accept. A malformed key (wrong length, missing "base64:", pasted from the
 * 2.0.x settings) would 500 every request, so such a value is used as the seed for a valid key instead.
 * Same seed, same key: it survives restarts, and sessions and encrypted values stay readable.
 */
class AppKey
{
    private const SALT = 'streamadmin.app-key';

    public static function normalize(?string $key, string $cipher = 'AES-256-CBC'): ?string
    {
        // empty stays empty: Laravel then reports the missing key itself
        if ($key === null || trim($key) === '') {
            return $key;
        }

        // surrounding whitespace and quotes (a quoted .env value, a trailing newline) are not part of the key
        $key = trim($key, " \t\n\r\0\x0B\"'");

        if (self::valid($key, $cipher)) {
            return $key;
        }

        return 'base64:'.base64_encode(hash_pbkdf2('sha256', $key, self::SALT, 100_000, 32, true));
    }

    private static function valid(string $key, string $cipher): bool
    {
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7), true);

            if ($key === false) {
                return false;
            }
        }

        return Encrypter::supported($key, $cipher);
    }
}
