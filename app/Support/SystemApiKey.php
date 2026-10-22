<?php

namespace App\Support;

/**
 * Short lived keys for sys.php, derived from the HTTP inbound secret (same as 2.0.1.3).
 * The bot gets one of these in FetchNextNotecard instead of the secret itself: a key only works
 * for one sys.php method/action and only for 1 to 2 hours.
 */
class SystemApiKey
{
    public const WINDOW = 3600;

    public static function forWindow(string $secret, string $method, string $action, int $window): string
    {
        return hash_hmac('sha256', 'sys|'.$method.'|'.$action.'|'.$window, $secret);
    }

    public static function current(string $secret, string $method, string $action): string
    {
        return self::forWindow($secret, $method, $action, intdiv(time(), self::WINDOW));
    }

    /**
     * Keys accepted right now: the current window and the previous one.
     *
     * @return list<string>
     */
    public static function accepted(string $secret, string $method, string $action): array
    {
        $window = intdiv(time(), self::WINDOW);

        return [
            self::forWindow($secret, $method, $action, $window),
            self::forWindow($secret, $method, $action, $window - 1),
        ];
    }
}
