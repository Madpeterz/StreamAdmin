<?php

namespace App\LegacyApi\Endpoints\Hudapi\Details;

use App\LegacyApi\Endpoints\Secondlifeapi\Details\Send;
use App\LegacyApi\HudEndpoint;

/** The HUD wearer asks for one of their rentals' details again (the Details/Send reply). */
class Resend extends HudEndpoint
{
    public function process(): void
    {
        if (! $this->slConfig()->hud_allow_details) {
            $this->failed('Hud details are currently disabled');

            return;
        }
        $this->output->reset();
        (new Send($this->input, $this->output, $this->request, $this->module, $this->area))->send($this->ownerAvatar);
    }
}
