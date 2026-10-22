<?php

namespace App\LegacyApi;

/**
 * hud.php endpoints: requests from a client's renter HUD (port of 2.0.x SecondlifeHudAjax).
 * Signed with the HUD link code; the HUD's wearer is the "owner" avatar, and no object or
 * reseller is recorded.
 */
abstract class HudEndpoint extends SlEndpoint
{
    protected bool $trackObject = false;

    protected function linkCode(): string
    {
        return (string) $this->slConfig()->hud_link_code;
    }

    protected function hashFailedMessage(): string
    {
        return 'Unable to vaildate request to API endpoint: ';
    }
}
