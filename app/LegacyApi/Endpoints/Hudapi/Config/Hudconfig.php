<?php

namespace App\LegacyApi\Endpoints\Hudapi\Config;

use App\LegacyApi\HudEndpoint;

/** What the renter HUD offers. Renewals need details, and are off while the rental time limit is on. */
class Hudconfig extends HudEndpoint
{
    public function process(): void
    {
        $config = $this->slConfig();
        $this->ok('Get config [ok]');
        $this->set('AllowDiscord', $config->hud_allow_discord);
        $this->set('AllowGroup', $config->hud_allow_group);
        $this->set('AllowDetails', $config->hud_allow_details);
        $this->set('AllowRenewal', $config->hud_allow_renewal && $config->hud_allow_details && ! $config->limit_time);
        $this->set('GroupLink', $config->hud_group_link);
        $this->set('DiscordLink', $config->hud_discord_link);
    }
}
