<?php

declare(strict_types=1);

namespace Contract;

/**
 * Link codes seeded by sql/reset.sql. Each API uses a different code so a request
 * signed for one API is rejected by the others.
 */
final class Secrets
{
    public const SL_LINK_CODE = "ctSlCode01";
    public const HUD_LINK_CODE = "ctHudCode01";
    /** Requests from the system owner's avatar (3.0): set by Database::rebuild after the migrations ran. */
    public const OWNER_LINK_CODE = "ctOwnerCode01";
    public const HTTP_INBOUND_SECRET = "contractsecret";
    public const ANS_SALT = "contract-ans-salt";
    /** botconfig.secret from sql/reset.sql, signs bot commands. */
    public const BOT_SECRET = "Signed command code";

    /**
     * Short lived sys.php key the bot receives in FetchNextNotecard (src/App/Helpers/SystemApiKey.php).
     * $windowOffset 0 = current hour window, -1 = previous (still accepted), -2 = expired.
     */
    public static function sysKey(string $method, string $action, int $windowOffset = 0): string
    {
        $window = intdiv(time(), 3600) + $windowOffset;
        return hash_hmac("sha256", "sys|" . $method . "|" . $action . "|" . $window, self::HTTP_INBOUND_SECRET);
    }

    public static function forKind(string $kind): string
    {
        return match ($kind) {
            "sl" => self::SL_LINK_CODE,
            "hud" => self::HUD_LINK_CODE,
            "sys" => self::HTTP_INBOUND_SECRET,
            "ans" => self::ANS_SALT,
            default => throw new \InvalidArgumentException("No secret for kind: " . $kind),
        };
    }
}
