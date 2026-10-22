<?php

namespace App\LegacyApi\Endpoints\Secondlifeapi\Object;

use App\LegacyApi\SlEndpoint;

/** The support key's code part, for the owner's server objects (the 2.0.x spelling is kept). */
class Severrequestcode extends SlEndpoint
{
    public function process(): void
    {
        if (! $this->hasAccessOwner()) {
            return;
        }
        $this->ok(explode('*', (string) $this->slConfig()->payment_key)[0]);
    }
}
