<?php

namespace App\Support;

use App\Models\Rental;
use Carbon\CarbonInterface;

/**
 * [[TAG]] placeholders used by templates, notices and bulk messages (port of 2.0.x SwapablesHelper).
 *
 * Line breaks typed into a message are dropped and [[NL]] adds one, as in 2.0.x: in-world objects
 * and the bot treat the message as a single line.
 */
class Swaps
{
    /** Tag => example value, in the order staff see them. The short names are 1.x aliases. */
    public static function examples(): array
    {
        $expires = now()->addWeek()->addMinutes(47);

        return [
            'AVATAR_FIRSTNAME' => 'Madpeter',
            'AVATAR_LASTNAME' => 'Zond',
            'AVATAR_FULLNAME' => 'Madpeter Zond',
            'RENTAL_EXPIRES_DATETIME' => self::expiresAt($expires),
            'RENTAL_TIMELEFT' => self::timeLeft($expires),
            'RENTAL_UID' => 'XXXXXXXX',
            'STREAM_PORT' => '4000',
            'STREAM_ADMINUSERNAME' => 'SuperAdmin',
            'STREAM_ADMINPASSWORD' => 'AdminPaSSwordHere',
            'STREAM_DJPASSWORD' => 'DJpasswordYo',
            'STREAM_MOUNTPOINT' => '/live',
            'SERVER_DOMAIN' => 'livestreamservice.demo',
            'SERVER_IP' => '192.168.0.1',
            'SERVER_CONTROLPANEL' => 'https://livestreamservice.demo:5000',
            'PACKAGE_NAME' => 'CheapWeeklyPackage',
            'PACKAGE_UID' => 'XXXXXXXX',
            'PACKAGE_LISTENERS' => '10',
            'PACKAGE_BITRATE' => '128',
            'PACKAGE_AUTODJ' => 'Enabled',
            'PACKAGE_AUTODJ_SIZE' => '3',
            'TIMEZONE' => date_default_timezone_get(),
            'NL' => "\n",
        ];
    }

    /** 1.x tag names still accepted in old templates => current tag. */
    public const ALIASES = [
        'FirstName' => 'AVATAR_FIRSTNAME',
        'LastName' => 'AVATAR_LASTNAME',
        'ExpiresAt' => 'RENTAL_EXPIRES_DATETIME',
        'TimeLeft' => 'RENTAL_TIMELEFT',
        'PortNum' => 'STREAM_PORT',
        'AdminUsername' => 'STREAM_ADMINUSERNAME',
        'AdminPassword' => 'STREAM_ADMINPASSWORD',
        'DjPassword' => 'STREAM_DJPASSWORD',
        'MountPoint' => 'STREAM_MOUNTPOINT',
        'Package' => 'PACKAGE_NAME',
        'Users' => 'PACKAGE_LISTENERS',
        'Kbps' => 'PACKAGE_BITRATE',
        'AutoDJ' => 'PACKAGE_AUTODJ',
        'Disk' => 'PACKAGE_AUTODJ_SIZE',
        'PackUID' => 'PACKAGE_UID',
        'RentUID' => 'RENTAL_UID',
    ];

    /** The message for one rental (its avatar, stream, package and server). */
    public static function forRental(string $template, Rental $rental): string
    {
        $rental->loadMissing(['avatar', 'package', 'stream.server']);
        $stream = $rental->stream;
        $server = $stream?->server;
        $package = $rental->package;
        $name = explode(' ', (string) $rental->avatar?->avatar_name, 2);

        return self::apply($template, [
            'AVATAR_FIRSTNAME' => $name[0],
            'AVATAR_LASTNAME' => $name[1] ?? 'Resident',
            'AVATAR_FULLNAME' => $rental->avatar?->avatar_name,
            'RENTAL_EXPIRES_DATETIME' => self::expiresAt($rental->expire_unixtime),
            'RENTAL_TIMELEFT' => self::timeLeft($rental->expire_unixtime),
            'RENTAL_UID' => $rental->rental_uid,
            'STREAM_PORT' => $stream?->port,
            'STREAM_ADMINUSERNAME' => $stream?->admin_username,
            'STREAM_ADMINPASSWORD' => $stream?->admin_password,
            'STREAM_DJPASSWORD' => $stream?->dj_password,
            'STREAM_MOUNTPOINT' => $stream?->mountpoint,
            'SERVER_DOMAIN' => $server?->domain,
            'SERVER_IP' => $server?->ip_address,
            'SERVER_CONTROLPANEL' => $server?->control_panel_url,
            'PACKAGE_NAME' => $package?->name,
            'PACKAGE_UID' => $package?->package_uid,
            'PACKAGE_LISTENERS' => $package?->listeners,
            'PACKAGE_BITRATE' => $package?->bitrate,
            'PACKAGE_AUTODJ' => $package?->autodj ? 'Enabled' : 'Disabled',
            'PACKAGE_AUTODJ_SIZE' => $package?->autodj_size,
            'TIMEZONE' => date_default_timezone_get(),
            'NL' => "\n",
        ]);
    }

    /** The message filled with the example values, for previews. */
    public static function example(string $template): string
    {
        return self::apply($template, self::examples());
    }

    /** @param array<string, mixed> $values */
    private static function apply(string $template, array $values): string
    {
        foreach (self::ALIASES as $alias => $tag) {
            $values[$alias] = $values[$tag];
        }
        $replace = [];
        foreach ($values as $tag => $value) {
            $replace['[['.$tag.']]'] = (string) $value;
        }

        return strtr(str_replace(["\r", "\n"], '', $template), $replace);
    }

    private static function expiresAt(?CarbonInterface $at): string
    {
        return $at?->format('l jS \of F Y h:i:s A') ?? '';
    }

    /** "3 days, 4 hours", "4 hours, 12 mins", "12 mins" or "Expired" (2.0.x wording). */
    public static function timeLeft(?CarbonInterface $at): string
    {
        $seconds = $at === null ? 0 : $at->getTimestamp() - time();
        if ($seconds <= 0) {
            return 'Expired';
        }
        $mins = intdiv($seconds, 60);
        $hours = intdiv($mins, 60);
        $days = intdiv($hours, 24);
        if ($days > 0) {
            return $days.' days, '.($hours - $days * 24).' hours';
        }
        if ($hours > 0) {
            return $hours.' hours, '.($mins - $hours * 60).' mins';
        }

        return $mins.' mins';
    }
}
