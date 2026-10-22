<?php

namespace App\LegacyApi\Endpoints\Hudapi\Rentals;

use App\LegacyApi\Endpoints\Secondlifeapi\Renew\Costandtime;
use App\LegacyApi\HudEndpoint;

/** Renewal price of one of the HUD wearer's rentals (the Renew/Costandtime reply). */
class Costs extends HudEndpoint
{
    public function process(): void
    {
        $config = $this->slConfig();
        if (! $config->hud_allow_details) {
            $this->failed('Hud details are currently disabled');

            return;
        }
        if (! $config->hud_allow_renewal) {
            $this->failed('Hud renewals are currently disabled');

            return;
        }
        $this->output->reset();
        (new Costandtime($this->input, $this->output, $this->request, $this->module, $this->area))->costOf($this->ownerAvatar);
    }
}
