<?php

namespace App\Support;

/**
 * StreamAdmin support key (port of the 2.0.x Slconfig/Paymentkey checks).
 *
 * A key is 23 characters, "<code>:<expires unixtime>*<check>", where <check> is the first 3
 * characters of sha1(code . site URL . expires . "web"), so a key only works on the site it was
 * issued for. The site URL is 2.0.x's SITE_URL, which ends with a slash.
 */
class PaymentKey
{
    /** @return array{ok: bool, status: string, expires: ?int} */
    public static function check(?string $key, ?string $siteUrl = null): array
    {
        $siteUrl ??= rtrim((string) config('app.url'), '/').'/';
        if ($key === null || strlen($key) !== 23) {
            return ['ok' => false, 'status' => 'No key', 'expires' => null];
        }
        $parts = explode('*', $key);
        if (count($parts) !== 2) {
            return ['ok' => false, 'status' => 'Old key format', 'expires' => null];
        }
        $core = explode(':', $parts[0]);
        if (count($core) !== 2 || ! ctype_digit($core[1])) {
            return ['ok' => false, 'status' => 'Not a valid key', 'expires' => null];
        }
        if (substr(sha1($core[0].$siteUrl.$core[1].'web'), 0, 3) !== $parts[1]) {
            return ['ok' => false, 'status' => 'Issued for a different site', 'expires' => null];
        }
        $expires = (int) $core[1];
        if (time() > $expires) {
            return ['ok' => false, 'status' => 'Expired', 'expires' => $expires];
        }

        return ['ok' => true, 'status' => 'Valid', 'expires' => $expires];
    }
}
