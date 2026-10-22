<?php

namespace App\LegacyApi\Endpoints\Hudapi\Rentals;

use App\LegacyApi\Endpoints\Secondlifeapi\Renew\Details as RenewDetails;
use App\LegacyApi\HudEndpoint;

/** The HUD wearer's rentals (the Renew/Details reply). */
class Details extends HudEndpoint
{
    public function process(): void
    {
        if (! $this->slConfig()->hud_allow_details) {
            $this->failed('Hud details are currently disabled');

            return;
        }
        $this->output->reset();
        (new RenewDetails($this->input, $this->output, $this->request, $this->module, $this->area))->detailsFor($this->ownerAvatar);
    }
}
