<?php

namespace App\Api;

use App\Models\SlConfig;

/**
 * The kinds of key an in-world caller signs with: one shared secret per class (System settings).
 * Vendor is resellers' objects, owner is the system owner's own objects, hud is the renter HUD,
 * bot is the Second Life bot.
 */
enum ApiClass: string
{
    case Vendor = 'vendor';
    case Owner = 'owner';
    case Hud = 'hud';
    case Bot = 'bot';

    public function secret(SlConfig $config): string
    {
        return (string) match ($this) {
            self::Vendor => $config->sl_link_code,
            self::Owner => $config->owner_link_code,
            self::Hud => $config->hud_link_code,
            self::Bot => $config->http_inbound_secret,
        };
    }
}
