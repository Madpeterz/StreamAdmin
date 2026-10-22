<?php

namespace App\Support;

/**
 * Short lived keys for the bot, derived from the bot secret (System settings: HTTP inbound secret).
 * The bot gets one of these in FetchNextNotecard instead of the secret itself: a key only works
 * for one API path and only for 1 to 2 hours, and is used like the secret to sign the request.
 */
class SystemApiKey
{
    public const WINDOW = 3600;

    public static function forWindow(string $secret, string $path, int $window): string
    {
        return hash_hmac('sha256', 'bot|'.$path.'|'.$window, $secret);
    }

    public static function current(string $secret, string $path): string
    {
        return self::forWindow($secret, $path, intdiv(time(), self::WINDOW));
    }

    /**
     * Keys accepted right now: the current window and the previous one.
     *
     * @return list<string>
     */
    public static function accepted(string $secret, string $path): array
    {
        $window = intdiv(time(), self::WINDOW);

        return [
            self::forWindow($secret, $path, $window),
            self::forWindow($secret, $path, $window - 1),
        ];
    }
}
